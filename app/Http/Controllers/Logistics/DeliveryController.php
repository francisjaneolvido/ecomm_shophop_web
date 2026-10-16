<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Services\ParcelScanService;
use App\Services\PhilippineDeliveryCoverage;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function board(Request $request): View
    {
        $partner = $this->partner($request);
        $ready = self::readyFor($partner);
        $deliveries = Delivery::with([
            'order.seller', 'order.items.product', 'rider', 'pickupRider', 'deliveryRider',
            'deliveredRider', 'destinationArea.sortingCenter',
            'originSortingCenter', 'currentSortingCenter', 'destinationSortingCenter',
            'transfers.fromCenter', 'transfers.toCenter', 'failureReports.rider',
        ])->where('logistics_partner_id', $partner->id)->latest()->get();

        $riders = Rider::with(['coverageArea.sortingCenter', 'sortingCenter'])
            ->where('logistics_partner_id', $partner->id)
            ->where('status', 'active')
            ->whereNotNull('email')->whereNotNull('password')
            ->orderBy('name')->get();
        $coverageAreas = $partner->coverageAreas()->with('sortingCenter')->orderBy('area_name')->get();
        $sortingCenters = $partner->sortingCenters()->where('status', 'active')->orderByDesc('is_main')->orderBy('name')->get();

        return view('logistics.deliveries.board', compact('ready', 'deliveries', 'riders', 'coverageAreas', 'sortingCenters'));
    }

    /** Assign the rider who will collect the parcel from the Seller and bring it to the sorting center. */
    public function assign(Request $request, int $order): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']]);
        $rider = $this->activeRider($partner, (int) $data['rider_id']);

        try {
            return DB::transaction(function () use ($partner, $rider, $order) {
                $ready = Order::with('pickupRequest')->whereKey($order)->lockForUpdate()->firstOrFail();
                $pickup = PickupRequest::where('order_id', $ready->id)->lockForUpdate()->first();
                if ($ready->status !== Order::STATUS_READY_FOR_PICKUP || ! $ready->isPaymentEligible()
                    || ! $pickup || (int) $pickup->logistics_partner_id !== (int) $partner->id
                    || $pickup->status !== PickupRequest::REQUESTED
                    || Delivery::where('order_id', $order)->exists()
                    || ! $this->riderStillActive($rider, $partner)) {
                    return $this->stale();
                }

                $originCenterId = $pickup->origin_sorting_center_id
                    ?: $rider->sorting_center_id
                    ?: $partner->mainSortingCenter()->value('id')
                    ?: $partner->sortingCenters()->where('status', 'active')->value('id');

                if ($originCenterId && $rider->sorting_center_id && (int) $rider->sorting_center_id !== (int) $originCenterId) {
                    throw ValidationException::withMessages([
                        'rider_id' => 'Choose a pickup Rider assigned to the requested origin Sorting Center.',
                    ]);
                }

                Delivery::create([
                    'order_id' => $ready->id,
                    'tracking_code' => $ready->tracking_code ?: $this->trackingCode($ready),
                    'logistics_partner_id' => $partner->id,
                    'origin_sorting_center_id' => $originCenterId,
                    'rider_id' => $rider->id,
                    'status' => Delivery::PICKUP_ASSIGNED,
                    'assigned_at' => now(),
                ]);

                $pickup->update([
                    'status' => PickupRequest::ASSIGNED,
                    'origin_sorting_center_id' => $originCenterId,
                    'assigned_at' => now(),
                ]);

                return redirect()->route('logistics.deliveries.board')
                    ->with('status', 'Pickup Rider assigned.');
            });
        } catch (QueryException $exception) {
            if (Delivery::where('order_id', $order)->exists()) {
                return $this->stale();
            }
            throw $exception;
        }
    }

    /** Sorting-center scan/receipt after the pickup rider physically hands over the parcel. */
    public function receive(Request $request, int $delivery, ParcelScanService $scanner): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['tracking_code' => ['required', 'string', 'max:60']]);
        $parcel = Delivery::where('logistics_partner_id', $partner->id)->findOrFail($delivery);
        if (! hash_equals((string) $parcel->tracking_code, trim($data['tracking_code']))) {
            throw ValidationException::withMessages(['tracking_code' => 'Tracking code does not match the selected delivery.']);
        }
        $centerId = (int) $parcel->origin_sorting_center_id;
        abort_unless($centerId, 422, 'Origin branch must be configured first.');
        $scanner->receive((int) $partner->id, $centerId, (int) $request->user()->id, $data['tracking_code']);
        return back()->with('status', 'Pickup parcel received and recorded at origin branch.');
    }

    /** Record the destination area selected after reading the delivery address. */
    public function sort(Request $request, int $delivery): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['destination_area_id' => ['required', 'integer']]);
        $area = LogisticsCoverageArea::where('logistics_partner_id', $partner->id)
            ->findOrFail($data['destination_area_id']);

        return DB::transaction(function () use ($partner, $delivery, $area) {
            $owned = Delivery::with('order')->where('logistics_partner_id', $partner->id)
                ->whereKey($delivery)->lockForUpdate()->firstOrFail();

            if ($owned->status !== Delivery::AT_SORTING_CENTER || $owned->order?->status !== Order::STATUS_TO_RECEIVE) {
                return $this->stale();
            }

            // The selected area must actually cover this order's recorded destination.
            if (! $this->areaCoversOrder($area, $owned->order)) {
                throw ValidationException::withMessages([
                    'destination_area_id' => 'The selected area does not cover the recorded delivery address.',
                ]);
            }

            $destinationCenterId = $area->sorting_center_id
                ?: $partner->mainSortingCenter()->value('id');

            $owned->update([
                'destination_area_id' => $area->id,
                'destination_sorting_center_id' => $destinationCenterId,
                'status' => Delivery::SORTED,
                'sorted_at' => now(),
            ]);

            return back()->with('status', 'Parcel sorted by destination area.');
        });
    }

    /** Assign the rider responsible for final delivery after sorting, or after a failed attempt. */
    public function assignDelivery(Request $request, int $delivery): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']]);
        $rider = $this->activeRider($partner, (int) $data['rider_id']);

        return DB::transaction(function () use ($partner, $delivery, $rider) {
            $owned = Delivery::with(['order', 'destinationArea', 'transfers'])
                ->where('logistics_partner_id', $partner->id)
                ->whereKey($delivery)->lockForUpdate()->firstOrFail();

            if (! in_array($owned->status, [Delivery::SORTED, Delivery::DELIVERY_FAILED], true)
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                || $owned->returnCase()->exists()
                || ! $owned->destination_area_id || ! $this->riderStillActive($rider, $partner)) {
                return $this->stale();
            }

            if ((int) $rider->coverage_area_id !== (int) $owned->destination_area_id) {
                throw ValidationException::withMessages([
                    'rider_id' => 'Choose an active Rider assigned to this parcel\'s destination area.',
                ]);
            }

            if ($owned->destination_sorting_center_id
                && (int) $owned->current_sorting_center_id !== (int) $owned->destination_sorting_center_id) {
                throw ValidationException::withMessages([
                    'rider_id' => 'Receive the parcel at its destination Sorting Center before assigning the final-delivery Rider.',
                ]);
            }

            if ($owned->destination_sorting_center_id
                && (int) $rider->sorting_center_id !== (int) $owned->destination_sorting_center_id) {
                throw ValidationException::withMessages([
                    'rider_id' => 'Choose a Rider assigned to the destination Sorting Center.',
                ]);
            }

            if ($owned->transfers->contains(fn ($transfer) => $transfer->status === ParcelTransfer::IN_TRANSIT)) {
                throw ValidationException::withMessages([
                    'rider_id' => 'This parcel is still in transit between Sorting Centers.',
                ]);
            }

            $owned->update([
                'rider_id' => $rider->id,
                'delivery_rider_id' => $rider->id,
                'status' => Delivery::DELIVERY_ASSIGNED,
                'delivery_assigned_at' => now(),
                'delivery_attempts' => $owned->delivery_attempts + 1,
            ]);

            return back()->with('status', 'Delivery Rider assigned for the destination area.');
        });
    }

    /** Dispatch a sorted parcel from its current center to the branch that owns the destination area. */
    public function dispatchTransfer(Request $request, int $delivery): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'to_sorting_center_id' => ['required', 'integer'],
            'transit_rider_id' => ['nullable', 'integer'],
        ]);

        return DB::transaction(function () use ($partner, $delivery, $data) {
            $owned = Delivery::with(['order', 'transfers'])
                ->where('logistics_partner_id', $partner->id)
                ->whereKey($delivery)->lockForUpdate()->firstOrFail();

            if ($owned->status !== Delivery::SORTED
                || ! $owned->current_sorting_center_id
                || ! $owned->destination_sorting_center_id
                || (int) $owned->current_sorting_center_id === (int) $owned->destination_sorting_center_id) {
                return $this->stale();
            }

            if ($owned->transfers->contains(fn ($transfer) => $transfer->status === ParcelTransfer::IN_TRANSIT)) {
                return back()->withErrors(['delivery' => 'This parcel already has an active center transfer.']);
            }

            $from = SortingCenter::where('logistics_partner_id', $partner->id)->findOrFail($owned->current_sorting_center_id);
            $to = SortingCenter::where('logistics_partner_id', $partner->id)
                ->where('status', 'active')
                ->findOrFail((int) $data['to_sorting_center_id']);

            if ((int) $from->id === (int) $to->id) {
                throw ValidationException::withMessages([
                    'to_sorting_center_id' => 'Choose a different next Sorting Center.',
                ]);
            }

            $transitRider = null;
            if (! empty($data['transit_rider_id'])) {
                $transitRider = $this->activeRider($partner, (int) $data['transit_rider_id']);
                if ((int) $transitRider->sorting_center_id !== (int) $from->id) {
                    throw ValidationException::withMessages([
                        'transit_rider_id' => 'Transit Rider must belong to the dispatching branch.',
                    ]);
                }
            }

            ParcelTransfer::create([
                'delivery_id' => $owned->id,
                'from_sorting_center_id' => $from->id,
                'transit_rider_id' => $transitRider?->id,
                'to_sorting_center_id' => $to->id,
                'status' => ParcelTransfer::IN_TRANSIT,
                'dispatched_at' => now(),
            ]);

            return back()->with('status', 'Parcel dispatched to '.$to->name.'.');
        });
    }

    /** Destination branch scan that closes the active hub-to-hub transfer. */
    public function receiveTransfer(Request $request, int $transfer, ParcelScanService $scanner): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['tracking_code' => ['required', 'string', 'max:60']]);
        $parcelTransfer = ParcelTransfer::with('delivery')->findOrFail($transfer);
        abort_unless((int) $parcelTransfer->delivery?->logistics_partner_id === (int) $partner->id, 404);
        abort_unless($parcelTransfer->status === ParcelTransfer::IN_TRANSIT, 422, 'Transfer is not in transit.');
        if (! hash_equals((string) $parcelTransfer->delivery->tracking_code, trim($data['tracking_code']))) {
            throw ValidationException::withMessages(['tracking_code' => 'Tracking code does not match this transfer.']);
        }
        $scanner->receive((int) $partner->id, (int) $parcelTransfer->to_sorting_center_id,
            (int) $request->user()->id, $data['tracking_code']);
        return back()->with('status', 'Transfer received and recorded at destination branch.');
    }

    public static function readyFor(LogisticsPartner $partner): Collection
    {
        return Order::with(['seller', 'items.product', 'pickupRequest.originSortingCenter'])
            ->where('status', Order::STATUS_READY_FOR_PICKUP)
            ->paymentEligible()
            ->whereHas('pickupRequest', function ($query) use ($partner) {
                $query->where('logistics_partner_id', $partner->id)
                    ->where('status', PickupRequest::REQUESTED);
            })
            ->whereNotIn('id', Delivery::select('order_id'))
            ->latest()->get();
    }

    private function areaCoversOrder(LogisticsCoverageArea $area, Order $order): bool
    {
        $coverage = app(PhilippineDeliveryCoverage::class);
        $partner = LogisticsPartner::findOrFail($area->logistics_partner_id);
        return $coverage->coversOrder($area, $order)
            && $coverage->availableForPartner($area, $partner);
    }

    private function activeRider(LogisticsPartner $partner, int $riderId): Rider
    {
        return Rider::where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->whereNotNull('email')->whereNotNull('password')
            ->findOrFail($riderId);
    }

    private function riderStillActive(Rider $rider, LogisticsPartner $partner): bool
    {
        return Rider::whereKey($rider->id)->where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->whereNotNull('email')->whereNotNull('password')->exists();
    }

    private function trackingCode(Order $order): string
    {
        $date = ($order->created_at ?? now())->format('Ymd');
        return 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    private function partner(Request $request): LogisticsPartner
    {
        return LogisticsPartner::with('coverageAreas')->where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }

    private function stale(): RedirectResponse
    {
        return redirect()->route('logistics.deliveries.board')
            ->withErrors(['delivery' => 'This delivery changed or is not eligible. Refresh and review it.']);
    }
}
