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
            <div class="min-w-0">
                <h2 id="directoryTitle" class="text-base font-bold text-navy">Hub directory</h2>
                <p class="mt-1 text-xs text-navy/55">{{ $centers->count() }} {{ request('search') ? 'matching ' : '' }}province hub(s) · Select View details to manage a hub.</p>
            </div>
            <form method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto" role="search">
                <label class="sr-only" for="hubSearch">Search hubs</label>
                <input id="hubSearch" name="search" value="{{ request('search') }}" placeholder="Name, province or code" class="sh-input min-w-[160px] flex-1" />
                <button class="sh-btn-soft" type="submit">Search</button>
                @if(request('search'))<a href="{{ route('logistics.sorting-centers.index') }}" class="sh-btn-ghost">Clear</a>@endif
            </form>
        </div>
        <div class="sh-hub-table-wrap">
            <table class="sh-hub-table">
                <thead><tr>
                    <th scope="col">Province hub</th>
                    <th scope="col">Manager / login</th>
                    <th scope="col">Station hubs</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($centers as $center)
                        @php $manager = $center->staff->first(fn ($s) => $s->role === 'manager' && $s->status === 'active'); @endphp
                        <tr>
                            <td data-label="Province hub">
                                <a class="sh-hub-link" href="{{ route('logistics.sorting-centers.show', $center) }}">{{ $center->name }}</a>
                                <p class="sh-hub-secondary">{{ $center->code }} · {{ $center->province }}, {{ $center->region }}</p>
                                <p class="sh-hub-secondary sh-hub-address" title="{{ $center->addressLabel() }}">{{ $center->addressLabel() }}</p>
                            </td>
                            <td data-label="Manager / login">
                                <p class="font-semibold text-navy">{{ $manager?->name ?: 'Not assigned' }}</p>
                                @if($manager)
                                    <div class="sh-hub-email-row">
                                        <span class="sh-hub-email" title="{{ $manager->email }}">{{ $manager->email }}</span>
                                        <button type="button" class="sh-hub-copy" data-copy="{{ $manager->email }}" aria-label="Copy login email for {{ $manager->name }}">Copy</button>
                                    </div>
                                @endif
                            </td>
                            <td data-label="Station hubs"><strong class="text-navy">{{ $center->station_hubs_count }}</strong></td>
                            <td data-label="Status"><span class="sh-badge {{ $center->status === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $center->status === 'active' ? 'Active' : 'Inactive' }}</span></td>
                            <td data-label="Actions">
                                <div class="sh-hub-actions">
                                    <a href="{{ route('logistics.sorting-centers.show', $center) }}" class="sh-btn-soft text-xs">View details</a>
                                    @if($manager)
                                        <button type="button" class="sh-btn-ghost text-xs"
                                                data-set-password
                                                data-hub="{{ $center->name }}"
                                                data-name="{{ $manager->name }}"
                                                data-email="{{ $manager->email }}"
                                                data-url="{{ route('logistics.staff.set-password', $manager) }}">Set password</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="sh-hub-empty"><x-lucide-warehouse class="mx-auto h-9 w-9 text-navy/25" />
                            <h3 class="mt-3 font-bold text-navy">{{ request('search') ? 'No matching province hubs' : 'No province hubs yet' }}</h3>
                            <p class="mt-1 text-sm text-navy/55">{{ request('search') ? 'Try another name, province, or hub code.' : 'Create your first province hub to manage station locations.' }}</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Create province hub --}}
