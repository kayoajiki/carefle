<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\DiagnosisAnswer;
use App\Models\Diary;
use App\Models\PersonalityAssessment;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Models\LifeEvent;
use Illuminate\Support\Facades\Auth;

class ReflectionContextService
{
    /**
     * ユーザーの背景情報を構築して文字列として返す
     * 
     * @param int|null $userId
     * @return string
     */
    public function buildContextForUser(?int $userId = null): string
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return '';
        }

        $contextParts = [];

        // 最新の診断結果を取得
        $latestDiagnosis = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->latest()
            ->first();

        if ($latestDiagnosis) {
            $contextParts[] = "【現職満足度診断】";
            $contextParts[] = "診断日: {$latestDiagnosis->updated_at->format('Y年n月j日')}";
            
            if ($latestDiagnosis->work_pillar_scores) {
                $scores = $latestDiagnosis->work_pillar_scores;
                $contextParts[] = "仕事の5つの柱スコア:";
                foreach ($scores as $pillar => $score) {
                    $pillarNames = [
                        'purpose' => 'Purpose（目的）',
                        'profession' => 'Profession（職業）',
                        'people' => 'People（人間関係）',
                        'privilege' => 'Privilege（待遇）',
                        'progress' => 'Progress（成長）',
                    ];
                    $pillarName = $pillarNames[$pillar] ?? $pillar;
                    $contextParts[] = "  - {$pillarName}: {$score}/100";
                }
            }
            $contextParts[] = "";
        }

        // 最新の自己診断結果を取得
        $latestAssessment = PersonalityAssessment::where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->first();

        if ($latestAssessment) {
            $contextParts[] = "【自己診断結果】";
            $contextParts[] = "診断名: " . ($latestAssessment->assessment_name ?: ucfirst($latestAssessment->assessment_type));
            if ($latestAssessment->completed_at) {
                $contextParts[] = "診断日: {$latestAssessment->completed_at->format('Y年n月j日')}";
            }
            
            if ($latestAssessment->mbti_type) {
                $contextParts[] = "MBTIタイプ: {$latestAssessment->mbti_type}";
            }
            
            if ($latestAssessment->strengths_top5) {
                $strengths = array_filter($latestAssessment->strengths_top5);
                if (!empty($strengths)) {
                    $contextParts[] = "ストレングスファインダー上位5つ: " . implode(', ', $strengths);
                }
            }
            
            if ($latestAssessment->enneagram_type) {
                $contextParts[] = "エニアグラムタイプ: {$latestAssessment->enneagram_type}";
            }
            $contextParts[] = "";
        }

        // 最新のWCMシートを取得
        $latestWcm = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        if ($latestWcm) {
            $contextParts[] = "【WCMシート】";
            $contextParts[] = "更新日: {$latestWcm->updated_at->format('Y年n月j日')}";
            
            if ($latestWcm->will_text) {
                $contextParts[] = "Will（なりたい自分）: " . mb_substr($latestWcm->will_text, 0, 200);
            }
            
            if ($latestWcm->can_text) {
                $contextParts[] = "Can（できること）: " . mb_substr($latestWcm->can_text, 0, 200);
            }
            
            if ($latestWcm->must_text) {
                $contextParts[] = "Must（やるべきこと）: " . mb_substr($latestWcm->must_text, 0, 200);
            }
            $contextParts[] = "";
        }

        // 進行中のマイルストーンを取得
        $activeMilestones = CareerMilestone::where('user_id', $userId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('target_date')
            ->limit(3)
            ->get();

        if ($activeMilestones->count() > 0) {
            $contextParts[] = "【進行中のマイルストーン】";
            foreach ($activeMilestones as $milestone) {
                $contextParts[] = "- {$milestone->title}";
                if ($milestone->target_date) {
                    $contextParts[] = "  目標日: {$milestone->target_date->format('Y年n月j日')}";
                }
            }
            $contextParts[] = "";
        }

        // 最近の日記の傾向（直近1週間）
        $recentDiaries = Diary::where('user_id', $userId)
            ->where('date', '>=', now()->subWeek())
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        if ($recentDiaries->count() > 0) {
            $avgMotivation = $recentDiaries->avg('motivation');
            $contextParts[] = "【最近の日記の傾向】";
            $contextParts[] = "直近1週間の平均モチベーション: " . round($avgMotivation) . "/100";
            $contextParts[] = "記録日数: {$recentDiaries->count()}日";
        }

        return implode("\n", $contextParts);
    }
}

