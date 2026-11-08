@extends('logistics.layouts.console')
@section('title','COD Settlements — ShopHop Logistics')
@section('content')
<div class="sh-ops sh-finance-ops space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">Finance · Actual cash custody</p><h1 class="title">COD Settlements</h1><p class="description">Track cash collected by the rider, remitted to Logistics, reconciled by Logistics, and independently received by ShopHop Admin. No transfer occurs automatically.</p></div><a class="sh-btn-soft" href="{{ route('logistics.earnings') }}">Earnings & Payouts</a></header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @foreach([['With rider',$stats['collected'] ?? 0,'Collected'],['Awaiting Logistics',$stats['remitted'] ?? 0,'Remitted'],['Reconciled',$stats['reconciled'] ?? 0,'Cash receipt confirmed'],['Pending Admin',$stats['admin_pending'] ?? 0,'Sent, not verified'],['Admin received',$stats['admin_received'] ?? 0,'Fully acknowledged']] as [$label,$count,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value">{{ number_format($count) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </div>
    <p class="ops-inline-alert">Collected → Remitted by rider → Logistics reconciled → Remitted to ShopHop → Admin received. <strong>Rider fees are NOT deducted from COD cash.</strong> Confirm actions only after actual money handoff.</p>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Cash custody records</h2><p class="ops-panel-note">Search and status filter run across all delivered COD records for this logistics partner.</p></div>
            <form method="GET" class="ops-toolbar"><label><span class="sr-only">Search COD</span><input name="q" class="sh-input" value="{{ $search }}" placeholder="Order, rider or tracking"></label><label><span class="sr-only">COD status</span><select class="sh-select" name="status"><option value="">All stages</option>@foreach(['collected'=>'With rider','remitted'=>'Awaiting receipt','reconciled'=>'Reconciled','admin_pending'=>'Pending Admin','admin_received'=>'Admin received','none'=>'No cash record'] as $code=>$label)<option value="{{ $code }}" @selected($status===$code)>{{ $label }}</option>@endforeach</select></label><button class="sh-btn-soft">Filter</button><a class="sh-btn-ghost" href="{{ route('logistics.settlements.index') }}">Clear</a></form>
        </div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order / Tracking</th><th>Rider / Seller</th><th>Expected</th><th>Collected</th><th>Stage</th><th>Action</th></tr></thead><tbody>
            @forelse($deliveries as $delivery)
                @php
                    $cash = $delivery->codSettlement;
                    $stage = ! $cash ? 'No record' : ($cash->admin_received_at ? 'Admin received' : ($cash->admin_remitted_at ? 'Pending Admin' : ucwords(str_replace('_',' ',$cash->status))));
                    $badge = ! $cash ? 'sh-badge-neutral' : ($cash->admin_received_at ? 'sh-badge-success' : ($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED ? 'sh-badge-warning' : 'sh-badge-info'));
                @endphp
                <tr><td><strong>Order #{{ $delivery->order_id }}</strong><div class="ops-row-sub">{{ $delivery->tracking_code ?: 'Tracking pending' }}</div></td>
                    <td>{{ $delivery->rider?->name ?: 'Rider unavailable' }}<div class="ops-row-sub">{{ $delivery->order?->seller?->business_name ?: 'Seller unavailable' }}</div></td>
                    <td class="ops-money">{{ $cash ? '₱'.number_format((float)$cash->expected_amount,2) : '—' }}</td><td class="ops-money">{{ $cash ? '₱'.number_format((float)$cash->collected_amount,2) : '—' }}</td>
                    <td><span class="sh-badge {{ $badge }}">{{ $stage }}</span></td>
                    <td class="ops-row-actions"><button class="ops-action" type="button" data-ops-template="cod-record-{{ $delivery->id }}" data-ops-title="COD · Order #{{ $delivery->order_id }}" data-ops-hint="Verify cash evidence before confirming custody">{{ $cash ? 'Manage' : 'View' }}</button></td></tr>
            @empty<tr><td class="ops-empty" colspan="6">No COD records match your filters.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} COD deliveries</div>
        @if($deliveries->hasPages())<div class="border-t p-4">{{ $deliveries->links() }}</div>@endif
    </section>
    @foreach($deliveries as $delivery)
    <template id="cod-record-{{ $delivery->id }}">
        @php $cash = $delivery->codSettlement; @endphp
        <div class="ops-modal-meta"><p><strong>Tracking:</strong> {{ $delivery->tracking_code ?: '—' }}</p><p><strong>Seller:</strong> {{ $delivery->order?->seller?->business_name ?: '—' }}</p><p><strong>Rider:</strong> {{ $delivery->rider?->name ?: '—' }}</p><p><strong>Expected:</strong> {{ $cash ? '₱'.number_format((float)$cash->expected_amount,2) : '—' }}</p><p><strong>Collected:</strong> {{ $cash ? '₱'.number_format((float)$cash->collected_amount,2) : '—' }}</p><p><strong>Collected on:</strong> {{ $cash?->collected_at?->format('M j, Y g:i A') ?: '—' }}</p></div>
        @if(!$cash)<p class="ops-inline-alert">No COD settlement record for this delivery. Do not fabricate a cash receipt.</p>
        @elseif($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED)
            <p class="ops-inline-alert">Cash is still with the rider. The rider must remit and submit the correct COD amount before Logistics can reconcile.</p>
        @elseif($cash->status === \App\Models\Logistics\CodSettlement::REMITTED)
            <p class="ops-inline-alert">Remitted by {{ $cash->remitter?->name ?: 'Rider' }} at {{ $cash->remitted_at?->format('M j, Y g:i A') ?: '—' }}. Confirm physical cash receipt after verifying the amount.</p>
            <form method="POST" class="space-y-3" action="{{ route('logistics.settlements.reconcile',$delivery) }}" data-confirm-title="Confirm COD cash receipt?" data-confirm-message="Only confirm after the actual rider remittance is received and matches the expected amount." data-confirm-button="Confirm cash receipt">@csrf
                <div class="ops-dialog-actions"><button class="sh-btn">Confirm receipt · ₱{{ number_format((float)$cash->collected_amount,2) }}</button></div>
            </form>
        @else
            <div class="ops-modal-section"><h3>Logistics receipt confirmed</h3><p class="text-xs">Received ₱{{ number_format((float)$cash->received_amount,2) }} on {{ $cash->reconciled_at?->format('M j, Y g:i A') ?: '—' }}.</p></div>
            @if($cash->admin_received_at)
                <p class="ops-inline-alert">ShopHop Admin confirmed ₱{{ number_format((float)$cash->admin_received_amount,2) }} on {{ $cash->admin_received_at->format('M j, Y g:i A') }}.</p>
            @elseif($cash->admin_remitted_at)
                <p class="ops-inline-alert">Sent to ShopHop with reference <strong>{{ $cash->admin_remittance_reference }}</strong>; Admin acknowledgement is pending.</p>
            @else
                <form method="POST" class="space-y-3" action="{{ route('logistics.settlements.remit-admin',$delivery) }}" data-confirm-title="Record remittance to ShopHop?" data-confirm-message="Only record after the funds have actually been sent to ShopHop Admin; Admin verifies independently." data-confirm-button="Record remittance">@csrf
                    <label class="sh-field"><span class="sh-label">Actual remittance reference *</span><input class="sh-input" name="reference" required minlength="4" maxlength="120" placeholder="Receipt / bank reference"></label>
                    <div class="ops-dialog-actions"><button class="sh-btn">Record ShopHop remittance</button></div>
                </form>
            @endif
        @endif
    </template>
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">COD details</h2><p class="ops-panel-note" id="opsRecordHint"></p></div><button class="ops-action" type="button" data-ops-close aria-label="Close">✕</button></div><div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-ghost" type="button" data-ops-close>Close</button></div></dialog>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261104-bh"><link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261104-finance">@endpush
@push('scripts')<script defer src="{{ asset('js/shophop-operations-table.js') }}?v=20261104-finance"></script>@endpush
