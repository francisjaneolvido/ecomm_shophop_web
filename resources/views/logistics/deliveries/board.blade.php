@extends('logistics.layouts.console')

@section('title', 'Parcel Operations — ShopHop Logistics')

@section('content')
@php
    $receiveCount = $deliveries->where('status', \App\Models\Logistics\Delivery::PICKED_UP)->count();
    $sortCount = $deliveries->where('status', \App\Models\Logistics\Delivery::AT_SORTING_CENTER)->count();
    $dispatchCount = $deliveries->whereIn('status', [\App\Models\Logistics\Delivery::SORTED, \App\Models\Logistics\Delivery::DELIVERY_FAILED])->count();
    $queueSummary = [
        ['label' => 'Pickup requests', 'count' => $ready->count(), 'note' => 'Assign available pickup riders', 'icon' => 'package-plus', 'href' => '#pickup-queue'],
        ['label' => 'Receive parcels', 'count' => $receiveCount, 'note' => 'Scan incoming parcels', 'icon' => 'scan-line', 'href' => '#active-deliveries'],
        ['label' => 'Sorting queue', 'count' => $sortCount, 'note' => 'Choose destination area', 'icon' => 'boxes', 'href' => '#active-deliveries'],
        ['label' => 'Dispatch / retry', 'count' => $dispatchCount, 'note' => 'Assign delivery riders', 'icon' => 'route', 'href' => '#active-deliveries'],
    ];
