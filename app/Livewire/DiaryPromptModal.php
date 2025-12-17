<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OnboardingProgressService;
use App\Models\Diagnosis;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;

class DiaryPromptModal extends Component
{
    public $show = false;
    
    protected OnboardingProgressService $progressService;

    public function boot(OnboardingProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function mount(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            $this->show = false;
            return;
        }

        $user = Auth::user();
        
        // プロフィールが完了していない場合は表示しない
        if (!$user || !$user->profile_completed) {
            $this->show = false;
            return;
        }

        // 診断が完了していない場合は表示しない
        if (!$this->progressService->checkStepCompletion($userId, 'diagnosis')) {
            $this->show = false;
            return;
        }

        // 初回日記が既に記録されている場合は表示しない
        if ($this->progressService->checkStepCompletion($userId, 'diary_first')) {
            $this->show = false;
            return;
        }

        // 今日の日記が既に存在する場合は表示しない
        $todayDiary = Diary::where('user_id', $userId)
            ->whereDate('date', today())
            ->first();
        
        if ($todayDiary) {
            $this->show = false;
            return;
        }

        // プロンプトを表示すべきかチェック（24時間以内に表示した場合は再表示しない）
        if (!$this->progressService->shouldShowPrompt($userId, 'diary_first')) {
            $this->show = false;
            return;
        }

        // すべての条件を満たした場合のみ表示
        $this->show = true;
    }

    public function writeDiary()
    {
        $this->show = false;
        $this->dispatch('close-modal');
        return $this->redirect(route('diary'), navigate: true);
    }

    public function dismiss(): void
    {
        $userId = Auth::id();
        if ($userId) {
            $this->progressService->markPromptShown($userId);
        }
        $this->show = false;
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.diary-prompt-modal');
    }
}
