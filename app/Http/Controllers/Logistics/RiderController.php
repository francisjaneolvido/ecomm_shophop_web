<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\Delivery;
use App\Services\RiderServiceAreaService;
use Illuminate\Support\Facades\DB;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Notifications\RiderApplicationDecisionNotification;
use App\Services\RiderRegistrationDocuments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RiderController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);
        // Nationwide demo can contain 1,500+ city riders. Never hydrate every
        // rider, relationship and form in one response.
        $riderQuery = Rider::query()->where('logistics_partner_id', $partner->id);
        $totals = (clone $riderQuery)->selectRaw("SUM(CASE WHEN status='pending' AND email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as pending_review")
            ->selectRaw("SUM(CASE WHEN status='pending' AND email_verified_at IS NULL THEN 1 ELSE 0 END) as awaiting_email")
            ->selectRaw("SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN status IN ('suspended','rejected') THEN 1 ELSE 0 END) as inactive_count")
            ->first();
        $term = trim((string) $request->query('q', ''));
        if ($term !== '') {
            $riderQuery->where(function ($query) use ($term) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhereHas('sortingCenter', fn ($q) => $q->where('name', 'like', '%'.$term.'%'));
            });
        }
        if ($request->filled('center')) {
            $center = $partner->sortingCenters()->find((int) $request->query('center'));
            $riderQuery->where('sorting_center_id', $center?->id ?? -1);
        }
        $riders = $riderQuery->with(['coverageArea.sortingCenter', 'sortingCenter', 'operationalServiceAreas'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 WHEN 'suspended' THEN 2 ELSE 3 END")
            ->orderByDesc('applied_at')->orderBy('name')->paginate(32)->withQueryString();
        $coverageAreas = $partner->coverageAreas()->with('sortingCenter')->orderBy('area_name')->get();
        $sortingCenters = $partner->sortingCenters()->with('coverageAreas')->orderByDesc('is_main')->orderBy('name')->get();
        $pageBranchIds = $riders->getCollection()->pluck('sorting_center_id')->filter()->unique()->all();
        $operationalAreas = OperationalServiceArea::with('sortingCenter')->where('logistics_partner_id', $partner->id)
            ->whereIn('sorting_center_id', $pageBranchIds)
            ->orderBy('municipality')->orderBy('barangay')->get();

        return view('logistics.riders.index', compact('riders', 'coverageAreas', 'sortingCenters', 'operationalAreas', 'totals'));
    }

    /** Operator-created Rider records remain available for controlled/manual onboarding. */
    public function store(Request $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'vehicle_type' => ['required', 'string', 'max:80'],
            'sorting_center_id' => ['nullable', 'integer'],
            'coverage_area_id' => ['nullable', 'integer'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('riders', 'email'),
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        [$centerId, $areaId] = $this->validatedAssignment(
            $partner,
            $data['sorting_center_id'] ?? null,
            $data['coverage_area_id'] ?? null,
        );

        Rider::create([
            ...$data,
            'sorting_center_id' => $centerId,
            'coverage_area_id' => $areaId,
            'password' => Hash::make($data['password']),
            'logistics_partner_id' => $partner->id,
            'status' => 'active',
            'availability_status' => 'available',
            'email_verified_at' => now(),
            'approved_at' => now(),
        ]);

        return redirect()->route('logistics.riders.index')->with('status', 'Rider added.');
    }

    public function provision(Request $request, int $rider): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('riders', 'email'),
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if (Rider::whereKey($ownedRider->id)
            ->whereNull('email')
            ->whereNull('password')
            ->update([
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]) !== 1) {
            return back()->withErrors(['rider' => 'Credentials already provisioned.']);
        }

        return back()->with('status', 'Rider credentials provisioned.');
    }

    public function approve(Request $request, int $rider): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);

        if (! $ownedRider->email_verified_at) {
            return back()->withErrors(['rider' => 'Rider must verify the registered email before approval.']);
        }

        $defaultCenterId = $ownedRider->sorting_center_id
            ?: $partner->mainSortingCenter()->value('id')
            ?: $partner->sortingCenters()->value('id');

        if (Rider::whereKey($ownedRider->id)
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->update([
                'sorting_center_id' => $defaultCenterId,
                'status' => 'active',
                'availability_status' => 'available',
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]) !== 1) {
            return back()->withErrors(['rider' => 'Rider application changed. Refresh and review it.']);
        }

        $this->sendDecisionEmail($ownedRider->fresh() ?? $ownedRider, true, $partner);

        return back()->with('status', 'Rider application approved.');
    }

    public function reject(Request $request, int $rider): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        if (! $ownedRider->email_verified_at) {
            return back()->withErrors(['rider' => 'Rider must verify the registered email before review.']);
        }

        if (Rider::whereKey($ownedRider->id)
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->update([
                'status' => 'rejected',
                'availability_status' => 'offline',
                'rejected_at' => now(),
                'rejection_reason' => trim($data['rejection_reason']),
            ]) !== 1) {
            return back()->withErrors(['rider' => 'Rider application changed. Refresh and review it.']);
        }

        $this->sendDecisionEmail($ownedRider->fresh() ?? $ownedRider, false, $partner);

        return back()->with('status', 'Rider application rejected.');
    }

    public function document(Request $request, int $rider, string $slot): StreamedResponse
    {
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $path = RiderRegistrationDocuments::scopedPath($ownedRider, $slot);
        $disk = Storage::disk(RiderRegistrationDocuments::DISK);
        abort_unless($path && $disk->exists($path), 404);

        $mime = $disk->mimeType($path);
        $safeMime = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)
            ? $mime
            : 'application/octet-stream';

        return $disk->response(
            $path,
            'rider-'.$ownedRider->id.'-'.$slot.'.'.pathinfo($path, PATHINFO_EXTENSION),
            [
                'Content-Type' => $safeMime,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "sandbox; default-src 'none'",
            ],
            'inline',
        );
    }

    public function updateArea(Request $request, int $rider): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $data = $request->validate([
            'sorting_center_id' => ['nullable', 'integer'],
            'coverage_area_id' => ['nullable', 'integer'],
        ]);

        [$centerId, $areaId] = $this->validatedAssignment(
            $partner,
            $data['sorting_center_id'] ?? null,
            $data['coverage_area_id'] ?? null,
        );

        if ((int) $ownedRider->sorting_center_id !== (int) $centerId
            && Delivery::where('logistics_partner_id', $partner->id)
                ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
                ->where(fn ($q) => $q->where('rider_id', $ownedRider->id)
                    ->orWhere('pickup_rider_id', $ownedRider->id)
                    ->orWhere('delivery_rider_id', $ownedRider->id))->exists()) {
            return back()->withErrors(['sorting_center_id' => 'Complete or reassign active parcels before moving this Rider to another branch.']);
        }
        DB::transaction(function () use ($ownedRider, $centerId, $areaId) {
            if ((int) $ownedRider->sorting_center_id !== (int) $centerId) {
                // Never silently reassign existing barangays to a different branch.
                $ownedRider->operationalServiceAreas()->detach();
            }
            $ownedRider->update(['sorting_center_id' => $centerId, 'coverage_area_id' => $areaId]);
        });

        return back()->with('status', 'Rider sorting center and delivery area updated.');
    }

    /** Create a local operational service area. It doesn't create a physical branch or rider. */
    public function storeOperationalArea(Request $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'sorting_center_id' => ['required', 'integer'],
            'province' => ['nullable', 'string', 'max:150'],
            'municipality' => ['required', 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:150'],
            'service_type' => ['required', Rule::in(['pickup', 'delivery', 'both'])],
        ]);
        $center = $partner->sortingCenters()->where('status', 'active')->whereNull('archived_at')
            ->findOrFail((int) $data['sorting_center_id']);
        if ($center->type !== 'station_hub') {
            return back()->withErrors(['sorting_center_id' => 'Assign operational pickup/delivery areas to an active Station / Branch.']);
        }
        // Avoid a typosquatted area belonging to an entirely different province.
        $province = trim((string) ($data['province'] ?? '')) ?: null;
        if (RiderServiceAreaService::normalize($province) !== RiderServiceAreaService::normalize($center->province)) {
            return back()->withErrors(['province' => 'Province must match the selected branch (leave blank for NCR).']);
        }
        $municipality = trim($data['municipality']);
        // An operational barangay must also be inside this branch's registered city/municipality coverage.
        $registered = $center->coverageAreas()->get();
        $matcher = app(\App\Services\PhilippineDeliveryCoverage::class);
        if ($registered->isNotEmpty() && ! $registered->contains(fn ($area) =>
            $matcher->matches($area, $province, $municipality, $center->region))) {
            return back()->withErrors(['municipality' => 'This city is not part of the branch registered coverage. Update the Branch coverage first.']);
        }
        $barangay = trim((string) ($data['barangay'] ?? '')) ?: null;
        $key = RiderServiceAreaService::locationKey($province, $municipality, $barangay, $data['service_type']);
        $existing = OperationalServiceArea::where('sorting_center_id', $center->id)->where('location_key', $key)->exists();
        if ($existing) return back()->withErrors(['municipality' => 'This branch already has this service area and task type.']);
        OperationalServiceArea::create([
            'logistics_partner_id' => $partner->id, 'sorting_center_id' => $center->id,
            'province' => $province, 'municipality' => $municipality, 'barangay' => $barangay,
            'service_type' => $data['service_type'], 'location_key' => $key,
        ]);
        return back()->with('status', 'Service area added. Assign eligible riders below before dispatch.');
    }

    public function removeOperationalArea(Request $request, int $area): RedirectResponse
    {
        $partner = $this->partner($request);
        $owned = OperationalServiceArea::where('logistics_partner_id', $partner->id)->findOrFail($area);
        if ($owned->riders()->exists()) {
            return back()->withErrors(['service_area' => 'Unassign riders before removing this area. Existing parcel assignments remain unchanged.']);
        }
        $owned->delete();
        return back()->with('status', 'Unassigned service area removed.');
    }

    public function syncRiderOperationalAreas(Request $request, int $rider): RedirectResponse
    {
        $partner = $this->partner($request);
        $owned = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $data = $request->validate(['area_ids' => ['nullable', 'array', 'max:150'],
            'area_ids.*' => ['required', 'integer', 'distinct']]);
        $ids = array_map('intval', $data['area_ids'] ?? []);
        $valid = OperationalServiceArea::where('logistics_partner_id', $partner->id)
            ->where('sorting_center_id', $owned->sorting_center_id)
            ->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($valid) !== count($ids)) {
            return back()->withErrors(['area_ids' => 'All selected service areas must belong to the rider home branch.']);
        }
        // Active work cannot lose its permitted service area while the rider still has custody.
        $existing = $owned->operationalServiceAreas()->pluck('operational_service_areas.id')->map(fn ($id) => (int) $id)->all();
        if (array_diff($existing, $valid) && Delivery::where('logistics_partner_id', $partner->id)
            ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
            ->where(fn ($q) => $q->where('rider_id', $owned->id)
                ->orWhere('pickup_rider_id', $owned->id)->orWhere('delivery_rider_id', $owned->id))->exists()) {
            return back()->withErrors(['area_ids' => 'Finish or reassign the rider active parcels before removing coverage.']);
        }
        $owned->operationalServiceAreas()->sync($valid);
        return back()->with('status', 'Rider barangay service areas saved. Mobile shows the updated coverage after refresh.');
    }

    public function suspend(Request $request, int $rider): RedirectResponse
    {
        return $this->setStatus($request, $rider, 'active', 'suspended', 'offline');
    }

    public function activate(Request $request, int $rider): RedirectResponse
    {
        return $this->setStatus($request, $rider, 'suspended', 'active', 'available');
    }

    private function setStatus(
        Request $request,
        int $riderId,
        string $from,
        string $to,
        string $availability,
    ): RedirectResponse {
        $partner = $this->partner($request);
        $rider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($riderId);

        if (Rider::whereKey($rider->id)
            ->where('status', $from)
            ->update([
                'status' => $to,
                'availability_status' => $availability,
            ]) !== 1) {
            return back()->withErrors(['rider' => 'Rider status changed. Refresh and review it.']);
        }

        return back()->with('status', 'Rider status updated.');
    }

    private function validatedAssignment(LogisticsPartner $partner, mixed $centerId, mixed $areaId): array
    {
        $center = null;
        $area = null;

        if ($centerId) {
            $center = SortingCenter::where('logistics_partner_id', $partner->id)
                ->where('status', 'active')
                ->findOrFail((int) $centerId);
        }

        if ($areaId) {
            $area = LogisticsCoverageArea::where('logistics_partner_id', $partner->id)
                ->with('sortingCenter')
                ->findOrFail((int) $areaId);

            if ($area->sorting_center_id) {
                if ($center && (int) $center->id !== (int) $area->sorting_center_id) {
                    throw ValidationException::withMessages([
                        'coverage_area_id' => 'The selected delivery area belongs to a different Sorting Center.',
                    ]);
                }

                $center = $area->sortingCenter;
            }
        }

        if (! $center) {
            $center = $partner->mainSortingCenter()->where('status', 'active')->first()
                ?? $partner->sortingCenters()->where('status', 'active')->first();
        }

        return [$center?->id, $area?->id];
    }

    private function sendDecisionEmail(Rider $rider, bool $approved, LogisticsPartner $partner): void
    {
        try {
            $rider->notify(new RiderApplicationDecisionNotification(
                $approved,
                $partner->company_name,
                $approved ? null : $rider->rejection_reason,
            ));
        } catch (\Throwable $exception) {
            // The review decision is authoritative even when outbound mail is temporarily unavailable.
            report($exception);
        }
    }

    private function partner(Request $request): LogisticsPartner
    {
        return LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }
}
