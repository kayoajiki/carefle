<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareerSatisfactionDiagnosis;
use App\Models\Question;
use App\Models\CareerSatisfactionDiagnosisImportanceAnswer;
use Illuminate\Support\Facades\Auth;

class CareerSatisfactionDiagnosisController extends Controller
{
    public function start()
    {
        $user = Auth::user();
        
        // プロフィールが未完了の場合は、プロフィール登録画面にリダイレクト
        if (!$user->profile_completed) {
            return redirect()->route('profile.setup');
        }
        
        return view('career-satisfaction-diagnosis.start');
    }

    public function result($id)
    {
        $diagnosis = CareerSatisfactionDiagnosis::with(['answers.question'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // レーダーチャート用のデータを準備
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];

        // ラベルの定義（日本語訳付き）
        $pillarLabels = [
            'purpose' => 'Purpose（目的）',
            'profession' => 'Profession（職業）',
            'people' => 'People（人間関係）',
            'privilege' => 'Privilege（待遇）',
            'progress' => 'Progress（成長）',
        ];

        // Workデータ
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

        // 重要度（青）オーバーレイ
        $importanceWork = [];
        $workQuestions = Question::where('type','work')->get();
        
        // Workタイプの各pillarの重要度スコアを計算（各pillar内でweightで加重平均）
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarWeight = 0;
            
            foreach ($qs as $q) {
                $ans = CareerSatisfactionDiagnosisImportanceAnswer::where('career_satisfaction_diagnosis_id', $diagnosis->id)
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

        // 引っかかりポイントの判定
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
        
        // ギャップの大きさ別に分類
        $gapSummary = [
            'mild' => [], // -10点以上（軽微）
            'moderate' => [], // -20点以上（中程度）
            'severe' => [], // -20点未満（深刻）
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
        
        // 状態タイプを判定
        $stateType = CareerSatisfactionDiagnosis::determineStateType(
            $workPillarScores,
            $importanceWork,
            $diagnosis->work_score ?? 0
        );
        
        // 状態タイプを保存（まだ保存されていない場合）
        if ($diagnosis->state_type !== $stateType) {
            $diagnosis->update(['state_type' => $stateType]);
        }

        // 診断未完了時のデフォルト値設定
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

