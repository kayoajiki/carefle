<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\Diary;
use App\Services\UserMappingService;
use Illuminate\Support\Facades\Log;

class TransformationTrackingService
{
    protected UserMappingService $mappingService;

    public function __construct(UserMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * 過去の自分と現在の自分を比較して変容ポイントを抽出
     */
    public function comparePastAndCurrent(int $userId): array
    {
        $past = $this->mappingService->generatePastMapping($userId);
        $current = $this->mappingService->generateCurrentMapping($userId);

        $transformations = [];

        // 診断結果の比較
        if (isset($past['items']['past_diagnosis']) && isset($current['items']['current_diagnosis'])) {
            $pastDiagnosis = $past['items']['past_diagnosis']['data'][0] ?? null;
            $currentDiagnosis = $current['items']['current_diagnosis']['data'];
            
            if ($pastDiagnosis && $currentDiagnosis) {
                $workScoreDiff = $currentDiagnosis['work_score'] - $pastDiagnosis['work_score'];
                $lifeScoreDiff = $currentDiagnosis['life_score'] - $pastDiagnosis['life_score'];
                
                if (abs($workScoreDiff) >= 5 || abs($lifeScoreDiff) >= 5) {
                    $transformations[] = [
                        'type' => 'diagnosis',
                        'title' => '満足度の変化',
                        'description' => $this->generateDiagnosisTransformationDescription($workScoreDiff, $lifeScoreDiff),
                        'past_value' => [
                            'work_score' => $pastDiagnosis['work_score'],
                            'life_score' => $pastDiagnosis['life_score'],
                        ],
                        'current_value' => [
                            'work_score' => $currentDiagnosis['work_score'],
                            'life_score' => $currentDiagnosis['life_score'],
                        ],
                        'change' => [
                            'work_score' => $workScoreDiff,
                            'life_score' => $lifeScoreDiff,
                        ],
                    ];
                }
            }
        }

        // 日記のモチベーション比較
        if (isset($past['items']['past_diaries']) && isset($current['items']['current_diaries'])) {
            $pastDiaries = $past['items']['past_diaries']['data'];
            $currentDiaries = $current['items']['current_diaries']['data'];
            
            if (!empty($pastDiaries) && !empty($currentDiaries)) {
                $pastAvgMotivation = array_sum(array_column($pastDiaries, 'motivation')) / count($pastDiaries);
                $currentAvgMotivation = array_sum(array_column($currentDiaries, 'motivation')) / count($currentDiaries);
                
                $motivationDiff = $currentAvgMotivation - $pastAvgMotivation;
                
                if (abs($motivationDiff) >= 10) {
                    $transformations[] = [
                        'type' => 'motivation',
                        'title' => 'モチベーションの変化',
                        'description' => $this->generateMotivationTransformationDescription($motivationDiff),
                        'past_value' => round($pastAvgMotivation),
                        'current_value' => round($currentAvgMotivation),
                        'change' => round($motivationDiff),
                    ];
                }
            }
        }

        return [
            'transformations' => $transformations,
            'transformation_count' => count($transformations),
        ];
    }

    /**
     * 診断結果の変容説明を生成
     */
    protected function generateDiagnosisTransformationDescription(int $workScoreDiff, int $lifeScoreDiff): string
    {
        $descriptions = [];
        
        if ($workScoreDiff > 0) {
            $descriptions[] = "仕事の満足度が{$workScoreDiff}点上がりました";
        } elseif ($workScoreDiff < 0) {
            $descriptions[] = "仕事の満足度が" . abs($workScoreDiff) . "点下がりました";
        }
        
        if ($lifeScoreDiff > 0) {
            $descriptions[] = "生活の満足度が{$lifeScoreDiff}点上がりました";
        } elseif ($lifeScoreDiff < 0) {
            $descriptions[] = "生活の満足度が" . abs($lifeScoreDiff) . "点下がりました";
        }
        
        return implode('。', $descriptions) . '。';
    }

    /**
     * モチベーションの変容説明を生成
     */
    protected function generateMotivationTransformationDescription(float $motivationDiff): string
    {
        if ($motivationDiff > 0) {
            return "日記のモチベーションが平均" . round($motivationDiff) . "点上がりました。";
        } else {
            return "日記のモチベーションが平均" . round(abs($motivationDiff)) . "点下がりました。";
        }
    }

    /**
     * 成長グラフのデータを生成
     */
    public function generateGrowthGraphData(int $userId, int $months = 6): array
    {
        $data = [];
        $now = now();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            
            // その月の診断結果を取得
            $diagnosis = Diagnosis::where('user_id', $userId)
                ->where('is_completed', true)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->orderBy('created_at', 'desc')
                ->first();
            
            // その月の日記の平均モチベーション
            $diaries = Diary::where('user_id', $userId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->whereNotNull('motivation')
                ->get();
            
            $avgMotivation = $diaries->isNotEmpty() 
                ? round($diaries->avg('motivation'))
                : null;
            
            $data[] = [
                'month' => $monthStart->format('Y年n月'),
                'month_key' => $monthStart->format('Y-m'),
                'work_score' => $diagnosis?->work_score,
                'life_score' => $diagnosis?->life_score,
                'avg_motivation' => $avgMotivation,
                'diary_count' => $diaries->count(),
            ];
        }
        
        return $data;
    }
}









