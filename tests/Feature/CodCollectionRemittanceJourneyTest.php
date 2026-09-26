<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CodCollectionRemittanceJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_requires_explicit_cash_collection_and_uses_persisted_order_total(): void
    {
        // Completion is the public financial boundary: no photo or client amount can silently imply cash collection.
        [$order, $delivery, $rider] = $this->delivery();
        Storage::fake('local');
        $this->actingAs($rider, 'rider');

        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('cash_collected');
        $this->assertSame('in_transit', $delivery->fresh()->status);
        $this->assertDatabaseMissing('cod_settlements', ['delivery_id' => $delivery->id]);

        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'expected_amount' => '0.01', 'collected_amount' => '0.01',
            'rider_id' => 999, 'logistics_partner_id' => 999,
            'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertRedirect();

        $settlement = DB::table('cod_settlements')->where('delivery_id', $delivery->id)->first();
        $this->assertNotNull($settlement);
        $this->assertSame($order->id, $settlement->order_id);
        $this->assertSame('178', (string) (int) $settlement->expected_amount);
        $this->assertEquals('178.00', $settlement->collected_amount);
        $this->assertSame($rider->id, $settlement->collected_by_rider_id);
        $this->assertNotNull($settlement->collected_at);
        $this->assertSame('collected', $settlement->status);
        $this->assertSame('delivered', $delivery->fresh()->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertNotNull($delivery->fresh()->proof_path);
        $this->assertSame($rider->id, $delivery->fresh()->delivered_rider_id);
        $this->assertEquals('178.00', $order->fresh()->total_amount);
        $this->assertEquals('58.00', $order->fresh()->shipping_fee);
        $this->assertEquals('20.00', $order->fresh()->cod_fee);

        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('again.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('delivery');
        $this->assertSame(1, DB::table('cod_settlements')->count());
    }

    public function test_foreign_rider_and_non_cod_delivery_cannot_collect(): void
    {
        // Assignment and payment method are persisted authority even when a forged Delivery row exists.
        [$order, $delivery, $rider] = $this->delivery();
        $foreign = $this->rider($rider->logistics_partner_id, 'foreign@example.test');
        Storage::fake('local');
        $this->actingAs($foreign, 'rider')->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertNotFound();
        $order->update(['payment_method' => 'gcash']);
        $this->actingAs($rider, 'rider')->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('delivery');
        $this->assertDatabaseMissing('cod_settlements', ['delivery_id' => $delivery->id]);
        $this->assertSame('in_transit', $delivery->fresh()->status);
    }

    public function test_responsible_rider_remits_and_owning_logistics_reconciles_once(): void
    {
        // A Rider declaration and partner receipt are separate persisted trust events with separate actors.
        [$order, $delivery, $rider, $operator] = $this->delivery();
        $this->complete($delivery, $rider);
        $foreign = $this->rider($rider->logistics_partner_id, 'foreign@example.test');

        $this->actingAs($foreign, 'rider')->post(route('rider.settlements.remit', $delivery), [
            'amount' => '0.01',
        ])->assertNotFound();
        $this->actingAs($rider, 'rider')->get(route('rider.settlements.index'))
            ->assertOk()->assertSee('178.00')->assertSee('Awaiting remittance');
        $this->post(route('rider.settlements.remit', $delivery), ['amount' => '0.01'])
            ->assertRedirect();
        $settlement = DB::table('cod_settlements')->where('delivery_id', $delivery->id)->first();
        $this->assertSame('remitted', $settlement->status);
        $this->assertSame($rider->id, $settlement->remitted_by_rider_id);
        $this->assertNotNull($settlement->remitted_at);
        $this->assertEquals('178.00', $settlement->collected_amount);
        // The first remittance is final; a stale form cannot move cash backward.
        $this->post(route('rider.settlements.remit', $delivery))->assertSessionHasErrors('settlement');

        $this->post(route('rider.logout'));
        $other = $this->user('logistics');
        $this->partner($other);
        $this->actingAs($other)->get(route('logistics.settlements.index'))
            ->assertOk()->assertDontSee('Order #'.$order->id);
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertNotFound();
        $this->actingAs($operator)->get(route('logistics.settlements.index'))
            ->assertOk()->assertSee('Order #'.$order->id)->assertSee('Awaiting receipt confirmation');
        $this->post(route('logistics.settlements.reconcile', $delivery), ['amount' => '0.01', 'partner_id' => 999])
            ->assertRedirect();
        $settlement = DB::table('cod_settlements')->where('delivery_id', $delivery->id)->first();
        $this->assertSame('reconciled', $settlement->status);
        $this->assertSame($operator->id, $settlement->reconciled_by_user_id);
        $this->assertNotNull($settlement->reconciled_at);
        $this->assertEquals('178.00', $settlement->received_amount);
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertSessionHasErrors('settlement');
    }

    public function test_legacy_delivered_cod_is_unrecorded_and_remittance_needs_collection(): void
    {
        // A historical delivery completion cannot supply a missing cash actor or timestamp.
        [$order, $delivery, $rider, $operator] = $this->delivery();
        $order->update(['status' => Order::STATUS_COMPLETED]);
        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->actingAs($rider, 'rider')->get(route('rider.settlements.index'))
            ->assertOk()->assertSee('Settlement not recorded');
        // No collector row exists, so an old delivery grants no remittance capability.
        $this->post(route('rider.settlements.remit', $delivery))->assertNotFound();
        $this->post(route('rider.logout'));
        $this->actingAs($operator)->get(route('logistics.settlements.index'))
            ->assertOk()->assertSee('Settlement not recorded');
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertSessionHasErrors('settlement');
        $this->assertDatabaseCount('cod_settlements', 0);
    }

    public function test_suspended_collector_can_return_existing_cash_but_cannot_resume_delivery_work(): void
    {
        // Suspension closes Rider fulfillment while leaving a narrow authenticated cash return path.
        [$order, $delivery, $rider] = $this->delivery();
        $this->complete($delivery, $rider);
        $this->post(route('rider.logout'));
        $rider->update(['status' => 'suspended']);

        $this->post(route('rider.login.store'), [
            'email' => $rider->email, 'password' => 'LongSecretPassword123!',
        ])->assertRedirect(route('rider.settlements.index'));
        $this->get(route('rider.deliveries.index'))->assertForbidden();
        $this->get(route('rider.settlements.index'))->assertOk()->assertSee('Awaiting remittance');
        $this->post(route('rider.settlements.remit', $delivery))->assertRedirect();
        $this->assertDatabaseHas('cod_settlements', [
            'delivery_id' => $delivery->id, 'status' => 'remitted',
            'remitted_by_rider_id' => $rider->id,
        ]);
    }

    public function test_logistics_cannot_confirm_before_remittance_and_buyer_seller_guest_cannot_mutate(): void
    {
        // Operational roles and the collection lifecycle deny receipt claims before the Rider submits cash.
        [$order, $delivery, $rider, $operator] = $this->delivery();
        $this->complete($delivery, $rider);
        $this->post(route('rider.logout'));
        $this->actingAs($operator)->post(route('logistics.settlements.reconcile', $delivery))
            ->assertSessionHasErrors('settlement');
        $this->post(route('logout'));

        $buyer = User::where('account_type', 'buyer')->firstOrFail();
        $seller = User::where('account_type', 'seller')->firstOrFail();
        $this->actingAs($buyer)->get(route('buyer.orders'))
            ->assertOk()->assertSee('178.00')->assertSee('collected');
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertForbidden();
        // A web Buyer cannot reuse the Rider guard even if the test client retained an old Rider cookie.
        $this->post(route('rider.settlements.remit', $delivery))->assertForbidden();
        $this->post(route('logout'));

        $this->actingAs($seller)->get(route('seller.orders.show', $order))
            ->assertOk()->assertSee('COD collected')->assertDontSee('Seller payout received');
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertForbidden();
        $this->post(route('logout'));
        $this->post(route('logistics.settlements.reconcile', $delivery))->assertRedirect(route('login'));
        $this->post(route('rider.settlements.remit', $delivery))->assertRedirect(route('login'));
        $this->assertDatabaseHas('cod_settlements', ['delivery_id' => $delivery->id, 'status' => 'collected']);
    }

    private function complete(Delivery $delivery, Rider $rider): void
    {
        // A real HTTP completion establishes the prerequisite without direct settlement row writes.
        Storage::fake('local');
        $this->actingAs($rider, 'rider')->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertRedirect();
    }

    private function delivery(): array
    {
        // Seed only the persisted role and fulfillment prerequisites; the test drives financial transitions by HTTP.
        $operator = $this->user('logistics');
        $partnerId = $this->partner($operator);
        $buyerId = $this->profile('buyers', $this->user('buyer'));
        $sellerId = $this->profile('sellers', $this->user('seller'));
        $rider = $this->rider($partnerId, 'owner@example.test');
        $order = Order::create([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId, 'status' => Order::STATUS_TO_RECEIVE,
            'total_amount' => '178.00', 'shipping_fee' => '58.00', 'cod_fee' => '20.00',
            'payment_method' => 'cod', 'delivery_name' => 'Test Buyer',
            'delivery_phone' => '09123456789', 'delivery_address' => '1 Test Street, Test, Imus, Cavite',
        ]);
        $delivery = Delivery::create([
            'order_id' => $order->id, 'logistics_partner_id' => $partnerId, 'rider_id' => $rider->id,
            'status' => 'in_transit', 'assigned_at' => now(), 'picked_up_at' => now(), 'in_transit_at' => now(),
        ]);

        return [$order, $delivery, $rider, $operator];
    }

    private function rider(int $partnerId, string $email): Rider
    {
        return Rider::create([
            'logistics_partner_id' => $partnerId, 'name' => $email, 'vehicle_type' => 'Motorcycle',
            'status' => 'active', 'email' => $email, 'password' => bcrypt('LongSecretPassword123!'),
        ]);
    }

    private function user(string $role): User
    {
        $user = new User();
        $user->forceFill(['email' => uniqid($role, true).'@example.test', 'password' => 'unused',
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now()])->save();
        return $user;
    }

    private function profile(string $table, User $user): int
    {
        return DB::table($table)->insertGetId([
            'user_id' => $user->id, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Cavite',
            'municipality_code' => '01', 'municipality_name' => 'Imus',
            'barangay_code' => '01', 'barangay_name' => 'Test',
            'street_address' => '1 Test Street', 'valid_id_path' => 'test.jpg',
        ] + ($table === 'sellers' ? [
            'business_name' => 'Test Shop', 'business_category' => 'Home',
            'business_permit_path' => 'test.jpg',
        ] : []));
    }

    private function partner(User $operator): int
    {
        return DB::table('logistics_partners')->insertGetId([
            'user_id' => $operator->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
    }
}
