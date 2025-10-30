<?php

namespace App\Livewire;

use App\Models\WcmSheet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.simple_component')]
class WcmSheetShow extends Component
{
    public WcmSheet $sheet;
    public string $will_text = '';
    public string $can_text = '';
    public string $must_text = '';

    public function mount(int $id): void
    {
        $this->sheet = WcmSheet::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $this->will_text = (string)$this->sheet->will_text;
        $this->can_text  = (string)$this->sheet->can_text;
        $this->must_text = (string)$this->sheet->must_text;
    }

    public function save(): void
    {
        $this->sheet->update([
            'will_text' => $this->will_text,
            'can_text'  => $this->can_text,
            'must_text' => $this->must_text,
            'is_draft'  => false,
        ]);
        session()->flash('saved', '保存しました');
    }

    public function updatedWillText(): void { $this->autosave(); }
    public function updatedCanText(): void { $this->autosave(); }
    public function updatedMustText(): void { $this->autosave(); }

    private function autosave(): void
    {
        // 下書き的に常時保存（バージョンは上げない）
        $this->sheet->update([
            'will_text' => $this->will_text,
            'can_text'  => $this->can_text,
            'must_text' => $this->must_text,
        ]);
    }

    public function saveAsNew(): mixed
    {
        $userId = Auth::id();
        $count = WcmSheet::where('user_id', $userId)->count();
        if ($count >= 10) {
            session()->flash('error', '保存上限（10件）に達しています。古いシートを削除してください。');
            return null;
        }

        $maxVersion = WcmSheet::where('user_id', $userId)->max('version') ?? 0;
        $new = WcmSheet::create([
            'user_id'   => $userId,
            'title'     => $this->sheet->title,
            'will_text' => $this->will_text,
            'can_text'  => $this->can_text,
            'must_text' => $this->must_text,
            'version'   => $maxVersion + 1,
        ]);

        return redirect()->route('wcm.sheet', ['id' => $new->id]);
    }

    public function render()
    {
        $versions = WcmSheet::where('user_id', Auth::id())
            ->where('is_draft', false)
            ->orderByDesc('version')
            ->limit(10)
            ->get(['id','version','created_at']);

        return view('livewire.wcm-sheet-show', [
            'versions' => $versions,
        ]);
    }
}


