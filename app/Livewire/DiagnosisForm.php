<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;
use App\Models\Diagnosis;
use App\Models\DiagnosisAnswer;
use Illuminate\Support\Facades\Auth;

class DiagnosisForm extends Component
{
    public $questions = [];
    public $currentIndex = 0;
    public $answers = [];
    public $diagnosisId = null;

    public function mount()
    {
        $this->questions = Question::orderBy('order')->get()->toArray();
        
        // 既存の下書き診断があるか確認
        $diagnosis = Diagnosis::where('user_id', Auth::id())
            ->where('is_draft', true)
            ->latest()
            ->first();
        
        if ($diagnosis) {
            $this->diagnosisId = $diagnosis->id;
            // 既存の回答を読み込む
            $existingAnswers = DiagnosisAnswer::where('diagnosis_id', $diagnosis->id)
                ->get()
                ->keyBy('question_id')
                ->toArray();
            
            foreach ($this->questions as $q) {
                $key = $q['id'];
                if (isset($existingAnswers[$key])) {
                    $this->answers[$key] = [
                        'answer_value' => $existingAnswers[$key]['answer_value'],
                        'comment' => $existingAnswers[$key]['comment'] ?? '',
                    ];
                } else {
                    $this->answers[$key] = [
                        'answer_value' => null,
                        'comment' => '',
                    ];
                }
            }
        } else {
            // 新しい診断を作成
            $diagnosis = Diagnosis::create([
                'user_id' => Auth::id(),
                'is_draft' => true,
                'is_completed' => false,
            ]);
            $this->diagnosisId = $diagnosis->id;
            
            // 空の回答を初期化
            foreach ($this->questions as $q) {
                $this->answers[$q['id']] = [
                    'answer_value' => null,
                    'comment' => '',
                ];
            }
        }
    }

    public function selectOption($questionId, $value)
    {
        $this->answers[$questionId]['answer_value'] = $value;
        $this->saveAnswer($questionId);
    }

    public function updateComment($questionId, $comment)
    {
        $this->answers[$questionId]['comment'] = $comment;
        $this->saveAnswer($questionId);
    }

    public function saveAnswer($questionId)
    {
        if (!$this->diagnosisId) {
            return;
        }

        $answer = $this->answers[$questionId];
        
        if ($answer['answer_value'] === null) {
            return;
        }

        DiagnosisAnswer::updateOrCreate(
            [
                'diagnosis_id' => $this->diagnosisId,
                'question_id' => $questionId,
            ],
            [
                'answer_value' => $answer['answer_value'],
                'comment' => $answer['comment'] ?? '',
            ]
        );
    }

    public function nextQuestion()
    {
        if ($this->currentIndex < count($this->questions) - 1) {
            $this->currentIndex++;
        }
    }

    public function prevQuestion()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function saveDraft()
    {
        // すべての回答を保存
        foreach ($this->answers as $questionId => $answer) {
            if ($answer['answer_value'] !== null) {
                $this->saveAnswer($questionId);
            }
        }

        session()->flash('message', '回答を一時保存しました。あとで続きから再開できます。');
        
        return redirect()->route('dashboard');
    }

    public function finish()
    {
        // すべての質問に回答があるか確認
        $allAnswered = true;
        foreach ($this->answers as $answer) {
            if ($answer['answer_value'] === null) {
                $allAnswered = false;
                break;
            }
        }

        if (!$allAnswered) {
            session()->flash('error', 'すべての質問に回答してください。');
            return;
        }

        // すべての回答を保存
        foreach ($this->answers as $questionId => $answer) {
            $this->saveAnswer($questionId);
        }

        // スコアを計算
        $this->calculateScores();

        // 診断を完了にする
        $diagnosis = Diagnosis::find($this->diagnosisId);
        $diagnosis->update([
            'is_completed' => true,
            'is_draft' => false,
        ]);

        return redirect()->route('diagnosis.result', $this->diagnosisId);
    }

    protected function calculateScores()
    {
        $diagnosis = Diagnosis::find($this->diagnosisId);
        $answers = DiagnosisAnswer::where('diagnosis_id', $this->diagnosisId)
            ->with('question')
            ->get();

        $workScores = [];
        $lifeScores = [];
        $workPillarScores = [];
        $lifePillarScores = [];

        foreach ($answers as $answer) {
            $question = $answer->question;
            // 1-5を0-100に変換
            $scaledScore = (($answer->answer_value - 1) / 4) * 100;

            if ($question->type === 'work') {
                $workScores[] = $scaledScore;
                
                // pillar別に集計
                if (!isset($workPillarScores[$question->pillar])) {
                    $workPillarScores[$question->pillar] = [];
                }
                $workPillarScores[$question->pillar][] = $scaledScore;
            } else {
                $lifeScores[] = $scaledScore;
                
                // pillar別に集計
                if (!isset($lifePillarScores[$question->pillar])) {
                    $lifePillarScores[$question->pillar] = [];
                }
                $lifePillarScores[$question->pillar][] = $scaledScore;
            }
        }

        // Workスコア：pillar別平均をweightで加重平均
        $workScore = 0;
        $totalWeight = 0;
        foreach ($workPillarScores as $pillar => $scores) {
            $pillarAvg = array_sum($scores) / count($scores);
            $question = Question::where('type', 'work')
                ->where('pillar', $pillar)
                ->first();
            if ($question && $question->weight) {
                $workScore += $pillarAvg * ($question->weight / 100);
                $totalWeight += $question->weight;
            }
        }
        $workScore = round($workScore);

        // Lifeスコア：単純平均
        $lifeScore = !empty($lifeScores) ? round(array_sum($lifeScores) / count($lifeScores)) : 0;

        // pillar別スコアを計算（平均）
        $workPillarFinal = [];
        foreach ($workPillarScores as $pillar => $scores) {
            $workPillarFinal[$pillar] = round(array_sum($scores) / count($scores));
        }

        $lifePillarFinal = [];
        foreach ($lifePillarScores as $pillar => $scores) {
            $lifePillarFinal[$pillar] = round(array_sum($scores) / count($scores));
        }

        $diagnosis->update([
            'work_score' => $workScore,
            'life_score' => $lifeScore,
            'work_pillar_scores' => $workPillarFinal,
            'life_pillar_scores' => $lifePillarFinal,
        ]);
    }

    public function getCurrentQuestionProperty()
    {
        return $this->questions[$this->currentIndex] ?? null;
    }

    public function getProgressPercentProperty()
    {
        $answeredCount = count(array_filter($this->answers, fn($a) => $a['answer_value'] !== null));
        return round(($answeredCount / count($this->questions)) * 100);
    }

    public function getIsLastQuestionProperty()
    {
        return $this->currentIndex === count($this->questions) - 1;
    }

    public function render()
    {
        return view('livewire.diagnosis-form');
    }
}
