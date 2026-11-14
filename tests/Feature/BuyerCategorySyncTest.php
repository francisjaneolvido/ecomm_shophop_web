<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Read-only category contracts: no database fixtures, migrations or seeders. */
class BuyerCategorySyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckMaintenanceMode::class);
        // Exercise the real category mappings while isolating product tables.
        Schema::shouldReceive('hasTable')->andReturn(false);
    }

    private function signInBuyer(): void
    {
        $user = (new User)->forceFill([
            'id' => 1, 'account_type' => 'buyer', 'status' => 'approved',
            'email_verified_at' => now(),
        ]);
        $user->setRelation('buyer', (new Buyer)->forceFill(['id' => 1, 'user_id' => 1]));
        Sanctum::actingAs($user);
    }

    private function assertCategoryParity(array $expected): void
    {
        $web = $this->get('/')->assertOk();
        $this->assertSame($expected, $web->viewData('landingCategories')->all());

        $this->signInBuyer();
        $home = $this->getJson('/api/mobile/buyer/home')->assertOk()->json('data.categories');
        $categories = $this->getJson('/api/mobile/buyer/categories')->assertOk()->json('data');
        $this->assertSame($home, $categories);
        foreach (['name', 'slug', 'subcategories'] as $key) {
            $this->assertSame(array_column($expected, $key), array_column($home, $key));
        }
        $this->assertCount(count($expected), array_unique(array_column($home, 'slug')));
        $this->assertSame(array_fill(0, count($expected), 0), array_column($home, 'product_count'));

        $dom = new \DOMDocument;
        @$dom->loadHTML($web->getContent());
        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//*[@data-shop-categories]//a[@data-shop-category]');
        $this->assertCount(count($expected), $links);
        foreach ($links as $i => $link) {
            $this->assertSame(route('buyer.category.show', $expected[$i]['slug']), $link->getAttribute('href'));
            $this->assertSame($expected[$i]['name'], trim($link->textContent));
        }
        $this->assertCount(1, $xpath->query('//*[@data-shop-category-prev]'));
        $this->assertCount(1, $xpath->query('//*[@data-shop-category-next]'));
    }

    public function test_web_home_and_both_mobile_endpoints_share_all_current_categories_in_order(): void
    {
        $expected = config('shophop_categories');
        $this->assertCount(14, $expected);
        $this->assertCategoryParity($expected);
    }

    public function test_category_configuration_changes_are_reflected_without_a_fixed_client_list(): void
    {
        $expected = array_reverse(config('shophop_categories'));
        $expected[0]['name'] = 'Updated department name';
        $expected[] = [
            'name' => 'New backend department', 'slug' => 'new-backend-department',
            'icon' => 'shopping-bag', 'subcategories' => ['New section'],
        ];
        config(['shophop_categories' => $expected]);
        $this->assertCategoryParity($expected);
    }

    public function test_empty_category_configuration_is_consistent_without_fallback_records(): void
    {
        config(['shophop_categories' => []]);
        $this->assertCategoryParity([]);
    }
}
