{{-- =========================================================
    SELLER REGISTRATION MODAL
    Path: resources/views/auth/modals/seller-registration-modal.blade.php

    Opened from account-type-modal when "Seller" is selected.

    REDESIGN NOTE: This modal now mirrors
    buyer-registration-modal.blade.php's compact split-screen
    layout (30/70 ratio, plain show/hide steps, feature-list
    left panel instead of the old floating-card cluster) so the
    two flows feel like one system. The floating back/close
    buttons and the icon + eyebrow + title header block are
    copied from login-modal.blade.php's "upper part" so all
    three modals now share the same chrome. The left panel
    copy/paddings were tightened and the logo enlarged so the
    panel never needs its own scrollbar.

    IMPORTANT: This file is @include()'d directly into
    layouts/app.blade.php. It must NEVER contain @extends —
    doing so causes layouts/app.blade.php to re-render itself
    from inside its own @include, which recurses forever and
    exhausts PHP's memory limit / execution time.
    UI/UX PASS: improved modal spacing, larger tap targets, softer dialog
    corners/shadow, wider content column, and more consistent field gaps.
========================================================= --}}

{{--
=====================================================================
BACKEND INTEGRATION NOTES — Seller Registration
=====================================================================

ROUTE:
  - Form posts to route('seller.register.store') via POST.
    Register this in web.php, e.g.:
        Route::post('/seller/register', [SellerRegistrationController::class, 'store'])
            ->name('seller.register.store');
  - Sign-in link/button dispatches shophop:open-login-modal
    (shared with buyer/seller/logistics login).

CONTROLLER / VALIDATION FIELDS (all posted as multipart/form-data):
  Personal:
    - first_name*        string
    - last_name*         string
    - middle_initial      string, max 2
    - sex*                enum: Male | Female
    - email*              email, unique:users,email (or sellers table)
    - contact_no*         string, exactly 11 digits, starts with 09
    - birthday*           date, must resolve to age >= 18 (recommend
                           server-side re-check since "age" input is
                           client-computed/read-only)
  Address:
    - province_code* / province_name*
    - municipality_code* / municipality_name*
    - barangay_code* / barangay_name*
    - street_address*     text (house no., street, subdivision, etc.)
    (province/municipality/barangay pulled client-side from the PSGC API:
     https://psgc.gitlab.io/api — codes + names are both submitted so the
     backend does not need to re-hit PSGC to resolve labels.)
  Business:
    - business_name*      string
    - business_category*  string / FK to a categories table — the
                           <select> below is currently hardcoded with
                           placeholder options and MUST be swapped for
                           a real @foreach over $categories passed from
                           the controller (see TODO comment at the
                           <select id="seller_business_category"> below).
  Verification:
    - valid_id*           file, mimes:jpg,jpeg,png,pdf, max 5MB
    - business_permit*    file, mimes:jpg,jpeg,png,pdf, max 5MB
  Security:
    - password*                  min:8, must contain upper/lower/number
                                  (special char recommended, not enforced
                                  server-side unless you want to mirror
                                  the JS checklist)
    - password_confirmation*     confirmed
    - terms*                     required, accepted

STATUS / APPROVAL FLOW:
  - New seller accounts should be created with a pending/unapproved
    status (e.g. sellers.status = 'pending') so they cannot log in to
    the seller portal until an admin approves them.
  - On approval/rejection, notify the registered email — the notice
    on this page tells the user to expect that.

FILE STORAGE:
  - Store valid_id and business_permit uploads outside of `public`
    disk if these are sensitive documents (e.g. storage/app/private or
    an admin-only disk) and reference the stored path in the sellers
    table (e.g. valid_id_path, business_permit_path).
--}}

