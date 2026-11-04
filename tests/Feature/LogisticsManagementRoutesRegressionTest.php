<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Contract for Logistics Management links. No DB writes or migrations. */
class LogisticsManagementRoutesRegressionTest extends TestCase
{
    public function test_management_routes_exist_with_correct_method_and_access_middleware(): void
    {
        $expected = [
            'logistics.branches.index' => ['GET', 'branches'],
            'logistics.branches.profile' => ['POST', 'branches/{center}/profile'],
            'logistics.sorting-centers.profile' => ['POST', 'sorting-centers/{center}/profile'],
            'logistics.riders.profile' => ['POST', 'riders/{rider}/profile'],
            'logistics.fleet.vehicles.update' => ['POST', 'fleet/vehicles/{vehicle}/update'],
            'logistics.fleet.drivers.update' => ['POST', 'fleet/drivers/{driver}/update'],
            'logistics.reports.export.csv' => ['GET', 'reports/export/csv'],
        ];

        foreach ($expected as $name => [$httpMethod, $uri]) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing route: {$name}");
            $this->assertContains($httpMethod, $route->methods(), "Wrong HTTP method: {$name}");
            $this->assertSame($uri, $route->uri(), "Wrong route URL: {$name}");
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware, "Unprotected route: {$name}");
            $this->assertContains('approved.role:logistics', $middleware, "Incorrect access role: {$name}");
        }
    }

    public function test_logistics_management_views_only_reference_registered_named_routes(): void
    {
        $paths = [
            'logistics/sorting-centers/index.blade.php',
            'logistics/sorting-centers/branches.blade.php',
            'logistics/sorting-centers/show.blade.php',
            'logistics/riders/index.blade.php',
            'logistics/fleet/index.blade.php',
            'logistics/route-network/index.blade.php',
            'logistics/layouts/console.blade.php',
        ];

        foreach ($paths as $path) {
            $file = resource_path('views/'.$path);
            $this->assertFileExists($file);
            preg_match_all('/\broute\s*\(\s*[\'\"](logistics\.[a-zA-Z0-9._-]+)[\'\"]/', file_get_contents($file), $matches);
            foreach (array_unique($matches[1]) as $name) {
                $this->assertTrue(Route::has($name), "{$path} references undefined route {$name}");
            }
        }
    }
}
