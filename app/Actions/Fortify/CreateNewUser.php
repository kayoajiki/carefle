<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // メール認証機能（一時的にコメントアウト）
        // Send email verification notification (if mail is configured)
        // try {
        //     $user->sendEmailVerificationNotification();
        // } catch (\Exception $e) {
        //     // Log error but don't fail registration if mail is not configured
        //     \Log::warning('Failed to send email verification: ' . $e->getMessage());
        // }

        // Log user registration
        $this->activityLogService->logUserRegistration($user->id);

        return $user;
    }
}
