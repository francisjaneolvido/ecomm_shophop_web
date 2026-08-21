@extends('layouts.app')

@section('title', 'Create Account — ShopHop')

{{-- This flag tells layouts/app.blade.php to skip rendering the navbar
     and footer on this page. See the instructions provided alongside
     this file for the small change needed in that layout. --}}
@section('hideChrome', true)

@section('content')

<section class="relative overflow-hidden bg-gray-bg">

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

    {{-- =========================================================
        REGISTRATION HERO / SPLIT SCREEN
    ========================================================= --}}
    <div class="relative min-h-screen">

        <div class="grid lg:grid-cols-[1fr_560px] min-h-screen">


            {{-- =====================================================
                LEFT ARTISTIC PANEL
            ====================================================== --}}
            <div
                class="relative hidden lg:flex
                       overflow-hidden
                       bg-navy
                       px-10 xl:px-16
                       py-8
                       items-center"
            >

                {{-- Background decorations --}}
                <div
                    class="pointer-events-none absolute
                           -top-32 -left-32
                           w-96 h-96
                           rounded-full
                           bg-teal/20
                           blur-2xl"
                ></div>

                <div
                    class="pointer-events-none absolute
                           -bottom-40 -right-24
                           w-120 h-120
                           rounded-full
                           bg-teal/10"
                ></div>

                <div
                    class="pointer-events-none absolute
                           top-[18%] right-[12%]
                           w-32 h-32
                           rounded-full
                           border border-white/10"
                ></div>

                <div
                    class="pointer-events-none absolute
                           bottom-[12%] left-[10%]
                           w-20 h-20
                           rounded-full
                           border border-teal/30"
                ></div>


                {{-- Tiny dots --}}
                <div class="pointer-events-none absolute inset-0 opacity-20">
                    <div class="absolute top-20 left-20 w-1.5 h-1.5 rounded-full bg-white"></div>
                    <div class="absolute top-36 left-[42%] w-1 h-1 rounded-full bg-teal"></div>
                    <div class="absolute top-[28%] right-[18%] w-1.5 h-1.5 rounded-full bg-white"></div>
                    <div class="absolute bottom-[28%] left-[20%] w-1 h-1 rounded-full bg-teal"></div>
                    <div class="absolute bottom-24 right-[32%] w-1.5 h-1.5 rounded-full bg-white"></div>
                </div>


                <div class="relative z-10 w-full max-w-2xl mx-auto">


                    {{-- Logo --}}
                    <div class="flex items-center gap-3">

                        <div
                            class="w-14 h-14
                                flex items-center justify-center"
                            >
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ShopHop"
                                class="w-14 h-14 object-contain"
                            >
                        </div>

                        <div>

                            <p class="text-white text-2xl font-bold leading-none">
                                ShopHop
                            </p>

                            <p
                                class="text-teal
                                       text-[9px]
                                       tracking-[0.24em]
                                       mt-2"
                            >
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Main artwork content --}}
                    <div class="mt-7 xl:mt-9">

                        <span
                            class="inline-flex items-center gap-2
                                   rounded-full
                                   bg-white/10
                                   border border-white/10
                                   backdrop-blur
                                   px-4 py-2
                                   text-xs font-semibold
                                   text-teal"
                        >
                            <x-lucide-sparkles class="w-4 h-4" />

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
                                   text-white/65
                                   text-base xl:text-lg
                                   leading-relaxed
                                   max-w-lg"
                        >
                            Create your ShopHop account and enjoy a smoother
                            way to discover products, save favorites,
                            and manage your shopping experience.
                        </p>

                    </div>


                    {{-- Artistic shopping cards --}}
                    <div
                        class="relative
                               mt-7 xl:mt-8
                               h-56
                               max-w-xl"
                    >

                        {{-- Main center card --}}
                        <div
                            class="absolute
                                   left-1/2 top-1/2
                                   -translate-x-1/2
                                   -translate-y-1/2
                                   w-52 h-52
                                   rounded-4xl
                                   bg-white
                                   shadow-2xl
                                   flex flex-col
                                   items-center justify-center"
                        >

                            <div
                                class="w-20 h-20
                                       rounded-3xl
                                       bg-teal-light
                                       flex items-center justify-center
                                       text-teal-dark"
                            >
                                <x-lucide-shopping-bag class="w-10 h-10" />
                            </div>

                            <p class="text-navy font-bold text-lg mt-5">
                                ShopHop
                            </p>

                            <p class="text-navy/40 text-xs mt-1">
                                Find. Love. Shop.
                            </p>

                        </div>


                        {{-- Floating card - wishlist --}}
                        <div
                            class="absolute
                                   left-4 top-4
                                   w-40
                                   rounded-2xl
                                   bg-white/95
                                   backdrop-blur
                                   p-4
                                   shadow-xl
                                   rotate-[-5deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-teal-light
                                           flex items-center justify-center"
                                >
                                    <x-lucide-heart class="w-5 h-5 text-teal-dark" />
                                </div>

                                <div>

                                    <p class="text-navy text-xs font-semibold">
                                        Save Favorites
                                    </p>

                                    <p class="text-navy/40 text-[10px] mt-1">
                                        Keep what you love
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating card - secure --}}
                        <div
                            class="absolute
                                   right-0 top-10
                                   w-40
                                   rounded-2xl
                                   bg-white/95
                                   backdrop-blur
                                   p-4
                                   shadow-xl
                                   rotate-[5deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-teal-light
                                           flex items-center justify-center"
                                >
                                    <x-lucide-shield-check class="w-5 h-5 text-teal-dark" />
                                </div>

                                <div>

                                    <p class="text-navy text-xs font-semibold">
                                        Secure Account
                                    </p>

                                    <p class="text-navy/40 text-[10px] mt-1">
                                        Shop with confidence
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating card - delivery --}}
                        <div
                            class="absolute
                                   left-10 bottom-0
                                   w-40
                                   rounded-2xl
                                   bg-teal
                                   p-4
                                   shadow-xl
                                   rotate-[4deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-white/15
                                           flex items-center justify-center"
                                >
                                    <x-lucide-truck class="w-5 h-5 text-white" />
                                </div>

                                <div>

                                    <p class="text-white text-xs font-semibold">
                                        Easy Shopping
                                    </p>

                                    <p class="text-white/60 text-[10px] mt-1">
                                        Everything in one place
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating mini badge --}}
                        <div
                            class="absolute
                                   right-9 bottom-3
                                   inline-flex items-center gap-2
                                   bg-white
                                   rounded-full
                                   px-4 py-2
                                   shadow-lg"
                        >

                            <span class="w-2 h-2 rounded-full bg-teal"></span>

                            <span class="text-[10px] font-semibold text-navy">
                                Ready to hop in?
                            </span>

                        </div>

                    </div>


                    {{-- Trust stats row --}}
                    <div
                        class="mt-7 xl:mt-8
                               flex items-center
                               gap-6 xl:gap-8
                               max-w-xl"
                    >

                        <div>

                            <p class="text-white text-2xl xl:text-3xl font-extrabold">
                                10K+
                            </p>

                            <p class="text-white/50 text-xs mt-1">
                                Active Sellers
                            </p>

                        </div>

                        <div class="w-px h-10 bg-white/10"></div>

                        <div>

                            <p class="text-white text-2xl xl:text-3xl font-extrabold">
                                50K+
                            </p>

                            <p class="text-white/50 text-xs mt-1">
                                Products Listed
                            </p>

                        </div>

                        <div class="w-px h-10 bg-white/10"></div>

                        <div>

                            <p class="text-white text-2xl xl:text-3xl font-extrabold">
                                4.8<span class="text-teal">★</span>
                            </p>

                            <p class="text-white/50 text-xs mt-1">
                                Customer Rating
                            </p>

                        </div>

                    </div>


                    {{-- Testimonial --}}
                    <div
                        class="mt-5 xl:mt-6
                               max-w-md
                               rounded-2xl
                               bg-white/5
                               border border-white/10
                               backdrop-blur
                               p-4"
                    >

                        <div class="flex items-center gap-1 text-teal mb-2.5">
                            <x-lucide-star class="w-4 h-4 fill-current" />
                            <x-lucide-star class="w-4 h-4 fill-current" />
                            <x-lucide-star class="w-4 h-4 fill-current" />
                            <x-lucide-star class="w-4 h-4 fill-current" />
                            <x-lucide-star class="w-4 h-4 fill-current" />
                        </div>

                        <p class="text-white/70 text-sm leading-relaxed">
                            "Sobrang bilis mag-checkout at ang dami pang deals
                            kada araw. Paborito ko na ito for online shopping!"
                        </p>

                        <div class="flex items-center gap-3 mt-3">

                            <div
                                class="w-8 h-8
                                       rounded-full
                                       bg-teal-light
                                       flex items-center justify-center
                                       text-teal-dark
                                       text-xs font-bold"
                            >
                                JM
                            </div>

                            <div>

                                <p class="text-white text-xs font-semibold">
                                    Jasmine M.
                                </p>

                                <p class="text-white/40 text-[10px]">
                                    Verified Buyer
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                RIGHT REGISTRATION PANEL
            ====================================================== --}}
            <div
                class="bg-white
                       px-4 sm:px-8 xl:px-10
                       py-8 sm:py-10 lg:py-12"
            >

                <div id="registration-panel" class="max-w-xl mx-auto">


                    {{-- Mobile branding --}}
                    <div class="lg:hidden flex items-center gap-3 mb-7">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-10 h-10 object-contain"
                        >

                        <div>

                            <p class="font-bold text-navy">
                                ShopHop
                            </p>

                            <p class="text-[7px] tracking-[0.2em] text-teal-dark mt-1">
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Header --}}
                    <div class="mb-7">

                        <p
                            class="text-xs
                                   font-semibold
                                   tracking-wide
                                   text-teal-dark
                                   mb-2"
                        >
                            CREATE ACCOUNT
                        </p>

                        <h2 class="text-navy text-3xl sm:text-4xl">
                            Sign Up
                        </h2>

                        <p
                            class="text-sm
                                   text-navy/50
                                   mt-2
                                   leading-relaxed"
                        >
                            Enter your details below to create your ShopHop account.
                        </p>

                    </div>


                    {{-- =================================================
                        STEP PROGRESS BAR
                    ================================================== --}}
                    <div class="mb-8">

                        {{-- Circles + connecting lines --}}
                        <div
                            class="grid items-center"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <div
                                class="step-circle justify-self-center w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold bg-teal border-teal text-white transition-colors duration-300"
                                data-step-circle="1"
                            >
                                <span class="step-number">1</span>
                                <x-lucide-check class="step-check hidden w-4 h-4" />
                            </div>

                            <div class="step-line h-0.5 mx-1 bg-gray-border transition-colors duration-300" data-step-line="1"></div>

                            <div
                                class="step-circle justify-self-center w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="2"
                            >
                                <span class="step-number">2</span>
                                <x-lucide-check class="step-check hidden w-4 h-4" />
                            </div>

                            <div class="step-line h-0.5 mx-1 bg-gray-border transition-colors duration-300" data-step-line="2"></div>

                            <div
                                class="step-circle justify-self-center w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="3"
                            >
                                <span class="step-number">3</span>
                                <x-lucide-check class="step-check hidden w-4 h-4" />
                            </div>

                            <div class="step-line h-0.5 mx-1 bg-gray-border transition-colors duration-300" data-step-line="3"></div>

                            <div
                                class="step-circle justify-self-center w-9 h-9 rounded-full border-2 flex items-center justify-center text-xs font-bold bg-white border-gray-border text-navy/30 transition-colors duration-300"
                                data-step-circle="4"
                            >
                                <span class="step-number">4</span>
                                <x-lucide-check class="step-check hidden w-4 h-4" />
                            </div>

                        </div>

                        {{-- Labels --}}
                        <div
                            class="grid mt-2"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto;"
                        >

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-semibold leading-tight text-navy transition-colors duration-300"
                                data-step-label="1"
                            >
                                Personal
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="2"
                            >
                                Address
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="3"
                            >
                                Verification
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="4"
                            >
                                Security
                            </p>

                        </div>

                    </div>



                    {{-- Validation Errors --}}
                    @if ($errors->any())

                        <div
                            class="mb-6
                                   rounded-2xl
                                   border border-red-200
                                   bg-red-50
                                   p-4"
                        >

                            <div class="flex gap-3">

                                <x-lucide-circle-alert
                                    class="w-5 h-5
                                           text-red-500
                                           shrink-0
                                           mt-0.5"
                                />

                                <div>

                                    <p class="text-sm font-semibold text-red-700">
                                        Please check your information.
                                    </p>

                                    <ul class="mt-2 space-y-1 text-xs text-red-600">

                                        @foreach ($errors->all() as $error)

                                            <li>
                                                • {{ $error }}
                                            </li>

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
                        id="register-form"
                    >

                        @csrf


                        {{-- =================================================
                            STEP 1 — PERSONAL DETAILS
                        ================================================== --}}
                        <div data-step-panel="1">

                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- First Name --}}
                                <div>

                                    <label
                                        for="first_name"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        First Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        required
                                        autocomplete="given-name"
                                        placeholder="Enter first name"
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="first_name_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Last Name --}}
                                <div>

                                    <label
                                        for="last_name"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Last Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        required
                                        autocomplete="family-name"
                                        placeholder="Enter last name"
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="last_name_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Middle Initial --}}
                                <div>

                                    <label
                                        for="middle_initial"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Middle Initial
                                    </label>

                                    <input
                                        type="text"
                                        id="middle_initial"
                                        name="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="2"
                                        placeholder="e.g. M."
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="middle_initial_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Sex --}}
                                <div>

                                    <label
                                        for="sex"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Sex
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="sex"
                                        name="sex"
                                        required
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select sex
                                        </option>

                                        <option
                                            value="Male"
                                            @selected(old('sex') === 'Male')
                                        >
                                            Male
                                        </option>

                                        <option
                                            value="Female"
                                            @selected(old('sex') === 'Female')
                                        >
                                            Female
                                        </option>
                                    </select>

                                    <p
                                        id="sex_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Email --}}
                                <div class="sm:col-span-2">

                                    <label
                                        for="email"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        E-mail
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autocomplete="email"
                                        placeholder="your@email.com"
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="email_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Contact --}}
                                <div>

                                    <label
                                        for="contact_no"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Contact No.
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="tel"
                                        id="contact_no"
                                        name="contact_no"
                                        value="{{ old('contact_no') }}"
                                        required
                                        inputmode="numeric"
                                        maxlength="11"
                                        placeholder="09XXXXXXXXX"
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="contact_no_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Birthday --}}
                                <div>

                                    <label
                                        for="birthday"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Birthday
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        id="birthday"
                                        name="birthday"
                                        value="{{ old('birthday') }}"
                                        max="{{ now()->format('Y-m-d') }}"
                                        required
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >

                                    <p
                                        id="birthday_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Age --}}
                                <div>

                                    <label
                                        for="age"
                                        class="block
                                               text-xs font-medium
                                               text-navy
                                               mb-2"
                                    >
                                        Age
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="number"
                                        id="age"
                                        value="{{ old('age') }}"
                                        readonly
                                        placeholder="Auto-generated"
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-gray-bg
                                               px-4 py-3
                                               text-sm
                                               text-navy
                                               outline-none"
                                    >

                                </div>

                            </div>


                            {{-- Step 1 navigation --}}
                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="step1-next"
                                    class="flex-1
                                           inline-flex
                                           items-center justify-center gap-2
                                           bg-teal
                                           hover:bg-teal-dark
                                           text-white
                                           text-sm font-semibold
                                           py-3.5
                                           rounded-xl
                                           shadow-lg shadow-teal/20
                                           hover:-translate-y-0.5
                                           transition-all duration-300"
                                >
                                    Next

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 2 — ADDRESS
                        ================================================== --}}
                        <div data-step-panel="2" class="hidden">

                            <div class="flex items-center gap-2 mb-4">

                                <x-lucide-map-pin
                                    class="w-4 h-4 text-teal-dark"
                                />

                                <p class="text-sm font-semibold text-navy">
                                    Address
                                </p>

                            </div>


                            <div
                                id="address-status"
                                class="hidden
                                       mb-4
                                       text-xs
                                       text-teal-dark"
                            >
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- Province --}}
                                <div>

                                    <label
                                        for="province"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="province"
                                        name="province_code"
                                        required
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select province
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="province_name"
                                        name="province_name"
                                        value="{{ old('province_name') }}"
                                    >

                                    <p
                                        id="province_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- City / Municipality --}}
                                <div>

                                    <label
                                        for="municipality"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Municipality / City
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="municipality"
                                        name="municipality_code"
                                        required
                                        disabled
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               disabled:bg-gray-bg
                                               disabled:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select municipality / city
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="municipality_name"
                                        name="municipality_name"
                                        value="{{ old('municipality_name') }}"
                                    >

                                    <p
                                        id="municipality_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Barangay --}}
                                <div class="sm:col-span-2">

                                    <label
                                        for="barangay"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="barangay"
                                        name="barangay_code"
                                        required
                                        disabled
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               disabled:bg-gray-bg
                                               disabled:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select barangay
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="barangay_name"
                                        name="barangay_name"
                                        value="{{ old('barangay_name') }}"
                                    >

                                    <p
                                        id="barangay_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                {{-- Street --}}
                                <div class="sm:col-span-2">

                                    <label
                                        for="street_address"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="street_address"
                                        name="street_address"
                                        rows="3"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full
                                               resize-none
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >{{ old('street_address') }}</textarea>

                                    <p
                                        id="street_address_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>

                            </div>


                            {{-- Step 2 navigation --}}
                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="step2-back"
                                    class="inline-flex
                                           items-center justify-center gap-2
                                           border border-gray-border
                                           text-navy
                                           text-sm font-semibold
                                           py-3.5 px-6
                                           rounded-xl
                                           hover:bg-gray-bg
                                           transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="step2-next"
                                    class="flex-1
                                           inline-flex
                                           items-center justify-center gap-2
                                           bg-teal
                                           hover:bg-teal-dark
                                           text-white
                                           text-sm font-semibold
                                           py-3.5
                                           rounded-xl
                                           shadow-lg shadow-teal/20
                                           hover:-translate-y-0.5
                                           transition-all duration-300"
                                >
                                    Next

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 3 — VALID ID
                        ================================================== --}}
                        <div data-step-panel="3" class="hidden">

                            <label
                                for="valid_id"
                                class="block text-xs font-medium text-navy mb-2"
                            >
                                Upload Valid ID
                                <span class="text-red-500">*</span>
                            </label>


                            <label
                                for="valid_id"
                                class="flex
                                       items-center gap-4
                                       rounded-xl
                                       border-2
                                       border-dashed
                                       border-gray-border
                                       bg-gray-bg
                                       hover:border-teal
                                       hover:bg-teal-light/30
                                       px-4 py-4
                                       cursor-pointer
                                       transition"
                            >

                                <div
                                    class="shrink-0
                                           w-11 h-11
                                           rounded-xl
                                           bg-white
                                           flex items-center justify-center
                                           text-teal-dark
                                           shadow-sm"
                                >
                                    <x-lucide-upload class="w-5 h-5" />
                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-semibold text-navy">
                                        Choose a valid ID
                                    </p>

                                    <p class="text-[11px] text-navy/40 mt-1">
                                        JPG, JPEG, PNG or PDF · Max 5MB
                                    </p>

                                    <p
                                        id="file-name"
                                        class="hidden
                                               text-[11px]
                                               text-teal-dark
                                               font-medium
                                               mt-1
                                               truncate"
                                    ></p>

                                </div>


                                <input
                                    type="file"
                                    id="valid_id"
                                    name="valid_id"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="hidden"
                                >

                            </label>

                            <p
                                id="valid_id_error"
                                class="hidden text-[11px] text-red-500 mt-1.5"
                            ></p>


                            {{-- Step 3 navigation --}}
                            <div class="flex items-center gap-3 mt-8">

                                <button
                                    type="button"
                                    id="step3-back"
                                    class="inline-flex
                                           items-center justify-center gap-2
                                           border border-gray-border
                                           text-navy
                                           text-sm font-semibold
                                           py-3.5 px-6
                                           rounded-xl
                                           hover:bg-gray-bg
                                           transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="button"
                                    id="step3-next"
                                    class="flex-1
                                           inline-flex
                                           items-center justify-center gap-2
                                           bg-teal
                                           hover:bg-teal-dark
                                           text-white
                                           text-sm font-semibold
                                           py-3.5
                                           rounded-xl
                                           shadow-lg shadow-teal/20
                                           hover:-translate-y-0.5
                                           transition-all duration-300"
                                >
                                    Next

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 4 — SECURITY
                        ================================================== --}}
                        <div data-step-panel="4" class="hidden">

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label
                                        for="password"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Minimum 8 characters"
                                            class="w-full
                                                   rounded-xl
                                                   border border-gray-border
                                                   bg-white
                                                   px-4 py-3
                                                   pr-11
                                                   text-sm
                                                   text-navy
                                                   outline-none
                                                   placeholder:text-navy/30
                                                   focus:border-teal
                                                   focus:ring-4
                                                   focus:ring-teal/10
                                                   transition"
                                        >

                                        <button
                                            type="button"
                                            id="toggle_password"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute
                                                   right-3 top-1/2
                                                   -translate-y-1/2
                                                   w-4 h-4
                                                   text-navy/40
                                                   hover:text-navy
                                                   transition"
                                        >
                                            <svg
                                                class="password-icon-show w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>

                                            <svg
                                                class="password-icon-hide w-4 h-4"
                                                style="display:none"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                                <path d="M1 1l22 22" />
                                            </svg>
                                        </button>

                                    </div>

                                    <p
                                        id="password_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>


                                <div>

                                    <label
                                        for="password_confirmation"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            minlength="8"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Re-enter password"
                                            class="w-full
                                                   rounded-xl
                                                   border border-gray-border
                                                   bg-white
                                                   px-4 py-3
                                                   pr-11
                                                   text-sm
                                                   text-navy
                                                   outline-none
                                                   placeholder:text-navy/30
                                                   focus:border-teal
                                                   focus:ring-4
                                                   focus:ring-teal/10
                                                   transition"
                                        >

                                        <button
                                            type="button"
                                            id="toggle_password_confirmation"
                                            aria-label="Show password"
                                            aria-pressed="false"
                                            class="absolute
                                                   right-3 top-1/2
                                                   -translate-y-1/2
                                                   w-4 h-4
                                                   text-navy/40
                                                   hover:text-navy
                                                   transition"
                                        >
                                            <svg
                                                class="password-icon-show w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>

                                            <svg
                                                class="password-icon-hide w-4 h-4"
                                                style="display:none"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                                <path d="M1 1l22 22" />
                                            </svg>
                                        </button>

                                    </div>

                                    <p
                                        id="password_confirmation_error"
                                        class="hidden text-[11px] text-red-500 mt-1.5"
                                    ></p>

                                </div>

                            </div>

                            {{-- Password requirements checklist --}}
                            <div
                                id="password-requirements"
                                class="mt-4
                                    rounded-xl
                                    border border-gray-border
                                    bg-gray-bg
                                    p-4
                                    grid
                                    grid-cols-1 sm:grid-cols-2
                                    gap-x-4 gap-y-2"
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
                            <div
                                class="mt-6
                                       rounded-xl
                                       border border-teal/20
                                       bg-teal-light/50
                                       p-4"
                            >

                                <div class="flex gap-3">

                                    <x-lucide-info
                                        class="w-4 h-4
                                               text-teal-dark
                                               shrink-0
                                               mt-0.5"
                                    />

                                    <p
                                        class="text-[11px] sm:text-xs
                                               text-navy/60
                                               leading-relaxed"
                                    >
                                        After submitting your registration, please
                                        wait for the administrator's approval.
                                        Your approval status will be sent to your
                                        registered email.
                                    </p>

                                </div>

                            </div>

                            {{-- Terms & Agreement --}}
                            <div class="mt-5">

                                <label
                                    for="terms"
                                    class="flex items-start gap-3 cursor-pointer group"
                                >

                                    <input
                                        type="checkbox"
                                        id="terms"
                                        name="terms"
                                        required
                                        class="peer sr-only"
                                    >

                                    <span
                                        class="mt-0.5
                                            shrink-0
                                            w-5 h-5
                                            rounded-md
                                            border-2 border-gray-border
                                            bg-white
                                            flex items-center justify-center
                                            peer-checked:bg-teal
                                            peer-checked:border-teal
                                            group-hover:border-teal
                                            transition-colors duration-200"
                                    >
                                        <x-lucide-check class="w-3.5 h-3.5 text-white" />
                                    </span>

                                    <span class="text-xs text-navy/60 leading-relaxed">
                                        I have read and agree to ShopHop's
                                        <a
                                            href="#"
                                            target="_blank"
                                            class="font-semibold text-teal-dark hover:text-navy transition"
                                            onclick="event.stopPropagation()"
                                        >Terms and Conditions</a>
                                        and
                                        <a
                                            href="#"
                                            target="_blank"
                                            class="font-semibold text-teal-dark hover:text-navy transition"
                                            onclick="event.stopPropagation()"
                                        >Privacy Policy</a>.
                                        <span class="text-red-500">*</span>
                                    </span>

                                </label>

                                <p
                                    id="terms_error"
                                    class="hidden text-[11px] text-red-500 mt-1.5 ml-8"
                                ></p>

                            </div>


                            {{-- Step 4 navigation --}}
                            <div class="flex items-center gap-3 mt-6">

                                <button
                                    type="button"
                                    id="step4-back"
                                    class="inline-flex
                                           items-center justify-center gap-2
                                           border border-gray-border
                                           text-navy
                                           text-sm font-semibold
                                           py-3.5 px-6
                                           rounded-xl
                                           hover:bg-gray-bg
                                           transition"
                                >
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>

                                <button
                                    type="submit"
                                    class="flex-1
                                           inline-flex
                                           items-center justify-center gap-2
                                           bg-teal
                                           hover:bg-teal-dark
                                           text-white
                                           text-sm font-semibold
                                           py-3.5
                                           rounded-xl
                                           shadow-lg shadow-teal/20
                                           hover:-translate-y-0.5
                                           transition-all duration-300"
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

                                <a
                                    href="{{ route('login') }}"
                                    class="font-semibold
                                           text-teal-dark
                                           hover:text-navy
                                           transition"
                                >
                                    Sign In
                                </a>
                            </p>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection



