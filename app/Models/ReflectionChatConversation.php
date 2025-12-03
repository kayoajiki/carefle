<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReflectionChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'conversation_history',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'conversation_history' => 'array',
        ];
    }

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the diaries associated with this conversation.
     */
    public function diaries(): HasMany
    {
        return $this->hasMany(Diary::class, 'chat_conversation_id');
    }
}

