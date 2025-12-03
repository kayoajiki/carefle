<?php

namespace App\Services;

use App\Services\BedrockService;
use App\Services\ReflectionContextService;
use App\Models\ReflectionChatConversation;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReflectionChatService
{
    protected BedrockService $bedrockService;
    protected ReflectionContextService $contextService;
    protected string $reflectionSystemPrompt;

    public function __construct(
        BedrockService $bedrockService,
        ReflectionContextService $contextService
    ) {
        $this->bedrockService = $bedrockService;
        $this->contextService = $contextService;
        $this->reflectionSystemPrompt = $this->buildReflectionSystemPrompt();
    }

    /**
     * 内省チャットの最初のメッセージを生成
     */
    public function generateInitialMessage(?string $reflectionType = null): string
    {
        $context = $this->contextService->buildContextForUser();
        
        $prompt = "ユーザーが内省を始めようとしています。";
        
        if ($reflectionType) {
            $typeMessages = [
                'daily' => '今日1日の振り返りをしたいようです。',
                'yesterday' => '昨日の振り返りをしたいようです。',
                'weekly' => '週次の振り返りをしたいようです。',
                'deep' => '深く内省して未来を描きたいようです。',
                'moya_moya' => '日常の仕事の中でモヤモヤを感じたことを解きたいようです。',
            ];
            $prompt .= $typeMessages[$reflectionType] ?? '';
        }
        
        $prompt .= "\n\nユーザーのコンテキスト:\n{$context}\n\n";
        $prompt .= "親しみやすく、共感的な口調で、最初の質問を1つだけしてください。";
        $prompt .= "質問は簡潔で、答えやすいものにしてください。";

        $response = $this->bedrockService->chat($prompt, []);
        
        return $response ?? "こんにちは！今日はどんな1日でしたか？";
    }

    /**
     * ユーザーのメッセージに対してAIが応答を生成
     */
    public function generateResponse(
        string $userMessage,
        array $conversationHistory,
        ?string $reflectionType = null
    ): ?string {
        $context = $this->contextService->buildContextForUser();
        
        // 会話履歴を構築
        $messages = [];
        
        // 会話履歴から、timestampを除いてroleとcontentのみを抽出
        // 最初のメッセージがassistantの場合はスキップ（最初は必ずuserでなければならない）
        $skipFirst = false;
        foreach ($conversationHistory as $index => $history) {
            if ($index === 0 && $history['role'] === 'assistant') {
                // 最初のassistantメッセージはスキップ
                $skipFirst = true;
                continue;
            }
            $messages[] = [
                'role' => $history['role'],
                'content' => $history['content'],
            ];
        }

        // 現在のユーザーメッセージを追加（まだ履歴に含まれていない場合）
        $lastMessage = end($conversationHistory);
        $needsUserMessage = true;
        
        // 最後のメッセージが現在のユーザーメッセージと一致する場合は追加しない
        if ($lastMessage && $lastMessage['role'] === 'user' && $lastMessage['content'] === $userMessage) {
            $needsUserMessage = false;
        }
        
        // 最初のメッセージがassistantだった場合、またはメッセージが空の場合、userメッセージを先頭に追加
        if ($skipFirst || empty($messages)) {
            array_unshift($messages, [
                'role' => 'user',
                'content' => $userMessage,
            ]);
            $needsUserMessage = false;
        } elseif ($needsUserMessage) {
            // 最後のメッセージがuserでない場合のみ追加
            if (empty($messages) || end($messages)['role'] !== 'user') {
                $messages[] = [
                    'role' => 'user',
                    'content' => $userMessage,
                ];
            }
        }
        
        // 最終チェック: 最初のメッセージがuserでない場合は修正
        if (!empty($messages) && $messages[0]['role'] !== 'user') {
            // 最初のメッセージを削除してuserメッセージを先頭に追加
            array_shift($messages);
            array_unshift($messages, [
                'role' => 'user',
                'content' => $userMessage,
            ]);
        }
        
        // 重複するuserメッセージを削除（連続している場合）
        $cleanedMessages = [];
        $lastRole = null;
        foreach ($messages as $msg) {
            if ($msg['role'] === 'user' && $lastRole === 'user') {
                // 連続するuserメッセージはスキップ（最後のものを保持）
                continue;
            }
            $cleanedMessages[] = $msg;
            $lastRole = $msg['role'];
        }
        $messages = $cleanedMessages;

        // コンテキストを含めたプロンプトを構築
        $contextualPrompt = $this->buildContextualPrompt($userMessage, $context, $reflectionType);

        // BedrockServiceのchatメソッドを使用（システムプロンプトを動的に設定）
        $response = $this->bedrockService->chat($contextualPrompt, $messages, $this->reflectionSystemPrompt);

        if ($response === null) {
            Log::warning('ReflectionChatService: Bedrock returned null response', [
                'user_message' => substr($userMessage, 0, 100),
                'conversation_length' => count($messages),
            ]);
        }

        return $response;
    }

    /**
     * コンテキストを含めたプロンプトを構築
     */
    private function buildContextualPrompt(
        string $userMessage,
        string $context,
        ?string $reflectionType = null
    ): string {
        $prompt = "ユーザーのコンテキスト情報:\n{$context}\n\n";
        $prompt .= "上記のコンテキストを参考にしながら、ユーザーの内省を深めるための質問や応答をしてください。\n\n";
        
        if ($reflectionType) {
            $prompt .= "内省タイプ: {$reflectionType}\n";
        }
        
        return $prompt;
    }

    /**
     * 内省伴走用のシステムプロンプトを構築
     */
    private function buildReflectionSystemPrompt(): string
    {
        return config('bedrock.reflection_system_prompt', 'あなたは内省を支援する優しい伴走者です。ユーザーが自分自身と向き合い、理想の自分に近づくための内省をサポートしてください。');
    }

    /**
     * 会話を保存
     */
    public function saveConversation(
        string $date,
        array $conversationHistory,
        ?string $summary = null
    ): ReflectionChatConversation {
        $conversation = ReflectionChatConversation::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $date,
            ],
            [
                'conversation_history' => $conversationHistory,
                'summary' => $summary,
            ]
        );

        return $conversation;
    }

    /**
     * 会話を日記として保存
     */
    public function saveAsDiary(
        string $date,
        array $conversationHistory,
        int $motivation = 50,
        ?string $reflectionType = null
    ): Diary {
        $conversation = $this->saveConversation($date, $conversationHistory);
        
        // 会話から日記の内容を生成
        $content = $this->extractDiaryContentFromConversation($conversationHistory);

        $diary = Diary::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $date,
            ],
            [
                'motivation' => $motivation,
                'content' => $content,
                'reflection_type' => $reflectionType,
                'chat_conversation_id' => $conversation->id,
            ]
        );

        return $diary;
    }

    /**
     * 会話履歴から日記の内容を抽出
     */
    private function extractDiaryContentFromConversation(array $conversationHistory): string
    {
        $userMessages = [];
        foreach ($conversationHistory as $message) {
            if ($message['role'] === 'user') {
                $userMessages[] = $message['content'];
            }
        }
        
        return implode("\n\n", $userMessages);
    }
}

