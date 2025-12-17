<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TransformationTrackingService;
use App\Services\MappingProgressService;
use Illuminate\Support\Facades\Auth;

class TransformationVisualization extends Component
{
    protected TransformationTrackingService $transformationService;
    protected MappingProgressService $progressService;

    public function boot(
        TransformationTrackingService $transformationService,
        MappingProgressService $progressService
    ): void {
        $this->transformationService = $transformationService;
        $this->progressService = $progressService;
    }

    public function render()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return view('livewire.transformation-visualization', [
                'transformations' => null,
                'growthData' => null,
                'isUnlocked' => false,
            ]);
        }

        // オンボーディング完了時のみ表示
        $isUnlocked = $this->progressService->isMappingUnlocked($userId);
        
        if (!$isUnlocked) {
            return view('livewire.transformation-visualization', [
                'transformations' => null,
                'growthData' => null,
                'isUnlocked' => false,
            ]);
        }

        // 変容データを取得
        $transformationResult = $this->transformationService->comparePastAndCurrent($userId);
        $growthData = $this->transformationService->generateGrowthGraphData($userId, 6);

        return view('livewire.transformation-visualization', [
            'transformations' => $transformationResult['transformations'] ?? [],
            'transformationCount' => $transformationResult['transformation_count'] ?? 0,
            'growthData' => $growthData,
            'isUnlocked' => $isUnlocked,
        ]);
    }
}
