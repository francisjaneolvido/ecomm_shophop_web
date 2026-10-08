@extends('logistics.layouts.console')

@section('title', 'Sorting Centers — ShopHop Logistics')

@section('content')
@php
    $activeCount = $centers->where('status', 'active')->count();
    $coverageCount = $centers->sum(fn ($center) => $center->coverageAreas->count());
    $riderCount = $centers->sum(fn ($center) => $center->riders->count());
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Branch network</p>
            <h1 class="title">Sorting Centers</h1>
            <p class="description">Manage the hubs that receive, sort, transfer, and dispatch parcels. This page is optimized for quick updates, cleaner spacing, and safer actions.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <div class="sh-stat min-w-[140px]">
                <p class="sh-stat-label">Active centers</p>
                <p class="sh-stat-value">{{ $activeCount }}</p>
                <p class="sh-stat-note">Live branch network</p>
            </div>
            <div class="sh-stat min-w-[140px]">
                <p class="sh-stat-label">Coverage areas</p>
                <p class="sh-stat-value">{{ $coverageCount }}</p>
                <p class="sh-stat-note">Across all branches</p>
            </div>
            <div class="sh-stat min-w-[140px]">
                <p class="sh-stat-label">Assigned riders</p>
                <p class="sh-stat-value">{{ $riderCount }}</p>
                <p class="sh-stat-note">Current fleet support</p>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div role="status" class="sh-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div role="alert" class="sh-alert-error">
            <p class="font-semibold">Please review the branch details before saving.</p>
            <p class="mt-1 text-xs">{{ $errors->first() }}</p>
        </div>
    @endif

    <section class="sh-surface sh-card-pad">
        <details {{ $errors->any() ? 'open' : '' }}>
            <summary class="sh-summary">
                <div class="sh-toolbar">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark">
                            <x-lucide-plus class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-navy">Add Sorting Center</h2>
                            <p class="mt-1 text-xs text-navy/50">Create another operational hub under the same logistics partner.</p>
                        </div>
                    </div>
                    <span class="sh-btn-soft">Open branch form</span>
                </div>
            </summary>

            <form method="POST" action="{{ route('logistics.sorting-centers.store') }}" class="mt-6 sh-stack">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="sh-field">
                        <span class="sh-label">Branch / Sorting Center name</span>
                        <input name="name" required maxlength="180" value="{{ old('name') }}" placeholder="e.g. Santa Cruz Sorting Center" class="sh-input">
                    </label>
                    <label class="sh-field">
                        <span class="sh-label">Contact number</span>
                        <input name="contact_no" maxlength="30" value="{{ old('contact_no') }}" placeholder="09XX XXX XXXX" class="sh-input">
                    </label>
                </div>

                <div class="sh-surface-soft sh-card-pad">
                    <h3 class="text-sm font-bold text-navy">Branch address</h3>
                    <p class="mt-1 text-xs text-navy/45">Use the exact physical location where riders or transfer parcels will be received and scanned.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ([
                            ['region', 'Region', 'Region IV-A (CALABARZON)'],
                            ['province', 'Province / NCR', 'Laguna'],
                            ['municipality', 'City / Municipality', 'Santa Cruz'],
                            ['barangay', 'Barangay', 'Poblacion'],
                            ['street_no', 'Street / House No.', '123 Rizal St.'],
                            ['unit_no', 'Unit / Building (optional)', 'Warehouse A'],
                        ] as [$name, $label, $placeholder])
                            <label class="sh-field">
                                <span class="sh-label">{{ $label }}</span>
                                <input name="{{ $name }}" {{ $name !== 'unit_no' ? 'required' : '' }} maxlength="180" value="{{ old($name) }}" placeholder="{{ $placeholder }}" class="sh-input">
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sh-surface-soft sh-card-pad">
                    <div class="sh-toolbar">
                        <div>
                            <h3 class="text-sm font-bold text-navy">Delivery coverage</h3>
                            <p class="mt-1 text-xs text-navy/45">Coverage determines which parcels route to this center and which riders may handle them.</p>
                        </div>
                        <button type="button" data-add-coverage="new" class="sh-btn-ghost">+ Add area</button>
                    </div>
                    <div data-coverage-list="new" class="mt-4 grid gap-3">
                        <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-white p-3 lg:grid-cols-[150px_1fr_1.4fr_auto]">
                            <select name="coverage[0][area_type]" required class="sh-select">
                                <option value="province">Province</option>
                                <option value="region">Region</option>
                            </select>
                            <input name="coverage[0][area_name]" required maxlength="150" placeholder="Laguna" class="sh-input">
                            <input name="coverage[0][cities]" maxlength="5000" placeholder="ALL or Santa Cruz, Pagsanjan" class="sh-input">
                            <button type="button" data-remove-coverage class="sh-btn-danger">Remove</button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="sh-btn">Create branch</button>
                </div>
            </form>
        </details>
    </section>

    <div class="grid gap-5 2xl:grid-cols-2">
        @forelse ($centers as $center)
            <article class="sh-surface sh-card-pad">
                <div class="sh-toolbar">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $center->is_main ? 'bg-teal text-white' : 'bg-teal-light text-teal-dark' }}">
                            <x-lucide-warehouse class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-base font-bold text-navy">{{ $center->name }}</h2>
                                @if ($center->is_main)
                                    <span class="sh-badge sh-badge-success">Main hub</span>
                                @endif
                                <span class="sh-badge {{ $center->status === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ ucfirst($center->status) }}</span>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-navy/40">{{ $center->code }}</p>
                        </div>
                    </div>
                    @if (! $center->is_main)
                        <form method="POST" action="{{ route('logistics.sorting-centers.toggle', $center) }}" data-confirm-title="{{ $center->status === 'active' ? 'Deactivate this branch?' : 'Activate this branch?' }}" data-confirm-message="{{ $center->status === 'active' ? 'The branch will stop accepting active logistics operations until reactivated.' : 'This branch will become available again for routing and assignments.' }}" data-confirm-button="{{ $center->status === 'active' ? 'Deactivate branch' : 'Activate branch' }}" data-confirm-danger="{{ $center->status === 'active' ? '1' : '0' }}">
                            @csrf
                            <button class="{{ $center->status === 'active' ? 'sh-btn-danger' : 'sh-btn-ghost' }}">{{ $center->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                    @endif
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="sh-surface-soft p-4">
                        <p class="sh-stat-label">Coverage areas</p>
                        <p class="mt-2 text-xl font-bold text-navy">{{ $center->coverageAreas->count() }}</p>
                    </div>
                    <div class="sh-surface-soft p-4">
                        <p class="sh-stat-label">Assigned riders</p>
                        <p class="mt-2 text-xl font-bold text-navy">{{ $center->riders->count() }}</p>
                    </div>
                    <div class="sh-surface-soft p-4">
                        <p class="sh-stat-label">Contact</p>
                        <p class="mt-2 truncate text-sm font-semibold text-navy">{{ $center->contact_no ?: '—' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-gray-border bg-white px-4 py-4">
                    <p class="sh-label">Address</p>
                    <p class="mt-1 text-sm font-semibold text-navy">{{ $center->addressLabel() }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($center->coverageAreas as $area)
                            <span class="sh-badge sh-badge-success">{{ $area->area_name }}{{ $area->cities && strtoupper($area->cities) !== 'ALL' ? ' · '.$area->cities : '' }}</span>
                        @empty
                            <span class="text-xs text-amber-700">No coverage area configured yet.</span>
                        @endforelse
                    </div>
                </div>

                <details class="mt-5 overflow-hidden rounded-2xl border border-gray-border bg-gray-bg/45">
                    <summary class="sh-summary flex items-center justify-between gap-3 px-4 py-3.5">
                        <div>
                            <p class="text-sm font-bold text-navy">Edit center details</p>
                            <p class="mt-1 text-xs text-navy/45">Update branch details, address, and delivery coverage.</p>
                        </div>
                        <span class="sh-btn-soft">Open editor</span>
                    </summary>

                    <form method="POST" action="{{ route('logistics.sorting-centers.update', $center) }}" class="grid gap-5 border-t border-gray-border px-4 py-4 sm:px-5">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="sh-field">
                                <span class="sh-label">Center name</span>
                                <input name="name" required value="{{ $center->name }}" class="sh-input">
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">Contact number</span>
                                <input name="contact_no" value="{{ $center->contact_no }}" class="sh-input">
                            </label>
                            @foreach ([['region', 'Region'], ['province', 'Province / NCR'], ['municipality', 'City / Municipality'], ['barangay', 'Barangay'], ['street_no', 'Street / House No.'], ['unit_no', 'Unit / Building']] as [$name, $label])
                                <label class="sh-field">
                                    <span class="sh-label">{{ $label }}</span>
                                    <input name="{{ $name }}" {{ $name !== 'unit_no' ? 'required' : '' }} value="{{ $center->{$name} }}" class="sh-input">
                                </label>
                            @endforeach
                        </div>

                        <div class="sh-surface-soft sh-card-pad">
                            <div class="sh-toolbar">
                                <div>
                                    <p class="text-sm font-bold text-navy">Coverage areas</p>
                                    <p class="mt-1 text-xs text-navy/45">Add or remove the locations serviced by this branch.</p>
                                </div>
                                <button type="button" data-add-coverage="center-{{ $center->id }}" class="sh-btn-ghost">+ Add area</button>
                            </div>

                            <div data-coverage-list="center-{{ $center->id }}" class="mt-4 grid gap-3">
                                @forelse ($center->coverageAreas as $i => $area)
                                    <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-white p-3 lg:grid-cols-[150px_1fr_1.4fr_auto]">
                                        <select name="coverage[{{ $i }}][area_type]" class="sh-select">
                                            <option value="province" @selected($area->area_type === 'province')>Province</option>
                                            <option value="region" @selected($area->area_type === 'region')>Region</option>
                                        </select>
                                        <input name="coverage[{{ $i }}][area_name]" required value="{{ $area->area_name }}" class="sh-input">
                                        <input name="coverage[{{ $i }}][cities]" value="{{ $area->cities }}" placeholder="ALL or cities" class="sh-input">
                                        <button type="button" data-remove-coverage class="sh-btn-danger">Remove</button>
                                    </div>
                                @empty
                                    <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-white p-3 lg:grid-cols-[150px_1fr_1.4fr_auto]">
                                        <select name="coverage[0][area_type]" class="sh-select"><option value="province">Province</option><option value="region">Region</option></select>
                                        <input name="coverage[0][area_name]" required placeholder="Laguna" class="sh-input">
                                        <input name="coverage[0][cities]" placeholder="ALL or cities" class="sh-input">
                                        <button type="button" data-remove-coverage class="sh-btn-danger">Remove</button>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button class="sh-btn">Save changes</button>
                        </div>
                    </form>
                </details>
            </article>
        @empty
            <div class="sh-empty 2xl:col-span-2">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-warehouse class="h-5 w-5" /></div>
                <p class="mt-3 text-sm font-semibold text-navy">No Sorting Centers yet</p>
                <p class="mt-1 text-xs text-navy/45">Create your first branch above to begin parcel routing and rider assignment.</p>
            </div>
        @endforelse
    </div>
</div>

<template id="coverage-row-template">
    <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-white p-3 lg:grid-cols-[150px_1fr_1.4fr_auto]">
        <select data-field="area_type" required class="sh-select"><option value="province">Province</option><option value="region">Region</option></select>
        <input data-field="area_name" required maxlength="150" placeholder="Laguna" class="sh-input">
        <input data-field="cities" maxlength="5000" placeholder="ALL or cities / municipalities" class="sh-input">
        <button type="button" data-remove-coverage class="sh-btn-danger">Remove</button>
    </div>
</template>

<script>
(function () {
    const template = document.getElementById('coverage-row-template');
    if (!template) return;

    function renumber(list) {
        list.querySelectorAll('[data-coverage-row]').forEach((row, index) => {
            row.querySelectorAll('[name], [data-field]').forEach((field) => {
                const key = field.dataset.field || ((field.name.match(/\]\[([^\]]+)\]$/) || [])[1]);
                if (key) field.name = `coverage[${index}][${key}]`;
            });
        });
    }

    document.addEventListener('click', function (event) {
        const add = event.target.closest('[data-add-coverage]');
        if (!add) return;
        const key = add.dataset.addCoverage;
        const list = document.querySelector(`[data-coverage-list="${key}"]`);
        if (!list) return;
        list.appendChild(template.content.cloneNode(true));
        renumber(list);
    });
})();
</script>
@endsection
