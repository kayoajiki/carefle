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
        } else {
            // 新しい会話を開始（マウント時は自動開始しない）
            $this->messages = [];
            $this->conversationId = null;
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
        
        // 選択に応じたメッセージを生成
        $response = $this->chatService->generateResponseForSelection($topic, $this->reflectionType);
        
        // AIの応答を追加
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response,
            'timestamp' => now()->toDateTimeString(),
        ];
        
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
            $response = $this->chatService->generateResponse(
                $userMessageContent,
                $this->messages,
                $this->reflectionType
            );
            
            if ($response) {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $response,
                    'timestamp' => now()->toDateTimeString(),
                ];
            } else {
                // より詳細なエラーメッセージを表示
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
            }
        } catch (\Exception $e) {
            // ローディングメッセージを削除
            if (!empty($this->messages) && end($this->messages)['content'] === 'loading') {
                array_pop($this->messages);
            }
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

    public function render()
    {
        return view('livewire.diary-chat-form');
    }
}
