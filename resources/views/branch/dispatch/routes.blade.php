@if($routePrefix === 'branch.dispatch')
@php
    $awaitingReceipts = $batchData->where('status', 'in_progress')->flatMap(fn ($route) => collect($route['items'])
        ->filter(fn ($item) => ($route['type'] === 'pickup' && $item['status'] === 'picked_up') || ($item['requires_branch_return'] ?? false))
        ->map(fn ($item) => array_merge($item, ['rider_name' => $route['rider']])));
@endphp
@if($awaitingReceipts->isNotEmpty())
<section class="ops-panel mb-4" aria-label="Awaiting branch receipt">
    <div class="ops-panel-head"><div><h3>Awaiting branch receipt</h3><p class="ops-panel-note">The rider has the parcel. Receive it here when it is physically at this branch.</p></div></div>
    @foreach($awaitingReceipts as $receipt)
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-border p-4">
        <div><p class="text-sm font-semibold">{{ $receipt['tracking_code'] }}</p><p class="text-xs text-slate-500">From {{ $receipt['rider_name'] }} &middot; Awaiting branch receipt</p></div>
        <a class="sh-btn" href="{{ route('branch.scanner', ['mode' => 'rider', 'tracking' => $receipt['tracking_code']]) }}">Receive parcel</a>
    </div>
    @endforeach
</section>
@endif
@endif
@foreach($batchData as $batchInfo)
<article data-batch-id="{{ $batchInfo['id'] }}" class="ops-panel p-4 space-y-3">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><p class="text-xs text-slate-500">{{ $batchInfo['branch'] }} &middot; {{ ucfirst($batchInfo['type']) }} route</p>
            <h3 class="text-sm font-semibold text-navy">{{ $batchInfo['code'] }}</h3>
            <p class="text-sm">{{ $batchInfo['rider'] }}</p></div>
        <span class="rounded-md bg-teal-light px-3 py-1 text-xs font-medium text-navy">{{ ucwords(str_replace('_',' ',$batchInfo['status'])) }}</span>
    </div>
    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($batchInfo['scheduled_start'])->timezone('Asia/Manila')->format('M j, g:i A') }}
        &middot; {{ \Carbon\Carbon::parse($batchInfo['scheduled_end'])->timezone('Asia/Manila')->format('g:i A') }} PHT</p>
    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs">
        <span>{{ $batchInfo['progress']['settled'] }}/{{ $batchInfo['progress']['total'] }} parcels resolved</span>
        <span>{{ $batchInfo['capacity']['totals']['stops'] }} stops</span>
        <span>{{ $batchInfo['capacity']['totals']['weight_kg'] }} kg</span>
        <span>{{ $batchInfo['capacity']['totals']['volume_m3'] }} m3</span>
        <span>Estimated {{ $batchInfo['capacity']['totals']['minutes'] }} min</span>
    </div>
    <details><summary class="cursor-pointer text-sm font-medium text-teal-dark">Stops & parcels</summary>
        <ol class="mt-3 space-y-3">
        @foreach(collect($batchInfo['items'])->groupBy('stop_sequence') as $position=>$stopItems)
            <li class="border-t border-gray-border pt-2">
                <p class="text-sm font-medium">{{ $position }}. {{ $stopItems->first()['stop']['name'] }}</p>
                <p class="text-xs text-slate-500">{{ $stopItems->first()['stop']['address'] }}</p>
                @foreach($stopItems as $item)
                    <div class="flex flex-wrap justify-between gap-2 py-1 text-xs">
                        <span>{{ $item['tracking_code'] }} @if($item['cod_amount']!==null) &middot; COD PHP {{ number_format($item['cod_amount'],2) }} @endif</span>
                        <span>{{ $batchInfo['type'] === 'pickup' && $item['status'] === 'picked_up' ? 'Awaiting branch receipt' : ucwords(str_replace('_',' ',$item['status'])) }} @if($item['requires_branch_return']??false) &middot; Awaiting Branch return scan @endif</span>
                    </div>
                    @if($item['reason']??null)<p class="text-xs text-slate-500">{{ $item['reason'] }}</p>@endif
                @endforeach
            </li>
        @endforeach
        </ol>
    </details>
        <div class="flex flex-wrap gap-2">
        @foreach(($batchInfo['status']==='queued'?['dispatch'=>'Dispatch route','cancel'=>'Cancel scheduled route']:($batchInfo['status']==='in_progress'?['complete'=>'Complete route']:[])) as $action=>$label)
            <form method="POST" action="{{ route($routePrefix.'.transition',$batchInfo['id']) }}" data-confirm-title="Confirm route action" data-confirm-message="{{ $action==='complete'?'Confirm that every parcel outcome and physical Branch return is resolved.':'Confirm this route action after reviewing its rider, schedule and parcels.' }}" data-confirm-button="{{ $label }}">
                @csrf<input type="hidden" name="action" value="{{ $action }}"><button class="sh-btn-soft" type="submit">{{ $label }}</button>
            </form>
        @endforeach
        </div>
</article>

@endforeach
@if($batchData->isEmpty())<div class="ops-panel p-5 text-sm text-slate-500">No rider routes yet. Assign a parcel above to get started.</div>@endif
@if($batches){{ $batches->links() }}@endif
