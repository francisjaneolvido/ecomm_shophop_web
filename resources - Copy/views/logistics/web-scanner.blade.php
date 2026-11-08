@extends($scannerLayout)
@section('title', 'QR Scanner')
@section('content')
@php
    $scannerUrls = [];
    foreach (['branches','inbound-preview','arrival','receive-all','outbound-manifests','outbound-preview','outbound-load','parcel-preview','parcel-receive'] as $route) {
        $scannerUrls[$route] = route($scannerPrefix.$route);
    }
@endphp
<div class="mx-auto max-w-5xl space-y-4" id="shopHopWebScanner">
    <header class="sh-surface sh-card-pad">
        <p class="sh-kicker">{{ $scannerRole === 'hub' ? 'PROVINCE HUB' : 'STATION BRANCH' }} / OPERATIONS</p>
        <h1 class="sh-title">QR Scanner</h1>
        <p class="mt-1 text-sm text-navy/60">Same custody checks as ShopHop Mobile. Scan → Preview → Physically verify → Confirm. A scan alone never updates parcel or manifest status.</p>
    </header>
    <section class="sh-surface sh-card-pad space-y-4">
        <div class="grid gap-3 md:grid-cols-[1fr_2fr] md:items-end">
            <label class="sh-field"><span class="sh-label">Authorized sorting center <span class="text-red-600">*</span></span>
                <select id="scannerCenter" class="sh-input" aria-label="Authorized sorting center" required><option value="">Loading authorized center...</option></select>
            </label>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Scan operation">
                <button type="button" data-mode="inbound" class="sh-btn" role="tab" aria-selected="true">Receive manifest</button>
                <button type="button" data-mode="outbound" class="sh-btn-ghost" role="tab" aria-selected="false">Load outbound</button>
                <button type="button" data-mode="parcel" class="sh-btn-ghost" role="tab" aria-selected="false">Parcel exception</button>
            </div>
        </div>
        <div id="scannerHelp" class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600"></div>
        <div id="scannerOutboundGroup" class="hidden">
            <label class="sh-field"><span class="sh-label">Outbound manifest <span class="text-red-600">*</span></span>
                <select id="scannerManifest" class="sh-input"><option value="">Choose active manifest...</option></select>
            </label>
        </div>
        <label class="sh-field"><span id="scannerCodeLabel" class="sh-label">Manifest QR code <span class="text-red-600">*</span></span>
            <input id="scannerCode" type="text" class="sh-input" autocomplete="off" spellcheck="false" placeholder="Scan or enter code" aria-describedby="scannerHelp" required>
        </label>
        <div class="flex flex-wrap items-center gap-2">
            <button id="scannerCameraToggle" type="button" class="sh-btn-ghost">Open camera</button>
            <button id="scannerPreview" type="button" class="sh-btn">Preview details</button>
        </div>
        <div id="scannerVideoBox" class="hidden overflow-hidden rounded-xl bg-slate-900" style="max-width:520px">
            <video id="scannerVideo" muted playsinline class="w-full" style="height:260px;object-fit:cover"></video>
        </div>
        <div id="scannerMessage" role="status" aria-live="polite" class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600">Select a mode and scan a code. Camera requires HTTPS or localhost; manual entry remains available.</div>
    </section>
    <section id="scannerReview" class="sh-surface sh-card-pad hidden space-y-3" aria-labelledby="scannerReviewTitle">
        <div><p class="sh-kicker">STEP 2 OF 3</p><h2 id="scannerReviewTitle" class="sh-title-sm">Review before confirmation</h2></div>
        <dl id="scannerDetails" class="grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-2"></dl>
        <div id="scannerCountGroup" class="hidden">
            <label class="sh-field"><span class="sh-label">Physically counted parcels <span class="text-red-600">*</span></span>
                <input id="scannerPhysicalCount" type="number" min="1" max="5000" class="sh-input" inputmode="numeric" placeholder="Enter actual parcel count">
            </label>
            <p class="mt-1 text-xs text-navy/55">Do not confirm an intact batch if its physical count differs. Use parcel exception processing instead.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="scannerConfirm" type="button" class="sh-btn">Confirm physical action</button>
            <button id="scannerReceiveAll" type="button" class="sh-btn hidden">Receive all intact</button>
            <button id="scannerCancel" type="button" class="sh-btn-ghost">Clear preview</button>
        </div>
    </section>
    <p class="text-xs text-navy/50">Camera uses the native QR reader when available, otherwise a compatible fallback. If the fallback cannot load, manual tracking entry stays available. A scan never updates custody automatically.</p>
