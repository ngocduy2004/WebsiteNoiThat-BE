<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       // Thêm dòng này để ưu tiên xử lý CORS
    $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

    // Tin tưởng Proxy Railway (Sửa lỗi 405 Method Not Allowed)
    $middleware->trustProxies(at: '*');

    $middleware->validateCsrfTokens(except: [
        'api/*', 
    ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
