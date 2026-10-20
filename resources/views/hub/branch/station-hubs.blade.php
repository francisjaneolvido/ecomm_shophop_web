@extends('hub.layouts.console')
@section('title', 'Station Hubs — Province Hub')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">{{ strtoupper($center->province) }} PROVINCE HUB</p>
            <h1 class="title">Station Hubs</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/60">A <strong>station hub</strong> handles last-mile delivery for a city or municipality in {{ $center->province }}. Ikaw ang gumagawa ng account ng bawat station hub.</p>
        </div>
        <button type="button" id="openStationModal" class="sh-btn inline-flex items-center gap-2"><x-lucide-plus class="h-4 w-4" /> New station hub</button>
    </div>

    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif

    @if($errors->hasBag('setPassword'))
        <div role="alert" class="sh-alert-error"><strong>Hindi na-save ang password.</strong>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->getBag('setPassword')->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if(session('new_staff_credentials'))
        <div class="rounded-2xl border-2 border-teal bg-teal-light p-5" role="status">
            <h2 class="font-bold text-navy">Station hub login — shown once</h2>
            <p class="mt-2 text-xs text-navy/60">Ibigay nang pribado sa station hub.</p>
            <p class="mt-3 text-sm"><strong>Email:</strong> {{ session('new_staff_credentials')['email'] }}</p>
            <p class="mt-1 text-sm"><strong>Password:</strong>
                <code class="rounded bg-white px-2 py-1 font-bold">{{ session('new_staff_credentials')['password'] }}</code>
                <button type="button" class="sh-btn-soft ml-2 text-xs"
                        onclick="navigator.clipboard.writeText(@js(session('new_staff_credentials')['password'])); this.textContent='Copied!'">Copy</button>
            </p>
        </div>
    @endif

    <section class="sh-surface overflow-hidden" aria-labelledby="stationsTitle">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-border p-4 sm:p-5">
            <h2 id="stationsTitle" class="text-base font-bold">Station hub directory</h2>
            <form method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto" role="search">
                <label class="sr-only" for="stationSearch">Search station hubs</label>
                <input id="stationSearch" name="search" value="{{ $search }}" placeholder="Name, city or code" class="sh-input min-w-[160px] flex-1">
                <button class="sh-btn-soft" type="submit">Search</button>
                @if($search !== '')<a href="{{ route('logistics.hub.station-hubs') }}" class="sh-btn-ghost">Clear</a>@endif
            </form>
        </div>

        <div class="divide-y divide-gray-border">
            @forelse($stationHubs as $hub)
                @php $active = $hub->status === 'active'; @endphp
                <div class="grid gap-4 p-4 transition hover:bg-gray-bg/40 sm:p-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1.3fr)] lg:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold">{{ $hub->name }}</h3>
                            <span class="sh-badge {{ $active ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/50">{{ $hub->code }}</p>
                        <p class="mt-1 truncate text-xs text-navy/65">{{ $hub->addressLabel() }}</p>
                    </div>

                    <div class="text-xs">
                        <p class="text-navy/45">Login email</p>
                        @if($hub->user)
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <code class="min-w-0 max-w-full truncate rounded bg-gray-bg px-2 py-1 font-semibold text-navy">{{ $hub->user->email }}</code>
                                <button type="button" class="sh-btn-ghost text-xs" data-copy="{{ $hub->user->email }}">Copy</button>
                                <button type="button" class="sh-btn-soft text-xs" data-set-password
                                        data-hub="{{ $hub->name }}"
                                        data-email="{{ $hub->user->email }}"
                                        data-url="{{ route('logistics.hub.station-hubs.set-password', $hub->id) }}">Set password</button>
                                <form method="POST" action="{{ route('logistics.hub.station-hubs.toggle', $hub->id) }}"
                                      data-confirm-title="{{ $active ? 'Deactivate station hub?' : 'Activate station hub?' }}"
                                      data-confirm-message="{{ $active ? 'Hindi na makakapag-login ang station hub na ito hangga\'t hindi mo ito ina-activate ulit.' : 'Makakapag-login na ulit ang station hub na ito.' }}"
                                      data-confirm-button="{{ $active ? 'Deactivate' : 'Activate' }}"
                                      @if($active) data-confirm-danger="1" @endif>
                                    @csrf
                                    <button type="submit" class="sh-btn-ghost text-xs">{{ $active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        @else
                            <p class="mt-1 font-semibold">Walang login account</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <x-lucide-warehouse class="mx-auto h-10 w-10 text-navy/25" />
                    <h3 class="mt-3 font-bold text-navy">{{ $search !== '' ? 'Walang nahanap' : 'Wala pang station hub' }}</h3>
                    <p class="mt-1 text-sm text-navy/55">
                        {{ $search !== '' ? 'Subukan ang ibang keyword.' : 'Gumawa ng unang station hub para sa isang city o municipality sa ' . $center->province . '.' }}
                    </p>
                </div>
            @endforelse
        </div>
    </section>
</div>

{{-- Create station hub --}}
<dialog id="stationModal" class="w-full max-w-3xl rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" action="{{ route('logistics.hub.station-hubs.store') }}" class="flex max-h-[90vh] flex-col">
        @csrf
        <div class="flex items-start justify-between gap-3 border-b border-gray-border p-5">
            <div>
                <h2 class="text-lg font-bold">Create station hub</h2>
                <p class="mt-1 text-xs text-navy/55">Sakop ito ng {{ $center->name }}. Ang province at region ay awtomatiko.</p>
            </div>
            <button type="button" data-close class="sh-btn-ghost text-xs" aria-label="Close">✕</button>
        </div>

        <div class="space-y-6 overflow-y-auto p-5">
            @if($errors->any())
                <div role="alert" class="sh-alert-error"><strong>Review the information before saving.</strong>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="space-y-4">
                <h3 class="text-sm font-bold">1. Station details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Station hub name *</span><input class="sh-input" name="name" value="{{ old('name') }}" required maxlength="180"></label>
                    <label class="sh-field"><span class="sh-label">Contact number</span><input class="sh-input" name="contact_no" value="{{ old('contact_no') }}" maxlength="30" placeholder="09XX XXX XXXX"></label>
                </div>
            </section>

            <section class="space-y-4">
                <h3 class="text-sm font-bold">2. Station address</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Province</span><input class="sh-input bg-gray-bg" value="{{ $center->province }}, {{ $center->region }}" disabled></label>
                    <label class="sh-field"><span class="sh-label">City / municipality *</span>
                        <select class="sh-input" name="municipality" id="stMunicipality" data-old="{{ old('municipality') }}" required disabled><option value="">Loading…</option></select></label>
                    <label class="sh-field"><span class="sh-label">Barangay *</span>
                        <select class="sh-input" name="barangay" id="stBarangay" data-old="{{ old('barangay') }}" required disabled><option value="">Select barangay</option></select></label>
                    <label class="sh-field"><span class="sh-label">Street / house number *</span><input class="sh-input" name="street_no" value="{{ old('street_no') }}" required maxlength="180"></label>
                    <label class="sh-field sm:col-span-2"><span class="sh-label">Unit / details (optional)</span><input class="sh-input" name="unit_no" value="{{ old('unit_no') }}" maxlength="180" placeholder="Floor, suite or bay"></label>
                </div>
            </section>

            <section class="space-y-4">
                <h3 class="text-sm font-bold">3. Station hub login</h3>
                <label class="sh-field"><span class="sh-label">Email (login) *</span><input class="sh-input" type="email" name="manager_email" value="{{ old('manager_email') }}" required maxlength="190" placeholder="station@example.com"></label>
                <label class="sh-field"><span class="sh-label">Password *</span>
                    <div class="flex gap-2">
                        <input class="sh-input min-w-0 flex-1" type="password" id="createPw" name="manager_password" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="At least 8 characters, may letters at numbers">
                        <button type="button" class="sh-btn-soft text-xs" data-toggle-pw="createPw">Show</button>
                        <button type="button" class="sh-btn-soft text-xs" data-generate-pw="createPw">Generate</button>
                    </div>
                </label>
                <p class="text-xs text-navy/55">Ikaw ang magse-set ng password. Ibigay ito sa station hub nang pribado.</p>
            </section>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-border p-4">
            <button type="button" data-close class="sh-btn-ghost">Cancel</button>
            <button type="submit" class="sh-btn">Create station hub</button>
        </div>
    </form>
</dialog>

{{-- Set station hub password --}}
<dialog id="passwordModal" class="w-full max-w-md rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" id="passwordForm" class="space-y-4 p-5">
        @csrf
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold">Set station hub password</h2>
                <p class="mt-1 text-xs text-navy/55" id="pmHub"></p>
            </div>
            <button type="button" data-close-password class="sh-btn-ghost text-xs" aria-label="Close">✕</button>
        </div>
        <div><p class="text-xs text-navy/45">Login email</p><code class="mt-1 block truncate rounded bg-gray-bg px-2 py-1.5 text-sm font-semibold" id="pmEmail"></code></div>
        <label class="sh-field"><span class="sh-label">New password *</span>
            <div class="flex gap-2">
                <input class="sh-input min-w-0 flex-1" type="password" id="resetPw" name="password" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="At least 8 characters">
                <button type="button" class="sh-btn-soft text-xs" data-toggle-pw="resetPw">Show</button>
                <button type="button" class="sh-btn-soft text-xs" data-generate-pw="resetPw">Generate</button>
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
    // Show / Generate password
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePw);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
    }));
    document.querySelectorAll('[data-generate-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.generatePw);
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        const pick = n => chars[n % chars.length];
        // letters + numbers guaranteed (matches the password rule)
        const raw = Array.from(crypto.getRandomValues(new Uint32Array(12)), pick);
        raw[0] = 'ABCDEFGHJKLMNPQRSTUVWXYZ'[crypto.getRandomValues(new Uint32Array(1))[0] % 24];
        raw[1] = 'abcdefghijkmnopqrstuvwxyz'[crypto.getRandomValues(new Uint32Array(1))[0] % 25];
        raw[2] = '23456789'[crypto.getRandomValues(new Uint32Array(1))[0] % 8];
        input.value = raw.join('') + '!';
        input.type = 'text';
        const toggle = document.querySelector(`[data-toggle-pw="${input.id}"]`);
        if (toggle) toggle.textContent = 'Hide';
    }));

    // Copy email
    document.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(btn.dataset.copy); btn.textContent = 'Copied!'; }
        catch (e) { btn.textContent = 'Copy failed'; }
        setTimeout(() => btn.textContent = 'Copy', 1500);
    }));

    // Create modal + PSGC (municipalities of THIS province only)
    const modal = document.getElementById('stationModal');
    document.getElementById('openStationModal').addEventListener('click', () => modal.showModal());
    modal.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => modal.close()));
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });

    const API = 'https://psgc.gitlab.io/api';
    const PROVINCE = @js($center->province);
    const REGION = @js($center->region);
    const cache = {};
    const munSel = document.getElementById('stMunicipality');
    const brgySel = document.getElementById('stBarangay');
    const norm = s => (s || '').toLowerCase().replace(/\s+/g, ' ').trim();

    async function getJSON(path) {
        if (cache[path]) return cache[path];
        const res = await fetch(API + path);
        if (!res.ok) throw new Error('PSGC request failed');
        return (cache[path] = await res.json());
    }
    function fill(select, items, label) {
        const old = select.dataset.old || '';
        select.dataset.old = '';
        select.innerHTML = `<option value="">Select ${label}</option>`;
        items.sort((a, b) => a.name.localeCompare(b.name)).forEach(i => {
            const o = new Option(i.name, i.name);
            o.dataset.code = i.code;
            select.add(o);
        });
        select.disabled = items.length === 0;
        if (old && [...select.options].some(o => o.value === old)) { select.value = old; return true; }
        return false;
    }
    async function loadBarangays() {
        brgySel.innerHTML = '<option value="">Select barangay</option>';
        brgySel.disabled = true;
        const code = munSel.selectedOptions[0]?.dataset.code;
        if (!code) return;
        try { fill(brgySel, await getJSON(`/cities-municipalities/${code}/barangays.json`), 'barangay'); }
        catch (e) { console.error(e); }
    }
    munSel.addEventListener('change', loadBarangays);

    (async () => {
        try {
            let cities;
            const provinces = await getJSON('/provinces.json');
            const prov = provinces.find(p => norm(p.name) === norm(PROVINCE));
            if (prov) {
                cities = await getJSON(`/provinces/${prov.code}/cities-municipalities.json`);
            } else {
                const regions = await getJSON('/regions.json');
                const reg = regions.find(r => norm(r.name) === norm(REGION) || norm(r.regionName) === norm(REGION));
                if (!reg) throw new Error('Province not found in PSGC');
                cities = await getJSON(`/regions/${reg.code}/cities-municipalities.json`);
            }
            if (fill(munSel, cities, 'city / municipality')) await loadBarangays();
        } catch (e) {
            console.error(e);
            munSel.innerHTML = '<option value="">Failed to load. Refresh the page.</option>';
        }
    })();

    @if($errors->any())
        modal.showModal();
    @endif

    // Set password modal
    const pm = document.getElementById('passwordModal');
    const pmInput = document.getElementById('resetPw');
    document.querySelectorAll('[data-set-password]').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('pmHub').textContent = btn.dataset.hub;
        document.getElementById('pmEmail').textContent = btn.dataset.email;
        document.getElementById('passwordForm').action = btn.dataset.url;
        pmInput.value = ''; pmInput.type = 'password';
        document.querySelector('[data-toggle-pw="resetPw"]').textContent = 'Show';
        pm.showModal();
    }));
    pm.querySelectorAll('[data-close-password]').forEach(b => b.addEventListener('click', () => pm.close()));
    pm.addEventListener('click', e => { if (e.target === pm) pm.close(); });
})();
</script>
@endpush