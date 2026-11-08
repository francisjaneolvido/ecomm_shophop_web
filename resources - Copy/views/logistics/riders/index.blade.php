@extends('logistics.layouts.console')

@section('title', 'Riders — ShopHop Logistics')

@section('content')
@php
    $pendingReview = (int) ($totals->pending_review ?? 0);
    $awaitingEmail = (int) ($totals->awaiting_email ?? 0);
    $activeCount = (int) ($totals->active_count ?? 0);
    $inactiveCount = (int) ($totals->inactive_count ?? 0);
@endphp

<div class="ops-page space-y-4">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Network · Fleet management</p>
            <h1 class="title">Rider Management</h1>
            <p class="description">Rider eligibility requires an active account, availability, the correct home branch, and a linked Pickup/Delivery barangay area. A legacy Delivery Area alone does not grant pickup permission.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" class="ops-button ops-button-primary" data-ops-open="createRider">+ Add rider</button>
            <a href="{{ route('logistics.branches.index') }}" class="ops-button">Branches</a>
        </div>
    </header>

    @if (session('status')) <div role="status" class="sh-alert-success">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div> @endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat"><p class="sh-stat-label">Pending review</p><p class="sh-stat-value">{{ $pendingReview }}</p><p class="sh-stat-note">Verified and ready for decision</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Awaiting email</p><p class="sh-stat-value">{{ $awaitingEmail }}</p><p class="sh-stat-note">Cannot be approved yet</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Active riders</p><p class="sh-stat-value">{{ $activeCount }}</p><p class="sh-stat-note">Operational accounts</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Suspended / Rejected</p><p class="sh-stat-value">{{ $inactiveCount }}</p><p class="sh-stat-note">Inactive or denied</p></div>
    </div>


<section class="ops-panel" id="rider-directory">
 <div class="ops-header"><div><h2 class="font-bold text-navy">Rider directory</h2><p class="ops-subtitle">Review applications and manage assignments without expanding cards.</p></div><button type="button" data-ops-open="createArea" class="ops-button">+ Add service area</button></div>
 <form class="ops-filters" method="GET" action="{{ route('logistics.riders.index') }}" role="search">
  <label class="sh-field"><span class="sh-label">Name / email / branch</span><input name="q" value="{{ request('q') }}" class="sh-input" placeholder="Search riders" maxlength="100"></label>
  <label class="sh-field"><span class="sh-label">Home branch</span><select class="sh-select" name="center"><option value="">All branches</option>@foreach($sortingCenters->where('type','station_hub') as $center)<option value="{{ $center->id }}" @selected((string)request('center')===(string)$center->id)>{{ $center->name }}</option>@endforeach</select></label>
  <label class="sh-field"><span class="sh-label">Status</span><select name="status" class="sh-select"><option value="">All</option>@foreach(['pending','active','suspended','rejected'] as $option)<option value="{{ $option }}" @selected(request('status')===$option)>{{ ucfirst($option) }}</option>@endforeach</select></label>
  <button class="ops-button ops-button-primary">Filter</button><a href="{{ route('logistics.riders.index') }}" class="ops-button">Clear</a>
 </form>
 <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Rider</th><th>Home Branch</th><th>Operational Areas</th><th>Vehicle</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead><tbody>
 @forelse($riders as $rider)
 <tr><td><div class="ops-primary">{{ $rider->name }}</div><div class="ops-secondary">{{ $rider->contact_no ?: 'No contact on record' }}</div></td>
 <td>{{ $rider->sortingCenter?->name ?: 'Unassigned' }}</td>
 <td><strong>{{ $rider->operationalServiceAreas->count() }}</strong> <span class="ops-muted text-xs">configured</span><div class="ops-secondary">{{ $rider->coverageArea?->area_name ?: '—' }} (legacy)</div></td>
 <td>{{ $rider->vehicle_type }}</td><td class="text-xs break-all">{{ $rider->email ?: 'Not provisioned' }}</td>
 <td><span class="ops-badge" data-status="{{ $rider->status }}">{{ ucfirst($rider->status) }}</span><div class="ops-secondary">{{ $rider->email_verified_at ? 'Email verified' : 'Unverified email' }}</div></td>
 <td><button type="button" class="ops-button" data-ops-open="riderDetails-{{ $rider->id }}">View / Edit</button></td></tr>
 @empty<tr><td colspan="7" class="ops-empty">No riders match your filters.</td></tr>@endforelse
 </tbody></table></div><div class="ops-footer"><span>{{ $riders->firstItem() ?? 0 }}–{{ $riders->lastItem() ?? 0 }} of {{ number_format($riders->total()) }} riders</span>{{ $riders->links() }}</div>
