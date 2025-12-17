<?php

namespace App\View\Composers;

use App\Models\Diagnosis;
use App\Models\WcmSheet;
use App\Services\FeatureDiscoveryService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SidebarComposer
{
    protected FeatureDiscoveryService $featureService;

    public function __construct(FeatureDiscoveryService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // 最新の完了済み診断を取得
        $latestDiagnosis = Diagnosis::where('user_id', $user->id)
            ->where('is_completed', true)
            ->latest()
            ->first();

        // 最新のWCMシートを取得（下書き以外）
        $latestWcmSheet = WcmSheet::where('user_id', $user->id)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        // 機能のアンロック状態を取得
        $unlockedFeatures = $this->featureService->getUnlockedFeatures($user->id);
        $discoveryHints = $this->featureService->getDiscoveryHints($user->id);

        $view->with([
            'latestDiagnosisId' => $latestDiagnosis?->id,
            'latestWcmSheetId' => $latestWcmSheet?->id,
            'unlockedFeatures' => $unlockedFeatures,
            'discoveryHints' => $discoveryHints,
        ]);
    }
}

