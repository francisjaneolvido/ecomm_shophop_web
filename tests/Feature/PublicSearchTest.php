<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        $this->runMigrations([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '0001_01_01_000003_create_products_table.php',
        ]);
    }

    public function test_guest_can_search_available_persisted_products_case_insensitively(): void
    {
        $this->createProduct('Wireless Earbuds Pro', 'Electronics and Gadgets');
        $this->createProduct('Ceramic Planter', 'Home and Garden');

        $this->get('/search?search=WIRELESS')
            ->assertOk()
            ->assertSee('Search results for')
            ->assertSee('Wireless Earbuds Pro')
            ->assertSee('5 in stock')
            ->assertDontSee('Ceramic Planter');

        $this->assertGuest();
    }

    public function test_guest_home_and_search_share_the_available_canonical_catalogue(): void
    {
        $this->createProduct('Commuter Backpack', 'Bags and Luggage');
        $this->createProduct('Archived Backpack', 'Bags and Luggage', 5, 'archived');
        $this->createProduct('Sold Out Backpack', 'Bags and Luggage', 0);

        $this->get('/')
            ->assertOk()
            ->assertSee('Commuter Backpack')
            ->assertDontSee('Archived Backpack')
            ->assertDontSee('Sold Out Backpack');

        $this->get('/search?search=commuter')
            ->assertOk()
            ->assertSee('Commuter Backpack');

        $this->get('/search?search=archived')
            ->assertOk()
            ->assertSee('No available products matched')
            ->assertDontSee('Archived Backpack');

        $this->get('/search?search=sold-out')
            ->assertOk()
            ->assertSee('No available products matched')
            ->assertDontSee('Sold Out Backpack');
    }

    public function test_guest_home_is_truthful_when_no_canonical_products_are_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('No products are available yet.')
            ->assertDontSee('Minimalist Canvas Sneakers');
    }

    public function test_guest_search_shows_a_truthful_empty_state_for_a_valid_query_without_matches(): void
    {
        $this->createProduct('Wireless Earbuds Pro', 'Electronics and Gadgets');

        $this->get('/search?search=not-a-catalogue-match')
            ->assertOk()
            ->assertSee('No available products matched')
            ->assertDontSee('Wireless Earbuds Pro');
    }

    public function test_empty_search_shows_intentional_help_instead_of_silently_failing(): void
    {
        $this->get('/search')
            ->assertOk()
            ->assertSee('Enter a product name, category, or description to search the available ShopHop catalogue.');
    }

    public function test_whitespace_only_search_shows_the_same_intentional_help_state(): void
    {
        $this->get('/search?search=%20%20')
            ->assertOk()
            ->assertSee('Enter a product name, category, or description to search the available ShopHop catalogue.');
    }

    public function test_search_results_do_not_link_guests_to_the_private_product_detail_route(): void
    {
        $product = $this->createProduct('Wireless Earbuds Pro', 'Electronics and Gadgets');

        $this->get('/search?search=wireless')
            ->assertOk()
            ->assertSee('Wireless Earbuds Pro')
            ->assertDontSee('/buyer/product/' . $product->id, false);
    }

    public function test_search_escapes_user_provided_query_text(): void
    {
        $this->get('/search?search=' . urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function createProduct(
        string $name,
        string $category,
        int $stock = 5,
        string $status = 'active'
    ): Product
    {
        $seller = new User();
        $seller->forceFill([
            'email' => strtolower(str_replace(' ', '-', $name)) . '@search.test',
            'password' => 'not-used-by-this-test',
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return Product::query()->create([
            'seller_id' => $seller->id,
            'name' => $name,
            'category' => $category,
            'price' => 1299,
            'stock' => $stock,
            'status' => $status,
        ]);
    }

    private function runMigrations(array $paths): void
    {
        foreach ($paths as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }
}
