@extends($scannerLayout)
@section('title', 'QR Scanner')
@section('content')
<style>#shopHopWebScanner .hidden{display:none!important}</style>
@php
    $scannerUrls = [];
    foreach (['branches','inbound-preview','arrival','receive-all','outbound-manifests','outbound-preview','outbound-load','parcel-preview','parcel-receive'] as $route) {
        $scannerUrls[$route] = route($scannerPrefix.$route);
    }
@endphp
<div class="mx-auto max-w-5xl space-y-4" id="shopHopWebScanner" data-role="{{ $scannerRole }}">
    <header class="sh-surface sh-card-pad">
        <p class="sh-kicker">{{ $scannerRole === 'hub' ? 'PROVINCE HUB' : 'STATION BRANCH' }} / OPERATIONS</p>
        <h1 class="sh-title">QR Scanner</h1>
        <p class="mt-1 text-sm text-navy/60">Choose what you need to do, scan the QR, then review and confirm the physical handover.</p>
    </header>
    <section class="sh-surface sh-card-pad space-y-4">
        <div class="grid gap-3 md:grid-cols-[1fr_2fr] md:items-end">
            <label class="sh-field"><span class="sh-label">Receiving / sending branch <span class="text-red-600">*</span></span>
                <select id="scannerCenter" class="sh-input" aria-label="Receiving / sending branch" required><option value="">Loading authorized center...</option></select>
            </label>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Scan operation">
                @if($scannerRole === 'branch')
                <button type="button" data-mode="rider" class="sh-btn" role="tab" aria-selected="true">Receive from rider</button>
                @endif
                <button type="button" data-mode="inbound" class="sh-btn-ghost" role="tab" aria-selected="{{ $scannerRole === 'hub' ? 'true' : 'false' }}">Receive from another branch</button>
                <button type="button" data-mode="outbound" class="sh-btn-ghost" role="tab" aria-selected="false">Send to another branch</button>
            </div>
        </div>
        <details class="text-xs text-navy/60"><summary class="cursor-pointer">Other receiving options</summary><button type="button" data-mode="parcel" class="sh-btn-ghost mt-2">Receive a single transfer parcel</button><p class="mt-2">For individual transfers or returned parcels that are not being received as a whole manifest.</p></details>
        <div id="scannerHelp" class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600"></div>
        <div id="scannerOutboundGroup" class="hidden">
            <label class="sh-field"><span class="sh-label">Shipment manifest <span class="text-red-600">*</span></span>
                <select id="scannerManifest" class="sh-input"><option value="">Choose active manifest...</option></select>
            </label>
        </div>
        <label class="sh-field"><span id="scannerCodeLabel" class="sh-label">Manifest QR code <span class="text-red-600">*</span></span>
            <input id="scannerCode" type="text" class="sh-input" autocomplete="off" spellcheck="false" placeholder="Scan or enter code" aria-describedby="scannerHelp" required>
        </label>
        <div class="flex flex-wrap items-center gap-2">
            <button id="scannerCameraToggle" type="button" class="sh-btn-ghost">Open camera</button>
            <button id="scannerPreview" type="button" class="sh-btn">Review parcel</button>
        </div>
        <div id="scannerVideoBox" class="hidden overflow-hidden rounded-xl bg-slate-900" style="max-width:520px">
            <video id="scannerVideo" muted playsinline class="w-full" style="height:260px;object-fit:cover"></video>
        </div>
        <div id="scannerMessage" role="status" aria-live="polite" class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600">Scan the parcel QR or enter its tracking code, then review the details before receiving.</div>
    </section>
    <section id="scannerReview" class="sh-surface sh-card-pad hidden space-y-3" aria-labelledby="scannerReviewTitle">
        <div><p class="sh-kicker">REVIEW BEFORE RECEIVING</p><h2 id="scannerReviewTitle" class="sh-title-sm">Review before confirmation</h2></div>
        <p id="scannerHandover" class="rounded-lg bg-teal-light p-3 text-sm font-medium text-navy"></p>
        <dl id="scannerDetails" class="grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-2"></dl>
        <div id="scannerCountGroup" class="hidden">
            <label class="sh-field"><span class="sh-label">Physically counted parcels <span class="text-red-600">*</span></span>
                <input id="scannerPhysicalCount" type="number" min="1" max="5000" class="sh-input" inputmode="numeric" placeholder="Enter actual parcel count">
            </label>
            <p class="mt-1 text-xs text-navy/55">The count must match the parcels physically present. If any parcel is missing or damaged, use Other receiving options to process parcels individually.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="scannerConfirm" type="button" class="sh-btn">Confirm receipt</button>
            <button id="scannerReceiveAll" type="button" class="sh-btn hidden">Receive all intact</button>
            <button id="scannerCancel" type="button" class="sh-btn-ghost">Clear preview</button>
        </div>
    </section>
    <p class="text-xs text-navy/50">Camera requires HTTPS or localhost. You can always enter the tracking code manually. Receipt is recorded only after you confirm.</p>
    @if($scannerRole === 'branch')<a class="sh-btn-ghost inline-flex" href="{{ route('branch.dispatch.index') }}">Back to Rider Dispatch</a>@endif
