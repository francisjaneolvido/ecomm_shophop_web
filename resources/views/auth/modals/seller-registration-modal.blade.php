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
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-6
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
               sm:max-h-[calc(100vh-3rem)]
               sm:max-w-4xl lg:max-w-5xl xl:max-w-6xl
               overflow-y-auto
               sm:rounded-2xl
               bg-white
               border-0 sm:border sm:border-gray-border/70
               shadow-xl shadow-navy/10
               opacity-0 scale-95 translate-y-3
               transition-all duration-300 ease-out"
    >

        {{-- Back — floating, matches login modal's close-button treatment --}}
        <button
            type="button"
            data-seller-registration-modal-back
            aria-label="Back to account type"
            class="absolute top-4 left-4 z-20
                   w-10 h-10
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
                   w-10 h-10
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
                       px-6 xl:px-8
                       py-10
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
                       px-4 sm:px-8 xl:px-10
                       py-6 sm:py-8"
            >

                <div id="seller-registration-panel" class="max-w-xl mx-auto">


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
                    <div class="mb-6">

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

                        <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-1.5">
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
                    <div class="mb-6">

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

                        <div id="seller-step-viewport">


                        {{-- =========================================
                            STEP 1 — PERSONAL DETAILS
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="grid sm:grid-cols-2 gap-3.5">


                                {{-- First Name --}}
                                <div>

                                    <label for="seller_first_name" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                    </div>

                                    <p id="seller_first_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Last Name --}}
                                <div>

                                    <label for="seller_last_name" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                    </div>

                                    <p id="seller_last_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Middle Initial --}}
                                <div>

                                    <label for="seller_middle_initial" class="block text-xs font-semibold text-navy mb-1.5">
                                        Middle Initial
                                    </label>

                                    <input
                                        type="text"
                                        id="seller_middle_initial"
                                        name="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="2"
                                        placeholder="e.g. M."
                                        class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="seller_middle_initial_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Sex --}}
                                <div>

                                    <label for="seller_sex" class="block text-xs font-semibold text-navy mb-1.5">
                                        Sex
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-users class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_sex"
                                            name="sex"
                                            required
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-9 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
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

                                    <label for="seller_email" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                    </div>

                                    <p id="seller_email_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Contact --}}
                                <div>

                                    <label for="seller_contact_no" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                    </div>

                                    <p id="seller_contact_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Birthday + Age --}}
                                <div>

                                    <label for="seller_birthday" class="block text-xs font-semibold text-navy mb-1.5">
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
                                                class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-2 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                            >

                                        </div>

                                        <input
                                            type="number"
                                            id="seller_age"
                                            value="{{ old('age') }}"
                                            readonly
                                            aria-label="Age (auto-generated)"
                                            placeholder="Age"
                                            class="w-16 shrink-0 min-h-11 rounded-xl border border-gray-border/70 bg-gray-bg px-2 text-center text-sm text-navy outline-none"
                                        >

                                    </div>

                                    <p id="seller_birthday_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-map-pin class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Address</p>
                            </div>


                            <div id="seller-address-status" class="hidden mb-3.5 rounded-xl bg-teal-light/50 px-3.5 py-2 text-xs text-teal-dark">
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-3.5">


                                {{-- Province --}}
                                <div>

                                    <label for="seller_province" class="block text-xs font-semibold text-navy mb-1.5">
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <x-lucide-map-pin class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />

                                        <select
                                            id="seller_province"
                                            name="province_code"
                                            required
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-9 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
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

                                    <label for="seller_municipality" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
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

                                    <label for="seller_barangay" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
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

                                    <label for="seller_street_address" class="block text-xs font-semibold text-navy mb-1.5">
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="seller_street_address"
                                        name="street_address"
                                        rows="2"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full resize-none rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >{{ old('street_address') }}</textarea>

                                    <p id="seller_street_address_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step2-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step2-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-store class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Business Details</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3.5">

                                {{-- Business Name --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_business_name" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                    </div>

                                    <p id="seller_business_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                </div>


                                {{-- Line of Business / Category --}}
                                <div class="sm:col-span-2">

                                    <label for="seller_business_category" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-9 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none"
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
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step3-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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
                            <label for="seller_valid_id" class="block text-xs font-semibold text-navy mb-1.5">
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


                            {{-- Business Permit --}}
                            <label for="seller_business_permit" class="block text-xs font-semibold text-navy mb-1.5 mt-5">
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


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="seller-step4-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="seller-step4-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="grid sm:grid-cols-2 gap-3.5">

                                <div>

                                    <label for="seller_password" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-11 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
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

                                    <label for="seller_password_confirmation" class="block text-xs font-semibold text-navy mb-1.5">
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
                                            class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-11 py-2.5 text-sm text-navy outline-none placeholder:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
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
                                class="mt-3.5 rounded-xl border border-gray-border/70 bg-gray-bg p-3 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5"
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
                            <div class="mt-3.5 flex gap-3 rounded-xl border border-teal/15 bg-teal-light/35 p-3">

                                <x-lucide-info class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />

                                <p class="text-[11px] text-navy/50 leading-relaxed">
                                    After submitting your registration, please wait for the administrator's approval. Your approval status will be sent to your registered email.
                                </p>

                            </div>

                            {{-- Terms & Agreement --}}
                            <div class="mt-3.5">

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
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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


@push('styles')
<style>
    /* Hide Chrome's built-in "strong password suggestion" and
       "saved credentials" icons inside password fields so only
       our custom show/hide eye button appears. */
    #seller-registration-modal input[type="password"]::-webkit-strong-password-auto-fill-button,
    #seller-registration-modal input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Hide Edge's built-in reveal-password icon for the same reason. */
    #seller-registration-modal input[type="password"]::-ms-reveal,
    #seller-registration-modal input[type="password"]::-ms-clear {
        display: none !important;
    }

    #seller-registration-modal .step-circle {
        transition:
            background-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.35s ease;
    }

    #seller-registration-modal .step-circle.step-circle-active {
        transform: scale(1.08);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.15);
    }

    #seller-registration-modal .step-check {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #seller-registration-modal .step-line {
        transition: background-color 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #seller-registration-modal .step-label {
        transition: color 0.3s ease, font-weight 0.3s ease;
    }

    #seller-registration-modal #seller-register-form button[type="button"],
    #seller-registration-modal #seller-register-form button[type="submit"] {
        transition:
            transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.25s ease,
            background-color 0.25s ease;
    }

    #seller-registration-modal #seller-register-form button[type="button"]:active,
    #seller-registration-modal #seller-register-form button[type="submit"]:active {
        transform: scale(0.97);
    }

    #seller-registration-modal .req-dot {
        transition:
            background-color 0.25s ease,
            border-color 0.25s ease,
            transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #seller-registration-modal .req-item.req-satisfied .req-dot {
        transform: scale(1.12);
    }

    /* Hide the dialog's scrollbar visually but keep it functional
   as a safety net for very short viewports. */
    #seller-registration-dialog {
        scrollbar-width: none;      /* Firefox */
        -ms-overflow-style: none;   /* old Edge/IE */
    }

    #seller-registration-dialog::-webkit-scrollbar {
        display: none;              /* Chrome / Safari / new Edge */
    }
