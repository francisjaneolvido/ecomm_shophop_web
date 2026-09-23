<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerQuickStockTest extends TestCase
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

    public function test_approved_seller_quick_stock_updates_only_the_owned_product_and_inventory_shows_it(): void
    {
        $seller = $this->seller('seller-a@quick-stock.test');
        $product = $this->product($seller);
        $otherProduct = $this->product($seller, 'Other Product');
        $otherProduct->update(['stock' => 8]);
        $image = $product->images()->create([
            'image_path' => 'products/original.jpg',
            'is_primary' => true,
        ]);
        $original = $product->fresh()->getAttributes();

        $inventory = $this->actingAs($seller)->get(route('seller.inventory'));
        $inventory->assertOk()
            ->assertSee('class="quick-stock-form inline-flex items-center gap-1"', false)
            ->assertSee('action="' . route('seller.inventory.products.stock', $product) . '"', false)
            ->assertSee('action="' . route('seller.inventory.products.stock', $otherProduct) . '"', false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="stock"', false)
            ->assertSee('value="12"', false);

        $document = new \DOMDocument();
        $document->loadHTML($inventory->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $forms = (new \DOMXPath($document))->query('//form[contains(concat(" ", normalize-space(@class), " "), " quick-stock-form ")]');
        $this->assertCount(2, $forms);
        $stocksByAction = [];
        foreach ($forms as $form) {
            $this->assertSame('POST', $form->getAttribute('method'));
            $fields = [];
            foreach ($form->getElementsByTagName('input') as $input) {
                $fields[$input->getAttribute('name')] = $input->getAttribute('value');
            }
            $this->assertNotEmpty($fields['_token'] ?? null);
            $this->assertSame('PATCH', $fields['_method'] ?? null);
            $stocksByAction[$form->getAttribute('action')] = $fields['stock'] ?? null;
        }
        $this->assertSame([
            route('seller.inventory.products.stock', $product) => '12',
            route('seller.inventory.products.stock', $otherProduct) => '8',
        ], $stocksByAction);

        $this->post(route('seller.inventory.products.stock', $product), [
            '_method' => 'PATCH',
            'stock' => '27',
        ])->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Stock updated.');

        $this->assertDatabaseCount('products', 2);
        $saved = $product->fresh();
        $this->assertSame($product->id, $saved->id);
        $this->assertSame($seller->id, $saved->seller_id);
        $this->assertSame(27, $saved->stock);
        $this->assertSame(8, $otherProduct->fresh()->stock);
        $this->assertSame($image->id, $saved->images()->sole()->id);
        $this->assertDatabaseCount('product_variants', 0);
        unset($original['stock'], $original['updated_at']);
        $savedAttributes = $saved->getAttributes();
        unset($savedAttributes['stock'], $savedAttributes['updated_at']);
        ksort($original);
        ksort($savedAttributes);
        $this->assertSame($original, $savedAttributes);

        $this->get(route('seller.inventory'))
            ->assertOk()
            ->assertSee('action="' . route('seller.inventory.products.stock', $product) . '"', false)
            ->assertSee('value="27"', false)
            ->assertSeeText('Stock updated.')
            ->assertSeeText('Original Product');
    }

    public function test_another_seller_cannot_quick_update_the_product(): void
    {
        $sellerA = $this->seller('seller-a@quick-stock.test');
        $sellerB = $this->seller('seller-b@quick-stock.test');
        $product = $this->product($sellerA);

        $this->actingAs($sellerB)
            ->patch(route('seller.inventory.products.stock', $product), ['stock' => '27'])
            ->assertForbidden();

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertSame($sellerA->id, $product->fresh()->seller_id);
    }

    public function test_invalid_stock_is_rejected_without_changing_the_product_and_feedback_is_visible(): void
    {
        $seller = $this->seller('seller-a@quick-stock.test');
        $product = $this->product($seller);

        foreach ([
            '-1' => 'The stock field must be at least 0.',
            '2.5' => 'The stock field must be an integer.',
        ] as $invalidStock => $error) {
            $this->actingAs($seller)
                ->followingRedirects()
                ->from(route('seller.inventory'))
                ->post(route('seller.inventory.products.stock', $product), [
                    '_method' => 'PATCH',
                    'stock' => $invalidStock,
                ])
                ->assertOk()
                ->assertSeeText('Please review the following:')
                ->assertSeeText($error);

            $this->assertSame(12, $product->fresh()->stock);
        }
    }

    public function test_zero_stock_stays_active_and_controls_public_discoverability(): void
    {
        $seller = $this->seller('seller-a@quick-stock.test');
        $product = $this->product($seller);
        $this->actingAs($seller);

        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());

        $this->patch(route('seller.inventory.products.stock', $product), ['stock' => '0'])
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Stock updated.');

        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame('active', $product->fresh()->status);
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
        $this->get(route('seller.inventory'))
            ->assertOk()
            ->assertSee('action="' . route('seller.inventory.products.stock', $product) . '"', false)
            ->assertSee('value="0"', false)
            ->assertSeeText('Out of Stock')
            ->assertSeeText('Restock required');

        $this->patch(route('seller.inventory.products.stock', $product), ['stock' => '5'])
            ->assertRedirect(route('seller.inventory'));

        $this->assertSame('active', $product->fresh()->status);
        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
    }

    public function test_variant_product_has_no_simple_stock_control_and_endpoint_refuses_the_update(): void
    {
        $seller = $this->seller('seller-a@quick-stock.test');
        $product = $this->product($seller);
        $product->update(['has_variants' => true]);
        $variant = $product->variants()->create([
            'name' => 'Original size',
            'sku' => 'ORIGINAL-SIZE-001',
            'price' => '1000.00',
            'stock' => 12,
            'status' => 'active',
        ]);

        $this->actingAs($seller)
            ->get(route('seller.inventory'))
            ->assertOk()
            ->assertDontSee('action="' . route('seller.inventory.products.stock', $product) . '"', false)
            ->assertSeeText('Variant total');

        $this->patch(route('seller.inventory.products.stock', $product), ['stock' => '27'])
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('error', 'Stock for this product is managed through its variants.');

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertSame(12, $variant->fresh()->stock);
        $this->get(route('seller.inventory'))
            ->assertOk()
            ->assertSeeText('Stock for this product is managed through its variants.');
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

    private function product(User $seller, string $name = 'Original Product'): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'sku' => $name === 'Original Product' ? 'ORIGINAL-001' : 'OTHER-001',
            'category' => 'Home & Living',
            'description' => 'Original description',
            'price' => '1000.00',
            'discount' => 4,
            'stock' => 12,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'status' => 'active',
            'image' => 'products/legacy-original.jpg',
        ]);
    }
}
