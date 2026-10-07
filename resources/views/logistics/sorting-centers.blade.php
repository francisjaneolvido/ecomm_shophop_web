@extends('logistics.layouts')

@section('title', 'Sorting Centers — ShopHop Logistics')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-teal-dark">Branch network</p>
        <h1 class="mt-1 text-2xl font-bold text-navy sm:text-3xl">Sorting Centers</h1>
        <p class="mt-1 max-w-2xl text-sm text-navy/55">
            Manage the main hub and additional branches that receive, sort, transfer, and dispatch parcels.
            Each branch owns its delivery coverage and Riders.
        </p>
    </div>
    <div class="rounded-2xl border border-teal/20 bg-teal-light/50 px-4 py-3 text-xs text-teal-dark">
        <span class="font-bold">{{ $centers->where('status', 'active')->count() }}</span> active center{{ $centers->where('status', 'active')->count() === 1 ? '' : 's' }}
    </div>
</div>

@if (session('status'))
    <div role="status" class="mb-4 rounded-2xl border border-teal/20 bg-teal-light px-4 py-3 text-sm text-teal-dark">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please check the Sorting Center details.</p>
        <p class="mt-1 text-xs">{{ $errors->first() }}</p>
    </div>
@endif

<section class="mb-6 rounded-3xl border border-gray-border bg-white p-5 shadow-soft sm:p-6">
    <details {{ $errors->any() ? 'open' : '' }}>
        <summary class="cursor-pointer list-none">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-light text-teal-dark">
                        <x-lucide-plus class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-bold text-navy">Add Sorting Center branch</h2>
                        <p class="mt-0.5 text-xs text-navy/50">Use this for another hub under the same Logistics company.</p>
                    </div>
                </div>
                <span class="rounded-full border border-gray-border px-3 py-1 text-xs font-semibold text-navy/60">Add branch</span>
            </div>
        </summary>

        <form method="POST" action="{{ route('logistics.sorting-centers.store') }}" class="mt-6 grid gap-5">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <label class="grid gap-1.5 text-xs font-semibold text-navy">
                    Branch / Sorting Center name
                    <input name="name" required maxlength="180" value="{{ old('name') }}" placeholder="e.g. Santa Cruz Sorting Center" class="rounded-xl border border-gray-border px-3 py-2.5 text-sm font-normal outline-none focus:border-teal focus:ring-4 focus:ring-teal/10">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-navy">
                    Contact number
                    <input name="contact_no" maxlength="30" value="{{ old('contact_no') }}" placeholder="09XX XXX XXXX" class="rounded-xl border border-gray-border px-3 py-2.5 text-sm font-normal outline-none focus:border-teal focus:ring-4 focus:ring-teal/10">
                </label>
            </div>

            <div>
                <h3 class="text-sm font-bold text-navy">Branch address</h3>
                <p class="mt-1 text-xs text-navy/45">Use the actual physical address where parcels are received and scanned.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['region', 'Region', 'Region IV-A (CALABARZON)'],
                        ['province', 'Province / NCR', 'Laguna'],
                        ['municipality', 'City / Municipality', 'Santa Cruz'],
                        ['barangay', 'Barangay', 'Poblacion'],
                        ['street_no', 'Street / House No.', '123 Rizal St.'],
                        ['unit_no', 'Unit / Building (optional)', 'Warehouse A'],
                    ] as [$name, $label, $placeholder])
                        <label class="grid gap-1.5 text-xs font-semibold text-navy">
                            {{ $label }}
                            <input name="{{ $name }}" {{ $name !== 'unit_no' ? 'required' : '' }} maxlength="180" value="{{ old($name) }}" placeholder="{{ $placeholder }}" class="rounded-xl border border-gray-border px-3 py-2.5 text-sm font-normal outline-none focus:border-teal focus:ring-4 focus:ring-teal/10">
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-navy">Delivery coverage</h3>
                        <p class="mt-1 text-xs text-navy/45">These areas determine which parcels route to this branch and which Riders may serve them.</p>
                    </div>
                    <button type="button" data-add-coverage="new" class="rounded-full border border-navy/15 px-3 py-1.5 text-xs font-semibold text-navy hover:bg-gray-bg">+ Add area</button>
                </div>

                <div data-coverage-list="new" class="mt-3 grid gap-3">
                    <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-gray-bg/50 p-3 md:grid-cols-[150px_1fr_1.4fr_auto]">
                        <select name="coverage[0][area_type]" required class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
                            <option value="province">Province</option>
                            <option value="region">Region</option>
                        </select>
                        <input name="coverage[0][area_name]" required maxlength="150" placeholder="Laguna" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
                        <input name="coverage[0][cities]" maxlength="5000" placeholder="ALL or Santa Cruz, Pagsanjan" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
                        <button type="button" data-remove-coverage class="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600">Remove</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-dark">Create branch</button>
            </div>
        </form>
    </details>
</section>

