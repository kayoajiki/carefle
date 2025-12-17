<?php

namespace App\Services;

use App\Models\MappingProgress;
use App\Models\OnboardingProgress;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use Illuminate\Support\Facades\Log;

class MappingProgressService
{
    /**
     * Check if mapping is unlocked (onboarding complete).
     */
    public function isMappingUnlocked(int $userId): bool
    {
        $onboardingService = app(OnboardingProgressService::class);
        return $onboardingService->isOnboardingComplete($userId);
    }

    /**
     * Check if an item is completed for a user.
     */
    public function checkItemCompletion(int $userId, string $item): bool
    {
        $progress = $this->getOrCreateProgress($userId);
        
        // 既に完了済みの場合はtrue
        if ($progress->isItemCompleted($item)) {
            return true;
        }

        // データベースから自動判定
        return $this->checkItemCompletionFromDatabase($userId, $item);
    }

    /**
     * Check item completion from database.
     */
    protected function checkItemCompletionFromDatabase(int $userId, string $item): bool
    {
        switch ($item) {
            case 'past_diagnosis':
                // 診断が2件以上かつ、最新の診断から30日以上経過した診断が存在
                $diagnoses = Diagnosis::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($diagnoses->count() < 2) {
                    return false;
                }
                
                $latestDiagnosis = $diagnoses->first();
                $thirtyDaysAgo = now()->subDays(30);
                
                // 30日以上前の診断が存在するか
                return $diagnoses->contains(function ($diagnosis) use ($thirtyDaysAgo) {
                    return $diagnosis->created_at->lt($thirtyDaysAgo);
                });

            case 'past_diaries':
                // 30日以上前の日記が3件以上
                $thirtyDaysAgo = now()->subDays(30);
                $pastDiaries = Diary::where('user_id', $userId)
                    ->where('date', '<', $thirtyDaysAgo)
                    ->whereNotNull('content')
                    ->where('content', '!=', '')
                    ->count();
                
                return $pastDiaries >= 3;

            case 'life_history':
                // 人生史データが存在
                return LifeEvent::where('user_id', $userId)->exists();

            case 'current_diagnosis':
                // 最新の診断が完了
                $latestDiagnosis = Diagnosis::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                return $latestDiagnosis && $latestDiagnosis->is_completed;

            case 'current_diaries':
                // OnboardingProgressでdiary_7daysが完了
                $onboardingService = app(OnboardingProgressService::class);
                return $onboardingService->checkStepCompletion($userId, 'diary_7days');

            case 'strengths_report':
                // OnboardingProgressでmanual_generatedが完了
                $onboardingService = app(OnboardingProgressService::class);
                return $onboardingService->checkStepCompletion($userId, 'manual_generated');

            case 'wcm_sheet':
                // WCMシートが存在
                return WcmSheet::where('user_id', $userId)
                    ->where('is_draft', false)
                    ->exists();

            case 'milestones':
                // マイルストーンが3件以上
                return CareerMilestone::where('user_id', $userId)->count() >= 3;

            default:
                return false;
        }
    }

    /**
     * Get the next incomplete item for a user.
     */
    public function getNextItem(int $userId): ?string
    {
        $allItems = [
            'past_diagnosis',
            'past_diaries',
            'life_history',
            'current_diagnosis',
            'current_diaries',
            'strengths_report',
            'wcm_sheet',
            'milestones',
        ];
        
        foreach ($allItems as $item) {
            if (!$this->checkItemCompletion($userId, $item)) {
                return $item;
            }
        }
        
        return null; // All items completed
    }

    /**
     * Update progress for a user (manual).
     */
    public function updateProgress(int $userId, string $item): void
    {
        $progress = $this->getOrCreateProgress($userId);
        
        // Mark item as completed
        $progress->markItemCompleted($item);
        
        // Update current section
        $nextItem = $this->getNextItem($userId);
        if ($nextItem) {
            $progress->current_section = $this->getSectionForItem($nextItem);
        }
        
        // Set started_at if not set
        if (!$progress->started_at) {
            $progress->started_at = now();
        }
        
        // Set completed_at if all items are done
        if ($progress->isMappingComplete() && !$progress->completed_at) {
            $progress->completed_at = now();
        }
        
        $progress->save();
    }

