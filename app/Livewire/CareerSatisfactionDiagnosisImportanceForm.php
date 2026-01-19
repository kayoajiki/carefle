<?php

namespace App\Livewire;

use App\Models\CareerSatisfactionDiagnosis;
use App\Models\CareerSatisfactionDiagnosisImportanceAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.simple_component')]
class CareerSatisfactionDiagnosisImportanceForm extends Component
{
    public int $diagnosisId;
    public array $questions = [];
    public int $currentIndex = 0;
    public array $answers = [];
    public array $displayTexts = [];
    public array $importanceOptions = [];
    private array $fixedTextsByIndex = [
        // Q1 - Q14 固定文（順番は Question::orderBy('order') の順に対応）
        '勤める会社の大義・ビジョン・目的に共感できることはあなたにとってどれくらい重要ですか？',
        '仕事にやりがい・価値を感じられることはあなたにとってどれくらい重要ですか？',
        '持っている強み・適性を仕事で活かすことはあなたにとってどれくらい重要ですか？',
        '「ずっとこの人と働きたい」と思える同僚・上司・部下がいることは、あなたにとってどれくらい重要ですか？',
        '一定の生活リズムや、バランスを保ちながら働くことはあなたにとってどれくらい重要ですか？',
        '収入や待遇はどれくらい重要ですか？',
        '仕事における肩書や評価はあなたにとってどれくらい重要ですか？',
        '仕事において成長実感を感じられることはあなたにとってどれくらい重要ですか？',
        '今の家族との関係、家庭生活、子育てなど仕事を除く生活全般が充実・満足していることは、あなたにとってどれくらい重要ですか？',
        '友人・家族を除く人間関係が充実・満足していることは、あなたにとってどれくらい重要ですか？',
        '自分のための時間（休息・趣味）をちゃんと確保できていることはあなたにとってどれくらい重要ですか？',
        '本業以外の活動（副業/事業/挑戦）を実施できていることや、充実していることは、あなたにとってどれくらい重要ですか？',
        '体調・メンタル・睡眠などに気を配った健康的な生活が送れていることはあなたにとってどれくらい重要ですか？',
        '将来に対する貯蓄・資産形成等ができていることはあなたにとってどれくらい重要ですか？',
    ];

    public function mount(int $id): void
    {
        $this->diagnosisId = $id;
        
        // 診断が存在し、ユーザーが所有しているか確認
        $diagnosis = CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Workタイプの質問のみを取得
        $this->questions = Question::where('type', 'work')
            ->orderBy('order')
            ->get()
            ->toArray();

        foreach ($this->questions as $i => $q) {
            $qid = $q['id'];
            $this->answers[$qid] = null;
            // 固定文が14件ある想定。範囲外は自動変換にフォールバック。
            $this->displayTexts[$qid] = $this->fixedTextsByIndex[$i] ?? $this->toImportanceText($q['text'] ?? '');
        }

        $this->importanceOptions = [
            ['value' => 5, 'label' => 'とても重要'],
            ['value' => 4, 'label' => '重要'],
            ['value' => 3, 'label' => 'どちらとも言えない'],
            ['value' => 2, 'label' => '重要でない'],
            ['value' => 1, 'label' => '全く重要でない'],
        ];
    }

    public function selectOption(string $questionId, int $value): void
    {
        $this->answers[$questionId] = $value;
        $question = Question::where('id', $questionId)->firstOrFail();
        CareerSatisfactionDiagnosisImportanceAnswer::updateOrCreate(
            [
                'career_satisfaction_diagnosis_id' => $this->diagnosisId,
                'question_id' => $question->id,
            ],
            [
                'importance_value' => $value,
            ]
        );
    }

    public function next(): void
    {
        if ($this->currentIndex < count($this->questions) - 1) {
            $this->currentIndex++;
        } else {
            $this->finish();
        }
    }

    public function prev(): void
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function finish(): mixed
    {
        // すべての質問に回答があるか確認（必須）
        $allAnswered = true;
        foreach ($this->answers as $answer) {
            if ($answer === null) {
                $allAnswered = false;
                break;
            }
        }

        if (!$allAnswered) {
            session()->flash('error', 'すべての質問に回答してください。重要度の入力は必須です。');
            return redirect()->back();
        }

        // 状態タイプを再計算して保存
        $diagnosis = CareerSatisfactionDiagnosis::find($this->diagnosisId);
        $workPillarScores = $diagnosis->work_pillar_scores ?? [];
        
        // 重要度スコアを計算
        $importanceWork = [];
        $workQuestions = Question::where('type','work')->get();
        
        foreach ($workQuestions->groupBy('pillar') as $pillar => $qs) {
            $pillarScore = 0;
            $pillarWeight = 0;
            
            foreach ($qs as $q) {
                $ans = CareerSatisfactionDiagnosisImportanceAnswer::where('career_satisfaction_diagnosis_id', $this->diagnosisId)
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
        
        // 状態タイプを判定して保存
        $stateType = CareerSatisfactionDiagnosis::determineStateType(
            $workPillarScores,
            $importanceWork,
            $diagnosis->work_score ?? 0
        );
        
        $diagnosis->update(['state_type' => $stateType]);
        
        return redirect()->route('career-satisfaction-diagnosis.result', ['id' => $this->diagnosisId]);
    }

    public function render()
    {
        return view('livewire.career-satisfaction-diagnosis-importance-form');
    }

    private function toImportanceText(string $original): string
    {
        $text = trim($original);
        // 語尾の「ですか？」や「か？」などを落として語幹を作る
        $text = preg_replace('/(ですか\?|でしょうか\?|か\?)$/u', '', $text);
        // 主語の調整（あなたが/あなたの/… を簡易置換）
        $text = preg_replace('/^あなた[はがの]/u', '', $text);
        // 重要度の定型文を付与
        return rtrim($text) . 'はあなたにとってどれくらい重要ですか？';
    }
}

