<?php

namespace App\Services;

use App\Services\ContextDetectionService;
use App\Services\DiaryAnalysisService;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Log;

class ContextualManualGeneratorService
{
    protected ContextDetectionService $contextService;
    protected DiaryAnalysisService $diaryService;
    protected BedrockService $bedrockService;

    public function __construct(
        ContextDetectionService $contextService,
        DiaryAnalysisService $diaryService,
        BedrockService $bedrockService
    ) {
        $this->contextService = $contextService;
        $this->diaryService = $diaryService;
        $this->bedrockService = $bedrockService;
    }

    /**
     * コンテキスト別取説を生成
     * 
     * @param int $userId ユーザーID
     * @param string $context コンテキスト（work, family, hobby, etc.）
     * @return array|null 生成された取説データ、生成できない場合はnull
     */
    public function generateContextualManual(int $userId, string $context): ?array
    {
        // コンテキストが生成可能かチェック
        if (!$this->contextService->canGenerateContextualManual($userId, $context, 5)) {
            return null;
        }

        // コンテキスト別の日記を取得
        $diaries = $this->contextService->getDiariesByContext($userId, $context);
        
        if ($diaries->isEmpty()) {
            return null;
        }

        // 日記を分析（コンテキスト特化）
        $diaryReport = $this->analyzeContextualDiaries($diaries, $context);

        // コンテキスト別取説コンテンツを構築
        $manualContent = $this->buildContextualManualContent($diaryReport, $context);

        return [
            'user_id' => $userId,
            'context' => $context,
            'context_label' => ContextDetectionService::getContextLabel($context),
            'generated_at' => now(),
            'content' => $manualContent,
            'diary_report' => $diaryReport,
        ];
    }

    /**
     * コンテキスト別の日記を分析
     */
    protected function analyzeContextualDiaries($diaries, string $context): array
    {
        $result = [
            'has_data' => true,
            'diary_count' => $diaries->count(),
            'context' => $context,
            'context_label' => ContextDetectionService::getContextLabel($context),
            'values_and_strengths' => [],
            'ai_analysis' => null,
        ];

        // 価値観と強みを抽出（簡易版）
        $valuesAndStrengths = $this->extractContextualValuesAndStrengths($diaries);
        $result['values_and_strengths'] = $valuesAndStrengths;

        // AIを使用して統合分析
        try {
            $result['ai_analysis'] = $this->generateContextualAIAnalysis($diaries, $context);
        } catch (\Exception $e) {
            Log::warning('Failed to generate contextual AI analysis', [
                'error' => $e->getMessage(),
                'context' => $context,
            ]);
        }

        return $result;
    }

    /**
     * コンテキスト別の価値観と強みを抽出
     */
    protected function extractContextualValuesAndStrengths($diaries): array
    {
        $result = [
            'values' => [],
            'strengths' => [],
            'themes' => [],
        ];

        // モチベーションの平均を計算
        $motivations = $diaries->pluck('motivation')->filter()->toArray();
        if (!empty($motivations)) {
            $avgMotivation = round(array_sum($motivations) / count($motivations));
            if ($avgMotivation >= 70) {
                $result['strengths'][] = 'このコンテキストで高いモチベーションを維持できています';
            }
        }

        // 日記の内容から頻出キーワードを抽出
        $allContent = $diaries->pluck('content')->filter()->implode(' ');
        $keywords = $this->extractKeywords($allContent);
        if (!empty($keywords)) {
            $result['themes'] = array_slice($keywords, 0, 5);
        }

        return $result;
    }

