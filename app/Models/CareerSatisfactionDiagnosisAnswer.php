<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerSatisfactionDiagnosisAnswer extends Model
{
    protected $fillable = [
        'career_satisfaction_diagnosis_id',
        'question_id',
        'answer_value',
        'comment',
    ];

    protected $casts = [
        'answer_value' => 'integer',
    ];

    public function careerSatisfactionDiagnosis(): BelongsTo
    {
        return $this->belongsTo(CareerSatisfactionDiagnosis::class, 'career_satisfaction_diagnosis_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

