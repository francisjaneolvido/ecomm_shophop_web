{{-- =========================================================
    BUYER REGISTRATION MODAL
    Path: resources/views/auth/modals/buyer-registration-modal.blade.php

    Opened from account-type-modal when "Buyer" is selected.
    Same split-screen registration content as the original
    register page (artistic navy panel + multi-step form),
    now rendered as a centered modal dialog with a light,
    translucent backdrop instead of a full page.
========================================================= --}}
<div
    id="buyer-registration-modal"
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-6
           opacity-0 transition-opacity duration-300 ease-out"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-buyer-registration-modal-close
        aria-label="Close buyer registration"
        class="absolute inset-0 w-full h-full
               bg-navy/35 backdrop-blur-[2px]
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
               sm:max-h-[calc(100vh-3rem)]
               sm:max-w-5xl xl:max-w-6xl
               overflow-y-auto
               sm:rounded-2xl
               bg-white
               border-0 sm:border sm:border-gray-border/70
               shadow-xl shadow-navy/10
               opacity-0 scale-95 translate-y-3
               transition-all duration-300 ease-out"
    >

        {{-- Top bar --}}
        <div
            class="sticky top-0 z-20
                   flex items-center gap-3
                   bg-white/90 backdrop-blur
                   border-b border-gray-border/60
                   px-4 sm:px-6
                   py-3"
        >
            <button
                type="button"
                data-buyer-registration-modal-back
                aria-label="Back to account type"
                class="w-8 h-8 shrink-0
                       rounded-full
                       text-navy/40
                       flex items-center justify-center
                       hover:bg-teal-light hover:text-teal-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-teal/15
                       transition"
            >
                <x-lucide-arrow-left class="w-4 h-4" />
            </button>

            <p
                id="buyer-registration-modal-title"
                class="flex-1 min-w-0 text-sm font-semibold text-navy truncate"
            >
                Buyer Registration
            </p>

            <button
                type="button"
                data-buyer-registration-modal-close
                aria-label="Close buyer registration"
                class="w-8 h-8 shrink-0
                       rounded-full
                       text-navy/40
                       flex items-center justify-center
                       hover:bg-teal-light hover:text-teal-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-teal/15
                       transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>


        {{-- =====================================================
            SPLIT CONTENT
        ====================================================== --}}
        <div class="grid lg:grid-cols-[1fr_560px]">


            {{-- =================================================
                LEFT ARTISTIC PANEL — simplified / minimalist
            ================================================== --}}
            <div
                class="relative hidden lg:flex
                       overflow-hidden
                       bg-navy
                       px-10 xl:px-16
                       py-10
                       items-center"
            >

                {{-- Background decorations — reduced to two soft blobs --}}
                <div
                    class="pointer-events-none absolute
                           -top-24 -left-24
                           w-80 h-80
                           rounded-full
                           bg-teal/15
                           blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute
                           -bottom-32 -right-20
                           w-96 h-96
                           rounded-full
                           bg-teal/[0.06]
                           blur-3xl"
                ></div>


                <div class="relative z-10 w-full max-w-2xl mx-auto">


                    {{-- Logo --}}
                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 flex items-center justify-center">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ShopHop"
                                class="w-12 h-12 object-contain"
                            >
                        </div>

                        <div>

                            <p class="text-white text-xl font-bold leading-none">
                                ShopHop
                            </p>

                            <p class="text-teal text-[9px] tracking-[0.24em] mt-2">
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Main artwork content --}}
                    <div class="mt-8 xl:mt-10">

                        <span
                            class="inline-flex items-center gap-2
                                   rounded-full
                                   bg-white/[0.06]
                                   border border-white/10
                                   px-3.5 py-1.5
                                   text-[11px] font-medium
                                   text-teal"
                        >
                            <x-lucide-sparkles class="w-3.5 h-3.5" />

                            YOUR SHOPPING JOURNEY STARTS HERE
                        </span>


                        <h1
                            class="mt-5
                                   text-white
                                   text-4xl xl:text-5xl
                                   font-extrabold
                                   leading-[1.05]
                                   max-w-xl"
                        >
                            <span class="text-white">Discover more.</span>
                            <span class="block text-teal">
                                Shop your way.
                            </span>
                        </h1>


                        <p
                            class="mt-4
                                   text-white/55
                                   text-base
                                   leading-relaxed
                                   max-w-md"
                        >
                            Create your ShopHop account and enjoy a smoother
                            way to discover products, save favorites,
                            and manage your shopping experience.
                        </p>

                    </div>


                    {{-- Minimal floating card cluster --}}
                    <div class="relative mt-10 xl:mt-12 h-48 max-w-xl">

                        {{-- Main center card --}}
                        <div
                            class="absolute
                                   left-1/2 top-1/2
                                   -translate-x-1/2
                                   -translate-y-1/2
                                   w-44 h-44
                                   rounded-3xl
                                   bg-white
                                   shadow-xl shadow-black/20
                                   flex flex-col
                                   items-center justify-center"
                        >

                            <div
                                class="w-16 h-16
                                       rounded-2xl
                                       bg-teal-light
                                       flex items-center justify-center
                                       text-teal-dark"
                            >
                                <x-lucide-shopping-bag class="w-8 h-8" />
                            </div>

                            <p class="text-navy font-bold text-base mt-4">
                                ShopHop
                            </p>

                            <p class="text-navy/40 text-[11px] mt-1">
                                Find. Love. Shop.
                            </p>

                        </div>


                        {{-- Floating card - save favorites --}}
                        <div
                            class="absolute
                                   left-2 top-2
                                   w-36
                                   rounded-2xl
                                   bg-white/95
                                   p-3.5
                                   shadow-lg shadow-black/10"
                        >

                            <div class="flex items-center gap-2.5">

                                <div
                                    class="w-9 h-9
                                           rounded-xl
                                           bg-teal-light
                                           flex items-center justify-center"
                                >
                                    <x-lucide-heart class="w-4 h-4 text-teal-dark" />
                                </div>

                                <div>

                                    <p class="text-navy text-[11px] font-semibold">
                                        Save Favorites
                                    </p>

                                    <p class="text-navy/40 text-[10px] mt-0.5">
                                        Keep what you love
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating card - easy shopping --}}
                        <div
                            class="absolute
                                   right-2 bottom-2
                                   w-36
                                   rounded-2xl
                                   bg-teal
                                   p-3.5
                                   shadow-lg shadow-black/10"
                        >

                            <div class="flex items-center gap-2.5">

                                <div
                                    class="w-9 h-9
                                           rounded-xl
                                           bg-white/15
                                           flex items-center justify-center"
                                >
                                    <x-lucide-truck class="w-4 h-4 text-white" />
                                </div>

                                <div>

                                    <p class="text-white text-[11px] font-semibold">
                                        Easy Shopping
                                    </p>

                                    <p class="text-white/60 text-[10px] mt-0.5">
                                        Everything in one place
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Trust stats row --}}
                    <div class="mt-8 flex items-center gap-6 xl:gap-8 max-w-xl">

                        <div>

                            <p class="text-white text-xl xl:text-2xl font-extrabold">
                                10K+
                            </p>

                            <p class="text-white/45 text-[11px] mt-1">
                                Active Sellers
                            </p>

                        </div>

                        <div class="w-px h-8 bg-white/10"></div>

                        <div>

                            <p class="text-white text-xl xl:text-2xl font-extrabold">
                                50K+
                            </p>

                            <p class="text-white/45 text-[11px] mt-1">
                                Products Listed
                            </p>

                        </div>

                        <div class="w-px h-8 bg-white/10"></div>

                        <div>

                            <p class="text-white text-xl xl:text-2xl font-extrabold">
                                4.8<span class="text-teal">★</span>
                            </p>

                            <p class="text-white/45 text-[11px] mt-1">
                                Customer Rating
                            </p>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                RIGHT REGISTRATION PANEL
            ================================================== --}}
            <div
                class="bg-white
                       px-4 sm:px-8 xl:px-10
                       py-8 sm:py-10 lg:py-12"
            >

                <div id="buyer-registration-panel" class="max-w-xl mx-auto">


                    {{-- Mobile branding --}}
                    <div class="lg:hidden flex items-center gap-3 mb-7">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-9 h-9 object-contain"
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
                    <div class="mb-7">

                        <p class="text-[11px] font-semibold tracking-wide text-teal-dark mb-2">
                            CREATE ACCOUNT
                        </p>

                        <h2 class="text-navy text-2xl sm:text-3xl font-bold">
                            Buyer Sign Up
                        </h2>

                        <p class="text-sm text-navy/45 mt-2 leading-relaxed">
                            Enter your details below to create your ShopHop buyer account.
                        </p>

                    </div>


                    {{-- =============================================
                        STEP PROGRESS BAR — sleeker, thinner
                    ============================================== --}}
                    <div class="mb-8">

                        <div
                            class="grid items-center"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border flex items-center justify-center text-[11px] font-bold bg-teal border-teal text-white transition-colors duration-300"
                                data-step-circle="1"
                            >
                                <span class="step-number">1</span>
                                <x-lucide-check class="step-check hidden w-3.5 h-3.5" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border transition-colors duration-300" data-step-line="1"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border flex items-center justify-center text-[11px] font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="2"
                            >
                                <span class="step-number">2</span>
                                <x-lucide-check class="step-check hidden w-3.5 h-3.5" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border transition-colors duration-300" data-step-line="2"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border flex items-center justify-center text-[11px] font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="3"
                            >
                                <span class="step-number">3</span>
                                <x-lucide-check class="step-check hidden w-3.5 h-3.5" />
                            </div>

                            <div class="step-line h-px mx-1 bg-gray-border transition-colors duration-300" data-step-line="3"></div>

                            <div
                                class="step-circle justify-self-center w-8 h-8 rounded-full border flex items-center justify-center text-[11px] font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="4"
                            >
                                <span class="step-number">4</span>
                                <x-lucide-check class="step-check hidden w-3.5 h-3.5" />
                            </div>

                        </div>

                        <div
                            class="grid mt-2"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <p class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-semibold leading-tight text-navy transition-colors duration-300" data-step-label="1">
                                Personal
                            </p>

                            <div></div>

                            <p class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300" data-step-label="2">
                                Address
                            </p>

                            <div></div>

                            <p class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300" data-step-label="3">
                                Verification
                            </p>

                            <div></div>

                            <p class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300" data-step-label="4">
                                Security
                            </p>

                        </div>

                    </div>



                    {{-- Validation Errors --}}
                    @if ($errors->any())

                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">

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


                        {{-- =========================================
                            STEP 1 — PERSONAL DETAILS
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- First Name --}}
                                <div>

                                    <label for="buyer_first_name" class="block text-xs font-medium text-navy/70 mb-2">
                                        First Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="buyer_first_name"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        required
                                        autocomplete="given-name"
                                        placeholder="Enter first name"
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_first_name_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Last Name --}}
                                <div>

                                    <label for="buyer_last_name" class="block text-xs font-medium text-navy/70 mb-2">
                                        Last Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="buyer_last_name"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        required
                                        autocomplete="family-name"
                                        placeholder="Enter last name"
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_last_name_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Middle Initial --}}
                                <div>

                                    <label for="buyer_middle_initial" class="block text-xs font-medium text-navy/70 mb-2">
                                        Middle Initial
                                    </label>

                                    <input
                                        type="text"
                                        id="buyer_middle_initial"
                                        name="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="2"
                                        placeholder="e.g. M."
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_middle_initial_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Sex --}}
                                <div>

                                    <label for="buyer_sex" class="block text-xs font-medium text-navy/70 mb-2">
                                        Sex
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="buyer_sex"
                                        name="sex"
                                        required
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >
                                        <option value="">Select sex</option>
                                        <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                        <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                    </select>

                                    <p id="buyer_sex_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Email --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_email" class="block text-xs font-medium text-navy/70 mb-2">
                                        E-mail
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="email"
                                        id="buyer_email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autocomplete="email"
                                        placeholder="your@email.com"
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_email_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Contact --}}
                                <div>

                                    <label for="buyer_contact_no" class="block text-xs font-medium text-navy/70 mb-2">
                                        Contact No.
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="tel"
                                        id="buyer_contact_no"
                                        name="contact_no"
                                        value="{{ old('contact_no') }}"
                                        required
                                        inputmode="numeric"
                                        maxlength="11"
                                        placeholder="09XXXXXXXXX"
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_contact_no_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Birthday --}}
                                <div>

                                    <label for="buyer_birthday" class="block text-xs font-medium text-navy/70 mb-2">
                                        Birthday
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        id="buyer_birthday"
                                        name="birthday"
                                        value="{{ old('birthday') }}"
                                        max="{{ now()->format('Y-m-d') }}"
                                        required
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >

                                    <p id="buyer_birthday_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Age --}}
                                <div>

                                    <label for="buyer_age" class="block text-xs font-medium text-navy/70 mb-2">
                                        Age
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="number"
                                        id="buyer_age"
                                        value="{{ old('age') }}"
                                        readonly
                                        placeholder="Auto-generated"
                                        class="w-full rounded-xl border border-gray-border/70 bg-gray-bg px-4 py-3 text-sm text-navy outline-none"
                                    >

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="buyer-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3.5 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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


                            <div id="buyer-address-status" class="hidden mb-4 text-xs text-teal-dark">
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- Province --}}
                                <div>

                                    <label for="buyer_province" class="block text-xs font-medium text-navy/70 mb-2">
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="buyer_province"
                                        name="province_code"
                                        required
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >
                                        <option value="">Select province</option>
                                    </select>

                                    <input type="hidden" id="buyer_province_name" name="province_name" value="{{ old('province_name') }}">

                                    <p id="buyer_province_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- City / Municipality --}}
                                <div>

                                    <label for="buyer_municipality" class="block text-xs font-medium text-navy/70 mb-2">
                                        Municipality / City
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="buyer_municipality"
                                        name="municipality_code"
                                        required
                                        disabled
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >
                                        <option value="">Select municipality / city</option>
                                    </select>

                                    <input type="hidden" id="buyer_municipality_name" name="municipality_name" value="{{ old('municipality_name') }}">

                                    <p id="buyer_municipality_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Barangay --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_barangay" class="block text-xs font-medium text-navy/70 mb-2">
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="buyer_barangay"
                                        name="barangay_code"
                                        required
                                        disabled
                                        class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >
                                        <option value="">Select barangay</option>
                                    </select>

                                    <input type="hidden" id="buyer_barangay_name" name="barangay_name" value="{{ old('barangay_name') }}">

                                    <p id="buyer_barangay_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                {{-- Street --}}
                                <div class="sm:col-span-2">

                                    <label for="buyer_street_address" class="block text-xs font-medium text-navy/70 mb-2">
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="buyer_street_address"
                                        name="street_address"
                                        rows="3"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full resize-none rounded-xl border border-gray-border/70 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                    >{{ old('street_address') }}</textarea>

                                    <p id="buyer_street_address_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="buyer-step2-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3.5 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="buyer-step2-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3.5 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <label for="buyer_valid_id" class="block text-xs font-medium text-navy/70 mb-2">
                                Upload Valid ID
                                <span class="text-red-500">*</span>
                            </label>


                            <label
                                for="buyer_valid_id"
                                class="flex items-center gap-4 rounded-xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition"
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


                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="buyer-step3-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3.5 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="buyer-step3-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3.5 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label for="buyer_password" class="block text-xs font-medium text-navy/70 mb-2">
                                        Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="buyer_password"
                                            name="password"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Minimum 8 characters"
                                            class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 pr-11 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                        <button
                                            type="button"
                                            id="buyer_toggle_password"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40 hover:text-navy transition"
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

                                    <p id="buyer_password_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                <div>

                                    <label for="buyer_password_confirmation" class="block text-xs font-medium text-navy/70 mb-2">
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="buyer_password_confirmation"
                                            name="password_confirmation"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Re-enter password"
                                            class="w-full rounded-xl border border-gray-border/70 bg-white px-4 py-3 pr-11 text-sm text-navy outline-none placeholder:text-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/10 transition"
                                        >

                                        <button
                                            type="button"
                                            id="buyer_toggle_password_confirmation"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40 hover:text-navy transition"
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

                                    <p id="buyer_password_confirmation_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>

                            </div>

                            {{-- Password requirements checklist --}}
                            <div
                                id="buyer-password-requirements"
                                class="mt-4 rounded-xl border border-gray-border/70 bg-gray-bg p-4 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2"
                            >

                                <p class="req-item flex items-center gap-2 text-[11px] text-navy/40 transition-colors duration-200" data-req="length">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0 transition-colors duration-200">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    Minimum 8 characters
                                </p>

                                <p class="req-item flex items-center gap-2 text-[11px] text-navy/40 transition-colors duration-200" data-req="uppercase">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0 transition-colors duration-200">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    At least 1 uppercase letter (A–Z)
                                </p>

                                <p class="req-item flex items-center gap-2 text-[11px] text-navy/40 transition-colors duration-200" data-req="lowercase">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0 transition-colors duration-200">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    At least 1 lowercase letter (a–z)
                                </p>

                                <p class="req-item flex items-center gap-2 text-[11px] text-navy/40 transition-colors duration-200" data-req="number">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0 transition-colors duration-200">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    At least 1 number (0–9)
                                </p>

                                <p class="req-item sm:col-span-2 flex items-center gap-2 text-[11px] text-navy/30 transition-colors duration-200" data-req="special">
                                    <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-border bg-white flex items-center justify-center shrink-0 transition-colors duration-200">
                                        <x-lucide-check class="req-check hidden w-2.5 h-2.5 text-white" />
                                    </span>
                                    Special character
                                    <span class="text-navy/30">(recommended: ! @ # $ % ^ &amp; *)</span>
                                </p>

                            </div>

                            {{-- Approval notice --}}
                            <div class="mt-6 rounded-xl border border-teal/15 bg-teal-light/40 p-4">

                                <div class="flex gap-3">

                                    <x-lucide-info class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />

                                    <p class="text-[11px] sm:text-xs text-navy/60 leading-relaxed">
                                        After submitting your registration, please
                                        wait for the administrator's approval.
                                        Your approval status will be sent to your
                                        registered email.
                                    </p>

                                </div>

                            </div>

                            {{-- Terms & Agreement --}}
                            <div class="mt-5">

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

                                <p id="buyer_terms_error" class="hidden text-[11px] text-red-500 mt-1.5 ml-8"></p>

                            </div>


                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="buyer-step4-back"
                                    class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3.5 px-6 rounded-full hover:bg-gray-bg transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3.5 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Create Account
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>


                        {{-- Sign in --}}
                        <div class="text-center mt-6">

                            <p class="text-xs text-navy/40">
                                Already have an account?

                                <button
                                    type="button"
                                    data-buyer-registration-modal-signin
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
    input[type="password"]::-webkit-strong-password-auto-fill-button,
    input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Hide Edge's built-in reveal-password icon for the same reason. */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none !important;
    }
