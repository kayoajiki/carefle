<?php

namespace App\Http\Controllers;

use App\Services\MappingProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MappingController extends Controller
{
    protected MappingProgressService $progressService;

    public function __construct(MappingProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Display mapping progress and visualization.
     */
    public function index()
    {
        $user = Auth::user();
        
        // オンボーディング完了時のみ表示
        if (!$this->progressService->isMappingUnlocked($user->id)) {
            return redirect()->route('dashboard')
                ->with('message', 'オンボーディングを完了すると、マッピング機能が利用できます。');
        }

        // 進捗を自動更新
        $this->progressService->autoUpdateProgress($user->id);

        $progress = $this->progressService->getOrCreateProgress($user->id);
        
        // セクション別進捗を取得
        $sections = ['past', 'current', 'future'];
        $sectionProgresses = [];
        foreach ($sections as $section) {
            $sectionProgresses[$section] = $this->progressService->getSectionProgress($user->id, $section);
        }

        return view('mapping.index', [
            'progress' => $progress,
            'sectionProgresses' => $sectionProgresses,
        ]);
    }
}
