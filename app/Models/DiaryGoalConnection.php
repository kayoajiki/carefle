<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiaryGoalConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'diary_id',
        'connection_type',
        'connected_id',
        'connection_score',
        'connection_reason',
        'will_theme',
    ];

    protected function casts(): array
    {
        return [
            'connection_score' => 'integer',
        ];
    }

    /**
     * Get the diary that owns this connection.
     */
    public function diary(): BelongsTo
    {
        return $this->belongsTo(Diary::class);
    }

    /**
     * Get the connected milestone (if connection_type is 'milestone').
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(CareerMilestone::class, 'connected_id');
    }

    /**
     * Get the connected WCM sheet (if connection_type is 'wcm_will').
     */
    public function wcmSheet(): BelongsTo
    {
        return $this->belongsTo(WcmSheet::class, 'connected_id');
    }

    /**
     * Get the connected entity (milestone or WCM sheet).
     */
    public function connected()
    {
        return match ($this->connection_type) {
            'milestone' => $this->milestone,
            'wcm_will' => $this->wcmSheet,
            default => null,
        };
    }
}