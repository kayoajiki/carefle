<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 信頼できるプロキシを設定（ロードバランサーやリバースプロキシ経由の場合）
        $middleware->trustProxies(at: '*');
        
        // CSRF保護の設定
        $middleware->validateCsrfTokens(except: [
            // CSRF除外が必要なエンドポイントがあれば追加
        ]);
        
        // 管理者認証ミドルウェアのエイリアス
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
