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
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('profile/setup', ProfileSetup::class)
        ->name('profile.setup');
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

    // WCM
    Route::get('wcm/start', WcmForm::class)->name('wcm.start');
    Route::get('wcm/sheet/{id}', function ($id) {
        return view('wcm.sheet', ['id' => (int)$id]);
    })->name('wcm.sheet');

    // 面談申し込み
    Route::get('consultation/request', function () {
        return view('consultation.request');
    })->name('consultation.request');

    // チャット相談
    Route::get('chat', function () {
        return view('chat.index');
    })->name('chat.index');
    
    // チャットAPI
    Route::post('chat/message', [ChatController::class, 'sendMessage'])
        ->name('chat.message');

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
