<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualCashlessPaymentJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_checkout_creates_one_payment_for_mixed_seller_orders_without_cod_fees(): void
    {
        // Checkout is the sole commerce mutation; the group payment derives its amount from saved Seller Orders.
        $buyer = $this->buyer();
        $first = $this->seller('first@example.test');
        $second = $this->seller('second@example.test');
        $firstProduct = $this->product($first, 'First item', '100.00');
        $secondProduct = $this->product($second, 'Second item', '200.00');
        $lines = [$this->line($buyer, $firstProduct), $this->line($buyer, $secondProduct)];

        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$lines[0]->id => ['quantity' => 1], $lines[1]->id => ['quantity' => 1]],
            'shipping_method' => [$first->id => 'standard', $second->id => 'express'],
            'payment_method' => 'online', 'expected_amount' => '0.01',
        ])->assertRedirect();

        $orders = Order::orderBy('seller_id')->get();
        $this->assertCount(2, $orders);
        $this->assertSame($orders[0]->checkout_group_id, $orders[1]->checkout_group_id);
        $this->assertSame(['online', 'online'], $orders->pluck('payment_method')->all());
        $this->assertSame(['0.00', '0.00'], $orders->pluck('cod_fee')->all());
        $this->assertSame('486.00', number_format((float) $orders->sum('total_amount'), 2, '.', ''));
        $this->assertDatabaseCount('manual_cashless_payments', 1);
        $this->assertDatabaseHas('manual_cashless_payments', [
            'buyer_id' => $buyer->id, 'checkout_group_id' => $orders[0]->checkout_group_id,
            'status' => 'awaiting_proof',
        ]);
        // Every Seller Order must link to the one payment; a copied group UUID alone has no authority.
        $paymentId = DB::table('manual_cashless_payments')->value('id');
        $this->assertSame([$paymentId, $paymentId], $orders->pluck('manual_cashless_payment_id')->all());
        $this->assertSame(0, CartItem::count());
        $this->assertSame(4, $firstProduct->fresh()->stock);
        $this->assertSame(4, $secondProduct->fresh()->stock);
    }

    public function test_buyer_proof_review_rejection_and_resubmission_release_the_whole_group_without_new_commerce_writes(): void
    {
        // The same payment can correct its proof; only an Admin decision releases both Seller Orders.
        Storage::fake('local');
        $buyer = $this->buyer();
        $otherBuyer = $this->buyer('other@example.test');
        $first = $this->seller('seller-a@example.test');
        $second = $this->seller('seller-b@example.test');
        $firstProduct = $this->product($first, 'A item', '100.00');
        $secondProduct = $this->product($second, 'B item', '200.00');
        $firstLine = $this->line($buyer, $firstProduct);
        $secondLine = $this->line($buyer, $secondProduct);
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$firstLine->id => ['quantity' => 1], $secondLine->id => ['quantity' => 1]],
            'shipping_method' => [$first->id => 'standard', $second->id => 'standard'],
            'payment_method' => 'online',
        ])->assertRedirect();
        $payment = DB::table('manual_cashless_payments')->first();
        $orders = Order::orderBy('id')->get();
        // Placed Orders still count historically, while unresolved payment is absent from actionable work.
        $dashboard = $this->actingAs($first->user)->get(route('seller.dashboard'))->assertOk();
        $this->assertSame(0, $dashboard->viewData('newOrders'));
        $this->assertSame(1, $dashboard->viewData('monthlyOrderCount'));
        $this->actingAs($first->user)->patch(route('seller.orders.start-preparation', $orders[0]))
            ->assertSessionHasErrors('status');
        $this->get(route('seller.orders.show', $orders[0]))->assertOk()->assertDontSee('Start Preparation');
        $this->actingAs($otherBuyer->user)->get(route('buyer.payments.show', $payment->id))->assertNotFound();
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'FOREIGN', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertNotFound();

        $this->actingAs($buyer->user)->post(route('buyer.payments.submit', $payment->id), [
            'reference' => '  AB-123  ', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertRedirect();
        $submitted = DB::table('manual_cashless_payments')->find($payment->id);
        $this->assertSame('pending_review', $submitted->status);
        $this->assertSame('AB-123', $submitted->reference);
        $this->assertSame('AB-123', $submitted->normalized_reference);
        Storage::disk('local')->assertExists($submitted->receipt_path);
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'CHANGED', 'receipt' => UploadedFile::fake()->create('pending.png', 2, 'image/png'),
        ])->assertSessionHasErrors('payment');
        $this->assertSame(200, $this->get(route('buyer.orders'))->status(), 'Buyer Orders status');
        $this->get(route('buyer.orders'))->assertSee('Pending Payment Verification');
        $this->actingAs($first->user)->patch(route('seller.orders.start-preparation', $orders[0]))
            ->assertSessionHasErrors('status');

        $admin = $this->user('admin@example.test', 'admin');
        $this->actingAs($admin)->get(route('admin.payments.index'))->assertOk()->assertSee('AB-123');
        $this->get(route('admin.payments.show', $payment->id))->assertOk()
            ->assertSee('AB-123')->assertSee($first->business_name)->assertSee($second->business_name)
            ->assertSee('416.00');
        $this->get(route('payments.receipt', $payment->id))->assertOk();
        // A stale second Seller Order prevents one decision from partially releasing the group.
        $orders[1]->update(['status' => Order::STATUS_PREPARING]);
        $this->post(route('admin.payments.verify', $payment->id))->assertSessionHasErrors('payment');
        $this->assertFalse($orders[0]->fresh()->isPaymentEligible());
        $this->assertFalse($orders[1]->fresh()->isPaymentEligible());
        $orders[1]->update(['status' => Order::STATUS_TO_SHIP]);
        // A missing explicit membership on one Seller Order blocks review of the whole group.
        $orders[1]->update(['manual_cashless_payment_id' => null]);
        $this->post(route('admin.payments.verify', $payment->id))->assertSessionHasErrors('payment');
        $this->assertFalse($orders[0]->fresh()->isPaymentEligible());
        $orders[1]->update(['manual_cashless_payment_id' => $payment->id]);
        $this->post(route('admin.payments.reject', $payment->id), ['reason' => 'Unreadable receipt'])
            ->assertRedirect();
        // A rejected review is current only until the Buyer submits corrected evidence.
        $this->assertDatabaseHas('manual_cashless_payments', [
            'id' => $payment->id, 'status' => 'rejected', 'decision' => 'rejected',
            'reviewer_user_id' => $admin->id, 'rejection_reason' => 'Unreadable receipt',
        ]);
        $this->actingAs($first->user)->patch(route('seller.orders.start-preparation', $orders[0]))
            ->assertSessionHasErrors('status');
        $this->actingAs($buyer->user)->get(route('buyer.payments.show', $payment->id))
            ->assertOk()->assertSee('Unreadable receipt')
            ->assertSee('Complete your payment using the agreed external payment method');
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'AB-123', 'receipt' => UploadedFile::fake()->create('corrected.png', 2, 'image/png'),
        ])->assertRedirect();
        Storage::disk('local')->assertMissing($submitted->receipt_path);
        Storage::disk('local')->assertExists(DB::table('manual_cashless_payments')->find($payment->id)->receipt_path);
        // Resubmission replaces the one current decision instead of showing stale rejection metadata.
        $this->assertDatabaseHas('manual_cashless_payments', [
            'id' => $payment->id, 'status' => 'pending_review', 'reference' => 'AB-123',
            'reviewer_user_id' => null, 'decision' => null, 'reviewed_at' => null,
            'rejection_reason' => null,
        ]);
        $this->actingAs($admin)->post(route('admin.payments.verify', $payment->id))->assertRedirect();
        // The second Admin decision records fresh review ownership and timestamp.
        $this->assertDatabaseHas('manual_cashless_payments', [
            'id' => $payment->id, 'status' => 'verified', 'decision' => 'verified',
            'reviewer_user_id' => $admin->id, 'rejection_reason' => null,
        ]);
        $this->post(route('admin.payments.verify', $payment->id))->assertSessionHasErrors('payment');
        $this->assertDatabaseHas('manual_cashless_payments', ['id' => $payment->id,
            'reviewer_user_id' => $admin->id, 'decision' => 'verified']);
        $this->assertNotNull(DB::table('manual_cashless_payments')->find($payment->id)->reviewed_at);
        $this->actingAs($buyer->user)->get(route('buyer.orders'))->assertOk()
            ->assertSee('Verified by ShopHop Admin');
        foreach ($orders as $order) {
            $this->actingAs($order->seller->user)->patch(route('seller.orders.start-preparation', $order))
                ->assertSessionHasNoErrors();
            $this->assertSame(Order::STATUS_PREPARING, $order->fresh()->status);
        }
        $this->assertSame(2, Order::count());
        $this->assertSame(2, DB::table('order_items')->count());
        $this->assertSame(4, $firstProduct->fresh()->stock);
        $this->assertSame(4, $secondProduct->fresh()->stock);
        $this->assertSame(0, CartItem::count());
        $this->actingAs($buyer->user)->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'CHANGED', 'receipt' => UploadedFile::fake()->create('later.png', 2, 'image/png'),
        ])->assertSessionHasErrors('payment');
    }

    public function test_verified_cashless_order_travels_through_seller_logistics_and_rider_without_cash_settlement(): void
    {
        // Rider delivery evidence is required after Admin verification, but cash custody remains COD-only.
        Storage::fake('local');
        $buyer = $this->buyer();
        $seller = $this->seller('delivery-seller@example.test');
        $product = $this->product($seller, 'Delivered item', '100.00');
        $line = $this->line($buyer, $product);
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'], 'payment_method' => 'online',
        ])->assertRedirect();
        $order = Order::sole();
        $payment = DB::table('manual_cashless_payments')->first();
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'DELIVERY-1', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertRedirect();
        $this->actingAs($this->user('reviewer@example.test', 'admin'))
            ->post(route('admin.payments.verify', $payment->id))->assertRedirect();
        $this->actingAs($seller->user)->patch(route('seller.orders.start-preparation', $order))->assertRedirect();
        $this->patch(route('seller.orders.mark-ready', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $order->fresh()->status);

        $operator = $this->user('operator@example.test', 'logistics');
        $partnerId = $this->partner($operator);
        $rider = Rider::create(['logistics_partner_id' => $partnerId, 'name' => 'Cashless Rider',
            'vehicle_type' => 'Motorcycle', 'status' => 'active', 'email' => 'rider@example.test',
            'password' => bcrypt('LongSecretPassword123!')]);
        $this->actingAs($operator)->get(route('logistics.deliveries.board'))->assertOk()->assertSee('Delivered item');
        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $rider->id])->assertRedirect();
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->post(route('logout'));
        $this->actingAs($rider, 'rider')->get(route('rider.deliveries.show', $delivery))
            ->assertOk()->assertSee('Verified by ShopHop Admin')->assertSee('Amount to Collect: ₱0.00');
        $this->post(route('rider.deliveries.pickup', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.transit', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.complete', $delivery), [])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('delivery.png', 2, 'image/png'),
            'cash_collected' => '1',
        ])->assertSessionHasErrors('cash_collected');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('delivery.png', 2, 'image/png'),
        ])->assertRedirect();
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame('delivered', $delivery->fresh()->status);
        Storage::disk('local')->assertExists($delivery->fresh()->proof_path);
        $this->assertDatabaseCount('cod_settlements', 0);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame('158.00', $order->fresh()->total_amount);
        $this->assertSame('0.00', $order->fresh()->cod_fee);
    }

    public function test_reference_validation_and_admin_stale_state_fail_closed(): void
    {
        // Malformed proof and stale fulfillment cannot create payment eligibility or mutate placed commerce.
        Storage::fake('local');
        $buyer = $this->buyer();
        $seller = $this->seller('guarded-seller@example.test');
        $product = $this->product($seller, 'Guarded item', '100.00');
        $line = $this->line($buyer, $product);
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'], 'payment_method' => 'online',
        ])->assertRedirect();
        $order = Order::sole();
        $payment = DB::table('manual_cashless_payments')->first();
        $operator = $this->user('guarded-operator@example.test', 'logistics');
        $partnerId = $this->partner($operator);
        $rider = Rider::create(['logistics_partner_id' => $partnerId, 'name' => 'Guarded Rider',
            'vehicle_type' => 'Motorcycle', 'status' => 'active', 'email' => 'guarded-rider@example.test',
            'password' => bcrypt('LongSecretPassword123!')]);
        // Even a forged ready state cannot put awaiting-proof Online Payment on the assignment board.
        $order->update(['status' => Order::STATUS_READY_FOR_PICKUP]);
        $this->actingAs($operator)->get(route('logistics.deliveries.board'))
            ->assertOk()->assertDontSee('Guarded item');
        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $rider->id])
            ->assertSessionHasErrors('delivery');
        $order->update(['status' => Order::STATUS_TO_SHIP]);
        $this->actingAs($buyer->user);
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => '   ', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertSessionHasErrors('reference');
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'GOOD', 'receipt' => UploadedFile::fake()->create('bad.txt', 2, 'text/plain'),
        ])->assertSessionHasErrors('receipt');
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'GOOD', 'receipt' => UploadedFile::fake()->create('large.png', 6000, 'image/png'),
        ])->assertSessionHasErrors('receipt');
        $this->assertDatabaseHas('manual_cashless_payments', ['id' => $payment->id, 'status' => 'awaiting_proof']);
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'GOOD', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertRedirect();
        $admin = $this->user('admin-stale@example.test', 'admin');
        $order->update(['status' => Order::STATUS_PREPARING]);
        $this->actingAs($admin)->post(route('admin.payments.verify', $payment->id))
            ->assertSessionHasErrors('payment');
        $this->post(route('admin.payments.reject', $payment->id), ['reason' => 'Stale'])
            ->assertSessionHasErrors('payment');
        $order->update(['status' => Order::STATUS_TO_SHIP]);
        Delivery::create(['order_id' => $order->id, 'logistics_partner_id' => $partnerId,
            'rider_id' => $rider->id, 'status' => 'assigned', 'assigned_at' => now()]);
        $this->post(route('admin.payments.verify', $payment->id))->assertSessionHasErrors('payment');
        $this->assertDatabaseHas('manual_cashless_payments', ['id' => $payment->id, 'status' => 'pending_review']);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_copied_group_id_without_payment_link_cannot_release_a_forged_order(): void
    {
        // Checkout group correlation must not turn an unrelated non-COD row into payable fulfillment.
        Storage::fake('local');
        $buyer = $this->buyer();
        $seller = $this->seller('linked-seller@example.test');
        $line = $this->line($buyer, $this->product($seller, 'Linked item', '100.00'));
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'], 'payment_method' => 'online',
        ])->assertRedirect();
        $real = Order::sole();
        $payment = DB::table('manual_cashless_payments')->first();
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'LINK-1', 'receipt' => UploadedFile::fake()->create('receipt.png', 2, 'image/png'),
        ])->assertRedirect();
        $admin = $this->user('link-admin@example.test', 'admin');
        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $payment->id))->assertRedirect();
        $forged = Order::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'checkout_group_id' => $real->checkout_group_id, 'payment_method' => 'online',
            'status' => Order::STATUS_TO_SHIP, 'total_amount' => '1.00']);
        // A copied group UUID neither joins the explicit payment nor inflates its Buyer/Admin amount.
        $linkedPayment = $real->manualCashlessPayment;
        $this->assertSame([$real->id], $linkedPayment->orders()->pluck('id')->all());
        $this->assertSame('158.00', $linkedPayment->expectedAmount());
        $this->actingAs($buyer->user)->get(route('buyer.payments.show', $payment->id))
            ->assertOk()->assertSee('Order #'.$real->id)->assertDontSee('Order #'.$forged->id)
            ->assertSee('158.00');
        $this->actingAs($admin)->get(route('admin.payments.show', $payment->id))
            ->assertOk()->assertDontSee('#'.$forged->id.' ·', false)->assertSee('158.00');
        $this->assertFalse($forged->isPaymentEligible());
        $this->actingAs($seller->user)->patch(route('seller.orders.start-preparation', $forged))
            ->assertSessionHasErrors('status');
    }

    public function test_reference_reuse_across_payments_is_rejected_and_receipt_access_is_role_scoped(): void
    {
        // Normalized references are unique across payments; the private receipt is not a Seller asset.
        Storage::fake('local');
        $buyerA = $this->buyer('a@example.test');
        $buyerB = $this->buyer('b@example.test');
        $seller = $this->seller('privacy-seller@example.test');
        $product = $this->product($seller, 'Privacy item', '100.00');
        foreach ([$buyerA, $buyerB] as $buyer) {
            $line = $this->line($buyer, $product);
            $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
                'items' => [$line->id => ['quantity' => 1]],
                'shipping_method' => [$seller->id => 'standard'], 'payment_method' => 'online',
            ])->assertRedirect();
        }
        $payments = DB::table('manual_cashless_payments')->orderBy('id')->get();
        $this->actingAs($buyerA->user)->post(route('buyer.payments.submit', $payments[0]->id), [
            'reference' => 'AB 123', 'receipt' => UploadedFile::fake()->create('first.png', 2, 'image/png'),
        ])->assertRedirect();
        $this->actingAs($buyerB->user)->post(route('buyer.payments.submit', $payments[1]->id), [
            'reference' => 'ab123', 'receipt' => UploadedFile::fake()->create('second.png', 2, 'image/png'),
        ])->assertSessionHasErrors('reference');
        $this->assertDatabaseHas('manual_cashless_payments', ['id' => $payments[1]->id, 'status' => 'awaiting_proof']);
        $this->get(route('payments.receipt', $payments[0]->id))->assertForbidden();
        $this->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($seller->user)->get(route('payments.receipt', $payments[0]->id))->assertForbidden();
        $operator = $this->user('receipt-operator@example.test', 'logistics');
        $this->partner($operator);
        $this->actingAs($operator)->get(route('payments.receipt', $payments[0]->id))->assertForbidden();
        $this->actingAs($buyerA->user)->get(route('payments.receipt', $payments[0]->id))->assertOk();
        $this->post(route('logout'));
        $this->get(route('payments.receipt', $payments[0]->id))->assertRedirect(route('login'));
    }

    public function test_cashless_review_never_repeats_variant_stock_or_voucher_mutations(): void
    {
        // Proof decisions leave the Checkout snapshot, Variant aggregate stock, and Voucher use untouched.
        Storage::fake('local');
        $buyer = $this->buyer();
        $seller = $this->seller('variant-seller@example.test');
        $product = $this->product($seller, 'Variant item', '100.00');
        $product->update(['has_variants' => true]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'name' => 'Large',
            'price' => '120.00', 'stock' => 5, 'status' => 'active']);
        $voucher = Voucher::create(['seller_id' => $seller->user_id, 'name' => 'SAVE20',
            'code' => 'SAVE20', 'type' => 'percent', 'value' => '20.00',
            'min_order_amount' => '0', 'usage_limit' => 2, 'used_count' => 0, 'status' => 'active']);
        $voucher->products()->attach($product);
        $line = CartItem::create(['buyer_id' => $buyer->id, 'product_id' => $product->id,
            'product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($buyer->user)->post(route('buyer.checkout.place'), [
            'items' => [$line->id => ['quantity' => 1]],
            'shipping_method' => [$seller->id => 'standard'], 'payment_method' => 'online',
            'voucher_code' => 'SAVE20',
        ])->assertRedirect();
        $order = Order::sole();
        $payment = DB::table('manual_cashless_payments')->first();
        $this->assertSame('154.00', $order->total_amount);
        $this->assertSame('120.00', $order->items()->sole()->price);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(4, $variant->fresh()->stock);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'VAR-1', 'receipt' => UploadedFile::fake()->create('first.png', 2, 'image/png'),
        ])->assertRedirect();
        $admin = $this->user('variant-admin@example.test', 'admin');
        $this->actingAs($admin)->post(route('admin.payments.reject', $payment->id), ['reason' => 'Retake image'])
            ->assertRedirect();
        $this->actingAs($buyer->user)->post(route('buyer.payments.submit', $payment->id), [
            'reference' => 'VAR-1', 'receipt' => UploadedFile::fake()->create('second.png', 2, 'image/png'),
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.payments.verify', $payment->id))->assertRedirect();
        $this->assertSame('154.00', $order->fresh()->total_amount);
        $this->assertSame('120.00', $order->items()->sole()->price);
        $this->assertSame('58.00', $order->fresh()->shipping_fee);
        $this->assertSame('0.00', $order->fresh()->cod_fee);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(4, $variant->fresh()->stock);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame(0, CartItem::count());
        $this->assertSame(1, Order::count());
    }

    private function buyer(string $email = 'buyer@example.test'): Buyer
    {
        // Approved real profiles exercise the HTTP role boundary and the persisted delivery snapshot.
        $user = $this->user($email, 'buyer');
        return Buyer::create($this->profile($user->id) + ['valid_id_path' => 'id.jpg']);
    }

    private function seller(string $email): Seller
    {
        // Seller Order ownership is sellers.id while Product ownership remains users.id.
        $user = $this->user($email, 'seller');
        return Seller::create($this->profile($user->id) + [
            'business_name' => $email, 'business_category' => 'Home',
            'valid_id_path' => 'id.jpg', 'business_permit_path' => 'permit.jpg',
        ]);
    }

    private function user(string $email, string $role): User
    {
        // The route middleware reads this persisted account type and approval status.
        $user = new User();
        $user->forceFill(['email' => $email, 'password' => 'unused', 'account_type' => $role,
            'status' => 'approved', 'email_verified_at' => now()])->save();
        return $user;
    }

    private function profile(int $userId): array
    {
        // Checkout accepts the registered address only when every delivery field is present.
        return ['user_id' => $userId, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Cavite', 'municipality_code' => '01',
            'municipality_name' => 'Imus', 'barangay_code' => '01', 'barangay_name' => 'Test',
            'street_address' => '1 Test Street'];
    }

    private function product(Seller $seller, string $name, string $price): Product
    {
        // Canonical inventory and price are loaded by Checkout, never accepted from the browser.
        return Product::create(['seller_id' => $seller->user_id, 'name' => $name,
            'category' => 'Home', 'price' => $price, 'discount' => 0, 'stock' => 5,
            'description' => 'Test item', 'status' => 'active']);
    }

    private function line(Buyer $buyer, Product $product): CartItem
    {
        // This stored line is consumed once when placement commits.
        return CartItem::create(['buyer_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);
    }

    private function partner(User $operator): int
    {
        // Coverage and an approved partner remain prerequisites for the existing Logistics assignment.
        $id = DB::table('logistics_partners')->insertGetId([
            'user_id' => $operator->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
        DB::table('logistics_coverage_areas')->insert(['logistics_partner_id' => $id,
            'area_name' => 'Cavite', 'area_type' => 'province', 'cities' => 'Imus']);
        return $id;
    }
}
