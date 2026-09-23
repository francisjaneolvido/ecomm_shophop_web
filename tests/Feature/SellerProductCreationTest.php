<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerProductCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        $this->runMigrations([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000002_create_product_images_table.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
        ]);
    }

    public function test_inventory_renders_a_native_add_product_post_form_for_an_approved_seller(): void
    {
        $seller = $this->seller('approved');

        $this->actingAs($seller)
            ->get(route('seller.inventory'))
            ->assertOk()
            ->assertSee('id="productForm"', false)
            ->assertSee('method="POST"', false)
            ->assertSee(route('seller.inventory.products.store'), false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="price"', false)
            ->assertSee('type="submit"', false);
    }

    public function test_approved_seller_can_create_an_active_canonical_product_from_the_add_product_form_contract(): void
    {
        $seller = $this->seller('approved');

        $response = $this->actingAs($seller)->post(
            route('seller.inventory.products.store'),
            [
                'product_id' => '',
                'primary_image_id' => '',
                'name' => 'Inventory flow desk lamp',
                'sku' => 'LAMP-POST-001',
                'category' => 'Home & Living',
                'description' => 'A valid payload shaped exactly like the Add Product form.',
                'price' => '1499.75',
                'discount' => '10',
                'stock' => '12',
                'low_stock_threshold' => '3',
                'has_variants' => '0',
            ]
        );

        $response
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Product added successfully.');

        $this->assertDatabaseCount('products', 1);

        $product = Product::query()->sole();

        $this->assertSame($seller->id, $product->seller_id);
        $this->assertSame('active', $product->status);
        $this->assertSame(12, $product->stock);
        $this->assertSame('Inventory flow desk lamp', $product->name);
        $this->assertSame('Home & Living', $product->category);
        $this->assertSame('1499.75', $product->price);
    }

    public function test_pending_seller_is_rejected_before_product_persistence(): void
    {
        $seller = $this->seller('pending');

        $response = $this->actingAs($seller)->post(
            route('seller.inventory.products.store'),
            [
                'name' => 'Blocked seller product',
                'category' => 'Home & Living',
                'price' => '1499.75',
                'stock' => '12',
            ]
        );

        $response->assertRedirect(route('home'));
        $this->assertDatabaseCount('products', 0);
    }

    private function seller(string $status): User
    {
        $seller = new User();

        $seller->forceFill([
            'email' => $status . '-seller@product-create.test',
            'password' => 'not-used-by-this-test',
            'account_type' => 'seller',
            'status' => $status,
            'email_verified_at' => now(),
        ])->save();

        return $seller;
    }

    private function runMigrations(array $paths): void
    {
        foreach ($paths as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }
}