</div>
<script src="/js/shophop-web-qr.js"></script>
<script>
(() => {
  const routes = @json($scannerUrls);
  const token = @json(csrf_token());
  const root = document.getElementById('shopHopWebScanner');
  if (!root) return;
  const el = id => document.getElementById(id);
  const defaultMode = root.dataset.role === 'branch' ? 'rider' : 'inbound';
  let mode = defaultMode, selected = null, working = false;
  let manifestArrived = false;
  const help = {
    rider: 'Receive parcels brought back by a rider. Scan the parcel QR or enter its tracking code, review the handover, then confirm receipt.',
    inbound: 'Receive a shipment from another branch or hub. Scan its manifest QR, confirm arrival, then count and receive the parcels.',
    outbound: 'Send parcels to another branch or hub. Choose the shipment manifest, scan each parcel, then confirm it has been loaded.',
    parcel: 'Receive one transfer or returned parcel. Scan its parcel QR and check the source and receiving branch before confirming.',
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
      notify('QR detected: ' + el('scannerCode').value + '. Review the details before confirming.', true);
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
    el('scannerHandover').textContent = '';
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
      if (response.status >= 500) throw new Error('Receiving is temporarily unavailable. Ask your administrator to check the database setup, then review the parcel again before retrying.');
      if ([401,403,419].includes(response.status)) throw new Error('Your session or access has changed. Sign in again before scanning.');
      const firstError = body.errors && Object.values(body.errors).flat()[0];
      throw new Error(String(firstError || body.message || `Request failed (${response.status}). Sign in again if session expired.`));
    }
    return body;
  }
  function centerId() { return Number(el('scannerCenter').value || 0); }
  function displayDetails(data) {
    el('scannerDetails').replaceChildren();
    const labels = {tracking_code:'Parcel tracking code', branch:'Receiving branch', rider:'From rider', delivery_status:'Parcel status', from_center:'From branch', to_center:'To branch', expected:'Expected parcels', loaded:'Loaded parcels', received:'Received parcels'};
    const keys = ['rider','manifest_code','code','tracking_code','from','to','from_center','to_center','branch','status',
      'expected','loaded','received','parcel_count','trip_code','trip_status','next_action','delivery_status','vehicle_code'];
    for (const key of keys) {
      const value = data?.[key];
      if (value === undefined || value === null || typeof value === 'object') continue;
      const item = document.createElement('div');
      const heading = document.createElement('dt'); heading.className = 'text-xs font-semibold text-slate-500';
      heading.textContent = labels[key] || key.replaceAll('_', ' ').replace(/\b\w/g, m => m.toUpperCase());
      const content = document.createElement('dd'); content.className = 'mt-0.5 text-sm font-semibold text-slate-900';
      content.textContent = key === 'delivery_status' ? String(value).replaceAll('_', ' ') : String(value);
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
        const option = new Option(`${branch.name} - ${branch.code}`, String(branch.id)); select.add(option);
      }
      if (!branches.length) notify('No active sorting center linked to this login. Contact Logistics Admin.');
      if (result.data?.branch_locked) select.disabled = true;
      if (branches.length && mode === 'outbound') await loadManifests();
      return branches.length > 0;
    } catch (error) { notify(error.message); }
  }
  async function loadManifests() {
    const select = el('scannerManifest'); select.replaceChildren();
    select.add(new Option('Choose active outbound manifest', ''));
    if (!centerId()) return;
    try {
      const result = await call('outbound-manifests', 'GET', {sorting_center_id:centerId()});
      for (const manifest of result.data?.manifests || []) {
        select.add(new Option(`${manifest.code} - ${manifest.loaded}/${manifest.expected} loaded`, String(manifest.id)));
      }
    } catch (error) { notify(error.message); }
  }
  function setMode(newMode) {
    if (working) return;
    mode = Object.hasOwn(help, newMode) ? newMode : defaultMode; stopCamera(); resetPreview();
    el('scannerCode').value = '';
    el('scannerHelp').textContent = help[mode];
    el('scannerPreview').textContent = mode === 'inbound' ? 'Review shipment' : 'Review parcel';
    el('scannerReviewTitle').textContent = mode === 'rider' ? 'Review rider handover' : 'Review before confirmation';
    notify(help[mode]);
    if (mode === 'outbound' && centerId()) loadManifests();
    el('scannerOutboundGroup').classList.toggle('hidden', mode !== 'outbound');
    el('scannerCodeLabel').textContent = mode === 'inbound' ? 'Manifest QR code *' : 'Parcel tracking code *';
    root.querySelectorAll('[data-mode]').forEach(btn => {
      const isCurrent = btn.dataset.mode === mode;
      btn.className = isCurrent ? 'sh-btn' : 'sh-btn-ghost';
      btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
    });
  }
  root.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => setMode(button.dataset.mode)));
  el('scannerCenter').addEventListener('change', async () => { resetPreview(); if (mode === 'outbound') await loadManifests(); });
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
      const record = result.data?.manifest || result.data?.parcel || {};
      if (mode === 'rider' && !['pickup_received','failed_delivery_received'].includes(record.event)) {
        throw new Error('This parcel is arriving from another branch. Choose Receive from another branch or use Other receiving options for a single transfer parcel.');
      }
      selected = {code, center:centerId(), manifestId:el('scannerManifest').value, mode};
      el('scannerHandover').textContent = mode === 'rider'
        ? `You are receiving parcel ${record.tracking_code || code} from ${record.rider || 'the rider'} at ${record.branch || el('scannerCenter').selectedOptions[0]?.textContent || 'this branch'}. Confirm only when the parcel is physically with you.`
        : mode === 'outbound' ? 'Check the parcel and destination, then confirm only after it has been physically loaded.'
        : 'Check the shipment and receiving branch before confirming the physical handover.';
      manifestArrived = record.arrival_acknowledged === true;
      displayDetails(record);
      el('scannerReview').classList.remove('hidden');
      el('scannerCountGroup').classList.toggle('hidden', mode !== 'inbound' || !manifestArrived);
      el('scannerConfirm').textContent = mode === 'inbound' ? 'Acknowledge arrival' : mode === 'outbound' ? 'Confirm parcel loaded' : 'Confirm receipt';
      el('scannerConfirm').classList.toggle('hidden', mode === 'inbound' && manifestArrived);
      el('scannerReceiveAll').classList.toggle('hidden', mode !== 'inbound' || !manifestArrived);
      notify(mode === 'rider' ? 'Details checked. Receipt has not been recorded yet. Review the parcel, then select Confirm receipt.' : (result.message || 'QR verified. Confirm only after physical handoff.'), true);
      el('scannerReview').scrollIntoView({behavior:'smooth', block:'nearest'});
    });
  }
  el('scannerPreview').addEventListener('click', preview);
  el('scannerConfirm').addEventListener('click', async () => {
    if (!selected) return;
    if (selected.mode !== 'rider' && !confirm('Have you physically verified the parcel or shipment and its destination?')) return;
    await run('confirm', async () => {
      const data = {sorting_center_id:selected.center};
      let result;
      if (selected.mode === 'inbound') result = await call('arrival', 'POST', {...data, manifest_code:selected.code});
      else if (selected.mode === 'outbound') result = await call('outbound-load', 'POST', {...data, manifest_id:selected.manifestId, tracking_code:selected.code});
      else result = await call('parcel-receive', 'POST', {...data, tracking_code:selected.code});
      notify(selected.mode === 'rider' ? 'Parcel received at this branch. Open Rider Dispatch to see the updated status.' : result.message, true);
      if (selected.mode === 'inbound') {
        manifestArrived = true;
        el('scannerConfirm').classList.add('hidden');
        el('scannerCountGroup').classList.remove('hidden');
        el('scannerReceiveAll').classList.remove('hidden');
        displayDetails(result.data?.manifest || {});
      } else { resetPreview(); el('scannerCode').value = ''; if (mode === 'outbound') await loadManifests(); }
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
  el('scannerCode').addEventListener('keydown', event => { if (event.key === 'Enter') {event.preventDefault(); preview();} });
  const params = new URLSearchParams(location.search);
  const requestedMode = params.get('mode');
  setMode(requestedMode === 'rider' && root.dataset.role !== 'branch' ? defaultMode : (requestedMode || defaultMode));
  const tracking = (params.get('tracking') || '').trim().toUpperCase();
  if (mode === 'rider' && /^SHP-[0-9]{8}-[0-9]{6,}$/.test(tracking)) el('scannerCode').value = tracking;
  loadBranches().then(ready => {if (ready && mode === 'rider' && el('scannerCode').value) preview();});
})();
</script>
@endsection
