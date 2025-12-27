<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\LifeEvent;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Services\MappingProgressService;
use Illuminate\Support\Facades\Log;

class UserMappingService
{
    protected MappingProgressService $progressService;

    public function __construct(MappingProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Generate past mapping (completed items only).
     */
    public function generatePastMapping(int $userId): array
    {
        $progress = $this->progressService->getOrCreateProgress($userId);
        $completedItems = $progress->completed_items ?? [];
        
        $mapping = [
            'section' => 'past',
            'items' => [],
        ];

        // 過去の診断
        if (in_array('past_diagnosis', $completedItems, true)) {
            $diagnoses = Diagnosis::where('user_id', $userId)
                ->where('is_completed', true)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $thirtyDaysAgo = now()->subDays(30);
            $pastDiagnoses = $diagnoses->filter(function ($diagnosis) use ($thirtyDaysAgo) {
                return $diagnosis->created_at->lt($thirtyDaysAgo);
            });
            
            if ($pastDiagnoses->isNotEmpty()) {
                $mapping['items']['past_diagnosis'] = [
                    'type' => 'diagnosis',
                    'data' => $pastDiagnoses->map(function ($diagnosis) {
                        return [
                            'id' => $diagnosis->id,
                            'work_score' => $diagnosis->work_score,
                            'life_score' => $diagnosis->life_score,
                            'created_at' => $diagnosis->created_at->format('Y-m-d'),
                        ];
                    })->toArray(),
                ];
            }
        }

        // 過去の日記
        if (in_array('past_diaries', $completedItems, true)) {
            $thirtyDaysAgo = now()->subDays(30);
            $pastDiaries = Diary::where('user_id', $userId)
                ->where('date', '<', $thirtyDaysAgo)
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
            
            if ($pastDiaries->isNotEmpty()) {
                $mapping['items']['past_diaries'] = [
                    'type' => 'diaries',
                    'data' => $pastDiaries->map(function ($diary) {
                        return [
                            'id' => $diary->id,
                            'date' => $diary->date->format('Y-m-d'),
                            'motivation' => $diary->motivation,
                            'content_preview' => mb_substr($diary->content, 0, 100),
                        ];
                    })->toArray(),
                ];
            }
        }

        // 人生史
        if (in_array('life_history', $completedItems, true)) {
            $lifeEvents = LifeEvent::where('user_id', $userId)
                ->orderBy('year', 'asc')
                ->get();
            
            if ($lifeEvents->isNotEmpty()) {
                $mapping['items']['life_history'] = [
                    'type' => 'life_events',
                    'data' => $lifeEvents->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'year' => $event->year,
                            'title' => $event->title,
                            'description' => $event->description,
                            'motivation' => $event->motivation,
                        ];
                    })->toArray(),
                ];
            }
        }

        return $mapping;
    }

    /**
     * Generate current mapping (completed items only).
     */
    public function generateCurrentMapping(int $userId): array
    {
        $progress = $this->progressService->getOrCreateProgress($userId);
        $completedItems = $progress->completed_items ?? [];
        
        $mapping = [
            'section' => 'current',
            'items' => [],
        ];

        // 最新の診断
        if (in_array('current_diagnosis', $completedItems, true)) {
            $latestDiagnosis = Diagnosis::where('user_id', $userId)
                ->where('is_completed', true)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($latestDiagnosis) {
                $mapping['items']['current_diagnosis'] = [
                    'type' => 'diagnosis',
                    'data' => [
                        'id' => $latestDiagnosis->id,
                        'work_score' => $latestDiagnosis->work_score,
                        'life_score' => $latestDiagnosis->life_score,
                        'created_at' => $latestDiagnosis->created_at->format('Y-m-d'),
                    ],
                ];
            }
        }

        // 最近の日記
        if (in_array('current_diaries', $completedItems, true)) {
            $recentDiaries = Diary::where('user_id', $userId)
                ->where('date', '>=', now()->subDays(7))
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->orderBy('date', 'desc')
                ->get();
            
            if ($recentDiaries->isNotEmpty()) {
                $mapping['items']['current_diaries'] = [
                    'type' => 'diaries',
                    'data' => $recentDiaries->map(function ($diary) {
                        return [
                            'id' => $diary->id,
                            'date' => $diary->date->format('Y-m-d'),
                            'motivation' => $diary->motivation,
                            'content_preview' => mb_substr($diary->content, 0, 100),
                        ];
                    })->toArray(),
                ];
            }
        }

        // 持ち味レポ
        if (in_array('strengths_report', $completedItems, true)) {
            // 持ち味レポのデータはOnboardingControllerから取得する必要があるが、
            // ここでは簡易的にフラグのみ設定
            $mapping['items']['strengths_report'] = [
                'type' => 'strengths_report',
                'data' => [
                    'generated' => true,
                ],
            ];
        }

        return $mapping;
    }

    /**
     * Generate future mapping (completed items only).
     */
    public function generateFutureMapping(int $userId): array
    {
        $progress = $this->progressService->getOrCreateProgress($userId);
        $completedItems = $progress->completed_items ?? [];
        
        $mapping = [
            'section' => 'future',
            'items' => [],
        ];

        // WCMシート
        if (in_array('wcm_sheet', $completedItems, true)) {
            $wcmSheets = WcmSheet::where('user_id', $userId)
                ->where('is_draft', false)
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($wcmSheets->isNotEmpty()) {
                $mapping['items']['wcm_sheet'] = [
                    'type' => 'wcm_sheets',
                    'data' => $wcmSheets->map(function ($sheet) {
                        return [
                            'id' => $sheet->id,
                            'title' => $sheet->title,
                            'will_text' => $sheet->will_text,
                            'can_text' => $sheet->can_text,
                            'must_text' => $sheet->must_text,
                            'created_at' => $sheet->created_at->format('Y-m-d'),
                        ];
                    })->toArray(),
                ];
            }
        }

        // マイルストーン
        if (in_array('milestones', $completedItems, true)) {
            $milestones = CareerMilestone::where('user_id', $userId)
                ->orderBy('target_date', 'asc')
                ->get();
            
            if ($milestones->isNotEmpty()) {
                $mapping['items']['milestones'] = [
                    'type' => 'milestones',
                    'data' => $milestones->map(function ($milestone) {
                        return [
                            'id' => $milestone->id,
                            'title' => $milestone->title,
                            'target_date' => $milestone->target_date?->format('Y-m-d'),
                            'target_year' => $milestone->target_year,
                            'status' => $milestone->status,
                            'will_theme' => $milestone->will_theme,
                        ];
                    })->toArray(),
                ];
            }
        }

        return $mapping;
    }

    /**
     * Integrate past, current, and future mappings.
     */
    public function integrateMappings(array $past, array $current, array $future): array
    {
        return [
            'past' => $past,
            'current' => $current,
            'future' => $future,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate complete mapping for a user.
     */
    public function generateCompleteMapping(int $userId): array
    {
        $past = $this->generatePastMapping($userId);
        $current = $this->generateCurrentMapping($userId);
        $future = $this->generateFutureMapping($userId);
        
        return $this->integrateMappings($past, $current, $future);
    }
}









