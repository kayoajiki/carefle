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

            // プロンプトを構築
            $prompt = $this->buildFeedbackPrompt($diary, $context, $pastDiaries);

            // AIからフィードバックを生成
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $this->feedback = $response;
            } else {
                $this->error = 'フィードバックの生成に失敗しました。';
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate reflection feedback', [
                'error' => $e->getMessage(),
                'diary_id' => $this->diaryId,
            ]);
            $this->error = 'エラーが発生しました。しばらく時間をおいて再度お試しください。';
        } finally {
            $this->isLoading = false;
        }
    }

    protected function buildFeedbackPrompt(Diary $diary, string $context, $pastDiaries): string
    {
        $prompt = "以下の日記内容に対して、内省を深めるためのフィードバックを提供してください。\n\n";
        $prompt .= "【日記内容】\n";
        $prompt .= "日付: {$diary->date->format('Y年m月d日')}\n";
        $prompt .= "モチベーション: {$diary->motivation}/100\n";
        $prompt .= "内容:\n{$diary->content}\n\n";

        if (!empty($context)) {
            $prompt .= "【ユーザーの背景情報（参考）】\n{$context}\n\n";
        }

        if ($pastDiaries->count() > 0) {
            $prompt .= "【過去の日記（比較用）】\n";
            foreach ($pastDiaries->take(3) as $pastDiary) {
                $prompt .= "- {$pastDiary->date->format('Y年m月d日')}: " . substr($pastDiary->content, 0, 100) . "...\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "【フィードバックのポイント】\n";
        $prompt .= "- 日記の内容に対して共感を示す\n";
        $prompt .= "- 気づきや成長のポイントを指摘する\n";
        $prompt .= "- 過去の日記と比較して変化や成長を感じられる点を伝える\n";
        $prompt .= "- より深い内省を促す質問を1-2個含める\n";
        $prompt .= "- 励ましの言葉を含める\n";
        $prompt .= "- 簡潔で読みやすい文章（3-5文程度）\n\n";
        $prompt .= "フィードバックを生成してください:";

        return $prompt;
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

