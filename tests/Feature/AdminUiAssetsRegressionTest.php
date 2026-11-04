<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminUiAssetsRegressionTest extends TestCase
{
    public function test_admin_layout_links_required_shared_styles_and_script(): void
    {
        $layout = file_get_contents(resource_path('views/admin/layout.blade.php'));

        $this->assertFileExists(public_path('css/shophop-admin-operations-v1.css'));
        $this->assertFileExists(public_path('js/shophop-admin-operations-v1.js'));
        $this->assertStringContainsString('css/shophop-admin-operations-v1.css', $layout);
        $this->assertStringContainsString('js/shophop-admin-operations-v1.js', $layout);
        $this->assertStringContainsString('id="adminMain"', $layout);
        $this->assertStringContainsString('id="adminMainContent"', $layout);
    }

    public function test_admin_finance_and_compliance_actions_still_have_named_routes(): void
    {
        foreach ([
            'admin.registrations.show', 'admin.users.approve', 'admin.users.reject',
            'admin.compliance.approve', 'admin.compliance.reject', 'admin.disputes.show',
            'admin.finance.cod.confirm', 'admin.finance.prepare',
            'admin.finance.distribute', 'admin.finance.paid', 'admin.returns.refund-paid',
        ] as $name) {
            $this->assertTrue(Route::has($name), "Missing Admin route: {$name}");
        }
    }
}