</section>
<section class="ops-panel" id="barangay-coverage"><div class="ops-header"><div><h2 class="font-bold text-navy">Branch Service Areas</h2><p class="ops-subtitle">Pickup and delivery barangay permissions; changes affect future assignments</p></div><button type="button" class="ops-button" data-ops-open="createArea">+ Configure area</button></div>
<form class="ops-filters" method="GET" action="{{ route('logistics.riders.index') }}" style="padding-top:10px"><label class="sh-field"><span class="sh-label">Search all service areas</span><input class="sh-input" name="area_q" value="{{ request('area_q') }}" placeholder="Barangay, municipality, branch"></label><button class="ops-button ops-button-primary">Search</button><a href="{{ route('logistics.riders.index') }}#barangay-coverage" class="ops-button">Clear</a></form>
<div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Service area</th><th>Branch</th><th>Work allowed</th><th>Actions</th></tr></thead><tbody>
 @forelse($serviceAreasDirectory as $area)<tr><td class="ops-primary">{{ $area->label() }}</td><td>{{ $area->sortingCenter?->name ?: '—' }}</td><td>{{ ucfirst($area->service_type) }}</td><td><form method="POST" action="{{ route('logistics.riders.operational-areas.delete',$area) }}" data-confirm-title="Remove service area?" data-confirm-message="Rider assignments must be removed first." data-confirm-button="Remove" data-confirm-danger="1">@csrf<button class="ops-button ops-button-danger">Remove</button></form></td></tr>
 @empty<tr><td colspan="4" class="ops-empty">No service areas configured yet. Add areas before assigning barangay permissions.</td></tr>@endforelse
