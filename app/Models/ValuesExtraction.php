<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class ValuesExtraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'value_name',
        'source_type',
        'source_id',
        'confidence_score',
        'first_detected_at',
        'last_detected_at',
    ];

    protected function casts(): array
    {
        return [
            'first_detected_at' => 'date',
            'last_detected_at' => 'date',
        ];
    }

    /**
     * Get the user that owns the value extraction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include values by source type.
     */
    public function scopeBySourceType(Builder $query, string $sourceType): Builder
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope a query to only include high confidence values.
     */
    public function scopeHighConfidence(Builder $query, int $threshold = 70): Builder
    {
        return $query->where('confidence_score', '>=', $threshold);
    }
}
