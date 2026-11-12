@extends('logistics.layouts.console')
@section('title','Delivery Reports — ShopHop Logistics')
@section('content')
<div class="sh-ops sh-finance-ops space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">Analytics · Actual delivery records</p><h1 class="title">Delivery Reports</h1><p class="description">Review shipment counts by lifecycle status and export real deliveries assigned to your logistics partner. Data is based on persisted delivery facts, not live location or SLA estimates.</p></div>
        <a class="sh-btn-soft" href="{{ route('logistics.reports.export.csv',request()->only(['from','to','q','status'])) }}">Export filtered CSV</a>
    </header>
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <section class="ops-panel">
        <form method="GET" action="{{ route('logistics.reports.index') }}" class="p-4 sm:p-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.3fr_1fr_auto_auto] items-end">
                <label class="sh-field"><span class="sh-label">From date</span><input class="sh-input" name="from" type="date" value="{{ $data['from'] ?? '' }}"></label>
                <label class="sh-field"><span class="sh-label">To date</span><input class="sh-input" name="to" type="date" value="{{ $data['to'] ?? '' }}"></label>
                <label class="sh-field"><span class="sh-label">Tracking / Order / Rider</span><input class="sh-input" name="q" value="{{ $search }}" placeholder="Search all records"></label>
                <label class="sh-field"><span class="sh-label">Status</span><select class="sh-select" name="status"><option value="">All statuses</option>@foreach($statuses as $item)<option value="{{ $item }}" @selected($selectedStatus === $item)>{{ ucwords(str_replace('_',' ',$item)) }}</option>@endforeach</select></label>
                <button type="submit" class="sh-btn">Apply filters</button><a class="sh-btn-ghost text-center" href="{{ route('logistics.reports.index') }}">Reset</a>
            </div>
        </form>
    </section>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([['Total in date range',$total,'All lifecycle statuses'],['Delivered',$counts[\App\Models\Logistics\Delivery::DELIVERED] ?? 0,'Reached buyer'],['Out for delivery',$counts[\App\Models\Logistics\Delivery::OUT_FOR_DELIVERY] ?? 0,'Last-mile stage'],['Failed attempts',$counts[\App\Models\Logistics\Delivery::DELIVERY_FAILED] ?? 0,'Needs review or retry']] as [$label,$count,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value">{{ number_format($count) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </div>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Current delivery status breakdown</h2><p class="ops-panel-note">Counts reflect the current status of all deliveries assigned within the date range, before status and text filters.</p></div><span class="sh-badge sh-badge-info">{{ number_format($total) }} total</span></div>
        <div class="grid grid-cols-2 gap-2 p-4 md:grid-cols-4">
            @foreach($statuses as $item)
                @if(($counts[$item] ?? 0) > 0)
                    <a class="ops-status-cell" href="{{ route('logistics.reports.index',array_merge(request()->only(['from','to','q']),['status'=>$item])) }}"><span>{{ ucwords(str_replace('_',' ',$item)) }}</span><strong>{{ number_format($counts[$item]) }}</strong></a>
                @endif
            @endforeach
            @if($total === 0)<p class="text-xs text-navy/60 col-span-full">No delivery statuses recorded for these dates.</p>@endif
        </div>
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Delivery directory</h2><p class="ops-panel-note">25 records per page • date refers to when the delivery was assigned.</p></div><span class="sh-badge sh-badge-neutral">{{ $deliveries->total() }} matching</span></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order / Tracking</th><th>Rider</th><th>Origin</th><th>Destination</th><th>Status</th><th>Assigned</th><th>Action</th></tr></thead><tbody>
            @forelse($deliveries as $delivery)
                @php $badge = match($delivery->status) { \App\Models\Logistics\Delivery::DELIVERED=>'sh-badge-success', \App\Models\Logistics\Delivery::DELIVERY_FAILED=>'sh-badge-danger', \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY=>'sh-badge-info', default=>'sh-badge-neutral' }; @endphp
                <tr><td><strong>#{{ $delivery->order_id }}</strong><div class="ops-row-sub">{{ $delivery->tracking_code ?: 'Tracking pending' }}</div></td><td>{{ $delivery->rider?->name ?: 'Unassigned' }}</td><td>{{ $delivery->originSortingCenter?->name ?: '—' }}</td><td>{{ $delivery->destinationSortingCenter?->name ?: 'Not assigned' }}</td><td><span class="sh-badge {{ $badge }}">{{ $delivery->statusLabel() }}</span></td><td>{{ $delivery->assigned_at?->format('M j, Y g:i A') ?: '—' }}</td><td class="ops-row-actions"><button type="button" class="ops-action" data-ops-template="report-row-{{ $delivery->id }}" data-ops-title="Delivery · Order #{{ $delivery->order_id }}" data-ops-hint="Persisted operational status and timestamps">View</button></td></tr>
            @empty<tr><td class="ops-empty" colspan="7">No deliveries match the selected filters.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} deliveries</div>
        @if($deliveries->hasPages())<div class="border-t p-4">{{ $deliveries->links() }}</div>@endif
    </section>
    <p class="text-xs text-navy/55">CSV contains actual filtered records and timestamps. PDF export, real-time location, revenue analytics, and SLA scoring are not implemented in this logistics report.</p>
    @foreach($deliveries as $delivery)
        <template id="report-row-{{ $delivery->id }}"><div class="ops-modal-meta"><p><strong>Order:</strong> #{{ $delivery->order_id }}</p><p><strong>Tracking:</strong> {{ $delivery->tracking_code ?: '—' }}</p><p><strong>Status:</strong> {{ $delivery->statusLabel() }}</p><p><strong>Rider:</strong> {{ $delivery->rider?->name ?: '—' }}</p><p><strong>Origin:</strong> {{ $delivery->originSortingCenter?->name ?: '—' }}</p><p><strong>Destination:</strong> {{ $delivery->destinationSortingCenter?->name ?: '—' }}</p><p><strong>Assigned:</strong> {{ $delivery->assigned_at?->format('M j, Y g:i A') ?: '—' }}</p><p><strong>Delivered:</strong> {{ $delivery->delivered_at?->format('M j, Y g:i A') ?: '—' }}</p></div></template>
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">Delivery report</h2><p class="ops-panel-note" id="opsRecordHint"></p></div><button class="ops-action" type="button" data-ops-close aria-label="Close">✕</button></div><div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-ghost" type="button" data-ops-close>Close</button></div></dialog>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261112-navy-pink-2"><link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261112-navy-pink-2">@endpush
@push('scripts')@endpush
