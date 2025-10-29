<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisAnswer extends Model
{
    protected $fillable = [
        'diagnosis_id',
        'question_id',
        'answer_value',
        'comment',
    ];

    protected $casts = [
        'answer_value' => 'integer',
    ];

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
