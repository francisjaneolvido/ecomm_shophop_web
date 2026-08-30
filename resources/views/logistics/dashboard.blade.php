@extends('logistics.layout')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')


@php

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DATA
    |--------------------------------------------------------------------------
    | Defaults muna para hindi mag-error habang hindi pa fully connected
    | sa database ang Logistics dashboard.
    */

    $stats = array_merge([
        'total_riders' => 0,
        'pending_riders' => 0,
        'active_deliveries' => 0,
        'completed_today' => 0,
    ], $stats ?? []);


    $recentRiderApplications =
        $recentRiderApplications
        ?? collect([
            [
                'name' => 'Juan Dela Cruz',
                'initials' => 'JD',
                'vehicle' => 'Motorcycle',
                'applied' => '8 minutes ago',
            ],
            [
                'name' => 'Pedro Santos',
                'initials' => 'PS',
                'vehicle' => 'Motorcycle',
                'applied' => '25 minutes ago',
            ],
            [
                'name' => 'Mark Reyes',
                'initials' => 'MR',
                'vehicle' => 'Van',
                'applied' => '1 hour ago',
            ],
        ]);


    $recentDeliveries =
        $recentDeliveries
        ?? collect([
            [
                'reference' => 'SHP-250829-001',
                'customer' => 'Maria Santos',
                'rider' => 'Juan Dela Cruz',
                'status' => 'In Transit',
            ],
            [
                'reference' => 'SHP-250829-002',
                'customer' => 'Carlo Reyes',
                'rider' => 'Pedro Santos',
                'status' => 'Assigned',
            ],
            [
                'reference' => 'SHP-250829-003',
                'customer' => 'Angela Cruz',
                'rider' => 'Mark Reyes',
                'status' => 'Delivered',
            ],
        ]);

@endphp


{{-- =========================================================
    WELCOME HEADER
========================================================= --}}
<div
    class="flex flex-col
           lg:flex-row
           lg:items-end
           lg:justify-between
           gap-4
           mb-6"
>

    <div>

        <p
            class="text-[11px]
                   font-semibold
                   uppercase
                   tracking-[0.14em]
                   text-teal-dark
                   mb-1"
        >
            Logistics Overview
        </p>


        <h1
            class="text-2xl
                   sm:text-3xl
                   font-bold
                   text-navy"
        >
            Welcome back,
            {{ $logisticsName ?? 'Logistics Partner' }}
        </h1>


        <p
            class="text-sm
                   text-slate-500
                   mt-1"
        >
            Manage your riders and monitor ShopHop deliveries.
        </p>

    </div>


    <div class="flex items-center gap-2">

        <a
            href="{{ route('logistics.riders.index') }}"
            class="inline-flex
                   items-center gap-2
                   px-4 py-2.5
                   rounded-xl
                   bg-navy
                   text-white
                   text-xs
                   font-semibold
                   hover:bg-navy-light
                   transition"
        >
            <x-lucide-bike class="w-4 h-4" />

            Manage Riders
        </a>


        <a
            href="{{ route('logistics.deliveries.board') }}"
            class="inline-flex
                   items-center gap-2
                   px-4 py-2.5
                   rounded-xl
                   bg-teal
                   text-white
                   text-xs
                   font-semibold
                   hover:bg-teal-dark
                   transition"
        >
            <x-lucide-package class="w-4 h-4" />

            Deliveries
        </a>

    </div>

</div>


{{-- =========================================================
    STAT CARDS
========================================================= --}}
<div
    class="grid
           grid-cols-1
           sm:grid-cols-2
           xl:grid-cols-4
           gap-4
           mb-6"
