<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerArchiveUnarchiveTest extends TestCase
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

    public function test_approved_seller_archives_and_restores_the_same_product_with_inventory_and_discovery_in_sync(): void
    {
        $seller = $this->seller('seller-a@archive.test');
        $product = $this->product($seller, 'Archive Milestone Product', 'ARCHIVE-001', true);
        $otherProduct = $this->product($seller, 'Unrelated Product', 'OTHER-001');
        $image = $product->images()->create([
            'image_path' => 'products/archive-original.jpg',
            'is_primary' => true,
        ]);
        $variant = $product->variants()->create([
            'name' => 'Blue',
            'sku' => 'ARCHIVE-BLUE',
            'price' => '1500.00',
            'stock' => 12,
            'status' => 'active',
        ]);
        $original = $product->fresh()->getAttributes();
        $variantOriginal = $variant->fresh()->getAttributes();
        $imageOriginal = $image->fresh()->getAttributes();
        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());

        $inventory = $this->actingAs($seller)->get(route('seller.inventory'));
        $inventory->assertOk()
            ->assertSee('data-product-id="' . $product->id . '"', false)
            ->assertSee('data-product-id="' . $otherProduct->id . '"', false)
            ->assertSee('data-is-archived="0"', false)
            ->assertSee('title="Archive product"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="PATCH"', false);
        $this->assertArchiveControl($inventory->getContent(), $product->id, '0', 'Archive product', false);
        $this->assertArchiveControl($inventory->getContent(), $otherProduct->id, '0', 'Archive product', false);

        $this->post(route('seller.inventory.products.archive', $product), ['_method' => 'PATCH'])
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Product archived.');

        $this->assertDatabaseCount('products', 2);
        $archived = $product->fresh();
        $this->assertSame($product->id, $archived->id);
        $this->assertSame($seller->id, $archived->seller_id);
        $this->assertSame('archived', $archived->status);
        $this->assertSame(12, $archived->stock);
        $this->assertSame($original, array_replace($archived->getAttributes(), ['status' => $original['status'], 'updated_at' => $original['updated_at']]));
        $this->assertSame('active', $otherProduct->fresh()->status);
        $this->assertSame($variantOriginal, $variant->fresh()->getAttributes());
        $this->assertSame($imageOriginal, $image->fresh()->getAttributes());
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
        $archivedInventory = $this->actingAs($seller)->get(route('seller.inventory'));
        $archivedInventory
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('data-product-id="' . $product->id . '"', false)
            ->assertSee('data-is-archived="1"', false)
            ->assertSee('title="Restore product"', false)
            ->assertSee('Archived');
        $this->assertArchiveControl($archivedInventory->getContent(), $product->id, '1', 'Restore product', true);

        $this->post(route('seller.inventory.products.archive', $product), ['_method' => 'PATCH'])
            ->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Product restored.');

        $restored = $product->fresh();
        $this->assertDatabaseCount('products', 2);
        $this->assertSame($product->id, $restored->id);
        $this->assertSame($seller->id, $restored->seller_id);
        $this->assertSame('active', $restored->status);
        $this->assertSame(12, $restored->stock);
        $this->assertSame($original, array_replace($restored->getAttributes(), ['updated_at' => $original['updated_at']]));
        $this->assertSame($variantOriginal, $variant->fresh()->getAttributes());
        $this->assertSame($imageOriginal, $image->fresh()->getAttributes());
        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
        $restoredInventory = $this->actingAs($seller)->get(route('seller.inventory'));
        $restoredInventory
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('data-product-id="' . $product->id . '"', false)
            ->assertSee('data-is-archived="0"', false)
            ->assertSee('title="Archive product"', false);
        $this->assertArchiveControl($restoredInventory->getContent(), $product->id, '0', 'Archive product', false);
    }

    public function test_another_seller_cannot_archive_or_restore_the_product(): void
    {
        $sellerA = $this->seller('seller-a@archive.test');
        $sellerB = $this->seller('seller-b@archive.test');
        $product = $this->product($sellerA);

        $this->actingAs($sellerB)->patch(route('seller.inventory.products.archive', $product))
            ->assertForbidden();
        $this->assertSame('active', $product->fresh()->status);

        $this->actingAs($sellerA)->patch(route('seller.inventory.products.archive', $product))
            ->assertRedirect(route('seller.inventory'));
        $this->assertSame('archived', $product->fresh()->status);

        $this->actingAs($sellerB)->patch(route('seller.inventory.products.archive', $product))
            ->assertForbidden();
        $this->assertSame('archived', $product->fresh()->status);
        $this->assertSame($sellerA->id, $product->fresh()->seller_id);
    }

    public function test_restored_zero_stock_product_stays_active_but_is_not_publicly_discoverable(): void
    {
        $seller = $this->seller('seller-a@archive.test');
        $product = $this->product($seller);
        $product->update(['stock' => 0]);

        $this->actingAs($seller)->patch(route('seller.inventory.products.archive', $product))
            ->assertSessionHas('status', 'Product archived.');
        $this->assertSame('archived', $product->fresh()->status);
        $this->actingAs($seller)->get(route('seller.inventory'))
            ->assertOk()
            ->assertSee('title="Restore product"', false)
            ->assertSee('Buyers can see it when stock is available.');
        $this->actingAs($seller)->patch(route('seller.inventory.products.archive', $product))
            ->assertSessionHas('status', 'Product restored.');

        $this->assertSame('active', $product->fresh()->status);
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
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

    private function assertArchiveControl(string $html, int $productId, string $isArchived, string $title, bool $archived): void
    {
        $document = new \DOMDocument();
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $buttons = $xpath->query('//button[@data-open-archive-modal][@data-product-id="' . $productId . '"]');
        $this->assertCount(1, $buttons);
        $button = $buttons->item(0);
        $this->assertSame($isArchived, $button->getAttribute('data-is-archived'));
        $this->assertSame($title, $button->getAttribute('title'));
        $row = $xpath->query('ancestor::tr', $button)->item(0);
        $this->assertNotNull($row);
        $this->assertSame($archived, str_contains($row->textContent, 'Archived'));

        $forms = $xpath->query('//form[@id="archiveForm"]');
        $this->assertCount(1, $forms);
        $form = $forms->item(0);
        $this->assertSame('POST', $form->getAttribute('method'));
        $this->assertNotEmpty($xpath->query('.//input[@name="_token"]', $form)->item(0)?->getAttribute('value'));
        $this->assertSame('PATCH', $xpath->query('.//input[@name="_method"]', $form)->item(0)?->getAttribute('value'));
    }

    private function product(User $seller, string $name = 'Archive Milestone Product', string $sku = 'ARCHIVE-001', bool $hasVariants = false): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'sku' => $sku,
            'category' => 'Home & Living',
            'description' => 'Archive milestone description',
            'price' => '1500.00',
            'discount' => 4,
            'stock' => 12,
            'low_stock_threshold' => 5,
            'has_variants' => $hasVariants,
            'status' => 'active',
            'image' => 'products/archive-cover.jpg',
        ]);
    }
}
