<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\Question;
use App\Models\DiagnosisImportanceAnswer;
use Illuminate\Support\Facades\Auth;

class DiagnosisController extends Controller
{
    public function start()
    {
        $user = Auth::user();
        
        // プロフィールが未完了の場合は、プロフィール登録画面にリダイレクト
        if (!$user->profile_completed) {
            return redirect()->route('profile.setup');
        }
        
        return view('diagnosis.start');
    }

    public function result($id)
    {
        $diagnosis = Diagnosis::with(['answers.question'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // 診断未完了時も結果ページを表示できるようにする（リダイレクトを削除）
        // データが不足している場合は空の配列やデフォルト値を使用

        // レーダーチャート用のデータを準備
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];
        $lifePillarScores = $diagnosis->life_pillar_scores ?? [];

        // ラベルの定義（日本語訳付き）
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

        // Lifeデータ：赤い点（平均値）と両サイドのWork点を線で結ぶ（左↔Life、右↔Life）
        $lifeAvg = !empty($lifePillarScores)
            ? round(array_sum($lifePillarScores) / count($lifePillarScores))
            : $diagnosis->life_score ?? 0;

        // Life 軸を追加
        $radarLabels[] = 'Life（ライフ）';
        // Work は Life 軸では線を引かない
        $radarWorkData[] = null;

        $countAfterAdd = count($radarLabels);        // 合計軸数
        $lastWorkIndex = $countAfterAdd - 2;         // 最後のWork軸
        $lifeIndex = $countAfterAdd - 1;             // Life軸

        // 線（左↔Life）
        $lifeEdgeLeftData = array_fill(0, $countAfterAdd, null);
        $lifeEdgeLeftData[0] = $radarWorkData[0] ?? null;                 // 左サイドのWork値
        $lifeEdgeLeftData[$lifeIndex] = $lifeAvg;                          // Life点

        // 線（右↔Life）
        $lifeEdgeRightData = array_fill(0, $countAfterAdd, null);
        $lifeEdgeRightData[$lastWorkIndex] = $radarWorkData[$lastWorkIndex] ?? null; // 右サイドのWork値
        $lifeEdgeRightData[$lifeIndex] = $lifeAvg;                         // Life点

        // 赤い点のみ（線なし）
        $lifePointData = array_fill(0, $countAfterAdd, null);
        $lifePointData[$lifeIndex] = $lifeAvg;

        // 塗りつぶし用（三角形の境界線は描かず、背景のみ）
        // すき間を無くすため、その他の軸は0（中心）を設定し、三角形内がすべて塗られるようにする
        $lifeFillData = array_fill(0, $countAfterAdd, 0);
        $lifeFillData[0] = $radarWorkData[0] ?? 0;
        $lifeFillData[$lastWorkIndex] = $radarWorkData[$lastWorkIndex] ?? 0;
        $lifeFillData[$lifeIndex] = $lifeAvg;

        // コメントの取得
        $answerNotes = [];
        foreach ($diagnosis->answers as $answer) {
            if ($answer->comment) {
                $answerNotes[] = [
                    'label' => $answer->question->text,
                    'comment' => $answer->comment,
                ];
            }
        }

        // 重要度（青）オーバーレイ
        // 満足度と同じロジックで計算：各pillar内でweightで加重平均 → 各pillarのスコアをそのpillarのweightの合計で加重平均
        $importanceWork = [];
        $workQuestions = Question::where('type','work')->get();
        
        // Workタイプの各pillarの重要度スコアを計算（各pillar内でweightで加重平均）
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarWeight = 0;
            
            foreach ($qs as $q) {
                $ans = DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)
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
        
        // Lifeタイプの重要度スコアを計算
        // Lifeタイプの質問はweightがnullなので、単純平均で計算
        $lifeQuestions = Question::where('type','life')->get();
        $importanceLifeScores = [];
        $totalLifeImportanceScore = 0;
        $totalLifeImportanceCount = 0;
        
        foreach ($lifeQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarCount = 0;
            
            foreach ($qs as $q) {
                $ans = DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)
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
                $importanceLifeScores[$pillar] = round($pillarAvg);
                // Life全体スコア計算用（各pillarの平均を単純平均）
                $totalLifeImportanceScore += $pillarAvg;
                $totalLifeImportanceCount++;
            }
        }
        
        // Life全体の重要度スコア（各pillarのスコアを単純平均）
        $importanceLifeAvg = null;
        if ($totalLifeImportanceCount > 0) {
            $importanceLifeAvg = round($totalLifeImportanceScore / $totalLifeImportanceCount);
        }
        
        $importanceDataset = [];
        foreach (array_keys($pillarLabels) as $key) {
            $importanceDataset[] = $importanceWork[$key] ?? null;
        }
        // Life 軸に重要度を設定（nullでない場合は表示）
        $importanceDataset[] = $importanceLifeAvg;

        // 重要度の全体スコアを計算（満足度と同じロジック：各pillarのスコアをそのpillarのweightの合計で加重平均）
        // WorkとLifeの両方を考慮
        $importanceScore = 0;
        $totalWeight = 0;
        
        // Workタイプの重要度スコア
        foreach ($importanceWork as $pillar => $pillarAvg) {
            if ($pillarAvg !== null) {
                // このpillar内の全質問のweightの合計を取得
                $pillarWeight = Question::where('type', 'work')
                    ->where('pillar', $pillar)
                    ->sum('weight');
                
                if ($pillarWeight > 0) {
                    $importanceScore += $pillarAvg * $pillarWeight;
                    $totalWeight += $pillarWeight;
                }
            }
        }
        
        // Lifeタイプの重要度スコアも追加（Lifeタイプはweightがないので、単純平均で追加）
        // ただし、全体スコアには含めない（Workタイプのみで計算）
        // 必要に応じて、Lifeタイプの質問数で重み付けすることも可能
        
        // totalWeightで正規化（pillarAvgは既に0-100の範囲なので、100を掛ける必要はない）
        if ($totalWeight > 0) {
            $importanceScore = round($importanceScore / $totalWeight);
        } else {
            $importanceScore = 0;
        }
        
        // 一つでもpillarのスコアが100点未満の場合、全体スコアが100点にならないようにする
        // 最小値のpillarを取得（nullを除外）
        $validImportanceWork = array_filter($importanceWork, fn($v) => $v !== null);
        $minPillarScore = !empty($validImportanceWork) ? min($validImportanceWork) : 100;
        // すべてのpillarが100点の場合のみ100点、それ以外は加重平均と最小値の平均を取る
        if ($minPillarScore < 100) {
            // 加重平均と最小値の平均を取る（最小値の影響を強くする）
            $importanceScore = round(($importanceScore + $minPillarScore) / 2);
        }

        // 重要度入力状態を判定
        $hasImportance = $totalWeight > 0 || DiagnosisImportanceAnswer::where('diagnosis_id', $diagnosis->id)->exists();

        // 診断未完了時のデフォルト値設定
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
            'importanceLifeAvg' => $importanceLifeAvg, // デバッグ用
            'answerNotes' => $answerNotes,
            'workPillarScores' => $workPillarScores,
            'importanceWork' => $importanceWork,
            'pillarLabels' => $pillarLabels,
            'hasImportance' => $hasImportance,
        ]);
    }
}
