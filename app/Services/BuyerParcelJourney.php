<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Buyer-only, read-only projection of persisted parcel actions. */
class BuyerParcelJourney
{
    private array $scans = [];
    private array $assignments = [];

    public function load(Collection $orders): void
    {
        $this->scans = $this->assignments = [];
        $relations = ['delivery.partner'];
        foreach ([
            'sorting_centers' => ['delivery.originSortingCenter'],
            'parcel_transfers' => ['delivery.transfers.fromCenter', 'delivery.transfers.toCenter'],
            'delivery_failure_reports' => ['delivery.failureReports'],
            'pickup_requests' => ['pickupRequest'],
        ] as $table => $names) {
            if (Schema::hasTable($table)) {
                $relations = array_merge($relations, $names);
            }
        }
        $orders->loadMissing($relations);
        $ids = $orders->pluck('delivery.id')->filter()->all();
        if (! $ids) {
            return;
        }
        if (Schema::hasTable('parcel_scan_events')) {
            $this->scans = DB::table('parcel_scan_events as e')
                ->leftJoin('sorting_centers as c', 'c.id', '=', 'e.sorting_center_id')
                ->whereIn('e.delivery_id', $ids)->select('e.*', 'c.name as location')
                ->orderBy('e.id')->get()->groupBy('delivery_id')->all();
        }
        if (Schema::hasTable('rider_batch_items')) {
            $this->assignments = DB::table('rider_batch_items as i')
                ->join('rider_batches as b', 'b.id', '=', 'i.rider_batch_id')
                ->leftJoin('riders as r', 'r.id', '=', 'b.rider_id')
                ->whereIn('i.delivery_id', $ids)
                ->select('i.delivery_id', 'i.created_at', 'i.pickup_failed_at', 'b.type', 'r.name as rider_name')
                ->orderBy('i.id')->get()->groupBy('delivery_id')->all();
        }
    }

