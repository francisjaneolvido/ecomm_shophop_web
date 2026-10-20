@extends('hub.layouts.console')
@section('title', 'Dashboard — Province Hub')
@section('content')
@php
    $stats = [
        ['Parcels in hub', '248', 'lucide-package', 'text-navy'],
        ['Inbound today', '96', 'lucide-arrow-down', 'text-teal'],
        ['Outbound today', '71', 'lucide-arrow-up', 'text-sky'],
        ['Delayed', '5', 'lucide-clock', 'text-yellow'],
    ];
    $stations = [
        ['Los Baños Station', 'Los Baños', 82, 12],
        ['Calamba Station', 'Calamba', 64, 18],
        ['Santa Rosa Station', 'Santa Rosa', 91, 15],
        ['San Pablo Station', 'San Pablo', 38, 9],
    ];
    $activity = [
        ['Truck LGN-204 arrived', '42 parcels · Main hub', '10 min ago'],
        ['Parcels dispatched to Calamba Station', '27 parcels', '35 min ago'],
        ['Parcel SH-100245 marked delayed', 'Wrong route', '1 hr ago'],
        ['Santa Rosa Station reached 90% capacity', 'Capacity alert', '2 hrs ago'],
    ];
@endphp
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <p class="eyebrow">LAGUNA PROVINCE HUB</p>
        <h1 class="title">Dashboard</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/60">Overview ng parcels at station hubs sa ilalim ng iyong province.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as [$label, $value, $icon, $color])
            <div class="sh-surface flex items-center justify-between p-5">
                <div>
                    <p class="text-xs text-navy/50">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold {{ $color }}">{{ $value }}</p>
                </div>
                @svg($icon, 'h-8 w-8 text-navy/20')
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="sh-surface overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-border p-4 sm:p-5">
                <h2 class="text-base font-bold">Station hub capacity</h2>
                <a href="{{ route('logistics.hub.station-hubs') }}" class="sh-btn-soft text-xs">View all</a>
            </div>
            <div class="divide-y divide-gray-border">
                @foreach($stations as [$name, $city, $load, $riders])
                    <div class="p-4 sm:px-5">
                        <div class="flex items-center justify-between text-sm">
                            <p class="font-bold">{{ $name }}</p>
                            <p class="text-xs text-navy/50">{{ $riders }} riders</p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-bg">
                            <div class="h-full rounded-full {{ $load >= 90 ? 'bg-yellow' : 'bg-teal' }}" style="width: {{ $load }}%"></div>
                        </div>
                        <p class="mt-1 text-[11px] text-navy/50">{{ $city }} · {{ $load }}% capacity</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="sh-surface overflow-hidden">
            <div class="border-b border-gray-border p-4 sm:p-5"><h2 class="text-base font-bold">Recent activity</h2></div>
            <ul class="divide-y divide-gray-border">
                @foreach($activity as [$title, $sub, $time])
                    <li class="flex items-start justify-between gap-3 p-4 sm:px-5">
                        <div>
                            <p class="text-sm font-semibold">{{ $title }}</p>
                            <p class="text-xs text-navy/50">{{ $sub }}</p>
                        </div>
                        <span class="whitespace-nowrap text-[11px] text-navy/40">{{ $time }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</div>
@endsection