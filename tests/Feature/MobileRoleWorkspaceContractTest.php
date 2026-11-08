<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class MobileRoleWorkspaceContractTest extends TestCase
{
    use SellerWorkspaceFixtures;

    private function partnerId(): int
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $owner = $this->user('logistics');
        return DB::table('logistics_partners')->insertGetId([
            'user_id' => $owner->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-11-08',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => uniqid('TEST'), 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV-A', 'province' => 'Laguna', 'municipality' => 'Calamba',
            'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
    }

    private function center(int $partner): SortingCenter
    {
        return SortingCenter::create(['logistics_partner_id' => $partner, 'code' => uniqid('BR-'),
            'name' => 'Test Branch', 'type' => 'station_hub', 'status' => 'active',
            'region' => 'Region IV-A', 'province' => 'Laguna', 'municipality' => 'Calamba',
            'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'contact_no' => '09123456789']);
    }

    private function driver(int $partner): LinehaulDriver
    {
        return LinehaulDriver::create(['logistics_partner_id' => $partner, 'name' => 'Test Driver',
            'email' => uniqid('driver').'@example.test', 'password' => 'TestPassword123!', 'status' => 'active']);
    }

    private function rider(int $partner): Rider
    {
        return Rider::create(['logistics_partner_id' => $partner, 'name' => 'Test Rider',
            'email' => uniqid('rider').'@example.test', 'password' => 'TestPassword123!',
            'status' => 'active', 'email_verified_at' => now(), 'vehicle_type' => 'Motorcycle']);
    }

    public function test_mobile_workspaces_require_a_session_and_correct_role(): void
    {
        $paths = ['/seller/dashboard', '/logistics/scanner/branches', '/linehaul/trips', '/rider/assignments'];
        foreach ($paths as $path) $this->getJson('/api/mobile'.$path)->assertUnauthorized();
        Sanctum::actingAs($this->seller());
        $this->getJson('/api/mobile/seller/dashboard')->assertOk();
        foreach (array_slice($paths, 1) as $path) $this->getJson('/api/mobile'.$path)->assertForbidden();
    }

    public function test_linehaul_reads_owned_trips_and_cannot_acknowledge_destination_arrival(): void
    {
        $partner = $this->partnerId();
        $driver = $this->driver($partner);
        $other = $this->driver($partner);
        $from = $this->center($partner);
        $to = $this->center($partner);
        $vehicle = FleetVehicle::create(['logistics_partner_id' => $partner, 'home_hub_id' => $from->id,
            'code' => 'VAN-01', 'plate_number' => 'ABC1234', 'vehicle_type' => 'van', 'status' => 'available']);
        $owned = LinehaulTrip::create(['logistics_partner_id' => $partner, 'driver_id' => $driver->id,
            'from_sorting_center_id' => $from->id, 'to_sorting_center_id' => $to->id,
            'vehicle_id' => $vehicle->id, 'code' => 'TRIP-OWNED', 'status' => LinehaulTrip::SCHEDULED]);
        LinehaulTrip::create(['logistics_partner_id' => $partner, 'driver_id' => $other->id,
            'from_sorting_center_id' => $from->id, 'to_sorting_center_id' => $to->id,
            'vehicle_id' => $vehicle->id, 'code' => 'TRIP-FOREIGN', 'status' => LinehaulTrip::SCHEDULED]);
        Sanctum::actingAs($driver);
        $this->getJson('/api/mobile/linehaul/trips')->assertOk()->assertJsonCount(1, 'data.trips')
            ->assertJsonPath('data.trips.0.code', 'TRIP-OWNED');
        $this->postJson('/api/mobile/linehaul/manifest/confirm', ['manifest_code' => 'MAN-TEST', 'action' => 'arrive'])
            ->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->assertSame(LinehaulTrip::SCHEDULED, $owned->fresh()->status);
        $driver->update(['status' => 'inactive']);
        $this->getJson('/api/mobile/linehaul/trips')->assertForbidden();
    }

    public function test_logistics_staff_is_locked_to_the_assigned_branch_for_reads_and_receipts(): void
    {
        $partner = $this->partnerId();
        $assigned = $this->center($partner);
        $other = $this->center($partner);
        $staff = BranchStaff::create(['logistics_partner_id' => $partner, 'sorting_center_id' => $assigned->id,
            'name' => 'Test Staff', 'email' => 'staff@example.test', 'password' => 'TestPassword123!',
            'role' => 'staff', 'status' => 'active']);
        Sanctum::actingAs($staff);
        $this->getJson('/api/mobile/logistics/scanner/branches')->assertOk()->assertJsonPath('data.branch_locked', true)
            ->assertJsonCount(1, 'data.branches')->assertJsonPath('data.branches.0.id', $assigned->id);
        $this->getJson('/api/mobile/logistics/scanner/history?sorting_center_id='.$assigned->id)->assertOk();
        $this->getJson('/api/mobile/logistics/scanner/history?sorting_center_id='.$other->id)->assertForbidden();
        $this->postJson('/api/mobile/logistics/scanner/receive', ['sorting_center_id' => $other->id,
            'tracking_code' => 'SHP-20261108-000001'])->assertForbidden();
        $this->assertDatabaseCount('parcel_scan_events', 0);
    }

    public function test_rider_permissions_follow_assignment_status_and_qr_cannot_be_bypassed(): void
    {
        $partner = $this->partnerId();
        $rider = $this->rider($partner);
        $seller = $this->seller();
        $buyer = $this->buyer();
        $order = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id,
            'status' => Order::STATUS_READY_FOR_PICKUP, 'total_amount' => 250, 'payment_method' => 'cod',
            'shipping_method' => 'standard', 'delivery_name' => 'Test Buyer', 'delivery_phone' => '09123456789',
            'delivery_address' => 'Calamba Laguna']);
        $delivery = Delivery::create(['order_id' => $order->id, 'logistics_partner_id' => $partner,
            'rider_id' => $rider->id, 'assigned_at' => now(), 'tracking_code' => 'SHP-20261108-000001', 'status' => Delivery::PICKUP_ASSIGNED]);
        Sanctum::actingAs($rider);
        $this->getJson('/api/mobile/rider/assignments')->assertOk()
            ->assertJsonPath('data.assignments.0.assignment_type', 'pickup')
            ->assertJsonPath('data.assignments.0.can_confirm_pickup', true)
            ->assertJsonPath('data.assignments.0.can_complete_delivery', false);
        foreach (['accept-pickup', 'pickup', 'out-for-delivery'] as $action) {
            $this->postJson('/api/mobile/rider/assignments/'.$delivery->id.'/'.$action)
                ->assertConflict()->assertJsonPath('status', 'qr_required');
        }
        $this->assertSame(Delivery::PICKUP_ASSIGNED, $delivery->fresh()->status);
        $order->update(['status' => Order::STATUS_TO_RECEIVE]);
        $delivery->update(['delivery_rider_id' => $rider->id, 'status' => Delivery::OUT_FOR_DELIVERY]);
        $this->getJson('/api/mobile/rider/assignments')->assertOk()
            ->assertJsonPath('data.assignments.0.assignment_type', 'delivery')
            ->assertJsonPath('data.assignments.0.can_confirm_pickup', false)
            ->assertJsonPath('data.assignments.0.can_complete_delivery', true);
        $this->postJson('/api/mobile/rider/assignments/'.$delivery->id.'/complete', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['proof', 'cash_collected']);
        $this->assertSame(Delivery::OUT_FOR_DELIVERY, $delivery->fresh()->status);
        Sanctum::actingAs($this->rider($partner));
        $this->getJson('/api/mobile/rider/assignments/'.$delivery->id)->assertNotFound();
        $this->getJson('/api/mobile/rider/assignments')->assertOk()->assertJsonCount(0, 'data.assignments');
    }
}
