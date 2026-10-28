@extends('logistics.layouts.console')

@section('title', 'Operations Dashboard')

@section('content')
@php
    // All numbers are sourced from the logged-in partner's persisted records.
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
    $partnerName = $partner->company_name ?: auth()->user()?->name ?: 'Logistics Partner';
    $pickupWork = $overview['pickup_requests'];
    $sortWork = $overview['inbound'] + $overview['at_center'];
    $dispatchWork = $overview['sorted'] + $overview['failed'];
    $attentionCount = $pickupWork + $sortWork + $dispatchWork + $overview['rider_reviews'];

    $keyMetrics = [
        ['label' => 'Pickup requests', 'value' => $pickupWork, 'note' => 'Ready for rider assignment', 'icon' => 'package-plus', 'tint' => 'bg-sky-50 text-sky-700', 'href' => route('logistics.deliveries.board').'#pickup-queue'],
        ['label' => 'Parcels to receive / sort', 'value' => $sortWork, 'note' => 'Picked up or at a center', 'icon' => 'scan-line', 'tint' => 'bg-amber-50 text-amber-700', 'href' => route('logistics.deliveries.board').'#active-deliveries'],
        ['label' => 'Out for delivery', 'value' => $overview['out_for_delivery'], 'note' => 'On the final delivery leg', 'icon' => 'truck', 'tint' => 'bg-teal-light text-teal-dark', 'href' => route('logistics.deliveries.board').'#active-deliveries'],
        ['label' => 'Delivered today', 'value' => $overview['today_delivered'], 'note' => 'Confirmed rider deliveries today', 'icon' => 'badge-check', 'tint' => 'bg-indigo-50 text-indigo-700', 'href' => route('logistics.reports.index')],
    ];

    $stages = [
        ['label' => 'Pickup queue', 'value' => $overview['pickup_requests'], 'description' => 'Awaiting assignment'],
        ['label' => 'Pickup in progress', 'value' => $overview['pickup_assigned'] + $overview['inbound'], 'description' => 'Assigned / collected'],
        ['label' => 'Sorting', 'value' => $overview['at_center'], 'description' => 'At a sorting center'],
        ['label' => 'Dispatch', 'value' => $overview['sorted'] + $overview['delivery_assigned'], 'description' => 'Sorted / assigned'],
        ['label' => 'Final delivery', 'value' => $overview['out_for_delivery'], 'description' => 'On the road'],
    ];

    $tasks = [
        ['label' => 'Assign pickup riders', 'note' => 'Seller parcels ready for collection', 'value' => $pickupWork, 'icon' => 'package-plus', 'tone' => 'text-sky-700 bg-sky-50', 'url' => route('logistics.deliveries.board').'#pickup-queue'],
        ['label' => 'Receive and sort parcels', 'note' => 'Scan parcels and confirm destination', 'value' => $sortWork, 'icon' => 'scan-barcode', 'tone' => 'text-amber-700 bg-amber-50', 'url' => route('logistics.deliveries.board').'#active-deliveries'],
        ['label' => 'Dispatch or retry delivery', 'note' => 'Sorted parcels and failed attempts', 'value' => $dispatchWork, 'icon' => 'route', 'tone' => 'text-teal-dark bg-teal-light', 'url' => route('logistics.deliveries.board').'#active-deliveries'],
        ['label' => 'Review rider applications', 'note' => 'Verified applicants awaiting decision', 'value' => $overview['rider_reviews'], 'icon' => 'user-round-check', 'tone' => 'text-indigo-700 bg-indigo-50', 'url' => route('logistics.riders.index').'#rider-directory'],
    ];
@endphp

