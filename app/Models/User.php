<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'birthdate',
        'gender',
        'prefecture',
        'occupation',
        'industry',
        'employment_type',
        'work_experience_years',
        'education',
        'profile_completed',
        'reflection_style',
        'goal_setting_style',
        'ai_companion_preferences',
        'preferred_reflection_time',
        'enable_adaptive_reminders',
        'goal_image',
        'goal_image_url',
        'goal_display_mode',
        'is_admin',
        'last_login_at',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'profile_completed' => 'boolean',
            'ai_companion_preferences' => 'array',
            'goal_display_mode' => 'string',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's diaries.
     */
    public function diaries()
    {
        return $this->hasMany(Diary::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Determine if email verification is required.
     * Currently optional, but can be made required in the future.
     */
    public function shouldRequireEmailVerification(): bool
    {
        return config('fortify.email_verification_required', false);
    }

    /**
     * Send the email verification notification.
     * メール認証機能（一時的にコメントアウト）
     */
    public function sendEmailVerificationNotification()
    {
        // メール認証機能は一時的に無効化
        return;
        
        // Only send if mail is properly configured
        // $mailDriver = config('mail.default');
        // $mailHost = config('mail.mailers.smtp.host');
        // 
        // // Skip if using mailpit (development only) or mail is not configured
        // if ($mailDriver === 'log' || $mailHost === 'mailpit' || empty($mailHost)) {
        //     \Log::info('Email verification skipped: Mail not configured properly');
        //     return;
        // }
        // 
        // try {
        //     $this->notify(new \App\Notifications\VerifyEmail);
        // } catch (\Exception $e) {
        //     \Log::warning('Failed to send email verification notification: ' . $e->getMessage());
        // }
    }
}