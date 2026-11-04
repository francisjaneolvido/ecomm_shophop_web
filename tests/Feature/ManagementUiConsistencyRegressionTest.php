<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ManagementUiConsistencyRegressionTest extends TestCase
{
    public function test_all_five_management_layouts_load_the_shared_stylesheet(): void
    {
        $stylesheet = public_path('css/shophop-management-unified.css');
        $this->assertFileExists($stylesheet);

        foreach ([
            'admin/layout.blade.php',
            'seller/partials/layout.blade.php',
            'logistics/layouts/console.blade.php',
            'branch/layout/app.blade.php',
            'hub/layouts/console.blade.php',
        ] as $layout) {
            $view = file_get_contents(resource_path('views/'.$layout));
            $this->assertStringContainsString('css/shophop-management-unified.css', $view, $layout);
            $this->assertStringContainsString('sh-management-main', $view, $layout);
            $this->assertStringContainsString('sh-management-ui', $view, $layout);
        }
    }

    public function test_demo_qr_is_removed_but_rates_are_still_editable(): void
    {
        $view = file_get_contents(resource_path('views/admin/commission.blade.php'));
        $this->assertStringNotContainsString('admin.finance.qr', $view);
        $this->assertStringNotContainsString('ShopHop demo payment QR', $view);
        $this->assertStringContainsString('admin.finance.rates', $view);
        $this->assertStringContainsString('id="shResetRates"', $view);
        $this->assertStringContainsString('id="shSaveRates"', $view);
        $this->assertSame(16, substr_count($view, 'data-saved-rate='));
        $this->assertTrue(Route::has('admin.finance.rates'));
        $this->assertFalse(Route::has('admin.finance.qr'));
    }

    public function test_seller_earnings_has_table_and_pagination(): void
    {
        $view = file_get_contents(resource_path('views/seller/earnings.blade.php'));
        $this->assertStringContainsString('sh-management-table-scroll', $view);
        $this->assertStringContainsString('$entries->links()', $view);
    }
}
