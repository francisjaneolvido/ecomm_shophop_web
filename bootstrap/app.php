<?php

use App\Http\Middleware\EnsureApprovedRole;
use App\Http\Middleware\EnsureActiveRider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/logistics.php'));
            // Rider routes use the same web sessions with a separate, partner-owned guard.
            Route::middleware('web')->group(base_path('routes/rider.php'));
            Route::middleware('web')->group(base_path('routes/buyer.php'));
            Route::middleware('web')->group(base_path('routes/seller.php'));
            Route::middleware('web')->group(base_path('routes/debug.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Deployment terminates HTTPS at a proxy; honor forwarded scheme for secure links and cookies.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'approved.role' => EnsureApprovedRole::class,
            // Active eligibility is checked on every Rider request, not only at login.
            'active.rider' => EnsureActiveRider::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') ||
                $request->expectsJson(),
        );
    })
    ->create();
