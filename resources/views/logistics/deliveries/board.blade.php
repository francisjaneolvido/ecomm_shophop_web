{{-- resources/views/logistics/deliveries/board.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Deliveries — ShopHop Logistics')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Deliveries</h1>
        <p class="text-navy/55 text-sm mt-1">Every pickup request moving through your fleet, live.</p>
    </div>

    <div class="relative w-full sm:w-auto">
        <x-lucide-search class="w-4 h-4 text-navy/35 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input type="text" placeholder="Search waybill or seller"
               class="pl-9 pr-4 py-2.5 text-sm border border-gray-border rounded-full w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($columns as $key => $column)
        <div class="bg-gray-bg rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-xs font-bold text-navy">{{ $column['label'] }}</p>
                <span class="text-[11px] font-semibold bg-white text-navy/50 rounded-full px-2 py-0.5">{{ count($column['items']) }}</span>
            </div>

            <div class="space-y-2.5">
                @forelse ($column['items'] as $item)
                    <div class="bg-white border border-gray-border rounded-xl p-3.5">
                        <p class="text-[11px] font-semibold text-navy/40 mb-1">#{{ $item['id'] }}</p>
                        <p class="text-sm font-semibold text-navy mb-2">{{ $item['seller'] }}</p>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-navy/50">{{ $item['meta'] }}</span>

                            @if (isset($item['status']))
                                <span class="text-[10px] font-semibold px-2 py-1 rounded-full whitespace-nowrap
                                    {{ str_contains($item['status'], 'Failed')
                                        ? 'bg-red-50 text-red-600'
                                        : (str_contains($item['status'], 'Delivered')
                                            ? 'bg-teal-light text-teal-dark'
                                            : 'bg-amber-50 text-amber-700') }}">
                                    {{ $item['status'] }}
                                </span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-teal-light text-teal-dark whitespace-nowrap">
                                    Unassigned
                                </span>
                            @endif
                        </div>

                        @if ($key === 'new')
                            <button type="button" class="w-full mt-3 text-xs font-semibold bg-navy hover:bg-teal text-white py-2 rounded-full transition">
                                Assign rider
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-navy/35 text-center py-6">Nothing here.</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@endsection
