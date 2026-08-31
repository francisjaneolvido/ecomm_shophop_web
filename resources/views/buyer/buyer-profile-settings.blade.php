{{--
    resources/views/buyer/buyer-profile-settings.blade.php

    Buyer Account Settings — full version.
    Tabs: Profile Info, Address Book, Allergens & Diet, Security,
    Privacy & Account, Vouchers, Chats, Activity Log, Notifications,
    Language & Region, Switch Account, Become a Seller, Help Center.
    Plus Log Out.

    NOTE ON ROUTES: most action URLs below are plain "#" with a
    "TODO: route(...)" comment — wala ka pa kasing backend routes/
    controllers para dito, so calling route() directly would crash
    the page (same error you hit earlier). Once you add a route,
    just swap the "#" for {{ Route::has('name') ? route('name') : '#' }}
    so it never hard-crashes even if the route name is off.

    Sample/demo arrays below ($sampleAddresses, $sampleVouchers, etc.)
    are placeholder data so you can see the UI populated. Swap them
    for real Eloquent data once your controllers are ready.
--}}
@extends('layouts.app')

@section('title', 'Account Settings - ShopHop')

{{--
    This page belongs to the Buyer area, so hide the default landing-page
    navbar/footer from layouts.app and render the Buyer chrome below.
--}}
@section('hideChrome', true)

@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }

    .settings-shell {
        background-image:
            radial-gradient(circle at 8% 0%, rgba(20, 184, 166, 0.08), transparent 28rem),
            radial-gradient(circle at 100% 20%, rgba(15, 27, 61, 0.05), transparent 24rem);
    }

    .settings-panel:not(.hidden) {
        animation: settingsPanelIn 180ms ease-out;
    }

    @keyframes settingsPanelIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .settings-shell input,
    .settings-shell select,
    .settings-shell textarea,
    .settings-shell button,
    .settings-shell a {
        -webkit-tap-highlight-color: transparent;
    }

    .settings-shell input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .settings-shell select,
    .settings-shell textarea {
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .settings-tab-btn:focus-visible,
    .settings-shell button:focus-visible,
    .settings-shell a:focus-visible,
    .settings-shell input:focus-visible,
    .settings-shell select:focus-visible,
    .settings-shell textarea:focus-visible {
        outline: 2px solid rgba(20, 184, 166, 0.75);
        outline-offset: 2px;
    }

    .settings-sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 27, 61, 0.18) transparent;
    }

    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }

        .settings-panel:not(.hidden) {
            animation: none;
        }
    }
</style>
@endpush

@php
    $user = auth()->user();

    $displayName = trim(
        ($user->first_name ?? 'Buyer') . ' ' . ($user->last_name ?? '')
    );

    $commonAllergens = [
        'Peanuts', 'Tree Nuts', 'Milk / Dairy', 'Eggs', 'Shellfish',
        'Fish', 'Soy', 'Wheat / Gluten', 'Sesame',
    ];

    $rawAllergens = $user->allergens ?? [];
    $userAllergens = is_array($rawAllergens)
        ? $rawAllergens
        : (json_decode($rawAllergens, true) ?: []);

    $normalizedAllergens = collect($userAllergens)
        ->map(function ($allergen) {
            if (is_array($allergen)) {
                return [
                    'name' => $allergen['name'] ?? '',
                    'severity' => $allergen['severity'] ?? 'mild',
                ];
            }

            return [
                'name' => $allergen,
                'severity' => 'mild',
            ];
        })
        ->filter(fn ($allergen) => filled($allergen['name']))
        ->values();

    // ---- Sample/demo data — replace with real queries later ----
    $sampleAddresses = [
        ['id' => 1, 'label' => 'Home', 'name' => $displayName, 'phone' => '0917 123 4567', 'full' => 'Blk 12 Lot 5, Molino Blvd., Brgy. Molino III, Bacoor, Cavite', 'default' => true],
        ['id' => 2, 'label' => 'Work', 'name' => $displayName, 'phone' => '0917 123 4567', 'full' => 'Unit 4B, ABC Tower, Ayala Ave., Makati City', 'default' => false],
    ];

    $sampleVouchers = [
        ['code' => 'SHOP100', 'title' => '₱100 Off', 'desc' => 'Min. spend ₱1,000', 'expiry' => 'Sep 30, 2026', 'status' => 'active'],
        ['code' => 'FREESHIP', 'title' => 'Free Shipping', 'desc' => 'No minimum spend', 'expiry' => 'Aug 31, 2026', 'status' => 'active'],
        ['code' => 'ELEC20', 'title' => '20% Off Electronics', 'desc' => 'Max discount ₱500', 'expiry' => 'Jul 15, 2026', 'status' => 'expired'],
    ];

    $sampleChats = [
        ['seller' => 'TechHub PH', 'message' => 'Your order has been shipped na po!', 'time' => '2h ago', 'unread' => 2],
        ['seller' => 'Cavite Pet Supplies', 'message' => 'Thank you for your order!', 'time' => '1d ago', 'unread' => 0],
        ['seller' => 'ShopHop Support', 'message' => 'We\'ve refunded your cancelled item.', 'time' => '3d ago', 'unread' => 0],
    ];

    $activeVoucherCount = collect($sampleVouchers)
        ->where('status', 'active')
        ->count();

    $unreadChatCount = collect($sampleChats)
        ->sum('unread');

    $sampleActivity = [
        ['action' => 'Logged in', 'detail' => 'Chrome on Windows · Bacoor, Cavite', 'time' => 'Today, 9:42 AM'],
        ['action' => 'Changed password', 'detail' => 'Security settings updated', 'time' => 'Aug 20, 2026'],
        ['action' => 'Redeemed voucher', 'detail' => 'SHOP100 applied to Order #10234', 'time' => 'Aug 18, 2026'],
        ['action' => 'Added address', 'detail' => 'New address "Work" saved', 'time' => 'Aug 15, 2026'],
    ];

    $linkedAccounts = [
        ['id' => 1, 'name' => $user->first_name ?? 'Yesel Ann Alegre', 'email' => $user->email ?? 'buyer@shophop.com', 'type' => 'Buyer', 'active' => true],
        ['id' => 2, 'name' => 'Yesel Store PH', 'email' => 'yeselstore@shophop.com', 'type' => 'Seller', 'active' => false],
    ];

    $twoFactorEnabled = (bool) ($user->two_factor_enabled ?? false);

    $birthdayValue = old(
        'birthday',
        filled($user->birthday ?? null)
            ? \Illuminate\Support\Carbon::parse($user->birthday)->format('Y-m-d')
            : ''
    );
@endphp

@section('content')

<a
    href="#settings-main"
    class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:text-navy focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg"
>
    Skip to account settings
</a>

@include('buyer.partials.navbar-buyer')

