@extends('seller.partials.layout')

@section('title', 'Account Management')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    |
    | Your backend teammate can later connect:
    | - seller profile/account records
    | - business/category records
    | - address API fields
    | - profile photo / shop logo upload
    | - ID and business permit files
    | - account verification status
    | - password changes
    | - notification preferences
    | - account deactivation
    */

    $seller = $seller ?? [
        'id' => 'SEL-000128',
        'first_name' => 'Andrea',
        'middle_initial' => 'M.',
        'last_name' => 'Reyes',
        'sex' => 'Female',
        'email' => 'andrea.reyes@example.com',
        'contact' => '0917 482 1930',
        'birthday' => '1997-04-18',
        'age' => 29,

        'province' => 'Laguna',
        'municipality' => 'Calamba',
        'barangay' => 'Real',
        'street' => 'Blk 7 Lot 14, Sampaguita Street',

        'business_name' => 'Andrea Local Finds',
        'business_category' => 'Home & Living',
        'business_description' => 'Locally sourced home decor, handmade products, coffee, and gift items from Laguna-based makers.',
        'business_phone' => '0917 482 1930',
        'business_email' => 'shop@andreafinds.example.com',

        'profile_photo' => null,
        'shop_logo' => null,

        'account_status' => 'ACTIVE',
        'verification_status' => 'VERIFIED',
        'member_since' => 'March 2026',
        'last_login' => 'Today, 8:42 AM',

        'id_document' => [
            'name' => 'government-id.pdf',
            'status' => 'VERIFIED',
            'uploaded_at' => 'Mar 4, 2026',
        ],

        'business_permit' => [
            'name' => 'business-permit-2026.pdf',
            'status' => 'VERIFIED',
            'uploaded_at' => 'Mar 4, 2026',
        ],

        'notifications' => [
            'new_orders' => true,
            'courier_updates' => true,
            'delivery_updates' => true,
            'customer_messages' => true,
            'customer_reviews' => true,
            'report_summary' => false,
            'marketing' => false,
        ],
    ];

    $fullName = trim(
        $seller['first_name'] . ' ' .
        ($seller['middle_initial'] ? $seller['middle_initial'] . ' ' : '') .
        $seller['last_name']
    );

    $initials =
        mb_strtoupper(mb_substr($seller['first_name'], 0, 1)) .
        mb_strtoupper(mb_substr($seller['last_name'], 0, 1));

    $verificationClasses = [
        'VERIFIED' => 'bg-teal/10 text-teal-dark',
        'PENDING' => 'bg-yellow/20 text-amber-700',
        'REJECTED' => 'bg-red-50 text-red-500',
    ];

    $accountClasses = [
        'ACTIVE' => 'bg-teal/10 text-teal-dark',
        'SUSPENDED' => 'bg-red-50 text-red-500',
        'DEACTIVATED' => 'bg-navy/10 text-navy/45',
    ];

    $verificationClass =
        $verificationClasses[$seller['verification_status']]
        ?? 'bg-navy/10 text-navy/45';

    $accountClass =
        $accountClasses[$seller['account_status']]
        ?? 'bg-navy/10 text-navy/45';
@endphp


<style>
    #sellerAccount .account-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerAccount .account-card:hover {
        transform: translateY(-1px);
    }

    #sellerAccount [hidden],
    #profileEditModal[hidden],
    #businessEditModal[hidden],
    #addressEditModal[hidden],
    #passwordModal[hidden],
    #documentModal[hidden],
    #deactivateModal[hidden] {
        display: none !important;
    }

    .account-nav-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    .account-toggle {
        appearance: none;
        width: 2.5rem;
        height: 1.4rem;
        border-radius: 999px;
        background: rgba(15, 44, 63, .14);
        position: relative;
        transition: background .16s ease;
        cursor: pointer;
        flex-shrink: 0;
    }

    .account-toggle::after {
        content: "";
        width: 1rem;
        height: 1rem;
        border-radius: 999px;
        background: #fff;
        position: absolute;
        left: .2rem;
        top: .2rem;
        box-shadow: 0 1px 3px rgba(15, 44, 63, .18);
        transition: transform .16s ease;
    }

    .account-toggle:checked {
        background: #2ECFA6;
    }

    .account-toggle:checked::after {
        transform: translateX(1.1rem);
    }
</style>


