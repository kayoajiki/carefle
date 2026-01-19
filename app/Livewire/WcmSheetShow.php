<?php

namespace App\Livewire;

use App\Models\WcmSheet;
use App\Services\WcmAutoGenerationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

class WcmSheetShow extends Component
{
    public WcmSheet $sheet;
    public string $will_text = '';
    public string $can_text = '';
    public string $must_text = '';
    public bool $isAdminView = false;
    
    // 提案用プロパティ
    public ?string $proposedWill = null;
    public ?string $proposedCan = null;
    public ?string $proposedMust = null;
    public bool $showProposalModal = false;
    public string $proposalType = ''; // 'will', 'can', 'must'
    public string $editingProposal = ''; // 編集モード用

    public function mount(int $id): void
    {
        $user = Auth::user();
        
        // 管理者の場合は、is_admin_visible = true のコンテンツも閲覧可能
        if ($user && $user->isAdmin()) {
            $this->sheet = WcmSheet::where('id', $id)
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere(function($q) {
                              $q->where('is_admin_visible', true);
                          });
                })
                ->firstOrFail();
            
            // 他のユーザーのコンテンツを閲覧している場合は編集不可
            $this->isAdminView = $this->sheet->user_id !== $user->id;
        } else {
            $this->sheet = WcmSheet::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            $this->isAdminView = false;
        }
        
        $this->will_text = (string)$this->sheet->will_text;
        $this->can_text  = (string)$this->sheet->can_text;
        $this->must_text = (string)$this->sheet->must_text;
    }

    public function save(): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }
        
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
        if ($this->isAdminView) {
            return; // 管理者は他のユーザーのコンテンツを編集できない
        }
        
        // 下書き的に常時保存（バージョンは上げない）
        $this->sheet->update([
            'will_text' => $this->will_text,
            'can_text'  => $this->can_text,
            'must_text' => $this->must_text,
        ]);
    }

    public function saveAsNew(): mixed
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return null;
        }
        
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
            ->get(['id','version','created_at','is_admin_visible']);

        return view('livewire.wcm-sheet-show', [
            'versions' => $versions,
        ]);
    }

    public function delete(int $id)
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを削除できません。');
            return;
        }
        
        $sheet = WcmSheet::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $sheet->delete();

        // 削除したのが現在表示中なら、最新の確定版へ遷移 or 作成へ
        if ($id === $this->sheet->id) {
            $next = WcmSheet::where('user_id', Auth::id())
                ->where('is_draft', false)
                ->orderByDesc('version')
                ->first();
            if ($next) {
                return redirect()->route('wcm.sheet', ['id' => $next->id]);
            }
            return redirect()->route('wcm.start');
        }

        // 画面はそのまま、一覧だけ再取得させる
        session()->flash('saved', '削除しました');
    }

    /**
     * WillをAI生成
     */
    public function generateWill(): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }
        
        try {
            $service = app(WcmAutoGenerationService::class);
            $generated = $service->generateWill(Auth::id(), $this->will_text);
            
            if ($generated !== null) {
                // 既存テキストは保持し、提案をプロパティに保存
                $this->proposedWill = $generated;
                $this->proposalType = 'will';
                $this->showProposalModal = true;
            } else {
                session()->flash('error', 'Willの生成に失敗しました。しばらく時間をおいて再度お試しください。');
            }
        } catch (\Exception $e) {
            Log::error('WcmSheetShow: Failed to generate Will', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Willの生成中にエラーが発生しました。');
        }
    }

    /**
     * CanをAI生成
     */
    public function generateCan(): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }
        
        try {
            $service = app(WcmAutoGenerationService::class);
            $generated = $service->generateCan(Auth::id(), $this->can_text);
            
            if ($generated !== null) {
                // 既存テキストは保持し、提案をプロパティに保存
                $this->proposedCan = $generated;
                $this->proposalType = 'can';
                $this->showProposalModal = true;
            } else {
                session()->flash('error', 'Canの生成に失敗しました。しばらく時間をおいて再度お試しください。');
            }
        } catch (\Exception $e) {
            Log::error('WcmSheetShow: Failed to generate Can', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Canの生成中にエラーが発生しました。');
        }
    }

    /**
     * MustをAI生成
     */
    public function generateMust(): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }
        
        try {
            $service = app(WcmAutoGenerationService::class);
            $generated = $service->generateMust(Auth::id(), $this->must_text);
            
            if ($generated !== null) {
                // 既存テキストは保持し、提案をプロパティに保存
                $this->proposedMust = $generated;
                $this->proposalType = 'must';
                $this->showProposalModal = true;
            } else {
                session()->flash('error', 'Mustの生成に失敗しました。しばらく時間をおいて再度お試しください。');
            }
        } catch (\Exception $e) {
            Log::error('WcmSheetShow: Failed to generate Must', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Mustの生成中にエラーが発生しました。');
        }
    }

    /**
     * 提案を採用（置き換え/追加/マージ）
     */
    public function acceptProposal(string $method = 'replace'): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }

        $proposal = $this->getCurrentProposal();
        if ($proposal === null) {
            return;
        }

        $currentText = $this->getCurrentText();
        
        switch ($method) {
            case 'replace':
                $newText = $proposal;
                break;
            case 'append':
                $newText = trim($currentText) . "\n\n" . trim($proposal);
                break;
            case 'merge':
                // 既存テキストと提案をマージ（重複を避けながら）
                $existingLines = array_filter(array_map('trim', explode("\n", $currentText)));
                $proposalLines = array_filter(array_map('trim', explode("\n", $proposal)));
                $mergedLines = array_unique(array_merge($existingLines, $proposalLines));
                $newText = implode("\n", $mergedLines);
                break;
            default:
                $newText = $proposal;
        }

        $this->setCurrentText($newText);
        $this->autosave();
        $this->closeProposalModal();
        session()->flash('saved', '提案を採用しました');
    }

    /**
     * 提案を破棄
     */
    public function rejectProposal(): void
    {
        $this->closeProposalModal();
        session()->flash('saved', '提案を破棄しました');
    }

    /**
     * 提案を編集モードに切り替え
     */
    public function editProposal(): void
    {
        $proposal = $this->getCurrentProposal();
        if ($proposal !== null) {
            $this->editingProposal = $proposal;
        }
    }

    /**
     * 編集した提案を適用
     */
    public function applyEditedProposal(string $method = 'replace'): void
    {
        if ($this->isAdminView) {
            session()->flash('error', '管理者は他のユーザーのコンテンツを編集できません。');
            return;
        }

        if (trim($this->editingProposal) === '') {
            session()->flash('error', '提案が空です。');
            return;
        }

        $currentText = $this->getCurrentText();
        
        switch ($method) {
            case 'replace':
                $newText = $this->editingProposal;
                break;
            case 'append':
                $newText = trim($currentText) . "\n\n" . trim($this->editingProposal);
                break;
            case 'merge':
                $existingLines = array_filter(array_map('trim', explode("\n", $currentText)));
                $proposalLines = array_filter(array_map('trim', explode("\n", $this->editingProposal)));
                $mergedLines = array_unique(array_merge($existingLines, $proposalLines));
                $newText = implode("\n", $mergedLines);
                break;
            default:
                $newText = $this->editingProposal;
        }

        $this->setCurrentText($newText);
        $this->autosave();
        $this->closeProposalModal();
        $this->editingProposal = '';
        session()->flash('saved', '編集した提案を適用しました');
    }

    /**
     * 提案モーダルを閉じる
     */
    public function closeProposalModal(): void
    {
        $this->showProposalModal = false;
        $this->proposedWill = null;
        $this->proposedCan = null;
        $this->proposedMust = null;
        $this->proposalType = '';
        $this->editingProposal = '';
    }

    /**
     * 現在の提案を取得
     */
    private function getCurrentProposal(): ?string
    {
        return match($this->proposalType) {
            'will' => $this->proposedWill,
            'can' => $this->proposedCan,
            'must' => $this->proposedMust,
            default => null,
        };
    }

    /**
     * 現在のテキストを取得
     */
    private function getCurrentText(): string
    {
        return match($this->proposalType) {
            'will' => $this->will_text,
            'can' => $this->can_text,
            'must' => $this->must_text,
            default => '',
        };
    }

    /**
     * 現在のテキストを設定
     */
    private function setCurrentText(string $text): void
    {
        match($this->proposalType) {
            'will' => $this->will_text = $text,
            'can' => $this->can_text = $text,
            'must' => $this->must_text = $text,
            default => null,
        };
    }
}
