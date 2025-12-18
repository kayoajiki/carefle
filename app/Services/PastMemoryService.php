<?php

namespace App\Services;

use Carbon\Carbon;

class PastMemoryService
{
    /**
     * 過去の記録に対して感情に訴えかけるメッセージを生成
     */
    public function generateMemoryMessage(array $record, string $category, string $timeAgo): string
    {
        $type = $record['type'] ?? 'diary';
        $date = $record['data']['date'] ?? '';
        
        // カテゴリと時間に基づいてメッセージを生成
        if ($category === 'same_date') {
            return $this->generateSameDateMessage($type, $timeAgo, $date);
        } elseif ($category === 'same_period') {
            return $this->generateSamePeriodMessage($type, $timeAgo, $date);
        } else {
            return $this->generateRecentMessage($type, $timeAgo, $date);
        }
    }

    /**
     * 同じ日付の記録に対するメッセージ
     */
    private function generateSameDateMessage(string $type, string $timeAgo, string $date): string
    {
        $messages = [
            'diary' => [
                '1年前' => "{$timeAgo}の今日、あなたはこんなことを考えていました",
                '2年前' => "{$timeAgo}の今日、あなたはこんなことを考えていました",
                '3年前' => "{$timeAgo}の今日、あなたはこんなことを考えていました",
            ],
            'diagnosis' => [
                '1年前' => "{$timeAgo}の今日、あなたの満足度は...",
                '2年前' => "{$timeAgo}の今日、あなたの満足度は...",
                '3年前' => "{$timeAgo}の今日、あなたの満足度は...",
            ],
        ];

        $typeMessages = $messages[$type] ?? $messages['diary'];
        return $typeMessages[$timeAgo] ?? "{$timeAgo}の今日、あなたは...";
    }

    /**
     * 同じ時期の記録に対するメッセージ
     */
    private function generateSamePeriodMessage(string $type, string $timeAgo, string $date): string
    {
        $messages = [
            'diary' => [
                '1ヶ月前' => "{$timeAgo}のこの時期、あなたはこんなことを考えていました",
                '3ヶ月前' => "{$timeAgo}のこの時期、あなたはこんなことを考えていました",
                '6ヶ月前' => "{$timeAgo}のこの時期、あなたはこんなことを考えていました",
                '1年前' => "{$timeAgo}のこの時期、あなたはこんなことを考えていました",
            ],
            'diagnosis' => [
                '1ヶ月前' => "{$timeAgo}のこの時期、あなたの満足度は...",
                '3ヶ月前' => "{$timeAgo}のこの時期、あなたの満足度は...",
                '6ヶ月前' => "{$timeAgo}のこの時期、あなたの満足度は...",
                '1年前' => "{$timeAgo}のこの時期、あなたの満足度は...",
            ],
        ];

        $typeMessages = $messages[$type] ?? $messages['diary'];
        return $typeMessages[$timeAgo] ?? "{$timeAgo}のこの時期、あなたは...";
    }

    /**
     * 最近の記録に対するメッセージ
     */
    private function generateRecentMessage(string $type, string $timeAgo, string $date): string
    {
        if ($type === 'diary') {
            return "{$timeAgo}、あなたはこんなことを考えていました";
        } elseif ($type === 'diagnosis') {
            return "{$timeAgo}、あなたの満足度は...";
        }
        
        return "{$timeAgo}の記録";
    }

    /**
     * 時間差を人間が読みやすい形式に変換
     */
    public function calculateTimeAgo(Carbon $pastDate): string
    {
        $now = now();
        
        // 過去の日付であることを確認し、絶対値で計算
        if ($pastDate->isFuture()) {
            // 未来の日付の場合は「最近」として扱う
            return "最近";
        }
        
        // 整数で計算（小数点を避けるため）
        $diffInDays = (int) $now->diffInDays($pastDate, false);
        $diffInMonths = (int) $now->diffInMonths($pastDate, false);
        $diffInYears = (int) $now->diffInYears($pastDate, false);

        // 絶対値を取得
        $diffInDays = abs($diffInDays);
        $diffInMonths = abs($diffInMonths);
        $diffInYears = abs($diffInYears);

        if ($diffInYears >= 1) {
            return "{$diffInYears}年前";
        } elseif ($diffInMonths >= 6) {
            return "6ヶ月前";
        } elseif ($diffInMonths >= 3) {
            return "3ヶ月前";
        } elseif ($diffInMonths >= 1) {
            return "{$diffInMonths}ヶ月前";
        } elseif ($diffInDays >= 7) {
            $weeks = (int) floor($diffInDays / 7);
            return "{$weeks}週間前";
        } else {
            return "{$diffInDays}日前";
        }
    }

    /**
     * カテゴリを判定
     */
    public function determineCategory(Carbon $pastDate): string
    {
        $now = now();
        
        // 同じ日付かチェック（月日が同じ）
        if ($pastDate->month === $now->month && $pastDate->day === $now->day) {
            return 'same_date';
        }
        
        // 同じ時期かチェック（同じ月、または±1ヶ月以内）
        $diffInMonths = abs($now->diffInMonths($pastDate));
        if ($diffInMonths <= 1 || ($pastDate->month === $now->month)) {
            return 'same_period';
        }
        
        return 'recent';
    }
}

