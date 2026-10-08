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
        // Web middleware uses Laravel's default session/cookie stack.
        // The application intentionally does not persist submitted customer data.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default Laravel exception handling.
    })->create();
