@extends('hub.layouts.console')
@section('title','Branches — Province Hub')
@section('content')
<div class="space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">{{ strtoupper($center->province) }} PROVINCE HUB</p><h1 class="title">Branches / Station Hubs</h1>
        <p class="description">One local Branch per city or municipality under this Province Hub. Manage branch contact details and login access here.</p></div>
        <button id="openStationModal" type="button" class="sh-btn">+ Add Branch</button></header>
    @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->hasBag('setPassword'))<div class="sh-alert-error">{{ $errors->getBag('setPassword')->first() }}</div>@endif
    @if(session('new_staff_credentials'))
        <div class="sh-alert-success" role="status"><strong>New/updated branch login — shown once.</strong> Share privately.
            <p class="mt-2">Email: {{ session('new_staff_credentials')['email'] }}</p>
            <p class="mt-1">Password: <code class="font-bold">{{ session('new_staff_credentials')['password'] }}</code></p>
        </div>
    @endif
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="sh-stat"><p class="sh-stat-label">Branches (filtered)</p><p class="sh-stat-value">{{ number_format($stationHubs->total()) }}</p><p class="sh-stat-note">{{ $center->province }}</p></div>
        <div class="sh-stat"><p class="sh-stat-label">On current page</p><p class="sh-stat-value">{{ $stationHubs->count() }}</p><p class="sh-stat-note">25 per page</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Province hub</p><p class="mt-2 text-sm font-bold text-navy">{{ $center->code }}</p><p class="sh-stat-note">Parent of all listed branches</p></div>
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Branch directory</h2><p class="ops-panel-note">Click Actions to edit details, manage credentials or change availability.</p></div>
        <form method="GET" class="ops-toolbar"><input type="search" name="search" value="{{ $search }}" maxlength="100" class="sh-input" placeholder="Branch, code or municipality" aria-label="Search branches"><button class="sh-btn" type="submit">Search</button><a href="{{ route('logistics.hub.station-hubs') }}" class="sh-btn-ghost">Clear</a></form></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Branch / Code</th><th>Municipality</th><th>Branch Login</th><th>Active Riders</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($stationHubs as $hub)
            @php $active=$hub->status==='active'; @endphp
            <tr><td><strong>{{ $hub->name }}</strong><div class="ops-row-sub">{{ $hub->code }} · {{ $hub->addressLabel() }}</div></td>
                <td>{{ $hub->municipality }}<div class="ops-row-sub">{{ $hub->province }}</div></td>
                <td>{{ $hub->user?->email ?? 'Not linked' }}</td><td>{{ number_format($hub->active_riders_count) }}</td>
                <td><span class="sh-badge {{ $active ? 'sh-badge-success':'sh-badge-neutral' }}">{{ ucfirst($hub->status) }}</span></td>
                <td class="ops-row-actions"><details><summary>Actions ▾</summary><div>
                    <button type="button" data-edit-station
                        data-url="{{ route('logistics.hub.station-hubs.update',$hub->id) }}" data-id="{{ $hub->id }}"
                        data-name="{{ $hub->name }}" data-code="{{ $hub->code }}"
                        data-contact="{{ $hub->contact_no }}" data-city="{{ $hub->municipality }}"
                        data-barangay="{{ $hub->barangay }}" data-street="{{ $hub->street_no }}" data-unit="{{ $hub->unit_no }}">Edit branch</button>
                    @if($hub->user)<button type="button" data-set-password data-hub="{{ $hub->name }}" data-email="{{ $hub->user->email }}" data-url="{{ route('logistics.hub.station-hubs.set-password',$hub->id) }}">Change password</button>@endif
                    <form method="POST" action="{{ route('logistics.hub.station-hubs.toggle',$hub->id) }}" data-confirm-title="{{ $active ? 'Deactivate this branch?' : 'Activate this branch?' }}" data-confirm-message="This affects whether the branch can operate. Verify existing parcel and rider assignments first." data-confirm-button="{{ $active ? 'Deactivate':'Activate' }}" @if($active) data-confirm-danger="1" @endif>
                        @csrf<button type="submit" class="text-left">{{ $active ? 'Deactivate':'Activate' }}</button></form>
                    </div></details></td>
            </tr>
        @empty<tr><td colspan="6" class="ops-empty">No branches match this search.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">{{ $stationHubs->firstItem() ?? 0 }}–{{ $stationHubs->lastItem() ?? 0 }} of {{ $stationHubs->total() }} branches</div>
        @if($stationHubs->hasPages())<div class="border-t p-4">{{ $stationHubs->links() }}</div>@endif
    </section>