<div
    id="seller-registration-modal"
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-4 md:p-6
           opacity-0 transition-opacity duration-300 ease-out"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-seller-registration-modal-close
        aria-label="Close seller registration"
        class="absolute inset-0 w-full h-full
               bg-navy/35 backdrop-blur-[2px]
               cursor-default"
    ></button>

    {{-- Dialog --}}
    <div
        id="seller-registration-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="seller-registration-modal-title"
        class="relative z-10
               w-full h-full
               sm:h-auto
               sm:max-h-[calc(100vh-2rem)]
               sm:max-w-5xl lg:max-w-6xl xl:max-w-7xl
               overflow-y-auto
               sm:rounded-3xl
               bg-white
               border-0 sm:border sm:border-gray-border/70
               shadow-2xl shadow-navy/15
               opacity-0 scale-95 translate-y-3
               transition-all duration-300 ease-out"
    >

        {{-- Back — floating, matches login modal's close-button treatment --}}
        <button
            type="button"
            data-seller-registration-modal-back
            aria-label="Back to account type"
            class="absolute top-4 left-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-gray-bg
                   text-navy/45
                   flex items-center justify-center
                   hover:bg-teal-light
                   hover:text-teal-dark
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition"
        >
            <x-lucide-arrow-left class="w-4 h-4" />
        </button>

        {{-- Close — same size/position/classes as login-modal.blade.php --}}
        <button
            type="button"
            data-seller-registration-modal-close
            aria-label="Close seller registration"
            class="absolute top-4 right-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-gray-bg
                   text-navy/45
                   flex items-center justify-center
                   hover:bg-teal-light
                   hover:text-teal-dark
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition"
        >
            <x-lucide-x class="w-4 h-4" />
        </button>

        {{-- Accent — same as login modal --}}
        <div class="hidden sm:block h-1.5 bg-teal"></div>

        <p id="seller-registration-modal-title" class="sr-only">Seller Registration</p>


        {{-- =====================================================
            SPLIT CONTENT — 30 / 70, same ratio as buyer modal
        ====================================================== --}}
        <div class="grid lg:grid-cols-[3fr_7fr]">


            {{-- =================================================
                LEFT ARTISTIC PANEL (30%) — condensed to a feature
                list (no absolutely-positioned card cluster) so it
                never needs its own scrollbar.
            ================================================== --}}
            <div
                class="relative hidden lg:flex
                       overflow-hidden
                       bg-navy
                       px-7 xl:px-10
                       py-8 xl:py-10
                       items-center"
            >

                {{-- Background decorations --}}
                <div
                    class="pointer-events-none absolute
                           -top-20 -left-20
                           w-64 h-64
                           rounded-full
                           bg-teal/15
                           blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute
                           -bottom-24 -right-16
                           w-72 h-72
                           rounded-full
                           bg-teal/[0.06]
                           blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute
                           top-[16%] right-[14%]
                           w-16 h-16
                           rounded-full
                           border border-white/10"
                ></div>

                <div
                    class="pointer-events-none absolute
                           bottom-[14%] left-[12%]
                           w-12 h-12
                           rounded-full
                           border border-teal/30"
                ></div>


                <div class="relative z-10 w-full mx-auto">


                    {{-- Logo — enlarged --}}
                    <div class="flex items-center gap-3">

                        <div class="w-16 h-16 flex items-center justify-center shrink-0">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ShopHop"
                                class="w-16 h-16 object-contain"
                            >
                        </div>

                        <div class="min-w-0">

                            <p class="text-white text-lg font-bold leading-none">
                                ShopHop
                            </p>

                            <p class="text-teal text-[8px] tracking-[0.22em] mt-2">
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Main content --}}
                    <div class="mt-3 xl:mt-4">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   bg-white/[0.06]
                                   border border-white/10
                                   px-3 py-1
                                   text-[10px] font-medium
                                   text-teal"
                        >
                            <x-lucide-store class="w-3 h-3" />

                            GROW YOUR BUSINESS
                        </span>


                        <h1
                            class="mt-4
                                   text-white
                                   text-2xl xl:text-3xl
                                   font-extrabold
                                   leading-[1.1]"
                        >
                            <span class="text-white">Sell more.</span>
                            <span class="block text-teal">
                                Reach further.
                            </span>
                        </h1>


                        <p
                            class="mt-3
                                   text-white/55
                                   text-[13px]
                                   leading-relaxed"
                        >
                            Register your business and start listing
                            products to thousands of active shoppers
                            across ShopHop.
                        </p>

                    </div>


                    {{-- Feature list --}}
                    <div class="mt-7 xl:mt-8 space-y-2.5">

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-package class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">List Products</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Reach more buyers</p>
                            </div>

                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-wallet class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">Fast Payouts</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Get paid on time</p>
                            </div>

                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-shield-check class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">Verified &amp; Trusted</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Admin-approved sellers</p>
                            </div>

                        </div>

                    </div>


                    {{-- Trust stats row --}}
                    <div class="mt-7 xl:mt-8 flex items-center gap-4 xl:gap-5">

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">10K+</p>
                            <p class="text-white/45 text-[9.5px] mt-1">Active Sellers</p>
                        </div>

                        <div class="w-px h-7 bg-white/10"></div>

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">50K+</p>
                            <p class="text-white/45 text-[9.5px] mt-1">Products Listed</p>
                        </div>

                        <div class="w-px h-7 bg-white/10"></div>

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">4.8<span class="text-teal">★</span></p>
                            <p class="text-white/45 text-[9.5px] mt-1">Rating</p>
                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                RIGHT REGISTRATION PANEL (70%)
            ================================================== --}}
            <div
                class="bg-white
                       px-5 sm:px-8 lg:px-10 xl:px-12
                       py-6 sm:py-8 lg:py-10"
            >

                <div id="seller-registration-panel" class="max-w-2xl mx-auto">


                    {{-- Mobile branding --}}
                    <div class="lg:hidden flex items-center gap-3 mb-5">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-10 h-10 object-contain"
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


                    {{-- Header — copied from login-modal.blade.php's
                         icon + eyebrow + title treatment so all three
                         modals share the same "upper part". --}}
                    <div class="mb-7">

                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-teal-light
                                   flex items-center justify-center
                                   text-teal-dark
                                   mb-4"
                        >
                            <x-lucide-store class="w-5 h-5" />
                        </div>

                        <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-2">
                            SELLER REGISTRATION
                        </p>

                        <h2 class="text-navy text-2xl sm:text-3xl font-bold leading-tight">
                            Become a Seller
                        </h2>

                        <p class="text-sm text-navy/50 mt-2 leading-relaxed">
                            Enter your details below to register your business on ShopHop.
                        </p>

                    </div>


                    {{-- =============================================
                        STEP PROGRESS BAR (5 steps)
                    ============================================== --}}
                    <div class="mb-7">

                        <div
                            class="grid items-center"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <div
                                class="step-circle step-circle-active justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold bg-teal border-teal text-white"
                                data-step-circle="1"
                            >
                                <span class="step-number">1</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border" data-step-line="1"></div>

                            <div
                                class="step-circle justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold bg-white border-gray-border text-navy/30"
                                data-step-circle="2"
                            >
                                <span class="step-number">2</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border" data-step-line="2"></div>

                            <div
                                class="step-circle justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold bg-white border-gray-border text-navy/30"
                                data-step-circle="3"
                            >
                                <span class="step-number">3</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border" data-step-line="3"></div>

                            <div
                                class="step-circle justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold bg-white border-gray-border text-navy/30"
                                data-step-circle="4"
                            >
                                <span class="step-number">4</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border" data-step-line="4"></div>

                            <div
                                class="step-circle justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold bg-white border-gray-border text-navy/30"
                                data-step-circle="5"
                            >
                                <span class="step-number">5</span>
                                <x-lucide-check class="step-check hidden w-3 h-3" />
                            </div>

                        </div>

                        <div
                            class="grid mt-2"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <p class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] font-semibold leading-tight text-navy" data-step-label="1">
                                Personal
                            </p>

                            <div></div>

                            <p class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="2">
                                Address
                            </p>

                            <div></div>

                            <p class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="3">
                                Business
                            </p>

                            <div></div>

                            <p class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="4">
                                Verification
                            </p>

                            <div></div>

                            <p class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30" data-step-label="5">
                                Security
                            </p>

                        </div>

                    </div>



                    {{-- Validation Errors --}}
                    @if ($errors->any())

                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">

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
                        action="{{ Route::has('seller.register.store') ? route('seller.register.store') : '#' }}"
                        method="POST"
                        enctype="multipart/form-data"
                        id="seller-register-form"
                    >

                        @csrf
                        <input type="hidden" name="account_type" value="seller">

                        <div id="seller-step-viewport" class="rounded-2xl bg-white">


                        {{-- =========================================
                            STEP 1 — PERSONAL DETAILS
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- First Name --}}
                                <div>

                                    <label for="seller_first_name" class="block text-xs font-semibold text-navy mb-2">
                                        First Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="text"
                                            id="seller_first_name"
                                            name="first_name"
                                            value="{{ old('first_name') }}"
                                            required
                                            autocomplete="given-name"
                                            placeholder="Enter first name"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="seller_first_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Last Name --}}
                                <div>

                                    <label for="seller_last_name" class="block text-xs font-semibold text-navy mb-2">
                                        Last Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="text"
                                            id="seller_last_name"
                                            name="last_name"
                                            value="{{ old('last_name') }}"
                                            required
                                            autocomplete="family-name"
                                            placeholder="Enter last name"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="seller_last_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Middle Initial --}}
                                <div>

                                    <label for="seller_middle_initial" class="block text-xs font-semibold text-navy mb-2">
                                        Middle Initial
                                    </label>

                                    <input
                                        type="text"
                                        id="seller_middle_initial"
                                        name="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="2"
                                        placeholder="e.g. M."
                                        class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                    >

                                    <p id="seller_middle_initial_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Sex --}}
                                <div>

                                    <label for="seller_sex" class="block text-xs font-semibold text-navy mb-2">
                                        Sex
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-users class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_sex"
                                            name="sex"
                                            required
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
                                        >
                                            <option value="">Select sex</option>
                                            <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                            <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <p id="seller_sex_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Email --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_email" class="block text-xs font-semibold text-navy mb-2">
                                        E-mail
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-mail class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="email"
                                            id="seller_email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autocomplete="email"
                                            placeholder="your@email.com"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="seller_email_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Contact --}}
                                <div>

                                    <label for="seller_contact_no" class="block text-xs font-semibold text-navy mb-2">
                                        Contact No.
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-phone class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="tel"
                                            id="seller_contact_no"
                                            name="contact_no"
                                            value="{{ old('contact_no') }}"
                                            required
                                            inputmode="numeric"
                                            maxlength="11"
                                            placeholder="09XXXXXXXXX"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="seller_contact_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Birthday + Age --}}
                                <div>

                                    <label for="seller_birthday" class="block text-xs font-semibold text-navy mb-2">
                                        Birthday
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="flex gap-2">

                                        <div class="relative flex-1">

                                            <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                            <input
                                                type="date"
                                                id="seller_birthday"
                                                name="birthday"
                                                value="{{ old('birthday') }}"
                                                max="{{ now()->format('Y-m-d') }}"
                                                required
                                                class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-2 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                            >

                                        </div>

                                        <input
                                            type="number"
                                            id="seller_age"
                                            value="{{ old('age') }}"
                                            readonly
                                            aria-label="Age (auto-generated)"
                                            placeholder="Age"
                                            class="w-16 shrink-0 min-h-12 rounded-xl border border-gray-border/70 bg-gray-bg px-2 text-center text-sm text-navy outline-none"
                                        >

                                    </div>

                                    <p id="seller_birthday_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-map-pin class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Address</p>
                            </div>


                            <div id="seller-address-status" class="hidden mb-4 rounded-xl bg-teal-light/50 px-3.5 py-2 text-xs text-teal-dark">
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- Province --}}
                                <div>

                                    <label for="seller_province" class="block text-xs font-semibold text-navy mb-2">
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-map-pin class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_province"
                                            name="province_code"
                                            required
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
                                        >
                                            <option value="">Select province</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="seller_province_name" name="province_name" value="{{ old('province_name') }}">

                                    <p id="seller_province_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- City / Municipality --}}
                                <div>

                                    <label for="seller_municipality" class="block text-xs font-semibold text-navy mb-2">
                                        Municipality / City
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-building-2 class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_municipality"
                                            name="municipality_code"
                                            required
                                            disabled
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none"
                                        >
                                            <option value="">Select municipality / city</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="seller_municipality_name" name="municipality_name" value="{{ old('municipality_name') }}">

                                    <p id="seller_municipality_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Barangay --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_barangay" class="block text-xs font-semibold text-navy mb-2">
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-home class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_barangay"
                                            name="barangay_code"
                                            required
                                            disabled
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none"
                                        >
                                            <option value="">Select barangay</option>
                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <input type="hidden" id="seller_barangay_name" name="barangay_name" value="{{ old('barangay_name') }}">

                                    <p id="seller_barangay_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Street --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_street_address" class="block text-xs font-semibold text-navy mb-2">
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="seller_street_address"
                                        name="street_address"
                                        rows="2"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full resize-none rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                    >{{ old('street_address') }}</textarea>

                                    <p id="seller_street_address_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step2-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step2-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 3 — BUSINESS DETAILS
                        ========================================== --}}
                        <div data-step-panel="3" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-store class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Business Details</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">

                                {{-- Business Name --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_business_name" class="block text-xs font-semibold text-navy mb-2">
                                        Business Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-store class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="text"
                                            id="seller_business_name"
                                            name="business_name"
                                            value="{{ old('business_name') }}"
                                            required
                                            placeholder="Enter your registered business name"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                    </div>

                                    <p id="seller_business_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Line of Business / Category --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_business_category" class="block text-xs font-semibold text-navy mb-2">
                                        Line of Business (Category)
                                        <span class="text-red-500">*</span>
                                    </label>

                                    {{--
                                        TODO (backend): Replace the hardcoded
                                        <option> list below with a real
                                        @foreach($categories as $category) loop
                                        once a categories table / controller
                                        variable is available. Keep the
                                        old('business_category') selection
                                        logic when you do.
                                    --}}
                                    <div class="relative">

                                        <x-lucide-tag class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_business_category"
                                            name="business_category"
                                            required
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
                                        >
                                            <option value="">Select line of business</option>

                                            @php
                                                $placeholderCategories = [
                                                    'Fashion & Apparel',
                                                    'Electronics & Gadgets',
                                                    'Health & Beauty',
                                                    'Home & Living',
                                                    'Groceries & Food',
                                                    'Toys, Kids & Baby',
                                                    'Sports & Outdoors',
                                                    'Automotive',
                                                    'Books, Hobbies & Stationery',
                                                    'Pet Supplies',
                                                    'Other',
                                                ];
                                            @endphp

                                            @foreach ($placeholderCategories as $category)

                                                <option value="{{ $category }}" @selected(old('business_category') === $category)>
                                                    {{ $category }}
                                                </option>

                                            @endforeach

                                        </select>

                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                    </div>

                                    <p id="seller_business_category_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step3-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step3-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 4 — VERIFICATION (VALID ID + BUSINESS PERMIT)
                        ========================================== --}}
                        <div data-step-panel="4" class="hidden">

                            {{-- Valid ID --}}
                            <label for="seller_valid_id" class="block text-xs font-semibold text-navy mb-2">
                                Upload Valid ID
                                <span class="text-red-500">*</span>
                            </label>

                            <label
                                for="seller_valid_id"
                                class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition"
                            >

                                <div class="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-teal-dark shadow-sm">
                                    <x-lucide-upload class="w-5 h-5" />
                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-semibold text-navy">
                                        Choose a valid ID
                                    </p>

                                    <p class="text-[11px] text-navy/40 mt-1">
                                        JPG, JPEG, PNG or PDF · Max 5MB
                                    </p>

                                    <p id="seller-file-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>

                                </div>


                                <input
                                    type="file"
                                    id="seller_valid_id"
                                    name="valid_id"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="hidden"
                                >

                            </label>

                            <p id="seller_valid_id_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                            {{-- Uploaded valid ID preview --}}
                            <div
                                id="seller-valid-id-preview-card"
                                class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                        <img
                                            id="seller-valid-id-preview-image"
                                            src=""
                                            alt="Uploaded valid ID preview"
                                            class="hidden h-full w-full object-contain"
                                        >
                                        <div id="seller-valid-id-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                            <x-lucide-file-text class="w-6 h-6" />
                                            <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                        <p id="seller-valid-id-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <button
                                                type="button"
                                                id="seller-valid-id-preview-view"
                                                class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                            >
                                                <x-lucide-maximize-2 class="w-3 h-3" />
                                                View file
                                            </button>

                                            <button
                                                type="button"
                                                id="seller-valid-id-preview-change"
                                                class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                            >
                                                Choose another
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            {{-- Business Permit --}}
                            <label for="seller_business_permit" class="block text-xs font-semibold text-navy mb-2 mt-5">
                                Upload Business Permit
                                <span class="text-red-500">*</span>
                            </label>

                            <label
                                for="seller_business_permit"
                                class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition"
                            >

                                <div class="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-teal-dark shadow-sm">
                                    <x-lucide-file-text class="w-5 h-5" />
                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-semibold text-navy">
                                        Choose your business permit
                                    </p>

                                    <p class="text-[11px] text-navy/40 mt-1">
                                        JPG, JPEG, PNG or PDF · Max 5MB
                                    </p>

                                    <p id="seller-business-permit-file-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>

                                </div>


                                <input
                                    type="file"
                                    id="seller_business_permit"
                                    name="business_permit"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="hidden"
                                >

                            </label>

                            <p id="seller_business_permit_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                            {{-- Uploaded business permit preview --}}
                            <div
                                id="seller-business-permit-preview-card"
                                class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                        <img
                                            id="seller-business-permit-preview-image"
                                            src=""
                                            alt="Uploaded business permit preview"
                                            class="hidden h-full w-full object-contain"
                                        >
                                        <div id="seller-business-permit-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                            <x-lucide-file-text class="w-6 h-6" />
                                            <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                        <p id="seller-business-permit-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <button
                                                type="button"
                                                id="seller-business-permit-preview-view"
                                                class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                            >
                                                <x-lucide-maximize-2 class="w-3 h-3" />
                                                View file
                                            </button>

                                            <button
                                                type="button"
                                                id="seller-business-permit-preview-change"
                                                class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                            >
                                                Choose another
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step4-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step4-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =========================================
                            STEP 5 — SECURITY
                        ========================================== --}}
                        <div data-step-panel="5" class="hidden">

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label for="seller_password" class="block text-xs font-semibold text-navy mb-2">
                                        Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="password"
                                            id="seller_password"
                                            name="password"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Minimum 8 characters"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-11 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                        <button
                                            type="button"
                                            id="seller_toggle_password"
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

                                    <p id="seller_password_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                <div>

                                    <label for="seller_password_confirmation" class="block text-xs font-semibold text-navy mb-2">
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <input
                                            type="password"
                                            id="seller_password_confirmation"
                                            name="password_confirmation"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Re-enter password"
                                            class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-11 py-3 text-sm text-navy outline-none placeholder:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition"
                                        >

                                        <button
                                            type="button"
                                            id="seller_toggle_password_confirmation"
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

                                    <p id="seller_password_confirmation_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>

                            {{-- Password requirements checklist --}}
                            <div
                                id="seller-password-requirements"
                                class="mt-4 rounded-xl border border-gray-border/70 bg-gray-bg p-3 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5"
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
                            <div class="mt-4 flex gap-3 rounded-xl border border-teal/15 bg-teal-light/35 p-3">

                                <x-lucide-info class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />

                                <p class="text-[11px] text-navy/50 leading-relaxed">
                                    After submitting your registration, please wait for the administrator's approval. Your approval status will be sent to your registered email.
                                </p>

                            </div>

                            {{-- Terms & Agreement --}}
                            <div class="mt-4">

                                <label for="seller_terms" class="flex items-start gap-3 cursor-pointer group">

                                    <input type="checkbox" id="seller_terms" name="terms" required class="peer sr-only">

                                    <span
                                        class="mt-0.5 shrink-0 w-5 h-5 rounded-md border-2 border-gray-border bg-white flex items-center justify-center peer-checked:bg-teal peer-checked:border-teal group-hover:border-teal transition-colors duration-200"
                                    >
                                        <x-lucide-check class="w-3.5 h-3.5 text-white" />
                                    </span>

                                    <span class="text-xs text-navy/60 leading-relaxed">
                                        I have read and agree to ShopHop's
                                        <a href="#" target="_blank" class="font-semibold text-teal-dark hover:text-navy transition" onclick="event.stopPropagation()">Seller Terms and Conditions</a>
                                        and
                                        <a href="#" target="_blank" class="font-semibold text-teal-dark hover:text-navy transition" onclick="event.stopPropagation()">Privacy Policy</a>.
                                        <span class="text-red-500">*</span>
                                    </span>

                                </label>

                                <p id="seller_terms_error" class="hidden text-[11px] text-red-500 mt-1 ml-8"></p>

                            </div>


                            <div class="flex items-center gap-3 mt-5">

                                <button
                                    type="button"
                                    id="seller-step5-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Create Seller Account
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>

                        </div> {{-- /#seller-step-viewport --}}


                        {{-- Sign in --}}
                        <div class="text-center mt-6">

                            <p class="text-xs text-navy/40">
                                Already have a seller account?

                                <button
                                    type="button"
                                    data-seller-registration-modal-signin
                                    class="font-semibold text-teal-dark hover:text-navy transition"
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
    SELLER UPLOADED FILE PREVIEW
========================================================= --}}
<div
    id="seller-file-preview-modal"
    class="fixed inset-0 z-[140] hidden items-center justify-center p-4 sm:p-6"
    aria-hidden="true"
