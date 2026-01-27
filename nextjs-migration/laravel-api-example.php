<?php
// Laravel APIエンドポイントのサンプル実装
// routes/api.php に追加するか、既存のコントローラーに追加してください

use App\Http\Controllers\CareerSatisfactionDiagnosisController;
use App\Livewire\CareerSatisfactionDiagnosisForm;
use App\Livewire\CareerSatisfactionDiagnosisImportanceForm;
use Illuminate\Support\Facades\Route;

// 認証が必要なAPIルート
Route::middleware(['auth:sanctum'])->group(function () {
    
    // 1. 診断開始（既存の下書きを取得または新規作成）
    Route::get('/career-satisfaction-diagnosis/start', function () {
        $user = auth()->user();
        
        // プロフィールが未完了の場合はエラー
        if (!$user->profile_completed) {
            return response()->json([
                'error' => 'プロフィールが未完了です'
            ], 400);
        }
        
        // Workタイプの質問を取得
        $questions = \App\Models\Question::where('type', 'work')
            ->orderBy('order')
            ->get();
        
        // 既存の下書き診断があるか確認
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('user_id', $user->id)
            ->where('is_draft', true)
            ->latest()
            ->first();
        
        $answers = [];
        
        if ($diagnosis) {
            // 既存の回答を読み込む
            $existingAnswers = \App\Models\CareerSatisfactionDiagnosisAnswer::where('career_satisfaction_diagnosis_id', $diagnosis->id)
                ->get()
                ->keyBy('question_id');
            
            foreach ($questions as $q) {
                $existing = $existingAnswers->get($q->id);
                $answers[$q->id] = [
                    'answer_value' => $existing ? $existing->answer_value : null,
                    'comment' => $existing ? ($existing->comment ?? '') : '',
                ];
            }
        } else {
            // 新しい診断を作成
            $diagnosis = \App\Models\CareerSatisfactionDiagnosis::create([
                'user_id' => $user->id,
                'is_draft' => true,
                'is_completed' => false,
            ]);
            
            // 空の回答を初期化
            foreach ($questions as $q) {
                $answers[$q->id] = [
                    'answer_value' => null,
                    'comment' => '',
                ];
            }
        }
        
        return response()->json([
            'questions' => $questions,
            'answers' => $answers,
            'diagnosisId' => $diagnosis->id,
        ]);
    });
    
    // 2. 質問一覧取得
    Route::get('/questions', function (\Illuminate\Http\Request $request) {
        $type = $request->query('type', 'work');
        $questions = \App\Models\Question::where('type', $type)
            ->orderBy('order')
            ->get();
        
        return response()->json($questions);
    });
    
    // 3. 回答保存
    Route::post('/career-satisfaction-diagnosis/{id}/answers', function ($id, \Illuminate\Http\Request $request) {
        $user = auth()->user();
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer_value' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        \App\Models\CareerSatisfactionDiagnosisAnswer::updateOrCreate(
            [
                'career_satisfaction_diagnosis_id' => $diagnosis->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'answer_value' => $validated['answer_value'],
                'comment' => $validated['comment'] ?? '',
            ]
        );
        
        return response()->json(['success' => true]);
    });
    
    // 4. 下書き保存
    Route::post('/career-satisfaction-diagnosis/{id}/save-draft', function ($id) {
        $user = auth()->user();
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // 診断は既にis_draft=trueなので、特に更新は不要
        // すべての回答は既に保存されている想定
        
        return response()->json(['success' => true]);
    });
    
    // 5. 満足度診断完了
    Route::post('/career-satisfaction-diagnosis/{id}/finish', function ($id) {
        $user = auth()->user();
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $answers = \App\Models\CareerSatisfactionDiagnosisAnswer::where('career_satisfaction_diagnosis_id', $diagnosis->id)
            ->with('question')
            ->get();
        
        // すべての質問に回答があるか確認
        $questions = \App\Models\Question::where('type', 'work')
            ->orderBy('order')
            ->get();
        
        $allAnswered = true;
        foreach ($questions as $q) {
            if (!$answers->where('question_id', $q->id)->first()) {
                $allAnswered = false;
                break;
            }
        }
        
        if (!$allAnswered) {
            return response()->json([
                'error' => 'すべての質問に回答してください。'
            ], 400);
        }
        
        // スコアを計算（CareerSatisfactionDiagnosisFormのcalculateScoresメソッドを参照）
        $workScores = [];
        $workPillarScores = [];
        
        foreach ($answers as $answer) {
            $question = $answer->question;
            $scaledScore = (($answer->answer_value - 1) / 4) * 100;
            
            if ($question->type === 'work') {
                $workScores[] = $scaledScore;
                
                if (!isset($workPillarScores[$question->pillar])) {
                    $workPillarScores[$question->pillar] = [];
                }
                $workPillarScores[$question->pillar][] = $scaledScore;
            }
        }
        
        // pillar別スコアを計算
        $workPillarFinal = [];
        foreach ($workPillarScores as $pillar => $scores) {
            $pillarQuestions = \App\Models\Question::where('type', 'work')
                ->where('pillar', $pillar)
                ->get();
            
            $pillarScore = 0;
            $pillarWeight = 0;
            foreach ($pillarQuestions as $q) {
                $answer = $answers->firstWhere('question_id', $q->id);
                if ($answer && $q->weight) {
                    $scaledScore = (($answer->answer_value - 1) / 4) * 100;
                    $pillarScore += $scaledScore * $q->weight;
                    $pillarWeight += $q->weight;
                }
            }
            
            if ($pillarWeight > 0) {
                $workPillarFinal[$pillar] = round($pillarScore / $pillarWeight);
            } else {
                $workPillarFinal[$pillar] = round(array_sum($scores) / count($scores));
            }
        }
        
        // Workスコアを計算
        $workScore = 0;
        $totalWeight = 0;
        foreach ($workPillarFinal as $pillar => $pillarAvg) {
            $pillarWeight = \App\Models\Question::where('type', 'work')
                ->where('pillar', $pillar)
                ->sum('weight');
            
            if ($pillarWeight > 0) {
                $workScore += $pillarAvg * $pillarWeight;
                $totalWeight += $pillarWeight;
            }
        }
        
        if ($totalWeight > 0) {
            $workScore = round($workScore / $totalWeight);
        } else {
            $workScore = 0;
        }
        
        $minPillarScore = !empty($workPillarFinal) ? min($workPillarFinal) : 100;
        if ($minPillarScore < 100) {
            $workScore = round(($workScore + $minPillarScore) / 2);
        }
        
        // 診断を完了にする
        $diagnosis->update([
            'work_score' => $workScore,
            'work_pillar_scores' => $workPillarFinal,
            'is_completed' => true,
            'is_draft' => false,
        ]);
        
        // Update user's last_activity_at
        $user->last_activity_at = now();
        $user->save();
        
        // アクティビティログに記録
        $activityLogService = app(\App\Services\ActivityLogService::class);
        $activityLogService->logDiagnosisCompleted($user->id, $diagnosis->id);
        
        return response()->json([
            'success' => true,
            'diagnosisId' => $diagnosis->id,
        ]);
    });
    
    // 6. 重要度回答保存
    Route::post('/career-satisfaction-diagnosis/{id}/importance-answers', function ($id, \Illuminate\Http\Request $request) {
        $user = auth()->user();
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'importance_value' => 'required|integer|min:1|max:5',
        ]);
        
        $question = \App\Models\Question::findOrFail($validated['question_id']);
        
        \App\Models\CareerSatisfactionDiagnosisImportanceAnswer::updateOrCreate(
            [
                'career_satisfaction_diagnosis_id' => $diagnosis->id,
                'question_id' => $question->id,
            ],
            [
                'importance_value' => $validated['importance_value'],
            ]
        );
        
        return response()->json(['success' => true]);
    });
    
    // 7. 重要度診断完了
    Route::post('/career-satisfaction-diagnosis/{id}/finish-importance', function ($id) {
        $user = auth()->user();
        $diagnosis = \App\Models\CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];
        
        // 重要度スコアを計算
        $importanceWork = [];
        $workQuestions = \App\Models\Question::where('type', 'work')->get();
        
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
            }
        }
        
        // すべての質問に重要度が入力されているか確認
        $allAnswered = true;
        foreach ($workQuestions as $q) {
            $ans = \App\Models\CareerSatisfactionDiagnosisImportanceAnswer::where('career_satisfaction_diagnosis_id', $diagnosis->id)
                ->where('question_id', $q->id)
                ->first();
            if (!$ans) {
                $allAnswered = false;
                break;
            }
        }
        
        if (!$allAnswered) {
            return response()->json([
                'error' => 'すべての質問に回答してください。重要度の入力は必須です。'
            ], 400);
        }
        
        // 状態タイプを判定して保存
        $stateType = \App\Models\CareerSatisfactionDiagnosis::determineStateType(
            $workPillarScores,
            $importanceWork,
            $diagnosis->work_score ?? 0
        );
        
        $diagnosis->update(['state_type' => $stateType]);
        
        return response()->json([
            'success' => true,
            'diagnosisId' => $diagnosis->id,
        ]);
    });
    
    // 8. 診断結果取得
    Route::get('/career-satisfaction-diagnosis/{id}/result', [CareerSatisfactionDiagnosisController::class, 'resultApi']);
});
