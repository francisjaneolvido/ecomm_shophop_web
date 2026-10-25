<?php

namespace App\Http\Controllers\hub;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logistics\BranchStaffController;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\User;
use App\Services\LinehaulService;
use App\Services\LogisticsRoutingService;
use App\Services\ManifestLoadingService;
use App\Services\ParcelScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Province hub portal.
 * The province hub manager (branch staff, guard: logistics_staff) creates and manages the
 * station hubs under HIS OWN province hub. A station hub = a sorting_centers row
 * (type = station_hub, parent_id = province hub) whose login is a `users` row with
 * account_type = 'branch' (linked through sorting_centers.user_id).
 */
class HubPortalController extends Controller
{
    /* ---------------------------------------------------------------
     | Dashboard
     * ------------------------------------------------------------- */
    public function dashboard(Request $request): View
    {
        $center = $this->center();
        $open = [Delivery::AT_SORTING_CENTER, Delivery::SORTED];

        // Parcels na nasa province hub ngayon
        $inHub = Delivery::where('current_sorting_center_id', $center->id)
            ->whereIn('status', $open)->count();

        // Inbound = scans na naitala sa hub ngayon
        $inboundToday = DB::table('parcel_scan_events')
            ->where('sorting_center_id', $center->id)
            ->whereDate('scanned_at', today())->count();

        // Outbound = transfers na umalis sa hub ngayon
        $outboundToday = ParcelTransfer::where('from_sorting_center_id', $center->id)
            ->whereDate('dispatched_at', today())->count();

        // Delayed = nasa hub nang higit 24 oras na walang galaw, o failed delivery
        $delayed = Delivery::where('current_sorting_center_id', $center->id)
            ->whereIn('status', array_merge($open, [Delivery::DELIVERY_FAILED]))
            ->where('updated_at', '<', now()->subDay())->count();

        $stats = [
            ['Parcels in hub', $inHub, 'lucide-package', 'text-navy'],
            ['Inbound today', $inboundToday, 'lucide-arrow-down', 'text-teal'],
            ['Outbound today', $outboundToday, 'lucide-arrow-up', 'text-sky'],
            ['Delayed (24h+)', $delayed, 'lucide-clock', $delayed > 0 ? 'text-yellow' : 'text-navy'],
        ];

        // Station hubs sa ilalim ng province hub na ito
        $stationModels = $center->stationHubs()->whereNull('archived_at')
            ->withCount(['riders as active_riders_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')->get();

        $stationIds = $stationModels->pluck('id');

        $activeByStation = Delivery::whereIn('destination_sorting_center_id', $stationIds)
            ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
            ->selectRaw('destination_sorting_center_id as center_id, COUNT(*) as total')
            ->groupBy('destination_sorting_center_id')->pluck('total', 'center_id');

        $maxLoad = max(1, (int) $activeByStation->max());

        $stations = $stationModels->map(fn ($s) => [
            'name'    => $s->name,
            'city'    => $s->municipality,
            'active'  => (int) ($activeByStation[$s->id] ?? 0),
            'riders'  => (int) $s->active_riders_count,
            'percent' => (int) round((($activeByStation[$s->id] ?? 0) / $maxLoad) * 100),
            'status'  => $s->status,
        ]);

        // Recent activity: scans at transfers sa hub at mga station niya
        $centerIds = $stationIds->push($center->id);

        $scans = DB::table('parcel_scan_events as e')
            ->join('deliveries as d', 'd.id', '=', 'e.delivery_id')
            ->join('sorting_centers as c', 'c.id', '=', 'e.sorting_center_id')
            ->whereIn('e.sorting_center_id', $centerIds)
            ->orderByDesc('e.scanned_at')->limit(8)
            ->get(['d.tracking_code', 'c.name as center', 'e.event_type', 'e.scanned_at'])
            ->map(fn ($r) => [
                'title' => match ($r->event_type) {
                    'pickup_received' => "Pickup received at {$r->center}",
                    'hub_sorted'      => "Sorted at {$r->center}",
                    default           => "Transfer received at {$r->center}",
                },
                'sub'   => $r->tracking_code,
                'at'    => Carbon::parse($r->scanned_at),
            ]);

        $transfers = ParcelTransfer::with(['delivery', 'fromCenter', 'toCenter'])
            ->where(fn ($q) => $q->whereIn('from_sorting_center_id', $centerIds)
                ->orWhereIn('to_sorting_center_id', $centerIds))
            ->whereNotNull('dispatched_at')
            ->latest('dispatched_at')->limit(8)->get()
            ->map(fn ($t) => [
                'title' => 'Dispatched: '.($t->fromCenter?->name ?? '—').' → '.($t->toCenter?->name ?? '—'),
                'sub'   => $t->delivery?->tracking_code ?? '',
                'at'    => $t->dispatched_at,
            ]);

        $activity = $scans->concat($transfers)->sortByDesc('at')->take(8)->values();
        $stationCount = $stationModels->count();

        return view('hub.dashboard.dashboard', compact('center', 'stats', 'stations', 'activity', 'stationCount'));
    }

    /* ---------------------------------------------------------------
     | Parcel operations: receive -> sort -> manifest -> dispatch
     * ------------------------------------------------------------- */
    public function parcels(Request $request, LogisticsRoutingService $routing): View
    {
        $center = $this->center();

        $incoming = ParcelTransfer::with([
                'delivery.order',
                'delivery.destinationSortingCenter.parent',
                'fromCenter',
                'linehaulTrip',
                'manifest',
            ])
            ->where('to_sorting_center_id', $center->id)
            ->where('status', ParcelTransfer::IN_TRANSIT)
            ->whereHas('delivery', fn ($q) => $q->where('logistics_partner_id', $center->logistics_partner_id))
            ->latest('id')
            ->limit(100)
            ->get();

        $awaiting = $incoming->filter(fn ($t) =>
            $t->linehaul_trip_id
                ? in_array($t->linehaulTrip?->status, ['arrived', 'completed'], true)
                : (! $t->transit_rider_id || $t->departed_at)
        )->values();
        $notDeparted = $incoming->filter(fn ($t) => ! $awaiting->contains('id', $t->id))->values();

        $toSort = Delivery::with(['order', 'destinationSortingCenter.parent'])
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('current_sorting_center_id', $center->id)
            ->where('status', Delivery::SORTED)
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_TO_RECEIVE))
            ->whereHas('transfers', fn ($q) => $q
                ->where('to_sorting_center_id', $center->id)
                ->where('status', ParcelTransfer::RECEIVED))
            ->whereNotExists(function ($q) use ($center) {
                $q->selectRaw('1')
                    ->from('parcel_scan_events as pse')
                    ->whereColumn('pse.delivery_id', 'deliveries.id')
                    ->where('pse.sorting_center_id', $center->id)
                    ->where('pse.event_type', 'hub_sorted');
            })
            ->latest('updated_at')
            ->get()
            ->map(function (Delivery $delivery) use ($routing) {
                $plan = $routing->transferPlan($delivery);
                $next = $plan['next'] ?? null;

                return [
                    'id' => $delivery->id,
                    'tracking' => $delivery->tracking_code,
                    'receiver' => $delivery->order?->delivery_name,
                    'destination_address' => $delivery->order?->delivery_municipality_name
                        ?: $delivery->order?->delivery_address,
                    'destination' => $delivery->destinationSortingCenter?->name,
                    'next_hop' => $next?->name,
                    'next_hop_type' => $next?->type,
                    'leg' => $plan['leg'] ?? null,
                    'sortable' => (bool) ($plan['valid'] ?? false)
                        && ! ($plan['complete'] ?? false)
                        && $next !== null,
                    'issue' => $plan['reason'] ?? null,
                ];
            });

