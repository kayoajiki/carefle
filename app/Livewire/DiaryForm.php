<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Diary;
use App\Models\CareerMilestone;
use App\Services\ActionItemGeneratorService;
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
    }

    public function save()
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

        // 既存の日記を確認
        if ($this->diaryId) {
            // diaryIdが設定されている場合は更新
            $existingDiary = Diary::where('user_id', Auth::id())
                ->where('id', $this->diaryId)
                ->firstOrFail();
            $existingDiary->update($data);
            session()->flash('message', '日記を更新しました');
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
            } else {
                // 新規作成
                $diary = Diary::create($data);
                $this->diaryId = $diary->id;
                session()->flash('message', '日記を保存しました');
            }
        }

        // 保存後の状態を更新
        $this->photo = null;
        
        // 保存した日記を再取得してexistingPhotoを確実に更新
        $savedDiary = null;
        if ($this->diaryId) {
            $savedDiary = Diary::where('user_id', Auth::id())
                ->where('id', $this->diaryId)
                ->first();
            
            if ($savedDiary) {
                $this->existingPhoto = $savedDiary->photo;
            }
        }
        
        // 日記内容からアクションアイテムを提案（コンテンツがある場合のみ）
        if ($savedDiary && !empty($this->content)) {
            $this->suggestActionItems($savedDiary);
            $this->updateMilestoneProgress($savedDiary);
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

    public function render()
    {
        return view('livewire.diary-form');
    }
}
