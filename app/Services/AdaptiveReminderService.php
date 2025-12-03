<?php

namespace App\Services;

use App\Models\User;
use App\Models\Diary;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdaptiveReminderService
{
    /**
     * ユーザーの行動パターンを分析し、最適なリマインダータイミングを決定
     */
    public function calculateOptimalReminderTime(User $user): ?Carbon
    {
        // 適応的リマインダーが無効の場合はnullを返す
        if (!$user->enable_adaptive_reminders) {
            return null;
        }

        // 過去の日記を分析して、最も内省が書かれた時間帯を特定
        $diaries = Diary::where('user_id', $user->id)
            ->whereNotNull('created_at')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        if ($diaries->isEmpty()) {
            // デフォルトの時間を返す（好みの時間が設定されている場合はそれを使用）
            if ($user->preferred_reflection_time) {
                return Carbon::parse($user->preferred_reflection_time);
            }
            return Carbon::now()->setTime(21, 0); // デフォルト: 21時
        }

        // 時間帯ごとの内省回数を集計
        $timeSlots = [];
        foreach ($diaries as $diary) {
            $hour = $diary->created_at->hour;
            $timeSlot = $this->getTimeSlot($hour);
            $timeSlots[$timeSlot] = ($timeSlots[$timeSlot] ?? 0) + 1;
        }

        // 最も多い時間帯を特定
        $mostFrequentSlot = array_search(max($timeSlots), $timeSlots);
        
        // 時間帯を具体的な時間に変換
        $optimalTime = $this->slotToTime($mostFrequentSlot);

        // 好みの時間が設定されている場合は、それを優先
        if ($user->preferred_reflection_time) {
            $optimalTime = Carbon::parse($user->preferred_reflection_time);
        }

        return $optimalTime;
    }

    /**
     * 時間帯を取得
     */
    protected function getTimeSlot(int $hour): string
    {
        if ($hour >= 6 && $hour < 12) {
            return 'morning';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'afternoon';
        } elseif ($hour >= 18 && $hour < 22) {
            return 'evening';
        } else {
            return 'night';
        }
    }

    /**
     * 時間帯を具体的な時間に変換
     */
    protected function slotToTime(string $slot): Carbon
    {
        $times = [
            'morning' => 8,
            'afternoon' => 14,
            'evening' => 20,
            'night' => 22,
        ];

        $hour = $times[$slot] ?? 20;
        return Carbon::now()->setTime($hour, 0);
    }

    /**
     * リマインダーを送信すべきかどうかを判定
     */
    public function shouldSendReminder(User $user): bool
    {
        // 今日の日記が既に書かれている場合は送信しない
        $todayDiary = Diary::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if ($todayDiary) {
            return false;
        }

        // 最適なリマインダータイミングを取得
        $optimalTime = $this->calculateOptimalReminderTime($user);
        
        if (!$optimalTime) {
            return false;
        }

        // 現在時刻が最適な時間の前後30分以内かチェック
        $now = Carbon::now();
        $timeDiff = abs($now->diffInMinutes($optimalTime));

        return $timeDiff <= 30;
    }
}

