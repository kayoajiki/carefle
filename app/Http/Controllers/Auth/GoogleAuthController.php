<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirect()
    {
        // Check if Google OAuth is configured
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')
                ->with('error', 'Google認証が設定されていません。管理者にお問い合わせください。');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(Request $request)
    {
        // Check if Google OAuth is configured
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')
                ->with('error', 'Google認証が設定されていません。管理者にお問い合わせください。');
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by email
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Existing user - update Google ID and avatar if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                } else {
                    // Update avatar if changed
                    if ($user->avatar !== $googleUser->avatar) {
                        $user->update(['avatar' => $googleUser->avatar]);
                    }
                }

                // Log login
                $this->activityLogService->logGoogleLogin($user->id);
                
                $isNewUser = false;
            } else {
                // New user - create account
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(), // Google accounts are already verified
                    'password' => Hash::make(uniqid('', true)), // Random password for Google users
                ]);

                // Log registration
                $this->activityLogService->logGoogleRegistration($user->id);
                
                $isNewUser = true;
            }

            // Login the user
            Auth::login($user, true);

            // Update last login
            $user->update(['last_login_at' => now()]);

            // If new user or profile not completed, redirect to profile setup
            if ($isNewUser || !$user->profile_completed) {
                return redirect()->route('profile.setup');
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Google認証に失敗しました。もう一度お試しください。');
        }
    }
}