@endphp
<div class="space-y-6" id="logisticsDeliveryBoard">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Operations · Parcel handoffs</p>
            <h1 class="title">Parcel Operations</h1>
            <p class="description">This is your work queue. Pickup Riders handle Seller → Branch; Linehaul vehicles and Drivers move consolidated manifests between Branches/Hubs; Delivery Riders complete Branch → Buyer.</p>
        </div>
        <a href="{{ route('logistics.returns.index') }}" class="sh-btn-soft">Return-to-Seller Queue →</a>
        <a href="{{ route('logistics.dashboard') }}" class="sh-btn-ghost"><x-lucide-arrow-left class="h-4 w-4" /> Back to Dashboard</a>
    </header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($queueSummary as $summary)
            <a href="{{ $summary['href'] }}" class="sh-stat group transition hover:border-teal/35 hover:shadow-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="sh-stat-label">{{ $summary['label'] }}</p>
                        <p class="sh-stat-value">{{ $summary['count'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-light text-teal-dark"><x-dynamic-component :component="'lucide-'.$summary['icon']" class="h-4 w-4" /></span>
                </div>
                <p class="sh-stat-note">{{ $summary['note'] }}</p>
            </a>
        @endforeach
    </div>

    <nav class="sh-ui-jump" aria-label="Parcel operation queues"><a href="#pickup-queue">Pickup requests <span>{{ $ready->count() }}</span></a><a href="#active-deliveries">Active deliveries <span>{{ $deliveries->count() }}</span></a><a href="{{ route('logistics.returns.index') }}">Returns ↗</a></nav>

    <details class="sh-surface sh-ui-guide" aria-label="Delivery process overview">
        <summary class="sh-ui-guide-summary"><strong>Parcel handling guide</strong><span>Expand workflow steps</span><x-lucide-chevron-down class="h-4 w-4" /></summary>
        <div class="sh-card-pad">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-light text-teal-dark"><x-lucide-route class="h-5 w-5" /></div>
            <div>
                <h2 class="text-sm font-bold text-navy">How a parcel moves through Logistics</h2>
                <p class="mt-1 text-xs leading-relaxed text-navy/55">Every action below records a real workflow transition. Review the parcel details before confirming.</p>
            </div>
        </div>
        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['01','Seller → Pickup Rider'],['02','Origin branch QR scan'],['03','Sort / optional transit'],['04','Destination → Delivery Rider']] as [$number, $label])
                <div class="flex items-center gap-3 rounded-2xl border border-gray-border bg-gray-bg/50 px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[11px] font-bold text-teal-dark">{{ $number }}</span>
                    <span class="text-xs font-semibold text-navy">{{ $label }}</span>
                </div>
            @endforeach
        </div>
        </div>
    </details>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-teal/25 bg-teal-light/30 p-4 text-xs text-navy/70">
        <p><strong>Branch routing:</strong> Each branch is a warehouse/sorting hub in one Logistics company. Staff scan at their assigned branch; riders carry parcels between locations.</p>
        <a class="sh-btn-soft" href="{{ route('logistics.sorting-centers.index') }}">Check branch readiness →</a>
    </div>

    @if (session('status')) <div role="status" class="sh-alert-success">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div> @endif

    <div class="grid gap-5 2xl:grid-cols-2">
        <section id="pickup-queue" class="sh-surface sh-card-pad scroll-mt-6">
            <div class="sh-toolbar items-start">
                <div>
                    <p class="sh-kicker">Step 1 · Seller pickup</p>
                    <h2 class="mt-1 text-base font-bold text-navy">Pickup requests</h2>
                    <p class="mt-1 text-xs text-navy/50">Orders ready for pickup. If no rider is eligible, open the exact-barangay coverage action below; then assign pickup.</p>
                </div>
                <span class="sh-badge {{ $ready->count() ? 'sh-badge-warning' : 'sh-badge-success' }}">{{ $ready->count() }} waiting</span>
            </div>
            <div class="mt-5 space-y-4">
                @forelse ($ready as $order)
                    <article class="rounded-2xl border border-gray-border bg-gray-bg/35 p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-navy">Order #{{ $order->id }}</p>
                                <p class="mt-1 text-xs text-navy/55">{{ $order->seller?->business_name ?? 'Seller unavailable' }}</p>
                            </div>
                            <span class="sh-badge sh-badge-warning">Needs pickup</span>
                        </div>
                        <div class="mt-4 grid gap-3 text-xs text-navy/65 sm:grid-cols-2">
                            <div><p class="sh-stat-label">Buyer</p><p class="mt-1 break-words font-medium text-navy">{{ $order->delivery_name ?? 'Buyer unavailable' }}</p></div>
                            <div><p class="sh-stat-label">Destination</p><p class="mt-1 break-words font-medium text-navy">{{ $order->delivery_address ?? 'Unavailable' }}</p></div>
                            <div><p class="sh-stat-label">Items / payment</p><p class="mt-1 font-medium text-navy">{{ $order->items->sum('quantity') }} items · {{ strtoupper($order->payment_method) }} · ₱{{ number_format($order->total_amount, 2) }}</p></div>
                            <div><p class="sh-stat-label">Requested hub</p><p class="mt-1 font-medium text-navy">{{ $order->pickupRequest?->originSortingCenter?->name ?? 'Main Sorting Center' }}</p></div>
                            <div class="sm:col-span-2"><p class="sh-stat-label">Seller pickup location (rider matching)</p><p class="mt-1 font-medium text-navy">{{ collect([$order->seller?->barangay_name, $order->seller?->municipality_name, $order->seller?->province_name])->filter()->implode(', ') ?: 'Seller location missing — review Seller account' }}</p></div>
                        </div>
                        <div class="mt-3 rounded-xl border border-gray-border bg-white px-3 py-2 text-xs text-navy/60">Tracking: <span class="font-semibold text-navy">{{ $order->tracking_code ?? 'Pending' }}</span></div>
                        @php
                            $originId = $order->pickupRequest?->origin_sorting_center_id;
                            $pickupRiders = $originId
                                ? $serviceAreas->matchingRiders($riders, $originId,
                                    $order->seller?->province_name, $order->seller?->municipality_name,
                                    $order->seller?->barangay_name, 'pickup')
                                : $riders->groupBy(fn ($rider) => (int) $rider->sorting_center_id)
                                    ->flatMap(fn ($group, $centerId) => $serviceAreas->matchingRiders(
                                        $group, (int) $centerId, $order->seller?->province_name,
                                        $order->seller?->municipality_name, $order->seller?->barangay_name, 'pickup'));
                            $strictPickup = $serviceAreas->configured((int) ($originId ?: 0));
                        @endphp
                        @if (! $strictPickup && $originId)
                            <p class="mt-2 text-[11px] text-amber-700">Legacy branch coverage: barangay rider eligibility has not been configured for this branch.</p>
                        @endif
                        @if ($pickupRiders->isNotEmpty())
                            <form method="POST" action="{{ route('logistics.deliveries.assign', $order) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm-title="Assign pickup rider?" data-confirm-message="The chosen rider will receive this pickup assignment. Please verify the rider and origin hub." data-confirm-button="Assign pickup">
                                @csrf
                                <label class="sh-field"><span class="sh-label">Pickup rider</span>
                                    <select name="rider_id" required class="sh-select"><option value="">Choose pickup rider</option>
                                        @foreach ($pickupRiders as $rider)
                                            <option value="{{ $rider->id }}" @selected($loop->first)>{{ $rider->name }} · {{ $rider->sortingCenter?->name ?? 'No center' }} · {{ $rider->vehicle_type }}{{ $loop->first ? ' · Recommended' : '' }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="sh-btn">Assign pickup</button>
                            </form>
                        @else
                            @php
                                $pickupCenter = $order->pickupRequest?->originSortingCenter;
                                $sellerLocation = $order->seller;
                                $locationReady = $pickupCenter && $pickupCenter->type === 'station_hub'
                                    && $pickupCenter->status === 'active' && ! $pickupCenter->archived_at
                                    && filled($sellerLocation?->municipality_name) && filled($sellerLocation?->barangay_name)
                                    && \App\Services\RiderServiceAreaService::normalize($pickupCenter->province) === \App\Services\RiderServiceAreaService::normalize($sellerLocation?->province_name)
                                    && (\App\Services\RiderServiceAreaService::normalizeCity($pickupCenter->municipality) === \App\Services\RiderServiceAreaService::normalizeCity($sellerLocation?->municipality_name)
                                        || $pickupCenter->coverageAreas()->exists());
                                $branchCandidates = $originId ? $riders->filter(fn ($rider) =>
                                    (int) $rider->sorting_center_id === (int) $originId
                                    && $rider->availability_status === 'available') : collect();
                            @endphp
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                                <div class="flex items-start gap-2"><x-lucide-info class="mt-0.5 h-4 w-4 shrink-0" /><div>
                                    <p class="font-bold">Pickup needs rider coverage — not another order</p>
                                    <p class="mt-1">The Seller is at <strong>{{ $sellerLocation?->barangay_name ?: 'Unknown barangay' }}, {{ $sellerLocation?->municipality_name ?: 'Unknown city' }}</strong>. Riders must belong to <strong>{{ $pickupCenter?->name ?: 'the origin branch' }}</strong>, be active/available, and have <strong>Pickup</strong> coverage here. The Buyer's barangay is for delivery, not pickup.</p>
                                </div></div>
                            </div>
                            @if ($locationReady && $branchCandidates->isNotEmpty())
                                <details class="mt-3 rounded-xl border border-teal/30 bg-teal-light/25 p-3" id="pickup-coverage-{{ $order->id }}">
                                    <summary class="cursor-pointer text-xs font-bold text-teal-dark">Enable pickup coverage for an existing rider</summary>
                                    <div class="mt-3 space-y-3 text-xs text-navy/75">
                                        <p><strong>Step 1.</strong> Select a rider who can really collect parcels in {{ $sellerLocation->barangay_name }}. This creates/links only this barangay's <strong>Pickup</strong> coverage. It will not make the rider citywide.</p>
                                        <form method="POST" action="{{ route('logistics.deliveries.pickup-coverage.enable', $order) }}" class="space-y-3" data-confirm-title="Approve this exact pickup area?" data-confirm-message="You are authorizing the selected rider to pick up parcels in the Seller's barangay at this origin branch. This does not assign the order or change custody/payment." data-confirm-button="Save pickup coverage">
                                            @csrf
                                            <label class="sh-field"><span class="sh-label">Available branch riders to configure ({{ $branchCandidates->count() }})</span>
                                                <select class="sh-select" name="rider_id" required>
                                                    <option value="">Choose the rider who covers {{ $sellerLocation->barangay_name }}</option>
                                                    @foreach ($branchCandidates as $candidate)
                                                        <option value="{{ $candidate->id }}">{{ $candidate->name }} · {{ $candidate->vehicle_type }} · {{ $candidate->operationalServiceAreas->count() }} existing areas</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label class="flex items-start gap-2 rounded-lg border border-teal/20 bg-white/70 p-3 text-xs text-navy">
                                                <input class="mt-0.5 h-4 w-4 accent-teal-600" name="confirm_coverage" type="checkbox" value="1" required>
                                                <span>I confirm this rider can physically serve <strong>{{ $sellerLocation->barangay_name }}, {{ $sellerLocation->municipality_name }}</strong> for Seller pickups.</span>
                                            </label>
                                            <div class="flex flex-wrap items-center justify-between gap-2"><p class="text-[11px] text-navy/60"><strong>Step 2.</strong> Save, then assign pickup after the page refreshes.</p><button type="submit" class="sh-btn">Save pickup coverage</button></div>
                                        </form>
                                    </div>
                                </details>
                            @elseif ($branchCandidates->isEmpty())
                                <div class="mt-3 rounded-xl border border-gray-border bg-white p-3 text-xs text-navy/70">No active, available rider with login at this origin branch. <a class="font-bold text-teal-dark underline" href="{{ route('logistics.riders.index', ['center' => $originId]) }}">Review branch riders and their accounts</a> before trying again.</div>
                            @else
                                <div class="mt-3 rounded-xl border border-gray-border bg-white p-3 text-xs text-navy/70">Seller location or origin branch needs review before granting pickup coverage. <a class="font-bold text-teal-dark underline" href="{{ route('logistics.sorting-centers.index') }}">Review branch setup</a> and Seller address.</div>
                            @endif
                        @endif
                    </article>
                @empty
                    <div class="sh-empty"><x-lucide-package-check class="mx-auto h-6 w-6 text-teal-dark" /><p class="mt-3 text-sm font-semibold text-navy">Pickup queue is clear</p><p class="mt-1 text-xs text-navy/45">New seller requests will appear here when parcels are ready.</p></div>
                @endforelse
            </div>
        </section>

        <section id="active-deliveries" class="sh-surface sh-card-pad scroll-mt-6">
            <div class="sh-toolbar items-start">
                <div>
                    <p class="sh-kicker">Steps 2–4 · Parcel operations</p>
                    <h2 class="mt-1 text-base font-bold text-navy">Active deliveries</h2>
                    <p class="mt-1 text-xs text-navy/50">Scan, sort, transfer between hubs, or assign a delivery rider.</p>
                </div>
                <span class="sh-badge sh-badge-info">{{ $deliveries->count() }} records</span>
            </div>
            <div class="mt-5 space-y-4">
                @forelse ($deliveries as $delivery)
                    <article id="delivery-{{ $delivery->id }}" class="scroll-mt-5 rounded-2xl border border-gray-border bg-gray-bg/35 p-4 sm:p-5">
                        @php
                            $statusBadge = match ($delivery->status) {
                                \App\Models\Logistics\Delivery::DELIVERED => 'sh-badge-success',
                                \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'sh-badge-danger',
                                \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY => 'sh-badge-info',
                                default => 'sh-badge-warning',
                            };
                        @endphp
                        <div class="sh-toolbar items-start">
                            <div class="min-w-0">
                                <p class="break-words text-sm font-bold text-navy">{{ $delivery->tracking_code ?? 'Tracking pending' }}</p>
                                <p class="mt-1 text-xs text-navy/50">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller unavailable' }}</p>
                            </div>
                            <span class="sh-badge {{ $statusBadge }}">{{ $delivery->statusLabel() }}</span>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div><p class="sh-stat-label">Destination</p><p class="mt-1 break-words text-xs font-medium text-navy">{{ $delivery->order?->delivery_address ?? 'Unavailable' }}</p></div>
                            <div><p class="sh-stat-label">Delivery area</p><p class="mt-1 text-xs font-medium text-navy">{{ $delivery->destinationArea?->area_name ?? 'Not sorted yet' }}</p></div>
                            <div><p class="sh-stat-label">Pickup rider</p><p class="mt-1 text-xs font-medium text-navy">{{ $delivery->pickupRider?->name ?? (($delivery->status === \App\Models\Logistics\Delivery::PICKUP_ASSIGNED || $delivery->status === \App\Models\Logistics\Delivery::PICKUP_ACCEPTED) ? ($delivery->rider?->name ?? 'Pending') : 'Pending') }}</p></div>
                            <div><p class="sh-stat-label">Delivery rider</p><p class="mt-1 text-xs font-medium text-navy">{{ $delivery->deliveryRider?->name ?? 'Not assigned yet' }}</p></div>
                        </div>
                        <div class="mt-4 grid gap-3 rounded-xl border border-gray-border bg-white p-3 sm:grid-cols-3">
                            <div><p class="sh-stat-label">Origin hub</p><p class="mt-1 break-words text-xs font-medium text-navy">{{ $delivery->originSortingCenter?->name ?? 'Pending' }}</p></div>
                            <div><p class="sh-stat-label">Current hub</p><p class="mt-1 break-words text-xs font-medium text-navy">{{ $delivery->currentSortingCenter?->name ?? 'In pickup stage' }}</p></div>
                            <div><p class="sh-stat-label">Destination hub</p><p class="mt-1 break-words text-xs font-medium text-navy">{{ $delivery->destinationSortingCenter?->name ?? 'Not determined' }}</p></div>
                        </div>

                        @php $journey = $journeyPreviews[$delivery->id] ?? null; @endphp
                        @if ($journey)
                            <details class="mt-3 overflow-hidden rounded-xl border border-teal/20 bg-white" aria-label="Route preview for {{ $delivery->tracking_code }}">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-xs font-semibold text-navy sm:px-4">
                                    <span class="flex min-w-0 items-center gap-2"><x-lucide-route class="h-4 w-4 shrink-0 text-teal-dark" />Route preview <span class="sh-badge sh-badge-info">{{ $journey['route_type'] }}</span></span>
                                    <span class="text-[11px] font-normal text-navy/60">{{ $journey['next_hop'] ? 'Next: '.$journey['next_hop'] : 'View route details' }}</span>
                                </summary>
                                <div class="border-t border-gray-border px-3 py-3 sm:px-4">
                                    @if (count($journey['stops']))
                                        <ol class="flex flex-wrap items-center gap-2 text-xs text-navy/80" aria-label="Required sorting stops">
                                            @foreach ($journey['stops'] as $stop)
                                                <li class="inline-flex items-center gap-1 rounded-lg border border-gray-border bg-gray-bg/70 px-2.5 py-1.5"><span class="text-teal-dark">{{ $loop->iteration }}.</span><span class="font-medium">{{ $stop['name'] }}</span></li>
                                                @unless ($loop->last)<li aria-hidden="true" class="text-teal-dark">→</li>@endunless
                                            @endforeach
                                        </ol>
                                    @else
                                        <p class="text-xs text-amber-800">Origin or destination sorting center is not assigned yet.</p>
                                    @endif
                                    <p class="mt-2 text-[11px] leading-relaxed text-navy/60">{{ $journey['note'] }}</p>
                                    @php $network = $journey['network_path'] ?? null; @endphp
                                    @if($network)
                                      <div class="mt-3 rounded-xl border border-teal/20 bg-teal-50/60 px-3 py-3 text-xs">
                                        <div class="flex items-center justify-between gap-2"><strong class="text-teal-800">Verified network</strong><span class="text-teal-800">{{ ucfirst($network['status'] ?? 'unavailable') }}</span></div>
                                        @if(($network['stops'] ?? []) !== [])
                                          <p class="mt-1 text-navy/75">{{ collect($network['stops'])->pluck('name')->implode(' → ') }}</p>
                                          @if(($network['legs'] ?? []) !== []) <p class="mt-1 text-navy/50">Transport: {{ collect($network['legs'])->pluck('mode')->implode(' → ') }}</p>@endif
                                        @endif
                                        <p class="mt-1 text-navy/60">{{ $network['note'] ?? '' }}</p>
                                      </div>
                                    @endif
                                    <p class="mt-2 text-[11px] font-semibold text-teal-dark">Route preview is informational. Only a verified manifest and receiving scan can change custody.</p>
                                </div>
                            </details>
                        @endif

                        @if ($delivery->status === \App\Models\Logistics\Delivery::PICKED_UP)
                            <form method="POST" action="{{ route('logistics.deliveries.receive', $delivery) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm-title="Receive this parcel?" data-confirm-message="Confirm that the tracking code matches the physical parcel received by your sorting center." data-confirm-button="Confirm receipt">
                                @csrf
                                <label class="sh-field"><span class="sh-label">Scan / enter tracking code</span><input name="tracking_code" required autocomplete="off" class="sh-input" placeholder="{{ $delivery->tracking_code }}"></label>
                                <button class="sh-btn">Receive parcel</button>
                            </form>
                        @elseif ($delivery->status === \App\Models\Logistics\Delivery::AT_SORTING_CENTER)
                            <form method="POST" action="{{ route('logistics.deliveries.sort', $delivery) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm-title="Confirm destination sort?" data-confirm-message="This records the destination area for the parcel. Verify the buyer address and coverage before confirming." data-confirm-button="Confirm sort">
                                @csrf
                                <label class="sh-field"><span class="sh-label">Destination coverage area</span><select name="destination_area_id" required class="sh-select"><option value="">Choose area</option>
                                    @foreach ($coverageAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>
                                    @endforeach
                                </select></label>
                                <button class="sh-btn">Confirm sort</button>
                            </form>
                        @elseif (in_array($delivery->status, [\App\Models\Logistics\Delivery::SORTED, \App\Models\Logistics\Delivery::DELIVERY_FAILED], true))
                            @php
                                $activeTransfer = $delivery->transfers->firstWhere('status', \App\Models\Logistics\ParcelTransfer::IN_TRANSIT);
                                $needsCenterTransfer = $delivery->status === \App\Models\Logistics\Delivery::SORTED
                                    && $delivery->current_sorting_center_id
                                    && $delivery->destination_sorting_center_id
                                    && (int) $delivery->current_sorting_center_id !== (int) $delivery->destination_sorting_center_id;
                                $transferPlan = $transferPlans[$delivery->id] ?? null;
                                $deliveryCenterId = (int) $delivery->destination_sorting_center_id;
                                $strictDelivery = $serviceAreas->configured($deliveryCenterId);
                                $areaRiders = $serviceAreas->matchingRiders($riders, $deliveryCenterId,
                                    $delivery->order?->delivery_province_name,
                                    $delivery->order?->delivery_municipality_name,
                                    $delivery->order?->delivery_barangay_name, 'delivery');
                                if (! $strictDelivery) {
                                    $areaRiders = $areaRiders->filter(fn ($rider) =>
                                        (int) $rider->coverage_area_id === (int) $delivery->destination_area_id)->values();
                                }
                            @endphp
                            @if (! $needsCenterTransfer && (int) $delivery->origin_sorting_center_id > 0
                                && (int) $delivery->origin_sorting_center_id === (int) $deliveryCenterId)
                                <p class="mt-2 text-xs font-semibold text-teal-dark">Local parcel · Origin and destination are the same branch. No Province Hub transfer needed; QR receiving and sorting remain required.</p>
                            @endif
                            @if ($needsCenterTransfer)
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                    <div class="flex items-start gap-2"><x-lucide-arrow-left-right class="mt-0.5 h-4 w-4 shrink-0 text-amber-800" /><div><p class="text-sm font-bold text-amber-900">Center transfer required</p><p class="mt-1 text-xs text-amber-800">{{ $delivery->currentSortingCenter?->name }} → {{ $activeTransfer?->toCenter?->name ?? $transferPlan['next']?->name ?? 'Routing unresolved' }} <span class="text-amber-700/70">· Final: {{ $delivery->destinationSortingCenter?->name }}</span></p></div></div>
                                    @if ($activeTransfer)
                                        <p class="mt-3 text-xs leading-relaxed text-amber-800">Parcel is already moving on an existing transfer. New center-to-center movements are handled through a Linehaul Manifest, Vehicle, and Driver.</p>
                                        <form method="POST" action="{{ route('logistics.transfers.receive', $activeTransfer) }}" class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm-title="Confirm next-center receipt?" data-confirm-message="Only confirm after physically receiving and checking this transferred parcel." data-confirm-button="Receive transfer">
                                            @csrf
                                            <label class="sh-field"><span class="sh-label">Tracking code</span><input name="tracking_code" required autocomplete="off" placeholder="{{ $delivery->tracking_code }}" class="sh-input"></label>
                                            <button class="sh-btn">Receive transfer</button>
                                        </form>
                                    @else
                                        @if (! $transferPlan || ! ($transferPlan['valid'] ?? false) || ! ($transferPlan['next'] ?? null))
                                            <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs leading-relaxed text-red-800">
                                                Routing blocked: {{ $transferPlan['reason'] ?? 'The required next hub cannot be resolved.' }}
                                            </div>
                                        @else
                                            <div class="mt-3 rounded-xl border border-teal-200 bg-teal-50 p-3 text-xs leading-relaxed text-teal-900">
                                                Required next hop: <strong>{{ $transferPlan['next']->name }}</strong>. This parcel must be consolidated into an Outbound Manifest at its current Branch/Hub, scanned while loading, then assigned to a Linehaul Vehicle + Driver.
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <form method="POST" action="{{ route('logistics.deliveries.assign-delivery', $delivery) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm-title="{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Reschedule this delivery?' : 'Assign delivery rider?' }}" data-confirm-message="Verify the destination area and rider before dispatching this parcel." data-confirm-button="{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Reschedule delivery' : 'Assign rider' }}">
                                    @csrf
                                    <label class="sh-field"><span class="sh-label">Delivery rider for {{ $delivery->destinationArea?->area_name }}</span><select name="rider_id" required class="sh-select"><option value="">Choose branch rider</option>
                                        @foreach ($areaRiders as $rider)
                                            <option value="{{ $rider->id }}" @selected($loop->first)>{{ $rider->name }} · {{ $rider->sortingCenter?->name }} · {{ $rider->vehicle_type }}{{ $loop->first ? ' · Recommended' : '' }}</option>
                                        @endforeach
                                    </select></label>
                                    <button class="sh-btn">{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Reschedule delivery' : 'Assign rider' }}</button>
                                </form>
                                @if ($areaRiders->isEmpty())
                                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">No eligible rider covers this Buyer's barangay from the destination branch. <a href="{{ route('logistics.riders.index') }}" class="font-bold underline">Manage Riders</a></div>
                                @endif
                            @endif
                            @if ($delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED)
                                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3"><p class="text-xs font-bold text-rose-900">Delivery failure reported</p><p class="mt-1 text-xs text-rose-800">{{ $delivery->failure_reason }}</p><p class="mt-2 text-[11px] text-rose-700">Choose whether to retry delivery or initiate a Return-to-Seller case. Both actions require real handoffs.</p></div>
                                <form action="{{ route('logistics.returns.initiate', $delivery) }}" method="POST" class="mt-3 rounded-xl border border-rose-200 bg-white p-3" data-confirm-title="Start Return-to-Seller?" data-confirm-message="This will hold the seller payout and disable further delivery retries. Continue only after reviewing the failed delivery." data-confirm-button="Start return" data-confirm-danger="1" data-confirm-subject="Order #{{ $delivery->order_id }}">@csrf
                                    <label class="sh-field"><span class="sh-label">Return decision reason (minimum 10 characters)</span><textarea required minlength="10" maxlength="1000" name="reason" class="sh-input" rows="2" placeholder="Why delivery will not be retried"></textarea></label>
                                    <button class="sh-btn mt-2" type="submit">Initiate Return to Seller</button>
                                </form>
                            @endif
                        @endif

                        @if(in_array($delivery->status, [\App\Models\Logistics\Delivery::RETURN_REQUESTED,\App\Models\Logistics\Delivery::RETURN_AT_BRANCH,\App\Models\Logistics\Delivery::RETURN_ASSIGNED,\App\Models\Logistics\Delivery::RETURN_IN_TRANSIT,\App\Models\Logistics\Delivery::RETURN_AWAITING_SELLER,\App\Models\Logistics\Delivery::RETURNED],true))
                          <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900">Return process: {{$delivery->statusLabel()}} · <a class="font-bold underline" href="{{route('logistics.returns.index')}}">Open returns queue</a></div>
                        @endif
                        @if ($delivery->failureReports->isNotEmpty())
                            <div class="mt-4 rounded-xl border border-gray-border bg-white p-4">
                                <p class="text-xs font-bold text-navy">Failed delivery attempts</p>
                                <div class="mt-3 space-y-2">
                                    @foreach ($delivery->failureReports as $report)
                                        <div class="rounded-xl bg-gray-bg px-3 py-3 text-xs text-navy/65">
                                            <div class="sh-toolbar"><span class="font-semibold text-navy">Attempt {{ $report->attempt_no }} · {{ $report->rider?->name ?? 'Rider' }}</span><span class="text-[10px] text-navy/40">{{ $report->reported_at?->format('M j, Y g:i A') }}</span></div>
                                            <p class="mt-1">{{ $report->reason }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <details class="mt-4 overflow-hidden rounded-xl border border-gray-border bg-white">
                            <summary class="sh-summary flex items-center justify-between px-4 py-3 text-xs font-semibold text-navy"><span>View shipment timeline</span><x-lucide-chevron-down class="h-4 w-4 text-navy/40" /></summary>
                            <ol class="list-disc space-y-2 border-t border-gray-border px-7 py-4 text-xs leading-relaxed text-navy/60">
                                <li>Pickup assigned: {{ $delivery->assigned_at?->format('M j, Y g:i A') ?? '—' }}</li>
                                @if ($delivery->pickup_accepted_at) <li>Pickup accepted: {{ $delivery->pickup_accepted_at->format('M j, Y g:i A') }}</li> @endif
                                @if ($delivery->picked_up_at) <li>Picked up: {{ $delivery->picked_up_at->format('M j, Y g:i A') }}</li> @endif
                                @if ($delivery->at_sorting_center_at) <li>Sorting center scan: {{ $delivery->at_sorting_center_at->format('M j, Y g:i A') }}</li> @endif
                                @if ($delivery->sorted_at) <li>Sorted: {{ $delivery->sorted_at->format('M j, Y g:i A') }}</li> @endif
                                @foreach ($delivery->transfers as $transfer)
                                    <li>Center transfer: {{ $transfer->fromCenter?->name }} → {{ $transfer->toCenter?->name }} · {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}{{ $transfer->dispatched_at ? ' · '.$transfer->dispatched_at->format('M j, Y g:i A') : '' }}</li>
                                    @if ($transfer->received_at) <li>Received at {{ $transfer->toCenter?->name }}: {{ $transfer->received_at->format('M j, Y g:i A') }}</li> @endif
                                @endforeach
                                @if ($delivery->delivery_assigned_at) <li>Delivery rider assigned: {{ $delivery->delivery_assigned_at->format('M j, Y g:i A') }}</li> @endif
                                @if ($delivery->out_for_delivery_at) <li>Out for delivery: {{ $delivery->out_for_delivery_at->format('M j, Y g:i A') }}</li> @endif
                                @foreach ($delivery->failureReports as $report)
                                    <li>Delivery attempt {{ $report->attempt_no }} failed: {{ $report->reported_at?->format('M j, Y g:i A') ?? '—' }} · {{ $report->reason }}</li>
                                @endforeach
                                @if ($delivery->delivered_at) <li>Delivered: {{ $delivery->delivered_at->format('M j, Y g:i A') }}</li> @endif
                            </ol>
                        </details>
                        @if ($delivery->proof_path)
                            <a href="{{ route('delivery.proof', $delivery) }}" class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-teal-dark hover:underline"><x-lucide-file-check class="h-4 w-4" /> View delivery proof</a>
                        @endif
                    </article>
                @empty
                    <div class="sh-empty"><x-lucide-package-open class="mx-auto h-6 w-6 text-teal-dark" /><p class="mt-3 text-sm font-semibold text-navy">No active parcels yet</p><p class="mt-1 text-xs text-navy/45">Shipments will appear here after a pickup rider is assigned.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
