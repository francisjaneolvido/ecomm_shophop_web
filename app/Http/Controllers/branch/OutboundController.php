<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Services\LinehaulService;
use App\Services\LogisticsRoutingService;
use App\Services\ManifestLoadingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(Request $request, LogisticsRoutingService $routing): View
    {
        $center  = $this->center($request);
        $partner = LogisticsPartner::with('coverageAreas')->findOrFail($center->logistics_partner_id);
        $hub     = $this->hub($center);
        $names   = SortingCenter::where('logistics_partner_id', $center->logistics_partner_id)->pluck('name', 'id');

        $toSort = Delivery::with('order')
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('current_sorting_center_id', $center->id)
            ->where('status', Delivery::AT_SORTING_CENTER)
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_TO_RECEIVE))
            ->latest('at_sorting_center_at')
            ->get()
            ->map(function (Delivery $d) use ($routing, $partner, $center, $names) {
                $dest = $this->resolveDestination($routing, $partner, $d->order);
                return [
                    'id' => $d->id,
                    'tracking' => $d->tracking_code,
                    'receiver' => $d->order?->delivery_name,
                    'barangay' => $d->order?->delivery_barangay_name ?: $d->order?->delivery_address,
                    'sortable' => (bool) $dest,
                    'local' => $dest && (int) $dest['center_id'] === (int) $center->id,
                    'destination' => $dest ? ($names[$dest['center_id']] ?? '-') : null,
                ];
            });

        $ready = $this->readyQuery($center)->with('order')->latest('sorted_at')->get()
            ->map(fn (Delivery $d) => [
                'id' => $d->id,
                'tracking' => $d->tracking_code,
                'receiver' => $d->order?->delivery_name,
                'barangay' => $d->order?->delivery_barangay_name ?: $d->order?->delivery_address,
                'final' => $names[$d->destination_sorting_center_id] ?? '-',
                'cod' => $d->order && $d->order->payment_method === 'cod' ? (float) $d->order->total_amount : null,
            ]);

        $manifestModels = ParcelManifest::with([
                'toCenter',
                'linehaulTrip.vehicle',
                'linehaulTrip.driver',
            ])
            ->where('from_sorting_center_id', $center->id)
            ->latest('id')
            ->limit(30)
            ->get();

        $manifestIds = $manifestModels->pluck('id');
        $counts = $manifestIds->isEmpty() ? collect() : DB::table('parcel_manifest_items')
            ->whereIn('manifest_id', $manifestIds)
            ->selectRaw('manifest_id, COUNT(*) as expected, COUNT(loaded_at) as loaded, COUNT(received_at) as received')
            ->groupBy('manifest_id')
            ->get()
            ->keyBy('manifest_id');

        $manifests = $manifestModels->map(function (ParcelManifest $m) use ($counts) {
            $c = $counts->get($m->id);
            return [
                'id' => $m->id,
                'code' => $m->code,
                'to' => $m->toCenter?->name,
                'count' => (int) ($c->expected ?? $m->parcel_count),
                'loaded' => (int) ($c->loaded ?? 0),
                'received' => (int) ($c->received ?? 0),
                'status' => $m->status,
                'trip_code' => $m->linehaulTrip?->code,
                'trip_status' => $m->linehaulTrip?->status,
                'driver' => $m->linehaulTrip?->driver?->name,
                'vehicle' => $m->linehaulTrip?->vehicle?->code,
                'plate' => $m->linehaulTrip?->vehicle?->plate_number,
                'created' => Carbon::parse($m->created_at)->format('M d, g:i A'),
            ];
        });

        $stats = [
            'to_sort' => $toSort->count(),
            'ready' => $ready->count(),
            'prepared' => $manifests->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING, ParcelManifest::READY])->count(),
            'in_transit' => $manifests->where('status', ParcelManifest::DISPATCHED)->count(),
        ];

        $drivers = LinehaulDriver::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('status', 'active')->orderBy('name')->get(['id','name']);

        $vehicles = $hub ? FleetVehicle::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('home_hub_id', $hub->id)
            ->where('status', FleetVehicle::AVAILABLE)
            ->orderByDesc('is_primary')->orderBy('code')->get() : collect();

        return view('branch.outbound.index', compact(
            'center', 'hub', 'toSort', 'ready', 'manifests', 'stats', 'drivers', 'vehicles'
        ));
    }

    public function sort(Request $request, LogisticsRoutingService $routing): RedirectResponse
    {
        $center  = $this->center($request);
        $partner = LogisticsPartner::with('coverageAreas')->findOrFail($center->logistics_partner_id);
        $data = $request->validate([
            'parcel_ids' => ['required', 'array', 'min:1', 'max:200'],
            'parcel_ids.*' => ['integer'],
        ], ['parcel_ids.required' => 'Pumili muna ng kahit isang parcel.']);

        $sorted = 0;
        $skipped = 0;

        DB::transaction(function () use ($center, $partner, $routing, $data, &$sorted, &$skipped) {
            $deliveries = Delivery::with('order')
                ->where('logistics_partner_id', $center->logistics_partner_id)
                ->where('current_sorting_center_id', $center->id)
                ->where('status', Delivery::AT_SORTING_CENTER)
                ->whereIn('id', $data['parcel_ids'])
                ->lockForUpdate()->get();

            foreach ($deliveries as $d) {
                $dest = $d->order?->status === Order::STATUS_TO_RECEIVE
                    ? $this->resolveDestination($routing, $partner, $d->order)
                    : null;
                if (! $dest) { $skipped++; continue; }

                $d->update([
                    'destination_area_id' => $dest['area_id'],
                    'destination_sorting_center_id' => $dest['center_id'],
                    'status' => Delivery::SORTED,
                    'sorted_at' => now(),
                ]);
                $sorted++;
            }
            $skipped += count($data['parcel_ids']) - $deliveries->count();
        });

        if ($sorted === 0) return $this->fail('Walang na-sort. Walang coverage area para sa address, o nagbago na ang status.');
        $message = "{$sorted} parcel(s) na-sort.";
        if ($skipped > 0) $message .= " {$skipped} ang nilaktawan.";
        return redirect()->route('branch.outbound.index')->with('status', $message);
    }

    public function store(Request $request, LogisticsRoutingService $routing): RedirectResponse
    {
        $center = $this->center($request);
        $hub = $this->hub($center);
        if (! $hub) return $this->fail('Walang aktibong Province Hub na naka-link sa branch na ito.');

        $data = $request->validate([
            'parcel_ids' => ['required', 'array', 'min:1', 'max:200'],
            'parcel_ids.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ], ['parcel_ids.required' => 'Pumili muna ng kahit isang parcel.']);

        return DB::transaction(function () use ($request, $center, $hub, $data, $routing) {
            $candidates = $this->readyQuery($center)
                ->with(['destinationSortingCenter.parent'])
                ->whereIn('id', $data['parcel_ids'])
                ->lockForUpdate()->get();

            $deliveries = $candidates->filter(function (Delivery $delivery) use ($routing, $hub) {
                $plan = $routing->transferPlan($delivery);
                return ($plan['valid'] ?? false)
                    && ! ($plan['complete'] ?? false)
                    && (int) (($plan['next']?->id) ?? 0) === (int) $hub->id;
            })->values();

            if ($deliveries->isEmpty()) return $this->fail('Walang eligible na parcel para sa required Province Hub.');

            $manifest = ParcelManifest::create([
                'code' => $this->manifestCode($center),
                'logistics_partner_id' => $center->logistics_partner_id,
                'from_sorting_center_id' => $center->id,
                'to_sorting_center_id' => $hub->id,
                'created_by_user_id' => $request->user()->id,
                'transit_rider_id' => null,
                'linehaul_trip_id' => null,
                'status' => ParcelManifest::PREPARED,
                'parcel_count' => $deliveries->count(),
                'note' => $data['note'] ?? null,
                'prepared_at' => now(),
            ]);

            DB::table('parcel_manifest_items')->insert($deliveries->map(fn ($d) => [
                'manifest_id' => $manifest->id, 'delivery_id' => $d->id,
                'created_at' => now(), 'updated_at' => now(),
            ])->all());

            $skipped = count($data['parcel_ids']) - $deliveries->count();
            $message = "Nagawa ang {$manifest->code} ({$deliveries->count()} parcel). I-verify ang physical count ng batch bago i-confirm ang loading.";
            if ($skipped > 0) $message .= " {$skipped} ang nilaktawan.";
            return redirect()->route('branch.outbound.index')->with('status', $message);
        }, 3);
    }

    public function loadManifest(
        Request $request,
        int $manifest,
        ManifestLoadingService $loading
    ): RedirectResponse {
        $center = $this->center($request);
        $data = $request->validate(['physical_count' => ['required', 'integer', 'min:1', 'max:5000']]);

        try {
            $result = $loading->confirmBatchLoad(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                $manifest,
                (int) $data['physical_count'],
                (int) $request->user()->id,
                null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $message = "Confirmed complete load for {$result['manifest_code']} ({$result['loaded']}/{$result['expected']}). Assign a Linehaul vehicle and driver.";
        return redirect()->route('branch.outbound.index')->with('status', $message);
    }

    public function assignLinehaul(
        Request $request,
        int $manifest,
        LinehaulService $linehaul
    ): RedirectResponse {
        $center = $this->center($request);
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
                (int) $request->user()->id,
                $data['trip_note'] ?? null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('branch.outbound.index')
            ->with('status', "Linehaul {$trip->code} assigned. Driver must scan manifest {$record->code} and Accept Load & Depart.");
    }

    /** Legacy URL kept safe: only the Linehaul Driver manifest scan can start transport now. */
    public function dispatchManifest(Request $request, int $manifest): RedirectResponse
    {
        return $this->fail('Direct web dispatch is disabled. Fully load the manifest, assign Linehaul, then the assigned Driver must scan the Manifest QR and Accept Load & Depart.');
    }

    public function cancelManifest(Request $request, int $manifest): RedirectResponse
    {
        $center = $this->center($request);
        return DB::transaction(function () use ($center, $manifest) {
            $m = ParcelManifest::whereKey($manifest)
                ->where('from_sorting_center_id', $center->id)
                ->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING, ParcelManifest::READY])
                ->lockForUpdate()->first();

            if (! $m || $m->linehaul_trip_id) return $this->stale();

            DB::table('parcel_manifest_items')->where('manifest_id', $m->id)->delete();
            $m->update(['status' => ParcelManifest::CANCELLED, 'parcel_count' => 0]);
            return redirect()->route('branch.outbound.index')->with('status', "Na-cancel ang {$m->code}.");
        }, 3);
    }

    public function printManifest(Request $request, int $manifest): View
    {
        $center = $this->center($request);
        $m = ParcelManifest::with(['toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->whereKey($manifest)->where('from_sorting_center_id', $center->id)->firstOrFail();

        $items = DB::table('parcel_manifest_items as i')
            ->join('deliveries as d', 'd.id', '=', 'i.delivery_id')
            ->join('orders as o', 'o.id', '=', 'd.order_id')
            ->leftJoin('sorting_centers as dc', 'dc.id', '=', 'd.destination_sorting_center_id')
            ->where('i.manifest_id', $m->id)->orderBy('d.tracking_code')
            ->get([
                'd.tracking_code', 'o.delivery_name', 'o.delivery_barangay_name', 'o.delivery_municipality_name',
                'o.payment_method', 'o.total_amount', 'dc.name as final_branch', 'i.loaded_at', 'i.received_at',
            ]);

        return view('branch.outbound.manifest', compact('center', 'm', 'items'));
    }

    private function center(Request $request): SortingCenter
    {
        $center = SortingCenter::where('user_id', $request->user()->id)
            ->where('type', 'station_hub')->where('status', 'active')->whereNull('archived_at')->first();
        abort_unless($center, 403, 'Walang aktibong branch na naka-link sa account mo.');
        return $center;
    }

    private function hub(SortingCenter $center): ?SortingCenter
    {
        if (! $center->parent_id) return null;
        return SortingCenter::where('id', $center->parent_id)
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('type', 'province_hub')->where('status', 'active')->whereNull('archived_at')->first();
    }

    private function baseReadyQuery(SortingCenter $center)
    {
        return Delivery::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('current_sorting_center_id', $center->id)
            ->where('status', Delivery::SORTED)
            ->whereNotNull('destination_sorting_center_id')
            ->where('destination_sorting_center_id', '!=', $center->id)
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_TO_RECEIVE))
            ->whereDoesntHave('transfers', fn ($q) => $q->where('status', ParcelTransfer::IN_TRANSIT))
            ->whereDoesntHave('returnCase');
    }

    private function readyQuery(SortingCenter $center)
    {
        return $this->baseReadyQuery($center)->whereNotIn('id', function ($q) {
            $q->select('i.delivery_id')->from('parcel_manifest_items as i')
                ->join('parcel_manifests as m', 'm.id', '=', 'i.manifest_id')
                ->whereIn('m.status', [ParcelManifest::PREPARED, ParcelManifest::LOADING, ParcelManifest::READY]);
        });
    }

    private function resolveDestination(LogisticsRoutingService $routing, LogisticsPartner $partner, ?Order $order): ?array
    {
        if (! $order) return null;
        $area = $routing->destinationArea($partner, $order);
        if (! $area) return null;
        $centerId = $area->sorting_center_id ?: $partner->mainSortingCenter()->value('id');
        return $centerId ? ['area_id' => $area->id, 'center_id' => (int) $centerId] : null;
    }

    private function manifestCode(SortingCenter $center): string
    {
        $prefix = 'MF-'.now()->format('Ymd').'-'.str_pad((string) $center->id, 3, '0', STR_PAD_LEFT);
        $n = ParcelManifest::where('code', 'like', $prefix.'-%')->count() + 1;
        do { $code = $prefix.'-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT); $n++; }
        while (ParcelManifest::where('code', $code)->exists());
        return $code;
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('branch.outbound.index')->withErrors(['outbound' => $message]);
    }

    private function stale(): RedirectResponse
    {
        return $this->fail('Nagbago na ang manifest o hindi na ito eligible. I-refresh at tingnan ulit.');
    }
}
