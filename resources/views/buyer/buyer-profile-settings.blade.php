{{--
    resources/views/buyer/buyer-profile-settings.blade.php

    Buyer Account Center — streamlined version.
    Sections: Profile Info, Address Book, Security,
    Privacy & Account, Vouchers, Chats, Activity Log, Notifications,
    Help Center, plus a direct My Orders shortcut and Log Out.

    Core profile fields use Buyer persistence. Other account settings remain
    unavailable until their own backend contracts exist.
--}}
@extends('layouts.app')

@section('title', 'Account Settings - ShopHop')

{{--
    This page belongs to the Buyer area, so hide the default landing-page
    navbar/footer from layouts.app and render the Buyer chrome below.
--}}
@section('hideChrome', true)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-buyer-discovery.css') }}?v=20261108">
<link rel="stylesheet" href="{{ asset('css/shophop-buyer-account.css') }}?v=20261108-account">
@endpush

@php
    $user = auth()->user();
    // Core buyer identity belongs to the buyer profile, not the login record.
    $buyer = $user?->buyer;

    $displayName = trim(
        ($buyer?->first_name ?? 'Buyer') . ' ' . ($buyer?->last_name ?? '')
    );

    $birthdayValue = old(
        'birthday',
        filled($buyer?->birthday)
            ? \Illuminate\Support\Carbon::parse($buyer->birthday)->format('Y-m-d')
            : ''
    );
@endphp

@section('content')

<a
    href="#settings-main"
    class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-100 focus:bg-white focus:text-navy focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg"
>
    Skip to account settings
</a>

@include('buyer.partials.navbar-buyer')

