{{--
    resources/views/buyer/buyer-profile.blade.php

    Buyer Account Settings — Profile Info, Address, Allergens & Preferences,
    Security, Notifications.

    Matches the ShopHop palette already used in the homepage hero/categories
    (text-navy, bg-teal / teal-dark / teal-light, bg-gray-bg, border-gray-border).

    NOTE: Route names below (e.g. buyer.settings.profile.update) are placeholders —
    swap them for your actual route names/controllers. Same goes for the address
    dropdown API call, which is stubbed to reuse whatever you already wired up
    on the registration page.
--}}
@extends('layouts.app')

@section('title', 'Account Settings - ShopHop')

@php
    $user = auth()->user();

    $commonAllergens = [
        'Peanuts', 'Tree Nuts', 'Milk / Dairy', 'Eggs', 'Shellfish',
        'Fish', 'Soy', 'Wheat / Gluten', 'Sesame',
    ];

    // Expected shape: [['name' => 'Peanuts', 'severity' => 'severe'], ...]
    $userAllergens = $user->allergens ?? [];
@endphp

@section('content')
<section class="bg-gray-bg min-h-screen py-10 sm:py-14">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="mb-8">
            <p class="text-teal-dark text-xs sm:text-sm font-semibold mb-1 tracking-wide">ACCOUNT</p>
            <h1 class="text-navy text-2xl sm:text-3xl font-bold">Account Settings</h1>
            <p class="text-sm text-navy/55 mt-1">
                Manage your profile, address, and shopping preferences.
            </p>
        </div>

        <div class="grid lg:grid-cols-[260px_1fr] gap-6 lg:gap-8 items-start">

            {{-- =========================================================
                SIDEBAR NAV
            ========================================================= --}}
            <aside class="bg-white rounded-2xl border border-gray-border shadow-sm p-3 lg:sticky lg:top-6">

                <div class="flex items-center gap-3 px-3 py-3 mb-2 border-b border-gray-border">
                    <img
                        id="avatarPreviewMini"
                        src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                        alt="Your avatar"
                        class="w-11 h-11 rounded-full object-cover border border-gray-border shrink-0"
                    >
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">
                            {{ $user->first_name ?? 'Buyer' }} {{ $user->last_name ?? '' }}
                        </p>
                        <p class="text-xs text-navy/50 truncate">
                            {{ $user->email ?? '' }}
                        </p>
                    </div>
                </div>

                <nav
                    data-settings-nav
                    class="flex lg:flex-col gap-1 overflow-x-auto lg:overflow-visible scrollbar-none [&::-webkit-scrollbar]:hidden"
                >
                    <button type="button" data-tab-btn="profile"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-white bg-teal transition-colors duration-200">
                        <x-lucide-user class="w-4 h-4" />
                        Profile Info
                    </button>

                    <button type="button" data-tab-btn="address"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-map-pin class="w-4 h-4" />
                        Address
                    </button>

                    <button type="button" data-tab-btn="allergens"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-shield-alert class="w-4 h-4" />
                        Allergens & Diet
                    </button>

                    <button type="button" data-tab-btn="security"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-lock class="w-4 h-4" />
                        Security
                    </button>

                    <button type="button" data-tab-btn="notifications"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-bell class="w-4 h-4" />
                        Notifications
                    </button>
                </nav>
            </aside>

            {{-- =========================================================
                CONTENT
            ========================================================= --}}
            <div class="min-w-0 space-y-6">

                {{-- =====================================================
                    PROFILE INFO TAB
                ===================================================== --}}
                <div data-tab-content="profile" class="settings-panel">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Profile Information</h2>
                        <p class="text-sm text-navy/55 mb-6">
                            This is how you appear across ShopHop.
                        </p>

                        <form method="POST" action="{{ Route::has('buyer.settings.profile.update') ? route('buyer.settings.profile.update') : '#' }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            {{-- Avatar --}}
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <img
                                        id="avatarPreviewLarge"
                                        src="{{ $user->avatar_url ?? asset('images/avatar-placeholder.png') }}"
                                        alt="Avatar preview"
                                        class="w-20 h-20 rounded-full object-cover border border-gray-border"
                                    >
                                    <label
                                        for="avatarInput"
                                        class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-teal hover:bg-teal-dark text-white flex items-center justify-center cursor-pointer shadow-md transition-colors"
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
                                    <input type="text" name="last_name" value="{{ $user->last_name ?? '' }}" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">First Name *</label>
                                    <input type="text" name="first_name" value="{{ $user->first_name ?? '' }}" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Middle Initial</label>
                                    <input type="text" name="middle_initial" maxlength="3" value="{{ $user->middle_initial ?? '' }}"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            {{-- Sex + Birthday + Age --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Sex *</label>
                                    <div class="flex gap-4 pt-2.5">
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Male" class="accent-teal" {{ ($user->sex ?? '') === 'Male' ? 'checked' : '' }}>
                                            Male
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-navy">
                                            <input type="radio" name="sex" value="Female" class="accent-teal" {{ ($user->sex ?? '') === 'Female' ? 'checked' : '' }}>
                                            Female
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Birthday *</label>
                                    <input id="birthdayInput" type="date" name="birthday" value="{{ $user->birthday ?? '' }}" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Age</label>
                                    <input id="ageInput" type="text" readonly value="{{ $user->age ?? '' }}"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border bg-gray-bg text-sm text-navy/60">
                                </div>
                            </div>

                            {{-- Email + Contact --}}
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">E-mail *</label>
                                    <input type="email" name="email" value="{{ $user->email ?? '' }}" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                                    <input type="tel" name="contact_no" value="{{ $user->contact_no ?? '' }}" placeholder="09XX XXX XXXX" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
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
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    ADDRESS TAB
                ===================================================== --}}
                <div data-tab-content="address" class="settings-panel hidden">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Delivery Address</h2>
                        <p class="text-sm text-navy/55 mb-6">
                            Used as your default address at checkout.
                        </p>

                        <form method="POST" action="{{ route('buyer.settings.address.update') ?? '#' }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- Province / Municipality / Barangay — wire this up to whatever
                                 PSGC API script already powers your registration page --}}
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Province *</label>
                                    <select id="provinceSelect" name="province" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                        <option value="">Select province</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Municipality/City *</label>
                                    <select id="municipalitySelect" name="municipality" required disabled
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                        <option value="">Select province first</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Barangay *</label>
                                    <select id="barangaySelect" name="barangay" required disabled
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                        <option value="">Select municipality first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Street *</label>
                                    <input type="text" name="street" value="{{ $user->street ?? '' }}" required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">House No. / Unit / Building</label>
                                    <input type="text" name="house_number" value="{{ $user->house_number ?? '' }}"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Landmark (optional)</label>
                                <input type="text" name="landmark" value="{{ $user->landmark ?? '' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
                                    Save Address
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    ALLERGENS & PREFERENCES TAB
                ===================================================== --}}
                <div data-tab-content="allergens" class="settings-panel hidden">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <div class="flex items-start gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-shield-alert class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-navy text-lg font-bold">Allergens & Dietary Preferences</h2>
                                <p class="text-sm text-navy/55 mt-1">
                                    Tell us what you're allergic to. Sellers disclose ingredients on food
                                    products, and we'll warn you at checkout if something you're ordering
                                    matches an allergen on this list.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('buyer.settings.allergens.update') ?? '#' }}" class="space-y-5">
                            @csrf
                            @method('PATCH')

                            {{-- Quick-add common allergens --}}
                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Common allergens — tap to add</label>
                                <div id="commonAllergenChips" class="flex flex-wrap gap-2">
                                    @foreach ($commonAllergens as $allergen)
                                        <button
                                            type="button"
                                            data-common-allergen="{{ $allergen }}"
                                            class="chip-toggle px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-medium border border-gray-border text-navy/70 hover:border-teal transition-colors duration-200"
                                        >
                                            {{ $allergen }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Custom allergen entry --}}
                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Something else? Add it manually</label>
                                <div class="flex gap-2">
                                    <input
                                        id="customAllergenInput"
                                        type="text"
                                        placeholder="e.g. Mangoes, MSG, Food dye..."
                                        class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal"
                                    >
                                    <button
                                        type="button"
                                        id="addCustomAllergenBtn"
                                        class="inline-flex items-center gap-1.5 bg-navy hover:bg-navy/90 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors duration-200"
                                    >
                                        <x-lucide-plus class="w-4 h-4" />
                                        Add
                                    </button>
                                </div>
                            </div>

                            {{-- Selected allergens list --}}
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <label class="block text-xs font-semibold text-navy/70">Your allergens</label>
                                    <span class="text-[11px] text-navy/40">Tap the severity word to cycle Mild → Moderate → Severe</span>
                                </div>

                                <div id="selectedAllergenList" class="flex flex-wrap gap-2 min-h-11">
                                    {{-- populated by JS on load + on change --}}
                                </div>

                                <p id="noAllergensNote" class="text-xs text-navy/40 italic">
                                    No allergens added yet.
                                </p>
                            </div>

                            {{-- Hidden inputs synced by JS before submit --}}
                            <div id="allergenHiddenInputs"></div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Security</h2>
                        <p class="text-sm text-navy/55 mb-6">
                            Update your password to keep your account secure.
                        </p>

                        <form id="passwordForm" method="POST" action="{{ route('buyer.settings.password.update') ?? '#' }}" class="space-y-4 max-w-md">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Current Password *</label>
                                <input type="password" name="current_password" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">New Password *</label>
                                <input id="newPassword" type="password" name="password" required minlength="8"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Confirm New Password *</label>
                                <input id="confirmPassword" type="password" name="password_confirmation" required minlength="8"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                <p id="passwordMismatchNote" class="hidden text-xs text-red-500 mt-1.5">Passwords do not match.</p>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- =====================================================
                    NOTIFICATIONS TAB
                ===================================================== --}}
                <div data-tab-content="notifications" class="settings-panel hidden">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Notifications</h2>
                        <p class="text-sm text-navy/55 mb-6">
                            Choose what ShopHop can notify you about.
                        </p>

                        <form method="POST" action="{{ route('buyer.settings.notifications.update') ?? '#' }}" class="space-y-1">
                            @csrf
                            @method('PATCH')

                            @foreach ([
                                ['name' => 'order_updates', 'label' => 'Order Updates', 'desc' => 'Shipping, delivery, and status changes.', 'checked' => true],
                                ['name' => 'promotions', 'label' => 'Promotions & Deals', 'desc' => 'Vouchers, discounts, and flash sales.', 'checked' => true],
                                ['name' => 'chat_messages', 'label' => 'Chat Messages', 'desc' => 'New messages from sellers or support.', 'checked' => true],
                                ['name' => 'price_drops', 'label' => 'Price Drop Alerts', 'desc' => 'When items in your cart or wishlist go on sale.', 'checked' => false],
                            ] as $pref)
                                <label class="flex items-center justify-between gap-4 py-4 border-b border-gray-border last:border-b-0 cursor-pointer">
                                    <span>
                                        <span class="block text-sm font-semibold text-navy">{{ $pref['label'] }}</span>
                                        <span class="block text-xs text-navy/50 mt-0.5">{{ $pref['desc'] }}</span>
                                    </span>
                                    <span class="relative inline-flex items-center shrink-0">
                                        <input type="checkbox" name="{{ $pref['name'] }}" value="1" {{ $pref['checked'] ? 'checked' : '' }}
                                            class="peer sr-only">
                                        <span class="w-11 h-6 rounded-full bg-gray-border peer-checked:bg-teal transition-colors duration-200"></span>
                                        <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            @endforeach

                            <div class="flex justify-end pt-5">
                                <button type="submit"
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       TAB SWITCHING
    ===================================================== */
    const navButtons = document.querySelectorAll('[data-tab-btn]');
    const panels = document.querySelectorAll('[data-tab-content]');

    navButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = btn.getAttribute('data-tab-btn');

            navButtons.forEach(function (b) {
                b.classList.remove('bg-teal', 'text-white');
                b.classList.add('text-navy/70');
            });
            btn.classList.add('bg-teal', 'text-white');
            btn.classList.remove('text-navy/70');

            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.getAttribute('data-tab-content') !== target);
            });
        });
    });

    /* =====================================================
       AGE AUTO-CALCULATION
    ===================================================== */
    const birthdayInput = document.getElementById('birthdayInput');
    const ageInput = document.getElementById('ageInput');

    function calculateAge(dobString) {
        if (!dobString) return '';
        const dob = new Date(dobString);
        if (isNaN(dob.getTime())) return '';

        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age >= 0 ? age : '';
    }

    if (birthdayInput && ageInput) {
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
            const file = avatarInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                if (avatarPreviewLarge) avatarPreviewLarge.src = e.target.result;
                if (avatarPreviewMini) avatarPreviewMini.src = e.target.result;
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

    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                mismatchNote.classList.remove('hidden');
                confirmPassword.focus();
            } else {
                mismatchNote.classList.add('hidden');
            }
        });
    }

    /* =====================================================
       ALLERGEN TAG MANAGEMENT
    ===================================================== */
    const SEVERITY_ORDER = ['mild', 'moderate', 'severe'];
    const SEVERITY_STYLES = {
        mild:     { bg: 'bg-gray-bg',   text: 'text-navy/60' },
        moderate: { bg: 'bg-amber-100', text: 'text-amber-700' },
        severe:   { bg: 'bg-red-100',   text: 'text-red-600' },
    };

    // Seed from the buyer's saved allergens
    let allergenTags = @json(collect($userAllergens)->map(function ($a) {
        return is_array($a) ? $a : ['name' => $a, 'severity' => 'mild'];
    })->values());

    const commonAllergenNames = @json($commonAllergens);
    const selectedList = document.getElementById('selectedAllergenList');
    const noAllergensNote = document.getElementById('noAllergensNote');
    const hiddenInputsContainer = document.getElementById('allergenHiddenInputs');
    const commonChips = document.querySelectorAll('[data-common-allergen]');

    function findTag(name) {
        return allergenTags.find(function (t) { return t.name.toLowerCase() === name.toLowerCase(); });
    }

    function addAllergen(name) {
        name = name.trim();
        if (!name || findTag(name)) return;
        allergenTags.push({ name: name, severity: 'mild' });
        renderAllergens();
    }

    function removeAllergen(name) {
        allergenTags = allergenTags.filter(function (t) { return t.name !== name; });
        renderAllergens();
    }

    function cycleSeverity(name) {
        const tag = findTag(name);
        if (!tag) return;
        const currentIndex = SEVERITY_ORDER.indexOf(tag.severity);
        tag.severity = SEVERITY_ORDER[(currentIndex + 1) % SEVERITY_ORDER.length];
        renderAllergens();
    }

    function renderAllergens() {
        // Selected pills
        selectedList.innerHTML = '';
        noAllergensNote.classList.toggle('hidden', allergenTags.length > 0);

        allergenTags.forEach(function (tag) {
            const style = SEVERITY_STYLES[tag.severity] || SEVERITY_STYLES.mild;

            const pill = document.createElement('span');
            pill.className = 'inline-flex items-center gap-2 pl-3.5 pr-2 py-1.5 rounded-full text-xs sm:text-sm font-medium border border-gray-border';

            pill.innerHTML =
                '<span class="text-navy">' + tag.name + '</span>' +
                '<button type="button" data-cycle="' + tag.name + '" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide ' + style.bg + ' ' + style.text + '">' + tag.severity + '</button>' +
                '<button type="button" data-remove="' + tag.name + '" class="text-navy/40 hover:text-red-500 transition-colors">' +
                    '&times;' +
                '</button>';

            selectedList.appendChild(pill);
        });

        // Wire up the new buttons
        selectedList.querySelectorAll('[data-remove]').forEach(function (btn) {
            btn.addEventListener('click', function () { removeAllergen(btn.getAttribute('data-remove')); });
        });
        selectedList.querySelectorAll('[data-cycle]').forEach(function (btn) {
            btn.addEventListener('click', function () { cycleSeverity(btn.getAttribute('data-cycle')); });
        });

        // Sync quick-add chip active states
        commonChips.forEach(function (chip) {
            const name = chip.getAttribute('data-common-allergen');
            const active = !!findTag(name);
            chip.classList.toggle('bg-teal', active);
            chip.classList.toggle('text-white', active);
            chip.classList.toggle('border-teal', active);
            chip.classList.toggle('text-navy/70', !active);
        });

        // Rebuild hidden inputs for form submission
        hiddenInputsContainer.innerHTML = allergenTags.map(function (tag, i) {
            return '<input type="hidden" name="allergens[' + i + '][name]" value="' + tag.name.replace(/"/g, '&quot;') + '">' +
                   '<input type="hidden" name="allergens[' + i + '][severity]" value="' + tag.severity + '">';
        }).join('');
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
    const addCustomBtn = document.getElementById('addCustomAllergenBtn');

    if (addCustomBtn) {
        addCustomBtn.addEventListener('click', function () {
            addAllergen(customInput.value);
            customInput.value = '';
            customInput.focus();
        });
    }
    if (customInput) {
        customInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addAllergen(customInput.value);
                customInput.value = '';
            }
        });
    }

    renderAllergens();

    /* =====================================================
       ADDRESS CASCADING DROPDOWNS (stub)
       Plug in your existing PSGC API calls from the
       registration page here — same province/municipality/
       barangay pattern, just re-used on this form.
    ===================================================== */
    // const provinceSelect = document.getElementById('provinceSelect');
    // const municipalitySelect = document.getElementById('municipalitySelect');
    // const barangaySelect = document.getElementById('barangaySelect');
    // fetchProvinces().then(populateProvinceSelect);
});
</script>
@endsection