</style>
@endpush


@push('scripts')
<script>
    (function () {

        const modal = document.getElementById('seller-registration-modal');

        if (!modal) {
            return;
        }

        const dialog = document.getElementById('seller-registration-dialog');

        let lastFocusedElement = null;
        let provincesLoaded = false;
        let closeTimeoutId = null;


        /*
        |--------------------------------------------------------------------------
        | MODAL OPEN / CLOSE — fade + scale transition
        |--------------------------------------------------------------------------
        */

        function openSellerRegistrationModal() {

            lastFocusedElement = document.activeElement;

            if (closeTimeoutId) {
                window.clearTimeout(closeTimeoutId);
                closeTimeoutId = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {

                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');

                    if (dialog) {
                        dialog.classList.remove('opacity-0', 'scale-95', 'translate-y-3');
                        dialog.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                    }

                });
            });

            

            if (!provincesLoaded) {
                provincesLoaded = true;
                loadProvinces();
            }

            window.setTimeout(function () {
                const firstField = document.getElementById('seller_first_name');
                if (firstField) firstField.focus();
            }, 50);

        }

        function closeSellerRegistrationModal() {

            modal.classList.add('opacity-0');
            modal.classList.remove('opacity-100');

            if (dialog) {
                dialog.classList.add('opacity-0', 'scale-95', 'translate-y-3');
                dialog.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            }

            document.body.classList.remove('overflow-hidden');

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }

            closeTimeoutId = window.setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                closeTimeoutId = null;
            }, 300);

        }

        // Opened when account-type-modal (or anything else) dispatches this event.
        document.addEventListener('shophop:open-registration-modal', function (event) {
            if (event.detail && event.detail.type === 'seller') {
                openSellerRegistrationModal();
            }
        });

        document.addEventListener('click', function (event) {

            const backTrigger = event.target.closest('[data-seller-registration-modal-back]');

            if (backTrigger && modal.contains(backTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-account-type-modal'));
                return;
            }

            const signInTrigger = event.target.closest('[data-seller-registration-modal-signin]');

            if (signInTrigger && modal.contains(signInTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
                return;
            }

            const closeTrigger = event.target.closest('[data-seller-registration-modal-close]');

            if (closeTrigger && modal.contains(closeTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
            }

        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeSellerRegistrationModal();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | FIELD REFERENCES
        |--------------------------------------------------------------------------
        */

        const birthdayInput = document.getElementById('seller_birthday');
        const ageInput = document.getElementById('seller_age');

        const provinceSelect = document.getElementById('seller_province');
        const municipalitySelect = document.getElementById('seller_municipality');
        const barangaySelect = document.getElementById('seller_barangay');

        const provinceNameInput = document.getElementById('seller_province_name');
        const municipalityNameInput = document.getElementById('seller_municipality_name');
        const barangayNameInput = document.getElementById('seller_barangay_name');

        const addressStatus = document.getElementById('seller-address-status');

        const validIdInput = document.getElementById('seller_valid_id');
        const fileName = document.getElementById('seller-file-name');
        const validIdError = document.getElementById('seller_valid_id_error');

        const businessPermitInput = document.getElementById('seller_business_permit');
        const businessPermitFileName = document.getElementById('seller-business-permit-file-name');
        const businessPermitError = document.getElementById('seller_business_permit_error');

        const registerForm = document.getElementById('seller-register-form');


        /*
        |--------------------------------------------------------------------------
        | AGE
        |--------------------------------------------------------------------------
        */

        function calculateAge() {

            if (!birthdayInput.value) {
                ageInput.value = '';
                return;
            }

            const birthday = new Date(birthdayInput.value + 'T00:00:00');
            const today = new Date();

            let age = today.getFullYear() - birthday.getFullYear();
            const monthDifference = today.getMonth() - birthday.getMonth();

            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthday.getDate())) {
                age--;
            }

            ageInput.value = age >= 0 ? age : '';

        }

        birthdayInput.addEventListener('change', calculateAge);
        calculateAge();


        /*
        |--------------------------------------------------------------------------
        | FILE PICKERS (shared validator for valid ID + business permit)
        |--------------------------------------------------------------------------
        */

        function setupFilePicker(inputEl, nameEl, errorEl) {

            inputEl.addEventListener('change', function () {

                if (this.files.length > 0) {

                    const file = this.files[0];

                    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    const maxSizeBytes = 5 * 1024 * 1024;

                    if (!allowedTypes.includes(file.type)) {
                        showError(inputEl, errorEl, 'Only JPG, JPEG, PNG or PDF files are allowed.');
                        nameEl.textContent = '';
                        nameEl.classList.add('hidden');
                        this.value = '';
                        return;
                    }

                    if (file.size > maxSizeBytes) {
                        showError(inputEl, errorEl, 'File is too large. Maximum size is 5MB.');
                        nameEl.textContent = '';
                        nameEl.classList.add('hidden');
                        this.value = '';
                        return;
                    }

                    showError(inputEl, errorEl, '');
                    nameEl.textContent = file.name;
                    nameEl.classList.remove('hidden');

                } else {
                    nameEl.textContent = '';
                    nameEl.classList.add('hidden');
                }

            });

        }

        setupFilePicker(validIdInput, fileName, validIdError);
        setupFilePicker(businessPermitInput, businessPermitFileName, businessPermitError);


        /*
        |--------------------------------------------------------------------------
        | REAL-TIME FIELD VALIDATION
        |--------------------------------------------------------------------------
        */

        const nameRegex = /^[A-Za-zÀ-ÿñÑ\s'.-]*$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const contactRegex = /^09\d{9}$/;

        const passwordUppercaseRegex = /[A-Z]/;
        const passwordLowercaseRegex = /[a-z]/;
        const passwordNumberRegex = /[0-9]/;
        const passwordSpecialRegex = /[!@#$%^&*]/;

        function showError(input, errorEl, message) {

            if (message) {

                errorEl.textContent = message;
                errorEl.classList.remove('hidden');

                input.classList.add('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                input.classList.remove('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');

            } else {

                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                input.classList.remove('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                input.classList.add('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');

            }

        }

        function validateNameField(input, errorEl, label) {

            const value = input.value;

            if (!value && input.required) {
                showError(input, errorEl, `${label} is required.`);
                return false;
            }

            if (value && !nameRegex.test(value)) {
                showError(input, errorEl, `${label} should not contain numbers or special characters.`);
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateEmailField(input, errorEl) {

            const value = input.value.trim();

            if (!value && input.required) {
                showError(input, errorEl, 'Email is required.');
                return false;
            }

            if (value && !emailRegex.test(value)) {
                showError(input, errorEl, 'Please enter a valid email address (e.g. juan@example.com).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateContactField(input, errorEl) {

            const value = input.value.trim();

            if (!value && input.required) {
                showError(input, errorEl, 'Contact number is required.');
                return false;
            }

            if (value && !contactRegex.test(value)) {
                showError(input, errorEl, 'Enter a valid 11-digit number starting with 09 (e.g. 09171234567).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateRequiredField(input, errorEl, label) {

            const value = input.value.trim();

            if (!value) {
                showError(input, errorEl, `${label} is required.`);
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function updatePasswordRequirements(value) {

            const checks = {
                length: value.length >= 8,
                uppercase: passwordUppercaseRegex.test(value),
                lowercase: passwordLowercaseRegex.test(value),
                number: passwordNumberRegex.test(value),
                special: passwordSpecialRegex.test(value),
            };

            Object.keys(checks).forEach(function (key) {

                const item = modal.querySelector(`[data-req="${key}"]`);

                if (!item) return;

                const dot = item.querySelector('.req-dot');
                const check = item.querySelector('.req-check');
                const satisfied = checks[key];

                item.classList.toggle('req-satisfied', satisfied);
                item.classList.toggle('text-teal-dark', satisfied);
                item.classList.toggle('text-navy/40', !satisfied && key !== 'special');
                item.classList.toggle('text-navy/30', !satisfied && key === 'special');

                dot.classList.toggle('bg-teal', satisfied);
                dot.classList.toggle('border-teal', satisfied);
                dot.classList.toggle('border-gray-border', !satisfied);

                check.classList.toggle('hidden', !satisfied);

            });

        }

        function validatePasswordField(input, errorEl) {

            const value = input.value;

            if (!value && input.required) {
                showError(input, errorEl, 'Password is required.');
                return false;
            }

            if (value && value.length < 8) {
                showError(input, errorEl, 'Password must be at least 8 characters long.');
                return false;
            }

            if (value && !passwordUppercaseRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 uppercase letter (A–Z).');
                return false;
            }

            if (value && !passwordLowercaseRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 lowercase letter (a–z).');
                return false;
            }

            if (value && !passwordNumberRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 number (0–9).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validatePasswordConfirmation(passwordInput, confirmInput, errorEl) {

            const value = confirmInput.value;

            if (!value && confirmInput.required) {
                showError(confirmInput, errorEl, 'Please confirm your password.');
                return false;
            }

            if (value && value !== passwordInput.value) {
                showError(confirmInput, errorEl, 'Passwords do not match.');
                return false;
            }

            showError(confirmInput, errorEl, '');
            return true;

        }


        const firstNameInput = document.getElementById('seller_first_name');
        const firstNameError = document.getElementById('seller_first_name_error');

        firstNameInput.addEventListener('input', function () {
            validateNameField(firstNameInput, firstNameError, 'First name');
        });


        const lastNameInput = document.getElementById('seller_last_name');
        const lastNameError = document.getElementById('seller_last_name_error');

        lastNameInput.addEventListener('input', function () {
            validateNameField(lastNameInput, lastNameError, 'Last name');
        });


        const middleInitialInput = document.getElementById('seller_middle_initial');
        const middleInitialError = document.getElementById('seller_middle_initial_error');

        middleInitialInput.addEventListener('input', function () {
            validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
        });


        const emailInput = document.getElementById('seller_email');
        const emailError = document.getElementById('seller_email_error');

        emailInput.addEventListener('input', function () {
            validateEmailField(emailInput, emailError);
        });

        emailInput.addEventListener('blur', function () {
            validateEmailField(emailInput, emailError);
        });


        const contactInput = document.getElementById('seller_contact_no');
        const contactError = document.getElementById('seller_contact_no_error');

        contactInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            validateContactField(contactInput, contactError);
        });


        const sexInput = document.getElementById('seller_sex');
        const sexError = document.getElementById('seller_sex_error');

        sexInput.addEventListener('change', function () {
            validateRequiredField(sexInput, sexError, 'Sex');
        });


        const birthdayError = document.getElementById('seller_birthday_error');

        birthdayInput.addEventListener('change', function () {
            validateRequiredField(birthdayInput, birthdayError, 'Birthday');
        });


        const provinceError = document.getElementById('seller_province_error');
        const municipalityError = document.getElementById('seller_municipality_error');
        const barangayError = document.getElementById('seller_barangay_error');

        provinceSelect.addEventListener('change', function () {
            validateRequiredField(provinceSelect, provinceError, 'Province');
        });

        municipalitySelect.addEventListener('change', function () {
            validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
        });

        barangaySelect.addEventListener('change', function () {
            validateRequiredField(barangaySelect, barangayError, 'Barangay');
        });


        const streetAddressInput = document.getElementById('seller_street_address');
        const streetAddressError = document.getElementById('seller_street_address_error');

        streetAddressInput.addEventListener('input', function () {
            validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
        });


        const businessNameInput = document.getElementById('seller_business_name');
        const businessNameError = document.getElementById('seller_business_name_error');

        businessNameInput.addEventListener('input', function () {
            validateRequiredField(businessNameInput, businessNameError, 'Business name');
        });


        const businessCategorySelect = document.getElementById('seller_business_category');
        const businessCategoryError = document.getElementById('seller_business_category_error');

        businessCategorySelect.addEventListener('change', function () {
            validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');
        });


        const passwordInput = document.getElementById('seller_password');
        const passwordError = document.getElementById('seller_password_error');

        const passwordConfirmInput = document.getElementById('seller_password_confirmation');
        const passwordConfirmError = document.getElementById('seller_password_confirmation_error');

        passwordInput.addEventListener('input', function () {

            updatePasswordRequirements(passwordInput.value);
            validatePasswordField(passwordInput, passwordError);

            if (passwordConfirmInput.value) {
                validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
            }

        });

        passwordConfirmInput.addEventListener('input', function () {
            validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
        });


        /*
        |--------------------------------------------------------------------------
        | SHOW / HIDE PASSWORD
        |--------------------------------------------------------------------------
        */

        function setupPasswordToggle(inputEl, buttonEl) {

            const showIcon = buttonEl.querySelector('.password-icon-show');
            const hideIcon = buttonEl.querySelector('.password-icon-hide');

            buttonEl.addEventListener('click', function () {

                const isHidden = inputEl.type === 'password';

                inputEl.type = isHidden ? 'text' : 'password';

                showIcon.style.display = isHidden ? 'none' : '';
                hideIcon.style.display = isHidden ? '' : 'none';

                buttonEl.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                buttonEl.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

                inputEl.focus();

                const caretPosition = inputEl.value.length;
                inputEl.setSelectionRange(caretPosition, caretPosition);

            });

        }

        setupPasswordToggle(passwordInput, document.getElementById('seller_toggle_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('seller_toggle_password_confirmation'));


        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW (5 steps) — plain show/hide, same as buyer modal
        |--------------------------------------------------------------------------
        */

        function getPanel(step) {
            return modal.querySelector(`[data-step-panel="${step}"]`);
        }

        function updateProgressBar(activeStep) {

            modal.querySelectorAll('[data-step-circle]').forEach(function (circle) {

                const s = parseInt(circle.dataset.stepCircle, 10);
                const numberEl = circle.querySelector('.step-number');
                const checkEl = circle.querySelector('.step-check');

                circle.classList.remove(
                    'bg-teal', 'border-teal', 'text-white',
                    'bg-white', 'border-gray-border', 'text-navy/30',
                    'step-circle-active'
                );

                if (s < activeStep) {

                    circle.classList.add('bg-teal', 'border-teal', 'text-white');
                    numberEl.classList.add('hidden');
                    checkEl.classList.remove('hidden');

                } else if (s === activeStep) {

                    circle.classList.add('bg-teal', 'border-teal', 'text-white', 'step-circle-active');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');

                } else {

                    circle.classList.add('bg-white', 'border-gray-border', 'text-navy/30');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');

                }

            });

            modal.querySelectorAll('[data-step-label]').forEach(function (label) {

                const s = parseInt(label.dataset.stepLabel, 10);

                label.classList.toggle('text-navy', s <= activeStep);
                label.classList.toggle('font-semibold', s <= activeStep);
                label.classList.toggle('text-navy/30', s > activeStep);
                label.classList.toggle('font-medium', s > activeStep);

            });

            modal.querySelectorAll('[data-step-line]').forEach(function (line) {

                const s = parseInt(line.dataset.stepLine, 10);

                line.classList.toggle('bg-teal', s < activeStep);
                line.classList.toggle('bg-gray-border', s >= activeStep);

            });

        }

        function goToStep(step) {

            modal.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                const s = parseInt(panel.dataset.stepPanel, 10);
                panel.classList.toggle('hidden', s !== step);
            });

            updateProgressBar(step);

            if (dialog) {
                dialog.scrollTo({ top: 0, behavior: 'smooth' });
            }

        }

        function validateAndReportPanel(panel) {

            const fields = panel.querySelectorAll('input[required], select[required], textarea[required]');

            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            return true;

        }

        function validatePanelSilently(panel) {

            const fields = panel.querySelectorAll('input[required], select[required], textarea[required]');

            let allValid = true;

            fields.forEach(function (field) {
                if (!field.checkValidity()) {
                    allValid = false;
                }
            });

            return allValid;

        }

        function validateFileInput(inputEl, errorEl, label) {

            if (!inputEl.files || inputEl.files.length === 0) {
                showError(inputEl, errorEl, `Please upload your ${label}.`);
                return false;
            }

            showError(inputEl, errorEl, '');
            return true;

        }

        function validateTerms() {

            const termsInput = document.getElementById('seller_terms');
            const termsErrorEl = document.getElementById('seller_terms_error');

            if (!termsInput.checked) {
                showError(termsInput, termsErrorEl, 'You must agree to the Terms and Conditions.');
                return false;
            }

            showError(termsInput, termsErrorEl, '');
            return true;

        }

        function validateStep1() {

            const isFirstNameValid = validateNameField(firstNameInput, firstNameError, 'First name');
            const isLastNameValid = validateNameField(lastNameInput, lastNameError, 'Last name');
            const isMiddleInitialValid = validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
            const isEmailValid = validateEmailField(emailInput, emailError);
            const isContactValid = validateContactField(contactInput, contactError);
            const isSexValid = validateRequiredField(sexInput, sexError, 'Sex');
            const isBirthdayValid = validateRequiredField(birthdayInput, birthdayError, 'Birthday');

            if (!isFirstNameValid || !isLastNameValid || !isMiddleInitialValid ||
                !isEmailValid || !isContactValid || !isSexValid || !isBirthdayValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(1));

        }

        function validateStep2() {

            const isProvinceValid = validateRequiredField(provinceSelect, provinceError, 'Province');
            const isMunicipalityValid = validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
            const isBarangayValid = validateRequiredField(barangaySelect, barangayError, 'Barangay');
            const isStreetAddressValid = validateRequiredField(streetAddressInput, streetAddressError, 'Street address');

            if (!isProvinceValid || !isMunicipalityValid || !isBarangayValid || !isStreetAddressValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(2));

        }

        function validateStep3() {

            const isBusinessNameValid = validateRequiredField(businessNameInput, businessNameError, 'Business name');
            const isBusinessCategoryValid = validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');

            if (!isBusinessNameValid || !isBusinessCategoryValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(3));

        }

        function validateStep4() {

            const isValidIdValid = validateFileInput(validIdInput, validIdError, 'valid ID');
            const isBusinessPermitValid = validateFileInput(businessPermitInput, businessPermitError, 'business permit');

            return isValidIdValid && isBusinessPermitValid;

        }


        document.getElementById('seller-step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('seller-step2-back').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('seller-step2-next').addEventListener('click', function () {
            if (validateStep2()) goToStep(3);
        });

        document.getElementById('seller-step3-back').addEventListener('click', function () {
            goToStep(2);
        });

        document.getElementById('seller-step3-next').addEventListener('click', function () {
            if (validateStep3()) goToStep(4);
        });

        document.getElementById('seller-step4-back').addEventListener('click', function () {
            goToStep(3);
        });

        document.getElementById('seller-step4-next').addEventListener('click', function () {
            if (validateStep4()) goToStep(5);
        });

        document.getElementById('seller-step5-back').addEventListener('click', function () {
            goToStep(4);
        });


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT GUARD
        |--------------------------------------------------------------------------
        */

        registerForm.addEventListener('submit', function (e) {

            const isFirstNameValid = validateNameField(firstNameInput, firstNameError, 'First name');
            const isLastNameValid = validateNameField(lastNameInput, lastNameError, 'Last name');
            const isMiddleInitialValid = validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
            const isEmailValid = validateEmailField(emailInput, emailError);
            const isContactValid = validateContactField(contactInput, contactError);
            const isSexValid = validateRequiredField(sexInput, sexError, 'Sex');
            const isBirthdayValid = validateRequiredField(birthdayInput, birthdayError, 'Birthday');
            const isStep1RequiredValid = validatePanelSilently(getPanel(1));

            const step1Ok = isFirstNameValid && isLastNameValid && isMiddleInitialValid &&
                isEmailValid && isContactValid && isSexValid && isBirthdayValid && isStep1RequiredValid;

            if (!step1Ok) {
                e.preventDefault();
                goToStep(1);
                return;
            }

            const isProvinceValid = validateRequiredField(provinceSelect, provinceError, 'Province');
            const isMunicipalityValid = validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
            const isBarangayValid = validateRequiredField(barangaySelect, barangayError, 'Barangay');
            const isStreetAddressValid = validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
            const isStep2RequiredValid = validatePanelSilently(getPanel(2));

            const step2Ok = isProvinceValid && isMunicipalityValid && isBarangayValid &&
                isStreetAddressValid && isStep2RequiredValid;

            if (!step2Ok) {
                e.preventDefault();
                goToStep(2);
                return;
            }

            const isBusinessNameValid = validateRequiredField(businessNameInput, businessNameError, 'Business name');
            const isBusinessCategoryValid = validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');
            const isStep3RequiredValid = validatePanelSilently(getPanel(3));

            const step3Ok = isBusinessNameValid && isBusinessCategoryValid && isStep3RequiredValid;

            if (!step3Ok) {
                e.preventDefault();
                goToStep(3);
                return;
            }

            const step4Ok = validateStep4();

            if (!step4Ok) {
                e.preventDefault();
                goToStep(4);
                return;
            }

            const isPasswordValid = validatePasswordField(passwordInput, passwordError);
            const isPasswordConfirmValid = validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
            const isTermsValid = validateTerms();

            if (!isPasswordValid || !isPasswordConfirmValid || !isTermsValid) {
                e.preventDefault();
                goToStep(5);
                return;
            }

        });


        /*
        |--------------------------------------------------------------------------
        | PSGC ADDRESS
        |--------------------------------------------------------------------------
        */

        const PSGC_BASE = 'https://psgc.gitlab.io/api';

        const oldProvinceCode = @json(old('province_code'));
        const oldMunicipalityCode = @json(old('municipality_code'));
        const oldBarangayCode = @json(old('barangay_code'));

        function setAddressStatus(message, isError = false) {

            if (!message) {
                addressStatus.classList.add('hidden');
                return;
            }

            addressStatus.textContent = message;
            addressStatus.classList.remove('hidden');
            addressStatus.classList.toggle('text-red-500', isError);
            addressStatus.classList.toggle('text-teal-dark', !isError);

        }

        function resetSelect(select, text) {
            select.innerHTML = `<option value="">${text}</option>`;
            select.disabled = true;
        }

        function fillSelect(select, items, placeholder, selectedCode = null) {

            select.innerHTML = `<option value="">${placeholder}</option>`;

            items.forEach(function (item) {

                const option = document.createElement('option');
                option.value = item.code;
                option.textContent = item.name;

                if (selectedCode && item.code === selectedCode) {
                    option.selected = true;
                }

                select.appendChild(option);

            });

            select.disabled = false;

        }

        async function fetchJson(url) {

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Unable to load address data.');
            }

            return response.json();

        }

        async function loadProvinces() {

            try {

                setAddressStatus('Loading provinces...');

                const provinces = await fetchJson(`${PSGC_BASE}/provinces/`);

                provinces.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(provinceSelect, provinces, 'Select province', oldProvinceCode);

                if (oldProvinceCode) {

                    provinceNameInput.value = provinceSelect.options[provinceSelect.selectedIndex]?.text || '';

                    await loadMunicipalities(oldProvinceCode, oldMunicipalityCode);

                }

                setAddressStatus('');

            } catch (error) {
                setAddressStatus('Unable to load address dropdowns. Please refresh the page.', true);
            }

        }

        async function loadMunicipalities(provinceCode, selectedCode = null) {

            resetSelect(municipalitySelect, 'Loading municipality / city...');
            resetSelect(barangaySelect, 'Select barangay');

            municipalityNameInput.value = '';
            barangayNameInput.value = '';

            if (!provinceCode) {
                resetSelect(municipalitySelect, 'Select municipality / city');
                return;
            }

            try {

                const cities = await fetchJson(`${PSGC_BASE}/provinces/${provinceCode}/cities-municipalities/`);

                cities.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(municipalitySelect, cities, 'Select municipality / city', selectedCode);

                if (selectedCode) {

                    municipalityNameInput.value = municipalitySelect.options[municipalitySelect.selectedIndex]?.text || '';

                    await loadBarangays(selectedCode, oldBarangayCode);

                }

            } catch (error) {
                resetSelect(municipalitySelect, 'Unable to load municipalities');
            }

        }

        async function loadBarangays(municipalityCode, selectedCode = null) {

            resetSelect(barangaySelect, 'Loading barangays...');

            barangayNameInput.value = '';

            if (!municipalityCode) {
                resetSelect(barangaySelect, 'Select barangay');
                return;
            }

            try {

                const barangays = await fetchJson(`${PSGC_BASE}/cities-municipalities/${municipalityCode}/barangays/`);

                barangays.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(barangaySelect, barangays, 'Select barangay', selectedCode);

                if (selectedCode) {
                    barangayNameInput.value = barangaySelect.options[barangaySelect.selectedIndex]?.text || '';
                }

            } catch (error) {
                resetSelect(barangaySelect, 'Unable to load barangays');
            }

        }

        provinceSelect.addEventListener('change', function () {

            provinceNameInput.value = this.options[this.selectedIndex]?.text || '';

            loadMunicipalities(this.value);

        });

        municipalitySelect.addEventListener('change', function () {

            municipalityNameInput.value = this.options[this.selectedIndex]?.text || '';

            loadBarangays(this.value);

        });

        barangaySelect.addEventListener('change', function () {
            barangayNameInput.value = this.options[this.selectedIndex]?.text || '';
        });


        // Initial visual state — safe even while the modal is still hidden.
        updateProgressBar(1);

    })();
</script>
@endpush