<div id="sellerAccount" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">

            <div class="min-w-0">

                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-teal"></span>

                    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-teal-dark">
                        Seller Account
                    </p>
                </div>


                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Account Management
                </h1>


                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-3xl">
                    Manage your personal details, seller business information, verification documents,
                    security settings, and notification preferences.
                </p>


                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <span class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-[10px] font-bold {{ $accountClass }}">
                    <x-lucide-circle-check class="w-3.5 h-3.5" />
                    {{ $seller['account_status'] }}
                </span>


                <span class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-[10px] font-bold {{ $verificationClass }}">
                    <x-lucide-badge-check class="w-3.5 h-3.5" />
                    {{ $seller['verification_status'] }}
                </span>

            </div>

        </div>
    </section>


    {{-- =========================================================
        PROFILE OVERVIEW
    ========================================================= --}}
    <section class="account-card bg-white border border-gray-border rounded-xl overflow-hidden">

        <div class="p-4 sm:p-5">

            <div class="flex flex-col lg:flex-row lg:items-center gap-5">

                {{-- Avatar --}}
                <div class="relative shrink-0">

                    <div
                        id="accountProfileAvatar"
                        class="w-20 h-20 rounded-2xl bg-navy text-white flex items-center justify-center text-xl font-bold overflow-hidden border border-navy/10"
                    >
                        @if (!empty($seller['profile_photo']))

                            <img
                                src="{{ $seller['profile_photo'] }}"
                                alt="{{ $fullName }}"
                                class="w-full h-full object-cover"
                            >

                        @else

                            <span id="accountAvatarInitials">
                                {{ $initials }}
                            </span>

                        @endif
                    </div>


                    <button
                        type="button"
                        id="demoPhotoButton"
                        class="absolute -right-2 -bottom-2 w-8 h-8 rounded-lg bg-white border border-gray-border shadow-soft text-navy/55 flex items-center justify-center hover:text-teal-dark hover:border-teal/30 transition"
                        title="Change profile photo"
                    >
                        <x-lucide-camera class="w-4 h-4" />
                    </button>

                </div>


                {{-- Main info --}}
                <div class="min-w-0 flex-1">

                    <div class="flex flex-wrap items-center gap-2">

                        <h2
                            id="overviewFullName"
                            class="text-lg font-bold text-navy"
                        >
                            {{ $fullName }}
                        </h2>


                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal/10 text-[10px] font-bold text-teal-dark">
                            <x-lucide-badge-check class="w-3 h-3" />
                            Verified Seller
                        </span>

                    </div>


                    <p
                        id="overviewBusinessName"
                        class="text-xs font-semibold text-navy/55 mt-1"
                    >
                        {{ $seller['business_name'] }}
                    </p>


                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-[11px] text-navy/40">

                        <span class="inline-flex items-center gap-1.5">
                            <x-lucide-mail class="w-3.5 h-3.5" />
                            <span id="overviewEmail">{{ $seller['email'] }}</span>
                        </span>


                        <span class="inline-flex items-center gap-1.5">
                            <x-lucide-phone class="w-3.5 h-3.5" />
                            <span id="overviewContact">{{ $seller['contact'] }}</span>
                        </span>


                        <span class="inline-flex items-center gap-1.5">
                            <x-lucide-map-pin class="w-3.5 h-3.5" />
                            <span id="overviewLocation">
                                {{ $seller['municipality'] }}, {{ $seller['province'] }}
                            </span>
                        </span>

                    </div>

                </div>


                {{-- Account meta --}}
                <div class="grid grid-cols-2 gap-3 lg:w-72 shrink-0">

                    <div class="rounded-xl bg-gray-bg p-3">

                        <p class="text-[10px] text-navy/35">
                            Seller ID
                        </p>

                        <p class="text-xs font-bold text-navy mt-1">
                            {{ $seller['id'] }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-gray-bg p-3">

                        <p class="text-[10px] text-navy/35">
                            Member Since
                        </p>

                        <p class="text-xs font-bold text-navy mt-1">
                            {{ $seller['member_since'] }}
                        </p>

                    </div>


                    <div class="col-span-2 rounded-xl bg-gray-bg p-3">

                        <p class="text-[10px] text-navy/35">
                            Last Login
                        </p>

                        <p class="text-xs font-bold text-navy mt-1">
                            {{ $seller['last_login'] }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        ACCOUNT NAVIGATION
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-2">

        <div class="flex items-center gap-1 overflow-x-auto">

            @foreach ([
                ['key' => 'profile', 'label' => 'Profile', 'icon' => 'user-round'],
                ['key' => 'business', 'label' => 'Business', 'icon' => 'store'],
                ['key' => 'verification', 'label' => 'Verification', 'icon' => 'badge-check'],
                ['key' => 'security', 'label' => 'Security', 'icon' => 'shield-check'],
                ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
            ] as $nav)

                <button
                    type="button"
                    data-account-nav="{{ $nav['key'] }}"
                    class="account-nav h-9 px-3.5 rounded-lg border border-transparent inline-flex items-center gap-1.5 text-xs font-semibold whitespace-nowrap transition
                        {{ $nav['key'] === 'profile'
                            ? 'account-nav-active'
                            : 'text-navy/50 hover:bg-gray-bg'
                        }}"
                >
                    <x-dynamic-component
                        :component="'lucide-' . $nav['icon']"
                        class="w-3.5 h-3.5"
                    />

                    {{ $nav['label'] }}
                </button>

            @endforeach

        </div>

    </section>


    {{-- =========================================================
        PROFILE SECTION
    ========================================================= --}}
    <section
        data-account-section="profile"
        class="space-y-4"
    >

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Personal Information --}}
            <article class="account-card bg-white border border-gray-border rounded-xl">

                <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-4 border-b border-gray-border">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Personal Information
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Basic seller identity and contact information.
                        </p>

                    </div>


                    <button
                        type="button"
                        id="openProfileEdit"
                        class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:border-teal/30 hover:text-teal-dark transition"
                    >
                        <x-lucide-pencil class="w-3.5 h-3.5" />
                        Edit
                    </button>

                </div>


                <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                    <div>

                        <p class="text-[10px] text-navy/35">
                            First Name
                        </p>

                        <p
                            id="profileFirstName"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['first_name'] }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Last Name
                        </p>

                        <p
                            id="profileLastName"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['last_name'] }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Middle Initial
                        </p>

                        <p
                            id="profileMiddleInitial"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['middle_initial'] ?: '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Sex
                        </p>

                        <p
                            id="profileSex"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['sex'] }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Email
                        </p>

                        <p
                            id="profileEmail"
                            class="text-xs font-semibold text-navy mt-1 break-all"
                        >
                            {{ $seller['email'] }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Contact Number
                        </p>

                        <p
                            id="profileContact"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['contact'] }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Birthday
                        </p>

                        <p
                            id="profileBirthday"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ \Carbon\Carbon::parse($seller['birthday'])->format('F d, Y') }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Age
                        </p>

                        <p
                            id="profileAge"
                            class="text-xs font-semibold text-navy mt-1"
                        >
                            {{ $seller['age'] }}
                        </p>

                    </div>

                </div>

            </article>


            {{-- Address --}}
            <article class="account-card bg-white border border-gray-border rounded-xl">

                <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-4 border-b border-gray-border">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Address
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Seller residential/business contact address.
                        </p>

                    </div>


                    <button
                        type="button"
                        id="openAddressEdit"
                        class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:border-teal/30 hover:text-teal-dark transition"
                    >
                        <x-lucide-pencil class="w-3.5 h-3.5" />
                        Edit
                    </button>

                </div>


                <div class="p-4 sm:p-5">

                    <div class="rounded-xl bg-gray-bg p-4">

                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-map-pin class="w-4 h-4" />
                            </div>


                            <div>

                                <p
                                    id="addressFullDisplay"
                                    class="text-xs font-semibold text-navy leading-relaxed"
                                >
                                    {{ $seller['street'] }},
                                    Brgy. {{ $seller['barangay'] }},
                                    {{ $seller['municipality'] }},
                                    {{ $seller['province'] }}
                                </p>


                                <p class="text-[10px] text-navy/35 mt-1">
                                    Philippines
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">

                        <div>

                            <p class="text-[10px] text-navy/35">
                                Province
                            </p>

                            <p
                                id="addressProvince"
                                class="text-xs font-semibold text-navy mt-1"
                            >
                                {{ $seller['province'] }}
                            </p>

                        </div>


                        <div>

                            <p class="text-[10px] text-navy/35">
                                Municipality
                            </p>

                            <p
                                id="addressMunicipality"
                                class="text-xs font-semibold text-navy mt-1"
                            >
                                {{ $seller['municipality'] }}
                            </p>

                        </div>


                        <div>

                            <p class="text-[10px] text-navy/35">
                                Barangay
                            </p>

                            <p
                                id="addressBarangay"
                                class="text-xs font-semibold text-navy mt-1"
                            >
                                {{ $seller['barangay'] }}
                            </p>

                        </div>

                    </div>

                </div>

            </article>

        </div>

    </section>


    {{-- =========================================================
        BUSINESS SECTION
    ========================================================= --}}
    <section
        data-account-section="business"
        hidden
        class="space-y-4"
    >

        <div class="grid grid-cols-1 xl:grid-cols-[1.25fr_.75fr] gap-4">

            <article class="account-card bg-white border border-gray-border rounded-xl">

                <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-4 border-b border-gray-border">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Business Information
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Store identity and registered line of business.
                        </p>

                    </div>


                    <button
                        type="button"
                        id="openBusinessEdit"
                        class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:border-teal/30 hover:text-teal-dark transition"
                    >
                        <x-lucide-pencil class="w-3.5 h-3.5" />
                        Edit
                    </button>

                </div>


                <div class="p-4 sm:p-5">

                    <div class="flex items-start gap-4">

                        <div
                            id="businessLogoDisplay"
                            class="w-16 h-16 rounded-xl bg-navy text-white flex items-center justify-center text-lg font-bold shrink-0 overflow-hidden"
                        >
                            @if (!empty($seller['shop_logo']))

                                <img
                                    src="{{ $seller['shop_logo'] }}"
                                    alt="{{ $seller['business_name'] }}"
                                    class="w-full h-full object-cover"
                                >

                            @else

                                <x-lucide-store class="w-7 h-7" />

                            @endif
                        </div>


                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <p
                                    id="businessNameDisplay"
                                    class="text-base font-bold text-navy"
                                >
                                    {{ $seller['business_name'] }}
                                </p>


                                <span class="inline-flex items-center gap-1 rounded-full bg-teal/10 px-2 py-0.5 text-[10px] font-bold text-teal-dark">
                                    <x-lucide-badge-check class="w-3 h-3" />
                                    Verified Business
                                </span>

                            </div>


                            <p
                                id="businessCategoryDisplay"
                                class="text-xs font-semibold text-teal-dark mt-1"
                            >
                                {{ $seller['business_category'] }}
                            </p>


                            <p
                                id="businessDescriptionDisplay"
                                class="text-xs text-navy/55 mt-3 leading-relaxed"
                            >
                                {{ $seller['business_description'] }}
                            </p>

                        </div>

                    </div>


                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">

                        <div class="rounded-xl bg-gray-bg p-3">

                            <p class="text-[10px] text-navy/35">
                                Business Email
                            </p>

                            <p
                                id="businessEmailDisplay"
                                class="text-xs font-semibold text-navy mt-1 break-all"
                            >
                                {{ $seller['business_email'] }}
                            </p>

                        </div>


                        <div class="rounded-xl bg-gray-bg p-3">

                            <p class="text-[10px] text-navy/35">
                                Business Contact
                            </p>

                            <p
                                id="businessPhoneDisplay"
                                class="text-xs font-semibold text-navy mt-1"
                            >
                                {{ $seller['business_phone'] }}
                            </p>

                        </div>

                    </div>

                </div>

            </article>


            <article class="account-card bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Shop Profile
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Optional storefront visual settings.
                    </p>

                </div>


                <div class="mt-5 space-y-3">

                    <button
                        type="button"
                        id="demoLogoButton"
                        class="w-full rounded-xl border border-gray-border p-3 text-left hover:border-teal/30 hover:bg-teal/5 transition"
                    >

                        <div class="flex items-center gap-3">

                            <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-image-up class="w-4 h-4" />
                            </div>


                            <div class="min-w-0">

                                <p class="text-xs font-semibold text-navy">
                                    Change Shop Logo
                                </p>

                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    Upload/store integration can be added later.
                                </p>

                            </div>

                        </div>

                    </button>


                    <div class="rounded-xl bg-gray-bg p-3">

                        <p class="text-[10px] text-navy/35">
                            Registered Category
                        </p>

                        <p class="text-xs font-semibold text-navy mt-1">
                            {{ $seller['business_category'] }}
                        </p>

                        <p class="text-[10px] text-navy/35 mt-1.5 leading-relaxed">
                            Products should remain within your approved seller category.
                        </p>

                    </div>

                </div>

            </article>

        </div>

    </section>


    {{-- =========================================================
        VERIFICATION SECTION
    ========================================================= --}}
    <section
        data-account-section="verification"
        hidden
        class="space-y-4"
    >

        <div class="grid grid-cols-1 xl:grid-cols-[.8fr_1.2fr] gap-4">

            {{-- Verification status --}}
            <article class="account-card bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div class="w-11 h-11 rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-badge-check class="w-5 h-5" />
                </div>


                <h2 class="text-base font-bold text-navy mt-4">
                    Seller Verification
                </h2>


                <p class="text-xs text-navy/50 mt-2 leading-relaxed">
                    Your seller identity and business documents have been reviewed.
                    Keep your information current so your account remains compliant.
                </p>


                <div class="mt-4 rounded-xl bg-teal-light border border-teal/20 p-3">

                    <div class="flex items-center justify-between gap-3">

                        <div>

                            <p class="text-[10px] text-navy/35">
                                Current Status
                            </p>

                            <p class="text-sm font-bold text-teal-dark mt-1">
                                {{ $seller['verification_status'] }}
                            </p>

                        </div>


                        <x-lucide-shield-check class="w-5 h-5 text-teal-dark" />

                    </div>

                </div>


                <div class="mt-4 rounded-xl bg-gray-bg p-3">

                    <p class="text-[10px] text-navy/35">
                        Account Status
                    </p>

                    <p class="text-xs font-bold text-navy mt-1">
                        {{ $seller['account_status'] }}
                    </p>

                </div>

            </article>


            {{-- Documents --}}
            <article class="account-card bg-white border border-gray-border rounded-xl overflow-hidden">

                <div class="px-4 sm:px-5 py-4 border-b border-gray-border">

                    <h2 class="text-base font-bold text-navy">
                        Verification Documents
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Government ID and registered business permit.
                    </p>

                </div>


                <div class="p-4 sm:p-5 space-y-3">

                    @foreach ([
                        [
                            'key' => 'id',
                            'label' => 'Government ID',
                            'icon' => 'id-card',
                            'document' => $seller['id_document'],
                        ],
                        [
                            'key' => 'permit',
                            'label' => 'Business Permit',
                            'icon' => 'file-badge',
                            'document' => $seller['business_permit'],
                        ],
                    ] as $document)

                        <div class="rounded-xl border border-gray-border p-3">

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy/55 flex items-center justify-center shrink-0">

                                    <x-dynamic-component
                                        :component="'lucide-' . $document['icon']"
                                        class="w-4 h-4"
                                    />

                                </div>


                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <p class="text-xs font-semibold text-navy">
                                            {{ $document['label'] }}
                                        </p>


                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal/10 text-[9px] font-bold text-teal-dark">
                                            <x-lucide-circle-check class="w-3 h-3" />
                                            {{ $document['document']['status'] }}
                                        </span>

                                    </div>


                                    <p class="text-[10px] text-navy/40 mt-1 break-all">
                                        {{ $document['document']['name'] }}
                                    </p>


                                    <p class="text-[9px] text-navy/30 mt-0.5">
                                        Uploaded {{ $document['document']['uploaded_at'] }}
                                    </p>

                                </div>


                                <div class="flex items-center gap-2 shrink-0">

                                    <button
                                        type="button"
                                        data-view-document="{{ $document['key'] }}"
                                        class="h-8 px-2.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:bg-gray-bg hover:text-navy transition"
                                    >
                                        View
                                    </button>


                                    <button
                                        type="button"
                                        data-replace-document="{{ $document['key'] }}"
                                        class="h-8 px-2.5 rounded-lg border border-teal/20 bg-teal/5 text-[10px] font-semibold text-teal-dark hover:bg-teal/10 transition"
                                    >
                                        Replace
                                    </button>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </article>

        </div>

    </section>


    {{-- =========================================================
        SECURITY SECTION
    ========================================================= --}}
    <section
        data-account-section="security"
        hidden
        class="space-y-4"
    >

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Password --}}
            <article class="account-card bg-white border border-gray-border rounded-xl overflow-hidden">

                <div class="px-4 sm:px-5 py-4 border-b border-gray-border">

                    <div class="flex items-start gap-3">

                        <div class="w-9 h-9 rounded-lg bg-sky/10 text-sky flex items-center justify-center shrink-0">
                            <x-lucide-key-round class="w-4 h-4" />
                        </div>


                        <div>

                            <h2 class="text-base font-bold text-navy">
                                Password
                            </h2>

                            <p class="text-[10px] text-navy/40 mt-0.5">
                                Keep your seller account password secure.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-4 sm:p-5">

                    <div class="rounded-xl bg-gray-bg p-3">

                        <div class="flex items-center justify-between gap-3">

                            <div>

                                <p class="text-xs font-semibold text-navy">
                                    Account Password
                                </p>

                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    Last changed 45 days ago
                                </p>

                            </div>


                            <span class="text-sm tracking-[0.2em] text-navy/35">
                                ••••••••••••
                            </span>

                        </div>

                    </div>


                    <button
                        type="button"
                        id="openPasswordModal"
                        class="mt-4 inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                    >
                        <x-lucide-lock-keyhole class="w-4 h-4" />
                        Change Password
                    </button>

                </div>

            </article>


            {{-- Login / safety --}}
            <article class="account-card bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Account Security
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Basic security information for this seller account.
                    </p>

                </div>


                <div class="mt-4 space-y-3">

                    <div class="rounded-xl border border-gray-border p-3">

                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-monitor-smartphone class="w-4 h-4" />
                            </div>


                            <div>

                                <p class="text-xs font-semibold text-navy">
                                    Current Session
                                </p>

                                <p class="text-[10px] text-navy/40 mt-1">
                                    Active seller session · Last login {{ $seller['last_login'] }}
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="rounded-xl border border-gray-border p-3">

                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center shrink-0">
                                <x-lucide-shield-alert class="w-4 h-4" />
                            </div>


                            <div>

                                <p class="text-xs font-semibold text-navy">
                                    Security Reminder
                                </p>

                                <p class="text-[10px] text-navy/40 mt-1 leading-relaxed">
                                    Never share your password or verification codes with buyers,
                                    riders, or other sellers.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </article>

        </div>


        {{-- Danger zone --}}
        <article class="bg-white border border-red-200 rounded-xl overflow-hidden">

            <div class="px-4 sm:px-5 py-4 border-b border-red-100">

                <h2 class="text-base font-bold text-red-600">
                    Account Status
                </h2>

                <p class="text-[10px] text-navy/40 mt-0.5">
                    Sensitive account-level actions.
                </p>

            </div>


            <div class="p-4 sm:p-5">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>

                        <p class="text-xs font-semibold text-navy">
                            Deactivate seller account
                        </p>

                        <p class="text-[10px] text-navy/40 mt-1 max-w-2xl leading-relaxed">
                            This frontend demo only shows the confirmation UI.
                            Actual deactivation should be validated and handled securely by the backend.
                        </p>

                    </div>


                    <button
                        type="button"
                        id="openDeactivateModal"
                        class="h-9 px-3.5 rounded-lg border border-red-200 bg-red-50 text-xs font-semibold text-red-600 hover:bg-red-100 transition shrink-0"
                    >
                        Deactivate Account
                    </button>

                </div>

            </div>

        </article>

    </section>


    {{-- =========================================================
        NOTIFICATIONS SECTION
    ========================================================= --}}
    <section
        data-account-section="notifications"
        hidden
        class="space-y-4"
    >

        <article class="account-card bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-4 sm:px-5 py-4 border-b border-gray-border">

                <h2 class="text-base font-bold text-navy">
                    Notification Preferences
                </h2>

                <p class="text-[10px] text-navy/40 mt-0.5">
                    Choose which seller updates you want to receive.
                </p>

            </div>


            <div class="divide-y divide-gray-border">

                @foreach ([
                    [
                        'key' => 'new_orders',
                        'title' => 'New Orders',
                        'description' => 'Notify me when a buyer places a new order.',
                        'icon' => 'shopping-bag',
                    ],
                    [
                        'key' => 'courier_updates',
                        'title' => 'Courier & Pickup Updates',
                        'description' => 'Notify me about rider assignment and parcel pickup.',
                        'icon' => 'bike',
                    ],
                    [
                        'key' => 'delivery_updates',
                        'title' => 'Delivery Updates',
                        'description' => 'Notify me when delivery status changes or the buyer receives an order.',
                        'icon' => 'truck',
                    ],
                    [
                        'key' => 'customer_messages',
                        'title' => 'Customer Messages',
                        'description' => 'Notify me when a buyer sends a new chat message.',
                        'icon' => 'message-circle',
                    ],
                    [
                        'key' => 'customer_reviews',
                        'title' => 'Customer Reviews',
                        'description' => 'Notify me when a buyer leaves product feedback.',
                        'icon' => 'star',
                    ],
                    [
                        'key' => 'report_summary',
                        'title' => 'Weekly Report Summary',
                        'description' => 'Receive a weekly seller performance summary.',
                        'icon' => 'bar-chart-3',
                    ],
                    [
                        'key' => 'marketing',
                        'title' => 'Platform Announcements & Marketing',
                        'description' => 'Receive seller tips, campaigns, and platform announcements.',
                        'icon' => 'megaphone',
                    ],
                ] as $preference)

                    <label class="flex items-start gap-3 px-4 sm:px-5 py-4 cursor-pointer hover:bg-gray-bg/30 transition">

                        <div class="w-9 h-9 rounded-lg bg-gray-bg text-navy/45 flex items-center justify-center shrink-0">

                            <x-dynamic-component
                                :component="'lucide-' . $preference['icon']"
                                class="w-4 h-4"
                            />

                        </div>


                        <div class="min-w-0 flex-1">

                            <p class="text-xs font-semibold text-navy">
                                {{ $preference['title'] }}
                            </p>

                            <p class="text-[10px] text-navy/40 mt-0.5 leading-relaxed">
                                {{ $preference['description'] }}
                            </p>

                        </div>


                        <input
                            type="checkbox"
                            data-notification-setting="{{ $preference['key'] }}"
                            class="account-toggle"
                            {{ !empty($seller['notifications'][$preference['key']]) ? 'checked' : '' }}
                        >

                    </label>

                @endforeach

            </div>

        </article>


        <div class="flex justify-end">

            <button
                type="button"
                id="saveNotificationPreferences"
                class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
            >
                Save Preferences
            </button>

        </div>

    </section>

