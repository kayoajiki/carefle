<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Services\ActiveUserService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ActiveUserService $activeUserService;

    public function __construct(ActiveUserService $activeUserService)
    {
        $this->activeUserService = $activeUserService;
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Active user statistics
        $activeUserStats = $this->activeUserService->getActiveUserStats();

        // New user registrations
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Main operation statistics
        $diagnosisCompletedToday = ActivityLog::where('action', 'diagnosis_completed')
            ->whereDate('created_at', today())
            ->count();
        $diaryCreatedToday = ActivityLog::where('action', 'diary_created')
            ->whereDate('created_at', today())
            ->count();
        $diagnosisCompletedThisWeek = ActivityLog::where('action', 'diagnosis_completed')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $diaryCreatedThisWeek = ActivityLog::where('action', 'diary_created')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Recent activities (latest 20)
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.dashboard', [
            'activeUserStats' => $activeUserStats,
            'newUsersToday' => $newUsersToday,
            'newUsersThisWeek' => $newUsersThisWeek,
            'newUsersThisMonth' => $newUsersThisMonth,
            'diagnosisCompletedToday' => $diagnosisCompletedToday,
            'diaryCreatedToday' => $diaryCreatedToday,
            'diagnosisCompletedThisWeek' => $diagnosisCompletedThisWeek,
            'diaryCreatedThisWeek' => $diaryCreatedThisWeek,
            'recentActivities' => $recentActivities,
        ]);
    }
}
