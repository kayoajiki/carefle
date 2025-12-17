<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MappingProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_section',
        'completed_items',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_items' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the mapping progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if an item is completed.
     */
    public function isItemCompleted(string $item): bool
    {
        $completedItems = $this->completed_items ?? [];
        return in_array($item, $completedItems, true);
    }

    /**
     * Get the current section.
     */
    public function getCurrentSection(): ?string
    {
        return $this->current_section;
    }

    /**
     * Mark an item as completed.
     */
    public function markItemCompleted(string $item): void
    {
        $completedItems = $this->completed_items ?? [];
        if (!in_array($item, $completedItems, true)) {
            $completedItems[] = $item;
            $this->completed_items = $completedItems;
            $this->save();
        }
    }

    /**
     * Check if a section is complete.
     */
    public function isSectionComplete(string $section): bool
    {
        $sectionItems = $this->getSectionItems($section);
        $completedItems = $this->completed_items ?? [];
        
        foreach ($sectionItems as $item) {
            if (!in_array($item, $completedItems, true)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if mapping is complete.
     */
    public function isMappingComplete(): bool
    {
        $sections = ['past', 'current', 'future'];
        
        foreach ($sections as $section) {
            if (!$this->isSectionComplete($section)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get items for a section.
     */
    protected function getSectionItems(string $section): array
    {
        $items = [
            'past' => ['past_diagnosis', 'past_diaries', 'life_history'],
            'current' => ['current_diagnosis', 'current_diaries', 'strengths_report'],
            'future' => ['wcm_sheet', 'milestones'],
        ];

        return $items[$section] ?? [];
    }
}
