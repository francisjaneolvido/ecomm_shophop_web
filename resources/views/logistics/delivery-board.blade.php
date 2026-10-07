@extends('logistics.layouts')

@section('title', 'Sorting & Deliveries — ShopHop Logistics')

@section('content')
<div class="mb-6">
    <h1 class="text-navy text-2xl sm:text-3xl font-bold">Sorting & Deliveries</h1>
    <p class="text-navy/55 text-sm mt-1">Pickup → sorting center scan → destination sorting → delivery assignment → delivery result.</p>
</div>

@if (session('status')) <p role="status" class="mb-4 rounded-xl bg-teal-light p-3 text-teal-dark">{{ session('status') }}</p> @endif
@if ($errors->any()) <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <section class="rounded-2xl bg-gray-bg p-4">
        <h2 class="text-navy font-bold mb-3">Pickup requests <span class="text-navy/45">({{ $ready->count() }})</span></h2>
        <div class="space-y-3">
            @forelse ($ready as $order)
                <article class="rounded-xl border border-gray-border bg-white p-4">
                    <p class="text-navy font-semibold">Order #{{ $order->id }} · {{ $order->seller?->business_name ?? 'Seller unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">{{ $order->delivery_name ?? 'Buyer unavailable' }} · {{ $order->delivery_address ?? 'Destination unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">{{ $order->items->sum('quantity') }} items · {{ strtoupper($order->payment_method) }} · ₱{{ number_format($order->total_amount, 2) }}</p>
                    <p class="text-xs text-navy/60 mt-1"><strong>Tracking:</strong> {{ $order->tracking_code ?? 'Pending' }} · <strong>Requested origin:</strong> {{ $order->pickupRequest?->originSortingCenter?->name ?? 'Main Sorting Center' }}</p>
                    @php
                        $pickupRiders = $order->pickupRequest?->origin_sorting_center_id
                            ? $riders->where('sorting_center_id', $order->pickupRequest->origin_sorting_center_id)
                            : $riders;
                    @endphp
                    @if ($pickupRiders->isNotEmpty())
                        <form method="POST" action="{{ route('logistics.deliveries.assign', $order) }}" class="mt-3 flex flex-wrap gap-2 items-center">
                            @csrf
                            <label for="pickup-rider-{{ $order->id }}" class="sr-only">Pickup Rider</label>
                            <select id="pickup-rider-{{ $order->id }}" name="rider_id" required class="rounded-full border border-gray-border px-3 py-2 text-sm">
                                <option value="">Choose pickup Rider</option>
                                @foreach ($pickupRiders as $rider)
                                    <option value="{{ $rider->id }}">{{ $rider->name }} · {{ $rider->sortingCenter?->name ?? 'No center' }} · {{ $rider->vehicle_type }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-teal">Assign Pickup</button>
                        </form>
                    @else
                        <p class="mt-3 text-sm text-amber-700">No active Rider is assigned to the requested origin Sorting Center.</p>
                    @endif
                </article>
            @empty
                <p class="text-sm text-navy/50">No Seller pickup requests assigned to your Logistics company.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl bg-gray-bg p-4">
        <h2 class="text-navy font-bold mb-3">Parcel operations <span class="text-navy/45">({{ $deliveries->count() }})</span></h2>
        <div class="space-y-3">
            @forelse ($deliveries as $delivery)
                <article class="rounded-xl border border-gray-border bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-navy font-semibold">{{ $delivery->tracking_code ?? 'Tracking pending' }} · Order #{{ $delivery->order_id }}</p>
                            <p class="text-xs text-navy/60 mt-1">{{ $delivery->order?->seller?->business_name ?? 'Seller unavailable' }}</p>
                        </div>
                        <span class="rounded-full bg-gray-bg px-3 py-1 text-xs font-semibold text-navy">{{ $delivery->statusLabel() }}</span>
                    </div>

                    <p class="text-xs text-navy/60 mt-2">Destination: {{ $delivery->order?->delivery_address ?? 'Unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">Pickup Rider: {{ $delivery->pickupRider?->name ?? ($delivery->status === \App\Models\Logistics\Delivery::PICKUP_ASSIGNED || $delivery->status === \App\Models\Logistics\Delivery::PICKUP_ACCEPTED ? ($delivery->rider?->name ?? 'Pending') : 'Pending') }}</p>
                    <p class="text-xs text-navy/60 mt-1">Delivery area: {{ $delivery->destinationArea?->area_name ?? 'Not sorted yet' }}</p>
                    <p class="text-xs text-navy/60 mt-1">Delivery Rider: {{ $delivery->deliveryRider?->name ?? 'Not assigned yet' }}</p>
                    <div class="mt-2 grid gap-1 rounded-xl bg-gray-bg px-3 py-2 text-[11px] text-navy/60 sm:grid-cols-3">
                        <p><strong>Origin center:</strong> {{ $delivery->originSortingCenter?->name ?? 'Pending' }}</p>
                        <p><strong>Current center:</strong> {{ $delivery->currentSortingCenter?->name ?? 'In pickup stage' }}</p>
                        <p><strong>Destination center:</strong> {{ $delivery->destinationSortingCenter?->name ?? 'Not determined' }}</p>
                    </div>

                    @if ($delivery->status === \App\Models\Logistics\Delivery::PICKED_UP)
                        <form method="POST" action="{{ route('logistics.deliveries.receive', $delivery) }}" class="mt-3 flex flex-wrap gap-2 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-navy mb-1" for="scan-{{ $delivery->id }}">Scan / enter tracking code</label>
                                <input id="scan-{{ $delivery->id }}" name="tracking_code" required autocomplete="off" class="rounded-xl border border-gray-border px-3 py-2 text-sm" placeholder="{{ $delivery->tracking_code }}">
                            </div>
                            <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">Receive Parcel</button>
                        </form>
                    @elseif ($delivery->status === \App\Models\Logistics\Delivery::AT_SORTING_CENTER)
                        <form method="POST" action="{{ route('logistics.deliveries.sort', $delivery) }}" class="mt-3 flex flex-wrap gap-2 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-navy mb-1" for="area-{{ $delivery->id }}">Destination area</label>
                                <select id="area-{{ $delivery->id }}" name="destination_area_id" required class="rounded-xl border border-gray-border px-3 py-2 text-sm">
                                    <option value="">Choose area</option>
                                    @foreach ($coverageAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">Confirm Sort</button>
                        </form>
                    @elseif (in_array($delivery->status, [\App\Models\Logistics\Delivery::SORTED, \App\Models\Logistics\Delivery::DELIVERY_FAILED], true))
                        @php
                            $activeTransfer = $delivery->transfers->firstWhere('status', \App\Models\Logistics\ParcelTransfer::IN_TRANSIT);
                            $needsCenterTransfer = $delivery->status === \App\Models\Logistics\Delivery::SORTED
                                && $delivery->current_sorting_center_id
                                && $delivery->destination_sorting_center_id
                                && (int) $delivery->current_sorting_center_id !== (int) $delivery->destination_sorting_center_id;
                            $areaRiders = $riders
                                ->where('coverage_area_id', $delivery->destination_area_id)
                                ->when($delivery->destination_sorting_center_id, fn ($items) => $items->where('sorting_center_id', $delivery->destination_sorting_center_id));
                        @endphp

                        @if ($needsCenterTransfer)
                            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                                <p class="text-xs font-bold text-amber-900">Branch transfer required</p>
                                <p class="mt-1 text-xs text-amber-800">
                                    {{ $delivery->currentSortingCenter?->name }} → {{ $delivery->destinationSortingCenter?->name }}
                                </p>

                                @if ($activeTransfer)
                                    <p class="mt-2 text-xs text-amber-800">Parcel is in transit. Scan the tracking code when the destination branch receives it.</p>
                                    <form method="POST" action="{{ route('logistics.transfers.receive', $activeTransfer) }}" class="mt-2 flex flex-wrap gap-2 items-end">
                                        @csrf
                                        <div>
                                            <label for="transfer-scan-{{ $activeTransfer->id }}" class="block text-[11px] font-semibold text-navy mb-1">Tracking code</label>
                                            <input id="transfer-scan-{{ $activeTransfer->id }}" name="tracking_code" required autocomplete="off" placeholder="{{ $delivery->tracking_code }}" class="rounded-xl border border-gray-border bg-white px-3 py-2 text-sm">
                                        </div>
                                        <button class="rounded-full bg-teal-dark px-4 py-2 text-xs font-semibold text-white">Receive at destination center</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('logistics.deliveries.transfer', $delivery) }}" class="mt-2 flex flex-wrap items-end gap-2">
                                        @csrf
                                        <div>
                                            <label for="next-center-{{ $delivery->id }}" class="block text-[11px] font-semibold text-navy mb-1">Next Sorting Center</label>
                                            <select id="next-center-{{ $delivery->id }}" name="to_sorting_center_id" required class="rounded-xl border border-gray-border bg-white px-3 py-2 text-xs">
                                                <option value="">Choose next center</option>
                                                @foreach ($sortingCenters->where('id', '!=', $delivery->current_sorting_center_id) as $center)
                                                    <option value="{{ $center->id }}" @selected((int) $center->id === (int) $delivery->destination_sorting_center_id)>
                                                        {{ $center->name }}{{ (int) $center->id === (int) $delivery->destination_sorting_center_id ? ' · Destination' : ' · Transfer hub' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="rounded-full bg-navy px-4 py-2 text-xs font-semibold text-white hover:bg-teal-dark">Dispatch center transfer</button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('logistics.deliveries.assign-delivery', $delivery) }}" class="mt-3 flex flex-wrap gap-2 items-end">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-navy mb-1" for="delivery-rider-{{ $delivery->id }}">Delivery Rider for {{ $delivery->destinationArea?->area_name }}</label>
                                    <select id="delivery-rider-{{ $delivery->id }}" name="rider_id" required class="rounded-xl border border-gray-border px-3 py-2 text-sm">
                                        <option value="">Choose branch Rider</option>
                                        @foreach ($areaRiders as $rider)
                                            <option value="{{ $rider->id }}">{{ $rider->name }} · {{ $rider->sortingCenter?->name }} · {{ $rider->vehicle_type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Reschedule Delivery' : 'Assign Delivery Rider' }}</button>
                            </form>
                            @if ($areaRiders->isEmpty())
                                <p class="mt-2 text-xs text-amber-700">No active Rider is assigned to this area at the destination Sorting Center.</p>
                            @endif
                        @endif

                        @if ($delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED)
                            <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3">
                                <p class="text-xs font-bold text-red-800">Delivery failure reported</p>
                                <p class="mt-1 text-xs leading-relaxed text-red-700">
                                    {{ $delivery->failure_reason }}
                                </p>
                                <p class="mt-2 text-[11px] text-red-600">
                                    ShopHop does not process parcel returns in-system. Keep the report for records, or assign another Rider above for another delivery attempt.
                                </p>
                            </div>
                        @endif

                        @if ($delivery->failureReports->isNotEmpty())
                            <div class="mt-3 rounded-xl border border-gray-border bg-gray-bg/60 p-3">
                                <p class="text-xs font-bold text-navy">Failed delivery reports</p>
                                <div class="mt-2 space-y-2">
                                    @foreach ($delivery->failureReports as $report)
                                        <div class="rounded-lg bg-white px-3 py-2 text-xs text-navy/65">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <span class="font-semibold text-navy">Attempt {{ $report->attempt_no }} · {{ $report->rider?->name ?? 'Rider' }}</span>
                                                <span class="text-[10px] text-navy/35">{{ $report->reported_at?->format('M j, Y g:i A') }}</span>
                                            </div>
                                            <p class="mt-1 leading-relaxed">{{ $report->reason }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <ol class="mt-4 border-t border-gray-border pt-3 text-xs text-navy/55 space-y-1">
                        <li>Pickup assigned: {{ $delivery->assigned_at?->format('M j, Y g:i A') ?? '—' }}</li>
                        @if ($delivery->pickup_accepted_at) <li>Pickup accepted: {{ $delivery->pickup_accepted_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->picked_up_at) <li>Picked up: {{ $delivery->picked_up_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->at_sorting_center_at) <li>Sorting center scan: {{ $delivery->at_sorting_center_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->sorted_at) <li>Sorted: {{ $delivery->sorted_at->format('M j, Y g:i A') }}</li> @endif
                        @foreach ($delivery->transfers as $transfer)
                            <li>Center transfer: {{ $transfer->fromCenter?->name }} → {{ $transfer->toCenter?->name }} · {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}{{ $transfer->dispatched_at ? ' · '.$transfer->dispatched_at->format('M j, Y g:i A') : '' }}</li>
                            @if ($transfer->received_at) <li>Received at {{ $transfer->toCenter?->name }}: {{ $transfer->received_at->format('M j, Y g:i A') }}</li> @endif
                        @endforeach
                        @if ($delivery->delivery_assigned_at) <li>Delivery Rider assigned: {{ $delivery->delivery_assigned_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->out_for_delivery_at) <li>Out for delivery: {{ $delivery->out_for_delivery_at->format('M j, Y g:i A') }}</li> @endif
                        @foreach ($delivery->failureReports as $report)
                            <li>Delivery attempt {{ $report->attempt_no }} failed: {{ $report->reported_at?->format('M j, Y g:i A') ?? '—' }} · {{ $report->reason }}</li>
                        @endforeach
                        @if ($delivery->delivered_at) <li>Delivered: {{ $delivery->delivered_at->format('M j, Y g:i A') }}</li> @endif
                    </ol>
                    @if ($delivery->proof_path)
                        <a class="mt-2 inline-block text-xs text-teal underline" href="{{ route('delivery.proof', $delivery) }}">View delivery proof</a>
                    @endif
                </article>
            @empty
                <p class="text-sm text-navy/50">No parcel operations yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
