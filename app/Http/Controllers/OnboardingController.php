<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MiniManualGeneratorService;
use App\Services\OnboardingProgressService;
use App\Services\ActivityLogService;
use App\Models\StrengthsReport;
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

        // 7日間記録が完了しているかチェック
        if (!$this->progressService->checkStepCompletion($user->id, 'diary_7days')) {
            // 7日間記録が完了していない場合、実際の日記数を確認
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryDates = \App\Models\Diary::where('user_id', $user->id)
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->get()
                ->pluck('date')
                ->map(fn($date) => $date->format('Y-m-d'))
                ->unique()
                ->count();
            
            if ($diaryDates < 7) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、7日間の日記記録が必要です。（現在: ' . $diaryDates . '日）');
            }
        }

        // 既存のレポを取得
        $existingReport = StrengthsReport::getLatestForUser($user->id);
        
        // 既存のレポがあり、1ヶ月経過していない場合は既存のレポを表示
        if ($existingReport && !StrengthsReport::canUpdate($user->id)) {
            $manual = [
                'user_id' => $user->id,
                'generated_at' => $existingReport->generated_at,
                'content' => $existingReport->content,
                'diagnosis_report' => $existingReport->diagnosis_report,
                'diary_report' => $existingReport->diary_report,
            ];
        } else {
            // 新規生成または更新可能な場合
            // 過去のレポを取得（更新前のレポ）
            $previousReport = $existingReport ? [
                'content' => $existingReport->content,
                'diagnosis_report' => $existingReport->diagnosis_report,
                'diary_report' => $existingReport->diary_report,
                'generated_at' => $existingReport->generated_at->toDateTimeString(),
            ] : null;
            
            $manual = $this->manualGenerator->generateMiniManual($user->id, $previousReport);
            
            // データベースに保存
            StrengthsReport::create([
                'user_id' => $user->id,
                'content' => $manual['content'],
                'diagnosis_report' => $manual['diagnosis_report'] ?? null,
                'diary_report' => $manual['diary_report'] ?? null,
                'generated_at' => $manual['generated_at'],
            ]);
        }

        // 持ち味レポ生成を進捗に記録
        if (!$this->progressService->checkStepCompletion($user->id, 'manual_generated')) {
            $this->progressService->updateProgress($user->id, 'manual_generated');
            
            // アクティビティログに記録
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logStrengthsReportGenerated($user->id);
        }
        
        // 見直し日時を更新
        $mappingProgressService = app(\App\Services\MappingProgressService::class);
        $mappingProgressService->markItemAsReviewed($user->id, 'strengths_report');

        $canUpdate = StrengthsReport::canUpdate($user->id);

        return view('onboarding.mini-manual', [
            'manual' => $manual,
            'user' => $user,
            'canUpdate' => $canUpdate,
        ]);
    }

    /**
     * 持ち味レポPDFをダウンロード
     */
    public function downloadMiniManualPdf()
    {
        $user = Auth::user();

        // 7日間記録が完了しているかチェック
        if (!$this->progressService->checkStepCompletion($user->id, 'diary_7days')) {
            // 7日間記録が完了していない場合、実際の日記数を確認
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryDates = \App\Models\Diary::where('user_id', $user->id)
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->get()
                ->pluck('date')
                ->map(fn($date) => $date->format('Y-m-d'))
                ->unique()
                ->count();
            
            if ($diaryDates < 7) {
                return redirect()->route('dashboard')
                    ->with('error', '持ち味レポを生成するには、7日間の日記記録が必要です。（現在: ' . $diaryDates . '日）');
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

    /**
     * 持ち味レポを更新
     */
    public function updateMiniManual()
    {
        $user = Auth::user();

        // 更新可能かチェック
        if (!StrengthsReport::canUpdate($user->id)) {
            $latestReport = StrengthsReport::getLatestForUser($user->id);
            $nextUpdateDate = $latestReport ? $latestReport->generated_at->copy()->addMonth()->format('Y年n月j日') : '';
            return redirect()->route('onboarding.mini-manual')
                ->with('error', '持ち味レポは1ヶ月に1回のみ更新できます。次回の更新可能日: ' . $nextUpdateDate);
        }

        // 7日間記録が完了しているかチェック
        if (!$this->progressService->checkStepCompletion($user->id, 'diary_7days')) {
            // 7日間記録が完了していない場合、実際の日記数を確認
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $today = now()->endOfDay();
            
            $diaryDates = \App\Models\Diary::where('user_id', $user->id)
                ->whereBetween('date', [$sevenDaysAgo, $today])
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->get()
                ->pluck('date')
                ->map(fn($date) => $date->format('Y-m-d'))
                ->unique()
                ->count();
            
            if ($diaryDates < 7) {
                return redirect()->route('onboarding.mini-manual')
                    ->with('error', '持ち味レポを更新するには、7日間の日記記録が必要です。（現在: ' . $diaryDates . '日）');
            }
        }

        // 過去のレポを取得（更新前のレポ）
        $previousReport = StrengthsReport::getLatestForUser($user->id);
        $previousData = $previousReport ? [
            'content' => $previousReport->content,
            'diagnosis_report' => $previousReport->diagnosis_report,
            'diary_report' => $previousReport->diary_report,
            'generated_at' => $previousReport->generated_at->toDateTimeString(),
        ] : null;
        
        // 新しいレポを生成
        $manual = $this->manualGenerator->generateMiniManual($user->id, $previousData);
        
        // データベースに保存
        StrengthsReport::create([
            'user_id' => $user->id,
            'content' => $manual['content'],
            'diagnosis_report' => $manual['diagnosis_report'] ?? null,
            'diary_report' => $manual['diary_report'] ?? null,
            'generated_at' => $manual['generated_at'],
        ]);

        // 見直し日時を更新
        $mappingProgressService = app(\App\Services\MappingProgressService::class);
        $mappingProgressService->markItemAsReviewed($user->id, 'strengths_report');

        return redirect()->route('onboarding.mini-manual')
            ->with('success', '持ち味レポを更新しました。');
    }
}
