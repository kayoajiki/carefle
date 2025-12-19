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

        // 接続情報を検出（コンテンツがある場合のみ）
        if (!empty($content)) {
            $this->detectGoalConnections($diary);
        }

        return $diary;
    }

    /**
     * 日記とマイルストーン・WCMシートのWillテーマの接続を検出
     */
    protected function detectGoalConnections(Diary $diary): void
    {
        try {
            $connectionService = app(\App\Services\GoalConnectionService::class);
            $connections = $connectionService->detectConnections($diary);

            // 既存の接続を削除
            \App\Models\DiaryGoalConnection::where('diary_id', $diary->id)->delete();

            // 新しい接続を保存（最大3件まで）
            foreach (array_slice($connections, 0, 3) as $connection) {
                \App\Models\DiaryGoalConnection::create($connection);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to detect goal connections', [
                'error' => $e->getMessage(),
                'diary_id' => $diary->id,
            ]);
        }
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

    /**
     * 出来事・事実の問いかけを生成
     */
    public function generateFactQuestion(string $category, ?string $reflectionType = null): ?string
    {
        $context = $this->contextService->buildContextForUser();
        
        $categoryNames = [
            'work' => '仕事',
            'family' => '家族',
            'love' => '恋愛',
            'relationships' => '人間関係',
            'health' => '健康',
            'goals' => '目標',
            'learning' => '学び',
            'other' => 'その他',
        ];
        $categoryName = $categoryNames[$category] ?? $category;

        $typeNames = [
            'daily' => '今日の振り返り',
            'yesterday' => '昨日の振り返り',
            'weekly' => '週次の振り返り',
            'deep' => '深い内省',
            'moya_moya' => 'モヤモヤの解消',
        ];
        $typeName = $typeNames[$reflectionType] ?? ($reflectionType ?? '振り返り');

        $prompt = "ユーザーが選択した分類: {$categoryName}\n";
        $prompt .= "内省のタイプ: {$typeName}\n\n";
        
        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考程度に）】\n{$context}\n\n";
            $prompt .= "※上記の情報は参考程度に留め、会話の流れに自然に織り交ぜてください。\n\n";
        }
        
        $prompt .= "ユーザーに対して、以下の形式で質問してください：\n";
        $prompt .= "1. まず「{$categoryName}についてですね。」のように選択した分類を確認する\n";
        $prompt .= "2. その後、出来事、やったこと、事実について簡潔に質問する\n";
        $prompt .= "- 1つの質問に絞る\n";
        $prompt .= "- 具体的で答えやすい質問\n";
        $prompt .= "- 親しみやすく、温かみのある口調\n";
        $prompt .= "- 2-3文程度の簡潔な応答を心がける\n";
        $prompt .= "- 例：「仕事についてですね。どんな出来事でしたか？」\n";
        $prompt .= "- 例：「家族のことですね。今日はどんなことがありましたか？」";

        $response = $this->bedrockService->chat($prompt, [], $this->reflectionSystemPrompt);

        if ($response === null) {
            Log::warning('ReflectionChatService: Failed to generate fact question', [
                'category' => $category,
                'reflection_type' => $reflectionType,
            ]);
        }

        return $response;
    }

    /**
     * 印象的だったことの問いかけを生成
     */
    public function generateImpressionQuestion(string $category, string $factResponse, ?string $reflectionType = null): ?string
    {
        $context = $this->contextService->buildContextForUser();
        
        $categoryNames = [
            'work' => '仕事',
            'family' => '家族',
            'love' => '恋愛',
            'relationships' => '人間関係',
            'health' => '健康',
            'goals' => '目標',
            'learning' => '学び',
            'other' => 'その他',
        ];
        $categoryName = $categoryNames[$category] ?? $category;

        $typeNames = [
            'daily' => '今日の振り返り',
            'yesterday' => '昨日の振り返り',
            'weekly' => '週次の振り返り',
            'deep' => '深い内省',
            'moya_moya' => 'モヤモヤの解消',
        ];
        $typeName = $typeNames[$reflectionType] ?? ($reflectionType ?? '振り返り');

        $prompt = "ユーザーが選択した分類: {$categoryName}\n";
        $prompt .= "内省のタイプ: {$typeName}\n";
        $prompt .= "ユーザーの前回の回答: {$factResponse}\n\n";
        
        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考程度に）】\n{$context}\n\n";
            $prompt .= "※上記の情報は参考程度に留め、会話の流れに自然に織り交ぜてください。\n\n";
        }
        
        $prompt .= "ユーザーに対して、印象的だったことについて簡潔に質問してください。\n";
        $prompt .= "- 1つの質問に絞る\n";
        $prompt .= "- 前回の回答を踏まえた質問\n";
        $prompt .= "- 親しみやすく、温かみのある口調\n";
        $prompt .= "- 2-3文程度の簡潔な応答を心がける";

        // 会話履歴として、ユーザーの前回の回答を含める
        $messages = [
            [
                'role' => 'user',
                'content' => $factResponse,
            ],
        ];

        // プロンプトを会話履歴の最後のuserメッセージとして追加するため、
        // プロンプトを直接$messageとして渡す
        $response = $this->bedrockService->chat($prompt, $messages, $this->reflectionSystemPrompt);

        if ($response === null) {
            Log::warning('ReflectionChatService: Failed to generate impression question', [
                'category' => $category,
                'reflection_type' => $reflectionType,
            ]);
        }

        return $response;
    }

    /**
     * 前向きなFBと問いを生成（自然な会話形式）
     */
    public function generateFeedbackAndQuestion(string $category, array $conversationHistory, ?string $reflectionType = null): ?string
    {
        $context = $this->contextService->buildContextForUser();
        
        $categoryNames = [
            'work' => '仕事',
            'family' => '家族',
            'love' => '恋愛',
            'relationships' => '人間関係',
            'health' => '健康',
            'goals' => '目標',
            'learning' => '学び',
            'other' => 'その他',
        ];
        $categoryName = $categoryNames[$category] ?? $category;

        $typeNames = [
            'daily' => '今日の振り返り',
            'yesterday' => '昨日の振り返り',
            'weekly' => '週次の振り返り',
            'deep' => '深い内省',
            'moya_moya' => 'モヤモヤの解消',
        ];
        $typeName = $typeNames[$reflectionType] ?? ($reflectionType ?? '振り返り');

        // 会話履歴を構築（timestampを除く）
        $messages = [];
        foreach ($conversationHistory as $history) {
            if (isset($history['role']) && isset($history['content'])) {
                // timestampフィールドを除外し、contentを文字列に変換
                $messages[] = [
                    'role' => $history['role'],
                    'content' => is_string($history['content']) ? $history['content'] : (string)$history['content'],
                ];
            }
        }

        // 会話履歴が空の場合は、エラーを返す
        if (empty($messages)) {
            Log::warning('ReflectionChatService: Empty conversation history for feedback and question', [
                'category' => $category,
            ]);
            return null;
        }

        // 会話履歴のテキスト表現を作成（プロンプト用）
        $conversationText = '';
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'user' ? 'ユーザー' : 'AI';
            $conversationText .= "{$role}: {$msg['content']}\n";
        }
        
        // 最後のメッセージがuserでない場合は、エラーを返す
        $lastMessage = end($messages);
        if ($lastMessage['role'] !== 'user') {
            Log::warning('ReflectionChatService: Last message is not user message', [
                'category' => $category,
                'last_message_role' => $lastMessage['role'] ?? 'unknown',
            ]);
            return null;
        }

        $prompt = "ユーザーが選択した分類: {$categoryName}\n";
        $prompt .= "内省のタイプ: {$typeName}\n\n";
        $prompt .= "会話履歴:\n{$conversationText}\n\n";
        
        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考程度に）】\n{$context}\n\n";
            $prompt .= "※上記の情報は参考程度に留め、会話の流れに自然に織り交ぜてください。\n\n";
        }
        
        $prompt .= "ユーザーの回答を踏まえて、自然な会話形式で以下を提供してください：\n\n";
        $prompt .= "1. 前向きなフィードバック（2-3文程度）\n";
        $prompt .= "   - ユーザーの行動や気づきを認める\n";
        $prompt .= "   - ポジティブな視点を示す\n";
        $prompt .= "   - 励ましの言葉を含める\n";
        $prompt .= "   - 「ここがいいですね」「この視点があると思いますよ」「つまりこういうふうに感じているのですね」など、寄り添いと励ましを込めた表現を使う\n\n";
        $prompt .= "2. 深めるための問いを1つ\n";
        $prompt .= "   - ユーザーがさらに考えを深められる問い\n";
        $prompt .= "   - 前向きで建設的な問い\n";
        $prompt .= "   - 簡潔で明確な問い\n\n";
        $prompt .= "【重要】\n";
        $prompt .= "- 【フィードバック】や【問いかけ】といった形式は使わない\n";
        $prompt .= "- フィードバックと問いを自然な会話の流れで統合した1つのメッセージとして提供する\n";
        $prompt .= "- 例：「それは素晴らしい気づきですね。その経験から、どんな学びや気づきがありましたか？」\n";
        $prompt .= "- 例：「ここがいいですね。この視点があると思いますよ。この経験を通して、どんなことを感じましたか？」\n";
        $prompt .= "- 例：「つまりこういうふうに感じているのですね。その気持ちを大切にしながら、この経験から何を学べそうですか？」\n";

        // 最初のメッセージがuserでない場合は削除（Bedrock APIの要件）
        while (!empty($messages) && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }
        
        // 会話履歴が空になった場合は、エラーを返す
        if (empty($messages)) {
            Log::warning('ReflectionChatService: No user messages in conversation history', [
                'category' => $category,
            ]);
            return null;
        }
        
        // プロンプトを会話履歴の最後のuserメッセージとして追加するため、
        // 会話履歴の最後のuserメッセージを削除してから、プロンプトを渡す
        // これにより、BedrockService::chat()がプロンプトを最後のuserメッセージとして追加できる
        $lastUserMessage = array_pop($messages);
        
        // 会話履歴の最後がassistantメッセージになるようにする
        // これにより、BedrockService::chat()がプロンプトを最後のuserメッセージとして追加できる
        $response = $this->bedrockService->chat($prompt, $messages, $this->reflectionSystemPrompt);

        if ($response === null) {
            Log::error('ReflectionChatService: Failed to generate feedback and question', [
                'category' => $category,
                'reflection_type' => $reflectionType,
                'messages_count' => count($messages),
                'last_message_role' => !empty($messages) ? end($messages)['role'] : 'none',
            ]);
            
            // リトライ: より簡潔なプロンプトで再試行
            $retryPrompt = "ユーザーの回答を踏まえて、自然な会話形式で前向きなフィードバックと、深めるための問いを1つ提供してください。\n";
            $retryPrompt .= "フィードバックと問いを自然な会話の流れで統合した1つのメッセージとして提供してください。\n";
            $retryPrompt .= "形式（【フィードバック】や【問いかけ】）は使わず、自然な会話形式で。\n\n";
            $retryPrompt .= "会話履歴:\n{$conversationText}";
            
            $response = $this->bedrockService->chat($retryPrompt, $messages, $this->reflectionSystemPrompt);
            
            if ($response === null) {
                Log::error('ReflectionChatService: Retry also failed for feedback and question', [
                    'category' => $category,
                ]);
            }
        }

        return $response;
    }

    /**
     * クロージングメッセージを生成
     */
    public function generateClosing(array $conversationHistory, ?string $reflectionType = null): ?string
    {
        $context = $this->contextService->buildContextForUser();

        $typeNames = [
            'daily' => '今日の振り返り',
            'yesterday' => '昨日の振り返り',
            'weekly' => '週次の振り返り',
            'deep' => '深い内省',
            'moya_moya' => 'モヤモヤの解消',
        ];
        $typeName = $typeNames[$reflectionType] ?? ($reflectionType ?? '振り返り');

        // 会話履歴を構築（timestampを除く）
        $messages = [];
        foreach ($conversationHistory as $history) {
            if (isset($history['role']) && isset($history['content'])) {
                // timestampフィールドを除外し、contentを文字列に変換
                $messages[] = [
                    'role' => $history['role'],
                    'content' => is_string($history['content']) ? $history['content'] : (string)$history['content'],
                ];
            }
        }

        // 最初のメッセージがuserでない場合は削除（Bedrock APIの要件）
        while (!empty($messages) && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }
        
        // 会話履歴が空になった場合は、エラーを返す
        if (empty($messages)) {
            Log::warning('ReflectionChatService: No user messages in conversation history for closing', [
                'reflection_type' => $reflectionType,
            ]);
            return null;
        }

        // 会話履歴のテキスト表現を作成（プロンプト用）
        $conversationText = '';
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'user' ? 'ユーザー' : 'AI';
            $conversationText .= "{$role}: {$msg['content']}\n";
        }

        $prompt = "内省のタイプ: {$typeName}\n\n";
        $prompt .= "会話履歴:\n{$conversationText}\n\n";
        
        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考程度に）】\n{$context}\n\n";
            $prompt .= "※上記の情報は参考程度に留め、会話の流れに自然に織り交ぜてください。\n\n";
        }
        
        $prompt .= "内省チャットを締めくくるメッセージを生成してください。\n";
        $prompt .= "- 今日の内省を認める\n";
        $prompt .= "- 前向きな言葉で締めくくる\n";
        $prompt .= "- 簡潔に（2-3文程度）\n";
        $prompt .= "- 親しみやすく、温かみのある口調を保つ";

        $response = $this->bedrockService->chat($prompt, $messages, $this->reflectionSystemPrompt);

        if ($response === null) {
            Log::warning('ReflectionChatService: Failed to generate closing', [
                'reflection_type' => $reflectionType,
            ]);
        }

        return $response;
    }
}