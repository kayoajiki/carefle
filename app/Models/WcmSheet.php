<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcmSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'will_text', 'can_text', 'must_text', 'version',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