    public static function timestamp(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)->setTimezone('Asia/Manila');
        }
        // Laravel writes SQL action times with now() in config/app.php's UTC timezone.
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $value)) {
            return null;
        }
        try {
            $at = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $value, config('app.timezone', 'UTC'));
            return $at && $at->format('Y-m-d H:i:s') === $value ? $at->setTimezone('Asia/Manila') : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function displayTime(mixed $value): string
    {
        $at = self::timestamp($value);
        return $at ? $at->format('M j, Y • g:i A').' PHT' : 'Time not recorded';
    }

    public function events(Order $order): array
    {
        $events = [];
        $delivery = $order->delivery;
        $provider = $delivery?->partner?->company_name;
        $tracking = $delivery?->tracking_code;
        $add = static function ($raw, string $key, string $title, string $description,
            ?string $location = null, string $icon = 'package', bool $undated = false) use (&$events, $provider, $tracking): void {
            $at = self::timestamp($raw);
            if (! $at && ! $undated) {
                return;
            }
            $events[$key.'|'.($at?->getTimestamp() ?? 'unknown')] = compact('title', 'description', 'location', 'icon', 'provider', 'tracking') + [
                'at' => $at?->toIso8601String(), 'sort' => $at?->getTimestamp(), 'latest' => false,
                'timestamp' => $at ? $at->format('M j, Y • g:i A').' PHT' : 'Time not recorded',
            ];
        };
        $raw = static fn ($model, $field) => $model?->getRawOriginal($field);
        $add($raw($order, 'created_at'), 'placed', 'Order placed', 'Your order was placed and is awaiting Seller confirmation.', icon: 'shopping-bag', undated: true);
        if ($order->relationLoaded('pickupRequest') && $order->pickupRequest) {
            $add($raw($order->pickupRequest, 'requested_at'), 'pickup_requested', 'Pickup requested', 'The Seller requested collection of your parcel.');
        }
        if ($delivery) {
            $scans = collect($this->scans[$delivery->id] ?? [])->filter(static fn ($scan) => self::timestamp($scan->scanned_at));
            $types = $scans->pluck('event_type')->all();
            $batches = collect($this->assignments[$delivery->id] ?? []);
            foreach ($batches as $batch) {
                $pickup = $batch->type === 'pickup';
                $add($batch->created_at, $pickup ? 'pickup_assigned' : 'delivery_assigned',
                    $pickup ? 'Pickup Rider assigned' : 'Delivery Rider assigned',
                    ($batch->rider_name ?: 'A Rider').' was assigned '.($pickup ? 'to collect your parcel from the Seller.' : 'for delivery to your address.'), icon: 'user');
                $add($batch->pickup_failed_at, 'pickup_failed', 'Pickup attempt unsuccessful', 'The Rider could not collect your parcel. Awaiting a new pickup arrangement.', icon: 'circle-alert');
            }
            if (! $batches->contains('type', 'pickup')) {
                $add($raw($delivery, 'assigned_at'), 'pickup_assigned', 'Pickup Rider assigned', 'A Rider was assigned to collect your parcel from the Seller.', icon: 'user');
            }
            $add($raw($delivery, 'pickup_accepted_at'), 'pickup_accepted', 'Pickup accepted', 'The Rider accepted the pickup assignment.', icon: 'user');
            if (! in_array('seller_handoff', $types, true)) {
                $add($raw($delivery, 'picked_up_at'), 'picked_up', 'Parcel picked up', 'The Rider collected your parcel from the Seller.', icon: 'truck');
            }
            if (! in_array('pickup_received', $types, true)) {
                $origin = $delivery->relationLoaded('originSortingCenter') ? $delivery->originSortingCenter?->name : null;
                $add($raw($delivery, 'at_sorting_center_at'), 'origin_received', 'Received at origin branch', 'Your parcel was scanned and received at the origin sorting facility.', $origin, 'building-2');
            }
            if (! in_array('hub_sorted', $types, true)) {
                $add($raw($delivery, 'sorted_at'), 'sorted', 'Parcel sorted', 'Your parcel was sorted for its next logistics stage.');
            }
            if ($delivery->relationLoaded('transfers')) {
                foreach ($delivery->transfers as $transfer) {
                    $leg = $scans->where('parcel_transfer_id', $transfer->id);
                    if ($leg->whereIn('event_type', ['linehaul_departed', 'cargo_departed'])->isEmpty()) {
                        $add($raw($transfer, 'departed_at') ?: $raw($transfer, 'dispatched_at'), 'departed:'.$transfer->id,
                            'In transit to next facility', 'Your parcel was dispatched to the next logistics facility.', $transfer->fromCenter?->name, 'truck');
                    }
                    if ($leg->where('event_type', 'transfer_received')->isEmpty()) {
                        $add($raw($transfer, 'received_at'), 'received:'.$transfer->id, 'Received at next facility',
                            'Your parcel was scanned and received at the receiving facility.', $transfer->toCenter?->name, 'building-2');
                    }
                }
            }
            $labels = [
                'seller_handoff' => ['picked_up', 'Parcel picked up', 'The Rider scanned and collected your parcel from the Seller.', 'truck'],
                'pickup_received' => ['origin_received', 'Received at origin branch', 'Your parcel was scanned and received at the origin sorting facility.', 'building-2'],
                'hub_sorted' => ['sorted', 'Parcel sorted at hub', 'Your parcel was scanned and sorted at this hub.', 'package'],
                'manifest_loaded' => ['loaded', 'Loaded for transport', 'Your parcel was verified as loaded for transport. Departure is recorded separately.', 'package'],
                'linehaul_departed' => ['departed', 'In transit to next facility', 'Transport carrying your parcel departed for the next logistics facility.', 'truck'],
                'cargo_departed' => ['departed', 'Cargo transport departed', 'Cargo transport carrying your parcel departed for the next facility.', 'truck'],
                'manifest_arrived' => ['arrived', 'Transport arrived at receiving facility', 'Transport arrived at this facility. Parcel receipt is recorded separately.', 'building-2'],
                'cargo_arrived' => ['arrived', 'Cargo arrived at receiving facility', 'Cargo transport arrived. Parcel receipt is recorded separately.', 'building-2'],
                'transfer_received' => ['received', 'Parcel received at next facility', 'Your parcel was scanned and received at this facility.', 'building-2'],
                'delivery_rider_handoff' => ['out_for_delivery', 'Out for delivery', 'The delivery Rider scanned and collected your parcel for delivery.', 'truck'],
                'failed_delivery_received' => ['failed_received', 'Parcel returned to branch after delivery attempt', 'The branch received your parcel after an unsuccessful delivery attempt.', 'building-2'],
            ];
            foreach ($scans as $scan) {
                if (! isset($labels[$scan->event_type])) {
                    continue;
                }
                [$key, $title, $description, $icon] = $labels[$scan->event_type];
                if ($scan->event_type === 'transfer_received' && $delivery->destination_sorting_center_id
                    && (int) $scan->sorting_center_id === (int) $delivery->destination_sorting_center_id) {
                    $title = 'Received at destination branch';
                }
                // Seller custody scan identifies the responsible branch, not a physical pickup location.
                $location = $scan->event_type === 'seller_handoff' ? null : $scan->location;
                $add($scan->scanned_at, $key.':'.($scan->parcel_transfer_id ?? '').':'.($scan->sorting_center_id ?? ''), $title, $description, $location, $icon);
            }
            if (! $batches->contains('type', 'delivery')) {
                $add($raw($delivery, 'delivery_assigned_at'), 'delivery_assigned', 'Delivery Rider assigned', 'A Rider was assigned for delivery to your address.', icon: 'user');
            }
            if (! in_array('delivery_rider_handoff', $types, true)) {
                $add($raw($delivery, 'out_for_delivery_at'), 'out_for_delivery', 'Out for delivery', 'Your parcel is out for delivery.', icon: 'truck');
            }
            if ($delivery->relationLoaded('failureReports') && $delivery->failureReports->isNotEmpty()) {
                foreach ($delivery->failureReports as $report) {
                    $add($raw($report, 'reported_at'), 'failed:'.$report->attempt_no, 'Delivery attempt '.$report->attempt_no.' unsuccessful', 'The delivery attempt was unsuccessful. Awaiting a retry or further instructions from Logistics.', icon: 'circle-alert');
                }
            } else {
                $add($raw($delivery, 'delivery_failed_at'), 'failed', 'Delivery attempt unsuccessful', 'The delivery attempt was unsuccessful. Awaiting further instructions from Logistics.', icon: 'circle-alert');
            }
            $add($raw($delivery, 'delivered_at'), 'delivered', 'Delivered', 'Successful delivery was recorded. Confirm Order Received to complete the transaction.', icon: 'check');
            $add($raw($delivery, 'returned_at'), 'returned', 'Returned to Seller', 'Your parcel was recorded as returned to the Seller.', icon: 'undo-2');
        }
        if ($order->status === Order::STATUS_COMPLETED) {
            $add(null, 'completed', 'Order received / completed', 'The order is completed. The receipt confirmation timestamp was not recorded.', icon: 'check', undated: true);
        } elseif ($order->status === Order::STATUS_CANCELLED) {
            $add(null, 'cancelled', 'Order cancelled', 'This order was cancelled. The cancellation timestamp was not recorded.', icon: 'x', undated: true);
        }
        $events = array_values($events);
        usort($events, static fn ($a, $b) => ($a['sort'] ?? PHP_INT_MAX) <=> ($b['sort'] ?? PHP_INT_MAX));
        for ($i = count($events) - 1; $i >= 0; $i--) {
            if ($events[$i]['at']) {
                $events[$i]['latest'] = true;
                break;
            }
        }
        return $events;
    }

    public function progress(Order $order): array
    {
        $delivery = $order->delivery;
        $steps = $order->progressSteps();
        foreach ($steps as &$step) {
            $step['done'] = match ($step['label']) {
                'Seller Confirmed' => $order->status === Order::STATUS_CONFIRMED,
                'Preparing' => in_array($order->status, [Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP], true),
                'Ready for Pickup' => $order->status === Order::STATUS_READY_FOR_PICKUP
                    || ($order->relationLoaded('pickupRequest') && $order->pickupRequest !== null),
                'In Transit' => collect($this->events($order))->contains(static fn ($event) => $event['at'] && in_array($event['icon'], ['truck', 'building-2', 'check'], true)),
                'Delivered' => (bool) self::timestamp($delivery?->getRawOriginal('delivered_at')) || $delivery?->status === Delivery::DELIVERED,
                default => $step['done'],
            };
            $step['active'] = false;
        }
        unset($step);
        if ($order->status !== Order::STATUS_CANCELLED && ($order->status === Order::STATUS_RETURNED
            || in_array($delivery?->status, [Delivery::DELIVERY_FAILED, Delivery::RETURN_REQUESTED, Delivery::RETURN_AT_BRANCH,
                Delivery::RETURN_ASSIGNED, Delivery::RETURN_IN_TRANSIT, Delivery::RETURN_AWAITING_SELLER, Delivery::RETURNED], true))) {
            $steps[] = ['label' => $delivery?->statusLabel() ?? 'Returned to Seller', 'done' => true, 'active' => false];
        }
        for ($i = count($steps) - 1; $i >= 0; $i--) {
            if ($steps[$i]['done']) {
                $steps[$i]['active'] = true;
                break;
            }
        }
        return $steps;
    }
}
