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

    @if (session('new_credentials'))
        <div role="status" class="sh-alert-success">
            <p class="font-semibold">Branch login ready. Kopyahin ito ngayon, hindi na ito makikita ulit.</p>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                <p>Email: <strong id="cred-email">{{ session('new_credentials.email') }}</strong></p>
                <p>Password: <strong id="cred-password">{{ session('new_credentials.password') }}</strong></p>
            </div>
            <button type="button" id="copy-credentials" class="sh-btn-ghost mt-3">Copy email &amp; password</button>
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="sh-alert-error">
            <p class="font-semibold">Please review the branch details before saving.</p>
            <p class="mt-1 text-xs">{{ $errors->first() }}</p>
        </div>
    @endif

    <section class="sh-surface sh-card-pad">
        <details data-psgc-details {{ $errors->any() ? 'open' : '' }}>
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
                    <p class="mt-1 text-xs text-navy/45">Piliin ang location mula sa listahan, tapos ilagay ang street at unit.</p>

                    <div data-psgc
                         data-init="{{ json_encode(['region' => old('region'), 'province' => old('province'), 'municipality' => old('municipality'), 'barangay' => old('barangay')]) }}"
                         class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <label class="sh-field">
                            <span class="sh-label">Region</span>
                            <select name="region" required data-level="region" class="sh-select"><option value="">Loading…</option></select>
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">Province / NCR</span>
                            <select name="province" required data-level="province" class="sh-select" disabled><option value="">Select province</option></select>
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">City / Municipality</span>
                            <select name="municipality" required data-level="municipality" class="sh-select" disabled><option value="">Select city / municipality</option></select>
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">Barangay</span>
                            <select name="barangay" required data-level="barangay" class="sh-select" disabled><option value="">Select barangay</option></select>
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">Street / House No.</span>
                            <input name="street_no" required maxlength="180" value="{{ old('street_no') }}" placeholder="123 Rizal St." class="sh-input">
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">Unit / Building (optional)</span>
                            <input name="unit_no" maxlength="180" value="{{ old('unit_no') }}" placeholder="Warehouse A" class="sh-input">
                        </label>
                    </div>
                </div>

                <div class="sh-surface-soft sh-card-pad">
                    <h3 class="text-sm font-bold text-navy">Branch login</h3>
                    <p class="mt-1 text-xs text-navy/45">Ikaw ang magtatakda ng email at password. Ibibigay mo ito sa branch para makapasok sa kanilang interface.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="sh-field">
                            <span class="sh-label">Login email</span>
                            <input type="email" name="login_email" required maxlength="190" autocomplete="off" value="{{ old('login_email') }}" placeholder="santacruz@yourlogistics.com" class="sh-input">
                        </label>
                        <label class="sh-field">
                            <span class="sh-label">Password (min. 8 characters)</span>
                            <div class="flex gap-2">
                                <input type="text" name="login_password" required minlength="8" maxlength="100" autocomplete="new-password" data-password-input class="sh-input">
                                <button type="button" data-generate-password class="sh-btn-ghost whitespace-nowrap">Generate</button>
                            </div>
                        </label>
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

    @if ($centers->count() > 0)
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold text-navy/50">{{ $centers->count() }} sorting {{ \Illuminate\Support\Str::plural('center', $centers->count()) }}</p>
            <div class="inline-flex items-center gap-1 rounded-2xl border border-gray-border bg-white p-1" role="group" aria-label="Switch view">
                <button type="button" data-view-btn="cards" aria-pressed="true" class="sh-btn-soft">Cards</button>
                <button type="button" data-view-btn="list" aria-pressed="false" class="sh-btn-ghost">List</button>
            </div>
        </div>
    @endif

    <div data-centers data-view="cards" class="grid gap-5 2xl:grid-cols-2">
        @forelse ($centers as $center)
            <article class="sh-surface sh-card-pad" data-center-card>
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

                {{-- Compact summary: lumalabas lang sa List view --}}
                <div data-list-summary class="text-navy/60">
                    <p>{{ $center->addressLabel() }}</p>
                    <p>{{ $center->coverageAreas->count() }} areas · {{ $center->riders->count() }} riders · {{ $center->user?->email ?? 'No login' }}</p>
                    <button type="button" data-toggle-body class="sh-btn-soft">Details</button>
                </div>

                <div data-center-body>
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

                @if ($center->user)
                    <form method="POST" action="{{ route('logistics.sorting-centers.reset-password', $center) }}"
                          data-confirm-title="Reset branch password?"
                          data-confirm-message="Papalitan ang password ng branch. Hindi na gagana ang luma at kailangan itong ibigay ulit sa branch."
                          data-confirm-button="Reset password"
                          data-confirm-danger="1"
                          class="mt-4 rounded-2xl border border-gray-border bg-white px-4 py-4">
                        @csrf
                        <p class="sh-label">Branch login</p>
                        <p class="mt-1 truncate text-sm font-semibold text-navy">{{ $center->user->email }}</p>

                        <div class="mt-3 grid gap-3 md:grid-cols-[1fr_auto]">
                            <div class="flex gap-2">
                                <input type="text" name="login_password" required minlength="8" maxlength="100"
                                       autocomplete="new-password" placeholder="New password (min. 8 characters)"
                                       data-password-input class="sh-input">
                                <button type="button" data-generate-password class="sh-btn-ghost whitespace-nowrap">Generate</button>
                            </div>
                            <button class="sh-btn-ghost">Reset password</button>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('logistics.sorting-centers.create-login', $center) }}" class="mt-4 rounded-2xl border border-gray-border bg-white px-4 py-4">
                        @csrf
                        <p class="sh-label">Branch login</p>
                        <p class="mt-1 text-xs text-amber-700">Wala pang login ang branch na ito.</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                            <input type="email" name="login_email" required maxlength="190" autocomplete="off" placeholder="Login email" class="sh-input">
                            <div class="flex gap-2">
                                <input type="text" name="login_password" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="Password" data-password-input class="sh-input">
                                <button type="button" data-generate-password class="sh-btn-ghost whitespace-nowrap">Generate</button>
                            </div>
                            <button class="sh-btn">Create login</button>
                        </div>
                    </form>
                @endif

                <details data-psgc-details class="mt-5 overflow-hidden rounded-2xl border border-gray-border bg-gray-bg/45">
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
                        </div>

                        <div data-psgc
                             data-init="{{ json_encode(['region' => $center->region, 'province' => $center->province, 'municipality' => $center->municipality, 'barangay' => $center->barangay]) }}"
                             class="grid gap-4 sm:grid-cols-2">
                            <label class="sh-field">
                                <span class="sh-label">Region</span>
                                <select name="region" required data-level="region" class="sh-select"><option value="{{ $center->region }}">{{ $center->region }}</option></select>
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">Province / NCR</span>
                                <select name="province" required data-level="province" class="sh-select"><option value="{{ $center->province }}">{{ $center->province }}</option></select>
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">City / Municipality</span>
                                <select name="municipality" required data-level="municipality" class="sh-select"><option value="{{ $center->municipality }}">{{ $center->municipality }}</option></select>
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">Barangay</span>
                                <select name="barangay" required data-level="barangay" class="sh-select"><option value="{{ $center->barangay }}">{{ $center->barangay }}</option></select>
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">Street / House No.</span>
                                <input name="street_no" required value="{{ $center->street_no }}" class="sh-input">
                            </label>
                            <label class="sh-field">
                                <span class="sh-label">Unit / Building</span>
                                <input name="unit_no" value="{{ $center->unit_no }}" class="sh-input">
                            </label>
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
                </div>{{-- /data-center-body --}}
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

