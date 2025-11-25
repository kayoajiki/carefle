<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class CareerMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wcm_sheet_id',
        'target_year',
        'target_date',
        'title',
        'will_theme',
        'description',
        'mandala_data',
        'action_overview',
        'category',
        'status',
        'impact_score',
        'effort_score',
        'achievement_rate',
        'progress_points',
        'linked_life_event_id',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'mandala_data' => 'array',
        ];
    }

    /**
     * Get the user that owns the milestone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the linked life event.
     */
    public function linkedLifeEvent(): BelongsTo
    {
        return $this->belongsTo(LifeEvent::class, 'linked_life_event_id');
    }

    public function wcmSheet(): BelongsTo
    {
        return $this->belongsTo(WcmSheet::class);
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(MilestoneActionItem::class, 'career_milestone_id');
    }

    /**
     * Scope a query to only include achieved milestones.
     */
    public function scopeAchieved(Builder $query): Builder
    {
        return $query->where('status', 'achieved');
    }

    /**
     * Scope a query to only include in-progress milestones.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include planned milestones.
     */
    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', 'planned');
    }
}
