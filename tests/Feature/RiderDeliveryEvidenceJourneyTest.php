<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderDeliveryEvidenceJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_provisions_a_hashed_rider_credential_and_rider_signs_in(): void
    {
        // Provisioning must bind credentials to the approved operator's partner, never a submitted partner ID.
        $operator = new User();
        $operator->forceFill(['email' => 'operator@example.test', 'password' => 'unused',
            'account_type' => 'logistics', 'status' => 'approved'])->save();
        $partnerId = DB::table('logistics_partners')->insertGetId([
            'user_id' => $operator->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);

        $this->actingAs($operator)->post(route('logistics.riders.store'), [
            'name' => 'Test Rider', 'vehicle_type' => 'Motorcycle',
            'email' => 'rider@example.test', 'password' => 'LongSecretPassword123!',
            'password_confirmation' => 'LongSecretPassword123!', 'logistics_partner_id' => 999,
        ])->assertRedirect();

        $rider = DB::table('riders')->where('email', 'rider@example.test')->first();
        $this->assertNotNull($rider);
        $this->assertSame($partnerId, $rider->logistics_partner_id);
        $this->assertNotSame('LongSecretPassword123!', $rider->password);

        $this->post(route('logout'));
        $this->post(route('rider.login.store'), [
            'email' => 'rider@example.test', 'password' => 'LongSecretPassword123!',
        ])->assertRedirect(route('rider.deliveries.index'));
        $this->get(route('rider.deliveries.index'))->assertOk();
    }

    public function test_assigned_rider_owns_transitions_and_private_immutable_photo_proof(): void
    {
        // A real partner, Buyer, Seller, Order and Delivery expose cross-role state through HTTP boundaries.
        Storage::fake('local');
        $operator = $this->user('logistics');
        $partnerId = $this->partner($operator);
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $order = Order::create([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId,
            'status' => Order::STATUS_READY_FOR_PICKUP, 'total_amount' => '178.00',
            'shipping_fee' => '58.00', 'cod_fee' => '20.00', 'payment_method' => 'cod',
            'shipping_method' => 'standard', 'delivery_address' => '1 Test Street, Test, Imus, Cavite',
            'delivery_name' => 'Test Buyer', 'delivery_phone' => '09123456789',
        ]);
        $this->actingAs($operator)->post(route('logistics.riders.store'), [
            'name' => 'Rider A', 'vehicle_type' => 'Motorcycle', 'email' => 'a@example.test',
            'password' => 'LongSecretPassword123!', 'password_confirmation' => 'LongSecretPassword123!',
        ])->assertRedirect();
        $riderA = Rider::where('email', 'a@example.test')->firstOrFail();
        $this->post(route('logistics.riders.store'), [
            'name' => 'Rider B', 'vehicle_type' => 'Motorcycle', 'email' => 'b@example.test',
            'password' => 'LongSecretPassword123!', 'password_confirmation' => 'LongSecretPassword123!',
        ])->assertRedirect();
        $riderB = Rider::where('email', 'b@example.test')->firstOrFail();
        $this->post(route('logistics.deliveries.assign', $order), ['rider_id' => $riderA->id])->assertRedirect();
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->assertSame($partnerId, $delivery->logistics_partner_id);
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $order->fresh()->status);

        $this->post(route('logout'));
        $this->get(route('rider.deliveries.index'))->assertRedirect(route('rider.login'));
        $this->actingAs($riderB, 'rider')->get(route('rider.deliveries.index'))
            ->assertOk()->assertDontSee('Order #'.$order->id);
        $this->get(route('rider.deliveries.show', $delivery))->assertNotFound();
        $this->post(route('rider.deliveries.pickup', $delivery))->assertNotFound();
        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('foreign.jpg', 2, 'image/jpeg'),
        ])->assertNotFound();
        $this->post(route('rider.logout'));

        $this->post(route('rider.login.store'), [
            'email' => 'a@example.test', 'password' => 'LongSecretPassword123!',
        ])->assertRedirect(route('rider.deliveries.index'));
        $this->get(route('rider.deliveries.show', $delivery))->assertOk()->assertSee('Test Buyer');
        // Cash acknowledgement does not let the Rider skip pickup or transit.
        $this->post(route('rider.deliveries.complete', $delivery), ['cash_collected' => '1', 'proof' => UploadedFile::fake()->create('early.jpg', 2, 'image/jpeg')])
            ->assertSessionHasErrors('delivery');
        $this->post(route('rider.deliveries.pickup', $delivery))->assertRedirect();
        $this->post(route('rider.deliveries.pickup', $delivery))->assertSessionHasErrors('delivery');
        $this->assertSame($riderA->id, $delivery->fresh()->pickup_rider_id);
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->post(route('rider.deliveries.transit', $delivery))->assertRedirect();
        $this->assertSame($riderA->id, $delivery->fresh()->transit_rider_id);
        $this->post(route('rider.deliveries.transit', $delivery))->assertSessionHasErrors('delivery');
        $this->post(route('rider.deliveries.complete', $delivery), [])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('text.txt', 2, 'text/plain'),
        ])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'proof' => UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg'),
        ])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            // Current COD completion records an explicit cash actor alongside immutable delivery proof.
            'cash_collected' => '1',
            'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'),
        ])->assertRedirect();
        $completed = $delivery->fresh();
        $this->assertSame('delivered', $completed->status);
        $this->assertSame($riderA->id, $completed->delivered_rider_id);
        $this->assertNotNull($completed->delivered_at);
        Storage::disk('local')->assertExists($completed->proof_path);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(200, $this->get(route('delivery.proof', $delivery))->getStatusCode(), 'Rider proof access');
        $this->post(route('rider.deliveries.complete', $delivery), [
            // A repeated cash declaration cannot replace an already completed delivery event.
            'cash_collected' => '1',
            'proof' => UploadedFile::fake()->create('replacement.jpg', 2, 'image/jpeg'),
        ])->assertSessionHasErrors('delivery');
        $this->assertSame($completed->proof_path, $delivery->fresh()->proof_path);

        $this->post(route('rider.logout'));
        $this->assertFalse(Auth::guard('rider')->check(), 'Rider logout clears the session identity');
        $this->actingAs($buyer, 'web')->get(route('buyer.orders'))->assertOk()->assertSee('Delivered');
        $this->assertSame($buyerId, Auth::guard('web')->user()?->buyer?->id);
        $this->assertSame(200, $this->get(route('delivery.proof', $delivery))->getStatusCode(), 'Buyer proof access');
        $this->actingAs($seller, 'web')->get(route('seller.orders.show', $order))->assertOk()->assertSee('Delivered');
        $this->assertSame(200, $this->get(route('delivery.proof', $delivery))->getStatusCode(), 'Seller proof access');
        $this->actingAs($operator, 'web')->get(route('logistics.deliveries.board'))->assertOk()->assertSee('Rider A');
        $this->assertSame(200, $this->get(route('delivery.proof', $delivery))->getStatusCode(), 'Logistics proof access');
        $this->actingAs($this->user('buyer'), 'web')->get(route('delivery.proof', $delivery))->assertForbidden();
        $this->get(route('rider.deliveries.index'))->assertForbidden();
        $this->post(route('rider.deliveries.pickup', $delivery))->assertForbidden();
        $this->post(route('logout'));
        $foreignOperator = $this->user('logistics');
        $foreignPartnerId = $this->partner($foreignOperator);
        $foreignRider = Rider::create(['logistics_partner_id' => $foreignPartnerId,
            'name' => 'Foreign Rider', 'vehicle_type' => 'Motorcycle', 'status' => 'active',
            'email' => 'foreign@example.test', 'password' => bcrypt('LongSecretPassword123!')]);
        $this->actingAs($foreignRider, 'rider')->get(route('rider.deliveries.show', $delivery))->assertNotFound();
        $this->get(route('delivery.proof', $delivery))->assertForbidden();
    }

    public function test_legacy_provisioning_and_suspension_revoke_rider_operations(): void
    {
        // An old Rider row gets no generated credential and cannot operate until its own partner provisions it.
        $operator = $this->user('logistics');
        $partnerId = $this->partner($operator);
        $legacy = Rider::create(['logistics_partner_id' => $partnerId,
            'name' => 'Legacy Rider', 'vehicle_type' => 'Motorcycle', 'status' => 'active']);
        $this->actingAs($operator)->post(route('logistics.riders.provision', $legacy), [
            'email' => 'legacy@example.test', 'password' => 'LongSecretPassword123!',
            'password_confirmation' => 'LongSecretPassword123!',
        ])->assertRedirect();
        $this->assertNotSame('LongSecretPassword123!', $legacy->fresh()->password);
        $this->post(route('logistics.riders.provision', $legacy), [
            'email' => 'replace@example.test', 'password' => 'AnotherLongSecret123!',
            'password_confirmation' => 'AnotherLongSecret123!',
        ])->assertSessionHasErrors('rider');
        $this->post(route('logout'));
        $this->post(route('rider.login.store'), [
            'email' => 'legacy@example.test', 'password' => 'LongSecretPassword123!',
        ])->assertRedirect(route('rider.deliveries.index'));
        $this->get(route('rider.deliveries.index'))->assertOk();
        $legacy->update(['status' => 'suspended']);
        $this->get(route('rider.deliveries.index'))->assertForbidden();
        $this->post(route('rider.logout'));
        $this->post(route('rider.login.store'), [
            'email' => 'legacy@example.test', 'password' => 'LongSecretPassword123!',
        ])->assertSessionHasErrors('email');
    }

    private function user(string $role): User
    {
        // Approved role fixtures expose the normal middleware without populating Kyle's runtime database.
        $user = new User();
        $user->forceFill(['email' => uniqid($role, true).'@example.test', 'password' => 'unused',
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now()])->save();
        return $user;
    }

    private function profile(string $table, User $user): int
    {
        // Buyer and Seller profile IDs, rather than users.id, own the Order foreign keys.
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
        // Coverage is a prerequisite for HTTP assignment, never a Delivery authorization shortcut.
        $partnerId = DB::table('logistics_partners')->insertGetId([
            'user_id' => $operator->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
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
