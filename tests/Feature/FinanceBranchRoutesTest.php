<?php

namespace Tests\Feature;

use App\Services\BranchLifecycleService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Safe no-database smoke tests; do not run destructive legacy tests on live data. */
class FinanceBranchRoutesTest extends TestCase
{
    public function test_branch_archival_is_reversible_and_is_scheduled_after_thirty_days(): void
    {
        $this->assertSame(30, BranchLifecycleService::ARCHIVE_AFTER_DAYS);
        foreach (['logistics.sorting-centers.archived','logistics.sorting-centers.archive',
            'logistics.sorting-centers.restore'] as $name) {
            $this->assertNotNull(Route::getRoutes()->getByName($name), "Missing route: {$name}");
        }
        $this->assertContains('POST', Route::getRoutes()->getByName('logistics.sorting-centers.archive')->methods());
        $this->assertContains('POST', Route::getRoutes()->getByName('logistics.sorting-centers.restore')->methods());
    }

    public function test_admin_cod_confirmation_and_mobile_rider_remittance_have_distinct_roles(): void
    {
        foreach (['admin.finance.cod.index','admin.finance.cod.confirm',
            'logistics.settlements.remit-admin'] as $name) {
            $this->assertNotNull(Route::getRoutes()->getByName($name));
        }
        $this->assertContains('POST', Route::getRoutes()->getByName('admin.finance.cod.confirm')->methods());
        $this->assertContains('POST', Route::getRoutes()->getByName('logistics.settlements.remit-admin')->methods());
    }
}
