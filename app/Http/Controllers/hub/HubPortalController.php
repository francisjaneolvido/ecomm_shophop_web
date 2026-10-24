<?php

namespace App\Http\Controllers\hub;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logistics\BranchStaffController;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\User;
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
                'title' => $r->event_type === 'pickup_received'
                    ? "Pickup received at {$r->center}"
                    : "Transfer received at {$r->center}",
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

    /** The logged-in manager's own province hub. */
    private function center(bool $mutating = false): SortingCenter
    {
        $staff = Auth::guard('logistics_staff')->user();
        $center = $staff?->sortingCenter;

        abort_unless($center, 404);
        abort_if($center->parent_id !== null, 403);

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