@extends('logistics.layouts.console')
@section('title', 'Branches & Hubs — ShopHop Logistics')
@section('content')
<div class="mx-auto max-w-7xl space-y-6" id="branchDirectory">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">YOUR LOGISTICS NETWORK</p>
            <h1 class="title">Branches &amp; Sorting Hubs</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/60">A <strong>branch</strong> is a physical facility within {{ $partner->company_name }}. Its <strong>sorting hub</strong> receives and dispatches parcels. Each branch can have a manager, scanner operators, riders and its own delivery coverage.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2"><a class="sh-btn-ghost" href="{{ route('logistics.sorting-centers.archived') }}">Archived ({{ $totals['archived'] }})</a>
        <a class="sh-btn inline-flex items-center gap-2" href="{{ route('logistics.sorting-centers.create') }}"><x-lucide-plus class="h-4 w-4"/> New branch</a></div>
    </div>

    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error" role="alert">{{ $errors->first() }}</div>@endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        <div class="sh-surface p-4"><p class="text-xs font-semibold text-navy/55">Total branches</p><p class="mt-1 text-2xl font-bold text-navy">{{ $totals['all'] }}</p></div>
        <div class="sh-surface p-4"><p class="text-xs font-semibold text-navy/55">Active branches</p><p class="mt-1 text-2xl font-bold text-teal-dark">{{ $totals['active'] }}</p></div>
        <div class="sh-surface col-span-2 p-4 lg:col-span-1"><p class="text-xs font-semibold text-navy/55">Setup needed</p><p class="mt-1 text-2xl font-bold text-amber-700">{{ $totals['needs_setup'] }}</p></div>
    </div>

    <div class="rounded-2xl border border-teal/30 bg-teal-light/40 p-4 text-sm text-navy/75">
        <p class="font-bold text-navy">How your network works</p>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="rounded-lg bg-white px-3 py-2">1. Create facility</span><x-lucide-arrow-right class="h-4 w-4"/>
            <span class="rounded-lg bg-white px-3 py-2">2. Set service areas</span><x-lucide-arrow-right class="h-4 w-4"/>
            <span class="rounded-lg bg-white px-3 py-2">3. Assign manager</span><x-lucide-arrow-right class="h-4 w-4"/>
            <span class="rounded-lg bg-white px-3 py-2">4. Activate</span><x-lucide-arrow-right class="h-4 w-4"/>
            <span class="rounded-lg bg-white px-3 py-2">5. Assign riders &amp; handle parcels</span>
        </div>
        <p class="mt-3 text-xs text-navy/55">Branches do not create new logistics companies or separate wallets. Staff log in using company-provisioned credentials. Shipping revenue remains company-level.</p>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">
        <strong>Branch deactivation is reversible.</strong> Deactivated branches stop receiving new parcels. After 30 days, the scheduler archives branches only when no unfinished parcels, transfers or assigned riders remain. Archived branches can be restored; history is never erased.
    </div>
    <section class="sh-surface overflow-hidden" aria-labelledby="directoryTitle">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-border p-4 sm:p-5">
            <div><h2 id="directoryTitle" class="text-base font-bold text-navy">Branch directory</h2><p class="mt-1 text-xs text-navy/50">Open a branch to manage its staff, coverage and operating status.</p></div>
            <form method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto" role="search">
                <label class="sr-only" for="branchSearch">Search branches</label>
                <input id="branchSearch" name="search" value="{{ request('search') }}" placeholder="Name, city or code" class="sh-input min-w-[160px] flex-1" />
                <label class="sr-only" for="branchStatus">Branch status</label>
                <select id="branchStatus" name="status" class="sh-select"><option value="">All statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Draft / Inactive</option></select>
                <button class="sh-btn-soft" type="submit">Filter</button>
                @if(request('search') || request('status'))<a href="{{ route('logistics.sorting-centers.index') }}" class="sh-btn-ghost">Clear</a>@endif
            </form>
        </div>
        <div class="divide-y divide-gray-border">
            @forelse($centers as $center)
                @php
                    $manager = $center->staff->first(fn($staff) => $staff->role === 'manager' && $staff->status === 'active');
                    $ready = $center->coverageAreas->isNotEmpty() && $manager;
                    $areas = $center->coverageAreas->map(fn($area) => $area->area_name)->unique()->take(3);
                @endphp
                <div class="grid gap-4 p-4 transition hover:bg-gray-bg/40 sm:p-5 lg:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)_auto] lg:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold text-navy">{{ $center->name }}</h3>
                            @if($center->is_main)<span class="sh-badge sh-badge-neutral">Main hub</span>@endif
                            <span class="sh-badge {{ $center->status === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $center->status === 'active' ? 'Active' : 'Inactive / Draft' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/50">{{ $center->code }} · {{ $center->municipality }}, {{ $center->province ?: $center->region }}</p>
                        <p class="mt-2 truncate text-xs text-navy/65">{{ $center->addressLabel() }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                        <div><p class="text-navy/45">Manager</p><p class="mt-1 font-semibold text-navy">{{ $manager?->name ?: 'Not assigned' }}</p></div>
                        <div><p class="text-navy/45">Riders</p><p class="mt-1 font-semibold text-navy">{{ $center->riders->count() }} assigned</p></div>
                        <div class="col-span-2"><p class="text-navy/45">Service areas</p><p class="mt-1 font-semibold text-navy">{{ $areas->implode(', ') ?: 'Not set' }}</p></div>
                        @if(! $ready && ! $center->is_main)<p class="col-span-2 text-amber-700">Setup incomplete: {{ !$manager ? 'assign a manager' : 'add coverage' }}</p>@endif
                        @if($center->is_main && !$manager)<p class="col-span-2 text-amber-700">Legacy main hub: manager still needs assignment</p>@endif
                    </div>
                    <div><a class="sh-btn-soft inline-flex w-full items-center justify-center gap-2 lg:w-auto" href="{{ route('logistics.sorting-centers.show', $center) }}">Manage branch <x-lucide-arrow-right class="h-4 w-4"/></a></div>
                </div>
            @empty
                <div class="px-6 py-12 text-center"><x-lucide-warehouse class="mx-auto h-10 w-10 text-navy/25"/><h3 class="mt-3 font-bold text-navy">No matching branches</h3><p class="mt-1 text-sm text-navy/55">Try clearing filters or create a new branch.</p><a class="sh-btn mt-4 inline-flex" href="{{ route('logistics.sorting-centers.create') }}">Create branch</a></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
