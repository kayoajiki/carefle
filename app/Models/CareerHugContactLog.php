<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerHugContactLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_hug_id',
        'contact_date',
        'contact_type',
        'theme',
        'decided_matters',
        'next_action',
    ];

    protected $casts = [
        'contact_date' => 'date',
    ];

    public function careerHug(): BelongsTo
    {
        return $this->belongsTo(CareerHug::class);
    }
}