    /**
     * キーワードを抽出（簡易版）
     */
    protected function extractKeywords(string $text): array
    {
        $stopWords = ['の', 'に', 'は', 'を', 'が', 'と', 'で', 'も', 'から', 'まで', 'より', 'など', 'こと', 'もの', 'ため'];
        
        $words = preg_split('/[\s、。！？\n\r]+/u', $text);
        $words = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) >= 2 && !in_array($word, $stopWords);
        });

        $wordCounts = array_count_values($words);
        arsort($wordCounts);

        return array_keys(array_slice($wordCounts, 0, 10));
    }

    /**
     * AIを使用してコンテキスト別の統合分析を生成
     */
    protected function generateContextualAIAnalysis($diaries, string $context): ?string
    {
        $contextLabel = ContextDetectionService::getContextLabel($context);
        
        $prompt = "以下の{$diaries->count()}件の日記は、すべて「{$contextLabel}」に関する内容です。\n\n";
        $prompt .= "これらの日記を分析して、このコンテキストにおけるユーザーの価値観、強み、成長ポイントを簡潔に（3-5文程度）まとめてください。\n\n";

        foreach ($diaries as $index => $diary) {
            $content = mb_substr($diary->content, 0, 200);
            $prompt .= "【日記" . ($index + 1) . "】{$diary->date->format('n月j日')}\n";
            $prompt .= "モチベーション: {$diary->motivation}/100\n";
            $prompt .= "内容: {$content}...\n\n";
        }

        $prompt .= "このコンテキスト（{$contextLabel}）における本質的な価値観、強み、成長ポイントを簡潔にまとめてください:";

        $response = $this->bedrockService->chat(
            $prompt,
            [],
            config('bedrock.reflection_system_prompt')
        );

        return $response;
    }

    /**
     * コンテキスト別取説コンテンツを構築
     */
    protected function buildContextualManualContent(array $diaryReport, string $context): array
    {
        $contextLabel = ContextDetectionService::getContextLabel($context);
        
        $content = [
            'title' => "私の{$contextLabel}の持ち味レポ",
            'agenda' => "{$contextLabel}の日記から見えるあなたの持ち味",
            'strengths' => [],
        ];

        // コンテキスト別の特徴3点を生成
        $strengths = $this->generateContextualStrengths($diaryReport, $context);
        
        if (!empty($strengths)) {
            $content['strengths'] = $strengths;
        } else {
            // データがない場合のフォールバック
            $content['strengths'] = [
                [
                    'title' => 'データがまだ不足しています',
                    'description' => "{$contextLabel}に関する日記の記録を続けることで、あなたの持ち味がより明確になります。",
                ],
            ];
        }

        return $content;
    }

    /**
     * コンテキスト別の特徴3点を生成
     */
    protected function generateContextualStrengths(array $diaryReport, string $context): array
    {
        if (!$diaryReport['has_data']) {
            return [];
        }

        $contextLabel = ContextDetectionService::getContextLabel($context);
        
        // プロンプトを構築
        $prompt = $this->buildContextualStrengthsPrompt($diaryReport, $context);

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                return $this->parseStrengthsResponse($response);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to generate contextual strengths report', [
                'error' => $e->getMessage(),
                'context' => $context,
            ]);
        }

        return [];
    }

    /**
     * コンテキスト別特徴生成用のプロンプトを構築
     */
    protected function buildContextualStrengthsPrompt(array $diaryReport, string $context): string
    {
        $contextLabel = ContextDetectionService::getContextLabel($context);
        
        $prompt = "{$contextLabel}に関する日記から見えるユーザーの持ち味を、特徴3点とその説明（各200文字程度）で生成してください。\n\n";

        // 日記分析結果の情報を追加
        if ($diaryReport['has_data']) {
            $prompt .= "【{$contextLabel}に関する日記分析結果】\n";
            
            if (!empty($diaryReport['ai_analysis'])) {
                $prompt .= "{$diaryReport['ai_analysis']}\n";
            }
            
            if (!empty($diaryReport['values_and_strengths']['strengths'])) {
                $prompt .= "日記から見える強み:\n";
                foreach ($diaryReport['values_and_strengths']['strengths'] as $strength) {
                    $prompt .= "- {$strength}\n";
                }
            }
            
            if (!empty($diaryReport['values_and_strengths']['themes'])) {
                $prompt .= "よく出てくるテーマ:\n";
                foreach ($diaryReport['values_and_strengths']['themes'] as $theme) {
                    $prompt .= "- {$theme}\n";
                }
            }
            
            $prompt .= "\n";
        }

        $prompt .= "【出力形式】\n";
        $prompt .= "以下の形式で、{$contextLabel}における特徴3点とその説明を生成してください。各説明は200文字程度で、具体的で励ましの言葉を含めてください。\n\n";
        $prompt .= "・[特徴1のタイトル]\n";
        $prompt .= "[特徴1の説明（200文字程度）]\n\n";
        $prompt .= "・[特徴2のタイトル]\n";
        $prompt .= "[特徴2の説明（200文字程度）]\n\n";
        $prompt .= "・[特徴3のタイトル]\n";
        $prompt .= "[特徴3の説明（200文字程度）]\n\n";
        $prompt .= "{$contextLabel}における特徴を生成してください:";

        return $prompt;
    }

    /**
     * AIレスポンスをパースして特徴3点を抽出
     */
    protected function parseStrengthsResponse(string $response): array
    {
        $strengths = [];
        
        $lines = explode("\n", $response);
        $currentTitle = null;
        $currentDescription = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // 「・」で始まる行は新しい特徴のタイトル
            if (mb_substr($line, 0, 1) === '・' || mb_substr($line, 0, 1) === '•') {
                // 前の特徴を保存
                if ($currentTitle !== null && !empty($currentDescription)) {
                    $strengths[] = [
                        'title' => $currentTitle,
                        'description' => implode("\n", $currentDescription),
                    ];
                }
                
                // 新しい特徴を開始
                $currentTitle = mb_substr($line, 1);
                $currentDescription = [];
            } else {
                // 説明文として追加
                if ($currentTitle !== null) {
                    $currentDescription[] = $line;
                }
            }
        }
        
        // 最後の特徴を保存
        if ($currentTitle !== null && !empty($currentDescription)) {
            $strengths[] = [
                'title' => $currentTitle,
                'description' => implode("\n", $currentDescription),
            ];
        }
        
        // 3点に制限
        return array_slice($strengths, 0, 3);
    }
}

