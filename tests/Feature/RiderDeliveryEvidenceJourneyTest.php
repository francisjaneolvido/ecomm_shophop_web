<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use App\Services\RiderBatchService;
use App\Services\RiderCustodyScanService;
use App\Services\RiderServiceAreaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderDeliveryEvidenceJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_creation_cannot_autoactivate_and_legacy_credentials_remain_company_scoped(): void
    {
        // Provisioning must bind credentials to the approved operator's partner, never a submitted partner ID.
        $operator = new User;
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

        $this->assertDatabaseMissing('riders', ['email' => 'rider@example.test']);
        $legacy = Rider::create(['logistics_partner_id' => $partnerId, 'name' => 'Existing Rider',
            'vehicle_type' => 'Motorcycle', 'status' => 'active']);
        $this->post(route('logistics.riders.provision', $legacy), [
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
        Storage::fake('local');
        $operator = $this->user('logistics');
        $partnerId = $this->partner($operator);
        $branchUser = $this->user('branch');
        $branch = SortingCenter::create([
            'logistics_partner_id' => $partnerId, 'user_id' => $branchUser->id, 'name' => 'Imus Branch', 'code' => uniqid('IMUS'),
            'type' => 'station_hub', 'status' => 'active', 'region' => 'Region IV-A', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'contact_no' => '09123456789']);
        $areaId = DB::table('logistics_coverage_areas')->where('logistics_partner_id', $partnerId)->value('id');
        DB::table('logistics_coverage_areas')->where('id', $areaId)->update(['sorting_center_id' => $branch->id]);
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $buyerId = $this->profile('buyers', $buyer);
        $sellerId = $this->profile('sellers', $seller);
        $order = Order::create([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId, 'logistics_partner_id' => $partnerId,
            'status' => Order::STATUS_TO_RECEIVE, 'total_amount' => '178.00', 'shipping_fee' => '58.00', 'cod_fee' => '20.00',
            'payment_method' => 'cod', 'shipping_method' => 'standard', 'delivery_address' => '1 Test Street, Test, Imus, Cavite',
            'delivery_name' => 'Test Buyer', 'delivery_phone' => '09123456789', 'delivery_province_name' => 'Cavite',
            'delivery_municipality_name' => 'Imus', 'delivery_barangay_name' => 'Test']);
        $order->update(['tracking_code' => 'SHP-20261108-'.str_pad($order->id, 6, '0', STR_PAD_LEFT)]);
        $riders = [];
        foreach (['a', 'b'] as $suffix) {
            $riders[] = Rider::create([
                'logistics_partner_id' => $partnerId, 'sorting_center_id' => $branch->id, 'name' => 'Rider '.strtoupper($suffix),
                'vehicle_type' => 'Motorcycle', 'plate_number' => 'TEST-'.$suffix, 'status' => 'active', 'availability_status' => 'available',
                'email' => $suffix.'@example.test', 'password' => 'LongSecretPassword123!', 'email_verified_at' => now()]);
        }
        [$riderA,$riderB] = $riders;
        $area = OperationalServiceArea::create([
            'logistics_partner_id' => $partnerId, 'sorting_center_id' => $branch->id, 'province' => 'Cavite',
            'municipality' => 'Imus', 'barangay' => 'Test', 'service_type' => 'both',
            'location_key' => RiderServiceAreaService::locationKey('Cavite', 'Imus', 'Test', 'both')]);
        $riderA->operationalServiceAreas()->attach($area->id);
        $delivery = Delivery::create([
            'order_id' => $order->id, 'tracking_code' => $order->tracking_code, 'logistics_partner_id' => $partnerId,
            'origin_sorting_center_id' => $branch->id, 'destination_sorting_center_id' => $branch->id,
            'current_sorting_center_id' => $branch->id, 'destination_area_id' => $areaId,
            'rider_id' => $riderA->id, 'assigned_at' => now(), 'status' => Delivery::SORTED]);
        $batches = app(RiderBatchService::class);
        $batches->configure($branch, $riderA->id, ['max_weight_kg' => 20, 'max_volume_m3' => 1, 'max_stops' => 10,
            'max_duration_minutes' => 120], 'user', $branchUser->id);
        $batch = $batches->create($branch, ['rider_id' => $riderA->id, 'type' => 'delivery',
            'scheduled_start' => now()->subMinute()->toIso8601String(), 'scheduled_end' => now()->addHour()->toIso8601String(),
            'estimated_minutes' => 30, 'parcels' => [['order_id' => $order->id, 'weight_kg' => 1, 'volume_m3' => 0.01]]],
            'user', $branchUser->id);
        $batches->transition($branch, $batch->id, 'dispatch', 'user', $branchUser->id);

        // Another rider cannot read or mutate the assigned parcel or its private evidence.
        $this->actingAs($riderB, 'rider')->get(route('rider.deliveries.show', $delivery))->assertNotFound();
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('foreign.jpg', 2, 'image/jpeg')])->assertNotFound();
        $this->post(route('rider.logout'));
        $this->actingAs($riderA, 'rider')->get(route('rider.deliveries.show', $delivery))->assertOk()->assertSee('Test Buyer');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('early.jpg', 2, 'image/jpeg')])->assertSessionHasErrors('delivery');
        // Web cannot bypass the existing destination-Branch camera/QR release.
        $this->post(route('rider.deliveries.out-for-delivery', $delivery))->assertSessionHasErrors('delivery');
        app(RiderCustodyScanService::class)->confirm($riderA, $order->tracking_code,
            RiderCustodyScanService::DELIVERY_HANDOFF);
        $this->assertSame(Delivery::OUT_FOR_DELIVERY, $delivery->fresh()->status);
        $this->assertSame($riderA->id, $delivery->fresh()->delivery_rider_id);
        $this->post(route('rider.deliveries.complete', $delivery), [])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('text.txt', 2, 'text/plain')])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg')])->assertSessionHasErrors('proof');
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg')])->assertRedirect();
        $completed = $delivery->fresh();
        $this->assertSame(Delivery::DELIVERED, $completed->status);
        $this->assertSame($riderA->id, $completed->delivered_rider_id);
        $this->assertNotNull($completed->delivered_at);
        Storage::disk('local')->assertExists($completed->proof_path);
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->get(route('delivery.proof', $delivery))->assertOk();
        $this->post(route('rider.deliveries.complete', $delivery), [
            'cash_collected' => '1', 'proof' => UploadedFile::fake()->create('replacement.jpg', 2, 'image/jpeg')])->assertSessionHasErrors('delivery');
        $this->assertSame($completed->proof_path, $delivery->fresh()->proof_path);
        $batches->transition($branch, $batch->id, 'complete', 'user', $branchUser->id);
        $this->post(route('rider.logout'));
        $this->assertFalse(Auth::guard('rider')->check());

        $this->actingAs($buyer, 'web')->get(route('buyer.orders'))->assertOk()->assertSee('Delivered');
        $this->get(route('delivery.proof', $delivery))->assertOk();
        $this->post(route('buyer.orders.confirm-receipt', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->actingAs($seller, 'web')->get(route('seller.orders.show', $order))->assertOk()->assertSee('Completed');
        $this->get(route('delivery.proof', $delivery))->assertOk();
        $this->actingAs($operator, 'web')->get(route('delivery.proof', $delivery))->assertOk();
        $this->actingAs($this->user('buyer'), 'web')->get(route('delivery.proof', $delivery))->assertForbidden();
        $this->get(route('rider.deliveries.index'))->assertForbidden();
        $this->post(route('logout'));
        $foreignOperator = $this->user('logistics');
        $foreignPartnerId = $this->partner($foreignOperator);
        $foreignRider = Rider::create(['logistics_partner_id' => $foreignPartnerId, 'name' => 'Foreign Rider',
            'vehicle_type' => 'Motorcycle', 'status' => 'active', 'email' => 'foreign@example.test', 'password' => 'LongSecretPassword123!']);
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
        $user = new User;
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
