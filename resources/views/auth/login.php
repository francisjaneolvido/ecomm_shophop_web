@extends('layouts.app')

@section('title', 'Sign In — ShopHop')

@section('content')

<section class="min-h-[calc(100vh-56px)] bg-gray-bg flex items-center py-10 sm:py-14">

    <div class="w-full max-w-md mx-auto px-4 sm:px-6">

        {{-- Header --}}
        <div class="text-center mb-7">

            <div
                class="w-14 h-14 mx-auto
                       rounded-2xl
                       bg-teal-light
                       flex items-center justify-center
                       text-teal-dark
                       mb-4"
            >
                <x-lucide-user-round class="w-6 h-6" />
            </div>

            <p class="text-teal-dark text-xs font-semibold tracking-wide mb-2">
                WELCOME BACK
            </p>

            <h1 class="text-navy text-3xl sm:text-4xl">
                Sign In
            </h1>

            <p class="text-sm text-navy/50 mt-2">
                Access your ShopHop account.
            </p>

        </div>


        {{-- Login Card --}}
        <div
            class="bg-white
                   rounded-2xl sm:rounded-3xl
                   border border-gray-border
                   shadow-xl shadow-navy/5
                   overflow-hidden"
        >

            <div class="h-1.5 bg-teal"></div>

            <form
                action="#"
                method="POST"
                class="p-6 sm:p-8"
            >

                @csrf


                {{-- Email --}}
                <div class="mb-5">

                    <label
                        for="email"
                        class="block text-sm font-medium text-navy mb-2"
                    >
                        E-mail
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">

                        <x-lucide-mail
                            class="absolute left-4 top-1/2
                                   -translate-y-1/2
                                   w-4 h-4
                                   text-navy/30"
                        />

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="Enter your email"
                            class="w-full
                                   rounded-xl
                                   border border-gray-border
                                   bg-white
                                   pl-11 pr-4 py-3
                                   text-sm text-navy
                                   outline-none
                                   placeholder:text-navy/30
                                   focus:border-teal
                                   focus:ring-4 focus:ring-teal/10
                                   transition"
                        >

                    </div>

                </div>


                {{-- Password --}}
                <div class="mb-4">

                    <div class="flex items-center justify-between mb-2">

                        <label
                            for="password"
                            class="text-sm font-medium text-navy"
                        >
                            Password
                            <span class="text-red-500">*</span>
                        </label>

                        <a
                            href="#"
                            class="text-xs font-medium
                                   text-teal-dark
                                   hover:text-navy
                                   transition"
                        >
                            Forgot password?
                        </a>

                    </div>


                    <div class="relative">

                        <x-lucide-lock-keyhole
                            class="absolute left-4 top-1/2
                                   -translate-y-1/2
                                   w-4 h-4
                                   text-navy/30"
                        />

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full
                                   rounded-xl
                                   border border-gray-border
                                   bg-white
                                   pl-11 pr-11 py-3
                                   text-sm text-navy
                                   outline-none
                                   placeholder:text-navy/30
                                   focus:border-teal
                                   focus:ring-4 focus:ring-teal/10
                                   transition"
                        >


                        <button
                            type="button"
                            id="toggle-password"
                            aria-label="Show password"
                            class="absolute right-3 top-1/2
                                   -translate-y-1/2
                                   w-8 h-8
                                   flex items-center justify-center
                                   rounded-lg
                                   text-navy/35
                                   hover:text-teal-dark
                                   hover:bg-gray-bg
                                   transition"
                        >
                            <x-lucide-eye
                                id="password-eye"
                                class="w-4 h-4"
                            />
                        </button>

                    </div>

                </div>


                {{-- Remember Me --}}
                <label
                    class="inline-flex items-center gap-2
                           text-xs sm:text-sm
                           text-navy/60
                           cursor-pointer"
                >

                    <input
                        type="checkbox"
                        name="remember"
                        class="w-4 h-4
                               rounded
                               border-gray-border
                               text-teal
                               focus:ring-teal/20"
                    >

                    Remember me

                </label>


                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full
                           mt-6
                           inline-flex items-center justify-center gap-2
                           bg-teal hover:bg-teal-dark
                           text-white
                           text-sm font-semibold
                           px-6 py-3.5
                           rounded-full
                           shadow-lg shadow-teal/20
                           hover:-translate-y-0.5
                           transition-all duration-300"
                >
                    Sign In

                    <x-lucide-arrow-right class="w-4 h-4" />
                </button>


                {{-- Register --}}
                <div class="text-center mt-6">

                    <p class="text-xs sm:text-sm text-navy/45">
                        Don't have an account?

                        <a
                            href="{{ route('register') }}"
                            class="font-semibold
                                   text-teal-dark
                                   hover:text-navy
                                   transition"
                        >
                            Create Account
                        </a>
                    </p>

                </div>

            </form>

        </div>


        {{-- Approval reminder --}}
        <div
            class="mt-5
                   flex gap-3
                   rounded-xl
                   border border-teal/15
                   bg-teal-light/40
                   p-4"
        >

            <x-lucide-info
                class="w-4 h-4
                       text-teal-dark
                       shrink-0
                       mt-0.5"
            />

            <p class="text-xs text-navy/55 leading-relaxed">
                Newly registered accounts must first be approved by the
                administrator before they can sign in.
            </p>

        </div>

    </div>

</section>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const password =
            document.getElementById('password');

        const toggle =
            document.getElementById('toggle-password');


        if (password && toggle) {

            toggle.addEventListener('click', function () {

                if (password.type === 'password') {

                    password.type = 'text';

                    toggle.setAttribute(
                        'aria-label',
                        'Hide password'
                    );

                } else {

                    password.type = 'password';

                    toggle.setAttribute(
                        'aria-label',
                        'Show password'
                    );

                }

            });

        }

    });
</script>

@endpush