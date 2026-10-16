<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Non-destructive route/guard contract tests. No table creation, seeding, or database reset. */
class Phase4BranchStaffRoutesTest extends TestCase
{
    public function test_staff_identity_has_an_independent_auth_provider(): void
    {
        $this->assertSame('logistics_staff', config('auth.guards.logistics_staff.provider'));
        $this->assertSame(\App\Models\Logistics\BranchStaff::class,
            config('auth.providers.logistics_staff.model'));
    }

    public function test_branch_operator_workspace_requires_its_own_guard(): void
    {
        $route = Route::getRoutes()->getByName('logistics.staff.workspace');
        $this->assertNotNull($route);
        $this->assertContains('auth:logistics_staff', $route->gatherMiddleware());
    }

    public function test_company_owner_only_can_provision_staff(): void
    {
        foreach (['logistics.staff.store', 'logistics.staff.toggle', 'logistics.staff.reset-password'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertContains('approved.role:logistics', $route->gatherMiddleware());
        }
    }

    public function test_branch_manager_workspace_cannot_directly_open_company_finance_route(): void
    {
        $route = Route::getRoutes()->getByName('logistics.earnings');
        $this->assertNotNull($route);
        $this->assertContains('approved.role:logistics', $route->gatherMiddleware());
        $this->assertNotContains('auth:logistics_staff', $route->gatherMiddleware());
    }
}
