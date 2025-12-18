<?php

namespace App\Services;

use App\Models\Diary;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Log;

class DiaryAnalysisService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 指定日数の日記を分析
     */
    public function analyzeDiaries(int $userId, int $days = 7): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $diaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date')
            ->get();

        if ($diaries->isEmpty()) {
            return [
                'has_data' => false,
                'summary' => '分析できる日記がまだありません。',
            ];
        }

        // 価値観と強みを抽出
        $valuesAndStrengths = $this->extractValuesAndStrengths($diaries);

        // AIを使用して統合分析
        $aiAnalysis = null;
        try {
            $aiAnalysis = $this->generateAIAnalysis($diaries);
        } catch (\Exception $e) {
            Log::warning('Failed to generate AI analysis for diaries', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }

        return [
            'has_data' => true,
            'diary_count' => $diaries->count(),
            'days_analyzed' => $days,
            'values_and_strengths' => $valuesAndStrengths,
            'ai_analysis' => $aiAnalysis,
        ];
    }

    /**
     * 価値観と強みを抽出
     */
    protected function extractValuesAndStrengths($diaries): array
    {
        $result = [
            'values' => [],
            'strengths' => [],
            'themes' => [],
        ];

        // 簡易的な分析（本質のみ）
        // モチベーションの平均を計算
        $motivations = $diaries->pluck('motivation')->filter()->toArray();
        if (!empty($motivations)) {
            $avgMotivation = round(array_sum($motivations) / count($motivations));
            if ($avgMotivation >= 70) {
                $result['strengths'][] = '高いモチベーションを維持できています';
            }
        }

        // 日記の内容から頻出キーワードを抽出（簡易版）
        $allContent = $diaries->pluck('content')->filter()->implode(' ');
        
        // よく使われる動詞や名詞を抽出（簡易版）
        $keywords = $this->extractKeywords($allContent);
        if (!empty($keywords)) {
            $result['themes'] = array_slice($keywords, 0, 5);
        }

        return $result;
    }

    /**
     * キーワードを抽出（簡易版）
     */
    protected function extractKeywords(string $text): array
    {
        // 日本語の助詞や助動詞を除去
        $stopWords = ['の', 'に', 'は', 'を', 'が', 'と', 'で', 'も', 'から', 'まで', 'より', 'など', 'こと', 'もの', 'ため'];
        
        // テキストを単語に分割（簡易版）
        $words = preg_split('/[\s、。！？\n\r]+/u', $text);
        $words = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) >= 2 && !in_array($word, $stopWords);
        });

        // 頻度をカウント
        $wordCounts = array_count_values($words);
        arsort($wordCounts);

        return array_keys(array_slice($wordCounts, 0, 10));
    }

    /**
     * AIを使用して統合分析を生成
     */
    protected function generateAIAnalysis($diaries): ?string
    {
        $prompt = "以下の{$diaries->count()}件の日記を分析して、ユーザーの価値観、強み、成長ポイントを簡潔に（3-5文程度）まとめてください。\n\n";

        foreach ($diaries as $index => $diary) {
            $content = mb_substr($diary->content, 0, 200); // 最初の200文字のみ
            $prompt .= "【日記" . ($index + 1) . "】{$diary->date->format('n月j日')}\n";
            $prompt .= "モチベーション: {$diary->motivation}/100\n";
            $prompt .= "内容: {$content}...\n\n";
        }

        $prompt .= "本質的な価値観、強み、成長ポイントを簡潔にまとめてください:";

        $response = $this->bedrockService->chat(
            $prompt,
            [],
            config('bedrock.reflection_system_prompt')
        );

        return $response;
    }
}


