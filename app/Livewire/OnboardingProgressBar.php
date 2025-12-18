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
        
        // オンボーディング完了後は完了メッセージを表示（1週間経過後は非表示）
        if ($isComplete) {
            $progress = $this->progressService->getOrCreateProgress($userId);
            $completedAt = $progress->completed_at;
            $showCompletionMessage = false;
            
            // 完了日時が記録されていて、1週間以内の場合のみ表示
            if ($completedAt) {
                $oneWeekAgo = now()->subWeek();
                $showCompletionMessage = $completedAt->isAfter($oneWeekAgo);
            }
            
            return view('livewire.onboarding-progress-bar', [
                'progress' => null,
                'isComplete' => true,
                'showCompletionMessage' => $showCompletionMessage,
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
            'diary_3days' => [
                'label' => '3日間記録',
                'icon' => 'calendar',
                'route' => 'diary',
            ],
            'diary_7days' => [
                'label' => '7日間記録',
                'icon' => 'calendar',
                'route' => 'diary',
            ],
            'manual_generated' => [
                'label' => '持ち味レポ',
                'icon' => 'book-open',
                'route' => 'onboarding.mini-manual',
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
