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
        id="account-type-modal-backdrop"
        class="absolute inset-0 w-full h-full
               bg-navy/65 backdrop-blur-sm
               cursor-default
               opacity-0
               transition-opacity duration-300 ease-out"
    ></button>

    {{-- Dialog --}}
    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="account-type-modal-title"
        aria-describedby="account-type-modal-description"
        id="account-type-modal-dialog"
        class="relative z-10
               w-full sm:max-w-lg
               max-h-[92vh] sm:max-h-[calc(100vh-3rem)]
               overflow-y-auto
               rounded-t-3xl sm:rounded-3xl
               bg-white
               border border-gray-border
               shadow-2xl shadow-navy/25
               opacity-0 scale-95 translate-y-3 sm:translate-y-0
               transition-all duration-300 ease-out"
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
            class="absolute top-3 left-3 z-20
                   w-9 h-9
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
            class="absolute top-3 right-3 z-20
                   w-9 h-9
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

        <div class="px-5 pb-4 pt-12 sm:pt-6 sm:p-6">

            {{-- Header --}}
            <div class="text-center mb-4 sm:mb-5">
                <div
                    class="w-9 h-9 mx-auto
                           rounded-xl
                           bg-teal-light
                           flex items-center justify-center
                           text-teal-dark
                           mb-2.5"
                >
                    <x-lucide-users-round class="w-4 h-4" />
                </div>

                <p class="text-teal-dark text-[10px] font-bold tracking-[0.12em] mb-1">
                    JOIN SHOPHOP
                </p>

                <h2
                    id="account-type-modal-title"
                    class="text-navy text-xl sm:text-2xl font-bold leading-tight"
                >
                    How will you use ShopHop?
                </h2>

                <p
                    id="account-type-modal-description"
                    class="text-xs sm:text-sm text-navy/50 mt-1.5 leading-relaxed"
                >
                    Pick an account type to get started. You can always add another later.
                </p>
            </div>

            {{-- Options --}}
            <div class="space-y-2.5">

                {{-- Buyer --}}
                <button
                    type="button"
                    data-account-type="buyer"
                    data-open-registration-modal="buyer"
                    class="group relative flex items-start gap-3 w-full
                           rounded-xl
                           border-2 border-gray-border
                           bg-white
                           hover:bg-teal-dark
                           hover:border-teal-dark
                           p-3.5
                           text-left
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition-colors duration-300"
                >
                    <div
                        class="w-10 h-10 shrink-0
                               rounded-lg
                               bg-teal-light
                               group-hover:bg-white/15
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:text-white
                               transition-colors duration-300"
                    >
                        <x-lucide-shopping-bag class="w-4.5 h-4.5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy group-hover:text-white transition-colors duration-300">
                            Buyer
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 group-hover:text-white/80 mt-0.5 leading-snug transition-colors duration-300">
                            Shop thousands of products, track your orders, and save your favorites.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-white
                               group-hover:translate-x-0.5
                               transition-all duration-300"
                    />
                </button>

                {{-- Seller --}}
                <button
                    type="button"
                    data-account-type="seller"
                    data-open-registration-modal="seller"
                    class="group relative flex items-start gap-3 w-full
                           rounded-xl
                           border-2 border-gray-border
                           bg-white
                           hover:bg-teal-dark
                           hover:border-teal-dark
                           p-3.5
                           text-left
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition-colors duration-300"
                >
                    <div
                        class="w-10 h-10 shrink-0
                               rounded-lg
                               bg-teal-light
                               group-hover:bg-white/15
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:text-white
                               transition-colors duration-300"
                    >
                        <x-lucide-store class="w-4.5 h-4.5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy group-hover:text-white transition-colors duration-300">
                            Seller
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 group-hover:text-white/80 mt-0.5 leading-snug transition-colors duration-300">
                            List your products, manage your store, and reach ShopHop buyers.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-white
                               group-hover:translate-x-0.5
                               transition-all duration-300"
                    />
                </button>

                {{-- Logistics --}}
                <button
                    type="button"
                    data-account-type="logistics"
                    data-open-registration-modal="logistics"
                    class="group relative flex items-start gap-3 w-full
                           rounded-xl
                           border-2 border-gray-border
                           bg-white
                           hover:bg-teal-dark
                           hover:border-teal-dark
                           p-3.5
                           text-left
                           focus:outline-none
                           focus:ring-4 focus:ring-teal/10
                           transition-colors duration-300"
                >
                    <div
                        class="w-10 h-10 shrink-0
                               rounded-lg
                               bg-teal-light
                               group-hover:bg-white/15
                               flex items-center justify-center
                               text-teal-dark
                               group-hover:text-white
                               transition-colors duration-300"
                    >
                        <x-lucide-truck class="w-4.5 h-4.5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-navy group-hover:text-white transition-colors duration-300">
                            Logistics
                        </p>

                        <p class="text-xs sm:text-sm text-navy/50 group-hover:text-white/80 mt-0.5 leading-snug transition-colors duration-300">
                            Partner with ShopHop to deliver orders and grow your fleet.
                        </p>
                    </div>

                    <x-lucide-arrow-right
                        class="w-4 h-4 shrink-0 mt-1
                               text-navy/25
                               group-hover:text-white
                               group-hover:translate-x-0.5
                               transition-all duration-300"
                    />
                </button>

            </div>

            {{-- Already have an account --}}
            <div
                class="mt-3.5
                       rounded-xl
                       bg-gray-bg
                       px-4 py-2.5
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
    (function () {
        let atModalOpenFrame = null;
        let atModalCloseTimeout = null;

        function getAccountTypeModalParts() {
            return {
                modal: document.getElementById('account-type-modal'),
                backdrop: document.getElementById('account-type-modal-backdrop'),
                dialog: document.getElementById('account-type-modal-dialog'),
            };
        }

        function openAccountTypeModal() {
            const { modal, backdrop, dialog } = getAccountTypeModalParts();
            if (!modal || !backdrop || !dialog) return;

            clearTimeout(atModalCloseTimeout);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');

            // Force a reflow so the transition triggers from the initial state.
            void dialog.offsetWidth;

            atModalOpenFrame = requestAnimationFrame(function () {
                backdrop.classList.remove('opacity-0');
                dialog.classList.remove('opacity-0', 'scale-95', 'translate-y-3');
                dialog.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            });
        }

        function closeAccountTypeModal() {
            const { modal, backdrop, dialog } = getAccountTypeModalParts();
            if (!modal || !backdrop || !dialog) return;

            cancelAnimationFrame(atModalOpenFrame);

            backdrop.classList.add('opacity-0');
            dialog.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            dialog.classList.add('opacity-0', 'scale-95', 'translate-y-3');
            modal.setAttribute('aria-hidden', 'true');

            atModalCloseTimeout = setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        document.addEventListener('click', function (event) {

            // Open the account type modal (e.g. from "Create an account" button/link)
            const openTrigger = event.target.closest('[data-open-account-type-modal]');
            if (openTrigger) {
                event.preventDefault();

                // Close the login modal first if it's the one that triggered this,
                // so both modals never appear stacked/overlapping.
                if (typeof window.closeLoginModal === 'function') {
                    window.closeLoginModal();
                }

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

        document.addEventListener('keydown', function (event) {
            const { modal } = getAccountTypeModalParts();
            if (modal && event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeAccountTypeModal();
            }
        });

        // Allow any other script (e.g. login modal) to reopen this modal via event
        document.addEventListener('shophop:open-account-type-modal', openAccountTypeModal);
    })();
</script>
@endonce