>
    <button
        type="button"
        data-seller-file-preview-close
        aria-label="Close uploaded file preview"
        class="absolute inset-0 h-full w-full bg-navy/80 backdrop-blur-sm"
    ></button>

    <div class="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-white/15 bg-white shadow-2xl shadow-navy/30">
        <div class="flex items-center justify-between gap-4 border-b border-gray-border/70 px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-teal-dark">Uploaded file preview</p>
                <p id="seller-file-preview-name" class="mt-0.5 truncate text-sm font-semibold text-navy">Uploaded file</p>
            </div>

            <button
                type="button"
                data-seller-file-preview-close
                aria-label="Close preview"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-bg text-navy/45 hover:bg-teal-light hover:text-teal-dark focus:outline-none focus:ring-4 focus:ring-teal/15 transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="flex min-h-[50vh] flex-1 items-center justify-center overflow-auto bg-gray-bg/80 p-3 sm:p-5">
            <img
                id="seller-file-preview-full-image"
                src=""
                alt="Full uploaded file preview"
                class="hidden max-h-[75vh] max-w-full rounded-2xl bg-white object-contain shadow-sm"
            >

            <iframe
                id="seller-file-preview-pdf-frame"
                title="Uploaded PDF preview"
                src=""
                class="hidden h-[70vh] w-full rounded-2xl border border-gray-border/70 bg-white"
            ></iframe>
        </div>
    </div>
</div>

