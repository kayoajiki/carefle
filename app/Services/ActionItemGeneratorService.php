<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\CareerMilestone;
use App\Models\MilestoneActionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActionItemGeneratorService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 日記内容からアクションアイテムを生成
     */
    public function generateActionItemsFromDiary(string $diaryContent, ?int $milestoneId = null): array
    {
        $userId = Auth::id();
        
        // 関連するマイルストーンを取得
        $milestones = $milestoneId 
            ? CareerMilestone::where('id', $milestoneId)->where('user_id', $userId)->get()
            : CareerMilestone::where('user_id', $userId)
                ->whereIn('status', ['planned', 'in_progress'])
                ->get();

        if ($milestones->isEmpty()) {
            return [];
        }

        $suggestedActions = [];

        foreach ($milestones as $milestone) {
            $prompt = "以下の日記内容から、マイルストーン「{$milestone->title}」に関連する具体的なアクションアイテムを1-3個提案してください。\n\n";
            $prompt .= "【日記内容】\n{$diaryContent}\n\n";
            $prompt .= "【マイルストーン】\n";
            $prompt .= "タイトル: {$milestone->title}\n";
            if ($milestone->description) {
                $prompt .= "説明: {$milestone->description}\n";
            }
            if ($milestone->will_theme) {
                $prompt .= "テーマ: {$milestone->will_theme}\n";
            }
            $prompt .= "\n";
            $prompt .= "アクションアイテムは、具体的で実行可能なものにしてください。\n";
            $prompt .= "JSON形式で返してください: {\"actions\": [{\"title\": \"アクション名\", \"description\": \"説明\"}]}";

            $response = $this->bedrockService->chat($prompt, []);

            if ($response) {
                // JSONをパース
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['actions'])) {
                    foreach ($json['actions'] as $action) {
                        $suggestedActions[] = [
                            'milestone_id' => $milestone->id,
                            'title' => $action['title'] ?? '',
                            'description' => $action['description'] ?? '',
                        ];
                    }
                }
            }
        }

        return $suggestedActions;
    }

    /**
     * 提案されたアクションアイテムを保存
     */
    public function saveSuggestedActions(array $suggestedActions, int $diaryId): void
    {
        foreach ($suggestedActions as $action) {
            MilestoneActionItem::create([
                'user_id' => Auth::id(),
                'career_milestone_id' => $action['milestone_id'],
                'title' => $action['title'],
                'description' => $action['description'],
                'status' => 'pending',
                'diary_id' => $diaryId,
            ]);
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    private function extractJsonFromResponse(string $response): ?array
    {
        // JSONブロックを探す
        if (preg_match('/\{[^{}]*"actions"[^{}]*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        // 全体をJSONとしてパースを試みる
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        return null;
    }
}

