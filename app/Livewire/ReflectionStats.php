<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReflectionStats extends Component
{
    public $selectedPeriod = 'month'; // 'week' or 'month'

    public function getReflectionStreakProperty(): int
    {
        $diaries = Diary::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->get()
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($diaries->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expectedDate = now()->format('Y-m-d');
        
        foreach ($diaries as $date) {
            if ($date === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }

    public function getWeeklyReflectionCountProperty(): int
    {
        return Diary::where('user_id', Auth::id())
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    public function getMonthlyReflectionCountProperty(): int
    {
        return Diary::where('user_id', Auth::id())
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();
    }

    public function getReflectionTypeDistributionProperty(): array
    {
        $startDate = $this->selectedPeriod === 'week' 
            ? now()->startOfWeek() 
            : now()->startOfMonth();

        $endDate = $this->selectedPeriod === 'week'
            ? now()->endOfWeek()
            : now()->endOfMonth();

        $diaries = Diary::where('user_id', Auth::id())
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('reflection_type')
            ->get();

        $distribution = [
            'daily' => 0,
            'yesterday' => 0,
            'weekly' => 0,
            'deep' => 0,
            'moya_moya' => 0,
        ];

        foreach ($diaries as $diary) {
            if (isset($distribution[$diary->reflection_type])) {
                $distribution[$diary->reflection_type]++;
            }
        }

        return $distribution;
    }

    public function getMotivationTrendProperty(): array
    {
        $startDate = $this->selectedPeriod === 'week'
            ? now()->startOfWeek()
            : now()->startOfMonth();

        $endDate = $this->selectedPeriod === 'week'
            ? now()->endOfWeek()
            : now()->endOfMonth();

        $diaries = Diary::where('user_id', Auth::id())
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $trend = [];
        foreach ($diaries as $diary) {
            $trend[] = [
                'date' => $diary->date->format('Y-m-d'),
                'motivation' => $diary->motivation,
            ];
        }

        return $trend;
    }

    public function render()
    {
        return view('livewire.reflection-stats', [
            'streak' => $this->reflectionStreak,
            'weeklyCount' => $this->weeklyReflectionCount,
            'monthlyCount' => $this->monthlyReflectionCount,
            'typeDistribution' => $this->reflectionTypeDistribution,
            'motivationTrend' => $this->motivationTrend,
        ]);
    }
}