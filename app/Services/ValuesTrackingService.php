<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\Diary;
use App\Models\WcmSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ValuesTrackingService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 価値観の変化を追跡
     */
    public function trackValuesChanges(?Carbon $startDate = null, ?Carbon $endDate = null): ?array
    {
        $userId = Auth::id();
        
        // WCMシートのWillを取得
        $wcmSheet = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        if (!$wcmSheet || empty($wcmSheet->will_text)) {
            return null;
        }

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

        // 価値観を抽出
        $extractedValues = $this->extractValuesFromDiaries($diaries);
        
        // Willと比較
        $comparison = $this->compareWithWill($extractedValues, $wcmSheet->will_text);

        return [
            'will' => $wcmSheet->will_text,
            'extracted_values' => $extractedValues,
            'comparison' => $comparison,
            'period' => [
                'start' => $startDate->format('Y年m月d日'),
                'end' => $endDate->format('Y年m月d日'),
            ],
        ];
    }

    /**
     * 日記から価値観を抽出
     */
    protected function extractValuesFromDiaries($diaries): array
    {
        try {
            $prompt = "以下の日記内容から、ユーザーの価値観や大切にしていることを抽出してください。\n\n";
            $prompt .= "【日記内容】\n";
            foreach ($diaries->take(15) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
            }

            $prompt .= "\n【抽出のポイント】\n";
            $prompt .= "- 日記の中で表現されている価値観、大切にしていること、理想を抽出\n";
            $prompt .= "- 5-10個の価値観を簡潔にまとめる\n";
            $prompt .= "- 価値観の変化も考慮する\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"values": ["価値観1", "価値観2", "価値観3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['values'])) {
                    return $json['values'];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to extract values from diaries', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Willと比較
     */
    protected function compareWithWill(array $extractedValues, string $willText): ?string
    {
        try {
            $valuesText = implode(', ', $extractedValues);
            
            $prompt = "以下の抽出された価値観と、WCMシートのWill（こう在りたい）を比較して分析してください。\n\n";
            $prompt .= "【抽出された価値観】\n";
            $prompt .= $valuesText;
            $prompt .= "\n\n";

            $prompt .= "【Will（こう在りたい）】\n";
            $prompt .= $willText;
            $prompt .= "\n\n";

            $prompt .= "【分析のポイント】\n";
            $prompt .= "- 一致している価値観を指摘する\n";
            $prompt .= "- 新たに発見された価値観を指摘する\n";
            $prompt .= "- 価値観の変化や進化を分析する\n";
            $prompt .= "- 簡潔で読みやすい文章（4-6文程度）\n\n";
            $prompt .= "比較分析を生成してください:";

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            return $response;
        } catch (\Exception $e) {
            Log::warning('Failed to compare values with Will', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        if (preg_match('/\{[^{}]*"values"[^{}]*\}/s', $response, $matches)) {
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


