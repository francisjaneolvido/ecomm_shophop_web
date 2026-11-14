<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use App\Services\BuyerParcelJourney;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/** Read-only fixtures; these tests do not connect to an existing database. */
class BuyerParcelJourneyTest extends TestCase
{
    public function test_utc_times_convert_once_and_invalid_dates_never_become_now(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('Nov 12, 2026 • 9:46 AM PHT', BuyerParcelJourney::displayTime('2026-11-12 01:46:00'));
        $this->assertSame('Nov 12, 2026 • 9:46 AM PHT', BuyerParcelJourney::displayTime(CarbonImmutable::parse('2026-11-12T09:46:00+08:00')));
        $this->assertSame('Nov 13, 2026 • 1:00 AM PHT', BuyerParcelJourney::displayTime('2026-11-12 17:00:00'));
        foreach ([null, '', 'now', 'invalid', '0000-00-00 00:00:00', '2026-02-30 09:00:00'] as $bad) {
            $this->assertSame('Time not recorded', BuyerParcelJourney::displayTime($bad));
        }
    }

    public function test_events_sort_by_action_time_without_generic_location_or_updated_at(): void
    {
        [$order] = $this->fixture(['assigned_at' => '2026-11-12 01:46:00', 'picked_up_at' => '2026-11-12 01:30:00', 'sorted_at' => 'invalid', 'updated_at' => '2026-12-31 00:00:00']);
        $events = (new BuyerParcelJourney)->events($order);
        $this->assertSame(['Order placed', 'Parcel picked up', 'Pickup Rider assigned'], array_column($events, 'title'));
        $this->assertSame([null, null, null], array_column($events, 'location'));
        $this->assertSame('Nov 12, 2026 • 9:30 AM PHT', $events[1]['timestamp']);
        $this->assertTrue($events[2]['latest']);
    }

    public function test_scans_replace_milestones_and_preserve_distinct_arrival_and_receipt(): void
    {
        [$order, $delivery] = $this->fixture(['picked_up_at' => '2026-11-12 01:46:00', 'at_sorting_center_at' => '2026-11-12 02:30:00', 'out_for_delivery_at' => '2026-11-12 05:00:00', 'destination_sorting_center_id' => 22]);
        $service = new BuyerParcelJourney;
        $scan = static fn ($type, $at, $branch = 11, $transfer = null) => (object) [
            'event_type' => $type, 'scanned_at' => $at, 'sorting_center_id' => $branch,
            'parcel_transfer_id' => $transfer, 'location' => $branch === 11 ? 'Origin Branch' : 'Destination Branch',
        ];
        (new \ReflectionProperty($service, 'scans'))->setValue($service, [10 => collect([
            $scan('seller_handoff', '2026-11-12 01:46:00'),
            $scan('pickup_received', '2026-11-12 02:30:00'),
            $scan('pickup_received', '2026-11-12 02:30:00'),
            $scan('linehaul_departed', '2026-11-12 03:00:00', 11, 8),
            $scan('manifest_arrived', '2026-11-12 04:00:00', 22, 8),
            $scan('transfer_received', '2026-11-12 04:15:00', 22, 8),
            $scan('delivery_rider_handoff', '2026-11-12 05:00:00', 22),
            $scan('internal_security_check', '2026-11-12 05:05:00'),
        ])]);
        $transfer = new ParcelTransfer;
        $transfer->setRawAttributes(['id' => 8, 'dispatched_at' => '2026-11-12 03:00:00', 'received_at' => '2026-11-12 04:15:00'], true);
        $transfer->setRelation('fromCenter', new SortingCenter(['name' => 'Origin Branch']));
        $transfer->setRelation('toCenter', new SortingCenter(['name' => 'Destination Branch']));
        $delivery->setRelation('transfers', new Collection([$transfer]));
        $events = $service->events($order);
        $this->assertCount(7, $events);
        $this->assertNull($events[1]['location']);
        $this->assertSame('Origin Branch', $events[2]['location']);
        $this->assertSame('Transport arrived at receiving facility', $events[4]['title']);
        $this->assertSame('Received at destination branch', $events[5]['title']);
        $this->assertSame('Out for delivery', $events[6]['title']);
    }

