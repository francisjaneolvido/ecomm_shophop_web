<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionHttpsRoutingTest extends TestCase
{
    public function test_production_generates_secure_urls_for_both_hosts_and_admin_actions(): void
    {
        $this->bootProductionEnvironment();

        $main = config('app.main_domain');
        $logistics = config('app.logistics_domain');

        // The origin connection is HTTP behind Cloudflare Tunnel. Browser-facing URLs remain HTTPS.
        $this->assertSame("https://{$main}/admin/users/5/approve", route('admin.users.approve', ['user' => 5]));
        $this->assertSame("https://{$logistics}/sign-in", route('logistics.login'));

        // Client-side forms use same-origin relative destinations and never downgrade to HTTP.
        $this->assertSame('/admin/users/5/approve', route('admin.users.approve', ['user' => 5], false));

        $postRoute = Route::getRoutes()->match(Request::create("https://{$main}/admin/users/5/approve", 'POST'));
        $this->assertSame('admin.users.approve', $postRoute->getName());
    }

    private function bootProductionEnvironment(): void
    {
        $saved = [getenv('APP_ENV'), $_ENV['APP_ENV'] ?? null, $_SERVER['APP_ENV'] ?? null];

        try {
            putenv('APP_ENV=production');
            $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'production';
            $this->refreshApplication();
            $this->assertTrue($this->app->environment('production'));
        } finally {
            $saved[0] === false ? putenv('APP_ENV') : putenv('APP_ENV='.$saved[0]);
            foreach (['_ENV', '_SERVER'] as $index => $global) {
                if ($saved[$index + 1] === null) {
                    unset($GLOBALS[$global]['APP_ENV']);
                } else {
                    $GLOBALS[$global]['APP_ENV'] = $saved[$index + 1];
                }
            }
        }
    }
}
