<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisImportanceAnswer extends Model
{
    protected $fillable = [
        'diagnosis_id', 'question_id', 'importance_value',
    ];

    protected $casts = [
        'importance_value' => 'integer',
    ];

    public function diagnosis(): BelongsTo { return $this->belongsTo(Diagnosis::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}


