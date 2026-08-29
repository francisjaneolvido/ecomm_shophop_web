@extends('admin.layout')

@section('title', 'Account Registrations')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DEMO REGISTRATION DATA
    |--------------------------------------------------------------------------
    | Temporary UI data muna ito.
    |
    | Later, puwede itong palitan ng:
    | $registrations galing sa AdminController / RegistrationController.
    |--------------------------------------------------------------------------
    */

    $registrations = collect([
        [
            'id' => 1,
            'name' => 'Francis Jane Olvido',
            'email' => 'olvidofrancisjane2003@gmail.com',
            'role' => 'buyer',
            'status' => 'pending',
            'initials' => 'FJ',
            'submitted' => '5 minutes ago',
            'document' => 'National ID',
        ],
        [
            'id' => 2,
            'name' => "Aling Nena's Store",
            'email' => 'nena.store@email.com',
            'role' => 'seller',
            'status' => 'pending',
            'initials' => 'AN',
            'submitted' => '18 minutes ago',
            'document' => 'Business Permit & Valid ID',
        ],
        [
            'id' => 3,
            'name' => 'QuickHop Logistics',
            'email' => 'quickhop@email.com',
            'role' => 'logistics',
            'status' => 'pending',
            'initials' => 'QL',
            'submitted' => '40 minutes ago',
            'document' => 'Company Documents',
        ],
        [
            'id' => 4,
            'name' => 'TechHub PH',
            'email' => 'techhubph@email.com',
            'role' => 'seller',
            'status' => 'approved',
            'initials' => 'TH',
            'submitted' => '2 hours ago',
            'document' => 'Business Permit & Valid ID',
        ],
        [
            'id' => 5,
            'name' => 'Maria Santos',
            'email' => 'maria.santos@email.com',
            'role' => 'buyer',
            'status' => 'approved',
            'initials' => 'MS',
            'submitted' => 'Yesterday',
            'document' => 'Driver\'s License',
        ],
        [
            'id' => 6,
            'name' => 'MetroGo Delivery Services',
            'email' => 'metrogo@email.com',
            'role' => 'logistics',
            'status' => 'rejected',
            'initials' => 'MD',
            'submitted' => '2 days ago',
            'document' => 'Company Documents',
        ],
    ]);

    $pendingCount = $registrations->where('status', 'pending')->count();
    $approvedCount = $registrations->where('status', 'approved')->count();
    $rejectedCount = $registrations->where('status', 'rejected')->count();

    $buyerCount = $registrations->where('role', 'buyer')->count();
    $sellerCount = $registrations->where('role', 'seller')->count();
    $logisticsCount = $registrations->where('role', 'logistics')->count();
@endphp


{{-- =========================================================
    PAGE HEADER
========================================================= --}}
<div class="mb-6">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-mint-dark mb-1">
                Registration Review
            </p>

            <h1 class="text-2xl font-bold text-navy">
                Account Registrations
            </h1>

            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Review and verify new Buyer, Seller, and Logistics account applications.
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-400">
            <x-lucide-shield-check class="w-4 h-4 text-mint-dark" />
            <span>Identity and account verification</span>
        </div>

    </div>

</div>