@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const birthdayInput =
            document.getElementById('birthday');

        const ageInput =
            document.getElementById('age');

        const provinceSelect =
            document.getElementById('province');

        const municipalitySelect =
            document.getElementById('municipality');

        const barangaySelect =
            document.getElementById('barangay');

        const provinceNameInput =
            document.getElementById('province_name');

        const municipalityNameInput =
            document.getElementById('municipality_name');

        const barangayNameInput =
            document.getElementById('barangay_name');

        const addressStatus =
            document.getElementById('address-status');

        const validIdInput =
            document.getElementById('valid_id');

        const fileName =
            document.getElementById('file-name');

        const validIdError =
            document.getElementById('valid_id_error');

        const registerForm =
            document.getElementById('register-form');


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

            const birthday =
                new Date(
                    birthdayInput.value +
                    'T00:00:00'
                );

            const today =
                new Date();

            let age =
                today.getFullYear() -
                birthday.getFullYear();

            const monthDifference =
                today.getMonth() -
                birthday.getMonth();

            if (
                monthDifference < 0 ||
                (
                    monthDifference === 0 &&
                    today.getDate() <
                    birthday.getDate()
                )
            ) {
                age--;
            }

            ageInput.value =
                age >= 0
                    ? age
                    : '';

        }


        birthdayInput.addEventListener(
            'change',
            calculateAge
        );

        calculateAge();



        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        validIdInput.addEventListener(
            'change',
            function () {

                if (this.files.length > 0) {

                    const file = this.files[0];

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'application/pdf',
                    ];

                    const maxSizeBytes = 5 * 1024 * 1024;

                    if (!allowedTypes.includes(file.type)) {

                        showError(
                            validIdInput,
                            validIdError,
                            'Only JPG, JPEG, PNG or PDF files are allowed.'
                        );

                        fileName.textContent = '';
                        fileName.classList.add('hidden');
                        this.value = '';
                        return;

                    }

                    if (file.size > maxSizeBytes) {

                        showError(
                            validIdInput,
                            validIdError,
                            'File is too large. Maximum size is 5MB.'
                        );

                        fileName.textContent = '';
                        fileName.classList.add('hidden');
                        this.value = '';
                        return;

                    }

                    showError(validIdInput, validIdError, '');

                    fileName.textContent =
                        file.name;

                    fileName.classList.remove(
                        'hidden'
                    );

                } else {

                    fileName.textContent = '';

                    fileName.classList.add(
                        'hidden'
                    );

                }

            }
        );



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

                input.classList.add(
                    'border-red-400',
                    'focus:border-red-400',
                    'focus:ring-red-100'
                );

                input.classList.remove(
                    'border-gray-border',
                    'focus:border-teal',
                    'focus:ring-teal/10'
                );

            } else {

                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                input.classList.remove(
                    'border-red-400',
                    'focus:border-red-400',
                    'focus:ring-red-100'
                );

                input.classList.add(
                    'border-gray-border',
                    'focus:border-teal',
                    'focus:ring-teal/10'
                );

            }

        }

        function validateNameField(input, errorEl, label) {

            const value = input.value;

            if (!value && input.required) {
                showError(input, errorEl, `${label} is required.`);
                return false;
            }

            if (value && !nameRegex.test(value)) {
                showError(
                    input,
                    errorEl,
                    `${label} should not contain numbers or special characters.`
                );
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
                showError(
                    input,
                    errorEl,
                    'Please enter a valid email address (e.g. juan@example.com).'
                );
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
                showError(
                    input,
                    errorEl,
                    'Enter a valid 11-digit number starting with 09 (e.g. 09171234567).'
                );
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

                const item = document.querySelector(`[data-req="${key}"]`);

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

        function validatePasswordConfirmation(
            passwordInput,
            confirmInput,
            errorEl
        ) {

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


        const firstNameInput = document.getElementById('first_name');
        const firstNameError = document.getElementById('first_name_error');

        firstNameInput.addEventListener('input', function () {
            validateNameField(firstNameInput, firstNameError, 'First name');
        });


        const lastNameInput = document.getElementById('last_name');
        const lastNameError = document.getElementById('last_name_error');

        lastNameInput.addEventListener('input', function () {
            validateNameField(lastNameInput, lastNameError, 'Last name');
        });


        const middleInitialInput = document.getElementById('middle_initial');
        const middleInitialError = document.getElementById('middle_initial_error');

        middleInitialInput.addEventListener('input', function () {
            validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
        });


        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('email_error');

        emailInput.addEventListener('input', function () {
            validateEmailField(emailInput, emailError);
        });

        emailInput.addEventListener('blur', function () {
            validateEmailField(emailInput, emailError);
        });


        const contactInput = document.getElementById('contact_no');
        const contactError = document.getElementById('contact_no_error');

        contactInput.addEventListener('input', function () {

            this.value = this.value.replace(/\D/g, '');

            validateContactField(contactInput, contactError);

        });


        {{-- Sex, birthday, and address required-field warnings --}}
        const sexInput = document.getElementById('sex');
        const sexError = document.getElementById('sex_error');

        sexInput.addEventListener('change', function () {
            validateRequiredField(sexInput, sexError, 'Sex');
        });


        const birthdayError = document.getElementById('birthday_error');

        birthdayInput.addEventListener('change', function () {
            validateRequiredField(birthdayInput, birthdayError, 'Birthday');
        });


        const provinceError = document.getElementById('province_error');
        const municipalityError = document.getElementById('municipality_error');
        const barangayError = document.getElementById('barangay_error');

        provinceSelect.addEventListener('change', function () {
            validateRequiredField(provinceSelect, provinceError, 'Province');
        });

        municipalitySelect.addEventListener('change', function () {
            validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
        });

        barangaySelect.addEventListener('change', function () {
            validateRequiredField(barangaySelect, barangayError, 'Barangay');
        });


        const streetAddressInput = document.getElementById('street_address');
        const streetAddressError = document.getElementById('street_address_error');

        streetAddressInput.addEventListener('input', function () {
            validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
        });


        const passwordInput = document.getElementById('password');
        const passwordError = document.getElementById('password_error');

        const passwordConfirmInput =
            document.getElementById('password_confirmation');

        const passwordConfirmError =
            document.getElementById('password_confirmation_error');

        passwordInput.addEventListener('input', function () {

            updatePasswordRequirements(passwordInput.value);

            validatePasswordField(passwordInput, passwordError);

            if (passwordConfirmInput.value) {
                validatePasswordConfirmation(
                    passwordInput,
                    passwordConfirmInput,
                    passwordConfirmError
                );
            }

        });

        passwordConfirmInput.addEventListener('input', function () {
            validatePasswordConfirmation(
                passwordInput,
                passwordConfirmInput,
                passwordConfirmError
            );
        });



        /*
        |--------------------------------------------------------------------------
        | SHOW / HIDE PASSWORD
        |--------------------------------------------------------------------------
        */

        function setupPasswordToggle(inputEl, buttonEl) {

            const showIcon =
                buttonEl.querySelector('.password-icon-show');

            const hideIcon =
                buttonEl.querySelector('.password-icon-hide');

            buttonEl.addEventListener('click', function () {

                const isHidden =
                    inputEl.type === 'password';

                inputEl.type =
                    isHidden
                        ? 'text'
                        : 'password';

                showIcon.style.display = isHidden ? 'none' : '';
                hideIcon.style.display = isHidden ? '' : 'none';

                buttonEl.setAttribute(
                    'aria-pressed',
                    isHidden ? 'true' : 'false'
                );

                buttonEl.setAttribute(
                    'aria-label',
                    isHidden ? 'Hide password' : 'Show password'
                );

                // Keep focus and caret on the input after toggling,
                // instead of losing focus to the button.
                inputEl.focus();

                const caretPosition = inputEl.value.length;

                inputEl.setSelectionRange(
                    caretPosition,
                    caretPosition
                );

            });

        }

        setupPasswordToggle(
            passwordInput,
            document.getElementById('toggle_password')
        );

        setupPasswordToggle(
            passwordConfirmInput,
            document.getElementById('toggle_password_confirmation')
        );



        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW
        |--------------------------------------------------------------------------
        */

        const registrationPanel = document.getElementById('registration-panel');

        function getPanel(step) {
            return document.querySelector(`[data-step-panel="${step}"]`);
        }

        function updateProgressBar(activeStep) {

            document.querySelectorAll('[data-step-circle]').forEach(function (circle) {

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

            document.querySelectorAll('[data-step-label]').forEach(function (label) {

                const s = parseInt(label.dataset.stepLabel, 10);

                label.classList.toggle('text-navy', s <= activeStep);
                label.classList.toggle('font-semibold', s <= activeStep);
                label.classList.toggle('text-navy/30', s > activeStep);
                label.classList.toggle('font-medium', s > activeStep);

            });

            document.querySelectorAll('[data-step-line]').forEach(function (line) {

                const s = parseInt(line.dataset.stepLine, 10);

                line.classList.toggle('bg-teal', s < activeStep);
                line.classList.toggle('bg-gray-border', s >= activeStep);

            });

        }

        function goToStep(step) {

            document.querySelectorAll('[data-step-panel]').forEach(function (panel) {

                const s = parseInt(panel.dataset.stepPanel, 10);

                panel.classList.toggle('hidden', s !== step);

            });

            updateProgressBar(step);

            if (registrationPanel) {
                registrationPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

            const termsInput = document.getElementById('terms');
            const termsError = document.getElementById('terms_error');

            if (!termsInput.checked) {
                showError(termsInput, termsError, 'You must agree to the Terms and Conditions.');
                return false;
            }

            showError(termsInput, termsError, '');
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


        document.getElementById('step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('step2-back').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('step2-next').addEventListener('click', function () {
            if (validateStep2()) goToStep(3);
        });

        document.getElementById('step3-back').addEventListener('click', function () {
            goToStep(2);
        });

        document.getElementById('step3-next').addEventListener('click', function () {
            if (validateStep3()) goToStep(4);
        });

        document.getElementById('step4-back').addEventListener('click', function () {
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

            const isPasswordConfirmValid = validatePasswordConfirmation(
                passwordInput,
                passwordConfirmInput,
                passwordConfirmError
            );

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

        const PSGC_BASE =
            'https://psgc.gitlab.io/api';


        const oldProvinceCode =
            @json(old('province_code'));

        const oldMunicipalityCode =
            @json(old('municipality_code'));

        const oldBarangayCode =
            @json(old('barangay_code'));


        function setAddressStatus(
            message,
            isError = false
        ) {

            if (!message) {

                addressStatus.classList.add(
                    'hidden'
                );

                return;
            }

            addressStatus.textContent =
                message;

            addressStatus.classList.remove(
                'hidden'
            );

            addressStatus.classList.toggle(
                'text-red-500',
                isError
            );

            addressStatus.classList.toggle(
                'text-teal-dark',
                !isError
            );

        }


        function resetSelect(
            select,
            text
        ) {

            select.innerHTML =
                `<option value="">${text}</option>`;

            select.disabled = true;

        }


        function fillSelect(
            select,
            items,
            placeholder,
            selectedCode = null
        ) {

            select.innerHTML =
                `<option value="">${placeholder}</option>`;

            items.forEach(function (item) {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    item.code;

                option.textContent =
                    item.name;

                if (
                    selectedCode &&
                    item.code === selectedCode
                ) {
                    option.selected = true;
                }

                select.appendChild(
                    option
                );

            });

            select.disabled = false;

        }


        async function fetchJson(url) {

            const response =
                await fetch(url);

            if (!response.ok) {
                throw new Error(
                    'Unable to load address data.'
                );
            }

            return response.json();

        }


        async function loadProvinces() {

            try {

                setAddressStatus(
                    'Loading provinces...'
                );

                const provinces =
                    await fetchJson(
                        `${PSGC_BASE}/provinces/`
                    );

                provinces.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );

                fillSelect(
                    provinceSelect,
                    provinces,
                    'Select province',
                    oldProvinceCode
                );

                if (oldProvinceCode) {

                    provinceNameInput.value =
                        provinceSelect.options[
                            provinceSelect.selectedIndex
                        ]?.text || '';

                    await loadMunicipalities(
                        oldProvinceCode,
                        oldMunicipalityCode
                    );

                }

                setAddressStatus('');

            } catch (error) {

                setAddressStatus(
                    'Unable to load address dropdowns. Please refresh the page.',
                    true
                );

            }

        }


        async function loadMunicipalities(
            provinceCode,
            selectedCode = null
        ) {

            resetSelect(
                municipalitySelect,
                'Loading municipality / city...'
            );

            resetSelect(
                barangaySelect,
                'Select barangay'
            );

            municipalityNameInput.value = '';
            barangayNameInput.value = '';


            if (!provinceCode) {

                resetSelect(
                    municipalitySelect,
                    'Select municipality / city'
                );

                return;
            }


            try {

                const cities =
                    await fetchJson(
                        `${PSGC_BASE}/provinces/${provinceCode}/cities-municipalities/`
                    );

                cities.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );


                fillSelect(
                    municipalitySelect,
                    cities,
                    'Select municipality / city',
                    selectedCode
                );


                if (selectedCode) {

                    municipalityNameInput.value =
                        municipalitySelect.options[
                            municipalitySelect.selectedIndex
                        ]?.text || '';

                    await loadBarangays(
                        selectedCode,
                        oldBarangayCode
                    );

                }

            } catch (error) {

                resetSelect(
                    municipalitySelect,
                    'Unable to load municipalities'
                );

            }

        }


        async function loadBarangays(
            municipalityCode,
            selectedCode = null
        ) {

            resetSelect(
                barangaySelect,
                'Loading barangays...'
            );

            barangayNameInput.value = '';


            if (!municipalityCode) {

                resetSelect(
                    barangaySelect,
                    'Select barangay'
                );

                return;
            }


            try {

                const barangays =
                    await fetchJson(
                        `${PSGC_BASE}/cities-municipalities/${municipalityCode}/barangays/`
                    );

                barangays.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );


                fillSelect(
                    barangaySelect,
                    barangays,
                    'Select barangay',
                    selectedCode
                );


                if (selectedCode) {

                    barangayNameInput.value =
                        barangaySelect.options[
                            barangaySelect.selectedIndex
                        ]?.text || '';

                }

            } catch (error) {

                resetSelect(
                    barangaySelect,
                    'Unable to load barangays'
                );

            }

        }


        provinceSelect.addEventListener(
            'change',
            function () {

                provinceNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

                loadMunicipalities(
                    this.value
                );

            }
        );


        municipalitySelect.addEventListener(
            'change',
            function () {

                municipalityNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

                loadBarangays(
                    this.value
                );

            }
        );


        barangaySelect.addEventListener(
            'change',
            function () {

                barangayNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

            }
        );


        loadProvinces();

        // Start on step 1
        updateProgressBar(1);

    });
</script>

@endpush