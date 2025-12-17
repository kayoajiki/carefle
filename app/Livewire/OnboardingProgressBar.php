<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OnboardingProgressService;
use Illuminate\Support\Facades\Auth;

class OnboardingProgressBar extends Component
{
    protected OnboardingProgressService $progressService;

    public function boot(OnboardingProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function render()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return view('livewire.onboarding-progress-bar', [
                'progress' => null,
                'isComplete' => false,
            ]);
        }

        $isComplete = $this->progressService->isOnboardingComplete($userId);
        
        // オンボーディング完了後は非表示（nullを返す）
        if ($isComplete) {
            return view('livewire.onboarding-progress-bar', [
                'progress' => null,
                'isComplete' => true,
            ]);
        }

        $progress = $this->progressService->getOrCreateProgress($userId);
        $nextStep = $this->progressService->getNextStep($userId);

        // ステップ定義
        $steps = [
            'diagnosis' => [
                'label' => '診断',
                'icon' => 'chart-bar',
                'route' => 'diagnosis.start',
            ],
            'diary_first' => [
                'label' => '初回日記',
                'icon' => 'document-text',
                'route' => 'diary',
            ],
            'assessment' => [
                'label' => '自己診断',
                'icon' => 'user-circle',
                'route' => 'assessments.index',
            ],
            'wcm_created' => [
                'label' => 'WCM作成',
                'icon' => 'light-bulb',
                'route' => 'wcm.start',
            ],
            'diary_7days' => [
                'label' => '7日間記録',
                'icon' => 'calendar',
                'route' => 'diary',
            ],
            'manual_generated' => [
                'label' => 'プチ取説',
                'icon' => 'book-open',
                'route' => 'dashboard', // TODO: Phase 5で実装予定
            ],
        ];

        // 各ステップの状態を計算
        $stepStatuses = [];
        foreach ($steps as $stepKey => $stepInfo) {
            $stepStatuses[$stepKey] = [
                'label' => $stepInfo['label'],
                'icon' => $stepInfo['icon'],
                'route' => $stepInfo['route'],
                'completed' => $progress->isStepCompleted($stepKey),
                'isCurrent' => $stepKey === $nextStep,
            ];
        }

        // 進捗率を計算
        $totalSteps = count($steps);
        $completedSteps = count($progress->completed_steps ?? []);
        $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

        return view('livewire.onboarding-progress-bar', [
            'progress' => $progress,
            'isComplete' => $isComplete,
            'stepStatuses' => $stepStatuses,
            'progressPercentage' => $progressPercentage,
            'nextStep' => $nextStep,
        ]);
    }
}
