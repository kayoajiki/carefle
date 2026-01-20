<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerHugWeapon extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_hug_id',
        'weapon',
    ];

    public function careerHug(): BelongsTo
    {
        return $this->belongsTo(CareerHug::class);
    }
}
