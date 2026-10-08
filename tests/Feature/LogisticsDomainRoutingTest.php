<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LogisticsDomainRoutingTest extends TestCase
{
    public function test_main_and_logistics_home_routes_are_host_scoped(): void
    {
        $marketplace = config('app.main_domain');
        $logistics = config('app.logistics_domain');

        $this->assertNotSame($marketplace, $logistics);
        $this->assertSame('home', $this->matchingRoute("https://{$marketplace}/", 'GET'));
        $this->assertSame('logistics.home', $this->matchingRoute("https://{$logistics}/", 'GET'));
        $this->assertSame('logistics.register', $this->matchingRoute("https://{$logistics}/apply", 'GET'));
        $this->assertSame('logistics.register.store', $this->matchingRoute("https://{$logistics}/apply", 'POST'));
        $this->assertSame('logistics.login', $this->matchingRoute("https://{$logistics}/sign-in", 'GET'));
        $this->assertSame('logistics.dashboard', $this->matchingRoute("https://{$logistics}/dashboard", 'GET'));
        $this->assertSame('logistics.legacy-apply', $this->matchingRoute("https://{$marketplace}/logistics-partner/apply", 'GET'));
    }

    public function test_mobile_api_route_is_still_available_on_main_host(): void
    {
        $marketplace = config('app.main_domain');
        $route = Route::getRoutes()->match(Request::create("https://{$marketplace}/api/mobile/rider/logistics-centers", 'GET'));
        $this->assertSame('api/mobile/rider/logistics-centers', $route->uri());
    }

    private function matchingRoute(string $url, string $method): ?string
    {
        return Route::getRoutes()->match(Request::create($url, $method))->getName();
    }
}
