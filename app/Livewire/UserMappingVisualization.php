<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\UserMappingService;
use App\Services\MappingProgressService;
use Illuminate\Support\Facades\Auth;

class UserMappingVisualization extends Component
{
    protected UserMappingService $mappingService;
    protected MappingProgressService $progressService;

    public function boot(
        UserMappingService $mappingService,
        MappingProgressService $progressService
    ): void {
        $this->mappingService = $mappingService;
        $this->progressService = $progressService;
    }

    public function render()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return view('livewire.user-mapping-visualization', [
                'mapping' => null,
                'isUnlocked' => false,
            ]);
        }

        // オンボーディング完了時のみ表示
        $isUnlocked = $this->progressService->isMappingUnlocked($userId);
        
        if (!$isUnlocked) {
            return view('livewire.user-mapping-visualization', [
                'mapping' => null,
                'isUnlocked' => false,
            ]);
        }

        // マッピングデータを生成
        $mapping = $this->mappingService->generateCompleteMapping($userId);

        return view('livewire.user-mapping-visualization', [
            'mapping' => $mapping,
            'isUnlocked' => $isUnlocked,
        ]);
    }
}
