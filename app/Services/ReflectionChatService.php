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
     * 内省チャットの最初のメッセージを生成（固定メッセージ）
     */
    public function generateInitialMessage(?string $reflectionType = null): string
    {
        $messages = [
            'daily' => "こんにちは。今日も1日お疲れ様でした。\n\n今日の1日を振り返ってみましょう。良かったことや充実していた瞬間はありましたか？",
            'yesterday' => "こんにちは。昨日のことを振り返りたいのですね。\n\n昨日の1日で、特に印象に残っていることはありますか？",
            'weekly' => "こんにちは。今週の振り返りですね。\n\nこの1週間で、あなたにとって最も大きな学びや変化は何でしたか？",
            'deep' => "こんにちは。深く内省したいのですね。\n\n今、あなたが最も考えてみたいテーマは何ですか？",
            'moya_moya' => "こんにちは。モヤモヤしているのですね。\n\nどんな状況で、どんな気持ちになりましたか？",
        ];

        return $messages[$reflectionType] ?? $messages['daily'];
    }

    /**
     * 選択肢に応じた次のメッセージを生成
     */
    public function generateResponseForSelection(string $selection, ?string $reflectionType = null): string
    {
        $responses = [
            'work' => "仕事についてですね。どんな出来事でしたか？",
            'family' => "家族のことですね。どんな出来事でしたか？",
            'love' => "恋愛についてですね。どんな出来事でしたか？",
            'relationships' => "人間関係についてですね。どんな出来事でしたか？",
            'health' => "健康についてですね。どんな出来事でしたか？",
            'goals' => "目標についてですね。どんな出来事でしたか？",
            'learning' => "学びについてですね。どんな出来事でしたか？",
            'other' => "そうですね。どんな出来事でしたか？",
        ];

        return $responses[$selection] ?? $responses['other'];
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
        
        // プロンプトに応答の指示を追加
        $contextualPrompt .= "\n\n【応答のポイント】\n";
        $contextualPrompt .= "- 2-3文程度の簡潔な応答を心がける\n";
        $contextualPrompt .= "- ユーザーの話に共感を示す\n";
        $contextualPrompt .= "- 自然な会話の流れで、次の質問やコメントをする\n";
        $contextualPrompt .= "- 親しみやすく、温かみのある口調を保つ";

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
        $prompt = "ユーザーが今話している内容:\n「{$userMessage}」\n\n";
        
        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考程度に）】\n{$context}\n\n";
            $prompt .= "※上記の情報は参考程度に留め、会話の流れに自然に織り交ぜてください。\n";
            $prompt .= "※情報を羅列するのではなく、ユーザーの話に共感し、自然な会話を続けてください。\n\n";
        }
        
        if ($reflectionType) {
            $typeNames = [
                'daily' => '今日の振り返り',
                'yesterday' => '昨日の振り返り',
                'weekly' => '週次の振り返り',
                'deep' => '深い内省',
                'moya_moya' => 'モヤモヤの解消',
            ];
            $typeName = $typeNames[$reflectionType] ?? $reflectionType;
            $prompt .= "内省のタイプ: {$typeName}\n\n";
        }
        
        $prompt .= "ユーザーの話を自然に受け止め、共感を示しながら、次の質問やコメントをしてください。\n";
        $prompt .= "会話は自然な流れを大切にし、無理に深く掘り下げる必要はありません。\n";
        
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

