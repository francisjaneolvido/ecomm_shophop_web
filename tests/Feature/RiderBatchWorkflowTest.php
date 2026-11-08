<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\RiderBatchItem;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Services\ParcelScanService;
use App\Services\RiderBatchService;
use App\Services\RiderCustodyScanService;
use App\Services\RiderServiceAreaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class RiderBatchWorkflowTest extends TestCase
{
    use SellerWorkspaceFixtures;

    private function network(): array
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $owner = $this->user('logistics');
        $partner = DB::table('logistics_partners')->insertGetId(['user_id' => $owner->id,
            'agreement_rep_name' => 'Test', 'agreement_date' => '2026-11-08', 'agreement_signature_path' => 'test.jpg',
            'company_name' => 'Batch Test Logistics', 'business_registration_no' => uniqid('BATCH'),
            'line_of_business' => 'motorcycle_courier', 'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'Test',
            'rep_sex' => 'male', 'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789', 'region' => 'IV-A',
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg']);
        $user = $this->user('branch');
        $branch = SortingCenter::create(['logistics_partner_id' => $partner, 'user_id' => $user->id, 'name' => 'Batch Branch',
            'code' => uniqid('BR'), 'type' => 'station_hub', 'status' => 'active', 'region' => 'IV-A', 'province' => 'Laguna',
            'municipality' => 'Calamba', 'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'contact_no' => '09123456789']);
        $rider = Rider::create(['logistics_partner_id' => $partner, 'sorting_center_id' => $branch->id, 'name' => 'Batch Rider',
            'email' => uniqid('rider').'@example.test', 'password' => 'TestPassword123!', 'email_verified_at' => now(),
            'vehicle_type' => 'Motorcycle', 'plate_number' => 'TEST-123', 'status' => 'active', 'availability_status' => 'available']);
        $area = OperationalServiceArea::create(['logistics_partner_id' => $partner, 'sorting_center_id' => $branch->id,
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => 'Real', 'service_type' => 'both',
            'location_key' => RiderServiceAreaService::locationKey('Laguna', 'Calamba', 'Real', 'both')]);
        $rider->operationalServiceAreas()->attach($area);
        $service = app(RiderBatchService::class);
        $service->configure($branch, $rider->id, ['max_weight_kg' => 100, 'max_volume_m3' => 2, 'max_stops' => 30,
            'max_duration_minutes' => 240, 'max_parcels' => null], 'user', $user->id);

        return compact('owner', 'partner', 'user', 'branch', 'rider', 'service');
    }

    private function parcel(array $n, string $type = 'pickup', string $barangay = 'Real'): Order
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $order = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id, 'logistics_partner_id' => $n['partner'],
            'status' => $type === 'pickup' ? Order::STATUS_READY_FOR_PICKUP : Order::STATUS_TO_RECEIVE,
            'total_amount' => 100, 'payment_method' => 'cod', 'shipping_method' => 'standard', 'delivery_name' => 'Buyer '.$buyer->id,
            'delivery_phone' => '09123456789', 'delivery_address' => '1 Street, '.$barangay.', Calamba, Laguna',
            'delivery_province_name' => 'Laguna', 'delivery_municipality_name' => 'Calamba', 'delivery_barangay_name' => $barangay]);
        $order->update(['tracking_code' => 'SHP-20261108-'.str_pad($order->id, 6, '0', STR_PAD_LEFT)]);
        if ($type === 'pickup') {
            PickupRequest::create(['order_id' => $order->id, 'logistics_partner_id' => $n['partner'],
                'origin_sorting_center_id' => $n['branch']->id, 'status' => PickupRequest::REQUESTED, 'requested_at' => now(),
                'origin_province_name' => 'Laguna', 'origin_municipality_name' => 'Calamba', 'origin_barangay_name' => $barangay, 'origin_street' => '1 Seller Street']);
        } else {
            $area = LogisticsCoverageArea::firstOrCreate(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
                'area_name' => 'Laguna', 'area_type' => 'province'], ['cities' => 'Calamba']);
            Delivery::create(['order_id' => $order->id, 'tracking_code' => $order->tracking_code, 'logistics_partner_id' => $n['partner'],
                'origin_sorting_center_id' => $n['branch']->id, 'destination_sorting_center_id' => $n['branch']->id,
                'current_sorting_center_id' => $n['branch']->id, 'destination_area_id' => $area->id, 'rider_id' => $n['rider']->id, 'assigned_at' => now(), 'status' => Delivery::SORTED]);
        }

        return $order;
    }

    private function plan(array $n, array $orders, string $type = 'pickup', int $offset = 0): array
    {
        return ['rider_id' => $n['rider']->id, 'type' => $type, 'scheduled_start' => now()->addMinutes($offset - 1)->toIso8601String(),
            'scheduled_end' => now()->addMinutes($offset + 60)->toIso8601String(), 'estimated_minutes' => 30,
            'parcels' => array_map(fn ($o) => ['order_id' => $o->id, 'weight_kg' => 1, 'volume_m3' => 0.01], $orders)];
    }

    private function createBatch(array $n, array $orders, string $type = 'pickup', int $offset = 0): RiderBatch
    {
        return $n['service']->create($n['branch'], $this->plan($n, $orders, $type, $offset), 'user', $n['user']->id);
    }

    private function invalid(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected batch validation to reject this operation.');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }

    private function dispatch(array $n, RiderBatch $b): void
    {
        $n['service']->transition($n['branch'], $b->id, 'dispatch', 'user', $n['user']->id);
    }

    public function test_multi_seller_pickup_has_independent_failure_and_requires_verified_branch_receipt(): void
    {
        $n = $this->network();
        $orders = [$this->parcel($n), $this->parcel($n)];
        $batch = $this->createBatch($n, $orders);
        $items = $batch->items()->get();
        $qr = app(RiderCustodyScanService::class);
        $this->assertSame(2, $n['service']->data($batch)['capacity']['totals']['stops']);
        $this->invalid(fn () => $qr->inspect($n['rider'], $orders[0]->tracking_code));
        $this->dispatch($n, $batch);
        $qr->confirm($n['rider'], $orders[0]->tracking_code, RiderCustodyScanService::SELLER_PICKUP);
        $n['service']->pickupFailure($n['rider'], $batch->id, $items[1]->id, 'Seller unavailable');
        $this->assertSame('in_progress', $batch->fresh()->status);
        $this->assertSame(1, $n['service']->data($batch->fresh())['progress']['settled']);
        $this->invalid(fn () => $n['service']->transition($n['branch'], $batch->id, 'complete', 'user', $n['user']->id));
        app(ParcelScanService::class)->receive($n['partner'], $n['branch']->id, $n['user']->id, $orders[0]->tracking_code);
        $n['service']->transition($n['branch'], $batch->id, 'complete', 'user', $n['user']->id);
        $this->assertSame('completed', $batch->fresh()->status);
        $this->assertSame(0, RiderBatchItem::whereNotNull('reserved_delivery_id')->count());
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $orders[1]->fresh()->status);
        $retry = $this->createBatch($n, [$orders[1]], 'pickup', 70);
        $this->assertNotSame($batch->id, $retry->id);
        $this->assertSame('pickup_failed', $n['service']->data($batch->fresh())['items'][1]['status']);
    }

    public function test_multi_buyer_delivery_preserves_cod_proof_and_failed_parcel_return(): void
    {
        Storage::fake('local');
        $n = $this->network();
        $orders = [$this->parcel($n, 'delivery'), $this->parcel($n, 'delivery')];
        $batch = $this->createBatch($n, $orders, 'delivery');
        $this->dispatch($n, $batch);
        foreach ($orders as $order) {
            app(RiderCustodyScanService::class)->confirm($n['rider'], $order->tracking_code, RiderCustodyScanService::DELIVERY_HANDOFF);
        }
        Sanctum::actingAs($n['rider'], ['*']);
        $this->postJson('/api/mobile/rider/assignments/'.$orders[0]->delivery->id.'/complete', [
            'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg'), 'cash_collected' => true])->assertOk();
        $this->postJson('/api/mobile/rider/assignments/'.$orders[1]->delivery->id.'/fail', ['failure_reason' => 'Buyer unavailable'])->assertOk();
        $this->assertDatabaseHas('cod_settlements', ['delivery_id' => $orders[0]->delivery->id, 'collected_by_rider_id' => $n['rider']->id]);
        $this->assertNotNull($orders[0]->delivery->fresh()->proof_path);
        $this->invalid(fn () => $n['service']->transition($n['branch'], $batch->id, 'complete', 'user', $n['user']->id));
        app(ParcelScanService::class)->receive($n['partner'], $n['branch']->id, $n['user']->id, $orders[1]->tracking_code);
        $this->invalid(fn () => app(ParcelScanService::class)->receive($n['partner'], $n['branch']->id, $n['user']->id, $orders[1]->tracking_code));
        $n['service']->transition($n['branch'], $batch->id, 'complete', 'user', $n['user']->id);
        $retry = $this->createBatch($n, [$orders[1]], 'delivery', 70);
        $orders[1]->delivery->update(['status' => Delivery::DELIVERED]);
        $this->assertSame(Delivery::DELIVERY_FAILED, $n['service']->data($batch->fresh())['items'][1]['status']);
    }

    public function test_reservations_schedule_and_one_active_batch_are_enforced(): void
    {
        $n = $this->network();
        $a = $this->parcel($n);
        $b = $this->parcel($n);
        $first = $this->createBatch($n, [$a]);
        $this->invalid(fn () => $this->createBatch($n, [$a], 'pickup', 70));
        $this->invalid(fn () => $this->createBatch($n, [$b], 'pickup', 10));
        $second = $this->createBatch($n, [$b], 'pickup', 70);
        $this->dispatch($n, $first);
        $this->travel(75)->minutes();
        $this->invalid(fn () => $this->dispatch($n, $second));
        $this->assertSame(1, RiderBatch::whereNotNull('active_rider_id')->count());
        $this->invalid(fn () => $n['service']->transition($n['branch'], $first->id, 'cancel', 'user', $n['user']->id));
    }

    public function test_capacity_and_out_of_coverage_roll_back_all_parcels(): void
    {
        $n = $this->network();
        $a = $this->parcel($n);
        $b = $this->parcel($n, 'pickup', 'Mayapa');
        $this->invalid(fn () => $this->createBatch($n, [$a, $b]));
        $this->assertSame(0, RiderBatch::count());
        $this->assertSame(0, Delivery::count());
        $n['service']->configure($n['branch'], $n['rider']->id, ['max_weight_kg' => 1, 'max_volume_m3' => 1, 'max_stops' => 10, 'max_duration_minutes' => 60], 'user', $n['user']->id);
        $plan = $this->plan($n, [$a]);
        $plan['parcels'][0]['weight_kg'] = 2;
        $this->invalid(fn () => $n['service']->create($n['branch'], $plan, 'user', $n['user']->id));
        $this->assertSame(0, Delivery::count());
        $this->assertDatabaseHas('pickup_requests', ['order_id' => $a->id, 'status' => PickupRequest::REQUESTED]);
    }

    public function test_quantity_is_configurable_and_missing_volume_is_not_zero(): void
    {
        $n = $this->network();
        $orders = [];
        for ($i = 0; $i < 8; $i++) {
            $orders[] = $this->parcel($n);
        }
        $batch = $this->createBatch($n, $orders);
        $this->assertCount(8, $batch->items);
        $this->invalid(fn () => $n['service']->configure($n['branch'], $n['rider']->id, ['max_weight_kg' => 1], 'user', $n['user']->id));
        $n['service']->transition($n['branch'], $batch->id, 'cancel', 'user', $n['user']->id);
        $plan = $this->plan($n, [$orders[0]]);
        $plan['parcels'][0]['volume_m3'] = 0;
        $this->invalid(fn () => $n['service']->create($n['branch'], $plan, 'user', $n['user']->id));
    }

    public function test_company_branch_and_rider_boundaries_and_main_hub_read_only(): void
    {
        $n = $this->network();
        $other = $this->network();
        $order = $this->parcel($n);
        $batch = $this->createBatch($n, [$order]);
        $payload = $this->plan($n, [$this->parcel($n)]);
        $this->actingAs($other['user'])->postJson(route('branch.rider-batches.store'), $payload)->assertNotFound();
        $this->actingAs($n['owner'])->postJson(route('branch.rider-batches.store'), $payload)->assertForbidden();
        $this->actingAs($n['user'])->postJson(route('branch.rider-batches.transition', $batch->id), ['action' => 'dispatch'])->assertOk();
        Sanctum::actingAs($other['rider'], ['*']);
        $this->getJson('/api/mobile/rider/batches/'.$batch->id)->assertNotFound();
        Sanctum::actingAs($n['rider'], ['*']);
        $this->getJson('/api/mobile/rider/batches/'.$batch->id)->assertOk()->assertJsonPath('data.batch.id', $batch->id);
        $this->postJson('/api/mobile/rider/batches/'.$batch->id.'/dispatch')->assertNotFound();
    }

    public function test_offline_rider_and_old_single_parcel_assignment_cannot_bypass_batch_policy(): void
    {
        $n = $this->network();
        $order = $this->parcel($n);
        $n['rider']->update(['availability_status' => 'offline']);
        $this->invalid(fn () => $this->createBatch($n, [$order]));
        $n['rider']->update(['availability_status' => 'available']);
        $this->actingAs($n['user'])->postJson(route('branch.dispatch.pickups.assign', $order->pickupRequest->id), ['rider_id' => $n['rider']->id])->assertUnprocessable();
        $this->assertSame(0, Delivery::count());
    }

    public function test_branch_and_main_hub_pages_render_and_staff_permissions_are_enforced(): void
    {
        $n = $this->network();
        $other = $this->network();
        $batch = $this->createBatch($n, [$this->parcel($n)]);
        $foreign = $this->createBatch($other, [$this->parcel($other)]);
        $this->actingAs($n['user'])->get(route('branch.rider-batches.index'))
            ->assertOk()->assertSee($batch->code)->assertDontSee($foreign->code);
        $this->actingAs($n['owner'])->get(route('logistics.rider-batches.index'))
            ->assertOk()->assertSee($batch->code)->assertDontSee($foreign->code)->assertDontSee('Dispatch route');
        $this->get(route('logistics.rider-batches.index', ['branch' => $other['branch']->id]))->assertNotFound();
        $staff = BranchStaff::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'name' => 'Branch Manager', 'email' => uniqid('staff').'@example.test', 'password' => 'TestPassword123!',
            'role' => 'manager', 'status' => 'active']);
        $this->actingAs($staff, 'logistics_staff')->get(route('logistics.staff.rider-batches.index'))->assertOk()->assertSee($batch->code);
        $staff->update(['role' => 'scanner']);
        $this->actingAs($staff->fresh(), 'logistics_staff')->postJson(route('logistics.staff.rider-batches.transition', $batch->id), ['action' => 'dispatch'])->assertForbidden();
        $n['branch']->update(['is_main' => true]);
        $this->actingAs($n['user'], 'web')->get(route('branch.rider-batches.index'))->assertForbidden();
    }

    public function test_web_live_updates_preserve_company_scope_and_branch_only_controls(): void
    {
        $n = $this->network();
        $other = $this->network();
        $batch = $this->createBatch($n, [$this->parcel($n)]);
        $foreign = $this->createBatch($other, [$this->parcel($other)]);
        $this->actingAs($n['user'])->getJson(route('branch.rider-batches.index'))
            ->assertOk()->assertSee($batch->code)->assertDontSee($foreign->code)->assertSee('Dispatch route');
        $this->dispatch($n, $batch);
        $this->actingAs($n['owner'])->getJson(route('logistics.rider-batches.index'))
            ->assertOk()->assertSee('In Progress')->assertDontSee($foreign->code)->assertDontSee('Complete batch');
    }

    public function test_dispatch_rechecks_physical_custody_address_and_current_coverage(): void
    {
        $n = $this->network();
        $order = $this->parcel($n, 'delivery');
        $batch = $this->createBatch($n, [$order], 'delivery');
        $foreign = $this->network();
        $order->delivery->update(['current_sorting_center_id' => $foreign['branch']->id]);
        $this->invalid(fn () => $this->dispatch($n, $batch));
        $order->delivery->update(['current_sorting_center_id' => $n['branch']->id]);
        $order->update(['delivery_address' => 'Changed address']);
        $this->invalid(fn () => $this->dispatch($n, $batch));
        $order->update(['delivery_address' => '1 Street, Real, Calamba, Laguna']);
        $n['rider']->operationalServiceAreas()->detach();
        $this->invalid(fn () => $this->dispatch($n, $batch));
        $this->assertNull($batch->fresh()->active_rider_id);
    }

    public function test_assignment_feed_identifies_reserved_parcels_for_mobile_without_duplicate_task_cards(): void
    {
        $n = $this->network();
        $batch = $this->createBatch($n, [$this->parcel($n)]);
        Sanctum::actingAs($n['rider'], ['*']);
        $this->getJson('/api/mobile/rider/assignments')->assertOk()
            ->assertJsonPath('data.assignments.0.rider_batch_id', $batch->id)
            ->assertJsonPath('data.assignments.0.rider_batch_status', 'queued');
    }

    public function test_same_rider_can_run_separate_pickup_and_delivery_batches_in_one_day(): void
    {
        $n = $this->network();
        $pickupOrder = $this->parcel($n);
        $deliveryOrder = $this->parcel($n, 'delivery');
        $pickup = $this->createBatch($n, [$pickupOrder]);
        $delivery = $this->createBatch($n, [$deliveryOrder], 'delivery', 70);
        $this->dispatch($n, $pickup);
        app(RiderCustodyScanService::class)->confirm($n['rider'], $pickupOrder->tracking_code, RiderCustodyScanService::SELLER_PICKUP);
        app(ParcelScanService::class)->receive($n['partner'], $n['branch']->id, $n['user']->id, $pickupOrder->tracking_code);
        $n['service']->transition($n['branch'], $pickup->id, 'complete', 'user', $n['user']->id);
        $this->travel(70)->minutes();
        $this->dispatch($n, $delivery);
        $this->assertSame('completed', $pickup->fresh()->status);
        $this->assertSame('in_progress', $delivery->fresh()->status);
        $this->assertSame(1, RiderBatch::whereNotNull('active_rider_id')->count());
        $this->assertSame('busy', $n['rider']->fresh()->availability_status);
    }

    public function test_configured_stop_volume_duration_and_optional_parcel_limits_are_independent(): void
    {
        $n = $this->network();
        $orders = [$this->parcel($n), $this->parcel($n)];
        $base = ['max_weight_kg' => 100, 'max_volume_m3' => 2, 'max_stops' => 30, 'max_duration_minutes' => 240, 'max_parcels' => null];
        foreach ([['max_stops' => 1], ['max_volume_m3' => 0.015], ['max_duration_minutes' => 29], ['max_parcels' => 1]] as $limit) {
            $n['service']->configure($n['branch'], $n['rider']->id, array_replace($base, $limit), 'user', $n['user']->id);
            $this->invalid(fn () => $this->createBatch($n, $orders));
            $this->assertSame(0, RiderBatch::count());
            $this->assertSame(0, Delivery::count());
        }
    }

    public function test_database_constraints_reject_second_active_rider_claim(): void
    {
        $n = $this->network();
        $first = $this->createBatch($n, [$this->parcel($n)]);
        $second = $this->createBatch($n, [$this->parcel($n)], 'pickup', 70);
        $this->dispatch($n, $first);
        $this->expectException(QueryException::class);
        $second->update(['status' => 'in_progress', 'active_rider_id' => $n['rider']->id]);
    }
}
