@extends('logistics.layouts.console')
@section('title', 'Transport Routes — ShopHop Logistics')
@section('content')
<div class="ops-page space-y-4">
 <header class="flex flex-wrap justify-between items-end gap-3"><div><p class="eyebrow">NETWORK OPERATIONS</p><h1 class="ops-title">Transport Connections</h1><p class="ops-subtitle">Verified directional links govern actual road, sea and air transfers. A proposed link cannot carry parcels until verified.</p></div><button class="ops-button ops-button-primary" type="button" data-ops-open="routeModal">+ Add connection</button></header>
 @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
 @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
 <div class="ops-note">A route is directional. Do not verify road connections for cross-island legs. Existing verified and historically used routes cannot be silently replaced.</div>
 <section class="ops-panel"><div class="ops-header"><div><h2 class="font-bold text-navy">Network links</h2><p class="ops-subtitle">Search this page, review proposed routes and suspend unavailable connections</p></div><label class="sh-field" style="min-width:220px"><span class="sh-label">Find route on this page</span><input id="routeSearch" class="sh-input" placeholder="Center, mode, notes" type="search"></label></div>
 <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Origin → Destination</th><th>Transport mode</th><th>Operations notes</th><th>Status</th><th>Actions</th></tr></thead><tbody id="routeRows">
 @forelse($links as $link)
 <tr><td><div class="ops-primary">{{ $link->fromCenter?->name ?: 'Removed' }} → {{ $link->toCenter?->name ?: 'Removed' }}</div></td><td>{{ strtoupper($link->mode) }}</td><td class="text-xs">{{ $link->notes }}</td><td><span class="ops-badge" data-status="{{ $link->status }}">{{ ucfirst($link->status) }}</span></td>
 <td><details class="ops-actions"><summary class="ops-button">Actions ▾</summary><div class="ops-actions-menu">
  @if(!in_array($link->status,['verified'],true))<button type="button" data-ops-open="routeModal" data-ops-record-edit="routeModal" data-title="Revise proposed connection" data-url="{{ route('logistics.route-network.store') }}" data-subject="{{ $link->fromCenter?->name }} → {{ $link->toCenter?->name }}" data-fields="{{ json_encode(['from_center_id'=>$link->from_center_id,'to_center_id'=>$link->to_center_id,'mode'=>$link->mode,'notes'=>$link->notes],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG) }}">Revise proposal</button>
  <form method="POST" action="{{ route('logistics.route-network.verify',$link->id) }}" data-confirm-title="Verify physical route?" data-confirm-message="Confirm a real operational carrier and gateway. Only verified links can be used for transfer." data-confirm-button="Verify route">@csrf<input type="hidden" name="confirmation" value="VERIFY_ACTUAL_TRANSPORT"><button>Verify route</button></form>@endif
  @if($link->status!=='suspended')<form method="POST" action="{{ route('logistics.route-network.suspend',$link->id) }}" data-confirm-title="Suspend connection?" data-confirm-message="This connection can no longer be used as a verified route." data-confirm-button="Suspend">@csrf<button class="ops-danger">Suspend</button></form>@endif
 </div></details></td></tr>
 @empty<tr><td colspan="5" class="ops-empty">No routes configured. Propose a link only for actual transport operations.</td></tr>@endforelse
 </tbody></table></div><footer class="ops-footer"><span>{{ $links->firstItem() ?? 0 }}–{{ $links->lastItem() ?? 0 }} of {{ number_format($links->total()) }} links</span><div>{{ $links->links() }}</div></footer></section>
</div>
<dialog id="routeModal" data-ops-modal class="ops-modal" aria-labelledby="routeModalTitle"><div class="ops-modal-head"><div><h2 id="routeModalTitle" data-ops-title>Add transport connection</h2><p data-ops-subject>Only physically available directional routes may be verified.</p></div><button class="ops-button" type="button" data-ops-close>✕</button></div>
 <form method="POST" action="{{ route('logistics.route-network.store') }}" data-confirm-title="Save proposed connection?" data-confirm-button="Save proposal" data-confirm-message="This creates a proposed route, not a verified operational connection.">@csrf
  <div class="ops-modal-body"><div class="ops-fields">
   <label class="sh-field"><span class="sh-label">From center *</span><select class="sh-select" name="from_center_id" required><option value="">Select origin</option>@foreach($centers as $center)<option value="{{ $center->id }}">{{ $center->name }}</option>@endforeach</select></label>
   <label class="sh-field"><span class="sh-label">To center *</span><select class="sh-select" name="to_center_id" required><option value="">Select destination</option>@foreach($centers as $center)<option value="{{ $center->id }}">{{ $center->name }}</option>@endforeach</select></label>
   <label class="sh-field"><span class="sh-label">Transport *</span><select class="sh-select" name="mode" required><option value="road">Road</option><option value="sea">Sea / ferry</option><option value="air">Air</option></select></label>
   <label class="sh-field ops-span"><span class="sh-label">Operations notes *</span><textarea name="notes" minlength="12" maxlength="500" required class="sh-textarea" rows="3" placeholder="Carrier, gateway and handoff arrangements"></textarea></label>
  </div><p class="ops-note mt-3">For cross-island Luzon / Visayas / Mindanao transfers, use SEA or AIR for the relevant cargo segment.</p></div>
  <div class="ops-modal-footer"><button class="ops-button" type="button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">Save proposal</button></div>
 </form>
</dialog>
@endsection
@push('scripts')
<script>
const routeSearch = document.getElementById('routeSearch');
routeSearch?.addEventListener('input', () => { const q=routeSearch.value.toLowerCase().trim();document.querySelectorAll('#routeRows tr').forEach(row => {row.hidden = !!q && !row.textContent.toLowerCase().includes(q)}); });
</script>
@endpush
