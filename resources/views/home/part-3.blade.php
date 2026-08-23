{{-- =========================================================
    NEW ARRIVALS
========================================================= --}}
<section
    id="new-arrivals"
    class="py-12 sm:py-16 lg:py-20 bg-gray-bg"
>

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">


        {{-- Header --}}
        <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">

            <p
                class="text-teal-dark
                       text-xs sm:text-sm
                       font-semibold
                       mb-2
                       tracking-wide"
            >
                JUST IN
            </p>


            <h2 class="text-navy">
                New Arrivals
            </h2>


            <p class="text-sm sm:text-base text-navy/55 mt-2">
                Fresh products and exciting finds added to ShopHop.
            </p>

        </div>



        {{-- Main card --}}
        <div
            class="bg-white
                   rounded-2xl sm:rounded-3xl
                   border border-gray-border
                   p-5 sm:p-8 lg:p-12"
        >

            <div class="grid lg:grid-cols-2 gap-8 sm:gap-10 items-center">


                {{-- Content --}}
                <div>

                    <span
                        class="inline-block
                               bg-teal-light
                               text-teal-dark
                               text-xs font-semibold
                               px-3 py-1.5
                               rounded-full
                               mb-4 sm:mb-5"
                    >
                        NEW THIS WEEK
                    </span>


                    <h2 class="text-navy">

                        Find something

                        <span class="text-teal">
                            you'll love.
                        </span>

                    </h2>


                    <p
                        class="text-sm sm:text-base
                               text-navy/55
                               mt-4
                               leading-relaxed
                               max-w-lg"
                    >
                        From everyday essentials to the latest trends,
                        there's always something new waiting for you.
                    </p>


                    <a
                        href="#trending"
                        class="inline-flex items-center gap-2
                               mt-5 sm:mt-6
                               text-sm font-semibold
                               text-teal-dark
                               hover:text-navy
                               transition"
                    >

                        Discover new arrivals

                        <x-lucide-arrow-right class="w-4 h-4" />

                    </a>

                </div>



                {{-- Images --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">


                    <div class="aspect-square rounded-2xl overflow-hidden bg-gray-bg">

                        <img
                            src="{{ asset('images/hero/earbuds.jpg') }}"
                            alt="New earbuds"
                            class="w-full h-full
                                   object-cover
                                   hover:scale-105
                                   transition-transform duration-500"
                        >

                    </div>


                    <div
                        class="aspect-square
                               rounded-2xl
                               overflow-hidden
                               bg-gray-bg
                               mt-5 sm:mt-8"
                    >

                        <img
                            src="{{ asset('images/hero/watch.jpg') }}"
                            alt="New fitness watch"
                            class="w-full h-full
                                   object-cover
                                   hover:scale-105
                                   transition-transform duration-500"
                        >

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
    FINAL CTA
========================================================= --}}
<section class="py-12 sm:py-16 lg:py-20 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative
                   overflow-hidden
                   rounded-2xl sm:rounded-3xl
                   bg-[#E9F8F4]
                   px-5 sm:px-8 lg:px-16
                   py-10 sm:py-12 lg:py-14
                   text-center"
        >


            {{-- Decorations --}}
            <div
                class="absolute
                       -left-20 -top-20
                       w-40 sm:w-48
                       h-40 sm:h-48
                       rounded-full
                       bg-teal/10"
            ></div>

            <div
                class="absolute
                       -right-20 -bottom-20
                       w-40 sm:w-48
                       h-40 sm:h-48
                       rounded-full
                       bg-teal/10"
            ></div>



            <div class="relative max-w-2xl mx-auto">

                <p
                    class="text-teal-dark
                           text-xs sm:text-sm
                           font-semibold
                           mb-3
                           tracking-wide"
                >
                    SHOPHOP
                </p>


                <h2 class="text-navy">
                    Ready to Hop In?
                </h2>


                <p class="text-sm sm:text-base text-navy/55 mt-3 mb-6 sm:mb-7">
                    Discover your next favorite product today.
                </p>


                <a
                    href="#trending"
                    data-login-required
                    class="inline-flex
                           items-center justify-center gap-2
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           text-sm font-semibold
                           px-6 sm:px-7
                           py-3 sm:py-3.5
                           rounded-full
                           transition-all duration-300
                           hover:-translate-y-0.5
                           shadow-lg shadow-teal/20"
                >

                    Start Shopping

                    <x-lucide-arrow-right class="w-4 h-4" />

                </a>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
    LOGIN MODAL
    UI/UX improved version.
    Keeps the same IDs/data attributes so your existing modal JS
    can continue to open/close/toggle the password field.
========================================================= --}}
<div
    id="login-modal"
    class="fixed inset-0 z-100 hidden items-end sm:items-center justify-center sm:p-6"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-login-modal-close
        aria-label="Close sign in modal"
        class="absolute inset-0 w-full h-full
               bg-navy/65 backdrop-blur-sm
               cursor-default"
    ></button>

    {{-- Dialog --}}
    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="login-modal-title"
        aria-describedby="login-modal-description"
        class="relative z-10
               w-full sm:max-w-md
               max-h-[92vh] sm:max-h-[calc(100vh-3rem)]
               overflow-y-auto
               rounded-t-3xl sm:rounded-3xl
               bg-white
               border border-gray-border
               shadow-2xl shadow-navy/25"
    >
        {{-- Mobile drag handle --}}
        <div class="sm:hidden flex justify-center pt-3">
            <div class="w-10 h-1 rounded-full bg-navy/10"></div>
        </div>

        {{-- Close --}}
        <button
            type="button"
            data-login-modal-close
            aria-label="Close sign in modal"
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

        {{-- Accent --}}
        <div class="hidden sm:block h-1.5 bg-teal"></div>

        <div class="px-5 pb-6 pt-5 sm:p-8">

            {{-- Header --}}
            <div class="pr-12 mb-6 sm:pr-10">
                <div
                    class="w-11 h-11
                           rounded-xl
                           bg-teal-light
                           flex items-center justify-center
                           text-teal-dark
                           mb-4"
                >
                    <x-lucide-user-round class="w-5 h-5" />
                </div>

                <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-1.5">
                    WELCOME TO SHOPHOP
                </p>

                <h2
                    id="login-modal-title"
                    class="text-navy text-2xl sm:text-3xl font-bold leading-tight"
                >
                    Sign in to continue
                </h2>

                <p
                    id="login-modal-description"
                    class="text-sm text-navy/50 mt-2 leading-relaxed"
                >
                    Save favorites, add items to your cart, and continue shopping.
                </p>
            </div>

            {{--
                Front-end only for now.
                When Google auth is ready, connect this button to your Google route.
            --}}
            <button
                type="button"
                id="google-login-button"
                class="w-full min-h-12
                       inline-flex items-center justify-center gap-3
                       rounded-xl
                       border border-gray-border
                       bg-white
                       px-5 py-3
                       text-sm font-semibold text-navy
                       shadow-sm
                       hover:bg-gray-bg
                       hover:border-navy/15
                       focus:outline-none
                       focus:ring-4 focus:ring-navy/5
                       transition"
            >
                {{-- Google "G" --}}
                <svg
                    class="w-5 h-5 shrink-0"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.23-.2-1.77H12v3.4h5.52a4.74 4.74 0 0 1-2.05 3.02l-.02.11 2.98 2.31.21.02c1.91-1.76 2.96-4.36 2.96-7.09Z"/>
                    <path fill="#34A853" d="M12 22c2.72 0 5-.9 6.67-2.45l-3.18-2.46c-.85.57-1.99.97-3.49.97-2.62 0-4.85-1.77-5.65-4.22l-.1.01-3.1 2.4-.04.1A10.08 10.08 0 0 0 12 22Z"/>
                    <path fill="#FBBC05" d="M6.35 13.84A6.05 6.05 0 0 1 6.01 12c0-.64.12-1.26.33-1.84l-.01-.12-3.14-2.44-.1.05A10.02 10.02 0 0 0 2 12c0 1.57.38 3.06 1.1 4.35l3.25-2.51Z"/>
                    <path fill="#EA4335" d="M12 5.94c1.89 0 3.16.82 3.89 1.5l2.84-2.77C17 3.06 14.72 2 12 2a10.08 10.08 0 0 0-8.9 5.65l3.24 2.51C7.15 7.71 9.38 5.94 12 5.94Z"/>
                </svg>

                Continue with Google
            </button>

            {{-- Divider --}}
            <div class="flex items-center gap-3 my-5">
                <div class="h-px flex-1 bg-gray-border"></div>

                <span class="text-[11px] font-medium text-navy/35 whitespace-nowrap">
                    or use your email
                </span>

                <div class="h-px flex-1 bg-gray-border"></div>
            </div>

            {{--
                Front-end modal only for now.
                When your authentication backend is ready, replace action="#"
                with your real login POST route and remove the JS preventDefault.
            --}}
            <form
                id="home-login-form"
                action="#"
                method="POST"
            >
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label
                        for="login-modal-email"
                        class="block text-xs font-semibold text-navy mb-2"
                    >
                        Email address
                    </label>

                    <div class="relative">
                        <x-lucide-mail
                            class="pointer-events-none
                                   absolute left-4 top-1/2
                                   -translate-y-1/2
                                   w-4 h-4
                                   text-navy/30"
                        />

                        <input
                            type="email"
                            id="login-modal-email"
                            name="email"
                            required
                            autocomplete="email"
                            inputmode="email"
                            placeholder="you@example.com"
                            class="w-full min-h-12
                                   rounded-xl
                                   border border-gray-border
                                   bg-white
                                   pl-11 pr-4 py-3
                                   text-sm text-navy
                                   outline-none
                                   placeholder:text-navy/30
                                   hover:border-navy/20
                                   focus:border-teal
                                   focus:ring-4 focus:ring-teal/10
                                   transition"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label
                        for="login-modal-password"
                        class="block text-xs font-semibold text-navy mb-2"
                    >
                        Password
                    </label>

                    <div class="relative">
                        <x-lucide-lock-keyhole
                            class="pointer-events-none
                                   absolute left-4 top-1/2
                                   -translate-y-1/2
                                   w-4 h-4
                                   text-navy/30"
                        />

                        <input
                            type="password"
                            id="login-modal-password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full min-h-12
                                   rounded-xl
                                   border border-gray-border
                                   bg-white
                                   pl-11 pr-12 py-3
                                   text-sm text-navy
                                   outline-none
                                   placeholder:text-navy/30
                                   hover:border-navy/20
                                   focus:border-teal
                                   focus:ring-4 focus:ring-teal/10
                                   transition"
                        >

                        <button
                            type="button"
                            id="login-modal-toggle-password"
                            aria-label="Show password"
                            class="absolute right-2 top-1/2
                                   -translate-y-1/2
                                   w-10 h-10
                                   rounded-lg
                                   flex items-center justify-center
                                   text-navy/35
                                   hover:text-teal-dark
                                   hover:bg-gray-bg
                                   focus:outline-none
                                   focus:ring-4 focus:ring-teal/10
                                   transition"
                        >
                            <x-lucide-eye class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- Remember / Forgot --}}
                <div class="flex items-center justify-between gap-4 mb-5">
                    <label
                        class="inline-flex items-center gap-2
                               text-xs sm:text-sm
                               text-navy/55
                               cursor-pointer select-none"
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

                    <a
                        href="#"
                        class="text-xs sm:text-sm font-semibold
                               text-teal-dark
                               hover:text-navy
                               focus:outline-none
                               focus:underline
                               transition"
                    >
                        Forgot password?
                    </a>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full min-h-12
                           inline-flex items-center justify-center gap-2
                           rounded-xl
                           bg-teal
                           px-6 py-3
                           text-sm font-semibold text-white
                           shadow-lg shadow-teal/20
                           hover:bg-teal-dark
                           hover:-translate-y-0.5
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/20
                           active:translate-y-0
                           transition-all duration-200"
                >
                    Sign In
                    <x-lucide-arrow-right class="w-4 h-4" />
                </button>

                <p
                    id="home-login-form-message"
                    class="hidden mt-3
                           rounded-xl
                           bg-gray-bg
                           px-4 py-3
                           text-center text-xs text-navy/50"
                >
                    The sign-in modal is ready. Connect this form to your login backend when authentication is available.
                </p>
            </form>

            {{-- Register --}}
            <div
                class="mt-5
                       rounded-xl
                       bg-gray-bg
                       px-4 py-3.5
                       text-center"
            >
                <p class="text-xs sm:text-sm text-navy/50">
                    New to ShopHop?

                    <a
                        href="{{ route('register') }}"
                        class="ml-1 font-semibold
                               text-teal-dark
                               hover:text-navy
                               focus:outline-none
                               focus:underline
                               transition"
                    >
                        Create an account
                    </a>
                </p>
            </div>

            {{-- Approval reminder --}}
            <div
                class="mt-4
                       flex gap-3
                       rounded-xl
                       border border-teal/15
                       bg-teal-light/35
                       p-3.5"
            >
                <x-lucide-info
                    class="w-4 h-4
                           text-teal-dark
                           shrink-0
                           mt-0.5"
                />

                <p class="text-[11px] sm:text-xs text-navy/50 leading-relaxed">
                    New accounts need administrator approval before sign in becomes available.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================
    HERO / CATEGORY ANIMATIONS
