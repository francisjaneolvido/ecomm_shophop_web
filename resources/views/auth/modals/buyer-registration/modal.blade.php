{{-- =========================================================
    BUYER REGISTRATION MODAL
    Path: resources/views/auth/modals/buyer-registration-modal.blade.php

    Opened from account-type-modal when "Buyer" is selected.
    Redesigned to match login-modal.blade.php's chrome: floating
    back/close circle buttons + a thin teal accent bar, plus a
    login-style header (icon badge, small label, big heading,
    description) — and the split-screen pattern from
    seller-registration-modal.blade.php — artistic navy panel on
    the left + form on the right — at a 30/70 ratio instead of
    the seller's wider left panel, since the buyer flow is
    shorter.

    Steps are plain step-by-step (one panel shown, the rest
    `hidden`) — no per-step transition, so navigation is
    instant and predictable. Panels are kept compact enough
    that the dialog doesn't need an internal scrollbar in
    normal use (a safety-net overflow-y-auto is still kept on
    the dialog for very small viewports).

    IMPORTANT: This file is @include()'d directly into
    layouts/app.blade.php. It must NEVER contain @extends —
    doing so causes layouts/app.blade.php to re-render itself
    from inside its own @include, which recurses forever and
    exhausts PHP's memory limit / execution time.

    NOTE: all ids, name attributes, data-* hooks, and the
    field-level JS below are unchanged from the original —
    only markup structure, layout, and the step-transition
    mechanics were reworked for the split-screen redesign.
    UI/UX PASS V2: refined hierarchy, responsive spacing, elevated form
    surfaces, and stronger touch/focus affordances while preserving all
    existing IDs, names, data hooks, validation flow, and backend fields.
========================================================= --}}

<div
    id="buyer-registration-modal"
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-4 md:p-6
           opacity-0 transition-opacity duration-300 ease-out"
    aria-hidden="true"
