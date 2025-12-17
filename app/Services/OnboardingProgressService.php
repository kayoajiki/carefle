<?php

namespace App\Services;

use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OnboardingProgressService
{
    /**
     * Check if a step is completed for a user.
     */
    public function checkStepCompletion(int $userId, string $step): bool
    {
        $progress = $this->getOrCreateProgress($userId);
        return $progress->isStepCompleted($step);
    }

    /**
     * Get the next step for a user.
     */
    public function getNextStep(int $userId): ?string
    {
        $progress = $this->getOrCreateProgress($userId);
        
        $steps = ['diagnosis', 'diary_first', 'assessment', 'diary_3days', 'diary_7days', 'manual_generated'];
        
        foreach ($steps as $step) {
            if (!$progress->isStepCompleted($step)) {
                return $step;
            }
        }
        
        return null; // All steps completed
    }

    /**
     * Update progress for a user.
     */
    public function updateProgress(int $userId, string $step): void
    {
        $progress = $this->getOrCreateProgress($userId);
        
        // Check if step was already completed
        $wasAlreadyCompleted = $progress->isStepCompleted($step);
        
        // Mark step as completed
        $progress->markStepCompleted($step);
        
        // Update current step
        $nextStep = $this->getNextStep($userId);
        $progress->current_step = $nextStep;
        
        // Set started_at if not set
        if (!$progress->started_at) {
            $progress->started_at = now();
        }
        
        // Set completed_at if all steps are done
        if ($progress->isOnboardingComplete() && !$progress->completed_at) {
            $progress->completed_at = now();
        }
        
        $progress->save();
        
        // Log activity for specific steps (only if newly completed)
        if (!$wasAlreadyCompleted) {
            $activityLogService = app(\App\Services\ActivityLogService::class);
            
            if ($step === 'diary_7days') {
                $activityLogService->log7DayDiaryCompleted($userId);
            }
        }
    }

    /**
     * Check if onboarding is complete.
     */
    public function isOnboardingComplete(int $userId): bool
    {
        $progress = $this->getOrCreateProgress($userId);
        return $progress->isOnboardingComplete();
    }

    /**
     * Check if a prompt should be shown for a step.
     * Returns true if the prompt should be shown (24 hours have passed since last prompt).
     */
    public function shouldShowPrompt(int $userId, string $step): bool
    {
        $progress = $this->getOrCreateProgress($userId);
        
        // If step is already completed, don't show prompt
        if ($progress->isStepCompleted($step)) {
            return false;
        }
        
        // If never prompted, show prompt
        if (!$progress->last_prompted_at) {
            return true;
        }
        
        // If 24 hours have passed since last prompt, show prompt
        $hoursSinceLastPrompt = $progress->last_prompted_at->diffInHours(now());
        return $hoursSinceLastPrompt >= 24;
    }

    /**
     * Mark that a prompt was shown.
     */
    public function markPromptShown(int $userId): void
    {
        $progress = $this->getOrCreateProgress($userId);
        $progress->last_prompted_at = now();
        $progress->save();
    }

    /**
     * Get or create onboarding progress for a user.
     */
    public function getOrCreateProgress(int $userId): OnboardingProgress
    {
        $progress = OnboardingProgress::where('user_id', $userId)->first();
        
        if (!$progress) {
            $progress = OnboardingProgress::create([
                'user_id' => $userId,
                'current_step' => 'diagnosis',
                'completed_steps' => [],
                'started_at' => now(),
            ]);
        }
        
        return $progress;
    }
}

