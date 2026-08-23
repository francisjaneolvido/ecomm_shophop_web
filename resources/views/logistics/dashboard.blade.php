{{-- resources/views/logistics/dashboard.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Dashboard — ShopHop Logistics')

@section('content')

{{-- =========================================================
    PAGE HEADER
========================================================= --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Good morning, J&amp;T Express</h1>
        <p class="text-navy/55 text-sm mt-1">Here's what's happening with your fleet today.</p>
    </div>

    @if (count($pendingApplications))
        <a href="{{ route('logistics.riders.index') }}"
           class="inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20 shrink-0">
            Review {{ count($pendingApplications) }} rider application{{ count($pendingApplications) === 1 ? '' : 's' }}
            <x-lucide-arrow-right class="w-4 h-4" />
        </a>
    @endif
</div>

{{-- =========================================================
    STAT CARDS
========================================================= --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($stats as $stat)
        <div class="bg-white border border-gray-border rounded-2xl p-5">
            <p class="text-xs font-semibold text-navy/50 mb-2">{{ $stat['label'] }}</p>
            <p class="text-2xl font-bold text-navy">{{ $stat['value'] }}</p>
            <p class="text-[11px] font-semibold mt-2 {{ $stat['tone'] === 'up' ? 'text-teal-dark' : 'text-amber-600' }}">
                {{ $stat['trend'] }}
            </p>
        </div>
    @endforeach
</div>

{{-- =========================================================
    CHART + TOP RIDERS
========================================================= --}}
<div class="grid lg:grid-cols-[1.4fr_1fr] gap-4 mb-6">

    <div class="bg-white border border-gray-border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <p class="text-sm font-bold text-navy">Deliveries — last 7 days</p>
            <a href="{{ route('logistics.reports.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">View report</a>
        </div>

        @php $max = collect($weeklyDeliveries)->max('value') ?: 1; @endphp
        <div class="flex items-end gap-2.5 h-28">
            @foreach ($weeklyDeliveries as $day)
                <div class="flex-1 bg-gradient-to-t from-teal to-teal-light rounded-t-md" style="height: {{ $day['value'] / $max * 100 }}%"></div>
            @endforeach
        </div>
        <div class="flex gap-2.5 mt-2">
            @foreach ($weeklyDeliveries as $day)
                <span class="flex-1 text-center text-[10px] font-semibold text-navy/40">{{ $day['label'] }}</span>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-gray-border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-bold text-navy">Top riders today</p>
            <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">See all</a>
        </div>
        <div class="divide-y divide-gray-border">
            @foreach ($topRiders as $rider)
                <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                        {{ collect(explode(' ', $rider['name']))->map(fn ($p) => $p[0])->implode('') }}
                    </div>
                    <p class="flex-1 text-sm font-semibold text-navy">{{ $rider['name'] }}</p>
                    <p class="text-xs text-navy/50">{{ $rider['deliveries'] }} deliveries</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- =========================================================
    PENDING RIDER APPLICATIONS
========================================================= --}}
<div class="bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-border">
        <p class="text-sm font-bold text-navy">Pending rider applications</p>
        <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">View all</a>
    </div>

    @forelse ($pendingApplications as $app)
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-border' : '' }}">
            <p class="flex-1 min-w-32 text-sm font-semibold text-navy">{{ $app['name'] }}</p>
            <p class="flex-1 min-w-32 text-xs text-navy/50">{{ $app['vehicle'] }}</p>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $app['complete'] ? 'bg-teal-light text-teal-dark' : 'bg-red-50 text-red-600' }}">
                {{ $app['complete'] ? 'Docs complete' : 'Docs incomplete' }}
            </span>
            <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-navy hover:text-teal-dark transition">Review</a>
        </div>
    @empty
        <p class="px-6 py-10 text-center text-navy/40 text-sm">No pending applications right now.</p>
    @endforelse
</div>

@endsection
