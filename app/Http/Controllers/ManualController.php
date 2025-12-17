<?php

namespace App\Http\Controllers;

use App\Services\ContextDetectionService;
use App\Services\ContextualManualGeneratorService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManualController extends Controller
{
    protected ContextDetectionService $contextService;
    protected ContextualManualGeneratorService $manualService;

    public function __construct(
        ContextDetectionService $contextService,
        ContextualManualGeneratorService $manualService
    ) {
        $this->contextService = $contextService;
        $this->manualService = $manualService;
    }

    /**
     * コンテキスト別取説を表示
     */
    public function showContextualManual(string $context)
    {
        $user = Auth::user();
        
        // 有効なコンテキストかチェック
        if (!array_key_exists($context, ContextDetectionService::CONTEXTS)) {
            abort(404, '無効なコンテキストです');
        }

        // コンテキスト別取説を生成
        $manual = $this->manualService->generateContextualManual($user->id, $context);

        // アクティビティログに記録（生成成功時のみ）
        if ($manual) {
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logContextualManualGenerated($user->id, $context);
        }

        if (!$manual) {
            // 生成できない場合（記録数が不足している場合）
            $contextCounts = $this->contextService->trackContextCount($user->id);
            $currentCount = $contextCounts[$context] ?? 0;
            $minCount = 5;
            
            return view('manual.context', [
                'context' => $context,
                'contextLabel' => ContextDetectionService::getContextLabel($context),
                'manual' => null,
                'currentCount' => $currentCount,
                'minCount' => $minCount,
                'canGenerate' => false,
            ]);
        }

        return view('manual.context', [
            'context' => $context,
            'contextLabel' => $manual['context_label'],
            'manual' => $manual,
            'canGenerate' => true,
        ]);
    }

    /**
     * コンテキスト別取説の一覧を表示
     */
    public function index()
    {
        $user = Auth::user();
        
        // 各コンテキストの記録数を取得
        $contextCounts = $this->contextService->trackContextCount($user->id);
        
        // 生成可能なコンテキストを判定
        $availableContexts = [];
        foreach (ContextDetectionService::CONTEXTS as $key => $label) {
            if ($key === 'other') {
                continue; // 'other'は一覧に表示しない
            }
            
            $count = $contextCounts[$key] ?? 0;
            $canGenerate = $this->contextService->canGenerateContextualManual($user->id, $key, 5);
            
            $availableContexts[] = [
                'key' => $key,
                'label' => $label,
                'count' => $count,
                'canGenerate' => $canGenerate,
            ];
        }

        // 記録数でソート（多い順）
        usort($availableContexts, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return view('manual.index', [
            'contexts' => $availableContexts,
        ]);
    }
}

