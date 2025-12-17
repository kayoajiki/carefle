<?php

namespace App\Livewire\Actions;

use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        $user = Auth::user();
        
        // Log logout activity before logging out
        if ($user) {
            $this->activityLogService->logLogout($user->id);
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