>
    {{-- Backdrop — matches seller-registration-modal's opacity/blur --}}
    <button
        type="button"
        data-buyer-registration-modal-close
        aria-label="Close buyer registration"
        class="absolute inset-0 w-full h-full
               bg-navy/45 backdrop-blur-[5px]
               cursor-default"
    ></button>

    {{-- Dialog --}}
    <div
        id="buyer-registration-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="buyer-registration-modal-title"
        class="relative z-10
               w-full h-full
               sm:h-auto
               sm:max-h-[calc(100vh-2rem)]
               sm:max-w-5xl lg:max-w-6xl xl:max-w-7xl
               overflow-y-auto
               sm:rounded-4xl
               bg-white
               border-0 sm:border sm:border-white/80
               shadow-2xl shadow-navy/25
               opacity-0 scale-95 translate-y-3
               transition-all duration-300 ease-out"
    >

        {{-- Back — floating, matches login-modal / account-type-modal chrome --}}
        <button
            type="button"
            data-buyer-registration-modal-back
            aria-label="Back to account type"
            class="absolute top-4 left-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-white/90 backdrop-blur
                   border border-gray-border/70
                   shadow-sm shadow-navy/10
                   text-navy/55
                   flex items-center justify-center
                   hover:bg-teal-light hover:border-teal/20
                   hover:text-teal-dark hover:-translate-y-0.5
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition-all duration-200"
        >
            <x-lucide-arrow-left class="w-4 h-4" />
        </button>

        {{-- Close — floating, matches login-modal / account-type-modal chrome --}}
        <button
            type="button"
            data-buyer-registration-modal-close
            aria-label="Close buyer registration"
            class="absolute top-4 right-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-white/90 backdrop-blur
                   border border-gray-border/70
                   shadow-sm shadow-navy/10
                   text-navy/55
                   flex items-center justify-center
                   hover:bg-teal-light hover:border-teal/20
                   hover:text-teal-dark hover:-translate-y-0.5
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition-all duration-200"
        >
            <x-lucide-x class="w-4 h-4" />
        </button>

        {{-- Accent — matches login-modal --}}
        <div class="hidden sm:block h-1.5 bg-linear-to-r from-teal/70 via-teal to-teal-dark"></div>


        {{-- =====================================================
            SPLIT CONTENT — 30 / 70
        ====================================================== --}}
        <div class="grid lg:grid-cols-[3fr_7fr]">


            @include('auth.modals.shared.registration-side-panel', [
                'roleIcon' => 'lucide-shopping-bag',
                'eyebrow' => 'JOIN AS A BUYER',
                'title' => 'Shop more.',
                'highlight' => 'Discover more.',
                'description' => 'Create your buyer account and explore products from trusted sellers with a smoother, safer ShopHop experience.',
                'features' => [
                    ['icon' => 'lucide-shopping-bag', 'title' => 'Thousands of Products', 'description' => 'Discover items from trusted sellers'],
                    ['icon' => 'lucide-shield-check', 'title' => 'Secure Checkout', 'description' => 'Protected payments from cart to checkout'],
                    ['icon' => 'lucide-truck', 'title' => 'Trackable Delivery', 'description' => 'Follow your order from dispatch to door'],
                ],
                'stats' => [
                    ['value' => '50K+', 'label' => 'Products'],
                    ['value' => '10K+', 'label' => 'Sellers'],
                    ['value' => '4.8', 'label' => 'Rating', 'star' => true],
                ],
            ])

            {{-- =================================================
                RIGHT REGISTRATION PANEL (70%)
            ================================================== --}}
            <div
                class="bg-linear-to-b from-white via-white to-gray-bg/30
                       px-5 sm:px-8 lg:px-10 xl:px-12
                       py-6 sm:py-8 lg:py-10"
            >

                <div id="buyer-registration-panel" class="max-w-2xl mx-auto lg:py-1">


                    <div class="pt-10 lg:pt-0">

                        {{-- Mobile branding --}}
                        <div class="lg:hidden inline-flex items-center gap-3 mb-6 rounded-2xl border border-gray-border/70 bg-white px-3 py-2 shadow-sm">

                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ShopHop"
                                class="w-8 h-8 object-contain"
                            >

                            <div>

                                <p class="font-bold text-navy text-sm">
                                    ShopHop
                                </p>

                                <p class="text-[7px] tracking-[0.2em] text-teal-dark mt-1">
                                    HOP IN. SHOP MORE.
                                </p>

                            </div>

                        </div>


                        {{-- Header --}}
                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="shrink-0 w-12 h-12
                                       rounded-2xl
                                       bg-teal-light
                                       border border-teal/10
                                       shadow-sm
                                       flex items-center justify-center
                                       text-teal-dark"
                            >
                                <x-lucide-shopping-bag class="w-5 h-5" />
                            </div>

                            <div class="min-w-0 pt-0.5">

                                <p class="text-teal-dark text-[10px] font-extrabold tracking-[0.16em] mb-1.5">
                                    BUYER REGISTRATION
                                </p>

                                <h2
                                    id="buyer-registration-modal-title"
                                    class="text-navy text-2xl sm:text-[1.75rem] font-extrabold tracking-tight leading-tight"
                                >
                                    Create Your Account
                                </h2>

                                <p class="text-sm text-navy/50 mt-1.5 leading-relaxed">
                                    Four quick steps and you're ready to shop.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =============================================
                        STEP PROGRESS BAR (4 steps)
                    ============================================== --}}
                    <div class="mb-6 rounded-2xl border border-gray-border/70 bg-white/90 px-4 sm:px-5 py-4 shadow-sm shadow-navy/5">

                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[10px] font-bold tracking-[0.14em] text-navy/45 uppercase">Registration progress</p>
                            <p class="text-[10px] font-semibold text-teal-dark">4 steps</p>
                        </div>

                        <div
                            class="grid items-center"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <div
                                class="step-circle step-circle-active justify-self-center w-8 h-8 rounded-full border-2 flex items-center justify-center text-[10.5px] font-bold shadow-sm bg-teal border-teal text-white"
                                data-step-circle="1"
                            >
                                <span class="step-number">1</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-0.5 mx-1.5 rounded-full bg-gray-border" data-step-line="1"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border-2 flex items-center justify-center text-[10.5px] font-bold shadow-sm bg-white border-gray-border text-navy/30"
                                data-step-circle="2"
                            >
                                <span class="step-number">2</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-0.5 mx-1.5 rounded-full bg-gray-border" data-step-line="2"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border-2 flex items-center justify-center text-[10.5px] font-bold shadow-sm bg-white border-gray-border text-navy/30"
                                data-step-circle="3"
                            >
                                <span class="step-number">3</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-0.5 mx-1.5 rounded-full bg-gray-border" data-step-line="3"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border-2 flex items-center justify-center text-[10.5px] font-bold shadow-sm bg-white border-gray-border text-navy/30"
                                data-step-circle="4"
                            >
                                <span class="step-number">4</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                        </div>

                        <div
                            class="grid mt-2.5"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <p class="step-label max-w-18 mx-auto text-center text-[10px] sm:text-[11px] font-semibold leading-tight text-navy" data-step-label="1">
                                Personal
                            </p>

                            <div></div>

                            <p class="step-label max-w-18 mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="2">
                                Address
                            </p>

                            <div></div>

                            <p class="step-label max-w-18 mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="3">
                                Verification
                            </p>

                            <div></div>

                            <p class="step-label max-w-18 mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="4">
                                Security
                            </p>

                        </div>

                    </div>



                    {{-- Validation Errors --}}
                    @if ($errors->any())

                        <div class="mb-5 rounded-2xl border border-red-200/80 bg-red-50 p-4 shadow-sm">

                            <div class="flex gap-3">

                                <x-lucide-circle-alert class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />

                                <div>

                                    <p class="text-sm font-semibold text-red-700">
                                        Please check your information.
                                    </p>

                                    <ul class="mt-2 space-y-1 text-xs text-red-600">

                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach

                                    </ul>

                                </div>

                            </div>

                        </div>

                    @endif



                    <form
                        action="{{ route('register.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        id="buyer-register-form"
                    >

                        @csrf
                        <input type="hidden" name="account_type" value="buyer">

                        {{-- Viewport wraps all step panels so absolutely
                             positioned panels (mid-transition) don't
                             collapse the layout height. --}}
                        <div id="buyer-step-viewport" class="rounded-3xl border border-gray-border/70 bg-white p-4 sm:p-5 shadow-sm shadow-navy/5">


                        {{-- =========================================
                            STEP 1 — PERSONAL DETAILS
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="flex items-start gap-3 mb-5">
                                <div class="shrink-0 w-9 h-9 rounded-xl bg-teal-light flex items-center justify-center text-teal-dark">
                                    <x-lucide-user class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Personal information</p>
                                    <p class="text-[11px] text-navy/45 mt-0.5">Tell us a little about yourself.</p>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- First Name --}}
                                <div>

                                    <label for="buyer_first_name" class="block text-xs font-semibold text-navy mb-2">
                                        First Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="text"
                                            id="buyer_first_name"
                                            name="first_name"
                                            value="{{ old('first_name') }}"
                                            required
                                            autocomplete="given-name"
                                            placeholder="Enter first name"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="buyer_first_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Last Name --}}
                                <div>

                                    <label for="buyer_last_name" class="block text-xs font-semibold text-navy mb-2">
                                        Last Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="text"
                                            id="buyer_last_name"
                                            name="last_name"
                                            value="{{ old('last_name') }}"
                                            required
                                            autocomplete="family-name"
                                            placeholder="Enter last name"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="buyer_last_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Middle Initial --}}
                                <div>

                                    <label for="buyer_middle_initial" class="block text-xs font-semibold text-navy mb-2">
                                        Middle Initial
                                    </label>

                                    <input
                                        type="text"
                                        id="buyer_middle_initial"
                                        name="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="2"
                                        placeholder="e.g. M."
                                        class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                    >

                                    <p id="buyer_middle_initial_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Sex --}}
                                <div>

                                    <label for="buyer_sex" class="block text-xs font-semibold text-navy mb-2">
                                        Sex
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-users class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
    id="buyer_sex"
    name="sex"
    required
    class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
