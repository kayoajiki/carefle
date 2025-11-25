<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilestoneActionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'career_milestone_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'impact_score',
        'effort_score',
        'completed_at',
        'diary_id',
        'points_awarded',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(CareerMilestone::class, 'career_milestone_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diary(): BelongsTo
    {
        return $this->belongsTo(Diary::class, 'diary_id');
    }
}

