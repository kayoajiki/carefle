<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\GoalRecommendationService;
use App\Services\NanobananaImageService;

class MyGoal extends Component
{
    public array $questions = [];
    public array $answers = [];
    public array $candidates = [];
    public array $suggestedExamples = []; // AIが生成した解答例

    public ?string $selectedGoal = null;
    public ?string $currentGoal = null;
    public ?string $currentGoalImageUrl = null;
    public string $displayMode = 'text'; // text | image

    public string $step = 'questions'; // questions | candidates | completed
    public bool $isGeneratingQuestions = false;
    public int $currentQuestionIndex = 0;
    public bool $isEditingGoal = false;
    public string $editingGoalText = '';
    public array $isGeneratingExample = []; // 各質問の解答例生成中フラグ

    protected GoalRecommendationService $goalService;
    protected NanobananaImageService $imageService;

    public function boot(
        GoalRecommendationService $goalService,
        NanobananaImageService $imageService
    ): void
    {
        $this->goalService = $goalService;
        $this->imageService = $imageService;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->currentGoal = $user?->goal_image;
        $this->currentGoalImageUrl = $user?->goal_image_url;
        $this->displayMode = $user?->goal_display_mode ?? 'text';
        $this->selectedGoal = $this->currentGoal;

        // セッションから質問を取得（キャッシュ）
        $cachedQuestions = session('goal_questions');
        if ($cachedQuestions && is_array($cachedQuestions) && !empty($cachedQuestions)) {
            $this->questions = $cachedQuestions;
            // セッションから回答も復元
            $cachedAnswers = session('goal_answers', []);
            $this->answers = $cachedAnswers;
            // 回答が不足している場合は空文字で埋める
            if (count($this->answers) < count($this->questions)) {
                $this->answers = array_merge(
                    $this->answers,
                    array_fill(count($this->answers), count($this->questions) - count($this->answers), '')
                );
            }
            $this->currentQuestionIndex = 0;
        } else {
            // キャッシュがない場合は生成
            $this->loadQuestions();
        }
        
        // デバッグ: 質問が空の場合のログとフォールバック
        if (empty($this->questions)) {
            \Illuminate\Support\Facades\Log::warning('MyGoal: questions array is empty after mount', [
                'has_cached' => !empty($cachedQuestions),
                'cached_count' => is_array($cachedQuestions) ? count($cachedQuestions) : 0,
            ]);
            // 強制的にデフォルトの質問を設定
            $this->questions = [
                ['question' => 'あなたが将来実現したいことは何ですか？', 'example' => '例：自分らしく働き、充実した毎日を送りたい'],
                ['question' => 'あなたが大切にしている価値観は何ですか？', 'example' => '例：誠実さ、成長、人とのつながり'],
                ['question' => 'あなたが理想とする働き方はどのようなものですか？', 'example' => '例：柔軟な働き方ができ、自分の強みを活かせる環境'],
                ['question' => 'あなたが人生で達成したいことは何ですか？', 'example' => '例：専門性を高め、周囲の人に貢献できる存在になる'],
                ['question' => 'あなたが将来の自分に期待することは何ですか？', 'example' => '例：自分らしさを大切にしながら、成長し続けている'],
            ];
            session(['goal_questions' => $this->questions]);
            $this->answers = array_fill(0, count($this->questions), '');
            $this->currentQuestionIndex = 0;
        }
    }

