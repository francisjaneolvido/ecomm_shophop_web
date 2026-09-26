<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerOrderFulfillmentJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep the focused disposable Commerce schema aligned with Seller's delivery and COD settlement reads.
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
            // Seller details now reads partner-owned Delivery milestones when present.
            '2026_09_24_000001_create_logistics_operations_tables.php',
            // Seller details may load recorded COD cash without treating a legacy Order as paid.
            '2026_09_26_000001_create_cod_settlements_table.php',
        ] as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }

    public function test_seller_lists_and_opens_only_their_persisted_checkout_order(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('one@example.test');
        $otherSeller = $this->seller('two@example.test');
        $own = $this->order($buyer, $seller, 'Own Recorded Product');
        $foreign = $this->order($buyer, $otherSeller, 'Foreign Recorded Product', $own->checkout_group_id);

        // A checkout group correlates orders but grants no access across sellers.id ownership.
        $this->actingAs($seller->user)->get(route('seller.orders.notifications'))
            ->assertOk()->assertSee('Own Recorded Product')->assertSee('89.25')
            ->assertDontSee('Foreign Recorded Product');
        $this->get(route('seller.orders.show', $own))->assertOk()
            ->assertSee('Own Recorded Product')->assertSee('89.25');
        $this->get(route('seller.orders.show', $foreign))->assertNotFound();
    }

    public function test_real_cod_checkout_reaches_only_its_seller_queue(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('checkout-seller@example.test');
        $otherSeller = $this->seller('other-seller@example.test');
        $product = $this->product($seller, 'Real Checkout Product');
        $line = CartItem::create(['buyer_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);

        // Exercise the real placement seam: checkout creates the Seller Order and deducts stock once.
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'],
            'payment_method' => 'cod',
        ])->assertRedirect(route('buyer.orders'));
        $order = Order::sole();
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame(Order::STATUS_TO_SHIP, $order->status);
        $this->assertSame(4, $product->fresh()->stock);

        // A different approved Seller cannot discover the newly placed Order.
        $this->actingAs($seller->user)->get(route('seller.orders.notifications'))
            ->assertOk()->assertSee('Real Checkout Product');
        // Seller mutation continues the Order created by Buyer Checkout, not a second fixture record.
        $this->patch(route('seller.orders.start-preparation', $order))
            ->assertRedirect(route('seller.orders.show', $order));
        $this->assertSame(Order::STATUS_PREPARING, $order->fresh()->status);
        $this->actingAs($otherSeller->user)->get(route('seller.orders.notifications'))
            ->assertOk()->assertDontSee('Real Checkout Product');
        $this->actingAs($buyer->user)->get(route('buyer.orders'))
            ->assertOk()->assertSee('Preparing');
    }

    public function test_seller_transitions_persist_without_changing_purchase_stock_voucher_or_cart(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('transition-seller@example.test');
        $order = $this->order($buyer, $seller, 'Historical Price Product');
        $item = $order->items()->firstOrFail();
        $product = $item->product;
        $product->update(['price' => '300.00', 'has_variants' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Blue', 'price' => '320.00',
            'stock' => 5, 'status' => 'active',
        ]);
        $item->update(['product_variant_id' => $variant->id]);
        $voucher = Voucher::create([
            'seller_id' => $seller->user_id, 'name' => 'Recorded Voucher', 'code' => 'RECORDED',
            'type' => 'fixed', 'value' => '10.00', 'used_count' => 3, 'status' => 'active',
        ]);
        // The purchased total already includes the recorded Voucher; fulfillment must preserve that snapshot.
        $order->update(['voucher_id' => $voucher->id, 'voucher_discount' => '10.00', 'total_amount' => '137.25']);
        $cartLine = CartItem::create(['buyer_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);

        // Recorded OrderItem price and Order financial fields must survive both Seller actions unchanged.
        $this->actingAs($seller->user)->get(route('seller.orders.show', $order))
            ->assertOk()->assertSee('Blue')->assertSee('89.25')->assertDontSee('300.00');
        $this->patch(route('seller.orders.start-preparation', $order))
            ->assertRedirect(route('seller.orders.show', $order));
        $this->assertSame(Order::STATUS_PREPARING, $order->fresh()->status);
        $this->get(route('seller.orders.show', $order))->assertOk()->assertSee('Preparing');
        $this->patch(route('seller.orders.mark-ready', $order))
            ->assertRedirect(route('seller.orders.show', $order));
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $order->fresh()->status);
        $this->get(route('seller.orders.courier'))->assertOk()->assertSee('Historical Price Product');

        // Fulfillment consumes the placed Order; a second inventory or voucher deduction would corrupt checkout.
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(5, $variant->fresh()->stock);
        $this->assertSame(3, $voucher->fresh()->used_count);
        $this->assertSame('89.25', $item->fresh()->price);
        $this->assertSame('137.25', $order->fresh()->total_amount);
        $this->assertSame('58.00', $order->fresh()->shipping_fee);
        $this->assertSame('10.00', $order->fresh()->voucher_discount);
        $this->assertTrue(CartItem::whereKey($cartLine->id)->exists());
    }

    public function test_invalid_repeated_stale_and_logistics_transitions_do_not_change_order(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('state-seller@example.test');
        $order = $this->order($buyer, $seller, 'State Product');
        $this->actingAs($seller->user);

        // Ready cannot skip preparation; a submitted target status cannot override the server transition.
        $this->patch(route('seller.orders.mark-ready', $order))->assertSessionHasErrors('status');
        $this->assertSame(Order::STATUS_TO_SHIP, $order->fresh()->status);
        $this->patch(route('seller.orders.start-preparation', $order), ['status' => Order::STATUS_COMPLETED])
            ->assertRedirect(route('seller.orders.show', $order));
        $this->assertSame(Order::STATUS_PREPARING, $order->fresh()->status);
        $this->patch(route('seller.orders.start-preparation', $order))->assertSessionHasErrors('status');
        $this->patch(route('seller.orders.mark-ready', $order))->assertRedirect(route('seller.orders.show', $order));
        $this->patch(route('seller.orders.mark-ready', $order))->assertSessionHasErrors('status');
        $this->patch(route('seller.orders.start-preparation', $order))->assertSessionHasErrors('status');
        $this->patch('/seller/orders/' . $order->id . '/delivered')->assertNotFound();
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $order->fresh()->status);

        // GCash has no verified payment contract, even if an older Order row exists.
        $gcashOrder = $this->order($buyer, $seller, 'Unverified Payment');
        $gcashOrder->update(['payment_method' => 'gcash']);
        $this->patch(route('seller.orders.start-preparation', $gcashOrder))->assertSessionHasErrors('status');
        $this->assertSame(Order::STATUS_TO_SHIP, $gcashOrder->fresh()->status);
    }

    public function test_foreign_group_orders_and_non_sellers_cannot_read_or_mutate(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('owner@example.test');
        $otherSeller = $this->seller('foreign@example.test');
        $own = $this->order($buyer, $seller, 'Own Group Product');
        $foreign = $this->order($buyer, $otherSeller, 'Foreign Group Product', $own->checkout_group_id);

        // Both Order IDs share a checkout group, but each mutation still resolves through sellers.id.
        $this->actingAs($seller->user)->get(route('seller.orders.prepare'))
            ->assertOk()->assertSee('Own Group Product')->assertDontSee('Foreign Group Product');
        $this->patch(route('seller.orders.start-preparation', $foreign), [
            'seller_id' => $seller->id, 'checkout_group_id' => $own->checkout_group_id,
        ])->assertNotFound();
        $foreign->update(['status' => Order::STATUS_PREPARING]);
        $this->patch(route('seller.orders.mark-ready', $foreign))->assertNotFound();
        $this->get(route('seller.orders.show', $foreign))->assertNotFound();
        $this->assertSame(Order::STATUS_PREPARING, $foreign->fresh()->status);

        // Role middleware and the profile lookup must reject non-Sellers and profile-less Sellers.
        $this->actingAs($buyer->user)->get(route('seller.orders.notifications'))->assertForbidden();
        $this->patch(route('seller.orders.start-preparation', $own))->assertForbidden();
        $profileless = $this->user('profileless@example.test', 'seller');
        $this->actingAs($profileless)->get(route('seller.orders.notifications'))->assertForbidden();
        $this->get(route('seller.orders.confirm'))->assertForbidden();
        $this->get(route('seller.dashboard'))->assertForbidden();
    }

    public function test_buyer_and_dashboard_read_the_same_persisted_seller_status(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('coherence-seller@example.test');
        $order = $this->order($buyer, $seller, 'Coherent Product');

        // Seller dashboard and Buyer Orders observe the one Order row, including its To Ship grouping.
        $this->actingAs($seller->user)->get(route('seller.dashboard'))
            ->assertOk()->assertSee('Recent Orders')->assertSee('New Orders');
        $this->patch(route('seller.orders.start-preparation', $order));
        $this->actingAs($buyer->user)->get(route('buyer.orders'))
            ->assertOk()->assertSee('Preparing')->assertSee('The seller is preparing this order.');
        $this->actingAs($seller->user)->patch(route('seller.orders.mark-ready', $order));
        $this->get(route('seller.dashboard'))->assertOk()->assertSee('Ready Pickup');
        $this->get(route('seller.orders.confirm'))->assertOk()
            ->assertSee('Delivery confirmation is unavailable')->assertDontSee('Mark Delivered');
        $this->actingAs($buyer->user)->get(route('buyer.orders'))
            ->assertOk()->assertSee('Ready for Pickup')
            // Buyer copy must describe readiness without claiming a courier has collected the parcel.
            ->assertSee('Ready for pickup; courier assignment and pickup are pending Logistics.');
    }

    public function test_dashboard_counts_only_owned_seller_statuses(): void
    {
        $buyer = $this->buyer();
        $seller = $this->seller('dashboard-owner@example.test');
        $otherSeller = $this->seller('dashboard-foreign@example.test');
        $new = $this->order($buyer, $seller, 'New Dashboard Product');
        $preparing = $this->order($buyer, $seller, 'Preparing Dashboard Product');
        $ready = $this->order($buyer, $seller, 'Ready Dashboard Product');
        $preparing->update(['status' => Order::STATUS_PREPARING]);
        $ready->update(['status' => Order::STATUS_READY_FOR_PICKUP]);
        $foreign = $this->order($buyer, $otherSeller, 'Foreign Dashboard Product', $new->checkout_group_id);

        // The visible pipeline must count Seller-owned Order states, not checkout siblings or Logistics fixtures.
        $response = $this->actingAs($seller->user)->get(route('seller.dashboard'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        foreach (['New Orders', 'Preparing', 'Ready Pickup'] as $label) {
            // Scope the DOM assertion to the pipeline so unrelated task badges cannot satisfy it.
            $count = $xpath->query("//section[.//h2[normalize-space()='Order Pipeline']]//p[normalize-space()='$label']/preceding-sibling::div[1]/span");
            $this->assertGreaterThan(0, $count->length);
            $this->assertSame('1', trim($count->item(0)->textContent));
        }
        // The monthly count is the three owned Orders, excluding the checkout-group sibling.
        $monthly = $xpath->query("//div[p[normalize-space()='Orders']]/p[2]");
        $this->assertGreaterThan(0, $monthly->length);
        $this->assertSame('3', trim($monthly->item(0)->textContent));
        $this->assertStringContainsString(route('seller.orders.show', $ready), $response->getContent());
        $this->assertStringNotContainsString(route('seller.orders.show', $foreign), $response->getContent());
    }

    private function buyer(): Buyer
    {
        // Real approved role rows exercise the same middleware as the seller portal.
        $user = $this->user('buyer@example.test', 'buyer');
        return Buyer::create($this->profile($user->id) + ['valid_id_path' => 'test-id.jpg']);
    }

    private function seller(string $email): Seller
    {
        // Seller profile IDs intentionally differ from users.id to catch Product/Order key confusion.
        $user = $this->user($email, 'seller');
        return Seller::create($this->profile($user->id) + [
            'business_name' => $email, 'business_category' => 'Home',
            'valid_id_path' => 'test-id.jpg', 'business_permit_path' => 'test-permit.jpg',
        ]);
    }

    private function user(string $email, string $role): User
    {
        // The test user must satisfy both approved role and verified email checks.
        $user = new User();
        $user->forceFill([
            'email' => $email, 'password' => 'unused', 'account_type' => $role,
            'status' => 'approved', 'email_verified_at' => now(),
        ])->save();
        return $user;
    }

    private function profile(int $userId): array
    {
        // Required registration fields represent the existing buyer and seller profile contract.
        return [
            'user_id' => $userId, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Province',
            'municipality_code' => '01', 'municipality_name' => 'City',
            'barangay_code' => '01', 'barangay_name' => 'Barangay',
            'street_address' => '1 Test Street',
        ];
    }

    private function order(Buyer $buyer, Seller $seller, string $productName, ?string $group = null): Order
    {
        // Order ownership is sellers.id; Product ownership remains users.id, and price is the purchase snapshot.
        $product = $this->product($seller, $productName);
        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'checkout_group_id' => $group ?? '00000000-0000-4000-8000-000000000001',
            'status' => Order::STATUS_TO_SHIP, 'total_amount' => '147.25',
            'shipping_method' => 'standard', 'shipping_fee' => '58.00',
            'payment_method' => 'cod', 'cod_fee' => '0.00',
            'delivery_name' => 'Test Person', 'delivery_address' => '1 Test Street',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'quantity' => 1, 'price' => '89.25',
        ]);
        return $order;
    }

    private function product(Seller $seller, string $name): Product
    {
        // Product ownership follows users.id, unlike the Seller-scoped Order foreign key.
        return Product::create([
            'seller_id' => $seller->user_id, 'name' => $name, 'category' => 'Home',
            'price' => '100.00', 'stock' => 5, 'description' => 'Test product', 'status' => 'active',
        ]);
    }
}
