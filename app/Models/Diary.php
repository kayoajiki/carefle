<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'motivation',
        'content',
        'photo',
        'reflection_type',
        'linked_milestone_id',
        'chat_conversation_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'motivation' => 'integer',
        ];
    }

    /**
     * Get the user that owns the diary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /**
     * Get the linked milestone.
     */
    public function linkedMilestone(): BelongsTo
    {
        return $this->belongsTo(CareerMilestone::class, 'linked_milestone_id');
    }

    /**
     * Get the chat conversation.
     */
    public function chatConversation(): BelongsTo
    {
        return $this->belongsTo(ReflectionChatConversation::class, 'chat_conversation_id');
    }
}