<div class="grid gap-4 xl:grid-cols-2">
    @forelse ($centers as $center)
        <article class="rounded-3xl border border-gray-border bg-white p-5 shadow-soft sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $center->is_main ? 'bg-teal text-white' : 'bg-teal-light text-teal-dark' }}">
                        <x-lucide-warehouse class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-navy">{{ $center->name }}</h2>
                            @if ($center->is_main)
                                <span class="rounded-full bg-teal-light px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-teal-dark">Main</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs font-semibold text-navy/40">{{ $center->code }}</p>
                    </div>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $center->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ ucfirst($center->status) }}
                </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-gray-bg p-3"><p class="text-[10px] uppercase tracking-wide text-navy/40">Coverage</p><p class="mt-1 font-bold text-navy">{{ $center->coverageAreas->count() }}</p></div>
                <div class="rounded-2xl bg-gray-bg p-3"><p class="text-[10px] uppercase tracking-wide text-navy/40">Riders</p><p class="mt-1 font-bold text-navy">{{ $center->riders->count() }}</p></div>
                <div class="col-span-2 rounded-2xl bg-gray-bg p-3 sm:col-span-1"><p class="text-[10px] uppercase tracking-wide text-navy/40">Contact</p><p class="mt-1 truncate text-xs font-semibold text-navy">{{ $center->contact_no ?: '—' }}</p></div>
            </div>

            <div class="mt-4 rounded-2xl border border-gray-border p-3">
                <p class="text-xs font-semibold text-navy">{{ $center->addressLabel() }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @forelse ($center->coverageAreas as $area)
                        <span class="rounded-full bg-teal-light px-2.5 py-1 text-[10px] font-semibold text-teal-dark">
                            {{ $area->area_name }}{{ $area->cities && strtoupper($area->cities) !== 'ALL' ? ' · '.$area->cities : '' }}
                        </span>
                    @empty
                        <span class="text-xs text-amber-700">No coverage area configured.</span>
                    @endforelse
                </div>
            </div>

            <details class="mt-4 rounded-2xl bg-gray-bg">
                <summary class="cursor-pointer px-4 py-3 text-xs font-bold text-navy">Edit center & coverage</summary>
                <form method="POST" action="{{ route('logistics.sorting-centers.update', $center) }}" class="grid gap-4 px-4 pb-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1 text-[11px] font-semibold text-navy">Center name<input name="name" required value="{{ $center->name }}" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-xs"></label>
                        <label class="grid gap-1 text-[11px] font-semibold text-navy">Contact<input name="contact_no" value="{{ $center->contact_no }}" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-xs"></label>
                        @foreach ([
                            ['region', 'Region'], ['province', 'Province / NCR'], ['municipality', 'City / Municipality'],
                            ['barangay', 'Barangay'], ['street_no', 'Street / House No.'], ['unit_no', 'Unit / Building'],
                        ] as [$name, $label])
                            <label class="grid gap-1 text-[11px] font-semibold text-navy">{{ $label }}<input name="{{ $name }}" {{ $name !== 'unit_no' ? 'required' : '' }} value="{{ $center->{$name} }}" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-xs"></label>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] font-bold text-navy">Coverage areas</p>
                        <button type="button" data-add-coverage="center-{{ $center->id }}" class="rounded-full border border-navy/15 px-2.5 py-1 text-[10px] font-semibold text-navy">+ Add</button>
                    </div>
                    <div data-coverage-list="center-{{ $center->id }}" class="grid gap-2">
                        @foreach ($center->coverageAreas as $i => $area)
                            <div data-coverage-row class="grid gap-2 md:grid-cols-[130px_1fr_1.4fr_auto]">
                                <select name="coverage[{{ $i }}][area_type]" class="rounded-xl border border-gray-border bg-white px-2 py-2 text-xs"><option value="province" @selected($area->area_type === 'province')>Province</option><option value="region" @selected($area->area_type === 'region')>Region</option></select>
                                <input name="coverage[{{ $i }}][area_name]" required value="{{ $area->area_name }}" class="rounded-xl border border-gray-border bg-white px-2 py-2 text-xs">
                                <input name="coverage[{{ $i }}][cities]" value="{{ $area->cities }}" placeholder="ALL or cities" class="rounded-xl border border-gray-border bg-white px-2 py-2 text-xs">
                                <button type="button" data-remove-coverage class="rounded-xl border border-red-200 px-2 py-2 text-[10px] font-semibold text-red-600">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    <button class="justify-self-end rounded-full bg-navy px-4 py-2 text-xs font-semibold text-white">Save changes</button>
                </form>
            </details>

            @if (! $center->is_main)
                <form method="POST" action="{{ route('logistics.sorting-centers.toggle', $center) }}" class="mt-3">
                    @csrf
                    <button class="rounded-full border border-navy/15 px-3 py-1.5 text-xs font-semibold text-navy hover:bg-gray-bg">
                        {{ $center->status === 'active' ? 'Deactivate branch' : 'Activate branch' }}
                    </button>
                </form>
            @endif
        </article>
    @empty
        <div class="rounded-3xl border border-dashed border-gray-border bg-white p-8 text-center text-sm text-navy/50 xl:col-span-2">
            No Sorting Center exists yet. Run the latest migration or create a branch.
        </div>
    @endforelse
</div>

<template id="coverage-row-template">
    <div data-coverage-row class="grid gap-3 rounded-2xl border border-gray-border bg-gray-bg/50 p-3 md:grid-cols-[150px_1fr_1.4fr_auto]">
        <select data-field="area_type" required class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm"><option value="province">Province</option><option value="region">Region</option></select>
        <input data-field="area_name" required maxlength="150" placeholder="Laguna" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
        <input data-field="cities" maxlength="5000" placeholder="ALL or cities / municipalities" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
        <button type="button" data-remove-coverage class="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600">Remove</button>
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
        if (add) {
            const key = add.dataset.addCoverage;
            const list = document.querySelector(`[data-coverage-list="${key}"]`);
            if (!list) return;
            list.appendChild(template.content.cloneNode(true));
            renumber(list);
            return;
        }

        const remove = event.target.closest('[data-remove-coverage]');
        if (!remove) return;
        const list = remove.closest('[data-coverage-list]');
        const row = remove.closest('[data-coverage-row]');
        if (!list || !row) return;
        if (list.querySelectorAll('[data-coverage-row]').length <= 1) return;
        row.remove();
        renumber(list);
    });
})();
</script>
@endsection
