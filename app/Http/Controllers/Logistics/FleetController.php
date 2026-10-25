<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\Rider;
use App\Models\Logistics\BranchStaff;
use App\Models\User;
use App\Models\Logistics\SortingCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FleetController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $request->user()->logisticsPartner()->firstOrFail();

        $provinceHubs = SortingCenter::where('logistics_partner_id', $partner->id)
            ->where('type', 'province_hub')
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get();

        $vehicles = FleetVehicle::with('homeHub')
            ->where('logistics_partner_id', $partner->id)
            ->orderByDesc('is_primary')
            ->orderBy('code')
            ->get();

        $drivers = LinehaulDriver::where('logistics_partner_id', $partner->id)
            ->orderBy('name')
            ->get();

        $trips = LinehaulTrip::with(['fromCenter', 'toCenter', 'vehicle', 'driver', 'manifests'])
            ->where('logistics_partner_id', $partner->id)
            ->latest('id')
            ->limit(40)
            ->get();

        return view('logistics.fleet.index', compact('partner', 'provinceHubs', 'vehicles', 'drivers', 'trips'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $partner = $request->user()->logisticsPartner()->firstOrFail();
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('fleet_vehicles', 'code')],
            'plate_number' => ['required', 'string', 'max:40', Rule::unique('fleet_vehicles', 'plate_number')],
            'vehicle_type' => ['required', Rule::in(['van', 'light_truck', '6_wheeler', '10_wheeler', 'other'])],
            'capacity_kg' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'volume_capacity_m3' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
            'home_hub_id' => ['required', 'integer'],
            'owner_type' => ['required', Rule::in(['company', 'contracted'])],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $hub = SortingCenter::whereKey($data['home_hub_id'])
            ->where('logistics_partner_id', $partner->id)
            ->where('type', 'province_hub')
            ->whereNull('archived_at')
            ->firstOrFail();

        if (! empty($data['is_primary'])) {
            FleetVehicle::where('logistics_partner_id', $partner->id)
                ->where('home_hub_id', $hub->id)
                ->update(['is_primary' => false]);
        }

        FleetVehicle::create([
            ...$data,
            'logistics_partner_id' => $partner->id,
            'home_hub_id' => $hub->id,
            'is_primary' => ! empty($data['is_primary']),
            'status' => FleetVehicle::AVAILABLE,
            'code' => strtoupper(trim($data['code'])),
            'plate_number' => strtoupper(trim($data['plate_number'])),
        ]);

        return back()->with('status', 'Vehicle registered successfully.');
    }

    public function updateVehicleStatus(Request $request, FleetVehicle $vehicle): RedirectResponse
    {
        $partner = $request->user()->logisticsPartner()->firstOrFail();
        abort_unless((int) $vehicle->logistics_partner_id === (int) $partner->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([FleetVehicle::AVAILABLE, FleetVehicle::MAINTENANCE, FleetVehicle::INACTIVE])],
        ]);

        if (LinehaulTrip::where('vehicle_id', $vehicle->id)
            ->whereIn('status', [LinehaulTrip::SCHEDULED, LinehaulTrip::IN_TRANSIT, LinehaulTrip::ARRIVED])
            ->exists()) {
            return back()->withErrors(['fleet' => 'Vehicle has an active Linehaul Trip and cannot be changed manually.']);
        }

        $vehicle->update(['status' => $data['status']]);
        return back()->with('status', 'Vehicle status updated.');
    }

    public function storeDriver(Request $request): RedirectResponse
    {
        $partner = $request->user()->logisticsPartner()->firstOrFail();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_no' => ['nullable', 'string', 'max:40'],
            'license_no' => ['nullable', 'string', 'max:80'],
            'license_expiry' => ['nullable', 'date'],
            'email' => ['required', 'email', 'max:190', Rule::unique('linehaul_drivers', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = strtolower(trim($data['email']));
        if (User::where('email', $email)->exists()
            || Rider::where('email', $email)->exists()
            || BranchStaff::where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'This email is already used by another ShopHop account.'])->withInput();
        }

        LinehaulDriver::create([
            'logistics_partner_id' => $partner->id,
            'name' => trim($data['name']),
            'contact_no' => $data['contact_no'] ?? null,
            'license_no' => $data['license_no'] ?? null,
            'license_expiry' => $data['license_expiry'] ?? null,
            'email' => $email,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        return back()->with('status', 'Linehaul Driver registered. The driver can now sign in on ShopHop mobile.');
    }

    public function toggleDriver(Request $request, LinehaulDriver $driver): RedirectResponse
    {
        $partner = $request->user()->logisticsPartner()->firstOrFail();
        abort_unless((int) $driver->logistics_partner_id === (int) $partner->id, 404);

        if ($driver->status === 'active'
            && LinehaulTrip::where('driver_id', $driver->id)
                ->whereIn('status', [LinehaulTrip::SCHEDULED, LinehaulTrip::IN_TRANSIT, LinehaulTrip::ARRIVED])
                ->exists()) {
            return back()->withErrors(['fleet' => 'Driver has an active Linehaul Trip and cannot be deactivated.']);
        }

        $driver->update(['status' => $driver->status === 'active' ? 'inactive' : 'active']);
        $driver->tokens()->delete();

        return back()->with('status', 'Linehaul Driver status updated.');
    }
}
