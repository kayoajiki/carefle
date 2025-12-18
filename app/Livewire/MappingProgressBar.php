<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\MappingProgressService;
use App\Models\WcmSheet;
use Illuminate\Support\Facades\Auth;

class MappingProgressBar extends Component
{
    protected MappingProgressService $progressService;

    public function boot(MappingProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function render()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return view('livewire.mapping-progress-bar', [
                'progress' => null,
                'isUnlocked' => false,
            ]);
        }

        // オンボーディング完了時のみ表示
        $isUnlocked = $this->progressService->isMappingUnlocked($userId);
        
        if (!$isUnlocked) {
            return view('livewire.mapping-progress-bar', [
                'progress' => null,
                'isUnlocked' => false,
            ]);
        }

        // 進捗を自動更新
        $this->progressService->autoUpdateProgress($userId);

        $progress = $this->progressService->getOrCreateProgress($userId);
        $nextItem = $this->progressService->getNextItem($userId);

        // 全項目を取得（セクションなし）
        $allItems = [
            'life_history',
            'current_diaries',
            'strengths_report',
            'wcm_sheet',
            'milestones',
            'my_goal',
        ];
        
        $completedItems = $progress->completed_items ?? [];
        
        // 最新のWCMシートIDを取得
        $latestWcmSheet = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();
        $latestWcmSheetId = $latestWcmSheet?->id;
        
        // 各項目のステータスを取得
        $itemStatuses = [];
        foreach ($allItems as $item) {
            $isCompleted = in_array($item, $completedItems, true);
            $canComplete = $this->progressService->checkItemCompletionFromDatabase($userId, $item);
            $medalLevel = $this->progressService->getMedalLevel($userId, $item);
            $hasReviewAlert = $this->progressService->checkReviewAlert($userId, $item);
            $medalDescription = $this->progressService->getMedalLevelDescription($item);
            
            $itemStatuses[$item] = [
                'key' => $item,
                'label' => $this->progressService->getItemLabel($item),
                'completed' => $isCompleted,
                'canComplete' => $canComplete,
                'isCurrent' => $nextItem === $item,
                'route' => $this->getRouteForItem($item, $latestWcmSheetId),
                'medalLevel' => $medalLevel,
                'hasReviewAlert' => $hasReviewAlert,
                'medalDescription' => $medalDescription,
            ];
        }

        // 全体の進捗率を計算
        $totalItems = count($allItems);
        $completedCount = count(array_filter($itemStatuses, fn($item) => $item['completed']));
        $progressPercentage = $totalItems > 0 ? round(($completedCount / $totalItems) * 100) : 0;

        return view('livewire.mapping-progress-bar', [
            'progress' => $progress,
            'isUnlocked' => $isUnlocked,
            'itemStatuses' => $itemStatuses,
            'progressPercentage' => $progressPercentage,
            'nextItem' => $nextItem,
        ]);
    }
    
    /**
     * Get route for an item.
     */
    protected function getRouteForItem(string $item, ?int $wcmSheetId = null): string
    {
        // WCMシートの場合はIDが必要
        if ($item === 'wcm_sheet') {
            if ($wcmSheetId) {
                return route('wcm.sheet', ['id' => $wcmSheetId]);
            }
            // WCMシートが存在しない場合は作成ページへ
            return route('wcm.start');
        }
        
        $routes = [
            'life_history' => 'life-history.timeline',
            'current_diaries' => 'diary',
            'strengths_report' => 'onboarding.mini-manual',
            'milestones' => 'career.milestones',
            'my_goal' => 'my-goal',
        ];

        $routeName = $routes[$item] ?? 'dashboard';
        return route($routeName);
    }
}
