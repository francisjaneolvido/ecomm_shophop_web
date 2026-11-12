@extends('logistics.layouts.console')
@section('title', 'Parcel Operations — ShopHop Logistics')
@section('content')
<div class="sh-ops sh-finance-ops space-y-5" id="parcelOperations">
    <header class="sh-page-header">
        <div><p class="eyebrow">Operations · Parcel custody</p><h1 class="title">Parcel Operations</h1>
            <p class="description">Work with pickup requests, receiving, sorting, and final-mile assignments. For hub-to-hub movement, use a verified outbound manifest and receiving scan.</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('logistics.returns.index') }}" class="sh-btn-soft">Returns & Exceptions</a><a class="sh-btn-ghost" href="{{ route('logistics.riders.index') }}">Manage Riders</a></div>
    </header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([['Pending pickups',$pickupTotal,'Awaiting rider assignment'],['To receive',$receiveCount,'Physical branch scan required'],['To sort',$sortCount,'Destination assignment pending'],['Dispatch / retry',$dispatchCount,'Rider or hub transfer action']] as [$label,$count,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value">{{ number_format($count) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </div>
    <p class="ops-inline-alert">QR receiving and last-mile rider handoff are separate custody events. A web assignment does not mark the parcel delivered or out for delivery. Operations here only affect your own logistics partner.</p>
    <section class="ops-panel" data-ops-tabs>
        <nav class="ops-tabs" aria-label="Parcel operation queues" role="tablist">
            <button class="ops-tab" type="button" role="tab" data-ops-tab="pickups" aria-selected="true">Seller pickup requests ({{ number_format($pickupTotal) }})</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="parcels" aria-selected="false">Parcel directory ({{ number_format($parcelTotal) }})</button>
        </nav>
        <section role="tabpanel" data-ops-panel="pickups" id="pickups">
            <div class="ops-panel-head"><div><h2>Pending pickup assignments</h2><p class="ops-panel-note">Search all requests; select Manage for eligible riders and exact-barangay pickup coverage.</p></div>
                <form method="GET" class="ops-toolbar"><input type="hidden" name="stage" value="pickups"><input type="hidden" name="parcel_q" value="{{ $parcelSearch }}"><input type="hidden" name="parcel_status" value="{{ $selectedStatus }}"><label><span class="sr-only">Search pickup requests</span><input class="sh-input" name="pickup_q" value="{{ $pickupSearch }}" placeholder="Tracking or seller"></label><button class="sh-btn-soft">Search</button><a class="sh-btn-ghost" href="{{ route('logistics.deliveries.board', ['stage'=>'pickups']) }}">Clear</a></form>
            </div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order / Tracking</th><th>Seller / Pickup location</th><th>Origin branch</th><th>Requested</th><th>Action</th></tr></thead><tbody>
                @forelse($ready as $order)
                    <tr><td><strong>#{{ $order->id }}</strong><div class="ops-row-sub">{{ $order->tracking_code ?: 'Tracking pending' }}</div></td>
                        <td><strong>{{ $order->seller?->business_name ?: 'Seller unavailable' }}</strong><div class="ops-row-sub">{{ collect([$order->seller?->barangay_name,$order->seller?->municipality_name,$order->seller?->province_name])->filter()->implode(', ') ?: 'Location not provided' }}</div></td>
                        <td>{{ $order->pickupRequest?->originSortingCenter?->name ?: 'Origin branch pending' }}</td>
                        <td>{{ $order->pickupRequest?->created_at?->format('M j, Y') ?: '—' }}</td>
                        <td class="ops-row-actions"><button type="button" class="ops-action" data-ops-template="pickup-action-{{ $order->id }}" data-ops-title="Pickup · Order #{{ $order->id }}" data-ops-hint="Assign an eligible rider or configure pickup coverage">Manage</button></td>
                    </tr>
                @empty<tr><td class="ops-empty" colspan="5">No pickup requests match the current filters.</td></tr>@endforelse
            </tbody></table></div>
            <div class="ops-count">{{ $ready->firstItem() ?? 0 }}–{{ $ready->lastItem() ?? 0 }} of {{ $ready->total() }} matching requests</div>
            @if($ready->hasPages())<div class="p-4 border-t border-gray-border">{{ $ready->links() }}</div>@endif
        </section>
        <section role="tabpanel" data-ops-panel="parcels" id="parcels" hidden>
            <div class="ops-panel-head"><div><h2>Delivery records</h2><p class="ops-panel-note">View custody route, latest status and validated actions within one detail modal.</p></div>
                <form method="GET" class="ops-toolbar"><input type="hidden" name="stage" value="parcels"><input type="hidden" name="pickup_q" value="{{ $pickupSearch }}"><label><span class="sr-only">Search parcels</span><input class="sh-input" name="parcel_q" value="{{ $parcelSearch }}" placeholder="Tracking, order or seller"></label><label><span class="sr-only">Parcel status</span><select name="parcel_status" class="sh-select"><option value="">All statuses</option>@foreach($statuses as $item)<option value="{{ $item }}" @selected($selectedStatus===$item)>{{ ucwords(str_replace('_',' ',$item)) }}</option>@endforeach</select></label><button class="sh-btn-soft">Filter</button><a class="sh-btn-ghost" href="{{ route('logistics.deliveries.board', ['stage'=>'parcels']) }}">Clear</a></form>
            </div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Parcel / Order</th><th>Seller</th><th>Current center</th><th>Destination</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @forelse($deliveries as $delivery)
                    @php $badge = match($delivery->status) { \App\Models\Logistics\Delivery::DELIVERED => 'sh-badge-success', \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'sh-badge-danger', \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY => 'sh-badge-info', default => 'sh-badge-warning' }; @endphp
                    <tr><td><strong>{{ $delivery->tracking_code ?: 'Tracking pending' }}</strong><div class="ops-row-sub">Order #{{ $delivery->order_id }}</div></td>
                        <td>{{ $delivery->order?->seller?->business_name ?: '—' }}</td>
                        <td>{{ $delivery->currentSortingCenter?->name ?: 'Pickup stage' }}</td>
                        <td>{{ $delivery->destinationSortingCenter?->name ?: 'Not sorted' }}</td>
                        <td><span class="sh-badge {{ $badge }}">{{ $delivery->statusLabel() }}</span></td>
                        <td class="ops-row-actions"><button type="button" class="ops-action" data-ops-template="parcel-action-{{ $delivery->id }}" data-ops-title="Parcel · {{ $delivery->tracking_code ?? 'Order #'.$delivery->order_id }}" data-ops-hint="Review physical custody and permitted next actions">Manage</button></td></tr>
                @empty<tr><td class="ops-empty" colspan="6">No parcels match the current filters.</td></tr>@endforelse
            </tbody></table></div>
            <div class="ops-count">{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} matching deliveries</div>
            @if($deliveries->hasPages())<div class="p-4 border-t border-gray-border">{{ $deliveries->links() }}</div>@endif
        </section>
    </section>

    @foreach($ready as $order)
    <template id="pickup-action-{{ $order->id }}">
        <div class="ops-modal-meta"><p><strong>Seller:</strong> {{ $order->seller?->business_name ?: '—' }}</p><p><strong>Origin:</strong> {{ $order->pickupRequest?->originSortingCenter?->name ?: 'Not assigned' }}</p><p><strong>Pickup location:</strong> {{ collect([$order->seller?->barangay_name,$order->seller?->municipality_name,$order->seller?->province_name])->filter()->implode(', ') ?: 'Unknown' }}</p><p><strong>Buyer:</strong> {{ $order->delivery_name ?: '—' }}</p><p><strong>COD/Total:</strong> ₱{{ number_format((float)$order->total_amount,2) }}</p></div>
        @php
            $originId = $order->pickupRequest?->origin_sorting_center_id;
            $pickupRiders = $originId
                ? $serviceAreas->matchingRiders($riders, $originId, $order->seller?->province_name,$order->seller?->municipality_name,$order->seller?->barangay_name,'pickup')
                : $riders->groupBy(fn ($rider) => (int)$rider->sorting_center_id)->flatMap(fn($group,$centerId) => $serviceAreas->matchingRiders($group,(int)$centerId,$order->seller?->province_name,$order->seller?->municipality_name,$order->seller?->barangay_name,'pickup'));
        @endphp
        @if($pickupRiders->isNotEmpty())
            <form method="POST" action="{{ route('logistics.deliveries.assign',$order) }}" class="space-y-3" data-confirm-title="Assign pickup rider?" data-confirm-message="Check seller location, branch and rider. This is an assignment only, not parcel custody." data-confirm-button="Assign pickup rider">@csrf
                <label class="sh-field"><span class="sh-label">Eligible pickup rider *</span><select name="rider_id" required class="sh-select"><option value="">Select rider</option>@foreach($pickupRiders as $rider)<option value="{{ $rider->id }}">{{ $rider->name }} · {{ $rider->sortingCenter?->name ?? 'No branch' }}</option>@endforeach</select></label>
                <div class="ops-dialog-actions"><button type="submit" class="sh-btn">Assign pickup</button></div>
            </form>
        @else
            <p class="ops-inline-alert">No rider currently matches this seller's pickup barangay. Verify operational service areas before assigning.</p>
            @php
                $pickupCenter = $order->pickupRequest?->originSortingCenter;
                $seller = $order->seller;
                $locationReady = $pickupCenter && $pickupCenter->type === 'station_hub'
                    && $pickupCenter->status === 'active' && ! $pickupCenter->archived_at
                    && filled($seller?->municipality_name) && filled($seller?->barangay_name)
                    && \App\Services\RiderServiceAreaService::normalize($pickupCenter->province) === \App\Services\RiderServiceAreaService::normalize($seller?->province_name)
                    && (\App\Services\RiderServiceAreaService::normalizeCity($pickupCenter->municipality) === \App\Services\RiderServiceAreaService::normalizeCity($seller?->municipality_name) || $pickupCenter->coverageAreas()->exists());
                $branchCandidates = $originId ? $riders->filter(fn($rider) => (int)$rider->sorting_center_id === (int)$originId && $rider->availability_status === 'available') : collect();
            @endphp
            @if($locationReady && $branchCandidates->isNotEmpty())
                <form method="POST" action="{{ route('logistics.deliveries.pickup-coverage.enable',$order) }}" class="mt-4 space-y-3" data-confirm-title="Authorize barangay pickup coverage?" data-confirm-message="Enable this exact seller barangay for the selected rider; no order assignment or custody occurs." data-confirm-button="Save coverage">@csrf
                    <label class="sh-field"><span class="sh-label">Available rider at origin branch</span><select class="sh-select" name="rider_id" required><option value="">Choose rider</option>@foreach($branchCandidates as $rider)<option value="{{ $rider->id }}">{{ $rider->name }}</option>@endforeach</select></label>
                    <label class="flex gap-2 text-xs"><input type="checkbox" required name="confirm_coverage" value="1"><span>I confirm this rider physically serves {{ $seller->barangay_name }}, {{ $seller->municipality_name }} for pickups.</span></label>
                    <div class="ops-dialog-actions"><button class="sh-btn" type="submit">Enable pickup coverage</button></div>
                </form>
            @else <a href="{{ route('logistics.riders.index') }}" class="sh-btn-soft inline-flex mt-3">Review rider and branch setup</a> @endif
        @endif
    </template>
    @endforeach

    @foreach($deliveries as $delivery)
    <template id="parcel-action-{{ $delivery->id }}">
        <div class="ops-modal-meta"><p><strong>Order:</strong> #{{ $delivery->order_id }}</p><p><strong>Seller:</strong> {{ $delivery->order?->seller?->business_name ?: '—' }}</p><p><strong>Buyer address:</strong> {{ $delivery->order?->delivery_address ?: '—' }}</p><p><strong>Origin:</strong> {{ $delivery->originSortingCenter?->name ?: '—' }}</p><p><strong>Current:</strong> {{ $delivery->currentSortingCenter?->name ?: 'Pickup stage' }}</p><p><strong>Destination:</strong> {{ $delivery->destinationSortingCenter?->name ?: 'Not sorted' }}</p><p><strong>Pickup rider:</strong> {{ $delivery->pickupRider?->name ?: $delivery->rider?->name ?: '—' }}</p><p><strong>Delivery rider:</strong> {{ $delivery->deliveryRider?->name ?: 'Not assigned' }}</p></div>
        @php $journey = $journeyPreviews[$delivery->id] ?? null; @endphp
        @if($journey)
            <details class="ops-detail-section"><summary>Route preview · {{ $journey['route_type'] ?? 'Unknown' }}</summary><div class="p-3 text-xs space-y-2"><p>{{ $journey['note'] ?? '' }}</p><p><strong>Next:</strong> {{ $journey['next_hop'] ?? 'Not resolved' }}</p>@if(($journey['network_path']['stops'] ?? []) !== [])<p>{{ collect($journey['network_path']['stops'])->pluck('name')->implode(' → ') }}</p>@endif<p class="text-teal-dark">Informational only — verified manifest and physical QR scans control custody.</p></div></details>
        @endif
        @if($delivery->status === \App\Models\Logistics\Delivery::PICKED_UP)
            <form class="space-y-3" method="POST" action="{{ route('logistics.deliveries.receive',$delivery) }}" data-confirm-title="Confirm parcel receipt?" data-confirm-message="Verify the actual parcel tracking QR at origin branch." data-confirm-button="Receive parcel">@csrf
                <label class="sh-field"><span class="sh-label">Scan or enter physical parcel tracking code *</span><input class="sh-input" autocomplete="off" required name="tracking_code" placeholder="{{ $delivery->tracking_code }}"></label><div class="ops-dialog-actions"><button class="sh-btn">Confirm branch receipt</button></div></form>
        @elseif($delivery->status === \App\Models\Logistics\Delivery::AT_SORTING_CENTER)
            <form class="space-y-3" method="POST" action="{{ route('logistics.deliveries.sort',$delivery) }}" data-confirm-title="Confirm destination sort?" data-confirm-message="The selected coverage area must match the buyer's recorded address." data-confirm-button="Sort parcel">@csrf
                <label class="sh-field"><span class="sh-label">Destination coverage area *</span><select class="sh-select" name="destination_area_id" required><option value="">Choose destination</option>@foreach($coverageAreas as $area)<option value="{{ $area->id }}">{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></label><div class="ops-dialog-actions"><button class="sh-btn">Confirm sorting</button></div></form>
        @elseif(in_array($delivery->status,[\App\Models\Logistics\Delivery::SORTED,\App\Models\Logistics\Delivery::DELIVERY_FAILED],true))
            @php
                $activeTransfer = $delivery->transfers->firstWhere('status',\App\Models\Logistics\ParcelTransfer::IN_TRANSIT);
                $needsTransfer = $delivery->status === \App\Models\Logistics\Delivery::SORTED && $delivery->current_sorting_center_id && $delivery->destination_sorting_center_id && (int)$delivery->current_sorting_center_id !== (int)$delivery->destination_sorting_center_id;
                $transferPlan = $transferPlans[$delivery->id] ?? null;
                $centerId = (int)$delivery->destination_sorting_center_id;
                $strict = $serviceAreas->configured($centerId);
                $areaRiders = $serviceAreas->matchingRiders($riders,$centerId,$delivery->order?->delivery_province_name,$delivery->order?->delivery_municipality_name,$delivery->order?->delivery_barangay_name,'delivery');
                if(!$strict) $areaRiders = $areaRiders->filter(fn($rider)=>(int)$rider->coverage_area_id === (int)$delivery->destination_area_id)->values();
            @endphp
            @if($needsTransfer)
                <p class="ops-inline-alert"><strong>Center transfer required:</strong> {{ $delivery->currentSortingCenter?->name ?: 'Current center' }} → {{ $activeTransfer?->toCenter?->name ?? $transferPlan['next']?->name ?? 'Unknown next hop' }}.</p>
                @if($activeTransfer)
                    <form class="mt-3 space-y-3" method="POST" action="{{ route('logistics.transfers.receive',$activeTransfer) }}" data-confirm-title="Confirm transfer receipt?" data-confirm-message="Confirm physical arrival and tracking match before receiving." data-confirm-button="Receive transfer">@csrf
                        <label class="sh-field"><span class="sh-label">Parcel tracking code *</span><input class="sh-input" required name="tracking_code" autocomplete="off" placeholder="{{ $delivery->tracking_code }}"></label><div class="ops-dialog-actions"><button class="sh-btn">Receive transfer</button></div></form>
                @elseif(!($transferPlan['valid'] ?? false))<p class="mt-3 text-xs text-red-700">Route blocked: {{ $transferPlan['reason'] ?? 'No verified connection found.' }}</p>
                @else<p class="mt-3 text-xs text-navy/70">Next hop: {{ $transferPlan['next']?->name ?? 'Unavailable' }}. Create/load an outbound manifest from the current branch or hub.</p>@endif
            @else
                <form class="space-y-3" method="POST" action="{{ route('logistics.deliveries.assign-delivery',$delivery) }}" data-confirm-title="{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Retry this delivery?' : 'Assign final-mile rider?' }}" data-confirm-message="Confirm the rider is qualified for the buyer barangay. Physical rider handoff still requires QR scan." data-confirm-button="Assign rider">@csrf
                    <label class="sh-field"><span class="sh-label">Eligible delivery rider *</span><select class="sh-select" name="rider_id" required><option value="">Choose rider</option>@foreach($areaRiders as $rider)<option value="{{ $rider->id }}">{{ $rider->name }} · {{ $rider->sortingCenter?->name ?? '—' }}</option>@endforeach</select></label>
                    @if($areaRiders->isEmpty())<p class="ops-inline-alert">No eligible rider assigned to this destination barangay. Update Rider Service Areas first.</p>@endif
                    <div class="ops-dialog-actions"><button class="sh-btn" @disabled($areaRiders->isEmpty())>{{ $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED ? 'Retry delivery' : 'Assign rider' }}</button></div>
                </form>
            @endif
            @if($delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED)
                <div class="mt-4 ops-modal-section"><h3>Delivery exception</h3><p class="text-xs text-rose-800">{{ $delivery->failure_reason ?: 'No failure reason' }}</p>
                    <form class="space-y-3 mt-3" method="POST" action="{{ route('logistics.returns.initiate',$delivery) }}" data-confirm-title="Start Return-to-Seller?" data-confirm-message="This holds the seller payout and stops delivery retry. Confirm this decision after reviewing the failed attempt." data-confirm-button="Start return" data-confirm-danger="1">@csrf
                        <label class="sh-field"><span class="sh-label">Return reason (minimum 10 characters) *</span><textarea class="sh-textarea" name="reason" required minlength="10" maxlength="1000" rows="3"></textarea></label><div class="ops-dialog-actions"><button class="sh-btn-danger">Initiate return</button></div></form>
                </div>
            @endif
        @elseif(in_array($delivery->status,[\App\Models\Logistics\Delivery::RETURN_REQUESTED,\App\Models\Logistics\Delivery::RETURN_AT_BRANCH,\App\Models\Logistics\Delivery::RETURN_ASSIGNED,\App\Models\Logistics\Delivery::RETURN_IN_TRANSIT,\App\Models\Logistics\Delivery::RETURN_AWAITING_SELLER,\App\Models\Logistics\Delivery::RETURNED],true))
            <p class="ops-inline-alert">This parcel is in the return process. <a href="{{ route('logistics.returns.index') }}" class="font-bold underline">Manage returns</a>.</p>
        @else <p class="mt-3 text-xs text-navy/60">No web action is available for this status. Check the mobile rider and scanner events for its next custody step.</p>
        @endif
        @if($delivery->failureReports->isNotEmpty())
            <details class="ops-detail-section"><summary>Failed delivery attempts ({{ $delivery->failureReports->count() }})</summary><ol class="p-3 space-y-2 text-xs">@foreach($delivery->failureReports as $report)<li>Attempt {{ $report->attempt_no }} · {{ $report->reason }} · {{ $report->reported_at?->format('M j, Y g:i A') ?? '—' }}</li>@endforeach</ol></details>
        @endif
        <details class="ops-detail-section"><summary>Shipment timeline</summary><ol class="p-3 space-y-1 text-xs"><li>Assigned: {{ $delivery->assigned_at?->format('M j, Y g:i A') ?? '—' }}</li>@if($delivery->picked_up_at)<li>Picked up: {{ $delivery->picked_up_at->format('M j, Y g:i A') }}</li>@endif @if($delivery->at_sorting_center_at)<li>Received: {{ $delivery->at_sorting_center_at->format('M j, Y g:i A') }}</li>@endif @if($delivery->sorted_at)<li>Sorted: {{ $delivery->sorted_at->format('M j, Y g:i A') }}</li>@endif @foreach($delivery->transfers as $transfer)<li>Transfer: {{ $transfer->fromCenter?->name }} → {{ $transfer->toCenter?->name }} · {{ $transfer->status }}</li>@endforeach @if($delivery->delivered_at)<li>Delivered: {{ $delivery->delivered_at->format('M j, Y g:i A') }}</li>@endif</ol></details>
        @if($delivery->proof_path)<a class="sh-btn-soft inline-flex mt-3" href="{{ route('delivery.proof',$delivery) }}" target="_blank" rel="noopener">View delivery proof</a>@endif
    </template>
    @endforeach

    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle">
        <div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">Parcel details</h2><p class="ops-panel-note" id="opsRecordHint">Review before taking action</p></div><button type="button" class="ops-action" data-ops-close aria-label="Close">✕</button></div>
        <div class="ops-dialog-body space-y-4" data-ops-record-content></div>
        <div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-ops-close>Close</button></div>
    </dialog>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261112-navy-pink-2">
<link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261112-navy-pink-2">
@endpush
@push('scripts')@endpush
