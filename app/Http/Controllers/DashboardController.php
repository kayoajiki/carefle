<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Diagnosis;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\Diary;
use App\Models\PersonalityAssessment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 診断の状態を取得
        $latestDiagnosis = Diagnosis::where('user_id', $user->id)
            ->where('is_completed', true)
            ->latest()
            ->first();
        
        $draftDiagnosis = Diagnosis::where('user_id', $user->id)
            ->where('is_draft', true)
            ->where('is_completed', false)
            ->latest()
            ->first();

        // 人生史のイベント数を取得
        $lifeEventCount = LifeEvent::where('user_id', $user->id)->count();
        $hasLifeHistory = $lifeEventCount > 0;

        // WCMシートの最新バージョンを取得
        $latestWcmSheet = WcmSheet::where('user_id', $user->id)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        $latestAssessment = PersonalityAssessment::where('user_id', $user->id)
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->first();

        // 内省ストリーク（連続記録日数）を計算
        $reflectionStreak = $this->calculateReflectionStreak($user->id);
        
        // 今月の内省回数
        $monthlyReflectionCount = Diary::where('user_id', $user->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();

        // 今週の内省回数
        $weeklyReflectionCount = Diary::where('user_id', $user->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return view('dashboard', [
            'latestDiagnosis' => $latestDiagnosis,
            'draftDiagnosis' => $draftDiagnosis,
            'hasLifeHistory' => $hasLifeHistory,
            'lifeEventCount' => $lifeEventCount,
            'latestWcmSheet' => $latestWcmSheet,
            'latestAssessment' => $latestAssessment,
            'reflectionStreak' => $reflectionStreak,
            'monthlyReflectionCount' => $monthlyReflectionCount,
            'weeklyReflectionCount' => $weeklyReflectionCount,
        ]);
    }

    /**
     * 内省ストリーク（連続記録日数）を計算
     */
    private function calculateReflectionStreak(int $userId): int
    {
        $diaries = Diary::where('user_id', $userId)
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
}

