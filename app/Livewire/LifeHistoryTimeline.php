<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LifeEvent;
use Illuminate\Support\Facades\Auth;

class LifeHistoryTimeline extends Component
{
    public function render()
    {
        // ログインユーザーのデータを年順で並べる
        $events = LifeEvent::where('user_id', Auth::id())
            ->orderBy('year', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 年ごとにグループ化
        $eventsByYear = $events->groupBy('year');

        // 全ての年を取得してソート
        $years = $events->pluck('year')->unique()->sort()->values();

        return view('livewire.life-history-timeline', [
            'events' => $events,
            'eventsByYear' => $eventsByYear,
            'years' => $years,
        ]);
    }
}
