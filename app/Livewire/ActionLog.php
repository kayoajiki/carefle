<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MilestoneActionItem;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActionLog extends Component
{
    public $filterPeriod = 'month'; // 'week', 'month', 'quarter', 'year', 'all'
    public $filterType = 'all'; // 'all', 'completed', 'from_diary', 'action_items'

    public function render()
    {
        $userId = Auth::id();
        
        // 期間を決定
        $endDate = Carbon::now();
        $startDate = match($this->filterPeriod) {
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->subMonth(),
            'quarter' => $endDate->copy()->subQuarter(),
            'year' => $endDate->copy()->subYear(),
            'all' => null,
            default => $endDate->copy()->subMonth(),
        };

        // アクションアイテムの完了
        $completedActions = MilestoneActionItem::where('user_id', $userId)
            ->where('status', 'completed')
            ->when($startDate, function($query) use ($startDate, $endDate) {
                return $query->whereBetween('completed_at', [$startDate, $endDate]);
            })
            ->with(['milestone'])
            ->orderByDesc('completed_at')
            ->get();

        // 日記から抽出した行動（簡易実装：日記内容から行動を抽出）
        $diaryActions = [];
        $diaries = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->when($startDate, function($query) use ($startDate, $endDate) {
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->orderByDesc('date')
            ->get();

        // フィルタリング
        $actions = [];
        
        if ($this->filterType === 'all' || $this->filterType === 'action_items') {
            foreach ($completedActions as $action) {
                $actions[] = [
                    'type' => 'action_item',
                    'title' => $action->title,
                    'description' => $action->description,
                    'date' => $action->completed_at,
                    'milestone' => $action->milestone?->title,
                    'source' => 'アクションアイテム',
                ];
            }
        }

        if ($this->filterType === 'all' || $this->filterType === 'from_diary') {
            foreach ($diaries as $diary) {
                // 簡易的に日記の内容から行動を抽出（将来的にはAIで改善可能）
                if (preg_match_all('/(?:今日|昨日|今週|先週|この|その)(?:は|で|に|を|が)(.+?)(?:た|した|できた|実行|実施|取り組|行っ)/u', $diary->content, $matches)) {
                    foreach ($matches[1] as $match) {
                        $actions[] = [
                            'type' => 'diary',
                            'title' => trim($match),
                            'description' => substr($diary->content, 0, 100) . '...',
                            'date' => $diary->date,
                            'milestone' => null,
                            'source' => '日記',
                        ];
                    }
                }
            }
        }

        // 日付でソート
        usort($actions, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return view('livewire.action-log', [
            'actions' => $actions,
            'total_count' => count($actions),
            'completed_actions_count' => $completedActions->count(),
            'diary_actions_count' => count($diaryActions),
        ]);
    }
}


