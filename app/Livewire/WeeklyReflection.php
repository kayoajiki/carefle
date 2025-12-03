<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WeeklyReflectionService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyReflection extends Component
{
    public $reflectionType = 'weekly'; // 'weekly' or 'monthly'
    public $summary = null;
    public $isLoading = false;
    public $error = null;
    public $selectedWeekStart = null;
    public $selectedMonthStart = null;

    protected WeeklyReflectionService $reflectionService;

    public function boot()
    {
        $this->reflectionService = app(WeeklyReflectionService::class);
    }

    public function mount($type = 'weekly')
    {
        $this->reflectionType = $type;
        
        if ($type === 'weekly') {
            $this->selectedWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        } else {
            $this->selectedMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
    }

    public function generateSummary()
    {
        $this->isLoading = true;
        $this->error = null;
        $this->summary = null;

        try {
            if ($this->reflectionType === 'weekly') {
                $weekStart = $this->selectedWeekStart 
                    ? Carbon::parse($this->selectedWeekStart)
                    : Carbon::now()->startOfWeek();
                $this->summary = $this->reflectionService->generateWeeklySummary($weekStart);
            } else {
                $monthStart = $this->selectedMonthStart 
                    ? Carbon::parse($this->selectedMonthStart)
                    : Carbon::now()->startOfMonth();
                $this->summary = $this->reflectionService->generateMonthlySummary($monthStart);
            }

            if (!$this->summary) {
                $this->error = '振り返りの対象となる日記が見つかりませんでした。';
            }
        } catch (\Exception $e) {
            $this->error = 'エラーが発生しました。しばらく時間をおいて再度お試しください。';
        } finally {
            $this->isLoading = false;
        }
    }

    public function switchType($type)
    {
        $this->reflectionType = $type;
        $this->summary = null;
        $this->error = null;
        
        if ($type === 'weekly') {
            $this->selectedWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        } else {
            $this->selectedMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
    }

    public function render()
    {
        return view('livewire.weekly-reflection');
    }
}

