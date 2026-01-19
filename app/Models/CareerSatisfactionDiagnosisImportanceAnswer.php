<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerSatisfactionDiagnosisImportanceAnswer extends Model
{
    protected $fillable = [
        'career_satisfaction_diagnosis_id',
        'question_id',
        'importance_value',
    ];

    protected $casts = [
        'importance_value' => 'integer',
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

