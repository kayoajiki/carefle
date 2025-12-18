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

        // マイルストーン進捗（目標日が最も近く、達成率が最も低いものを1件のみ）
        $activeMilestones = CareerMilestone::where('user_id', $user->id)
            ->whereIn('status', ['planned', 'in_progress'])
            ->with(['actionItems'])
            ->orderByRaw('CASE WHEN target_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_date')
            ->get();

        $milestoneProgressData = [];
        foreach ($activeMilestones as $milestone) {
            $totalActions = $milestone->actionItems()->count();
            $completedActions = $milestone->actionItems()->where('status', 'completed')->count();
            $completionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 1) : 0;
            
            $milestoneProgressData[] = [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'completion_rate' => $completionRate,
                'total_actions' => $totalActions,
                'completed_actions' => $completedActions,
                'target_date' => $milestone->target_date,
            ];
        }

        // 目標日が最も近いものを確認
        $milestoneProgress = [];
        if (!empty($milestoneProgressData)) {
            // 目標日が近い順にソート（nullは最後）
            usort($milestoneProgressData, function ($a, $b) {
                if ($a['target_date'] === null && $b['target_date'] === null) {
                    return 0;
                }
                if ($a['target_date'] === null) {
                    return 1;
                }
                if ($b['target_date'] === null) {
                    return -1;
                }
                return strtotime($a['target_date']) <=> strtotime($b['target_date']);
            });

            $nearestMilestone = $milestoneProgressData[0];
            
            // 目標日が最も近いものが達成率100%の場合、達成率が低いものを優先
            if ($nearestMilestone['completion_rate'] >= 100) {
                // 達成率が低い順にソート（達成率100%でないものを優先）
                usort($milestoneProgressData, function ($a, $b) {
                    // 達成率100%でないものを優先
                    if ($a['completion_rate'] >= 100 && $b['completion_rate'] < 100) {
                        return 1;
                    }
                    if ($a['completion_rate'] < 100 && $b['completion_rate'] >= 100) {
                        return -1;
                    }
                    // 両方とも100%でない、または両方とも100%の場合は達成率が低い順
                    return $a['completion_rate'] <=> $b['completion_rate'];
                });
                
                // 達成率が100%でないものを探す
                $selectedMilestone = null;
                foreach ($milestoneProgressData as $milestone) {
                    if ($milestone['completion_rate'] < 100) {
                        $selectedMilestone = $milestone;
                        break;
                    }
                }
                
                // 達成率が100%でないものが見つからない場合は、達成率が最も低いものを選択
                if ($selectedMilestone === null) {
                    $selectedMilestone = $milestoneProgressData[0];
                }
                
                $milestoneProgress = [$selectedMilestone];
            } else {
                // 目標日が最も近いものが達成率100%でない場合は、それを選択
                $milestoneProgress = [$nearestMilestone];
            }
        }

        // AI伴走の履歴（最近の会話）
        $recentConversations = ReflectionChatConversation::where('user_id', $user->id)
            ->with(['diary'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        // 過去の記録へのアクセス（Phase 8.1）
        $pastRecords = $this->getPastRecords($user->id);

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
            'pastRecords' => $pastRecords,
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

    /**
     * 過去の記録を取得（Phase 8.1: 自分を思い出す機能）
     */
    private function getPastRecords(int $userId): array
    {
        $thirtyDaysAgo = now()->subDays(30);
        $pastItems = [];

        // 過去の日記（30日以上前、最大10件）
        $pastDiaries = Diary::where('user_id', $userId)
            ->where('date', '<', $thirtyDaysAgo)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($diary) {
                return [
                    'id' => $diary->id,
                    'date' => $diary->date->format('Y年n月j日'),
                    'date_key' => $diary->date->format('Y-m-d'),
                    'content_preview' => mb_substr($diary->content, 0, 50) . '...',
                    'motivation' => $diary->motivation,
                ];
            });

        // 日記をスライドアイテムに追加
        foreach ($pastDiaries as $diary) {
            $pastItems[] = [
                'type' => 'diary',
                'data' => $diary,
            ];
        }

        // 過去の診断結果（30日以上前、最大10件）
        $pastDiagnoses = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('created_at', '<', $thirtyDaysAgo)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($diagnosis) {
                return [
                    'id' => $diagnosis->id,
                    'date' => $diagnosis->created_at->format('Y年n月j日'),
                    'work_score' => $diagnosis->work_score,
                    'life_score' => $diagnosis->life_score,
                ];
            });

        // 診断をスライドアイテムに追加
        foreach ($pastDiagnoses as $diagnosis) {
            $pastItems[] = [
                'type' => 'diagnosis',
                'data' => $diagnosis,
            ];
        }

        // 持ち味レポが生成済みかチェック
        $progressService = app(OnboardingProgressService::class);
        $hasStrengthsReport = $progressService->checkStepCompletion($userId, 'manual_generated');

        // 持ち味レポをスライドアイテムに追加
        if ($hasStrengthsReport) {
            $pastItems[] = [
                'type' => 'strengths_report',
                'data' => [
                    'title' => '持ち味レポ',
                    'description' => '診断と日記から見えるあなたの持ち味を確認できます。',
                ],
            ];
        }

        // ランダムに並び替え
        shuffle($pastItems);

        return [
            'past_items' => $pastItems,
            'has_past_records' => !empty($pastItems),
        ];
    }
}