{{-- =========================================================
    SUMMARY CARDS
========================================================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    {{-- Pending --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4">

        <div class="flex items-center justify-between gap-3">

            <div>
                <p class="text-xs text-slate-400 font-medium">
                    Pending Review
                </p>

                <p class="text-2xl font-bold text-navy mt-1">
                    {{ $pendingCount }}
                </p>
            </div>

            <div
                class="w-10 h-10 rounded-xl
                       bg-amber-100 text-amber-600
                       flex items-center justify-center"
            >
                <x-lucide-clock-3 class="w-5 h-5" />
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
                    {{ $buyerCount }}
                </p>
            </div>

            <div
                class="w-10 h-10 rounded-xl
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
                    {{ $sellerCount }}
                </p>
            </div>

            <div
                class="w-10 h-10 rounded-xl
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
                    {{ $logisticsCount }}
                </p>
            </div>

            <div
                class="w-10 h-10 rounded-xl
                       bg-violet-100 text-violet-600
                       flex items-center justify-center"
            >
                <x-lucide-truck class="w-5 h-5" />
            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    FILTER TOOLBAR
========================================================= --}}
<div
    class="bg-white
           border border-slate-200
           rounded-2xl
           p-3
           mb-5"
>

    <div
        class="flex flex-col xl:flex-row
               xl:items-center
               xl:justify-between
               gap-4"
    >

        {{-- Status Filters --}}
        <div class="flex items-center gap-1 flex-wrap">

            <button
                type="button"
                data-status-filter="pending"
                class="registration-status-filter
                       px-4 py-2 rounded-xl
                       text-xs sm:text-sm font-semibold
                       bg-navy text-white
                       transition"
            >
                Pending

                <span class="ml-1 opacity-70">
                    ({{ $pendingCount }})
                </span>
            </button>


            <button
                type="button"
                data-status-filter="approved"
                class="registration-status-filter
                       px-4 py-2 rounded-xl
                       text-xs sm:text-sm font-semibold
                       text-slate-500
                       hover:bg-slate-100
                       transition"
            >
                Approved

                <span class="ml-1 opacity-70">
                    ({{ $approvedCount }})
                </span>
            </button>


            <button
                type="button"
                data-status-filter="rejected"
                class="registration-status-filter
                       px-4 py-2 rounded-xl
                       text-xs sm:text-sm font-semibold
                       text-slate-500
                       hover:bg-slate-100
                       transition"
            >
                Rejected

                <span class="ml-1 opacity-70">
                    ({{ $rejectedCount }})
                </span>
            </button>

        </div>


        <div
            class="flex flex-col sm:flex-row
                   sm:items-center
                   gap-3"
        >

            {{-- Role Filters --}}
            <div
                class="flex items-center
                       gap-1
                       p-1
                       rounded-xl
                       bg-slate-50"
            >

                <button
                    type="button"
                    data-role-filter="all"
                    class="registration-role-filter
                           px-3 py-1.5
                           rounded-lg
                           text-xs font-semibold
                           bg-white text-navy
                           shadow-sm
                           transition"
                >
                    All
                </button>


                <button
                    type="button"
                    data-role-filter="buyer"
                    class="registration-role-filter
                           px-3 py-1.5
                           rounded-lg
                           text-xs font-semibold
                           text-slate-500
                           hover:bg-white
                           transition"
                >
                    Buyers
                </button>


                <button
                    type="button"
                    data-role-filter="seller"
                    class="registration-role-filter
                           px-3 py-1.5
                           rounded-lg
                           text-xs font-semibold
                           text-slate-500
                           hover:bg-white
                           transition"
                >
                    Sellers
                </button>


                <button
                    type="button"
                    data-role-filter="logistics"
                    class="registration-role-filter
                           px-3 py-1.5
                           rounded-lg
                           text-xs font-semibold
                           text-slate-500
                           hover:bg-white
                           transition"
                >
                    Logistics
                </button>

            </div>


            {{-- Search --}}
            <div class="relative w-full sm:w-64">

                <x-lucide-search
                    class="absolute
                           left-3 top-1/2
                           -translate-y-1/2
                           w-4 h-4
                           text-slate-400"
                />

                <input
                    id="registrationSearch"
                    type="text"
                    autocomplete="off"
                    placeholder="Search applicant..."
                    class="w-full
                           pl-9 pr-4 py-2
                           rounded-xl
                           border border-slate-200
                           bg-white
                           text-xs sm:text-sm
                           text-navy
                           placeholder:text-slate-400
                           focus:outline-none
                           focus:border-mint
                           focus:ring-2
                           focus:ring-mint/10
                           transition"
                >

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    REGISTRATIONS LIST
========================================================= --}}
<div
    class="bg-white
           rounded-2xl
           border border-slate-200
           overflow-hidden"
