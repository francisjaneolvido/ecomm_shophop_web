<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Services\PhilippineDeliveryCoverage;
use App\Services\RiderServiceAreaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** A deliberate, one-barangay coverage approval from the pickup queue.
 * This never assigns the parcel, changes custody, or touches payment records. */
class PickupCoverageController extends Controller
{
    public function enable(Request $request, int $order, RiderServiceAreaService $matcher): RedirectResponse
    {
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'rider_id' => ['required', 'integer'],
            'confirm_coverage' => ['required', 'accepted'],
        ]);

        DB::transaction(function () use ($order, $partner, $data, $matcher) {
            $ready = Order::with('seller')->whereKey($order)->lockForUpdate()->firstOrFail();
            $pickup = PickupRequest::where('order_id', $ready->id)->lockForUpdate()->firstOrFail();
            if ($ready->status !== Order::STATUS_READY_FOR_PICKUP || ! $ready->isPaymentEligible()
                || $pickup->status !== PickupRequest::REQUESTED
                || (int) $pickup->logistics_partner_id !== (int) $partner->id
                || Delivery::where('order_id', $order)->exists()) {
                throw ValidationException::withMessages(['rider_id' =>
                    'This pickup request has changed. Refresh Parcel Operations before continuing.']);
            }

            $center = SortingCenter::where('logistics_partner_id', $partner->id)
                ->where('status', 'active')->whereNull('archived_at')
                ->where('type', 'station_hub')
                ->findOrFail((int) $pickup->origin_sorting_center_id);
            $seller = $ready->seller;
            $province = trim((string) ($seller?->province_name));
            $city = trim((string) ($seller?->municipality_name));
            $barangay = trim((string) ($seller?->barangay_name));
            if ($city === '' || $barangay === ''
                || RiderServiceAreaService::normalize($center->province) !== RiderServiceAreaService::normalize($province)) {
                throw ValidationException::withMessages(['rider_id' =>
                    'Seller pickup location is incomplete or is outside the selected branch province. Review the Seller address and branch first.']);
            }
            // Prefer an exact city branch. A legacy multi-city station must have
            // an explicit registered coverage entry for the Seller location.
            $sameCity = RiderServiceAreaService::normalizeCity($center->municipality) ===
                RiderServiceAreaService::normalizeCity($city);
            if (! $sameCity) {
                $coverage = app(PhilippineDeliveryCoverage::class);
                $registered = $center->coverageAreas()->get();
                if ($registered->isEmpty() || ! $registered->contains(fn ($area) =>
                    $coverage->matches($area, $province ?: null, $city, $seller?->region_name))) {
                    throw ValidationException::withMessages(['rider_id' =>
                        'Seller city is outside this branch registered coverage. Fix origin routing before enabling a rider.']);
                }
            }

            $rider = Rider::where('logistics_partner_id', $partner->id)
                ->where('sorting_center_id', $center->id)
                ->where('status', 'active')->where('availability_status', 'available')
                ->whereNotNull('email')->whereNotNull('password')
                ->findOrFail((int) $data['rider_id']);

            // Check *explicitly linked* coverage rather than legacy eligibility:
            // unconfigured branches still allow old assignment behavior.
            $alreadyLinked = $rider->operationalServiceAreas()->
                where('operational_service_areas.sorting_center_id', $center->id)->get()
                ->contains(fn (OperationalServiceArea $area) =>
                    $matcher->matches($area, $province ?: null, $city, $barangay, 'pickup'));
            if ($alreadyLinked) {
                throw ValidationException::withMessages(['rider_id' =>
                    'This rider is already eligible. Refresh and assign pickup instead.']);
            }

            // Attach only one exact pickup barangay. Never grant citywide or
            // delivery permissions from a pickup request.
            $areas = OperationalServiceArea::where('logistics_partner_id', $partner->id)
                ->where('sorting_center_id', $center->id)
                ->whereNotNull('barangay')->get();
            $area = $areas->first(fn (OperationalServiceArea $area) =>
                $matcher->matches($area, $province ?: null, $city, $barangay, 'pickup'));
            if (! $area) {
                $key = RiderServiceAreaService::locationKey($province ?: null, $city, $barangay, 'pickup');
                $area = OperationalServiceArea::firstOrCreate([
                    'sorting_center_id' => $center->id,
                    'location_key' => $key,
                ], [
                    'logistics_partner_id' => $partner->id,
                    'province' => $province ?: null,
                    'municipality' => $city,
                    'barangay' => $barangay,
                    'service_type' => 'pickup',
                ]);
                if ((int) $area->logistics_partner_id !== (int) $partner->id
                    || $area->barangay === null
                    || ! $matcher->matches($area, $province ?: null, $city, $barangay, 'pickup')) {
                    throw ValidationException::withMessages(['rider_id' =>
                        'Existing service area conflicts with the pickup location. No coverage was changed.']);
                }
            }
            $rider->operationalServiceAreas()->syncWithoutDetaching([$area->id]);
            $rider->unsetRelation('operationalServiceAreas');
            // Read back via fresh matcher, failing closed if the pivot did not stick.
            if (! (new RiderServiceAreaService)->pickupEligible($rider, $center->id, $seller)) {
                throw ValidationException::withMessages(['rider_id' =>
                    'Coverage verification failed. No changes were saved.']);
            }
        }, 2);

        return redirect()->to(route('logistics.deliveries.board').'#pickup-queue')
            ->with('status', 'Pickup coverage saved for the selected rider and Seller barangay. Now choose Assign pickup to dispatch.');
    }
}