</div>
<script src="/js/shophop-web-qr.js"></script>
<script>
(() => {
  const routes = @json($scannerUrls);
  const token = @json(csrf_token());
  const root = document.getElementById('shopHopWebScanner');
  if (!root) return;
  const el = id => document.getElementById(id);
  let mode = 'inbound', selected = null, working = false;
  let manifestArrived = false;
  const help = {
    inbound: 'Inbound: Scan the Manifest QR. First acknowledge arrival, then physically count and receive all intact.',
    outbound: 'Outbound: Select a prepared Manifest, scan each Parcel QR to preview, then confirm its load.',
    parcel: 'Exception: Scan an individual Parcel QR to verify its custody eligibility before receiving.',
  };
  function notify(message, success = false) {
    const box = el('scannerMessage'); box.textContent = message;
    box.className = 'rounded-xl p-3 text-xs ' + (success ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-50 text-slate-700');
  }
  const qrCamera = window.ShopHopQrCamera({
    video: el('scannerVideo'),
    onResult: code => {
      el('scannerCode').value = String(code).trim().toUpperCase();
      stopCamera(); resetPreview();
      notify('QR detected: ' + el('scannerCode').value + '. Click Preview details before confirming.', true);
    },
    onStatus: notify,
  });
  function stopCamera() {
    qrCamera.close();
    el('scannerVideoBox').classList.add('hidden');
    el('scannerCameraToggle').textContent = 'Open camera';
  }
  el('scannerCameraToggle').addEventListener('click', async () => {
    if (qrCamera.isActive()) {stopCamera(); notify('Camera stopped.'); return;}
    const button = el('scannerCameraToggle');
    button.disabled = true;
    try {
      el('scannerVideoBox').classList.remove('hidden');
      await qrCamera.open();
      button.textContent = 'Stop camera';
    } catch (error) {
      stopCamera(); notify(error.message);
    } finally {button.disabled = false;}
  });
  function resetPreview() {
    selected = null; manifestArrived = false;
    el('scannerPhysicalCount').value = '';
    el('scannerReview').classList.add('hidden');
    el('scannerReceiveAll').classList.add('hidden');
    el('scannerCountGroup').classList.add('hidden');
    el('scannerDetails').replaceChildren();
  }
  async function call(routeName, method, data) {
    const url = new URL(routes[routeName], location.href);
    if (method === 'GET') for (const [key, value] of Object.entries(data)) url.searchParams.set(key, String(value));
    const response = await fetch(url, {
      method, credentials: 'same-origin',
      headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token},
      ...(method === 'POST' ? {body: JSON.stringify(data)} : {}),
    });
    const body = await response.json().catch(() => ({}));
    if (!response.ok || body.success !== true) {
      const firstError = body.errors && Object.values(body.errors).flat()[0];
      throw new Error(String(firstError || body.message || `Request failed (${response.status}). Sign in again if session expired.`));
    }
    return body;
  }
  function centerId() { return Number(el('scannerCenter').value || 0); }
  function displayDetails(data) {
    el('scannerDetails').replaceChildren();
    const keys = ['manifest_code','code','tracking_code','from','to','from_center','to_center','branch','status',
      'expected','loaded','received','parcel_count','trip_code','trip_status','next_action','delivery_status','vehicle_code'];
    for (const key of keys) {
      const value = data?.[key];
      if (value === undefined || value === null || typeof value === 'object') continue;
      const item = document.createElement('div');
      const heading = document.createElement('dt'); heading.className = 'text-xs font-semibold text-slate-500';
      heading.textContent = key.replaceAll('_', ' ').replace(/\b\w/g, m => m.toUpperCase());
      const content = document.createElement('dd'); content.className = 'mt-0.5 text-sm font-semibold text-slate-900';
      content.textContent = String(value);
      item.append(heading, content); el('scannerDetails').append(item);
    }
    if (!el('scannerDetails').children.length) {
      const item = document.createElement('p'); item.textContent = 'Server verified the QR. Review the current status before confirming.';
      el('scannerDetails').append(item);
    }
  }
  async function loadBranches() {
    try {
      const result = await call('branches', 'GET', {});
      const branches = result.data?.branches || [];
      const select = el('scannerCenter'); select.replaceChildren();
      for (const branch of branches) {
        const option = new Option(`${branch.name} · ${branch.code}`, String(branch.id)); select.add(option);
      }
      if (!branches.length) notify('No active sorting center linked to this login. Contact Logistics Admin.');
      if (result.data?.branch_locked) select.disabled = true;
      if (branches.length) await loadManifests();
    } catch (error) { notify(error.message); }
  }
  async function loadManifests() {
    const select = el('scannerManifest'); select.replaceChildren();
    select.add(new Option('Choose active outbound manifest', ''));
    if (!centerId()) return;
    try {
      const result = await call('outbound-manifests', 'GET', {sorting_center_id:centerId()});
      for (const manifest of result.data?.manifests || []) {
        select.add(new Option(`${manifest.code} · ${manifest.loaded}/${manifest.expected} loaded`, String(manifest.id)));
      }
    } catch (error) { notify(error.message); }
  }
  function setMode(newMode) {
    if (working) return;
    mode = newMode; stopCamera(); resetPreview();
    el('scannerCode').value = '';
    el('scannerHelp').textContent = help[mode];
    el('scannerOutboundGroup').classList.toggle('hidden', mode !== 'outbound');
    el('scannerCodeLabel').textContent = mode === 'inbound' ? 'Manifest QR code *' : 'Parcel tracking code *';
    root.querySelectorAll('[data-mode]').forEach(btn => {
      const isCurrent = btn.dataset.mode === mode;
      btn.className = isCurrent ? 'sh-btn' : 'sh-btn-ghost';
      btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
    });
  }
  root.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => setMode(button.dataset.mode)));
  el('scannerCenter').addEventListener('change', async () => { resetPreview(); await loadManifests(); });
  el('scannerCode').addEventListener('input', resetPreview);
  el('scannerManifest').addEventListener('change', resetPreview);
  el('scannerCancel').addEventListener('click', resetPreview);
  async function run(action, fn) {
    if (working || !centerId()) return;
    working = true; root.querySelectorAll('button').forEach(button => button.disabled = true);
    try { await fn(); } catch (error) { notify(error.message); }
    finally {
      working = false;
      root.querySelectorAll('button').forEach(button => button.disabled = false);
    }
  }
  async function preview() {
    const code = el('scannerCode').value.trim().toUpperCase();
    if (!code) { notify('Scan or type a code first.'); return; }
    if (mode === 'outbound' && !el('scannerManifest').value) { notify('Select an outbound manifest first.'); return; }
    await run('preview', async () => {
      resetPreview();
      const fields = {sorting_center_id:centerId()};
      let result;
      if (mode === 'inbound') result = await call('inbound-preview', 'GET', {...fields, manifest_code:code});
      else if (mode === 'outbound') result = await call('outbound-preview', 'GET', {...fields, manifest_id:el('scannerManifest').value, tracking_code:code});
      else result = await call('parcel-preview', 'GET', {...fields, tracking_code:code});
      selected = {code, center:centerId(), manifestId:el('scannerManifest').value, mode};
      const record = result.data?.manifest || result.data?.parcel || {};
      manifestArrived = record.arrival_acknowledged === true;
      displayDetails(record);
      el('scannerReview').classList.remove('hidden');
      el('scannerCountGroup').classList.toggle('hidden', mode !== 'inbound' || !manifestArrived);
      el('scannerConfirm').textContent = mode === 'inbound' ? 'Acknowledge arrival' : mode === 'outbound' ? 'Confirm parcel loaded' : 'Confirm parcel received';
      el('scannerConfirm').classList.toggle('hidden', mode === 'inbound' && manifestArrived);
      el('scannerReceiveAll').classList.toggle('hidden', mode !== 'inbound' || !manifestArrived);
      notify(result.message || 'QR verified. Confirm only after physical handoff.', true);
    });
  }
  el('scannerPreview').addEventListener('click', preview);
  el('scannerConfirm').addEventListener('click', async () => {
    if (!selected) return;
    if (!confirm('Have you physically verified the parcel or manifest and are you authorized to confirm this custody action?')) return;
    await run('confirm', async () => {
      const data = {sorting_center_id:selected.center};
      let result;
      if (selected.mode === 'inbound') result = await call('arrival', 'POST', {...data, manifest_code:selected.code});
      else if (selected.mode === 'outbound') result = await call('outbound-load', 'POST', {...data, manifest_id:selected.manifestId, tracking_code:selected.code});
      else result = await call('parcel-receive', 'POST', {...data, tracking_code:selected.code});
      notify(result.message, true);
      if (selected.mode === 'inbound') {
        manifestArrived = true;
        el('scannerConfirm').classList.add('hidden');
        el('scannerCountGroup').classList.remove('hidden');
        el('scannerReceiveAll').classList.remove('hidden');
        displayDetails(result.data?.manifest || {});
      } else { resetPreview(); await loadManifests(); }
    });
  });
  el('scannerReceiveAll').addEventListener('click', async () => {
    if (!selected || selected.mode !== 'inbound' || !manifestArrived) return;
    const count = Number(el('scannerPhysicalCount').value);
    if (!Number.isInteger(count) || count < 1) { notify('Enter the actual physical parcel count first.'); return; }
    if (!confirm(`Receive all ${count} parcels as an intact manifest? Continue only after counting and verifying each physical parcel.`)) return;
    await run('receive', async () => {
      const result = await call('receive-all', 'POST', {
        sorting_center_id:selected.center, manifest_code:selected.code, physical_count:count,
      });
      notify(result.message, true); resetPreview(); await loadManifests();
    });
  });
  document.addEventListener('visibilitychange', () => { if (document.hidden) stopCamera(); });
  window.addEventListener('pagehide', stopCamera);
  setMode('inbound'); loadBranches();
})();
</script>
@endsection
