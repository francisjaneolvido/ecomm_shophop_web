<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogisticsOperationsJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_order_moves_through_pickup_sorting_delivery_and_buyer_confirmation(): void
    {
        Storage::fake('local');
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $operator = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $partnerId = $this->partner($operator);
        $areaId = DB::table('logistics_coverage_areas')->where('logistics_partner_id', $partnerId)->value('id');

        $order = Order::create([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId, 'status' => Order::STATUS_TO_SHIP,
            'total_amount' => '123.00', 'shipping_fee' => '15.00', 'cod_fee' => '8.00',
            'voucher_discount' => '10.00', 'payment_method' => 'cod', 'shipping_method' => 'standard',
            'delivery_address' => '1 Test Street, Test, Imus, Cavite',
            'delivery_name' => 'Test Buyer', 'delivery_phone' => '09123456789',
        ]);
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

        $this->actingAs($seller)->patch(route('seller.orders.start-preparation', $order))->assertRedirect();
        $this->patch(route('seller.orders.mark-ready', $order))->assertRedirect();

        $this->actingAs($operator)->post(route('logistics.riders.store'), [
            'name' => 'Test Rider', 'vehicle_type' => 'Motorcycle', 'coverage_area_id' => $areaId,
            'email' => 'rider@example.test', 'password' => 'LongSecretPassword123!',
            'password_confirmation' => 'LongSecretPassword123!',
        ])->assertRedirect();
        $rider = Rider::where('email', 'rider@example.test')->firstOrFail();

        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $rider->id])->assertRedirect();
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(Delivery::PICKUP_ASSIGNED, $delivery->status);
        $this->assertNotNull($delivery->tracking_code);
        $this->actingAs($buyer)->get(route('buyer.orders'))->assertOk()->assertSee('Pickup Rider assigned');

        $this->post(route('logout'));
        $this->post(route('rider.login.store'), ['email' => 'rider@example.test',
            'password' => 'LongSecretPassword123!'])->assertRedirect();
        $this->post(route('rider.deliveries.accept-pickup', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.pickup', $delivery))->assertRedirect();
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->assertSame(Delivery::PICKED_UP, $delivery->fresh()->status);

        $this->post(route('rider.logout'));
        $this->actingAs($operator, 'web');
        $this->post(route('logistics.deliveries.receive', $delivery), [
            'tracking_code' => $delivery->fresh()->tracking_code,
        ])->assertRedirect();
        $this->post(route('logistics.deliveries.sort', $delivery), ['destination_area_id' => $areaId])->assertRedirect();
        $this->post(route('logistics.deliveries.assign-delivery', $delivery), ['rider_id' => $rider->id])->assertRedirect();
        $this->assertSame(Delivery::DELIVERY_ASSIGNED, $delivery->fresh()->status);

        $this->post(route('logout'));
        $this->actingAs($rider, 'rider');
        $this->post(route('rider.deliveries.out-for-delivery', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1',
            'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertRedirect();
        $this->assertSame(Delivery::DELIVERED, $delivery->fresh()->status);
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status, 'Rider delivery waits for Buyer confirmation.');

        $this->post(route('rider.logout'));
        $this->actingAs($buyer, 'web')->post(route('buyer.orders.confirm-receipt', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);

        $this->actingAs($operator, 'web')->get(route('logistics.dashboard'))->assertOk()->assertSee('Delivered');
        $this->get(route('logistics.reports.index'))->assertOk()->assertSee('Order #'.$order->id);
        $this->assertSame($purchase, $order->fresh()->only(array_keys($purchase)));
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(4, $variant->fresh()->stock);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame('110.00', $item->fresh()->price);
        $this->assertTrue(CartItem::whereKey($cart->id)->exists());
    }

    public function test_assignment_rejects_wrong_role_missing_profile_foreign_rider_and_ineligible_order(): void
    {
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
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_transitions_reject_skips_repeats_bad_scan_and_stale_order_state(): void
    {
        Storage::fake('local');
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $operator = $this->user('logistics');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $partnerId = $this->partner($operator);
        $areaId = DB::table('logistics_coverage_areas')->where('logistics_partner_id', $partnerId)->value('id');
        $order = $this->order($buyerId, $sellerId);
        $riderId = $this->rider($partnerId);
        $rider = Rider::findOrFail($riderId);

        $this->actingAs($operator)->post(route('logistics.deliveries.assign', $order), ['rider_id' => $riderId])->assertRedirect();
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->post(route('logout'));
        $this->actingAs($rider, 'rider');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('early.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('delivery');
        $this->post(route('rider.deliveries.out-for-delivery', $delivery))->assertSessionHasErrors('delivery');
        $this->post(route('rider.deliveries.accept-pickup', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.accept-pickup', $delivery))->assertSessionHasErrors('delivery');
        $this->post(route('rider.deliveries.pickup', $delivery))->assertRedirect();
        $pickupAt = $delivery->fresh()->picked_up_at;
        $this->post(route('rider.deliveries.pickup', $delivery))->assertSessionHasErrors('delivery');
        $this->assertEquals($pickupAt, $delivery->fresh()->picked_up_at);

        $this->post(route('rider.logout'));
        $this->actingAs($operator, 'web');
        $this->post(route('logistics.deliveries.receive', $delivery), ['tracking_code' => 'WRONG'])
            ->assertSessionHasErrors('delivery');
        $this->post(route('logistics.deliveries.receive', $delivery), ['tracking_code' => $delivery->tracking_code])->assertRedirect();
        $this->post(route('logistics.deliveries.sort', $delivery), ['destination_area_id' => $areaId])->assertRedirect();
        $this->post(route('logistics.deliveries.assign-delivery', $delivery), ['rider_id' => $riderId])->assertRedirect();

        $this->post(route('logout'));
        $this->actingAs($rider, 'rider')->post(route('rider.deliveries.out-for-delivery', $delivery))->assertRedirect();
        $order->update(['status' => Order::STATUS_CANCELLED]);
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('stale.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('delivery');
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id, 'status' => Delivery::OUT_FOR_DELIVERY, 'delivered_at' => null,
        ]);
    }

    public function test_checkout_group_does_not_merge_seller_orders_or_transfer_partner_ownership(): void
    {
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
        $firstDelivery = Delivery::where('order_id', $first->id)->firstOrFail();
        $this->actingAs($sellerA)->get(route('seller.orders.show', $second))->assertNotFound();
        $this->actingAs($sellerA)->post(route('rider.deliveries.pickup', $firstDelivery))->assertForbidden();
    }

    private function order(int $buyerId, int $sellerId, string $status = Order::STATUS_READY_FOR_PICKUP,
        string $address = '1 Test Street, Test, Imus, Cavite'): Order
    {
        return Order::create(['buyer_id' => $buyerId, 'seller_id' => $sellerId, 'status' => $status,
            'total_amount' => '100.00', 'payment_method' => 'cod', 'shipping_method' => 'standard',
            'delivery_address' => $address]);
    }

    private function rider(int $partnerId): int
    {
        $areaId = DB::table('logistics_coverage_areas')->where('logistics_partner_id', $partnerId)->value('id');
        return DB::table('riders')->insertGetId([
            'logistics_partner_id' => $partnerId, 'coverage_area_id' => $areaId,
            'name' => 'Test Rider '.$partnerId, 'vehicle_type' => 'Motorcycle', 'status' => 'active',
            'availability_status' => 'available', 'email' => uniqid('rider', true).'@example.test',
            'password' => bcrypt('LongSecretPassword123!'),
        ]);
    }

    private function user(string $role): User
    {
        $user = new User();
        $user->forceFill([
            'email' => uniqid($role, true).'@example.test', 'password' => 'unused',
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now(),
        ])->save();
        return $user;
    }

    private function profile(string $table, User $user): int
    {
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
        $partnerId = DB::table('logistics_partners')->insertGetId([
            'user_id' => $user->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
        DB::table('logistics_coverage_areas')->insert([
            'logistics_partner_id' => $partnerId, 'area_name' => 'Cavite',
            'area_type' => 'province', 'cities' => 'Imus',
        ]);
        return $partnerId;
    }
}
