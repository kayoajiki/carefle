<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReflectionArchive extends Component
{
    public $search = '';
    public $filterType = 'all';
    public $filterDateFrom = null;
    public $filterDateTo = null;
    public $selectedDiaryId = null;

    public function mount()
    {
        $this->filterDateTo = Carbon::now()->format('Y-m-d');
        $this->filterDateFrom = Carbon::now()->subMonth()->format('Y-m-d');
    }

    public function render()
    {
        $userId = Auth::id();
        
        $query = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '');

        // 検索
        if (!empty($this->search)) {
            $query->where('content', 'like', '%' . $this->search . '%');
        }

        // タイプフィルター
        if ($this->filterType !== 'all') {
            $query->where('reflection_type', $this->filterType);
        }

        // 日付フィルター
        if ($this->filterDateFrom) {
            $query->whereDate('date', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->whereDate('date', '<=', $this->filterDateTo);
        }

        $diaries = $query->orderByDesc('date')->paginate(20);

        return view('livewire.reflection-archive', [
            'diaries' => $diaries,
        ]);
    }

    public function selectDiary($diaryId)
    {
        $this->selectedDiaryId = $diaryId;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterType = 'all';
        $this->filterDateFrom = Carbon::now()->subMonth()->format('Y-m-d');
        $this->filterDateTo = Carbon::now()->format('Y-m-d');
    }
}