    public function loadQuestions(): void
    {
        if (!empty($this->questions)) {
            return;
        }

        $this->isGeneratingQuestions = true;

        try {
            $this->questions = $this->goalService->generateQuestions();
            
            // デバッグ: 質問が空の場合のログ
            if (empty($this->questions)) {
                \Illuminate\Support\Facades\Log::warning('GoalRecommendationService returned empty questions array');
                // フォールバック: デフォルトの質問を直接設定
                $this->questions = [
                    ['question' => 'あなたが将来実現したいことは何ですか？', 'example' => '例：自分らしく働き、充実した毎日を送りたい'],
                    ['question' => 'あなたが大切にしている価値観は何ですか？', 'example' => '例：誠実さ、成長、人とのつながり'],
                    ['question' => 'あなたが理想とする働き方はどのようなものですか？', 'example' => '例：柔軟な働き方ができ、自分の強みを活かせる環境'],
                    ['question' => 'あなたが人生で達成したいことは何ですか？', 'example' => '例：専門性を高め、周囲の人に貢献できる存在になる'],
                    ['question' => 'あなたが将来の自分に期待することは何ですか？', 'example' => '例：自分らしさを大切にしながら、成長し続けている'],
                ];
            }
            
            // セッションにキャッシュ
            session(['goal_questions' => $this->questions]);
            // 初期化
            $this->answers = array_fill(0, count($this->questions), '');
            $this->currentQuestionIndex = 0;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to load questions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // エラー時もデフォルトの質問を設定
            $this->questions = [
                ['question' => 'あなたが将来実現したいことは何ですか？', 'example' => '例：自分らしく働き、充実した毎日を送りたい'],
                ['question' => 'あなたが大切にしている価値観は何ですか？', 'example' => '例：誠実さ、成長、人とのつながり'],
                ['question' => 'あなたが理想とする働き方はどのようなものですか？', 'example' => '例：柔軟な働き方ができ、自分の強みを活かせる環境'],
                ['question' => 'あなたが人生で達成したいことは何ですか？', 'example' => '例：専門性を高め、周囲の人に貢献できる存在になる'],
                ['question' => 'あなたが将来の自分に期待することは何ですか？', 'example' => '例：自分らしさを大切にしながら、成長し続けている'],
            ];
            session(['goal_questions' => $this->questions]);
            $this->answers = array_fill(0, count($this->questions), '');
            $this->currentQuestionIndex = 0;
        } finally {
            $this->isGeneratingQuestions = false;
        }
    }

    public function updatedAnswers($value, $key): void
    {
        // 配列のサイズを確認し、必要に応じて拡張
        while (count($this->answers) < count($this->questions)) {
            $this->answers[] = '';
        }
        
        // 回答が更新されたらセッションに保存
        session(['goal_answers' => $this->answers]);
        
        // 入力内容が一定文字数以上の場合、AIで解答例を生成
        $trimmedValue = trim($value);
        if (strlen($trimmedValue) >= 10 && !empty($trimmedValue)) {
            $this->generateAnswerExample($key, $trimmedValue);
        } else {
            // 入力が短い場合は解答例をクリア
            if (isset($this->suggestedExamples[$key])) {
                unset($this->suggestedExamples[$key]);
            }
        }
    }
    