</div>

{{-- Add branch --}}
<dialog id="stationModal" class="w-full max-w-3xl rounded-lg p-0 backdrop:bg-navy/50">
    <form method="POST" action="{{ route('logistics.hub.station-hubs.store') }}" class="flex max-h-[90vh] flex-col">
        @csrf
        <div class="flex items-start justify-between gap-3 border-b border-gray-border p-5">
            <div>
                <h2 class="text-lg font-bold">Add branch</h2>
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

            <section class="sh-ui-form-section space-y-4">
                <h3 class="text-sm font-bold">1. Station details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Branch name *</span><input class="sh-input" name="name" value="{{ old('name') }}" required maxlength="180"></label>
                    <label class="sh-field"><span class="sh-label">Contact number</span><input class="sh-input" name="contact_no" value="{{ old('contact_no') }}" maxlength="30" placeholder="09XX XXX XXXX"></label>
                </div>
            </section>

            <section class="sh-ui-form-section space-y-4">
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

            <section class="sh-ui-form-section space-y-4">
                <h3 class="text-sm font-bold">3. Branch login</h3>
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
            <button type="submit" class="sh-btn">Add branch</button>
        </div>
    </form>
</dialog>

{{-- Set station hub password --}}
<dialog id="passwordModal" class="w-full max-w-md rounded-lg p-0 backdrop:bg-navy/50">
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
<dialog id="editStationModal" class="ops-dialog">
  <form method="POST" id="editStationForm">@csrf @method('PATCH')<input type="hidden" name="_operation" value="update"><input type="hidden" name="_station_id" value="">
    <div class="ops-dialog-head"><div><h2 class="ops-dialog-title">Edit branch</h2><p class="ops-panel-note" id="editStationSub"></p></div><button type="button" class="ops-action" data-ops-close>✕</button></div>
    <div class="ops-dialog-body space-y-4">@if($errors->any() && old('_operation') === 'update')<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <p class="ops-inline-alert">Municipality and parent Province Hub are locked to protect active routes, rider service areas and parcel assignments.</p>
      <div class="ops-form-grid"><label class="sh-field"><span class="sh-label">Branch name *</span><input name="name" class="sh-input" required maxlength="180"></label>
        <label class="sh-field"><span class="sh-label">Contact number</span><input name="contact_no" class="sh-input" maxlength="30"></label>
        <label class="sh-field"><span class="sh-label">Municipality (locked)</span><input id="editStationCity" class="sh-input" disabled></label>
        <label class="sh-field"><span class="sh-label">Barangay *</span><input name="barangay" class="sh-input" maxlength="180" required></label>
        <label class="sh-field"><span class="sh-label">Street / house number *</span><input name="street_no" class="sh-input" maxlength="180" required></label>
        <label class="sh-field"><span class="sh-label">Unit / details</span><input name="unit_no" class="sh-input" maxlength="180"></label></div>
    </div><div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-ops-close>Cancel</button><button type="submit" class="sh-btn">Save changes</button></div>
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

    @if($errors->any() && old('_operation') !== 'update' && ! $errors->hasBag('setPassword'))
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
@push('scripts')
<script>
(() => {
 const modal=document.getElementById('editStationModal'), form=document.getElementById('editStationForm');
 document.querySelectorAll('[data-edit-station]').forEach(btn=>btn.addEventListener('click',()=>{
   form.action=btn.dataset.url;
   form.elements.namedItem('_station_id').value=btn.dataset.id;
   for(const key of ['name','contact_no','barangay','street_no','unit_no']){
     const field=form.elements.namedItem(key);if(field)field.value=btn.dataset[key==='contact_no'?'contact':key==='street_no'?'street':key==='unit_no'?'unit':key]||'';
   }
   document.getElementById('editStationCity').value=btn.dataset.city||'';
   document.getElementById('editStationSub').textContent=btn.dataset.code+' · '+btn.dataset.city;
   modal.showModal();
 }));
 @if($errors->any() && old('_operation') === 'update')
 const pendingId=@js(old('_station_id'));
 const editButton=Array.from(document.querySelectorAll('[data-edit-station]')).find(b=>b.dataset.id===String(pendingId));
 if(editButton) {
   editButton.click();
   const previous=@js(collect(old())->only(['name','contact_no','barangay','street_no','unit_no'])->all());
   Object.entries(previous).forEach(([key,value])=>{const field=form.elements.namedItem(key);if(field)field.value=value||''});
 }
 @endif
})();
</script>
@endpush