<dialog id="hubModal" aria-labelledby="hubModalTitle" class="sh-dialog w-full max-w-3xl rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" action="{{ route('logistics.sorting-centers.store') }}" id="createHubForm" class="flex max-h-[90vh] flex-col">
        @csrf
        <div class="flex items-start justify-between gap-3 border-b border-gray-border p-5">
            <div>
                <h2 id="hubModalTitle" class="text-lg font-bold text-navy">Create province hub</h2>
                <p class="mt-1 text-xs text-navy/55">One province hub per province. It covers the whole province; station hubs are created later by its manager.</p>
            </div>
            <button type="button" data-close-modal class="sh-btn-ghost text-xs" aria-label="Close">✕</button>
        </div>

        <div class="sh-modal-scroll space-y-4 overflow-y-auto p-4 sm:p-5" id="hubCreateBody">
            <nav class="sh-form-steps sh-hub-steps" aria-label="Hub setup sections">
                <a href="#hub-section-details"><b>1</b> Hub</a><a href="#hub-section-address"><b>2</b> Address</a><a href="#hub-section-manager"><b>3</b> Manager</a><span aria-current="step" class="sh-hub-step-review"><b>4</b> Review</span>
            </nav>
            <p class="sh-hub-required-note"><span class="sh-required-star" aria-hidden="true">*</span> Required fields · Review details before creating the hub.</p>
            @if($errors->any())
                <div role="alert" class="sh-alert-error"><strong>Review the information before saving.</strong>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section id="hub-section-details" class="sh-form-section space-y-3">
                <h3 class="sh-form-section-title"><span>01</span> Hub details</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Hub name *</span><input class="sh-input" name="name" value="{{ old('name') }}" required maxlength="180" placeholder="Example: Laguna Province Hub"></label>
                    <label class="sh-field"><span class="sh-label">Contact number</span><input class="sh-input" name="contact_no" value="{{ old('contact_no') }}" maxlength="30" placeholder="09XX XXX XXXX"></label>
                </div>
            </section>

            <section id="hub-section-address" class="sh-form-section space-y-3">
                <h3 class="sh-form-section-title"><span>02</span> Warehouse address</h3><p class="sh-help">Physical truck drop-off location; select the actual province served.</p>
                <div id="hubAddressStatus" class="text-xs text-navy/55" role="status" aria-live="polite">Loading address options…</div>
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

            <section id="hub-section-manager" class="sh-form-section space-y-3">
                <h3 class="sh-form-section-title"><span>03</span> Hub manager account</h3><p class="sh-help">This creates the login for the person who manages this province hub.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="sh-field"><span class="sh-label">Manager name *</span><input class="sh-input" name="manager_name" value="{{ old('manager_name') }}" required maxlength="150" placeholder="Manager full name"></label>
                    <label class="sh-field"><span class="sh-label">Manager email (login) *</span><input class="sh-input" type="email" name="manager_email" value="{{ old('manager_email') }}" required maxlength="190" placeholder="manager@example.com"></label>
                </div>
                <label class="sh-field">
                    <span class="sh-label">Password *</span>
                    <div class="flex gap-2">
                        <input class="sh-input min-w-0 flex-1" type="password" id="createPassword" name="manager_password" required minlength="8" maxlength="100" autocomplete="new-password" aria-describedby="hubPwHelp" placeholder="At least 8 characters">
                        <button type="button" class="sh-btn-soft text-xs" data-toggle-pw="createPassword">Show</button>
                        <button type="button" class="sh-btn-soft text-xs" data-generate-pw="createPassword">Generate</button>
                    </div>
                </label>
                <label class="sh-field">
                    <span class="sh-label">Confirm password <span class="sh-required-star" aria-hidden="true">*</span></span>
                    <input class="sh-input" type="password" id="confirmCreatePassword" name="manager_password_confirmation" required minlength="8" maxlength="100" autocomplete="new-password" placeholder="Re-enter password">
                </label>
                <p id="hubPwHelp" class="text-xs text-navy/55">At least 8 characters with letters and numbers. You can enter your own password or generate one. Share it privately.</p>
            </section>
            <section id="hubReview" class="sh-hub-review" hidden aria-labelledby="hubReviewTitle">
                <h3 id="hubReviewTitle" class="sh-form-section-title"><span>04</span> Review your province hub</h3>
                <p class="sh-help">Confirm these details before creating the hub and its manager account. The password is never shown in this summary.</p>
                <dl class="sh-hub-review-grid">
                    <div><dt>Hub</dt><dd data-review="name"></dd></div>
                    <div><dt>Contact</dt><dd data-review="contact_no"></dd></div>
                    <div class="sh-hub-review-wide"><dt>Warehouse address</dt><dd data-review="address"></dd></div>
                    <div><dt>Manager</dt><dd data-review="manager_name"></dd></div>
                    <div><dt>Login email</dt><dd data-review="manager_email"></dd></div>
                </dl>
                <p class="sh-hub-review-warning">This will create one province hub and one manager login. Check the email carefully; existing duplicate emails are rejected.</p>
            </section>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-border p-4">
            <button type="button" data-close-modal class="sh-btn-ghost">Cancel</button>
            <button type="button" id="hubEditDetails" class="sh-btn-soft" hidden>Back to edit</button>
            <button type="button" id="hubReviewButton" class="sh-btn">Review details <x-lucide-arrow-right class="h-4 w-4" /></button>
            <button type="submit" id="hubCreateSubmit" class="sh-btn" data-submit-label="Creating hub…" hidden>Create province hub <x-lucide-check class="h-4 w-4" /></button>
        </div>
    </form>
</dialog>

