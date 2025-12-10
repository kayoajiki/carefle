<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Services\ReflectionContextService;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeeklyReflectionService
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
     * 週次の振り返りサマリーを生成
     */
    public function generateWeeklySummary(?Carbon $weekStart = null): ?array
    {
        $userId = Auth::id();
        
        if (!$weekStart) {
            $weekStart = Carbon::now()->startOfWeek();
        }
        $weekEnd = $weekStart->copy()->endOfWeek();

        $diaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date')
            ->get();

        if ($diaries->isEmpty()) {
            return null;
        }

        return $this->generateSummary($diaries, 'weekly', $weekStart, $weekEnd);
    }

    /**
     * 月次の振り返りサマリーを生成
     */
    public function generateMonthlySummary(?Carbon $monthStart = null): ?array
    {
        $userId = Auth::id();
        
        if (!$monthStart) {
            $monthStart = Carbon::now()->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        $diaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date')
            ->get();

        if ($diaries->isEmpty()) {
            return null;
        }

        return $this->generateSummary($diaries, 'monthly', $monthStart, $monthEnd);
    }

    /**
     * サマリーを生成
     */
    protected function generateSummary($diaries, string $type, Carbon $startDate, Carbon $endDate): ?array
    {
        try {
            $context = $this->contextService->buildContextForUser();
            
            // 日記内容をまとめる
            $diaryContents = [];
            $motivationScores = [];
            foreach ($diaries as $diary) {
                $diaryContents[] = [
                    'date' => $diary->date->format('Y年m月d日'),
                    'content' => $diary->content,
                    'motivation' => $diary->motivation,
                ];
                $motivationScores[] = $diary->motivation;
            }

            $avgMotivation = count($motivationScores) > 0 
                ? round(array_sum($motivationScores) / count($motivationScores), 1)
                : 0;

            // プロンプトを構築
            $periodLabel = $type === 'weekly' ? '週' : '月';
            $prompt = $this->buildSummaryPrompt($diaryContents, $context, $periodLabel, $startDate, $endDate, $avgMotivation);

            // AIからサマリーを生成
            $summary = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if (!$summary) {
                return null;
            }

            // 気づきを抽出
            $insights = $this->extractInsights($diaryContents, $context, $periodLabel);

            return [
                'summary' => $summary,
                'insights' => $insights,
                'period' => $periodLabel,
                'start_date' => $startDate->format('Y年m月d日'),
                'end_date' => $endDate->format('Y年m月d日'),
                'diary_count' => $diaries->count(),
                'avg_motivation' => $avgMotivation,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate reflection summary', [
                'error' => $e->getMessage(),
                'type' => $type,
            ]);
            return null;
        }
    }

    /**
     * サマリープロンプトを構築
     */
    protected function buildSummaryPrompt(array $diaryContents, string $context, string $periodLabel, Carbon $startDate, Carbon $endDate, float $avgMotivation): string
    {
        $prompt = "以下の期間（{$startDate->format('Y年m月d日')}〜{$endDate->format('Y年m月d日')}）の日記を振り返って、サマリーと気づきを提供してください。\n\n";
        
        $prompt .= "【期間中の日記】\n";
        foreach ($diaryContents as $diary) {
            $prompt .= "- {$diary['date']}（モチベーション: {$diary['motivation']}/100）\n";
            $prompt .= "  {$diary['content']}\n\n";
        }

        $prompt .= "【平均モチベーション】\n{$avgMotivation}/100\n\n";

        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考）】\n{$context}\n\n";
        }

        $prompt .= "【サマリーの内容】\n";
        $prompt .= "- この{$periodLabel}の主な出来事や気づきをまとめる\n";
        $prompt .= "- モチベーションの推移から見える傾向を分析する\n";
        $prompt .= "- 成長や変化を感じられるポイントを指摘する\n";
        $prompt .= "- 次の{$periodLabel}への提案や励ましを含める\n";
        $prompt .= "- 簡潔で読みやすい文章（5-7文程度）\n\n";
        $prompt .= "サマリーを生成してください:";

        return $prompt;
    }

    /**
     * 気づきを抽出
     */
    protected function extractInsights(array $diaryContents, string $context, string $periodLabel): array
    {
        try {
            $prompt = "以下の期間の日記から、重要な気づきを3-5個抽出してください。\n\n";
            $prompt .= "【日記内容】\n";
            foreach ($diaryContents as $diary) {
                $prompt .= "- {$diary['date']}: " . substr($diary['content'], 0, 200) . "...\n";
            }

            if (!empty($context)) {
                $prompt .= "\n【ユーザーの背景情報】\n{$context}\n\n";
            }

            $prompt .= "気づきは、以下のJSON形式で返してください:\n";
            $prompt .= '{"insights": ["気づき1", "気づき2", "気づき3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                // JSONを抽出
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['insights'])) {
                    return $json['insights'];
                }
            }

            // JSON抽出に失敗した場合、レスポンスから箇条書きを抽出
            return $this->extractInsightsFromText($response ?? '');
        } catch (\Exception $e) {
            Log::warning('Failed to extract insights', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        // JSONブロックを探す
        if (preg_match('/\{[^{}]*"insights"[^{}]*\}/s', $response, $matches)) {
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
     * テキストから気づきを抽出
     */
    protected function extractInsightsFromText(string $text): array
    {
        $insights = [];
        // 箇条書きや番号付きリストを抽出
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[-•*]\s*(.+)$/', $line, $matches) || 
                preg_match('/^\d+[\.\)]\s*(.+)$/', $line, $matches)) {
                $insights[] = $matches[1];
            }
        }
        return array_slice($insights, 0, 5); // 最大5個
    }
}



