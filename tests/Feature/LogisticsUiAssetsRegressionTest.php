<?php

namespace Tests\Feature;

use Tests\TestCase;

class LogisticsUiAssetsRegressionTest extends TestCase
{
    public function test_shared_operations_directory_assets_are_loaded_from_logistics_layout(): void
    {
        $layout = file_get_contents(resource_path('views/logistics/layouts/console.blade.php'));
        $this->assertStringContainsString("asset('css/logistics-directory-v1.css')", $layout);
        $this->assertStringContainsString("asset('js/logistics-directory-v1.js')", $layout);
        $this->assertFileExists(public_path('css/logistics-directory-v1.css'));
        $this->assertFileExists(public_path('js/logistics-directory-v1.js'));
    }

    public function test_directory_pages_use_the_shared_table_and_modal_classes(): void
    {
        foreach ([
            'sorting-centers/index', 'sorting-centers/branches',
            'sorting-centers/show', 'demo-network/index', 'riders/index',
            'fleet/index', 'route-network/index',
        ] as $page) {
            $content = file_get_contents(resource_path("views/logistics/{$page}.blade.php"));
            $this->assertStringContainsString('ops-table', $content, "{$page} is missing the shared table markup");
        }
        $script = file_get_contents(public_path('js/logistics-directory-v1.js'));
        $this->assertStringContainsString('data-ops-record-edit', $script);
        $this->assertStringContainsString('data-ops-open', $script);
    }
}
