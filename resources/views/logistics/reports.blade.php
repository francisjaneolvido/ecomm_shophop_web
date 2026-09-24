@extends('logistics.layouts')

@section('title', 'Delivery Reports — ShopHop Logistics')

@section('content')
{{-- This operational report uses assigned-at dates and persisted stages only; PDF and performance scores are unavailable. --}}
<h1 class="text-navy text-2xl sm:text-3xl font-bold mb-2">Delivery Reports</h1>
<p class="text-navy/55 text-sm mb-5">Partner-owned delivery records by assignment date.</p>
<form method="GET" action="{{ route('logistics.reports.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
    <div><label for="report-from" class="block text-xs text-navy mb-1">From</label><input id="report-from" type="date" name="from" value="{{ $data['from'] ?? '' }}" class="rounded-xl border border-gray-border px-3 py-2"></div>
    <div><label for="report-to" class="block text-xs text-navy mb-1">To</label><input id="report-to" type="date" name="to" value="{{ $data['to'] ?? '' }}" class="rounded-xl border border-gray-border px-3 py-2"></div>
    <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach (['assigned' => 'Assigned', 'picked_up' => 'Picked up', 'in_transit' => 'In transit', 'delivered' => 'Delivered'] as $key => $label)
        <div class="rounded-xl border border-gray-border bg-white p-4"><p class="text-sm text-navy/60">{{ $label }}</p><p class="text-2xl font-bold text-navy">{{ $counts[$key] ?? 0 }}</p></div>
    @endforeach
</div>
{{-- Each row is an assigned Delivery from this partner and the selected date range. --}}
<div class="space-y-3">
    @forelse ($deliveries as $delivery)
        <div class="rounded-xl border border-gray-border bg-white p-4 text-sm text-navy">
            Order #{{ $delivery->order_id }} · {{ $delivery->rider?->name ?? 'Rider unavailable' }} · {{ str_replace('_', ' ', ucfirst($delivery->status)) }} · Assigned {{ $delivery->assigned_at?->format('M j, Y') }}
        </div>
    @empty
        <p class="text-sm text-navy/50">No persisted deliveries in this date range.</p>
    @endforelse
</div>
{{-- Unsupported analytics and exports are named explicitly instead of retaining sample charts. --}}
<p class="mt-6 text-xs text-navy/50">PDF export, revenue, SLA, and live location reporting are unavailable.</p>
@endsection