    /**
     * AIで解答例を生成
     */
    public function generateAnswerExample(int $questionIndex, string $userInput): void
    {
        if (!isset($this->questions[$questionIndex])) {
            return;
        }
        
        $question = $this->questions[$questionIndex];
        $questionText = is_array($question) ? ($question['question'] ?? '') : (is_string($question) ? $question : '');
        
        if (empty($questionText)) {
            return;
        }
        
        $this->isGeneratingExample[$questionIndex] = true;
        
        try {
            $suggestedExample = $this->goalService->generateAnswerExample($questionText, $userInput);
            if (!empty($suggestedExample)) {
                $this->suggestedExamples[$questionIndex] = $suggestedExample;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate answer example', [
                'error' => $e->getMessage(),
                'question_index' => $questionIndex,
            ]);
        } finally {
            $this->isGeneratingExample[$questionIndex] = false;
        }
    }
    
    /**
     * 生成された解答例を使用する
     */
    public function useSuggestedExample(int $questionIndex): void
    {
        if (isset($this->suggestedExamples[$questionIndex])) {
            $this->answers[$questionIndex] = $this->suggestedExamples[$questionIndex];
            session(['goal_answers' => $this->answers]);
            // 使用後は解答例をクリア
            unset($this->suggestedExamples[$questionIndex]);
        }
    }

    public function nextQuestion(): void
    {
        // 現在の回答を確実に保存
        $this->saveCurrentAnswer();
        
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->currentQuestionIndex++;
        }
    }

    public function prevQuestion(): void
    {
        // 現在の回答を確実に保存
        $this->saveCurrentAnswer();
        
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    protected function saveCurrentAnswer(): void
    {
        // 配列のサイズを確認し、必要に応じて拡張
        while (count($this->answers) < count($this->questions)) {
            $this->answers[] = '';
        }
        
        // セッションに保存
        session(['goal_answers' => $this->answers]);
    }

    public function saveAnswers(): void
    {
        // 最後の回答をセッションに保存
        session(['goal_answers' => $this->answers]);
        
        // 空の質問のみの送信を防止
        $trimmed = array_map(fn($a) => trim($a), $this->answers);
        if (empty(array_filter($trimmed, fn($a) => $a !== ''))) {
            $this->addError('answers', '少なくとも1つの質問に回答してください。');
            return;
        }

        // セッションキャッシュをクリア（新しい回答で再生成するため）
        session()->forget('goal_questions');
        session()->forget('goal_answers');
        
        $this->candidates = $this->goalService->generateGoalCandidates($trimmed);
        $this->step = 'candidates';
    }

    public function backToQuestions(): void
    {
        $this->step = 'questions';
    }

    public function selectCandidate(int $index, ?string $edited = null): void
    {
        if (!isset($this->candidates[$index])) {
            return;
        }

        $text = $edited !== null ? trim($edited) : trim($this->candidates[$index]);
        if ($text === '') {
            return;
        }

        $this->goalService->updateGoalImage($text);
        $this->selectedGoal = $text;
        $this->currentGoal = $text;
        $this->displayMode = 'text';
        $this->step = 'completed';
        
        // 見直し日時を更新
        $mappingProgressService = app(\App\Services\MappingProgressService::class);
        $mappingProgressService->markItemAsReviewed(Auth::id(), 'my_goal');
        
        session()->flash('saved', 'マイゴールを更新しました');
    }

    public function setDisplayMode(string $mode): void
    {
        $mode = in_array($mode, ['text', 'image'], true) ? $mode : 'text';
        $user = Auth::user();
        if ($user) {
            $user->update(['goal_display_mode' => $mode]);
            $this->displayMode = $mode;
        }
    }

    public function generateGoalImage(): void
    {
        $goalText = $this->currentGoal ?? $this->selectedGoal;
        if (!$goalText) {
            return;
        }

        $url = $this->imageService->generateAndSave($goalText);
        if ($url) {
            $this->currentGoalImageUrl = $url;
            $this->displayMode = 'image';
            session()->flash('saved', '図式を生成しました');
        }
    }

    public function startEditingGoal(): void
    {
        $this->editingGoalText = $this->currentGoal ?? '';
        $this->isEditingGoal = true;
    }

    public function cancelEditingGoal(): void
    {
        $this->isEditingGoal = false;
        $this->editingGoalText = '';
    }

    public function saveEditedGoal(): void
    {
        $trimmed = trim($this->editingGoalText);
        if ($trimmed === '') {
            $this->addError('editingGoalText', 'ゴールイメージを入力してください。');
            return;
        }

        $this->goalService->updateGoalImage($trimmed);
        $this->currentGoal = $trimmed;
        $this->selectedGoal = $trimmed;
        $this->isEditingGoal = false;
        $this->editingGoalText = '';
        
        // 見直し日時を更新
        $mappingProgressService = app(\App\Services\MappingProgressService::class);
        $mappingProgressService->markItemAsReviewed(Auth::id(), 'my_goal');
        
        session()->flash('saved', 'ゴールイメージを更新しました');
    }

    public function render()
    {
        return view('livewire.my-goal');
    }
}