<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\PersonalityAssessment;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Log;

class DiagnosisIntegrationService
{
    protected BedrockService $bedrockService;

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * ユーザーの全診断結果を統合分析
     */
    public function analyzeDiagnoses(int $userId): array
    {
        // 完了済みの診断を取得
        $diagnoses = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->orderByDesc('created_at')
            ->get();

        // 自己診断結果を取得
        $assessments = PersonalityAssessment::where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->get();

        if ($diagnoses->isEmpty() && $assessments->isEmpty()) {
            return [
                'has_data' => false,
                'summary' => '診断結果がまだありません。',
            ];
        }

        // 統合レポートを生成
        $report = $this->generateIntegrationReport($diagnoses, $assessments);

        return [
            'has_data' => true,
            'diagnosis_count' => $diagnoses->count(),
            'assessment_count' => $assessments->count(),
            'report' => $report,
        ];
    }

    /**
     * 統合レポート生成
     */
    protected function generateIntegrationReport($diagnoses, $assessments): array
    {
        $report = [
            'summary' => '',
            'key_insights' => [],
            'strengths' => [],
            'values' => [],
        ];

        // 診断結果から主要な情報を抽出
        $workScores = [];
        $lifeScores = [];
        $assessmentTypes = [];

        foreach ($diagnoses as $diagnosis) {
            if ($diagnosis->work_score !== null) {
                $workScores[] = $diagnosis->work_score;
            }
            if ($diagnosis->life_score !== null) {
                $lifeScores[] = $diagnosis->life_score;
            }
        }

        foreach ($assessments as $assessment) {
            $assessmentTypes[] = $assessment->assessment_type;
        }

        // 簡易的な統合分析（本質のみ）
        if (!empty($workScores) || !empty($lifeScores)) {
            $avgWorkScore = !empty($workScores) ? round(array_sum($workScores) / count($workScores)) : null;
            $avgLifeScore = !empty($lifeScores) ? round(array_sum($lifeScores) / count($lifeScores)) : null;

            if ($avgWorkScore !== null && $avgLifeScore !== null) {
                $report['summary'] = "仕事の満足度: {$avgWorkScore}点、生活の満足度: {$avgLifeScore}点";
                
                if ($avgWorkScore >= 70 && $avgLifeScore >= 70) {
                    $report['key_insights'][] = '仕事と生活のバランスが良好です。';
                } elseif ($avgWorkScore < 50 || $avgLifeScore < 50) {
                    $report['key_insights'][] = '改善の余地がある領域が見つかりました。';
                }
            }
        }

        // 自己診断結果から強みを抽出
        foreach ($assessments as $assessment) {
            $resultData = $assessment->result_data ?? [];
            
            if ($assessment->assessment_type === 'mbti' && isset($resultData['mbti_type'])) {
                $report['strengths'][] = "MBTIタイプ: {$resultData['mbti_type']}";
            }
            
            if ($assessment->assessment_type === 'strengthsfinder' && isset($resultData['strengths_top5'])) {
                $top5 = $resultData['strengths_top5'];
                if (is_array($top5) && !empty($top5)) {
                    $strengthsList = array_filter($top5);
                    if (!empty($strengthsList)) {
                        $report['strengths'][] = 'トップ5の強み: ' . implode('、', $strengthsList);
                    }
                }
            }
        }

        // AIを使用して自然な文章で統合解釈を生成（オプション）
        if (!empty($report['key_insights']) || !empty($report['strengths'])) {
            try {
                $aiSummary = $this->generateAISummary($report);
                if ($aiSummary) {
                    $report['ai_summary'] = $aiSummary;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to generate AI summary for diagnosis integration', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $report;
    }

    /**
     * AIを使用して統合解釈を生成
     */
    protected function generateAISummary(array $report): ?string
    {
        $prompt = "以下の診断結果を統合して、ユーザーの本質的な特徴を簡潔に（3-5文程度）まとめてください。\n\n";
        
        if (!empty($report['summary'])) {
            $prompt .= "【診断結果】\n{$report['summary']}\n\n";
        }
        
        if (!empty($report['key_insights'])) {
            $prompt .= "【主要な気づき】\n" . implode("\n", $report['key_insights']) . "\n\n";
        }
        
        if (!empty($report['strengths'])) {
            $prompt .= "【強み】\n" . implode("\n", $report['strengths']) . "\n\n";
        }
        
        $prompt .= "簡潔で励ましの言葉を含む統合解釈を生成してください:";

        $response = $this->bedrockService->chat(
            $prompt,
            [],
            config('bedrock.reflection_system_prompt')
        );

        return $response;
    }
}

