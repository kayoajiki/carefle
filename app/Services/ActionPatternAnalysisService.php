<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\MilestoneActionItem;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActionPatternAnalysisService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 行動パターンを分析
     */
    public function analyzePatterns(?Carbon $startDate = null, ?Carbon $endDate = null): ?array
    {
        $userId = Auth::id();
        
        if (!$startDate) {
            $startDate = Carbon::now()->subMonth();
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        }

        // 完了したアクションアイテムを取得
        $completedActions = MilestoneActionItem::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->with(['milestone'])
            ->get();

        // 日記を取得
        $diaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get();

        // パターンを分析
        $frequency = $this->analyzeFrequency($completedActions, $diaries);
        $patterns = $this->analyzePatterns($completedActions, $diaries);
        $effectiveActions = $this->identifyEffectiveActions($completedActions, $diaries);

        return [
            'frequency' => $frequency,
            'patterns' => $patterns,
            'effective_actions' => $effectiveActions,
            'period' => [
                'start' => $startDate->format('Y年m月d日'),
                'end' => $endDate->format('Y年m月d日'),
            ],
        ];
    }

    /**
     * 頻度を分析
     */
    protected function analyzeFrequency($completedActions, $diaries): array
    {
        // 週ごとの完了数
        $weeklyCompletion = [];
        foreach ($completedActions as $action) {
            $week = $action->completed_at->format('Y-W');
            $weeklyCompletion[$week] = ($weeklyCompletion[$week] ?? 0) + 1;
        }

        // 日記の頻度
        $diaryFrequency = $diaries->count();

        return [
            'total_completed' => $completedActions->count(),
            'weekly_average' => count($weeklyCompletion) > 0 
                ? round(array_sum($weeklyCompletion) / count($weeklyCompletion), 1)
                : 0,
            'diary_frequency' => $diaryFrequency,
            'weekly_completion' => $weeklyCompletion,
        ];
    }

    /**
     * パターンを分析
     */
    protected function analyzePatterns($completedActions, $diaries): array
    {
        // 曜日ごとの完了傾向
        $dayOfWeek = [];
        foreach ($completedActions as $action) {
            $day = $action->completed_at->dayOfWeek;
            $dayOfWeek[$day] = ($dayOfWeek[$day] ?? 0) + 1;
        }

        // 時間帯ごとの完了傾向（簡易実装）
        $timeOfDay = [];
        foreach ($completedActions as $action) {
            $hour = $action->completed_at->hour;
            $timeSlot = $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');
            $timeOfDay[$timeSlot] = ($timeOfDay[$timeSlot] ?? 0) + 1;
        }

        return [
            'day_of_week' => $dayOfWeek,
            'time_of_day' => $timeOfDay,
        ];
    }

    /**
     * 効果的な行動を特定
     */
    protected function identifyEffectiveActions($completedActions, $diaries): array
    {
        try {
            // 完了したアクションのタイトルを収集
            $actionTitles = $completedActions->pluck('title')->toArray();
            
            if (empty($actionTitles)) {
                return [];
            }

            $prompt = "以下の完了したアクションから、効果的だったと思われる行動パターンを3-5個特定してください。\n\n";
            $prompt .= "【完了したアクション】\n";
            foreach (array_slice($actionTitles, 0, 20) as $title) {
                $prompt .= "- {$title}\n";
            }

            $prompt .= "\n【分析のポイント】\n";
            $prompt .= "- 繰り返し実行されている行動\n";
            $prompt .= "- 目標達成に貢献していると思われる行動\n";
            $prompt .= "- 効果的な行動パターンを簡潔にまとめる\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"effective_actions": ["パターン1", "パターン2", "パターン3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['effective_actions'])) {
                    return $json['effective_actions'];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to identify effective actions', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        if (preg_match('/\{[^{}]*"effective_actions"[^{}]*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        return null;
    }
}

