@extends('admin.layout')

@section('title', 'User Accounts')

@section('content')

    @php
        // Style maps ginagamit both ng table rows AT ng modal (JS side)
        $roleStyles = [
            'buyer' => ['class' => 'text-sky bg-sky/10', 'icon' => 'shopping-bag', 'label' => 'Buyer'],
            'seller' => ['class' => 'text-coral bg-coral/10', 'icon' => 'store', 'label' => 'Seller'],
            'logistics' => ['class' => 'text-violet-600 bg-violet-100', 'icon' => 'truck', 'label' => 'Logistics'],
        ];

        $statusStyles = [
            'approved' => ['dot' => 'bg-mint-dark', 'text' => 'text-mint-dark', 'bg' => 'bg-mint/10', 'label' => 'Active'],
            'suspended' => ['dot' => 'bg-coral', 'text' => 'text-coral', 'bg' => 'bg-coral/10', 'label' => 'Suspended'],
        ];
    @endphp

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">
            {{ session('status') }}
        </div>
    @endif


    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <div class="mb-6">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-navy">
                    User Accounts
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage approved Buyer, Seller, and Logistics accounts.
                </p>
            </div>

        </div>

    </div>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

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

    </div>


    {{-- =========================================================
        FILTER + SORT + SEARCH
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
                href="{{ route('admin.users', ['sort' => $sort]) }}"
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
                href="{{ route('admin.users', ['filter' => 'buyers', 'sort' => $sort]) }}"
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
                href="{{ route('admin.users', ['filter' => 'sellers', 'sort' => $sort]) }}"
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
                href="{{ route('admin.users', ['filter' => 'logistics', 'sort' => $sort]) }}"
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


            {{-- Suspended --}}
            <a
                href="{{ route('admin.users', ['filter' => 'suspended', 'sort' => $sort]) }}"
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


        {{-- SORT + SEARCH --}}
        <div class="flex items-center gap-2 w-full xl:w-auto">

            {{-- SORT --}}
            <form method="GET" action="{{ route('admin.users') }}" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="text-sm
                           rounded-xl
                           border border-slate-200
                           px-3 py-2.5
                           bg-white
                           text-slate-600
                           font-medium
                           focus:outline-none
                           focus:ring-2
                           focus:ring-mint/20
                           transition"
                >
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Name (Z–A)</option>
                </select>
            </form>


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

                <input
                    type="hidden"
                    name="sort"
                    value="{{ $sort }}"
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
                                   tracking-wide
                                   w-[196px]"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($users as $user)

                        @php
                            $roleStyle = $roleStyles[$user->account_type]
                                ?? ['class' => 'text-slate-500 bg-slate-100', 'icon' => 'user', 'label' => ucfirst($user->account_type)];

                            $statusStyle = $statusStyles[$user->status]
                                ?? ['dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'bg' => 'bg-slate-100', 'label' => ucfirst($user->status)];
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

                                <div class="flex items-center justify-end gap-2 w-[196px] ml-auto">

                                    {{-- Slot 1: Suspend / Reactivate (or empty spacer) --}}
                                    <div class="w-[112px] flex justify-end">

                                        @if ($user->status === 'suspended')

                                            <button
                                                type="button"
                                                class="reactivate-btn
                                                       action-btn
                                                       h-8 w-full
                                                       inline-flex items-center justify-center gap-1.5
                                                       px-3
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-white
                                                       bg-mint-dark
                                                       hover:opacity-90
                                                       transition"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->display_name }}"
                                            >
                                                <x-lucide-rotate-ccw class="w-3.5 h-3.5" />
                                                Reactivate
                                            </button>

                                        @elseif ($user->status === 'approved')

                                            <button
                                                type="button"
                                                class="suspend-btn
                                                       action-btn
                                                       h-8 w-full
                                                       inline-flex items-center justify-center gap-1.5
                                                       px-3
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-coral
                                                       border border-coral/30
                                                       hover:bg-coral/5
                                                       transition"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->display_name }}"
                                            >
                                                <x-lucide-ban class="w-3.5 h-3.5" />
                                                Suspend
                                            </button>

                                        @endif

                                    </div>


                                    {{-- Slot 2: View — always in the same spot --}}
                                    <div class="w-[76px] flex justify-end">

                                        <button
                                            type="button"
                                            class="user-view-btn
                                                   action-btn
                                                   h-8 w-full
                                                   inline-flex items-center justify-center gap-1.5
                                                   px-3
                                                   rounded-lg
                                                   text-xs font-semibold
                                                   text-slate-500
                                                   border border-slate-200
                                                   hover:bg-slate-50
                                                   hover:text-navy
                                                   transition"
                                            data-user-id="{{ $user->id }}"
                                        >
                                            <x-lucide-eye class="w-3.5 h-3.5" />
                                            View
                                        </button>

                                    </div>

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


    {{-- =========================================================
        USER DETAILS MODAL
    ========================================================= --}}
    <div
        id="userModalOverlay"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="userModalPanel"
            class="relative w-full max-w-3xl
                   max-h-[90vh]
                   overflow-y-auto
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150"
        >

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button
                type="button"
                id="userModalClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20
                       w-10 h-10
                       rounded-full
                       bg-slate-100
                       text-navy/45
                       flex items-center justify-center
                       hover:bg-mint/10
                       hover:text-mint-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-mint/15
                       transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-12 h-12 rounded-2xl bg-mint/15 flex items-center justify-center shrink-0">
                        <span id="modalInitials" class="text-sm font-bold text-mint-dark"></span>
                    </div>

                    <div class="min-w-0">

                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">
                            ACCOUNT DETAILS
                        </p>

                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="modalName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>

                            <span
                                id="modalVerifiedBadge"
                                class="hidden inline-flex items-center gap-1 text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full shrink-0"
                            >
                                <x-lucide-badge-check class="w-3 h-3" />
                                Verified
                            </span>
                        </div>

                        <p id="modalEmail" class="text-xs text-slate-500 truncate mt-0.5"></p>

                    </div>

                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span id="modalRoleBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"></span>
                    <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full">
                        <span id="modalStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                        <span id="modalStatusLabel"></span>
                    </span>
                </div>

            </div>


            {{-- BODY --}}
            <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- LEFT COLUMN --}}
                <div class="space-y-4">

                    {{-- Contact info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Contact Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Phone</dt>
                                <dd id="modalPhone" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Address</dt>
                                <dd id="modalAddress" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Registration details --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Registration Details</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Sex</dt>
                                <dd id="modalSex" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Birthday</dt>
                                <dd id="modalBirthday" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Age</dt>
                                <dd id="modalAge" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="modalBusinessNameRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Business Name</dt>
                                <dd id="modalBusinessName" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="modalBusinessCategoryRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Line of Business</dt>
                                <dd id="modalBusinessCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Account info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Account Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Account ID</dt>
                                <dd id="modalAccountNo" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Joined</dt>
                                <dd id="modalJoined" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Last Login</dt>
                                <dd id="modalLastLogin" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Admin Notes</p>
                        <p id="modalNotes" class="text-xs text-slate-600 leading-relaxed"></p>
                    </div>

                    {{-- Reports & Flags --}}
                    <div class="bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-3">
                            <x-lucide-flag class="w-3.5 h-3.5 text-coral" />
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-coral">Reports &amp; Flags</p>
                        </div>
                        <div id="modalReports" class="space-y-2.5"></div>
                    </div>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-4">

                    {{-- Role stats --}}
                    <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div>
                            <p id="modalStatsLabel" class="text-xs text-slate-500"></p>
                            <p id="modalStatsValue" class="text-xl font-bold text-navy mt-0.5"></p>
                        </div>
                        <p id="modalStatsSub" class="text-[11px] text-slate-400 text-right max-w-[50%]"></p>
                    </div>

                    {{-- Submitted documents --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Submitted Documents</p>
                        <div id="modalDocuments" class="grid grid-cols-3 gap-3"></div>
                    </div>

                    {{-- Recent activity --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Recent Activity</p>
                        <ul id="modalActivity" class="space-y-3"></ul>
                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end">

                <button
                    type="button"
                    id="modalActionBtn"
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold transition-all duration-300"
                ></button>

            </div>

        </div>

    </div>


    {{-- =========================================================
        SUSPEND / REACTIVATE — CONFIRMATION MODAL
    ========================================================= --}}
    <div
        id="confirmModalOverlay"
        class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="confirmModalPanel"
            class="w-full max-w-sm
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150
                   p-6"
        >

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-6"></p>

            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    id="confirmCancelBtn"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmProceedBtn"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white transition-all duration-300"
                ></button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        DOCUMENT PREVIEW MODAL (para di na mag-open ng bagong tab
        pag pinindot yung "View" sa Submitted Documents)
    ========================================================= --}}
    <div
        id="docPreviewOverlay"
        class="fixed inset-0 z-[70] hidden items-center justify-center bg-navy/50 backdrop-blur-[2px] px-4"
    >

        <div
            id="docPreviewPanel"
            class="relative w-full max-w-2xl
                   max-h-[90vh]
                   overflow-y-auto
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150"
        >

            <button
                type="button"
                id="docPreviewClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20
                       w-10 h-10
                       rounded-full
                       bg-slate-100
                       text-navy/45
                       flex items-center justify-center
                       hover:bg-mint/10
                       hover:text-mint-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-mint/15
                       transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

            <div class="px-6 pt-6 pb-6">

                <p id="docPreviewLabel" class="text-sm font-semibold text-navy mb-4 pr-10 truncate"></p>

                <div
                    id="docPreviewBody"
                    class="w-full min-h-[320px] max-h-[65vh] rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden"
                ></div>

                <div class="flex justify-end mt-4">
                    <a
                        id="docPreviewOpenNewTab"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-xs font-semibold text-slate-500 hover:text-mint-dark transition inline-flex items-center gap-1.5"
                    >
                        <x-lucide-external-link class="w-3.5 h-3.5" />
                        Open in new tab
                    </a>
                </div>

            </div>

        </div>

    </div>


    {{-- Shared hidden form --}}
    <form id="actionForm" method="POST" action="">
        @csrf
    </form>


    {{-- =========================================================
        MODAL DATA + BEHAVIOR — now driven by $usersForJs from the
        controller (current page of real accounts) instead of a
        hardcoded array.
    ========================================================= --}}
    <script>
        const usersData = @json($usersForJs);

        const roleBadgeClasses = {
            buyer:     'text-sky bg-sky/10',
            seller:    'text-coral bg-coral/10',
            logistics: 'text-violet-600 bg-violet-100',
        };
        const roleLabels = { buyer: 'Buyer', seller: 'Seller', logistics: 'Logistics' };

        const statusBadge = {
            approved:  { dot: 'bg-mint-dark', text: 'text-mint-dark', bg: 'bg-mint/10',  label: 'Active' },
            suspended: { dot: 'bg-coral',     text: 'text-coral',     bg: 'bg-coral/10', label: 'Suspended' },
        };

        const suspendUrlTemplate    = "{{ route('admin.users.suspend', 0) }}";
        const reactivateUrlTemplate = "{{ route('admin.users.reactivate', 0) }}";

        function buildActionUrl(action, id) {
            const tpl = action === 'suspend' ? suspendUrlTemplate : reactivateUrlTemplate;
            return tpl.replace('/0', '/' + id);
        }

        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        /* -----------------------------------------------------------
           USER DETAILS MODAL
        ----------------------------------------------------------- */
        const overlay  = document.getElementById('userModalOverlay');
        const panel    = document.getElementById('userModalPanel');
        const closeBtn = document.getElementById('userModalClose');
        const modalActionBtn = document.getElementById('modalActionBtn');

        let currentModalUser = null;

        function openUserModal(id) {
            const user = usersData.find(u => u.id === id);
            if (!user) return;

            currentModalUser = user;

            document.getElementById('modalInitials').textContent = user.initials;
            document.getElementById('modalName').textContent = user.display_name;
            document.getElementById('modalEmail').textContent = user.email;
            document.getElementById('modalVerifiedBadge').classList.toggle('hidden', !user.verified);

            const roleBadge = document.getElementById('modalRoleBadge');
            roleBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + (roleBadgeClasses[user.account_type] || 'text-slate-500 bg-slate-100');
            roleBadge.textContent = roleLabels[user.account_type] || user.account_type;

            const sBadge = statusBadge[user.status] || { dot: 'bg-slate-400', text: 'text-slate-500', bg: 'bg-slate-100', label: user.status };
            document.getElementById('modalStatusBadge').className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            document.getElementById('modalStatusDot').className = 'w-1.5 h-1.5 rounded-full ' + sBadge.dot;
            document.getElementById('modalStatusLabel').textContent = sBadge.label;

            document.getElementById('modalPhone').textContent = user.phone || '—';
            document.getElementById('modalAddress').textContent = user.address || '—';
            document.getElementById('modalAccountNo').textContent = user.account_no;
            document.getElementById('modalJoined').textContent = user.joined;
            document.getElementById('modalLastLogin').textContent = user.last_login;

            // Stats — not backed by an orders/products/deliveries table yet,
            // so this is an honest placeholder instead of a fake number.
            if (user.stats) {
                document.getElementById('modalStatsValue').textContent = user.stats.value;
                document.getElementById('modalStatsLabel').textContent = user.stats.label;
                document.getElementById('modalStatsSub').textContent = user.stats.sub;
            } else {
                document.getElementById('modalStatsValue').textContent = '—';
                document.getElementById('modalStatsLabel').textContent = 'Activity Stats';
                document.getElementById('modalStatsSub').textContent = 'Not tracked yet in this build.';
            }

            document.getElementById('modalNotes').textContent = user.notes || 'No admin notes yet.';

            document.getElementById('modalSex').textContent = user.sex || '—';
            document.getElementById('modalBirthday').textContent = user.birthday || '—';
            document.getElementById('modalAge').textContent = user.age ?? '—';

            const businessNameRow = document.getElementById('modalBusinessNameRow');
            const businessCategoryRow = document.getElementById('modalBusinessCategoryRow');

            if (user.business_name) {
                businessNameRow.className = 'flex justify-between gap-3';
                document.getElementById('modalBusinessName').textContent = user.business_name;
            } else {
                businessNameRow.className = 'hidden justify-between gap-3';
            }

            if (user.business_category) {
                businessCategoryRow.className = 'flex justify-between gap-3';
                document.getElementById('modalBusinessCategory').textContent = user.business_category;
            } else {
                businessCategoryRow.className = 'hidden justify-between gap-3';
            }

            // Recent activity — no audit-log table yet, show an honest
            // empty state instead of fake timeline entries.
            const activityList = document.getElementById('modalActivity');
            activityList.innerHTML = '';
            if (!user.activity || user.activity.length === 0) {
                activityList.innerHTML = '<li class="text-xs text-slate-400">No activity log yet.</li>';
            } else {
                user.activity.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'flex gap-2.5';
                    li.innerHTML = `
                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-mint-dark shrink-0"></span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700">${item.label}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${item.time}</p>
                        </div>
                    `;
                    activityList.appendChild(li);
                });
            }

            const reportsWrap = document.getElementById('modalReports');
            reportsWrap.innerHTML = '';
            if (!user.reports || user.reports.length === 0) {
                reportsWrap.innerHTML = '<p class="text-xs text-slate-400">No reports or flags on file.</p>';
            } else {
                user.reports.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'flex items-start gap-2.5';
                    item.innerHTML = `
                        <span class="mt-0.5 shrink-0 text-[10px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">${r.type}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700 leading-snug">${r.description}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${r.date}</p>
                        </div>
                    `;
                    reportsWrap.appendChild(item);
                });
            }

            const docsGrid = document.getElementById('modalDocuments');
            docsGrid.innerHTML = '';
            if (!user.documents || user.documents.length === 0) {
                docsGrid.innerHTML = '<p class="col-span-3 text-xs text-slate-400">No documents uploaded.</p>';
            } else {
                user.documents.forEach(doc => {
                    // Dating <a target="_blank">, ngayon <button> na
                    // nagbubukas ng in-page preview modal (di na
                    // nag-a-navigate palabas ng page).
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'block group text-left w-full';
                    card.innerHTML = `
                        <div class="w-full aspect-square rounded-lg border border-dashed border-slate-300 bg-slate-50/60 flex flex-col items-center justify-center gap-1 text-slate-400 cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark">
                            ${docFileIconSvg}
                            <span class="text-[9px] font-medium uppercase tracking-wide">View</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1 text-center truncate transition group-hover:text-mint-dark">${doc.label}</p>
                    `;
                    card.addEventListener('click', () => openDocPreview(doc));
                    docsGrid.appendChild(card);
                });
            }

            if (user.status === 'suspended') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg> Reactivate Account';
                modalActionBtn.style.display = '';
            } else if (user.status === 'approved') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg> Suspend Account';
                modalActionBtn.style.display = '';
            } else {
                modalActionBtn.style.display = 'none';
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeUserModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.user-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openUserModal(parseInt(btn.dataset.userId, 10)));
        });

        closeBtn.addEventListener('click', closeUserModal);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeUserModal();
        });

        modalActionBtn.addEventListener('click', () => {
            if (!currentModalUser) return;
            const action = currentModalUser.status === 'suspended' ? 'reactivate' : 'suspend';
            openConfirmModal(action, currentModalUser.id, currentModalUser.display_name);
        });

        /* -----------------------------------------------------------
           SUSPEND / REACTIVATE — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay     = document.getElementById('confirmModalOverlay');
        const confirmPanel       = document.getElementById('confirmModalPanel');
        const confirmIconWrap    = document.getElementById('confirmIconWrap');
        const confirmTitle       = document.getElementById('confirmTitle');
        const confirmMessage     = document.getElementById('confirmMessage');
        const confirmProceedBtn  = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn   = document.getElementById('confirmCancelBtn');
        const actionForm         = document.getElementById('actionForm');

        const banIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>';
        const rotateIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>';

        function openConfirmModal(action, id, name) {
            actionForm.action = buildActionUrl(action, id);

            if (action === 'suspend') {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/10 text-coral';
                confirmIconWrap.innerHTML = banIconSvg;
                confirmTitle.textContent = 'Suspend this account?';
                confirmMessage.textContent = `${name} won't be able to log in or use the platform until you reactivate the account.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Suspend Account';
            } else {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/10 text-mint-dark';
                confirmIconWrap.innerHTML = rotateIconSvg;
                confirmTitle.textContent = 'Reactivate this account?';
                confirmMessage.textContent = `${name} will regain full access to their account right away.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Reactivate Account';
            }

            confirmOverlay.classList.remove('hidden');
            confirmOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                confirmPanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeConfirmModal() {
            confirmPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                confirmOverlay.classList.add('hidden');
                confirmOverlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.suspend-btn, .reactivate-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id     = parseInt(btn.dataset.userId, 10);
                const name   = btn.dataset.userName;
                const action = btn.classList.contains('suspend-btn') ? 'suspend' : 'reactivate';
                openConfirmModal(action, id, name);
            });
        });

        confirmCancelBtn.addEventListener('click', closeConfirmModal);
        confirmOverlay.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) closeConfirmModal();
        });

        confirmProceedBtn.addEventListener('click', () => {
            actionForm.submit();
        });

        /* -----------------------------------------------------------
           DOCUMENT PREVIEW MODAL — bukas sa loob mismo ng page,
           di na kailangan mag-navigate/mag-open ng bagong tab.
        ----------------------------------------------------------- */
        const docPreviewOverlay    = document.getElementById('docPreviewOverlay');
        const docPreviewPanel      = document.getElementById('docPreviewPanel');
        const docPreviewClose      = document.getElementById('docPreviewClose');
        const docPreviewLabel      = document.getElementById('docPreviewLabel');
        const docPreviewBody       = document.getElementById('docPreviewBody');
        const docPreviewOpenNewTab = document.getElementById('docPreviewOpenNewTab');

        function isImageUrl(url) {
            return /\.(jpe?g|png|gif|webp|bmp|svg)(\?.*)?$/i.test(url || '');
        }

        function isPdfUrl(url) {
            return /\.pdf(\?.*)?$/i.test(url || '');
        }

        function openDocPreview(doc) {
            const url = doc.url || '';
            docPreviewLabel.textContent = doc.label || 'Document';
            docPreviewOpenNewTab.href = url || '#';
            docPreviewBody.innerHTML = '';

            if (!url) {
                docPreviewBody.innerHTML = '<p class="text-xs text-slate-400 p-6">No file available for this document.</p>';
            } else if (isImageUrl(url)) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = doc.label || 'Document preview';
                img.className = 'max-w-full max-h-[65vh] object-contain';
                docPreviewBody.appendChild(img);
            } else if (isPdfUrl(url)) {
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.className = 'w-full h-[65vh]';
                docPreviewBody.appendChild(iframe);
            } else {
                docPreviewBody.innerHTML = '<p class="text-xs text-slate-400 p-6">Preview not available for this file type. Use "Open in new tab" instead.</p>';
            }

            docPreviewOverlay.classList.remove('hidden');
            docPreviewOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                docPreviewPanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeDocPreview() {
            docPreviewPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                docPreviewOverlay.classList.add('hidden');
                docPreviewOverlay.classList.remove('flex');
                docPreviewBody.innerHTML = '';
            }, 150);
        }

        docPreviewClose.addEventListener('click', closeDocPreview);
        docPreviewOverlay.addEventListener('click', (e) => {
            if (e.target === docPreviewOverlay) closeDocPreview();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!docPreviewOverlay.classList.contains('hidden')) closeDocPreview();
            else if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeUserModal();
        });
    </script>

@endsection