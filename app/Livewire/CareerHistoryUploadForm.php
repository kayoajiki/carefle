<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CareerHistoryDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CareerHistoryUploadForm extends Component
{
    use WithFileUploads;

    public $pdf;
    public $documents;

    protected $rules = [
        'pdf' => 'required|mimes:pdf|max:10240', // 10MBまで
    ];

    public function mount()
    {
        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        $this->documents = CareerHistoryDocument::where('user_id', Auth::id())
            ->recent()
            ->get();
    }

    public function save()
    {
        $this->validate();

        try {
            // ファイルを保存
            $path = $this->pdf->store('career-histories/' . Auth::id(), 'public');

            // データベースに記録
            CareerHistoryDocument::create([
                'user_id' => Auth::id(),
                'original_filename' => $this->pdf->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $this->pdf->getSize(),
                'uploaded_at' => now(),
            ]);

            // 成功メッセージ
            session()->flash('message', '職務経歴書をアップロードしました。');

            // 状態をリセット
            $this->pdf = null;
            $this->loadDocuments();
        } catch (\Exception $e) {
            session()->flash('error', 'アップロードに失敗しました: ' . $e->getMessage());
        }
    }

    public function updateMemo($id, $memo)
    {
        $document = CareerHistoryDocument::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // 20文字制限
        $memo = mb_substr($memo, 0, 20);

        $document->update(['memo' => $memo]);
        $this->loadDocuments();
    }

    public function delete($id)
    {
        $document = CareerHistoryDocument::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // ファイルを削除
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // データベースから削除
        $document->delete();

        session()->flash('message', '職務経歴書を削除しました。');
        $this->loadDocuments();
    }

    public function render()
    {
        return view('livewire.career-history-upload-form');
    }
}
