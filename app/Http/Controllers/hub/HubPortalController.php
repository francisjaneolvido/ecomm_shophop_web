<?php

namespace App\Http\Controllers\hub;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logistics\BranchStaffController;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     | Create station hub + its login account
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
            // The station hub's login account (same pattern as other branch accounts).
            $account = new User();
            // `users` has no `name` column: the station hub's name lives on sorting_centers.name.
            $account->forceFill([
                'email'             => $email,
                // Safe with or without a 'hashed' cast: an already-hashed value is not re-hashed.
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

            SortingCenter::create([
                'logistics_partner_id' => $center->logistics_partner_id,
                'user_id'              => $account->id,
                'type'                 => 'station_hub',
                'parent_id'            => $center->id,
                'code'                 => $code,
                'name'                 => trim($data['name']),
                'is_main'              => false,
                'status'               => 'active',
                'contact_no'           => $data['contact_no'] ?? null,
                'region'               => $center->region,     // galing sa province hub
                'province'             => $center->province,
                'municipality'         => $data['municipality'],
                'barangay'             => $data['barangay'],
                'street_no'            => $data['street_no'],
                'unit_no'              => $data['unit_no'] ?? null,
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
                $account->tokens()->delete(); // revoke existing mobile sessions
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
        // A station hub can never create further station hubs.
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