{{-- Cards / List view toggle --}}
<style>
    [data-list-summary] { display: none; }
    [data-centers][data-view="list"] { grid-template-columns: minmax(0, 1fr) !important; gap: .75rem !important; }
    [data-centers][data-view="list"] [data-list-summary] {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        margin-top: .75rem; font-size: .75rem;
    }
    [data-centers][data-view="list"] [data-list-summary] p {
        margin: 0; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    [data-centers][data-view="list"] [data-list-summary] p:first-child { flex: 1 1 auto; }
    [data-centers][data-view="list"] [data-list-summary] p:nth-child(2) { flex: 0 1 auto; }
    [data-centers][data-view="list"] [data-list-summary] button { flex: none; }
    [data-centers][data-view="list"] [data-center-body] { display: none; }
    [data-centers][data-view="list"] [data-center-card][data-open] [data-center-body] { display: block; }
</style>
<script>
(function () {
    const grid = document.querySelector('[data-centers]');
    if (!grid) return;

    const buttons = document.querySelectorAll('[data-view-btn]');
    const KEY = 'sh-sorting-centers-view';

    function apply(mode) {
        grid.dataset.view = mode;

        buttons.forEach((b) => {
            const on = b.dataset.viewBtn === mode;
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
            b.classList.toggle('sh-btn-soft', on);
            b.classList.toggle('sh-btn-ghost', !on);
        });

        if (mode === 'cards') {
            grid.querySelectorAll('[data-center-card][data-open]').forEach((c) => c.removeAttribute('data-open'));
            grid.querySelectorAll('[data-toggle-body]').forEach((b) => { b.textContent = 'Details'; });
        }
    }

    let saved = 'cards';
    try { saved = localStorage.getItem(KEY) || 'cards'; } catch (e) {}
    apply(saved === 'list' ? 'list' : 'cards');

    buttons.forEach((b) => b.addEventListener('click', () => {
        apply(b.dataset.viewBtn);
        try { localStorage.setItem(KEY, b.dataset.viewBtn); } catch (e) {}
    }));

    grid.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-toggle-body]');
        if (!toggle) return;
        const card = toggle.closest('[data-center-card]');
        const isOpen = card.hasAttribute('data-open');
        card.toggleAttribute('data-open', !isOpen);
        toggle.textContent = isOpen ? 'Details' : 'Hide';
    });
})();
</script>

