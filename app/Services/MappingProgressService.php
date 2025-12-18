<?php

namespace App\Services;

use App\Models\MappingProgress;
use App\Models\OnboardingProgress;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Models\MilestoneActionItem;
use App\Models\StrengthsReport;
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
                // 持ち味レポの更新回数を取得
                $updateCount = $this->getStrengthsReportUpdateCount($userId);
                if ($updateCount >= 5) return 'platinum';
                if ($updateCount >= 3) return 'gold';
                if ($updateCount >= 1) return 'silver';
                return 'bronze'; // 生成完了

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
                // 全ての行動アイテムが完了済みのマイルストーンのみカウント
                $count = $this->getCompletedMilestoneCount($userId);
                if ($count >= 10) return 'platinum';
                if ($count >= 5) return 'gold';
                if ($count >= 3) return 'silver';
                if ($count >= 1) return 'bronze';
                return 'none';

            case 'my_goal':
                $user = \App\Models\User::find($userId);
                if (!$user || empty($user->goal_image)) {
                    return 'none';
                }
                // マイゴールの更新回数を取得
                $updateCount = $this->getMyGoalUpdateCount($userId);
                if ($updateCount >= 3) return 'platinum';
                if ($updateCount >= 2) return 'gold';
                if ($updateCount >= 1) return 'silver';
                return 'bronze'; // 設定完了

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
     * Returns true if last review was more than the specified period ago.
     */
    public function checkReviewAlert(int $userId, string $item): bool
    {
        // 各項目ごとのアラート期間を取得
        $alertPeriod = $this->getAlertPeriod($item);
        
        // 持ち味レポの特別処理：更新可能になったらアラート表示
        if ($item === 'strengths_report') {
            if (StrengthsReport::canUpdate($userId)) {
                return true;
            }
        }
        
        $progress = $this->getOrCreateProgress($userId);
        $lastReviewed = $progress->last_reviewed_at ?? [];
        $itemLastReviewed = $lastReviewed[$item] ?? null;

        if (!$itemLastReviewed) {
            // 初回はアラートなし
            // ただし、項目が完了している場合は、完了日時を基準にする
            if ($this->checkItemCompletion($userId, $item)) {
                // 完了日時を取得（各項目の最終更新日時）
                $completionDate = $this->getItemCompletionDate($userId, $item);
                if ($completionDate) {
                    $thresholdDate = now()->sub($alertPeriod);
                    return \Carbon\Carbon::parse($completionDate)->lt($thresholdDate);
                }
            }
            return false;
        }

        $thresholdDate = now()->sub($alertPeriod);
        return \Carbon\Carbon::parse($itemLastReviewed)->lt($thresholdDate);
    }
    
    /**
     * Get alert period for an item.
     */
    private function getAlertPeriod(string $item): \DateInterval
    {
        switch ($item) {
            case 'life_history':
            case 'wcm_sheet':
            case 'my_goal':
                return new \DateInterval('P180D'); // 6ヶ月（180日）
            case 'current_diaries':
                return new \DateInterval('P7D'); // 7日
            case 'strengths_report':
            case 'milestones':
                return new \DateInterval('P30D'); // 1ヶ月
            default:
                return new \DateInterval('P180D'); // デフォルトは6ヶ月
        }
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
                // 最新の持ち味レポの生成日時を取得
                $latestReport = StrengthsReport::getLatestForUser($userId);
                return $latestReport ? $latestReport->generated_at->toDateTimeString() : null;

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
    
    /**
     * Get medal level description for an item.
     */
    public function getMedalLevelDescription(string $item): array
    {
        $descriptions = [
            'life_history' => [
                'bronze' => '1件以上',
                'silver' => '5件以上',
                'gold' => '10件以上',
                'platinum' => '20件以上',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示',
            ],
            'current_diaries' => [
                'bronze' => '7日間記録完了（オンボーディング完了）',
                'silver' => '30日間記録完了',
                'gold' => '90日間記録完了',
                'platinum' => '180日間記録完了',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示',
            ],
            'strengths_report' => [
                'bronze' => '持ち味レポ生成完了',
                'silver' => '持ち味レポ更新1回以上（更新は１ヶ月に一回）',
                'gold' => '持ち味レポ更新3回以上',
                'platinum' => '持ち味レポ更新5回以上',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示（新しく更新できるようになったらアラートが表示される）',
            ],
            'wcm_sheet' => [
                'bronze' => 'WCMシート1件作成',
                'silver' => 'WCMシート3件作成',
                'gold' => 'WCMシート5件作成',
                'platinum' => 'WCMシート10件作成',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示',
            ],
            'milestones' => [
                'bronze' => '1件設定してその全ての行動を完了済み',
                'silver' => '3件設定してその全ての行動を完了済み',
                'gold' => '5件設定してその全ての行動を完了済み',
                'platinum' => '10件設定してその全ての行動を完了済み',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示',
            ],
            'my_goal' => [
                'bronze' => 'マイゴール設定完了',
                'silver' => 'マイゴール更新1回以上',
                'gold' => 'マイゴール更新2回以上',
                'platinum' => 'マイゴール更新3回以上',
                'alert' => '最終更新から一定期間以上経過している場合にアラートを表示',
            ],
        ];
        
        return $descriptions[$item] ?? [];
    }
    
    /**
     * Get strengths report update count.
     */
    private function getStrengthsReportUpdateCount(int $userId): int
    {
        return StrengthsReport::where('user_id', $userId)->count();
    }
    
    /**
     * Get completed milestone count (all action items completed).
     */
    private function getCompletedMilestoneCount(int $userId): int
    {
        $milestones = CareerMilestone::where('user_id', $userId)->get();
        $completedCount = 0;
        
        foreach ($milestones as $milestone) {
            $actionItems = MilestoneActionItem::where('career_milestone_id', $milestone->id)
                ->where('user_id', $userId)
                ->get();
            
            // アクションアイテムが存在しない場合はスキップ
            if ($actionItems->isEmpty()) {
                continue;
            }
            
            // 全てのアクションアイテムが完了済みかチェック
            $allCompleted = $actionItems->every(function ($item) {
                return $item->status === 'completed';
            });
            
            if ($allCompleted) {
                $completedCount++;
            }
        }
        
        return $completedCount;
    }
    
    /**
     * Get my goal update count (simplified tracking).
     * 
     * Note: This is a simplified tracking method. For accurate tracking,
     * a separate table to track goal updates would be needed.
     */
    private function getMyGoalUpdateCount(int $userId): int
    {
        $user = \App\Models\User::find($userId);
        if (!$user || empty($user->goal_image)) {
            return 0;
        }
        
        // 簡易的な追跡：goal_imageが設定されている場合、updated_atの変更を追跡
        // 初回設定時は1回（設定完了）、その後更新されるたびにカウント
        
        // goal_imageが設定されている = 最低1回（設定完了）
        // updated_atがcreated_atより後 = 更新があった可能性
        if ($user->updated_at && $user->created_at) {
            $hasUpdate = $user->updated_at->gt($user->created_at->copy()->addMinutes(5)); // 5分以上の差があれば更新とみなす
            if ($hasUpdate) {
                // 更新があった場合、更新回数を簡易的に推定
                // 実際の更新回数は正確には追跡できないため、updated_atの変更回数を簡易的に推定
                // ここでは、goal_imageが設定されてから現在までの期間を基準に簡易的に推定
                $daysSinceCreation = $user->created_at->diffInDays(now());
                // 簡易的な推定：60日ごとに1回更新と仮定（最大3回まで）
                // これにより、設定完了(1回)、1回更新(2回)、2回更新(3回)を推定
                $estimatedUpdates = min(floor($daysSinceCreation / 60) + 1, 3);
                return $estimatedUpdates;
            }
        }
        
        return 1; // 設定完了のみ
    }
}

