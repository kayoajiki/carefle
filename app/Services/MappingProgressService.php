<?php

namespace App\Services;

use App\Models\MappingProgress;
use App\Models\OnboardingProgress;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Services\ActivityLogService;
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
    public function checkItemCompletionFromDatabase(int $userId, string $item): bool
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

            case 'my_goal':
                // マイゴールが設定されている
                $user = \App\Models\User::find($userId);
                return $user && !empty($user->goal_image);

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
            'life_history',
            'current_diaries',
            'strengths_report',
            'wcm_sheet',
            'milestones',
            'my_goal',
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
        
        // Check if item was already completed
        $wasAlreadyCompleted = $progress->isItemCompleted($item);
        
        // Mark item as completed
        $progress->markItemCompleted($item);
        
        // Get section for this item
        $section = $this->getSectionForItem($item);
        
        // Check if section is now complete
        $sectionItems = $this->getSectionItems($section);
        $isSectionComplete = $progress->isSectionComplete($section, $sectionItems);
        
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
        
        // Log activity for section completion (only if newly completed)
        if (!$wasAlreadyCompleted && $isSectionComplete) {
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logMappingProgressCompleted($userId, $section);
        }
    }

    /**
     * Auto-update progress from database.
     */
    public function autoUpdateProgress(int $userId): void
    {
        $allItems = [
            'life_history',
            'current_diaries',
            'strengths_report',
            'wcm_sheet',
            'milestones',
            'my_goal',
        ];
        
        $progress = $this->getOrCreateProgress($userId);
        $completedItems = $progress->completed_items ?? [];
        $newlyCompletedItems = [];
        $completedSections = [];
        
        foreach ($allItems as $item) {
            // データベースから判定
            if ($this->checkItemCompletionFromDatabase($userId, $item)) {
                if (!in_array($item, $completedItems, true)) {
                    $progress->markItemCompleted($item);
                    $newlyCompletedItems[] = $item;
                    
                    // Check if section is now complete
                    $section = $this->getSectionForItem($item);
                    $sectionItems = $this->getSectionItems($section);
                    if ($progress->isSectionComplete($section, $sectionItems) && !in_array($section, $completedSections, true)) {
                        $completedSections[] = $section;
                    }
                }
                
                // 項目が完了している場合、最終更新日時をlast_reviewed_atに設定（初回のみ）
                $lastReviewed = $progress->last_reviewed_at ?? [];
                if (!isset($lastReviewed[$item])) {
                    $completionDate = $this->getItemCompletionDate($userId, $item);
                    if ($completionDate) {
                        $lastReviewed[$item] = $completionDate;
                        $progress->last_reviewed_at = $lastReviewed;
                    }
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
        
        // Log activity for newly completed sections
        if (!empty($completedSections)) {
            $activityLogService = app(ActivityLogService::class);
            foreach ($completedSections as $section) {
                $activityLogService->logMappingProgressCompleted($userId, $section);
            }
        }
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
                'last_reviewed_at' => [],
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
    public function getItemLabel(string $item): string
    {
        $labels = [
            'life_history' => '人生史',
            'current_diaries' => '日記',
            'strengths_report' => '持ち味レポ',
            'wcm_sheet' => 'WCMシート',
            'milestones' => 'マイルストーン',
            'my_goal' => 'マイゴール',
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

    /**
     * Get medal level for an item.
     * Returns: 'none', 'bronze', 'silver', 'gold', 'platinum'
     */
    public function getMedalLevel(int $userId, string $item): string
    {
        switch ($item) {
            case 'life_history':
                $count = LifeEvent::where('user_id', $userId)->count();
                if ($count >= 20) return 'platinum';
                if ($count >= 10) return 'gold';
                if ($count >= 5) return 'silver';
                if ($count >= 1) return 'bronze';
                return 'none';

            case 'current_diaries':
                // 連続記録日数を計算
                $consecutiveDays = $this->calculateConsecutiveDiaryDays($userId);
                if ($consecutiveDays >= 180) return 'platinum';
                if ($consecutiveDays >= 90) return 'gold';
                if ($consecutiveDays >= 30) return 'silver';
                // 7日間記録完了で銅
                $onboardingService = app(OnboardingProgressService::class);
                if ($onboardingService->checkStepCompletion($userId, 'diary_7days')) {
                    return 'bronze';
                }
                return 'none';

            case 'strengths_report':
                $onboardingService = app(OnboardingProgressService::class);
                if (!$onboardingService->checkStepCompletion($userId, 'manual_generated')) {
                    return 'none';
                }
                // 持ち味レポの更新回数を取得（簡易版：OnboardingProgressのmanual_generated完了日時を基準に）
                // TODO: より正確な更新回数追跡を実装する場合は別途テーブルが必要
                // 現時点では、生成済みで銅、更新があれば銀以上とする
                // 更新回数の正確な追跡は後で実装
                return 'bronze'; // 暫定

            case 'wcm_sheet':
                $count = WcmSheet::where('user_id', $userId)
                    ->where('is_draft', false)
                    ->count();
                if ($count >= 10) return 'platinum';
                if ($count >= 5) return 'gold';
                if ($count >= 3) return 'silver';
                if ($count >= 1) return 'bronze';
                return 'none';

            case 'milestones':
                $count = CareerMilestone::where('user_id', $userId)->count();
                if ($count >= 20) return 'platinum';
                if ($count >= 10) return 'gold';
                if ($count >= 5) return 'silver';
                if ($count >= 3) return 'bronze';
                return 'none';

            case 'my_goal':
                $user = \App\Models\User::find($userId);
                if (!$user || empty($user->goal_image)) {
                    return 'none';
                }
                // マイゴールの更新回数を追跡（goal_imageの更新日時を基準に）
                // 簡易版：設定済みで銅、更新があれば銀以上とする
                // より正確な追跡には別途テーブルが必要
                return 'bronze'; // 暫定

            default:
                return 'none';
        }
    }

    /**
     * Calculate consecutive diary days.
     */
    private function calculateConsecutiveDiaryDays(int $userId): int
    {
        $diaries = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date', 'desc')
            ->get()
            ->pluck('date')
            ->unique()
            ->sortDesc()
            ->values();

        if ($diaries->isEmpty()) {
            return 0;
        }

        $consecutiveDays = 0;
        $expectedDate = now()->startOfDay();

        foreach ($diaries as $date) {
            $dateStart = $date->startOfDay();
            if ($dateStart->eq($expectedDate) || $dateStart->eq($expectedDate->copy()->subDay())) {
                $consecutiveDays++;
                $expectedDate = $dateStart->copy()->subDay();
            } else {
                break;
            }
        }

        return $consecutiveDays;
    }

    /**
     * Check if review alert should be shown for an item.
     * Returns true if last review was more than 6 months ago.
     */
    public function checkReviewAlert(int $userId, string $item): bool
    {
        $progress = $this->getOrCreateProgress($userId);
        $lastReviewed = $progress->last_reviewed_at ?? [];
        $itemLastReviewed = $lastReviewed[$item] ?? null;

        if (!$itemLastReviewed) {
            // 初回はアラートなし（6ヶ月後にアラート）
            // ただし、項目が完了している場合は、完了日時を基準にする
            if ($this->checkItemCompletion($userId, $item)) {
                // 完了日時を取得（各項目の最終更新日時）
                $completionDate = $this->getItemCompletionDate($userId, $item);
                if ($completionDate) {
                    $sixMonthsAgo = now()->subMonths(6);
                    return \Carbon\Carbon::parse($completionDate)->lt($sixMonthsAgo);
                }
            }
            return false;
        }

        $sixMonthsAgo = now()->subMonths(6);
        return \Carbon\Carbon::parse($itemLastReviewed)->lt($sixMonthsAgo);
    }

    /**
     * Get item completion/update date.
     */
    private function getItemCompletionDate(int $userId, string $item): ?string
    {
        switch ($item) {
            case 'life_history':
                $latest = LifeEvent::where('user_id', $userId)
                    ->latest('updated_at')
                    ->first();
                return $latest ? $latest->updated_at->toDateTimeString() : null;

            case 'current_diaries':
                $latest = Diary::where('user_id', $userId)
                    ->whereNotNull('content')
                    ->where('content', '!=', '')
                    ->latest('updated_at')
                    ->first();
                return $latest ? $latest->updated_at->toDateTimeString() : null;

            case 'strengths_report':
                // OnboardingProgressのmanual_generated完了日時を取得
                $onboardingProgress = OnboardingProgress::where('user_id', $userId)->first();
                if ($onboardingProgress && $onboardingProgress->isStepCompleted('manual_generated')) {
                    // completed_stepsからmanual_generatedの完了日時を取得
                    $completedSteps = $onboardingProgress->completed_steps ?? [];
                    if (isset($completedSteps['manual_generated'])) {
                        if (is_array($completedSteps['manual_generated'])) {
                            return $completedSteps['manual_generated']['completed_at'] ?? null;
                        }
                        // 配列でない場合はupdated_atを使用
                        return $onboardingProgress->updated_at->toDateTimeString();
                    }
                    // completed_stepsにmanual_generatedがない場合はupdated_atを使用
                    return $onboardingProgress->updated_at->toDateTimeString();
                }
                return null;

            case 'wcm_sheet':
                $latest = WcmSheet::where('user_id', $userId)
                    ->where('is_draft', false)
                    ->latest('updated_at')
                    ->first();
                return $latest ? $latest->updated_at->toDateTimeString() : null;

            case 'milestones':
                $latest = CareerMilestone::where('user_id', $userId)
                    ->latest('updated_at')
                    ->first();
                return $latest ? $latest->updated_at->toDateTimeString() : null;

            case 'my_goal':
                $user = \App\Models\User::find($userId);
                return $user && $user->updated_at ? $user->updated_at->toDateTimeString() : null;

            default:
                return null;
        }
    }

    /**
     * Mark item as reviewed.
     */
    public function markItemAsReviewed(int $userId, string $item): void
    {
        $progress = $this->getOrCreateProgress($userId);
        $lastReviewed = $progress->last_reviewed_at ?? [];
        $lastReviewed[$item] = now()->toDateTimeString();
        $progress->last_reviewed_at = $lastReviewed;
        $progress->save();
    }
}

