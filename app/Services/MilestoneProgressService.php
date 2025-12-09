<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Services\ReflectionContextService;
use App\Models\CareerMilestone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MilestoneProgressService
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
     * マイルストーンの進捗を分析し、フィードバックを生成
     */
    public function analyzeProgress(int $milestoneId): ?array
    {
        $userId = Auth::id();
        
        $milestone = CareerMilestone::where('id', $milestoneId)
            ->where('user_id', $userId)
            ->with(['actionItems'])
            ->first();

        if (!$milestone) {
            return null;
        }

        // 進捗データを計算
        $progressData = $this->calculateProgress($milestone);

        // AIからフィードバックを生成
        $feedback = $this->generateFeedback($milestone, $progressData);

        return [
            'milestone' => $milestone,
            'progress' => $progressData,
            'feedback' => $feedback,
        ];
    }

    /**
     * 進捗データを計算
     */
    protected function calculateProgress(CareerMilestone $milestone): array
    {
        $totalActions = $milestone->actionItems()->count();
        $completedActions = $milestone->actionItems()->where('status', 'completed')->count();
        $inProgressActions = $milestone->actionItems()->where('status', 'in_progress')->count();
        $pendingActions = $milestone->actionItems()->where('status', 'pending')->count();

        $completionRate = $totalActions > 0 
            ? round(($completedActions / $totalActions) * 100, 1)
            : 0;

        $daysRemaining = null;
        if ($milestone->target_date) {
            $daysRemaining = max(0, now()->diffInDays($milestone->target_date, false));
        }

        $progressPoints = $milestone->progress_points ?? 0;
        $achievementRate = $milestone->achievement_rate ?? 0;

        return [
            'total_actions' => $totalActions,
            'completed_actions' => $completedActions,
            'in_progress_actions' => $inProgressActions,
            'pending_actions' => $pendingActions,
            'completion_rate' => $completionRate,
            'days_remaining' => $daysRemaining,
            'progress_points' => $progressPoints,
            'achievement_rate' => $achievementRate,
            'status' => $milestone->status,
        ];
    }

    /**
     * フィードバックを生成
     */
    protected function generateFeedback(CareerMilestone $milestone, array $progressData): ?string
    {
        try {
            $context = $this->contextService->buildContextForUser();
            
            $prompt = $this->buildFeedbackPrompt($milestone, $progressData, $context);
            
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to generate milestone progress feedback', [
                'error' => $e->getMessage(),
                'milestone_id' => $milestone->id,
            ]);
            return null;
        }
    }

    /**
     * フィードバックプロンプトを構築
     */
    protected function buildFeedbackPrompt(CareerMilestone $milestone, array $progressData, string $context): string
    {
        $prompt = "以下のマイルストーンの進捗状況を分析し、フィードバックを提供してください。\n\n";
        
        $prompt .= "【マイルストーン】\n";
        $prompt .= "タイトル: {$milestone->title}\n";
        if ($milestone->description) {
            $prompt .= "説明: {$milestone->description}\n";
        }
        if ($milestone->target_date) {
            $prompt .= "目標日: {$milestone->target_date->format('Y年m月d日')}\n";
        }
        $prompt .= "ステータス: {$milestone->status}\n\n";

        $prompt .= "【進捗状況】\n";
        $prompt .= "完了アクション: {$progressData['completed_actions']}/{$progressData['total_actions']}\n";
        $prompt .= "進行中アクション: {$progressData['in_progress_actions']}\n";
        $prompt .= "未着手アクション: {$progressData['pending_actions']}\n";
        $prompt .= "完了率: {$progressData['completion_rate']}%\n";
        if ($progressData['days_remaining'] !== null) {
            $prompt .= "残り日数: {$progressData['days_remaining']}日\n";
        }
        $prompt .= "進捗ポイント: {$progressData['progress_points']}\n";
        $prompt .= "達成率: {$progressData['achievement_rate']}%\n\n";

        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考）】\n{$context}\n\n";
        }

        $prompt .= "【フィードバックの内容】\n";
        $prompt .= "- 進捗状況を評価する\n";
        $prompt .= "- 良い点や成長を認める\n";
        $prompt .= "- 改善点や次のステップを提案する\n";
        $prompt .= "- 目標達成に向けた励ましを含める\n";
        $prompt .= "- 簡潔で読みやすい文章（4-6文程度）\n\n";
        $prompt .= "フィードバックを生成してください:";

        return $prompt;
    }
}


