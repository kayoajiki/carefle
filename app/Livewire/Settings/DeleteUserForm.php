<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        $userId = $user->id;

        // アクティビティログに記録（削除前に記録）
        $activityLogService = app(ActivityLogService::class);
        $activityLogService->logUserAccountDeleted($userId);

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
