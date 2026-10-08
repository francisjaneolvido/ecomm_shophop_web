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
        // Register logistics before marketplace routes to avoid '/' host collisions.
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Separate Logistics hostname, but same codebase / backend.
            Route::middleware('web')->group(base_path('routes/logistics.php'));

            // Marketplace routes must NOT also be served from logistics.<domain>.
            Route::middleware('web')
                ->domain(config('app.main_domain'))
                ->group(function () {
                    require base_path('routes/web.php');
                    require base_path('routes/admin.php');
                    require base_path('routes/rider.php');
                    require base_path('routes/buyer.php');
                    require base_path('routes/seller.php');
                    require base_path('routes/debug.php');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Deployment terminates HTTPS at a proxy; honor forwarded scheme for secure links and cookies.
        $middleware->trustProxies(at: '*');

        // Guests opening a protected logistics URL should not be sent to
        // the main ShopHop login / account type chooser.
        $middleware->redirectGuestsTo(fn (Request $request) =>
            $request->getHost() === config('app.logistics_domain')
                ? route('logistics.login')
                : route('login')
        );

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
