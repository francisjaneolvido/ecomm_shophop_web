<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\RiderBatchItem;
use App\Models\Logistics\RiderBatchVehicleProfile;
use App\Models\Logistics\SortingCenter;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Local pickup/delivery orchestration. Delivery/QR/POD/COD remain authoritative. */
class RiderBatchService
{
    public function available(): bool
    {
        foreach (['rider_batch_vehicle_profiles', 'rider_batches', 'rider_batch_items', 'rider_batch_events'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['batch' => $message]);
    }

    private function event(RiderBatch $batch, string $event, string $actorType, int $actorId, array $details = []): void
    {
        DB::table('rider_batch_events')->insert(['rider_batch_id' => $batch->id, 'event' => $event,
            'actor_type' => $actorType, 'actor_id' => $actorId, 'details' => json_encode($details, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }

    private function branch(SortingCenter $branch): void
    {
        $branch->refresh()->load('partner.user');
        if ($branch->type !== 'station_hub' || $branch->is_main || $branch->status !== 'active' || $branch->archived_at
            || $branch->partner?->user?->status !== 'approved') {
            $this->fail('An active station/branch of an approved company is required.');
        }
    }

    private function rider(SortingCenter $branch, int $id, bool $requireActive = true): Rider
    {
        $rider = Rider::where('logistics_partner_id', $branch->logistics_partner_id)->where('sorting_center_id', $branch->id)
            ->whereKey($id)->lockForUpdate()->firstOrFail();
        if ($requireActive && (! $rider->canOperate() || ! $rider->email_verified_at || ! $rider->password
            || ($rider->operational_status !== null && $rider->operational_status !== 'active')
            || ($rider->application_status !== null && $rider->application_status !== 'approved'))) {
            $this->fail('Only activated, verified riders belonging to this branch can receive batches.');
        }

        return $rider;
    }

    private function profile(Rider $rider): RiderBatchVehicleProfile
    {
        $profile = RiderBatchVehicleProfile::where('rider_id', $rider->id)->first();
        if (! $profile || $profile->vehicle_type !== $rider->vehicle_type || $profile->plate_number !== $rider->plate_number) {
            $this->fail('The Branch must configure capacity for the rider current vehicle first.');
        }

        return $profile;
    }

    public function configure(SortingCenter $branch, int $riderId, array $limits, string $actorType, int $actorId): void
    {
        DB::transaction(function () use ($branch, $riderId, $limits, $actorType, $actorId) {
            $this->branch($branch);
            $rider = $this->rider($branch, $riderId);
            if (RiderBatch::where('rider_id', $rider->id)->whereIn('status', ['queued', 'in_progress'])->exists()) {
                $this->fail('Close or cancel outstanding batches before changing vehicle capacity.');
            }
            foreach (['max_weight_kg', 'max_volume_m3', 'max_stops', 'max_duration_minutes'] as $key) {
                if (! isset($limits[$key]) || ! is_numeric($limits[$key]) || ! is_finite((float) $limits[$key]) || (float) $limits[$key] <= 0) {
                    $this->fail('Configure positive weight, volume, stop and duration limits.');
                }
            }
            if (isset($limits['max_parcels']) && (int) $limits['max_parcels'] < 1) {
                $this->fail('A configured parcel limit must be positive.');
            }
            RiderBatchVehicleProfile::updateOrCreate(['rider_id' => $rider->id], [
                'vehicle_type' => $rider->vehicle_type, 'plate_number' => $rider->plate_number, 'limits' => $limits,
                'configured_by_user_id' => $actorType === 'user' ? $actorId : null, 'configured_by_staff_id' => $actorType === 'staff' ? $actorId : null]);
        }, 3);
    }

    private function capacity(array $limits, array $totals): void
    {
        foreach (['weight_kg' => 'max_weight_kg', 'volume_m3' => 'max_volume_m3', 'stops' => 'max_stops', 'minutes' => 'max_duration_minutes', 'parcels' => 'max_parcels'] as $value => $limit) {
            if (isset($limits[$limit]) && $totals[$value] > (float) $limits[$limit] + 0.0000001) {
                $this->fail('Batch exceeds configured '.$limit.'. Adjust the route or use an appropriate rider vehicle.');
            }
        }
    }

    public function create(SortingCenter $branch, array $data, string $actorType, int $actorId): RiderBatch
    {
        abort_unless($this->available(), 503, 'Batch migration has not been deployed.');
        try {
            return DB::transaction(function () use ($branch, $data, $actorType, $actorId) {
                $this->branch($branch);
                $rider = $this->rider($branch, (int) $data['rider_id']);
                $profile = $this->profile($rider);
                $start = CarbonImmutable::parse($data['scheduled_start'])->utc();
                $end = CarbonImmutable::parse($data['scheduled_end'])->utc();
                app(RiderManagementService::class)->assertSchedule($rider, $start, $end);
                $minutes = (int) $data['estimated_minutes'];
                $type = $data['type'];
                if (! in_array($type, ['pickup', 'delivery'], true) || $end <= $start || $end <= now() || $minutes < 1 || $start->diffInMinutes($end) < $minutes) {
                    $this->fail('Choose a valid separate pickup/delivery route and a sufficient future schedule window.');
                }
                if (ReturnCase::where('return_rider_id', $rider->id)
                    ->whereIn('status', ['assigned', 'in_transit', 'awaiting_seller'])->exists()) {
                    $this->fail('Resolve the rider separate return custody before planning another route.');
                }
                $outstanding = RiderBatch::where('rider_id', $rider->id)->whereIn('status', ['queued', 'in_progress']);
                if ((clone $outstanding)->where('scheduled_start', '<', $end)->where('scheduled_end', '>', $start)->exists()) {
                    $this->fail('This schedule overlaps another queued or active rider batch.');
                }
                if (! in_array($rider->availability_status, ['available', 'online'], true)
                    && ! ($rider->availability_status === 'busy' && (clone $outstanding)->where('status', 'in_progress')->exists())) {
                    $this->fail('This rider is offline, on break or otherwise unavailable.');
                }
                $parcels = $data['parcels'] ?? [];
                $ids = array_map(fn ($p) => (int) $p['order_id'], $parcels);
                if (! $ids || count($ids) !== count(array_unique($ids))) {
                    $this->fail('Select distinct parcels.');
                }
                // Lock in canonical ID order; preserve the submitted stop ordering separately.
                $orders = Order::with(['seller', 'pickupRequest', 'financial'])->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                if ($orders->count() !== count($ids)) {
                    $this->fail('A selected parcel is unavailable.');
                }
                $batch = RiderBatch::create(['code' => 'RB-'.strtoupper(Str::ulid()), 'logistics_partner_id' => $branch->logistics_partner_id,
                    'sorting_center_id' => $branch->id, 'rider_id' => $rider->id, 'type' => $type, 'status' => 'queued',
                    'scheduled_start' => $start, 'scheduled_end' => $end, 'estimated_minutes' => $minutes, 'capacity_snapshot' => []]);
                $stops = [];
                $weight = 0;
                $volume = 0;
                foreach ($parcels as $parcel) {
                    $order = $orders[(int) $parcel['order_id']];
                    if ((int) $order->logistics_partner_id !== (int) $branch->logistics_partner_id || ! $order->isPaymentEligible()) {
                        $this->fail('Parcel company or payment eligibility is invalid.');
                    }
                    $delivery = Delivery::where('order_id', $order->id)->lockForUpdate()->first();
                    if ($delivery && RiderBatchItem::where('reserved_delivery_id', $delivery->id)->exists()) {
                        $this->fail('A parcel is already reserved by another batch.');
                    }
                    if ($delivery && ($delivery->returnCase()->exists() || $delivery->transfers()->where('status', ParcelTransfer::IN_TRANSIT)->exists())) {
                        $this->fail('Parcel has a separate return or linehaul custody movement.');
                    }
                    if ($type === 'pickup') {
                        $pickup = PickupRequest::where('order_id', $order->id)->lockForUpdate()->first();
                        if (! $pickup || (int) $pickup->logistics_partner_id !== (int) $branch->logistics_partner_id
                            || (int) $pickup->origin_sorting_center_id !== (int) $branch->id
                            || ! in_array($pickup->status, [PickupRequest::REQUESTED, PickupRequest::ASSIGNED], true)
                            || $order->status !== Order::STATUS_READY_FOR_PICKUP
                            || ($delivery && (! in_array($delivery->status, [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED], true)
                                || (int) $delivery->origin_sorting_center_id !== (int) $branch->id
                                || (int) $delivery->logistics_partner_id !== (int) $branch->logistics_partner_id))) {
                            $this->fail('Pickup must be requested, uncollected and owned by this origin branch.');
                        }
                        $stop = ['name' => $order->seller?->business_name ?: 'Seller', 'phone' => $order->seller?->contact_no,
                            'address' => $pickup->originAddressLabel(), 'province' => $pickup->origin_province_name,
                            'municipality' => $pickup->origin_municipality_name, 'barangay' => $pickup->origin_barangay_name];
                        $owner = 'seller-'.$order->seller_id;
                    } else {
                        if (! $delivery || (int) $delivery->logistics_partner_id !== (int) $branch->logistics_partner_id
                            || (int) $delivery->destination_sorting_center_id !== (int) $branch->id
                            || (int) $delivery->current_sorting_center_id !== (int) $branch->id || ! $delivery->destination_area_id
                            || ! in_array($delivery->status, [Delivery::SORTED, Delivery::DELIVERY_FAILED, Delivery::DELIVERY_ASSIGNED], true)
                            || $order->status !== Order::STATUS_TO_RECEIVE || $delivery->returnCase()->exists()
                            || $delivery->transfers()->where('status', ParcelTransfer::IN_TRANSIT)->exists()) {
                            $this->fail('Delivery parcel must be physically at its destination branch and eligible for release.');
                        }
                        if ($delivery->status === Delivery::DELIVERY_FAILED && ! $this->receivedFailure($delivery)) {
                            $this->fail('A failed parcel needs verified Branch return before another delivery batch.');
                        }
                        $stop = ['name' => $order->delivery_name, 'phone' => $order->delivery_phone, 'address' => $order->delivery_address,
                            'province' => $order->delivery_province_name, 'municipality' => $order->delivery_municipality_name, 'barangay' => $order->delivery_barangay_name];
                        $owner = 'buyer-'.$order->buyer_id;
                    }
                    // New batch work never uses the old unconfigured-branch coverage bypass.
                    if (! $stop['address'] || ! $stop['barangay'] || ! $rider->operationalServiceAreas()
                        ->where('operational_service_areas.logistics_partner_id', $branch->logistics_partner_id)
                        ->where('operational_service_areas.sorting_center_id', $branch->id)->get()
                        ->contains(fn ($a) => app(RiderServiceAreaService::class)->matches($a, $stop['province'], $stop['municipality'], $stop['barangay'], $type))) {
                        $this->fail('Every stop must fall inside this rider official branch barangay coverage.');
                    }
                    $kg = max((float) ($parcel['weight_kg'] ?? 0), (float) ($order->financial?->shipping_weight_kg ?? 0));
                    $m3 = (float) ($parcel['volume_m3'] ?? 0);
                    if (! is_finite($kg) || ! is_finite($m3) || $kg <= 0 || $m3 <= 0) {
                        $this->fail('Provide measured parcel weight and volume; unknown size is not treated as zero.');
                    }
                    $kg = ceil($kg * 1000) / 1000;
                    $m3 = ceil($m3 * 1000000) / 1000000;
                    $weight += $kg;
                    $volume += $m3;
                    $key = hash('sha256', $owner.'|'.json_encode($stop));
                    if (! isset($stops[$key])) {
                        $stops[$key] = count($stops) + 1;
                    }
                    if (! $delivery) {
                        $delivery = Delivery::create(['order_id' => $order->id, 'tracking_code' => $order->tracking_code,
                            'logistics_partner_id' => $branch->logistics_partner_id, 'origin_sorting_center_id' => $branch->id,
                            'status' => Delivery::PICKUP_ASSIGNED, 'rider_id' => $rider->id, 'assigned_at' => now()]);
                    }
                    if ($type === 'pickup') {
                        $delivery->update(['rider_id' => $rider->id, 'assigned_at' => now()]);
                        $pickup->update(['status' => PickupRequest::ASSIGNED, 'assigned_at' => now()]);
                    } else {
                        $delivery->update(['rider_id' => $rider->id, 'delivery_rider_id' => $rider->id,
                            'status' => Delivery::DELIVERY_ASSIGNED, 'delivery_assigned_at' => now(), 'delivery_attempts' => $delivery->delivery_attempts + 1]);
                    }
                    $batch->items()->create(['delivery_id' => $delivery->id, 'reserved_delivery_id' => $delivery->id,
                        'stop_sequence' => $stops[$key], 'stop_key' => $key, 'stop' => $stop, 'weight_kg' => $kg, 'volume_m3' => $m3,
                        'receipt_event_floor' => DB::table('parcel_scan_events')->where('delivery_id', $delivery->id)->max('id') ?? 0]);
                }
                $totals = ['parcels' => count($ids), 'stops' => count($stops), 'weight_kg' => round($weight, 3), 'volume_m3' => round($volume, 6), 'minutes' => $minutes];
                $this->capacity($profile->limits, $totals);
                $batch->update(['capacity_snapshot' => ['limits' => $profile->limits, 'totals' => $totals]]);
                $this->event($batch, 'created', $actorType, $actorId, ['orders' => $ids, 'capacity' => $batch->capacity_snapshot]);

                return $batch;
            }, 3);
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                $this->fail('A parcel or rider reservation changed concurrently. Refresh and retry.');
            }
            throw $e;
        }
    }

    public function transition(SortingCenter $branch, int $id, string $action, string $actorType, int $actorId): RiderBatch
    {
        try {
            return DB::transaction(function () use ($branch, $id, $action, $actorType, $actorId) {
                $this->branch($branch);
                $found = RiderBatch::where('logistics_partner_id', $branch->logistics_partner_id)->where('sorting_center_id', $branch->id)->findOrFail($id);
                $rider = $this->rider($branch, $found->rider_id, $action === 'dispatch');
                $batch = RiderBatch::whereKey($found->id)->lockForUpdate()->firstOrFail();
                if ($action === 'dispatch') {
                    app(RiderManagementService::class)->assertSchedule($rider, $batch->scheduled_start, $batch->scheduled_end);
                    if ($batch->status !== 'queued' || ! in_array($rider->availability_status, ['available', 'online'], true)
                        || now()->lt($batch->scheduled_start) || now()->gte($batch->scheduled_end)) {
                        $this->fail('Batch is not ready for dispatch in its scheduled window.');
                    }
                    if (RiderBatch::where('active_rider_id', $rider->id)->exists()) {
                        $this->fail('This rider already has an active batch.');
                    }
                    $this->capacity($this->profile($rider)->limits, $batch->capacity_snapshot['totals']);
                    if (Delivery::where('rider_id', $rider->id)->whereIn('status', [Delivery::PICKED_UP, Delivery::OUT_FOR_DELIVERY])
                        ->whereNotIn('id', $batch->items()->pluck('delivery_id'))->exists()) {
                        $this->fail('Resolve the rider existing parcel custody before starting another route.');
                    }

                    if (ReturnCase::where('return_rider_id', $rider->id)
                        ->whereIn('status', ['assigned', 'in_transit', 'awaiting_seller'])->exists()) {
                        $this->fail('Resolve the rider separate return route before dispatch.');
                    }
                    $items = $batch->items()->orderBy('delivery_id')->lockForUpdate()->get();
                    $deliveries = Delivery::whereIn('id', $items->pluck('delivery_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                    $areas = $rider->operationalServiceAreas()->where('operational_service_areas.logistics_partner_id', $branch->logistics_partner_id)
                        ->where('operational_service_areas.sorting_center_id', $branch->id)->get();
                    foreach ($items as $item) {
                        $d = $deliveries->get($item->delivery_id);
                        $order = $d?->order;
                        $expected = $batch->type === 'pickup' ? [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED] : [Delivery::DELIVERY_ASSIGNED];
                        if ((int) $item->reserved_delivery_id !== (int) $item->delivery_id || ! $d || ! in_array($d->status, $expected, true)
                            || (int) $d->rider_id !== (int) $rider->id || (int) $d->logistics_partner_id !== (int) $branch->logistics_partner_id
                            || ! $order || ! $order->isPaymentEligible() || (int) $order->logistics_partner_id !== (int) $branch->logistics_partner_id
                            || $d->returnCase()->exists() || $d->transfers()->where('status', ParcelTransfer::IN_TRANSIT)->exists()) {
                            $this->fail('A reserved parcel changed. Review this batch before dispatch.');
                        }
                        $stop = $item->stop;
                        if ($batch->type === 'pickup') {
                            $pickup = PickupRequest::where('order_id', $d->order_id)->lockForUpdate()->first();
                            if ($order->status !== Order::STATUS_READY_FOR_PICKUP || ! $pickup
                                || $pickup->status !== PickupRequest::ASSIGNED || (int) $pickup->origin_sorting_center_id !== (int) $branch->id
                                || (int) $pickup->logistics_partner_id !== (int) $branch->logistics_partner_id
                                || (int) $d->origin_sorting_center_id !== (int) $branch->id
                                || $stop['address'] !== $pickup->originAddressLabel()
                                || $stop['province'] !== $pickup->origin_province_name
                                || $stop['municipality'] !== $pickup->origin_municipality_name
                                || $stop['barangay'] !== $pickup->origin_barangay_name) {
                                $this->fail('Pickup ownership, eligibility or address changed before dispatch.');
                            }
                        } else {
                            if ($order->status !== Order::STATUS_TO_RECEIVE || (int) $d->current_sorting_center_id !== (int) $branch->id
                                || (int) $d->destination_sorting_center_id !== (int) $branch->id || ! $d->destination_area_id
                                || $stop['address'] !== $order->delivery_address || $stop['province'] !== $order->delivery_province_name
                                || $stop['municipality'] !== $order->delivery_municipality_name || $stop['barangay'] !== $order->delivery_barangay_name) {
                                $this->fail('Delivery custody, destination or address changed before dispatch.');
                            }
                        }
                        if (! $areas->contains(fn ($a) => app(RiderServiceAreaService::class)->matches($a, $stop['province'], $stop['municipality'], $stop['barangay'], $batch->type))) {
                            $this->fail('Official rider coverage changed before dispatch.');
                        }
                    }
                    $batch->update(['status' => 'in_progress', 'active_rider_id' => $rider->id, 'started_at' => now()]);
                    $rider->update(['availability_status' => 'busy']);
                } elseif ($action === 'complete') {
                    if ($batch->status !== 'in_progress') {
                        $this->fail('Only an active batch can be completed.');
                    }
                    foreach ($batch->items()->with('delivery')->lockForUpdate()->get() as $item) {
                        $outcome = $this->outcome($batch, $item);
                        if (! $outcome['settled']) {
                            $this->fail('Resolve each parcel outcome and return held/failed parcels to the Branch before completion.');
                        }
                        $item->update(['outcome_snapshot' => $outcome]);
                    }
                    $batch->update(['status' => 'completed', 'active_rider_id' => null, 'completed_at' => now()]);
                    $batch->items()->update(['reserved_delivery_id' => null]);
                    if ($rider->status === 'active' && $rider->availability_status === 'busy') {
                        $rider->update(['availability_status' => 'available']);
                    }
                } elseif ($action === 'cancel') {
                    if ($batch->status !== 'queued') {
                        $this->fail('Only an unstarted queued batch can be cancelled.');
                    }
                    $batch->update(['status' => 'cancelled', 'active_rider_id' => null, 'cancelled_at' => now()]);
                    $batch->items()->update(['reserved_delivery_id' => null]);
                } else {
                    $this->fail('Unknown batch transition.');
                }
                $this->event($batch, $action, $actorType, $actorId);

                return $batch;
            }, 3);
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                $this->fail('Another batch has already claimed this rider. Refresh.');
            }
            throw $e;
        }
    }

    public function guardLegacyAssignment(Rider $rider, ?Delivery $parcel = null): void
    {
        if (! $this->available()) {
            return;
        }
        Rider::whereKey($rider->id)->lockForUpdate()->firstOrFail();
        if (app(RiderManagementService::class)->available()
            || RiderBatchVehicleProfile::where('rider_id', $rider->id)->exists()
            || ($parcel && RiderBatchItem::where('reserved_delivery_id', $parcel->id)->exists())) {
            $this->fail('Use the assigned Branch Rider Batches workspace for this rider or reserved parcel.');
        }
    }

    public function assertParcelAllowed(Rider $rider, Delivery $delivery, bool $legacyHeld = false): void
    {
        if (! $this->available()) {
            return;
        }
        $item = RiderBatchItem::with('batch')->where('reserved_delivery_id', $delivery->id)->first();
        if ($item) {
            if ($item->batch->status !== 'in_progress' || (int) $item->batch->rider_id !== (int) $rider->id
                || (int) $item->batch->active_rider_id !== (int) $rider->id || $item->pickup_failed_at
                || (int) $delivery->rider_id !== (int) $rider->id
                || (int) $item->batch->logistics_partner_id !== (int) $rider->logistics_partner_id
                || (int) $delivery->logistics_partner_id !== (int) $rider->logistics_partner_id
                || (int) $item->batch->sorting_center_id !== (int) $rider->sorting_center_id) {
                $this->fail('This parcel is not actionable in your one active batch. Wait for Branch dispatch.');
            }
        } elseif (RiderBatch::where('active_rider_id', $rider->id)->exists()
            || (! $legacyHeld && RiderBatchVehicleProfile::where('rider_id', $rider->id)->exists())) {
            $this->fail('This parcel is outside your active batch. Ask the Branch to review the route.');
        }
    }

    public function pickupFailure(Rider $actor, int $batchId, int $itemId, string $reason): void
    {
        DB::transaction(function () use ($actor, $batchId, $itemId, $reason) {
            $rider = Rider::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $batch = RiderBatch::where('rider_id', $rider->id)->where('logistics_partner_id', $rider->logistics_partner_id)
                ->where('sorting_center_id', $rider->sorting_center_id)->where('type', 'pickup')->findOrFail($batchId);
            $item = $batch->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            $delivery = Delivery::whereKey($item->delivery_id)->lockForUpdate()->firstOrFail();
            $rider->load(['partner.user', 'sortingCenter']);
            if ($rider->status !== 'active' || ! $rider->email_verified_at || $rider->partner?->user?->status !== 'approved'
                || $rider->sortingCenter?->status !== 'active' || $rider->sortingCenter?->archived_at) {
                $this->fail('Only an active rider of an active approved branch may record a pickup outcome.');
            }
            $this->assertParcelAllowed($rider, $delivery);
            if (! in_array($delivery->status, [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED], true) || trim($reason) === '') {
                $this->fail('Only an uncollected pickup can be marked unsuccessful, with a reason.');
            }
            $item->update(['pickup_failed_at' => now(), 'pickup_failure_reason' => trim($reason)]);
            $this->event($batch, 'pickup_attempt_failed', 'rider', $rider->id, ['delivery_id' => $delivery->id, 'reason' => trim($reason)]);
        }, 3);
    }

    public function canReceiveFailed(Delivery $delivery): bool
    {
        if (! $this->available()) {
            return false;
        }
        $item = RiderBatchItem::with('batch')->where('reserved_delivery_id', $delivery->id)->first();

        return $item && $item->batch->type === 'delivery' && $item->batch->status === 'in_progress'
            && ! $this->receivedFailure($delivery);
    }

    public function guardOtherWork(int $riderId): void
    {
        if (! $this->available()) {
            return;
        }
        Rider::whereKey($riderId)->lockForUpdate()->firstOrFail();
        if (RiderBatch::where('rider_id', $riderId)->whereIn('status', ['queued', 'in_progress'])->exists()) {
            $this->fail('Resolve or reschedule the rider open batches before assigning a separate return route.');
        }
    }

    public function guardReservedParcel(Delivery $delivery): void
    {
        if ($this->available() && RiderBatchItem::where('reserved_delivery_id', $delivery->id)->exists()) {
            $this->fail('This parcel is reserved by an open batch. Complete its Branch handoff and close the batch first.');
        }
    }

    public function receivedFailure(Delivery $delivery): bool
    {
        $item = RiderBatchItem::where('delivery_id', $delivery->id)->latest('id')->first();

        return $item && DB::table('parcel_scan_events')->where('delivery_id', $delivery->id)
            ->where('event_type', 'failed_delivery_received')->where('id', '>', $item->receipt_event_floor)->exists();
    }

    public function outcome(RiderBatch $batch, RiderBatchItem $item, ?array $receipts = null): array
    {
        $d = $item->delivery;
        if ($batch->status === 'completed' && $item->outcome_snapshot) {
            return $item->outcome_snapshot;
        }
        if ($batch->status === 'cancelled') {
            return ['status' => 'cancelled', 'settled' => true];
        }
        if ($batch->type === 'pickup') {
            if ($item->pickup_failed_at) {
                return ['status' => 'pickup_failed', 'settled' => true, 'reason' => $item->pickup_failure_reason];
            }
            $received = $receipts !== null ? ($receipts[$item->delivery_id]['pickup_received'] ?? 0) > $item->receipt_event_floor :
                DB::table('parcel_scan_events')->where('delivery_id', $item->delivery_id)->where('event_type', 'pickup_received')
                    ->where('id', '>', $item->receipt_event_floor)->exists();

            return ['status' => $received ? 'received_at_branch' : ($d?->status ?? 'unavailable'), 'settled' => $received];
        }
        $delivered = $d?->status === Delivery::DELIVERED;
        $failed = $d?->status === Delivery::DELIVERY_FAILED;
        $returned = $failed && ($receipts !== null ? ($receipts[$item->delivery_id]['failed_delivery_received'] ?? 0) > $item->receipt_event_floor : $this->receivedFailure($d));

        return ['status' => $d?->status ?? 'unavailable', 'settled' => $delivered || $returned,
            'requires_branch_return' => $failed && ! $returned];
    }

    public function data(RiderBatch $batch): array
    {
        $batch->loadMissing(['rider', 'branch', 'items.delivery.order']);
        $items = [];
        $receipts = [];
        if (in_array($batch->status, ['queued', 'in_progress'], true)) {
            foreach (DB::table('parcel_scan_events')->whereIn('delivery_id', $batch->items->pluck('delivery_id'))
                ->whereIn('event_type', ['pickup_received', 'failed_delivery_received'])
                ->select('delivery_id', 'event_type', DB::raw('MAX(id) AS last_id'))->groupBy('delivery_id', 'event_type')->get() as $event) {
                $receipts[$event->delivery_id][$event->event_type] = (int) $event->last_id;
            }
        }
        foreach ($batch->items as $item) {
            $d = $item->delivery;
            $items[] = ['id' => $item->id, 'delivery_id' => $item->delivery_id,
                'tracking_code' => $d?->tracking_code, 'stop_sequence' => $item->stop_sequence, 'stop' => $item->stop,
                'weight_kg' => (float) $item->weight_kg, 'volume_m3' => (float) $item->volume_m3,
                'cod_amount' => $d?->order?->payment_method === 'cod' ? (float) $d->order->total_amount : null, ...$this->outcome($batch, $item, $receipts)];
        }

        return ['id' => $batch->id, 'code' => $batch->code, 'type' => $batch->type, 'status' => $batch->status,
            'rider' => $batch->rider?->name, 'branch' => $batch->branch?->name, 'branch_id' => $batch->sorting_center_id, 'scheduled_start' => $batch->scheduled_start->toIso8601String(),
            'scheduled_end' => $batch->scheduled_end->toIso8601String(), 'capacity' => $batch->capacity_snapshot,
            'progress' => ['settled' => count(array_filter($items, fn ($i) => $i['settled'])), 'total' => count($items)], 'items' => $items];
    }
}