</style>
@endpush


@push('scripts')
<script>
    (function () {

        const modal = document.getElementById('buyer-registration-modal');

        if (!modal) {
            return;
        }

        const dialog = document.getElementById('buyer-registration-dialog');

        let lastFocusedElement = null;
        let provincesLoaded = false;
        let closeTimeoutId = null;


        /*
        |--------------------------------------------------------------------------
        | MODAL OPEN / CLOSE — fade + scale transition
        |--------------------------------------------------------------------------
        */

        function openBuyerRegistrationModal() {

            lastFocusedElement = document.activeElement;

            if (closeTimeoutId) {
                window.clearTimeout(closeTimeoutId);
                closeTimeoutId = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            // Force layout so the transition actually animates from the
            // starting (opacity-0 / scale-95) state instead of jump-cutting.
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

            if (dialog) {
                dialog.scrollTo({ top: 0 });
            }

            if (!provincesLoaded) {
                provincesLoaded = true;
                loadProvinces();
            }

            window.setTimeout(function () {
                const firstField = document.getElementById('buyer_first_name');
                if (firstField) firstField.focus();
            }, 50);

        }

        function closeBuyerRegistrationModal() {

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
            if (event.detail && event.detail.type === 'buyer') {
                openBuyerRegistrationModal();
            }
        });

        document.addEventListener('click', function (event) {

            const backTrigger = event.target.closest('[data-buyer-registration-modal-back]');

            if (backTrigger && modal.contains(backTrigger)) {
                event.preventDefault();
                closeBuyerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-account-type-modal'));
                return;
            }

            const signInTrigger = event.target.closest('[data-buyer-registration-modal-signin]');

            if (signInTrigger && modal.contains(signInTrigger)) {
                event.preventDefault();
                closeBuyerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
                return;
            }

            const closeTrigger = event.target.closest('[data-buyer-registration-modal-close]');

            if (closeTrigger && modal.contains(closeTrigger)) {
                event.preventDefault();
                closeBuyerRegistrationModal();
            }

        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeBuyerRegistrationModal();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | FIELD REFERENCES
        |--------------------------------------------------------------------------
        */

        const birthdayInput = document.getElementById('buyer_birthday');
        const ageInput = document.getElementById('buyer_age');

        const provinceSelect = document.getElementById('buyer_province');
        const municipalitySelect = document.getElementById('buyer_municipality');
        const barangaySelect = document.getElementById('buyer_barangay');

        const provinceNameInput = document.getElementById('buyer_province_name');
        const municipalityNameInput = document.getElementById('buyer_municipality_name');
        const barangayNameInput = document.getElementById('buyer_barangay_name');

        const addressStatus = document.getElementById('buyer-address-status');

        const validIdInput = document.getElementById('buyer_valid_id');
        const fileName = document.getElementById('buyer-file-name');
        const validIdError = document.getElementById('buyer_valid_id_error');

        const registerForm = document.getElementById('buyer-register-form');


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
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        validIdInput.addEventListener('change', function () {

            if (this.files.length > 0) {

                const file = this.files[0];

                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                const maxSizeBytes = 5 * 1024 * 1024;

                if (!allowedTypes.includes(file.type)) {
                    showError(validIdInput, validIdError, 'Only JPG, JPEG, PNG or PDF files are allowed.');
                    fileName.textContent = '';
                    fileName.classList.add('hidden');
                    this.value = '';
                    return;
                }

                if (file.size > maxSizeBytes) {
                    showError(validIdInput, validIdError, 'File is too large. Maximum size is 5MB.');
                    fileName.textContent = '';
                    fileName.classList.add('hidden');
                    this.value = '';
                    return;
                }

                showError(validIdInput, validIdError, '');
                fileName.textContent = file.name;
                fileName.classList.remove('hidden');

            } else {
                fileName.textContent = '';
                fileName.classList.add('hidden');
            }

        });


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


        const firstNameInput = document.getElementById('buyer_first_name');
        const firstNameError = document.getElementById('buyer_first_name_error');

        firstNameInput.addEventListener('input', function () {
            validateNameField(firstNameInput, firstNameError, 'First name');
        });


        const lastNameInput = document.getElementById('buyer_last_name');
        const lastNameError = document.getElementById('buyer_last_name_error');

        lastNameInput.addEventListener('input', function () {
            validateNameField(lastNameInput, lastNameError, 'Last name');
        });


        const middleInitialInput = document.getElementById('buyer_middle_initial');
        const middleInitialError = document.getElementById('buyer_middle_initial_error');

        middleInitialInput.addEventListener('input', function () {
            validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
        });


        const emailInput = document.getElementById('buyer_email');
        const emailError = document.getElementById('buyer_email_error');

        emailInput.addEventListener('input', function () {
            validateEmailField(emailInput, emailError);
        });

        emailInput.addEventListener('blur', function () {
            validateEmailField(emailInput, emailError);
        });


        const contactInput = document.getElementById('buyer_contact_no');
        const contactError = document.getElementById('buyer_contact_no_error');

        contactInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            validateContactField(contactInput, contactError);
        });


        const sexInput = document.getElementById('buyer_sex');
        const sexError = document.getElementById('buyer_sex_error');

        sexInput.addEventListener('change', function () {
            validateRequiredField(sexInput, sexError, 'Sex');
        });


        const birthdayError = document.getElementById('buyer_birthday_error');

        birthdayInput.addEventListener('change', function () {
            validateRequiredField(birthdayInput, birthdayError, 'Birthday');
        });


        const provinceError = document.getElementById('buyer_province_error');
        const municipalityError = document.getElementById('buyer_municipality_error');
        const barangayError = document.getElementById('buyer_barangay_error');

        provinceSelect.addEventListener('change', function () {
            validateRequiredField(provinceSelect, provinceError, 'Province');
        });

        municipalitySelect.addEventListener('change', function () {
            validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
        });

        barangaySelect.addEventListener('change', function () {
            validateRequiredField(barangaySelect, barangayError, 'Barangay');
        });


        const streetAddressInput = document.getElementById('buyer_street_address');
        const streetAddressError = document.getElementById('buyer_street_address_error');

        streetAddressInput.addEventListener('input', function () {
            validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
        });


        const passwordInput = document.getElementById('buyer_password');
        const passwordError = document.getElementById('buyer_password_error');

        const passwordConfirmInput = document.getElementById('buyer_password_confirmation');
        const passwordConfirmError = document.getElementById('buyer_password_confirmation_error');

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

        setupPasswordToggle(passwordInput, document.getElementById('buyer_toggle_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('buyer_toggle_password_confirmation'));


        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW
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
                    'bg-white', 'border-gray-border', 'text-navy/30'
                );

                if (s < activeStep) {
                    circle.classList.add('bg-teal', 'border-teal', 'text-white');
                    numberEl.classList.add('hidden');
                    checkEl.classList.remove('hidden');
                } else if (s === activeStep) {
                    circle.classList.add('bg-teal', 'border-teal', 'text-white');
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

        function validateValidId() {

            if (!validIdInput.files || validIdInput.files.length === 0) {
                showError(validIdInput, validIdError, 'Please upload a valid ID.');
                return false;
            }

            showError(validIdInput, validIdError, '');
            return true;

        }

        function validateTerms() {

            const termsInput = document.getElementById('buyer_terms');
            const termsErrorEl = document.getElementById('buyer_terms_error');

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
            return validateValidId();
        }


        document.getElementById('buyer-step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('buyer-step2-back').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('buyer-step2-next').addEventListener('click', function () {
            if (validateStep2()) goToStep(3);
        });

        document.getElementById('buyer-step3-back').addEventListener('click', function () {
            goToStep(2);
        });

        document.getElementById('buyer-step3-next').addEventListener('click', function () {
            if (validateStep3()) goToStep(4);
        });

        document.getElementById('buyer-step4-back').addEventListener('click', function () {
            goToStep(3);
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

            const isValidIdValid = validateValidId();

            if (!isValidIdValid) {
                e.preventDefault();
                goToStep(3);
                return;
            }

            const isPasswordValid = validatePasswordField(passwordInput, passwordError);
            const isPasswordConfirmValid = validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
            const isTermsValid = validateTerms();

            if (!isPasswordValid || !isPasswordConfirmValid || !isTermsValid) {
                e.preventDefault();
                goToStep(4);
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