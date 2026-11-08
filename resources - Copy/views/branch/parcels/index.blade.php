@extends('branch.layout.app')
@section('title', 'Parcel Tracking')
@section('content')
@php
    $filters = [
        'all' => 'All parcels', 'at_branch' => 'At branch', 'assigned' => 'Assigned',
        'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered',
        'failed' => 'Failed delivery', 'returns' => 'Returns',
    ];
@endphp
<div class="space-y-5">
    <header class="sh-page-header">
        <div><p class="eyebrow">{{ $center->name }}</p><h1 class="title">Parcel Tracking</h1>
            <p class="description">Live parcel records for your branch. Assign riders through Dispatch; physical handoff requires validated QR scanning.</p></div>
        <div class="flex flex-wrap gap-2"><a class="sh-btn-soft" href="{{ route('branch.dispatch.index') }}">Open Dispatch</a><a class="sh-btn" href="{{ route('branch.scanner') }}">Scan parcel</a></div>
    </header>
    @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach(['all'=>'Total parcels','at_branch'=>'At branch','out_for_delivery'=>'Out for delivery','failed'=>'Needs attention'] as $key=>$label)
            <div class="sh-stat"><div class="sh-stat-label">{{ $label }}</div><div class="sh-stat-value">{{ number_format($counts[$key] ?? 0) }}</div><div class="sh-stat-note">Branch-scoped live records</div></div>
        @endforeach
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Parcel directory</h2><p class="ops-panel-note">{{ $parcels->total() }} matching records · 25 per page</p></div>
            <form method="GET" class="ops-toolbar">
                <input class="sh-input" type="search" name="q" value="{{ $search }}" maxlength="120" placeholder="Tracking or receiver" aria-label="Search parcels">
                <select class="sh-select" style="width:auto" name="status" aria-label="Filter status">@foreach($filters as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>@endforeach</select>
                <button class="sh-btn" type="submit">Filter</button><a class="sh-btn-ghost" href="{{ route('branch.parcels.index') }}">Clear</a>
            </form>
        </div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Parcel / Order</th><th>Buyer / Destination</th><th>Rider</th><th>Amount</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
            @forelse($parcels as $d)
                @php
                    $o=$d->order;
                    $statusClass=match($d->status){\App\Models\Logistics\Delivery::DELIVERED=>'sh-badge-success',\App\Models\Logistics\Delivery::DELIVERY_FAILED=>'sh-badge-danger',\App\Models\Logistics\Delivery::OUT_FOR_DELIVERY=>'sh-badge-info',default=>'sh-badge-neutral'};
                @endphp
                <tr><td><strong>{{ $d->tracking_code }}</strong><div class="ops-row-sub">Order #{{ $d->order_id }}</div></td>
                    <td>{{ $o?->delivery_name ?? '—' }}<div class="ops-row-sub">{{ collect([$o?->delivery_barangay_name,$o?->delivery_municipality_name])->filter()->implode(', ') ?: ($o?->delivery_address ?? '—') }}</div></td>
                    <td>{{ $d->deliveryRider?->name ?? 'Not assigned' }}</td>
                    <td>{{ $o?->payment_method === 'cod' ? '₱'.number_format((float)$o->total_amount,2) : 'Prepaid' }}</td>
                    <td><span class="sh-badge {{ $statusClass }}">{{ $d->statusLabel() }}</span></td>
                    <td>{{ $d->updated_at?->format('M d, Y g:i A') ?? '—' }}</td>
                    <td class="ops-row-actions">
                        @if(in_array($d->status,[\App\Models\Logistics\Delivery::SORTED,\App\Models\Logistics\Delivery::DELIVERY_FAILED],true) && (int)$d->destination_sorting_center_id===(int)$center->id)
                            <a class="ops-action" href="{{ route('branch.dispatch.index', ['stage'=>'deliveries']) }}#deliveries">Assign in Dispatch</a>
                        @else
                            <a class="ops-action" href="{{ route('branch.scanner') }}">Open Scanner</a>
                        @endif
                    </td>
                </tr>
            @empty<tr><td colspan="7" class="ops-empty">No parcels match this filter. Change the status or search criteria.</td></tr>@endforelse
        </tbody></table></div>
        <div class="ops-count">Showing {{ $parcels->firstItem() ?? 0 }}–{{ $parcels->lastItem() ?? 0 }} of {{ $parcels->total() }} records</div>
        @if($parcels->hasPages())<div class="p-4 border-t border-gray-border">{{ $parcels->links() }}</div>@endif
    </section>
</div>
@endsection
