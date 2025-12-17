<?php

namespace App\Services;

use App\Services\DiagnosisIntegrationService;
use App\Services\DiaryAnalysisService;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Log;

class MiniManualGeneratorService
{
    protected DiagnosisIntegrationService $diagnosisService;
    protected DiaryAnalysisService $diaryService;
    protected BedrockService $bedrockService;

    public function __construct(
        DiagnosisIntegrationService $diagnosisService,
        DiaryAnalysisService $diaryService,
        BedrockService $bedrockService
    ) {
        $this->diagnosisService = $diagnosisService;
        $this->diaryService = $diaryService;
        $this->bedrockService = $bedrockService;
    }

    /**
     * 持ち味レポを生成
     */
    public function generateMiniManual(int $userId): array
    {
        // 診断結果を統合分析
        $diagnosisReport = $this->diagnosisService->analyzeDiagnoses($userId);

        // 日記を分析（7日間）
        $diaryReport = $this->diaryService->analyzeDiaries($userId, 7);

        // 持ち味レポコンテンツを構築
        $manualContent = $this->buildManualContent($diagnosisReport, $diaryReport);

        return [
            'user_id' => $userId,
            'generated_at' => now(),
            'content' => $manualContent,
            'diagnosis_report' => $diagnosisReport,
            'diary_report' => $diaryReport,
        ];
    }

    /**
     * 持ち味レポコンテンツを構築
     */
    protected function buildManualContent(array $diagnosisReport, array $diaryReport): array
    {
        $content = [
            'title' => '私の持ち味レポ',
            'agenda' => '診断と日記から見えるあなたの持ち味',
            'strengths' => [],
        ];

        // 診断と日記のデータから特徴3点を生成
        $strengths = $this->generateStrengthsReport($diagnosisReport, $diaryReport);
        
        if (!empty($strengths)) {
            $content['strengths'] = $strengths;
        } else {
            // データがない場合のフォールバック
            $content['strengths'] = [
                [
                    'title' => 'データがまだ不足しています',
                    'description' => '診断と日記の記録を続けることで、あなたの持ち味がより明確になります。',
                ],
            ];
        }

        return $content;
    }

    /**
     * 診断と日記から特徴3点を生成
     */
    protected function generateStrengthsReport(array $diagnosisReport, array $diaryReport): array
    {
        // データがない場合は空配列を返す
        if (!$diagnosisReport['has_data'] && !$diaryReport['has_data']) {
            return [];
        }

        // プロンプトを構築
        $prompt = $this->buildStrengthsPrompt($diagnosisReport, $diaryReport);

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
            Log::warning('Failed to generate strengths report', [
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * 特徴生成用のプロンプトを構築
     */
    protected function buildStrengthsPrompt(array $diagnosisReport, array $diaryReport): string
    {
        $prompt = "診断と日記から見えるユーザーの持ち味を、特徴3点とその説明（各200文字程度）で生成してください。\n\n";

        // 診断結果の情報を追加
        if ($diagnosisReport['has_data']) {
            $prompt .= "【診断結果】\n";
            
            if (!empty($diagnosisReport['report']['summary'])) {
                $prompt .= "{$diagnosisReport['report']['summary']}\n";
            }
            
            if (!empty($diagnosisReport['report']['ai_summary'])) {
                $prompt .= "{$diagnosisReport['report']['ai_summary']}\n";
            }
            
            if (!empty($diagnosisReport['report']['key_insights'])) {
                $prompt .= "主要な気づき:\n";
                foreach ($diagnosisReport['report']['key_insights'] as $insight) {
                    $prompt .= "- {$insight}\n";
                }
            }
            
            if (!empty($diagnosisReport['report']['strengths'])) {
                $prompt .= "強み:\n";
                foreach ($diagnosisReport['report']['strengths'] as $strength) {
                    $prompt .= "- {$strength}\n";
                }
            }
            
            $prompt .= "\n";
        }

        // 日記分析結果の情報を追加
        if ($diaryReport['has_data']) {
            $prompt .= "【日記分析結果】\n";
            
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
        $prompt .= "以下の形式で、特徴3点とその説明を生成してください。各説明は200文字程度で、具体的で励ましの言葉を含めてください。\n\n";
        $prompt .= "・[特徴1のタイトル]\n";
        $prompt .= "[特徴1の説明（200文字程度）]\n\n";
        $prompt .= "・[特徴2のタイトル]\n";
        $prompt .= "[特徴2の説明（200文字程度）]\n\n";
        $prompt .= "・[特徴3のタイトル]\n";
        $prompt .= "[特徴3の説明（200文字程度）]\n\n";
        $prompt .= "特徴を生成してください:";

        return $prompt;
    }

    /**
     * AIレスポンスをパースして特徴3点を抽出
     */
    protected function parseStrengthsResponse(string $response): array
    {
        $strengths = [];
        
        // 「・」で始まる行を特徴のタイトルとして抽出
        $lines = explode("\n", $response);
        $currentTitle = null;
        $currentDescription = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 空行はスキップ
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
                $currentTitle = mb_substr($line, 1); // 「・」を除去
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

