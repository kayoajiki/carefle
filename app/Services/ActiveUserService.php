<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActiveUserService
{
    /**
     * Cache duration in seconds (5 minutes).
     */
    protected const CACHE_DURATION = 300;

    /**
     * Get daily active users count (DAU).
     */
    public function getDailyActiveUsers(): int
    {
        return Cache::remember('admin:dau', self::CACHE_DURATION, function () {
            return DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subDay()->timestamp)
                ->distinct('user_id')
                ->count('user_id');
        });
    }

    /**
     * Get weekly active users count (WAU).
     */
    public function getWeeklyActiveUsers(): int
    {
        return Cache::remember('admin:wau', self::CACHE_DURATION, function () {
            return DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subWeek()->timestamp)
                ->distinct('user_id')
                ->count('user_id');
        });
    }

    /**
     * Get monthly active users count (MAU).
     */
    public function getMonthlyActiveUsers(): int
    {
        return Cache::remember('admin:mau', self::CACHE_DURATION, function () {
            return DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMonth()->timestamp)
                ->distinct('user_id')
                ->count('user_id');
        });
    }

    /**
     * Get all active user statistics.
     */
    public function getActiveUserStats(): array
    {
        return [
            'dau' => $this->getDailyActiveUsers(),
            'wau' => $this->getWeeklyActiveUsers(),
            'mau' => $this->getMonthlyActiveUsers(),
        ];
    }

    /**
     * Clear cache for active user statistics.
     */
    public function clearCache(): void
    {
        Cache::forget('admin:dau');
        Cache::forget('admin:wau');
        Cache::forget('admin:mau');
    }
}

