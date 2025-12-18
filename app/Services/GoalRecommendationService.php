<?php

namespace App\Services;

use App\Models\User;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoalRecommendationService
{
    public function __construct(
        private BedrockService $bedrockService,
        private ReflectionContextService $contextService
    ) {
    }

    /**
     * 診断・履歴を基に質問を生成（5-7件想定）
     */
    public function generateQuestions(): array
    {
        $context = $this->contextService->buildContextForUser();
        $prompt = $this->buildQuestionPrompt($context);

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if (empty($response)) {
                Log::warning('BedrockService returned empty response for goal questions');
                return $this->getDefaultQuestions();
            }

            $questions = $this->extractJson($response, 'questions');
            
            Log::info('Goal questions extraction', [
                'has_response' => !empty($response),
                'response_length' => strlen($response),
                'questions_count' => is_array($questions) ? count($questions) : 0,
            ]);

            if (is_array($questions) && !empty($questions)) {
                // 質問オブジェクトの配列をそのまま返す（ビューで使用するため）
                $formattedQuestions = array_map(function($q) {
                    if (is_array($q) && isset($q['question'])) {
                        // 既にオブジェクト形式
                        return [
                            'question' => $q['question'],
                            'example' => $q['example'] ?? ''
                        ];
                    } elseif (is_string($q) && !empty(trim($q))) {
                        // 文字列の場合はオブジェクト形式に変換
                        return ['question' => trim($q), 'example' => ''];
                    }
                    return null;
                }, array_filter($questions, fn($q) => !empty($q)));

                // 有効な質問が存在する場合のみ返す
                $validQuestions = array_filter($formattedQuestions, fn($q) => $q !== null && !empty($q['question']));
                if (!empty($validQuestions)) {
                    return array_values($validQuestions);
                }
            }

            // フォールバック: デフォルトの質問リストを返す（オブジェクト形式）
            Log::info('Using default questions for goal generation');
            return $this->getDefaultQuestions();
        } catch (\Throwable $e) {
            Log::error('Failed to generate goal questions', ['error' => $e->getMessage()]);
            return $this->getDefaultQuestions();
        }
    }

    /**
     * デフォルトの質問リストを返す（オブジェクト形式）
     */
    protected function getDefaultQuestions(): array
    {
        return [
            ['question' => 'あなたが将来実現したいことは何ですか？', 'example' => '例：自分らしく働き、充実した毎日を送りたい'],
            ['question' => 'あなたが大切にしている価値観は何ですか？', 'example' => '例：誠実さ、成長、人とのつながり'],
            ['question' => 'あなたが理想とする働き方はどのようなものですか？', 'example' => '例：柔軟な働き方ができ、自分の強みを活かせる環境'],
            ['question' => 'あなたが人生で達成したいことは何ですか？', 'example' => '例：専門性を高め、周囲の人に貢献できる存在になる'],
            ['question' => 'あなたが将来の自分に期待することは何ですか？', 'example' => '例：自分らしさを大切にしながら、成長し続けている'],
        ];
    }

    private function buildQuestionPrompt(string $context): string
    {
        $prompt = "あなたはキャリア伴走の専門家です。以下のユーザー情報を参考に、ゴールイメージを言語化するための質問を5-7個生成してください。各質問には簡潔な回答例も付けてください。\n\n";
        $prompt .= "【ユーザー情報】\n";
        if (!empty($context)) {
            $prompt .= $context . "\n";
        } else {
            $prompt .= "（まだデータが不足しています）\n";
        }
        $prompt .= "\n【質問の要件】\n";
        $prompt .= "- ユーザーが答えやすいように平易な日本語で\n";
        $prompt .= "- 未来7割、価値観・過去の経験3割を意識\n";
        $prompt .= "- 1問につき1行で質問、1-2行で回答例\n";
        $prompt .= "- 配列JSONで返す: {\"questions\":[{\"question\":\"...\",\"example\":\"...\"}]}\n";

        return $prompt;
    }

    /**
     * 回答を基にゴール候補を生成（3-5件想定）
     */
    public function generateGoalCandidates(array $answers): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return [];
        }

        $context = $this->contextService->buildContextForUser();
        $prompt = $this->buildGoalCandidatePrompt($context, $answers);

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            $candidates = $this->extractJson($response, 'candidates');

            if (is_array($candidates) && !empty($candidates)) {
                return array_filter($candidates, fn($c) => !empty(trim($c)));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate goal candidates', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }

        // フォールバック: 回答から簡易的なゴール候補を生成
        return $this->generateFallbackCandidates($answers);
    }

    private function buildGoalCandidatePrompt(string $context, array $answers): string
    {
        $answersText = implode("\n", array_map(function($i, $a) {
            return ($i + 1) . ". " . trim($a);
        }, array_keys($answers), $answers));

        $prompt = "あなたはキャリア伴走の専門家です。以下のユーザー情報と回答を基に、ゴールイメージ候補を3-5個生成してください。\n\n";
        $prompt .= "【ユーザー情報】\n";
        if (!empty($context)) {
            $prompt .= $context . "\n";
        } else {
            $prompt .= "（まだデータが不足しています）\n";
        }
        $prompt .= "\n【ユーザーの回答】\n";
        $prompt .= $answersText . "\n\n";
        $prompt .= "【ゴール候補の要件】\n";
        $prompt .= "- 各候補は3-4行の日本語テキスト\n";
        $prompt .= "- 未来7割、価値観3割のバランス\n";
        $prompt .= "- 決めつけず、ユーザーが選べる複数案\n";
        $prompt .= "- JSONで返す: {\"candidates\":[\"候補1\",\"候補2\",...]}\n";

        return $prompt;
    }

    private function extractJson(string $response, string $key)
    {
        // JSONブロック抽出
        if (preg_match('/\{.*\}/s', $response, $match)) {
            $json = json_decode($match[0], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json[$key])) {
                return $json[$key];
            }
        }

        // 念のため全文パースも試行
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json[$key])) {
            return $json[$key];
        }

        return null;
    }


    /**
     * フォールバック: 回答から簡易的なゴール候補を生成
     */
    protected function generateFallbackCandidates(array $answers): array
    {
        $filteredAnswers = array_filter($answers, fn($a) => !empty(trim($a)));
        
        if (empty($filteredAnswers)) {
            return [
                '自分らしく働き、充実した毎日を送る',
                '周囲の人と良好な関係を築きながら成長する',
                '自分の強みを活かして社会に貢献する',
            ];
        }

        $candidates = [];
        foreach (array_slice($filteredAnswers, 0, 5) as $answer) {
            $trimmed = trim($answer);
            if (!empty($trimmed)) {
                $candidates[] = $trimmed;
            }
        }

        // 3個未満の場合はデフォルトを追加
        while (count($candidates) < 3) {
            $candidates[] = '自分らしく働き、充実した毎日を送る';
        }

        return array_slice($candidates, 0, 5);
    }

    /**
     * ユーザーの入力内容から解答例を生成
     */
    public function generateAnswerExample(string $question, string $userInput): ?string
    {
        $context = $this->contextService->buildContextForUser();
        
        $prompt = "あなたはキャリア伴走の専門家です。以下の質問とユーザーの入力内容を基に、より具体的で充実した解答例を生成してください。\n\n";
        $prompt .= "【質問】\n{$question}\n\n";
        $prompt .= "【ユーザーの入力内容】\n{$userInput}\n\n";
        $prompt .= "【ユーザー情報】\n";
        if (!empty($context)) {
            $prompt .= $context . "\n";
        }
        $prompt .= "\n【要件】\n";
        $prompt .= "- ユーザーの入力内容を尊重し、それをより具体的に発展させた解答例を生成\n";
        $prompt .= "- 2-3行程度の具体的な内容\n";
        $prompt .= "- ユーザーの価値観や将来の展望を反映\n";
        $prompt .= "- 直接的な回答のみを返してください（説明文は不要）\n";

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                // 余分な説明文を除去して、回答のみを抽出
                $lines = explode("\n", trim($response));
                $answerLines = array_filter($lines, function($line) {
                    $trimmed = trim($line);
                    // 説明文っぽい行を除外
                    return !empty($trimmed) 
                        && !preg_match('/^(【|要件|質問|ユーザー|以上|参考|注意)/u', $trimmed)
                        && strlen($trimmed) > 10;
                });
                
                if (!empty($answerLines)) {
                    return implode("\n", array_slice($answerLines, 0, 3));
                }
                
                return trim($response);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate answer example', [
                'error' => $e->getMessage(),
                'question' => $question,
            ]);
        }

        return null;
    }

    /**
     * ゴールを保存
     */
    public function updateGoalImage(string $goalText): void
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return;
        }

        $user->update([
            'goal_image' => trim($goalText),
            'goal_display_mode' => $user->goal_display_mode ?? 'text',
        ]);
    }
}

