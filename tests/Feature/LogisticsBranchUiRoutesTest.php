<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Safe route/view checks; no database resets or destructive migrations. */
class LogisticsBranchUiRoutesTest extends TestCase
{
    public function test_company_branch_pages_are_protected_get_routes(): void
    {
        foreach ([
            'logistics.sorting-centers.index',
            'logistics.sorting-centers.create',
            'logistics.sorting-centers.show',
            'logistics.sorting-centers.edit',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertContains('GET', $route->methods());
            $this->assertContains('auth', $route->gatherMiddleware());
        }
    }

    public function test_branch_directory_editor_and_details_views_exist(): void
    {
        foreach (['index', 'form', 'show'] as $name) {
            $this->assertTrue(view()->exists('logistics.sorting-centers.'.$name));
        }
    }
}
