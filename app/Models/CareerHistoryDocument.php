<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class CareerHistoryDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'file_path',
        'file_size',
        'uploaded_at',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the user that owns the career history document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Scope a query to only include recent documents.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('uploaded_at', 'desc');
    }
}
