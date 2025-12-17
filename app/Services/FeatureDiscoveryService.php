<?php

namespace App\Services;

use App\Models\User;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\PersonalityAssessment;
use App\Models\WcmSheet;
use App\Models\LifeEvent;
use App\Services\OnboardingProgressService;
use Illuminate\Support\Facades\Auth;

class FeatureDiscoveryService
{
    protected OnboardingProgressService $progressService;

    public function __construct(OnboardingProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Check if a feature should be unlocked for a user.
     */
    public function isFeatureUnlocked(int $userId, string $feature): bool
    {
        // オンボーディング完了後はすべての機能をアンロック
        if ($this->progressService->isOnboardingComplete($userId)) {
            return true;
        }

        // 機能ごとのアンロック条件
        return match($feature) {
            'diagnosis' => true, // 常に利用可能
            'diary' => $this->hasCompletedStep($userId, 'diagnosis'),
            'assessment' => $this->hasCompletedStep($userId, 'diary_first'),
            'wcm' => $this->hasCompletedStep($userId, 'assessment'),
            'life_history' => $this->hasCompletedStep($userId, 'wcm_created'),
            'milestones' => $this->hasCompletedStep($userId, 'wcm_created'),
            'mapping' => $this->hasCompletedStep($userId, 'manual_generated'),
            default => false,
        };
    }

    /**
     * Get unlocked features for a user.
     */
    public function getUnlockedFeatures(int $userId): array
    {
        $features = [
            'diagnosis',
            'diary',
            'assessment',
            'wcm',
            'life_history',
            'milestones',
            'mapping',
        ];

        $unlocked = [];
        foreach ($features as $feature) {
            if ($this->isFeatureUnlocked($userId, $feature)) {
                $unlocked[] = $feature;
            }
        }

        return $unlocked;
    }

    /**
     * Check if user has completed a specific step.
     */
    protected function hasCompletedStep(int $userId, string $step): bool
    {
        return $this->progressService->checkStepCompletion($userId, $step);
    }

    /**
     * Get feature discovery hints (when to show tooltips/badges).
     */
    public function getDiscoveryHints(int $userId): array
    {
        $hints = [];

        // 診断完了後、日記機能を案内
        if ($this->hasCompletedStep($userId, 'diagnosis') && !$this->hasCompletedStep($userId, 'diary_first')) {
            $hints['diary'] = '診断が完了しました！次は日記を書いてみましょう。';
        }

        // 初回日記完了後、自己診断を案内
        if ($this->hasCompletedStep($userId, 'diary_first') && !$this->hasCompletedStep($userId, 'assessment')) {
            $hints['assessment'] = '日記を書きましたね！自己診断結果を入力すると、もっと深い分析ができます。';
        }

        // 自己診断完了後、WCMを案内
        if ($this->hasCompletedStep($userId, 'assessment') && !$this->hasCompletedStep($userId, 'wcm_created')) {
            $hints['wcm'] = '自己診断結果が記録されました！WCMシートを作成すると、未来の自分が見えてきます。';
        }

        return $hints;
    }
}

