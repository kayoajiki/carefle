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
use App\Services\PastMemoryService;
use Carbon\Carbon;

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
        $pastRecords = $this->getPastRecords($user->id, $latestDiagnosis);

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
    private function getPastRecords(int $userId, ?Diagnosis $latestDiagnosis = null): array
    {
        $memoryService = app(PastMemoryService::class);
        $now = now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $pastItems = [];

        // 1. 同じ日付の過去の記録を優先取得
        $sameDateItems = $this->getSameDateRecords($userId, $memoryService, $latestDiagnosis);
        foreach ($sameDateItems as $item) {
            $pastItems[] = $item;
        }

        // 2. 同じ時期の記録を取得
        $samePeriodItems = $this->getSamePeriodRecords($userId, $memoryService, $latestDiagnosis);
        foreach ($samePeriodItems as $item) {
            $pastItems[] = $item;
        }

        // 3. 最近の記録（7日以上前、最大5件）を取得
        $recentItems = $this->getRecentRecords($userId, $memoryService, $sevenDaysAgo, $latestDiagnosis);
        foreach ($recentItems as $item) {
            $pastItems[] = $item;
        }

        // 持ち味レポが生成済みかチェック
        $progressService = app(OnboardingProgressService::class);
        $hasStrengthsReport = $progressService->checkStepCompletion($userId, 'manual_generated');

        // 持ち味レポをスライドアイテムに追加
        if ($hasStrengthsReport) {
            $pastItems[] = [
                'type' => 'strengths_report',
                'category' => 'recent',
                'time_ago' => '',
                'message' => 'あなたの持ち味を確認できます',
                'data' => [
                    'title' => '持ち味レポ',
                    'description' => '診断と日記から見えるあなたの持ち味を確認できます。',
                ],
            ];
        }

        // 優先順位でソート（same_date > same_period > recent）
        usort($pastItems, function ($a, $b) {
            $priority = ['same_date' => 1, 'same_period' => 2, 'recent' => 3];
            $aPriority = $priority[$a['category'] ?? 'recent'] ?? 3;
            $bPriority = $priority[$b['category'] ?? 'recent'] ?? 3;
            return $aPriority <=> $bPriority;
        });

        // 最大10件に制限
        $pastItems = array_slice($pastItems, 0, 10);

        return [
            'past_items' => $pastItems,
            'has_past_records' => !empty($pastItems),
        ];
    }

    /**
     * 過去と現在の比較データを追加
     */
    private function addComparisonData(array $item, ?Diagnosis $latestDiagnosis, int $userId): array
    {
        if ($item['type'] === 'diagnosis' && $latestDiagnosis) {
            $pastWorkScore = $item['data']['work_score'] ?? 0;
            $pastLifeScore = $item['data']['life_score'] ?? 0;
            $currentWorkScore = $latestDiagnosis->work_score ?? 0;
            $currentLifeScore = $latestDiagnosis->life_score ?? 0;
            
            $item['comparison'] = [
                'work_score_change' => $currentWorkScore - $pastWorkScore,
                'life_score_change' => $currentLifeScore - $pastLifeScore,
                'current_work_score' => $currentWorkScore,
                'current_life_score' => $currentLifeScore,
            ];
        } elseif ($item['type'] === 'diary') {
            // 最近のモチベーション平均を取得
            $recentMotivation = Diary::where('user_id', $userId)
                ->whereNotNull('motivation')
                ->where('date', '>=', now()->subDays(7))
                ->avg('motivation');
            
            $pastMotivation = $item['data']['motivation'] ?? null;
            if ($pastMotivation !== null && $recentMotivation !== null) {
                $item['comparison'] = [
                    'motivation_change' => round($recentMotivation - $pastMotivation, 1),
                    'current_motivation' => round($recentMotivation, 0),
                ];
            }
        }
        
        return $item;
    }

    /**
     * 同じ日付の過去の記録を取得
     */
    private function getSameDateRecords(int $userId, PastMemoryService $memoryService, ?Diagnosis $latestDiagnosis = null): array
    {
        $items = [];
        $now = now();
        $currentMonth = $now->month;
        $currentDay = $now->day;

        // 1年前、2年前、3年前の同じ日付の記録を取得
        for ($yearsAgo = 1; $yearsAgo <= 3; $yearsAgo++) {
            $targetDate = $now->copy()->subYears($yearsAgo);
            
            // 日記
            $diary = Diary::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereDay('date', $currentDay)
                ->whereYear('date', $targetDate->year)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->first();

            if ($diary) {
                $timeAgo = "{$yearsAgo}年前";
                $category = 'same_date';
                $item = [
                    'type' => 'diary',
                    'category' => $category,
                    'time_ago' => $timeAgo,
                    'message' => $memoryService->generateMemoryMessage([
                        'type' => 'diary',
                        'data' => ['date' => $diary->date->format('Y年n月j日')],
                    ], $category, $timeAgo),
                    'data' => [
                        'id' => $diary->id,
                        'date' => $diary->date->format('Y年n月j日'),
                        'date_key' => $diary->date->format('Y-m-d'),
                        'content_preview' => mb_substr($diary->content, 0, 50) . '...',
                        'motivation' => $diary->motivation,
                        'photo' => $diary->photo,
                    ],
                ];
                $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
            }

            // 診断
            $diagnosis = Diagnosis::where('user_id', $userId)
                ->where('is_completed', true)
                ->whereMonth('created_at', $currentMonth)
                ->whereDay('created_at', $currentDay)
                ->whereYear('created_at', $targetDate->year)
                ->first();

            if ($diagnosis) {
                $timeAgo = "{$yearsAgo}年前";
                $category = 'same_date';
                $item = [
                    'type' => 'diagnosis',
                    'category' => $category,
                    'time_ago' => $timeAgo,
                    'message' => $memoryService->generateMemoryMessage([
                        'type' => 'diagnosis',
                        'data' => ['date' => $diagnosis->created_at->format('Y年n月j日')],
                    ], $category, $timeAgo),
                    'data' => [
                        'id' => $diagnosis->id,
                        'date' => $diagnosis->created_at->format('Y年n月j日'),
                        'work_score' => $diagnosis->work_score,
                        'life_score' => $diagnosis->life_score,
                    ],
                ];
                $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
            }
        }

        return $items;
    }

    /**
     * 同じ時期の記録を取得
     */
    private function getSamePeriodRecords(int $userId, PastMemoryService $memoryService, ?Diagnosis $latestDiagnosis = null): array
    {
        $items = [];
        $now = now();
        $currentMonth = $now->month;

        // 1ヶ月前、3ヶ月前、6ヶ月前、1年前の同じ月の記録を取得
        $periods = [
            ['months' => 1, 'label' => '1ヶ月前'],
            ['months' => 3, 'label' => '3ヶ月前'],
            ['months' => 6, 'label' => '6ヶ月前'],
            ['months' => 12, 'label' => '1年前'],
        ];

        foreach ($periods as $period) {
            $targetDate = $now->copy()->subMonths($period['months']);
            
            // 日記（同じ月、最大1件）
            $diary = Diary::where('user_id', $userId)
                ->whereMonth('date', $targetDate->month)
                ->whereYear('date', $targetDate->year)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('date', 'desc')
                ->first();

            if ($diary) {
                $timeAgo = $period['label'];
                $category = 'same_period';
                $item = [
                    'type' => 'diary',
                    'category' => $category,
                    'time_ago' => $timeAgo,
                    'message' => $memoryService->generateMemoryMessage([
                        'type' => 'diary',
                        'data' => ['date' => $diary->date->format('Y年n月j日')],
                    ], $category, $timeAgo),
                    'data' => [
                        'id' => $diary->id,
                        'date' => $diary->date->format('Y年n月j日'),
                        'date_key' => $diary->date->format('Y-m-d'),
                        'content_preview' => mb_substr($diary->content, 0, 50) . '...',
                        'motivation' => $diary->motivation,
                        'photo' => $diary->photo,
                    ],
                ];
                $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
            }

            // 診断（同じ月、最大1件）
            $diagnosis = Diagnosis::where('user_id', $userId)
                ->where('is_completed', true)
                ->whereMonth('created_at', $targetDate->month)
                ->whereYear('created_at', $targetDate->year)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($diagnosis) {
                $timeAgo = $period['label'];
                $category = 'same_period';
                $item = [
                    'type' => 'diagnosis',
                    'category' => $category,
                    'time_ago' => $timeAgo,
                    'message' => $memoryService->generateMemoryMessage([
                        'type' => 'diagnosis',
                        'data' => ['date' => $diagnosis->created_at->format('Y年n月j日')],
                    ], $category, $timeAgo),
                    'data' => [
                        'id' => $diagnosis->id,
                        'date' => $diagnosis->created_at->format('Y年n月j日'),
                        'work_score' => $diagnosis->work_score,
                        'life_score' => $diagnosis->life_score,
                    ],
                ];
                $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
            }
        }

        return $items;
    }

    /**
     * 最近の記録を取得
     */
    private function getRecentRecords(int $userId, PastMemoryService $memoryService, Carbon $sevenDaysAgo, ?Diagnosis $latestDiagnosis = null): array
    {
        $items = [];

        // 過去の日記（7日以上前、最大3件）
        $pastDiaries = Diary::where('user_id', $userId)
            ->where('date', '<', $sevenDaysAgo)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date', 'desc')
            ->limit(3)
            ->get();

        foreach ($pastDiaries as $diary) {
            $timeAgo = $memoryService->calculateTimeAgo($diary->date);
            $category = 'recent';
            $item = [
                'type' => 'diary',
                'category' => $category,
                'time_ago' => $timeAgo,
                'message' => $memoryService->generateMemoryMessage([
                    'type' => 'diary',
                    'data' => ['date' => $diary->date->format('Y年n月j日')],
                ], $category, $timeAgo),
                'data' => [
                    'id' => $diary->id,
                    'date' => $diary->date->format('Y年n月j日'),
                    'date_key' => $diary->date->format('Y-m-d'),
                    'content_preview' => mb_substr($diary->content, 0, 50) . '...',
                    'motivation' => $diary->motivation,
                    'photo' => $diary->photo,
                ],
            ];
            $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
        }

        // 過去の診断結果（7日以上前、最大2件）
        $pastDiagnoses = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('created_at', '<', $sevenDaysAgo)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        foreach ($pastDiagnoses as $diagnosis) {
            $timeAgo = $memoryService->calculateTimeAgo($diagnosis->created_at);
            $category = 'recent';
            $item = [
                'type' => 'diagnosis',
                'category' => $category,
                'time_ago' => $timeAgo,
                'message' => $memoryService->generateMemoryMessage([
                    'type' => 'diagnosis',
                    'data' => ['date' => $diagnosis->created_at->format('Y年n月j日')],
                ], $category, $timeAgo),
                'data' => [
                    'id' => $diagnosis->id,
                    'date' => $diagnosis->created_at->format('Y年n月j日'),
                    'work_score' => $diagnosis->work_score,
                    'life_score' => $diagnosis->life_score,
                ],
            ];
            $items[] = $this->addComparisonData($item, $latestDiagnosis, $userId);
        }

        return $items;
    }
}

