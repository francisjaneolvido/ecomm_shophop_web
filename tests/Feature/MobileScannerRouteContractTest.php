<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Non-destructive checks: never creates, truncates, or migrates tables. */
class MobileScannerRouteContractTest extends TestCase
{
    public function test_preview_and_history_are_authenticated_read_only_routes(): void
    {
        foreach (['preview', 'history'] as $action) {
            $route = Route::getRoutes()->match(Request::create("/api/mobile/logistics/scanner/{$action}", 'GET'));
            $this->assertSame('GET', $route->methods()[0]);
            $this->assertContains('auth:sanctum', $route->gatherMiddleware());
            $this->assertContains('throttle:60,1', $route->gatherMiddleware());
        }
    }

    public function test_receipt_remains_an_authenticated_post_action(): void
    {
        $route = Route::getRoutes()->match(Request::create('/api/mobile/logistics/scanner/receive', 'POST'));
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertContains('throttle:30,1', $route->gatherMiddleware());
    }
}
