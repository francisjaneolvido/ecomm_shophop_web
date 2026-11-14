<div class="p-4 sm:p-5 lg:border-r border-gray-border/80 min-w-0">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h3 class="text-sm font-bold text-navy">Parcel Journey</h3>
            <p class="text-xs text-navy/60 mt-1">Oldest to newest · Philippine Standard Time (PHT)</p>
        </div>
        @if ($order['tracking_no'])
            <button type="button" class="copy-tracking-btn px-3 py-2 rounded-lg border border-gray-border bg-white text-xs font-semibold text-teal-dark break-all"
                data-copy-tracking="{{ $order['tracking_no'] }}">Copy Tracking No.</button>
        @endif
    </div>
    <div class="rounded-lg bg-teal-light p-3 mb-5 text-xs leading-relaxed text-navy">
        <p class="font-semibold">{{ $order['status_label'] }}</p>
        <p class="mt-1">{{ $order['status_note'] }}</p>
        @if (in_array($order['status'], ['CONFIRMED', 'PREPARING', 'READY_FOR_PICKUP']))
            <p class="mt-1 text-navy/60">Seller stage timestamps are not recorded in the available history.</p>
        @endif
    </div>
    <ol aria-label="Recorded parcel history">
        @foreach ($order['tracking_events'] as $event)
            <li class="relative flex gap-3 sm:gap-4 pb-6 last:pb-0">
                @if (! $loop->last)
                    <span aria-hidden="true" class="absolute left-4 top-8 bottom-0 w-px bg-gray-border"></span>
                @endif
                <span aria-hidden="true" class="relative z-10 w-8 h-8 rounded-full border shrink-0 flex items-center justify-center {{ $event['latest'] ? 'bg-teal border-teal text-white ring-4 ring-teal/10' : 'bg-white border-gray-border text-teal-dark' }}">
                    <x-dynamic-component :component="'lucide-'.$event['icon']" class="w-4 h-4" />
                </span>
                <div class="min-w-0 flex-1 {{ $event['latest'] ? 'rounded-lg bg-white p-3 border border-teal/30' : 'pt-1' }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold {{ $event['latest'] ? 'text-teal-dark' : 'text-navy' }}">{{ $event['title'] }}</p>
                        @if ($event['latest'])
                            <span class="text-[10px] font-semibold text-teal-dark">Latest recorded event</span>
                        @endif
                    </div>
                    @if ($event['at'])
                        <time datetime="{{ $event['at'] }}" class="block text-xs text-navy/65 mt-1">{{ $event['timestamp'] }}</time>
                    @else
                        <p class="text-xs text-navy/60 mt-1">{{ $event['timestamp'] }} · Position in history unavailable</p>
                    @endif
                    @if ($event['location'])
                        <p class="flex items-start gap-1.5 text-xs text-navy/70 mt-2">
                            <x-lucide-map-pin class="w-3.5 h-3.5 shrink-0" />
                            <span class="break-words">{{ $event['location'] }}</span>
                        </p>
                    @endif
                    <p class="text-xs leading-relaxed text-navy/70 mt-2">{{ $event['description'] }}</p>
                    @if ($event['provider'] || $event['tracking'])
                        <p class="text-[11px] text-navy/55 mt-2 break-all">{{ $event['provider'] }}{{ $event['provider'] && $event['tracking'] ? ' · ' : '' }}{{ $event['tracking'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
