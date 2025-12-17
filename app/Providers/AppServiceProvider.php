<?php

namespace App\Providers;

use App\Listeners\LogEmailVerified;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use App\View\Composers\SidebarComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // タイムゾーンを日本時間に設定（config/app.phpのtimezone設定を使用）
        date_default_timezone_set(config('app.timezone'));
        
        // サイドバーにデータを渡す
        View::composer('components.layouts.app.sidebar', SidebarComposer::class);
        
        // イベントリスナーを登録
        Event::listen(Verified::class, LogEmailVerified::class);
    }
}
