<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\Question;
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

        // Lifeデータ（平均値を1つの値として表示、または各pillarを表示）
        $lifeAvg = !empty($lifePillarScores) 
            ? round(array_sum($lifePillarScores) / count($lifePillarScores))
            : $diagnosis->life_score ?? 0;

        // Lifeを1つの値として追加
        $radarLabels[] = 'Life（ライフ）';
        $radarWorkData[] = null;
        $radarLifeData = array_fill(0, count($radarLabels) - 1, null);
        $radarLifeData[] = $lifeAvg;

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

        return view('diagnosis.result', [
            'diagnosis' => $diagnosis,
            'workScore' => $diagnosis->work_score ?? 0,
            'lifeScore' => $diagnosis->life_score ?? 0,
            'radarLabels' => $radarLabels,
            'radarWorkData' => $radarWorkData,
            'radarLifeData' => $radarLifeData,
            'answerNotes' => $answerNotes,
        ]);
    }
}