>

    {{-- Riders --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               p-5"
    >

        <div
            class="flex
                   items-start
                   justify-between
                   gap-4"
        >

            <div>

                <p
                    class="text-xs
                           font-medium
                           text-slate-400"
                >
                    Total Riders
                </p>

                <p
                    class="text-2xl
                           font-bold
                           text-navy
                           mt-1"
                >
                    {{ $stats['total_riders'] }}
                </p>

                <p
                    class="text-[10px]
                           text-slate-400
                           mt-1"
                >
                    Approved riders
                </p>

            </div>


            <div
                class="w-11 h-11
                       rounded-xl
                       bg-teal/10
                       text-teal-dark
                       flex items-center
                       justify-center"
            >
                <x-lucide-bike class="w-5 h-5" />
            </div>

        </div>

    </div>


    {{-- Pending --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               p-5"
    >

        <div
            class="flex
                   items-start
                   justify-between
                   gap-4"
        >

            <div>

                <p
                    class="text-xs
                           font-medium
                           text-slate-400"
                >
                    Rider Applications
                </p>

                <p
                    class="text-2xl
                           font-bold
                           text-navy
                           mt-1"
                >
                    {{ $stats['pending_riders'] }}
                </p>

                <p
                    class="text-[10px]
                           text-amber-600
                           mt-1
                           font-medium"
                >
                    Waiting for review
                </p>

            </div>


            <div
                class="w-11 h-11
                       rounded-xl
                       bg-amber-100
                       text-amber-600
                       flex items-center
                       justify-center"
            >
                <x-lucide-user-round-plus class="w-5 h-5" />
            </div>

        </div>

    </div>


    {{-- Active Deliveries --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               p-5"
    >

        <div
            class="flex
                   items-start
                   justify-between
                   gap-4"
        >

            <div>

                <p
                    class="text-xs
                           font-medium
                           text-slate-400"
                >
                    Active Deliveries
                </p>

                <p
                    class="text-2xl
                           font-bold
                           text-navy
                           mt-1"
                >
                    {{ $stats['active_deliveries'] }}
                </p>

                <p
                    class="text-[10px]
                           text-sky
                           mt-1
                           font-medium"
                >
                    Currently in progress
                </p>

            </div>


            <div
                class="w-11 h-11
                       rounded-xl
                       bg-sky/10
                       text-sky
                       flex items-center
                       justify-center"
            >
                <x-lucide-truck class="w-5 h-5" />
            </div>

        </div>

    </div>


    {{-- Completed --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               p-5"
    >

        <div
            class="flex
                   items-start
                   justify-between
                   gap-4"
        >

            <div>

                <p
                    class="text-xs
                           font-medium
                           text-slate-400"
                >
                    Completed Today
                </p>

                <p
                    class="text-2xl
                           font-bold
                           text-navy
                           mt-1"
                >
                    {{ $stats['completed_today'] }}
                </p>

                <p
                    class="text-[10px]
                           text-teal-dark
                           mt-1
                           font-medium"
                >
                    Successful deliveries
                </p>

            </div>


            <div
                class="w-11 h-11
                       rounded-xl
                       bg-teal-light
                       text-teal-dark
                       flex items-center
                       justify-center"
            >
                <x-lucide-circle-check-big class="w-5 h-5" />
            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    MAIN GRID
========================================================= --}}
<div
    class="grid
           xl:grid-cols-[1fr_0.85fr]
           gap-5
           mb-6"
>

    {{-- =====================================================
        RIDER APPLICATIONS
    ===================================================== --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               overflow-hidden"
    >

        <div
            class="flex items-center
                   justify-between
                   gap-4
                   px-5 py-4
                   border-b border-slate-100"
        >

            <div>

                <h2
                    class="text-sm
                           font-semibold
                           text-navy"
                >
                    Rider Applications
                </h2>

                <p
                    class="text-[10px]
                           text-slate-400
                           mt-0.5"
                >
                    Riders requesting to join your logistics team.
                </p>

            </div>


            <a
                href="{{ route('logistics.riders.index') }}"
                class="inline-flex
                       items-center gap-1
                       text-[10px]
                       font-semibold
                       text-teal-dark
                       hover:text-navy
                       transition"
            >
                View all

                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>

        </div>


        <div class="divide-y divide-slate-100">

            @forelse ($recentRiderApplications as $application)

                <div
                    class="flex
                           items-center
                           justify-between
                           gap-4
                           px-5 py-4
                           hover:bg-slate-50/60
                           transition"
                >

                    <div
                        class="flex
                               items-center
                               gap-3
                               min-w-0"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/10
                                   flex items-center
                                   justify-center
                                   shrink-0"
                        >
                            <span
                                class="text-xs
                                       font-bold
                                       text-teal-dark"
                            >
                                {{ $application['initials'] }}
                            </span>
                        </div>


                        <div class="min-w-0">

                            <p
                                class="text-xs
                                       font-semibold
                                       text-navy
                                       truncate"
                            >
                                {{ $application['name'] }}
                            </p>

                            <p
                                class="text-[10px]
                                       text-slate-400
                                       mt-0.5"
                            >
                                {{ $application['vehicle'] }}
                                ·
                                {{ $application['applied'] }}
                            </p>

                        </div>

                    </div>


                    <span
                        class="inline-flex
                               items-center
                               gap-1.5
                               px-2.5 py-1
                               rounded-full
                               bg-amber-100
                               text-amber-600
                               text-[9px]
                               font-semibold
                               shrink-0"
                    >

                        <span
                            class="w-1.5 h-1.5
                                   rounded-full
                                   bg-amber-500"
                        ></span>

                        Pending

                    </span>

                </div>

            @empty

                <div class="px-5 py-10 text-center">

                    <x-lucide-user-check
                        class="w-7 h-7
                               text-slate-300
                               mx-auto"
                    />

                    <p
                        class="text-xs
                               font-semibold
                               text-navy
                               mt-2"
                    >
                        No pending applications
                    </p>

                </div>

            @endforelse

        </div>

    </div>


    {{-- =====================================================
        QUICK ACTIONS
    ===================================================== --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               p-5"
    >

        <div class="mb-4">

            <h2
                class="text-sm
                       font-semibold
                       text-navy"
            >
                Quick Actions
            </h2>

            <p
                class="text-[10px]
                       text-slate-400
                       mt-0.5"
            >
                Common logistics management tasks.
            </p>

        </div>


        <div class="grid sm:grid-cols-2 xl:grid-cols-1 gap-3">

            {{-- Riders --}}
            <a
                href="{{ route('logistics.riders.index') }}"
                class="group
                       flex items-center
                       gap-3
                       p-3.5
                       rounded-xl
                       border border-slate-200
                       hover:border-teal/30
                       hover:bg-teal-light/40
                       transition"
            >

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-teal/10
                           text-teal-dark
                           flex items-center
                           justify-center
                           shrink-0"
                >
                    <x-lucide-users class="w-4 h-4" />
                </div>


                <div class="min-w-0 flex-1">

                    <p
                        class="text-xs
                               font-semibold
                               text-navy"
                    >
                        Manage Riders
                    </p>

                    <p
                        class="text-[9px]
                               text-slate-400
                               mt-0.5"
                    >
                        Review applications and rider accounts.
                    </p>

                </div>


                <x-lucide-chevron-right
                    class="w-4 h-4
                           text-slate-300
                           group-hover:text-teal-dark"
                />

            </a>


            {{-- Deliveries --}}
            <a
                href="{{ route('logistics.deliveries.board') }}"
                class="group
                       flex items-center
                       gap-3
                       p-3.5
                       rounded-xl
                       border border-slate-200
                       hover:border-sky/30
                       hover:bg-sky/5
                       transition"
            >

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-sky/10
                           text-sky
                           flex items-center
                           justify-center
                           shrink-0"
                >
                    <x-lucide-package-check class="w-4 h-4" />
                </div>


                <div class="min-w-0 flex-1">

                    <p
                        class="text-xs
                               font-semibold
                               text-navy"
                    >
                        Delivery Board
                    </p>

                    <p
                        class="text-[9px]
                               text-slate-400
                               mt-0.5"
                    >
                        Assign and monitor deliveries.
                    </p>

                </div>


                <x-lucide-chevron-right
                    class="w-4 h-4
                           text-slate-300
                           group-hover:text-sky"
                />

            </a>


            {{-- Reports --}}
            <a
                href="{{ route('logistics.reports.index') }}"
                class="group
                       flex items-center
                       gap-3
                       p-3.5
                       rounded-xl
                       border border-slate-200
                       hover:border-violet-300
                       hover:bg-violet-50
                       transition"
            >

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-violet-100
                           text-violet-600
                           flex items-center
                           justify-center
                           shrink-0"
                >
                    <x-lucide-chart-no-axes-combined class="w-4 h-4" />
                </div>


                <div class="min-w-0 flex-1">

                    <p
                        class="text-xs
                               font-semibold
                               text-navy"
                    >
                        View Reports
                    </p>

                    <p
                        class="text-[9px]
                               text-slate-400
                               mt-0.5"
                    >
                        Review logistics performance.
                    </p>

                </div>


                <x-lucide-chevron-right
                    class="w-4 h-4
                           text-slate-300
                           group-hover:text-violet-600"
                />

            </a>

        </div>

    </div>

</div>


{{-- =========================================================
    RECENT DELIVERIES
========================================================= --}}
<div
    class="bg-white
           rounded-2xl
           border border-slate-200
           overflow-hidden"
>

    <div
        class="flex
               items-center
               justify-between
               gap-4
               px-5 py-4
               border-b border-slate-100"
    >

        <div>

            <h2
                class="text-sm
                       font-semibold
                       text-navy"
            >
                Recent Deliveries
            </h2>

            <p
                class="text-[10px]
                       text-slate-400
                       mt-0.5"
            >
                Latest delivery activity handled by your riders.
            </p>

        </div>


        <a
            href="{{ route('logistics.deliveries.board') }}"
            class="text-[10px]
                   font-semibold
                   text-teal-dark
                   hover:text-navy"
        >
            View delivery board
        </a>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>

                <tr
                    class="bg-slate-50
                           border-b border-slate-100"
                >

                    <th
                        class="text-left
                               px-5 py-3
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-wide
                               text-slate-400"
                    >
                        Reference
                    </th>

                    <th
                        class="text-left
                               px-5 py-3
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-wide
                               text-slate-400"
                    >
                        Customer
                    </th>

                    <th
                        class="text-left
                               px-5 py-3
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-wide
                               text-slate-400"
                    >
                        Rider
                    </th>

                    <th
                        class="text-left
                               px-5 py-3
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-wide
                               text-slate-400"
                    >
                        Status
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-slate-100">

                @foreach ($recentDeliveries as $delivery)

                    @php

                        $deliveryStatus = match (
                            $delivery['status']
                        ) {

                            'Delivered' =>
                                'bg-teal/10 text-teal-dark',

                            'In Transit' =>
                                'bg-sky/10 text-sky',

                            'Assigned' =>
                                'bg-amber-100 text-amber-600',

                            default =>
                                'bg-slate-100 text-slate-500',

                        };

                    @endphp


                    <tr
                        class="hover:bg-slate-50/60
                               transition"
                    >

                        <td
                            class="px-5 py-4
                                   text-xs
                                   font-semibold
                                   text-navy"
                        >
                            {{ $delivery['reference'] }}
                        </td>

                        <td
                            class="px-5 py-4
                                   text-xs
                                   text-slate-600"
                        >
                            {{ $delivery['customer'] }}
                        </td>

                        <td
                            class="px-5 py-4
                                   text-xs
                                   text-slate-600"
                        >
                            {{ $delivery['rider'] }}
                        </td>

                        <td class="px-5 py-4">

                            <span
                                class="inline-flex
                                       px-2.5 py-1
                                       rounded-full
                                       text-[9px]
                                       font-semibold
                                       {{ $deliveryStatus }}"
                            >
                                {{ $delivery['status'] }}
                            </span>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection