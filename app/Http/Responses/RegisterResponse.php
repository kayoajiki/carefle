<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // Check if email verification is required
        $emailVerificationRequired = config('fortify.email_verification_required', false);

        // If email verification is required and user hasn't verified, redirect to verification notice
        if ($emailVerificationRequired && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Otherwise, redirect to profile setup
        return redirect()->route('profile.setup');
    }
}


