@extends('logistics.layouts.console')

@section('title', 'Delivery Reports — ShopHop Logistics')

@section('content')
<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Analytics · Operational reports</p>
            <h1 class="title">Delivery Reports</h1>
            <p class="description">Review shipment volume by operational stage and inspect deliveries assigned to your logistics company within a selected date range.</p>
        </div>
    </header>

    <form method="GET" action="{{ route('logistics.reports.index') }}" class="sh-surface sh-card-pad">
        <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
            <label class="sh-field">
                <span class="sh-label">From</span>
                <input id="report-from" type="date" name="from" value="{{ $data['from'] ?? '' }}" class="sh-input">
            </label>
            <label class="sh-field">
                <span class="sh-label">To</span>
                <input id="report-to" type="date" name="to" value="{{ $data['to'] ?? '' }}" class="sh-input">
            </label>
            <button class="sh-btn">Filter report</button>
        </div>
    </form>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            \App\Models\Logistics\Delivery::PICKUP_ASSIGNED => 'Pickup assigned',
            \App\Models\Logistics\Delivery::PICKED_UP => 'Picked up',
            \App\Models\Logistics\Delivery::AT_SORTING_CENTER => 'At sorting center',
            \App\Models\Logistics\Delivery::SORTED => 'Sorted',
            \App\Models\Logistics\Delivery::DELIVERY_ASSIGNED => 'Delivery assigned',
            \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY => 'Out for delivery',
            \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'Delivery failed',
            \App\Models\Logistics\Delivery::DELIVERED => 'Delivered',
        ] as $key => $label)
            <div class="sh-stat">
                <p class="sh-stat-label">{{ $label }}</p>
                <p class="sh-stat-value">{{ $counts[$key] ?? 0 }}</p>
                <p class="sh-stat-note">Selected period count</p>
            </div>
        @endforeach
    </div>

    <section class="sh-surface overflow-hidden">
        <div class="border-b border-gray-border px-5 py-4">
            <h2 class="text-sm font-bold text-navy">Delivery list</h2>
            <p class="mt-1 text-xs text-navy/45">Each row reflects a persisted delivery assigned to this logistics partner.</p>
        </div>

        @if ($deliveries->isEmpty())
            <div class="sh-empty m-5">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-chart-no-axes-combined class="h-5 w-5" /></div>
                <p class="mt-3 text-sm font-semibold text-navy">No deliveries found for this range</p>
                <p class="mt-1 text-xs text-navy/45">Try adjusting the date range to view more shipment activity.</p>
            </div>
        @else
            <div class="sh-table-wrap">
                <table class="sh-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Rider</th>
                            <th>Status</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveries as $delivery)
                            @php
                                $badge = match($delivery->status) {
                                    \App\Models\Logistics\Delivery::DELIVERED => 'sh-badge-success',
                                    \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'sh-badge-danger',
                                    \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY => 'sh-badge-info',
                                    default => 'sh-badge-neutral',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <p class="font-semibold text-navy">Order #{{ $delivery->order_id }}</p>
                                    <p class="mt-1 text-xs text-navy/45">{{ $delivery->tracking_code ?: 'Tracking pending' }}</p>
                                </td>
                                <td>{{ $delivery->rider?->name ?? 'Rider unavailable' }}</td>
                                <td><span class="sh-badge {{ $badge }}">{{ $delivery->statusLabel() }}</span></td>
                                <td>{{ $delivery->assigned_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="px-5 py-4 text-xs text-navy/45">PDF export, revenue analytics, SLA scoring, and live location reporting are not available in this logistics console build.</p>
    </section>
</div>
@endsection
