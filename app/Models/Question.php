<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'question_id',
        'type',
        'pillar',
        'weight',
        'text',
        'helper',
        'options',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'order' => 'integer',
        'weight' => 'integer',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(DiagnosisAnswer::class);
    }
}
