<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Public Store URLs must disclose availability rather than invent a Seller or persisted shop behavior.
class BuyerStoreJourneyTest extends TestCase
{
    // Exercise the existing guest HTTP seam without Seller/Product records or normal business data.
    public function test_public_store_does_not_invent_a_shop_without_seller_resolution(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $response = $this->get(route('buyer.store.show'))->assertOk();
        $this->assertFalse(str_contains($response->getContent(), 'ShopHop Tech Store'),
            'A public Store request without Seller resolution must not fabricate ShopHop Tech Store.');
        $response->assertSee('Individual Store pages are not available yet.')
            ->assertSee('This address does not open a live shop.')
            ->assertSee('Store browsing and shop interactions are unavailable.');
    }

    // Legacy text and internal numeric references disclose the same limitation without claiming a resolved Seller.
    public function test_legacy_parameters_do_not_resolve_fabricated_or_default_sellers(): void
    {
        foreach (['shophop-tech-store', 'unknown-seller', '1'] as $parameter) {
            $response = $this->get(route('buyer.store.show', ['slug' => $parameter]));
            $response->assertOk()->assertSee('Individual Store pages are not available yet.')
                ->assertSee('This address does not open a live shop.')
                ->assertDontSee('ShopHop Tech Store')->assertDontSee('@shophoptech');
        }
    }

    // The Store content offers only a real public Search link; shared navigation is outside this bounded correction.
    public function test_store_has_no_fake_interactions_products_or_metrics(): void
    {
        $response = $this->get(route('buyer.store.show'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $store = $xpath->query('//*[@id="buyerStore"]');
        $this->assertSame(1, $store->length);
        $this->assertSame(0, $xpath->query('//*[@id="buyerStore"]//*[self::form or self::button or self::input or self::select or self::textarea or self::script or self::table or self::img]')->length);
        $links = $xpath->query('//*[@id="buyerStore"]//a');
        $this->assertSame(1, $links->length);
        $this->assertSame(route('search.index'), $links->item(0)->getAttribute('href'));
        $this->assertSame('Search ShopHop products', trim($links->item(0)->textContent));

        // Product Reviews and Seller approval do not define Store ratings, merchant badges or account-level claims.
        foreach (['Follow', 'Claim', 'Rating', 'Reviews', 'MALL', 'Preferred', 'Verified Merchant',
            'Chat Performance', 'Joined', 'Sold', '354,200', '330,000', 'TECH100', '₱'] as $unsupported) {
            $this->assertStringNotContainsString($unsupported, $store->item(0)->textContent);
        }
        foreach (['followStoreBtn', 'claim-voucher-btn', 'You are now following this store.',
            'Voucher claimed successfully.', 'data-store-product', 'storeSearch'] as $fakeHandler) {
            $response->assertDontSee($fakeHandler);
        }
    }

    // Availability URLs accept no Store writes, and no Follow/Claim endpoints are invented to emulate persistence.
    public function test_store_urls_do_not_accept_unsupported_actions(): void
    {
        foreach (['/buyer/store', '/buyer/store/sample-slug'] as $path) {
            foreach (['post', 'patch', 'delete'] as $method) {
                $this->{$method}($path, ['follow' => true, 'claim' => 'TECH100'])
                    ->assertStatus(405)->assertSessionMissing('status');
            }
        }
        foreach (['follow', 'claim'] as $action) {
            $this->post('/buyer/store/sample-slug/'.$action)->assertNotFound();
        }
    }

    // The existing local TEST mapping renders the same limitation and supplies no fixture shop identity.
    public function test_local_preview_discloses_store_unavailability(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/buyer/storefront')->assertOk()
            ->assertSee('Individual Store pages are not available yet.')
            ->assertDontSee('ShopHop Tech Store')->assertDontSee('followStoreBtn')
            ->assertDontSee('claim-voucher-btn');
    }
}
