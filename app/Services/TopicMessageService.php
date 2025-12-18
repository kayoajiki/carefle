<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\LifeEvent;
use App\Models\CareerMilestone;
use App\Models\WcmSheet;
use App\Models\OnboardingProgress;
use App\Models\MappingProgress;
use Illuminate\Support\Facades\Auth;

class TopicMessageService
{
    /**
     * ユーザーの進捗に基づいてトピックスメッセージを生成
     */
    public function generateTopicMessage(?int $userId = null): ?string
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) {
            return null;
        }

        // 最近の活動をチェック（過去24時間以内）
        $recentActivity = $this->checkRecentActivity($userId);
        
        if ($recentActivity) {
            return $recentActivity;
        }

        // 進捗状況に基づくメッセージ
        return $this->getProgressBasedMessage($userId);
    }

    /**
     * 最近の活動をチェック
     */
    protected function checkRecentActivity(int $userId): ?string
    {
        $yesterday = now()->subDay();

        // 最近の日記入力
        $recentDiaries = Diary::where('user_id', $userId)
            ->where('created_at', '>=', $yesterday)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->count();

        if ($recentDiaries > 0) {
            $streak = $this->calculateDiaryStreak($userId);
            if ($streak >= 7) {
                return "日記{$streak}日連続記録中！素晴らしい継続力ですね🎉";
            } elseif ($streak >= 3) {
                return "日記{$streak}日連続記録中！順調ですね✨";
            } else {
                return "日記の入力が進みましたね🎉";
            }
        }

        // 最近の人生史追加
        $recentLifeEvents = LifeEvent::where('user_id', $userId)
            ->where('created_at', '>=', $yesterday)
            ->count();

        if ($recentLifeEvents > 0) {
            $totalEvents = LifeEvent::where('user_id', $userId)->count();
            return "人生史が{$totalEvents}件になりました！過去を振り返ることで未来が見えてきます✨";
        }

        // 最近のマイルストーン追加
        $recentMilestones = CareerMilestone::where('user_id', $userId)
            ->where('created_at', '>=', $yesterday)
            ->count();

        if ($recentMilestones > 0) {
            $totalMilestones = CareerMilestone::where('user_id', $userId)->count();
            return "マイルストーンが{$totalMilestones}件になりました！目標に向かって進んでいます🚀";
        }

        // 最近のWCMシート作成
        $recentWcmSheets = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->where('created_at', '>=', $yesterday)
            ->count();

        if ($recentWcmSheets > 0) {
            return "WCMシートを作成しましたね！Will/Can/Mustを整理することで、行動が明確になります💡";
        }

        return null;
    }

    /**
     * 進捗状況に基づくメッセージ
     */
    protected function getProgressBasedMessage(int $userId): ?string
    {
        // オンボーディング進捗をチェック
        $onboardingProgress = OnboardingProgress::where('user_id', $userId)->first();
        
        if ($onboardingProgress) {
            $nextStep = app(OnboardingProgressService::class)->getNextStep($userId);
            
            if ($nextStep) {
                $stepMessages = [
                    'diagnosis' => '現職満足度診断を完了すると、次のステップに進めます📊',
                    'diary_first' => '初めての日記を書いてみましょう📝',
                    'assessment' => '自己診断結果を入力すると、より詳しい分析ができます🔍',
                    'diary_3days' => '3日間連続で日記を書くと、習慣化の第一歩です✨',
                    'diary_7days' => '7日間連続で日記を書くと、持ち味レポが生成できます🎯',
                    'manual_generated' => '持ち味レポを生成して、自分の強みを確認しましょう💪',
                ];
                
                if (isset($stepMessages[$nextStep])) {
                    return $stepMessages[$nextStep];
                }
            }
        }

        // マッピング進捗をチェック
        $mappingProgress = MappingProgress::where('user_id', $userId)->first();
        
        if ($mappingProgress) {
            $nextItem = app(MappingProgressService::class)->getNextItem($userId);
            
            if ($nextItem) {
                $itemMessages = [
                    'life_history' => '人生史を入力すると、過去の経験から学べます📚',
                    'current_diaries' => '日記を続けることで、現在の自分が見えてきます📖',
                    'strengths_report' => '持ち味レポを確認して、自分の強みを活かしましょう💎',
                    'wcm_sheet' => 'WCMシートを作成して、Will/Can/Mustを整理しましょう🎯',
                    'milestones' => 'マイルストーンを設定して、目標に向かって進みましょう🚀',
                    'my_goal' => 'マイゴールを設定して、将来のビジョンを明確にしましょう🌟',
                ];
                
                if (isset($itemMessages[$nextItem])) {
                    return $itemMessages[$nextItem];
                }
            } else {
                // すべて完了
                return "すべてのステップが完了しました！素晴らしい進捗です🎉";
            }
        }

        // デフォルトメッセージ
        return "今日も一歩ずつ、自分らしさを見つけていきましょう✨";
    }

    /**
     * 日記の連続記録日数を計算
     */
    protected function calculateDiaryStreak(int $userId): int
    {
        $diaries = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date', 'desc')
            ->get()
            ->pluck('date')
            ->unique()
            ->sortDesc()
            ->values();

        if ($diaries->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expectedDate = now()->startOfDay();

        foreach ($diaries as $date) {
            $dateStart = $date->startOfDay();
            if ($dateStart->eq($expectedDate) || $dateStart->eq($expectedDate->copy()->subDay())) {
                $streak++;
                $expectedDate = $dateStart->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}


