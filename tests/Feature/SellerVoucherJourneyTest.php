<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SellerVoucherJourneyTest extends TestCase
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

    public function test_seller_assigns_replaces_and_removes_voucher_products_through_the_inventory_form(): void
    {
        $seller = $this->seller('seller-a@voucher.test');
        $productA = $this->product($seller, 'Voucher Product A', 'VOUCHER-A');
        $productB = $this->product($seller, 'Voucher Product B', 'VOUCHER-B');
        $unrelatedVoucher = $this->voucher($seller, 'Other Voucher', 'OTHER20');
        $unrelatedVoucher->products()->attach($productB);
        $before = $productA->fresh()->only(['seller_id', 'name', 'sku', 'price', 'stock', 'status', 'image']);

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.store'), $this->form([
            'product_ids' => [$productA->id, $productA->id],
        ]))->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Voucher created successfully.');

        $voucher = Voucher::where('code', 'SAVE20')->sole();
        $this->assertSame([$productA->id], $voucher->products()->pluck('products.id')->all());
        $this->assertSame(1, DB::table('voucher_product')->where('voucher_id', $voucher->id)->count());
        $this->assertSame($before, $productA->fresh()->only(array_keys($before)));
        $this->assertInventorySelection($voucher, [$productA->id], '1 selected product(s)');

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.store'), $this->form([
            'voucher_id' => $voucher->id,
            'product_ids' => [$productB->id],
        ]))->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Voucher updated successfully.');

        $this->assertSame([$productB->id], $voucher->products()->pluck('products.id')->all());
        $this->assertSame([$productB->id], $unrelatedVoucher->products()->pluck('products.id')->all());
        $this->assertSame($before, $productA->fresh()->only(array_keys($before)));
        $this->assertInventorySelection($voucher, [$productB->id], '1 selected product(s)');

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.store'), $this->form([
            'voucher_id' => $voucher->id,
        ]))->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Voucher updated successfully.');

        $this->assertSame([], $voucher->products()->pluck('products.id')->all());
        $this->assertInventorySelection($voucher, [], 'No products selected');
        $this->assertSame($before, $productA->fresh()->only(array_keys($before)));
    }

    public function test_other_seller_cannot_attach_their_voucher_to_another_sellers_product_or_edit_their_voucher(): void
    {
        $sellerA = $this->seller('seller-a@voucher.test');
        $sellerB = $this->seller('seller-b@voucher.test');
        $productA = $this->product($sellerA, 'Seller A Product', 'OWNER-A');
        $voucherA = $this->voucher($sellerA, 'Seller A Voucher', 'OWNER20');
        $voucherA->products()->attach($productA);
        $before = $productA->fresh()->only(['seller_id', 'name', 'sku', 'price', 'stock', 'status']);

        $foreignProductResponse = $this->actingAs($sellerB)->post(route('seller.inventory.vouchers.store'), $this->form([
            'name' => 'Seller B Voucher',
            'code' => 'SELLERB20',
            'product_ids' => [$productA->id],
        ]));
        $this->assertSame(403, $foreignProductResponse->status());

        $this->assertDatabaseMissing('vouchers', ['code' => 'SELLERB20']);
        $this->assertSame([$voucherA->id], $productA->vouchers()->pluck('vouchers.id')->all());

        $this->actingAs($sellerB)->post(route('seller.inventory.vouchers.store'), $this->form([
            'voucher_id' => $voucherA->id,
            'product_ids' => [],
        ]))->assertNotFound();

        $this->assertSame($before, $productA->fresh()->only(array_keys($before)));
        $this->assertSame([$voucherA->id], $productA->vouchers()->pluck('vouchers.id')->all());
    }

    public function test_archived_product_is_rejected_without_changing_an_existing_assignment(): void
    {
        $seller = $this->seller('seller-a@voucher.test');
        $active = $this->product($seller, 'Active Product', 'ACTIVE-A');
        $archived = $this->product($seller, 'Archived Product', 'ARCHIVED-A');
        $archived->update(['status' => 'archived']);
        $voucher = $this->voucher($seller, 'Existing Voucher', 'EXIST20');
        $voucher->products()->attach($active);

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.store'), $this->form([
            'voucher_id' => $voucher->id,
            'product_ids' => [$archived->id],
        ]))->assertSessionHasErrors('product_ids.0');

        $this->assertSame([$active->id], $voucher->products()->pluck('products.id')->all());
        $this->assertSame('archived', $archived->fresh()->status);
    }

    public function test_buyer_product_page_shows_an_active_assigned_voucher_but_not_a_removed_one(): void
    {
        (require database_path('migrations/2026_08_29_000002_create_sellers_table.php'))->up();
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->unsignedInteger('rating');
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->unsignedInteger('quantity');
        });

        $seller = $this->seller('seller-a@voucher.test');
        $buyer = $this->seller('buyer@voucher.test', 'buyer');
        $product = $this->product($seller, 'Buyer Voucher Product', 'BUYER-VOUCHER');
        $voucher = $this->voucher($seller, 'Buyer Voucher', 'BUYER20');
        $voucher->products()->attach($product);

        $this->actingAs($buyer)->get(route('buyer.product.show', $product))
            ->assertOk()
            ->assertSee('BUYER20')
            ->assertSee('20.00% off');

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.toggle', $voucher), [
            '_method' => 'PATCH',
        ])->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Voucher disabled.');
        $this->actingAs($buyer)->get(route('buyer.product.show', $product))
            ->assertOk()
            ->assertDontSee('BUYER20');

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.toggle', $voucher), [
            '_method' => 'PATCH',
        ])->assertRedirect(route('seller.inventory'))
            ->assertSessionHas('status', 'Voucher enabled.');

        $this->actingAs($seller)->post(route('seller.inventory.vouchers.store'), $this->form([
            'voucher_id' => $voucher->id,
            'name' => $voucher->name,
            'code' => $voucher->code,
        ]))->assertRedirect(route('seller.inventory'));

        $this->actingAs($buyer)->get(route('buyer.product.show', $product))
            ->assertOk()
            ->assertDontSee('BUYER20');
    }

    private function form(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Voucher Journey Discount',
            'code' => 'SAVE20',
            'type' => 'percent',
            'value' => '20.00',
            'min_order_amount' => '0',
        ], $overrides);
    }

    private function seller(string $email, string $role = 'seller'): User
    {
        $seller = new User();
        $seller->forceFill([
            'email' => $email,
            'password' => 'not-used-by-this-test',
            'account_type' => $role,
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $seller;
    }

    private function product(User $seller, string $name, string $sku): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'sku' => $sku,
            'category' => 'Home & Living',
            'description' => 'Voucher journey product',
            'price' => '1500.00',
            'discount' => 4,
            'stock' => 12,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'status' => 'active',
            'image' => 'products/voucher-cover.jpg',
        ]);
    }

    private function voucher(User $seller, string $name, string $code): Voucher
    {
        return Voucher::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'code' => $code,
            'type' => 'percent',
            'value' => '20.00',
            'min_order_amount' => '0',
            'status' => 'active',
        ]);
    }

    private function assertInventorySelection(Voucher $voucher, array $productIds, string $summary): void
    {
        $response = $this->actingAs(User::findOrFail($voucher->seller_id))
            ->get(route('seller.inventory'));
        $response->assertOk()->assertSee($summary)->assertSee($voucher->code);

        $document = new \DOMDocument();
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $data = null;
        foreach ($xpath->query('//button[@data-open-voucher-modal][@data-voucher]') as $button) {
            $candidate = json_decode($button->getAttribute('data-voucher'), true, 512, JSON_THROW_ON_ERROR);
            if ($candidate['id'] === $voucher->id) {
                $data = $candidate;
                break;
            }
        }
        $this->assertNotNull($data);
        $this->assertSame($productIds, $data['product_ids']);
    }
}
