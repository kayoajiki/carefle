<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\CareerMilestone;
use App\Models\WcmSheet;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoalConnectionService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 日記内容とマイルストーン・WCMシートのWillテーマの関連性を検出
     */
    public function detectConnections(Diary $diary): array
    {
        if (empty($diary->content)) {
            return [];
        }

        $userId = $diary->user_id;
        $connections = [];

        // マイルストーンとの関連性を検出
        $milestoneConnections = $this->detectMilestoneConnections($diary, $userId);
        $connections = array_merge($connections, $milestoneConnections);

        // WCMシートのWillテーマとの関連性を検出
        $wcmConnections = $this->detectWcmWillConnections($diary, $userId);
        $connections = array_merge($connections, $wcmConnections);

        return $connections;
    }

    /**
     * マイルストーンとの関連性を検出
     */
    protected function detectMilestoneConnections(Diary $diary, int $userId): array
    {
        $milestones = CareerMilestone::where('user_id', $userId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->get();

        if ($milestones->isEmpty()) {
            return [];
        }

        $connections = [];

        foreach ($milestones as $milestone) {
            $connection = $this->analyzeMilestoneConnection($diary, $milestone);
            if ($connection && $connection['score'] >= 50) {
                $connections[] = $connection;
            }
        }

        // スコアの高い順にソート
        usort($connections, fn($a, $b) => $b['score'] <=> $a['score']);

        return $connections;
    }

    /**
     * マイルストーンとの関連性を分析
     */
    protected function analyzeMilestoneConnection(Diary $diary, CareerMilestone $milestone): ?array
    {
        $prompt = "以下の日記内容が、マイルストーン「{$milestone->title}」とどの程度関連しているかを分析してください。\n\n";
        $prompt .= "【日記内容】\n{$diary->content}\n\n";
        $prompt .= "【マイルストーン】\n";
        $prompt .= "タイトル: {$milestone->title}\n";
        if ($milestone->description) {
            $prompt .= "説明: {$milestone->description}\n";
        }
        if ($milestone->will_theme) {
            $prompt .= "テーマ: {$milestone->will_theme}\n";
        }
        $prompt .= "\n";
        $prompt .= "以下のJSON形式で返してください：\n";
        $prompt .= "{\n";
        $prompt .= "  \"score\": 0-100の整数（関連度。50以上で関連ありと判断）\n";
        $prompt .= "  \"reason\": \"関連している理由を1-2文で説明\"\n";
        $prompt .= "  \"will_theme\": \"関連するWillテーマ（マイルストーンのテーマ）\"\n";
        $prompt .= "}\n";
        $prompt .= "関連性が低い場合（score < 50）は、nullを返してください。";

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['score']) && $json['score'] >= 50) {
                    return [
                        'diary_id' => $diary->id,
                        'connection_type' => 'milestone',
                        'connected_id' => $milestone->id,
                        'connection_score' => (int)$json['score'],
                        'connection_reason' => $json['reason'] ?? '',
                        'will_theme' => $json['will_theme'] ?? $milestone->will_theme,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to analyze milestone connection', [
                'error' => $e->getMessage(),
                'diary_id' => $diary->id,
                'milestone_id' => $milestone->id,
            ]);
        }

        return null;
    }

    /**
     * WCMシートのWillテーマとの関連性を検出
     */
    protected function detectWcmWillConnections(Diary $diary, int $userId): array
    {
        $wcmSheets = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->limit(3)
            ->get();

        if ($wcmSheets->isEmpty()) {
            return [];
        }

        $connections = [];

        foreach ($wcmSheets as $wcmSheet) {
            if (empty($wcmSheet->will_text)) {
                continue;
            }

            $connection = $this->analyzeWcmWillConnection($diary, $wcmSheet);
            if ($connection && $connection['score'] >= 50) {
                $connections[] = $connection;
            }
        }

        // スコアの高い順にソート
        usort($connections, fn($a, $b) => $b['score'] <=> $a['score']);

        return $connections;
    }

    /**
     * WCMシートのWillテーマとの関連性を分析
     */
    protected function analyzeWcmWillConnection(Diary $diary, WcmSheet $wcmSheet): ?array
    {
        $prompt = "以下の日記内容が、WCMシートのWill（こう在りたい）テーマとどの程度関連しているかを分析してください。\n\n";
        $prompt .= "【日記内容】\n{$diary->content}\n\n";
        $prompt .= "【Will（こう在りたい）】\n{$wcmSheet->will_text}\n\n";
        $prompt .= "以下のJSON形式で返してください：\n";
        $prompt .= "{\n";
        $prompt .= "  \"score\": 0-100の整数（関連度。50以上で関連ありと判断）\n";
        $prompt .= "  \"reason\": \"関連している理由を1-2文で説明\"\n";
        $prompt .= "  \"will_theme\": \"関連するWillテーマの要約（1-2文）\"\n";
        $prompt .= "}\n";
        $prompt .= "関連性が低い場合（score < 50）は、nullを返してください。";

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['score']) && $json['score'] >= 50) {
                    return [
                        'diary_id' => $diary->id,
                        'connection_type' => 'wcm_will',
                        'connected_id' => $wcmSheet->id,
                        'connection_score' => (int)$json['score'],
                        'connection_reason' => $json['reason'] ?? '',
                        'will_theme' => $json['will_theme'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to analyze WCM Will connection', [
                'error' => $e->getMessage(),
                'diary_id' => $diary->id,
                'wcm_sheet_id' => $wcmSheet->id,
            ]);
        }

        return null;
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        // JSONブロックを探す
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['score'])) {
                return $json;
            }
        }

        // 全体をJSONとしてパースを試みる
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json['score'])) {
            return $json;
        }

        return null;
    }
}