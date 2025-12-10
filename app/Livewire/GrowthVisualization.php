<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diary;
use App\Models\WcmSheet;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GrowthVisualization extends Component
{
    public $comparisonPeriod = 'month'; // 'week', 'month', 'quarter', 'year'
    public $growthData = null;
    public $isLoading = false;

    protected BedrockService $bedrockService;

    public function boot()
    {
        $this->bedrockService = app(BedrockService::class);
    }

    public function mount()
    {
        $this->loadGrowthData();
    }

    public function updatedComparisonPeriod()
    {
        $this->loadGrowthData();
    }

    public function loadGrowthData()
    {
        $this->isLoading = true;

        try {
            $userId = Auth::id();
            
            // 期間を決定
            $endDate = Carbon::now();
            $startDate = match($this->comparisonPeriod) {
                'week' => $endDate->copy()->subWeek(),
                'month' => $endDate->copy()->subMonth(),
                'quarter' => $endDate->copy()->subQuarter(),
                'year' => $endDate->copy()->subYear(),
                default => $endDate->copy()->subMonth(),
            };

            // 過去の日記を取得
            $pastDiaries = Diary::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('date')
                ->get();

            // 現在の日記を取得（直近1週間）
            $recentStartDate = $endDate->copy()->subWeek();
            $recentDiaries = Diary::where('user_id', $userId)
                ->whereBetween('date', [$recentStartDate, $endDate])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('date')
                ->get();

            // WCMシートを取得
            $latestWcmSheet = WcmSheet::where('user_id', $userId)
                ->where('is_draft', false)
                ->latest('updated_at')
                ->first();

            // 成長分析を生成
            $this->growthData = $this->analyzeGrowth($pastDiaries, $recentDiaries, $latestWcmSheet);
        } catch (\Exception $e) {
            $this->growthData = null;
        } finally {
            $this->isLoading = false;
        }
    }

    protected function analyzeGrowth($pastDiaries, $recentDiaries, $wcmSheet): array
    {
        // モチベーションの推移
        $pastMotivation = $pastDiaries->avg('motivation') ?? 0;
        $recentMotivation = $recentDiaries->avg('motivation') ?? 0;
        $motivationChange = $recentMotivation - $pastMotivation;

        // 日記の頻度
        $pastFrequency = $pastDiaries->count();
        $recentFrequency = $recentDiaries->count();

        // AIによる成長分析
        $aiAnalysis = $this->generateGrowthAnalysis($pastDiaries, $recentDiaries, $wcmSheet);

        return [
            'motivation' => [
                'past' => round($pastMotivation, 1),
                'recent' => round($recentMotivation, 1),
                'change' => round($motivationChange, 1),
            ],
            'frequency' => [
                'past' => $pastFrequency,
                'recent' => $recentFrequency,
                'change' => $recentFrequency - $pastFrequency,
            ],
            'analysis' => $aiAnalysis,
            'period' => $this->comparisonPeriod,
        ];
    }

    protected function generateGrowthAnalysis($pastDiaries, $recentDiaries, $wcmSheet): ?string
    {
        try {
            $prompt = "以下の過去と現在の内省を比較して、成長や変化を分析してください。\n\n";
            
            $prompt .= "【過去の期間の内省】\n";
            foreach ($pastDiaries->take(5) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 150) . "...\n";
            }

            $prompt .= "\n【最近の期間の内省】\n";
            foreach ($recentDiaries->take(5) as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}: " . substr($diary->content, 0, 150) . "...\n";
            }

            if ($wcmSheet) {
                $prompt .= "\n【理想のあり方（Will）】\n";
                $prompt .= $wcmSheet->will_text ?? '未設定';
                $prompt .= "\n";
            }

            $prompt .= "\n【分析のポイント】\n";
            $prompt .= "- 過去と現在の内省を比較して、成長や変化を感じられる点を指摘する\n";
            $prompt .= "- 価値観や考え方の変化を分析する\n";
            $prompt .= "- 強みや成長している点を認める\n";
            $prompt .= "- 簡潔で読みやすい文章（4-6文程度）\n\n";
            $prompt .= "成長分析を生成してください:";

            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            return $response;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.growth-visualization');
    }
}



