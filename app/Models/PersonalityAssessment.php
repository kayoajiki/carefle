<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalityAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assessment_type',
        'assessment_name',
        'result_data',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'result_data' => 'array',
            'completed_at' => 'date',
        ];
    }

    /**
     * Get the user that owns the assessment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted result for display.
     */
    public function getFormattedResultAttribute(): array
    {
        $data = $this->result_data ?? [];
        
        switch ($this->assessment_type) {
            case 'mbti':
                return [
                    'type' => $data['type'] ?? null,
                    'dimensions' => $data['dimensions'] ?? [],
                    'percentage' => $data['percentage'] ?? [],
                ];
            case 'strengthsfinder':
                return [
                    'top5' => $data['top5'] ?? [],
                    'all34' => $data['all34'] ?? [],
                ];
            case 'enneagram':
                return [
                    'type' => $data['type'] ?? null,
                    'wing' => $data['wing'] ?? null,
                    'tritype' => $data['tritype'] ?? null,
                    'instinctual_variant' => $data['instinctual_variant'] ?? null,
                ];
            case 'big5':
                return [
                    'openness' => $data['openness'] ?? 0,
                    'conscientiousness' => $data['conscientiousness'] ?? 0,
                    'extraversion' => $data['extraversion'] ?? 0,
                    'agreeableness' => $data['agreeableness'] ?? 0,
                    'neuroticism' => $data['neuroticism'] ?? 0,
                ];
            default:
                return $data;
        }
    }
}
