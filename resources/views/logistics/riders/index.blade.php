@extends('logistics.layouts.console')

@section('title', 'Riders — ShopHop Logistics')

@section('content')
@php
    $pendingReview = (int) ($totals->pending_review ?? 0);
    $awaitingEmail = (int) ($totals->awaiting_email ?? 0);
    $activeCount = (int) ($totals->active_count ?? 0);
    $inactiveCount = (int) ($totals->inactive_count ?? 0);
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Network · Fleet management</p>
            <h1 class="title">Rider Management</h1>
            <p class="description">Rider eligibility requires an active account, availability, the correct home branch, and a linked Pickup/Delivery barangay area. A legacy Delivery Area alone does not grant pickup permission.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="#rider-directory" class="sh-btn-ghost">View riders <x-lucide-arrow-down class="h-4 w-4" /></a>
            <a href="{{ route('logistics.sorting-centers.index') }}" class="sh-btn-soft">Manage branches <x-lucide-arrow-up-right class="h-4 w-4" /></a>
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

    <section class="sh-surface sh-card-pad border-teal/20" aria-label="How rider eligibility works">
        <div class="sh-toolbar items-start"><div><p class="sh-kicker">Dispatch readiness</p><h2 class="text-base font-bold text-navy">How to make a rider eligible</h2><p class="mt-1 text-xs text-navy/60">Pickup uses the Seller's barangay; delivery uses the Buyer's barangay.</p></div><a class="sh-btn-soft" href="{{ route('logistics.deliveries.board') }}#pickup-queue">Open pickup queue</a></div>
        <div class="mt-3 grid gap-2 sm:grid-cols-3 text-xs">
            <div class="sh-surface-soft p-3"><strong>1 · Active & available</strong><p class="mt-1 text-navy/60">Rider account has login and belongs to the pickup branch.</p></div>
            <div class="sh-surface-soft p-3"><strong>2 · Cover the barangay</strong><p class="mt-1 text-navy/60">Enable <strong>Pickup</strong> coverage from the waiting order, or edit the rider's service areas below.</p></div>
            <div class="sh-surface-soft p-3"><strong>3 · Assign the order</strong><p class="mt-1 text-navy/60">Return to Parcel Operations and confirm Assign Pickup. Coverage alone never dispatches an order.</p></div>
        </div>
    </section>

    <section class="sh-surface sh-card-pad">
        <details>
            <summary class="sh-summary sh-toolbar">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-user-plus class="h-5 w-5" /></div>
                    <div>
                        <h2 class="text-base font-bold text-navy">Add Rider manually</h2>
                        <p class="mt-1 text-xs text-navy/50">Create a rider account directly under this partner for immediate setup.</p>
                    </div>
                </div>
                <span class="sh-btn-soft">Open rider form</span>
            </summary>

            <form method="POST" action="{{ route('logistics.riders.store') }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3" data-confirm-title="Create rider account?" data-confirm-message="This adds a new rider under your logistics partner. Double-check the rider's email and assigned branch." data-confirm-button="Create rider">
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
        </details>
    </section>

    <section class="sh-surface sh-card-pad" id="barangay-coverage">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="sh-kicker">Branch operations · Area eligibility</p>
                <h2 class="text-base font-bold text-navy">Barangay Service Areas</h2>
                <p class="mt-1 text-xs text-navy/55">Add areas actually served by each branch. Riders must be explicitly assigned below; a nationwide label alone does not qualify them.</p>
            </div>
            <span class="sh-badge sh-badge-neutral">{{ $operationalAreas->count() }} for current page</span>
        </div>
        <details class="mt-3 rounded-xl border border-gray-border p-3">
            <summary class="cursor-pointer text-sm font-semibold text-teal-dark">+ Configure branch service area</summary>
            <form method="POST" action="{{ route('logistics.riders.operational-areas.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-5" data-confirm-title="Activate geographic rider checks for this branch?" data-confirm-message="After the first area is saved, ONLY riders explicitly assigned to matching barangays will qualify for new pickups/deliveries from this branch. Add the riders' coverage before testing orders." data-confirm-button="Save service area">
                @csrf
                <label class="sh-field xl:col-span-2"><span class="sh-label">Active Station / Branch *</span><select name="sorting_center_id" class="sh-select" required><option value="">Select station</option>@foreach($sortingCenters->where('type', 'station_hub')->where('status', 'active') as $center)<option value="{{ $center->id }}" data-province="{{ $center->province }}">{{ $center->name }} · {{ $center->province ?: 'NCR' }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Province (blank for NCR)</span><input class="sh-input" name="province" maxlength="150" value="{{ old('province') }}" placeholder="Laguna"></label>
                <label class="sh-field"><span class="sh-label">City / Municipality *</span><input class="sh-input" name="municipality" required maxlength="150" value="{{ old('municipality') }}" placeholder="Calamba City"></label>
                <label class="sh-field"><span class="sh-label">Barangay (optional)</span><input class="sh-input" name="barangay" maxlength="150" value="{{ old('barangay') }}" placeholder="Canlubang / blank = all"></label>
                <label class="sh-field xl:col-span-2"><span class="sh-label">Work allowed *</span><select name="service_type" class="sh-select"><option value="both">Pickup and delivery</option><option value="pickup">Pickup only</option><option value="delivery">Delivery only</option></select></label>
                <div class="xl:col-span-3 flex items-end justify-end"><button class="sh-btn">Save service area</button></div>
            </form>
        </details>
        @if($operationalAreas->isEmpty())
            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">Legacy routing is still active: no branches have barangay-level service areas configured yet. Add areas, then assign riders before testing new parcels.</div>
        @else
            <div class="mt-3 grid max-h-72 gap-2 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3">
                @foreach($operationalAreas as $area)
                    <div class="flex items-start justify-between gap-2 rounded-xl border border-gray-border bg-gray-bg/40 px-3 py-2">
                        <div class="min-w-0"><p class="truncate text-xs font-bold text-navy">{{ $area->label() }}</p><p class="mt-0.5 text-[10px] text-navy/55">{{ $area->sortingCenter?->name }} · {{ ucfirst($area->service_type) }}</p></div>
                        <form method="POST" action="{{ route('logistics.riders.operational-areas.delete', $area) }}" data-confirm-title="Remove service area?" data-confirm-message="You must unassign all riders before removal." data-confirm-button="Remove" data-confirm-danger="1">@csrf<button type="submit" class="sh-btn-ghost !px-2 !py-1 text-xs" aria-label="Remove area">Remove</button></form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <form method="GET" action="{{ route('logistics.riders.index') }}" class="sh-surface sh-card-pad mb-3 flex flex-wrap items-end gap-2" role="search">
      <label class="sh-field min-w-[180px] flex-1"><span class="sh-label">Search all riders</span><input name="q" value="{{ request('q') }}" class="sh-input" placeholder="Rider, email, or branch" maxlength="100"></label>
      <label class="sh-field min-w-[180px] flex-1"><span class="sh-label">Branch</span><select name="center" class="sh-select"><option value="">All branches</option>@foreach($sortingCenters->where('type','station_hub') as $center)<option value="{{ $center->id }}" @selected((string)request('center') === (string)$center->id)>{{ $center->name }} · {{ $center->province ?: 'NCR' }}</option>@endforeach</select></label>
      <button class="sh-btn" type="submit">Apply filters</button><a href="{{ route('logistics.riders.index') }}" class="sh-btn-ghost">Clear</a>
      <div class="w-full text-xs text-navy/55">Showing {{ $riders->firstItem() ?? 0 }}–{{ $riders->lastItem() ?? 0 }} of {{ number_format($riders->total()) }} matching riders. All-network totals appear above.</div>
    </form>
    <section class="sh-ui-filter" aria-label="Filter rider directory">
        <div class="sh-ui-filter-title"><strong>Rider directory</strong><span>Search and review applications without leaving the page.</span></div>
        <label class="sh-ui-search"><span class="sr-only">Search visible riders</span><input class="sh-input" type="search" placeholder="Search rider, email, branch…" data-ui-list-search="rider-directory" autocomplete="off"></label>
        <label><span class="sr-only">Filter rider status</span><select class="sh-select" data-ui-status-select="rider-directory"><option value="all">All statuses</option><option value="pending">Pending</option><option value="active">Active</option><option value="suspended">Suspended</option><option value="rejected">Rejected</option></select></label>
        <span class="sh-ui-count" data-ui-count="rider-directory" aria-live="polite"></span>
    </section>
    <div id="rider-directory" class="sh-ui-directory grid grid-cols-1 gap-3 xl:grid-cols-2" data-ui-collection>
        @forelse ($riders as $rider)
            @php
                $statusClasses = match ($rider->status) {
                    'active' => 'sh-badge-success',
                    'pending' => 'sh-badge-warning',
                    'rejected' => 'sh-badge-danger',
                    'suspended' => 'sh-badge-neutral',
                    default => 'sh-badge-neutral',
                };
            @endphp

            <article class="sh-surface sh-rider-card sh-ui-record" data-ui-record data-ui-status="{{ $rider->status }}">
                <div class="sh-toolbar items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-base font-bold text-navy">{{ $rider->name }}</h2>
                            <span class="sh-badge {{ $statusClasses }}">{{ ucfirst($rider->status) }}</span>
                            @if ($rider->status === 'active')
                                <span class="sh-badge {{ $rider->availability_status === 'available' ? 'sh-badge-success' : 'sh-badge-warning' }}">{{ $rider->availability_status === 'available' ? 'Available for dispatch' : 'Unavailable for dispatch' }}</span>
                            @endif
                            @if ($rider->email_verified_at)
                                <span class="sh-badge sh-badge-info">Email verified</span>
                            @elseif ($rider->status === 'pending')
                                <span class="sh-badge sh-badge-warning">Waiting for email verification</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-navy/60">{{ $rider->vehicle_type }}{{ $rider->plate_number ? ' · '.$rider->plate_number : '' }}</p>
                        <p class="mt-1 text-sm text-navy/50">{{ $rider->email ?: 'Credentials not provisioned yet' }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-light text-teal-dark">
                        <x-lucide-bike class="h-5 w-5" />
                    </div>
                </div>

                <details class="sh-record-details" @if($rider->status === 'pending') open @endif>
                    <summary class="sh-record-summary"><span>View rider details & actions</span><x-lucide-chevron-down class="h-4 w-4" /></summary>
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
                </details>
            </article>
        @empty
            <div class="sh-empty 2xl:col-span-2">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-bike class="h-5 w-5" /></div>
                <p class="mt-3 text-sm font-semibold text-navy">No riders yet</p>
                <p class="mt-1 text-xs text-navy/45">Mobile rider applications will appear here after registration, or you may add one manually above.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $riders->links() }}</div>
</div>
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
