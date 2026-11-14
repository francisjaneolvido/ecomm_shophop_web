<?php

namespace Tests\Feature;

use App\Support\SellerProductCategories;
use Tests\TestCase;

/** These checks deliberately require no database fixtures or migrations. */
class BuyerCategoryDirectoryRemovalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Direct view rendering has no ShareErrorsFromSession middleware.
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
    }

    public function test_old_directory_bookmarks_redirect_to_product_search(): void
    {
        // Maintenance middleware reads/writes platform_settings. Isolate the
        // redirect contract so this suite needs no database setup at all.
        $this->withoutMiddleware(\App\Http\Middleware\CheckMaintenanceMode::class);

        foreach (['/categories', '/buyer/categories', '/buyer/categories?search=lamps'] as $url) {
            $this->get($url)->assertStatus(301)->assertRedirect('/search');
        }

        $this->assertFalse(view()->exists('buyer.category.index'));
    }

    public function test_rendered_storefront_navigation_has_no_directory_links(): void
    {
        foreach (['partials.shopper-header', 'partials.footer'] as $partial) {
            $html = view($partial)->render();
            $this->assertStringNotContainsString('/buyer/categories', $html);
            $this->assertStringNotContainsString('>Categories</a>', $html);
            $this->assertStringContainsString('#deals', $html);
            $this->assertStringContainsString('#new-arrivals', $html);
        }
    }

    public function test_seller_subcategory_options_remain_available(): void
    {
        $this->assertContains('Furniture & Decor', SellerProductCategories::forBusiness('Home and Garden'));
        $this->assertContains('Dog Food & Treats', SellerProductCategories::forBusiness('Pet Supplies'));
        $this->assertSame([], SellerProductCategories::forBusiness('Unknown category'));
    }

    public function test_existing_product_listing_renders_subcategory_filters_and_shop_breadcrumb(): void
    {
        $html = view('buyer.category.show', [
            'activeSlug' => 'home-and-garden',
            'products' => [],
            'previousPageUrl' => null,
            'nextPageUrl' => null,
            'subcategories' => [
                ['name' => 'Furniture & Decor', 'slug' => 'furniture-decor', 'active' => true],
            ],
        ])->render();

        $this->assertStringContainsString('sub=furniture-decor', $html);
        $this->assertStringContainsString('Furniture &amp; Decor', $html);
        $this->assertStringContainsString(route('search.index'), $html);
        $this->assertStringNotContainsString('/buyer/categories', $html);
    }

    public function test_search_destination_still_renders_without_directory_links(): void
    {
        $html = view('search.index', ['search' => '', 'products' => null])->render();

        $this->assertStringContainsString('Search ShopHop', $html);
        $this->assertStringNotContainsString('/buyer/categories', $html);
    }
}
