@extends('logistics.layouts.console')
@section('title', 'Province Hubs — ShopHop Logistics')
@section('content')
<div class="mx-auto max-w-7xl space-y-6" id="hubDirectory">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">YOUR LOGISTICS NETWORK</p>
            <h1 class="title">Province Hubs</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/60">A <strong>province hub</strong> is where trucks drop off and collect parcels for a whole province. Each province hub manages its own station hubs.</p>
        </div>
        <button type="button" id="openHubModal" class="sh-btn inline-flex items-center gap-2"><x-lucide-plus class="h-4 w-4"/> New province hub</button>
    </div>

    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif

    @if($errors->hasBag('setPassword'))
        <div role="alert" class="sh-alert-error"><strong>Hindi na-save ang password.</strong>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->getBag('setPassword')->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if(session('new_staff_credentials'))
        <div class="rounded-2xl border-2 border-teal bg-teal-light p-5" role="status">
            <h2 class="font-bold text-navy">Manager login — shown once</h2>
            <p class="mt-2 text-xs text-navy/60">Share privately with the province hub manager.</p>
            <p class="mt-3 text-sm"><strong>Email:</strong> {{ session('new_staff_credentials')['email'] }}</p>
            <p class="mt-1 text-sm"><strong>Password:</strong>
                <code class="rounded bg-white px-2 py-1 font-bold">{{ session('new_staff_credentials')['password'] }}</code>
                <button type="button" class="sh-btn-soft ml-2 text-xs"
                        onclick="navigator.clipboard.writeText(@js(session('new_staff_credentials')['password'])); this.textContent='Copied!'">Copy</button>
            </p>
        </div>
    @endif

    <section class="sh-surface overflow-hidden" aria-labelledby="directoryTitle">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-border p-4 sm:p-5">
            <h2 id="directoryTitle" class="text-base font-bold text-navy">Hub directory</h2>
            <form method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto" role="search">
                <label class="sr-only" for="hubSearch">Search hubs</label>
                <input id="hubSearch" name="search" value="{{ request('search') }}" placeholder="Name, province or code" class="sh-input min-w-[160px] flex-1" />
                <button class="sh-btn-soft" type="submit">Search</button>
                @if(request('search'))<a href="{{ route('logistics.sorting-centers.index') }}" class="sh-btn-ghost">Clear</a>@endif
            </form>
        </div>
        <div class="divide-y divide-gray-border">
            @forelse($centers as $center)
                @php $manager = $center->staff->first(fn ($s) => $s->role === 'manager' && $s->status === 'active'); @endphp
                <div class="grid gap-4 p-4 transition hover:bg-gray-bg/40 sm:p-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1.2fr)] lg:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold text-navy">{{ $center->name }}</h3>
                            <span class="sh-badge {{ $center->status === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $center->status === 'active' ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/50">{{ $center->code }} · {{ $center->province }}, {{ $center->region }}</p>
                        <p class="mt-2 truncate text-xs text-navy/65">{{ $center->addressLabel() }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                        <div><p class="text-navy/45">Manager</p><p class="mt-1 font-semibold text-navy">{{ $manager?->name ?: 'Not assigned' }}</p></div>
                        <div><p class="text-navy/45">Station hubs</p><p class="mt-1 font-semibold text-navy">{{ $center->station_hubs_count }}</p></div>
                        @if($manager)
                            <div class="col-span-2">
                                <p class="text-navy/45">Login email</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <code class="min-w-0 max-w-full truncate rounded bg-gray-bg px-2 py-1 font-semibold text-navy">{{ $manager->email }}</code>
                                    <button type="button" class="sh-btn-ghost text-xs" data-copy="{{ $manager->email }}">Copy</button>
                                    <button type="button" class="sh-btn-soft text-xs"
                                            data-set-password
                                            data-hub="{{ $center->name }}"
                                            data-name="{{ $manager->name }}"
                                            data-email="{{ $manager->email }}"
                                            data-url="{{ route('logistics.staff.set-password', $manager) }}">Set password</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center"><x-lucide-warehouse class="mx-auto h-10 w-10 text-navy/25"/><h3 class="mt-3 font-bold text-navy">No province hubs yet</h3><p class="mt-1 text-sm text-navy/55">Create the first one, for example Laguna or Quezon.</p></div>
            @endforelse
        </div>
    </section>
</div>

{{-- Create province hub --}}
<dialog id="hubModal" class="w-full max-w-3xl rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" action="{{ route('logistics.sorting-centers.store') }}" class="flex max-h-[90vh] flex-col">
        @csrf
        <div class="flex items-start justify-between gap-3 border-b border-gray-border p-5">
            <div>
                <h2 class="text-lg font-bold text-navy">Create province hub</h2>
                <p class="mt-1 text-xs text-navy/55">One province hub per province. It covers the whole province; station hubs are created later by its manager.</p>
            </div>
            <button type="button" data-close-modal class="sh-btn-ghost text-xs" aria-label="Close">✕</button>
        </div>

        <div class="space-y-6 overflow-y-auto p-5">
            @if($errors->any())
                <div role="alert" class="sh-alert-error"><strong>Review the information before saving.</strong>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="space-y-4">
                <h3 class="text-sm font-bold text-navy">1. Hub details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Hub name *</span><input class="sh-input" name="name" value="{{ old('name') }}" required maxlength="180" placeholder="Example: Laguna Province Hub"></label>
                    <label class="sh-field"><span class="sh-label">Contact number</span><input class="sh-input" name="contact_no" value="{{ old('contact_no') }}" maxlength="30" placeholder="09XX XXX XXXX"></label>
                </div>
            </section>

            <section class="space-y-4">
                <h3 class="text-sm font-bold text-navy">2. Warehouse address (truck drop-off)</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach([
                        ['region', 'Region'],
                        ['province', 'Province (served by this hub)'],
                        ['municipality', 'City / municipality'],
                        ['barangay', 'Barangay'],
                    ] as [$field, $label])
                        <label class="sh-field">
                            <span class="sh-label">{{ $label }} *</span>
                            <select class="sh-input" name="{{ $field }}" id="{{ $field }}Select"
                                    data-old="{{ old($field) }}" required disabled>
                                <option value="">Loading…</option>
                            </select>
                        </label>
                    @endforeach

                    <label class="sh-field"><span class="sh-label">Street / house number *</span>
                        <input class="sh-input" name="street_no" value="{{ old('street_no') }}" required maxlength="180" placeholder="Street, building, house no.">
                    </label>
                    <label class="sh-field"><span class="sh-label">Unit / warehouse details (optional)</span>
                        <input class="sh-input" name="unit_no" value="{{ old('unit_no') }}" maxlength="180" placeholder="Floor, suite or warehouse bay">
                    </label>
                </div>
            </section>

            <section class="space-y-4">
                <h3 class="text-sm font-bold text-navy">3. Province hub manager login</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Manager name *</span><input class="sh-input" name="manager_name" value="{{ old('manager_name') }}" required maxlength="150" placeholder="Manager full name"></label>
                    <label class="sh-field"><span class="sh-label">Manager email (login) *</span><input class="sh-input" type="email" name="manager_email" value="{{ old('manager_email') }}" required maxlength="190" placeholder="manager@example.com"></label>
                </div>
                <label class="sh-field">
                    <span class="sh-label">Password *</span>
                    <div class="flex gap-2">
                        <input class="sh-input min-w-0 flex-1" type="password" id="createPassword" name="manager_password" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="At least 8 characters">
                        <button type="button" class="sh-btn-soft text-xs" data-toggle-pw="createPassword">Show</button>
                        <button type="button" class="sh-btn-soft text-xs" data-generate-pw="createPassword">Generate</button>
                    </div>
                </label>
                <p class="text-xs text-navy/55">Ikaw ang magse-set ng password. Ibigay ito sa manager nang pribado.</p>
            </section>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-border p-4">
            <button type="button" data-close-modal class="sh-btn-ghost">Cancel</button>
            <button type="submit" class="sh-btn">Create province hub</button>
        </div>
    </form>
</dialog>

{{-- Set manager password --}}
<dialog id="passwordModal" class="w-full max-w-md rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" id="passwordForm" class="space-y-4 p-5">
        @csrf
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-navy">Set manager password</h2>
                <p class="mt-1 text-xs text-navy/55" id="pmHub"></p>
            </div>
            <button type="button" data-close-password class="sh-btn-ghost text-xs" aria-label="Close">✕</button>
        </div>

        <div class="space-y-3 text-sm">
            <div><p class="text-xs text-navy/45">Manager</p><p class="font-semibold text-navy" id="pmName"></p></div>
            <div><p class="text-xs text-navy/45">Login email</p><code class="mt-1 block truncate rounded bg-gray-bg px-2 py-1.5 font-semibold text-navy" id="pmEmail"></code></div>
        </div>

        <label class="sh-field">
            <span class="sh-label">New password *</span>
            <div class="flex gap-2">
                <input class="sh-input min-w-0 flex-1" type="password" id="resetPassword" name="password" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="At least 8 characters">
                <button type="button" class="sh-btn-soft text-xs" data-toggle-pw="resetPassword">Show</button>
                <button type="button" class="sh-btn-soft text-xs" data-generate-pw="resetPassword">Generate</button>
            </div>
        </label>
        <p class="text-xs leading-5 text-navy/55">Hindi na makikita ang dating password dahil naka-encrypt ito. Papalitan ito ng ilalagay mo dito.</p>

        <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" data-close-password class="sh-btn-ghost">Cancel</button>
            <button type="submit" class="sh-btn">Save password</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
(function () {
    // ---------- Create modal ----------
    const modal = document.getElementById('hubModal');
    document.getElementById('openHubModal').addEventListener('click', () => modal.showModal());
    modal.querySelectorAll('[data-close-modal]').forEach(b => b.addEventListener('click', () => modal.close()));
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });

    // ---------- Password helpers (Show / Generate) ----------
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePw);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
    }));
    document.querySelectorAll('[data-generate-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.generatePw);
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        input.value = Array.from(crypto.getRandomValues(new Uint32Array(12)), n => chars[n % chars.length]).join('');
        input.type = 'text';
        const toggle = document.querySelector(`[data-toggle-pw="${input.id}"]`);
        if (toggle) toggle.textContent = 'Hide';
    }));

    // ---------- Copy email ----------
    document.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(btn.dataset.copy); btn.textContent = 'Copied!'; }
        catch (e) { btn.textContent = 'Copy failed'; }
        setTimeout(() => btn.textContent = 'Copy', 1500);
    }));

    // ---------- Set password modal ----------
    const pm = document.getElementById('passwordModal');
    const pmInput = document.getElementById('resetPassword');
    document.querySelectorAll('[data-set-password]').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('pmHub').textContent = btn.dataset.hub;
        document.getElementById('pmName').textContent = btn.dataset.name;
        document.getElementById('pmEmail').textContent = btn.dataset.email;
        document.getElementById('passwordForm').action = btn.dataset.url;
        pmInput.value = ''; pmInput.type = 'password';
        document.querySelector('[data-toggle-pw="resetPassword"]').textContent = 'Show';
        pm.showModal();
    }));
    pm.querySelectorAll('[data-close-password]').forEach(b => b.addEventListener('click', () => pm.close()));
    pm.addEventListener('click', e => { if (e.target === pm) pm.close(); });

    // ---------- PSGC cascading address dropdowns ----------
    const API = 'https://psgc.gitlab.io/api';
    const cache = {};
    const el = {
        region: document.getElementById('regionSelect'),
        province: document.getElementById('provinceSelect'),
        municipality: document.getElementById('municipalitySelect'),
        barangay: document.getElementById('barangaySelect'),
    };
    const labels = { region: 'region', province: 'province', municipality: 'city / municipality', barangay: 'barangay' };

    async function getJSON(path) {
        if (cache[path]) return cache[path];
        const res = await fetch(API + path);
        if (!res.ok) throw new Error('PSGC request failed');
        return (cache[path] = await res.json());
    }

    // Fills a <select>; returns true if the old value was restored.
    function fill(key, items) {
        const s = el[key];
        const old = s.dataset.old || '';
        s.dataset.old = '';
        s.innerHTML = `<option value="">Select ${labels[key]}</option>`;
        items.sort((a, b) => a.name.localeCompare(b.name)).forEach(i => {
            const o = new Option(i.name, i.name);
            o.dataset.code = i.code;
            s.add(o);
        });
        s.disabled = items.length === 0;
        if (old && [...s.options].some(o => o.value === old)) { s.value = old; return true; }
        return false;
    }

    function reset(...keys) {
        keys.forEach(k => {
            el[k].innerHTML = `<option value="">Select ${labels[k]}</option>`;
            el[k].disabled = true;
        });
    }

    const code = key => el[key].selectedOptions[0]?.dataset.code;

    async function onRegion() {
        reset('province', 'municipality', 'barangay');
        delete el.province.dataset.code;
        const c = code('region');
        if (!c) return;
        try {
            const provinces = await getJSON(`/regions/${c}/provinces.json`);
            if (provinces.length) {
                if (fill('province', provinces)) await onProvince();
            } else {
                // e.g. NCR has no provinces: use the region itself as the "province"
                const name = el.region.value;
                el.province.innerHTML = '';
                el.province.add(new Option(name, name));
                el.province.dataset.code = c;
                el.province.disabled = false;
                const cities = await getJSON(`/regions/${c}/cities-municipalities.json`);
                if (fill('municipality', cities)) await onMunicipality();
            }
        } catch (e) { console.error(e); }
    }

    async function onProvince() {
        reset('municipality', 'barangay');
        const opt = el.province.selectedOptions[0];
        const c = opt?.dataset.code || el.province.dataset.code;
        if (!opt?.value || !c) return;
        try {
            // NCR fallback: the code belongs to the region, so use the region endpoint
            const path = opt.dataset.code
                ? `/provinces/${c}/cities-municipalities.json`
                : `/regions/${c}/cities-municipalities.json`;
            const cities = await getJSON(path);
            if (fill('municipality', cities)) await onMunicipality();
        } catch (e) { console.error(e); }
    }

    async function onMunicipality() {
        reset('barangay');
        const c = code('municipality');
        if (!c) return;
        try {
            fill('barangay', await getJSON(`/cities-municipalities/${c}/barangays.json`));
        } catch (e) { console.error(e); }
    }

    el.region.addEventListener('change', onRegion);
    el.province.addEventListener('change', onProvince);
    el.municipality.addEventListener('change', onMunicipality);

    (async () => {
        try {
            const regions = await getJSON('/regions.json');
            if (fill('region', regions)) await onRegion();
        } catch (e) {
            el.region.innerHTML = '<option value="">Failed to load. Refresh the page.</option>';
        }
    })();

    @if($errors->any())
        modal.showModal();
    @endif
})();
</script>
@endpush