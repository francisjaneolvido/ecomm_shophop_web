<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogisticsOperationsJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_order_assignment_and_delivery_persist_for_buyer_and_seller(): void
    {
        // A Seller-scoped Order is the fulfillment unit; Logistics transitions must survive separate HTTP requests.
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $operator = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $this->partner($operator);
        $order = Order::create([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId, 'status' => Order::STATUS_TO_SHIP,
            'total_amount' => '123.00', 'shipping_fee' => '15.00', 'cod_fee' => '8.00',
            'voucher_discount' => '10.00', 'payment_method' => 'cod', 'shipping_method' => 'standard',
            'delivery_address' => '1 Test Street, Test, Imus, Cavite',
        ]);
        // Purchase facts are fixed before Logistics begins and must remain byte-for-byte stable.
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Parcel Item', 'category' => 'Home',
            'price' => '110.00', 'stock' => 4, 'description' => 'Test', 'status' => 'active']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'name' => 'Blue',
            'price' => '110.00', 'stock' => 4, 'status' => 'active']);
        $voucher = Voucher::create(['seller_id' => $seller->id, 'name' => 'Test Voucher', 'code' => 'LOG10',
            'type' => 'fixed', 'value' => '10.00', 'min_order_amount' => '0', 'usage_limit' => 10,
            'used_count' => 1, 'status' => 'active']);
        $order->update(['voucher_id' => $voucher->id]);
        $item = $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id,
            'quantity' => 1, 'price' => '110.00']);
        $cart = CartItem::create(['buyer_id' => $buyerId, 'product_id' => $product->id, 'quantity' => 1]);
        $purchase = $order->only(['total_amount', 'shipping_fee', 'cod_fee', 'voucher_discount', 'voucher_id']);

        // The real Seller routes establish readiness before Logistics can claim the parcel.
        $this->actingAs($seller)->patch(route('seller.orders.start-preparation', $order))->assertRedirect();
        $this->patch(route('seller.orders.mark-ready', $order))->assertRedirect();
        $this->actingAs($operator)->get(route('logistics.deliveries.board'))->assertOk()
            ->assertSee('Order #'.$order->id)->assertSee('Live GPS and delivery proof are unavailable.')
            ->assertDontSee('SPXPH');
        $this->post(route('logistics.riders.store'), ['name' => 'Test Rider', 'vehicle_type' => 'Motorcycle'])
            ->assertRedirect();
        $riderId = DB::table('riders')->value('id');
        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $riderId])->assertRedirect();
        $this->assertDatabaseHas('deliveries', ['order_id' => $order->id, 'rider_id' => $riderId, 'status' => 'assigned']);
        $this->get(route('logistics.deliveries.board'))->assertOk()->assertSee('Test Rider');
        $this->actingAs($buyer)->get(route('buyer.orders'))->assertOk()->assertSee('Rider assigned; pickup is pending.');
        $this->actingAs($operator);
        $this->post(route('logistics.deliveries.pickup', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->actingAs($buyer)->get(route('buyer.orders'))->assertOk()->assertSee('Package picked up by Logistics.');
        $this->actingAs($seller)->get(route('seller.orders.show', $order))->assertOk()->assertSee('Picked up');
        $this->actingAs($operator);
        $this->post(route('logistics.deliveries.transit', $order))->assertRedirect();
        $this->actingAs($buyer)->get(route('buyer.orders'))->assertOk()->assertSee('Package in transit with Logistics.');
        $this->actingAs($operator);
        $this->post(route('logistics.deliveries.complete', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $deliveredAt = DB::table('deliveries')->where('order_id', $order->id)->value('delivered_at');
        $this->post(route('logistics.deliveries.complete', $order))->assertSessionHasErrors('delivery');
        $this->assertSame($deliveredAt, DB::table('deliveries')->where('order_id', $order->id)->value('delivered_at'));
        $this->get(route('logistics.dashboard'))->assertOk()->assertSee('Delivered');
        $this->get(route('logistics.reports.index'))->assertOk()->assertSee('Order #'.$order->id);
        // Delivery actions have no inventory, voucher, price, fee, or Cart side effects.
        $this->assertSame($purchase, $order->fresh()->only(array_keys($purchase)));
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(4, $variant->fresh()->stock);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame('110.00', $item->fresh()->price);
        $this->assertTrue(CartItem::whereKey($cart->id)->exists());
        $this->actingAs($buyer)->get(route('buyer.orders'))->assertOk()->assertSee('Delivered');
        $this->actingAs($seller)->get(route('seller.orders.show', $order))->assertOk()->assertSee('Delivered');
    }

    public function test_assignment_rejects_wrong_role_missing_profile_foreign_rider_and_ineligible_order(): void
    {
        // The middleware, partner profile, Rider owner, Order state, and registered area are independent gates.
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $operator = $this->user('logistics');
        $otherOperator = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $partnerId = $this->partner($operator);
        $otherPartnerId = $this->partner($otherOperator);
        $ready = $this->order($buyerId, $sellerId);
        $notReady = $this->order($buyerId, $sellerId, Order::STATUS_TO_SHIP);
        $outside = $this->order($buyerId, $sellerId, Order::STATUS_READY_FOR_PICKUP, '1 Road, Test, Cebu City, Cebu');
        // A forged ready state cannot turn unsupported GCash into an operable COD shipment.
        $unverified = $this->order($buyerId, $sellerId);
        $unverified->update(['payment_method' => 'gcash']);
        $rider = $this->rider($partnerId);
        $foreignRider = $this->rider($otherPartnerId);

        $this->actingAs($buyer)->get(route('logistics.deliveries.board'))->assertForbidden();
        $this->post(route('logistics.deliveries.assign', $ready), ['rider_id' => $rider])->assertForbidden();
        $profileless = $this->user('logistics');
        $this->actingAs($profileless)->get(route('logistics.deliveries.board'))->assertForbidden();
        $this->actingAs($operator)->get(route('logistics.deliveries.board'))->assertOk()
            ->assertSee('Order #'.$ready->id)->assertDontSee('Order #'.$outside->id);
        $this->post(route('logistics.deliveries.assign', $ready), ['rider_id' => $foreignRider])->assertNotFound();
        $this->post(route('logistics.deliveries.assign', $notReady), ['rider_id' => $rider])->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.assign', $outside), ['rider_id' => $rider])->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.assign', $unverified), ['rider_id' => $rider])->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.assign', 999999), ['rider_id' => $rider])->assertNotFound();
        $this->assertSame(0, DB::table('deliveries')->count());

        $this->post(route('logistics.riders.suspend', $rider))->assertRedirect();
        $this->post(route('logistics.deliveries.assign', $ready), ['rider_id' => $rider])->assertNotFound();
        $this->post(route('logistics.riders.activate', $rider))->assertRedirect();
        $this->post(route('logistics.deliveries.assign', $ready), ['rider_id' => $rider])->assertRedirect();
        $this->post(route('logistics.deliveries.assign', $ready), ['rider_id' => $rider])->assertSessionHasErrors('delivery');
        $this->actingAs($otherOperator)->post(route('logistics.deliveries.pickup', $ready))->assertNotFound();
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_transitions_reject_skips_repeats_and_stale_order_state(): void
    {
        // Only the assigned partner can advance one legal stage at a time; repeats do not rewrite event times.
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $operator = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $partnerId = $this->partner($operator);
        $order = $this->order($buyerId, $sellerId);
        $rider = $this->rider($partnerId);
        $this->actingAs($operator);
        $this->post(route('logistics.deliveries.pickup', $order))->assertNotFound();
        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $rider])->assertRedirect();
        $this->post(route('logistics.deliveries.complete', $order))->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.transit', $order))->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.pickup', $order))->assertRedirect();
        $pickupAt = DB::table('deliveries')->where('order_id', $order->id)->value('picked_up_at');
        $this->post(route('logistics.deliveries.pickup', $order))->assertSessionHasErrors('delivery');
        $this->assertSame($pickupAt, DB::table('deliveries')->where('order_id', $order->id)->value('picked_up_at'));
        $this->post(route('logistics.deliveries.transit', $order))->assertRedirect();
        $order->update(['status' => Order::STATUS_CANCELLED]);
        $this->post(route('logistics.deliveries.complete', $order))->assertSessionHasErrors('delivery');
        $this->assertDatabaseHas('deliveries', ['order_id' => $order->id, 'status' => 'in_transit', 'delivered_at' => null]);
    }

    public function test_checkout_group_does_not_merge_seller_orders_or_transfer_partner_ownership(): void
    {
        // A shared checkout group is correlation only; every Seller Order has its own claim and Rider.
        $buyer = $this->user('buyer');
        $sellerA = $this->user('seller');
        $sellerB = $this->user('seller');
        $operatorA = $this->user('logistics');
        $operatorB = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerAId = $this->profile('sellers', $sellerA);
        $sellerBId = $this->profile('sellers', $sellerB);
        $partnerAId = $this->partner($operatorA);
        $partnerBId = $this->partner($operatorB);
        $first = $this->order($buyerId, $sellerAId);
        $second = $this->order($buyerId, $sellerBId);
        $first->update(['checkout_group_id' => 'same-checkout']);
        $second->update(['checkout_group_id' => 'same-checkout']);
        $riderA = $this->rider($partnerAId);
        $riderB = $this->rider($partnerBId);

        $this->actingAs($operatorA)->post(route('logistics.deliveries.assign', $first), ['rider_id' => $riderA])->assertRedirect();
        $this->actingAs($operatorB)->post(route('logistics.deliveries.assign', $second), ['rider_id' => $riderB])->assertRedirect();
        $this->assertDatabaseHas('deliveries', ['order_id' => $first->id, 'logistics_partner_id' => $partnerAId]);
        $this->assertDatabaseHas('deliveries', ['order_id' => $second->id, 'logistics_partner_id' => $partnerBId]);
        $this->post(route('logistics.deliveries.pickup', $first))->assertNotFound();
        $this->actingAs($sellerA)->get(route('seller.orders.show', $second))->assertNotFound();
        $this->actingAs($sellerA)->post(route('logistics.deliveries.pickup', $first))->assertForbidden();
    }

    private function order(int $buyerId, int $sellerId, string $status = Order::STATUS_READY_FOR_PICKUP,
        string $address = '1 Test Street, Test, Imus, Cavite'): Order
    {
        // Each factory Order is a real Seller-scoped COD row with a checkout destination snapshot.
        return Order::create(['buyer_id' => $buyerId, 'seller_id' => $sellerId, 'status' => $status,
            'total_amount' => '100.00', 'payment_method' => 'cod', 'shipping_method' => 'standard',
            'delivery_address' => $address]);
    }

    private function rider(int $partnerId): int
    {
        // Distinct partner Rider IDs expose cross-partner assignment mistakes.
        return DB::table('riders')->insertGetId(['logistics_partner_id' => $partnerId,
            'name' => 'Test Rider '.$partnerId, 'vehicle_type' => 'Motorcycle', 'status' => 'active']);
    }

    private function user(string $role): User
    {
        // Approved roles exercise the production middleware while each profile has a separate primary key.
        $user = new User();
        $user->forceFill([
            'email' => uniqid($role, true).'@example.test', 'password' => 'unused',
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now(),
        ])->save();
        return $user;
    }

    private function profile(string $table, User $user): int
    {
        // Registration fields are fixture prerequisites; the route must still resolve ownership from auth.
        return DB::table($table)->insertGetId([
            'user_id' => $user->id, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Cavite',
            'municipality_code' => '01', 'municipality_name' => 'Imus',
            'barangay_code' => '01', 'barangay_name' => 'Test', 'street_address' => '1 Test Street',
            'valid_id_path' => 'test.jpg',
        ] + ($table === 'sellers' ? ['business_name' => 'Test Shop', 'business_category' => 'Home', 'business_permit_path' => 'test.jpg'] : []));
    }

    private function partner(User $user): int
    {
        // Logistics profile identity comes from users.id; it is never accepted from a form field.
        $partnerId = DB::table('logistics_partners')->insertGetId([
            'user_id' => $user->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
        // Registered province/city coverage is required before an Order can enter this partner's queue.
        DB::table('logistics_coverage_areas')->insert([
            'logistics_partner_id' => $partnerId, 'area_name' => 'Cavite',
            'area_type' => 'province', 'cities' => 'Imus',
        ]);
        return $partnerId;
    }
}