        $readyRows = $this->readyQuery($center)
            ->with(['order', 'destinationSortingCenter.parent'])
            ->latest('sorted_at')
            ->get()
            ->map(function (Delivery $delivery) use ($routing) {
                $plan = $routing->transferPlan($delivery);
                $next = $plan['next'] ?? null;

                if (! ($plan['valid'] ?? false) || ($plan['complete'] ?? false) || ! $next) {
                    return null;
                }

                return [
                    'next_id' => (int) $next->id,
                    'next_name' => $next->name,
                    'next_type' => $next->type,
                    'leg' => $plan['leg'] ?? null,
                    'parcel' => [
                        'id' => $delivery->id,
                        'tracking' => $delivery->tracking_code,
                        'receiver' => $delivery->order?->delivery_name,
                        'address' => collect([
                            $delivery->order?->delivery_barangay_name,
                            $delivery->order?->delivery_municipality_name,
                        ])->filter()->implode(', ') ?: $delivery->order?->delivery_address,
                        'final_destination' => $delivery->destinationSortingCenter?->name,
                        'cod' => $delivery->order?->payment_method === 'cod'
                            ? (float) $delivery->order?->total_amount
                            : null,
                    ],
                ];
            })
            ->filter();

        $ready = $readyRows
            ->groupBy('next_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'next_id' => (int) $first['next_id'],
                    'next_name' => $first['next_name'],
                    'next_type' => $first['next_type'],
                    'leg' => $first['leg'],
                    'parcels' => $rows->pluck('parcel')->values(),
                ];
            })
            ->values();

        $manifestRows = ParcelManifest::with(['toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->where('from_sorting_center_id', $center->id)
            ->latest('id')
            ->limit(40)
            ->get();

        $manifestIds = $manifestRows->pluck('id');
        $manifestCounts = $manifestIds->isEmpty() ? collect() : DB::table('parcel_manifest_items')
            ->whereIn('manifest_id', $manifestIds)
            ->selectRaw('manifest_id, COUNT(*) as expected, COUNT(loaded_at) as loaded, COUNT(received_at) as received')
            ->groupBy('manifest_id')
            ->get()
            ->keyBy('manifest_id');

        $manifests = $manifestRows->map(function (ParcelManifest $manifest) use ($manifestCounts) {
            $counts = $manifestCounts->get($manifest->id);

            return [
                'id' => $manifest->id,
                'code' => $manifest->code,
                'to' => $manifest->toCenter?->name,
                'to_type' => $manifest->toCenter?->type,
                'count' => (int) ($counts->expected ?? $manifest->parcel_count),
                'loaded' => (int) ($counts->loaded ?? 0),
                'received' => (int) ($counts->received ?? 0),
                'status' => $manifest->status,
                'trip_code' => $manifest->linehaulTrip?->code,
                'trip_status' => $manifest->linehaulTrip?->status,
                'driver' => $manifest->linehaulTrip?->driver?->name,
                'vehicle' => $manifest->linehaulTrip?->vehicle?->code,
                'plate' => $manifest->linehaulTrip?->vehicle?->plate_number,
                'created' => optional($manifest->prepared_at ?: $manifest->created_at)?->format('M d, g:i A'),
            ];
        });

        $drivers = LinehaulDriver::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $vehicles = FleetVehicle::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('home_hub_id', $center->id)
            ->where('status', FleetVehicle::AVAILABLE)
            ->orderByDesc('is_primary')->orderBy('code')->get();

        $stats = [
            'awaiting' => $awaiting->count(),
            'not_departed' => $notDeparted->count(),
            'to_sort' => $toSort->where('sortable', true)->count(),
            'ready' => $ready->sum(fn ($group) => collect($group['parcels'])->count()),
        ];

        return view('hub.parcel.parcels', compact(
            'center', 'awaiting', 'notDeparted', 'toSort', 'ready', 'manifests', 'drivers', 'vehicles', 'stats'
        ));
    }

    /** Receive one parcel physically arriving at the Province Hub. */
    public function receiveParcel(Request $request, ParcelScanService $scanner): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $staff = $this->staff();
        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:60'],
        ]);

        $operatorUserId = $this->partnerUserId($center);

        try {
            $scanner->inspect(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                $data['tracking_code']
            );

            $result = $scanner->receive(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                $operatorUserId,
                $data['tracking_code'],
                (int) $staff->id
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('logistics.hub.parcels')
            ->with('status', "Parcel {$result['tracking_code']} received at {$center->name}. Next step: validate its required next hub/station.");
    }

    /** Confirm/validate the required next hierarchy hop after hub receipt. */
    public function sortParcels(Request $request, LogisticsRoutingService $routing): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $staff = $this->staff();
        $data = $request->validate([
            'parcel_ids' => ['required', 'array', 'min:1', 'max:200'],
            'parcel_ids.*' => ['integer'],
        ], ['parcel_ids.required' => 'Pumili muna ng parcel na ise-sort.']);

        $operatorUserId = $this->partnerUserId($center);
        $sorted = 0;
        $skipped = 0;

        DB::transaction(function () use ($center, $staff, $operatorUserId, $data, $routing, &$sorted, &$skipped) {
            $deliveries = Delivery::with(['order', 'destinationSortingCenter.parent'])
                ->where('logistics_partner_id', $center->logistics_partner_id)
                ->where('current_sorting_center_id', $center->id)
                ->where('status', Delivery::SORTED)
                ->whereIn('id', $data['parcel_ids'])
                ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_TO_RECEIVE))
                ->lockForUpdate()
                ->get();

            foreach ($deliveries as $delivery) {
                $plan = $routing->transferPlan($delivery);
                if (! ($plan['valid'] ?? false) || ($plan['complete'] ?? false) || ! ($plan['next'] ?? null)) {
                    $skipped++;
                    continue;
                }

                $incoming = ParcelTransfer::where('delivery_id', $delivery->id)
                    ->where('to_sorting_center_id', $center->id)
                    ->where('status', ParcelTransfer::RECEIVED)
                    ->latest('received_at')
                    ->lockForUpdate()
                    ->first();

                if (! $incoming || ParcelTransfer::where('delivery_id', $delivery->id)
                    ->where('status', ParcelTransfer::IN_TRANSIT)->exists()) {
                    $skipped++;
                    continue;
                }

                $alreadySorted = DB::table('parcel_scan_events')
                    ->where('delivery_id', $delivery->id)
                    ->where('sorting_center_id', $center->id)
                    ->where('event_type', 'hub_sorted')
                    ->exists();

                if ($alreadySorted) {
                    $skipped++;
                    continue;
                }

                DB::table('parcel_scan_events')->insert([
                    'delivery_id' => $delivery->id,
                    'sorting_center_id' => $center->id,
                    'operator_user_id' => $operatorUserId,
                    'operator_staff_id' => $staff->id,
                    'parcel_transfer_id' => $incoming->id,
                    'event_type' => 'hub_sorted',
                    'scanned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $delivery->forceFill(['sorted_at' => now()])->save();
                $sorted++;
            }

            $skipped += count($data['parcel_ids']) - $deliveries->count();
        }, 3);

        if ($sorted === 0) {
            return $this->parcelFail('Walang na-sort. Posibleng invalid ang hub hierarchy, hindi pa received, o nagbago na ang parcel state.');
        }

        $message = "{$sorted} parcel(s) na-sort at validated ang required next hub/station.";
        if ($skipped > 0) {
            $message .= " {$skipped} ang nilaktawan.";
        }

        return redirect()->route('logistics.hub.parcels')->with('status', $message);
    }

    /** Create one outbound manifest for parcels going to ONE required next hub/station. */
    public function storeManifest(Request $request, LogisticsRoutingService $routing): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $data = $request->validate([
            'next_sorting_center_id' => ['required', 'integer'],
            'parcel_ids' => ['required', 'array', 'min:1', 'max:200'],
            'parcel_ids.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ], ['parcel_ids.required' => 'Pumili muna ng parcel para sa manifest.']);

        $target = $this->networkCenter($center, (int) $data['next_sorting_center_id']);
        $operatorUserId = $this->partnerUserId($center);

        return DB::transaction(function () use ($center, $target, $data, $operatorUserId, $routing) {
            $candidates = $this->readyQuery($center)
                ->with(['destinationSortingCenter.parent'])
                ->whereIn('id', $data['parcel_ids'])
                ->lockForUpdate()->get();

            $deliveries = $candidates->filter(function (Delivery $delivery) use ($routing, $target) {
                $plan = $routing->transferPlan($delivery);
                return ($plan['valid'] ?? false)
                    && ! ($plan['complete'] ?? false)
                    && (int) (($plan['next']?->id) ?? 0) === (int) $target->id;
            })->values();

            if ($deliveries->isEmpty()) {
                return $this->parcelFail('Walang eligible na parcel para sa required next hop.');
            }

            $manifest = ParcelManifest::create([
                'code' => $this->manifestCode($center),
                'logistics_partner_id' => $center->logistics_partner_id,
                'from_sorting_center_id' => $center->id,
                'to_sorting_center_id' => $target->id,
                'created_by_user_id' => $operatorUserId,
                'transit_rider_id' => null,
                'linehaul_trip_id' => null,
                'status' => ParcelManifest::PREPARED,
                'parcel_count' => $deliveries->count(),
                'note' => $data['note'] ?? null,
                'prepared_at' => now(),
            ]);

            DB::table('parcel_manifest_items')->insert($deliveries->map(fn ($delivery) => [
                'manifest_id' => $manifest->id,
                'delivery_id' => $delivery->id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());

            $skipped = count($data['parcel_ids']) - $deliveries->count();
            $message = "Nagawa ang {$manifest->code}: {$deliveries->count()} parcel papunta sa {$target->name}. I-verify ang physical batch count bago i-confirm ang loading.";
            if ($skipped > 0) $message .= " {$skipped} ang nilaktawan.";
            return redirect()->route('logistics.hub.parcels')->with('status', $message);
        }, 3);
    }

    public function loadManifest(Request $request, int $manifest, ManifestLoadingService $loading): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $staff = $this->staff();
        $data = $request->validate(['physical_count' => ['required', 'integer', 'min:1', 'max:5000']]);

        try {
            $result = $loading->confirmBatchLoad(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                $manifest,
                (int) $data['physical_count'],
                $this->partnerUserId($center),
                (int) $staff->id,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $message = "Confirmed complete load for {$result['manifest_code']} ({$result['loaded']}/{$result['expected']}). Assign a Linehaul vehicle and driver.";
        return redirect()->route('logistics.hub.parcels')->with('status', $message);
    }

    public function assignLinehaul(Request $request, int $manifest, LinehaulService $linehaul): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $data = $request->validate([
            'driver_id' => ['required', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'trip_note' => ['nullable', 'string', 'max:255'],
        ]);

        $record = ParcelManifest::whereKey($manifest)
            ->where('from_sorting_center_id', $center->id)
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->firstOrFail();

        try {
            $trip = $linehaul->assignManifest(
                $record,
                (int) $data['driver_id'],
                (int) $data['vehicle_id'],
                $this->partnerUserId($center),
                $data['trip_note'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('logistics.hub.parcels')
            ->with('status', "Linehaul {$trip->code} assigned. Driver must scan manifest {$record->code} and Accept Load & Depart.");
    }

    /** Dispatch is now owned by the assigned Linehaul Driver manifest scan. */
    public function dispatchManifest(Request $request, int $manifest): RedirectResponse
    {
        return $this->parcelFail('Direct web dispatch is disabled. Fully load the manifest, assign Linehaul, then the assigned Driver must scan the Manifest QR and Accept Load & Depart.');
    }

    public function cancelManifest(Request $request, int $manifest): RedirectResponse
    {
        $center = $this->center(mutating: true);

        return DB::transaction(function () use ($center, $manifest) {
            $record = ParcelManifest::whereKey($manifest)
                ->where('from_sorting_center_id', $center->id)
                ->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING, ParcelManifest::READY])
                ->whereNull('linehaul_trip_id')
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return $this->parcelStale();
            }

            DB::table('parcel_manifest_items')->where('manifest_id', $record->id)->delete();
            $record->update(['status' => ParcelManifest::CANCELLED, 'parcel_count' => 0]);

            return redirect()->route('logistics.hub.parcels')
                ->with('status', "Na-cancel ang {$record->code}. Bumalik sa ready list ang parcels.");
        }, 3);
    }

    public function printManifest(Request $request, int $manifest): View
    {
        $center = $this->center();

        $record = ParcelManifest::with(['toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->whereKey($manifest)
            ->where('from_sorting_center_id', $center->id)
            ->firstOrFail();

        $items = DB::table('parcel_manifest_items as i')
            ->join('deliveries as d', 'd.id', '=', 'i.delivery_id')
            ->join('orders as o', 'o.id', '=', 'd.order_id')
            ->where('i.manifest_id', $record->id)
            ->orderBy('d.tracking_code')
            ->get([
                'd.tracking_code', 'o.delivery_name', 'o.delivery_barangay_name',
                'o.delivery_municipality_name', 'o.payment_method', 'o.total_amount', 'i.loaded_at', 'i.received_at',
            ]);

        return view('hub.parcel.manifest', compact('center', 'record', 'items'));
    }

    /* ---------------------------------------------------------------
     | Station hubs list
     * ------------------------------------------------------------- */
    public function stationHubs(Request $request): View
    {
        $center = $this->center();
        $search = trim((string) $request->query('search', ''));

        $stationHubs = $center->stationHubs()
            ->whereNull('archived_at')
            ->with('user')
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('municipality', 'like', "%{$search}%");
            }))
            ->latest()
            ->get();

        return view('hub.branch.station-hubs', compact('center', 'stationHubs', 'search'));
    }

    /* ---------------------------------------------------------------
     | Create station hub + its login account + its coverage area
     * ------------------------------------------------------------- */
    public function storeStationHub(Request $request): RedirectResponse
    {
        $center = $this->center(mutating: true);

        $data = $request->validate([
            'name'             => ['required', 'string', 'min:2', 'max:180'],
            'contact_no'       => ['nullable', 'string', 'max:30'],
            'municipality'     => ['required', 'string', 'max:180',
                Rule::unique('sorting_centers', 'municipality')->where(
                    fn ($q) => $q->where('parent_id', $center->id)->whereNull('archived_at')
                )],
            'barangay'         => ['required', 'string', 'max:180'],
            'street_no'        => ['required', 'string', 'max:180'],
            'unit_no'          => ['nullable', 'string', 'max:180'],
            'manager_email'    => ['required', 'email', 'max:190'],
            'manager_password' => ['required', 'string', 'max:100', Password::min(8)->letters()->numbers()],
        ], [
            'municipality.unique' => 'May station hub na para sa city/municipality na ito.',
        ]);

        $email = strtolower(trim($data['manager_email']));
        BranchStaffController::assertNewEmail($email); // email must not belong to any ShopHop account

        DB::transaction(function () use ($center, $data, $email) {
            $account = new User();
            $account->forceFill([
                'email'             => $email,
                'password'          => Hash::make($data['manager_password']),
                'account_type'      => 'branch',
                'status'            => 'approved',
                'email_verified_at' => now(),
                'reviewed_at'       => now(),
                'notes'             => 'Created by province hub ' . $center->code . ' (' . $center->name . ').',
            ])->save();

            $seq = $center->stationHubs()->count() + 1;
            do {
                $code = $center->code . '-S' . str_pad((string) $seq++, 2, '0', STR_PAD_LEFT);
            } while (SortingCenter::where('code', $code)->exists());

            $station = SortingCenter::create([
                'logistics_partner_id' => $center->logistics_partner_id,
                'user_id'              => $account->id,
                'type'                 => 'station_hub',
                'parent_id'            => $center->id,
                'code'                 => $code,
                'name'                 => trim($data['name']),
                'is_main'              => false,
                'status'               => 'active',
                'contact_no'           => $data['contact_no'] ?? null,
                'region'               => $center->region,
                'province'             => $center->province,
                'municipality'         => $data['municipality'],
                'barangay'             => $data['barangay'],
                'street_no'            => $data['street_no'],
                'unit_no'              => $data['unit_no'] ?? null,
            ]);

            // Coverage: ang station hub ang sumasakop sa city niya (pickup at delivery).
            LogisticsCoverageArea::create([
                'logistics_partner_id' => $center->logistics_partner_id,
                'sorting_center_id'    => $station->id,
                'area_name'            => $center->province,
                'area_type'            => 'province',
                'cities'               => $data['municipality'],
            ]);
        });

        return redirect()->route('logistics.hub.station-hubs')
            ->with('status', 'Station hub created.')
            ->with('new_staff_credentials', ['email' => $email, 'password' => $data['manager_password']]);
    }

    /* ---------------------------------------------------------------
     | Province manager sets a station hub's password
     * ------------------------------------------------------------- */
    public function setStationPassword(Request $request, int $stationHub): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $hub = $this->ownStation($center, $stationHub);

        $data = $request->validateWithBag('setPassword', [
            'password' => ['required', 'string', 'max:100', Password::min(8)->letters()->numbers()],
        ]);

        $account = $hub->user;
        abort_unless($account, 404, 'This station hub has no login account.');

        DB::transaction(function () use ($account, $data) {
            $account->forceFill(['password' => Hash::make($data['password'])])->save();
            if (method_exists($account, 'tokens')) {
                $account->tokens()->delete();
            }
        });

        return redirect()->route('logistics.hub.station-hubs')
            ->with('status', "Password updated for {$hub->name}.")
            ->with('new_staff_credentials', ['email' => $account->email, 'password' => $data['password']]);
    }

    /* ---------------------------------------------------------------
     | Activate / deactivate a station hub
     * ------------------------------------------------------------- */
    public function toggleStationHub(int $stationHub): RedirectResponse
    {
        $center = $this->center(mutating: true);
        $hub = $this->ownStation($center, $stationHub);

        $target = $hub->status === 'active' ? 'inactive' : 'active';

        DB::transaction(function () use ($hub, $target) {
            $hub->update([
                'status' => $target,
                'deactivated_at' => $target === 'inactive' ? now() : null,
            ]);
            if ($target === 'inactive') {
                $hub->staff->each(fn ($s) => $s->tokens()->delete());
                if ($hub->user && method_exists($hub->user, 'tokens')) {
                    $hub->user->tokens()->delete();
                }
            }
        });

        return back()->with('status', $target === 'active' ? 'Station hub activated.' : 'Station hub deactivated.');
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    private function staff(): \App\Models\Logistics\BranchStaff
    {
        $staff = Auth::guard('logistics_staff')->user();
        abort_unless($staff, 403, 'Province Hub staff login required.');
        return $staff;
    }

    /** parcel_scan_events / manifests still keep the partner owner User as legacy actor. */
    private function partnerUserId(SortingCenter $center): int
    {
        $userId = $center->partner()->value('user_id');
        abort_unless($userId, 422, 'Logistics partner owner account is missing.');
        return (int) $userId;
    }

    private function activeRiders(SortingCenter $center)
    {
        return Rider::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('sorting_center_id', $center->id)
            ->where('status', 'active')
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->orderBy('name');
    }

    /** Sorted+hub-validated parcels; includes parcels currently held in a prepared manifest. */
    private function baseReadyQuery(SortingCenter $center)
    {
        return Delivery::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('current_sorting_center_id', $center->id)
            ->where('status', Delivery::SORTED)
            ->whereNotNull('destination_sorting_center_id')
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_TO_RECEIVE))
            ->whereExists(function ($q) use ($center) {
                $q->selectRaw('1')
                    ->from('parcel_scan_events as pse')
                    ->whereColumn('pse.delivery_id', 'deliveries.id')
                    ->where('pse.sorting_center_id', $center->id)
                    ->where('pse.event_type', 'hub_sorted');
            })
            ->whereDoesntHave('transfers', fn ($q) => $q->where('status', ParcelTransfer::IN_TRANSIT))
            ->whereDoesntHave('returnCase');
    }

    /** Ready parcels not yet reserved by another prepared manifest. */
    private function readyQuery(SortingCenter $center)
    {
        return $this->baseReadyQuery($center)->whereNotIn('id', function ($q) {
            $q->select('i.delivery_id')
                ->from('parcel_manifest_items as i')
                ->join('parcel_manifests as m', 'm.id', '=', 'i.manifest_id')
                ->whereIn('m.status', [ParcelManifest::PREPARED, ParcelManifest::LOADING, ParcelManifest::READY]);
        });
    }

    /** Any active routing center in the same Logistics network. Route-plan validation decides whether it is the allowed next hop. */
    private function networkCenter(SortingCenter $center, int $id): SortingCenter
    {
        return SortingCenter::query()
            ->whereKey($id)
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->whereIn('type', ['province_hub', 'station_hub'])
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->firstOrFail();
    }

    private function manifestCode(SortingCenter $center): string
    {
        $prefix = 'MF-'.now()->format('Ymd').'-H'.str_pad((string) $center->id, 3, '0', STR_PAD_LEFT);
        $number = ParcelManifest::where('code', 'like', $prefix.'-%')->count() + 1;

        do {
            $code = $prefix.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $number++;
        } while (ParcelManifest::where('code', $code)->exists());

        return $code;
    }

    private function parcelFail(string $message): RedirectResponse
    {
        return redirect()->route('logistics.hub.parcels')->withErrors(['parcels' => $message]);
    }

    private function parcelStale(): RedirectResponse
    {
        return $this->parcelFail('Nagbago na ang manifest o hindi na ito eligible. I-refresh at tingnan ulit.');
    }

    /** The logged-in manager's own province hub. */
    private function center(bool $mutating = false): SortingCenter
    {
        $staff = Auth::guard('logistics_staff')->user();
        $center = $staff?->sortingCenter;

        abort_unless($center, 404);
        abort_if($center->parent_id !== null || $center->type !== 'province_hub', 403);

        if ($mutating) {
            abort_if($center->archived_at || $center->status !== 'active', 403, 'Your province hub is not active.');
        }

        return $center;
    }

    /** Only a station hub that belongs to this province hub. */
    private function ownStation(SortingCenter $center, int $id): SortingCenter
    {
        return $center->stationHubs()->whereNull('archived_at')->with('user')->findOrFail($id);
    }
}