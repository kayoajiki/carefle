<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\PersonalityAssessment;
use App\Models\WcmSheet;
use App\Models\LifeEvent;
use App\Models\CareerSatisfactionDiagnosis;
use App\Models\StrengthsReport;
use App\Models\CareerMilestone;
use App\Services\OnboardingProgressService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected OnboardingProgressService $onboardingProgressService;

    public function __construct(OnboardingProgressService $onboardingProgressService)
    {
        $this->onboardingProgressService = $onboardingProgressService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by admin status
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->is_admin === '1');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Login history (latest 50)
        $loginHistory = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Activity logs (latest 100)
        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Created data (共有されたコンテンツのみ)
        $diagnoses = Diagnosis::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $diaries = Diary::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();
        
        // 共有されたコンテンツのみ取得
        $assessments = PersonalityAssessment::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $wcmSheets = WcmSheet::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // 人生史: 全体共有が有効な場合、全イベントを取得（個別のis_admin_visibleは無視）
        if ($user->life_history_is_admin_visible) {
            $lifeEvents = LifeEvent::where('user_id', $user->id)
                ->orderBy('year', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $lifeEvents = collect([]);
        }

        $careerSatisfactionDiagnoses = CareerSatisfactionDiagnosis::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $strengthsReports = StrengthsReport::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('generated_at', 'desc')
            ->get();

        $milestones = CareerMilestone::where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->orderBy('target_year', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $myGoalShared = $user->goal_is_admin_visible && $user->goal_image;

        // Onboarding progress
        $onboardingProgress = $this->onboardingProgressService->getOrCreateProgress($user->id);

        return view('admin.users.show', [
            'user' => $user,
            'loginHistory' => $loginHistory,
            'activityLogs' => $activityLogs,
            'diagnoses' => $diagnoses,
            'diaries' => $diaries,
            'assessments' => $assessments,
            'wcmSheets' => $wcmSheets,
            'lifeEvents' => $lifeEvents,
            'careerSatisfactionDiagnoses' => $careerSatisfactionDiagnoses,
            'strengthsReports' => $strengthsReports,
            'milestones' => $milestones,
            'myGoalShared' => $myGoalShared,
            'onboardingProgress' => $onboardingProgress,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['boolean'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'ユーザー情報を更新しました。');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $userId = $user->id;
        $user->delete();

        // アクティビティログに記録
        app(\App\Services\ActivityLogService::class)->logUserAccountDeleted($userId);

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザーを削除しました。');
    }

    /**
     * 管理者が共有されたコンテンツを閲覧
     */
    public function viewWcm(User $user, $id)
    {
        $sheet = WcmSheet::where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->firstOrFail();

        return view('wcm.sheet', ['id' => (int)$id]);
    }

    public function viewDiagnosis(User $user, $id)
    {
        $diagnosis = Diagnosis::with(['answers.question'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->firstOrFail();

        // DiagnosisControllerのresultメソッドと同じロジックを使用
        // ただし、user_idチェックをバイパスするため、直接ロジックを実装
        return $this->renderDiagnosisResult($diagnosis);
    }

    public function viewCareerSatisfaction(User $user, $id)
    {
        $diagnosis = CareerSatisfactionDiagnosis::with(['answers.question'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->firstOrFail();

        // CareerSatisfactionDiagnosisControllerのresultメソッドと同じロジックを使用
        return $this->renderCareerSatisfactionResult($diagnosis);
    }

    public function viewStrengthsReport(User $user, $id)
    {
        $report = StrengthsReport::where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_admin_visible', true)
            ->firstOrFail();

        $manual = [
            'user_id' => $user->id,
            'generated_at' => $report->generated_at,
            'content' => $report->content,
            'diagnosis_report' => $report->diagnosis_report,
            'diary_report' => $report->diary_report,
        ];

        return view('onboarding.mini-manual', [
            'manual' => $manual,
            'user' => $user,
            'canUpdate' => false,
            'latestReport' => $report,
            'isAdminVisible' => true,
        ]);
    }

    /**
     * 管理者が対象ユーザーの人生史を閲覧
     */
    public function viewLifeHistory(User $user)
    {
        // 全体共有が有効でない場合は表示しない
        if (!$user->life_history_is_admin_visible) {
            abort(403, 'このユーザーの人生史は共有されていません。');
        }

        $events = LifeEvent::where('user_id', $user->id)
            ->orderBy('year', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 年ごとにグループ化
        $years = $events->pluck('year')->unique()->sort()->values();
        $eventsByYear = $events->groupBy('year');

        return view('admin.users.life-history', [
            'user' => $user,
            'events' => $events,
            'years' => $years,
            'eventsByYear' => $eventsByYear,
        ]);
    }

    /**
     * 診断結果を表示（管理者用）
     */
    protected function renderDiagnosisResult(Diagnosis $diagnosis)
    {
        // DiagnosisControllerのresultメソッドと同じロジック
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];
        $lifePillarScores = $diagnosis->life_pillar_scores ?? [];

        $pillarLabels = [
            'purpose' => 'Purpose（目的）',
            'profession' => 'Profession（職業）',
            'people' => 'People（人間関係）',
            'privilege' => 'Privilege（待遇）',
            'progress' => 'Progress（成長）',
        ];

        $lifePillarLabels = [
            'family' => 'Family（家族）',
            'friends' => 'Friends（友人）',
            'leisure' => 'Leisure（余暇）',
            'sidejob' => 'Sidejob（副業）',
            'health' => 'Health（健康）',
            'finance' => 'Finance（財務）',
        ];

        $radarLabels = [];
        $radarWorkData = [];
        foreach ($pillarLabels as $key => $label) {
            if (isset($workPillarScores[$key])) {
                $radarLabels[] = $label;
                $radarWorkData[] = $workPillarScores[$key];
            } else {
                $radarLabels[] = $label;
                $radarWorkData[] = null;
            }
        }

        $lifeAvg = !empty($lifePillarScores)
            ? round(array_sum($lifePillarScores) / count($lifePillarScores))
            : $diagnosis->life_score ?? 0;

        $radarLabels[] = 'Life（ライフ）';
        $radarWorkData[] = null;

        $countAfterAdd = count($radarLabels);
        $lastWorkIndex = $countAfterAdd - 2;
        $lifeIndex = $countAfterAdd - 1;

        $lifeEdgeLeftData = array_fill(0, $countAfterAdd, null);
        $lifeEdgeLeftData[0] = $radarWorkData[0] ?? null;
        $lifeEdgeLeftData[$lifeIndex] = $lifeAvg;

        $lifeEdgeRightData = array_fill(0, $countAfterAdd, null);
        $lifeEdgeRightData[$lastWorkIndex] = $radarWorkData[$lastWorkIndex] ?? null;
        $lifeEdgeRightData[$lifeIndex] = $lifeAvg;

        $lifePointData = array_fill(0, $countAfterAdd, null);
        $lifePointData[$lifeIndex] = $lifeAvg;

        $lifeFillData = array_fill(0, $countAfterAdd, 0);
        $lifeFillData[0] = $radarWorkData[0] ?? 0;
        $lifeFillData[$lastWorkIndex] = $radarWorkData[$lastWorkIndex] ?? 0;
        $lifeFillData[$lifeIndex] = $lifeAvg;

        $answerNotes = [];
        foreach ($diagnosis->answers as $answer) {
            if ($answer->comment) {
                $answerNotes[] = [
                    'label' => $answer->question->text,
                    'comment' => $answer->comment,
                ];
            }
        }

        $importanceWork = [];
        $workQuestions = \App\Models\Question::where('type','work')->get();
        
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarWeight = 0;
            
            foreach ($qs as $q) {
                $ans = \App\Models\DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)
                    ->where('question_id', $q->id)
                    ->first();
                
                if ($ans && $q->weight) {
                    $importanceValue = (($ans->importance_value - 1) / 4) * 100;
                    $pillarScore += $importanceValue * $q->weight;
                    $pillarWeight += $q->weight;
                }
            }
            
            if ($pillarWeight > 0) {
                $importanceWork[$pillar] = round($pillarScore / $pillarWeight);
            } else {
                $importanceWork[$pillar] = null;
            }
        }

        $lifeQuestions = \App\Models\Question::where('type','life')->get();
        $totalLifeImportanceScore = 0;
        $totalLifeImportanceCount = 0;
        
        foreach ($lifeQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarCount = 0;
            
            foreach ($qs as $q) {
                $ans = \App\Models\DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)
                    ->where('question_id', $q->id)
                    ->first();
                
                if ($ans) {
                    $importanceValue = (($ans->importance_value - 1) / 4) * 100;
                    $pillarScore += $importanceValue;
                    $pillarCount++;
                }
            }
            
            if ($pillarCount > 0) {
                $pillarAvg = $pillarScore / $pillarCount;
                $totalLifeImportanceScore += $pillarAvg;
                $totalLifeImportanceCount++;
            }
        }
        
        $importanceLifeAvg = null;
        if ($totalLifeImportanceCount > 0) {
            $importanceLifeAvg = round($totalLifeImportanceScore / $totalLifeImportanceCount);
        }
        
        $importanceDataset = [];
        foreach (array_keys($pillarLabels) as $key) {
            $importanceDataset[] = $importanceWork[$key] ?? null;
        }
        $importanceDataset[] = $importanceLifeAvg;

        $importanceScore = 0;
        $totalWeight = 0;
        
        foreach ($importanceWork as $pillar => $pillarAvg) {
            if ($pillarAvg !== null) {
                $pillarWeight = \App\Models\Question::where('type', 'work')
                    ->where('pillar', $pillar)
                    ->sum('weight');
                
                if ($pillarWeight > 0) {
                    $importanceScore += $pillarAvg * $pillarWeight;
                    $totalWeight += $pillarWeight;
                }
            }
        }
        
        if ($totalWeight > 0) {
            $importanceScore = round($importanceScore / $totalWeight);
        } else {
            $importanceScore = 0;
        }
        
        $validImportanceWork = array_filter($importanceWork, fn($v) => $v !== null);
        $minPillarScore = !empty($validImportanceWork) ? min($validImportanceWork) : 100;
        if ($minPillarScore < 100) {
            $importanceScore = round(($importanceScore + $minPillarScore) / 2);
        }

        $hasImportance = $totalWeight > 0 || \App\Models\DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)->exists();

        if (!$diagnosis->is_completed) {
            $workPillarScores = [];
            $importanceWork = [];
            $workScore = 0;
            $importanceScore = 0;
            $radarWorkData = array_fill(0, count($pillarLabels), null);
            $lifeEdgeLeftData = array_fill(0, count($radarLabels), null);
            $lifeEdgeRightData = array_fill(0, count($radarLabels), null);
            $lifePointData = array_fill(0, count($radarLabels), null);
            $lifeFillData = array_fill(0, count($radarLabels), 0);
            $importanceDataset = array_fill(0, count($radarLabels), null);
            $answerNotes = [];
        }

        return view('diagnosis.result', [
            'diagnosis' => $diagnosis,
            'workScore' => $diagnosis->work_score ?? 0,
            'lifeScore' => $importanceScore,
            'radarLabels' => $radarLabels,
            'radarWorkData' => $radarWorkData,
            'lifeEdgeLeftData' => $lifeEdgeLeftData,
            'lifeEdgeRightData' => $lifeEdgeRightData,
            'lifePointData' => $lifePointData,
            'lifeFillData' => $lifeFillData,
            'importanceDataset' => $importanceDataset,
            'importanceLifeAvg' => $importanceLifeAvg,
            'answerNotes' => $answerNotes,
            'workPillarScores' => $workPillarScores,
            'importanceWork' => $importanceWork,
            'pillarLabels' => $pillarLabels,
            'hasImportance' => $hasImportance,
        ]);
    }

    /**
     * 現職満足度診断結果を表示（管理者用）
     */
    protected function renderCareerSatisfactionResult(CareerSatisfactionDiagnosis $diagnosis)
    {
        // CareerSatisfactionDiagnosisControllerのresultメソッドと同じロジック
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];

        $pillarLabels = [
            'purpose' => 'Purpose（目的）',
            'profession' => 'Profession（職業）',
            'people' => 'People（人間関係）',
            'privilege' => 'Privilege（待遇）',
            'progress' => 'Progress（成長）',
        ];

        $radarLabels = [];
        $radarWorkData = [];
        foreach ($pillarLabels as $key => $label) {
            if (isset($workPillarScores[$key])) {
                $radarLabels[] = $label;
                $radarWorkData[] = $workPillarScores[$key];
            } else {
                $radarLabels[] = $label;
                $radarWorkData[] = null;
            }
        }

        $importanceWork = [];
        $workQuestions = \App\Models\Question::where('type','work')->get();
        
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarWeight = 0;
            
            foreach ($qs as $q) {
                $ans = \App\Models\CareerSatisfactionDiagnosisImportanceAnswer::where('career_satisfaction_diagnosis_id', $diagnosis->id)
                    ->where('question_id', $q->id)
                    ->first();
                
                if ($ans && $q->weight) {
                    $importanceValue = (($ans->importance_value - 1) / 4) * 100;
                    $pillarScore += $importanceValue * $q->weight;
                    $pillarWeight += $q->weight;
                }
            }
            
            if ($pillarWeight > 0) {
                $importanceWork[$pillar] = round($pillarScore / $pillarWeight);
            } else {
                $importanceWork[$pillar] = null;
            }
        }
        
        $importanceDataset = [];
        foreach (array_keys($pillarLabels) as $key) {
            $importanceDataset[] = $importanceWork[$key] ?? null;
        }

        $stuckPoints = [];
        $maxDiff = null;
        $stuckPointDetails = [];
        
        foreach ($workPillarScores as $pillar => $satisfactionScore) {
            $importanceScore = $importanceWork[$pillar] ?? null;
            if ($importanceScore !== null && $satisfactionScore !== null) {
                $diff = $satisfactionScore - $importanceScore;
                if ($diff < 0) {
                    $stuckPoints[] = $pillar;
                    $stuckPointDetails[$pillar] = [
                        'label' => $pillarLabels[$pillar],
                        'satisfaction' => $satisfactionScore,
                        'importance' => $importanceScore,
                        'diff' => $diff,
                    ];
                    if ($maxDiff === null || $diff < $maxDiff) {
                        $maxDiff = $diff;
                    }
                }
            }
        }
        
        $stuckPointCount = count($stuckPoints);
        
        $gapSummary = [
            'mild' => [],
            'moderate' => [],
            'severe' => [],
        ];
        
        foreach ($stuckPointDetails as $pillar => $detail) {
            if ($detail['diff'] >= -10) {
                $gapSummary['mild'][] = $detail['label'];
            } elseif ($detail['diff'] >= -20) {
                $gapSummary['moderate'][] = $detail['label'];
            } else {
                $gapSummary['severe'][] = $detail['label'];
            }
        }
        
        $stateType = CareerSatisfactionDiagnosis::determineStateType(
            $workPillarScores,
            $importanceWork,
            $diagnosis->work_score ?? 0
        );
        
        if ($diagnosis->state_type !== $stateType) {
            $diagnosis->update(['state_type' => $stateType]);
        }

        if (!$diagnosis->is_completed) {
            $workPillarScores = [];
            $importanceWork = [];
            $workScore = 0;
            $radarWorkData = array_fill(0, count($pillarLabels), null);
            $importanceDataset = array_fill(0, count($pillarLabels), null);
            $stuckPoints = [];
            $stuckPointDetails = [];
            $gapSummary = ['mild' => [], 'moderate' => [], 'severe' => []];
            $stateType = null;
        }

        return view('career-satisfaction-diagnosis.result', [
            'diagnosis' => $diagnosis,
            'workScore' => $diagnosis->work_score ?? 0,
            'radarLabels' => $radarLabels,
            'radarWorkData' => $radarWorkData,
            'importanceDataset' => $importanceDataset,
            'workPillarScores' => $workPillarScores,
            'importanceWork' => $importanceWork,
            'pillarLabels' => $pillarLabels,
            'stuckPoints' => $stuckPoints,
            'stuckPointCount' => $stuckPointCount,
            'stuckPointDetails' => $stuckPointDetails,
            'maxDiff' => $maxDiff,
            'gapSummary' => $gapSummary,
            'stateType' => $stateType,
        ]);
    }
}
