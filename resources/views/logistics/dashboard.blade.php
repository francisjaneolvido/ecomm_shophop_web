@extends('logistics.layouts')

@section('title', 'Logistics Dashboard — ShopHop')

@section('content')
{{-- Counts come only from covered ready Orders and this partner's persisted Riders and Deliveries. --}}
<h1 class="text-navy text-2xl sm:text-3xl font-bold mb-2">Logistics Dashboard</h1>
<p class="text-navy/55 text-sm mb-6">Current operational counts for your partner.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach ($stats as $stat)
        <div class="rounded-2xl border border-gray-border bg-white p-5">
            <p class="text-sm text-navy/60">{{ $stat['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $stat['value'] }}</p>
        </div>
    @endforeach
</div>
<div class="mt-6 flex gap-3">
    <a href="{{ route('logistics.deliveries.board') }}" class="rounded-full bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-teal">Delivery Board</a>
    <a href="{{ route('logistics.riders.index') }}" class="rounded-full border border-navy px-5 py-2 text-sm font-semibold text-navy">Riders</a>
</div>
@endsection