<section id="settings-main" class="settings-shell bg-gray-bg min-h-screen py-5 sm:py-7 lg:py-8 scroll-mt-20">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page heading / account overview --}}
        <div class="relative overflow-hidden bg-white border border-gray-border rounded-2xl shadow-sm mb-5 sm:mb-6">
            <div class="pointer-events-none absolute -top-20 -right-14 w-56 h-56 rounded-full bg-teal/10"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 w-48 h-48 rounded-full bg-navy/5"></div>

            <div class="relative p-4 sm:p-5 lg:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-[10px] sm:text-xs font-bold text-teal-dark uppercase tracking-[0.16em]">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                            Buyer Account Center
                        </div>

                        <h1 class="text-navy text-2xl sm:text-[28px] lg:text-3xl font-bold leading-tight mt-1.5">
                            Account Settings
                        </h1>

                        <p class="text-xs sm:text-sm text-navy/50 mt-1.5 max-w-2xl leading-relaxed">
                            Keep your personal information, delivery details, security, shopping preferences, and account activity in one place.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 lg:justify-end">
                        <div class="flex items-center gap-3 min-w-0 rounded-xl bg-gray-bg/80 border border-gray-border px-3 py-2.5">
                            <img
                                src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                                alt="{{ $displayName }}"
                                class="w-9 h-9 rounded-full object-cover border border-white shadow-sm shrink-0"
                            >
                            <div class="min-w-0">
                                <p class="text-xs sm:text-sm font-semibold text-navy truncate">{{ $displayName }}</p>
                                <p class="text-[10px] sm:text-[11px] text-navy/45 truncate">{{ $user->email ?? 'Buyer account' }}</p>
                            </div>
                            <span class="hidden sm:inline-flex text-[9px] font-bold uppercase tracking-wide text-teal-dark bg-teal-light px-2 py-1 rounded-md shrink-0">
                                Buyer
                            </span>
                        </div>

                        <a
                            href="{{ Route::has('buyer.dashboard') ? route('buyer.dashboard') : '#' }}"
                            class="inline-flex items-center justify-center gap-1.5
                                   text-xs font-semibold text-navy/65 hover:text-teal-dark
                                   bg-white hover:bg-teal-light/30 border border-gray-border hover:border-teal/30
                                   rounded-xl px-3.5 py-2.5 transition-all"
                        >
                            <x-lucide-arrow-left class="w-3.5 h-3.5" />
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact mobile navigation --}}
        <div class="lg:hidden sticky top-16 z-40 bg-white/95 backdrop-blur border border-gray-border rounded-xl p-3 mb-4 shadow-sm">
            <label for="settingsMobileSelect" class="block text-[10px] font-bold text-navy/40 uppercase tracking-[0.12em] mb-1.5">
                Settings section
            </label>

            <div class="relative">
                <select
                    id="settingsMobileSelect"
                    class="w-full appearance-none bg-gray-bg border border-gray-border
                           rounded-lg px-3 py-2.5 pr-9 text-sm font-semibold text-navy
                           focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal"
                >
                    <option value="profile">Profile Info</option>
                    <option value="address">Address Book</option>
                    <option value="allergens">Allergens & Diet</option>
                    <option value="security">Security</option>
                    <option value="privacy">Privacy & Account</option>
                    <option value="vouchers">Vouchers ({{ $activeVoucherCount }})</option>
                    <option value="chats">Chats ({{ $unreadChatCount }})</option>
                    <option value="activity">Activity Log</option>
                    <option value="notifications">Notifications</option>
                    <option value="language">Language & Region</option>
                    <option value="switch-account">Switch Account</option>
                    <option value="become-seller">Become a Seller</option>
                    <option value="help">Help Center</option>
                </select>

                <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40" />
            </div>
        </div>

        <div class="grid lg:grid-cols-[240px_minmax(0,1fr)] gap-4 lg:gap-6 items-start">

            {{-- =========================================================
                SIDEBAR NAV
            ========================================================= --}}
            <aside class="settings-sidebar-scroll hidden lg:block bg-white rounded-2xl border border-gray-border shadow-sm p-2.5 lg:sticky lg:top-20 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto">

                <div class="flex items-center gap-2.5 px-2.5 py-2.5 mb-2 border-b border-gray-border">
                    <img
                        id="avatarPreviewMini"
                        src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                        alt="Your avatar"
                        class="w-9 h-9 rounded-full object-cover border border-gray-border shrink-0"
                    >
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-navy truncate">
                            {{ $user->first_name ?? 'Buyer' }} {{ $user->last_name ?? '' }}
                        </p>
                        <p class="text-[11px] text-navy/45 truncate">
                            {{ $user->email ?? '' }}
                        </p>
                        <span class="inline-flex mt-1 text-[8px] font-bold uppercase tracking-wide text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">
                            Buyer
                        </span>
                    </div>
                </div>

                <nav
                    data-settings-nav
                    class="flex flex-col gap-0.5"
                >
                    <p class="hidden lg:block text-[9px] font-bold text-navy/35 uppercase tracking-[0.14em] px-2.5 pt-2 pb-1">Account</p>
                    <button type="button" data-tab-btn="profile"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-white bg-teal transition-colors duration-200">
                        <x-lucide-user class="w-3.5 h-3.5" /> Profile Info
                    </button>
                    <button type="button" data-tab-btn="address"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-map-pin class="w-4 h-4" /> Address Book
                    </button>
                    <button type="button" data-tab-btn="allergens"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-shield-alert class="w-4 h-4" /> Allergens & Diet
                    </button>
                    <button type="button" data-tab-btn="security"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-lock class="w-4 h-4" /> Security
                    </button>
                    <button type="button" data-tab-btn="privacy"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-shield class="w-4 h-4" /> Privacy & Account
                    </button>

                    <p class="hidden lg:block text-[9px] font-bold text-navy/35 uppercase tracking-[0.14em] px-2.5 pt-3 pb-1">Shopping</p>
                    <button type="button" data-tab-btn="vouchers"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-ticket class="w-4 h-4" />
                        <span class="flex-1 text-left">Vouchers</span>
                        @if ($activeVoucherCount > 0)
                            <span class="min-w-5 h-5 px-1.5 rounded-md bg-teal-light text-teal-dark text-[9px] font-bold flex items-center justify-center">
                                {{ $activeVoucherCount }}
                            </span>
                        @endif
                    </button>
                    <button type="button" data-tab-btn="chats"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-message-circle class="w-4 h-4" />
                        <span class="flex-1 text-left">Chats</span>
                        @if ($unreadChatCount > 0)
                            <span class="min-w-5 h-5 px-1.5 rounded-md bg-teal text-white text-[9px] font-bold flex items-center justify-center">
                                {{ $unreadChatCount }}
                            </span>
                        @endif
                    </button>
                    <button type="button" data-tab-btn="activity"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-history class="w-4 h-4" /> Activity Log
                    </button>

                    <p class="hidden lg:block text-[9px] font-bold text-navy/35 uppercase tracking-[0.14em] px-2.5 pt-3 pb-1">Preferences</p>
                    <button type="button" data-tab-btn="notifications"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-bell class="w-4 h-4" /> Notifications
                    </button>
                    <button type="button" data-tab-btn="language"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-globe class="w-4 h-4" /> Language & Region
                    </button>

                    <p class="hidden lg:block text-[9px] font-bold text-navy/35 uppercase tracking-[0.14em] px-2.5 pt-3 pb-1">More</p>
                    <button type="button" data-tab-btn="switch-account"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-repeat class="w-4 h-4" /> Switch Account
                    </button>
                    <button type="button" data-tab-btn="become-seller"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-store class="w-4 h-4" /> Become a Seller
                    </button>
                    <button type="button" data-tab-btn="help"
                        class="settings-tab-btn flex items-center gap-2 shrink-0 w-full px-2.5 py-2 rounded-lg text-[12px] font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-help-circle class="w-4 h-4" /> Help Center
                    </button>
                </nav>

                <button type="button" data-confirm-action="logout"
                    class="w-full mt-2 pt-2.5 border-t border-gray-border flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-semibold text-red-500 hover:bg-red-50 transition-colors duration-200">
                    <x-lucide-log-out class="w-4 h-4" /> Log Out
                </button>
            </aside>

            {{-- =========================================================
                CONTENT
            ========================================================= --}}
            <div class="min-w-0 space-y-4 sm:space-y-5">

                {{-- Form feedback --}}
                @if (session('status'))
                    <div class="flex items-start gap-2.5 bg-teal-light/60 border border-teal/20 rounded-xl px-3.5 py-3 text-xs sm:text-sm text-teal-dark">
                        <x-lucide-badge-check class="w-4 h-4 shrink-0 mt-0.5" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flex items-start gap-2.5 bg-red-50 border border-red-200 rounded-xl px-3.5 py-3">
                        <x-lucide-alert-triangle class="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-red-600">
                                Please review the highlighted information.
                            </p>
                            <p class="text-[11px] text-red-500/80 mt-0.5">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- =====================================================
                    PROFILE INFO TAB
                ===================================================== --}}
                <div data-tab-content="profile" class="settings-panel">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Profile Information</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">
                            This is how you appear across ShopHop.
                        </p>

                        <form method="POST" action="{{ Route::has('buyer.settings.profile.update') ? route('buyer.settings.profile.update') : '#' }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- Avatar --}}
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <img
                                        id="avatarPreviewLarge"
                                        src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                                        alt="Avatar preview"
                                        class="w-16 h-16 rounded-full object-cover border border-gray-border"
                                    >
                                    <label
                                        for="avatarInput"
                                        class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-teal hover:bg-teal-dark text-white flex items-center justify-center cursor-pointer shadow-md transition-colors"
                                    >
                                        <x-lucide-camera class="w-3.5 h-3.5" />
                                    </label>
                                    <input id="avatarInput" name="avatar" type="file" accept="image/*" class="hidden">
                                </div>
                                <div class="text-xs text-navy/50">
                                    JPG or PNG. Max 2MB.
                                </div>
                            </div>

                            {{-- Name row --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Last Name *</label>
                                    <input type="text" name="last_name" autocomplete="family-name" value="{{ old('last_name', $user->last_name ?? '') }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">First Name *</label>
                                    <input type="text" name="first_name" autocomplete="given-name" value="{{ old('first_name', $user->first_name ?? '') }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Middle Initial</label>
                                    <input type="text" name="middle_initial" autocomplete="additional-name" maxlength="3" value="{{ old('middle_initial', $user->middle_initial ?? '') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            {{-- Sex + Birthday + Age --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Sex *</label>
                                    <div class="flex gap-4 pt-2.5">
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Male" class="accent-teal" {{ old('sex', $user->sex ?? '') === 'Male' ? 'checked' : '' }}>
                                            Male
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Female" class="accent-teal" {{ old('sex', $user->sex ?? '') === 'Female' ? 'checked' : '' }}>
                                            Female
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Birthday *</label>
                                    <input id="birthdayInput" type="date" name="birthday" autocomplete="bday" value="{{ $birthdayValue }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Age</label>
                                    <input id="ageInput" type="text" readonly value="{{ $user->age ?? '' }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border bg-gray-bg text-sm text-navy/60">
                                </div>
                            </div>

                            {{-- Email + Contact --}}
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">E-mail *</label>
                                    <input type="email" name="email" autocomplete="email" value="{{ old('email', $user->email ?? '') }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                                    <input type="tel" name="contact_no" autocomplete="tel" value="{{ old('contact_no', $user->contact_no ?? '') }}" placeholder="09XX XXX XXXX" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            {{-- Valid ID --}}
                            <div class="pt-2 border-t border-gray-border">
                                <label class="block text-xs font-semibold text-navy/70 mb-2">Valid ID on File</label>
                                <div class="flex flex-wrap items-center gap-4">
                                    <img
                                        src="{{ $user->id_document_url ?? asset('images/id-placeholder.png') }}"
                                        alt="Uploaded ID"
                                        class="w-28 h-18 object-cover rounded-lg border border-gray-border"
                                    >

                                    @php $idStatus = $user->id_verification_status ?? 'pending'; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $idStatus === 'verified' ? 'bg-teal-light text-teal-dark' : ($idStatus === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                                        <x-lucide-badge-check class="w-3.5 h-3.5" />
                                        {{ ucfirst($idStatus) }}
                                    </span>

                                    <label for="idInput" class="text-xs font-semibold text-teal-dark hover:text-teal cursor-pointer underline underline-offset-2">
                                        Re-upload ID
                                    </label>
                                    <input id="idInput" name="valid_id" type="file" accept="image/*,.pdf" class="hidden">
                                </div>
                                <p class="text-[11px] text-navy/45 mt-2">
                                    Re-uploading your ID will require re-verification by the administrator.
                                </p>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    ADDRESS BOOK TAB
                ===================================================== --}}
                <div data-tab-content="address" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Address Book</h2>
                                <p class="text-sm text-navy/55">
                                    Manage where your orders get delivered.
                                </p>
                            </div>
                            <button type="button" id="addAddressBtn"
                                class="inline-flex items-center gap-1.5 bg-navy hover:bg-navy/90 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-colors duration-200 shrink-0">
                                <x-lucide-plus class="w-4 h-4" /> Add New Address
                            </button>
                        </div>

                        <div id="addressList" class="space-y-3">
                            @foreach ($sampleAddresses as $address)
                                <div data-address-card data-address-id="{{ $address['id'] }}" class="border border-gray-border rounded-xl p-3.5 hover:border-teal/30 hover:bg-gray-bg/40 transition-colors">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-teal-light text-teal-dark">
                                                    {{ $address['label'] }}
                                                </span>
                                                @if ($address['default'])
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-navy/50">
                                                        <x-lucide-star class="w-3 h-3 fill-current" /> Default
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm font-semibold text-navy">{{ $address['name'] }} &middot; {{ $address['phone'] }}</p>
                                            <p class="text-sm text-navy/60 mt-0.5">{{ $address['full'] }}</p>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            @if (! $address['default'])
                                                <button type="button" data-set-default="{{ $address['id'] }}" class="text-xs font-semibold text-navy/60 hover:text-teal-dark">
                                                    Set as Default
                                                </button>
                                            @endif
                                            <button type="button" data-edit-address="{{ $address['id'] }}" class="text-navy/50 hover:text-teal-dark" aria-label="Edit address">
                                                <x-lucide-pencil class="w-4 h-4" />
                                            </button>
                                            <button type="button"
                                                data-confirm-action="remove-address"
                                                data-confirm-message="This will remove the &quot;{{ $address['label'] }}&quot; address from your address book."
                                                data-target-id="{{ $address['id'] }}"
                                                class="text-navy/50 hover:text-red-500" aria-label="Remove address">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p id="noAddressesNote" class="hidden text-sm text-navy/40 italic text-center py-6">
                            No saved addresses yet.
                        </p>
                    </div>
                </div>

                {{-- =====================================================
                    ALLERGENS & PREFERENCES TAB
                ===================================================== --}}
                <div data-tab-content="allergens" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-9 h-9 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-shield-alert class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-navy text-base sm:text-lg font-bold">Allergens & Dietary Preferences</h2>
                                <p class="text-sm text-navy/55 mt-1">
                                    Tell us what you're allergic to. Sellers disclose ingredients on food
                                    products, and we'll warn you at checkout if something you're ordering
                                    matches an allergen on this list.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ Route::has('buyer.settings.allergens.update') ? route('buyer.settings.allergens.update') : '#' }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Common allergens — tap to add</label>
                                <div id="commonAllergenChips" class="flex flex-wrap gap-2">
                                    @foreach ($commonAllergens as $allergen)
                                        <button type="button" data-common-allergen="{{ $allergen }}"
                                            class="chip-toggle px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-border bg-white text-navy/70 hover:border-teal transition-colors duration-200">
                                            {{ $allergen }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Something else? Add it manually</label>
                                <div class="flex gap-2">
                                    <input id="customAllergenInput" type="text" placeholder="e.g. Mangoes, MSG, Food dye..."
                                        class="flex-1 px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                    <button type="button" id="addCustomAllergenBtn"
                                        class="inline-flex items-center gap-1.5 bg-navy hover:bg-navy/90 text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                        <x-lucide-plus class="w-4 h-4" /> Add
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <label class="block text-xs font-semibold text-navy/70">Your allergens</label>
                                    <span class="text-[11px] text-navy/40">Tap the severity word to cycle Mild → Moderate → Severe</span>
                                </div>
                                <div id="selectedAllergenList" class="flex flex-wrap gap-2 min-h-11"></div>
                                <p id="noAllergensNote" class="text-xs text-navy/40 italic">No allergens added yet.</p>
                            </div>

                            <div id="allergenHiddenInputs"></div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    SECURITY TAB
                ===================================================== --}}
                <div data-tab-content="security" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6 space-y-8">

                        <div>
                            <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Security</h2>
                            <p class="text-xs sm:text-sm text-navy/50 mb-4">
                                Update your password to keep your account secure.
                            </p>

                            <form id="passwordForm" method="POST" action="{{ Route::has('buyer.settings.password.update') ? route('buyer.settings.password.update') : '#' }}" class="space-y-4 max-w-md">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Current Password *</label>
                                    <input type="password" name="current_password" autocomplete="current-password" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">New Password *</label>
                                    <input id="newPassword" type="password" name="password" autocomplete="new-password" required minlength="8"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Confirm New Password *</label>
                                    <input id="confirmPassword" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                    <p id="passwordMismatchNote" class="hidden text-xs text-red-500 mt-1.5">Passwords do not match.</p>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="pt-5 border-t border-gray-border">
                            <label class="flex items-center justify-between gap-4 cursor-pointer max-w-lg border border-gray-border rounded-xl p-3.5 hover:bg-gray-bg/50 transition-colors">
                                <span>
                                    <span class="block text-sm font-semibold text-navy">Two-Factor Authentication</span>
                                    <span class="block text-xs text-navy/50 mt-0.5">Adds an extra verification step when logging in.</span>
                                </span>
                                <span class="relative inline-flex items-center shrink-0">
                                    <input type="checkbox" name="two_factor_enabled" value="1" {{ $twoFactorEnabled ? 'checked' : '' }} class="peer sr-only">
                                    <span class="w-11 h-6 rounded-full bg-gray-border peer-checked:bg-teal transition-colors duration-200"></span>
                                    <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    PRIVACY & ACCOUNT OWNERSHIP TAB
                ===================================================== --}}
                <div data-tab-content="privacy" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6 space-y-8">

                        <div>
                            <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Privacy & Account</h2>
                            <p class="text-sm text-navy/55">
                                Control your data and how visible your activity is to others.
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4 max-w-xl border border-gray-border rounded-xl p-3.5">
                            <div>
                                <p class="text-sm font-semibold text-navy">Download My Data</p>
                                <p class="text-xs text-navy/50 mt-0.5">Get a copy of your profile, orders, and activity.</p>
                            </div>
                            <button type="button" class="inline-flex items-center gap-1.5 border border-navy text-navy text-xs font-semibold px-3.5 py-2 rounded-lg hover:bg-navy hover:text-white transition-colors duration-200 shrink-0">
                                <x-lucide-download class="w-4 h-4" /> Request
                            </button>
                        </div>

                        <label class="flex items-center justify-between gap-4 cursor-pointer max-w-xl border border-gray-border rounded-xl p-3.5 hover:bg-gray-bg/50 transition-colors">
                            <span>
                                <span class="block text-sm font-semibold text-navy">Show My Review Activity Publicly</span>
                                <span class="block text-xs text-navy/50 mt-0.5">Other buyers can see reviews you've posted.</span>
                            </span>
                            <span class="relative inline-flex items-center shrink-0">
                                <input type="checkbox" name="public_reviews" value="1" checked class="peer sr-only">
                                <span class="w-11 h-6 rounded-full bg-gray-border peer-checked:bg-teal transition-colors duration-200"></span>
                                <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                            </span>
                        </label>

                        {{-- Danger zone --}}
                        <div class="pt-5 border-t border-gray-border">
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wide mb-4">Danger Zone</p>

                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">Deactivate Account</p>
                                        <p class="text-xs text-navy/55 mt-0.5">Temporarily hide your profile. Log back in anytime to reactivate.</p>
                                    </div>
                                    <button type="button" data-confirm-action="deactivate"
                                        class="inline-flex items-center gap-1.5 bg-white border border-amber-500 text-amber-700 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-500 hover:text-white transition-colors duration-200 shrink-0">
                                        <x-lucide-power class="w-4 h-4" /> Deactivate
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">Delete Account</p>
                                        <p class="text-xs text-navy/55 mt-0.5">Permanently erase your account, orders, and saved data. Cannot be undone.</p>
                                    </div>
                                    <button type="button" data-confirm-action="delete"
                                        class="inline-flex items-center gap-1.5 bg-white border border-red-500 text-red-600 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg hover:bg-red-500 hover:text-white transition-colors duration-200 shrink-0">
                                        <x-lucide-trash-2 class="w-4 h-4" /> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    VOUCHERS TAB
                ===================================================== --}}
                <div data-tab-content="vouchers" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">My Vouchers</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Discounts and perks available on your account.</p>

                        <div class="flex gap-2 mb-4 max-w-md">
                            <input type="text" placeholder="Enter voucher code"
                                class="flex-1 px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            <button type="button" class="bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                Redeem
                            </button>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach ($sampleVouchers as $voucher)
                                @php $expired = $voucher['status'] === 'expired'; @endphp
                                <div class="relative border border-dashed rounded-xl p-3.5 {{ $expired ? 'border-gray-border opacity-50' : 'border-teal bg-teal-light/20' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-bold text-navy">{{ $voucher['title'] }}</p>
                                            <p class="text-xs text-navy/55 mt-0.5">{{ $voucher['desc'] }}</p>
                                        </div>
                                        <x-lucide-ticket class="w-5 h-5 {{ $expired ? 'text-navy/30' : 'text-teal' }} shrink-0" />
                                    </div>
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-dashed border-gray-border">
                                        <span class="text-[11px] font-mono text-navy/50">{{ $voucher['code'] }}</span>
                                        <span class="text-[11px] {{ $expired ? 'text-navy/40' : 'text-navy/50' }}">
                                            {{ $expired ? 'Expired' : 'Valid until' }} {{ $voucher['expiry'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    CHATS TAB
                ===================================================== --}}
                <div data-tab-content="chats" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Chats</h2>
                                <p class="text-sm text-navy/55">Recent conversations with sellers and support.</p>
                            </div>
                            <a href="#" class="text-xs sm:text-sm font-semibold text-teal-dark hover:text-teal shrink-0">
                                Open Full Chat
                            </a>
                        </div>

                        <div class="divide-y divide-gray-border">
                            @foreach ($sampleChats as $chat)
                                <a href="#" class="flex items-center gap-3 py-3 hover:bg-gray-bg -mx-2 px-2 rounded-lg transition-colors duration-150">
                                    <div class="w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($chat['seller'], 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-navy truncate">{{ $chat['seller'] }}</p>
                                        <p class="text-xs text-navy/55 truncate">{{ $chat['message'] }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[11px] text-navy/40">{{ $chat['time'] }}</p>
                                        @if ($chat['unread'] > 0)
                                            <span class="inline-flex items-center justify-center mt-1 w-4.5 h-4.5 rounded-full bg-teal text-white text-[10px] font-bold">
                                                {{ $chat['unread'] }}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    ACTIVITY LOG TAB
                ===================================================== --}}
                <div data-tab-content="activity" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Activity Log</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">A history of actions taken on your account.</p>

                        <div class="space-y-0">
                            @foreach ($sampleActivity as $i => $entry)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <span class="w-2.5 h-2.5 rounded-full bg-teal shrink-0 mt-1.5"></span>
                                        @if (! $loop->last)
                                            <span class="w-px flex-1 bg-gray-border"></span>
                                        @endif
                                    </div>
                                    <div class="pb-5">
                                        <p class="text-sm font-semibold text-navy">{{ $entry['action'] }}</p>
                                        <p class="text-xs text-navy/55 mt-0.5">{{ $entry['detail'] }}</p>
                                        <p class="text-[11px] text-navy/40 mt-1">{{ $entry['time'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    NOTIFICATIONS TAB
                ===================================================== --}}
                <div data-tab-content="notifications" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Notifications</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Choose what ShopHop can notify you about.</p>

                        <form method="POST" action="{{ Route::has('buyer.settings.notifications.update') ? route('buyer.settings.notifications.update') : '#' }}" class="space-y-1">
                            @csrf
                            @method('PATCH')

                            @foreach ([
                                ['name' => 'order_updates', 'label' => 'Order Updates', 'desc' => 'Shipping, delivery, and status changes.', 'checked' => true],
                                ['name' => 'promotions', 'label' => 'Promotions & Deals', 'desc' => 'Vouchers, discounts, and flash sales.', 'checked' => true],
                                ['name' => 'chat_messages', 'label' => 'Chat Messages', 'desc' => 'New messages from sellers or support.', 'checked' => true],
                                ['name' => 'price_drops', 'label' => 'Price Drop Alerts', 'desc' => 'When items in your cart or wishlist go on sale.', 'checked' => false],
                            ] as $pref)
                                <label class="flex items-center justify-between gap-4 py-3.5 border-b border-gray-border last:border-b-0 cursor-pointer">
                                    <span>
                                        <span class="block text-sm font-semibold text-navy">{{ $pref['label'] }}</span>
                                        <span class="block text-xs text-navy/50 mt-0.5">{{ $pref['desc'] }}</span>
                                    </span>
                                    <span class="relative inline-flex items-center shrink-0">
                                        <input type="checkbox" name="{{ $pref['name'] }}" value="1" {{ $pref['checked'] ? 'checked' : '' }} class="peer sr-only">
                                        <span class="w-11 h-6 rounded-full bg-gray-border peer-checked:bg-teal transition-colors duration-200"></span>
                                        <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            @endforeach

                            <div class="flex justify-end pt-4">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    LANGUAGE & REGION TAB
                ===================================================== --}}
                <div data-tab-content="language" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Language & Region</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Set how ShopHop displays content for you.</p>

                        <form method="POST" action="{{ Route::has('buyer.settings.language.update') ? route('buyer.settings.language.update') : '#' }}" class="space-y-4 max-w-md">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Language</label>
                                <select name="language"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                    <option value="en" {{ ($user->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="fil" {{ ($user->language ?? '') === 'fil' ? 'selected' : '' }}>Filipino</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Currency</label>
                                <input type="text" value="₱ Philippine Peso" disabled
                                    class="w-full px-3 py-2 rounded-lg border border-gray-border bg-gray-bg text-sm text-navy/50">
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    SWITCH ACCOUNT TAB
                ===================================================== --}}
                <div data-tab-content="switch-account" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Switch Account</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Accounts linked to this device.</p>

                        <div class="space-y-3">
                            @foreach ($linkedAccounts as $account)
                                <div class="flex items-center justify-between gap-3 border rounded-xl p-3.5 {{ $account['active'] ? 'border-teal bg-teal-light/30' : 'border-gray-border' }}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($account['name'], 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-navy truncate">{{ $account['name'] }}</p>
                                            <p class="text-xs text-navy/50 truncate">{{ $account['email'] }} &middot; {{ $account['type'] }}</p>
                                        </div>
                                    </div>

                                    @if ($account['active'])
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-teal-dark shrink-0">
                                            <x-lucide-badge-check class="w-4 h-4" /> Active
                                        </span>
                                    @else
                                        <button type="button"
                                            data-confirm-action="switch-account"
                                            data-confirm-message="Switch to &quot;{{ $account['name'] }}&quot;? You'll be logged out of your current session."
                                            class="text-xs sm:text-sm font-semibold text-navy border border-navy px-3.5 py-1.5 rounded-lg hover:bg-navy hover:text-white transition-colors duration-200 shrink-0">
                                            Switch
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-teal-dark hover:text-teal">
                            <x-lucide-plus class="w-4 h-4" /> Add Another Account
                        </button>
                    </div>
                </div>

                {{-- =====================================================
                    BECOME A SELLER TAB
                ===================================================== --}}
                <div data-tab-content="become-seller" class="settings-panel hidden">
                    <div class="relative overflow-hidden bg-navy rounded-xl border border-navy shadow-sm p-5 sm:p-6 text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 text-teal flex items-center justify-center shrink-0">
                                <x-lucide-store class="w-6 h-6" />
                            </div>
                            <div>
                                <h2 class="text-white text-lg sm:text-xl font-bold mb-1">Turn Your Passion Into Profit</h2>
                                <p class="text-xs sm:text-sm text-white/55 max-w-lg">
                                    Start selling on ShopHop and reach buyers all over the country.
                                    Your buyer account stays active — you'll just get a Seller Center too.
                                </p>
                                <button type="button" data-confirm-action="become-seller"
                                    class="mt-4 inline-flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                                    Get Started <x-lucide-store class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                    HELP CENTER TAB
                ===================================================== --}}
                <div data-tab-content="help" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Help Center</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Get support or learn more about ShopHop.</p>

                        <div class="divide-y divide-gray-border">
                            @foreach ([
                                'Frequently Asked Questions',
                                'Contact Support',
                                'Report a Problem',
                                'Terms of Service',
                                'Privacy Policy',
                            ] as $link)
                                <a href="#" class="flex items-center justify-between py-3 text-sm text-navy hover:text-teal-dark hover:pl-1 transition-all">
                                    {{ $link }}
                                    <x-lucide-chevron-right class="w-4 h-4 text-navy/30" />
                                </a>
                            @endforeach
                        </div>

                        <p class="text-xs text-navy/35 mt-6">ShopHop v1.0.0</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@include('partials.footer')

