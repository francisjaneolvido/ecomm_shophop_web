<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerProductEditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000002_create_product_images_table.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
        ] as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }

    public function test_approved_seller_edits_the_same_owned_product_and_inventory_renders_saved_values(): void
    {
        $seller = $this->seller('seller-a@product-edit.test');
        $product = $this->product($seller);
        $image = $product->images()->create([
            'image_path' => 'products/original-image.jpg',
            'is_primary' => true,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.inventory'))
            ->assertOk()
            ->assertSee('data-open-product-modal', false)
            ->assertSee('name="product_id"', false)
            ->assertSee('Original Product')
            ->assertSee('Original description');

        $this->post(route('seller.inventory.products.store'), $this->editPayload($product, [
            'name' => 'Edited Product',
            'price' => '1250.50',
            'category' => 'Electronics & Gadgets',
            'description' => 'Edited description',
            'primary_image_id' => (string) $image->id,
            // Browser clients can add these fields, but they are not Seller edit inputs.
            'seller_id' => 9999,
            'status' => 'archived',
            'image' => 'products/replaced.jpg',
        ]))
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Product updated successfully.');

        $this->assertDatabaseCount('products', 1);
        $product->refresh();
        $this->assertSame($product->id, Product::query()->sole()->id);
        $this->assertSame($seller->id, $product->seller_id);
        $this->assertSame('Edited Product', $product->name);
        $this->assertSame('Electronics & Gadgets', $product->category);
        $this->assertSame('1250.50', $product->price);
        $this->assertSame('Edited description', $product->description);
        $this->assertSame('active', $product->status);
        $this->assertSame('ORIGINAL-001', $product->sku);
        $this->assertSame(17, $product->stock);
        $this->assertSame(5, $product->low_stock_threshold);
        $this->assertSame(4, $product->discount);
        $this->assertFalse($product->has_variants);
        $this->assertSame('products/legacy-original.jpg', $product->image);
        $this->assertSame($image->id, $product->images()->sole()->id);
        $this->assertTrue((bool) $product->images()->sole()->is_primary);

        $this->get(route('seller.inventory'))
            ->assertOk()
            ->assertSeeText('Edited Product')
            ->assertSee('1,250.50')
            ->assertSee('Edited description');
    }

    public function test_editing_a_variant_product_preserves_its_existing_variant_row(): void
    {
        $seller = $this->seller('seller-a@product-edit.test');
        $product = $this->product($seller);
        $product->update(['has_variants' => true]);
        $variant = $product->variants()->create([
            'name' => 'Original size',
            'sku' => 'ORIGINAL-SIZE-001',
            'price' => '1050.00',
            'stock' => 17,
            'status' => 'active',
        ]);

        $payload = $this->editPayload($product, [
            'name' => 'Edited Variant Product',
            'has_variants' => '1',
            'variants' => [[
                'id' => (string) $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => (string) $variant->stock,
                'status' => $variant->status,
            ]],
        ]);
        unset($payload['stock']); // Variant mode disables the simple-stock input.

        $this->actingAs($seller)
            ->post(route('seller.inventory.products.store'), $payload)
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Product updated successfully.');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);
        $this->assertSame($variant->id, $product->variants()->sole()->id);
        $this->assertSame('ORIGINAL-SIZE-001', $variant->fresh()->sku);
        $this->assertSame(17, $product->fresh()->stock);
        $this->assertTrue($product->fresh()->has_variants);
        $this->assertSame('active', $product->fresh()->status);
    }

    public function test_other_seller_cannot_edit_an_owned_product(): void
    {
        $sellerA = $this->seller('seller-a@product-edit.test');
        $sellerB = $this->seller('seller-b@product-edit.test');
        $product = $this->product($sellerA);

        $this->actingAs($sellerB)
            ->post(route('seller.inventory.products.store'), $this->editPayload($product, [
                'name' => 'Hijacked Product',
                'price' => '9999.00',
            ]))
            ->assertNotFound();

        $this->assertDatabaseCount('products', 1);
        $this->assertSame('Original Product', $product->fresh()->name);
        $this->assertSame('1000.00', $product->fresh()->price);
        $this->assertSame($sellerA->id, $product->fresh()->seller_id);
    }

    public function test_invalid_edit_preserves_the_product_and_exposes_validation_feedback(): void
    {
        $seller = $this->seller('seller-a@product-edit.test');
        $product = $this->product($seller);

        $this->actingAs($seller)
            ->followingRedirects()
            ->from(route('seller.inventory'))
            ->post(route('seller.inventory.products.store'), $this->editPayload($product, [
                'name' => 'Should Not Persist',
                'price' => '-1',
            ]))
            ->assertOk()
            ->assertSeeText('Please review the following:')
            ->assertSeeText('The price field must be at least 0.');

        $this->assertDatabaseCount('products', 1);
        $this->assertSame('Original Product', $product->fresh()->name);
        $this->assertSame('1000.00', $product->fresh()->price);
    }

    private function seller(string $email): User
    {
        $seller = new User();
        $seller->forceFill([
            'email' => $email,
            'password' => 'not-used-by-this-test',
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $seller;
    }

    private function product(User $seller): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => 'Original Product',
            'sku' => 'ORIGINAL-001',
            'category' => 'Home & Living',
            'description' => 'Original description',
            'price' => '1000.00',
            'discount' => 4,
            'stock' => 17,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'status' => 'active',
            'image' => 'products/legacy-original.jpg',
        ]);
    }

    private function editPayload(Product $product, array $changes = []): array
    {
        return array_replace([
            'product_id' => (string) $product->id,
            'primary_image_id' => '',
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $product->price,
            'discount' => (string) $product->discount,
            'stock' => (string) $product->stock,
            'low_stock_threshold' => (string) $product->low_stock_threshold,
            'has_variants' => '0',
        ], $changes);
    }
}
