<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OnboardingProgressService;
use App\Models\Diagnosis;
use Illuminate\Support\Facades\Auth;

class DiagnosisPromptModal extends Component
{
    public $show = false;
    
    protected OnboardingProgressService $progressService;

    public function boot(OnboardingProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function mount(): void
    {
        // 一時的に完全に無効化
        $this->show = false;
        return;
    }

    public function continueDiagnosis()
    {
        $this->show = false;
        $this->dispatch('close-modal');
        return $this->redirect(route('diagnosis.start'), navigate: true);
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
        // 一時的に無効化
        return view('livewire.diagnosis-prompt-modal', ['show' => false]);
    }
}