>

    {{-- Header --}}
    <div
        class="flex items-center justify-between
               gap-4
               px-5 py-4
               border-b border-slate-100"
    >

        <div>
            <h2
                id="registrationListTitle"
                class="font-semibold text-navy text-sm"
            >
                Pending Applications
            </h2>

            <p class="text-[11px] text-slate-400 mt-0.5">
                Review submitted information and documents before approval.
            </p>
        </div>


        <span
            id="registrationVisibleCount"
            class="text-xs text-slate-400 shrink-0"
        >
            {{ $pendingCount }} total
        </span>

    </div>


    {{-- Items --}}
    <div id="registrationList" class="divide-y divide-slate-100">

        @foreach ($registrations as $registration)

            @php
                $roleStyles = [
                    'buyer' => [
                        'avatar' => 'bg-sky/10 text-sky',
                        'badge' => 'bg-sky/10 text-sky',
                        'icon' => 'shopping-bag',
                    ],

                    'seller' => [
                        'avatar' => 'bg-coral/10 text-coral',
                        'badge' => 'bg-coral/10 text-coral',
                        'icon' => 'store',
                    ],

                    'logistics' => [
                        'avatar' => 'bg-violet-100 text-violet-600',
                        'badge' => 'bg-violet-100 text-violet-600',
                        'icon' => 'truck',
                    ],
                ];

                $style =
                    $roleStyles[$registration['role']]
                    ?? [
                        'avatar' => 'bg-slate-100 text-slate-500',
                        'badge' => 'bg-slate-100 text-slate-500',
                        'icon' => 'user',
                    ];
            @endphp


            <div
                data-registration-item
                data-status="{{ $registration['status'] }}"
                data-role="{{ $registration['role'] }}"
                data-search="{{ strtolower(
                    $registration['name'] . ' ' .
                    $registration['email'] . ' ' .
                    $registration['role']
                ) }}"
                class="registration-item
                       flex flex-col
                       lg:flex-row
                       lg:items-center
                       lg:justify-between
                       gap-4
                       px-5 py-4
                       hover:bg-slate-50/60
                       transition"
            >

                {{-- Applicant Info --}}
                <div class="flex items-start sm:items-center gap-3 min-w-0">

                    <div
                        class="w-11 h-11
                               rounded-xl
                               {{ $style['avatar'] }}
                               flex items-center justify-center
                               shrink-0"
                    >
                        <span class="text-sm font-bold">
                            {{ $registration['initials'] }}
                        </span>
                    </div>


                    <div class="min-w-0">

                        <div class="flex items-center gap-2 flex-wrap">

                            <p
                                class="text-sm
                                       font-semibold
                                       text-navy
                                       truncate"
                            >
                                {{ $registration['name'] }}
                            </p>


                            <span
                                class="inline-flex
                                       items-center gap-1.5
                                       px-2 py-0.5
                                       rounded-full
                                       text-[10px]
                                       font-semibold
                                       {{ $style['badge'] }}"
                            >

                                <x-dynamic-component
                                    :component="'lucide-' . $style['icon']"
                                    class="w-3 h-3"
                                />

                                {{ ucfirst($registration['role']) }}

                            </span>

                        </div>


                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                            {{ $registration['email'] }}
                        </p>


                        <div
                            class="flex flex-wrap
                                   items-center
                                   gap-x-3 gap-y-1
                                   mt-1.5"
                        >

                            <span
                                class="inline-flex
                                       items-center gap-1
                                       text-[10px]
                                       text-slate-400"
                            >
                                <x-lucide-file-check class="w-3 h-3" />

                                {{ $registration['document'] }}
                            </span>


                            <span
                                class="inline-flex
                                       items-center gap-1
                                       text-[10px]
                                       text-slate-400"
                            >
                                <x-lucide-clock class="w-3 h-3" />

                                Applied {{ $registration['submitted'] }}
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div
                    class="flex items-center
                           gap-2
                           sm:pl-14
                           lg:pl-0
                           shrink-0"
                >

                    {{-- View --}}
                    <button
                        type="button"
                        class="inline-flex
                               items-center justify-center
                               gap-1.5
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
                    </button>


                    {{-- Pending Actions --}}
                    @if ($registration['status'] === 'pending')

                        <button
                            type="button"
                            class="inline-flex
                                   items-center justify-center
                                   gap-1.5
                                   px-3 py-1.5
                                   rounded-lg
                                   text-xs font-semibold
                                   text-white
                                   bg-mint-dark
                                   hover:opacity-90
                                   transition"
                        >
                            <x-lucide-check class="w-3.5 h-3.5" />

                            Approve
                        </button>


                        <button
                            type="button"
                            class="inline-flex
                                   items-center justify-center
                                   gap-1.5
                                   px-3 py-1.5
                                   rounded-lg
                                   text-xs font-semibold
                                   text-coral
                                   border border-coral/30
                                   hover:bg-coral/5
                                   transition"
                        >
                            <x-lucide-x class="w-3.5 h-3.5" />

                            Reject
                        </button>


                    {{-- Approved --}}
                    @elseif ($registration['status'] === 'approved')

                        <span
                            class="inline-flex
                                   items-center gap-1.5
                                   px-3 py-1.5
                                   rounded-lg
                                   bg-mint/10
                                   text-mint-dark
                                   text-xs font-semibold"
                        >
                            <x-lucide-circle-check class="w-3.5 h-3.5" />

                            Approved
                        </span>


                    {{-- Rejected --}}
                    @elseif ($registration['status'] === 'rejected')

                        <span
                            class="inline-flex
                                   items-center gap-1.5
                                   px-3 py-1.5
                                   rounded-lg
                                   bg-coral/5
                                   text-coral
                                   text-xs font-semibold"
                        >
                            <x-lucide-circle-x class="w-3.5 h-3.5" />

                            Rejected
                        </span>

                    @endif

                </div>

            </div>

        @endforeach


        {{-- Empty State --}}
        <div
            id="registrationEmpty"
            hidden
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
                <x-lucide-user-search class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy mt-3">
                No applications found
            </p>

            <p class="text-xs text-slate-400 mt-1">
                Try changing the status, role, or search filter.
            </p>

        </div>

    </div>


    {{-- =========================================================
        FOOTER / PAGINATION PLACEHOLDER
    ========================================================= --}}
    <div
        class="flex flex-col sm:flex-row
               sm:items-center
               sm:justify-between
               gap-3
               px-5 py-4
               border-t border-slate-100"
    >

        <p
            id="registrationShowingText"
            class="text-xs text-slate-400"
        >
            Showing applications
        </p>


        <div class="flex items-center gap-1">

            <button
                type="button"
                class="w-8 h-8
                       rounded-lg
                       text-xs font-semibold
                       border border-slate-200
                       text-slate-300
                       cursor-not-allowed"
                disabled
            >
                ‹
            </button>


            <button
                type="button"
                class="w-8 h-8
                       rounded-lg
                       text-xs font-semibold
                       bg-navy text-white"
            >
                1
            </button>


            <button
                type="button"
                class="w-8 h-8
                       rounded-lg
                       text-xs font-semibold
                       border border-slate-200
                       text-slate-400
                       cursor-not-allowed"
                disabled
            >
                ›
            </button>

        </div>

    </div>

