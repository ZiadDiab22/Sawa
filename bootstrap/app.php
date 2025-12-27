<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check_admin' => \App\Http\Middleware\CheckAdmin::class,
            'check_driver' => \App\Http\Middleware\CheckDriver::class,
            'check_user' => \App\Http\Middleware\CheckUser::class,
            'driver.commission.check' => \App\Http\Middleware\CheckDriverCommissionStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
