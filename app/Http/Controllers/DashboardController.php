<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Diagnosis;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\Diary;
use App\Models\PersonalityAssessment;
use App\Models\CareerMilestone;
use App\Models\ReflectionChatConversation;
use App\Services\OnboardingProgressService;

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

        // 7日間記録の進捗を計算（オンボーディング用）
        $progressService = app(OnboardingProgressService::class);
        $diary7DaysProgress = $this->calculateDiary7DaysProgress($user->id, $progressService);

        // 過去7日間の記録状況を取得（カレンダーミニマップ用）
        $diary7DaysCalendar = $this->getDiary7DaysCalendar($user->id);

        // マイルストーン進捗
        $activeMilestones = CareerMilestone::where('user_id', $user->id)
            ->whereIn('status', ['planned', 'in_progress'])
            ->with(['actionItems'])
            ->orderBy('target_date')
            ->limit(5)
            ->get();

        $milestoneProgress = [];
        foreach ($activeMilestones as $milestone) {
            $totalActions = $milestone->actionItems()->count();
            $completedActions = $milestone->actionItems()->where('status', 'completed')->count();
            $completionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 1) : 0;
            
            $milestoneProgress[] = [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'completion_rate' => $completionRate,
                'total_actions' => $totalActions,
                'completed_actions' => $completedActions,
                'target_date' => $milestone->target_date,
            ];
        }

        // AI伴走の履歴（最近の会話）
        $recentConversations = ReflectionChatConversation::where('user_id', $user->id)
            ->with(['diary'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'latestDiagnosis' => $latestDiagnosis,
            'draftDiagnosis' => $draftDiagnosis,
            'hasLifeHistory' => $hasLifeHistory,
            'lifeEventCount' => $lifeEventCount,
            'latestWcmSheet' => $latestWcmSheet,
            'latestAssessment' => $latestAssessment,
            'reflectionStreak' => $reflectionStreak,
            'monthlyReflectionCount' => $monthlyReflectionCount,
            'weeklyReflectionCount' => $weeklyReflectionCount,
            'diary7DaysProgress' => $diary7DaysProgress,
            'diary7DaysCalendar' => $diary7DaysCalendar,
            'milestoneProgress' => $milestoneProgress,
            'recentConversations' => $recentConversations,
        ]);
    }

    /**
     * 過去7日間の記録状況を取得（カレンダーミニマップ用）
     */
    private function getDiary7DaysCalendar(int $userId): array
    {
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $today = now()->endOfDay();
        
        $diaryDates = Diary::where('user_id', $userId)
            ->whereBetween('date', [$sevenDaysAgo, $today])
            ->get()
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->toArray();

        $calendar = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $calendar[] = [
                'date' => $dateKey,
                'day' => $date->format('j'),
                'dayOfWeek' => $date->format('D'),
                'hasDiary' => in_array($dateKey, $diaryDates),
            ];
        }

        return $calendar;
    }

    /**
     * 7日間記録の進捗を計算
     */
    private function calculateDiary7DaysProgress(int $userId, OnboardingProgressService $progressService): array
    {
        // オンボーディングが完了している場合は進捗を表示しない
        if ($progressService->isOnboardingComplete($userId)) {
            return [
                'show' => false,
                'current' => 0,
                'target' => 7,
                'remaining' => 0,
                'percentage' => 100,
            ];
        }

        // 初回日記が記録されているかチェック
        if (!$progressService->checkStepCompletion($userId, 'diary_first')) {
            return [
                'show' => false,
                'current' => 0,
                'target' => 7,
                'remaining' => 7,
                'percentage' => 0,
            ];
        }

        // 7日間記録が既に完了しているかチェック
        if ($progressService->checkStepCompletion($userId, 'diary_7days')) {
            return [
                'show' => false,
                'current' => 7,
                'target' => 7,
                'remaining' => 0,
                'percentage' => 100,
            ];
        }

        // 過去7日間の記録日数を計算
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $today = now()->endOfDay();
        
        $diaryDates = Diary::where('user_id', $userId)
            ->whereBetween('date', [$sevenDaysAgo, $today])
            ->get()
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->count();

        $remaining = max(0, 7 - $diaryDates);
        $percentage = round(($diaryDates / 7) * 100);

        return [
            'show' => true,
            'current' => $diaryDates,
            'target' => 7,
            'remaining' => $remaining,
            'percentage' => $percentage,
        ];
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

