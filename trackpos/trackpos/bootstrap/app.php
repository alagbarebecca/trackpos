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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'can' => \App\Http\Middleware\PermissionMiddleware::class,
            'CheckUserStatus' => \App\Http\Middleware\CheckUserStatus::class,
        ]);
        
        // Exclude install routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'install/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
