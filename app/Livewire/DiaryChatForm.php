<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReflectionChatService;
use App\Models\Diary;
use App\Models\ReflectionChatConversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiaryChatForm extends Component
{
    public $date;
    public $reflectionType = 'daily';
    public $messages = [];
    public $currentMessage = '';
    public $isLoading = false;
    public $conversationId = null;
    public $motivation = 50;
    public $showMotivationSlider = false;
    public $showSelectionButtons = false; // 選択肢ボタンを表示するかどうか
    public $conversationStep = 'category_selection'; // 会話ステップ: category_selection, fact_question, impression_question, feedback_and_question, user_response, closing
    public $selectedCategory = null; // 選択された分類

    protected ReflectionChatService $chatService;

    public function boot()
    {
        $this->chatService = app(ReflectionChatService::class);
    }

    public function mount($date = null, $reflectionType = null)
    {
        $this->date = $date ?? date('Y-m-d');
        $this->reflectionType = $reflectionType ?? 'daily';

        // 既存の会話を読み込む
        $this->loadExistingConversation();
    }

    public function loadExistingConversation()
    {
        $diary = Diary::where('user_id', Auth::id())
            ->whereDate('date', $this->date)
            ->whereNotNull('chat_conversation_id')
            ->first();

        if ($diary && $diary->chatConversation) {
            $this->conversationId = $diary->chat_conversation_id;
            $history = $diary->chatConversation->conversation_history ?? [];
            // タイムスタンプがない場合に追加
            $this->messages = array_map(function($msg) {
                if (!isset($msg['timestamp'])) {
                    $msg['timestamp'] = now()->toDateTimeString();
                }
                return $msg;
            }, $history);
            $this->motivation = $diary->motivation ?? 50;
            
            // 既存の会話からステップを判定
            $this->determineStepFromHistory();
        } else {
            // 新しい会話を開始（マウント時は自動開始しない）
            $this->messages = [];
            $this->conversationId = null;
            $this->conversationStep = 'category_selection';
            $this->selectedCategory = null;
        }
    }

    /**
     * 会話履歴から現在のステップを判定
     */
    protected function determineStepFromHistory()
    {
        $userMessageCount = 0;
        $assistantMessageCount = 0;
        $hasCategorySelection = false;
        
        foreach ($this->messages as $msg) {
            if ($msg['role'] === 'user') {
                $userMessageCount++;
            } elseif ($msg['role'] === 'assistant') {
                $assistantMessageCount++;
                // 分類選択のメッセージを検出
                if (str_contains($msg['content'], 'についてですね') || str_contains($msg['content'], 'どんな出来事でしたか？')) {
                    $hasCategorySelection = true;
                }
            }
        }
        
        // ステップ判定ロジック
        if (!$hasCategorySelection) {
            $this->conversationStep = 'category_selection';
        } elseif ($userMessageCount === 0) {
            $this->conversationStep = 'fact_question';
        } elseif ($userMessageCount === 1) {
            $this->conversationStep = 'impression_question';
        } elseif ($userMessageCount === 2) {
            $this->conversationStep = 'feedback_and_question';
        } elseif ($userMessageCount === 3) {
            $this->conversationStep = 'user_response';
        } else {
            $this->conversationStep = 'closing';
        }
        
        // 分類を抽出（最初のassistantメッセージから）
        foreach ($this->messages as $msg) {
            if ($msg['role'] === 'assistant') {
                if (str_contains($msg['content'], '仕事について')) {
                    $this->selectedCategory = 'work';
                    break;
                } elseif (str_contains($msg['content'], '家族')) {
                    $this->selectedCategory = 'family';
                    break;
                } elseif (str_contains($msg['content'], '恋愛')) {
                    $this->selectedCategory = 'love';
                    break;
                } elseif (str_contains($msg['content'], '人間関係')) {
                    $this->selectedCategory = 'relationships';
                    break;
                } elseif (str_contains($msg['content'], '健康')) {
                    $this->selectedCategory = 'health';
                    break;
                } elseif (str_contains($msg['content'], '目標')) {
                    $this->selectedCategory = 'goals';
                    break;
                } elseif (str_contains($msg['content'], '学び')) {
                    $this->selectedCategory = 'learning';
                    break;
                } elseif (str_contains($msg['content'], 'その他') || str_contains($msg['content'], 'そうですね')) {
                    $this->selectedCategory = 'other';
                    break;
                }
            }
        }
    }

    public function startNewConversation()
    {
        if (!empty($this->messages)) {
            return; // 既に会話が開始されている
        }

        $this->messages = [];
        $this->conversationId = null;
        
        // 最初のメッセージを生成
        $this->isLoading = true;
        try {
            $initialMessage = $this->chatService->generateInitialMessage($this->reflectionType);
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $initialMessage,
                'timestamp' => now()->toDateTimeString(),
            ];
            
            // 今日の振り返りの場合は選択肢を表示
            if ($this->reflectionType === 'daily') {
                $this->showSelectionButtons = true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate initial message', ['error' => $e->getMessage()]);
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'こんにちは。今日も1日お疲れ様でした。',
                'timestamp' => now()->toDateTimeString(),
            ];
            if ($this->reflectionType === 'daily') {
                $this->showSelectionButtons = true;
            }
        } finally {
            $this->isLoading = false;
        }
    }

    public function selectTopic($topic)
    {
        // 選択肢ボタンを非表示
        $this->showSelectionButtons = false;
        
        // 分類を保存
        $this->selectedCategory = $topic;
        
        // ステップをfact_questionに進める
        $this->conversationStep = 'fact_question';
        
        // 選択に応じたメッセージを生成（従来の方法）
        $response = $this->chatService->generateResponseForSelection($topic, $this->reflectionType);
        
        // AIの応答を追加
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        // fact_questionを生成
        $this->isLoading = true;
        try {
            $factQuestion = $this->chatService->generateFactQuestion($topic, $this->reflectionType);
            
            if ($factQuestion) {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $factQuestion,
                    'timestamp' => now()->toDateTimeString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate fact question', ['error' => $e->getMessage()]);
        } finally {
            $this->isLoading = false;
        }
        
        $this->dispatch('scroll-to-bottom');
    }

    public function sendMessage()
    {
        if (empty(trim($this->currentMessage))) {
            return;
        }

        // ユーザーメッセージを追加
        $userMessage = [
            'role' => 'user',
            'content' => trim($this->currentMessage),
            'timestamp' => now()->toDateTimeString(),
        ];
        $this->messages[] = $userMessage;
        $userMessageContent = $this->currentMessage;
        $this->currentMessage = '';

        $this->isLoading = true;

        try {
            $response = null;
            
            // 現在のステップに応じて適切な処理を実行
            switch ($this->conversationStep) {
                case 'fact_question':
                    // 印象的だったことの問いかけを生成
                    $response = $this->chatService->generateImpressionQuestion(
                        $this->selectedCategory ?? 'other',
                        $userMessageContent,
                        $this->reflectionType
                    );
                    if ($response) {
                        $this->conversationStep = 'impression_question';
                    }
                    break;
                    
                case 'impression_question':
                    // 前向きなFBと問いを生成
                    $response = $this->chatService->generateFeedbackAndQuestion(
                        $this->selectedCategory ?? 'other',
                        $this->messages,
                        $this->reflectionType
                    );
                    if ($response) {
                        $this->conversationStep = 'feedback_and_question';
                    }
                    break;
                    
                case 'feedback_and_question':
                    // ユーザーの応答を受けて、クロージングを生成
                    $response = $this->chatService->generateClosing(
                        $this->messages,
                        $this->reflectionType
                    );
                    if ($response) {
                        $this->conversationStep = 'closing';
                    }
                    break;
                    
                case 'user_response':
                    // クロージングを生成（念のため）
                    $response = $this->chatService->generateClosing(
                        $this->messages,
                        $this->reflectionType
                    );
                    if ($response) {
                        $this->conversationStep = 'closing';
                    }
                    break;
                    
                case 'closing':
                    // 既にクロージング済みの場合は、応答を生成しない
                    $response = null;
                    break;
                    
                default:
                    // 従来の方法で応答を生成（フォールバック）
                    $response = $this->chatService->generateResponse(
                        $userMessageContent,
                        $this->messages,
                        $this->reflectionType
                    );
                    break;
            }
            
            if ($response) {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $response,
                    'timestamp' => now()->toDateTimeString(),
                ];
            } elseif ($this->conversationStep !== 'closing' && $this->conversationStep !== 'user_response') {
                // closingステップとuser_responseステップ以外でエラーが発生した場合
                $errorMessage = '申し訳ございません。応答を生成できませんでした。';
                $errorMessage .= PHP_EOL . PHP_EOL;
                $errorMessage .= '【確認事項】';
                $errorMessage .= PHP_EOL . '1. AWS BedrockでAnthropicモデルの使用目的フォームを提出済みか確認してください';
                $errorMessage .= PHP_EOL . '2. .envファイルにAWS_ACCESS_KEY_IDとAWS_SECRET_ACCESS_KEYが正しく設定されているか確認してください';
                $errorMessage .= PHP_EOL . '3. しばらく時間をおいて再度お試しください';
                
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $errorMessage,
                    'timestamp' => now()->toDateTimeString(),
                ];
            } elseif ($this->conversationStep === 'feedback_and_question' || $this->conversationStep === 'user_response') {
                // クロージング生成に失敗した場合のフォールバックメッセージ
                $fallbackMessage = '今日の内省、お疲れ様でした。あなたの気づきを大切にしてくださいね。';
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $fallbackMessage,
                    'timestamp' => now()->toDateTimeString(),
                ];
                $this->conversationStep = 'closing';
            }
        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'error' => $e->getMessage(),
                'step' => $this->conversationStep,
            ]);
            
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'エラーが発生しました。しばらく時間をおいて再度お試しください。',
                'timestamp' => now()->toDateTimeString(),
            ];
        } finally {
            $this->isLoading = false;
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function saveConversation()
    {
        if (empty($this->messages)) {
            session()->flash('error', '保存する会話がありません。');
            return;
        }

        try {
            $conversation = $this->chatService->saveConversation(
                $this->date,
                $this->messages
            );

            $diary = $this->chatService->saveAsDiary(
                $this->date,
                $this->messages,
                $this->motivation,
                $this->reflectionType
            );

            $this->conversationId = $conversation->id;
            
            // アクションアイテムを生成（オプション）
            $this->generateActionItems($diary);

            session()->flash('message', '内省を保存しました。');
            $this->dispatch('diary-saved');
        } catch (\Exception $e) {
            session()->flash('error', '保存に失敗しました: ' . $e->getMessage());
        }
    }

    protected function generateActionItems($diary)
    {
        if (!$diary->content) {
            return;
        }

        try {
            $actionService = app(\App\Services\ActionItemGeneratorService::class);
            $suggestedActions = $actionService->generateActionItemsFromDiary($diary->content);
            
            if (!empty($suggestedActions)) {
                $actionService->saveSuggestedActions($suggestedActions, $diary->id);
            }
        } catch (\Exception $e) {
            // エラーは無視（アクションアイテム生成はオプション機能）
            Log::warning('Failed to generate action items', ['error' => $e->getMessage()]);
        }
    }

    public function toggleMotivationSlider()
    {
        $this->showMotivationSlider = !$this->showMotivationSlider;
    }

    public function updatedMotivation()
    {
        // モチベーションが変更されたら自動保存（既存の会話がある場合）
        if ($this->conversationId && !empty($this->messages)) {
            $this->saveConversation();
        }
    }

    /**
     * チャットを保存して閉じる
     */
    public function saveConversationAndClose()
    {
        if (empty($this->messages)) {
            session()->flash('error', '保存する会話がありません。');
            return;
        }

        try {
            $conversation = $this->chatService->saveConversation(
                $this->date,
                $this->messages
            );

            $diary = $this->chatService->saveAsDiary(
                $this->date,
                $this->messages,
                $this->motivation,
                $this->reflectionType
            );

            $this->conversationId = $conversation->id;
            
            // アクションアイテムを生成（オプション）
            $this->generateActionItems($diary);

            session()->flash('message', '内省を保存しました。');
            $this->dispatch('diary-saved');
            
            // 日記カレンダーページにリダイレクト
            return redirect()->route('diary');
        } catch (\Exception $e) {
            Log::error('Failed to save conversation and close', ['error' => $e->getMessage()]);
            session()->flash('error', '保存に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * チャットを削除して閉じる
     */
    public function deleteConversationAndClose()
    {
        try {
            // 会話を削除
            if ($this->conversationId) {
                $conversation = ReflectionChatConversation::where('id', $this->conversationId)
                    ->where('user_id', Auth::id())
                    ->first();
                
                if ($conversation) {
                    // 関連する日記のchat_conversation_idをクリア
                    Diary::where('chat_conversation_id', $this->conversationId)
                        ->where('user_id', Auth::id())
                        ->update(['chat_conversation_id' => null]);
                    
                    // 会話を削除
                    $conversation->delete();
                }
            }
            
            // 日付で日記を検索して削除（チャットから作成された日記の場合）
            $diary = Diary::where('user_id', Auth::id())
                ->whereDate('date', $this->date)
                ->whereNotNull('chat_conversation_id')
                ->first();
            
            if ($diary && $diary->chat_conversation_id === $this->conversationId) {
                // チャットから作成された日記のみ削除
                $diary->delete();
            }
            
            session()->flash('message', 'チャットを削除しました。');
            $this->dispatch('diary-saved');
            
            // 日記カレンダーページにリダイレクト
            return redirect()->route('diary');
        } catch (\Exception $e) {
            Log::error('Failed to delete conversation and close', ['error' => $e->getMessage()]);
            session()->flash('error', '削除に失敗しました: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.diary-chat-form');
    }
}
