<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MiniManualGeneratorService;
use App\Services\OnboardingProgressService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    protected MiniManualGeneratorService $manualGenerator;
    protected OnboardingProgressService $progressService;

    public function __construct(
        MiniManualGeneratorService $manualGenerator,
        OnboardingProgressService $progressService
    ) {
        $this->manualGenerator = $manualGenerator;
        $this->progressService = $progressService;
    }

    /**
     * 持ち味レポを表示
     */
    public function showMiniManual()
    {
        $user = Auth::user();

        // 開発環境では7日間記録のチェックを緩和（実際の日記データがあればOK）
        $isDevelopment = app()->environment('local');
        
        if (!$isDevelopment) {
            // 本番環境では7日間記録が完了しているかチェック
            if (!$this->progressService->checkStepCompletion($user->id, 'diary_7days')) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、7日間の日記記録が必要です。');
            }
        } else {
            // 開発環境では、過去7日間に日記が1件以上あればOK
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryCount = \App\Models\Diary::where('user_id', $user->id)
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->count();
            
            if ($diaryCount === 0) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、過去7日間に日記記録が必要です。テスト用データを生成するには: php artisan test:generate-diaries ' . $user->email);
            }
        }

        // 持ち味レポを生成（キャッシュがあれば使用）
        $manual = $this->manualGenerator->generateMiniManual($user->id);

        // 持ち味レポ生成を進捗に記録
        if (!$this->progressService->checkStepCompletion($user->id, 'manual_generated')) {
            $this->progressService->updateProgress($user->id, 'manual_generated');
            
            // アクティビティログに記録
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logStrengthsReportGenerated($user->id);
        }

        return view('onboarding.mini-manual', [
            'manual' => $manual,
            'user' => $user,
        ]);
    }

    /**
     * 持ち味レポPDFをダウンロード
     */
    public function downloadMiniManualPdf()
    {
        $user = Auth::user();

        // 開発環境では7日間記録のチェックを緩和
        $isDevelopment = app()->environment('local');
        
        if (!$isDevelopment) {
            if (!$this->progressService->checkStepCompletion($user->id, 'diary_7days')) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、7日間の日記記録が必要です。');
            }
        } else {
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryCount = \App\Models\Diary::where('user_id', $user->id)
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->count();
            
            if ($diaryCount === 0) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、過去7日間に日記記録が必要です。');
            }
        }

        // 持ち味レポを生成
        $manual = $this->manualGenerator->generateMiniManual($user->id);

        // PDF生成（後で実装）
        // 今は一旦HTMLを返す
        return view('onboarding.mini-manual-pdf', [
            'manual' => $manual,
            'user' => $user,
        ]);
    }
}