{{-- Discard unsaved hub input without native browser confirmation. --}}
<dialog id="hubDiscardModal" aria-labelledby="hubDiscardTitle" class="sh-dialog w-full max-w-md rounded-2xl p-0 backdrop:bg-navy/50">
    <div class="p-5 space-y-4">
        <div><h2 id="hubDiscardTitle" class="text-lg font-bold text-navy">Discard hub details?</h2>
            <p class="mt-2 text-sm text-navy/65">You have unsaved changes in this form. Closing it will discard those changes.</p></div>
        <div class="flex justify-end gap-2"><button type="button" id="hubKeepEditing" class="sh-btn-ghost">Keep editing</button>
            <button type="button" id="hubDiscardConfirm" class="sh-btn-danger sh-btn-danger-solid px-4 py-2">Discard changes</button></div>
    </div>
</dialog>

{{-- Set manager password --}}
<dialog id="passwordModal" aria-labelledby="passwordModalTitle" class="sh-dialog w-full max-w-md rounded-2xl p-0 backdrop:bg-navy/50">
    <form method="POST" id="passwordForm" class="space-y-4 p-5">
        @csrf
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="passwordModalTitle" class="text-lg font-bold text-navy">Set manager password</h2>
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
                <input class="sh-input min-w-0 flex-1" type="password" id="resetPassword" name="password" required minlength="8" maxlength="100" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,100}" title="At least 8 characters with letters and numbers" autocomplete="new-password" placeholder="At least 8 characters">
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

