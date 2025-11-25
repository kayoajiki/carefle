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

        if (!$diagnosis->is_completed) {
            return redirect()->route('diagnosis.start');
        }

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
        $importanceWork = [];
        $workQuestions = Question::where('type','work')->get();
        $totalWorkWeight = $workQuestions->sum('weight');
        $totalImportanceScore = 0;
        $totalImportanceWeight = 0;
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $sum=0; $w=0;
            foreach ($qs as $q) {
                $ans = DiagnosisImportanceAnswer::where('diagnosis_id',$diagnosis->id)->where('question_id',$q->id)->first();
                if ($ans) { 
                    $importanceValue = (($ans->importance_value-1)/4*100) * $q->weight;
                    $sum += $importanceValue; 
                    $w += $q->weight;
                    // 全体の重要度スコア計算用
                    $totalImportanceScore += $importanceValue;
                    $totalImportanceWeight += $q->weight;
                }
            }
            $importanceWork[$pillar] = $w>0 ? round($sum/$w) : null;
        }
        $importanceDataset = [];
        foreach (array_keys($pillarLabels) as $key) { $importanceDataset[] = $importanceWork[$key] ?? null; }
        // Life 軸は使わない
        $importanceDataset[] = null;

        // 重要度の全体スコアを計算
        $importanceScore = $totalImportanceWeight > 0 ? round($totalImportanceScore / $totalImportanceWeight) : 0;

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
            'answerNotes' => $answerNotes,
            'workPillarScores' => $workPillarScores,
            'importanceWork' => $importanceWork,
            'pillarLabels' => $pillarLabels,
        ]);
    }
}
