<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Models\PersonalityAssessment;
use App\Models\WcmSheet;
use App\Services\OnboardingProgressService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected OnboardingProgressService $onboardingProgressService;

    public function __construct(OnboardingProgressService $onboardingProgressService)
    {
        $this->onboardingProgressService = $onboardingProgressService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by admin status
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->is_admin === '1');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Login history (latest 50)
        $loginHistory = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Activity logs (latest 100)
        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Created data
        $diagnoses = Diagnosis::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $diaries = Diary::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();
        
        $assessments = PersonalityAssessment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $wcmSheets = WcmSheet::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Onboarding progress
        $onboardingProgress = $this->onboardingProgressService->getOrCreateProgress($user->id);

        return view('admin.users.show', [
            'user' => $user,
            'loginHistory' => $loginHistory,
            'activityLogs' => $activityLogs,
            'diagnoses' => $diagnoses,
            'diaries' => $diaries,
            'assessments' => $assessments,
            'wcmSheets' => $wcmSheets,
            'onboardingProgress' => $onboardingProgress,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['boolean'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'ユーザー情報を更新しました。');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $userId = $user->id;
        $user->delete();

        // アクティビティログに記録
        app(\App\Services\ActivityLogService::class)->logUserAccountDeleted($userId);

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザーを削除しました。');
    }
}
