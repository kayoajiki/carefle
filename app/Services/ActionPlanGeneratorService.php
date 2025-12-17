<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Services\ReflectionContextService;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActionPlanGeneratorService
{
    protected BedrockService $bedrockService;
    protected ReflectionContextService $contextService;

    public function __construct(
        BedrockService $bedrockService,
        ReflectionContextService $contextService
    ) {
        $this->bedrockService = $bedrockService;
        $this->contextService = $contextService;
    }

    /**
     * WCMシートからアクションプランを生成
     */
    public function generateFromWcmSheet(?int $wcmSheetId = null): ?array
    {
        $userId = Auth::id();
        
        $wcmSheet = $wcmSheetId
            ? WcmSheet::where('id', $wcmSheetId)->where('user_id', $userId)->first()
            : WcmSheet::where('user_id', $userId)
                ->where('is_draft', false)
                ->latest('updated_at')
                ->first();

        if (!$wcmSheet) {
            return null;
        }

        return $this->generateActionPlan([
            'type' => 'wcm',
            'wcm_sheet' => $wcmSheet,
        ]);
    }

    /**
     * マイルストーンからアクションプランを生成
     */
    public function generateFromMilestone(int $milestoneId): ?array
    {
        $userId = Auth::id();
        
        $milestone = CareerMilestone::where('id', $milestoneId)
            ->where('user_id', $userId)
            ->first();

        if (!$milestone) {
            return null;
        }

        return $this->generateActionPlan([
            'type' => 'milestone',
            'milestone' => $milestone,
        ]);
    }

    /**
     * アクションプランを生成
     */
    protected function generateActionPlan(array $data): ?array
    {
        try {
            $context = $this->contextService->buildContextForUser();
            
            $prompt = $this->buildActionPlanPrompt($data, $context);
            
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if (!$response) {
                return null;
            }

            // アクションプランをパース
            $actionPlan = $this->parseActionPlan($response);

            // アクションを優先度順にソートし、最大5個に制限
            $actions = $actionPlan['actions'] ?? [];
            $actions = $this->sortActionsByPriority($actions);
            $actions = array_slice($actions, 0, 5); // 最大5個に制限

            return [
                'title' => $actionPlan['title'] ?? 'アクションプラン',
                'description' => $actionPlan['description'] ?? '',
                'actions' => $actions,
                'source_type' => $data['type'],
                'source_id' => $data['type'] === 'wcm' 
                    ? $data['wcm_sheet']->id 
                    : $data['milestone']->id,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate action plan', [
                'error' => $e->getMessage(),
                'type' => $data['type'],
            ]);
            return null;
        }
    }

    /**
     * アクションプランプロンプトを構築
     */
    protected function buildActionPlanPrompt(array $data, string $context): string
    {
        $prompt = "以下の情報を基に、具体的で実行可能なアクションプランを提案してください。\n\n";

        if ($data['type'] === 'wcm') {
            $wcmSheet = $data['wcm_sheet'];
            $prompt .= "【WCMシート】\n";
            $prompt .= "Will（こう在りたい）: " . ($wcmSheet->will_text ?? '未設定') . "\n";
            $prompt .= "Can（できること）: " . ($wcmSheet->can_text ?? '未設定') . "\n";
            $prompt .= "Must（やるべきこと）: " . ($wcmSheet->must_text ?? '未設定') . "\n\n";
        } else {
            $milestone = $data['milestone'];
            $prompt .= "【マイルストーン】\n";
            $prompt .= "タイトル: {$milestone->title}\n";
            if ($milestone->description) {
                $prompt .= "説明: {$milestone->description}\n";
            }
            if ($milestone->will_theme) {
                $prompt .= "テーマ: {$milestone->will_theme}\n";
            }
            if ($milestone->target_date) {
                $prompt .= "目標日: {$milestone->target_date->format('Y年m月d日')}\n";
            }
            $prompt .= "\n";
        }

        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考）】\n{$context}\n\n";
        }

        $prompt .= "【アクションプランの要件】\n";
        $prompt .= "- 具体的で実行可能なアクションを5個提案する\n";
        $prompt .= "- 各アクションには、タイトル、説明、優先度（高/中/低）、推定所要時間を含める\n";
        $prompt .= "- 優先度の高いものから順に並べる（高→中→低）\n";
        $prompt .= "- 現実的で達成可能な内容にする\n\n";
        $prompt .= "以下のJSON形式で返してください:\n";
        $prompt .= '{"title": "アクションプランのタイトル", "description": "概要", "actions": [{"title": "アクション名", "description": "説明", "priority": "高|中|低", "estimated_time": "所要時間"}]}';

        return $prompt;
    }

    /**
     * アクションプランをパース
     */
    protected function parseActionPlan(string $response): array
    {
        // JSONを抽出
        $json = $this->extractJsonFromResponse($response);
        if ($json) {
            return $json;
        }

        // JSON抽出に失敗した場合、デフォルト値を返す
        return [
            'title' => 'アクションプラン',
            'description' => $response,
            'actions' => [],
        ];
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
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

    /**
     * アクションを優先度順にソート
     */
    protected function sortActionsByPriority(array $actions): array
    {
        $priorityOrder = ['高' => 1, '中' => 2, '低' => 3];
        
        usort($actions, function ($a, $b) use ($priorityOrder) {
            $priorityA = $priorityOrder[$a['priority'] ?? '低'] ?? 3;
            $priorityB = $priorityOrder[$b['priority'] ?? '低'] ?? 3;
            
            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }
            
            // 優先度が同じ場合は元の順序を保持
            return 0;
        });
        
        return $actions;
    }
}
