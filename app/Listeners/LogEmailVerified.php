<?php

namespace App\Listeners;

use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Verified;

class LogEmailVerified
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $this->activityLogService->logEmailVerified($event->user->id);
    }
}









