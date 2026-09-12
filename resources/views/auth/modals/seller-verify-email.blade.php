@extends('layouts.app')

@section('title', 'Verify Seller Email | ShopHop')

@section('hideChrome', true)

@section('content')

<main
    class="relative min-h-screen overflow-hidden bg-gray-bg
           flex items-center justify-center
           px-4 py-8 sm:px-6 lg:px-8"
>

    {{-- Background decoration --}}
    <div
        class="pointer-events-none absolute -top-32 -right-24
               w-96 h-96 rounded-full bg-teal/10 blur-3xl"
    ></div>

    <div
        class="pointer-events-none absolute -bottom-32 -left-24
               w-96 h-96 rounded-full bg-navy/5 blur-3xl"
    ></div>


    {{-- Main container --}}
    <div
        class="relative z-10
               w-full max-w-5xl
               overflow-hidden
               rounded-3xl
               border border-white/80
               bg-white
               shadow-2xl shadow-navy/15"
    >

        {{-- Top accent --}}
        <div
            class="h-1.5
                   bg-linear-to-r
                   from-teal/70 via-teal to-teal-dark"
        ></div>


        <div class="grid lg:grid-cols-[4fr_6fr]">


            {{-- =====================================================
                LEFT — SELLER BRAND PANEL
            ====================================================== --}}

            <aside
                class="relative hidden lg:flex
                       min-h-155
                       overflow-hidden
                       bg-navy
                       px-9 py-10"
            >

                <div
                    class="pointer-events-none absolute inset-0
                           bg-linear-to-br
                           from-teal/10 via-transparent to-transparent"
                ></div>

                <div
                    class="pointer-events-none absolute inset-0
                           opacity-[0.05]
                           text-white
                           bg-[radial-gradient(currentColor_1px,transparent_1px)]
                           bg-size-[20px_20px]"
                ></div>


                <div
                    class="relative z-10
                           flex w-full flex-col justify-between"
                >

                    {{-- Brand --}}
                    <div class="flex items-center gap-3">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-12 h-12 object-contain"
                        >

                        <div>

                            <p
                                class="text-lg font-extrabold
                                       text-white tracking-tight"
                            >
                                ShopHop
                            </p>

                            <p
                                class="mt-1
                                       text-[7px]
                                       font-bold
                                       tracking-[0.24em]
                                       text-teal"
                            >
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Main seller copy --}}
                    <div>

                        <div
                            class="inline-flex items-center gap-2
                                   rounded-full
                                   bg-white/8
                                   px-3 py-1.5"
                        >

                            <x-lucide-store
                                class="w-3.5 h-3.5 text-teal"
                            />

                            <span
                                class="text-[9px]
                                       font-bold
                                       uppercase
                                       tracking-[0.14em]
                                       text-teal"
                            >
                                Seller Verification
                            </span>

                        </div>


                        <h2
                            class="mt-5
                                   text-[2rem]
                                   font-extrabold
                                   leading-[1.05]
                                   tracking-[-0.04em]"
                        >
                            <span class="block text-white">
                                Verify your email.
                            </span>

                            <span class="block text-teal mt-1">
                                Start selling securely.
                            </span>
                        </h2>


                        <p
                            class="mt-5
                                   max-w-xs
                                   text-[12px]
                                   leading-relaxed
                                   text-white/45"
                        >
                            Before your seller application is reviewed,
                            confirm that the email address you registered
                            really belongs to you.
                        </p>


                        {{-- Seller verification process --}}
                        <div class="mt-8 space-y-5">

                            {{-- Step 1 --}}
                            <div class="flex items-start gap-3">

                                <div
                                    class="flex w-9 h-9 shrink-0
                                           items-center justify-center
                                           rounded-full
                                           bg-teal
                                           text-white"
                                >
                                    <x-lucide-mail
                                        class="w-4 h-4"
                                    />
                                </div>

                                <div class="pt-0.5">

                                    <p
                                        class="text-xs
                                               font-semibold
                                               text-white"
                                    >
                                        Check your inbox
                                    </p>

                                    <p
                                        class="mt-1
                                               text-[10px]
                                               leading-relaxed
                                               text-white/35"
                                    >
                                        We sent a 6-digit code to
                                        your seller email.
                                    </p>

                                </div>

                            </div>


                            {{-- Step 2 --}}
                            <div class="flex items-start gap-3">

                                <div
                                    class="flex w-9 h-9 shrink-0
                                           items-center justify-center
                                           rounded-full
                                           border border-teal/30
                                           bg-teal/10
                                           text-teal"
                                >
                                    <x-lucide-key-round
                                        class="w-4 h-4"
                                    />
                                </div>

                                <div class="pt-0.5">

                                    <p
                                        class="text-xs
                                               font-semibold
                                               text-white"
                                    >
                                        Enter your code
                                    </p>

                                    <p
                                        class="mt-1
                                               text-[10px]
                                               leading-relaxed
                                               text-white/35"
                                    >
                                        Your verification code
                                        expires after 10 minutes.
                                    </p>

                                </div>

                            </div>


                            {{-- Step 3 --}}
                            <div class="flex items-start gap-3">

                                <div
                                    class="flex w-9 h-9 shrink-0
                                           items-center justify-center
                                           rounded-full
                                           border border-white/10
                                           bg-white/5
                                           text-white/40"
                                >
                                    <x-lucide-shield-check
                                        class="w-4 h-4"
                                    />
                                </div>

                                <div class="pt-0.5">

                                    <p
                                        class="text-xs
                                               font-semibold
                                               text-white"
                                    >
                                        Administrator review
                                    </p>

                                    <p
                                        class="mt-1
                                               text-[10px]
                                               leading-relaxed
                                               text-white/35"
                                    >
                                        After email verification,
                                        ShopHop will review your seller
                                        application and documents.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Security footer --}}
                    <div
                        class="border-t border-white/10
                               pt-5
                               flex items-center gap-2
                               text-[9px] text-white/30"
                    >

                        <x-lucide-lock-keyhole
                            class="w-3.5 h-3.5 text-teal"
                        />

                        <span>
                            Never share your verification code.
                        </span>

                    </div>

                </div>

            </aside>



            {{-- =====================================================
                RIGHT — OTP FORM
            ====================================================== --}}

            <section
                class="flex items-center
                       bg-linear-to-b
                       from-white via-white to-gray-bg/30
                       px-5 py-8
                       sm:px-10 sm:py-10
                       lg:px-14"
            >

                <div class="w-full max-w-lg mx-auto">


                    {{-- Mobile brand --}}
                    <div
                        class="lg:hidden
                               inline-flex items-center gap-3
                               mb-8
                               rounded-2xl
                               border border-gray-border/70
                               bg-white
                               px-3 py-2
                               shadow-sm"
                    >

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-9 h-9 object-contain"
                        >

                        <div>

                            <p
                                class="text-sm
                                       font-extrabold
                                       text-navy"
                            >
                                ShopHop
                            </p>

                            <p
                                class="mt-0.5
                                       text-[7px]
                                       font-semibold
                                       tracking-[0.2em]
                                       text-teal-dark"
                            >
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>



                    {{-- Header --}}
                    <div class="text-center">

                        <div
                            class="mx-auto
                                   flex w-15 h-15
                                   items-center justify-center
                                   rounded-2xl
                                   border border-teal/10
                                   bg-teal-light
                                   text-teal-dark
                                   shadow-sm"
                        >

                            <x-lucide-store
                                class="w-7 h-7"
                            />

                        </div>


                        <p
                            class="mt-5
                                   text-[10px]
                                   font-extrabold
                                   uppercase
                                   tracking-[0.16em]
                                   text-teal-dark"
                        >
                            Seller Email Verification
                        </p>


                        <h1
                            class="mt-2
                                   text-2xl sm:text-[1.8rem]
                                   font-extrabold
                                   tracking-tight
                                   text-navy"
                        >
                            Check your email
                        </h1>


                        <p
                            class="mt-3
                                   text-sm
                                   leading-relaxed
                                   text-navy/50"
                        >
                            We sent a 6-digit seller verification code to
                        </p>


                        <div
                            class="mt-2
                                   inline-flex items-center gap-2
                                   rounded-full
                                   bg-gray-bg
                                   border border-gray-border/70
                                   px-3 py-1.5"
                        >

                            <x-lucide-mail
                                class="w-3.5 h-3.5 text-teal-dark"
                            />

                            <span
                                class="text-xs
                                       font-semibold
                                       text-navy"
                            >
                                {{ $email }}
                            </span>

                        </div>

                    </div>



                    {{-- Status --}}
                    @if (session('status'))

                        <div
                            class="mt-6
                                   flex items-start gap-3
                                   rounded-2xl
                                   border border-teal/20
                                   bg-teal-light/40
                                   px-4 py-3.5"
                        >

                            <x-lucide-circle-check
                                class="mt-0.5
                                       w-4 h-4
                                       shrink-0
                                       text-teal-dark"
                            />

                            <p
                                class="text-xs
                                       leading-relaxed
                                       text-teal-dark"
                            >
                                {{ session('status') }}
                            </p>

                        </div>

                    @endif



                    {{-- Errors --}}
                    @if ($errors->any())

                        <div
                            class="mt-6
                                   flex items-start gap-3
                                   rounded-2xl
                                   border border-red-200
                                   bg-red-50
                                   px-4 py-3.5"
                        >

                            <x-lucide-circle-alert
                                class="mt-0.5
                                       w-4 h-4
                                       shrink-0
                                       text-red-500"
                            />

                            <div>

                                <p
                                    class="text-xs
                                           font-semibold
                                           text-red-700"
                                >
                                    Verification unsuccessful
                                </p>

                                <p
                                    class="mt-1
                                           text-[11px]
                                           leading-relaxed
                                           text-red-600"
                                >
                                    {{ $errors->first() }}
                                </p>

                            </div>

                        </div>

                    @endif



                    {{-- =================================================
                        VERIFY SELLER OTP
                    ================================================== --}}

                    <form
                        method="POST"
                        action="{{ route('seller.verify-email.verify') }}"
                        id="seller-email-verification-form"
                        class="mt-7"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="code"
                            id="seller-verification-code"
                            value="{{ old('code') }}"
                        >


                        <div
                            class="flex items-center
                                   justify-center
                                   gap-2 sm:gap-3"
                            id="seller-otp-inputs"
                        >

                            @for ($i = 0; $i < 6; $i++)

                                <input
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                    aria-label="Verification code digit {{ $i + 1 }}"
                                    data-seller-otp-input
                                    class="seller-otp-box
                                           w-11 h-13
                                           sm:w-13 sm:h-14
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           text-center
                                           text-xl sm:text-2xl
                                           font-extrabold
                                           text-navy
                                           outline-none
                                           shadow-sm
                                           hover:border-navy/20
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            @endfor

                        </div>


                        <div
                            class="mt-4
                                   flex items-center
                                   justify-center gap-2
                                   text-[11px]
                                   text-navy/40"
                        >

                            <x-lucide-clock-3
                                class="w-3.5 h-3.5"
                            />

                            <span>
                                Code expires in 10 minutes
                            </span>

                        </div>


                        <button
                            type="submit"
                            id="seller-verify-email-button"
                            disabled
                            class="mt-6
                                   inline-flex w-full
                                   min-h-12
                                   items-center justify-center gap-2
                                   rounded-2xl
                                   bg-teal
                                   px-5 py-3
                                   text-sm font-semibold
                                   text-white
                                   shadow-md shadow-teal/20
                                   hover:bg-teal-dark
                                   hover:-translate-y-0.5
                                   focus:outline-none
                                   focus:ring-4
                                   focus:ring-teal/15
                                   disabled:cursor-not-allowed
                                   disabled:opacity-45
                                   disabled:hover:translate-y-0
                                   transition-all duration-200"
                        >

                            <span>
                                Verify Seller Email
                            </span>

                            <x-lucide-arrow-right
                                class="w-4 h-4"
                            />

                        </button>

                    </form>



                    {{-- =================================================
                        RESEND
                    ================================================== --}}

                    <div
                        class="mt-6
                               border-t border-gray-border/70
                               pt-5
                               text-center"
                    >

                        <p
                            class="text-xs
                                   text-navy/45"
                        >
                            Didn't receive your verification code?
                        </p>


                        <form
                            method="POST"
                            action="{{ route('seller.verify-email.resend') }}"
                            class="mt-2"
                        >

                            @csrf

                            <button
                                type="submit"
                                id="seller-resend-code-button"
                                disabled
                                class="inline-flex
                                       items-center gap-1.5
                                       text-xs
                                       font-semibold
                                       text-teal-dark
                                       hover:text-navy
                                       disabled:cursor-not-allowed
                                       disabled:text-navy/30
                                       transition"
                            >

                                <x-lucide-rotate-cw
                                    class="w-3.5 h-3.5"
                                />

                                <span id="seller-resend-code-text">
                                    Resend code in 60s
                                </span>

                            </button>

                        </form>

                    </div>



                    {{-- Security note --}}
                    <div
                        class="mt-6
                               flex items-start gap-3
                               rounded-2xl
                               bg-gray-bg
                               px-4 py-3.5"
                    >

                        <x-lucide-shield-check
                            class="mt-0.5
                                   w-4 h-4
                                   shrink-0
                                   text-teal-dark"
                        />

                        <p
                            class="text-[11px]
                                   leading-relaxed
                                   text-navy/45"
                        >
                            ShopHop will never ask you to send your
                            verification code to another person.
                        </p>

                    </div>


                    {{-- Back --}}
                    <div class="mt-5 text-center">

                        <a
                            href="{{ route('home') }}"
                            class="inline-flex
                                   items-center gap-1.5
                                   text-[11px]
                                   font-medium
                                   text-navy/40
                                   hover:text-teal-dark
                                   transition"
                        >

                            <x-lucide-arrow-left
                                class="w-3.5 h-3.5"
                            />

                            Back to ShopHop

                        </a>

                    </div>

                </div>

            </section>

        </div>

    </div>

