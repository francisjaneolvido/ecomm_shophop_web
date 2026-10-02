<?php

namespace Tests\Feature;

use Tests\TestCase;

// Preview isolation is an actual environment-booted route contract, not a source-string convention.
class ProductionPreviewIsolationTest extends TestCase
{
    private const LEGACY_PREVIEWS = [
        'debug/modal/login',
        'debug/modal/account-type',
        'debug/modal/buyer-registration',
        'debug/modal/seller-registration',
        'debug/modal/logistics-registration',
        'dev/loading-preview',
    ];

    // Production must not register any TEST, modal or loading preview, even without a visible navigation link.
    public function test_production_route_collection_excludes_all_development_previews(): void
    {
        $this->bootEnvironment('production');
        $this->assertSame([], $this->previewUris());
        foreach (self::LEGACY_PREVIEWS as $uri) {
            $this->get('/'.$uri)->assertNotFound();
        }
        $this->get('/__dev/accounts')->assertNotFound();
        $this->assertNormalRoutes();
    }

    // Testing does not implicitly authorize TEST previews; the established supported preview context is local only.
    public function test_testing_route_collection_excludes_development_previews(): void
    {
        $this->bootEnvironment('testing');
        $this->assertSame([], $this->previewUris());
        $this->assertNormalRoutes();
    }

    // Useful local modal/loading previews and TEST destinations remain available without authenticating a product identity.
    public function test_local_route_collection_preserves_intended_previews_and_normal_routes(): void
    {
        $this->bootEnvironment('local');
        $uris = $this->previewUris();
        foreach ([...self::LEGACY_PREVIEWS, '__dev/accounts', '__dev/preview/buyer/messages'] as $uri) {
            $this->assertContains($uri, $uris);
        }
        $this->get('/dev/loading-preview')->assertOk();
        $this->assertGuest('web');
        $this->assertNormalRoutes();
    }

    // Reboot through bootstrap/app.php using process-local environment inputs; restore them without touching .env or runtime data.
    private function bootEnvironment(string $environment): void
    {
        $previous = [getenv('APP_ENV'), $_ENV['APP_ENV'] ?? null, $_SERVER['APP_ENV'] ?? null];
        try {
            putenv('APP_ENV='.$environment);
            $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = $environment;
            $this->refreshApplication();
            $this->assertSame($environment, $this->app->environment());
            $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        } finally {
            $previous[0] === false ? putenv('APP_ENV') : putenv('APP_ENV='.$previous[0]);
            foreach (['_ENV', '_SERVER'] as $index => $global) {
                if ($previous[$index + 1] === null) {
                    unset($GLOBALS[$global]['APP_ENV']);
                } else {
                    $GLOBALS[$global]['APP_ENV'] = $previous[$index + 1];
                }
            }
        }
    }

    // Match every preview prefix so future additions cannot silently escape the same environment boundary.
    private function previewUris(): array
    {
        return array_values(array_map(fn ($route) => $route->uri(), array_filter(
            $this->app['router']->getRoutes()->getRoutes(),
            fn ($route) => preg_match('#^(?:__dev|debug|dev)/#', $route->uri()) === 1,
        )));
    }

    // Isolation must retain public navigation and native authentication, together with each protected role destination.
    private function assertNormalRoutes(): void
    {
        foreach (['home', 'search.index', 'login', 'login.store', 'logout', 'buyer.dashboard',
            'buyer.messages', 'seller.dashboard', 'admin.dashboard', 'admin.settings'] as $name) {
            $this->assertNotNull($this->app['router']->getRoutes()->getByName($name), $name);
        }
    }
}
