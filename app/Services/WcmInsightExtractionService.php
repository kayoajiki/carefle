<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\Diary;
use App\Models\WcmSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WcmInsightExtractionService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 日記内容からWCMシートに関連する気づきを抽出
     */
    public function extractInsights(?Carbon $startDate = null, ?Carbon $endDate = null): ?array
    {
        $userId = Auth::id();
        
        // WCMシートを取得
        $wcmSheet = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        if (!$wcmSheet) {
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

        // 気づきを抽出
        $willInsights = $this->extractWillInsights($diaries, $wcmSheet);
        $canInsights = $this->extractCanInsights($diaries, $wcmSheet);
        $mustInsights = $this->extractMustInsights($diaries, $wcmSheet);

        return [
            'will_insights' => $willInsights,
            'can_insights' => $canInsights,
            'must_insights' => $mustInsights,
            'period' => [
                'start' => $startDate->format('Y年m月d日'),
                'end' => $endDate->format('Y年m月d日'),
            ],
        ];
    }

    /**
     * Willに関連する気づきを抽出
     */
    protected function extractWillInsights($diaries, WcmSheet $wcmSheet): array
    {
        try {
            $prompt = "以下の日記内容から、WCMシートの「Will（こう在りたい）」に関連する気づきを抽出してください。\n\n";
            $prompt .= "【Will（こう在りたい）】\n";
            $prompt .= $wcmSheet->will_text ?? '未設定';
            $prompt .= "\n\n";

            $prompt .= "【日記内容】\n";
            foreach ($diaries->take(10) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
            }

            $prompt .= "\n【抽出のポイント】\n";
            $prompt .= "- 日記の中で、Willに関連する価値観や理想が表現されている部分を抽出\n";
            $prompt .= "- Willとの一致点や変化を分析\n";
            $prompt .= "- 3-5個の気づきを簡潔にまとめる\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"insights": ["気づき1", "気づき2", "気づき3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['insights'])) {
                    return $json['insights'];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to extract Will insights', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Canに関連する気づきを抽出
     */
    protected function extractCanInsights($diaries, WcmSheet $wcmSheet): array
    {
        try {
            $prompt = "以下の日記内容から、WCMシートの「Can（できること）」に関連する気づきを抽出してください。\n\n";
            $prompt .= "【Can（できること）】\n";
            $prompt .= $wcmSheet->can_text ?? '未設定';
            $prompt .= "\n\n";

            $prompt .= "【日記内容】\n";
            foreach ($diaries->take(10) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
            }

            $prompt .= "\n【抽出のポイント】\n";
            $prompt .= "- 日記の中で、Canに関連する能力や強みが表現されている部分を抽出\n";
            $prompt .= "- Canとの一致点や新たに発見された強みを分析\n";
            $prompt .= "- 3-5個の気づきを簡潔にまとめる\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"insights": ["気づき1", "気づき2", "気づき3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['insights'])) {
                    return $json['insights'];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to extract Can insights', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Mustに関連する気づきを抽出
     */
    protected function extractMustInsights($diaries, WcmSheet $wcmSheet): array
    {
        try {
            $prompt = "以下の日記内容から、WCMシートの「Must（やるべきこと）」に関連する気づきを抽出してください。\n\n";
            $prompt .= "【Must（やるべきこと）】\n";
            $prompt .= $wcmSheet->must_text ?? '未設定';
            $prompt .= "\n\n";

            $prompt .= "【日記内容】\n";
            foreach ($diaries->take(10) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
            }

            $prompt .= "\n【抽出のポイント】\n";
            $prompt .= "- 日記の中で、Mustに関連する行動や取り組みが表現されている部分を抽出\n";
            $prompt .= "- Mustとの一致点や進捗を分析\n";
            $prompt .= "- 3-5個の気づきを簡潔にまとめる\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"insights": ["気づき1", "気づき2", "気づき3"]}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json && isset($json['insights'])) {
                    return $json['insights'];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to extract Must insights', ['error' => $e->getMessage()]);
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


