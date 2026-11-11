<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use App\Services\CodRemittanceService;
use App\Services\FinanceMoney;
use App\Services\PartnerCompensationService;
use App\Services\PlatformFinancialPolicyService;
use App\Services\ShopHopFinance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HybridFinancialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function collected(): array
    {
        [$order, $delivery, $rider, $operator] = $this->delivery();
        $order->update(['total_amount' => '1100.00', 'shipping_fee' => '100.00', 'cod_fee' => '0.00']);
        $center = SortingCenter::create(['logistics_partner_id' => $rider->logistics_partner_id,
            'name' => 'Receiving branch', 'code' => uniqid('BR'), 'type' => 'station_hub', 'status' => 'active',
            'region' => 'Region IV-A', 'province' => 'Cavite', 'municipality' => 'Imus', 'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1']);
        $this->complete($delivery, $rider);

        return [$order, $delivery, $rider, $operator, $center, CodSettlement::where('delivery_id', $delivery->id)->firstOrFail()];
    }

    private function policy(User $operator, Rider $rider, string $type = 'per_delivery', string $rate = '25.00'): object
    {
        return app(PartnerCompensationService::class)->createPolicy($operator, [
            'personnel_type' => 'rider', 'personnel_id' => $rider->id, 'type' => $type,
            'rate' => $rate, 'schedule' => 'Partner weekly payroll', 'effective_from' => now()->subMonth()->startOfMonth()->toDateString(),
            'effective_until' => null,
        ]);
    }

    public function test_full_1100_collection_remittance_and_950_65_85_allocation(): void
    {
        [$order, $delivery, $rider, $operator, $center, $cash] = $this->collected();
        MarketplaceRateSetting::current()->update(['commission_percent' => '5.00', 'shipping_commission_percent' => '15.00']);
        $finance = app(ShopHopFinance::class);
        $finance->snapshot($order, 1000, 0, 0, 0, 0);
        $snapshot = OrderFinancial::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(95000, FinanceMoney::minor($snapshot->seller_earnings));
        $this->assertSame(8500, FinanceMoney::minor($snapshot->logistics_earnings));
        $this->assertSame(6500, FinanceMoney::minor($snapshot->platform_net));
        $this->assertSame(110000, FinanceMoney::minor($snapshot->seller_earnings) + FinanceMoney::minor($snapshot->logistics_earnings) + FinanceMoney::minor($snapshot->platform_net));
        $this->policy($operator, $rider);
        app(PartnerCompensationService::class)->delivery($delivery);
        app(PartnerCompensationService::class)->delivery($delivery);
        $this->assertDatabaseCount('partner_personnel_earnings', 1);
        $earning = DB::table('partner_personnel_earnings')->first();
        $this->assertSame(2500, $earning->amount_minor);
        // Personnel can be paid before Buyer receipt confirmation and before platform settlement release.
        app(PartnerCompensationService::class)->approve($operator, $earning->id);
        app(PartnerCompensationService::class)->paid($operator, $earning->id, 'BANK-PARTNER-PAID-25');
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $service = app(CodRemittanceService::class);
        $batch = $service->submit($rider, $center->id, 'full-1100', 'RECEIPT-001', [['collection_id' => $cash->id, 'amount' => '1100.00']]);
        $service->verify($operator, $batch->id, [['collection_id' => $cash->id, 'amount' => '1100.00']]);
        $this->assertSame('1100.00', $cash->fresh()->received_amount);
        $this->assertSame('reconciled', $cash->fresh()->status);
        $this->assertDatabaseHas('cash_custody_movements', ['batch_id' => $batch->id, 'amount_minor' => 110000]);
        $this->assertDatabaseHas('partner_personnel_earnings', ['id' => $earning->id, 'amount_minor' => 2500, 'status' => 'paid']);
        $order->update(['status' => Order::STATUS_COMPLETED]);
        $cash->fresh()->update(['admin_remitted_at' => now(), 'admin_remittance_reference' => 'ADMIN-CASH',
            'admin_received_at' => now(), 'admin_received_amount' => '1100.00']);
        $finance->prepareForOrder($order->id);
        $finance->prepareForOrder($order->id);
        $this->assertDatabaseCount('settlement_entries', 2);
        $this->assertDatabaseHas('settlement_entries', ['order_id' => $order->id, 'kind' => 'seller', 'amount' => '950.00']);
        $this->assertDatabaseHas('settlement_entries', ['order_id' => $order->id, 'kind' => 'logistics', 'amount' => '85.00']);
        $admin = $this->user('admin');
        $finance->distributeOrder($order->id, $admin->id);
        $this->actingAs($admin)->get(route('admin.commission'))->assertOk()->assertViewHas('commissionTotal', 65.0);
        $sellerEntry = DB::table('settlement_entries')->where('order_id', $order->id)->where('kind', 'seller')->first();
        $finance->markAdminPaid($sellerEntry->id, $admin->id, 'SELLER-ACTUAL-PAYMENT');
        $this->assertDatabaseHas('settlement_entries', ['id' => $sellerEntry->id, 'status' => 'paid']);
        MarketplaceRateSetting::current()->update(['commission_percent' => 20, 'shipping_commission_percent' => 30]);
        $this->assertSame('5.00', (string) $snapshot->fresh()->product_rate_snapshot);
        $this->assertSame(95000, FinanceMoney::minor($snapshot->fresh()->seller_earnings));
        $this->actingAs($order->seller->user)->get(route('seller.reports'))->assertOk()
            ->assertViewHas('commissionAmount', 50.0)->assertViewHas('netEarnings', 950.0);
    }

    public function test_shortage_remains_owed_and_supplemental_batch_preserves_original(): void
    {
        [, , $rider, $operator, $center, $cash] = $this->collected();
        $s = app(CodRemittanceService::class);
        $batch = $s->submit($rider, $center->id, 'short', 'SHORT-001', [['collection_id' => $cash->id, 'amount' => '1050.00']]);
        $verified = $s->verify($operator, $batch->id, [['collection_id' => $cash->id, 'amount' => '1050.00']]);
        $this->assertSame(-5000, $verified->difference_minor);
        $this->assertSame('discrepancy', $cash->fresh()->status);
        $this->assertNull($cash->fresh()->reconciled_at);
        $next = $s->submit($rider, $center->id, 'short-topup', 'TOPUP-001', [['collection_id' => $cash->id, 'amount' => '50.00']]);
        $this->assertSame(5000, $next->expected_minor);
        $s->verify($operator, $next->id, [['collection_id' => $cash->id, 'amount' => '50.00']]);
        $this->assertSame('reconciled', $cash->fresh()->status);
        $this->assertDatabaseHas('cod_remittance_batches', ['id' => $batch->id, 'difference_minor' => -5000]);
        $this->assertDatabaseCount('cash_custody_movements', 2);
    }

    public function test_overage_is_a_discrepancy_and_never_platform_revenue(): void
    {
        [, , $rider, $operator, $center, $cash] = $this->collected();
        $s = app(CodRemittanceService::class);
        $batch = $s->submit($rider, $center->id, 'over', 'OVER-001', [['collection_id' => $cash->id, 'amount' => '1120.00']]);
        $result = $s->verify($operator, $batch->id, [['collection_id' => $cash->id, 'amount' => '1120.00']]);
        $this->assertSame(2000, $result->difference_minor);
        $this->assertSame('discrepancy', $cash->fresh()->status);
        $this->assertDatabaseCount('settlement_entries', 0);
    }

    public function test_idempotent_retry_has_one_posting_and_one_custody_receipt(): void
    {
        [, , $rider, $operator, $center, $cash] = $this->collected();
        $s = app(CodRemittanceService::class);
        $items = [['collection_id' => $cash->id, 'amount' => '1100.00']];
        $first = $s->submit($rider, $center->id, 'retry', 'RETRY-001', $items);
        $again = $s->submit($rider, $center->id, 'retry', 'RETRY-001', $items);
        $this->assertSame($first->id, $again->id);
        $s->verify($operator, $first->id, $items);
        $s->verify($operator, $first->id, $items);
        $this->assertDatabaseCount('cod_remittance_batches', 1);
        $this->assertDatabaseCount('cash_custody_movements', 1);
        $this->assertSame('1100.00', $cash->fresh()->received_amount);
    }

    public function test_second_submission_with_another_key_cannot_reserve_same_collection(): void
    {
        [, , $rider, , $center, $cash] = $this->collected();
        $s = app(CodRemittanceService::class);
        $items = [['collection_id' => $cash->id, 'amount' => '1100.00']];
        $s->submit($rider, $center->id, 'first', 'REC-001', $items);
        $this->expectException(ValidationException::class);
        $s->submit($rider, $center->id, 'second', 'REC-002', $items);
    }

    public function test_multiple_orders_share_one_batch_without_netting_away_discrepancies(): void
    {
        [$order, $delivery, $rider, $operator, $center, $cash] = $this->collected();
        $secondOrder = $order->replicate();
        $secondOrder->save();
        $secondDelivery = $delivery->fresh()->replicate();
        $secondDelivery->order_id = $secondOrder->id;
        $secondDelivery->tracking_code = null;
        $secondDelivery->save();
        $second = $cash->replicate();
        $second->order_id = $secondOrder->id;
        $second->delivery_id = $secondDelivery->id;
        $second->save();
        $s = app(CodRemittanceService::class);
        $items = [['collection_id' => $cash->id, 'amount' => '1050.00'], ['collection_id' => $second->id, 'amount' => '1150.00']];
        $batch = $s->submit($rider, $center->id, 'multi', 'MULTI-001', $items);
        $this->assertSame(220000, $batch->expected_minor);
        $verified = $s->verify($operator, $batch->id, $items);
        $this->assertSame(0, $verified->difference_minor);
        $this->assertSame('discrepancy', $verified->status);
        $this->assertSame('discrepancy', $cash->fresh()->status);
        $this->assertSame('discrepancy', $second->fresh()->status);
        Sanctum::actingAs($rider);
        $this->getJson('/api/mobile/rider/cod')->assertOk()->assertJsonPath('data.summary.outstanding', '50.00');
    }

    public function test_foreign_partner_and_buyer_cannot_verify_and_rider_cannot_self_approve(): void
    {
        [, , $rider, , $center, $cash] = $this->collected();
        $batch = app(CodRemittanceService::class)->submit($rider, $center->id, 'auth', 'AUTH-001', [['collection_id' => $cash->id, 'amount' => '1100.00']]);
        $this->actingAs($rider, 'rider')->post(route('hybrid.verify', $batch->id), ['items' => [['collection_id' => $cash->id, 'amount' => '1100.00']]])->assertForbidden();
        $this->post(route('rider.logout'));
        $other = $this->user('logistics');
        $this->partner($other);
        $this->actingAs($other)->post(route('logistics.hybrid.verify', $batch->id), ['items' => [['collection_id' => $cash->id, 'amount' => '1100.00']]])->assertForbidden();
        $this->actingAs($this->user('buyer'))->get(route('hybrid.index'))->assertForbidden();
        $this->assertDatabaseCount('cash_custody_movements', 0);
    }

    public function test_partner_policy_required_and_overlapping_effective_dates_rejected(): void
    {
        [, $delivery, $rider, $operator] = $this->delivery();
        $delivery->update(['status' => Delivery::DELIVERED, 'proof_path' => 'proof.jpg', 'delivered_at' => now(), 'delivered_rider_id' => $rider->id]);
        app(PartnerCompensationService::class)->delivery($delivery);
        $this->assertDatabaseCount('partner_personnel_earnings', 0);
        $this->policy($operator, $rider);
        $this->expectException(ValidationException::class);
        $this->policy($operator, $rider);
    }

    public function test_fixed_hourly_and_base_incentive_payroll_are_separate_from_cod(): void
    {
        [, , $rider, $operator] = $this->delivery();
        $service = app(PartnerCompensationService::class);
        $period = now()->subMonth()->format('Y-m');
        foreach ([['fixed_salary', '10000.00', 0, '0.00', 1000000], ['hourly', '100.00', 480, '0.00', 80000], ['base_incentive', '10000.00', 0, '1500.00', 1150000]] as [$type,$rate,$minutes,$bonus,$expected]) {
            $policy = $this->policy($operator, $rider, $type, $rate);
            $entry = $service->payroll($operator, $policy->id, $period, $minutes, $bonus, 'Approved contractual payroll');
            $this->assertSame($expected, $entry->amount_minor);
            $again = $service->payroll($operator, $policy->id, $period, $minutes, $bonus, 'Approved contractual payroll');
            $this->assertSame($entry->id, $again->id);
        }
        $this->assertDatabaseCount('cod_settlements', 0);
        $this->assertDatabaseCount('settlement_entries', 0);
    }

    public function test_paid_requires_approval_and_foreign_policy_cannot_be_created(): void
    {
        [, $delivery, $rider, $operator] = $this->delivery();
        $this->policy($operator, $rider);
        $delivery->update(['status' => Delivery::DELIVERED, 'proof_path' => 'proof.jpg', 'delivered_at' => now(), 'delivered_rider_id' => $rider->id]);
        $service = app(PartnerCompensationService::class);
        $service->delivery($delivery);
        $id = DB::table('partner_personnel_earnings')->value('id');
        try {
            $service->paid($operator, $id, 'BANK-001');
            $this->fail('Unapproved payment accepted');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('partner_personnel_earnings', ['id' => $id, 'status' => 'pending']);
        $foreign = $this->user('logistics');
        $this->partner($foreign);
        $this->actingAs($foreign)->post(route('logistics.hybrid.approve', $id))->assertNotFound();
    }

    public function test_web_workspaces_render_for_admin_partner_branch_and_rider(): void
    {
        [, , $rider, $operator, $center] = $this->collected();
        $this->get(route('hybrid.index'))->assertOk()->assertSee('Submit a cash handover');
        $this->post(route('rider.logout'));
        $this->actingAs($operator)->get(route('logistics.hybrid'))->assertOk()->assertSee('Partner compensation policies');
        $branch = $this->user('branch');
        $center->update(['user_id' => $branch->id]);
        $this->actingAs($branch)->get(route('logistics.hybrid'))->assertOk()->assertDontSee('Create policy');
        $this->actingAs($this->user('admin'))->get(route('hybrid.index'))->assertOk();
    }

    public function test_partner_earnings_approval_endpoint_does_not_accept_rider_identity(): void
    {
        [, , $rider] = $this->delivery();
        $this->actingAs($rider, 'rider')->post(route('hybrid.approve', 1))->assertForbidden();
        $this->post(route('hybrid.policy'), [])->assertForbidden();
    }

    public function test_decimal_money_uses_half_up_rounding_without_floating_postings(): void
    {
        $this->assertSame(1, FinanceMoney::percent(10, '5.00'));
        $this->assertSame(5000, FinanceMoney::percent(100000, '5.00'));
        $this->assertSame('-50.01', FinanceMoney::decimal(-5001));
        $this->expectException(ValidationException::class);
        FinanceMoney::minor('25.001');
    }

    public function test_prospective_platform_rule_preserves_prior_rates_and_snapshots_hold(): void
    {
        [$order] = $this->delivery();
        $order->update(['total_amount' => '1100.00', 'shipping_fee' => '100.00', 'cod_fee' => '0.00']);
        $service = app(PlatformFinancialPolicyService::class);
        $admin = $this->user('admin');
        $effective = now()->addDay()->startOfMinute();
        $service->schedule($admin, ['product_percent' => '5.00', 'shipping_percent' => '15.00', 'seller_hold_days' => 7, 'effective_at' => $effective->format('Y-m-d H:i:s')]);
        $this->assertSame('10.00', (string) $service->active()['product_percent']);
        $this->travelTo($effective->copy()->addSecond());
        app(ShopHopFinance::class)->snapshot($order, 1000, 0, 0, 0, 0);
        $financial = OrderFinancial::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('5.00', $financial->product_rate_snapshot);
        $this->assertSame(7, $financial->seller_hold_days);
        $order->update(['status' => Order::STATUS_TO_RECEIVE]);
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $delivery->update(['status' => Delivery::DELIVERED, 'delivered_at' => now()]);
        $this->assertFalse($service->eligible($order, $financial));
        $this->travel(7)->days();
        $this->assertTrue($service->eligible($order->fresh(), $financial));
        $this->travelBack();
    }

    public function test_pickup_compensation_does_not_depend_on_seller_confirmation_or_settlement_hold(): void
    {
        [$order, $delivery, $rider, $operator] = $this->delivery();
        $policy = $this->policy($operator, $rider, 'per_pickup', '10.00');
        $center = SortingCenter::create(['logistics_partner_id' => $rider->logistics_partner_id,
            'name' => 'Pickup branch', 'code' => 'PICKUP', 'type' => 'station_hub', 'status' => 'active',
            'region' => 'IV-A', 'province' => 'Cavite', 'municipality' => 'Imus', 'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1']);
        $delivery->update(['status' => Delivery::AT_SORTING_CENTER, 'pickup_rider_id' => $rider->id, 'origin_sorting_center_id' => $center->id]);
        app(PartnerCompensationService::class)->pickup($delivery, $center->id);
        app(PartnerCompensationService::class)->pickup($delivery, $center->id);
        $this->assertDatabaseCount('partner_personnel_earnings', 1);
        $this->assertDatabaseHas('partner_personnel_earnings', ['policy_id' => $policy->id, 'amount_minor' => 1000]);
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->assertDatabaseCount('settlement_entries', 0);
    }

    public function test_per_trip_earnings_are_based_on_verified_trip_and_partner_policy(): void
    {
        [, , $rider, $operator, $center] = $this->collected();
        $driver = LinehaulDriver::create(['logistics_partner_id' => $rider->logistics_partner_id,
            'name' => 'Driver', 'email' => 'driver@example.test', 'password' => 'LongSecretPassword123!', 'status' => 'active']);
        $vehicle = FleetVehicle::create(['logistics_partner_id' => $rider->logistics_partner_id,
            'home_hub_id' => $center->id, 'code' => 'TEST-VAN', 'plate_number' => 'XYZ123', 'vehicle_type' => 'van', 'status' => 'available']);
        $trip = LinehaulTrip::create(['logistics_partner_id' => $rider->logistics_partner_id,
            'driver_id' => $driver->id, 'vehicle_id' => $vehicle->id, 'from_sorting_center_id' => $center->id,
            'to_sorting_center_id' => $center->id, 'code' => 'TEST-TRIP', 'status' => 'scheduled']);
        $service = app(PartnerCompensationService::class);
        $service->createPolicy($operator, ['personnel_type' => 'linehaul_driver', 'personnel_id' => $driver->id,
            'type' => 'per_trip', 'rate' => '200.00', 'schedule' => 'Weekly', 'effective_from' => now()->toDateString(), 'effective_until' => null]);
        $service->trip($trip);
        $this->assertDatabaseCount('partner_personnel_earnings', 0);
        $trip->update(['status' => LinehaulTrip::COMPLETED, 'completed_at' => now()]);
        $service->trip($trip);
        $service->trip($trip);
        $this->assertDatabaseCount('partner_personnel_earnings', 1);
        $this->assertDatabaseHas('partner_personnel_earnings', ['personnel_type' => 'linehaul_driver', 'amount_minor' => 20000]);
    }

    public function test_branch_verification_and_mobile_contract_preserve_outstanding_cash(): void
    {
        [, , $rider, , $center, $cash] = $this->collected();
        $branch = $this->user('branch');
        $center->update(['user_id' => $branch->id]);
        $service = app(CodRemittanceService::class);
        $batch = $service->submit($rider, $center->id, 'branch-cash', 'BRANCH-REC', [['collection_id' => $cash->id, 'amount' => '1050.00']]);
        $service->verify($branch, $batch->id, [['collection_id' => $cash->id, 'amount' => '1050.00']]);
        Sanctum::actingAs($rider);
        $this->getJson('/api/mobile/rider/cod')->assertOk()->assertJsonPath('data.collections.0.outstanding', '50.00')
            ->assertJsonPath('data.batches.0.difference_minor', -5000)->assertJsonPath('data.collections.0.can_remit', true);
    }

    public function test_cancelled_unconfirmed_and_disputed_orders_cannot_release_settlements(): void
    {
        [$order, , , , , $cash] = $this->collected();
        $finance = app(ShopHopFinance::class);
        $finance->snapshot($order, 1000, 0, 0, 0, 0);
        foreach ([Order::STATUS_TO_RECEIVE, Order::STATUS_CANCELLED, Order::STATUS_RETURNED] as $state) {
            $order->update(['status' => $state]);
            try {
                $finance->prepareForOrder($order->id);
                $this->fail('Ineligible order accepted');
            } catch (ValidationException) {
            }
        }
        $order->update(['status' => Order::STATUS_COMPLETED]);
        OrderFinancial::where('order_id', $order->id)->update(['settlement_hold' => true, 'hold_reason' => 'Refund review']);
        try {
            $finance->prepareForOrder($order->id);
            $this->fail('Held order accepted');
        } catch (ValidationException) {
        }
        $this->assertDatabaseCount('settlement_entries', 0);
    }

    public function test_partner_operating_expense_is_isolated_and_never_changes_cod(): void
    {
        [, , , $operator, , $cash] = $this->collected();
        $this->post(route('rider.logout'));
        $this->actingAs($operator)->post(route('logistics.hybrid.expense'), [
            'category' => 'fuel', 'amount' => '500.00', 'incurred_on' => now()->toDateString(),
            'evidence_reference' => 'FUEL-RECEIPT-001', 'payment_reference' => 'BANK-EXPENSE-001',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('partner_operating_expenses', ['amount_minor' => 50000, 'category' => 'fuel']);
        $this->assertSame('1100.00', $cash->fresh()->collected_amount);
        $other = $this->user('logistics');
        $this->partner($other);
        $this->actingAs($other)->get(route('logistics.hybrid'))->assertOk()->assertDontSee('FUEL-RECEIPT-001');
    }

    public function test_legacy_personnel_obligation_can_be_approved_and_paid_before_platform_settlement(): void
    {
        [$order, , $rider, $operator] = $this->delivery();
        $entry = SettlementEntry::create(['order_id' => $order->id, 'kind' => 'delivery_rider',
            'payee_id' => $rider->id, 'amount' => '25.00', 'status' => 'pending']);
        $service = app(ShopHopFinance::class);
        $service->approveLegacyRider($entry->id, $rider->logistics_partner_id, $operator->id);
        $service->markRiderPaid($entry->id, $rider->logistics_partner_id, $operator->id, 'PARTNER-OWN-FUNDS');
        $this->assertSame('paid', $entry->fresh()->status);
        $this->assertSame(Order::STATUS_TO_RECEIVE, $order->fresh()->status);
        $this->assertDatabaseMissing('settlement_entries', ['order_id' => $order->id, 'kind' => 'logistics']);
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
            'delivery_rider_id' => $rider->id, 'status' => Delivery::OUT_FOR_DELIVERY,
            'assigned_at' => now(), 'picked_up_at' => now(), 'delivery_assigned_at' => now(),
            'out_for_delivery_at' => now(), 'in_transit_at' => now(), 'transit_rider_id' => $rider->id,
            'delivery_attempts' => 1,
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
        $user = new User;
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