</div>


@endsection


{{-- =========================================================
    REGISTRATION FILTER JAVASCRIPT
========================================================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const statusButtons =
        document.querySelectorAll('[data-status-filter]');

    const roleButtons =
        document.querySelectorAll('[data-role-filter]');

    const items =
        Array.from(
            document.querySelectorAll('[data-registration-item]')
        );

    const searchInput =
        document.getElementById('registrationSearch');

    const emptyState =
        document.getElementById('registrationEmpty');

    const visibleCount =
        document.getElementById('registrationVisibleCount');

    const showingText =
        document.getElementById('registrationShowingText');

    const listTitle =
        document.getElementById('registrationListTitle');


    let currentStatus = 'pending';

    let currentRole = 'all';


    /*
    |--------------------------------------------------------------------------
    | STATUS LABELS
    |--------------------------------------------------------------------------
    */

    const statusLabels = {
        pending: 'Pending Applications',
        approved: 'Approved Applications',
        rejected: 'Rejected Applications',
    };


    /*
    |--------------------------------------------------------------------------
    | FILTER ITEMS
    |--------------------------------------------------------------------------
    */

    function filterRegistrations() {

        const search =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();


        let count = 0;


        items.forEach(function (item) {

            const itemStatus =
                item.dataset.status;

            const itemRole =
                item.dataset.role;

            const itemSearch =
                item.dataset.search || '';


            const matchesStatus =
                itemStatus === currentStatus;


            const matchesRole =
                currentRole === 'all'
                || itemRole === currentRole;


            const matchesSearch =
                search === ''
                || itemSearch.includes(search);


            const visible =
                matchesStatus
                && matchesRole
                && matchesSearch;


            item.hidden = !visible;


            if (visible) {
                count++;
            }

        });


        if (emptyState) {
            emptyState.hidden = count > 0;
        }


        if (visibleCount) {
            visibleCount.textContent =
                count + (count === 1 ? ' result' : ' results');
        }


        if (showingText) {

            if (count === 0) {
                showingText.textContent =
                    'No applications to display';
            } else {
                showingText.textContent =
                    'Showing ' + count +
                    (count === 1
                        ? ' application'
                        : ' applications');
            }

        }


        if (listTitle) {

            listTitle.textContent =
                statusLabels[currentStatus]
                || 'Applications';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS FILTERS
    |--------------------------------------------------------------------------
    */

    statusButtons.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                currentStatus =
                    button.dataset.statusFilter;


                statusButtons.forEach(
                    function (item) {

                        item.classList.remove(
                            'bg-navy',
                            'text-white'
                        );

                        item.classList.add(
                            'text-slate-500'
                        );

                    }
                );


                button.classList.remove(
                    'text-slate-500'
                );

                button.classList.add(
                    'bg-navy',
                    'text-white'
                );


                filterRegistrations();

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | ROLE FILTERS
    |--------------------------------------------------------------------------
    */

    roleButtons.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                currentRole =
                    button.dataset.roleFilter;


                roleButtons.forEach(
                    function (item) {

                        item.classList.remove(
                            'bg-white',
                            'text-navy',
                            'shadow-sm'
                        );

                        item.classList.add(
                            'text-slate-500'
                        );

                    }
                );


                button.classList.remove(
                    'text-slate-500'
                );

                button.classList.add(
                    'bg-white',
                    'text-navy',
                    'shadow-sm'
                );


                filterRegistrations();

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    searchInput?.addEventListener(
        'input',
        filterRegistrations
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL DISPLAY
    |--------------------------------------------------------------------------
    */

    filterRegistrations();

});
</script>

@endpush