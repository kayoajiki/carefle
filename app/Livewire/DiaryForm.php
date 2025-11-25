<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DiaryForm extends Component
{
    use WithFileUploads;

    public $date;
    public $motivation = 50;
    public $content;
    public $photo;
    public $existingPhoto;
    public $diaryId = null;

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
        if ($this->diaryId) {
            $savedDiary = Diary::where('user_id', Auth::id())
                ->where('id', $this->diaryId)
                ->first();
            
            if ($savedDiary) {
                $this->existingPhoto = $savedDiary->photo;
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

    public function render()
    {
        return view('livewire.diary-form');
    }
}