</div>


{{-- =============================================================
    PROFILE EDIT MODAL
============================================================= --}}
<div
    id="profileEditModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-profile class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Edit Personal Information
                </p>

                <p class="text-[11px] text-navy/40 mt-0.5">
                    Frontend demo changes only
                </p>

            </div>


            <button
                type="button"
                data-close-profile
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            id="profileEditForm"
            class="p-5"
        >

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>

                    <label
                        for="editFirstName"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        First Name
                    </label>

                    <input
                        id="editFirstName"
                        type="text"
                        value="{{ $seller['first_name'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editLastName"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Last Name
                    </label>

                    <input
                        id="editLastName"
                        type="text"
                        value="{{ $seller['last_name'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editMiddleInitial"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Middle Initial
                    </label>

                    <input
                        id="editMiddleInitial"
                        type="text"
                        maxlength="3"
                        value="{{ $seller['middle_initial'] }}"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editSex"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Sex
                    </label>

                    <select
                        id="editSex"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        <option {{ $seller['sex'] === 'Male' ? 'selected' : '' }}>Male</option>
                        <option {{ $seller['sex'] === 'Female' ? 'selected' : '' }}>Female</option>
                        <option {{ $seller['sex'] === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                    </select>

                </div>


                <div class="sm:col-span-2">

                    <label
                        for="editEmail"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Email
                    </label>

                    <input
                        id="editEmail"
                        type="email"
                        value="{{ $seller['email'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editContact"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Contact Number
                    </label>

                    <input
                        id="editContact"
                        type="text"
                        value="{{ $seller['contact'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editBirthday"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Birthday
                    </label>

                    <input
                        id="editBirthday"
                        type="date"
                        value="{{ $seller['birthday'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>

            </div>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-profile
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
    BUSINESS EDIT MODAL
============================================================= --}}
<div
    id="businessEditModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-business class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Edit Business Information
                </p>

                <p class="text-[11px] text-navy/40 mt-0.5">
                    Frontend demo changes only
                </p>

            </div>


            <button
                type="button"
                data-close-business
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            id="businessEditForm"
            class="p-5"
        >

            <div class="space-y-4">

                <div>

                    <label
                        for="editBusinessName"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Business Name
                    </label>

                    <input
                        id="editBusinessName"
                        type="text"
                        value="{{ $seller['business_name'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="editBusinessCategory"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Line of Business / Category
                    </label>

                    <select
                        id="editBusinessCategory"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        @foreach ([
                            'Home & Living',
                            'Food & Beverages',
                            'Beauty & Personal Care',
                            'Fashion',
                            'Electronics',
                            'Arts & Crafts',
                        ] as $category)

                            <option {{ $seller['business_category'] === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>

                        @endforeach
                    </select>

                </div>


                <div>

                    <label
                        for="editBusinessDescription"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Business Description
                    </label>

                    <textarea
                        id="editBusinessDescription"
                        rows="4"
                        maxlength="400"
                        class="mt-1.5 w-full px-3 py-2.5 rounded-xl border border-gray-border text-xs text-navy resize-none focus:outline-none focus:border-teal/50"
                    >{{ $seller['business_description'] }}</textarea>

                </div>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>

                        <label
                            for="editBusinessEmail"
                            class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                        >
                            Business Email
                        </label>

                        <input
                            id="editBusinessEmail"
                            type="email"
                            value="{{ $seller['business_email'] }}"
                            class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                        >

                    </div>


                    <div>

                        <label
                            for="editBusinessPhone"
                            class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                        >
                            Business Contact
                        </label>

                        <input
                            id="editBusinessPhone"
                            type="text"
                            value="{{ $seller['business_phone'] }}"
                            class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                        >

                    </div>

                </div>

            </div>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-business
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Save Business
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
    ADDRESS EDIT MODAL
============================================================= --}}
<div
    id="addressEditModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-address class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Edit Address
                </p>

                <p class="text-[11px] text-navy/40 mt-0.5">
                    API dropdowns can be connected by the backend later
                </p>

            </div>


            <button
                type="button"
                data-close-address
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            id="addressEditForm"
            class="p-5"
        >

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>

                    <label
                        for="editProvince"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Province
                    </label>

                    <select
                        id="editProvince"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        <option selected>Laguna</option>
                        <option>Cavite</option>
                        <option>Batangas</option>
                        <option>Rizal</option>
                    </select>

                </div>


                <div>

                    <label
                        for="editMunicipality"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Municipality
                    </label>

                    <select
                        id="editMunicipality"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        <option selected>Calamba</option>
                        <option>Los Baños</option>
                        <option>Santa Cruz</option>
                        <option>Pagsanjan</option>
                    </select>

                </div>


                <div>

                    <label
                        for="editBarangay"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Barangay
                    </label>

                    <select
                        id="editBarangay"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        <option selected>Real</option>
                        <option>Halang</option>
                        <option>Parian</option>
                        <option>Lecheria</option>
                    </select>

                </div>


                <div>

                    <label
                        for="editStreet"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Street / House Number
                    </label>

                    <input
                        id="editStreet"
                        type="text"
                        value="{{ $seller['street'] }}"
                        required
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>

            </div>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-address
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Save Address
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
    PASSWORD MODAL
