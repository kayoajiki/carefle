<?php

namespace App\Http\Controllers;

use App\Services\BedrockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * Send a chat message and get AI response
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_history' => 'sometimes|array',
            'conversation_history.*.role' => 'required|in:user,assistant',
            'conversation_history.*.content' => 'required|string',
        ]);

        try {
            $message = $request->input('message');
            $conversationHistory = $request->input('conversation_history', []);

            $response = $this->bedrockService->chat($message, $conversationHistory);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'AIからの応答を取得できませんでした。しばらく時間をおいて再度お試しください。',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Chat controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました。しばらく時間をおいて再度お試しください。',
            ], 500);
        }
    }
}