</main>

@endsection



{{-- =========================================================
    PAGE STYLES
========================================================= --}}

@push('styles')

<style>

    .seller-otp-box {
        caret-color: transparent;
    }

    @media (prefers-reduced-motion: no-preference) {

        .seller-otp-box {
            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                transform .2s ease;
        }

        .seller-otp-box:focus {
            transform: translateY(-2px);
        }

    }

</style>

@endpush



{{-- =========================================================
    PAGE SCRIPT
========================================================= --}}

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const otpInputs =
        Array.from(
            document.querySelectorAll(
                '[data-seller-otp-input]'
            )
        );

    const hiddenCodeInput =
        document.getElementById(
            'seller-verification-code'
        );

    const verificationForm =
        document.getElementById(
            'seller-email-verification-form'
        );

    const verifyButton =
        document.getElementById(
            'seller-verify-email-button'
        );

    const resendButton =
        document.getElementById(
            'seller-resend-code-button'
        );

    const resendText =
        document.getElementById(
            'seller-resend-code-text'
        );


    /*
    |--------------------------------------------------------------------------
    | GET FULL OTP
    |--------------------------------------------------------------------------
    */

    function getOtpValue() {

        return otpInputs
            .map(function (input) {
                return input.value;
            })
            .join('');

    }


    function updateOtpState() {

        const value = getOtpValue();

        hiddenCodeInput.value = value;

        verifyButton.disabled =
            !/^\d{6}$/.test(value);

    }


    /*
    |--------------------------------------------------------------------------
    | RESTORE OLD CODE
    |--------------------------------------------------------------------------
    */

    const oldCode =
        hiddenCodeInput.value || '';

    if (/^\d{1,6}$/.test(oldCode)) {

        oldCode
            .split('')
            .forEach(function (digit, index) {

                if (otpInputs[index]) {
                    otpInputs[index].value = digit;
                }

            });

    }

    updateOtpState();



    /*
    |--------------------------------------------------------------------------
    | OTP INPUT EVENTS
    |--------------------------------------------------------------------------
    */

    otpInputs.forEach(function (input, index) {

        input.addEventListener(
            'input',
            function () {

                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(-1);

                if (
                    this.value &&
                    index < otpInputs.length - 1
                ) {
                    otpInputs[index + 1].focus();
                }

                updateOtpState();

            }
        );


        input.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Backspace' &&
                    !this.value &&
                    index > 0
                ) {
                    otpInputs[index - 1].focus();
                }


                if (
                    event.key === 'ArrowLeft' &&
                    index > 0
                ) {
                    event.preventDefault();

                    otpInputs[index - 1].focus();
                }


                if (
                    event.key === 'ArrowRight' &&
                    index < otpInputs.length - 1
                ) {
                    event.preventDefault();

                    otpInputs[index + 1].focus();
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | PASTE COMPLETE OTP
        |--------------------------------------------------------------------------
        */

        input.addEventListener(
            'paste',
            function (event) {

                const pastedValue =
                    event.clipboardData
                        .getData('text')
                        .replace(/\D/g, '')
                        .slice(0, 6);

                if (!pastedValue) {
                    return;
                }

                event.preventDefault();

                otpInputs.forEach(function (otpInput) {
                    otpInput.value = '';
                });

                pastedValue
                    .split('')
                    .forEach(
                        function (digit, pastedIndex) {

                            if (otpInputs[pastedIndex]) {
                                otpInputs[pastedIndex].value =
                                    digit;
                            }

                        }
                    );

                updateOtpState();

                const focusIndex =
                    Math.min(
                        pastedValue.length,
                        otpInputs.length
                    ) - 1;

                if (focusIndex >= 0) {
                    otpInputs[focusIndex].focus();
                }

            }
        );

    });



    /*
    |--------------------------------------------------------------------------
    | SUBMIT GUARD
    |--------------------------------------------------------------------------
    */

    verificationForm.addEventListener(
        'submit',
        function (event) {

            updateOtpState();

            if (
                !/^\d{6}$/.test(
                    hiddenCodeInput.value
                )
            ) {

                event.preventDefault();

                otpInputs[0].focus();

            }

        }
    );



    /*
    |--------------------------------------------------------------------------
    | AUTOFOCUS FIRST EMPTY BOX
    |--------------------------------------------------------------------------
    */

    const firstEmptyInput =
        otpInputs.find(function (input) {
            return !input.value;
        });

    if (firstEmptyInput) {
        firstEmptyInput.focus();
    }



    /*
    |--------------------------------------------------------------------------
    | 60-SECOND RESEND TIMER
    |--------------------------------------------------------------------------
    */

    let remainingSeconds = 60;

    function updateResendTimer() {

        if (remainingSeconds <= 0) {

            resendButton.disabled = false;

            resendText.textContent =
                'Resend verification code';

            return;
        }

        resendButton.disabled = true;

        resendText.textContent =
            'Resend code in ' +
            remainingSeconds +
            's';

        remainingSeconds--;

        window.setTimeout(
            updateResendTimer,
            1000
        );

    }

    updateResendTimer();

});
</script>

@endpush