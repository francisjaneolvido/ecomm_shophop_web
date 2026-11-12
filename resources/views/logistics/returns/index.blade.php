@extends('logistics.layouts.console')
@section('title','Returns & Exceptions — ShopHop Logistics')
@section('content')
<div class="sh-ops sh-finance-ops space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">Operations · Exceptions</p><h1 class="title">Returns & Exceptions</h1><p class="description">Track failed-delivery return cases, physical branch receipt, return rider assignment, and seller acknowledgement. Refunds are processed separately by Admin.</p></div><a class="sh-btn-ghost" href="{{ route('logistics.deliveries.board',['stage'=>'parcels','parcel_status'=>'delivery_failed']) }}">Failed delivery parcels</a></header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="sh-stat"><p class="sh-stat-label">Failed delivery exceptions</p><p class="sh-stat-value">{{ number_format($exceptionCount) }}</p><p class="sh-stat-note">Retry or initiate return</p></div>
        @foreach([['Return requested', \App\Models\Logistics\ReturnCase::REQUESTED,'Waiting for branch receipt'],['At branch', \App\Models\Logistics\ReturnCase::AT_BRANCH,'Assign a return rider'],['In return transit', \App\Models\Logistics\ReturnCase::IN_TRANSIT,'Rider is handling return'],['Awaiting seller', \App\Models\Logistics\ReturnCase::AWAITING_SELLER,'Seller acknowledgement required']] as [$title,$code,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $title }}</p><p class="sh-stat-value">{{ number_format($summary[$code] ?? 0) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </div>
    <p class="ops-inline-alert">Returning a parcel does not automatically restore stock or initiate a refund. Physical receipt, rider proof and seller acknowledgement remain separate custody actions.</p>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Failed delivery exceptions</h2><p class="ops-panel-note">These deliveries have no return case yet. Retry or return decisions remain in the validated Parcel Operations workflow.</p></div>
            <form method="GET" class="ops-toolbar"><input name="q" type="hidden" value="{{ $search }}"><input name="status" type="hidden" value="{{ $status }}"><label><span class="sr-only">Search failed deliveries</span><input class="sh-input" name="exception_q" value="{{ $exceptionSearch }}" maxlength="100" placeholder="Order, seller or tracking"></label><button class="sh-btn-soft">Search exceptions</button></form>
        </div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Parcel</th><th>Seller</th><th>Destination branch</th><th>Failed at</th><th>Reason</th><th>Action</th></tr></thead><tbody>
            @forelse($exceptions as $failure)
                <tr><td><strong>{{ $failure->tracking_code ?: 'Tracking pending' }}</strong><div class="ops-row-sub">Order #{{ $failure->order_id }}</div></td><td>{{ $failure->order?->seller?->business_name ?: '—' }}</td><td>{{ $failure->destinationSortingCenter?->name ?: '—' }}</td><td>{{ $failure->delivery_failed_at?->format('M j, Y') ?: '—' }}</td><td class="ops-wrap">{{ \Illuminate\Support\Str::limit($failure->failure_reason ?: 'No reason recorded', 95) }}</td><td class="ops-row-actions"><a class="ops-action inline-flex" href="{{ route('logistics.deliveries.board',['stage'=>'parcels','parcel_status'=>'delivery_failed','parcel_q'=>$failure->tracking_code ?: $failure->order_id]) }}">Review & decide</a></td></tr>
            @empty<tr><td class="ops-empty" colspan="6">No unresolved failed-delivery exceptions match the search.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">{{ $exceptions->firstItem() ?? 0 }}–{{ $exceptions->lastItem() ?? 0 }} of {{ $exceptions->total() }} failed deliveries</div>
        @if($exceptions->hasPages())<div class="p-4 border-t">{{ $exceptions->links() }}</div>@endif
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Return case directory</h2><p class="ops-panel-note">Database-wide search and status filters • 25 records per page.</p></div>
            <form method="GET" class="ops-toolbar"><label><span class="sr-only">Search return cases</span><input class="sh-input" name="q" value="{{ $search }}" placeholder="Order, tracking or seller"></label><label><span class="sr-only">Return status</span><select class="sh-select" name="status"><option value="">All stages</option>@foreach(['requested'=>'Requested','at_branch'=>'At branch','assigned'=>'Assigned','in_transit'=>'In transit','awaiting_seller'=>'Awaiting seller','seller_received'=>'Seller received'] as $value=>$label)<option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>@endforeach</select></label><button class="sh-btn-soft">Filter</button><a class="sh-btn-ghost" href="{{ route('logistics.returns.index') }}">Clear</a></form>
        </div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Case / Tracking</th><th>Seller</th><th>Return branch</th><th>Rider</th><th>Stage</th><th>Action</th></tr></thead><tbody>
            @forelse($cases as $case)
                <tr><td><strong>Return #{{ $case->id }}</strong><div class="ops-row-sub">Order #{{ $case->order_id }} · {{ $case->delivery?->tracking_code ?: '—' }}</div></td>
                    <td>{{ $case->order?->seller?->business_name ?: '—' }}</td>
                    <td>{{ $case->branch?->name ?: 'Not received' }}</td>
                    <td>{{ $case->returnRider?->name ?: 'Not assigned' }}</td>
                    <td><span class="sh-badge {{ $case->status === \App\Models\Logistics\ReturnCase::SELLER_RECEIVED ? 'sh-badge-success' : ($case->status === \App\Models\Logistics\ReturnCase::REQUESTED ? 'sh-badge-warning' : 'sh-badge-info') }}">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></td>
                    <td class="ops-row-actions"><button class="ops-action" type="button" data-ops-template="return-case-{{ $case->id }}" data-ops-title="Return case #{{ $case->id }}" data-ops-hint="Physical handoff and seller return status">Manage</button></td>
                </tr>
            @empty<tr><td class="ops-empty" colspan="6">No return cases match the filters. Failed deliveries can be initiated from Parcel Operations.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }} of {{ $cases->total() }} cases</div>
        @if($cases->hasPages())<div class="border-t p-4">{{ $cases->links() }}</div>@endif
    </section>
    @foreach($cases as $case)
    <template id="return-case-{{ $case->id }}">
        <div class="ops-modal-meta"><p><strong>Order:</strong> #{{ $case->order_id }}</p><p><strong>Tracking:</strong> {{ $case->delivery?->tracking_code ?: '—' }}</p><p><strong>Seller:</strong> {{ $case->order?->seller?->business_name ?: '—' }}</p><p><strong>Branch:</strong> {{ $case->branch?->name ?: 'Not received' }}</p><p><strong>Return rider:</strong> {{ $case->returnRider?->name ?: 'Not assigned' }}</p><p><strong>Refund:</strong> {{ ucwords(str_replace('_',' ',$case->refund_status ?: 'Pending / none')) }}</p></div>
        <div class="ops-modal-section"><h3>Return reason</h3><p class="text-xs text-navy/75 whitespace-pre-wrap">{{ $case->reason }}</p></div>
        @if($case->status === \App\Models\Logistics\ReturnCase::REQUESTED)
            <form class="space-y-3" method="POST" action="{{ route('logistics.returns.receive',$case) }}" data-confirm-title="Confirm physical branch return receipt?" data-confirm-message="The parcel must be in hand; verify branch and tracking code." data-confirm-button="Confirm receipt">@csrf
                <label class="sh-field"><span class="sh-label">Receiving branch *</span><select class="sh-select" name="branch_id" required><option value="">Choose branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($branch->id === $case->delivery?->destination_sorting_center_id)>{{ $branch->name }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Scan or enter tracking code *</span><input class="sh-input" name="tracking_code" required maxlength="60" autocomplete="off" placeholder="{{ $case->delivery?->tracking_code }}"></label>
                <div class="ops-dialog-actions"><button class="sh-btn">Confirm branch receipt</button></div>
            </form>
        @elseif($case->status === \App\Models\Logistics\ReturnCase::AT_BRANCH)
            <form class="space-y-3" method="POST" action="{{ route('logistics.returns.assign',$case) }}" data-confirm-title="Assign return rider?" data-confirm-message="This rider must collect the parcel and submit return handover proof." data-confirm-button="Assign rider">@csrf
                <label class="sh-field"><span class="sh-label">Eligible rider at {{ $case->branch?->name ?: 'branch' }} *</span><select class="sh-select" name="rider_id" required><option value="">Choose rider</option>@foreach($riders->where('sorting_center_id',$case->return_branch_id) as $rider)<option value="{{ $rider->id }}">{{ $rider->name }}</option>@endforeach</select></label>
                <div class="ops-dialog-actions"><button class="sh-btn">Assign return rider</button></div>
            </form>
        @elseif($case->status === \App\Models\Logistics\ReturnCase::ASSIGNED)
            <p class="ops-inline-alert">Assigned to {{ $case->returnRider?->name ?? 'Rider' }}. The rider must confirm pickup in the mobile app.</p>
        @elseif($case->status === \App\Models\Logistics\ReturnCase::IN_TRANSIT)
            <p class="ops-inline-alert">Return rider has collected the parcel. Waiting for seller handover proof.</p>
        @elseif($case->status === \App\Models\Logistics\ReturnCase::AWAITING_SELLER)
            <p class="ops-inline-alert">Proof submitted. Seller must confirm physical receipt from Seller Web.</p>
        @else
            <p class="ops-inline-alert">Seller acknowledged return at {{ $case->seller_received_at?->format('M j, Y g:i A') ?? '—' }}. Refund status: {{ ucwords(str_replace('_',' ',$case->refund_status ?: 'Pending')) }}.</p>
        @endif
        <p class="text-xs text-amber-700">Stock is not automatically restored. Returned orders do not automatically generate Seller payouts.</p>
    </template>
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">Return details</h2><p class="ops-panel-note" id="opsRecordHint"></p></div><button class="ops-action" type="button" data-ops-close aria-label="Close">✕</button></div><div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-ghost" type="button" data-ops-close>Close</button></div></dialog>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261104-bh"><link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261104-finance">@endpush
@push('scripts')@endpush
