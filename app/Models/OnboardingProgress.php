<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_step',
        'completed_steps',
        'last_prompted_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_steps' => 'array',
            'last_prompted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the onboarding progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a step is completed.
     */
    public function isStepCompleted(string $step): bool
    {
        $completedSteps = $this->completed_steps ?? [];
        return in_array($step, $completedSteps, true);
    }

    /**
     * Get the current step.
     */
    public function getCurrentStep(): ?string
    {
        return $this->current_step;
    }

    /**
     * Mark a step as completed.
     */
    public function markStepCompleted(string $step): void
    {
        $completedSteps = $this->completed_steps ?? [];
        if (!in_array($step, $completedSteps, true)) {
            $completedSteps[] = $step;
            $this->completed_steps = $completedSteps;
            $this->save();
        }
    }

    /**
     * Check if onboarding is complete.
     */
    public function isOnboardingComplete(): bool
    {
        $requiredSteps = ['diagnosis', 'diary_first', 'assessment', 'diary_3days', 'diary_7days', 'manual_generated'];
        $completedSteps = $this->completed_steps ?? [];
        
        foreach ($requiredSteps as $step) {
            if (!in_array($step, $completedSteps, true)) {
                return false;
            }
        }
        
        return true;
    }
}