============================================================= --}}
<div
    id="passwordModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-password class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Change Password
                </p>

                <p class="text-[11px] text-navy/40 mt-0.5">
                    Backend validation should be added later
                </p>

            </div>


            <button
                type="button"
                data-close-password
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            id="passwordForm"
            class="p-5"
        >

            <div class="space-y-4">

                <div>

                    <label
                        for="currentPassword"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Current Password
                    </label>

                    <input
                        id="currentPassword"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="newPassword"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        New Password
                    </label>

                    <input
                        id="newPassword"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                    <div class="mt-2 h-1.5 rounded-full bg-gray-bg overflow-hidden">

                        <div
                            id="passwordStrengthBar"
                            class="h-full rounded-full bg-navy/20 transition-all"
                            style="width: 0%"
                        ></div>

                    </div>


                    <p
                        id="passwordStrengthText"
                        class="text-[10px] text-navy/35 mt-1"
                    >
                        Use at least 8 characters.
                    </p>

                </div>


                <div>

                    <label
                        for="confirmNewPassword"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Confirm New Password
                    </label>

                    <input
                        id="confirmNewPassword"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="mt-1.5 w-full h-10 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                    >

                    <p
                        id="passwordMatchError"
                        hidden
                        class="text-[10px] text-red-500 font-semibold mt-1"
                    >
                        Passwords do not match.
                    </p>

                </div>

            </div>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-password
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Update Password
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
    DOCUMENT MODAL
