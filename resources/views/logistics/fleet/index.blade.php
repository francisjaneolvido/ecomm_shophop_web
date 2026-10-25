@extends('logistics.layouts.console')

@section('title', 'Fleet & Linehaul')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
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
        <div class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.12em] text-teal">Vehicle Registration</p>
                    <h2 class="mt-1 text-lg font-bold text-navy">Register a Linehaul vehicle</h2>
                </div>
            </div>
            <form method="POST" action="{{ route('logistics.fleet.vehicles.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Vehicle code</span><input class="rounded-xl border border-gray-border px-3 py-2" name="code" required placeholder="VH-LAG-001"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Plate number</span><input class="rounded-xl border border-gray-border px-3 py-2" name="plate_number" required placeholder="ABC 1234"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Vehicle type</span>
                    <select class="rounded-xl border border-gray-border px-3 py-2" name="vehicle_type" required>
                        <option value="van">Van</option><option value="light_truck">Light Truck</option>
                        <option value="6_wheeler">6-Wheeler</option><option value="10_wheeler">10-Wheeler</option><option value="other">Other</option>
                    </select>
                </label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Home Province Hub</span>
                    <select class="rounded-xl border border-gray-border px-3 py-2" name="home_hub_id" required>
                        <option value="">Select Province Hub</option>
                        @foreach($provinceHubs as $hub)<option value="{{ $hub->id }}">{{ $hub->name }}</option>@endforeach
                    </select>
                </label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Capacity (kg)</span><input class="rounded-xl border border-gray-border px-3 py-2" type="number" step="0.01" min="1" name="capacity_kg" placeholder="4000"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Volume (m³, optional)</span><input class="rounded-xl border border-gray-border px-3 py-2" type="number" step="0.01" min="0.01" name="volume_capacity_m3"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Ownership</span>
                    <select class="rounded-xl border border-gray-border px-3 py-2" name="owner_type"><option value="company">Company owned</option><option value="contracted">Contracted / 3PL</option></select>
                </label>
                <label class="flex items-center gap-2 self-end rounded-xl border border-gray-border px-3 py-2 text-sm"><input type="checkbox" name="is_primary" value="1"><span>Primary vehicle for this Province Hub</span></label>
                <div class="md:col-span-2"><button class="rounded-xl bg-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-navy-light">Register vehicle</button></div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
            <p class="text-xs font-bold uppercase tracking-[.12em] text-teal">Driver Registration</p>
            <h2 class="mt-1 text-lg font-bold text-navy">Register a Linehaul Driver</h2>
            <p class="mt-1 text-xs text-navy/50">This is a separate operational identity from a Rider. The credentials below are used in the ShopHop mobile app.</p>
            <form method="POST" action="{{ route('logistics.fleet.drivers.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Driver name</span><input class="rounded-xl border border-gray-border px-3 py-2" name="name" required></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Contact</span><input class="rounded-xl border border-gray-border px-3 py-2" name="contact_no"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">License no.</span><input class="rounded-xl border border-gray-border px-3 py-2" name="license_no"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">License expiry</span><input class="rounded-xl border border-gray-border px-3 py-2" type="date" name="license_expiry"></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Mobile login email</span><input class="rounded-xl border border-gray-border px-3 py-2" type="email" name="email" required></label>
                <div></div>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Temporary password</span><input class="rounded-xl border border-gray-border px-3 py-2" type="password" name="password" required></label>
                <label class="grid gap-1 text-sm"><span class="font-semibold text-navy">Confirm password</span><input class="rounded-xl border border-gray-border px-3 py-2" type="password" name="password_confirmation" required></label>
                <div class="md:col-span-2"><button class="rounded-xl bg-teal px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-dark">Register driver</button></div>
            </form>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-border bg-white p-5 shadow-soft">
            <h2 class="text-lg font-bold text-navy">Vehicles</h2>
            <div class="mt-4 space-y-3">
                @forelse($vehicles as $vehicle)
                    <div class="rounded-xl border border-gray-border p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2"><span class="font-bold text-navy">{{ $vehicle->code }}</span>@if($vehicle->is_primary)<span class="rounded-full bg-teal-light px-2 py-0.5 text-[10px] font-bold text-teal-dark">PRIMARY</span>@endif</div>
                                <p class="mt-1 text-sm text-navy/65">{{ $vehicle->plate_number }} · {{ ucwords(str_replace('_',' ', $vehicle->vehicle_type)) }}</p>
                                <p class="text-xs text-navy/45">{{ $vehicle->homeHub?->name ?? 'No home hub' }} · {{ $vehicle->capacity_kg ? number_format((float)$vehicle->capacity_kg).' kg' : 'Capacity not set' }}</p>
                            </div>
                            <span class="rounded-full bg-gray-bg px-2.5 py-1 text-xs font-semibold text-navy">{{ ucfirst(str_replace('_',' ', $vehicle->status)) }}</span>
                        </div>
                        @if(!in_array($vehicle->status, ['in_transit']))
                        <form method="POST" action="{{ route('logistics.fleet.vehicles.status', $vehicle) }}" class="mt-3 flex gap-2">
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
            <h2 class="text-lg font-bold text-navy">Linehaul Drivers</h2>
            <div class="mt-4 space-y-3">
                @forelse($drivers as $driver)
                    <div class="rounded-xl border border-gray-border p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-navy">{{ $driver->name }}</p>
                                <p class="mt-1 text-sm text-navy/65">{{ $driver->email }}</p>
                                <p class="text-xs text-navy/45">{{ $driver->license_no ?: 'No license no.' }} @if($driver->license_expiry)· expires {{ $driver->license_expiry->format('M d, Y') }}@endif</p>
                            </div>
                            <span class="rounded-full bg-gray-bg px-2.5 py-1 text-xs font-semibold text-navy">{{ ucfirst($driver->status) }}</span>
                        </div>
                        <form method="POST" action="{{ route('logistics.fleet.drivers.toggle', $driver) }}" class="mt-3">
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
