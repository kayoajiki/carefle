<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Diary;
use App\Models\CareerMilestone;
use App\Models\DiaryGoalConnection;
use App\Services\ActionItemGeneratorService;
use App\Services\GoalConnectionService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DiaryForm extends Component
{
    use WithFileUploads;

    public $date;
    public $motivation = 50;
    public $content;
    public $photo;
    public $existingPhoto;
    public $diaryId = null;
    public $suggestedActionItems = [];
    public $showActionItems = false;
    public $goalConnections = [];

    protected $rules = [
        'date' => 'required|date',
        'motivation' => 'required|integer|min:0|max:100',
        'content' => 'nullable|string|max:2000',
        'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:5120', // 5MBまで、複数の画像形式に対応
    ];

    public function mount($date = null, $diaryId = null)
    {
        $this->date = $date ?? date('Y-m-d');
        
        if ($diaryId) {
            $this->loadDiary($diaryId);
        } else {
            // 指定された日付の日記があれば読み込む
            $diary = Diary::where('user_id', Auth::id())
                ->whereDate('date', $this->date)
                ->first();
            
            if ($diary) {
                $this->loadDiary($diary->id);
            } else {
                // 新規作成の場合は初期値を設定
                $this->diaryId = null;
                $this->motivation = 50;
                $this->content = null;
                $this->existingPhoto = null;
            }
        }
    }

    public function loadDiary($id)
    {
        $diary = Diary::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $this->diaryId = $diary->id;
        $this->date = $diary->date->format('Y-m-d');
        $this->motivation = $diary->motivation;
        $this->content = $diary->content;
        $this->existingPhoto = $diary->photo;
        $this->loadGoalConnections($diary->id);
    }

    /**
     * 保存処理の共通ロジック
     */
    protected function performSave(): Diary
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'date' => $this->date,
            'motivation' => $this->motivation,
            'content' => $this->content,
        ];

        // 写真のアップロード処理
        if ($this->photo) {
            // 既存の写真を削除
            if ($this->existingPhoto && Storage::disk('public')->exists($this->existingPhoto)) {
                Storage::disk('public')->delete($this->existingPhoto);
            }

            // 新しい写真を保存
            $path = $this->photo->store('diaries/' . Auth::id(), 'public');
            $data['photo'] = $path;
        } elseif ($this->existingPhoto) {
            // 既存の写真を保持
            $data['photo'] = $this->existingPhoto;
        }

        // オンボーディング進捗サービスを取得
        $progressService = app(\App\Services\OnboardingProgressService::class);
        
        // 初回日記ステップが完了しているかチェック
        $isDiaryFirstCompleted = $progressService->checkStepCompletion(Auth::id(), 'diary_first');
        
        // 初回日記かどうかをチェック（保存前）
        $isFirstDiary = !Diary::where('user_id', Auth::id())->exists();
        $wasNewDiary = !$this->diaryId; // 保存前の状態を保持

        // 既存の日記を確認
        if ($this->diaryId) {
            // diaryIdが設定されている場合は更新
            $existingDiary = Diary::where('user_id', Auth::id())
                ->where('id', $this->diaryId)
                ->firstOrFail();
            $existingDiary->update($data);
            session()->flash('message', '日記を更新しました');
            $savedDiary = $existingDiary;
        } else {
            // diaryIdが設定されていない場合は、日付で既存の日記を検索
            $existingDiary = Diary::where('user_id', Auth::id())
                ->whereDate('date', $this->date)
                ->first();

            if ($existingDiary) {
                // 既存の日記を更新
                $existingDiary->update($data);
                $this->diaryId = $existingDiary->id;
                session()->flash('message', '日記を更新しました');
                $savedDiary = $existingDiary;
            } else {
                // 新規作成
                $diary = Diary::create($data);
                $this->diaryId = $diary->id;
                session()->flash('message', '日記を保存しました');
                $savedDiary = $diary;
            }
        }

        // 保存後の状態を更新
        $this->photo = null;
        
        // 保存した日記を再取得してexistingPhotoを確実に更新
        if ($savedDiary) {
            $this->existingPhoto = $savedDiary->photo;
            
            // Update user's last_activity_at
            $user = Auth::user();
            if ($user) {
                $user->last_activity_at = now();
                $user->save();
            }
        }
        
        // 初回日記保存時にオンボーディング進捗を更新
        // 初回日記ステップが未完了の場合、日記を保存したら完了としてマーク
        if (!$isDiaryFirstCompleted && ($isFirstDiary || $wasNewDiary || !empty($savedDiary->content))) {
            $progressService->updateProgress(Auth::id(), 'diary_first');
            
            // アクティビティログに記録（初回日記作成のみ）
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logDiaryCreated(Auth::id(), $savedDiary->id, $savedDiary->date->format('Y-m-d'));
            
            session()->flash('message', '日記を保存しました！🎉 初回の記録、おめでとうございます！');
        } else {
            // 連続記録日数を計算して褒めメッセージを追加
            $streak = $this->calculateStreak(Auth::id());
            if ($streak > 0) {
                $praiseMessage = $this->getPraiseMessage($streak);
                if ($praiseMessage) {
                    session()->flash('message', '日記を保存しました！' . $praiseMessage);
                }
            }
        }
        
        // 3日間記録の進捗をチェック
        if (!$progressService->checkStepCompletion(Auth::id(), 'diary_3days')) {
            $threeDaysAgo = now()->subDays(2)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryCount3Days = Diary::where('user_id', Auth::id())
                ->whereBetween('date', [$threeDaysAgo, $today])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->distinct('date')
                ->count('date');
            
            if ($diaryCount3Days >= 3) {
                $progressService->updateProgress(Auth::id(), 'diary_3days');
            }
        }
        
        // 7日間記録の進捗をチェック
        if (!$progressService->checkStepCompletion(Auth::id(), 'diary_7days')) {
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryCount7Days = Diary::where('user_id', Auth::id())
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->distinct('date')
                ->count('date');
            
            if ($diaryCount7Days >= 7) {
                $progressService->updateProgress(Auth::id(), 'diary_7days');
            }
        }
        
        return $savedDiary;
    }

    /**
     * 保存のみ（AI稼働なし）
     */
    public function save()
    {
        $savedDiary = $this->performSave();
        
        // 既存の接続情報を読み込む（AI処理は実行しない）
        if ($savedDiary) {
            $this->loadGoalConnections($savedDiary->id);
        } else {
            $this->goalConnections = [];
        }
        
        // 親コンポーネント（DiaryCalendar）に更新を通知
        $this->dispatch('diary-saved');
    }

    /**
     * 保存 + アクション提案（AI稼働）
     */
    public function saveWithActionSuggestion()
    {
        $savedDiary = $this->performSave();
        
        // AI処理を実行（コンテンツがある場合のみ）
        if ($savedDiary && !empty($this->content)) {
            $this->suggestActionItems($savedDiary);
            $this->updateMilestoneProgress($savedDiary);
            // 接続情報の検出と読み込み（detectGoalConnections内でgoalConnectionsも更新される）
            $this->detectGoalConnections($savedDiary);
        } else {
            // コンテンツがない場合は既存の接続情報を読み込む
            if ($savedDiary) {
                $this->loadGoalConnections($savedDiary->id);
            } else {
                $this->goalConnections = [];
            }
        }
        
        // 親コンポーネント（DiaryCalendar）に更新を通知
        $this->dispatch('diary-saved');
    }

    public function deletePhoto()
    {
        if ($this->existingPhoto && Storage::disk('public')->exists($this->existingPhoto)) {
            Storage::disk('public')->delete($this->existingPhoto);
        }
        $this->existingPhoto = null;
        
        if ($this->diaryId) {
            $diary = Diary::where('user_id', Auth::id())
                ->where('id', $this->diaryId)
                ->first();
            if ($diary) {
                $diary->update(['photo' => null]);
            }
        }
        
        $this->dispatch('diary-saved');
    }

    /**
     * 日記内容からアクションアイテムを提案
     */
    protected function suggestActionItems(Diary $diary)
    {
        if (empty($diary->content)) {
            return;
        }

        try {
            $actionService = app(ActionItemGeneratorService::class);
            $suggestedActions = $actionService->generateActionItemsFromDiary($diary->content);
            
            if (!empty($suggestedActions)) {
                $this->suggestedActionItems = $suggestedActions;
                $this->showActionItems = true;
                
                // アクションアイテムを保存（ユーザーが承認するまで保留状態）
                // ここでは提案のみ表示し、ユーザーが承認したら保存する
            }
        } catch (\Exception $e) {
            // エラーは無視（アクションアイテム生成はオプション機能）
            Log::warning('Failed to generate action items', ['error' => $e->getMessage()]);
        }
    }

    /**
     * マイルストーンの進捗を更新
     */
    protected function updateMilestoneProgress(Diary $diary)
    {
        if (empty($diary->content)) {
            return;
        }

        try {
            // 日記に関連するマイルストーンを取得
            $milestones = CareerMilestone::where('user_id', Auth::id())
                ->whereIn('status', ['planned', 'in_progress'])
                ->get();

            foreach ($milestones as $milestone) {
                // 日記内容がマイルストーンに関連しているかチェック
                // 簡単なキーワードマッチング（将来的にはAIで改善可能）
                $related = false;
                if ($milestone->title && stripos($diary->content, $milestone->title) !== false) {
                    $related = true;
                }
                if ($milestone->will_theme && stripos($diary->content, $milestone->will_theme) !== false) {
                    $related = true;
                }

                if ($related) {
                    // マイルストーンの進捗ポイントを更新
                    // 日記を書いたことで進捗ポイントを追加（簡単な実装）
                    $milestone->increment('progress_points', 1);
                    
                    // 達成率を再計算（完了アクション数 / 全アクション数）
                    $totalActions = $milestone->actionItems()->count();
                    $completedActions = $milestone->actionItems()->where('status', 'completed')->count();
                    
                    if ($totalActions > 0) {
                        $achievementRate = ($completedActions / $totalActions) * 100;
                        $milestone->update(['achievement_rate' => round($achievementRate, 2)]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update milestone progress', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 提案されたアクションアイテムを承認して保存
     */
    public function acceptActionItem($index)
    {
        if (!isset($this->suggestedActionItems[$index])) {
            return;
        }

        try {
            $actionService = app(ActionItemGeneratorService::class);
            $action = $this->suggestedActionItems[$index];
            
            // アクションアイテムを保存
            $actionService->saveSuggestedActions([$action], $this->diaryId);
            
            // 提案リストから削除
            unset($this->suggestedActionItems[$index]);
            $this->suggestedActionItems = array_values($this->suggestedActionItems);
            
            if (empty($this->suggestedActionItems)) {
                $this->showActionItems = false;
            }
            
            session()->flash('message', 'アクションアイテムを追加しました');
        } catch (\Exception $e) {
            Log::error('Failed to save action item', ['error' => $e->getMessage()]);
            session()->flash('error', 'アクションアイテムの保存に失敗しました');
        }
    }

    /**
     * 提案されたアクションアイテムを却下
     */
    public function dismissActionItems()
    {
        $this->suggestedActionItems = [];
        $this->showActionItems = false;
    }

    /**
     * 日記とマイルストーン・WCMシートのWillテーマの接続を検出
     */
    protected function detectGoalConnections(Diary $diary)
    {
        if (empty($diary->content)) {
            $this->goalConnections = [];
            return;
        }

        try {
            $connectionService = app(GoalConnectionService::class);
            $connections = $connectionService->detectConnections($diary);

            // 既存の接続を削除
            DiaryGoalConnection::where('diary_id', $diary->id)->delete();

            // 新しい接続を保存（最大3件まで）
            $savedConnections = [];
            foreach (array_slice($connections, 0, 3) as $connection) {
                $savedConnections[] = DiaryGoalConnection::create($connection);
            }

            // 保存した接続情報を読み込んでgoalConnectionsを更新
            $this->loadGoalConnections($diary->id);
        } catch (\Exception $e) {
            Log::warning('Failed to detect goal connections', [
                'error' => $e->getMessage(),
                'diary_id' => $diary->id,
            ]);
            // エラー時は既存の接続情報を読み込む
            $this->loadGoalConnections($diary->id);
        }
    }

    /**
     * 接続情報を読み込む
     */
    protected function loadGoalConnections($diaryId)
    {
        if (!$diaryId) {
            $this->goalConnections = [];
            return;
        }

        $connections = DiaryGoalConnection::where('diary_id', $diaryId)
            ->with(['milestone', 'wcmSheet'])
            ->orderBy('connection_score', 'desc')
            ->get();

        $this->goalConnections = $connections->map(function ($connection) {
            $connected = $connection->connected();
            return [
                'id' => $connection->id,
                'type' => $connection->connection_type,
                'score' => $connection->connection_score,
                'reason' => $connection->connection_reason,
                'will_theme' => $connection->will_theme,
                'connected' => $connected ? [
                    'id' => $connected->id,
                    'title' => $connection->connection_type === 'milestone' 
                        ? $connected->title 
                        : ($connected->title ?? 'WCMシート'),
                ] : null,
            ];
        })->toArray();
    }

    /**
     * 連続記録日数を計算
     */
    protected function calculateStreak(int $userId): int
    {
        $diaries = Diary::where('user_id', $userId)
            ->orderByDesc('date')
            ->get()
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($diaries->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expectedDate = now()->format('Y-m-d');
        
        foreach ($diaries as $date) {
            if ($date === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * 連続記録日数に応じた褒めメッセージを取得
     */
    protected function getPraiseMessage(int $streak): ?string
    {
        return match(true) {
            $streak >= 30 => ' 30日連続記録達成！素晴らしい継続力です！🌟',
            $streak >= 14 => ' 2週間連続記録達成！習慣化ができていますね！✨',
            $streak >= 7 => ' 7日連続記録達成！1週間続けられました！🎉',
            $streak >= 3 => ' ' . $streak . '日連続記録中！この調子で続けましょう！💪',
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.diary-form');
    }
}