{{-- =========================================================
    ACCOUNT TYPE MODAL
    Shown after "Create an account" is clicked from the login
    modal. Lets the user pick how they'll use ShopHop before
    opening the matching registration modal:
      - Buyer      -> shophop:open-registration-modal (type: buyer)
      - Seller     -> shophop:open-registration-modal (type: seller)
      - Logistics  -> shophop:open-registration-modal (type: logistics)
========================================================= --}}
<div
    id="account-type-modal"
    class="fixed inset-0 z-100 hidden items-end sm:items-center justify-center sm:p-6"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-account-type-modal-close
        aria-label="Close account type modal"
        class="absolute inset-0 w-full h-full
               bg-navy/65 backdrop-blur-sm
               cursor-default"
    ></button>

    {{-- Dialog --}}
    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="account-type-modal-title"
        aria-describedby="account-type-modal-description"
        class="relative z-10
               w-full sm:max-w-lg
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

        {{-- Back to sign in --}}
        <button
            type="button"
            data-account-type-modal-back
            aria-label="Back to sign in"
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

        {{-- Close --}}
        <button
            type="button"
            data-account-type-modal-close
            aria-label="Close account type modal"
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

        <div class="px-5 pb-6 pt-14 sm:pt-8 sm:p-8">

            {{-- Header --}}
            <div class="text-center mb-6 sm:mb-7">
                <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-1.5">
                    JOIN SHOPHOP
                </p>

                <h2
                    id="account-type-modal-title"
                    class="text-navy text-2xl sm:text-3xl font-bold leading-tight"
                >
                    How will you use ShopHop?
                </h2>

                <p
                    id="account-type-modal-description"
                    class="text-sm text-navy/50 mt-2 leading-relaxed"
                >
                    Pick an account type to get started. You can always add another later.
                </p>
            </div>

            {{-- Options --}}
            <div class="space-y-3">

                {{-- Buyer --}}
                <button
                    type="button"
                    data-account-type="buyer"
                    data-open-registration-modal="buyer"
                    class="group flex items-start gap-4 w-full
                           rounded-2xl
                           border border-gray-border
                           bg-white
                           p-4 sm:p-5
                           text-left
                           hover:border-teal
                           hover:bg-teal-light/30
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition"
                >
                    <div
                        class="w-11 h-11 shrink-0
                               rounded-xl
                               bg-teal-light
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:bg-teal
                               group-hover:text-white
                               transition"
                    >
                        <x-lucide-shopping-bag class="w-5 h-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy">
                            Buyer
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 mt-1 leading-relaxed">
                            Shop thousands of products, track your orders, and save your favorites.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               transition-all"
                    />
                </button>

                {{-- Seller --}}
                <button
                    type="button"
                    data-account-type="seller"
                    data-open-registration-modal="seller"
                    class="group flex items-start gap-4 w-full
                           rounded-2xl
                           border border-gray-border
                           bg-white
                           p-4 sm:p-5
                           text-left
                           hover:border-teal
                           hover:bg-teal-light/30
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition"
                >
                    <div
                        class="w-11 h-11 shrink-0
                               rounded-xl
                               bg-teal-light
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:bg-teal
                               group-hover:text-white
                               transition"
                    >
                        <x-lucide-store class="w-5 h-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy">
                            Seller
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 mt-1 leading-relaxed">
                            List your products, manage your store, and reach ShopHop buyers.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               transition-all"
                    />
                </button>

                {{-- Logistics --}}
                <button
                    type="button"
                    data-account-type="logistics"
                    data-open-registration-modal="logistics"
                    class="group flex items-start gap-4 w-full
                           rounded-2xl
                           border border-gray-border
                           bg-white
                           p-4 sm:p-5
                           text-left
                           hover:border-teal
                           hover:bg-teal-light/30
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition"
                >
                    <div
                        class="w-11 h-11 shrink-0
                               rounded-xl
                               bg-teal-light
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:bg-teal
                               group-hover:text-white
                               transition"
                    >
                        <x-lucide-truck class="w-5 h-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy">
                            Logistics
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 mt-1 leading-relaxed">
                            Partner with ShopHop to deliver orders and grow your fleet.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               transition-all"
                    />
                </button>

            </div>

            {{-- Already have an account --}}
            <div
                class="mt-5
                       rounded-xl
                       bg-gray-bg
                       px-4 py-3.5
                       text-center"
            >
                <p class="text-xs sm:text-sm text-navy/50">
                    Already have an account?

                    <button
                        type="button"
                        data-account-type-modal-back
                        class="ml-1 font-semibold
                               text-teal-dark
                               hover:text-navy
                               focus:outline-none
                               focus:underline
                               transition"
                    >
                        Sign in
                    </button>
                </p>
            </div>

        </div>
    </div>
</div>

@once
<script>
    function openAccountTypeModal() {
        const modal = document.getElementById('account-type-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeAccountTypeModal() {
        const modal = document.getElementById('account-type-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('click', function (event) {

        // Open the account type modal (e.g. from "Create an account" button/link)
        const openTrigger = event.target.closest('[data-open-account-type-modal]');
        if (openTrigger) {
            event.preventDefault();
            openAccountTypeModal();
            return;
        }

        // Close the account type modal (backdrop / X button)
        const closeTrigger = event.target.closest('[data-account-type-modal-close]');
        if (closeTrigger) {
            event.preventDefault();
            closeAccountTypeModal();
            return;
        }

        // "Back to sign in" -> close this modal, reopen login modal
        const backTrigger = event.target.closest('[data-account-type-modal-back]');
        if (backTrigger) {
            event.preventDefault();
            closeAccountTypeModal();
            document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
            return;
        }

        // Buyer / Seller / Logistics -> close this modal, open the matching registration modal
        const registrationTrigger = event.target.closest('[data-open-registration-modal]');
        if (registrationTrigger) {
            event.preventDefault();
            const type = registrationTrigger.dataset.openRegistrationModal;
            closeAccountTypeModal();
            document.dispatchEvent(new CustomEvent('shophop:open-registration-modal', { detail: { type: type } }));
            return;
        }

    });

    // Allow any other script (e.g. login modal) to reopen this modal via event
    document.addEventListener('shophop:open-account-type-modal', openAccountTypeModal);
</script>
@endonce