@extends('logistics.layouts.console')
@section('title','COD Settlements')
@section('content')
@php
    $queues = [''=>'All records', 'collected'=>'With rider', 'remitted'=>'To verify', 'reconciled'=>'To remit',
        'admin_pending'=>'Awaiting ShopHop', 'admin_received'=>'ShopHop received', 'review'=>'Under review'];
@endphp
<div class="sh-ops sh-finance-ops space-y-4">
    <header class="sh-page-header">
        <div><p class="eyebrow">Finance · Cash custody</p><h1 class="title">COD Settlements</h1>
            <p class="description">Review collected cash, verify handovers, and track remittances to ShopHop.</p></div>
        <div class="flex flex-wrap gap-2">
            <a class="sh-btn-soft" href="{{ route('logistics.hybrid', ['section'=>'handovers']) }}">Cash handovers</a>
            <a class="sh-btn-soft" href="{{ route('logistics.earnings') }}">Earnings & Payouts</a>
        </div>
    </header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-5" aria-label="Recorded COD by custody stage">
        @foreach([['collected','With rider','Recorded collected COD'],['remitted','Awaiting verification','COD awaiting receipt check'],['reconciled','Ready to remit','Verified cash received'],['admin_pending','Awaiting ShopHop','Recorded remittance amount'],['review','Under review','Expected COD in open cases']] as [$key,$label,$note])
        <a class="sh-stat block" href="{{ route('logistics.settlements.index', ['status'=>$key]) }}">
            <p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value ops-money-value">₱{{ number_format((float)$amounts[$key],2) }}</p>
            <p class="sh-stat-note">{{ number_format($stats[$key]) }} record(s) · {{ $note }}</p>
        </a>
        @endforeach
    </section>
    <p class="sh-notice">The full COD is handed over. Rider earnings are paid separately by Logistics. ShopHop receipt confirmation and seller settlement are separate steps.</p>
    <section class="ops-panel">
        <nav class="sh-console-tabs" aria-label="COD custody queues">
            @foreach($queues as $value=>$label)
            <a href="{{ request()->fullUrlWithQuery(['status'=>$value, 'page'=>null]) }}" @if($status===$value) aria-current="page" @endif>
                {{ $label }} <span class="sh-tab-count">{{ number_format($stats[$value ?: 'all']) }}</span>
            </a>
            @endforeach
        </nav>
        <div class="ops-header"><div><h2>Cash custody records</h2><p class="ops-panel-note">All delivered COD records for your company. Queue totals include all records before search.</p></div></div>
        <form method="GET" class="ops-filters" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="sh-field"><span class="sh-label">Order, tracking, rider or seller</span><input name="q" class="sh-input" value="{{ $search }}" maxlength="100" placeholder="Search COD records"></label>
            <button class="sh-btn-soft" type="submit">Search</button><a class="sh-btn-ghost" href="{{ route('logistics.settlements.index', ['status'=>$status]) }}">Clear</a>
        </form>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr>
            <th scope="col">Reference</th><th scope="col">Rider / Receiving branch</th><th scope="col" class="ops-money">Expected COD</th>
            <th scope="col" class="ops-money">Verified received</th><th scope="col" class="ops-money">Difference</th><th scope="col">Status</th><th scope="col">Next action</th>
        </tr></thead><tbody>
        @forelse($deliveries as $delivery)
            @php
                $cash = $delivery->codSettlement;
                $reviewCase = $cash ? $openCases->get($cash->id) : null;
                $handover = $cash ? $handovers->get($cash->id) : null;
                $rowStage = !$cash ? 'No cash record' : ($reviewCase ? 'Under review' : ($handover && !$handover->verified_at ? 'To verify'
                    : ($cash->admin_received_at ? 'ShopHop received' : ($cash->admin_remitted_at ? 'Awaiting ShopHop' : ($queues[$cash->status] ?? ucfirst($cash->status))))));
                $badge = $reviewCase ? 'sh-badge-warning' : ($cash?->admin_received_at ? 'sh-badge-success' : 'sh-badge-neutral');
                $difference = $cash && $cash->received_amount !== null
                    ? \App\Services\FinanceMoney::minor($cash->received_amount) - \App\Services\FinanceMoney::minor($cash->expected_amount) : null;
                $actionLabel = !$cash ? 'View record' : ($reviewCase ? 'Review discrepancy' : ($cash->admin_received_at ? 'View receipt'
                    : ($cash->admin_remitted_at ? 'View remittance' : ($cash->status === 'remitted' ? 'Verify receipt'
                    : ($cash->status === 'reconciled' ? 'Record remittance' : 'View collection')))));
            @endphp
            <tr>
                <td><strong class="ops-reference">Order #{{ $delivery->order_id }}</strong><div class="ops-row-sub">{{ $delivery->tracking_code ?: 'Tracking pending' }}</div><div class="ops-row-sub">{{ $delivery->order?->seller?->business_name ?: 'Seller unavailable' }}</div></td>
                <td>{{ $cash?->collector?->name ?: ($delivery->rider?->name ?: 'Rider unavailable') }}
                    <div class="ops-row-sub">{{ $handover?->branch_name ?: ($cash?->remitted_at ? 'Company handover · no branch recorded' : 'Handover not recorded') }}</div></td>
                <td class="ops-money">{{ $cash ? '₱'.number_format((float)$cash->expected_amount,2) : '—' }}</td>
                <td class="ops-money">{{ $cash?->received_amount !== null ? '₱'.number_format((float)$cash->received_amount,2) : 'Pending' }}</td>
                <td class="ops-money">{{ $difference === null ? '—' : '₱'.number_format($difference / 100,2) }}</td>
                <td><span class="sh-badge {{ $badge }}">{{ $rowStage }}</span>
                    @if($reviewCase)<div class="ops-row-sub">{{ $reviewCase->reference }}</div>@endif</td>
                <td class="ops-row-actions">
                    @if($reviewCase)<a class="ops-action" data-console-details data-console-detail-target="case-{{ $reviewCase->id }}" href="{{ route('logistics.hybrid.discrepancies.index', ['case'=>$reviewCase->id, 'status'=>'all']) }}#case-{{ $reviewCase->id }}">{{ $actionLabel }}</a>
                    @elseif($handover && !$handover->verified_at)<a class="ops-action" href="{{ route('logistics.hybrid', ['section'=>'handovers']) }}#handover-{{ $handover->batch_id }}">Verify branch handover</a>
                    @else<button class="ops-action" type="button" data-ops-template="cod-record-{{ $delivery->id }}" data-ops-title="COD · Order #{{ $delivery->order_id }}" data-ops-hint="Review the actual cash receipt and next responsible actor">{{ $actionLabel }}</button>@endif
                </td>
            </tr>
        @empty<tr><td class="ops-empty" colspan="7"><strong>No COD records match this queue.</strong><div class="ops-row-sub">Try another queue or clear your search.</div></td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-footer"><span>{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} COD deliveries</span>{{ $deliveries->links() }}</div>
    </section>
    @foreach($deliveries as $delivery)
    <template id="cod-record-{{ $delivery->id }}">
        @php $cash = $delivery->codSettlement; $handover = $cash ? $handovers->get($cash->id) : null; @endphp
        <div class="ops-modal-meta"><p><strong>Tracking:</strong> {{ $delivery->tracking_code ?: '—' }}</p><p><strong>Seller:</strong> {{ $delivery->order?->seller?->business_name ?: '—' }}</p><p><strong>Rider:</strong> {{ $delivery->rider?->name ?: '—' }}</p><p><strong>Expected:</strong> {{ $cash ? '₱'.number_format((float)$cash->expected_amount,2) : '—' }}</p><p><strong>Collected:</strong> {{ $cash ? '₱'.number_format((float)$cash->collected_amount,2) : '—' }}</p><p><strong>Collected on:</strong> {{ $cash?->collected_at?->format('M j, Y g:i A') ?: '—' }}</p></div>
        @if($cash)
        <div class="sh-flow" aria-label="COD cash flow"><span>Rider handover</span><span>Branch verification</span><span>Logistics remittance</span><span>ShopHop receipt</span></div>
        @endif
        @if(!$cash)<p class="ops-inline-alert">No COD settlement record for this delivery. Do not fabricate a cash receipt.</p>
        @elseif($reviewCase = $openCases->get($cash->id))
            <p class="ops-inline-alert">COD discrepancy review is required before remitting this cash to ShopHop.</p>
            <a class="sh-btn-soft" data-console-details data-console-detail-target="case-{{ $reviewCase->id }}" href="{{ route('logistics.hybrid.discrepancies.index', ['case'=>$reviewCase->id, 'status'=>'all']) }}#case-{{ $reviewCase->id }}">Review cash case</a>
        @elseif($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED)
            <p class="ops-inline-alert">Next action: the Rider hands over the full COD to the receiving Branch. The Branch then verifies the counted cash.</p>
        @elseif($handover && !$handover->verified_at)
            <p class="sh-notice">Next action: the receiving Branch or Logistics company must count and verify this cash handover.</p>
            <a class="sh-btn" href="{{ route('logistics.hybrid', ['section'=>'handovers']) }}#handover-{{ $handover->batch_id }}">Review branch handover</a>
        @elseif($cash->status === \App\Models\Logistics\CodSettlement::REMITTED)
            <p class="ops-inline-alert">Remitted by {{ $cash->remitter?->name ?: 'Rider' }} at {{ $cash->remitted_at?->format('M j, Y g:i A') ?: '—' }}. Confirm physical cash receipt after verifying the amount.</p>
            <form method="POST" class="space-y-3" action="{{ route('logistics.settlements.reconcile',$delivery) }}" data-confirm-title="Confirm COD cash receipt?" data-confirm-message="Only confirm after the actual rider remittance is received and matches the expected amount." data-confirm-button="Confirm cash receipt">@csrf
                <div class="ops-dialog-actions"><button class="sh-btn">Confirm receipt · ₱{{ number_format((float)$cash->collected_amount,2) }}</button></div>
            </form>
        @elseif($cash->status === \App\Models\Logistics\CodSettlement::RECONCILED)
            <div class="ops-modal-section"><h3>Logistics receipt confirmed</h3><p class="text-xs">Received ₱{{ number_format((float)$cash->received_amount,2) }} on {{ $cash->reconciled_at?->format('M j, Y g:i A') ?: '—' }}.</p></div>
            @if($cash->admin_received_at)
                <p class="ops-inline-alert">ShopHop Admin confirmed ₱{{ number_format((float)$cash->admin_received_amount,2) }} on {{ $cash->admin_received_at->format('M j, Y g:i A') }}.</p>
            @elseif($cash->admin_remitted_at)
                <p class="ops-inline-alert">Sent to ShopHop with reference <strong>{{ $cash->admin_remittance_reference }}</strong>; Next action: ShopHop Admin independently verifies the actual receipt.</p>
            @else
                <form method="POST" class="space-y-3" action="{{ route('logistics.settlements.remit-admin',$delivery) }}" data-confirm-title="Record remittance to ShopHop?" data-confirm-message="Only record after the funds have actually been sent to ShopHop Admin; Admin verifies independently." data-confirm-button="Record remittance">@csrf
                    <label class="sh-field"><span class="sh-label">Actual remittance reference *</span><input class="sh-input" name="reference" required minlength="4" maxlength="120" placeholder="Receipt / bank reference"></label>
                    <div class="ops-dialog-actions"><button class="sh-btn">Record ShopHop remittance</button></div>
                </form>
            @endif
        @else
            <p class="sh-notice sh-notice-warning">This cash record needs Logistics review before further remittance. Open Cash Handovers to check the original receipt.</p>
        @endif
    </template>
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">COD details</h2><p class="ops-panel-note" id="opsRecordHint"></p></div><button class="ops-action" type="button" data-ops-close aria-label="Close">✕</button></div><div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-ghost" type="button" data-ops-close>Close</button></div></dialog>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261104-bh"><link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261104-finance">@endpush
