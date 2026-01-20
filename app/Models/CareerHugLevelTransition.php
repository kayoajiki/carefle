<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerHugLevelTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_hug_id',
        'from_level',
        'to_level',
        'transition_reason',
        'reason_note',
    ];

    public function careerHug(): BelongsTo
    {
        return $this->belongsTo(CareerHug::class);
    }
}