========================================================= --}}
@push('styles')
<style>
    @keyframes shopHopFloat {
        0%, 100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes categorySlideshow {
        0% {
            opacity: 0;
            transform: scale(1.03);
        }

        4% {
            opacity: 1;
            transform: scale(1);
        }

        21% {
            opacity: 1;
            transform: scale(1);
        }

        25% {
            opacity: 0;
            transform: scale(1.02);
        }

        100% {
            opacity: 0;
        }
    }

    .category-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        animation: categorySlideshow 12s ease-in-out infinite;
    }

    .hero-main-product {
        animation: shopHopFloat 5s ease-in-out infinite;
    }

    .hero-floating-card {
        animation: shopHopFloat 4s ease-in-out infinite;
    }

    .hero-delay {
        animation-delay: 1.5s;
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-main-product,
        .hero-floating-card,
        .category-slide {
            animation: none;
        }

        .category-slide:first-child {
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('login-modal');
        const emailInput = document.getElementById('login-modal-email');
        const passwordInput = document.getElementById('login-modal-password');
        const togglePassword = document.getElementById('login-modal-toggle-password');
        const loginForm = document.getElementById('home-login-form');
        const formMessage = document.getElementById('home-login-form-message');
        let lastFocusedElement = null;

        if (!modal) {
            return;
        }

        function openLoginModal() {
            lastFocusedElement = document.activeElement;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            window.setTimeout(function () {
                if (emailInput) {
                    emailInput.focus();
                }
            }, 50);
        }

        function closeLoginModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        }

        function controlHasLoginIcon(control) {
            if (!control) {
                return false;
            }

            return Boolean(control.querySelector(
                '.lucide-heart, ' +
                '.lucide-shopping-cart, ' +
                '.lucide-user, ' +
                '.lucide-user-round, ' +
                '.lucide-circle-user, ' +
                '.lucide-circle-user-round, ' +
                '.lucide-user-circle, ' +
                '.lucide-users, ' +
                '.lucide-search'
            ));
        }

        function isLoginAction(control) {
            if (!control) {
                return false;
            }

            // Lucide heart/cart/user/search icons are treated as protected actions
            // even if the layout navbar is not wrapped in a <header> or <nav>.
            if (controlHasLoginIcon(control)) {
                return true;
            }

            const text = (control.textContent || '').trim().replace(/\s+/g, ' ').toLowerCase();
            const ariaLabel = (control.getAttribute('aria-label') || '').toLowerCase();
            const title = (control.getAttribute('title') || '').toLowerCase();
            const href = (control.getAttribute('href') || '').toLowerCase();
            const combined = [text, ariaLabel, title, href].join(' ');
            const isHeaderControl = Boolean(control.closest('header, nav'));

            if (
                text === 'log in' ||
                text === 'login' ||
                text === 'sign in' ||
                text === 'search' ||
                combined.includes('/login')
            ) {
                return true;
            }

            if (!isHeaderControl) {
                return false;
            }

            return (
                combined.includes('wishlist') ||
                combined.includes('shopping cart') ||
                combined.includes('/cart') ||
                combined.includes('profile') ||
                combined.includes('account')
            );
        }

        // Home-page buttons plus navbar heart/cart/user/search/login controls.
        document.addEventListener('click', function (event) {
            const closeButton = event.target.closest('[data-login-modal-close]');

            if (closeButton && modal.contains(closeButton)) {
                event.preventDefault();
                closeLoginModal();
                return;
            }

            if (modal.contains(event.target)) {
                return;
            }

            const control = event.target.closest('a, button');

            if (!control) {
                return;
            }

            if (control.hasAttribute('data-login-required') || isLoginAction(control)) {
                event.preventDefault();
                event.stopPropagation();
                openLoginModal();
            }
        });

        // Also catch pressing Enter/submitting the navbar search form.
        document.addEventListener('submit', function (event) {
            const form = event.target;

            if (!form || form === loginForm || modal.contains(form)) {
                return;
            }

            const isHeaderForm = Boolean(form.closest('header, nav'));
            const hasSearchField = Boolean(form.querySelector(
                'input[type="search"], input[name*="search"], input[placeholder*="Search"], input[placeholder*="search"]'
            ));

            if (isHeaderForm && hasSearchField) {
                event.preventDefault();
                openLoginModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeLoginModal();
            }
        });

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const passwordIsHidden = passwordInput.type === 'password';

                passwordInput.type = passwordIsHidden ? 'text' : 'password';
                togglePassword.setAttribute(
                    'aria-label',
                    passwordIsHidden ? 'Hide password' : 'Show password'
                );
            });
        }

        // Remove this preventDefault once you connect a real authentication POST route.
        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (formMessage) {
                    formMessage.classList.remove('hidden');
                }
            });
        }
    });
</script>
@endpush
