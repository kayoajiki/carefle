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

        $data = $this->prepareDiagnosisData($diagnosis);

        return view('career-satisfaction-diagnosis.result', $data);
    }

    /**
     * API用：診断結果をJSONで返す
     */
    public function resultApi($id)
    {
        $diagnosis = CareerSatisfactionDiagnosis::with(['answers.question'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $this->prepareDiagnosisData($diagnosis);

        return response()->json($data);
    }

    /**
     * 診断結果表示用のデータを準備する
     */
    private function prepareDiagnosisData($diagnosis)
    {
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

        // Pillarごとのメモを集計
        $pillarMemos = [];
        foreach ($diagnosis->answers as $answer) {
            if ($answer->comment && $answer->question) {
                $pillar = $answer->question->pillar;
                if (!isset($pillarMemos[$pillar])) {
                    $pillarMemos[$pillar] = [];
                }
                $pillarMemos[$pillar][] = $answer->comment;
            }
        }

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
                        'memos' => $pillarMemos[$pillar] ?? [],
                    ];
                    if ($maxDiff === null || $diff < $maxDiff) {
                        $maxDiff = $diff;
                    }
                }
            }
        }
        
        $stuckPointCount = count($stuckPoints);

        // 安心ゾーン（ギャップがない領域）
        $safeZoneDetails = [];
        foreach ($pillarLabels as $pillar => $label) {
            if (!in_array($pillar, $stuckPoints) && isset($workPillarScores[$pillar])) {
                $safeZoneDetails[$pillar] = [
                    'label' => $label,
                    'satisfaction' => $workPillarScores[$pillar],
                    'importance' => $importanceWork[$pillar] ?? null,
                    'memos' => $pillarMemos[$pillar] ?? [],
                ];
            }
        }
        
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

        // パターン判定
        $workScore = $diagnosis->work_score ?? 0;
        $relationshipPattern = $this->determineRelationshipPattern($stateType, $workScore, $stuckPointCount, $maxDiff);
        $summaryPattern = $this->determineSummaryPattern($stateType, $workScore);
        $continuationPosition = $this->calculateContinuationPosition($workScore, $stuckPointCount, $maxDiff);

        // 診断未完了時のデフォルト値設定
        if (!$diagnosis->is_completed) {
            return [
                'diagnosis' => $diagnosis,
                'workScore' => 0,
                'radarLabels' => $radarLabels,
                'radarWorkData' => array_fill(0, count($pillarLabels), null),
                'importanceDataset' => array_fill(0, count($pillarLabels), null),
                'workPillarScores' => [],
                'importanceWork' => [],
                'pillarLabels' => $pillarLabels,
                'stuckPoints' => [],
                'stuckPointCount' => 0,
                'stuckPointDetails' => [],
                'safeZoneDetails' => [],
                'maxDiff' => null,
                'gapSummary' => ['mild' => [], 'moderate' => [], 'severe' => []],
                'stateType' => null,
                'relationshipPattern' => 'PATTERN_DEFAULT',
                'summaryPattern' => 'SUMMARY_DEFAULT',
                'continuationPosition' => 50,
            ];
        }

        return [
            'diagnosis' => $diagnosis,
            'workScore' => $workScore,
            'radarLabels' => $radarLabels,
            'radarWorkData' => $radarWorkData,
            'importanceDataset' => $importanceDataset,
            'workPillarScores' => $workPillarScores,
            'importanceWork' => $importanceWork,
            'pillarLabels' => $pillarLabels,
            'stuckPoints' => $stuckPoints,
            'stuckPointCount' => $stuckPointCount,
            'stuckPointDetails' => $stuckPointDetails,
            'safeZoneDetails' => $safeZoneDetails,
            'maxDiff' => $maxDiff,
            'gapSummary' => $gapSummary,
            'stateType' => $stateType,
            'relationshipPattern' => $relationshipPattern,
            'summaryPattern' => $summaryPattern,
            'continuationPosition' => $continuationPosition,
        ];
    }

    /**
     * ファーストビュー「いまは、こんな距離感にいます」のパターンを判定
     */
    private function determineRelationshipPattern($stateType, $workScore, $stuckPointCount, $maxDiff) {
        if ($stateType === 'C' && $stuckPointCount === 0) {
            return 'PATTERN_1';
        } elseif ($stateType === 'C' && $stuckPointCount >= 1 && $stuckPointCount <= 2 && $workScore >= 70 && $maxDiff >= -10) {
            return 'PATTERN_2';
        } elseif ($stateType === 'A' && $stuckPointCount >= 1 && $stuckPointCount <= 2 && $workScore < 70 && $maxDiff >= -10) {
            return 'PATTERN_3';
        } elseif ($stateType === 'A' && $stuckPointCount >= 3 && $workScore >= 70 && $maxDiff >= -10) {
            return 'PATTERN_4';
        } elseif ($stateType === 'B' && $stuckPointCount >= 1 && $stuckPointCount <= 2 && $maxDiff < -10 && $maxDiff >= -20) {
            return 'PATTERN_5';
        } elseif ($stateType === 'B' && $stuckPointCount >= 3 && $maxDiff < -10 && $maxDiff >= -20) {
            return 'PATTERN_6';
        } elseif ($stateType === 'B' && $stuckPointCount >= 1 && $maxDiff < -20) {
            return 'PATTERN_7';
        } elseif ($stateType === 'B' && $stuckPointCount >= 3 && $workScore < 60) {
            return 'PATTERN_8';
        }
        return 'PATTERN_DEFAULT';
    }

    /**
     * 状態サマリー「今の状態をひとことで言うと」のパターンを判定
     */
    private function determineSummaryPattern($stateType, $workScore) {
        if ($stateType === 'C' && $workScore >= 80) {
            return 'SUMMARY_C_HIGH';
        } elseif ($stateType === 'C' && $workScore < 80 && $workScore >= 70) {
            return 'SUMMARY_C_MID';
        } elseif ($stateType === 'A' && $workScore >= 70) {
            return 'SUMMARY_A_HIGH';
        } elseif ($stateType === 'A' && $workScore < 70) {
            return 'SUMMARY_A_MID';
        } elseif ($stateType === 'B' && $workScore >= 60) {
            return 'SUMMARY_B_MID';
        } elseif ($stateType === 'B' && $workScore < 60) {
            return 'SUMMARY_B_LOW';
        }
        return 'SUMMARY_DEFAULT';
    }

    /**
     * 横棒線の図解「今の仕事を『続けること』への気持ち」の位置を計算（0-100）
     */
    private function calculateContinuationPosition($workScore, $stuckPointCount, $maxDiff) {
        // 基本位置：workScoreを0-100のスケールに変換（0=迷い、100=前向き）
        $basePosition = $workScore;
        
        // stuckPointCountによる調整（1つにつき-10ポイント）
        $stuckAdjustment = $stuckPointCount * -10;
        
        // maxDiffによる調整（深刻なギャップほど迷い側に）
        $diffAdjustment = 0;
        if ($maxDiff !== null && $maxDiff < -20) {
            $diffAdjustment = -15; // 深刻なギャップ
        } elseif ($maxDiff !== null && $maxDiff < -10) {
            $diffAdjustment = -8; // 中程度のギャップ
        } elseif ($maxDiff !== null && $maxDiff >= -10) {
            $diffAdjustment = -3; // 軽微なギャップ
        }
        
        $position = $basePosition + $stuckAdjustment + $diffAdjustment;
        
        // 0-100の範囲に制限
        return max(0, min(100, $position));
    }
}
