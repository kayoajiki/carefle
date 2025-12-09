<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InsightExtractionService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 過去の内省から重要な気づきを抽出
     */
    public function extractInsights(?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): ?array
    {
        $userId = Auth::id();
        
        if (!$startDate) {
            $startDate = Carbon::now()->subMonth();
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        }

        // 期間内の日記を取得
        $diaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date')
            ->get();

        if ($diaries->isEmpty()) {
            return null;
        }

        // 気づきを抽出
        $insights = $this->extractKeyInsights($diaries, $limit);

        return [
            'insights' => $insights,
            'period' => [
                'start' => $startDate->format('Y年m月d日'),
                'end' => $endDate->format('Y年m月d日'),
            ],
            'diary_count' => $diaries->count(),
        ];
    }

    /**
     * 重要な気づきを抽出
     */
    protected function extractKeyInsights($diaries, int $limit): array
    {
        try {
            $prompt = "以下の過去の内省から、重要な気づきや学びを抽出してください。\n\n";
            $prompt .= "【内省内容】\n";
            foreach ($diaries->take(20) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
            }

            $prompt .= "\n【抽出のポイント】\n";
            $prompt .= "- 繰り返し現れるテーマやパターンを特定\n";
            $prompt .= "- 重要な気づきや学びを抽出\n";
            $prompt .= "- 成長や変化を示すポイントを指摘\n";
            $prompt .= "- {$limit}個の気づきを簡潔にまとめる\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"insights": [{"title": "気づきのタイトル", "description": "説明", "relevance": "関連性"}]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['insights'])) {
                    return array_slice($json['insights'], 0, $limit);
                }
            }

            return [];
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
        if (preg_match('/\{[^{}]*"insights"[^{}]*\}/s', $response, $matches)) {
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


