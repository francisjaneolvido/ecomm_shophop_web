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

<div id="logisticsOperationsDashboard" class="space-y-5 sm:space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-dark">ShopHop Logistics · Command center</p>
            <h1 class="text-2xl font-bold tracking-tight text-navy sm:text-[28px]">{{ $greeting }}, {{ $partnerName }}</h1>
            <p class="mt-1.5 max-w-2xl text-xs leading-relaxed text-navy/55 sm:text-sm">Monitor parcel handoffs, organize your riders, and take action on shipments that need attention.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex h-9 items-center gap-2 rounded-xl border border-gray-border bg-white px-3 text-[11px] font-medium text-navy/60">
                <x-lucide-calendar-days class="h-4 w-4 text-teal-dark" /> {{ now()->format('M d, Y') }}
            </span>
            <a href="{{ route('logistics.deliveries.board') }}" class="inline-flex h-9 items-center gap-2 rounded-xl bg-navy px-4 text-[11px] font-bold text-white transition hover:bg-teal-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-dark">
                Open delivery board <x-lucide-arrow-up-right class="h-3.5 w-3.5" />
            </a>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($keyMetrics as $metric)
            <a href="{{ $metric['href'] }}" class="group rounded-2xl border border-gray-border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal/35 hover:shadow-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $metric['tint'] }}">
                        <x-dynamic-component :component="'lucide-'.$metric['icon']" class="h-[18px] w-[18px]" />
                    </span>
                    <x-lucide-arrow-up-right class="h-4 w-4 text-navy/20 transition group-hover:text-teal-dark" />
                </div>
                <p class="mt-3 text-[11px] font-medium text-navy/55">{{ $metric['label'] }}</p>
                <p class="mt-1 text-[30px] font-bold leading-none tracking-tight text-navy">{{ number_format($metric['value']) }}</p>
                <p class="mt-2 text-[10px] text-navy/40">{{ $metric['note'] }}</p>
            </a>
        @endforeach
    </div>

    <section class="rounded-2xl border border-gray-border bg-white p-4 shadow-sm sm:p-5" aria-labelledby="logistics-flow-title">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="logistics-flow-title" class="text-sm font-bold text-navy">Parcel journey overview</h2>
                <p class="mt-1 text-[11px] text-navy/45">Current shipments across each stage · counts are from live records</p>
            </div>
            <a href="{{ route('logistics.deliveries.board') }}" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-teal-dark hover:underline">Manage shipments <x-lucide-arrow-right class="h-3.5 w-3.5" /></a>
        </div>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($stages as $stage)
                <div class="relative flex min-w-0 gap-3 rounded-xl border border-gray-border/80 bg-gray-bg/50 px-3 py-3 lg:block">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-light text-[11px] font-bold text-teal-dark">{{ sprintf('%02d', $loop->iteration) }}</div>
                    <div class="min-w-0 lg:mt-3">
                        <p class="text-[11px] font-semibold text-navy">{{ $stage['label'] }}</p>
                        <p class="mt-0.5 text-[10px] text-navy/45">{{ $stage['description'] }}</p>
                        <p class="mt-2 text-xl font-bold text-navy">{{ number_format($stage['value']) }}</p>
                    </div>
                    @unless ($loop->last)
                        <x-lucide-arrow-right class="absolute -right-3 top-1/2 z-10 hidden h-4 w-4 -translate-y-1/2 rounded-full bg-white text-teal-dark lg:block" />
                    @endunless
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-[10px] text-navy/40">Completed and failed deliveries are shown separately in the activity and action sections below.</p>
    </section>

    <div class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="overflow-hidden rounded-2xl border border-gray-border bg-white shadow-sm" aria-labelledby="logistics-priorities-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-border px-4 py-4 sm:px-5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><x-lucide-list-todo class="h-4 w-4" /></span>
                    <div>
                        <h2 id="logistics-priorities-title" class="text-sm font-bold text-navy">Needs your attention</h2>
                        <p class="mt-0.5 text-[10px] text-navy/45">Actionable queues across your logistics network</p>
                    </div>
                </div>
                <span class="rounded-full {{ $attentionCount ? 'bg-amber-50 text-amber-700' : 'bg-teal-light text-teal-dark' }} px-3 py-1 text-[10px] font-bold">{{ number_format($attentionCount) }} open items</span>
            </div>
            <div class="divide-y divide-gray-border/75">
                @foreach ($tasks as $task)
                    <a href="{{ $task['url'] }}" class="group flex items-center gap-3 px-4 py-3.5 transition hover:bg-gray-bg/60 sm:px-5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $task['tone'] }}"><x-dynamic-component :component="'lucide-'.$task['icon']" class="h-4 w-4" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-navy group-hover:text-teal-dark">{{ $task['label'] }}</p>
                            <p class="mt-0.5 text-[10px] text-navy/45">{{ $task['note'] }}</p>
                        </div>
                        <span class="rounded-lg bg-gray-bg px-2.5 py-1 text-xs font-bold text-navy">{{ $task['value'] }}</span>
                        <x-lucide-chevron-right class="h-4 w-4 shrink-0 text-navy/25" />
                    </a>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-border bg-white p-4 shadow-sm sm:p-5" aria-labelledby="logistics-network-title">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="logistics-network-title" class="text-sm font-bold text-navy">Network overview</h2>
                    <p class="mt-1 text-[11px] text-navy/45">Your approved operations capacity</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-light text-teal-dark"><x-lucide-network class="h-4 w-4" /></span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('logistics.sorting-centers.index') }}" class="rounded-xl border border-gray-border bg-gray-bg/40 p-4 transition hover:border-teal/30 hover:bg-teal-light/30">
                    <x-lucide-warehouse class="h-4 w-4 text-teal-dark" />
                    <p class="mt-3 text-2xl font-bold text-navy">{{ $overview['centers'] }}</p>
                    <p class="mt-1 text-[11px] font-semibold text-navy/60">Active centers</p>
                </a>
                <a href="{{ route('logistics.riders.index') }}" class="rounded-xl border border-gray-border bg-gray-bg/40 p-4 transition hover:border-teal/30 hover:bg-teal-light/30">
                    <x-lucide-bike class="h-4 w-4 text-teal-dark" />
                    <p class="mt-3 text-2xl font-bold text-navy">{{ $overview['riders'] }}</p>
                    <p class="mt-1 text-[11px] font-semibold text-navy/60">Active riders</p>
                </a>
            </div>
            <div class="mt-4 rounded-xl border border-gray-border/80 p-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-navy">Delivery exceptions</p>
                        <p class="mt-0.5 text-[10px] text-navy/45">Failed attempts requiring review</p>
                    </div>
                    <span class="rounded-lg bg-rose-50 px-2.5 py-1 text-sm font-bold text-rose-700">{{ $overview['failed'] }}</span>
                </div>
                <a href="{{ route('logistics.deliveries.board') }}#active-deliveries" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-teal-dark hover:underline">Review delivery board <x-lucide-arrow-right class="h-3.5 w-3.5" /></a>
            </div>
        </section>
    </div>

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
