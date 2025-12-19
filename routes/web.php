<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\ProfileSetup;
use App\Livewire\LifeHistory;
use App\Livewire\LifeHistoryTimeline;
use App\Livewire\DiagnosisImportanceForm;
use App\Livewire\WcmForm;
use App\Livewire\DiaryCalendar;
use App\Livewire\PersonalityAssessmentForm;
use App\Livewire\PersonalityAssessmentVisualization;
use App\Livewire\CareerTimeline;
use App\Livewire\CareerMilestoneBoard;
use App\Livewire\CareerMilestoneForm;
use App\Livewire\CareerHistoryUploadForm;
use App\Livewire\ResumeUploadForm;
use App\Http\Controllers\DiagnosisController;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CareerHistoryController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth']) // メール認証機能（一時的にコメントアウト）: 'verified'を削除
    ->name('dashboard');

// Google OAuth routes (一時的にコメントアウト)
// Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
// Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('profile/setup', ProfileSetup::class)
        ->name('profile.setup');
    
    // プチ取説
    Route::get('onboarding/mini-manual', [\App\Http\Controllers\OnboardingController::class, 'showMiniManual'])
        ->name('onboarding.mini-manual');
    Route::post('onboarding/mini-manual/update', [\App\Http\Controllers\OnboardingController::class, 'updateMiniManual'])
        ->name('onboarding.mini-manual.update');
    Route::get('onboarding/mini-manual/pdf', [\App\Http\Controllers\OnboardingController::class, 'downloadMiniManualPdf'])
        ->name('onboarding.mini-manual.pdf');
    
    // コンテキスト別取説
    Route::get('manual', [\App\Http\Controllers\ManualController::class, 'index'])
        ->name('manual.index');
    Route::get('manual/context/{context}', [\App\Http\Controllers\ManualController::class, 'showContextualManual'])
        ->name('manual.context');
    
    // マッピング
    Route::get('mapping', [\App\Http\Controllers\MappingController::class, 'index'])
        ->name('mapping.index');
    Route::get('diagnosis/start', [DiagnosisController::class, 'start'])
        ->name('diagnosis.start');
    Route::get('diagnosis/result/{id}', [DiagnosisController::class, 'result'])
        ->name('diagnosis.result');
    Route::get('diagnosis/importance/{id}', DiagnosisImportanceForm::class)
        ->name('diagnosis.importance');
    Route::get('life-history', function () {
        return view('life-history');
    })->name('life-history');
    Route::get('life-history/timeline', LifeHistoryTimeline::class)
        ->name('life-history.timeline');

    // 日記
    Route::get('diary', function () {
        return view('diary');
    })->name('diary');
    Route::get('diary/chat', function () {
        return view('diary.chat');
    })->name('diary.chat');
    // TODO: WeeklyReflectionクラスを実装後に有効化
    // Route::get('reflection/weekly', \App\Livewire\WeeklyReflection::class)
    //     ->name('reflection.weekly');
    // TODO: これらのLivewireコンポーネントを実装後に有効化
    // Route::get('milestones/progress', \App\Livewire\MilestoneProgressVisualization::class)
    //     ->name('milestones.progress');
    // Route::get('growth', \App\Livewire\GrowthVisualization::class)
    //     ->name('growth');
    // Route::get('actions/log', \App\Livewire\ActionLog::class)
    //     ->name('actions.log');
    // Route::get('reflection/archive', \App\Livewire\ReflectionArchive::class)
    //     ->name('reflection.archive');

    // WCM
    Route::get('wcm/start', WcmForm::class)->name('wcm.start');
    Route::get('wcm/sheet/{id}', function ($id) {
        return view('wcm.sheet', ['id' => (int)$id]);
    })->name('wcm.sheet');

    // 面談申し込み（外部リンクに変更したため削除）
    // Route::get('consultation/request', function () {
    //     return view('consultation.request');
    // })->name('consultation.request');

    // チャット相談（LINE登録に変更したため削除）
    // Route::get('chat', function () {
    //     return view('chat.index');
    // })->name('chat.index');

    // マイゴール
    Route::get('my-goal', \App\Livewire\MyGoal::class)->name('my-goal');
    Route::post('my-goal/display-mode', function (Request $request) {
        $mode = $request->string('mode')->toString();
        if (!in_array($mode, ['text', 'image'], true)) {
            return back();
        }
        $user = $request->user();
        if ($user) {
            $user->update(['goal_display_mode' => $mode]);
        }
        return back();
    })->name('my-goal.display-mode');
    
    // チャットAPI
    Route::post('chat/message', [ChatController::class, 'sendMessage'])
        ->name('chat.message');

    // 自己診断
    Route::get('assessments', function () {
        return view('personality-assessments');
    })->name('assessments.index');
    Route::get('assessments/visualization', PersonalityAssessmentVisualization::class)->name('assessments.visualization');

    // キャリアタイムライン
    Route::get('career/timeline', CareerTimeline::class)->name('career.timeline');
    Route::get('career/milestones', CareerMilestoneBoard::class)->name('career.milestones');

    // 職務経歴書
    Route::get('career-history/upload', function () {
        return view('career-history.upload');
    })->name('career-history.upload');
    Route::get('career-history/{id}/view', [CareerHistoryController::class, 'view'])->name('career-history.view');

    // 履歴書
    Route::get('resume/upload', function () {
        return view('resume.upload');
    })->name('resume.upload');
    Route::get('resume/{id}/view', [ResumeController::class, 'view'])->name('resume.view');

});


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
    Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('activity-logs/export', [\App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('admin.activity-logs.export');
    Route::get('profile-stats', [\App\Http\Controllers\Admin\ProfileStatsController::class, 'index'])->name('admin.profile-stats.index');
    Route::get('profile-stats/export', [\App\Http\Controllers\Admin\ProfileStatsController::class, 'export'])->name('admin.profile-stats.export');
});