</tbody></table></div><footer class="ops-footer"><span>{{ $serviceAreasDirectory->firstItem() ?? 0 }}–{{ $serviceAreasDirectory->lastItem() ?? 0 }} of {{ number_format($serviceAreasDirectory->total()) }} service areas</span>{{ $serviceAreasDirectory->links() }}</footer></section>
</div>
<dialog id="createRider" data-ops-modal class="ops-modal"><div class="ops-modal-head"><div><h2>Add rider manually</h2><p>Rider account and initial home branch</p></div><button class="ops-button" type="button" data-ops-close>✕</button></div><div class="ops-modal-body">
            <form method="POST" action="{{ route('logistics.riders.store') }}" class="ops-fields" data-confirm-title="Create rider account?" data-confirm-message="This adds a new rider under your logistics partner. Double-check the rider's email and assigned branch." data-confirm-button="Create rider">
                @csrf
                <label class="sh-field"><span class="sh-label">Rider name</span><input id="rider-name" name="name" required maxlength="150" value="{{ old('name') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Vehicle type</span><input id="rider-vehicle" name="vehicle_type" required maxlength="80" value="{{ old('vehicle_type') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Home branch</span><select id="rider-center" name="sorting_center_id" class="sh-select"><option value="">Select home branch</option>@foreach ($sortingCenters->where('status', 'active') as $center)<option value="{{ $center->id }}" @selected(old('sorting_center_id') == $center->id)>{{ $center->name }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Legacy delivery area (not barangay eligibility)</span><select id="rider-area" name="coverage_area_id" class="sh-select"><option value="">Pickup only / unassigned</option>@foreach ($coverageAreas as $area)<option value="{{ $area->id }}" @selected(old('coverage_area_id') == $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Rider email</span><input id="rider-email" name="email" type="email" required value="{{ old('email') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Temporary password</span><input id="rider-password" name="password" type="password" minlength="12" required class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Confirm password</span><input id="rider-password-confirmation" name="password_confirmation" type="password" minlength="12" required class="sh-input"></label>
                <div class="md:col-span-2 xl:col-span-3 flex justify-end"><button class="sh-btn">Add rider</button></div>
            </form>
</div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Close</button></div></dialog>
<dialog id="createArea" data-ops-modal class="ops-modal"><div class="ops-modal-head"><div><h2>Configure branch service area</h2><p>Barangay-level permissions for pickup / delivery</p></div><button class="ops-button" type="button" data-ops-close>✕</button></div><div class="ops-modal-body"><p class="ops-note mb-3">Once service areas are enabled, assign matching areas to riders or they cannot receive new pickups.</p>
            <form method="POST" action="{{ route('logistics.riders.operational-areas.store') }}" class="ops-fields" data-confirm-title="Activate geographic rider checks for this branch?" data-confirm-message="After the first area is saved, ONLY riders explicitly assigned to matching barangays will qualify for new pickups/deliveries from this branch. Add the riders' coverage before testing orders." data-confirm-button="Save service area">
                @csrf
                <label class="sh-field xl:col-span-2"><span class="sh-label">Active Station / Branch *</span><select name="sorting_center_id" class="sh-select" required><option value="">Select station</option>@foreach($sortingCenters->where('type', 'station_hub')->where('status', 'active') as $center)<option value="{{ $center->id }}" data-province="{{ $center->province }}">{{ $center->name }} · {{ $center->province ?: 'NCR' }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Province (blank for NCR)</span><input class="sh-input" name="province" maxlength="150" value="{{ old('province') }}" placeholder="Laguna"></label>
                <label class="sh-field"><span class="sh-label">City / Municipality *</span><input class="sh-input" name="municipality" required maxlength="150" value="{{ old('municipality') }}" placeholder="Calamba City"></label>
                <label class="sh-field"><span class="sh-label">Barangay (optional)</span><input class="sh-input" name="barangay" maxlength="150" value="{{ old('barangay') }}" placeholder="Canlubang / blank = all"></label>
                <label class="sh-field xl:col-span-2"><span class="sh-label">Work allowed *</span><select name="service_type" class="sh-select"><option value="both">Pickup and delivery</option><option value="pickup">Pickup only</option><option value="delivery">Delivery only</option></select></label>
                <div class="xl:col-span-3 flex items-end justify-end"><button class="sh-btn">Save service area</button></div>
            </form>
</div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Close</button></div></dialog>
@foreach($riders as $rider)
<dialog id="riderDetails-{{ $rider->id }}" data-ops-modal class="ops-modal" aria-label="Manage rider {{ $rider->name }}">
 <div class="ops-modal-head"><div><h2>{{ $rider->name }}</h2><p>{{ $rider->email ?: 'No mobile login yet' }} · {{ $rider->sortingCenter?->name ?: 'Unassigned branch' }}</p></div><button class="ops-button" type="button" data-ops-close>✕</button></div>
 <div class="ops-modal-body"><p class="ops-note">Profile, branch assignment and barangay permissions have separate Save actions. Account approval and credentials retain their existing verification rules.</p>
 <details class="mt-3 border border-gray-border rounded-md p-3" open><summary class="font-semibold text-sm text-navy cursor-pointer">Edit basic rider details</summary>
 <form method="POST" action="{{ route('logistics.riders.profile',$rider) }}" class="ops-fields mt-3" data-confirm-title="Save rider profile?" data-confirm-message="Name and vehicle details only. Rider account status, login and branch permissions remain unchanged." data-confirm-button="Save profile">@csrf
 <label class="sh-field"><span class="sh-label">Rider name</span><input name="name" value="{{ $rider->name }}" class="sh-input" maxlength="150" required></label>
 <label class="sh-field"><span class="sh-label">Contact</span><input name="contact_no" value="{{ $rider->contact_no }}" class="sh-input" maxlength="40"></label>
 <label class="sh-field"><span class="sh-label">Vehicle type</span><input name="vehicle_type" value="{{ $rider->vehicle_type }}" class="sh-input" maxlength="80" required></label>
 <label class="sh-field"><span class="sh-label">Plate number</span><input name="plate_number" value="{{ $rider->plate_number }}" class="sh-input" maxlength="40"></label>
 <div class="ops-span flex justify-end"><button class="ops-button ops-button-primary">Save profile</button></div>
 </form></details>

                    <div class="sh-rider-expanded">
                @if ($rider->applied_at)
                    <div class="mt-4 sh-surface-soft p-4 text-xs text-navy/65">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <p><strong>Application:</strong> {{ $rider->applied_at->diffForHumans() }}</p>
                            <p><strong>Status gate:</strong> {{ $rider->email_verified_at ? 'Ready for review' : 'Email verification required' }}</p>
                            @if ($rider->contact_no)<p><strong>Contact:</strong> {{ $rider->contact_no }}</p>@endif
                            @if ($rider->birthday)<p><strong>Birthday:</strong> {{ $rider->birthday->format('M d, Y') }} · {{ $rider->sex }}</p>@endif
                            @if ($rider->applicationAddress())<p class="sm:col-span-2"><strong>Address:</strong> {{ $rider->applicationAddress() }}</p>@endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($rider->or_cr_path)
                                <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'or_cr']) }}" class="sh-btn-ghost">View OR/CR</a>
                            @endif
                            @if ($rider->id_or_license_path)
                                <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'id_or_license']) }}" class="sh-btn-ghost">View ID / License</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($rider->status === 'pending')
                    @if ($rider->email_verified_at)
                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            <form method="POST" action="{{ route('logistics.riders.approve', $rider) }}" data-confirm-subject="{{ $rider->name }}" data-confirm-title="Approve this rider?" data-confirm-message="The rider will become active and can be assigned to deliveries." data-confirm-button="Approve rider">
                                @csrf
                                <button class="sh-btn w-full">Approve rider</button>
                            </form>
                            <form method="POST" action="{{ route('logistics.riders.reject', $rider) }}" class="sh-surface-soft p-4" data-confirm-subject="{{ $rider->name }}" data-confirm-title="Reject this rider application?" data-confirm-message="This decision marks the application as rejected. Make sure the rejection reason is complete." data-confirm-button="Reject rider" data-confirm-danger="1">
                                @csrf
                                <label class="sh-field"><span class="sh-label">Reason for rejection</span><textarea name="rejection_reason" required maxlength="1000" rows="3" placeholder="Explain why the rider application is being rejected." class="sh-textarea"></textarea></label>
                                <div class="mt-3 flex justify-end"><button class="sh-btn-danger">Reject rider</button></div>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">Approval is locked until the rider verifies the registered email address.</div>
                    @endif
                @endif

                @if ($rider->status === 'rejected' && $rider->rejection_reason)
                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700"><strong>Rejection reason:</strong> {{ $rider->rejection_reason }}</div>
                @endif

                @if (in_array($rider->status, ['active', 'suspended'], true))
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="sh-surface-soft p-4">
                            <p class="sh-stat-label">Sorting Center</p>
                            <p class="mt-2 text-sm font-semibold text-navy">{{ $rider->sortingCenter?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div class="sh-surface-soft p-4">
                            <p class="sh-stat-label">Legacy delivery area</p>
                            <p class="mt-2 text-sm font-semibold text-navy">{{ $rider->coverageArea?->area_name ?? 'Unassigned' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logistics.riders.area', $rider) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1.4fr_auto] sm:items-end" data-confirm-title="Update rider assignment?" data-confirm-message="This changes the rider's home branch and/or coverage area. Confirm that these are correct before saving." data-confirm-subject="{{ $rider->name }}" data-confirm-button="Save assignment">
                        @csrf
                        <label class="sh-field"><span class="sh-label">Sorting center</span><select name="sorting_center_id" class="sh-select"><option value="">Select home branch</option>@foreach ($sortingCenters->where('status', 'active') as $center)<option value="{{ $center->id }}" @selected($rider->sorting_center_id === $center->id)>{{ $center->name }}</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Legacy delivery area (not pickup eligibility)</span><select name="coverage_area_id" class="sh-select"><option value="">Pickup only / unassigned</option>@foreach ($coverageAreas as $area)<option value="{{ $area->id }}" @selected($rider->coverage_area_id === $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></label>
                        <button class="sh-btn-ghost">Update assignment</button>
                    </form>

                    <details class="mt-3 rounded-xl border border-gray-border bg-gray-bg/35 px-3 py-2">
                        <summary class="cursor-pointer text-xs font-bold text-teal-dark">Pickup / delivery barangay permissions ({{ $rider->operationalServiceAreas->count() }})</summary>
                        <form method="POST" action="{{ route('logistics.riders.operational-areas.sync', $rider) }}" class="mt-3" data-confirm-title="Save rider service areas?" data-confirm-message="Only the selected areas will qualify this rider for future assignments at their home branch." data-confirm-button="Save areas">
                            @csrf
                            @php $myAreas = $operationalAreas->where('sorting_center_id', $rider->sorting_center_id); @endphp
                            @if($myAreas->isEmpty())
                                <p class="text-xs text-amber-800">No linked barangay areas at this branch. For an unassigned pickup order, use Enable pickup coverage in Parcel Operations. Otherwise create a service area above and link it here.</p>
                            @else
                                <div class="grid max-h-36 gap-1.5 overflow-y-auto pr-1 sm:grid-cols-2">
                                @foreach($myAreas as $area)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-border bg-white px-2 py-1.5 text-xs text-navy">
                                        <input type="checkbox" class="h-4 w-4 accent-teal-600" name="area_ids[]" value="{{ $area->id }}" @checked($rider->operationalServiceAreas->contains('id', $area->id))>
                                        <span class="min-w-0"><strong>{{ $area->barangay ?: 'All barangays' }}</strong> · {{ $area->municipality }} <span class="text-navy/45">({{ $area->service_type }})</span></span>
                                    </label>
                                @endforeach
                                </div>
                                <div class="mt-2 flex items-center justify-between gap-2"><p class="text-[10px] text-navy/55">Multiple barangays per rider are supported.</p><button class="sh-btn-soft">Save areas</button></div>
                            @endif
                        </form>
                    </details>

                    @if (! $rider->email)
                        <form method="POST" action="{{ route('logistics.riders.provision', $rider) }}" class="mt-4 sh-surface-soft p-4" data-confirm-title="Provision rider credentials?" data-confirm-message="Login credentials will be created for this rider using the email and password below." data-confirm-button="Provision credentials">
                            @csrf
                            <p class="text-sm font-bold text-navy">Provision rider credentials</p>
                            <p class="mt-1 text-xs text-navy/45">Use a secure temporary password and share it safely with the rider.</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <label class="sh-field"><span class="sh-label">Email</span><input name="email" type="email" required aria-label="Rider email" placeholder="Email" class="sh-input"></label>
                                <label class="sh-field"><span class="sh-label">Password</span><input name="password" type="password" minlength="12" required aria-label="Rider password" placeholder="Password" class="sh-input"></label>
                                <label class="sh-field"><span class="sh-label">Confirm password</span><input name="password_confirmation" type="password" minlength="12" required aria-label="Confirm Rider password" placeholder="Confirm password" class="sh-input"></label>
                            </div>
                            <div class="mt-4 flex justify-end"><button class="sh-btn">Provision credentials</button></div>
                        </form>
                    @endif

                    <form method="POST" action="{{ $rider->status === 'active' ? route('logistics.riders.suspend', $rider) : route('logistics.riders.activate', $rider) }}" class="mt-4" data-confirm-subject="{{ $rider->name }}" data-confirm-title="{{ $rider->status === 'active' ? 'Suspend this rider?' : 'Reactivate this rider?' }}" data-confirm-message="{{ $rider->status === 'active' ? 'The rider will no longer be assignable until reactivated.' : 'The rider will become available for assignment again.' }}" data-confirm-button="{{ $rider->status === 'active' ? 'Suspend rider' : 'Activate rider' }}" data-confirm-danger="{{ $rider->status === 'active' ? '1' : '0' }}">
                        @csrf
                        <button class="{{ $rider->status === 'active' ? 'sh-btn-danger' : 'sh-btn-ghost' }}">{{ $rider->status === 'active' ? 'Suspend rider' : 'Activate rider' }}</button>
                    </form>
                @endif
                    </div>
                
            
 </div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Close</button></div>
</dialog>
@endforeach
@endsection
@push('scripts')
<script>
(() => {
  const form = document.querySelector('form[action="{{ route('logistics.riders.operational-areas.store') }}"]');
  if (!form) return;
  const branch = form.querySelector('[name=sorting_center_id]');
  const province = form.querySelector('[name=province]');
  if (branch && province) {
    branch.addEventListener('change', () => {
      province.value = branch.selectedOptions[0]?.dataset.province || '';
    });
  }
})();
</script>
@endpush
