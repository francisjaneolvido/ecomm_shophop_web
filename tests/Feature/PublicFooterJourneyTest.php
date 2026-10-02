<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Public rendered navigation must offer real destinations rather than placeholder support or legal promises.
class PublicFooterJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Build only an empty canonical catalogue after proving the disposable in-memory connection.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['2026_09_16_000000_ensure_products_schema_contract.php',
            'Seller/Manage_inventory/2026_09_30_000001_add_product_compliance.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // Every visible footer action must resolve somewhere beyond the empty fragment placeholder.
    public function test_public_footer_has_no_dead_placeholder_destinations(): void
    {
        $response = $this->get('/')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//footer//a[@href="#"]')->length,
            'Public footer must not present unsupported destinations as navigable placeholder links.');
    }

    // Preserve the existing Home sections on every shared footer while keeping unsupported information non-interactive.
    public function test_home_and_search_render_real_footer_links_and_unavailable_labels(): void
    {
        foreach (['/', '/search'] as $path) {
            $response = $this->get($path)->assertOk();
            $document = new \DOMDocument();
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            $this->assertSame(1, $xpath->query('//footer')->length);
            $this->assertSame(4, $xpath->query('//footer//a')->length);
            foreach (['', '#categories', '#deals', '#new-arrivals'] as $fragment) {
                $this->assertSame(1, $xpath->query('//footer//a[@href="'.route('home').$fragment.'"]')->length);
                if ($path === '/' && $fragment !== '') {
                    $this->assertSame(1, $xpath->query('//*[@id="'.substr($fragment, 1).'"]')->length);
                }
            }
            $this->assertSame(9, $xpath->query('//footer//span[@data-footer-unavailable]')->length);
            $this->assertSame(0, $xpath->query('//footer//*[@data-footer-unavailable]//*[@tabindex or @href] | //footer//*[@data-footer-unavailable and (@tabindex or @href)]')->length);
            foreach (['All Products (unavailable)', 'Customer Support', 'Shipping & Delivery', 'Returns & Refunds',
                'FAQs', 'About ShopHop', 'Contact Us', 'Terms & Conditions', 'Privacy Policy', 'Social links unavailable.'] as $label) {
                $this->assertStringContainsString($label, $xpath->query('//footer')->item(0)->textContent);
            }
            $this->assertGuest('web');
        }
    }
}
