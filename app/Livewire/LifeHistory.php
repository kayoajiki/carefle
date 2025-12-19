<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LifeEvent;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class LifeHistory extends Component
{
    public $year;
    public $title;
    public $description;
    public $motivation = 50;

    public $editingId = null;

    protected $rules = [
        'year' => 'required|integer|min:1900|max:2100',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:2000',
        'motivation' => 'required|integer|min:0|max:100',
    ];

    public function render()
    {
        // ログインユーザーのデータを年順で並べる
        $events = LifeEvent::where('user_id', Auth::id())
            ->orderBy('year', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.life-history', [
            'events' => $events,
        ]);
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            // 更新モード
            $event = LifeEvent::where('user_id', Auth::id())
                ->where('id', $this->editingId)
                ->firstOrFail();

            $event->update([
                'year' => $this->year,
                'title' => $this->title,
                'description' => $this->description,
                'motivation' => $this->motivation,
            ]);
        } else {
            // 新規登録
            $event = LifeEvent::create([
                'user_id' => Auth::id(),
                'year' => $this->year,
                'title' => $this->title,
                'description' => $this->description,
                'motivation' => $this->motivation,
            ]);

            // アクティビティログに記録
            $eventCount = LifeEvent::where('user_id', Auth::id())->count();
            app(ActivityLogService::class)->logLifeEventCreated(Auth::id(), $eventCount);
        }

        // 更新時も新規登録時も見直し日時を更新
        $mappingProgressService = app(\App\Services\MappingProgressService::class);
        $mappingProgressService->markItemAsReviewed(Auth::id(), 'life_history');

        $this->resetInput();
    }

    public function edit($id)
    {
        $event = LifeEvent::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $this->editingId = $event->id;
        $this->year = $event->year;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->motivation = $event->motivation;
    }

    public function delete($id)
    {
        LifeEvent::where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        // 編集中に削除された時のリセット
        if ($this->editingId === $id) {
            $this->resetInput();
        }
    }

    public function cancelEdit()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->editingId = null;
        $this->year = null;
        $this->title = null;
        $this->description = null;
        $this->motivation = 50;
    }
}