============================================================= --}}
<div
    id="documentModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-document class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p
                    id="documentModalTitle"
                    class="text-base font-bold text-navy"
                >
                    Document
                </p>

                <p
                    id="documentModalSubtitle"
                    class="text-[11px] text-navy/40 mt-0.5"
                ></p>

            </div>


            <button
                type="button"
                data-close-document
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5">

            <div class="rounded-xl border-2 border-dashed border-gray-border bg-gray-bg/40 py-10 text-center">

                <div class="w-12 h-12 mx-auto rounded-xl bg-navy/10 text-navy/45 flex items-center justify-center">
                    <x-lucide-file-check-2 class="w-5 h-5" />
                </div>


                <p
                    id="documentFileName"
                    class="text-xs font-semibold text-navy mt-3"
                ></p>


                <p class="text-[10px] text-navy/35 mt-1">
                    Demo document preview placeholder
                </p>

            </div>


            <div
                id="documentReplaceArea"
                hidden
                class="mt-4"
            >

                <label class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Select Replacement File
                </label>


                <input
                    type="file"
                    id="replacementFileInput"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="mt-2 block w-full text-xs text-navy/50
                           file:mr-3 file:rounded-lg file:border-0
                           file:bg-navy file:px-3 file:py-2
                           file:text-[10px] file:font-semibold file:text-white"
                >


                <p class="text-[10px] text-navy/35 mt-1">
                    PDF, JPG, or PNG. Backend upload validation can be added later.
                </p>


                <div class="mt-4 flex justify-end">

                    <button
                        type="button"
                        id="demoSaveDocument"
                        class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                    >
                        Replace Document
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
    DEACTIVATE MODAL
