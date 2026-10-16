@extends('logistics.layouts.console')
@section('title', $center->name.' — Branch details')
@section('content')
@php
    $hasManager = $center->staff->contains(fn ($user) => $user->role === 'manager' && $user->status === 'active');
    $hasCoverage = $center->coverageAreas->isNotEmpty();
    $canActivate = $hasManager && $hasCoverage;
    $activeCount = $center->staff->where('status', 'active')->count();
    $archiveDue = $center->deactivated_at?->copy()->addDays(30);
@endphp
<div class="mx-auto max-w-6xl space-y-6" id="branchWorkspace">
    <a href="{{ route('logistics.sorting-centers.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-teal-dark hover:underline"><x-lucide-arrow-left class="h-4 w-4"/> Branch directory</a>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">{{ $partner->company_name }} / {{ $center->code }}</p>
            <div class="mt-1 flex flex-wrap items-center gap-2"><h1 class="title">{{ $center->name }}</h1>@if($center->is_main)<span class="sh-badge sh-badge-neutral">Main hub</span>@endif<span class="sh-badge {{ $center->status === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $center->status === 'active' ? 'Active' : 'Draft / Inactive' }}</span></div>
            <p class="mt-2 max-w-3xl text-sm text-navy/60">Branch management: warehouse details, delivery service areas, assigned people and readiness to handle parcels.</p>
        </div>
        <a href="{{ route('logistics.sorting-centers.edit', $center) }}" class="sh-btn-soft inline-flex items-center gap-2"><x-lucide-pencil class="h-4 w-4"/> Edit address &amp; coverage</a>
    </div>
    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error" role="alert"><p class="font-semibold">The requested action could not be completed.</p><ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('new_staff_credentials'))
        <div class="rounded-2xl border-2 border-teal bg-teal-light p-5" role="status">
            <h2 class="font-bold text-navy">Temporary staff login — shown once</h2>
            <p class="mt-2 text-xs text-navy/60">Share privately with the branch representative. Ask them to change their password on their first Web login. This is a staff login, not a new Logistics company.</p>
            <p class="mt-3 text-sm"><strong>Email:</strong> {{ session('new_staff_credentials')['email'] }}</p>
            <p class="mt-1 text-sm"><strong>Temporary password:</strong> <code class="rounded bg-white px-2 py-1 font-bold">{{ session('new_staff_credentials')['password'] }}</code></p>
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,0.8fr)]">
        <section class="sh-surface p-5" aria-labelledby="readinessTitle">
            <h2 id="readinessTitle" class="text-base font-bold text-navy">Branch readiness</h2><p class="mt-1 text-xs text-navy/50">Complete these before using the branch for new routing.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-border p-3"><p class="text-xs font-semibold {{ $hasCoverage ? 'text-teal-dark' : 'text-amber-700' }}">{{ $hasCoverage ? '✓' : '○' }} Coverage configured</p><p class="mt-1 text-xs text-navy/55">{{ $center->coverageAreas->count() }} service area(s)</p><a href="{{ route('logistics.sorting-centers.edit', $center) }}" class="mt-2 inline-block text-xs font-semibold text-teal-dark hover:underline">Manage coverage →</a></div>
                <div class="rounded-xl border border-gray-border p-3"><p class="text-xs font-semibold {{ $hasManager ? 'text-teal-dark' : 'text-amber-700' }}">{{ $hasManager ? '✓' : '○' }} Branch manager assigned</p><p class="mt-1 text-xs text-navy/55">{{ $activeCount }} active operator(s)</p><a href="#branchStaff" class="mt-2 inline-block text-xs font-semibold text-teal-dark hover:underline">Manage staff →</a></div>
            </div>
            @if(!$canActivate)
                <p class="mt-3 text-xs text-amber-700">{{ $center->is_main ? 'Legacy main hub: assign a manager for accountable scans.' : 'Activation requires an active manager and at least one coverage area.' }}</p>
            @elseif($center->status === 'inactive')
                <p class="mt-3 text-xs text-teal-dark">This branch is ready. Activate it to allow new assignments.</p>
            @endif
        </section>
        <section class="sh-surface p-5" aria-labelledby="statusTitle">
            <h2 id="statusTitle" class="text-base font-bold text-navy">Operating status</h2>
            <p class="mt-2 text-sm font-bold text-navy">{{ $center->status === 'active' ? 'Active facility' : 'Draft / inactive facility' }}</p>
            <p class="mt-2 text-xs leading-5 text-navy/60">{{ $center->status === 'active' ? 'Available for parcel routing, transfers and scanning.' : 'Unavailable for new routing until activated.' }}</p>
            @unless($center->is_main)
                <form class="mt-4" method="POST" action="{{ route('logistics.sorting-centers.toggle', $center) }}" data-confirm-title="{{ $center->status === 'active' ? 'Deactivate branch?' : 'Activate branch?' }}" data-confirm-message="{{ $center->status === 'active' ? 'The system will reject deactivation if unfinished parcels or transfers exist.' : 'This will allow parcel assignments for the branch.' }}" data-confirm-button="{{ $center->status === 'active' ? 'Deactivate branch' : 'Activate branch' }}" data-confirm-danger="{{ $center->status === 'active' ? '1' : '0' }}">@csrf
                    <button class="{{ $center->status === 'active' ? 'sh-btn-danger' : 'sh-btn' }}" {{ $center->status === 'inactive' && ! $canActivate ? 'disabled' : '' }}>{{ $center->status === 'active' ? 'Deactivate branch' : 'Activate branch' }}</button>
                </form>
                @if($center->status === 'inactive')
                    <div class="mt-4 border-t border-gray-border pt-4">
                        <h3 class="text-sm font-bold text-navy">Archive or restore later</h3>
                        <p class="mt-2 text-xs leading-5 text-navy/60">@if($archiveDue)Auto-archive eligible after {{ $archiveDue->format('M j, Y') }} (30 days from deactivation).@elseThis draft has not been deliberately deactivated; no automatic archive countdown yet.@endif Historical deliveries, scans, and earnings stay available. To archive, first move all assigned riders and finish outstanding parcel movements.</p>
                        <form class="mt-3" method="POST" action="{{ route('logistics.sorting-centers.archive', $center) }}" data-confirm-title="Archive this branch?" data-confirm-message="This hides the branch from normal operations but preserves all history. You can restore it from Archived Branches." data-confirm-button="Archive branch" data-confirm-danger="1">
                            @csrf
                            <button class="sh-btn-danger" type="submit">Archive branch now</button>
                        </form>
                    </div>
                @endif
            @else<p class="mt-4 text-xs text-navy/55">The main hub remains active by system policy.</p>@endunless
        </section>
    </div>

    <nav class="flex flex-wrap gap-2" aria-label="Branch page sections">
        @foreach([['#facilityDetails','Warehouse'],['#serviceCoverage','Coverage'],['#branchStaff','Staff'],['#branchOperations','Riders & parcels']] as [$href,$label])
            <a href="{{ $href }}" class="sh-btn-ghost text-xs">{{ $label }} ↓</a>
        @endforeach
    </nav>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="sh-surface p-5" id="facilityDetails"><div class="flex items-center justify-between"><h2 class="text-base font-bold text-navy">Physical warehouse</h2><x-lucide-map-pin class="h-5 w-5 text-teal-dark"/></div>
            <p class="mt-2 text-sm leading-6 font-semibold text-navy">{{ $center->addressLabel() }}</p>
            <dl class="mt-4 grid grid-cols-2 gap-3 text-xs"><div><dt class="text-navy/45">Facility code</dt><dd class="mt-1 font-semibold text-navy">{{ $center->code }}</dd></div><div><dt class="text-navy/45">Contact</dt><dd class="mt-1 font-semibold text-navy">{{ $center->contact_no ?: 'Not provided' }}</dd></div><div><dt class="text-navy/45">Region</dt><dd class="mt-1 font-semibold text-navy">{{ $center->region }}</dd></div><div><dt class="text-navy/45">City</dt><dd class="mt-1 font-semibold text-navy">{{ $center->municipality }}</dd></div></dl>
        </section>
        <section class="sh-surface p-5" id="serviceCoverage"><div class="flex items-center justify-between gap-3"><div><h2 class="text-base font-bold text-navy">Delivery service areas</h2><p class="mt-1 text-xs text-navy/50">Where parcels from this facility can be delivered</p></div><a class="text-xs font-bold text-teal-dark hover:underline" href="{{ route('logistics.sorting-centers.edit', $center) }}">Edit</a></div>
            <div class="mt-3 space-y-2">@forelse($center->coverageAreas as $area)
                <div class="rounded-xl border border-gray-border p-3"><div class="flex items-center justify-between gap-2"><span class="font-semibold text-sm text-navy">{{ $area->area_name }}</span><span class="text-[10px] uppercase text-navy/40">{{ $area->area_type }}</span></div><p class="mt-1 break-words text-xs text-navy/55">{{ strtoupper($area->cities ?: 'ALL') === 'ALL' ? 'All cities / municipalities in this area' : str_replace('|', ', ', $area->cities) }}</p>
                @if($area->area_type === 'region' && !str_contains(mb_strtolower($center->region), mb_strtolower($area->area_name)) && !str_contains(mb_strtolower($area->area_name), mb_strtolower($center->region)))<p class="mt-2 text-xs text-amber-700">Cross-region coverage — verify it is intentional.</p>@endif</div>
            @empty<p class="text-xs text-amber-700">No coverage configured. Set a service area before activation.</p>@endforelse</div>
        </section>
    </div>

    <section class="sh-surface p-5" id="branchStaff"><div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-base font-bold text-navy">Branch representatives &amp; scanner staff</h2><p class="mt-1 max-w-2xl text-xs leading-5 text-navy/55">Manager = branch representative and authorized staff operator. Scanner = scan-only mobile operator. Each person has their own login, restricted to this branch.</p></div><span class="sh-badge sh-badge-neutral">{{ $center->staff->count() }} accounts</span></div>
        <div class="mt-4 grid gap-3 md:grid-cols-2">@forelse($center->staff as $operator)
            <div class="rounded-xl border border-gray-border bg-gray-bg/25 p-4"><div class="flex flex-wrap items-start justify-between gap-2"><div><p class="text-sm font-bold text-navy">{{ $operator->name }}</p><p class="mt-1 text-xs text-navy/55">{{ $operator->email }}</p></div><div><span class="sh-badge sh-badge-neutral">{{ ucfirst($operator->role) }}</span><span class="ml-1 text-xs {{ $operator->status === 'active' ? 'text-teal-dark' : 'text-amber-700' }}">{{ ucfirst($operator->status) }}</span></div></div>
                <div class="mt-3 flex flex-wrap gap-2"><form method="POST" action="{{ route('logistics.staff.reset-password', $operator) }}" data-confirm-title="Reset staff password?" data-confirm-message="Existing mobile sessions will be revoked. A new temporary password will be shown once." data-confirm-button="Reset password">@csrf<button class="sh-btn-ghost text-xs">Reset password</button></form>
                    <form method="POST" action="{{ route('logistics.staff.toggle', $operator) }}" data-confirm-title="{{ $operator->status === 'active' ? 'Disable operator?' : 'Enable operator?' }}" data-confirm-message="Only authorized staff can operate the branch. Last active manager cannot be disabled while branch is active." data-confirm-button="Continue" data-confirm-danger="{{ $operator->status === 'active' ? '1' : '0' }}">@csrf<button class="sh-btn-ghost text-xs">{{ $operator->status === 'active' ? 'Disable login' : 'Enable login' }}</button></form></div>
            </div>
        @empty<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">No branch manager yet. Create a manager account below to complete setup.</div>@endforelse</div>
        <details class="mt-5 rounded-xl border border-gray-border p-4" {{ $center->staff->isEmpty() ? 'open' : '' }}><summary class="cursor-pointer text-sm font-bold text-teal-dark">+ Create a manager or scanner login</summary>
            <form method="POST" action="{{ route('logistics.staff.store', $center) }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                <label class="sh-field"><span class="sh-label">Full name *</span><input name="name" required minlength="2" maxlength="150" class="sh-input" placeholder="Staff full name"></label>
                <label class="sh-field"><span class="sh-label">Unique email *</span><input name="email" required type="email" maxlength="190" class="sh-input" placeholder="staff@example.com"></label>
                <label class="sh-field"><span class="sh-label">Access permissions *</span><select name="role" required class="sh-select"><option value="manager">Branch Manager — Web workspace + scanner</option><option value="scanner">Scanner Operator — mobile QR only</option></select></label>
                <div class="flex items-end"><button class="sh-btn">Create staff login</button></div>
            </form><p class="mt-3 text-xs text-navy/55">No public registration needed. The company provides credentials. Temporary password is shown once after creation.</p>
        </details>
    </section>
    <section class="sh-surface p-5" id="branchOperations"><h2 class="text-base font-bold text-navy">Riders &amp; parcel operations</h2><p class="mt-1 text-xs text-navy/55">Riders have separate mobile accounts. Branch staff scan parcels; riders collect, transfer and deliver them.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-4"><div class="rounded-xl bg-gray-bg p-3"><p class="text-xs text-navy/50">Assigned riders</p><p class="mt-1 text-xl font-bold text-navy">{{ $center->riders->count() }}</p></div><div class="rounded-xl bg-gray-bg p-3"><p class="text-xs text-navy/50">Open parcels linked here</p><p class="mt-1 text-xl font-bold text-navy">{{ $openParcels }}</p></div><div class="rounded-xl bg-gray-bg p-3"><p class="text-xs text-navy/50">In-transit transfers</p><p class="mt-1 text-xl font-bold text-navy">{{ $inTransit }}</p></div><div class="rounded-xl bg-gray-bg p-3"><p class="text-xs text-navy/50">Rider assignment</p><p class="mt-1 text-sm font-semibold text-navy">Company fleet management</p></div></div>
        <div class="mt-4 flex flex-wrap gap-2"><a href="{{ route('logistics.riders.index') }}" class="sh-btn-soft">Manage rider assignments →</a><a href="{{ route('logistics.deliveries.board') }}" class="sh-btn-ghost">Open parcel operations →</a></div>
        @if($center->riders->isNotEmpty())<p class="mt-3 text-xs text-navy/55">Assigned: {{ $center->riders->pluck('name')->take(8)->implode(', ') }}</p>@endif
    </section>
</div>
@endsection
