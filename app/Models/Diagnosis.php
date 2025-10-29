<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnosis extends Model
{
    protected $fillable = [
        'user_id',
        'work_score',
        'life_score',
        'work_pillar_scores',
        'life_pillar_scores',
        'is_completed',
        'is_draft',
    ];

    protected $casts = [
        'work_pillar_scores' => 'array',
        'life_pillar_scores' => 'array',
        'is_completed' => 'boolean',
        'is_draft' => 'boolean',
        'work_score' => 'integer',
        'life_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DiagnosisAnswer::class);
    }
}
