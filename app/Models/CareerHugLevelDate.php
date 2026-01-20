<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerHugLevelDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_hug_id',
        'level',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function careerHug(): BelongsTo
    {
        return $this->belongsTo(CareerHug::class);
    }
}
