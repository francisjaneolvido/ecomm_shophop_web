@extends('admin.layout')

@section('title', 'User Accounts')

@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <div class="mb-6">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-mint-dark mb-1">
                    Account Directory
                </p>

                <h1 class="text-2xl font-bold text-navy">
                    User Accounts
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage approved Buyer, Seller, Logistics, and Rider accounts.
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-400">
                <x-lucide-users class="w-4 h-4 text-mint-dark" />
                <span>Platform user management</span>
            </div>

        </div>

    </div>


    {{-- =========================================================
        SESSION STATUS
    ========================================================= --}}
    @if (session('status'))

        <div
            class="mb-5
                   flex items-start gap-3
                   rounded-xl
                   border border-mint/30
                   bg-mint/10
                   px-4 py-3"
        >
            <x-lucide-circle-check
                class="w-4 h-4 text-mint-dark mt-0.5 shrink-0"
            />

            <p class="text-sm text-mint-dark font-medium">
                {{ session('status') }}
            </p>
        </div>

    @endif


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">

        {{-- All Users --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Total Users
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['all'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-navy/10 text-navy
                           flex items-center justify-center"
                >
                    <x-lucide-users class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Buyers --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Buyers
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['buyers'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-sky/10 text-sky
                           flex items-center justify-center"
                >
                    <x-lucide-shopping-bag class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Sellers --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Sellers
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['sellers'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-coral/10 text-coral
                           flex items-center justify-center"
                >
                    <x-lucide-store class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Logistics --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Logistics
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['logistics'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-violet-100 text-violet-600
                           flex items-center justify-center"
                >
                    <x-lucide-truck class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Riders --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Riders
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['riders'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-amber-100 text-amber-600
                           flex items-center justify-center"
                >
                    <x-lucide-bike class="w-5 h-5" />
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        FILTER + SEARCH
    ========================================================= --}}
    <div
        class="flex flex-col xl:flex-row
               xl:items-center
               xl:justify-between
               gap-4
               mb-5"
    >

        {{-- FILTER TABS --}}
        <div class="flex items-center gap-1 flex-wrap">

            {{-- All --}}
            <a
                href="{{ route('admin.users') }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'all'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                All

                <span class="ml-1 opacity-70">
                    ({{ $counts['all'] ?? 0 }})
                </span>
            </a>


            {{-- Buyers --}}
            <a
                href="{{ route('admin.users', ['filter' => 'buyers']) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'buyers'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Buyers

                <span class="ml-1 opacity-70">
                    ({{ $counts['buyers'] ?? 0 }})
                </span>
            </a>


            {{-- Sellers --}}
            <a
                href="{{ route('admin.users', ['filter' => 'sellers']) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'sellers'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Sellers

                <span class="ml-1 opacity-70">
                    ({{ $counts['sellers'] ?? 0 }})
                </span>
            </a>


            {{-- Logistics --}}
            <a
                href="{{ route('admin.users', ['filter' => 'logistics']) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'logistics'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Logistics

                <span class="ml-1 opacity-70">
                    ({{ $counts['logistics'] ?? 0 }})
                </span>
            </a>


            {{-- Riders --}}
            <a
                href="{{ route('admin.users', ['filter' => 'riders']) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'riders'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Riders

                <span class="ml-1 opacity-70">
                    ({{ $counts['riders'] ?? 0 }})
                </span>
            </a>


            {{-- Suspended --}}
            <a
                href="{{ route('admin.users', ['filter' => 'suspended']) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'suspended'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Suspended

                <span class="ml-1 opacity-70">
                    ({{ $counts['suspended'] ?? 0 }})
                </span>
            </a>

        </div>


        {{-- SEARCH --}}
        <form
            method="GET"
            action="{{ route('admin.users') }}"
            class="relative w-full xl:w-72"
        >

            <input
                type="hidden"
                name="filter"
                value="{{ $filter }}"
            >


            <x-lucide-search
                class="w-4 h-4
                       absolute left-3 top-1/2
                       -translate-y-1/2
                       text-slate-400"
            />


            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by name or email..."
                class="w-full
                       pl-9 pr-4
                       py-2.5
                       text-sm
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-navy
                       placeholder:text-slate-400
                       focus:border-mint
                       focus:outline-none
                       focus:ring-2
                       focus:ring-mint/20
                       transition"
            >

        </form>

    </div>


    {{-- =========================================================
        USER ACCOUNTS TABLE
    ========================================================= --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               overflow-hidden"
    >

        {{-- TABLE HEADER / INFO --}}
        <div
            class="flex items-center justify-between
                   px-5 py-4
                   border-b border-slate-100"
        >

            <div>
                <h2 class="font-semibold text-navy text-sm">
                    User Directory
                </h2>

                <p class="text-[11px] text-slate-400 mt-0.5">
                    Approved platform accounts and their current status.
                </p>
            </div>

            <span class="text-xs text-slate-400">
                {{ $users->total() }} total
            </span>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="bg-slate-50 border-b border-slate-100">

                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            User
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Role
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Status
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Joined
                        </th>


                        <th
                            class="text-right
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($users as $user)

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | ROLE STYLES
                            |--------------------------------------------------------------------------
                            */

                            $roleStyles = [
                                'buyer' => [
                                    'class' => 'text-sky bg-sky/10',
                                    'icon' => 'shopping-bag',
                                ],

                                'seller' => [
                                    'class' => 'text-coral bg-coral/10',
                                    'icon' => 'store',
                                ],

                                'logistics' => [
                                    'class' => 'text-violet-600 bg-violet-100',
                                    'icon' => 'truck',
                                ],

                                'rider' => [
                                    'class' => 'text-amber-600 bg-amber-100',
                                    'icon' => 'bike',
                                ],
                            ];


                            $roleStyle =
                                $roleStyles[$user->account_type]
                                ?? [
                                    'class' => 'text-slate-500 bg-slate-100',
                                    'icon' => 'user',
                                ];


                            /*
                            |--------------------------------------------------------------------------
                            | ACCOUNT STATUS
                            |--------------------------------------------------------------------------
                            |
                            | Existing backend mo currently uses "approved".
                            | Display lang natin ito bilang "Active".
                            |
                            */

                            $statusStyles = [
                                'approved' => [
                                    'dot' => 'bg-mint-dark',
                                    'text' => 'text-mint-dark',
                                    'label' => 'Active',
                                ],

                                'active' => [
                                    'dot' => 'bg-mint-dark',
                                    'text' => 'text-mint-dark',
                                    'label' => 'Active',
                                ],

                                'suspended' => [
                                    'dot' => 'bg-coral',
                                    'text' => 'text-coral',
                                    'label' => 'Suspended',
                                ],

                                'disabled' => [
                                    'dot' => 'bg-slate-400',
                                    'text' => 'text-slate-500',
                                    'label' => 'Disabled',
                                ],
                            ];


                            $statusStyle =
                                $statusStyles[$user->status]
                                ?? [
                                    'dot' => 'bg-slate-400',
                                    'text' => 'text-slate-500',
                                    'label' => ucfirst($user->status),
                                ];
                        @endphp


                        <tr class="hover:bg-slate-50/60 transition">

                            {{-- USER --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-10 h-10
                                               rounded-full
                                               bg-mint/15
                                               flex items-center justify-center
                                               shrink-0"
                                    >
                                        <span class="text-xs font-bold text-mint-dark">
                                            {{ $user->initials }}
                                        </span>
                                    </div>


                                    <div class="min-w-0">

                                        <p
                                            class="font-semibold
                                                   text-navy
                                                   truncate"
                                        >
                                            {{ $user->display_name }}
                                        </p>


                                        <p
                                            class="text-xs
                                                   text-slate-500
                                                   truncate
                                                   mt-0.5"
                                        >
                                            {{ $user->email }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- ROLE --}}
                            <td class="px-5 py-4">

                                <span
                                    class="inline-flex
                                           items-center gap-1.5
                                           text-xs
                                           font-semibold
                                           px-2.5 py-1
                                           rounded-full
                                           {{ $roleStyle['class'] }}"
                                >

                                    <x-dynamic-component
                                        :component="'lucide-' . $roleStyle['icon']"
                                        class="w-3.5 h-3.5"
                                    />

                                    {{ ucfirst($user->account_type) }}

                                </span>

                            </td>


                            {{-- STATUS --}}
                            <td class="px-5 py-4">

                                <span
                                    class="inline-flex
                                           items-center gap-1.5
                                           text-xs font-semibold
                                           {{ $statusStyle['text'] }}"
                                >

                                    <span
                                        class="w-1.5 h-1.5
                                               rounded-full
                                               {{ $statusStyle['dot'] }}"
                                    ></span>

                                    {{ $statusStyle['label'] }}

                                </span>

                            </td>


                            {{-- JOINED --}}
                            <td class="px-5 py-4">

                                <div>

                                    <p class="text-sm text-slate-600">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </p>

                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>

                                </div>

                            </td>


                            {{-- ACTIONS --}}
                            <td class="px-5 py-4">

                                <div
                                    class="flex items-center
                                           justify-end
                                           gap-2"
                                >

                                    {{-- View --}}
<a
    href="{{ route('admin.users.show', $user) }}"
    class="inline-flex
           items-center gap-1.5
           px-3 py-1.5
           rounded-lg
           text-xs font-semibold
           text-slate-500
           border border-slate-200
           hover:bg-slate-50
           hover:text-navy
           transition"
>
    <x-lucide-eye class="w-3.5 h-3.5" />

    View
</a>


                                    {{-- Suspended → Reactivate --}}
                                    @if ($user->status === 'suspended')

                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.reactivate', $user) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex
                                                       items-center gap-1.5
                                                       px-3 py-1.5
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-white
                                                       bg-mint-dark
                                                       hover:opacity-90
                                                       transition"
                                            >
                                                <x-lucide-rotate-ccw class="w-3.5 h-3.5" />

                                                Reactivate
                                            </button>

                                        </form>


                                    {{-- Active / Approved → Suspend --}}
                                    @elseif (
                                        $user->status === 'approved'
                                        || $user->status === 'active'
                                    )

                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.suspend', $user) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex
                                                       items-center gap-1.5
                                                       px-3 py-1.5
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-coral
                                                       border border-coral/30
                                                       hover:bg-coral/5
                                                       transition"
                                            >
                                                <x-lucide-ban class="w-3.5 h-3.5" />

                                                Suspend
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-5 py-14 text-center"
                            >

                                <div
                                    class="w-12 h-12
                                           mx-auto
                                           rounded-2xl
                                           bg-slate-100
                                           text-slate-400
                                           flex items-center justify-center"
                                >
                                    <x-lucide-users-round class="w-5 h-5" />
                                </div>


                                <p
                                    class="text-sm
                                           font-semibold
                                           text-navy
                                           mt-3"
                                >
                                    No accounts found
                                </p>


                                <p
                                    class="text-xs
                                           text-slate-400
                                           mt-1"
                                >
                                    Try changing the filter or search keyword.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================================================
            PAGINATION
        ========================================================= --}}
        @if ($users->hasPages())

            <div
                class="px-5 py-4
                       border-t border-slate-100"
            >
                {{ $users->withQueryString()->links() }}
            </div>

        @endif

    </div>

@endsection