@extends('branch.layout.app')
@section('title','Dispatch')
@section('content')
<div class="space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">{{ $center->name }}</p><h1 class="title">Rider Dispatch</h1><p class="description">Pickup and last-mile assignment queues. Only riders eligible for the selected barangay are offered by the backend.</p></div>
        <a href="{{ route('branch.scanner') }}" class="sh-btn-soft">Open QR Scanner</a></header>
    @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-3">
        @foreach([['Pending pickup',$pickups->total(),'Awaiting pickup assignment'],['Ready for delivery',$deliveries->total(),'Awaiting last-mile assignment'],['Active riders',count($riders),'Current branch login accounts']] as [$label,$count,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value">{{ number_format($count) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </section>
    <div class="ops-panel" data-ops-tabs>
        <nav class="ops-tabs" role="tablist" aria-label="Dispatch queues">
            <button class="ops-tab" type="button" role="tab" aria-selected="true" data-ops-tab="pickups">Seller Pickups ({{ $pickups->total() }})</button>
            <button class="ops-tab" type="button" role="tab" aria-selected="false" data-ops-tab="deliveries">Buyer Deliveries ({{ $deliveries->total() }})</button>
        </nav>
        <section id="pickups" data-ops-panel="pickups" role="tabpanel">
            <div class="ops-panel-head"><div><h2>Pickup requests</h2><p class="ops-panel-note">Choose an eligible rider to collect the parcel from the seller.</p></div><input class="sh-input" data-ops-search="pickupRows" type="search" placeholder="Search this page" aria-label="Search pickups" style="max-width:250px"></div>
            <div class="ops-table-scroll"><table id="pickupRows" class="ops-table"><thead><tr><th>Seller / Parcel</th><th>Pickup location</th><th>Requested</th><th>Ready</th><th>Eligible riders</th><th>Action</th></tr></thead><tbody>
            @forelse($pickups as $p)<tr data-ops-row>
                <td><strong>{{ $p['seller'] }}</strong><div class="ops-row-sub">{{ $p['tracking'] ?? 'Tracking pending' }}</div></td>
                <td>{{ $p['address'] ?: '—' }}</td><td>{{ $p['requested'] ?? '—' }}</td>
                <td><span class="sh-badge {{ $p['ready'] ? 'sh-badge-success':'sh-badge-warning' }}">{{ $p['ready'] ? 'Ready' : 'Not ready' }}</span></td>
                <td>{{ count($p['riders']) }} matched</td>
                <td class="ops-row-actions"><button type="button" class="ops-action" data-dispatch-open data-kind="pickup" data-name="{{ $p['seller'] }}" data-tracking="{{ $p['tracking'] ?? '' }}" data-url="{{ route('branch.dispatch.pickups.assign',$p['id']) }}" data-riders="{{ json_encode($p['riders']) }}" data-suggested="{{ $p['suggested'] }}" @disabled(! $p['ready'] || empty($p['riders']))>Assign pickup</button></td>
            </tr>@empty<tr><td class="ops-empty" colspan="6">No pickup requests currently waiting.</td></tr>@endforelse
            <tr data-ops-search-empty hidden><td colspan="6" class="ops-empty">No matching pickups on this page.</td></tr>
            </tbody></table></div>
            <div class="ops-count">{{ $pickups->firstItem() ?? 0 }}–{{ $pickups->lastItem() ?? 0 }} of {{ $pickups->total() }} requests</div>
            @if($pickups->hasPages())<div class="border-t p-4">{{ $pickups->links() }}</div>@endif
        </section>
        <section id="deliveries" data-ops-panel="deliveries" role="tabpanel" hidden>
            <div class="ops-panel-head"><div><h2>Ready for last-mile delivery</h2><p class="ops-panel-note">QR receiving and sorting must be completed before rider dispatch.</p></div><input class="sh-input" data-ops-search="deliveryRows" type="search" placeholder="Search this page" aria-label="Search deliveries" style="max-width:250px"></div>
            <div class="ops-table-scroll"><table id="deliveryRows" class="ops-table"><thead><tr><th>Parcel</th><th>Receiver / barangay</th><th>Payment</th><th>Type</th><th>Eligible riders</th><th>Action</th></tr></thead><tbody>
            @forelse($deliveries as $d)<tr data-ops-row>
                <td><strong>{{ $d['tracking'] }}</strong><div class="ops-row-sub">{{ $d['failed'] ? 'Failed attempt #'.$d['attempts'] : 'Sorted / waiting' }}</div></td>
                <td>{{ $d['receiver'] ?: '—' }}<div class="ops-row-sub">{{ $d['barangay'] ?: '—' }}</div></td>
                <td>{{ $d['cod'] !== null ? 'COD ₱'.number_format($d['cod'],2) : 'Prepaid' }}</td>
                <td><span class="sh-badge {{ $d['failed'] ? 'sh-badge-warning':'sh-badge-info' }}">{{ $d['failed'] ? 'Retry' : ($d['local'] ? 'Local' : 'Transfer') }}</span></td>
                <td>{{ count($d['riders']) }} matched</td>
                <td class="ops-row-actions"><button type="button" class="ops-action" data-dispatch-open data-kind="delivery" data-name="{{ $d['receiver'] }}" data-tracking="{{ $d['tracking'] }}" data-url="{{ route('branch.dispatch.deliveries.assign',$d['id']) }}" data-riders="{{ json_encode($d['riders']) }}" data-suggested="{{ $d['suggested'] }}" @disabled(empty($d['riders']))>{{ $d['failed'] ? 'Retry delivery':'Assign rider' }}</button></td>
            </tr>@empty<tr><td colspan="6" class="ops-empty">No parcels ready for rider assignment.</td></tr>@endforelse
            <tr data-ops-search-empty hidden><td colspan="6" class="ops-empty">No matching deliveries on this page.</td></tr>
            </tbody></table></div>
            <div class="ops-count">{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} parcels</div>
            @if($deliveries->hasPages())<div class="border-t p-4">{{ $deliveries->links() }}</div>@endif
        </section>
    </div>
</div>
<dialog id="dispatchAssignModal" class="ops-dialog">
    <form method="POST" id="dispatchAssignForm" data-confirm-title="Confirm rider assignment?" data-confirm-message="The assigned rider must still scan the parcel for physical custody handoff." data-confirm-button="Assign rider">
        @csrf
        <div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="dispatchAssignTitle">Assign rider</h2><p class="ops-panel-note" id="dispatchAssignTracking"></p></div><button type="button" class="ops-action" data-ops-close aria-label="Close">✕</button></div>
        <div class="ops-dialog-body space-y-4"><label class="sh-field"><span class="sh-label">Eligible rider *</span><select class="sh-select" name="rider_id" id="dispatchRiderSelect" required></select></label><p class="ops-inline-alert">Only riders authorized for the location are listed. Final eligibility is rechecked when you save.</p></div>
        <div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-ops-close>Cancel</button><button type="submit" class="sh-btn">Confirm assignment</button></div>
    </form>
</dialog>
@endsection
@push('scripts')
<script>
(() => {
 const modal=document.getElementById('dispatchAssignModal'), form=document.getElementById('dispatchAssignForm'), select=document.getElementById('dispatchRiderSelect');
 document.querySelectorAll('[data-dispatch-open]').forEach(button=>button.addEventListener('click',()=>{
   if(button.disabled)return;
   let options=[];try{options=JSON.parse(button.dataset.riders||'[]')}catch(_){return}
   select.replaceChildren(new Option('Choose rider',''));
   options.forEach(r=>{const option=new Option(r.name,r.id);option.selected=String(r.id)===button.dataset.suggested;select.add(option)});
   document.getElementById('dispatchAssignTitle').textContent=(button.dataset.kind==='pickup'?'Assign Pickup Rider':'Assign Delivery Rider');
   document.getElementById('dispatchAssignTracking').textContent=(button.dataset.name||'')+' · '+(button.dataset.tracking||'');
   form.action=button.dataset.url;modal.showModal();
 }));
})();
</script>
@endpush
