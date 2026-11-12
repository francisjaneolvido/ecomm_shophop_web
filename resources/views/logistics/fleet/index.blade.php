@extends('logistics.layouts.console')
@section('title', 'Fleet & Linehaul — ShopHop Logistics')
@section('content')
<div class="ops-page space-y-4">
 <header class="flex flex-wrap justify-between items-end gap-3"><div><p class="eyebrow">NETWORK & PEOPLE</p><h1 class="ops-title">Fleet & Linehaul Management</h1><p class="ops-subtitle">Vehicles and linehaul drivers transport manifested parcel batches. Pickup and delivery riders are managed separately.</p></div></header>
 @if(session('status'))<div class="sh-alert-success ops-status" role="status">{{ session('status') }}</div>@endif
 @if($errors->any())<div class="sh-alert-error ops-status" role="alert">{{ $errors->first() }}</div>@endif
 <section class="ops-panel">
  <div class="ops-tabs" data-ops-tabs data-ops-tab-param="tab" role="tablist" aria-label="Fleet directories">
    <button type="button" data-ops-tab="vehicles" id="tabVehicles" aria-controls="panelVehicles" role="tab">Vehicles <span class="ops-muted">({{ number_format($vehicles->total()) }})</span></button>
    <button type="button" data-ops-tab="drivers" id="tabDrivers" aria-controls="panelDrivers" role="tab">Linehaul Drivers <span class="ops-muted">({{ number_format($drivers->total()) }})</span></button>
    <button type="button" data-ops-tab="trips" id="tabTrips" aria-controls="panelTrips" role="tab">Recent Trips</button>
  </div>
  <div class="ops-tab-panel" id="panelVehicles" role="tabpanel" aria-labelledby="tabVehicles">
   <div class="ops-header"><div><h2 class="font-bold text-navy">Registered Vehicles</h2><p class="ops-subtitle">Search, inspect and maintain fleet capacity</p></div><button class="ops-button ops-button-primary" type="button" data-ops-open="createVehicle">+ Add vehicle</button></div>
   <form class="ops-filters" method="GET"><input type="hidden" name="tab" value="vehicles"><label class="sh-field"><span class="sh-label">Plate / Code / Home Hub</span><input class="sh-input" name="vehicles_q" value="{{ request('vehicles_q') }}" placeholder="Search vehicles"></label>
    <label class="sh-field"><span class="sh-label">Status</span><select class="sh-select" name="vehicles_status"><option value="">All statuses</option>@foreach(['available','in_transit','maintenance','inactive'] as $status)<option value="{{ $status }}" @selected(request('vehicles_status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></label><button class="ops-button">Filter</button><a class="ops-button" href="{{ route('logistics.fleet.index', ['tab'=>'vehicles']) }}">Clear</a>
   </form>
   <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Vehicle</th><th>Type</th><th>Home Province Hub</th><th>Capacity</th><th>Ownership</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($vehicles as $vehicle)
    <tr><td><div class="ops-primary">{{ $vehicle->code }} @if($vehicle->is_primary)<span class="ops-badge" data-status="verified">Primary</span>@endif</div><div class="ops-secondary">{{ $vehicle->plate_number }}</div></td><td>{{ ucwords(str_replace('_',' ', $vehicle->vehicle_type)) }}</td><td>{{ $vehicle->homeHub?->name ?: 'Unassigned' }}</td><td class="ops-compact">{{ $vehicle->capacity_kg ? number_format((float)$vehicle->capacity_kg).' kg' : '—' }}<div class="ops-secondary">{{ $vehicle->volume_capacity_m3 ? $vehicle->volume_capacity_m3.' m³' : '' }}</div></td><td>{{ $vehicle->owner_type==='company' ? 'Company' : 'Contracted' }}</td><td><span class="ops-badge" data-status="{{ $vehicle->status }}">{{ ucwords(str_replace('_',' ',$vehicle->status)) }}</span></td>
    <td><details class="ops-actions"><summary class="ops-button">Actions ▾</summary><div class="ops-actions-menu">
      <button type="button" data-ops-open="editVehicle" data-ops-record-edit="editVehicle" data-subject="{{ $vehicle->code }} · {{ $vehicle->plate_number }}" data-url="{{ route('logistics.fleet.vehicles.update',$vehicle) }}" data-fields="{{ json_encode(['code'=>$vehicle->code,'plate_number'=>$vehicle->plate_number,'vehicle_type'=>$vehicle->vehicle_type,'home_hub_id'=>$vehicle->home_hub_id,'capacity_kg'=>$vehicle->capacity_kg,'volume_capacity_m3'=>$vehicle->volume_capacity_m3,'owner_type'=>$vehicle->owner_type,'is_primary'=>(bool)$vehicle->is_primary],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG) }}">Edit details</button>
      @if($vehicle->status !== 'in_transit')<button type="button" data-ops-open="statusVehicle" data-ops-record-edit="statusVehicle" data-subject="{{ $vehicle->code }}" data-url="{{ route('logistics.fleet.vehicles.status',$vehicle) }}" data-fields="{{ json_encode(['status'=>$vehicle->status],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG) }}">Change status</button>@endif
    </div></details></td></tr>
    @empty<tr><td colspan="7" class="ops-empty">No vehicles match your filters.</td></tr>@endforelse
   </tbody></table></div><div class="ops-footer"><span>{{ $vehicles->firstItem() ?? 0 }}–{{ $vehicles->lastItem() ?? 0 }} of {{ number_format($vehicles->total()) }} vehicles</span>{{ $vehicles->links() }}</div>
  </div>
  <div class="ops-tab-panel" id="panelDrivers" role="tabpanel" aria-labelledby="tabDrivers" hidden>
   <div class="ops-header"><div><h2 class="font-bold text-navy">Linehaul Drivers</h2><p class="ops-subtitle">Separate from pickup/delivery rider accounts</p></div><button class="ops-button ops-button-primary" data-ops-open="createDriver">+ Add driver</button></div>
   <form class="ops-filters" method="GET"><input type="hidden" name="tab" value="drivers"><label class="sh-field"><span class="sh-label">Name / Email / License</span><input name="drivers_q" class="sh-input" value="{{ request('drivers_q') }}" placeholder="Search drivers"></label><label class="sh-field"><span class="sh-label">Status</span><select class="sh-select" name="drivers_status"><option value="">All</option><option value="active" @selected(request('drivers_status')==='active')>Active</option><option value="inactive" @selected(request('drivers_status')==='inactive')>Inactive</option></select></label><button class="ops-button">Filter</button><a class="ops-button" href="{{ route('logistics.fleet.index', ['tab'=>'drivers']) }}">Clear</a></form>
   <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Driver</th><th>Contact</th><th>License</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($drivers as $driver)<tr><td><div class="ops-primary">{{ $driver->name }}</div><div class="ops-secondary">{{ $driver->email }}</div></td><td>{{ $driver->contact_no ?: '—' }}</td><td>{{ $driver->license_no ?: '—' }}</td><td class="ops-compact">{{ $driver->license_expiry?->format('M d, Y') ?: '—' }}</td><td><span class="ops-badge" data-status="{{ $driver->status }}">{{ ucfirst($driver->status) }}</span></td>
    <td><details class="ops-actions"><summary class="ops-button">Actions ▾</summary><div class="ops-actions-menu"><button type="button" data-ops-open="editDriver" data-ops-record-edit="editDriver" data-url="{{ route('logistics.fleet.drivers.update',$driver) }}" data-subject="{{ $driver->name }}" data-fields="{{ json_encode(['name'=>$driver->name,'email'=>$driver->email,'contact_no'=>$driver->contact_no,'license_no'=>$driver->license_no,'license_expiry'=>$driver->license_expiry?->format('Y-m-d')],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG) }}">Edit details</button><form method="POST" action="{{ route('logistics.fleet.drivers.toggle',$driver) }}" data-confirm-title="{{ $driver->status==='active' ? 'Deactivate driver?' : 'Activate driver?' }}" data-confirm-message="This changes linehaul access. Active trips must be completed first." data-confirm-button="Confirm">@csrf<button class="{{ $driver->status==='active'?'ops-danger':'' }}">{{ $driver->status==='active'?'Deactivate':'Activate' }}</button></form></div></details></td></tr>
    @empty<tr><td colspan="6" class="ops-empty">No linehaul drivers match your filters.</td></tr>@endforelse
   </tbody></table></div><div class="ops-footer"><span>{{ $drivers->firstItem() ?? 0 }}–{{ $drivers->lastItem() ?? 0 }} of {{ number_format($drivers->total()) }} drivers</span>{{ $drivers->links() }}</div>
  </div>
  <div class="ops-tab-panel" id="panelTrips" role="tabpanel" aria-labelledby="tabTrips" hidden>
   <div class="ops-header"><h2 class="font-bold text-navy">Latest Linehaul Trips</h2><span class="ops-muted text-xs">Most recent 40 records</span></div>
   <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Trip</th><th>Route</th><th>Vehicle</th><th>Driver</th><th>Manifest</th><th>Status</th></tr></thead><tbody>
   @forelse($trips as $trip)<tr><td class="ops-primary">{{ $trip->code }}</td><td>{{ $trip->fromCenter?->name }} → {{ $trip->toCenter?->name }}</td><td>{{ $trip->vehicle?->code }}<div class="ops-secondary">{{ $trip->vehicle?->plate_number }}</div></td><td>{{ $trip->driver?->name }}</td><td>{{ $trip->manifests->pluck('code')->implode(', ') ?: '—' }}</td><td><span class="ops-badge" data-status="{{ $trip->status }}">{{ ucwords(str_replace('_',' ',$trip->status)) }}</span></td></tr>@empty<tr><td colspan="6" class="ops-empty">No trips recorded yet.</td></tr>@endforelse
   </tbody></table></div>
  </div>
 </section>
</div>

@foreach([['createVehicle','Register vehicle','logistics.fleet.vehicles.store'],['editVehicle','Edit vehicle','logistics.fleet.vehicles.store']] as [$id,$title,$unused])
<dialog id="{{ $id }}" data-ops-modal class="ops-modal" aria-labelledby="{{ $id }}Title">
 <div class="ops-modal-head"><div><h2 id="{{ $id }}Title">{{ $title }}</h2><p data-ops-subject>{{ $id==='createVehicle' ? 'Assign vehicle capacity and home Province Hub.' : 'Changes are blocked while an active trip exists.' }}</p></div><button type="button" class="ops-button" data-ops-close>✕</button></div>
 <form method="POST" action="{{ route('logistics.fleet.vehicles.store') }}" data-confirm-title="{{ $title }}?" data-confirm-button="Save vehicle" data-confirm-message="Review vehicle details and the home hub before saving.">@csrf
  <div class="ops-modal-body"><div class="ops-fields">
   <label class="sh-field"><span class="sh-label">Vehicle code *</span><input name="code" class="sh-input" required maxlength="40" placeholder="VH-LAG-001"></label>
   <label class="sh-field"><span class="sh-label">Plate number *</span><input name="plate_number" class="sh-input" required maxlength="40" placeholder="ABC 1234"></label>
   <label class="sh-field"><span class="sh-label">Vehicle type *</span><select name="vehicle_type" class="sh-select" required><option value="van">Van</option><option value="light_truck">Light truck</option><option value="6_wheeler">6-wheeler</option><option value="10_wheeler">10-wheeler</option><option value="other">Other</option></select></label>
   <label class="sh-field"><span class="sh-label">Home Province Hub *</span><select name="home_hub_id" class="sh-select" required><option value="">Select hub</option>@foreach($provinceHubs as $hub)<option value="{{ $hub->id }}">{{ $hub->name }}</option>@endforeach</select></label>
   <label class="sh-field"><span class="sh-label">Capacity (kg)</span><input type="number" min="1" step="0.01" name="capacity_kg" class="sh-input"></label>
   <label class="sh-field"><span class="sh-label">Volume (m³)</span><input type="number" min="0.01" step="0.01" name="volume_capacity_m3" class="sh-input"></label>
   <label class="sh-field"><span class="sh-label">Ownership</span><select name="owner_type" class="sh-select"><option value="company">Company owned</option><option value="contracted">Contracted / 3PL</option></select></label>
   <label class="sh-field flex items-center gap-2"><input type="checkbox" name="is_primary" value="1"><span class="sh-label">Primary vehicle for hub</span></label>
  </div></div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">{{ $id==='createVehicle' ? 'Register vehicle' : 'Save changes' }}</button></div>
 </form>
</dialog>
@endforeach
@foreach([['createDriver','Register linehaul driver'],['editDriver','Edit linehaul driver']] as [$id,$title])
<dialog id="{{ $id }}" data-ops-modal class="ops-modal" aria-labelledby="{{ $id }}Title">
 <div class="ops-modal-head"><div><h2 id="{{ $id }}Title">{{ $title }}</h2><p data-ops-subject>Driver credentials are for ShopHop Mobile linehaul access.</p></div><button type="button" class="ops-button" data-ops-close>✕</button></div>
 <form method="POST" action="{{ route('logistics.fleet.drivers.store') }}" data-confirm-title="Save linehaul driver?" data-confirm-button="Save driver" data-confirm-message="Confirm the driver's email and license information.">@csrf
  <div class="ops-modal-body"><div class="ops-fields">
   <label class="sh-field"><span class="sh-label">Driver name *</span><input class="sh-input" name="name" maxlength="150" required></label>
   <label class="sh-field"><span class="sh-label">Email *</span><input class="sh-input" name="email" type="email" maxlength="190" required></label>
   <label class="sh-field"><span class="sh-label">Contact</span><input class="sh-input" name="contact_no" maxlength="40"></label>
   <label class="sh-field"><span class="sh-label">License number</span><input class="sh-input" name="license_no" maxlength="80"></label>
   <label class="sh-field"><span class="sh-label">License expiry</span><input class="sh-input" name="license_expiry" type="date"></label><div></div>
   <label class="sh-field"><span class="sh-label">{{ $id==='editDriver' ? 'New password (optional)' : 'Temporary password *' }}</span><input class="sh-input" name="password" type="password" minlength="8" autocomplete="new-password" @if($id==='createDriver') required @endif></label>
   <label class="sh-field"><span class="sh-label">Confirm password</span><input class="sh-input" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" @if($id==='createDriver') required @endif></label>
  </div></div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">{{ $id==='createDriver' ? 'Register driver' : 'Save changes' }}</button></div>
 </form>
</dialog>
@endforeach
<dialog id="statusVehicle" data-ops-modal class="ops-modal" aria-labelledby="statusVehicleTitle" style="max-width:480px"><div class="ops-modal-head"><div><h2 id="statusVehicleTitle">Change vehicle status</h2><p data-ops-subject></p></div><button class="ops-button" type="button" data-ops-close>✕</button></div><form method="POST" action="" data-confirm-title="Change vehicle status?" data-confirm-button="Update" data-confirm-message="Vehicle status determines availability for future trips.">@csrf<div class="ops-modal-body"><label class="sh-field"><span class="sh-label">New status</span><select name="status" class="sh-select"><option value="available">Available</option><option value="maintenance">Maintenance</option><option value="inactive">Inactive</option></select></label></div><div class="ops-modal-footer"><button class="ops-button" type="button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">Update status</button></div></form></dialog>
@endsection
