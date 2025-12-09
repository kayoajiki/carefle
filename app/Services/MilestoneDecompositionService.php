<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\CareerMilestone;
use App\Models\MilestoneActionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MilestoneDecompositionService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 大きなマイルストーンを小さなステップに分解
     */
    public function decomposeMilestone(int $milestoneId): ?array
    {
        $userId = Auth::id();
        
        $milestone = CareerMilestone::where('id', $milestoneId)
            ->where('user_id', $userId)
            ->first();

        if (!$milestone) {
            return null;
        }

        // 既にアクションアイテムがある場合はスキップ
        $existingActions = $milestone->actionItems()->count();
        if ($existingActions > 0) {
            return [
                'message' => 'このマイルストーンには既にアクションアイテムが存在します。',
                'existing_count' => $existingActions,
            ];
        }

        try {
            // AIで小さなステップを生成
            $steps = $this->generateSteps($milestone);

            if (empty($steps)) {
                return null;
            }

            // アクションアイテムとして保存
            $this->saveStepsAsActionItems($milestone, $steps);

            return [
                'milestone_id' => $milestone->id,
                'steps_count' => count($steps),
                'steps' => $steps,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to decompose milestone', [
                'error' => $e->getMessage(),
                'milestone_id' => $milestoneId,
            ]);
            return null;
        }
    }

    /**
     * 小さなステップを生成
     */
    protected function generateSteps(CareerMilestone $milestone): array
    {
        $prompt = "以下のマイルストーンを、達成可能な小さなステップ（アクションアイテム）に分解してください。\n\n";
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

        $prompt .= "【要件】\n";
        $prompt .= "- 5-10個の小さなステップに分解する\n";
        $prompt .= "- 各ステップは具体的で実行可能なものにする\n";
        $prompt .= "- ステップは時系列で並べる（最初のステップから順に）\n";
        $prompt .= "- 各ステップには、タイトル、説明、推定所要時間を含める\n\n";
        $prompt .= "以下のJSON形式で返してください:\n";
        $prompt .= '{"steps": [{"title": "ステップ名", "description": "説明", "estimated_time": "所要時間", "order": 1}]}';

        $response = $this->bedrockService->chat(
            $prompt,
            [],
            config('bedrock.reflection_system_prompt')
        );

        if (!$response) {
            return [];
        }

        // JSONを抽出
        $json = $this->extractJsonFromResponse($response);
        if ($json && isset($json['steps'])) {
            return $json['steps'];
        }

        return [];
    }

    /**
     * ステップをアクションアイテムとして保存
     */
    protected function saveStepsAsActionItems(CareerMilestone $milestone, array $steps): void
    {
        foreach ($steps as $step) {
            MilestoneActionItem::create([
                'user_id' => Auth::id(),
                'career_milestone_id' => $milestone->id,
                'title' => $step['title'] ?? '',
                'description' => $step['description'] ?? '',
                'status' => 'pending',
                'priority' => $this->determinePriority($step['order'] ?? 0),
            ]);
        }
    }

    /**
     * 優先度を決定
     */
    protected function determinePriority(int $order): string
    {
        if ($order <= 3) {
            return 'high';
        } elseif ($order <= 6) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        // JSONブロックを探す
        if (preg_match('/\{[^{}]*"steps"[^{}]*\}/s', $response, $matches)) {
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


