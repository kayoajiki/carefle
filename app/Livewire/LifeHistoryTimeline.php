<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LifeEvent;
use Illuminate\Support\Facades\Auth;

class LifeHistoryTimeline extends Component
{
    public function updateColor(int $eventId, string $color): void
    {
        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return;
        }
        LifeEvent::where('user_id', Auth::id())
            ->where('id', $eventId)
            ->update(['timeline_color' => $color]);

        $this->dispatch('livewire:update');
    }

    public function updateLabel(int $eventId, string $label): void
    {
        $label = trim(mb_substr($label, 0, 32));
        LifeEvent::where('user_id', Auth::id())
            ->where('id', $eventId)
            ->update(['timeline_label' => $label === '' ? null : $label]);

        $this->dispatch('livewire:update');
    }

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
