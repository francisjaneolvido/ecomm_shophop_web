@extends('branch.layout.app')
@section('title','Incoming Transfers')
@section('content')
@php
    $filters=['awaiting'=>'Awaiting receipt','preparing'=>'In transit','received'=>'Received','all'=>'All transfers'];
@endphp
<div class="space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">{{ $center->name }}</p><h1 class="title">Incoming Transfers</h1><p class="description">Manifest and parcel receiving queue. A shipment may only be received after validated physical arrival.</p></div>
        <div class="flex gap-2"><button type="button" class="sh-btn-soft" data-ops-open="exceptionReceiveModal">Exception receive</button><a class="sh-btn" href="{{ route('branch.scanner') }}">Scan Manifest QR</a></div></header>
    @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([['Awaiting receipt',$counts['awaiting'],'Eligible for receiving'],['In transit',$counts['preparing'],'Awaiting physical arrival'],['Received today',$counts['received_today'],'Completed scans today'],['All incoming',$counts['all'],'All recorded transfers']] as [$label,$value,$note])
            <div class="sh-stat"><div class="sh-stat-label">{{ $label }}</div><div class="sh-stat-value">{{ number_format($value) }}</div><div class="sh-stat-note">{{ $note }}</div></div>
        @endforeach
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Inbound transfer directory</h2><p class="ops-panel-note">View the manifest and current transport status, then scan after arrival.</p></div>
        <form method="GET" class="ops-toolbar"><input class="sh-input" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Tracking or manifest" aria-label="Search inbound transfers">
            <select class="sh-select" name="status" aria-label="Transfer status" style="width:auto">@foreach($filters as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach</select>
            <button class="sh-btn" type="submit">Filter</button><a class="sh-btn-ghost" href="{{ route('branch.transfers.index') }}">Clear</a></form></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Tracking / Buyer</th><th>Origin</th><th>Manifest</th><th>Transport</th><th>Status</th><th>Updated</th><th>Action</th></tr></thead><tbody>
        @forelse($transfers as $transfer)
            @php
                $trip=$transfer->linehaulTrip;
                $cargo=$transfer->cargoTransfer;
                $arrived=($cargo && in_array($cargo->status,['arrived','completed'],true)) || ($trip && in_array($trip->status,['arrived','completed'],true)) || (! $trip && ! $cargo && (! $transfer->transit_rider_id || $transfer->departed_at));
                $statusLabel=$transfer->status===\App\Models\Logistics\ParcelTransfer::RECEIVED?'Received':($arrived?'Awaiting receipt':'In transit');
                $badge=$transfer->status===\App\Models\Logistics\ParcelTransfer::RECEIVED?'sh-badge-success':($arrived?'sh-badge-info':'sh-badge-warning');
            @endphp
            <tr><td><strong>{{ $transfer->delivery?->tracking_code ?? '—' }}</strong><p class="ops-row-sub">{{ $transfer->delivery?->order?->delivery_name ?? '—' }}</p></td>
                <td>{{ $transfer->fromCenter?->name ?? 'Unknown center' }}</td>
                <td>{{ $transfer->manifest?->code ?? 'Individual transfer' }}</td>
                <td>{{ $cargo ? strtoupper((string) $cargo->mode).' Cargo' : ($trip ? 'Road · '.($trip->vehicle?->plate_number ?? 'Trip') : 'Legacy transport') }}<div class="ops-row-sub">{{ $trip?->driver?->name ?? '' }}</div></td>
                <td><span class="sh-badge {{ $badge }}">{{ $statusLabel }}</span></td>
                <td>{{ $transfer->received_at?->format('M d, g:i A') ?? $transfer->dispatched_at?->format('M d, g:i A') ?? '—' }}</td>
                <td class="ops-row-actions"><button class="ops-action" type="button" data-ops-template="transferInfo{{ $transfer->id }}">Details</button></td></tr>
            <template id="transferInfo{{ $transfer->id }}"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title">{{ $transfer->delivery?->tracking_code }}</h2><p class="ops-panel-note">Transfer #{{ $transfer->id }} · {{ $transfer->manifest?->code ?? 'No manifest' }}</p></div><button type="button" class="ops-action" data-ops-close>✕</button></div>
                <div class="ops-dialog-body space-y-4"><div class="ops-form-grid"><div><div class="sh-label">Origin</div>{{ $transfer->fromCenter?->name ?? '—' }}</div><div><div class="sh-label">Destination</div>{{ $center->name }}</div><div><div class="sh-label">Receiver</div>{{ $transfer->delivery?->order?->delivery_name ?? '—' }}</div><div><div class="sh-label">Transport</div>{{ $cargo ? 'Cargo: '.$cargo->status : ($trip ? 'Linehaul: '.$trip->status : 'Legacy') }}</div></div>
                    <div class="ops-inline-alert">Normal batches: receive using the Manifest QR in the scanner. Individual parcel receipt is for exceptions and is revalidated server-side.</div></div>
                <div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-ops-close>Close</button>@if($arrived && $transfer->status===\App\Models\Logistics\ParcelTransfer::IN_TRANSIT)<button type="button" class="sh-btn" data-ops-open="exceptionReceiveModal">Exception receive</button>@endif</div></template>
        @empty<tr><td colspan="7" class="ops-empty">No transfers match this filter.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">Showing {{ $transfers->firstItem() ?? 0 }}–{{ $transfers->lastItem() ?? 0 }} of {{ $transfers->total() }} transfers</div>
        @if($transfers->hasPages())<div class="p-4 border-t">{{ $transfers->links() }}</div>@endif
    </section>
</div>
<dialog id="exceptionReceiveModal" class="ops-dialog"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title">Exception parcel receiving</h2><p class="ops-panel-note">Use only for physically present exceptions or reconciliation</p></div><button type="button" data-ops-close class="ops-action">✕</button></div>
    <div class="ops-dialog-body"><form method="POST" action="{{ route('branch.receive') }}" data-confirm-title="Confirm physical receipt?" data-confirm-message="Confirm the parcel is physically present and the tracking code matches." data-confirm-button="Receive parcel" class="space-y-3">@csrf
        <label class="sh-field"><span class="sh-label">Parcel tracking code *</span><input class="sh-input" name="tracking_code" id="branch-exception-code" value="{{ old('tracking_code') }}" autocomplete="off" maxlength="60" required></label>
        @include('partials.logistics-qr-scanner',['inputId'=>'branch-exception-code'])
        <div class="flex justify-end gap-2"><button type="button" class="sh-btn-ghost" data-ops-close>Cancel</button><button type="submit" class="sh-btn">Confirm receipt</button></div>
    </form></div></dialog>
<dialog id="opsRecordDialog" class="ops-dialog"><div data-ops-record-content></div></dialog>
@endsection
@push('scripts')
<script>
// Close the detail modal when launching the separate exception-receive modal.
document.querySelectorAll('[data-ops-template^="transferInfo"]').forEach(btn=>btn.addEventListener('click',()=>{}));
document.getElementById('opsRecordDialog')?.addEventListener('click',event=>{if(event.target.closest('[data-ops-open="exceptionReceiveModal"]'))document.getElementById('opsRecordDialog').close()});
@if($errors->any())document.getElementById('exceptionReceiveModal')?.showModal();@endif
</script>
@endpush
