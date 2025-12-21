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
    public function generateMiniManual(int $userId, ?array $previousReport = null): array
    {
        // 診断結果を統合分析
        $diagnosisReport = $this->diagnosisService->analyzeDiagnoses($userId);
        $diagnosisReport['user_id'] = $userId;

        // 日記を分析（7日間）
        $diaryReport = $this->diaryService->analyzeDiaries($userId, 7);
        $diaryReport['user_id'] = $userId;

        // 持ち味レポコンテンツを構築（過去のレポを渡す）
        $manualContent = $this->buildManualContent($diagnosisReport, $diaryReport, $previousReport);

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
    protected function buildManualContent(array $diagnosisReport, array $diaryReport, ?array $previousReport = null): array
    {
        $content = [
            'title' => '私の持ち味レポ',
            'agenda' => '診断と日記から見えるあなたの持ち味',
            'strengths' => [],
            'changes' => [], // 変化要素を追加
        ];

        // 診断と日記のデータから特徴3点を生成（過去のレポを渡す）
        $userId = $diagnosisReport['user_id'] ?? ($diaryReport['user_id'] ?? null);
        $strengths = $this->generateStrengthsReport($diagnosisReport, $diaryReport, $previousReport, $userId);
        
        if (!empty($strengths)) {
            // 持ち味3つと変化要素を分離
            $content['strengths'] = array_slice($strengths, 0, 3); // 最初の3つが持ち味
            if (count($strengths) > 3) {
                $content['changes'] = array_slice($strengths, 3); // 残りが変化要素
            }
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
     * 診断と日記から特徴3点を生成（変化要素も含む）
     */
    protected function generateStrengthsReport(array $diagnosisReport, array $diaryReport, ?array $previousReport = null, ?int $userId = null): array
    {
        // タイムアウト設定（120秒）
        set_time_limit(120);

        // データがない場合は空配列を返す
        if (!$diagnosisReport['has_data'] && !$diaryReport['has_data']) {
            return [];
        }

        try {
            // 既存の持ち味3点を生成
            $strengths = $this->generateCurrentStrengths($diagnosisReport, $diaryReport);
            
            // 2回目以降の場合、変化要素も生成
            if ($previousReport !== null && $userId !== null) {
                // 前回のレポ生成日時を取得
                $previousDate = isset($previousReport['generated_at']) 
                    ? (is_string($previousReport['generated_at']) 
                        ? new \DateTime($previousReport['generated_at'])
                        : $previousReport['generated_at'])
                    : null;
                
                // データ変化があるかチェック
                if ($previousDate && $this->hasDataChanges($userId, $previousDate)) {
                    // 変化がある場合は通常の変化要素3つを生成
                    $changes = $this->generateChangesReport($diagnosisReport, $diaryReport, $previousReport);
                    return array_merge($strengths, $changes);
                } else if ($previousDate) {
                    // 変化がない場合は「変化が少ない期間」メッセージを1つ生成
                    $changes = $this->generateNoChangesMessage($diagnosisReport, $diaryReport, $previousReport);
                    return array_merge($strengths, $changes);
                }
            }
            
            return $strengths;
        } catch (\Exception $e) {
            // タイムアウトやその他のエラーをログに記録
            Log::error('Failed to generate strengths report', [
                'error' => $e->getMessage(),
                'timeout' => strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'Maximum execution time') !== false,
            ]);
            
            // タイムアウトの場合は空配列を返す（フォールバックメッセージを表示）
            return [];
        }
    }

    /**
     * 現在の持ち味3点を生成
     */
    protected function generateCurrentStrengths(array $diagnosisReport, array $diaryReport): array
    {
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
            Log::warning('Failed to generate current strengths', [
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

    /**
     * 前回のレポ生成以降にデータ変化があるかチェック
     */
    protected function hasDataChanges(?int $userId, \DateTime $previousReportDate): bool
    {
        if (!$userId) {
            return false;
        }

        // 前回のレポ生成日時以降の新しい診断があるか
        $newDiagnoses = \App\Models\Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('created_at', '>', $previousReportDate)
            ->exists();
        
        // 前回のレポ生成日時以降の新しい日記があるか
        $newDiaries = \App\Models\Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->where('date', '>', $previousReportDate->format('Y-m-d'))
            ->exists();
        
        // 前回のレポ生成日時以降の新しいマイルストーン完了があるか
        $newMilestones = \App\Models\CareerMilestone::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('completed_at', '>', $previousReportDate)
            ->exists();
        
        return $newDiagnoses || $newDiaries || $newMilestones;
    }

    /**
     * 変化要素3つを生成
     */
    protected function generateChangesReport(array $diagnosisReport, array $diaryReport, array $previousReport): array
    {
        $prompt = $this->buildChangesPrompt($diagnosisReport, $diaryReport, $previousReport);
        
        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );
            
            if ($response) {
                $parsed = $this->parseStrengthsResponse($response);
                // 3つに制限
                return array_slice($parsed, 0, 3);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate changes report', [
                'error' => $e->getMessage(),
            ]);
        }
        
        return [];
    }

    /**
     * データ変化がない場合の「変化が少ない期間」メッセージを生成
     */
    protected function generateNoChangesMessage(array $diagnosisReport, array $diaryReport, array $previousReport): array
    {
        $prompt = $this->buildNoChangesPrompt($diagnosisReport, $diaryReport, $previousReport);
        
        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );
            
            if ($response) {
                $parsed = $this->parseStrengthsResponse($response);
                // 1つだけ返す（配列の最初の要素）
                return !empty($parsed) ? [array_shift($parsed)] : [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate no changes message', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // フォールバック: デフォルトメッセージ
        return [[
            'title' => 'この期間は落ち着いた時間を過ごせました',
            'description' => '前回の持ち味レポ生成以降、大きな変化は見られませんでしたが、あなたの持ち味はしっかりと維持されています。日記や診断を継続的に記録していくと、さらに変化や成長が見られるようになりますよ。継続は力です。',
        ]];
    }

    /**
     * 変化要素生成用プロンプトを構築
     */
    protected function buildChangesPrompt(array $diagnosisReport, array $diaryReport, array $previousReport): string
    {
        $prompt = "前回の持ち味レポ（n回目）と今回のデータ（n+1回目）を比較して、ユーザーの変化要素3つを生成してください。\n\n";
        
        $prompt .= "【前回の持ち味レポ（n回目）】\n";
        $prompt .= "タイトル: " . ($previousReport['content']['title'] ?? '私の持ち味レポ') . "\n";
        $prompt .= "アジェンダ: " . ($previousReport['content']['agenda'] ?? '診断と日記から見えるあなたの持ち味') . "\n";
        $prompt .= "持ち味:\n";
        if (!empty($previousReport['content']['strengths'])) {
            foreach ($previousReport['content']['strengths'] as $index => $strength) {
                $prompt .= ($index + 1) . ". " . ($strength['title'] ?? '') . ": " . ($strength['description'] ?? '') . "\n";
            }
        }
        
        $prompt .= "\n【前回の診断結果（n回目）】\n";
        if (!empty($previousReport['diagnosis_report']['report']['summary'])) {
            $prompt .= $previousReport['diagnosis_report']['report']['summary'] . "\n";
        }
        if (!empty($previousReport['diagnosis_report']['report']['ai_summary'])) {
            $prompt .= $previousReport['diagnosis_report']['report']['ai_summary'] . "\n";
        }
        
        $prompt .= "\n【前回の日記分析（n回目）】\n";
        if (!empty($previousReport['diary_report']['ai_analysis'])) {
            $prompt .= $previousReport['diary_report']['ai_analysis'] . "\n";
        }
        
        $prompt .= "\n【今回の診断結果（n+1回目）】\n";
        if ($diagnosisReport['has_data']) {
            if (!empty($diagnosisReport['report']['summary'])) {
                $prompt .= $diagnosisReport['report']['summary'] . "\n";
            }
            if (!empty($diagnosisReport['report']['ai_summary'])) {
                $prompt .= $diagnosisReport['report']['ai_summary'] . "\n";
            }
        }
        
        $prompt .= "\n【今回の日記分析（n+1回目）】\n";
        if ($diaryReport['has_data']) {
            if (!empty($diaryReport['ai_analysis'])) {
                $prompt .= $diaryReport['ai_analysis'] . "\n";
            }
        }
        
        $prompt .= "\n【出力形式】\n";
        $prompt .= "以下の形式で、前回からの変化要素3つを生成してください。\n";
        $prompt .= "変化要素は、持ち味の変化だけでなく、以下のような様々な変化を含めることができます：\n";
        $prompt .= "- 持ち味の変化\n";
        $prompt .= "- マイルストーンを完了することができた\n";
        $prompt .= "- 感情の変化（ポジティブな感情が増えたなど）\n";
        $prompt .= "- 話題の変化（家族に関する話題が増えたなど）\n";
        $prompt .= "- 行動の変化（ログインする回数が増えたなど）\n";
        $prompt .= "- その他の成長や変化\n\n";
        $prompt .= "各説明は200文字程度で、具体的で励ましの言葉を含めてください。\n\n";
        $prompt .= "・[変化要素1のタイトル]\n";
        $prompt .= "[変化要素1の説明（200文字程度）]\n\n";
        $prompt .= "・[変化要素2のタイトル]\n";
        $prompt .= "[変化要素2の説明（200文字程度）]\n\n";
        $prompt .= "・[変化要素3のタイトル]\n";
        $prompt .= "[変化要素3の説明（200文字程度）]\n\n";
        $prompt .= "変化要素を生成してください:";
        
        return $prompt;
    }

    /**
     * データ変化がない場合のプロンプトを構築
     */
    protected function buildNoChangesPrompt(array $diagnosisReport, array $diaryReport, array $previousReport): string
    {
        $prompt = "前回の持ち味レポ生成以降、新しい診断や日記の記録が少ない期間でした。\n\n";
        
        $prompt .= "【前回の持ち味レポ】\n";
        $prompt .= "タイトル: " . ($previousReport['content']['title'] ?? '私の持ち味レポ') . "\n";
        $prompt .= "アジェンダ: " . ($previousReport['content']['agenda'] ?? '診断と日記から見えるあなたの持ち味') . "\n";
        $prompt .= "持ち味:\n";
        if (!empty($previousReport['content']['strengths'])) {
            foreach ($previousReport['content']['strengths'] as $index => $strength) {
                $prompt .= ($index + 1) . ". " . ($strength['title'] ?? '') . ": " . ($strength['description'] ?? '') . "\n";
            }
        }
        
        $prompt .= "\n【今回の診断結果】\n";
        if ($diagnosisReport['has_data'] && !empty($diagnosisReport['report']['summary'])) {
            $prompt .= $diagnosisReport['report']['summary'] . "\n";
        } else {
            $prompt .= "診断結果は前回とほぼ同じです。\n";
        }
        
        $prompt .= "\n【今回の日記分析】\n";
        if ($diaryReport['has_data'] && !empty($diaryReport['ai_analysis'])) {
            $prompt .= $diaryReport['ai_analysis'] . "\n";
        } else {
            $prompt .= "日記の記録が少ない期間でした。\n";
        }
        
        $prompt .= "\n【出力形式】\n";
        $prompt .= "以下の形式で、「変化が少ない期間でした」という旨を伝えつつ、\n";
        $prompt .= "「日記や診断を継続していくと変化が見られますよ」という丁寧かつ励ましのメッセージを含む変化要素を1つ生成してください。\n\n";
        $prompt .= "・[タイトル（例：「この期間は落ち着いた時間を過ごせました」）]\n";
        $prompt .= "[説明（200文字程度）]\n";
        $prompt .= "- 前回の持ち味を維持していることを肯定的に伝える\n";
        $prompt .= "- 継続的な入力（日記、診断）によって変化が見られるようになることを励ましの言葉で伝える\n";
        $prompt .= "- 丁寧で前向きなトーンを保つ\n\n";
        $prompt .= "変化要素を生成してください:";
        
        return $prompt;
    }
}

