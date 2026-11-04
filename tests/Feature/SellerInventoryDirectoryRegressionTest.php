<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerInventoryDirectoryRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Run only against the isolated sqlite :memory: testing DB.
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail('SellerInventoryDirectoryRegressionTest requires sqlite :memory:.');
        }
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2026_09_30_000001_add_product_compliance.php',
            'Seller/Manage_inventory/2025_01_15_000002_create_product_images_table.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }

    public function test_products_are_paginated_seller_scoped_and_sorted_in_sql(): void
    {
        $seller = $this->seller('owner@inventory.test');
        $other = $this->seller('another@inventory.test');
        foreach (range(1, 31) as $n) $this->product($seller, sprintf('Item %02d', $n), 10 + $n);
        $this->product($other, 'Foreign Product', 9999);

        $page1 = $this->actingAs($seller)->get(route('seller.inventory'));
        $page1->assertOk()->assertSee('Item 31')->assertDontSee('Foreign Product');
        $this->assertSame(25, substr_count($page1->getContent(), 'class="inventory-row'));
        $this->assertStringContainsString('31</strong> matching products', $page1->getContent());

        $page2 = $this->get(route('seller.inventory', ['page' => 2]));
        $page2->assertOk()->assertSee('Item 01')->assertDontSee('Foreign Product');
        $this->assertSame(6, substr_count($page2->getContent(), 'class="inventory-row'));

        $price = $this->get(route('seller.inventory', ['sort' => 'price-asc', 'per_page' => 50]));
        $price->assertOk();
        $this->assertTrue(strpos($price->getContent(), 'Item 01') < strpos($price->getContent(), 'Item 31'));
    }

    public function test_low_stock_archived_review_status_and_search_filters_use_all_seller_records(): void
    {
        $seller = $this->seller('owner@filters.test');
        $low = $this->product($seller, 'Low Lamp', 3);
        $low->update(['low_stock_threshold' => 5, 'compliance_status' => 'rejected']);
        $empty = $this->product($seller, 'Empty Lamp', 0);
        $this->product($seller, 'Healthy Lamp', 42);
        $archived = $this->product($seller, 'Archived Lamp', 14);
        $archived->update(['status' => 'archived']);
        $other = $this->seller('other@filters.test');
        $this->product($other, 'Other Low Lamp', 1);

        $lowPage = $this->actingAs($seller)->get(route('seller.inventory', ['status' => 'low']));
        $lowPage->assertOk()->assertSee('Low Lamp')->assertDontSee('Healthy Lamp')->assertDontSee('Other Low Lamp');
        $this->assertSame(1, substr_count($lowPage->getContent(), 'class="inventory-row'));

        $rejected = $this->get(route('seller.inventory', ['compliance' => 'rejected']));
        $rejected->assertOk()->assertSee('Low Lamp')->assertDontSee('Empty Lamp');

        $archivedPage = $this->get(route('seller.inventory', ['status' => 'archived', 'q' => 'Archived']));
        $archivedPage->assertOk()->assertSee('Archived Lamp')->assertDontSee('Healthy Lamp');

        $out = $this->get(route('seller.inventory', ['status' => 'out']));
        $out->assertOk()->assertSee('Empty Lamp')->assertDontSee('Low Lamp');
    }

    public function test_variant_stock_endpoint_checks_owner_and_recalculates_parent(): void
    {
        $owner = $this->seller('owner@variants.test');
        $other = $this->seller('other@variants.test');
        $p = $this->product($owner, 'Variant Lamp', 8);
        $p->update(['has_variants' => true]);
        $variant = $p->variants()->create(['name' => 'Blue', 'stock' => 8, 'status' => 'active']);
        $second = $p->variants()->create(['name' => 'Red', 'stock' => 2, 'status' => 'active']);
        $inactive = $p->variants()->create(['name' => 'Old', 'stock' => 6, 'status' => 'archived']);

        $this->actingAs($other)->patch(route('seller.inventory.products.variants.stock', $variant), ['stock' => 19])->assertForbidden();
        $this->actingAs($owner)->patch(route('seller.inventory.products.variants.stock', $variant), ['stock' => -1])->assertSessionHasErrors('stock');
        $this->actingAs($owner)->patch(route('seller.inventory.products.variants.stock', $variant), ['stock' => 13])
            ->assertRedirect(route('seller.inventory'));
        $this->assertSame(13, $variant->fresh()->stock);
        $this->assertSame(15, $p->fresh()->stock);
        $this->assertSame(2, $second->fresh()->stock);
        $this->assertSame(6, $inactive->fresh()->stock);
    }

    public function test_bulk_archive_restore_requires_owned_products_and_preserves_review(): void
    {
        $seller = $this->seller('owner@bulk.test');
        $other = $this->seller('other@bulk.test');
        $a = $this->product($seller, 'Owner First', 6);
        $b = $this->product($seller, 'Owner Second', 5);
        $foreign = $this->product($other, 'Foreign Item', 8);
        $this->actingAs($seller)->patch(route('seller.inventory.products.bulk-status'), [
            'action' => 'archive', 'ids' => [$a->id, $foreign->id],
        ])->assertForbidden();
        $this->assertSame('active', $a->fresh()->status);
        $this->assertSame('active', $foreign->fresh()->status);
        $this->actingAs($seller)->patch(route('seller.inventory.products.bulk-status'), [
            'action' => 'archive', 'ids' => [$a->id, $b->id],
        ])->assertRedirect(route('seller.inventory'));
        $this->assertSame('archived', $a->fresh()->status);
        $this->assertSame('archived', $b->fresh()->status);
        $this->assertSame('approved', $a->fresh()->compliance_status);
        $this->actingAs($seller)->patch(route('seller.inventory.products.bulk-status'), [
            'action' => 'restore', 'ids' => [$a->id, $b->id],
        ])->assertRedirect(route('seller.inventory'));
        $this->assertSame('active', $a->fresh()->status);
        $this->assertSame('active', $b->fresh()->status);
        $this->assertSame('active', $foreign->fresh()->status);
    }

    private function seller(string $email): User
    {
        $user = new User();
        $user->forceFill([
            'email' => $email,
            'password' => 'not-used-by-test',
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();
        return $user;
    }

    private function product(User $seller, string $name, int $stock): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'sku' => 'S-'.$seller->id.'-'.str_replace(' ', '-', $name),
            'category' => 'Home & Living',
            'subcategory' => 'Home Decor',
            'price' => '1000.00',
            'stock' => $stock,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'status' => 'active',
            'compliance_status' => 'approved',
        ]);
    }
}
