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
                        <span>{{ ucwords(str_replace('_',' ',$item['status'])) }} @if($item['requires_branch_return']??false) &middot; Awaiting Branch return scan @endif</span>
                    </div>
                    @if($item['reason']??null)<p class="text-xs text-slate-500">{{ $item['reason'] }}</p>@endif
                @endforeach
            </li>
        @endforeach
        </ol>
    </details>
    @if($canManage??false)
        <div class="flex flex-wrap gap-2">
        @foreach(($batchInfo['status']==='queued'?['dispatch'=>'Dispatch route','cancel'=>'Cancel scheduled route']:($batchInfo['status']==='in_progress'?['complete'=>'Complete route']:[])) as $action=>$label)
            <form method="POST" action="{{ route($routePrefix.'.transition',$batchInfo['id']) }}" data-confirm-title="Confirm route action" data-confirm-message="{{ $action==='complete'?'Confirm that every parcel outcome and physical Branch return is resolved.':'Confirm this route action after reviewing its rider, schedule and parcels.' }}" data-confirm-button="{{ $label }}">
                @csrf<input type="hidden" name="action" value="{{ $action }}"><button class="sh-btn-soft" type="submit">{{ $label }}</button>
            </form>
        @endforeach
        </div>
    @endif
</article>
