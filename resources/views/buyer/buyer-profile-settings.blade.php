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

@php
    $user = auth()->user();

    $commonAllergens = [
        'Peanuts', 'Tree Nuts', 'Milk / Dairy', 'Eggs', 'Shellfish',
        'Fish', 'Soy', 'Wheat / Gluten', 'Sesame',
    ];
    $userAllergens = $user->allergens ?? [];

    // ---- Sample/demo data — replace with real queries later ----
    $sampleAddresses = [
        ['id' => 1, 'label' => 'Home', 'name' => 'Yesel Ann Alegre', 'phone' => '0917 123 4567', 'full' => 'Blk 12 Lot 5, Molino Blvd., Brgy. Molino III, Bacoor, Cavite', 'default' => true],
        ['id' => 2, 'label' => 'Work', 'name' => 'Yesel Ann Alegre', 'phone' => '0917 123 4567', 'full' => 'Unit 4B, ABC Tower, Ayala Ave., Makati City', 'default' => false],
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

    $twoFactorEnabled = $user->two_factor_enabled ?? false;
@endphp

@section('content')
<section class="bg-gray-bg min-h-screen py-10 sm:py-14">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="mb-8">
            <p class="text-teal-dark text-xs sm:text-sm font-semibold mb-1 tracking-wide">ACCOUNT</p>
            <h1 class="text-navy text-2xl sm:text-3xl font-bold">Account Settings</h1>
            <p class="text-sm text-navy/55 mt-1">
                Manage your profile, orders, and shopping preferences.
            </p>
        </div>

        <div class="grid lg:grid-cols-[260px_1fr] gap-6 lg:gap-8 items-start">

            {{-- =========================================================
                SIDEBAR NAV
            ========================================================= --}}
            <aside class="bg-white rounded-2xl border border-gray-border shadow-sm p-3 lg:sticky lg:top-6 lg:max-h-[calc(100vh-3rem)] lg:overflow-y-auto">

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
                    <p class="hidden lg:block text-[10px] font-bold text-navy/40 uppercase tracking-wider px-3 pt-2 pb-1">Account</p>
                    <button type="button" data-tab-btn="profile"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-white bg-teal transition-colors duration-200">
                        <x-lucide-user class="w-4 h-4" /> Profile Info
                    </button>
                    <button type="button" data-tab-btn="address"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-map-pin class="w-4 h-4" /> Address Book
                    </button>
                    <button type="button" data-tab-btn="allergens"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-shield-alert class="w-4 h-4" /> Allergens & Diet
                    </button>
                    <button type="button" data-tab-btn="security"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-lock class="w-4 h-4" /> Security
                    </button>
                    <button type="button" data-tab-btn="privacy"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-shield class="w-4 h-4" /> Privacy & Account
                    </button>

                    <p class="hidden lg:block text-[10px] font-bold text-navy/40 uppercase tracking-wider px-3 pt-4 pb-1">Shopping</p>
                    <button type="button" data-tab-btn="vouchers"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-ticket class="w-4 h-4" /> Vouchers
                    </button>
                    <button type="button" data-tab-btn="chats"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-message-circle class="w-4 h-4" /> Chats
                    </button>
                    <button type="button" data-tab-btn="activity"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-history class="w-4 h-4" /> Activity Log
                    </button>

                    <p class="hidden lg:block text-[10px] font-bold text-navy/40 uppercase tracking-wider px-3 pt-4 pb-1">Preferences</p>
                    <button type="button" data-tab-btn="notifications"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-bell class="w-4 h-4" /> Notifications
                    </button>
                    <button type="button" data-tab-btn="language"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-globe class="w-4 h-4" /> Language & Region
                    </button>

                    <p class="hidden lg:block text-[10px] font-bold text-navy/40 uppercase tracking-wider px-3 pt-4 pb-1">More</p>
                    <button type="button" data-tab-btn="switch-account"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-repeat class="w-4 h-4" /> Switch Account
                    </button>
                    <button type="button" data-tab-btn="become-seller"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-store class="w-4 h-4" /> Become a Seller
                    </button>
                    <button type="button" data-tab-btn="help"
                        class="settings-tab-btn flex items-center gap-2.5 shrink-0 lg:w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-navy/70 hover:bg-gray-bg transition-colors duration-200">
                        <x-lucide-help-circle class="w-4 h-4" /> Help Center
                    </button>
                </nav>

                <button type="button" data-confirm-action="logout"
                    class="w-full mt-2 pt-3 border-t border-gray-border flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-red-500 hover:bg-red-50 transition-colors duration-200">
                    <x-lucide-log-out class="w-4 h-4" /> Log Out
                </button>
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
                    ADDRESS BOOK TAB
                ===================================================== --}}
                <div data-tab-content="address" class="settings-panel hidden">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-navy text-lg font-bold mb-1">Address Book</h2>
                                <p class="text-sm text-navy/55">
                                    Manage where your orders get delivered.
                                </p>
                            </div>
                            <button type="button" id="addAddressBtn"
                                class="inline-flex items-center gap-1.5 bg-navy hover:bg-navy/90 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors duration-200 shrink-0">
                                <x-lucide-plus class="w-4 h-4" /> Add New Address
                            </button>
                        </div>

                        <div id="addressList" class="space-y-3">
                            @foreach ($sampleAddresses as $address)
                                <div data-address-card data-address-id="{{ $address['id'] }}" class="border border-gray-border rounded-xl p-4">
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

                        <form method="POST" action="{{ Route::has('buyer.settings.allergens.update') ? route('buyer.settings.allergens.update') : '#' }}" class="space-y-5">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Common allergens — tap to add</label>
                                <div id="commonAllergenChips" class="flex flex-wrap gap-2">
                                    @foreach ($commonAllergens as $allergen)
                                        <button type="button" data-common-allergen="{{ $allergen }}"
                                            class="chip-toggle px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-medium border border-gray-border text-navy/70 hover:border-teal transition-colors duration-200">
                                            {{ $allergen }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-2.5">Something else? Add it manually</label>
                                <div class="flex gap-2">
                                    <input id="customAllergenInput" type="text" placeholder="e.g. Mangoes, MSG, Food dye..."
                                        class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                    <button type="button" id="addCustomAllergenBtn"
                                        class="inline-flex items-center gap-1.5 bg-navy hover:bg-navy/90 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors duration-200">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8 space-y-8">

                        <div>
                            <h2 class="text-navy text-lg font-bold mb-1">Security</h2>
                            <p class="text-sm text-navy/55 mb-6">
                                Update your password to keep your account secure.
                            </p>

                            <form id="passwordForm" method="POST" action="{{ Route::has('buyer.settings.password.update') ? route('buyer.settings.password.update') : '#' }}" class="space-y-4 max-w-md">
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

                        <div class="pt-6 border-t border-gray-border">
                            <label class="flex items-center justify-between gap-4 cursor-pointer max-w-md">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8 space-y-8">

                        <div>
                            <h2 class="text-navy text-lg font-bold mb-1">Privacy & Account</h2>
                            <p class="text-sm text-navy/55">
                                Control your data and how visible your activity is to others.
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4 max-w-lg">
                            <div>
                                <p class="text-sm font-semibold text-navy">Download My Data</p>
                                <p class="text-xs text-navy/50 mt-0.5">Get a copy of your profile, orders, and activity.</p>
                            </div>
                            <button type="button" class="inline-flex items-center gap-1.5 border-2 border-navy text-navy text-xs sm:text-sm font-semibold px-4 py-2 rounded-full hover:bg-navy hover:text-white transition-colors duration-200 shrink-0">
                                <x-lucide-download class="w-4 h-4" /> Request
                            </button>
                        </div>

                        <label class="flex items-center justify-between gap-4 cursor-pointer max-w-lg">
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
                        <div class="pt-6 border-t border-gray-border">
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wide mb-4">Danger Zone</p>

                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">Deactivate Account</p>
                                        <p class="text-xs text-navy/55 mt-0.5">Temporarily hide your profile. Log back in anytime to reactivate.</p>
                                    </div>
                                    <button type="button" data-confirm-action="deactivate"
                                        class="inline-flex items-center gap-1.5 bg-white border-2 border-amber-500 text-amber-700 text-xs sm:text-sm font-semibold px-4 py-2 rounded-full hover:bg-amber-500 hover:text-white transition-colors duration-200 shrink-0">
                                        <x-lucide-power class="w-4 h-4" /> Deactivate
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">Delete Account</p>
                                        <p class="text-xs text-navy/55 mt-0.5">Permanently erase your account, orders, and saved data. Cannot be undone.</p>
                                    </div>
                                    <button type="button" data-confirm-action="delete"
                                        class="inline-flex items-center gap-1.5 bg-white border-2 border-red-500 text-red-600 text-xs sm:text-sm font-semibold px-4 py-2 rounded-full hover:bg-red-500 hover:text-white transition-colors duration-200 shrink-0">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">My Vouchers</h2>
                        <p class="text-sm text-navy/55 mb-6">Discounts and perks available on your account.</p>

                        <div class="flex gap-2 mb-6 max-w-md">
                            <input type="text" placeholder="Enter voucher code"
                                class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            <button type="button" class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors duration-200">
                                Redeem
                            </button>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach ($sampleVouchers as $voucher)
                                @php $expired = $voucher['status'] === 'expired'; @endphp
                                <div class="relative border border-dashed rounded-xl p-4 {{ $expired ? 'border-gray-border opacity-50' : 'border-teal' }}">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-navy text-lg font-bold mb-1">Chats</h2>
                                <p class="text-sm text-navy/55">Recent conversations with sellers and support.</p>
                            </div>
                            <a href="#" class="text-xs sm:text-sm font-semibold text-teal-dark hover:text-teal shrink-0">
                                Open Full Chat
                            </a>
                        </div>

                        <div class="divide-y divide-gray-border">
                            @foreach ($sampleChats as $chat)
                                <a href="#" class="flex items-center gap-3 py-3.5 hover:bg-gray-bg -mx-2 px-2 rounded-lg transition-colors duration-150">
                                    <div class="w-10 h-10 rounded-full bg-teal-light text-teal-dark flex items-center justify-center text-sm font-bold shrink-0">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Activity Log</h2>
                        <p class="text-sm text-navy/55 mb-6">A history of actions taken on your account.</p>

                        <div class="space-y-0">
                            @foreach ($sampleActivity as $i => $entry)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <span class="w-2.5 h-2.5 rounded-full bg-teal shrink-0 mt-1.5"></span>
                                        @if (! $loop->last)
                                            <span class="w-px flex-1 bg-gray-border"></span>
                                        @endif
                                    </div>
                                    <div class="pb-6">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Notifications</h2>
                        <p class="text-sm text-navy/55 mb-6">Choose what ShopHop can notify you about.</p>

                        <form method="POST" action="{{ Route::has('buyer.settings.notifications.update') ? route('buyer.settings.notifications.update') : '#' }}" class="space-y-1">
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
                                        <input type="checkbox" name="{{ $pref['name'] }}" value="1" {{ $pref['checked'] ? 'checked' : '' }} class="peer sr-only">
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

                {{-- =====================================================
                    LANGUAGE & REGION TAB
                ===================================================== --}}
                <div data-tab-content="language" class="settings-panel hidden">
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Language & Region</h2>
                        <p class="text-sm text-navy/55 mb-6">Set how ShopHop displays content for you.</p>

                        <form method="POST" action="{{ Route::has('buyer.settings.language.update') ? route('buyer.settings.language.update') : '#' }}" class="space-y-4 max-w-md">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Language</label>
                                <select name="language"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                                    <option value="en" {{ ($user->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="fil" {{ ($user->language ?? '') === 'fil' ? 'selected' : '' }}>Filipino</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Currency</label>
                                <input type="text" value="₱ Philippine Peso" disabled
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border bg-gray-bg text-sm text-navy/50">
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Switch Account</h2>
                        <p class="text-sm text-navy/55 mb-6">Accounts linked to this device.</p>

                        <div class="space-y-3">
                            @foreach ($linkedAccounts as $account)
                                <div class="flex items-center justify-between gap-3 border rounded-xl p-4 {{ $account['active'] ? 'border-teal bg-teal-light/30' : 'border-gray-border' }}">
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
                                            class="text-xs sm:text-sm font-semibold text-navy border-2 border-navy px-4 py-1.5 rounded-full hover:bg-navy hover:text-white transition-colors duration-200 shrink-0">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8 sm:p-10 text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="w-16 h-16 rounded-2xl bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-store class="w-8 h-8" />
                            </div>
                            <div>
                                <h2 class="text-navy text-lg font-bold mb-1">Turn Your Passion Into Profit</h2>
                                <p class="text-sm text-navy/55 max-w-md">
                                    Start selling on ShopHop and reach buyers all over the country.
                                    Your buyer account stays active — you'll just get a Seller Center too.
                                </p>
                                <button type="button" data-confirm-action="become-seller"
                                    class="mt-4 inline-flex items-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-colors duration-200">
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
                    <div class="bg-white rounded-2xl border border-gray-border shadow-sm p-6 sm:p-8">

                        <h2 class="text-navy text-lg font-bold mb-1">Help Center</h2>
                        <p class="text-sm text-navy/55 mb-6">Get support or learn more about ShopHop.</p>

                        <div class="divide-y divide-gray-border">
                            @foreach ([
                                'Frequently Asked Questions',
                                'Contact Support',
                                'Report a Problem',
                                'Terms of Service',
                                'Privacy Policy',
                            ] as $link)
                                <a href="#" class="flex items-center justify-between py-3.5 text-sm text-navy hover:text-teal-dark transition-colors">
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

{{-- =========================================================
    CONFIRM MODAL (generic — used by all data-confirm-action buttons)
========================================================= --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-navy/50"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-border max-w-sm w-full p-6">
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
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
        </div>

        <div class="flex gap-3">
            <button type="button" id="confirmCancelBtn"
                class="flex-1 border-2 border-navy text-navy text-sm font-semibold px-4 py-2.5 rounded-full hover:bg-navy hover:text-white transition-colors duration-200">
                Cancel
            </button>
            <button type="button" id="confirmActionBtn"
                class="flex-1 text-white text-sm font-semibold px-4 py-2.5 rounded-full transition-colors duration-200 disabled:opacity-40 disabled:cursor-not-allowed">
                Confirm
            </button>
        </div>
    </div>
</div>

{{-- =========================================================
    ADD / EDIT ADDRESS MODAL
========================================================= --}}
<div id="addressModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-navy/50"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-border max-w-lg w-full p-6 sm:p-7 max-h-[90vh] overflow-y-auto">
        <h3 id="addressModalTitle" class="text-navy text-base font-bold mb-5">Add New Address</h3>

        <form id="addressForm" method="POST" action="{{ Route::has('buyer.settings.address.store') ? route('buyer.settings.address.store') : '#' }}" class="space-y-4">
            @csrf
            <input type="hidden" name="address_id" id="addressIdInput" value="">

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Label</label>
                    <select name="label" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="Home">Home</option>
                        <option value="Work">Work</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Recipient Name *</label>
                    <input type="text" name="recipient_name" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy/70 mb-1.5">Contact No. *</label>
                <input type="tel" name="phone" placeholder="09XX XXX XXXX" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
            </div>

            {{-- Province / Municipality / Barangay — wire up to your existing PSGC API script from registration --}}
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Province *</label>
                    <select id="modalProvinceSelect" name="province" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select province</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Municipality/City *</label>
                    <select id="modalMunicipalitySelect" name="municipality" required disabled
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select province first</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Barangay *</label>
                    <select id="modalBarangaySelect" name="barangay" required disabled
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy disabled:bg-gray-bg disabled:text-navy/40 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select municipality first</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">Street *</label>
                    <input type="text" name="street" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy/70 mb-1.5">House No. / Unit / Building</label>
                    <input type="text" name="house_number"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-border text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-navy">
                <input type="checkbox" name="set_default" value="1" class="accent-teal">
                Set as default address
            </label>

            <div class="flex gap-3 pt-2">
                <button type="button" id="addressModalCancelBtn"
                    class="flex-1 border-2 border-navy text-navy text-sm font-semibold px-4 py-2.5 rounded-full hover:bg-navy hover:text-white transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-4 py-2.5 rounded-full transition-colors duration-200">
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

            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
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
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age--;
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

    let allergenTags = @json(collect($userAllergens)->map(function ($a) {
        return is_array($a) ? $a : ['name' => $a, 'severity' => 'mild'];
    })->values());

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
        if (!selectedList) return;
        selectedList.innerHTML = '';
        noAllergensNote.classList.toggle('hidden', allergenTags.length > 0);

        allergenTags.forEach(function (tag) {
            const style = SEVERITY_STYLES[tag.severity] || SEVERITY_STYLES.mild;
            const pill = document.createElement('span');
            pill.className = 'inline-flex items-center gap-2 pl-3.5 pr-2 py-1.5 rounded-full text-xs sm:text-sm font-medium border border-gray-border';
            pill.innerHTML =
                '<span class="text-navy">' + tag.name + '</span>' +
                '<button type="button" data-cycle="' + tag.name + '" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide ' + style.bg + ' ' + style.text + '">' + tag.severity + '</button>' +
                '<button type="button" data-remove="' + tag.name + '" class="text-navy/40 hover:text-red-500 transition-colors">&times;</button>';
            selectedList.appendChild(pill);
        });

        selectedList.querySelectorAll('[data-remove]').forEach(function (btn) {
            btn.addEventListener('click', function () { removeAllergen(btn.getAttribute('data-remove')); });
        });
        selectedList.querySelectorAll('[data-cycle]').forEach(function (btn) {
            btn.addEventListener('click', function () { cycleSeverity(btn.getAttribute('data-cycle')); });
        });

        commonChips.forEach(function (chip) {
            const name = chip.getAttribute('data-common-allergen');
            const active = !!findTag(name);
            chip.classList.toggle('bg-teal', active);
            chip.classList.toggle('text-white', active);
            chip.classList.toggle('border-teal', active);
            chip.classList.toggle('text-navy/70', !active);
        });

        hiddenInputsContainer.innerHTML = allergenTags.map(function (tag, i) {
            return '<input type="hidden" name="allergens[' + i + '][name]" value="' + tag.name.replace(/"/g, '&quot;') + '">' +
                   '<input type="hidden" name="allergens[' + i + '][severity]" value="' + tag.severity + '">';
        }).join('');
    }

    commonChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const name = chip.getAttribute('data-common-allergen');
            if (findTag(name)) removeAllergen(name); else addAllergen(name);
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
            if (e.key === 'Enter') { e.preventDefault(); addAllergen(customInput.value); customInput.value = ''; }
        });
    }

    renderAllergens();

    /* =====================================================
       GENERIC CONFIRM MODAL
       Every destructive/important action routes through here.
       Add data-confirm-action="key" to any button to use it.
    ===================================================== */
    const CONFIRM_ACTIONS = {
        logout: {
            title: 'Log Out',
            message: 'Are you sure you want to log out of your ShopHop account?',
            confirmLabel: 'Log Out',
            danger: false,
            onConfirm: function () { document.getElementById('logoutForm').submit(); },
        },
        deactivate: {
            title: 'Deactivate Account',
            message: 'Your profile and listings will be hidden until you log back in. You can reactivate anytime by logging in again.',
            confirmLabel: 'Deactivate Account',
            danger: true,
            onConfirm: function () { document.getElementById('deactivateForm').submit(); },
        },
        delete: {
            title: 'Delete Account Permanently',
            message: 'This permanently deletes your account, order history, and saved data. This cannot be undone.',
            confirmLabel: 'Delete My Account',
            danger: true,
            requireTyping: 'DELETE',
            onConfirm: function () { document.getElementById('deleteAccountForm').submit(); },
        },
        'remove-address': {
            title: 'Remove Address',
            message: 'This will remove this address from your address book.',
            confirmLabel: 'Remove',
            danger: true,
            onConfirm: function (triggerBtn) {
                const id = triggerBtn.getAttribute('data-target-id');
                const card = document.querySelector('[data-address-card][data-address-id="' + id + '"]');
                if (card) card.remove();
                // TODO: also fire a real DELETE request to your address route here.
            },
        },
        'switch-account': {
            title: 'Switch Account',
            message: 'Switch to this account? You will be logged out of your current session.',
            confirmLabel: 'Switch',
            danger: false,
            onConfirm: function () {
                // TODO: redirect to your real switch-account route.
                window.location.href = '#';
            },
        },
        'become-seller': {
            title: 'Become a Seller',
            message: "You'll be taken to Seller Registration. Your buyer account stays active.",
            confirmLabel: 'Continue',
            danger: false,
            onConfirm: function () {
                // TODO: redirect to your real seller registration route.
                window.location.href = '#';
            },
        },
    };

    const confirmModal = document.getElementById('confirmModal');
    const confirmIconWrap = document.getElementById('confirmIconWrap');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const confirmTypingWrap = document.getElementById('confirmTypingWrap');
    const confirmTypingWord = document.getElementById('confirmTypingWord');
    const confirmTypingInput = document.getElementById('confirmTypingInput');

    let activeConfirmConfig = null;
    let activeTriggerBtn = null;

    function openConfirmModal(actionKey, triggerBtn) {
        const config = CONFIRM_ACTIONS[actionKey];
        if (!config) return;

        activeConfirmConfig = config;
        activeTriggerBtn = triggerBtn;

        const overrideMessage = triggerBtn.getAttribute('data-confirm-message');

        confirmTitle.textContent = config.title;
        confirmMessage.textContent = overrideMessage || config.message;
        confirmActionBtn.textContent = config.confirmLabel;

        if (config.danger) {
            confirmIconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-red-100 text-red-600';
            confirmActionBtn.className = 'flex-1 text-white text-sm font-semibold px-4 py-2.5 rounded-full transition-colors duration-200 disabled:opacity-40 disabled:cursor-not-allowed bg-red-500 hover:bg-red-600';
        } else {
            confirmIconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-teal-light text-teal-dark';
            confirmActionBtn.className = 'flex-1 text-white text-sm font-semibold px-4 py-2.5 rounded-full transition-colors duration-200 disabled:opacity-40 disabled:cursor-not-allowed bg-teal hover:bg-teal-dark';
        }

        if (config.requireTyping) {
            confirmTypingWrap.classList.remove('hidden');
            confirmTypingWord.textContent = config.requireTyping;
            confirmTypingInput.value = '';
            confirmActionBtn.disabled = true;
        } else {
            confirmTypingWrap.classList.add('hidden');
            confirmActionBtn.disabled = false;
        }

        confirmModal.classList.remove('hidden');
    }

    function closeConfirmModal() {
        confirmModal.classList.add('hidden');
        activeConfirmConfig = null;
        activeTriggerBtn = null;
    }

    document.querySelectorAll('[data-confirm-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openConfirmModal(btn.getAttribute('data-confirm-action'), btn);
        });
    });

    confirmCancelBtn.addEventListener('click', closeConfirmModal);

    if (confirmTypingInput) {
        confirmTypingInput.addEventListener('input', function () {
            if (activeConfirmConfig && activeConfirmConfig.requireTyping) {
                confirmActionBtn.disabled = confirmTypingInput.value !== activeConfirmConfig.requireTyping;
            }
        });
    }

    confirmActionBtn.addEventListener('click', function () {
        if (!activeConfirmConfig) return;
        activeConfirmConfig.onConfirm(activeTriggerBtn);
        closeConfirmModal();
    });

    /* =====================================================
       ADDRESS MODAL (add / edit)
    ===================================================== */
    const addressModal = document.getElementById('addressModal');
    const addressModalTitle = document.getElementById('addressModalTitle');
    const addAddressBtn = document.getElementById('addAddressBtn');
    const addressModalCancelBtn = document.getElementById('addressModalCancelBtn');
    const addressIdInput = document.getElementById('addressIdInput');

    function openAddressModal(isEdit) {
        addressModalTitle.textContent = isEdit ? 'Edit Address' : 'Add New Address';
        addressModal.classList.remove('hidden');
    }

    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', function () {
            addressIdInput.value = '';
            openAddressModal(false);
        });
    }

    document.querySelectorAll('[data-edit-address]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            addressIdInput.value = btn.getAttribute('data-edit-address');
            // TODO: prefill the modal fields with this address's real data.
            openAddressModal(true);
        });
    });

    document.querySelectorAll('[data-set-default]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // TODO: submit a request to set this address as default.
            btn.closest('[data-address-card]').scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    if (addressModalCancelBtn) {
        addressModalCancelBtn.addEventListener('click', function () {
            addressModal.classList.add('hidden');
        });
    }

    /* =====================================================
       ADDRESS CASCADING DROPDOWNS (stub)
       Plug in your existing PSGC API calls from the
       registration page here.
    ===================================================== */
    // const provinceSelect = document.getElementById('modalProvinceSelect');
    // const municipalitySelect = document.getElementById('modalMunicipalitySelect');
    // const barangaySelect = document.getElementById('modalBarangaySelect');
    // fetchProvinces().then(populateProvinceSelect);
});
</script>
@endsection