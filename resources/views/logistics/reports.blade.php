{{-- resources/views/logistics/reports.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Reports — ShopHop Logistics')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Delivery &amp; earnings report</h1>
        <p class="text-navy/55 text-sm mt-1">Filter by date range, then export for your records.</p>
    </div>
</div>

<form method="GET" action="{{ route('logistics.reports.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mb-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-navy/55">
        <span>From</span>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
               class="border border-gray-border rounded-lg px-3 py-2 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal w-full sm:w-auto">
        <span>To</span>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
               class="border border-gray-border rounded-lg px-3 py-2 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal w-full sm:w-auto">
    </div>
    <div class="flex flex-wrap items-center gap-3 sm:contents">
        <button type="submit" class="text-xs font-semibold bg-gray-bg hover:bg-navy hover:text-white text-navy px-4 py-2.5 rounded-full transition">
            Apply
        </button>
        <a href="{{ route('logistics.reports.export', request()->query()) }}"
           class="sm:ml-auto inline-flex items-center justify-center gap-2 text-xs font-semibold bg-navy hover:bg-teal text-white px-4 py-2.5 rounded-full transition">
            <x-lucide-download class="w-3.5 h-3.5" />
            Export CSV
        </a>
    </div>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Total deliveries</p>
        <p class="text-2xl font-bold text-navy">{{ number_format($summary['total_deliveries']) }}</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">On-time rate</p>
        <p class="text-2xl font-bold text-navy">{{ $summary['on_time_rate'] }}%</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Gross delivery fees</p>
        <p class="text-2xl font-bold text-navy">₱{{ number_format($summary['gross_fees']) }}</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Platform commission</p>
        <p class="text-2xl font-bold text-navy">₱{{ number_format($summary['commission']) }}</p>
    </div>
</div>

<div class="bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg">
                    <th class="px-6 py-3">Rider</th>
                    <th class="px-6 py-3">Deliveries</th>
                    <th class="px-6 py-3">On-time %</th>
                    <th class="px-6 py-3">Earnings</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-border">
                @foreach ($riders as $rider)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-navy whitespace-nowrap">{{ $rider['name'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['deliveries'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['on_time'] }}%</td>
                        <td class="px-6 py-4 text-navy/60">₱{{ number_format($rider['earnings']) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap {{ $rider['status'] === 'paid' ? 'bg-teal-light text-teal-dark' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($rider['status']) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