>
    <option value="">Select sex</option>

    <option value="Male" @selected(old('sex') === 'Male')>
        Male
    </option>

    <option value="Female" @selected(old('sex') === 'Female')>
        Female
    </option>

    <option value="Prefer not to say" @selected(old('sex') === 'Prefer not to say')>
        Prefer not to say
    </option>
</select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <p id="buyer_sex_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Email --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_email" class="block text-xs font-semibold text-navy mb-2">
                                        E-mail
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-mail class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="email"
                                            id="buyer_email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autocomplete="email"
                                            placeholder="your@email.com"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="buyer_email_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Contact --}}
                                <div>

                                    <label for="buyer_contact_no" class="block text-xs font-semibold text-navy mb-2">
                                        Contact No.
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-phone class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="tel"
                                            id="buyer_contact_no"
                                            name="contact_no"
                                            value="{{ old('contact_no') }}"
                                            required
                                            inputmode="numeric"
                                            maxlength="11"
                                            placeholder="09XXXXXXXXX"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="buyer_contact_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Birthday + Age --}}
                                <div>

                                    <label for="buyer_birthday" class="block text-xs font-semibold text-navy mb-2">
                                        Birthday
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="flex gap-2">

                                        <div class="relative flex-1">

                                            <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                            <input
                                                type="date"
                                                id="buyer_birthday"
                                                name="birthday"
                                                value="{{ old('birthday') }}"
                                                max="{{ now()->format('Y-m-d') }}"
                                                required
                                                class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-2 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                            >

                                        </div>

                                        <input
                                            type="number"
                                            id="buyer_age"
                                            value="{{ old('age') }}"
                                            readonly
                                            aria-label="Age (auto-generated)"
                                            placeholder="Age"
                                            class="w-16 shrink-0 min-h-12 rounded-2xl border border-gray-border/70 bg-gray-bg px-2 text-center text-sm text-navy outline-none"
                                        >

                                    </div>

                                    <p id="buyer_birthday_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="buyer-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-2xl shadow-md shadow-teal/20 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-teal/20 focus:outline-none focus:ring-4 focus:ring-teal/15 transition-all duration-200"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 2 — ADDRESS
                        ========================================== --}}
                        <div data-step-panel="2" class="hidden">

                            <div class="flex items-start gap-3 mb-5">
                                <div class="shrink-0 w-9 h-9 rounded-xl bg-teal-light flex items-center justify-center text-teal-dark">
                                    <x-lucide-map-pin class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Delivery address</p>
                                    <p class="text-[11px] text-navy/45 mt-0.5">Choose your location, then add your exact street address.</p>
                                </div>
                            </div>


                            <div id="buyer-address-status" class="hidden mb-4 rounded-2xl border border-teal/10 bg-teal-light/50 px-3.5 py-2.5 text-xs text-teal-dark">
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- Province --}}
                                <div>

                                    <label for="buyer_province" class="block text-xs font-semibold text-navy mb-2">
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-map-pin class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="buyer_province"
                                            name="province_code"
                                            required
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
                                        >
                                            <option value="">Select province</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="buyer_province_name" name="province_name" value="{{ old('province_name') }}">

                                    <p id="buyer_province_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- City / Municipality --}}
                                <div>

                                    <label for="buyer_municipality" class="block text-xs font-semibold text-navy mb-2">
                                        Municipality / City
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-building-2 class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="buyer_municipality"
                                            name="municipality_code"
                                            required
                                            disabled
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none"
                                        >
                                            <option value="">Select municipality / city</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="buyer_municipality_name" name="municipality_name" value="{{ old('municipality_name') }}">

                                    <p id="buyer_municipality_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Barangay --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_barangay" class="block text-xs font-semibold text-navy mb-2">
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-home class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="buyer_barangay"
                                            name="barangay_code"
                                            required
                                            disabled
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none"
                                        >
                                            <option value="">Select barangay</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="buyer_barangay_name" name="barangay_name" value="{{ old('barangay_name') }}">

                                    <p id="buyer_barangay_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Street --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_street_address" class="block text-xs font-semibold text-navy mb-2">
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="buyer_street_address"
                                        name="street_address"
                                        rows="2"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full resize-none rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                    >{{ old('street_address') }}</textarea>

                                    <p id="buyer_street_address_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="buyer-step2-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 bg-white text-navy text-sm font-semibold min-h-12 px-5 sm:px-6 py-3 rounded-2xl hover:bg-gray-bg hover:border-navy/15 focus:outline-none focus:ring-4 focus:ring-navy/5 transition-all duration-200"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="buyer-step2-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-2xl shadow-md shadow-teal/20 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-teal/20 focus:outline-none focus:ring-4 focus:ring-teal/15 transition-all duration-200"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 3 — VALID ID
                        ========================================== --}}
                        <div data-step-panel="3" class="hidden">

                            <div class="flex items-start gap-3 mb-5">
                                <div class="shrink-0 w-9 h-9 rounded-xl bg-teal-light flex items-center justify-center text-teal-dark">
                                    <x-lucide-shield-check class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Verify your identity</p>
                                    <p class="text-[11px] text-navy/45 mt-0.5">Upload one clear valid ID to help keep ShopHop secure.</p>
                                </div>
                            </div>

                            <label for="buyer_valid_id" class="block text-xs font-semibold text-navy mb-2">
                                Upload Valid ID
                                <span class="text-red-500">*</span>
                            </label>


                            <label
                                for="buyer_valid_id"
                                class="group flex items-center gap-4 rounded-2xl border-2 border-dashed border-gray-border/80 bg-gray-bg/70 hover:border-teal/60 hover:bg-teal-light/30 px-4 sm:px-5 py-5 cursor-pointer transition-all duration-200"
                            >

                                <div class="shrink-0 w-12 h-12 rounded-2xl bg-white border border-gray-border/60 flex items-center justify-center text-teal-dark shadow-sm group-hover:-translate-y-0.5 transition-transform">
                                    <x-lucide-upload class="w-5 h-5" />
                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-semibold text-navy">
                                        Choose a valid ID
                                    </p>

                                    <p class="text-[11px] text-navy/40 mt-1">
                                        JPG, JPEG, PNG or PDF · Max 5MB
                                    </p>

                                    <p id="buyer-file-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>

                                </div>


                                <input
                                    type="file"
                                    id="buyer_valid_id"
                                    name="valid_id"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="hidden"
                                >

                            </label>

                            <p id="buyer_valid_id_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>


                            {{-- Uploaded ID preview --}}
                            <div
                                id="buyer-id-preview-card"
                                class="hidden mt-4 overflow-hidden rounded-2xl border border-gray-border/70 bg-white shadow-sm"
                            >
                                <div class="relative flex min-h-48 items-center justify-center overflow-hidden bg-gray-bg/70 p-3 sm:min-h-56">
                                    <img
                                        id="buyer-id-preview-image"
                                        src=""
                                        alt="Uploaded valid ID preview"
                                        class="hidden max-h-56 w-full rounded-xl object-contain"
                                    >

                                    <div id="buyer-id-preview-pdf" class="hidden flex-col items-center justify-center py-8 text-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-teal-dark shadow-sm ring-1 ring-gray-border/60">
                                            <x-lucide-file-text class="w-6 h-6" />
                                        </div>
                                        <p class="mt-3 text-sm font-bold text-navy">PDF selected</p>
                                        <p class="mt-1 text-[11px] text-navy/45">Use View file to open the document preview.</p>
                                    </div>

                                    <button
                                        type="button"
                                        id="buyer-id-preview-view"
                                        class="absolute right-3 top-3 inline-flex min-h-9 items-center justify-center gap-1.5 rounded-xl bg-navy/90 px-3 py-2 text-[11px] font-semibold text-white shadow-lg backdrop-blur hover:bg-navy focus:outline-none focus:ring-4 focus:ring-navy/15 transition"
                                    >
                                        <x-lucide-maximize-2 class="w-3.5 h-3.5" />
                                        View file
                                    </button>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-t border-gray-border/60 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Uploaded file</p>
                                        <p id="buyer-id-preview-status" class="mt-0.5 truncate text-xs font-semibold text-navy">Preview ready</p>
                                    </div>

                                    <button
                                        type="button"
                                        id="buyer-id-preview-change"
                                        class="shrink-0 text-[11px] font-bold text-teal-dark hover:text-navy transition"
                                    >
                                        Choose another
                                    </button>
                                </div>
                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="buyer-step3-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 bg-white text-navy text-sm font-semibold min-h-12 px-5 sm:px-6 py-3 rounded-2xl hover:bg-gray-bg hover:border-navy/15 focus:outline-none focus:ring-4 focus:ring-navy/5 transition-all duration-200"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="buyer-step3-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-2xl shadow-md shadow-teal/20 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-teal/20 focus:outline-none focus:ring-4 focus:ring-teal/15 transition-all duration-200"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 4 — SECURITY
                        ========================================== --}}
                        <div data-step-panel="4" class="hidden">

                            <div class="flex items-start gap-3 mb-5">
                                <div class="shrink-0 w-9 h-9 rounded-xl bg-teal-light flex items-center justify-center text-teal-dark">
                                    <x-lucide-lock class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-navy">Secure your account</p>
                                    <p class="text-[11px] text-navy/45 mt-0.5">Create a strong password and review the final agreement.</p>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label for="buyer_password" class="block text-xs font-semibold text-navy mb-2">
                                        Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="password"
                                            id="buyer_password"
                                            name="password"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Minimum 8 characters"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-11 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                        <button
                                            type="button"
                                            id="buyer_toggle_password"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:text-teal-dark hover:bg-gray-bg focus:outline-none focus:ring-4 focus:ring-teal/10 transition"
                                        >
                                            <svg class="password-icon-show w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>

                                            <svg class="password-icon-hide w-4 h-4" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                                <path d="M1 1l22 22" />
                                            </svg>
                                        </button>

                                    </div>

                                    <p id="buyer_password_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                <div>

                                    <label for="buyer_password_confirmation" class="block text-xs font-semibold text-navy mb-2">
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="password"
                                            id="buyer_password_confirmation"
                                            name="password_confirmation"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Re-enter password"
                                            class="w-full min-h-12 rounded-2xl border border-gray-border/80 bg-white shadow-sm shadow-navy/5 pl-11 pr-11 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                        <button
                                            type="button"
                                            id="buyer_toggle_password_confirmation"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:text-teal-dark hover:bg-gray-bg focus:outline-none focus:ring-4 focus:ring-teal/10 transition"
                                        >
                                            <svg class="password-icon-show w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>

                                            <svg class="password-icon-hide w-4 h-4" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                                <path d="M1 1l22 22" />
                                            </svg>
                                        </button>

                                    </div>

                                    <p id="buyer_password_confirmation_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>

                            {{-- Password requirements checklist --}}
                            <div
                                id="buyer-password-requirements"
                                class="mt-4 rounded-2xl border border-gray-border/70 bg-gray-bg/70 p-4 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2"
                            >

                                <p class="req-item flex items-center gap-2 text-[10.5px] text-navy/40" data-req="length">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    Minimum 8 characters
                                </p>

                                <p class="req-item flex items-center gap-2 text-[10.5px] text-navy/40" data-req="uppercase">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    1 uppercase letter (A–Z)
                                </p>

                                <p class="req-item flex items-center gap-2 text-[10.5px] text-navy/40" data-req="lowercase">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    1 lowercase letter (a–z)
                                </p>

                                <p class="req-item flex items-center gap-2 text-[10.5px] text-navy/40" data-req="number">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    1 number (0–9)
                                </p>

                                <p class="req-item sm:col-span-2 flex items-center gap-2 text-[10.5px] text-navy/30" data-req="special">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    Special character
                                    <span class="text-navy/30">(! @ # $ % ^ &amp; *)</span>
                                </p>

                            </div>

                            {{-- Approval notice --}}
                            <div class="mt-4 flex gap-3 rounded-2xl border border-teal/15 bg-teal-light/35 p-3.5">

                                <x-lucide-info class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />

                                <p class="text-[11px] text-navy/50 leading-relaxed">
                                    After submitting, please wait for the administrator's approval. Your status will be emailed to you.
                                </p>

                            </div>

                            {{-- Terms & Agreement --}}
                            <div class="mt-4 rounded-2xl border border-gray-border/70 bg-white p-3.5">

                                <label for="buyer_terms" class="flex items-start gap-3 cursor-pointer group">

                                    <input type="checkbox" id="buyer_terms" name="terms" required class="peer sr-only">

                                    <span
                                        class="mt-0.5 shrink-0 w-5 h-5 rounded-md border-2 border-gray-border bg-white flex items-center justify-center peer-checked:bg-teal peer-checked:border-teal group-hover:border-teal transition-colors duration-200"
                                    >
                                        <x-lucide-check class="w-3.5 h-3.5 text-white" />
                                    </span>

                                    <span class="text-xs text-navy/60 leading-relaxed">
                                        I have read and agree to ShopHop's
                                        <a href="#" target="_blank" class="font-semibold text-teal-dark hover:text-navy transition" onclick="event.stopPropagation()">Terms and Conditions</a>
                                        and
                                        <a href="#" target="_blank" class="font-semibold text-teal-dark hover:text-navy transition" onclick="event.stopPropagation()">Privacy Policy</a>.
                                        <span class="text-red-500">*</span>
                                    </span>

                                </label>

                                <p id="buyer_terms_error" class="hidden text-[11px] text-red-500 mt-1 ml-8"></p>

                            </div>


                            <div class="flex items-center gap-3 mt-5">

                                <button
                                    type="button"
                                    id="buyer-step4-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 bg-white text-navy text-sm font-semibold min-h-12 px-5 sm:px-6 py-3 rounded-2xl hover:bg-gray-bg hover:border-navy/15 focus:outline-none focus:ring-4 focus:ring-navy/5 transition-all duration-200"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-2xl shadow-md shadow-teal/20 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-teal/20 focus:outline-none focus:ring-4 focus:ring-teal/15 transition-all duration-200"
                                >
                                    Create Account
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>

                        </div> {{-- /#buyer-step-viewport --}}


                        {{-- Sign in --}}
                        <div class="text-center mt-5 pt-5 border-t border-gray-border/60">

                            <p class="text-xs text-navy/40">
                                Already have an account?

                                <button
                                    type="button"
                                    data-buyer-registration-modal-signin
                                    class="font-bold text-teal-dark hover:text-navy transition"
                                >
                                    Sign In
                                </button>
                            </p>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    BUYER VALID ID FILE PREVIEW
========================================================= --}}
<div
    id="buyer-id-preview-modal"
    class="fixed inset-0 z-140 hidden items-center justify-center p-4 sm:p-6"
    aria-hidden="true"
>
    <button
        type="button"
        data-buyer-id-preview-close
        aria-label="Close uploaded ID preview"
        class="absolute inset-0 h-full w-full bg-navy/80 backdrop-blur-sm"
    ></button>

    <div class="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-white/15 bg-white shadow-2xl shadow-navy/30">
        <div class="flex items-center justify-between gap-4 border-b border-gray-border/70 px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-teal-dark">Valid ID preview</p>
                <p id="buyer-id-preview-file-name" class="mt-0.5 truncate text-sm font-semibold text-navy">Uploaded file</p>
            </div>

            <button
                type="button"
                data-buyer-id-preview-close
                aria-label="Close preview"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-bg text-navy/45 hover:bg-teal-light hover:text-teal-dark focus:outline-none focus:ring-4 focus:ring-teal/15 transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="flex min-h-[50vh] flex-1 items-center justify-center overflow-auto bg-gray-bg/80 p-3 sm:p-5">
            <img
                id="buyer-id-preview-full-image"
                src=""
                alt="Full uploaded valid ID preview"
                class="hidden max-h-[75vh] max-w-full rounded-2xl bg-white object-contain shadow-sm"
            >

            <iframe
                id="buyer-id-preview-pdf-frame"
                title="Uploaded valid ID PDF preview"
                src=""
                class="hidden h-[70vh] w-full rounded-2xl border border-gray-border/70 bg-white"
            ></iframe>
        </div>
    </div>
</div>