{{-- =========================================================
    CONFIRM MODAL (generic — used by all data-confirm-action buttons)
========================================================= --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <button type="button" class="absolute inset-0 bg-navy/50" data-modal-backdrop aria-label="Close modal"></button>
    <div class="relative bg-white rounded-xl shadow-2xl border border-gray-border max-w-sm w-full p-5">
        <div class="flex items-start gap-3 mb-4">
            <div id="confirmIconWrap" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                <x-lucide-alert-triangle id="confirmIcon" class="w-5 h-5" />
            </div>
            <div>
                <h3 id="confirmTitle" class="text-navy text-base font-bold"></h3>
                <p id="confirmMessage" class="text-sm text-navy/55 mt-1"></p>
            </div>
        </div>

        <div id="confirmTypingWrap" class="hidden mb-4">
            <label class="block text-xs font-semibold text-navy/70 mb-1.5">
                Type <span id="confirmTypingWord" class="font-mono"></span> to confirm
            </label>
            <input id="confirmTypingInput" type="text"
                class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
        </div>

        <div class="flex gap-3">
            <button type="button" id="confirmCancelBtn"
                class="flex-1 border border-navy text-navy text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg hover:bg-navy hover:text-white transition-colors duration-200">
                Cancel
            </button>
            <button type="button" id="confirmActionBtn"
                class="flex-1 text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200 disabled:opacity-40 disabled:cursor-not-allowed">
                Confirm
            </button>
        </div>
    </div>
</div>

{{-- =========================================================
    ADD / EDIT ADDRESS MODAL
========================================================= --}}
<div id="addressModal" class="hidden fixed inset-0 z-50 items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="addressModalTitle">
    <button type="button" class="absolute inset-0 bg-navy/50" data-modal-backdrop aria-label="Close modal"></button>
    <div class="relative bg-white rounded-xl shadow-2xl border border-gray-border max-w-lg w-full p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
        <h3 id="addressModalTitle" class="text-navy text-base font-bold mb-5">Add New Address</h3>

        <form id="addressForm" method="POST" action="{{ Route::has('buyer.settings.address.store') ? route('buyer.settings.address.store') : '#' }}" class="space-y-4">
            @csrf
            <input type="hidden" name="address_id" id="addressIdInput" value="">

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Label</label>
                    <select name="label" class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="Home">Home</option>
                        <option value="Work">Work</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Recipient Name *</label>
                    <input type="text" name="recipient_name" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                <input type="tel" name="phone" autocomplete="tel" placeholder="09XX XXX XXXX" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
            </div>

            {{-- Province / Municipality / Barangay — wire up to your existing PSGC API script from registration --}}
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Province *</label>
                    <select id="modalProvinceSelect" name="province" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select province</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Municipality/City *</label>
                    <select id="modalMunicipalitySelect" name="municipality" required disabled
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select province first</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Barangay *</label>
                    <select id="modalBarangaySelect" name="barangay" required disabled
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select municipality first</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Street *</label>
                    <input type="text" name="street" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">House No. / Unit / Building</label>
                    <input type="text" name="house_number"
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-navy">
                <input type="checkbox" name="set_default" value="1" class="accent-teal">
                Set as default address
            </label>

            <div class="flex gap-3 pt-2">
                <button type="button" id="addressModalCancelBtn"
                    class="flex-1 border border-navy text-navy text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg hover:bg-navy hover:text-white transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-teal hover:bg-teal-dark text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Hidden forms submitted by the confirm modal for account-level actions --}}
<form id="logoutForm" method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="hidden">
    @csrf
</form>
<form id="deactivateForm" method="POST" action="{{ Route::has('buyer.settings.account.deactivate') ? route('buyer.settings.account.deactivate') : '#' }}" class="hidden">
    @csrf
    @method('PATCH')
</form>
<form id="deleteAccountForm" method="POST" action="{{ Route::has('buyer.settings.account.destroy') ? route('buyer.settings.account.destroy') : '#' }}" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       TAB SWITCHING
       - Desktop sidebar + mobile select stay in sync.
       - Active tab is stored in the URL hash.
    ===================================================== */
    const navButtons = Array.from(document.querySelectorAll('[data-tab-btn]'));
    const panels = Array.from(document.querySelectorAll('[data-tab-content]'));
    const mobileSelect = document.getElementById('settingsMobileSelect');
    const validTabs = panels.map(function (panel) {
        return panel.getAttribute('data-tab-content');
    });

    function activateTab(target, updateHash) {
        if (!validTabs.includes(target)) {
            target = 'profile';
        }

        navButtons.forEach(function (button) {
            const isActive = button.getAttribute('data-tab-btn') === target;

            button.classList.toggle('bg-teal', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('shadow-sm', isActive);
            button.classList.toggle('text-navy/70', !isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        panels.forEach(function (panel) {
            const isActive = panel.getAttribute('data-tab-content') === target;
            panel.classList.toggle('hidden', !isActive);
        });

        if (mobileSelect) {
            mobileSelect.value = target;
        }

        if (updateHash !== false) {
            history.replaceState(null, '', '#' + target);
        }
    }

    navButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(button.getAttribute('data-tab-btn'), true);
        });
    });

    if (mobileSelect) {
        mobileSelect.addEventListener('change', function () {
            activateTab(mobileSelect.value, true);

            const activePanel = document.querySelector(
                '[data-tab-content="' + mobileSelect.value + '"]'
            );

            if (activePanel && window.matchMedia('(max-width: 1023px)').matches) {
                window.setTimeout(function () {
                    activePanel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }, 40);
            }
        });
    }

    const initialTab = window.location.hash.replace('#', '');
    activateTab(validTabs.includes(initialTab) ? initialTab : 'profile', false);

    window.addEventListener('hashchange', function () {
        const hashTab = window.location.hash.replace('#', '');
        if (validTabs.includes(hashTab)) {
            activateTab(hashTab, false);
        }
    });


    /* =====================================================
       AGE AUTO-CALCULATION
    ===================================================== */
    const birthdayInput = document.getElementById('birthdayInput');
    const ageInput = document.getElementById('ageInput');

    function calculateAge(dobString) {
        if (!dobString) return '';

        const parts = dobString.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) return '';

        const dob = new Date(parts[0], parts[1] - 1, parts[2]);
        const today = new Date();

        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        if (
            monthDiff < 0 ||
            (monthDiff === 0 && today.getDate() < dob.getDate())
        ) {
            age--;
        }

        return age >= 0 ? age : '';
    }

    if (birthdayInput && ageInput) {
        const todayIso = new Date().toISOString().split('T')[0];
        birthdayInput.max = todayIso;

        ageInput.value = calculateAge(birthdayInput.value);

        birthdayInput.addEventListener('change', function () {
            ageInput.value = calculateAge(birthdayInput.value);
        });
    }


    /* =====================================================
       AVATAR PREVIEW
    ===================================================== */
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreviewLarge = document.getElementById('avatarPreviewLarge');
    const avatarPreviewMini = document.getElementById('avatarPreviewMini');

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            const file = avatarInput.files && avatarInput.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                window.alert('Please choose an image file.');
                avatarInput.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                window.alert('Avatar must be 2MB or smaller.');
                avatarInput.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                if (avatarPreviewLarge) {
                    avatarPreviewLarge.src = event.target.result;
                }

                if (avatarPreviewMini) {
                    avatarPreviewMini.src = event.target.result;
                }
            };

            reader.readAsDataURL(file);
        });
    }


    /* =====================================================
       PASSWORD MATCH CHECK
    ===================================================== */
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const mismatchNote = document.getElementById('passwordMismatchNote');

    function updatePasswordMatchState() {
        if (!newPassword || !confirmPassword || !mismatchNote) {
            return true;
        }

        const hasConfirmation = confirmPassword.value.length > 0;
        const matches = newPassword.value === confirmPassword.value;

        mismatchNote.classList.toggle(
            'hidden',
            !hasConfirmation || matches
        );

        return matches;
    }

    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', updatePasswordMatchState);
        confirmPassword.addEventListener('input', updatePasswordMatchState);
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', function (event) {
            if (!updatePasswordMatchState()) {
                event.preventDefault();
                confirmPassword.focus();
            }
        });
    }


    /* =====================================================
       ALLERGEN TAG MANAGEMENT
       DOM nodes are created with textContent instead of innerHTML
       so custom allergen names are safely rendered.
    ===================================================== */
    const SEVERITY_ORDER = ['mild', 'moderate', 'severe'];
    const SEVERITY_STYLES = {
        mild: {
            background: ['bg-gray-bg'],
            text: ['text-navy/60'],
        },
        moderate: {
            background: ['bg-amber-100'],
            text: ['text-amber-700'],
        },
        severe: {
            background: ['bg-red-100'],
            text: ['text-red-600'],
        },
    };

    let allergenTags = @json($normalizedAllergens);

    const selectedList = document.getElementById('selectedAllergenList');
    const noAllergensNote = document.getElementById('noAllergensNote');
    const hiddenInputsContainer = document.getElementById('allergenHiddenInputs');
    const commonChips = Array.from(
        document.querySelectorAll('[data-common-allergen]')
    );

    function normalizedName(name) {
        return String(name || '').trim().toLocaleLowerCase();
    }

    function findTag(name) {
        const normalized = normalizedName(name);

        return allergenTags.find(function (tag) {
            return normalizedName(tag.name) === normalized;
        });
    }

    function addAllergen(name) {
        const cleanName = String(name || '').trim();

        if (!cleanName || findTag(cleanName)) {
            return;
        }

        allergenTags.push({
            name: cleanName,
            severity: 'mild',
        });

        renderAllergens();
    }

    function removeAllergen(name) {
        const normalized = normalizedName(name);

        allergenTags = allergenTags.filter(function (tag) {
            return normalizedName(tag.name) !== normalized;
        });

        renderAllergens();
    }

    function cycleSeverity(name) {
        const tag = findTag(name);

        if (!tag) return;

        const currentIndex = SEVERITY_ORDER.indexOf(tag.severity);
        const safeIndex = currentIndex >= 0 ? currentIndex : 0;

        tag.severity =
            SEVERITY_ORDER[(safeIndex + 1) % SEVERITY_ORDER.length];

        renderAllergens();
    }

    function renderAllergens() {
        if (!selectedList || !hiddenInputsContainer) return;

        selectedList.replaceChildren();
        hiddenInputsContainer.replaceChildren();

        if (noAllergensNote) {
            noAllergensNote.classList.toggle(
                'hidden',
                allergenTags.length > 0
            );
        }

        allergenTags.forEach(function (tag, index) {
            const style =
                SEVERITY_STYLES[tag.severity] ||
                SEVERITY_STYLES.mild;

            const pill = document.createElement('span');
            pill.className =
                'inline-flex items-center gap-1.5 rounded-lg ' +
                'border border-gray-border bg-white pl-2.5 pr-1.5 ' +
                'py-1.5 text-xs font-medium';

            const nameText = document.createElement('span');
            nameText.className = 'text-navy';
            nameText.textContent = tag.name;

            const severityButton = document.createElement('button');
            severityButton.type = 'button';
            severityButton.className =
                'px-2 py-0.5 rounded-md text-[9px] font-bold ' +
                'uppercase tracking-wide ' +
                style.background.join(' ') + ' ' +
                style.text.join(' ');
            severityButton.textContent = tag.severity;
            severityButton.setAttribute(
                'aria-label',
                'Change severity for ' + tag.name
            );
            severityButton.addEventListener('click', function () {
                cycleSeverity(tag.name);
            });

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className =
                'w-5 h-5 rounded-md flex items-center justify-center ' +
                'text-navy/35 hover:bg-red-50 hover:text-red-500 ' +
                'transition-colors';
            removeButton.textContent = '×';
            removeButton.setAttribute(
                'aria-label',
                'Remove ' + tag.name
            );
            removeButton.addEventListener('click', function () {
                removeAllergen(tag.name);
            });

            pill.append(nameText, severityButton, removeButton);
            selectedList.appendChild(pill);

            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'allergens[' + index + '][name]';
            nameInput.value = tag.name;

            const severityInput = document.createElement('input');
            severityInput.type = 'hidden';
            severityInput.name =
                'allergens[' + index + '][severity]';
            severityInput.value = tag.severity;

            hiddenInputsContainer.append(nameInput, severityInput);
        });

        commonChips.forEach(function (chip) {
            const name = chip.getAttribute('data-common-allergen');
            const active = Boolean(findTag(name));

            chip.classList.toggle('bg-teal', active);
            chip.classList.toggle('text-white', active);
            chip.classList.toggle('border-teal', active);
            chip.classList.toggle('bg-white', !active);
            chip.classList.toggle('text-navy/70', !active);
        });
    }

    commonChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const name = chip.getAttribute('data-common-allergen');

            if (findTag(name)) {
                removeAllergen(name);
            } else {
                addAllergen(name);
            }
        });
    });

    const customInput = document.getElementById('customAllergenInput');
    const addCustomButton = document.getElementById('addCustomAllergenBtn');

    function submitCustomAllergen() {
        if (!customInput) return;

        addAllergen(customInput.value);
        customInput.value = '';
        customInput.focus();
    }

    if (addCustomButton) {
        addCustomButton.addEventListener(
            'click',
            submitCustomAllergen
        );
    }

    if (customInput) {
        customInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitCustomAllergen();
            }
        });
    }

    renderAllergens();


    /* =====================================================
       MODAL HELPERS
    ===================================================== */
    const confirmModal = document.getElementById('confirmModal');
    const addressModal = document.getElementById('addressModal');

    function syncBodyScroll() {
        const confirmOpen =
            confirmModal && !confirmModal.classList.contains('hidden');
        const addressOpen =
            addressModal && !addressModal.classList.contains('hidden');

        document.body.classList.toggle(
            'overflow-hidden',
            Boolean(confirmOpen || addressOpen)
        );
    }

    function showModal(modal) {
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        syncBodyScroll();
    }

    function hideModal(modal) {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        syncBodyScroll();
    }


    /* =====================================================
       GENERIC CONFIRM MODAL
    ===================================================== */
    const CONFIRM_ACTIONS = {
        logout: {
            title: 'Log Out',
            message:
                'Are you sure you want to log out of your ShopHop account?',
            confirmLabel: 'Log Out',
            danger: false,
            onConfirm: function () {
                document.getElementById('logoutForm').submit();
            },
        },

        deactivate: {
            title: 'Deactivate Account',
            message:
                'Your profile will be hidden until you log back in. ' +
                'You can reactivate anytime by signing in again.',
            confirmLabel: 'Deactivate',
            danger: true,
            onConfirm: function () {
                document.getElementById('deactivateForm').submit();
            },
        },

        delete: {
            title: 'Delete Account Permanently',
            message:
                'This permanently deletes your account and saved data. ' +
                'This action cannot be undone.',
            confirmLabel: 'Delete Account',
            danger: true,
            requireTyping: 'DELETE',
            onConfirm: function () {
                document.getElementById('deleteAccountForm').submit();
            },
        },

        'remove-address': {
            title: 'Remove Address',
            message:
                'This address will be removed from your address book.',
            confirmLabel: 'Remove',
            danger: true,
            onConfirm: function (triggerButton) {
                const id =
                    triggerButton &&
                    triggerButton.getAttribute('data-target-id');

                const card = document.querySelector(
                    '[data-address-card][data-address-id="' + id + '"]'
                );

                if (card) {
                    card.remove();
                }

                const addressList =
                    document.getElementById('addressList');
                const emptyNote =
                    document.getElementById('noAddressesNote');

                if (addressList && emptyNote) {
                    emptyNote.classList.toggle(
                        'hidden',
                        addressList.querySelector('[data-address-card]') !== null
                    );
                }

                // TODO:
                // Submit a real DELETE request when the address route exists.
            },
        },

        'switch-account': {
            title: 'Switch Account',
            message:
                'Switch to this account? Your current session will end.',
            confirmLabel: 'Switch',
            danger: false,
            onConfirm: function () {
                // TODO: redirect to the real switch-account route.
                window.location.href = '#';
            },
        },

        'become-seller': {
            title: 'Become a Seller',
            message:
                'Continue to Seller Registration. ' +
                'Your buyer account will stay active.',
            confirmLabel: 'Continue',
            danger: false,
            onConfirm: function () {
                // TODO: redirect to the real seller registration route.
                window.location.href = '#';
            },
        },
    };

    const confirmIconWrap = document.getElementById('confirmIconWrap');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmCancelButton = document.getElementById('confirmCancelBtn');
    const confirmActionButton = document.getElementById('confirmActionBtn');
    const confirmTypingWrap = document.getElementById('confirmTypingWrap');
    const confirmTypingWord = document.getElementById('confirmTypingWord');
    const confirmTypingInput = document.getElementById('confirmTypingInput');

    let activeConfirmConfig = null;
    let activeTriggerButton = null;

    function closeConfirmModal() {
        hideModal(confirmModal);
        activeConfirmConfig = null;
        activeTriggerButton = null;
    }

    function openConfirmModal(actionKey, triggerButton) {
        const config = CONFIRM_ACTIONS[actionKey];

        if (!config || !confirmModal) return;

        activeConfirmConfig = config;
        activeTriggerButton = triggerButton;

        const overrideMessage =
            triggerButton &&
            triggerButton.getAttribute('data-confirm-message');

        confirmTitle.textContent = config.title;
        confirmMessage.textContent =
            overrideMessage || config.message;
        confirmActionButton.textContent = config.confirmLabel;

        const commonButtonClasses =
            'flex-1 text-white text-xs sm:text-sm font-semibold ' +
            'px-4 py-2 rounded-lg transition-colors ' +
            'disabled:opacity-40 disabled:cursor-not-allowed';

        if (config.danger) {
            confirmIconWrap.className =
                'w-9 h-9 rounded-lg flex items-center justify-center ' +
                'shrink-0 bg-red-100 text-red-600';

            confirmActionButton.className =
                commonButtonClasses +
                ' bg-red-500 hover:bg-red-600';
        } else {
            confirmIconWrap.className =
                'w-9 h-9 rounded-lg flex items-center justify-center ' +
                'shrink-0 bg-teal-light text-teal-dark';

            confirmActionButton.className =
                commonButtonClasses +
                ' bg-teal hover:bg-teal-dark';
        }

        if (config.requireTyping) {
            confirmTypingWrap.classList.remove('hidden');
            confirmTypingWord.textContent = config.requireTyping;
            confirmTypingInput.value = '';
            confirmActionButton.disabled = true;
        } else {
            confirmTypingWrap.classList.add('hidden');
            confirmActionButton.disabled = false;
        }

        showModal(confirmModal);

        window.setTimeout(function () {
            if (config.requireTyping) {
                confirmTypingInput.focus();
            } else {
                confirmActionButton.focus();
            }
        }, 0);
    }

    document
        .querySelectorAll('[data-confirm-action]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                openConfirmModal(
                    button.getAttribute('data-confirm-action'),
                    button
                );
            });
        });

    if (confirmCancelButton) {
        confirmCancelButton.addEventListener(
            'click',
            closeConfirmModal
        );
    }

    if (confirmTypingInput) {
        confirmTypingInput.addEventListener('input', function () {
            if (
                activeConfirmConfig &&
                activeConfirmConfig.requireTyping
            ) {
                confirmActionButton.disabled =
                    confirmTypingInput.value !==
                    activeConfirmConfig.requireTyping;
            }
        });
    }

    if (confirmActionButton) {
        confirmActionButton.addEventListener('click', function () {
            if (!activeConfirmConfig) return;

            const config = activeConfirmConfig;
            const trigger = activeTriggerButton;

            closeConfirmModal();
            config.onConfirm(trigger);
        });
    }

    const confirmBackdrop =
        confirmModal &&
        confirmModal.querySelector('[data-modal-backdrop]');

    if (confirmBackdrop) {
        confirmBackdrop.addEventListener(
            'click',
            closeConfirmModal
        );
    }


    /* =====================================================
       ADDRESS MODAL
    ===================================================== */
    const addressModalTitle =
        document.getElementById('addressModalTitle');
    const addressForm =
        document.getElementById('addressForm');
    const addAddressButton =
        document.getElementById('addAddressBtn');
    const addressModalCancelButton =
        document.getElementById('addressModalCancelBtn');
    const addressIdInput =
        document.getElementById('addressIdInput');

    function closeAddressModal() {
        hideModal(addressModal);
    }

    function openAddressModal(isEdit) {
        if (!addressModal) return;

        addressModalTitle.textContent =
            isEdit ? 'Edit Address' : 'Add New Address';

        showModal(addressModal);

        window.setTimeout(function () {
            const firstField =
                addressForm &&
                addressForm.querySelector(
                    'select:not([disabled]), input:not([type="hidden"])'
                );

            if (firstField) {
                firstField.focus();
            }
        }, 0);
    }

    if (addAddressButton) {
        addAddressButton.addEventListener('click', function () {
            if (addressForm) {
                addressForm.reset();
            }

            if (addressIdInput) {
                addressIdInput.value = '';
            }

            openAddressModal(false);
        });
    }

    document
        .querySelectorAll('[data-edit-address]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                if (addressIdInput) {
                    addressIdInput.value =
                        button.getAttribute('data-edit-address');
                }

                // TODO:
                // Prefill fields using real address data from your backend.
                openAddressModal(true);
            });
        });

    document
        .querySelectorAll('[data-set-default]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                // TODO:
                // Submit a request to set this address as default.
                const card =
                    button.closest('[data-address-card]');

                if (card) {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                }
            });
        });

    if (addressModalCancelButton) {
        addressModalCancelButton.addEventListener(
            'click',
            closeAddressModal
        );
    }

    const addressBackdrop =
        addressModal &&
        addressModal.querySelector('[data-modal-backdrop]');

    if (addressBackdrop) {
        addressBackdrop.addEventListener(
            'click',
            closeAddressModal
        );
    }


    /* =====================================================
       ESCAPE KEY
    ===================================================== */
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (
            confirmModal &&
            !confirmModal.classList.contains('hidden')
        ) {
            closeConfirmModal();
            return;
        }

        if (
            addressModal &&
            !addressModal.classList.contains('hidden')
        ) {
            closeAddressModal();
        }
    });


    /* =====================================================
       ADDRESS CASCADING DROPDOWNS
       Hook your existing PSGC API implementation here.
    ===================================================== */
    // const provinceSelect =
    //     document.getElementById('modalProvinceSelect');
    // const municipalitySelect =
    //     document.getElementById('modalMunicipalitySelect');
    // const barangaySelect =
    //     document.getElementById('modalBarangaySelect');
});
</script>
@endsection