    /**
     * Auto-update progress from database.
     */
    public function autoUpdateProgress(int $userId): void
    {
        $allItems = [
            'past_diagnosis',
            'past_diaries',
            'life_history',
            'current_diagnosis',
            'current_diaries',
            'strengths_report',
            'wcm_sheet',
            'milestones',
        ];
        
        $progress = $this->getOrCreateProgress($userId);
        $completedItems = $progress->completed_items ?? [];
        
        foreach ($allItems as $item) {
            // データベースから判定
            if ($this->checkItemCompletionFromDatabase($userId, $item)) {
                if (!in_array($item, $completedItems, true)) {
                    $progress->markItemCompleted($item);
                }
            }
        }
        
        // Update current section
        $nextItem = $this->getNextItem($userId);
        if ($nextItem) {
            $progress->current_section = $this->getSectionForItem($nextItem);
        } else {
            $progress->current_section = null;
        }
        
        // Set started_at if not set
        if (!$progress->started_at) {
            $progress->started_at = now();
        }
        
        // Set completed_at if all items are done
        if ($progress->isMappingComplete() && !$progress->completed_at) {
            $progress->completed_at = now();
        }
        
        $progress->save();
    }

    /**
     * Get section progress for a user.
     */
    public function getSectionProgress(int $userId, string $section): array
    {
        $progress = $this->getOrCreateProgress($userId);
        $sectionItems = $this->getSectionItems($section);
        $completedItems = $progress->completed_items ?? [];
        
        $items = [];
        foreach ($sectionItems as $item) {
            $isCompleted = in_array($item, $completedItems, true);
            $items[] = [
                'key' => $item,
                'label' => $this->getItemLabel($item),
                'completed' => $isCompleted,
                'canComplete' => $this->checkItemCompletionFromDatabase($userId, $item),
            ];
        }
        
        $completedCount = count(array_filter($items, fn($item) => $item['completed']));
        $totalCount = count($items);
        
        return [
            'section' => $section,
            'sectionLabel' => $this->getSectionLabel($section),
            'items' => $items,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'progressPercentage' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0,
            'isComplete' => $progress->isSectionComplete($section),
        ];
    }

    /**
     * Get or create mapping progress for a user.
     */
    public function getOrCreateProgress(int $userId): MappingProgress
    {
        $progress = MappingProgress::where('user_id', $userId)->first();
        
        if (!$progress) {
            $progress = MappingProgress::create([
                'user_id' => $userId,
                'current_section' => 'past',
                'completed_items' => [],
                'started_at' => now(),
            ]);
        }
        
        return $progress;
    }

    /**
     * Get section for an item.
     */
    protected function getSectionForItem(string $item): string
    {
        $pastItems = ['past_diagnosis', 'past_diaries', 'life_history'];
        $currentItems = ['current_diagnosis', 'current_diaries', 'strengths_report'];
        $futureItems = ['wcm_sheet', 'milestones'];
        
        if (in_array($item, $pastItems, true)) {
            return 'past';
        } elseif (in_array($item, $currentItems, true)) {
            return 'current';
        } elseif (in_array($item, $futureItems, true)) {
            return 'future';
        }
        
        return 'past';
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

    /**
     * Get label for an item.
     */
    protected function getItemLabel(string $item): string
    {
        $labels = [
            'past_diagnosis' => '過去の診断',
            'past_diaries' => '過去の日記',
            'life_history' => '人生史',
            'current_diagnosis' => '最新の診断',
            'current_diaries' => '最近の日記',
            'strengths_report' => '持ち味レポ',
            'wcm_sheet' => 'WCMシート',
            'milestones' => 'マイルストーン',
        ];

        return $labels[$item] ?? $item;
    }

    /**
     * Get label for a section.
     */
    protected function getSectionLabel(string $section): string
    {
        $labels = [
            'past' => '過去',
            'current' => '現在',
            'future' => '未来',
        ];

        return $labels[$section] ?? $section;
    }
}

