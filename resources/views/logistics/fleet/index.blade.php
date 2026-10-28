@extends('logistics.layouts.console')

@section('title', 'Fleet & Linehaul')

@section('content')
<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-teal">Fleet Management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-navy">Vehicles & Linehaul Drivers</h1>
            <p class="mt-2 max-w-3xl text-sm text-navy/55">
                Riders handle Seller pickup and Buyer delivery. Branch/Hub-to-Branch/Hub transport is handled by registered Linehaul vehicles and drivers through manifests and trips.
            </p>
        </div>
    </header>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-5 xl:grid-cols-2">
        <details class="sh-surface sh-collapsible-form" @if($errors->any()) open @endif>
            <summary class="sh-collapsible-summary"><span><span class="sh-kicker">Vehicle registration</span><strong>Register a linehaul vehicle</strong><small>Plate, type, capacity and home hub</small></span><x-lucide-chevron-down class="h-5 w-5" /></summary>
            <form method="POST" action="{{ route('logistics.fleet.vehicles.store') }}" class="sh-collapse-content mt-0 grid gap-3 p-4 md:grid-cols-2 sm:p-5" data-confirm-title="Register vehicle?" data-confirm-message="Create this vehicle with the selected home hub and capacity?" data-confirm-button="Register vehicle">
                @csrf
                <label class="sh-field"><span class="sh-label">Vehicle code</span><input class="sh-input" name="code" required placeholder="VH-LAG-001"></label>
                <label class="sh-field"><span class="sh-label">Plate number</span><input class="sh-input" name="plate_number" required placeholder="ABC 1234"></label>
                <label class="sh-field"><span class="sh-label">Vehicle type</span>
                    <select class="sh-input" name="vehicle_type" required>
                        <option value="van">Van</option><option value="light_truck">Light Truck</option>
                        <option value="6_wheeler">6-Wheeler</option><option value="10_wheeler">10-Wheeler</option><option value="other">Other</option>
                    </select>
                </label>
                <label class="sh-field"><span class="sh-label">Home Province Hub</span>
                    <select class="sh-input" name="home_hub_id" required>
                        <option value="">Select Province Hub</option>
                        @foreach($provinceHubs as $hub)<option value="{{ $hub->id }}">{{ $hub->name }}</option>@endforeach
                    </select>
                </label>
                <label class="sh-field"><span class="sh-label">Capacity (kg)</span><input class="sh-input" type="number" step="0.01" min="1" name="capacity_kg" placeholder="4000"></label>
                <label class="sh-field"><span class="sh-label">Volume (m³, optional)</span><input class="sh-input" type="number" step="0.01" min="0.01" name="volume_capacity_m3"></label>
                <label class="sh-field"><span class="sh-label">Ownership</span>
                    <select class="sh-input" name="owner_type"><option value="company">Company owned</option><option value="contracted">Contracted / 3PL</option></select>
                </label>
                <label class="flex items-center gap-2 self-end rounded-xl border border-gray-border px-3 py-2 text-sm"><input type="checkbox" name="is_primary" value="1"><span>Primary vehicle for this Province Hub</span></label>
                <div class="md:col-span-2"><button class="sh-btn">Register vehicle</button></div>
            </form>
        </details>

        <details class="sh-surface sh-collapsible-form" @if($errors->any()) open @endif>
            <summary class="sh-collapsible-summary"><span><span class="sh-kicker">Driver registration</span><strong>Register a linehaul driver</strong><small>License details and secure mobile login</small></span><x-lucide-chevron-down class="h-5 w-5" /></summary>
            <p class="px-4 pt-4 text-xs text-navy/55 sm:px-5">Linehaul drivers and delivery riders have different responsibilities. These credentials are for the ShopHop mobile app.</p>
            <form method="POST" action="{{ route('logistics.fleet.drivers.store') }}" class="sh-collapse-content mt-0 grid gap-3 p-4 md:grid-cols-2 sm:p-5" data-confirm-title="Register driver account?" data-confirm-message="Create a new linehaul driver and their mobile login credentials?" data-confirm-button="Register driver">
                @csrf
                <label class="sh-field"><span class="sh-label">Driver name</span><input class="sh-input" name="name" required></label>
                <label class="sh-field"><span class="sh-label">Contact</span><input class="sh-input" name="contact_no"></label>
                <label class="sh-field"><span class="sh-label">License no.</span><input class="sh-input" name="license_no"></label>
                <label class="sh-field"><span class="sh-label">License expiry</span><input class="sh-input" type="date" name="license_expiry"></label>
                <label class="sh-field"><span class="sh-label">Mobile login email</span><input class="sh-input" type="email" name="email" required></label>
                <div></div>
                <label class="sh-field"><span class="sh-label">Temporary password</span><input class="sh-input" type="password" name="password" required></label>
                <label class="sh-field"><span class="sh-label">Confirm password</span><input class="sh-input" type="password" name="password_confirmation" required></label>
                <div class="md:col-span-2"><button class="sh-btn">Register driver</button></div>
            </form>
        </details>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
            <div class="sh-ui-list-heading"><h2 class="text-lg font-bold text-navy">Vehicles</h2><label><span class="sr-only">Filter vehicles</span><input type="search" class="sh-input" data-ui-list-search="fleetVehicles" placeholder="Search plate, code or hub"></label></div>
            <div id="fleetVehicles" class="mt-4 space-y-3 sh-ui-record-stack" data-ui-collection>
                @forelse($vehicles as $vehicle)
                    <div class="rounded-xl border border-gray-border p-4 sh-ui-record" data-ui-record>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2"><span class="font-bold text-navy">{{ $vehicle->code }}</span>@if($vehicle->is_primary)<span class="rounded-full bg-teal-light px-2 py-0.5 text-[10px] font-bold text-teal-dark">PRIMARY</span>@endif</div>
                                <p class="mt-1 text-sm text-navy/65">{{ $vehicle->plate_number }} · {{ ucwords(str_replace('_',' ', $vehicle->vehicle_type)) }}</p>
                                <p class="text-xs text-navy/45">{{ $vehicle->homeHub?->name ?? 'No home hub' }} · {{ $vehicle->capacity_kg ? number_format((float)$vehicle->capacity_kg).' kg' : 'Capacity not set' }}</p>
                            </div>
                            <span class="rounded-full bg-gray-bg px-2.5 py-1 text-xs font-semibold text-navy">{{ ucfirst(str_replace('_',' ', $vehicle->status)) }}</span>
                        </div>
                        @if(!in_array($vehicle->status, ['in_transit']))
                        <form method="POST" action="{{ route('logistics.fleet.vehicles.status', $vehicle) }}" class="mt-3 flex gap-2" data-confirm-title="Update vehicle status?" data-confirm-message="The new availability status will affect fleet planning." data-confirm-subject="{{ $vehicle->code }} · {{ $vehicle->plate_number }}" data-confirm-button="Update status">
                            @csrf
                            <select name="status" class="rounded-lg border border-gray-border px-2 py-1.5 text-xs">
                                <option value="available" @selected($vehicle->status==='available')>Available</option>
                                <option value="maintenance" @selected($vehicle->status==='maintenance')>Maintenance</option>
                                <option value="inactive" @selected($vehicle->status==='inactive')>Inactive</option>
                            </select>
                            <button class="rounded-lg border border-gray-border px-3 py-1.5 text-xs font-semibold">Update</button>
                        </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-navy/50">No registered Linehaul vehicles yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
            <div class="sh-ui-list-heading"><h2 class="text-lg font-bold text-navy">Linehaul Drivers</h2><label><span class="sr-only">Filter drivers</span><input type="search" class="sh-input" data-ui-list-search="fleetDrivers" placeholder="Search driver, email or license"></label></div>
            <div id="fleetDrivers" class="mt-4 space-y-3 sh-ui-record-stack" data-ui-collection>
                @forelse($drivers as $driver)
                    <div class="rounded-xl border border-gray-border p-4 sh-ui-record" data-ui-record>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-navy">{{ $driver->name }}</p>
                                <p class="mt-1 text-sm text-navy/65">{{ $driver->email }}</p>
                                <p class="text-xs text-navy/45">{{ $driver->license_no ?: 'No license no.' }} @if($driver->license_expiry)· expires {{ $driver->license_expiry->format('M d, Y') }}@endif</p>
                            </div>
                            <span class="rounded-full bg-gray-bg px-2.5 py-1 text-xs font-semibold text-navy">{{ ucfirst($driver->status) }}</span>
                        </div>
                        <form method="POST" action="{{ route('logistics.fleet.drivers.toggle', $driver) }}" class="mt-3" data-confirm-title="{{ $driver->status === 'active' ? 'Deactivate driver?' : 'Activate driver?' }}" data-confirm-message="This changes the driver's access to linehaul assignments." data-confirm-subject="{{ $driver->name }}" data-confirm-button="{{ $driver->status === 'active' ? 'Deactivate' : 'Activate' }}" data-confirm-danger="{{ $driver->status === 'active' ? '1' : '0' }}">
                            @csrf
                            <button class="rounded-lg border border-gray-border px-3 py-1.5 text-xs font-semibold">{{ $driver->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-navy/50">No registered Linehaul Drivers yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
        <div class="flex items-center justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[.12em] text-teal">Operations</p><h2 class="mt-1 text-lg font-bold text-navy">Recent Linehaul Trips</h2></div></div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[58rem] text-left text-sm">
                <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="px-3 py-3">Trip</th><th class="px-3 py-3">Route</th><th class="px-3 py-3">Vehicle</th><th class="px-3 py-3">Driver</th><th class="px-3 py-3">Manifest</th><th class="px-3 py-3">Status</th></tr></thead>
                <tbody>
                @forelse($trips as $trip)
                    <tr class="border-b border-gray-border/70"><td class="px-3 py-3 font-semibold">{{ $trip->code }}</td><td class="px-3 py-3">{{ $trip->fromCenter?->name }} → {{ $trip->toCenter?->name }}</td><td class="px-3 py-3">{{ $trip->vehicle?->code }} · {{ $trip->vehicle?->plate_number }}</td><td class="px-3 py-3">{{ $trip->driver?->name }}</td><td class="px-3 py-3">{{ $trip->manifests->pluck('code')->implode(', ') ?: '—' }}</td><td class="px-3 py-3">{{ ucfirst(str_replace('_',' ', $trip->status)) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8 text-center text-navy/45">No Linehaul Trips yet. Trips are created by Branch/Hub staff after a manifest is fully loaded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
