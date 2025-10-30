<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'title',
        'description',
        'motivation',
        'timeline_color',
        'timeline_label',
    ];

    /**
     * Get the user that owns the life event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