{{-- Native top-layer confirmation so this dialog appears ABOVE the password form dialog. --}}
<dialog id="hubPasswordConfirmModal" aria-labelledby="hubPasswordConfirmTitle" class="sh-dialog w-full max-w-md rounded-2xl p-0 backdrop:bg-navy/50">
    <div class="p-5 space-y-4">
        <h2 id="hubPasswordConfirmTitle" class="text-lg font-bold text-navy">Change manager password?</h2>
        <p class="text-sm text-navy/65">The current password will stop working, and existing mobile sessions for this account will be revoked.</p>
        <div class="rounded-xl bg-gray-bg p-3 text-sm">
            <p class="font-semibold text-navy" id="hubPasswordConfirmName"></p>
            <p class="mt-1 break-all text-xs text-navy/60" id="hubPasswordConfirmEmail"></p>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" id="hubPasswordKeep" class="sh-btn-ghost">Cancel</button>
            <button type="button" id="hubPasswordConfirm" class="sh-btn-danger sh-btn-danger-solid px-4 py-2">Save new password</button>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(function () {
    // ---------- Create modal: validation, review, and unsaved-change guard ----------
    const modal = document.getElementById('hubModal');
    const hubForm = document.getElementById('createHubForm');
    const review = document.getElementById('hubReview');
    const sections = [...hubForm.querySelectorAll('#hub-section-details, #hub-section-address, #hub-section-manager')];
    const reviewButton = document.getElementById('hubReviewButton');
    const createButton = document.getElementById('hubCreateSubmit');
    const backButton = document.getElementById('hubEditDetails');
    const discard = document.getElementById('hubDiscardModal');
    const password = document.getElementById('createPassword');
    const confirmPassword = document.getElementById('confirmCreatePassword');
    const originalValues = new Map([...hubForm.elements].filter(e => e.name && e.type !== 'hidden').map(e => [e.name, e.value]));
    const dirty = () => [...hubForm.elements].some(e => e.name && e.type !== 'hidden' && e.value !== (originalValues.get(e.name) ?? ''));

    function validatePassword() {
        const value = password.value;
        password.setCustomValidity(value && (!/[A-Za-z]/.test(value) || !/[0-9]/.test(value)) ? 'Password must include letters and numbers.' : '');
        confirmPassword.setCustomValidity(confirmPassword.value && confirmPassword.value !== value ? 'Passwords do not match.' : '');
    }
    [password, confirmPassword].forEach(input => input.addEventListener('input', validatePassword));

    function showReview(show) {
        sections.forEach(section => section.hidden = show);
        review.hidden = !show;
        reviewButton.hidden = show;
        backButton.hidden = !show;
        createButton.hidden = !show;
        const nav = hubForm.querySelector('.sh-hub-steps');
        if (nav) nav.dataset.reviewing = show ? '1' : '0';
        document.getElementById('hubCreateBody').scrollTop = 0;
    }
    function requestCloseHub() {
        if (dirty()) { if (!discard.open) discard.showModal(); }
        else modal.close();
    }
    document.getElementById('openHubModal').addEventListener('click', () => modal.showModal());
    modal.querySelectorAll('[data-close-modal]').forEach(b => b.addEventListener('click', requestCloseHub));
    modal.addEventListener('click', e => { if (e.target === modal) requestCloseHub(); });
    modal.addEventListener('cancel', e => { e.preventDefault(); requestCloseHub(); });
    document.getElementById('hubKeepEditing').addEventListener('click', () => discard.close());
    document.getElementById('hubDiscardConfirm').addEventListener('click', () => {
        discard.close(); modal.close(); hubForm.reset(); showReview(false);
        for (const element of hubForm.elements) if (element.name && element.type !== 'hidden') originalValues.set(element.name, element.value);
        password.setCustomValidity(''); confirmPassword.setCustomValidity('');
    });
    reviewButton.addEventListener('click', () => {
        validatePassword();
        if ([el.region, el.province, el.municipality, el.barangay].some(select => select.disabled || !select.value)) {
            const status = document.getElementById('hubAddressStatus');
            status.textContent = 'Please complete the address selections. If options did not load, check the internet connection and reopen this form.';
            status.classList.add('text-red-700');
            document.getElementById('hub-section-address').scrollIntoView({block:'nearest'});
            return;
        }
        if (!hubForm.reportValidity()) return;
        const value = name => (hubForm.elements.namedItem(name)?.value || '').trim();
        const address = [value('street_no'), value('unit_no'), value('barangay'), value('municipality'), value('province'), value('region')].filter(Boolean).join(', ');
        const summaries = { name:value('name'), contact_no:value('contact_no') || '—', address,
            manager_name:value('manager_name'), manager_email:value('manager_email') };
        Object.entries(summaries).forEach(([key,val]) => {
            const el = review.querySelector(`[data-review="${key}"]`);
            if (el) el.textContent = val;
        });
        showReview(true);
    });
    backButton.addEventListener('click', () => showReview(false));
    hubForm.addEventListener('submit', event => {
        validatePassword();
        if (review.hidden || !hubForm.checkValidity()) {
            event.preventDefault(); showReview(false); hubForm.reportValidity(); return;
        }
        createButton.disabled = true;
        createButton.setAttribute('aria-busy', 'true');
        createButton.textContent = 'Creating hub…';
    });

    // ---------- Password helpers (Show / Generate) ----------
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePw);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
    }));
    document.querySelectorAll('[data-generate-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.generatePw);
        const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        const numbers = '23456789';
        const chars = letters + numbers + '!@#$%';
        const pick = alphabet => alphabet[crypto.getRandomValues(new Uint32Array(1))[0] % alphabet.length];
        const parts = [pick(letters), pick(numbers), ...Array.from({length:12}, () => pick(chars))];
        // Shuffle so the mandatory letter and number do not always lead the password.
        for (let i = parts.length - 1; i > 0; i--) {
            const j = crypto.getRandomValues(new Uint32Array(1))[0] % (i + 1);
            [parts[i], parts[j]] = [parts[j], parts[i]];
        }
        input.value = parts.join('');
        // Keep the generated password hidden until the user explicitly selects Show.
        input.type = 'password';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        const toggle = document.querySelector(`[data-toggle-pw="${input.id}"]`);
        if (toggle) toggle.textContent = 'Show';
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
        passwordForm.dataset.approvedReset = '0';
        document.getElementById('passwordForm').dataset.confirmSubject = btn.dataset.name + ' · ' + btn.dataset.email;
        pmInput.value = ''; pmInput.type = 'password';
        document.querySelector('[data-toggle-pw="resetPassword"]').textContent = 'Show';
        pm.showModal();
    }));
    pm.querySelectorAll('[data-close-password]').forEach(b => b.addEventListener('click', () => pm.close()));
    pm.addEventListener('click', e => { if (e.target === pm) pm.close(); });

    // Password reset confirmation is itself a native dialog (always above pm).
    const passwordForm = document.getElementById('passwordForm');
    const passwordConfirm = document.getElementById('hubPasswordConfirmModal');
    passwordForm.addEventListener('submit', e => {
        if (passwordForm.dataset.approvedReset === '1') return;
        e.preventDefault();
        if (!passwordForm.reportValidity()) return;
        document.getElementById('hubPasswordConfirmName').textContent = document.getElementById('pmName').textContent;
        document.getElementById('hubPasswordConfirmEmail').textContent = document.getElementById('pmEmail').textContent;
        passwordConfirm.showModal();
    });
    document.getElementById('hubPasswordKeep').addEventListener('click', () => passwordConfirm.close());
    document.getElementById('hubPasswordConfirm').addEventListener('click', () => {
        passwordConfirm.close();
        passwordForm.dataset.approvedReset = '1';
        passwordForm.requestSubmit();
    });

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
            document.getElementById('hubAddressStatus').textContent = 'Choose your region, then select province, city / municipality and barangay.';
        } catch (e) {
            el.region.innerHTML = '<option value="">Failed to load. Refresh the page.</option>';
            const status = document.getElementById('hubAddressStatus');
            status.textContent = 'Unable to load address choices. Check your internet connection, then refresh the page.';
            status.classList.add('text-red-700');
        }
    })();

    @if($errors->any() && ! $errors->hasBag('setPassword'))
        modal.showModal();
    @endif
})();
</script>
@endpush