<div id="logisticsOperationsDashboard" class="sh-dashboard space-y-4 lg:space-y-5">
    <header class="sh-dashboard-heading">
        <div class="min-w-0">
            <p class="sh-kicker">Logistics / Overview</p>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-navy sm:text-2xl">{{ $greeting }}, {{ $partnerName }}</h1>
            <p class="mt-1 text-xs text-navy/55">Your operation at a glance. Start with the queues that need action.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="sh-dashboard-date"><x-lucide-calendar-days class="h-4 w-4 text-teal-dark" /> {{ now()->format('M d, Y') }}</span>
            <a href="{{ route('logistics.deliveries.board') }}" class="sh-btn"><x-lucide-package-check class="h-4 w-4" /> Open delivery board</a>
        </div>
    </header>

    <div class="sh-dashboard-stats grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Key logistics metrics">
        @foreach ($keyMetrics as $metric)
            <a href="{{ $metric['href'] }}" class="sh-dashboard-stat group">
                <div class="min-w-0">
                    <p class="sh-dashboard-stat-label">{{ $metric['label'] }}</p>
                    <p class="sh-dashboard-stat-number">{{ number_format($metric['value']) }}</p>
                    <p class="sh-dashboard-stat-note">{{ $metric['note'] }}</p>
                </div>
                <span class="sh-dashboard-stat-icon {{ $metric['tint'] }}"><x-dynamic-component :component="'lucide-'.$metric['icon']" class="h-[18px] w-[18px]" /></span>
            </a>
        @endforeach
    </div>

    <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(280px,.85fr)]">
        <section class="sh-dashboard-card" aria-labelledby="logistics-priorities-title">
            <header class="sh-dashboard-card-header">
                <div class="min-w-0">
                    <p class="sh-kicker">Action center</p>
                    <h2 id="logistics-priorities-title" class="sh-title-sm mt-1">Needs your attention</h2>
                    <p class="sh-text-muted mt-1 text-xs">Open a queue to complete the next task.</p>
                </div>
                <span class="{{ $attentionCount ? 'sh-badge-warning' : 'sh-badge-success' }} sh-badge shrink-0">{{ number_format($attentionCount) }} open</span>
            </header>
            <div class="divide-y divide-gray-border/80">
                @foreach ($tasks as $task)
                    <a href="{{ $task['url'] }}" class="sh-task-link group">
                        <span class="sh-task-icon {{ $task['tone'] }}"><x-dynamic-component :component="'lucide-'.$task['icon']" class="h-[17px] w-[17px]" /></span>
                        <span class="min-w-0 flex-1"><span class="block text-xs font-semibold text-navy">{{ $task['label'] }}</span><span class="mt-0.5 block text-[11px] text-navy/50">{{ $task['note'] }}</span></span>
                        <span class="sh-task-count">{{ number_format($task['value']) }}</span>
                        <x-lucide-chevron-right class="h-4 w-4 shrink-0 text-navy/25" />
                    </a>
                @endforeach
            </div>
        </section>

        <section class="sh-dashboard-card" aria-labelledby="logistics-network-title">
            <header class="sh-dashboard-card-header">
                <div><p class="sh-kicker">Your network</p><h2 id="logistics-network-title" class="sh-title-sm mt-1">Capacity & exceptions</h2></div>
                <x-lucide-network class="h-5 w-5 text-teal-dark" />
            </header>
            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('logistics.sorting-centers.index') }}" class="sh-network-stat"><x-lucide-warehouse class="h-4 w-4 text-teal-dark" /><strong>{{ number_format($overview['centers']) }}</strong><span>Active centers</span></a>
                    <a href="{{ route('logistics.riders.index') }}" class="sh-network-stat"><x-lucide-bike class="h-4 w-4 text-teal-dark" /><strong>{{ number_format($overview['riders']) }}</strong><span>Active riders</span></a>
                </div>
                <a href="{{ route('logistics.deliveries.board') }}#active-deliveries" class="sh-exception-link"><span><span class="block text-xs font-semibold text-navy">Failed deliveries</span><span class="block text-[11px] text-navy/50">Review exceptions on the board</span></span><strong>{{ number_format($overview['failed']) }}</strong><x-lucide-arrow-up-right class="h-4 w-4 text-rose-700" /></a>
            </div>
        </section>
    </div>

    <section class="sh-dashboard-card" aria-labelledby="logistics-flow-title">
        <header class="sh-dashboard-card-header">
            <div><p class="sh-kicker">Parcel journey</p><h2 id="logistics-flow-title" class="sh-title-sm mt-1">Live shipment stages</h2></div>
            <a href="{{ route('logistics.deliveries.board') }}" class="sh-link-inline">Manage shipments <x-lucide-arrow-right class="h-4 w-4" /></a>
        </header>
        <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3 sm:p-4 xl:grid-cols-5">
            @foreach ($stages as $stage)
                <div class="sh-stage">
                    <div class="flex items-center justify-between gap-2"><span class="sh-stage-index">{{ sprintf('%02d', $loop->iteration) }}</span><strong class="text-lg leading-none text-navy">{{ number_format($stage['value']) }}</strong></div>
                    <p class="mt-2 text-[11px] font-semibold text-navy">{{ $stage['label'] }}</p>
                    <p class="mt-0.5 text-[10px] text-navy/50">{{ $stage['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-border bg-white shadow-sm" aria-labelledby="logistics-recent-title">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-border px-4 py-4 sm:px-5">
            <div>
                <h2 id="logistics-recent-title" class="text-sm font-bold text-navy">Recent shipment activity</h2>
                <p class="mt-1 text-[10px] text-navy/45">Six most recently updated shipments belonging to your company</p>
            </div>
            <a href="{{ route('logistics.deliveries.board') }}#active-deliveries" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-teal-dark hover:underline">View all <x-lucide-arrow-up-right class="h-4 w-4" /></a>
        </div>
        @if ($recentDeliveries->isEmpty())
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-package-open class="h-5 w-5" /></span>
                <p class="mt-3 text-sm font-semibold text-navy">No shipment history yet</p>
                <p class="mt-1 text-xs text-navy/45">Assigned pickup requests will appear here once a rider is assigned.</p>
                <a href="{{ route('logistics.deliveries.board') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-teal-dark hover:underline">Check pickup requests <x-lucide-arrow-right class="h-4 w-4" /></a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left">
                    <thead class="bg-gray-bg/70 text-[10px] font-semibold uppercase tracking-wide text-navy/45">
                        <tr><th class="px-5 py-3">Shipment</th><th class="px-4 py-3">Seller</th><th class="px-4 py-3">Current stage</th><th class="px-4 py-3">Last updated</th><th class="px-5 py-3 text-right">Open</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-border/70">
                        @foreach ($recentDeliveries as $delivery)
                            @php
                                $statusClass = match($delivery->status) {
                                    \App\Models\Logistics\Delivery::DELIVERED => 'bg-teal-light text-teal-dark',
                                    \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'bg-rose-50 text-rose-700',
                                    \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY => 'bg-sky-50 text-sky-700',
                                    default => 'bg-gray-bg text-navy/65',
                                };
                            @endphp
                            <tr class="transition hover:bg-gray-bg/45">
                                <td class="px-5 py-3.5"><p class="text-xs font-bold text-navy">Order #{{ $delivery->order_id }}</p><p class="mt-1 text-[10px] text-navy/45">{{ $delivery->tracking_code ?: 'Tracking pending' }}</p></td>
                                <td class="px-4 py-3.5 text-xs text-navy/65">{{ $delivery->order?->seller?->business_name ?: 'Seller unavailable' }}</td>
                                <td class="px-4 py-3.5"><span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">{{ $delivery->statusLabel() }}</span></td>
                                <td class="px-4 py-3.5 text-[11px] text-navy/55">{{ $delivery->updated_at?->format('M d, Y · h:i A') ?: 'Unavailable' }}</td>
                                <td class="px-5 py-3.5 text-right"><a href="{{ route('logistics.deliveries.board') }}#delivery-{{ $delivery->id }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-dark hover:underline">View <x-lucide-arrow-right class="h-3 w-3" /></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection