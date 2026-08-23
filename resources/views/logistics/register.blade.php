@extends('layouts.app')

@section('title', 'Create Account — ShopHop')

{{-- Hides the navbar/footer on this page, same as auth/register.blade.php. --}}
@section('hideChrome', true)

@section('content')

{{--
    ============================================================
    UX-ONLY MOCKUP — no backend wiring yet
    ============================================================
    This page is the lightweight "create your login" flow:
    Email → Email Verification → OTP → Password.

    Nothing here talks to a server:
      - "Continue" on Step 1 does NOT send a verification email.
      - The OTP step does NOT check the code against anything —
        any 6 digits will pass.
      - "Create Account" does NOT hit an endpoint — it just reveals
        the success panel at the bottom of this file.

    To make this real later, you'll want to wire up:
      - POST to send the verification code (Step 1 → Step 2)
      - POST to verify the OTP (Step 3 → Step 4)
      - POST to actually create the account (Step 4 → success)
      - Resend-code endpoint (see #resend-code in the script)
============================================================ --}}

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

        /* OTP boxes should never show number spinners / autofill icons. */
        .otp-box::-webkit-outer-spin-button,
        .otp-box::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
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
                RIGHT PANEL — ACCOUNT CREATION FLOW
            ====================================================== --}}
            <div
                class="bg-white
                       px-4 sm:px-8 xl:px-10
                       py-8 sm:py-10 lg:py-12"
            >

                <div id="account-panel" class="max-w-xl mx-auto">


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
                    <div class="mb-7" data-form-chrome>

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
                            Let's get you started
                        </h2>

                        <p
                            class="text-sm
                                   text-navy/50
                                   mt-2
                                   leading-relaxed"
                        >
                            Just a few quick steps to set up your ShopHop account.
                        </p>

                    </div>


                    {{-- =================================================
                        STEP PROGRESS BAR
                    ================================================== --}}
                    <div class="mb-8" data-form-chrome>

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
                                Email
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="2"
                            >
                                Verify Email
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="3"
                            >
                                Enter Code
                            </p>

                            <div></div>

                            <p
                                class="step-label max-w-[70px] mx-auto text-center text-[10px] sm:text-[11px] font-medium leading-tight text-navy/30 transition-colors duration-300"
                                data-step-label="4"
                            >
                                Password
                            </p>

                        </div>

                    </div>


                    <form id="create-account-form" onsubmit="return false;">


                        {{-- =================================================
                            STEP 1 — EMAIL
                        ================================================== --}}
                        <div data-step-panel="1">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-mail class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Your email address</p>
                            </div>

                            <label
                                for="signup_email"
                                class="block text-xs font-medium text-navy mb-2"
                            >
                                E-mail
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="email"
                                id="signup_email"
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

                            <p id="signup_email_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                            <p class="text-[11px] text-navy/40 mt-2">
                                We'll send a 6-digit code to this address to confirm it's yours.
                            </p>


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
                                    Continue

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 2 — EMAIL VERIFICATION (INTERSTITIAL)
                        ================================================== --}}
                        <div data-step-panel="2" class="hidden">

                            <div class="flex flex-col items-center text-center py-2">

                                <div class="w-16 h-16 rounded-2xl bg-teal-light flex items-center justify-center text-teal-dark mb-5">
                                    <x-lucide-mail-check class="w-8 h-8" />
                                </div>

                                <p class="text-navy font-bold text-lg">
                                    Check your inbox
                                </p>

                                <p class="text-sm text-navy/50 mt-2 leading-relaxed max-w-xs">
                                    We sent a 6-digit verification code to
                                    <span class="font-semibold text-navy" id="verify-email-display">your@email.com</span>.
                                    Enter it on the next step to continue.
                                </p>

                                <button
                                    type="button"
                                    id="step2-edit-email"
                                    class="text-xs font-semibold text-teal-dark hover:text-navy transition mt-4"
                                >
                                    Wrong email? Edit it
                                </button>

                            </div>


                            {{-- Step 2 navigation --}}
                            <div class="flex items-center gap-3 mt-6">

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
                                    I've got my code

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 3 — OTP
                        ================================================== --}}
                        <div data-step-panel="3" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Enter verification code</p>
                            </div>

                            <p class="text-xs text-navy/50 mb-4">
                                Enter the 6-digit code we sent to
                                <span class="font-semibold text-navy" id="otp-email-display">your@email.com</span>.
                            </p>

                            <div class="flex items-center justify-between gap-2 sm:gap-3" id="otp-inputs">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">

                            </div>

                            <p id="otp_error" class="hidden text-[11px] text-red-500 mt-2"></p>

                            <div class="flex items-center justify-between mt-4">

                                <p class="text-[11px] text-navy/40">
                                    Didn't get a code?
                                </p>

                                <button
                                    type="button"
                                    id="resend-code"
                                    disabled
                                    class="text-[11px] font-semibold text-navy/30 transition"
                                >
                                    <span data-resend-label>Resend in</span> <span id="resend-timer">00:30</span>
                                </button>

                            </div>


                            {{-- Step 3 navigation --}}
                            <div class="flex items-center gap-3 mt-6">

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
                                    Verify Code

                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            STEP 4 — PASSWORD
                        ================================================== --}}
                        <div data-step-panel="4" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-lock class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Set your password</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label
                                        for="signup_password"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="signup_password"
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
                                            id="toggle_signup_password"
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

                                    <p id="signup_password_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>


                                <div>

                                    <label
                                        for="signup_password_confirmation"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            id="signup_password_confirmation"
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
                                            id="toggle_signup_password_confirmation"
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

                                    <p id="signup_password_confirmation_error" class="hidden text-[11px] text-red-500 mt-1.5"></p>

                                </div>

                            </div>

                            {{-- Password requirements checklist --}}
                            <div
                                id="signup-password-requirements"
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

                            {{-- Terms & Agreement --}}
                            <div class="mt-5">

                                <label
                                    for="signup_terms"
                                    class="flex items-start gap-3 cursor-pointer group"
                                >

                                    <input
                                        type="checkbox"
                                        id="signup_terms"
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
                                        I agree to ShopHop's
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

                                <p id="signup_terms_error" class="hidden text-[11px] text-red-500 mt-1.5 ml-8"></p>

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
                                    type="button"
                                    id="create-account-btn"
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


                        {{-- =================================================
                            SUCCESS STATE (shown after Step 4, UX-only)
                        ================================================== --}}
                        <div data-success-panel class="hidden text-center py-4">

                            <div class="w-16 h-16 rounded-2xl bg-teal-light flex items-center justify-center text-teal-dark mx-auto mb-5">
                                <x-lucide-check class="w-8 h-8" />
                            </div>

                            <p class="text-navy font-bold text-xl">
                                You're all set!
                            </p>

                            <p class="text-sm text-navy/50 mt-2 leading-relaxed max-w-xs mx-auto">
                                Your ShopHop account has been created. Start discovering
                                products and deals made for you.
                            </p>

                            <a
                                href="#"
                                class="inline-flex
                                       items-center justify-center gap-2
                                       bg-teal
                                       hover:bg-teal-dark
                                       text-white
                                       text-sm font-semibold
                                       py-3.5 px-8
                                       rounded-xl
                                       shadow-lg shadow-teal/20
                                       hover:-translate-y-0.5
                                       transition-all duration-300
                                       mt-6"
                            >
                                Start Shopping

                                <x-lucide-arrow-right class="w-4 h-4" />
                            </a>

                        </div>


                        {{-- Sign in --}}
                        <div class="text-center mt-6" data-form-chrome>

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

        /*
        |--------------------------------------------------------------------------
        | ELEMENT REFS
        |--------------------------------------------------------------------------
        */

        const accountPanel = document.getElementById('account-panel');

        const emailInput = document.getElementById('signup_email');
        const emailError = document.getElementById('signup_email_error');

        const verifyEmailDisplay = document.getElementById('verify-email-display');
        const otpEmailDisplay = document.getElementById('otp-email-display');

        const otpInputs = Array.from(document.querySelectorAll('[data-otp-digit]'));
        const otpError = document.getElementById('otp_error');

        const resendBtn = document.getElementById('resend-code');
        const resendLabel = resendBtn.querySelector('[data-resend-label]');
        const resendTimer = document.getElementById('resend-timer');

        const passwordInput = document.getElementById('signup_password');
        const passwordError = document.getElementById('signup_password_error');

        const passwordConfirmInput = document.getElementById('signup_password_confirmation');
        const passwordConfirmError = document.getElementById('signup_password_confirmation_error');

        const termsInput = document.getElementById('signup_terms');
        const termsError = document.getElementById('signup_terms_error');

        const successPanel = document.querySelector('[data-success-panel]');
        const formChromeEls = document.querySelectorAll('[data-form-chrome]');
        const createAccountForm = document.getElementById('create-account-form');


        /*
        |--------------------------------------------------------------------------
        | SHARED HELPERS (same pattern used elsewhere on the site)
        |--------------------------------------------------------------------------
        */

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const passwordUppercaseRegex = /[A-Z]/;
        const passwordLowercaseRegex = /[a-z]/;
        const passwordNumberRegex = /[0-9]/;
        const passwordSpecialRegex = /[!@#$%^&*]/;

        function showError(input, errorEl, message) {

            if (!errorEl) return;

            if (message) {

                errorEl.textContent = message;
                errorEl.classList.remove('hidden');

                if (input) {
                    input.classList.add('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.remove('border-gray-border', 'focus:border-teal', 'focus:ring-teal/10');
                }

            } else {

                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                if (input) {
                    input.classList.remove('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.add('border-gray-border', 'focus:border-teal', 'focus:ring-teal/10');
                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | STEP NAVIGATION (same wizard pattern as auth/register.blade.php)
        |--------------------------------------------------------------------------
        */

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
                panel.classList.toggle('hidden', parseInt(panel.dataset.stepPanel, 10) !== step);
            });

            updateProgressBar(step);

            if (accountPanel) {
                accountPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        }


        /*
        |--------------------------------------------------------------------------
        | STEP 1 — EMAIL
        |--------------------------------------------------------------------------
        */

        document.getElementById('step1-next').addEventListener('click', function () {

            const value = emailInput.value.trim();

            if (!value) {
                showError(emailInput, emailError, 'Please enter your email address.');
                return;
            }

            if (!emailRegex.test(value)) {
                showError(emailInput, emailError, 'Please enter a valid email address (e.g. juan@example.com).');
                return;
            }

            showError(emailInput, emailError, '');

            // Mockup only — a real send-verification-code request would go here.
            if (verifyEmailDisplay) verifyEmailDisplay.textContent = value;
            if (otpEmailDisplay) otpEmailDisplay.textContent = value;

            goToStep(2);

        });

        emailInput.addEventListener('input', function () {
            if (!emailError.classList.contains('hidden')) {
                showError(emailInput, emailError, '');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | STEP 2 — EMAIL VERIFICATION (INTERSTITIAL)
        |--------------------------------------------------------------------------
        */

        document.getElementById('step2-edit-email').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('step2-back').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('step2-next').addEventListener('click', function () {
            goToStep(3);
            startResendCountdown();
            if (otpInputs[0]) otpInputs[0].focus();
        });


        /*
        |--------------------------------------------------------------------------
        | STEP 3 — OTP
        |--------------------------------------------------------------------------
        */

        otpInputs.forEach(function (input, index) {

            input.addEventListener('input', function () {

                this.value = this.value.replace(/\D/g, '').slice(0, 1);

                if (this.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                if (!otpError.classList.contains('hidden')) {
                    showError(null, otpError, '');
                }

            });

            input.addEventListener('keydown', function (e) {

                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }

            });

            input.addEventListener('paste', function (e) {

                e.preventDefault();

                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text')
                    .replace(/\D/g, '')
                    .slice(0, otpInputs.length);

                pasted.split('').forEach(function (digit, i) {
                    if (otpInputs[i]) otpInputs[i].value = digit;
                });

                const nextEmpty = otpInputs.findIndex(function (box) { return !box.value; });

                (otpInputs[nextEmpty] || otpInputs[otpInputs.length - 1]).focus();

            });

        });

        function otpValue() {
            return otpInputs.map(function (input) { return input.value; }).join('');
        }

        document.getElementById('step3-back').addEventListener('click', function () {
            goToStep(2);
        });

        document.getElementById('step3-next').addEventListener('click', function () {

            const code = otpValue();

            if (code.length < otpInputs.length) {
                showError(null, otpError, 'Please enter the full 6-digit code.');
                return;
            }

            // Mockup only — the code isn't actually checked against anything yet.
            showError(null, otpError, '');

            goToStep(4);

        });


        // Resend countdown — UX only, doesn't actually resend anything yet.
        let resendInterval = null;

        function startResendCountdown() {

            let secondsLeft = 30;

            resendBtn.disabled = true;
            resendBtn.classList.add('text-navy/30');
            resendBtn.classList.remove('text-teal-dark', 'hover:text-navy', 'cursor-pointer');

            resendLabel.textContent = 'Resend in';
            resendTimer.classList.remove('hidden');

            if (resendInterval) clearInterval(resendInterval);

            function render() {
                const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
                const secs = String(secondsLeft % 60).padStart(2, '0');
                resendTimer.textContent = `${mins}:${secs}`;
            }

            render();

            resendInterval = setInterval(function () {

                secondsLeft--;

                if (secondsLeft <= 0) {

                    clearInterval(resendInterval);

                    resendBtn.disabled = false;
                    resendLabel.textContent = 'Resend code';
                    resendTimer.classList.add('hidden');
                    resendBtn.classList.remove('text-navy/30');
                    resendBtn.classList.add('text-teal-dark', 'hover:text-navy', 'cursor-pointer');

                    return;

                }

                render();

            }, 1000);

        }

        resendBtn.addEventListener('click', function () {

            if (resendBtn.disabled) return;

            // Mockup only — a real resend-code request would go here.
            otpInputs.forEach(function (input) { input.value = ''; });
            if (otpInputs[0]) otpInputs[0].focus();

            startResendCountdown();

        });


        /*
        |--------------------------------------------------------------------------
        | STEP 4 — PASSWORD
        |--------------------------------------------------------------------------
        */

        function updatePasswordRequirements(value) {

            const checks = {
                length: value.length >= 8,
                uppercase: passwordUppercaseRegex.test(value),
                lowercase: passwordLowercaseRegex.test(value),
                number: passwordNumberRegex.test(value),
                special: passwordSpecialRegex.test(value),
            };

            Object.keys(checks).forEach(function (key) {

                const item = document.querySelector(`#signup-password-requirements [data-req="${key}"]`);

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

        function validatePasswordField() {

            const value = passwordInput.value;

            if (!value) {
                showError(passwordInput, passwordError, 'Password is required.');
                return false;
            }

            if (value.length < 8) {
                showError(passwordInput, passwordError, 'Password must be at least 8 characters long.');
                return false;
            }

            if (!passwordUppercaseRegex.test(value)) {
                showError(passwordInput, passwordError, 'Password must contain at least 1 uppercase letter (A–Z).');
                return false;
            }

            if (!passwordLowercaseRegex.test(value)) {
                showError(passwordInput, passwordError, 'Password must contain at least 1 lowercase letter (a–z).');
                return false;
            }

            if (!passwordNumberRegex.test(value)) {
                showError(passwordInput, passwordError, 'Password must contain at least 1 number (0–9).');
                return false;
            }

            showError(passwordInput, passwordError, '');
            return true;

        }

        function validatePasswordConfirmation() {

            const value = passwordConfirmInput.value;

            if (!value) {
                showError(passwordConfirmInput, passwordConfirmError, 'Please confirm your password.');
                return false;
            }

            if (value !== passwordInput.value) {
                showError(passwordConfirmInput, passwordConfirmError, 'Passwords do not match.');
                return false;
            }

            showError(passwordConfirmInput, passwordConfirmError, '');
            return true;

        }

        function validateTerms() {

            if (!termsInput.checked) {
                showError(termsInput, termsError, 'You must agree to the Terms and Conditions.');
                return false;
            }

            showError(termsInput, termsError, '');
            return true;

        }

        passwordInput.addEventListener('input', function () {

            updatePasswordRequirements(passwordInput.value);
            validatePasswordField();

            if (passwordConfirmInput.value) {
                validatePasswordConfirmation();
            }

        });

        passwordConfirmInput.addEventListener('input', validatePasswordConfirmation);

        document.getElementById('step4-back').addEventListener('click', function () {
            goToStep(3);
        });

        document.getElementById('create-account-btn').addEventListener('click', function () {

            const isPasswordValid = validatePasswordField();
            const isPasswordConfirmValid = validatePasswordConfirmation();
            const isTermsValid = validateTerms();

            if (!isPasswordValid || !isPasswordConfirmValid || !isTermsValid) {
                return;
            }

            // Mockup only — a real "create account" request would go here.
            // On success, show the confirmation panel instead of the wizard.
            document.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                panel.classList.add('hidden');
            });

            formChromeEls.forEach(function (el) { el.classList.add('hidden'); });

            if (successPanel) successPanel.classList.remove('hidden');

            if (accountPanel) {
                accountPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

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

        setupPasswordToggle(passwordInput, document.getElementById('toggle_signup_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('toggle_signup_password_confirmation'));


        // Start on step 1
        updateProgressBar(1);

    });
</script>

@endpush