<section id="settings-main" class="shb-profile-page settings-shell bg-gray-bg min-h-[70vh] py-4 sm:py-5 lg:py-6 scroll-mt-20">
    <div class="sh-shop-shell">

        <header class="settings-page-header">
            <div>
                <p class="settings-eyebrow">Your ShopHop account</p>
                <h1>Account Settings</h1>
                <p class="settings-intro">Manage your profile, delivery address and account preferences.</p>
            </div>
            <div class="settings-header-actions">
                <a class="settings-primary-action" href="{{ route('buyer.orders') }}">
                    <x-lucide-package class="w-4 h-4" /> My Orders
                </a>
                <a class="settings-secondary-action" href="{{ route('buyer.dashboard') }}">
                    <x-lucide-arrow-left class="w-4 h-4" /> Dashboard
                </a>
            </div>
        </header>

        {{-- Compact mobile navigation --}}
        <div class="lg:hidden sticky top-16 z-40 bg-white/95 backdrop-blur border border-gray-border rounded-xl p-3 mb-4 shadow-sm">

            <div class="grid grid-cols-3 gap-2 mb-3">
                <a href="{{ Route::has('buyer.orders') ? route('buyer.orders') : url('/buyer/orders') }}"
                   class="h-9 rounded-lg bg-teal-light text-teal-dark text-[9px] font-semibold
                          flex items-center justify-center gap-1.5 hover:bg-teal hover:text-white transition">
                    <x-lucide-package class="w-3 h-3" />
                    Orders
                </a>

                <a href="#vouchers"
                   class="h-9 rounded-lg bg-gray-bg text-navy/55 text-[9px] font-semibold
                          flex items-center justify-center gap-1.5 hover:bg-teal-light hover:text-teal-dark transition">
                    <x-lucide-ticket class="w-3 h-3" />
                    Vouchers
                </a>

                <a href="#notifications"
                   class="h-9 rounded-lg bg-gray-bg text-navy/55 text-[9px] font-semibold
                          flex items-center justify-center gap-1.5 hover:bg-teal-light hover:text-teal-dark transition">
                    <x-lucide-bell class="w-3 h-3" />
                    Alerts
                </a>
            </div>

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

                    <option value="security">Security</option>
                    <option value="privacy">Privacy & Account</option>
                    <option value="vouchers">Vouchers</option>
                    <option value="chats">Chats</option>
                    <option value="activity">Activity Log</option>
                    <option value="notifications">Notifications</option>
                    <option value="help">Help Center</option>
                </select>

                <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40" />
            </div>
        </div>

        <div class="settings-layout grid lg:grid-cols-[252px_minmax(0,1fr)] gap-4 lg:gap-5 items-start">

            {{-- =========================================================
                SIDEBAR NAV
            ========================================================= --}}
            <aside class="hidden lg:block lg:sticky lg:top-20">

                <div class="bg-white rounded-2xl border border-gray-border shadow-sm overflow-hidden">

                    {{-- Buyer summary --}}
                    <div class="p-3 border-b border-gray-border/80">

                        <div class="flex items-center gap-3">

                            @if (! empty($user->avatar_url))
                                <img
                                    id="avatarPreviewMini"
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $displayName }}"
                                    class="w-11 h-11 rounded-full object-cover border border-gray-border shrink-0"
                                >
                            @else
                                <span
                                    id="avatarPreviewMini"
                                    class="w-11 h-11 rounded-full bg-teal-light text-teal-dark
                                           flex items-center justify-center text-[15px] font-bold
                                           border border-teal/10 shrink-0"
                                >
                                    {{ strtoupper(substr($buyer?->first_name ?? 'B', 0, 1)) }}
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">

                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="text-[12.5px] font-bold text-navy truncate">
                                        {{ $buyer?->first_name ?? 'Buyer' }} {{ $buyer?->last_name ?? '' }}
                                    </p>

                                    <span class="shrink-0 text-[7px] font-bold uppercase tracking-wide
                                                 text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">
                                        Buyer
                                    </span>
                                </div>

                                <p class="text-[9.5px] text-navy/40 truncate mt-0.5">
                                    {{ $user->email ?? 'Buyer account' }}
                                </p>

                            </div>
                        </div>

                        <button
                            type="button"
                            data-tab-btn="profile"
                            class="settings-tab-btn mt-3 w-full h-8 rounded-lg
                                   bg-gray-bg hover:bg-teal-light/50
                                   text-[9.5px] font-semibold text-navy/55 hover:text-teal-dark
                                   flex items-center justify-center gap-1.5 transition"
                        >
                            <x-lucide-pencil class="w-3 h-3" />
                            Edit Profile
                        </button>

                    </div>


                    {{-- Navigation --}}
                    <nav data-settings-nav class="p-2.5">

                        <p class="text-[8px] font-bold text-navy/30 uppercase tracking-[0.16em]
                                  px-2.5 pt-1 pb-1.5">
                            Account
                        </p>

                        <div class="space-y-0.5">

                            <button
                                type="button"
                                data-tab-btn="profile"
                                class="settings-tab-btn group w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       bg-teal text-white text-[11px] font-semibold
                                       transition-colors duration-200"
                            >
                                <x-lucide-user class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Profile Info</span>
                            </button>

                            <button
                                type="button"
                                data-tab-btn="address"
                                class="settings-tab-btn group w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-map-pin class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Address Book</span>
                            </button>

                            <button
                                type="button"
                                data-tab-btn="security"
                                class="settings-tab-btn group w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-lock class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Security</span>
                            </button>

                            <button
                                type="button"
                                data-tab-btn="privacy"
                                class="settings-tab-btn group w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-shield class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Privacy & Account</span>
                            </button>

                        </div>


                        <div class="my-2.5 border-t border-gray-border/75"></div>

                        <p class="text-[8px] font-bold text-navy/30 uppercase tracking-[0.16em]
                                  px-2.5 pb-1.5">
                            Shopping
                        </p>

                        <div class="space-y-0.5">

                            <a
                                href="{{ Route::has('buyer.orders') ? route('buyer.orders') : url('/buyer/orders') }}"
                                class="w-full h-9 px-2.5 rounded-lg flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-package class="w-3.5 h-3.5 shrink-0" />
                                <span class="flex-1 text-left truncate">My Orders</span>
                            </a>

                            <button
                                type="button"
                                data-tab-btn="vouchers"
                                class="settings-tab-btn w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-ticket class="w-3.5 h-3.5 shrink-0" />
                                <span class="flex-1 text-left truncate">Vouchers</span>

                            </button>

                            <button
                                type="button"
                                data-tab-btn="chats"
                                class="settings-tab-btn w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-message-circle class="w-3.5 h-3.5 shrink-0" />
                                <span class="flex-1 text-left truncate">Chats</span>

                            </button>

                            <button
                                type="button"
                                data-tab-btn="activity"
                                class="settings-tab-btn w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-history class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Activity Log</span>
                            </button>

                        </div>


                        <div class="my-2.5 border-t border-gray-border/75"></div>

                        <p class="text-[8px] font-bold text-navy/30 uppercase tracking-[0.16em]
                                  px-2.5 pb-1.5">
                            Support
                        </p>

                        <div class="space-y-0.5">

                            <button
                                type="button"
                                data-tab-btn="notifications"
                                class="settings-tab-btn w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-bell class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Notifications</span>
                            </button>

                            <button
                                type="button"
                                data-tab-btn="help"
                                class="settings-tab-btn w-full h-9 px-2.5 rounded-lg
                                       flex items-center gap-2.5
                                       text-navy/65 hover:bg-gray-bg hover:text-teal-dark
                                       text-[11px] font-medium transition-colors duration-200"
                            >
                                <x-lucide-help-circle class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">Help Center</span>
                            </button>

                        </div>

                    </nav>


                    {{-- Logout --}}
                    <div class="p-2.5 pt-0">

                        <button
                            type="button"
                            data-confirm-action="logout"
                            class="w-full h-9 rounded-lg border border-red-100 bg-red-50/50
                                   flex items-center gap-2.5 px-2.5
                                   text-[10.5px] font-semibold text-red-500
                                   hover:bg-red-50 transition-colors duration-200"
                        >
                            <x-lucide-log-out class="w-3.5 h-3.5 shrink-0" />
                            Log Out
                        </button>

                    </div>

                </div>

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

                        <form method="POST" action="{{ route('buyer.settings.profile.update') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- Avatar --}}
                            <div class="flex items-center gap-4">
                                <div>
                                    <img
                                        id="avatarPreviewLarge"
                                        src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                                        alt="Avatar preview"
                                        class="w-16 h-16 rounded-full object-cover border border-gray-border"
                                    >
                                </div>
                                <div class="text-xs text-navy/50">
                                    Avatar upload is unavailable.
                                </div>
                            </div>

                            {{-- Name row --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Last Name *</label>
                                    <input type="text" name="last_name" autocomplete="family-name" value="{{ old('last_name', $buyer?->last_name ?? '') }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">First Name *</label>
                                    <input type="text" name="first_name" autocomplete="given-name" value="{{ old('first_name', $buyer?->first_name ?? '') }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Middle Initial</label>
                                    <input type="text" name="middle_initial" autocomplete="additional-name" maxlength="2" value="{{ old('middle_initial', $buyer?->middle_initial ?? '') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            {{-- Sex + Birthday + Age --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Sex *</label>
                                    <div class="flex flex-wrap gap-4 pt-2.5">
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Male" class="accent-teal" {{ old('sex', $buyer?->sex ?? '') === 'Male' ? 'checked' : '' }}>
                                            Male
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Female" class="accent-teal" {{ old('sex', $buyer?->sex ?? '') === 'Female' ? 'checked' : '' }}>
                                            Female
                                        </label>
                                        @if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite')
                                            <label class="flex items-center gap-2 text-sm text-navy">
                                                <input type="radio" name="sex" value="Prefer not to say" class="accent-teal" {{ old('sex', $buyer?->sex ?? '') === 'Prefer not to say' ? 'checked' : '' }}>
                                                Prefer not to say
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Birthday *</label>
                                    <input id="birthdayInput" type="date" name="birthday" autocomplete="bday" value="{{ $birthdayValue }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Age</label>
                                    <input id="ageInput" type="text" readonly value="{{ $buyer?->birthday?->age ?? '' }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border bg-gray-bg text-sm text-navy/60">
                                </div>
                            </div>

                            {{-- Email + Contact --}}
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">E-mail</label>
                                    <input type="email" autocomplete="email" value="{{ $user->email ?? '' }}" readonly
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border bg-gray-bg text-sm text-navy/60">
                                    <p class="text-[11px] text-navy/45 mt-1">Email changes require a separate verification flow and are unavailable here.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                                    <input type="tel" name="contact_no" autocomplete="tel" value="{{ old('contact_no', $buyer?->contact_no ?? '') }}" placeholder="09XX XXX XXXX" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            {{-- Valid ID --}}
                            <div class="pt-2 border-t border-gray-border">
                                <label class="block text-xs font-semibold text-navy/70 mb-2">Valid ID on File</label>
                                <div class="flex flex-wrap items-center gap-4">
                                    <span class="text-xs text-navy/60">{{ filled($buyer?->valid_id_path) ? 'ID document on file' : 'No ID document on file' }}</span>
                                </div>
                                <p class="text-[11px] text-navy/45 mt-2">
                                    ID replacement and re-verification are unavailable here.
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

                        {{-- Keep the read-only address disclosure contained when mobile navigation reveals this panel. --}}
                        <div class="flex flex-wrap sm:flex-nowrap items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Address Book</h2>
                                <p class="text-sm text-navy/55">
                                    You can update your main delivery address from any Philippine region, including NCR.
                                </p>
                            </div>
                            <button type="button" id="addAddressBtn"
                                class="inline-flex items-center gap-1.5 bg-teal-light text-teal-dark text-xs font-semibold px-3.5 py-2 rounded-lg shrink-0">
                                <x-lucide-pencil class="w-4 h-4" /> Edit delivery address
                            </button>
                        </div>

                        <div id="addressList" class="space-y-3">
                            @if ($buyer)
                                <div class="border border-gray-border rounded-xl p-3.5">
                                    <p class="text-xs font-semibold text-navy/50 mb-1">Default delivery address · editable</p>
                                    <p class="text-sm font-semibold text-navy">{{ $displayName }} &middot; {{ $buyer->contact_no }}</p>
                                    <p class="text-sm text-navy/60 mt-0.5">{{ $buyer->street_address }}, {{ $buyer->barangay_name }}, {{ $buyer->municipality_name }}, {{ $buyer->province_name }}</p>
                                </div>
                            @endif
                        </div>
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

                            <p class="text-xs text-navy/50 mb-4">Password change is unavailable from this page.</p>
                            <form id="passwordForm" class="space-y-4 max-w-md">
                                <fieldset disabled>

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
                                    <button type="button" disabled
                                        class="inline-flex items-center justify-center gap-1.5 bg-gray-bg text-navy/45 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed">
                                        Password change unavailable
                                    </button>
                                </div>
                                </fieldset>
                            </form>
                        </div>

                        <div class="pt-5 border-t border-gray-border">
                            <label class="flex items-center justify-between gap-4 max-w-lg border border-gray-border rounded-xl p-3.5">
                                <span>
                                    <span class="block text-sm font-semibold text-navy">Two-Factor Authentication</span>
                                    <span class="block text-xs text-navy/50 mt-0.5">Unavailable. No two-factor login contract exists.</span>
                                </span>
                                <span class="relative inline-flex items-center shrink-0">
                                    <input type="checkbox" disabled class="peer sr-only">
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
                                These account controls are unavailable until their backend contracts are built.
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4 max-w-xl border border-gray-border rounded-xl p-3.5">
                            <div>
                                <p class="text-sm font-semibold text-navy">Download My Data</p>
                                <p class="text-xs text-navy/50 mt-0.5">Data export requests are unavailable.</p>
                            </div>
                            <button type="button" disabled class="inline-flex items-center gap-1.5 border border-gray-border text-navy/45 text-xs font-semibold px-3.5 py-2 rounded-lg cursor-not-allowed shrink-0">
                                <x-lucide-download class="w-4 h-4" /> Unavailable
                            </button>
                        </div>

                        <label class="flex items-center justify-between gap-4 max-w-xl border border-gray-border rounded-xl p-3.5">
                            <span>
                                <span class="block text-sm font-semibold text-navy">Show My Review Activity Publicly</span>
                                <span class="block text-xs text-navy/50 mt-0.5">Review visibility settings are unavailable.</span>
                            </span>
                            <span class="relative inline-flex items-center shrink-0">
                                <input type="checkbox" disabled class="peer sr-only">
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
                                        <p class="text-xs text-navy/55 mt-0.5">Self-deactivation and reactivation are unavailable.</p>
                                    </div>
                                    <button type="button" disabled
                                        class="inline-flex items-center gap-1.5 bg-white border border-gray-border text-navy/45 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed shrink-0">
                                        <x-lucide-power class="w-4 h-4" /> Unavailable
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">Delete Account</p>
                                        <p class="text-xs text-navy/55 mt-0.5">Account deletion requests are unavailable.</p>
                                    </div>
                                    <button type="button" disabled
                                        class="inline-flex items-center gap-1.5 bg-white border border-gray-border text-navy/45 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed shrink-0">
                                        <x-lucide-trash-2 class="w-4 h-4" /> Unavailable
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
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Account vouchers and redemption are unavailable. Seller voucher codes appear on eligible real Product details.</p>

                        <div class="flex gap-2 mb-4 max-w-md">
                            <input type="text" disabled placeholder="Voucher redemption unavailable"
                                class="flex-1 px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            <button type="button" disabled class="bg-gray-bg text-navy/45 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed">
                                Unavailable
                            </button>
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
                                <p class="text-sm text-navy/55">Account chat history is unavailable here. Buyer Messages remains a frontend demo.</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- =====================================================
                    ACTIVITY LOG TAB
                ===================================================== --}}
                <div data-tab-content="activity" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Activity Log</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Account activity history is unavailable.</p>
                    </div>
                </div>

                {{-- =====================================================
                    NOTIFICATIONS TAB
                ===================================================== --}}
                <div data-tab-content="notifications" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">

                        <h2 class="text-navy text-base sm:text-lg font-bold mb-1">Notifications</h2>
                        <p class="text-xs sm:text-sm text-navy/50 mb-4">Notification preferences are unavailable and are not saved.</p>

                        <form class="space-y-1">
                            <fieldset disabled>

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
                                        <input type="checkbox" class="peer sr-only">
                                        <span class="w-11 h-6 rounded-full bg-gray-border peer-checked:bg-teal transition-colors duration-200"></span>
                                        <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            @endforeach

                            <div class="flex justify-end pt-4">
                                <button type="button" disabled
                                    class="inline-flex items-center justify-center gap-1.5 bg-gray-bg text-navy/45 text-xs sm:text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed">
                                    Preferences unavailable
                                </button>
                            </div>
                            </fieldset>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    HELP CENTER TAB
                ===================================================== --}}
                <div data-tab-content="help" class="settings-panel hidden">
                    <div class="bg-white rounded-xl border border-gray-border shadow-sm p-4 sm:p-5 lg:p-6">@include('buyer.help.content')</div>
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
        <h3 id="addressModalTitle" class="text-navy text-base font-bold mb-5">Edit Delivery Address</h3>

        <form id="addressForm" method="POST" action="{{ route('buyer.settings.address.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
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
                    <input type="text" name="recipient_name" value="{{ $displayName }}" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                <input type="tel" name="phone" autocomplete="tel" value="{{ $buyer?->contact_no }}" placeholder="09XX XXX XXXX" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <label class="block text-xs font-semibold text-navy/70">Region *
                    <select id="modalRegionSelect" name="region_code" required disabled class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm">
                        <option value="">Loading regions...</option>
                    </select>
                    <input type="hidden" name="region_name" id="modalRegionName" value="{{ $buyer?->region_name }}">
                </label>
                <label class="block text-xs font-semibold text-navy/70">Province / NCR *
                    <select id="modalProvinceSelect" name="province_code" required disabled class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm">
                        <option value="">Select region first</option>
                    </select>
                    <input type="hidden" id="modalProvinceName" name="province_name" value="{{ $buyer?->province_name }}">
                </label>
                <label class="block text-xs font-semibold text-navy/70">City / Municipality *
                    <select id="modalMunicipalitySelect" name="municipality_code" required disabled class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm">
                        <option value="">Select province / NCR first</option>
                    </select>
                    <input type="hidden" id="modalMunicipalityName" name="municipality_name" value="{{ $buyer?->municipality_name }}">
                </label>
                <label class="block text-xs font-semibold text-navy/70">Barangay *
                    <select id="modalBarangaySelect" name="barangay_code" required disabled class="w-full px-3 py-2 rounded-lg border border-gray-border text-sm">
                        <option value="">Select city first</option>
                    </select>
                    <input type="hidden" id="modalBarangayName" name="barangay_name" value="{{ $buyer?->barangay_name }}">
                </label>
            </div>
            <p id="profileAddressStatus" role="status" class="text-xs text-navy/60"></p>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Street *</label>
                    <input type="text" name="street" value="{{ $buyer?->street_address }}" required
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
        activateTab(hashTab, false);
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
    // Keep confirmation actions inside the shared initializer so navigation, derived age and logout all initialize.
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
            'Edit Delivery Address';

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
                // Preserve the current saved address for editing.
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


    // Shared nationwide address hierarchy. Values update the Buyer default
    // address used on subsequent checkouts; existing orders retain snapshots.
    ShopHopPHAddress.bind({
        region: document.getElementById('modalRegionSelect'),
        province: document.getElementById('modalProvinceSelect'),
        city: document.getElementById('modalMunicipalitySelect'),
        barangay: document.getElementById('modalBarangaySelect'),
        regionName: document.getElementById('modalRegionName'),
        provinceName: document.getElementById('modalProvinceName'),
        cityName: document.getElementById('modalMunicipalityName'),
        barangayName: document.getElementById('modalBarangayName'),
        oldRegion: @json(old('region_code', $buyer?->region_code)),
        oldProvince: @json(old('province_code', $buyer?->province_code)),
        oldProvinceName: @json(old('province_name', $buyer?->province_name)),
        oldCity: @json(old('municipality_code', $buyer?->municipality_code)),
        oldCityName: @json(old('municipality_name', $buyer?->municipality_name)),
        oldBarangay: @json(old('barangay_code', $buyer?->barangay_code)),
        oldBarangayName: @json(old('barangay_name', $buyer?->barangay_name)),
        status: (message, error) => {
            const status = document.getElementById('profileAddressStatus');
            status.textContent = message;
            status.style.color = error ? '#b91c1c' : '#56727a';
        },
    });
});
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/shophop-buyer-discovery.js') }}?v=20261108" defer></script>
@endpush
