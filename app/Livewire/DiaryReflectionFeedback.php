<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diary;
use App\Services\BedrockService;
use App\Services\ReflectionContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiaryReflectionFeedback extends Component
{
    public $diaryId;
    public $feedback = null;
    public $isLoading = false;
    public $error = null;

    protected BedrockService $bedrockService;
    protected ReflectionContextService $contextService;

    public function boot()
    {
        $this->bedrockService = app(BedrockService::class);
        $this->contextService = app(ReflectionContextService::class);
    }

    public function mount($diaryId)
    {
        $this->diaryId = $diaryId;
    }

    public function generateFeedback()
    {
        $diary = Diary::where('user_id', Auth::id())
            ->where('id', $this->diaryId)
            ->first();

        if (!$diary || empty($diary->content)) {
            $this->error = '日記の内容が見つかりません。';
            return;
        }

        $this->isLoading = true;
        $this->error = null;

        try {
            // ユーザーのコンテキストを取得
            $context = $this->contextService->buildContextForUser();

            // 過去の日記を取得（比較用）
            $pastDiaries = Diary::where('user_id', Auth::id())
                ->where('id', '!=', $diary->id)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderByDesc('date')
                ->limit(5)
                ->get();

            // 連続記録日数を計算
            $streak = $this->calculateStreak(Auth::id());

            // プロンプトを構築
            $prompt = $this->buildFeedbackPrompt($diary, $context, $pastDiaries, $streak);

            // AIからフィードバックを生成
            // 会話履歴は空配列で、プロンプトを最初のuserメッセージとして渡す
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $this->feedback = $response;
            } else {
                Log::warning('DiaryReflectionFeedback: Failed to generate feedback', [
                    'diary_id' => $this->diaryId,
                    'prompt_length' => strlen($prompt),
                ]);
                $this->error = 'フィードバックの生成に失敗しました。もう一度お試しください。';
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate reflection feedback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'diary_id' => $this->diaryId,
            ]);
            $this->error = 'エラーが発生しました。しばらく時間をおいて再度お試しください。';
        } finally {
            $this->isLoading = false;
        }
    }

    protected function buildFeedbackPrompt(Diary $diary, string $context, $pastDiaries, int $streak = 0): string
    {
        // UTF-8として正規化（不正な文字を削除）
        $diaryContent = $this->sanitizeUtf8($diary->content);
        $context = $this->sanitizeUtf8($context);
        
        $prompt = "以下の日記内容に対して、内省を深めるためのフィードバックを提供してください。\n\n";
        $prompt .= "【日記内容】\n";
        $prompt .= "日付: {$diary->date->format('Y年m月d日')}\n";
        $prompt .= "モチベーション: {$diary->motivation}/100\n";
        $prompt .= "内容:\n{$diaryContent}\n\n";

        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考）】\n{$context}\n\n";
        }

        if ($pastDiaries->count() > 0) {
            $prompt .= "【過去の日記（比較用）】\n";
            foreach ($pastDiaries->take(3) as $pastDiary) {
                $pastContent = $this->sanitizeUtf8($pastDiary->content);
                $prompt .= "- {$pastDiary->date->format('Y年m月d日')}: " . mb_substr($pastContent, 0, 100) . "...\n";
            }
            $prompt .= "\n";
        }

        // 連続記録日数の情報を追加
        if ($streak > 0) {
            $streakMessage = match(true) {
                $streak >= 30 => "ユーザーは{$streak}日連続で日記を記録しています。これは素晴らしい継続力です。",
                $streak >= 14 => "ユーザーは{$streak}日連続で日記を記録しています。習慣化ができています。",
                $streak >= 7 => "ユーザーは{$streak}日連続で日記を記録しています。1週間続けられています。",
                default => "ユーザーは{$streak}日連続で日記を記録しています。",
            };
            $prompt .= "【記録状況】\n{$streakMessage}\n\n";
        }

        $prompt .= "【フィードバックのポイント】\n";
        $prompt .= "- 日記の内容に対して共感を示す\n";
        $prompt .= "- 気づきや成長のポイントを指摘する\n";
        $prompt .= "- 過去の日記と比較して変化や成長を感じられる点を伝える\n";
        if ($streak >= 3) {
            $prompt .= "- 連続記録日数に触れて、継続を褒める\n";
        }
        $prompt .= "- より深い内省を促す質問を1-2個含める\n";
        $prompt .= "- 励ましの言葉を含める\n";
        $prompt .= "- 簡潔で読みやすい文章（3-5文程度）\n\n";
        $prompt .= "フィードバックを生成してください:";

        // 最終的なプロンプトもUTF-8として正規化
        $prompt = $this->sanitizeUtf8($prompt);

        return $prompt;
    }

    /**
     * 連続記録日数を計算
     */
    protected function calculateStreak(int $userId): int
    {
        $diaries = Diary::where('user_id', $userId)
            ->orderByDesc('date')
            ->get()
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($diaries->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expectedDate = now()->format('Y-m-d');
        
        foreach ($diaries as $date) {
            if ($date === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * UTF-8文字列を正規化（不正な文字を削除）
     *
     * @param string $string
     * @return string
     */
    protected function sanitizeUtf8(string $string): string
    {
        // UTF-8として正規化
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        // 不正なUTF-8文字を削除
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        // 制御文字（改行・タブ以外）を削除
        $string = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $string);
        
        return $string;
    }

    public function render()
    {
        $diary = Diary::where('user_id', Auth::id())
            ->where('id', $this->diaryId)
            ->first();

        return view('livewire.diary-reflection-feedback', [
            'diary' => $diary,
        ]);
    }
}
