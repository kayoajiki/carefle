<?php

namespace App\Services;

use App\Models\User;
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

            $questions = $this->extractJson($response, 'questions');

            return is_array($questions) ? $questions : [];
        } catch (\Throwable $e) {
            Log::error('Failed to generate goal questions', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
    * 回答を基にゴール候補を生成（3-5件想定）
    */
    public function generateGoalCandidates(array $answers): array
    {
        $context = $this->contextService->buildContextForUser();
        $prompt = $this->buildGoalCandidatePrompt($context, $answers);

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            $candidates = $this->extractJson($response, 'candidates');

            return is_array($candidates) ? $candidates : [];
        } catch (\Throwable $e) {
            Log::error('Failed to generate goal candidates', ['error' => $e->getMessage()]);
            return [];
        }
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
            'goal_image' => $goalText,
            'goal_display_mode' => $user->goal_display_mode ?? 'text',
        ]);
    }

    private function buildQuestionPrompt(string $context): string
    {
        return <<<PROMPT
あなたはキャリア伴走の専門家です。以下のユーザー情報を参考に、ゴールイメージを言語化するための質問を5-7個生成してください。各質問には簡潔な回答例も付けてください。

【ユーザー情報】
{$context}

【質問の要件】
- ユーザーが答えやすいように平易な日本語で
- 未来7割、価値観・過去の経験3割を意識
- 1問につき1行で質問、1-2行で回答例
- 配列JSONで返す: {"questions":[{"question":"...","example":"..."}]}
PROMPT;
    }

    private function buildGoalCandidatePrompt(string $context, array $answers): string
    {
        $answersText = json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
あなたはキャリア伴走の専門家です。以下のユーザー情報と回答を基に、ゴールイメージ候補を3-5個生成してください。

【ユーザー情報】
{$context}

【ユーザーの回答】
{$answersText}

【ゴール候補の要件】
- 各候補は3-4行の日本語テキスト
- 未来7割、価値観3割のバランス
- 決めつけず、ユーザーが選べる複数案
- JSONで返す: {"candidates":["候補1","候補2",...]}
PROMPT;
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
}


