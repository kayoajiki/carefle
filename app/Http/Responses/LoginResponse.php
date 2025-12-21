<?php

namespace App\Http\Responses;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        
        if ($user) {
            // Update last_login_at
            $user->last_login_at = now();
            $user->save();
            
            // Log login activity
            $this->activityLogService->logLogin(
                $user->id,
                $request->ip(),
                $request->userAgent(),
                true
            );
        }

        return redirect()->intended(config('fortify.home'));
    }
}






