<?php

namespace App\Services;

use App\Models\User;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Models\Diagnosis;
use App\Models\PersonalityAssessment;
use App\Models\LifeEvent;
use Illuminate\Support\Facades\Auth;

class ReflectionContextService
{
    /**
     * ユーザーのコンテキストを統合して文字列として返す
     */
    public function buildContextForUser(?int $userId = null): string
    {
        $userId = $userId ?? Auth::id();
        $user = User::find($userId);
        
        if (!$user) {
            return '';
        }

        $context = [];

        // WCMシートの最新版を取得
        $latestWcmSheet = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        if ($latestWcmSheet) {
            $context[] = "【WCMシート - 理想のあり方】";
            $context[] = "Will（こう在りたい）: " . ($latestWcmSheet->will_text ?? '未設定');
            $context[] = "Can（できること）: " . ($latestWcmSheet->can_text ?? '未設定');
            $context[] = "Must（やるべきこと）: " . ($latestWcmSheet->must_text ?? '未設定');
            $context[] = "";
        }

        // 進行中のマイルストーンを取得
        $activeMilestones = CareerMilestone::where('user_id', $userId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('target_date')
            ->limit(5)
            ->get();

        if ($activeMilestones->count() > 0) {
            $context[] = "【現在のマイルストーン】";
            foreach ($activeMilestones as $milestone) {
                $context[] = "- {$milestone->title}";
                if ($milestone->will_theme) {
                    $context[] = "  テーマ: {$milestone->will_theme}";
                }
                if ($milestone->target_date) {
                    $context[] = "  目標日: {$milestone->target_date->format('Y年m月d日')}";
                }
            }
            $context[] = "";
        }

        // 最新の診断結果を取得
        $latestDiagnosis = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->latest()
            ->first();

        if ($latestDiagnosis) {
            $context[] = "【現職満足度診断結果】";
            $context[] = "Workスコア: {$latestDiagnosis->work_score}点";
            $context[] = "Lifeスコア: {$latestDiagnosis->life_score}点";
            $context[] = "";
        }

        // 最新の自己診断結果を取得
        $latestAssessment = PersonalityAssessment::where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->first();

        if ($latestAssessment) {
            $context[] = "【自己診断結果】";
            $context[] = "タイプ: " . $this->formatAssessmentResult($latestAssessment);
            $context[] = "";
        }

        // 最近の人生史イベントを取得
        $recentLifeEvents = LifeEvent::where('user_id', $userId)
            ->orderByDesc('year')
            ->limit(3)
            ->get();

        if ($recentLifeEvents->count() > 0) {
            $context[] = "【最近の人生のターニングポイント】";
            foreach ($recentLifeEvents as $event) {
                $context[] = "- {$event->year}年: {$event->title}";
            }
            $context[] = "";
        }

        return implode("\n", $context);
    }

    /**
     * 自己診断結果をフォーマット
     */
    private function formatAssessmentResult(PersonalityAssessment $assessment): string
    {
        $data = $assessment->result_data ?? [];
        
        switch ($assessment->assessment_type) {
            case 'mbti':
                return $data['type'] ?? '未設定';
            case 'strengthsfinder':
                $top5 = $data['top5'] ?? [];
                return implode(', ', $top5);
            case 'enneagram':
                return "タイプ{$data['type']}" . ($data['wing'] ? " (翼: {$data['wing']})" : '');
            default:
                return $assessment->assessment_name ?? '未設定';
        }
    }
}