    public function test_assignment_history_keeps_original_and_replacement_handlers(): void
    {
        [$order] = $this->fixture(['assigned_at' => '2026-11-12 02:00:00']);
        $service = new BuyerParcelJourney;
        (new \ReflectionProperty($service, 'assignments'))->setValue($service, [10 => collect([
            (object) ['type' => 'pickup', 'created_at' => '2026-11-12 01:00:00', 'rider_name' => 'Original Rider', 'pickup_failed_at' => null],
            (object) ['type' => 'pickup', 'created_at' => '2026-11-12 02:00:00', 'rider_name' => 'Replacement Rider', 'pickup_failed_at' => null],
        ])]);
        $events = $service->events($order);
        $this->assertCount(3, $events);
        $this->assertStringContainsString('Original Rider', $events[1]['description']);
        $this->assertStringContainsString('Replacement Rider', $events[2]['description']);
    }

    public function test_completed_does_not_invent_delivery_or_receipt_timestamps(): void
    {
        [$order, $delivery] = $this->fixture(['status' => Delivery::DELIVERED, 'delivered_at' => '2026-11-12 06:00:00']);
        $service = new BuyerParcelJourney;
        $progress = collect($service->progress($order))->keyBy('label');
        $this->assertTrue($progress['Delivered']['done']);
        $this->assertFalse($progress['Completed']['done']);
        $order->status = Order::STATUS_COMPLETED;
        $events = $service->events($order);
        $this->assertNull(end($events)['at']);
        $this->assertFalse(end($events)['latest']);
        $this->assertTrue($events[count($events) - 2]['latest']);
        $delivery->setRawAttributes(['id' => 10, 'status' => Delivery::PICKUP_ASSIGNED], true);
        $progress = collect($service->progress($order))->keyBy('label');
        $this->assertFalse($progress['Delivered']['done']);
        $this->assertFalse($progress['In Transit']['done']);
        $this->assertFalse($progress['Seller Confirmed']['done']);
    }

    public function test_missing_history_failure_status_and_blade_rendering(): void
    {
        [$order] = $this->fixture(['status' => Delivery::DELIVERY_FAILED, 'delivery_failed_at' => '2026-11-12 04:00:00']);
        $service = new BuyerParcelJourney;
        $steps = $service->progress($order);
        $this->assertSame('Delivery Failed / Reported', end($steps)['label']);
        $this->assertTrue(end($steps)['active']);
        $html = view('buyer.orders.partials.parcel-journey', ['order' => [
            'tracking_no' => 'PARCEL-REAL', 'status_label' => 'Delivery Failed', 'status_note' => 'Awaiting retry.',
            'status' => $order->status, 'tracking_events' => $service->events($order),
        ]])->render();
        $this->assertStringContainsString('data-copy-tracking="PARCEL-REAL"', $html);
        $this->assertStringContainsString('2026-11-12T12:00:00+08:00', $html);
        $this->assertStringContainsString('Latest recorded event', $html);
        $this->assertStringNotContainsString('ShopHop Logistics', $html);
        $order->setRelation('delivery', null);
        $this->assertCount(1, $service->events($order));
    }

    private function fixture(array $attributes = []): array
    {
        $order = new Order;
        $order->setRawAttributes(['id' => 1, 'created_at' => '2026-11-11 23:00:00', 'status' => Order::STATUS_TO_RECEIVE, 'payment_method' => 'cod'], true);
        $delivery = new Delivery;
        $delivery->setRawAttributes($attributes + ['id' => 10, 'tracking_code' => 'PARCEL-REAL', 'status' => Delivery::PICKUP_ASSIGNED], true);
        foreach (['partner', 'rider', 'originSortingCenter'] as $relation) {
            $delivery->setRelation($relation, null);
        }
        $delivery->setRelation('transfers', new Collection);
        $delivery->setRelation('failureReports', new Collection);
        $order->setRelation('delivery', $delivery);
        $order->setRelation('pickupRequest', null);
        return [$order, $delivery];
    }
}