============================================================= --}}
<div
    id="deactivateModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div data-close-deactivate class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">

        <div class="p-5">

            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                <x-lucide-triangle-alert class="w-5 h-5" />
            </div>


            <h2 class="text-base font-bold text-navy mt-4">
                Deactivate seller account?
            </h2>


            <p class="text-xs text-navy/50 mt-2 leading-relaxed">
                This is a frontend-only confirmation. Your backend should decide whether
                accounts with active orders, pending payouts, disputes, or unfulfilled parcels
                are allowed to deactivate.
            </p>


            <label class="flex items-start gap-3 mt-4 rounded-xl bg-gray-bg p-3 cursor-pointer">

                <input
                    id="confirmDeactivateCheckbox"
                    type="checkbox"
                    class="w-4 h-4 mt-0.5 rounded border-gray-border text-red-500 focus:ring-red-200"
                >


                <span class="text-[11px] text-navy/60 leading-relaxed">
                    I understand that deactivating the seller account may prevent access to seller features.
                </span>

            </label>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-deactivate
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    id="confirmDeactivateButton"
                    disabled
                    class="h-9 px-4 rounded-lg bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Deactivate
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
    TOAST
============================================================= --}}
<div
    id="accountToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>

    <div class="flex items-start gap-3">

        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>


        <div>

            <p class="text-xs font-bold text-navy">
                Account updated
            </p>

            <p
                id="accountToastMessage"
                class="text-[11px] text-navy/45 mt-0.5"
            ></p>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const navButtons =
        Array.from(
            document.querySelectorAll('[data-account-nav]')
        );

    const sections =
        Array.from(
            document.querySelectorAll('[data-account-section]')
        );


    const profileModal =
        document.getElementById('profileEditModal');

    const businessModal =
        document.getElementById('businessEditModal');

    const addressModal =
        document.getElementById('addressEditModal');

    const passwordModal =
        document.getElementById('passwordModal');

    const documentModal =
        document.getElementById('documentModal');

    const deactivateModal =
        document.getElementById('deactivateModal');


    const toast =
        document.getElementById('accountToast');

    const toastMessage =
        document.getElementById('accountToastMessage');


    let activeDocumentKey =
        null;

    let documentMode =
        'view';

    let toastTimer =
        null;


    const documents = {
        id: {
            title: 'Government ID',
            file: @json($seller['id_document']['name']),
            status: @json($seller['id_document']['status'])
        },

        permit: {
            title: 'Business Permit',
            file: @json($seller['business_permit']['name']),
            status: @json($seller['business_permit']['status'])
        }
    };


    function setBodyLock(locked) {

        document.body.style.overflow =
            locked ? 'hidden' : '';
    }


    function showToast(message) {

        toastMessage.textContent =
            message;


        toast.hidden =
            false;


        if (toastTimer) {
            clearTimeout(toastTimer);
        }


        toastTimer =
            setTimeout(function () {

                toast.hidden =
                    true;

            }, 3200);
    }


    function openModal(modal) {

        modal.hidden =
            false;


        setBodyLock(true);
    }


    function closeModal(modal) {

        modal.hidden =
            true;


        setBodyLock(false);
    }


    /* ---------------------------------------------------------
       SECTION NAVIGATION
    --------------------------------------------------------- */
    function showSection(key) {

        sections.forEach(function (section) {

            section.hidden =
                section.dataset.accountSection !== key;
        });


        navButtons.forEach(function (button) {

            const active =
                button.dataset.accountNav === key;


            button.classList.toggle(
                'account-nav-active',
                active
            );


            button.classList.toggle(
                'text-navy/50',
                !active
            );


            button.classList.toggle(
                'hover:bg-gray-bg',
                !active
            );
        });
    }


    navButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            showSection(
                button.dataset.accountNav || 'profile'
            );
        });
    });


    /* ---------------------------------------------------------
       DEMO PHOTO / LOGO
    --------------------------------------------------------- */
    document
        .getElementById('demoPhotoButton')
        ?.addEventListener('click', function () {

            showToast(
                'Profile photo upload UI is ready for backend/storage integration.'
            );
        });


    document
        .getElementById('demoLogoButton')
        ?.addEventListener('click', function () {

            showToast(
                'Shop logo upload UI is ready for backend/storage integration.'
            );
        });


    /* ---------------------------------------------------------
       PROFILE EDIT
    --------------------------------------------------------- */
    document
        .getElementById('openProfileEdit')
        ?.addEventListener('click', function () {

            openModal(
                profileModal
            );
        });


    document
        .querySelectorAll('[data-close-profile]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    profileModal
                );
            });
        });


    function calculateAge(dateString) {

        if (!dateString) {
            return '';
        }


        const birth =
            new Date(dateString);


        const today =
            new Date();


        let age =
            today.getFullYear() -
            birth.getFullYear();


        const monthDiff =
            today.getMonth() -
            birth.getMonth();


        if (
            monthDiff < 0 ||
            (
                monthDiff === 0 &&
                today.getDate() < birth.getDate()
            )
        ) {
            age--;
        }


        return age;
    }


    document
        .getElementById('profileEditForm')
        ?.addEventListener('submit', function (event) {

            event.preventDefault();


            const firstName =
                document.getElementById('editFirstName').value.trim();

            const lastName =
                document.getElementById('editLastName').value.trim();

            const middleInitial =
                document.getElementById('editMiddleInitial').value.trim();

            const sex =
                document.getElementById('editSex').value;

            const email =
                document.getElementById('editEmail').value.trim();

            const contact =
                document.getElementById('editContact').value.trim();

            const birthday =
                document.getElementById('editBirthday').value;

            const age =
                calculateAge(
                    birthday
                );


            document
                .getElementById('profileFirstName')
                .textContent =
                    firstName;


            document
                .getElementById('profileLastName')
                .textContent =
                    lastName;


            document
                .getElementById('profileMiddleInitial')
                .textContent =
                    middleInitial || '—';


            document
                .getElementById('profileSex')
                .textContent =
                    sex;


            document
                .getElementById('profileEmail')
                .textContent =
                    email;


            document
                .getElementById('profileContact')
                .textContent =
                    contact;


            document
                .getElementById('profileAge')
                .textContent =
                    age;


            document
                .getElementById('overviewEmail')
                .textContent =
                    email;


            document
                .getElementById('overviewContact')
                .textContent =
                    contact;


            const fullName =
                [
                    firstName,
                    middleInitial,
                    lastName
                ]
                .filter(Boolean)
                .join(' ');


            document
                .getElementById('overviewFullName')
                .textContent =
                    fullName;


            document
                .getElementById('accountAvatarInitials')
                ?.replaceChildren(
                    document.createTextNode(
                        (
                            firstName.charAt(0) +
                            lastName.charAt(0)
                        )
                        .toUpperCase()
                    )
                );


            if (birthday) {

                const formatted =
                    new Date(
                        birthday + 'T00:00:00'
                    )
                    .toLocaleDateString(
                        undefined,
                        {
                            year: 'numeric',
                            month: 'long',
                            day: '2-digit'
                        }
                    );


                document
                    .getElementById('profileBirthday')
                    .textContent =
                        formatted;
            }


            closeModal(
                profileModal
            );


            showToast(
                'Personal information updated in the frontend demo.'
            );
        });


    /* ---------------------------------------------------------
       BUSINESS EDIT
    --------------------------------------------------------- */
    document
        .getElementById('openBusinessEdit')
        ?.addEventListener('click', function () {

            openModal(
                businessModal
            );
        });


    document
        .querySelectorAll('[data-close-business]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    businessModal
                );
            });
        });


    document
        .getElementById('businessEditForm')
        ?.addEventListener('submit', function (event) {

            event.preventDefault();


            const name =
                document.getElementById('editBusinessName').value.trim();

            const category =
                document.getElementById('editBusinessCategory').value;

            const description =
                document.getElementById('editBusinessDescription').value.trim();

            const email =
                document.getElementById('editBusinessEmail').value.trim();

            const phone =
                document.getElementById('editBusinessPhone').value.trim();


            document
                .getElementById('businessNameDisplay')
                .textContent =
                    name;


            document
                .getElementById('overviewBusinessName')
                .textContent =
                    name;


            document
                .getElementById('businessCategoryDisplay')
                .textContent =
                    category;


            document
                .getElementById('businessDescriptionDisplay')
                .textContent =
                    description;


            document
                .getElementById('businessEmailDisplay')
                .textContent =
                    email;


            document
                .getElementById('businessPhoneDisplay')
                .textContent =
                    phone;


            closeModal(
                businessModal
            );


            showToast(
                'Business information updated in the frontend demo.'
            );
        });


    /* ---------------------------------------------------------
       ADDRESS EDIT
    --------------------------------------------------------- */
    document
        .getElementById('openAddressEdit')
        ?.addEventListener('click', function () {

            openModal(
                addressModal
            );
        });


    document
        .querySelectorAll('[data-close-address]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    addressModal
                );
            });
        });


    document
        .getElementById('addressEditForm')
        ?.addEventListener('submit', function (event) {

            event.preventDefault();


            const province =
                document.getElementById('editProvince').value;

            const municipality =
                document.getElementById('editMunicipality').value;

            const barangay =
                document.getElementById('editBarangay').value;

            const street =
                document.getElementById('editStreet').value.trim();


            document
                .getElementById('addressProvince')
                .textContent =
                    province;


            document
                .getElementById('addressMunicipality')
                .textContent =
                    municipality;


            document
                .getElementById('addressBarangay')
                .textContent =
                    barangay;


            document
                .getElementById('addressFullDisplay')
                .textContent =
                    street +
                    ', Brgy. ' +
                    barangay +
                    ', ' +
                    municipality +
                    ', ' +
                    province;


            document
                .getElementById('overviewLocation')
                .textContent =
                    municipality +
                    ', ' +
                    province;


            closeModal(
                addressModal
            );


            showToast(
                'Address updated in the frontend demo.'
            );
        });


    /* ---------------------------------------------------------
       DOCUMENTS
    --------------------------------------------------------- */
    function openDocument(key, mode) {

        activeDocumentKey =
            key;


        documentMode =
            mode;


        const documentData =
            documents[key];


        if (!documentData) {
            return;
        }


        document
            .getElementById('documentModalTitle')
            .textContent =
                documentData.title;


        document
            .getElementById('documentModalSubtitle')
            .textContent =
                documentData.status;


        document
            .getElementById('documentFileName')
            .textContent =
                documentData.file;


        document
            .getElementById('documentReplaceArea')
            .hidden =
                mode !== 'replace';


        const fileInput =
            document.getElementById('replacementFileInput');


        if (fileInput) {
            fileInput.value = '';
        }


        openModal(
            documentModal
        );
    }


    document
        .querySelectorAll('[data-view-document]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openDocument(
                    button.dataset.viewDocument,
                    'view'
                );
            });
        });


    document
        .querySelectorAll('[data-replace-document]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openDocument(
                    button.dataset.replaceDocument,
                    'replace'
                );
            });
        });


    document
        .querySelectorAll('[data-close-document]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    documentModal
                );
            });
        });


    document
        .getElementById('demoSaveDocument')
        ?.addEventListener('click', function () {

            const file =
                document
                    .getElementById('replacementFileInput')
                    ?.files?.[0];


            if (!file) {

                showToast(
                    'Choose a replacement file first.'
                );

                return;
            }


            if (
                activeDocumentKey &&
                documents[activeDocumentKey]
            ) {

                documents[activeDocumentKey].file =
                    file.name;
            }


            document
                .getElementById('documentFileName')
                .textContent =
                    file.name;


            closeModal(
                documentModal
            );


            showToast(
                'Document replacement selected. Backend upload can be connected later.'
            );
        });


    /* ---------------------------------------------------------
       PASSWORD
    --------------------------------------------------------- */
    document
        .getElementById('openPasswordModal')
        ?.addEventListener('click', function () {

            document
                .getElementById('passwordForm')
                ?.reset();


            document
                .getElementById('passwordStrengthBar')
                .style.width =
                    '0%';


            document
                .getElementById('passwordStrengthText')
                .textContent =
                    'Use at least 8 characters.';


            document
                .getElementById('passwordMatchError')
                .hidden =
                    true;


            openModal(
                passwordModal
            );
        });


    document
        .querySelectorAll('[data-close-password]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    passwordModal
                );
            });
        });


    const newPasswordInput =
        document.getElementById('newPassword');


    newPasswordInput
        ?.addEventListener('input', function () {

            const value =
                newPasswordInput.value;


            let score =
                0;


            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;


            const widths =
                ['0%', '25%', '50%', '75%', '100%'];


            const labels = [
                'Use at least 8 characters.',
                'Weak',
                'Fair',
                'Good',
                'Strong'
            ];


            const bar =
                document.getElementById('passwordStrengthBar');


            bar.style.width =
                widths[score];


            bar.className =
                'h-full rounded-full transition-all ' +
                (
                    score <= 1
                        ? 'bg-red-400'
                        : score === 2
                            ? 'bg-yellow'
                            : 'bg-teal'
                );


            document
                .getElementById('passwordStrengthText')
                .textContent =
                    labels[score];
        });


    document
        .getElementById('passwordForm')
        ?.addEventListener('submit', function (event) {

            event.preventDefault();


            const newPassword =
                document.getElementById('newPassword').value;

            const confirmPassword =
                document.getElementById('confirmNewPassword').value;

            const matchError =
                document.getElementById('passwordMatchError');


            if (
                newPassword !== confirmPassword
            ) {

                matchError.hidden =
                    false;

                return;
            }


            matchError.hidden =
                true;


            closeModal(
                passwordModal
            );


            showToast(
                'Password change validated in the frontend demo.'
            );
        });


    /* ---------------------------------------------------------
       NOTIFICATION PREFERENCES
    --------------------------------------------------------- */
    document
        .getElementById('saveNotificationPreferences')
        ?.addEventListener('click', function () {

            const selected =
                {};


            document
                .querySelectorAll('[data-notification-setting]')
                .forEach(function (input) {

                    selected[
                        input.dataset.notificationSetting
                    ] =
                        input.checked;
                });


            console.log(
                'Demo notification preferences:',
                selected
            );


            showToast(
                'Notification preferences saved in the frontend demo.'
            );
        });


    /* ---------------------------------------------------------
       DEACTIVATE
    --------------------------------------------------------- */
    document
        .getElementById('openDeactivateModal')
        ?.addEventListener('click', function () {

            const checkbox =
                document.getElementById('confirmDeactivateCheckbox');


            checkbox.checked =
                false;


            document
                .getElementById('confirmDeactivateButton')
                .disabled =
                    true;


            openModal(
                deactivateModal
            );
        });


    document
        .querySelectorAll('[data-close-deactivate]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                closeModal(
                    deactivateModal
                );
            });
        });


    document
        .getElementById('confirmDeactivateCheckbox')
        ?.addEventListener('change', function (event) {

            document
                .getElementById('confirmDeactivateButton')
                .disabled =
                    !event.target.checked;
        });


    document
        .getElementById('confirmDeactivateButton')
        ?.addEventListener('click', function () {

            closeModal(
                deactivateModal
            );


            showToast(
                'Deactivation is only a demo. No account was changed.'
            );
        });


    /* ---------------------------------------------------------
       ESCAPE
    --------------------------------------------------------- */
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }


        const openModals = [
            profileModal,
            businessModal,
            addressModal,
            passwordModal,
            documentModal,
            deactivateModal
        ];


        const openModalElement =
            openModals.find(
                modal =>
                    modal &&
                    !modal.hidden
            );


        if (openModalElement) {

            closeModal(
                openModalElement
            );
        }
    });


    showSection('profile');
});
</script>
@endpush

@endsection
