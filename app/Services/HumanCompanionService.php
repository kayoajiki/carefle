<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Models\Diary;
use App\Models\ReflectionChatConversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HumanCompanionService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 人が必要かどうかを判定
     */
    public function shouldSuggestHumanCompanion(?int $userId = null): ?array
    {
        $userId = $userId ?? Auth::id();
        
        // 最近の日記と会話を分析
        $recentDiaries = Diary::where('user_id', $userId)
            ->whereBetween('date', [Carbon::now()->subWeek(), Carbon::now()])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        $recentConversations = ReflectionChatConversation::where('user_id', $userId)
            ->whereBetween('updated_at', [Carbon::now()->subWeek(), Carbon::now()])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        if ($recentDiaries->isEmpty() && $recentConversations->isEmpty()) {
            return null;
        }

        // AIで分析
        $analysis = $this->analyzeNeedForHumanCompanion($recentDiaries, $recentConversations);

        if ($analysis && $analysis['should_suggest']) {
            return [
                'should_suggest' => true,
                'reason' => $analysis['reason'],
                'suggestion_message' => $analysis['suggestion_message'],
            ];
        }

        return [
            'should_suggest' => false,
        ];
    }

    /**
     * 人が必要かどうかをAIで分析
     */
    protected function analyzeNeedForHumanCompanion($diaries, $conversations): ?array
    {
        try {
            $prompt = "以下の最近の内省と会話を分析して、専門家（カウンセラー）による伴走が必要かどうかを判定してください。\n\n";

            if ($diaries->isNotEmpty()) {
                $prompt .= "【最近の日記】\n";
                foreach ($diaries->take(5) as $diary) {
                    $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 200) . "...\n";
                }
                $prompt .= "\n";
            }

            if ($conversations->isNotEmpty()) {
                $prompt .= "【最近のAIチャット】\n";
                foreach ($conversations->take(3) as $conversation) {
                    $history = $conversation->conversation_history ?? [];
                    if (!empty($history)) {
                        $lastMessage = end($history);
                        $prompt .= "- " . substr($lastMessage['content'] ?? '', 0, 200) . "...\n";
                    }
                }
                $prompt .= "\n";
            }

            $prompt .= "【判定のポイント】\n";
            $prompt .= "- 深刻な悩みや課題が繰り返し現れている\n";
            $prompt .= "- AIだけでは解決が難しい複雑な状況\n";
            $prompt .= "- 専門的なアドバイスやサポートが必要\n";
            $prompt .= "- 長期的な伴走が必要な状況\n\n";
            $prompt .= "以下のJSON形式で返してください:\n";
            $prompt .= '{"should_suggest": true/false, "reason": "理由", "suggestion_message": "提案メッセージ"}';

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $json = $this->extractJsonFromResponse($response);
                if ($json) {
                    return $json;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to analyze need for human companion', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * レスポンスからJSONを抽出
     */
    protected function extractJsonFromResponse(string $response): ?array
    {
        if (preg_match('/\{[^{}]*"should_suggest"[^{}]*\}/s', $response, $matches)) {
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

