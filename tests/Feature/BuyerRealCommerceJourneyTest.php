<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerRealCommerceJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep the focused disposable Commerce schema aligned with the Delivery relation now read by Buyer Orders.
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            '2026_08_29_000002_create_sellers_table.php',
            '2026_08_29_000003_create_logistics_partners_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
            'Buyer/2026_09_12_102227_create_orders_table.php',
            'Buyer/2026_09_12_102228_create_order_items_table.php',
            'Buyer/2026_09_23_065258_add_checkout_fields_to_orders_table.php',
            'Buyer/2026_09_23_065410_create_cart_items_table.php',
            // Buyer Orders now reads Delivery milestones when present.
            '2026_09_24_000001_create_logistics_operations_tables.php',
        ] as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }

    public function test_cart_checkout_and_cod_order_use_canonical_rows_and_clear_only_purchased_lines(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-one@example.test');
        $product = $this->product($seller, 'Canonical Product', '100.00', 5, 10);
        // Exercise the public add endpoint before Checkout so selection originates in the persisted Cart.
        $this->actingAs($buyer->user)->postJson(route('buyer.cart.add'), [
            'product_id' => $product->id, 'qty' => 2,
        ])->assertOk();
        $selected = CartItem::where('product_id', $product->id)->sole();
        $remaining = $this->cartLine($buyer, $this->product($seller, 'Remaining Product', '45.00', 3), 1);
        $product->update(['price' => '120.00']);

        // A changed canonical price overrides both stale cart data and a forged display price.
        $this->actingAs($buyer->user)->get(route('buyer.cart.checkout', ['items' => [$selected->id], 'price' => 1]))
            ->assertOk()->assertSee('Canonical Product')->assertSee('₱108.00')
            ->assertDontSee('Remaining Product');

        $this->place([$selected->id => 2], [$seller->id => 'standard'])
            ->assertRedirect(route('buyer.orders'));

        $order = Order::with('items')->sole();
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('294.00', $order->total_amount);
        $this->assertCount(1, $order->items);
        $this->assertSame('108.00', $order->items->first()->price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertFalse(CartItem::whereKey($selected->id)->exists());
        $this->assertTrue(CartItem::whereKey($remaining->id)->exists());
        // Buyer Orders may show the recorded purchase but cannot fabricate courier or proof records.
        $this->get(route('buyer.orders'))->assertOk()->assertSee('Canonical Product')
            ->assertSee('294.00')->assertDontSee('SPXPH0')->assertDontSee('Courier partner');
    }

    public function test_variant_stock_and_price_are_canonical_and_belong_to_the_selected_product(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-two@example.test');
        $product = $this->product($seller, 'Variant Product', '100.00', 3);
        $product->update(['has_variants' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Blue', 'price' => '140.00', 'stock' => 3, 'status' => 'active',
        ]);
        $line = $this->cartLine($buyer, $product, 2, $variant);

        // Seller Inventory stores Product stock as the sum of active variants, so both rows must decrease once.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 2], [$seller->id => 'standard'])->assertRedirect(route('buyer.orders'));
        $this->assertSame(1, $variant->fresh()->stock);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame('140.00', Order::firstOrFail()->items()->firstOrFail()->price);
    }

    public function test_variant_available_quantity_cannot_exceed_product_aggregate(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('aggregate-seller@example.test');
        $product = $this->product($seller, 'Aggregate Product', '100.00', 1);
        $product->update(['has_variants' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Large', 'price' => '100.00',
            'stock' => 3, 'status' => 'active',
        ]);
        $line = $this->cartLine($buyer, $product, 2, $variant);

        // The Product aggregate is a second real inventory limit even when the Variant row is higher.
        $this->assertSame(1, $line->load('product', 'variant')->availableStock());
        $this->actingAs($buyer->user);
        $this->place([$line->id => 2], [$seller->id => 'standard'])->assertSessionHasErrors('items');
        $this->assertSame(0, Order::count());
    }

    public function test_cart_add_rejects_archived_products_foreign_variants_and_zero_stock(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('cart-seller@example.test');
        $product = $this->product($seller, 'Cart Product', '100.00', 5);
        $other = $this->product($seller, 'Other Cart Product', '100.00', 5);
        $foreignVariant = ProductVariant::create([
            'product_id' => $other->id, 'name' => 'Wrong Product', 'price' => '120.00',
            'stock' => 5, 'status' => 'active',
        ]);
        $unsupportedVariant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'No Variant Mode', 'price' => '120.00',
            'stock' => 5, 'status' => 'active',
        ]);

        // A cart line must represent currently purchasable Product and Variant identity.
        $this->actingAs($buyer->user)->postJson(route('buyer.cart.add'), [
            'product_id' => $product->id, 'variant_id' => $foreignVariant->id, 'qty' => 1,
        ])->assertUnprocessable();
        $this->postJson(route('buyer.cart.add'), [
            'product_id' => $product->id, 'variant_id' => $unsupportedVariant->id, 'qty' => 1,
        ])->assertUnprocessable();
        $product->update(['status' => 'archived']);
        $this->postJson(route('buyer.cart.add'), ['product_id' => $product->id])->assertUnprocessable();
        $product->update(['status' => 'active', 'stock' => 0]);
        $this->postJson(route('buyer.cart.add'), ['product_id' => $product->id])->assertUnprocessable();
        $this->assertSame(0, CartItem::count());
    }

    public function test_buyer_adds_canonical_product_to_persisted_cart(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('add-seller@example.test');
        $product = $this->product($seller, 'Add Product', '100.00', 5);

        // Client display fields cannot set Cart price or seller identity; only Product ID and quantity persist.
        $this->actingAs($buyer->user)->postJson(route('buyer.cart.add'), [
            'product_id' => $product->id, 'qty' => 2, 'price' => 1, 'shop_id' => 999,
        ])->assertOk()->assertJsonPath('cart_count', 1);
        $line = CartItem::sole();
        $this->assertSame($buyer->id, $line->buyer_id);
        $this->assertSame($product->id, $line->product_id);
        $this->assertSame(2, $line->quantity);
    }

    public function test_cart_update_rejects_stale_stock_instead_of_inventing_available_quantity(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('update-seller@example.test');
        $product = $this->product($seller, 'Update Product', '100.00', 2);
        $line = $this->cartLine($buyer, $product, 1);
        $product->update(['stock' => 0]);

        // Cart quantity updates must not use a fallback stock figure when canonical stock is exhausted.
        $this->actingAs($buyer->user)->patchJson(route('buyer.cart.update', $line->id), ['qty' => 2])
            ->assertUnprocessable();
        $this->assertSame(1, $line->fresh()->quantity);
    }

    public function test_dashboard_cart_badge_uses_persisted_cart_lines(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('badge-seller@example.test');
        $line = $this->cartLine($buyer, $this->product($seller, 'Badge Product', '100.00', 5), 2);

        // The dashboard badge must follow cart_items even when the obsolete session cart is empty.
        $response = $this->actingAs($buyer->user)->withSession(['shophop_cart' => []])
            ->get(route('buyer.dashboard'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $counts = (new \DOMXPath($document))->query('//*[@data-cart-count]');
        $this->assertGreaterThan(0, $counts->length);
        foreach ($counts as $count) {
            $this->assertSame('1', trim($count->textContent));
        }
        $this->assertTrue(CartItem::whereKey($line->id)->exists());
    }

    public function test_unavailable_variant_or_product_cannot_place_and_leaves_cart_intact(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('stale-seller@example.test');
        $product = $this->product($seller, 'Stale Product', '100.00', 5);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Stale Variant', 'price' => '120.00',
            'stock' => 5, 'status' => 'inactive',
        ]);
        $line = $this->cartLine($buyer, $product, 1, $variant);

        // The checkout trust boundary rechecks status and variant ownership after cart creation.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 1], [$seller->id => 'standard'])->assertSessionHasErrors('items');
        $variant->update(['status' => 'active']);
        $product->update(['status' => 'archived']);
        $this->place([$line->id => 1], [$seller->id => 'standard'])->assertSessionHasErrors('items');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(5, $variant->fresh()->stock);
        $this->assertTrue(CartItem::whereKey($line->id)->exists());
    }

    public function test_stale_or_insufficient_stock_rolls_back_and_preserves_the_cart(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-three@example.test');
        $product = $this->product($seller, 'Low Stock', '100.00', 1);
        $line = $this->cartLine($buyer, $product, 2);

        // Revalidation at placement must reject a stale quantity without partial persistence.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 2], [$seller->id => 'standard'])->assertSessionHasErrors('items');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('order_items')->count());
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertTrue(CartItem::whereKey($line->id)->exists());
    }

    public function test_ineligible_or_expired_voucher_rejects_the_order_without_mutation(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-four@example.test');
        $product = $this->product($seller, 'Selected Product', '100.00', 5);
        $other = $this->product($seller, 'Other Product', '100.00', 5);
        $line = $this->cartLine($buyer, $product, 1);
        $voucher = $this->voucher($seller, 'WRONG20');
        $voucher->products()->attach($other);

        // Product assignment and dates must be checked on the server, regardless of the submitted code.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 1], [$seller->id => 'standard'], 'WRONG20')->assertSessionHasErrors('voucher_code');
        $voucher->products()->sync([$product->id]);
        $voucher->update(['ends_at' => now()->subDay()]);
        $this->place([$line->id => 1], [$seller->id => 'standard'], 'WRONG20')->assertSessionHasErrors('voucher_code');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertTrue(CartItem::whereKey($line->id)->exists());
    }

    public function test_eligible_voucher_discounts_only_assigned_product_and_counts_one_redemption(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-five@example.test');
        $eligible = $this->product($seller, 'Eligible Product', '100.00', 5);
        $other = $this->product($seller, 'Excluded Product', '100.00', 5);
        $first = $this->cartLine($buyer, $eligible, 1);
        $second = $this->cartLine($buyer, $other, 1);
        $voucher = $this->voucher($seller, 'SAVE20');
        $voucher->products()->attach($eligible);

        // A seller voucher discounts its assigned merchandise only, then records its real use.
        $this->actingAs($buyer->user);
        $this->place([$first->id => 1, $second->id => 1], [$seller->id => 'standard'], 'SAVE20')
            ->assertRedirect(route('buyer.orders'));
        $this->assertSame('258.00', Order::firstOrFail()->total_amount);
        $this->assertSame('20.00', Order::firstOrFail()->voucher_discount);
        $this->assertSame(1, $voucher->fresh()->used_count);
    }

    public function test_cart_and_checkout_expose_only_persisted_voucher_assignment_for_preview(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('preview-seller@example.test');
        $product = $this->product($seller, 'Assigned Product', '100.00', 5);
        $line = $this->cartLine($buyer, $product, 1);
        $voucher = $this->voucher($seller, 'PREVIEW20');
        $voucher->products()->attach($product);

        // Preview data carries the assigned Product and Seller profile IDs, while placement remains authoritative.
        $this->actingAs($buyer->user)->get(route('buyer.cart'))->assertOk()
            ->assertSee('PREVIEW20')->assertSee('data-voucher-products', false)
            ->assertSee('data-product-id="'.$product->id.'"', false);
        $this->get(route('buyer.cart.checkout', ['items' => [$line->id]]))->assertOk()
            ->assertSee('PREVIEW20');
    }

    public function test_exhausted_voucher_preserves_cart_stock_and_orders(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('exhausted-seller@example.test');
        $product = $this->product($seller, 'Limited Product', '100.00', 5);
        $line = $this->cartLine($buyer, $product, 1);
        $voucher = $this->voucher($seller, 'USED20');
        $voucher->products()->attach($product);
        $voucher->update(['used_count' => 2]);

        // Usage limits already persisted in the Voucher schema must reject another redemption.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 1], [$seller->id => 'standard'], 'USED20')
            ->assertSessionHasErrors('voucher_code');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertTrue(CartItem::whereKey($line->id)->exists());
    }

    public function test_mixed_sellers_create_linked_seller_orders_without_cross_seller_discount(): void
    {
        $buyer = $this->buyer();
        $firstSeller = $this->seller('seller-six@example.test');
        $secondSeller = $this->seller('seller-seven@example.test');
        $first = $this->cartLine($buyer, $this->product($firstSeller, 'First Seller Product', '100.00', 5), 1);
        $second = $this->cartLine($buyer, $this->product($secondSeller, 'Second Seller Product', '100.00', 5), 1);

        // Current checkout schema represents a mixed cart as seller orders sharing one checkout group.
        $this->actingAs($buyer->user);
        $this->place([$first->id => 1, $second->id => 1], [
            $firstSeller->id => 'standard', $secondSeller->id => 'express',
        ])->assertRedirect(route('buyer.orders'));
        $orders = Order::orderBy('seller_id')->get();
        $this->assertCount(2, $orders);
        $this->assertSame($orders[0]->checkout_group_id, $orders[1]->checkout_group_id);
        $this->assertEqualsCanonicalizing([$firstSeller->id, $secondSeller->id], $orders->pluck('seller_id')->all());
        $this->assertSame(2, DB::table('order_items')->count());
    }

    public function test_second_seller_failure_rolls_back_first_seller_order_and_stock(): void
    {
        $buyer = $this->buyer();
        $firstSeller = $this->seller('rollback-first@example.test');
        $secondSeller = $this->seller('rollback-second@example.test');
        $firstProduct = $this->product($firstSeller, 'First Product', '100.00', 5);
        $secondProduct = $this->product($secondSeller, 'Second Product', '100.00', 5);
        $first = $this->cartLine($buyer, $firstProduct, 1);
        $second = $this->cartLine($buyer, $secondProduct, 1);

        // The second shop's missing shipping choice fails after the first shop is processed and must roll back both.
        $this->actingAs($buyer->user);
        $this->place([$first->id => 1, $second->id => 1], [$firstSeller->id => 'standard'])
            ->assertSessionHasErrors('shipping_method');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('order_items')->count());
        $this->assertSame(5, $firstProduct->fresh()->stock);
        $this->assertSame(5, $secondProduct->fresh()->stock);
        $this->assertSame(2, CartItem::count());
    }

    public function test_buyer_orders_show_only_owned_persisted_orders_and_repeated_post_cannot_duplicate(): void
    {
        $buyer = $this->buyer();
        $otherBuyer = $this->buyer('other-buyer@example.test');
        $seller = $this->seller('seller-eight@example.test');
        $line = $this->cartLine($buyer, $this->product($seller, 'Private Purchase', '100.00', 5), 1);

        // A consumed cart line cannot create another order, and ownership is enforced by the query.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 1], [$seller->id => 'standard'])->assertRedirect(route('buyer.orders'));
        $this->place([$line->id => 1], [$seller->id => 'standard'])->assertSessionHasErrors('items');
        $this->assertSame(1, Order::count());
        $this->actingAs($otherBuyer->user)->get(route('buyer.orders'))
            ->assertOk()->assertDontSee('Private Purchase');
    }

    public function test_later_order_status_does_not_invent_tracking_or_delivery_proof(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('tracking-seller@example.test');
        $line = $this->cartLine($buyer, $this->product($seller, 'Tracked Product', '100.00', 5), 1);

        // Status alone does not create courier assignment, tracking events, or delivery proof records.
        $this->actingAs($buyer->user);
        $this->place([$line->id => 1], [$seller->id => 'standard'])->assertRedirect(route('buyer.orders'));
        $order = Order::firstOrFail();
        $this->get(route('buyer.orders'))->assertOk()
            ->assertDontSee('The seller is preparing your order for courier pickup.')
            ->assertDontSee('Contact Seller');
        // Historical online-payment rows must not expose a dead payment-confirmation action.
        $order->update(['status' => Order::STATUS_TO_PAY, 'payment_method' => 'gcash']);
        $this->get(route('buyer.orders'))->assertOk()->assertDontSee('Complete Payment');
        $order->update(['status' => Order::STATUS_TO_RECEIVE]);
        $this->get(route('buyer.orders'))->assertOk()
            ->assertDontSee('SPXPH0')->assertDontSee('Rider assignment pending')
            ->assertDontSee('Your parcel is on its way to your delivery address.');
        $order->update(['status' => Order::STATUS_COMPLETED]);
        $this->assertFalse($order->fresh()->canReport());
        $this->get(route('buyer.orders'))->assertOk()
            ->assertDontSee('Parcel handed over at the delivery address.')
            ->assertDontSee('Courier partner')
            ->assertDontSee('Report submitted. ShopHop support will review your concern.')
            ->assertDontSee('Rider-uploaded delivery evidence')
            ->assertDontSee('Report an issue if something is wrong')
            ->assertDontSee('Buy Again');
    }

    public function test_gcash_has_no_verified_provider_and_cannot_place_an_order(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('seller-nine@example.test');
        $product = $this->product($seller, 'COD Only', '100.00', 5);
        $line = $this->cartLine($buyer, $product, 1);

        // A client supplied reference or image cannot prove payment without a provider or review contract.
        $this->actingAs($buyer->user)->get(route('buyer.cart.checkout'))
            ->assertOk()->assertSee('GCash (unavailable)')->assertDontSee('0917 123 4567');
        $this->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'],
            'payment_method' => 'gcash',
            'gcash_reference' => 'invented',
        ])->assertSessionHasErrors('payment_method');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
    }

    private function place(array $items, array $shipping, ?string $voucher = null)
    {
        // Submit only cart identities and choices; totals are never supplied by the test client.
        return $this->post(route('buyer.checkout.place'), [
            'items' => collect($items)->map(fn ($quantity) => ['quantity' => $quantity])->all(),
            'shipping_method' => $shipping,
            'payment_method' => 'cod',
            'voucher_code' => $voucher,
        ]);
    }

    private function buyer(string $email = 'buyer@example.test'): Buyer
    {
        // Real role and profile rows exercise the production buyer middleware and delivery snapshot.
        $user = $this->user($email, 'buyer');
        return Buyer::create($this->profile($user->id) + ['valid_id_path' => 'test-id.jpg']);
    }

    private function seller(string $email): Seller
    {
        // Seller profile IDs intentionally differ from users.id to expose foreign key confusion.
        $user = $this->user($email, 'seller');
        return Seller::create($this->profile($user->id) + [
            'business_name' => $email, 'business_category' => 'Home',
            'valid_id_path' => 'test-id.jpg', 'business_permit_path' => 'test-permit.jpg',
        ]);
    }

    private function user(string $email, string $role): User
    {
        // Only approved and verified users may traverse buyer commerce routes.
        $user = new User();
        $user->forceFill([
            'email' => $email, 'password' => 'unused', 'account_type' => $role,
            'status' => 'approved', 'email_verified_at' => now(),
        ])->save();
        return $user;
    }

    private function profile(int $userId): array
    {
        // Required registration address fields are the only delivery address contract currently persisted.
        return [
            'user_id' => $userId, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Province',
            'municipality_code' => '01', 'municipality_name' => 'City',
            'barangay_code' => '01', 'barangay_name' => 'Barangay',
            'street_address' => '1 Test Street',
        ];
    }

    private function product(Seller $seller, string $name, string $price, int $stock, int $discount = 0): Product
    {
        // Products and vouchers currently store the seller user ID, unlike orders.seller_id.
        return Product::create([
            'seller_id' => $seller->user_id, 'name' => $name, 'category' => 'Home',
            'price' => $price, 'discount' => $discount, 'stock' => $stock,
            'description' => 'Test product', 'status' => 'active',
        ]);
    }

    private function cartLine(Buyer $buyer, Product $product, int $quantity, ?ProductVariant $variant = null): CartItem
    {
        // Cart records selection identity and quantity; placement must reload all purchase data.
        return CartItem::create([
            'buyer_id' => $buyer->id, 'product_id' => $product->id,
            'product_variant_id' => $variant?->id, 'quantity' => $quantity,
        ]);
    }

    private function voucher(Seller $seller, string $code): Voucher
    {
        // Voucher eligibility uses seller ownership, assignment, dates, and recorded usage.
        return Voucher::create([
            'seller_id' => $seller->user_id, 'name' => $code, 'code' => $code,
            'type' => 'percent', 'value' => '20.00', 'min_order_amount' => '0',
            'usage_limit' => 2, 'used_count' => 0, 'status' => 'active',
        ]);
    }
}
