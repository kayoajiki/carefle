<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\MappingProgressService;
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

        // セクション定義
        $sections = ['past', 'current', 'future'];
        
        // 各セクションの進捗を取得
        $sectionProgresses = [];
        foreach ($sections as $section) {
            $sectionProgresses[$section] = $this->progressService->getSectionProgress($userId, $section);
        }

        // 全体の進捗率を計算
        $totalItems = 8; // past_diagnosis, past_diaries, life_history, current_diagnosis, current_diaries, strengths_report, wcm_sheet, milestones
        $completedItems = count($progress->completed_items ?? []);
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        return view('livewire.mapping-progress-bar', [
            'progress' => $progress,
            'isUnlocked' => $isUnlocked,
            'sectionProgresses' => $sectionProgresses,
            'progressPercentage' => $progressPercentage,
            'nextItem' => $nextItem,
        ]);
    }
}
