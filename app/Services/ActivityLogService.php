<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log a generic action.
     */
    public function logAction(
        int $userId,
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log user login.
     */
    public function logLogin(int $userId, ?string $ipAddress = null, ?string $userAgent = null, bool $success = true): ActivityLog
    {
        $action = $success ? 'login' : 'login_failed';
        
        return $this->logAction(
            $userId,
            $action,
            null,
            null,
            ['success' => $success],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log user logout.
     */
    public function logLogout(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'logout');
    }

    /**
     * Log user registration.
     */
    public function logUserRegistration(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'user_registered');
    }

    /**
     * Log Google authentication login.
     */
    public function logGoogleLogin(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'google_login', null, null, ['provider' => 'google']);
    }

    /**
     * Log Google authentication registration.
     */
    public function logGoogleRegistration(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'google_registration', null, null, ['provider' => 'google']);
    }

    /**
     * Log profile completion.
     */
    public function logProfileCompleted(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'profile_completed');
    }

    /**
     * Log email verification.
     */
    public function logEmailVerified(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'email_verified');
    }

    /**
     * Log diagnosis completion.
     */
    public function logDiagnosisCompleted(int $userId, int $diagnosisId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'diagnosis_completed',
            'App\Models\Diagnosis',
            $diagnosisId,
            ['diagnosis_id' => $diagnosisId]
        );
    }

    /**
     * Log diary creation (first diary only).
     */
    public function logDiaryCreated(int $userId, int $diaryId, string $date): ActivityLog
    {
        return $this->logAction(
            $userId,
            'diary_created',
            'App\Models\Diary',
            $diaryId,
            ['diary_id' => $diaryId, 'date' => $date]
        );
    }

    /**
     * Log personality assessment completion.
     */
    public function logPersonalityAssessmentCompleted(int $userId, int $assessmentId, string $assessmentType): ActivityLog
    {
        return $this->logAction(
            $userId,
            'personality_assessment_completed',
            'App\Models\PersonalityAssessment',
            $assessmentId,
            ['assessment_id' => $assessmentId, 'type' => $assessmentType]
        );
    }

    /**
     * Log WCM sheet completion.
     */
    public function logWcmSheetCompleted(int $userId, int $wcmSheetId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'wcm_sheet_completed',
            'App\Models\WcmSheet',
            $wcmSheetId,
            ['wcm_sheet_id' => $wcmSheetId]
        );
    }

    /**
     * Log life event creation.
     */
    public function logLifeEventCreated(int $userId, int $eventCount): ActivityLog
    {
        return $this->logAction(
            $userId,
            'life_event_created',
            null,
            null,
            ['event_count' => $eventCount]
        );
    }

    /**
     * Log career milestone creation.
     */
    public function logCareerMilestoneCreated(int $userId, int $milestoneId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'career_milestone_created',
            'App\Models\CareerMilestone',
            $milestoneId,
            ['milestone_id' => $milestoneId]
        );
    }

    /**
     * Log 7-day diary completion.
     */
    public function log7DayDiaryCompleted(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'diary_7days_completed');
    }

    /**
     * Log strengths report generation.
     */
    public function logStrengthsReportGenerated(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'strengths_report_generated');
    }

    /**
     * Log contextual manual generation.
     */
    public function logContextualManualGenerated(int $userId, string $context): ActivityLog
    {
        return $this->logAction(
            $userId,
            'contextual_manual_generated',
            null,
            null,
            ['context' => $context]
        );
    }

    /**
     * Log mapping progress completion.
     */
    public function logMappingProgressCompleted(int $userId, string $section): ActivityLog
    {
        return $this->logAction(
            $userId,
            'mapping_progress_completed',
            null,
            null,
            ['section' => $section]
        );
    }

    /**
     * Log diagnosis deletion.
     */
    public function logDiagnosisDeleted(int $userId, int $diagnosisId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'diagnosis_deleted',
            'App\Models\Diagnosis',
            $diagnosisId,
            ['diagnosis_id' => $diagnosisId]
        );
    }

    /**
     * Log diary deletion.
     */
    public function logDiaryDeleted(int $userId, int $diaryId, string $date): ActivityLog
    {
        return $this->logAction(
            $userId,
            'diary_deleted',
            'App\Models\Diary',
            $diaryId,
            ['diary_id' => $diaryId, 'date' => $date]
        );
    }

    /**
     * Log personality assessment deletion.
     */
    public function logPersonalityAssessmentDeleted(int $userId, int $assessmentId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'personality_assessment_deleted',
            'App\Models\PersonalityAssessment',
            $assessmentId,
            ['assessment_id' => $assessmentId]
        );
    }

    /**
     * Log WCM sheet deletion.
     */
    public function logWcmSheetDeleted(int $userId, int $wcmSheetId): ActivityLog
    {
        return $this->logAction(
            $userId,
            'wcm_sheet_deleted',
            'App\Models\WcmSheet',
            $wcmSheetId,
            ['wcm_sheet_id' => $wcmSheetId]
        );
    }

    /**
     * Log user account deletion.
     */
    public function logUserAccountDeleted(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'user_account_deleted');
    }

    /**
     * Log resume upload.
     */
    public function logResumeUploaded(int $userId, int $resumeId, string $filename, int $fileSize): ActivityLog
    {
        return $this->logAction(
            $userId,
            'resume_uploaded',
            'App\Models\Resume',
            $resumeId,
            ['resume_id' => $resumeId, 'filename' => $filename, 'file_size' => $fileSize]
        );
    }

    /**
     * Log career history document upload.
     */
    public function logCareerHistoryUploaded(int $userId, int $documentId, string $filename, int $fileSize): ActivityLog
    {
        return $this->logAction(
            $userId,
            'career_history_uploaded',
            'App\Models\CareerHistoryDocument',
            $documentId,
            ['document_id' => $documentId, 'filename' => $filename, 'file_size' => $fileSize]
        );
    }

    /**
     * Log goal image upload.
     */
    public function logGoalImageUploaded(int $userId, string $filename, int $fileSize): ActivityLog
    {
        return $this->logAction(
            $userId,
            'goal_image_uploaded',
            null,
            null,
            ['filename' => $filename, 'file_size' => $fileSize]
        );
    }

    /**
     * Log password reset request.
     */
    public function logPasswordResetRequested(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'password_reset_requested');
    }

    /**
     * Log password reset completed.
     */
    public function logPasswordResetCompleted(int $userId): ActivityLog
    {
        return $this->logAction($userId, 'password_reset_completed');
    }
}


