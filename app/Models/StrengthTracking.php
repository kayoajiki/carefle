<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class StrengthTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'strength_name',
        'assessment_id',
        'detected_from',
        'strength_level',
        'detected_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'date',
        ];
    }

    /**
     * Get the user that owns the strength tracking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assessment that detected this strength.
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(PersonalityAssessment::class, 'assessment_id');
    }

    /**
     * Scope a query to only include strengths within a date range.
     */
    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('detected_at', [$startDate, $endDate]);
    }
}