{{-- Coverage rows (add / remove) --}}
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

{{-- Password generator + copy credentials --}}
<script>
(function () {
    document.addEventListener('click', function (event) {
        const gen = event.target.closest('[data-generate-password]');
        if (gen) {
            const input = gen.parentElement.querySelector('[data-password-input]');
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
            const bytes = crypto.getRandomValues(new Uint32Array(12));
            if (input) input.value = Array.from(bytes, (b) => chars[b % chars.length]).join('');
            return;
        }
        const copy = event.target.closest('#copy-credentials');
        if (copy) {
            const text = 'Email: ' + document.getElementById('cred-email').textContent.trim()
                + '\nPassword: ' + document.getElementById('cred-password').textContent.trim();
            navigator.clipboard.writeText(text).then(() => { copy.textContent = 'Copied!'; });
        }
    });
})();
</script>

{{-- PSGC address dropdowns --}}
<script>
(function () {
    const API = 'https://psgc.gitlab.io/api';
    const cache = {};

    function get(path) {
        if (!cache[path]) {
            cache[path] = fetch(API + path).then((res) => {
                if (!res.ok) throw new Error('PSGC request failed: ' + path);
                return res.json();
            }).catch((err) => { delete cache[path]; throw err; });
        }
        return cache[path];
    }

    function fill(select, items, selected, placeholder, keepOrder) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        if (!keepOrder) items.sort((a, b) => a.label.localeCompare(b.label));
        let found = false;
        items.forEach((i) => {
            const opt = new Option(i.label, i.label);
            opt.dataset.code = i.code;
            if (i.type) opt.dataset.type = i.type;
            if (selected && i.label === selected) { opt.selected = true; found = true; }
            select.add(opt);
        });
        // Keep old saved value kahit hindi match sa API (para hindi mawala sa edit)
        if (selected && !found) {
            select.add(new Option(selected, selected, true, true));
        }
        select.disabled = items.length === 0 && !selected;
    }

    function reset(select, placeholder) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        select.disabled = true;
    }

    const codeOf = (select) => select.selectedOptions[0]?.dataset.code;

    async function initPsgc(root) {
        if (root.dataset.psgcReady) return;
        root.dataset.psgcReady = '1';

        const el = {};
        root.querySelectorAll('[data-level]').forEach((s) => (el[s.dataset.level] = s));
        let saved = {};
        try { saved = JSON.parse(root.dataset.init || '{}') || {}; } catch (e) {}

        async function loadRegions() {
            const data = await get('/regions/');
            fill(el.region, data.map((r) => ({
                code: r.code,
                label: r.regionName ? `${r.name} (${r.regionName})` : r.name,
            })), saved.region, 'Select region', true);
        }

        async function loadProvinces() {
            const rc = codeOf(el.region);
            if (!rc) return reset(el.province, 'Select province');
            const data = await get(`/regions/${rc}/provinces/`);
            let items = data.map((p) => ({ code: p.code, label: p.name, type: 'province' }));
            // NCR walang provinces sa PSGC → gamitin ang region mismo
            if (!items.length) items = [{ code: rc, label: 'Metro Manila', type: 'region' }];
            fill(el.province, items, saved.province, 'Select province');
        }

        async function loadCities() {
            const opt = el.province.selectedOptions[0];
            if (!opt || !opt.dataset.code) return reset(el.municipality, 'Select city / municipality');
            const base = opt.dataset.type === 'region' ? 'regions' : 'provinces';
            const data = await get(`/${base}/${opt.dataset.code}/cities-municipalities/`);
            fill(el.municipality, data.map((c) => ({ code: c.code, label: c.name })), saved.municipality, 'Select city / municipality');
        }

        async function loadBarangays() {
            const cc = codeOf(el.municipality);
            if (!cc) return reset(el.barangay, 'Select barangay');
            const data = await get(`/cities-municipalities/${cc}/barangays/`);
            fill(el.barangay, data.map((b) => ({ code: b.code, label: b.name })), saved.barangay, 'Select barangay');
        }

        el.region.addEventListener('change', async () => {
            saved.province = saved.municipality = saved.barangay = null;
            reset(el.municipality, 'Select city / municipality');
            reset(el.barangay, 'Select barangay');
            try { await loadProvinces(); } catch (e) { console.error(e); }
        });
        el.province.addEventListener('change', async () => {
            saved.municipality = saved.barangay = null;
            reset(el.barangay, 'Select barangay');
            try { await loadCities(); } catch (e) { console.error(e); }
        });
        el.municipality.addEventListener('change', async () => {
            saved.barangay = null;
            try { await loadBarangays(); } catch (e) { console.error(e); }
        });

        try {
            await loadRegions();
            await loadProvinces();
            await loadCities();
            await loadBarangays();
        } catch (e) {
            console.error(e);
            // Kung bumagsak ang API, naiiwan ang naka-save na value sa dropdowns kaya hindi mawawala ang data.
        }
    }

    // Create form: i-init agad
    const createRoot = document.querySelector('section [data-psgc]');
    if (createRoot) initPsgc(createRoot);

    // Edit forms: i-init lang kapag binuksan (para di mag-load ng sabay-sabay)
    document.addEventListener('toggle', function (event) {
        const details = event.target;
        if (!(details instanceof HTMLDetailsElement) || !details.open) return;
        details.querySelectorAll('[data-psgc]').forEach(initPsgc);
    }, true);
})();
</script>
@endsection