{{-- =========================================================
    LOGISTICS REGISTRATION MODAL
    Path: resources/views/auth/modals/logistics-registration-modal.blade.php

    Opened from account-type-modal when "Logistics" is selected.

    This reuses buyer/seller-registration-modal's shell — the 30/70
    split layout, the floating back/close buttons, the icon +
    eyebrow + title header, and the step-circle progress bar style —
    so all account-type modals share one visual system. The FIELD
    CONTENT and step-by-step flow are unchanged from the old
    logistics/register.blade.php full-page wizard (7 steps: Terms &
    Agreement, Company Details, Verify Email, Enter Code, Create
    Password, Coverage & Documents, Review & Submit), just re-skinned
    to match seller's field styling (icon-left inputs, chevron
    selects, etc.) and split into per-step Next/Back buttons instead
    of one shared wizard nav bar.

    BUG FIX: account-type-modal.blade.php already dispatches
    `shophop:open-registration-modal` with `{ type: 'logistics' }`
    when "Logistics" is clicked — but until now nothing listened for
    that type (only the seller modal listened for `type: 'seller'`),
    so the logistics option silently did nothing. This file listens
    for it and opens itself, the same way the seller modal does for
    its own type.

    IMPORTANT: This file is @include()'d directly into
    layouts/app.blade.php. It must NEVER contain @extends — doing so
    causes layouts/app.blade.php to re-render itself from inside its
    own @include, which recurses forever and exhausts PHP's memory
    limit / execution time.
========================================================= --}}

{{--
=====================================================================
BACKEND INTEGRATION NOTES — Logistics Registration
=====================================================================

ROUTE:
  - Form posts to route('logistics.register.store') via POST.
        Route::post('/logistics/register', [LogisticsRegistrationController::class, 'store'])
            ->name('logistics.register.store');
  - Sign-in link/button dispatches shophop:open-login-modal
    (shared with buyer/seller/logistics login).

CONTROLLER / VALIDATION FIELDS (all posted as multipart/form-data):
  Terms & Agreement:
    - terms_agree                required, accepted
    - agreement_rep_name*         string, letters only (no numbers)
    - agreement_date*             date
    - agreement_signature*        file, jpg/jpeg/png/webp/pdf, max 5MB

  Company Details:
    - company_name*               string, must contain at least 1 letter
    - business_registration_no*   string (DTI / SEC / CDA number)
    - line_of_business*           enum: motorcycle_courier | van_truck_freight | same_day | other
    - rep_last_name / rep_first_name   string, letters only
    - rep_valid_id*                file, image/pdf, max 5MB
    - rep_id_number*               string, alphanumeric + "-"
    - rep_sex*                     enum: male | female
    - rep_birthday*                date
    - email*                       email, unique — becomes the partner's login email
    - contact_no*                  string, digits/+/- only
    - region* / province* / municipality* / barangay*   strings (PSGC names)
    - street_no* / unit_no*        strings

  Account verification (UX-only for now — see JS TODOs below):
    - otp_code*                    string, 6 digits
    - password* / password_confirmation*   min:8, upper/lower/number

  Coverage & Documents:
    - coverage_areas[]             string[] of province/region names
    - coverage_cities[{province}]  string, "ALL" or "|"-separated city/municipality names
    - business_permit*             file
    - accreditation_docs           file, optional

STATUS / APPROVAL FLOW:
  - New logistics accounts should be created with a pending/unapproved
    status so they can't log in to the Partner Console until an admin
    approves the application. Notify the registered email on approval
    or rejection.

STILL STUBBED (same as before, not yet wired to real endpoints):
  - POST /logistics/detect-id            — ID auto-fill, fails gracefully
  - POST to send the OTP (Step 2 → 3)
  - POST to verify the OTP (Step 4 → 5)
  - POST to resend the OTP
--}}

<div
    id="logistics-registration-modal"
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-6
           opacity-0 transition-opacity duration-300 ease-out"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-logistics-registration-modal-close
        aria-label="Close logistics registration"
        class="absolute inset-0 w-full h-full
               bg-navy/35 backdrop-blur-[2px]
               cursor-default"
    ></button>

    {{-- Dialog --}}
    <div
        id="logistics-registration-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logistics-registration-modal-title"
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

        {{-- Back --}}
        <button
            type="button"
            data-logistics-registration-modal-back
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

        {{-- Close --}}
        <button
            type="button"
            data-logistics-registration-modal-close
            aria-label="Close logistics registration"
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

        <p id="logistics-registration-modal-title" class="sr-only">Logistics Partner Registration</p>


        {{-- =====================================================
            SPLIT CONTENT — 30 / 70, same ratio as buyer/seller
        ====================================================== --}}
        <div class="grid lg:grid-cols-[3fr_7fr]">


            {{-- =================================================
                LEFT ARTISTIC PANEL (30%)
            ================================================== --}}
            <div
                class="relative hidden lg:flex
                       overflow-hidden
                       bg-navy
                       px-6 xl:px-8
                       py-10
                       items-center"
            >

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

                    {{-- Logo --}}
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
                            <x-lucide-truck class="w-3 h-3" />

                            PARTNER WITH SHOPHOP
                        </span>


                        <h1
                            class="mt-4
                                   text-white
                                   text-2xl xl:text-3xl
                                   font-extrabold
                                   leading-[1.1]"
                        >
                            <span class="text-white">Deliver for</span>
                            <span class="block text-teal">
                                Every Seller.
                            </span>
                        </h1>


                        <p
                            class="mt-3
                                   text-white/55
                                   text-[13px]
                                   leading-relaxed"
                        >
                            Register your fleet as an accredited Logistics Partner
                            and manage every pickup, delivery, and payout from your
                            own console.
                        </p>

                    </div>


                    {{-- Feature list --}}
                    <div class="mt-7 xl:mt-8 space-y-2.5">

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-map class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">Pick Your Coverage</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Serve the areas you know</p>
                            </div>

                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-wallet class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">Fast Payouts</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Bi-monthly payout cycle</p>
                            </div>

                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] border border-white/10 px-3.5 py-2.5">

                            <div class="shrink-0 w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center text-teal-dark">
                                <x-lucide-shield-check class="w-4 h-4" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-white text-[11.5px] font-semibold leading-tight">Verified &amp; Trusted</p>
                                <p class="text-white/45 text-[10px] mt-0.5 leading-tight">Admin-approved partners</p>
                            </div>

                        </div>

                    </div>


                    {{-- Trust stats row --}}
                    <div class="mt-7 xl:mt-8 flex items-center gap-4 xl:gap-5">

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">500+</p>
                            <p class="text-white/45 text-[9.5px] mt-1">Partner Fleets</p>
                        </div>

                        <div class="w-px h-7 bg-white/10"></div>

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">80+</p>
                            <p class="text-white/45 text-[9.5px] mt-1">Cities Covered</p>
                        </div>

                        <div class="w-px h-7 bg-white/10"></div>

                        <div>
                            <p class="text-white text-base xl:text-lg font-extrabold">4.7<span class="text-teal">★</span></p>
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

                <div id="logistics-registration-panel" class="max-w-xl mx-auto">


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


                    {{-- Header — copied from seller/login modal's
                         icon + eyebrow + title treatment. --}}
                    <div class="mb-6">

                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-teal-light
                                   flex items-center justify-center
                                   text-teal-dark
                                   mb-4"
                        >
                            <x-lucide-truck class="w-5 h-5" />
                        </div>

                        <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-1.5">
                            LOGISTICS REGISTRATION
                        </p>

                        <h2 class="text-navy text-2xl sm:text-3xl font-bold leading-tight">
                            Become a Logistics Partner
                        </h2>

                        <p class="text-sm text-navy/50 mt-2 leading-relaxed">
                            Register your fleet to start delivering for ShopHop sellers.
                        </p>

                    </div>


                    {{-- =============================================
                        STEP PROGRESS BAR (7 steps)
                    ============================================== --}}
                    <div class="mb-6 overflow-x-auto">

                        <div
                            class="grid items-center min-w-[560px] sm:min-w-0"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto;"
                        >
                            @foreach (['Terms', 'Company', 'Verify', 'Code', 'Password', 'Coverage', 'Review'] as $i => $label)
                                @if ($i > 0)
                                    <div class="step-line h-px mx-1 bg-gray-border" data-step-line="{{ $i }}"></div>
                                @endif
                                <div
                                    class="step-circle {{ $i === 0 ? 'step-circle-active bg-teal border-teal text-white' : 'bg-white border-gray-border text-navy/30' }} justify-self-center w-7 h-7 rounded-full border flex items-center justify-center text-[10.5px] font-bold"
                                    data-step-circle="{{ $i + 1 }}"
                                >
                                    <span class="step-number">{{ $i + 1 }}</span>
                                    <x-lucide-check class="step-check hidden w-3 h-3" />
                                </div>
                            @endforeach
                        </div>

                        <div
                            class="grid mt-2 min-w-[560px] sm:min-w-0"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto;"
                        >
                            @foreach (['Terms', 'Company', 'Verify', 'Code', 'Password', 'Coverage', 'Review'] as $i => $label)
                                @if ($i > 0)
                                    <div></div>
                                @endif
                                <p
                                    class="step-label max-w-[64px] mx-auto text-center text-[10px] sm:text-[11px] leading-tight {{ $i === 0 ? 'text-navy font-semibold' : 'text-navy/30 font-medium' }}"
                                    data-step-label="{{ $i + 1 }}"
                                >
                                    {{ $label }}
                                </p>
                            @endforeach
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
                        action="{{ Route::has('logistics.register.store') ? route('logistics.register.store') : '#' }}"
                        method="POST"
                        enctype="multipart/form-data"
                        id="logistics-register-form"
                    >

                        @csrf
                        <input type="hidden" name="account_type" value="logistics">

                        <div id="logistics-step-viewport">


                        {{-- =========================================
                            STEP 1 — TERMS & AGREEMENT
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="flex items-start gap-3 bg-teal-light/60 text-teal-dark text-xs rounded-xl px-3.5 py-2.5 mb-3.5">
                                <x-lucide-info class="w-4 h-4 shrink-0 mt-0.5" />
                                <span>Please read the Courier Terms &amp; Agreement in full. You'll need to
                                    scroll to the end before you can accept and continue.</span>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-navy/50 uppercase tracking-wide">Courier Terms &amp; Agreement</p>
                                <a href="{{ Route::has('logistics.terms') ? route('logistics.terms') : '#' }}" target="_blank" rel="noopener"
                                   class="text-xs font-semibold text-teal-dark hover:text-navy underline underline-offset-2 flex items-center gap-1 transition-colors">
                                    See full terms
                                    <x-lucide-external-link class="w-3 h-3" />
                                </a>
                            </div>

                            <div id="logistics-terms-scroll-wrap" class="relative">
                                <div id="logistics-terms-scroll"
                                     class="h-56 sm:h-64 overflow-y-auto border border-gray-border/70 rounded-2xl p-4 sm:p-5 bg-gray-bg/40 space-y-4 text-xs text-navy/75 leading-relaxed">

                                    <div>
                                        <h4 class="text-navy font-bold mb-1">1. Partnership Terms</h4>
                                        <p>Accreditation is non-exclusive, non-transferable, and limited to the coverage areas approved on your application. This does not create an employment, joint venture, or franchise relationship — the Courier Partner remains an independent contractor.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">2. Courier Responsibilities</h4>
                                        <p>Maintain a sufficient, licensed rider/driver pool, ensure valid IDs and vehicle documents are carried at all times, and keep vehicles roadworthy and insured as required by law.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">3. Service &amp; Delivery Standards</h4>
                                        <p>Pickups and deliveries must meet the timeframes and success-rate thresholds published in the Logistics Partner Handbook. Delays or issues must be reported through the partner console promptly.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">4. Fees &amp; Payment Terms</h4>
                                        <p>Delivery fees follow the applicable rate card and are paid on a bi-monthly payout cycle, net of platform fees or adjustments. Disputes must be raised within 15 days of the payout statement.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">5. COD &amp; Remittance</h4>
                                        <p>Cash-on-Delivery funds are held in trust for the seller and must be remitted in full, less the agreed handling fee, within 3 banking days of successful delivery.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">6. Lost / Damaged Package Liability</h4>
                                        <p>The Courier Partner is liable for the declared value of parcels lost, stolen, or damaged while in its custody, except where caused by defective packaging, the buyer, or force majeure.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">7. Returns &amp; Failed Deliveries</h4>
                                        <p>Failed or refused deliveries must be logged with proof of attempt and returned to the seller's nominated hub within 5 calendar days.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">8. Data Privacy &amp; Confidentiality</h4>
                                        <p>Buyer and seller data may only be used to complete deliveries, in line with the Data Privacy Act of 2012, and must never be copied, stored beyond necessity, or used for off-platform solicitation.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">9. Prohibited Items</h4>
                                        <p>Illegal drugs, firearms and explosives, counterfeit goods, hazardous materials, and any item prohibited under Philippine law must never knowingly be accepted or transported.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">10. Compliance Requirements</h4>
                                        <p>The Courier Partner must hold and maintain all permits and licenses required to operate (DTI/SEC/CDA, LTFRB/LTO where applicable, local business permits) and provide updates upon renewal or request.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">11. Suspension &amp; Termination</h4>
                                        <p>ShopHop may suspend or terminate accreditation for serious violations, or with 15 days' notice for uncured material breaches. The Courier Partner may terminate with 30 days' written notice.</p>
                                    </div>
                                    <div>
                                        <h4 class="text-navy font-bold mb-1">12. Dispute Resolution</h4>
                                        <p>Disputes are first raised in good faith through Partner Support, then mediation, then binding arbitration or the proper courts of the Philippines, under Philippine law.</p>
                                    </div>

                                    <p class="text-[11px] text-navy/45 pt-2 border-t border-gray-border">
                                        This is a summary. The full Courier Terms &amp; Agreement governs in the event of any conflict.
                                    </p>
                                </div>
                                <div id="logistics-terms-scroll-fade"
                                     class="pointer-events-none absolute left-1 right-2.5 bottom-1 h-8 rounded-b-2xl transition-opacity duration-200"
                                     style="background: linear-gradient(to bottom, transparent, rgba(249,250,251,.95));"
                                ></div>
                            </div>

                            <p id="logistics-terms-scroll-hint" class="text-[11px] text-navy/45 mt-2 flex items-center gap-1.5">
                                <x-lucide-arrow-down class="w-3.5 h-3.5" />
                                Scroll to the end of the agreement to unlock the checkbox below.
                            </p>

                            <div class="bg-gray-bg rounded-2xl p-4 sm:p-5 mt-4 space-y-4">
                                <label class="flex items-start gap-3 text-sm text-navy cursor-pointer">
                                    <input type="checkbox" name="terms_agree" id="logistics_terms_agree" required disabled
                                           class="mt-0.5 w-4 h-4 accent-teal rounded disabled:opacity-40">
                                    <span>
                                        I confirm that I have read and understood the ShopHop Courier Terms
                                        &amp; Agreement in full, and I agree, on behalf of the company named
                                        in this application, to be bound by its terms. <span class="text-red-500">*</span>
                                    </span>
                                </label>

                                <div class="grid sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label for="logistics_agreement_rep_name" class="block text-xs font-semibold text-navy mb-1.5">
                                            Authorized representative — full name <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            <input type="text" name="agreement_rep_name" id="logistics_agreement_rep_name" value="{{ old('agreement_rep_name') }}" required
                                                   placeholder="Juan Dela Cruz"
                                                   class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        </div>
                                        <p id="logistics_agreement_rep_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                    </div>
                                    <div>
                                        <label for="logistics_agreement_date" class="block text-xs font-semibold text-navy mb-1.5">
                                            Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            <input type="date" name="agreement_date" id="logistics_agreement_date" value="{{ old('agreement_date', now()->toDateString()) }}" required
                                                   class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-2 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_agreement_signature" class="block text-xs font-semibold text-navy mb-1.5">
                                        E-signature <span class="text-red-500">*</span>
                                    </label>
                                    <label for="logistics_agreement_signature"
                                           class="flex items-center gap-2 border border-dashed border-gray-border/80 rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal hover:text-navy/70 hover:bg-teal-light/30 transition bg-white">
                                        <x-lucide-pen-line class="w-4 h-4 shrink-0" />
                                        <span id="logistics-agreement-signature-name">Upload a photo or scan of your signature</span>
                                        <input type="file" name="agreement_signature" id="logistics_agreement_signature"
                                               accept="image/png,image/jpeg,image/webp,application/pdf" class="hidden" required>
                                    </label>
                                    <p class="text-[11px] text-navy/45 mt-1.5">
                                        JPG, PNG, WEBP or PDF, up to 5MB. This constitutes a legally binding
                                        electronic signature under the Electronic Commerce Act of 2000.
                                    </p>
                                    <p id="logistics_agreement_signature_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                            </div>

                            <p id="logistics-step1-error" class="hidden text-xs text-red-500 font-medium mt-3 flex items-center gap-1.5">
                                <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                                Please read the agreement, then check the box and complete your name, date, and signature upload to continue.
                            </p>

                            <div class="flex items-center gap-3 mt-6">
                                <button
                                    type="button"
                                    id="logistics-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
                                >
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 2 — COMPANY DETAILS
                        ========================================== --}}
                        <div data-step-panel="2" class="hidden">

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-building-2 class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Company Details</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3.5">

                                <div class="sm:col-span-2">
                                    <label for="logistics_company_name" class="block text-xs font-semibold text-navy mb-1.5">
                                        Company / business name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-store class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="text" name="company_name" id="logistics_company_name" value="{{ old('company_name') }}" required
                                               placeholder="e.g. J&amp;T Express — Cavite Hub"
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_company_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_business_registration_no" class="block text-xs font-semibold text-navy mb-1.5">
                                        Business registration no. <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="business_registration_no" id="logistics_business_registration_no" value="{{ old('business_registration_no') }}" required
                                           placeholder="DTI / SEC / CDA number"
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_business_registration_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_line_of_business" class="block text-xs font-semibold text-navy mb-1.5">
                                        Line of business <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select name="line_of_business" id="logistics_line_of_business" required
                                                class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                            <option value="">Select</option>
                                            <option value="motorcycle_courier" @selected(old('line_of_business') === 'motorcycle_courier')>Motorcycle courier</option>
                                            <option value="van_truck_freight" @selected(old('line_of_business') === 'van_truck_freight')>Van / truck freight</option>
                                            <option value="same_day" @selected(old('line_of_business') === 'same_day')>Same-day delivery</option>
                                            <option value="other" @selected(old('line_of_business') === 'other')>Other</option>
                                        </select>
                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                    </div>
                                    <p id="logistics_line_of_business_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_rep_last_name" class="block text-xs font-semibold text-navy mb-1.5">Authorized representative — last name</label>
                                    <input type="text" name="rep_last_name" id="logistics_rep_last_name" value="{{ old('rep_last_name') }}"
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_rep_last_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_rep_first_name" class="block text-xs font-semibold text-navy mb-1.5">First name</label>
                                    <input type="text" name="rep_first_name" id="logistics_rep_first_name" value="{{ old('rep_first_name') }}"
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_rep_first_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                {{-- Representative ID + ID number --}}
                                <div class="sm:col-span-2">
                                    <label for="logistics_rep_valid_id" class="block text-xs font-semibold text-navy mb-1.5">
                                        Representative's valid ID <span class="text-red-500">*</span>
                                    </label>
                                    <label for="logistics_rep_valid_id"
                                           class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition">
                                        <div class="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-teal-dark shadow-sm">
                                            <x-lucide-upload class="w-5 h-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-navy">Choose a valid ID</p>
                                            <p class="text-[11px] text-navy/40 mt-1">JPG, JPEG, PNG or PDF · Max 5MB</p>
                                            <p id="logistics-rep-valid-id-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>
                                        </div>
                                        <input type="file" name="rep_valid_id" id="logistics_rep_valid_id"
                                               accept="image/png,image/jpeg,image/webp,application/pdf" class="hidden" required>
                                    </label>
                                    <p id="logistics-id-detect-status" class="text-[11px] text-navy/45 mt-1.5">
                                        We'll try to read your name and ID number off this automatically once
                                        it's uploaded. <span class="text-navy/35">(Auto-fill is a work in progress — please double-check the fields below either way.)</span>
                                    </p>
                                    <p id="logistics_rep_valid_id_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="logistics_rep_id_number" class="block text-xs font-semibold text-navy mb-1.5">
                                        ID number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rep_id_number" id="logistics_rep_id_number" value="{{ old('rep_id_number') }}" required
                                           placeholder="e.g. N01-23-456789"
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p class="text-[11px] text-navy/40 mt-1">Letters and numbers only (hyphens okay) — matches the ID uploaded above.</p>
                                    <p id="logistics_rep_id_number_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_rep_sex" class="block text-xs font-semibold text-navy mb-1.5">Sex <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select name="rep_sex" id="logistics_rep_sex" required
                                                class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                            <option value="">Select</option>
                                            <option value="male" @selected(old('rep_sex') === 'male')>Male</option>
                                            <option value="female" @selected(old('rep_sex') === 'female')>Female</option>
                                        </select>
                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                    </div>
                                </div>
                                <div>
                                    <label for="logistics_rep_birthday" class="block text-xs font-semibold text-navy mb-1.5">Birthday <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="date" name="rep_birthday" id="logistics_rep_birthday" value="{{ old('rep_birthday') }}" required
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-2 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_email" class="block text-xs font-semibold text-navy mb-1.5">E-mail <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-mail class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="email" name="email" id="logistics_email" value="{{ old('email') }}" required
                                               placeholder="ops@company.com"
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_email_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_contact_no" class="block text-xs font-semibold text-navy mb-1.5">Contact no. <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-phone class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="tel" name="contact_no" id="logistics_contact_no" value="{{ old('contact_no') }}" required
                                               inputmode="tel" placeholder="+63"
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-4 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_contact_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                {{-- Address cascade --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-navy mb-1.5">
                                        Business address <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-[11px] text-navy/45 mb-2">
                                        Pick your region first — province, city/municipality, and barangay
                                        choices narrow down automatically.
                                    </p>

                                    <div id="logistics-address-status" class="hidden mb-2.5 rounded-xl bg-teal-light/50 px-3.5 py-2 text-xs text-teal-dark"></div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Region <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select name="region" id="logistics_region" required
                                                        class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                                    <option value="">Loading regions…</option>
                                                </select>
                                                <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            </div>
                                            <p id="logistics_region_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Province <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select name="province" id="logistics_province" required disabled
                                                        class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                                    <option value="">Select region first</option>
                                                </select>
                                                <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            </div>
                                            <p id="logistics_province_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">City / Municipality <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select name="municipality" id="logistics_municipality" required disabled
                                                        class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                                    <option value="">Select province first</option>
                                                </select>
                                                <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            </div>
                                            <p id="logistics_municipality_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Barangay <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select name="barangay" id="logistics_barangay" required disabled
                                                        class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 pr-9 py-2.5 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/30 hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                                    <option value="">Select city/municipality first</option>
                                                </select>
                                                <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            </div>
                                            <p id="logistics_barangay_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_street_no" class="block text-xs font-semibold text-navy mb-1.5">
                                        Street no. / name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="street_no" id="logistics_street_no" value="{{ old('street_no') }}" required
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_street_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_unit_no" class="block text-xs font-semibold text-navy mb-1.5">
                                        Unit / house no. <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="unit_no" id="logistics_unit_no" value="{{ old('unit_no') }}" required
                                           class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 py-2.5 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_unit_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                            </div>

                            <p id="logistics-step2-error" class="hidden text-xs text-red-500 font-medium mt-3 flex items-center gap-1.5">
                                <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                                Please complete all required fields correctly — including your full business
                                address and representative ID — before continuing.
                            </p>

                            <div class="flex items-center gap-3 mt-6">
                                <button type="button" id="logistics-step2-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step2-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 3 — VERIFY EMAIL (interstitial)
                        ========================================== --}}
                        <div data-step-panel="3" class="hidden">

                            <div class="flex flex-col items-center text-center py-4">

                                <div class="w-16 h-16 rounded-2xl bg-teal-light flex items-center justify-center text-teal-dark mb-5">
                                    <x-lucide-mail-check class="w-8 h-8" />
                                </div>

                                <p class="text-navy font-bold text-lg">Verify your email</p>

                                <p class="text-sm text-navy/50 mt-2 leading-relaxed max-w-sm">
                                    We're sending a 6-digit verification code to
                                    <span class="font-semibold text-navy" id="logistics-verify-email-display">—</span>.
                                    This will also be your login e-mail for the Partner Console.
                                </p>

                                <p class="text-[11px] text-navy/40 mt-3">
                                    Wrong email? Go back to Company Details to update it.
                                </p>

                            </div>

                            <div class="flex items-center gap-3 mt-4">
                                <button type="button" id="logistics-step3-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step3-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 4 — ENTER CODE (OTP)
                        ========================================== --}}
                        <div data-step-panel="4" class="hidden">

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Enter verification code</p>
                            </div>

                            <p class="text-xs text-navy/50 mb-4">
                                Enter the 6-digit code we sent to
                                <span class="font-semibold text-navy" id="logistics-otp-email-display">—</span>.
                            </p>

                            <div class="flex items-center justify-between gap-2 sm:gap-3" id="logistics-otp-boxes">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/70 bg-white text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                            </div>

                            <input type="hidden" name="otp_code" id="logistics-otp-hidden" value="">

                            <p id="logistics-otp-error" class="hidden text-xs text-red-500 font-medium mt-2">
                                Please enter the full 6-digit code.
                            </p>

                            <div class="flex items-center justify-between mt-4">
                                <p class="text-[11px] text-navy/40">Didn't get a code?</p>
                                <button type="button" id="logistics-resend-code" disabled
                                        class="text-[11px] font-semibold text-navy/30 transition">
                                    <span id="logistics-resend-label">Resend in</span> <span id="logistics-resend-timer">00:30</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-3 mt-5">
                                <button type="button" id="logistics-step4-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step4-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 5 — CREATE PASSWORD
                        ========================================== --}}
                        <div data-step-panel="5" class="hidden">

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-lock class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Create your login password</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3.5">

                                <div>
                                    <label for="logistics_password" class="block text-xs font-semibold text-navy mb-1.5">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="password" name="password" id="logistics_password" minlength="8" required
                                               autocomplete="new-password" placeholder="Minimum 8 characters"
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-11 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        <button type="button" id="logistics_toggle_password"
                                                aria-label="Show password" aria-pressed="false"
                                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:text-teal-dark hover:bg-gray-bg focus:outline-none focus:ring-4 focus:ring-teal/10 transition">
                                            <svg class="password-icon-show w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg class="password-icon-hide w-4 h-4" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" /><path d="M1 1l22 22" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-1.5 mt-2">
                                        <div class="h-1 flex-1 rounded-full bg-gray-border overflow-hidden">
                                            <div id="logistics-password-strength-fill" class="h-full rounded-full bg-gray-border transition-all duration-300" style="width:0%"></div>
                                        </div>
                                        <span id="logistics-password-strength-label" class="text-[10px] font-semibold text-navy/35 w-12 text-right"></span>
                                    </div>

                                    <p id="logistics_password_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_password_confirmation" class="block text-xs font-semibold text-navy mb-1.5">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="password" name="password_confirmation" id="logistics_password_confirmation" minlength="8" required
                                               autocomplete="new-password" placeholder="Re-enter password"
                                               class="w-full min-h-11 rounded-xl border border-gray-border/70 bg-white pl-11 pr-11 py-2.5 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        <button type="button" id="logistics_toggle_password_confirmation"
                                                aria-label="Show password" aria-pressed="false"
                                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:text-teal-dark hover:bg-gray-bg focus:outline-none focus:ring-4 focus:ring-teal/10 transition">
                                            <svg class="password-icon-show w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg class="password-icon-hide w-4 h-4" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                                                <path d="M6.61 6.61A13.53 13.53 0 0 0 1 11s4 7 11 7a9.26 9.26 0 0 0 5.39-1.61" />
                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" /><path d="M1 1l22 22" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p id="logistics_password_confirmation_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                            </div>

                            <div id="logistics-password-requirements"
                                 class="mt-3.5 rounded-xl border border-gray-border/70 bg-gray-bg p-3 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5">

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

                            </div>

                            <div class="flex items-center gap-3 mt-5">
                                <button type="button" id="logistics-step5-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step5-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 6 — COVERAGE & DOCUMENTS
                        ========================================== --}}
                        <div data-step-panel="6" class="hidden">

                            <div class="flex items-center gap-2 mb-3.5">
                                <x-lucide-map class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Coverage &amp; Documents</p>
                            </div>

                            <label class="block text-xs font-semibold text-navy mb-2">
                                Coverage areas serviced <span class="text-red-500">*</span>
                            </label>
                            <p class="hidden text-[11px] text-teal-dark mb-3" id="logistics-coverage-suggestion-note">
                                Suggested based on your business region (<span id="logistics-coverage-region-name"></span>)
                                — remove anything you don't cover, or add more provinces below.
                            </p>

                            <div id="logistics-coverage-list" class="space-y-3 mb-4">
                                <p class="text-xs text-navy/40" id="logistics-coverage-empty">
                                    No coverage areas yet. Set your business region/province in Company Details, or add one below.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <select id="logistics-coverage-add-select"
                                        class="flex-1 min-h-11 rounded-xl border border-gray-border/70 bg-white px-4 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <option value="">Loading provinces…</option>
                                </select>
                                <button type="button" id="logistics-coverage-add-btn"
                                        class="shrink-0 bg-navy text-white text-sm font-semibold px-5 py-3 rounded-xl hover:bg-navy/90 transition">
                                    + Add province
                                </button>
                            </div>
                            <p id="logistics-coverage-error" class="hidden text-xs text-red-500 font-medium mt-2">
                                Add at least one coverage area before continuing.
                            </p>

                            <div class="grid sm:grid-cols-2 gap-3.5 mt-5">
                                <div>
                                    <label for="logistics_business_permit" class="block text-xs font-semibold text-navy mb-1.5">
                                        Business permit <span class="text-red-500">*</span>
                                    </label>
                                    <label for="logistics_business_permit"
                                           class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition">
                                        <div class="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-teal-dark shadow-sm">
                                            <x-lucide-upload class="w-5 h-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-navy">Choose business permit</p>
                                            <p class="text-[11px] text-navy/40 mt-1">JPG, JPEG, PNG or PDF</p>
                                            <p id="logistics-business-permit-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>
                                        </div>
                                        <input type="file" name="business_permit" id="logistics_business_permit" class="hidden" required>
                                    </label>
                                    <p id="logistics_business_permit_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_accreditation_docs" class="block text-xs font-semibold text-navy mb-1.5">
                                        Accreditation / franchise docs
                                    </label>
                                    <label for="logistics_accreditation_docs"
                                           class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-border/80 bg-gray-bg hover:border-teal hover:bg-teal-light/30 px-4 py-4 cursor-pointer transition">
                                        <div class="shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center text-teal-dark shadow-sm">
                                            <x-lucide-file-text class="w-5 h-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-navy">Optional</p>
                                            <p class="text-[11px] text-navy/40 mt-1">JPG, JPEG, PNG or PDF</p>
                                            <p id="logistics-accreditation-docs-name" class="hidden text-[11px] text-teal-dark font-medium mt-1 truncate"></p>
                                        </div>
                                        <input type="file" name="accreditation_docs" id="logistics_accreditation_docs" class="hidden">
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mt-6">
                                <button type="button" id="logistics-step6-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step6-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 7 — REVIEW & SUBMIT
                        ========================================== --}}
                        <div data-step-panel="7" class="hidden">

                            <div class="bg-gray-bg rounded-2xl p-5 divide-y divide-gray-border text-sm">
                                <p class="text-xs font-semibold text-navy/50 uppercase tracking-wide pb-3">Review before submitting</p>

                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Company</span><span class="font-semibold text-navy text-right" data-review="company_name">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Registration no.</span><span class="font-semibold text-navy text-right" data-review="business_registration_no">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Representative ID no.</span><span class="font-semibold text-navy text-right" data-review="rep_id_number">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">E-mail</span><span class="font-semibold text-navy text-right" data-review="email">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Contact no.</span><span class="font-semibold text-navy text-right" data-review="contact_no">—</span></div>

                                <div class="py-2.5 flex justify-between gap-4">
                                    <span class="text-navy/55">Email verification</span>
                                    <span class="font-semibold text-teal-dark flex items-center gap-1.5">
                                        <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                                        Code entered
                                    </span>
                                </div>
                                <div class="py-2.5 flex justify-between gap-4">
                                    <span class="text-navy/55">Login password</span>
                                    <span class="font-semibold text-teal-dark flex items-center gap-1.5">
                                        <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                                        Set
                                    </span>
                                </div>

                                <div class="py-2.5">
                                    <span class="text-navy/55 block mb-1">Business address</span>
                                    <span class="font-semibold text-navy block" id="logistics-review-address">—</span>
                                </div>
                                <div class="py-2.5">
                                    <span class="text-navy/55 block mb-1">Coverage areas</span>
                                    <span class="font-semibold text-navy block" id="logistics-review-coverage">—</span>
                                </div>

                                <div class="py-2.5 flex justify-between gap-4">
                                    <span class="text-navy/55">Terms &amp; Agreement</span>
                                    <span class="font-semibold text-teal-dark flex items-center gap-1.5">
                                        <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                                        Accepted
                                    </span>
                                </div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Signed by</span><span class="font-semibold text-navy text-right" data-review="agreement_rep_name">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4"><span class="text-navy/55">Date signed</span><span class="font-semibold text-navy text-right" data-review="agreement_date">—</span></div>
                                <div class="py-2.5 flex justify-between gap-4">
                                    <span class="text-navy/55">Signature file</span>
                                    <span class="font-semibold text-navy text-right" id="logistics-review-signature-file">—</span>
                                </div>
                            </div>

                            <label class="flex items-start gap-2.5 mt-5 text-xs text-navy/65 cursor-pointer">
                                <input type="checkbox" id="logistics_certify" required class="mt-0.5 accent-teal rounded">
                                I certify that the information and documents provided are accurate, and I
                                understand ShopHop will review this application before approval.
                            </label>
                            <p id="logistics_certify_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                            <div class="flex items-center gap-3 mt-5">
                                <button type="button" id="logistics-step7-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/70 text-navy text-sm font-semibold py-3 px-6 rounded-full hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-full shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Submit application
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>

                        </div> {{-- /#logistics-step-viewport --}}


                        {{-- Sign in --}}
                        <div class="text-center mt-6">
                            <p class="text-xs text-navy/40">
                                Already have a logistics account?
                                <button type="button" data-logistics-registration-modal-signin
                                        class="font-semibold text-teal-dark hover:text-navy transition">
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
    #logistics-registration-modal input[type="password"]::-webkit-strong-password-auto-fill-button,
    #logistics-registration-modal input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    #logistics-registration-modal input[type="password"]::-ms-reveal,
    #logistics-registration-modal input[type="password"]::-ms-clear {
        display: none !important;
    }

    #logistics-registration-modal .step-circle {
        transition:
            background-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.35s ease;
    }

    #logistics-registration-modal .step-circle.step-circle-active {
        transform: scale(1.08);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.15);
    }

    #logistics-registration-modal .step-check {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #logistics-registration-modal .step-line {
        transition: background-color 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #logistics-registration-modal .step-label {
        transition: color 0.3s ease, font-weight 0.3s ease;
    }

    #logistics-registration-modal #logistics-register-form button[type="button"],
    #logistics-registration-modal #logistics-register-form button[type="submit"] {
        transition:
            transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.25s ease,
            background-color 0.25s ease;
    }

    #logistics-registration-modal #logistics-register-form button[type="button"]:active,
    #logistics-registration-modal #logistics-register-form button[type="submit"]:active {
        transform: scale(0.97);
    }

    #logistics-registration-modal .req-dot {
        transition:
            background-color 0.25s ease,
            border-color 0.25s ease,
            transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #logistics-registration-modal .otp-box { transition: border-color .2s ease, box-shadow .2s ease, transform .15s ease; }
    #logistics-registration-modal .otp-box:focus { transform: translateY(-2px); }
    #logistics-registration-modal .otp-box.otp-filled { animation: logisticsOtpPop .2s ease; }
    @keyframes logisticsOtpPop {
        0% { transform: scale(1); }
        45% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    #logistics-registration-modal [data-coverage-chip] { animation: logisticsChipFadeIn .25s ease; }
    @keyframes logisticsChipFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    #logistics-registration-dialog {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #logistics-registration-dialog::-webkit-scrollbar {
        display: none;
    }

    #logistics-terms-scroll { scrollbar-width: thin; scrollbar-color: #99cfc9 transparent; }
    #logistics-terms-scroll::-webkit-scrollbar { width: 6px; }
    #logistics-terms-scroll::-webkit-scrollbar-thumb { background-color: #99cfc9; border-radius: 999px; }
    #logistics-terms-scroll-wrap.terms-at-bottom #logistics-terms-scroll-fade { opacity: 0; }
</style>
@endpush


@push('scripts')
<script>
    (function () {

        const modal = document.getElementById('logistics-registration-modal');

        if (!modal) {
            return;
        }

        const dialog = document.getElementById('logistics-registration-dialog');

        let lastFocusedElement = null;
        let addressLoaded = false;
        let closeTimeoutId = null;


        /*
        |--------------------------------------------------------------------------
        | MODAL OPEN / CLOSE
        |--------------------------------------------------------------------------
        */

        function openLogisticsRegistrationModal() {

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

            if (!addressLoaded) {
                addressLoaded = true;
                initRegions();
            }

            window.setTimeout(function () {
                const firstField = document.getElementById('logistics_agreement_rep_name');
                if (firstField) firstField.focus();
            }, 50);

        }

        function closeLogisticsRegistrationModal() {

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

        // Opened when account-type-modal (or anything else) dispatches this event
        // with { type: 'logistics' }. This is the piece that was missing before —
        // the event was already being fired, nothing was listening for it.
        document.addEventListener('shophop:open-registration-modal', function (event) {
            if (event.detail && event.detail.type === 'logistics') {
                openLogisticsRegistrationModal();
            }
        });

        document.addEventListener('click', function (event) {

            const backTrigger = event.target.closest('[data-logistics-registration-modal-back]');

            if (backTrigger && modal.contains(backTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-account-type-modal'));
                return;
            }

            const signInTrigger = event.target.closest('[data-logistics-registration-modal-signin]');

            if (signInTrigger && modal.contains(signInTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
                return;
            }

            const closeTrigger = event.target.closest('[data-logistics-registration-modal-close]');

            if (closeTrigger && modal.contains(closeTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
            }

        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeLogisticsRegistrationModal();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | PSGC (Philippine Standard Geographic Code) API
        | Same approach as the old full-page logistics wizard: regions are
        | hardcoded (stable, official codes), provinces/cities/barangays are
        | fetched live from https://psgc.gitlab.io/api.
        |--------------------------------------------------------------------------
        */

        const PSGC_BASE = 'https://psgc.gitlab.io/api';

        const PH_REGIONS = [
            { code: '010000000', name: 'Region I (Ilocos Region)' },
            { code: '020000000', name: 'Region II (Cagayan Valley)' },
            { code: '030000000', name: 'Region III (Central Luzon)' },
            { code: '040000000', name: 'Region IV-A (CALABARZON)' },
            { code: '170000000', name: 'MIMAROPA Region' },
            { code: '050000000', name: 'Region V (Bicol Region)' },
            { code: '060000000', name: 'Region VI (Western Visayas)' },
            { code: '070000000', name: 'Region VII (Central Visayas)' },
            { code: '080000000', name: 'Region VIII (Eastern Visayas)' },
            { code: '090000000', name: 'Region IX (Zamboanga Peninsula)' },
            { code: '100000000', name: 'Region X (Northern Mindanao)' },
            { code: '110000000', name: 'Region XI (Davao Region)' },
            { code: '120000000', name: 'Region XII (SOCCSKSARGEN)' },
            { code: '130000000', name: 'National Capital Region (NCR)' },
            { code: '140000000', name: 'Cordillera Administrative Region (CAR)' },
            { code: '150000000', name: 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' },
            { code: '160000000', name: 'Region XIII (Caraga)' },
        ];

        const psgcCache = {
            allProvinces: null,
            citiesByProvince: {},
            allCitiesMunicipalities: null,
            barangaysByCity: {},
        };

        async function psgcGet(path) {
            const res = await fetch(PSGC_BASE + path);
            if (!res.ok) throw new Error('PSGC request failed: ' + path);
            const json = await res.json();
            return Array.isArray(json) ? json : (json.data || []);
        }

        function sortByName(list) {
            return [...list].sort(function (a, b) { return a.name.localeCompare(b.name, 'en'); });
        }

        function loadRegions() {
            return Promise.resolve(sortByName(PH_REGIONS));
        }

        async function loadAllProvinces() {
            if (psgcCache.allProvinces) return psgcCache.allProvinces;
            psgcCache.allProvinces = sortByName(await psgcGet('/provinces/'));
            return psgcCache.allProvinces;
        }

        async function loadProvinces(regionCode) {
            const all = await loadAllProvinces();
            return all.filter(function (p) { return p.regionCode === regionCode; });
        }

        async function loadCitiesByProvince(provinceCode) {
            if (psgcCache.citiesByProvince[provinceCode]) return psgcCache.citiesByProvince[provinceCode];
            const cities = sortByName(await psgcGet('/provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities/'));
            psgcCache.citiesByProvince[provinceCode] = cities;
            return cities;
        }

        async function loadCitiesByRegion(regionCode) {
            if (!psgcCache.allCitiesMunicipalities) {
                psgcCache.allCitiesMunicipalities = await psgcGet('/cities-municipalities/');
            }
            const cities = psgcCache.allCitiesMunicipalities.filter(function (c) {
                const cRegion = c.regionCode || c.region_code;
                const cProvince = c.provinceCode || c.province_code;
                return cRegion === regionCode && !cProvince;
            });
            return sortByName(cities);
        }

        async function loadBarangays(cityCode) {
            if (psgcCache.barangaysByCity[cityCode]) return psgcCache.barangaysByCity[cityCode];
            const brgys = sortByName(await psgcGet('/cities-municipalities/' + encodeURIComponent(cityCode) + '/barangays/'));
            psgcCache.barangaysByCity[cityCode] = brgys;
            return brgys;
        }

        function fillOptionSelect(selectEl, items, placeholder) {
            selectEl.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = placeholder;
            selectEl.appendChild(ph);
            items.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.code = item.code;
                opt.textContent = item.name;
                selectEl.appendChild(opt);
            });
        }

        const regionSelect = document.getElementById('logistics_region');
        const provinceSelect = document.getElementById('logistics_province');
        const municipalitySelect = document.getElementById('logistics_municipality');
        const barangaySelect = document.getElementById('logistics_barangay');
        const addressStatusEl = document.getElementById('logistics-address-status');

        const addressState = { regionCode: '', regionName: '', provinceCode: '', provinceName: '', isNcrLike: false };

        function setAddressStatus(message, isError) {
            if (!addressStatusEl) return;
            if (!message) {
                addressStatusEl.classList.add('hidden');
                return;
            }
            addressStatusEl.textContent = message;
            addressStatusEl.classList.remove('hidden');
            addressStatusEl.classList.toggle('text-red-500', !!isError);
            addressStatusEl.classList.toggle('text-teal-dark', !isError);
        }

        async function initRegions() {
            if (!regionSelect) return;
            try {
                const regions = await loadRegions();
                fillOptionSelect(regionSelect, regions, 'Select region');
            } catch (e) {
                regionSelect.innerHTML = '<option value="">Couldn\'t load regions — check your connection</option>';
            }
        }

        if (regionSelect) {
            regionSelect.addEventListener('change', async function () {
                const opt = regionSelect.selectedOptions[0];
                addressState.regionCode = (opt && opt.dataset.code) || '';
                addressState.regionName = regionSelect.value;

                provinceSelect.innerHTML = '<option value="">Loading provinces…</option>';
                provinceSelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Select province first</option>';
                municipalitySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select city/municipality first</option>';
                barangaySelect.disabled = true;

                if (!addressState.regionCode) return;

                try {
                    setAddressStatus('Loading provinces…');
                    const provinces = await loadProvinces(addressState.regionCode);

                    if (provinces.length === 0) {
                        addressState.isNcrLike = true;
                        addressState.provinceCode = '';
                        addressState.provinceName = addressState.regionName;
                        provinceSelect.innerHTML = '<option value="' + addressState.regionName + '">' + addressState.regionName + ' (no provinces)</option>';
                        provinceSelect.value = addressState.regionName;
                        provinceSelect.disabled = true;

                        municipalitySelect.innerHTML = '<option value="">Loading cities/municipalities…</option>';
                        const cities = await loadCitiesByRegion(addressState.regionCode);
                        fillOptionSelect(municipalitySelect, cities, 'Select city/municipality');
                        municipalitySelect.disabled = false;
                    } else {
                        addressState.isNcrLike = false;
                        fillOptionSelect(provinceSelect, provinces, 'Select province');
                        provinceSelect.disabled = false;
                    }
                    setAddressStatus('');
                } catch (e) {
                    provinceSelect.innerHTML = '<option value="">Couldn\'t load provinces</option>';
                    setAddressStatus('Unable to load address dropdowns. Please check your connection.', true);
                }
            });
        }

        if (provinceSelect) {
            provinceSelect.addEventListener('change', async function () {
                const opt = provinceSelect.selectedOptions[0];
                addressState.provinceCode = (opt && opt.dataset.code) || '';
                addressState.provinceName = provinceSelect.value;

                municipalitySelect.innerHTML = '<option value="">Loading cities/municipalities…</option>';
                municipalitySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select city/municipality first</option>';
                barangaySelect.disabled = true;

                if (!addressState.provinceCode) return;
                try {
                    const cities = await loadCitiesByProvince(addressState.provinceCode);
                    fillOptionSelect(municipalitySelect, cities, 'Select city/municipality');
                    municipalitySelect.disabled = false;
                } catch (e) {
                    municipalitySelect.innerHTML = '<option value="">Couldn\'t load cities</option>';
                }
            });
        }

        if (municipalitySelect) {
            municipalitySelect.addEventListener('change', async function () {
                const opt = municipalitySelect.selectedOptions[0];
                const cityCode = (opt && opt.dataset.code) || '';

                barangaySelect.innerHTML = '<option value="">Loading barangays…</option>';
                barangaySelect.disabled = true;

                if (!cityCode) return;
                try {
                    const brgys = await loadBarangays(cityCode);
                    fillOptionSelect(barangaySelect, brgys, 'Select barangay');
                    barangaySelect.disabled = false;
                } catch (e) {
                    barangaySelect.innerHTML = '<option value="">Couldn\'t load barangays</option>';
                }
            });
        }


        /*
        |--------------------------------------------------------------------------
        | COVERAGE AREAS BUILDER (Step 6)
        |--------------------------------------------------------------------------
        */

        const coverageListEl = document.getElementById('logistics-coverage-list');
        const coverageEmptyEl = document.getElementById('logistics-coverage-empty');
        const coverageAddSelect = document.getElementById('logistics-coverage-add-select');
        const coverageAddBtn = document.getElementById('logistics-coverage-add-btn');
        const coverageErrorEl = document.getElementById('logistics-coverage-error');
        const coverageSuggestNote = document.getElementById('logistics-coverage-suggestion-note');
        const coverageRegionNameEl = document.getElementById('logistics-coverage-region-name');

        const coverageChips = new Map();
        let coverageAutoSuggested = false;
        let coverageOptionsLoaded = false;

        async function initCoverageAddOptions() {
            if (!coverageAddSelect) return;
            try {
                const regions = await loadRegions();
                const provinces = await loadAllProvinces();
                const options = provinces.map(function (p) {
                    return { value: p.name, code: p.code, type: 'province', label: p.name };
                });

                const noProvinceRegion = regions.find(function (r) { return /national capital region|\bncr\b/i.test(r.name); });
                if (noProvinceRegion) {
                    options.push({ value: noProvinceRegion.name, code: noProvinceRegion.code, type: 'region', label: noProvinceRegion.name + ' (no provinces)' });
                }
                options.sort(function (a, b) { return a.label.localeCompare(b.label, 'en'); });

                coverageAddSelect.innerHTML = '<option value="">+ Choose a province to add…</option>';
                options.forEach(function (o) {
                    const opt = document.createElement('option');
                    opt.value = o.value;
                    opt.dataset.code = o.code;
                    opt.dataset.type = o.type;
                    opt.textContent = o.label;
                    coverageAddSelect.appendChild(opt);
                });
            } catch (e) {
                coverageAddSelect.innerHTML = '<option value="">Couldn\'t load provinces</option>';
            }
        }

        async function loadCoverageCities(key) {
            const chip = coverageChips.get(key);
            if (!chip) return;
            try {
                const cities = chip.type === 'region' ? await loadCitiesByRegion(chip.code) : await loadCitiesByProvince(chip.code);
                cities.forEach(function (c) { chip.cities.set(c.code, { name: c.name, checked: false }); });
                chip.citiesLoaded = true;
            } catch (e) {
                chip.citiesLoaded = 'error';
            }
            renderCoverageList();
        }

        function addCoverageChip(name, code, type) {
            const key = type + ':' + (code || name);
            if (coverageChips.has(key)) return;
            coverageChips.set(key, { key: key, name: name, code: code, type: type, cities: new Map(), citiesLoaded: false, selectAll: false });
            renderCoverageList();
            loadCoverageCities(key);
        }

        function removeCoverageChip(key) {
            coverageChips.delete(key);
            renderCoverageList();
        }

        function renderCoverageList() {
            if (!coverageListEl) return;
            coverageListEl.querySelectorAll('[data-coverage-chip]').forEach(function (el) { el.remove(); });
            if (coverageEmptyEl) coverageEmptyEl.classList.toggle('hidden', coverageChips.size > 0);

            coverageChips.forEach(function (chip) {
                const wrap = document.createElement('div');
                wrap.className = 'border border-gray-border/70 rounded-xl p-4 bg-white hover:border-teal/30 transition-colors';
                wrap.dataset.coverageChip = chip.key;

                let citiesHtml;
                if (!chip.citiesLoaded) {
                    citiesHtml = '<p class="text-[11px] text-navy/40 col-span-full">Loading cities/municipalities…</p>';
                } else if (chip.citiesLoaded === 'error') {
                    citiesHtml = '<p class="text-[11px] text-red-500 col-span-full">Couldn\'t load cities for this province.</p>';
                } else {
                    citiesHtml = [...chip.cities.entries()].map(function ([code, c]) {
                        return '<label class="flex items-center gap-1.5 text-[11px] text-navy/70 py-1.5 px-1 -mx-1 rounded-lg hover:bg-gray-bg/60 active:bg-gray-bg transition cursor-pointer break-words">' +
                            '<input type="checkbox" data-city-code="' + code + '" data-chip-key="' + chip.key + '" class="w-4 h-4 shrink-0 accent-teal" ' + (c.checked ? 'checked' : '') + '>' +
                            '<span>' + c.name + '</span></label>';
                    }).join('');
                }

                wrap.innerHTML =
                    '<div class="flex items-start justify-between gap-2 mb-2">' +
                        '<span class="inline-flex items-center gap-1.5 text-sm font-bold text-navy break-words">' +
                            '<span class="w-1.5 h-1.5 rounded-full bg-teal shrink-0"></span>' + chip.name +
                        '</span>' +
                        '<button type="button" data-coverage-remove="' + chip.key + '" class="shrink-0 text-navy/40 hover:text-red-500 text-xs font-semibold px-2 py-1 -m-1 rounded-lg hover:bg-red-50 active:bg-red-50 transition">Remove</button>' +
                    '</div>' +
                    '<div class="flex items-center justify-between gap-2 flex-wrap mb-1">' +
                        '<span class="text-[11px] text-navy/50">Cities / municipalities covered</span>' +
                        '<label class="flex items-center gap-1.5 text-[11px] font-semibold text-teal-dark cursor-pointer shrink-0 py-1 px-1.5 -mx-1.5 rounded-lg hover:bg-teal-light/50 active:bg-teal-light/50 transition">' +
                            '<input type="checkbox" data-select-all-key="' + chip.key + '" class="w-4 h-4 accent-teal" ' + (chip.selectAll ? 'checked' : '') + '>' +
                            'Select all' +
                        '</label>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-2 gap-y-0.5 max-h-44 overflow-y-auto pr-1">' + citiesHtml + '</div>';

                coverageListEl.appendChild(wrap);
            });
        }

        if (coverageListEl) {
            coverageListEl.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('[data-coverage-remove]');
                if (removeBtn) removeCoverageChip(removeBtn.dataset.coverageRemove);
            });

            coverageListEl.addEventListener('change', function (e) {
                const cityCb = e.target.closest('[data-city-code]');
                if (cityCb) {
                    const chip = coverageChips.get(cityCb.dataset.chipKey);
                    const city = chip && chip.cities.get(cityCb.dataset.cityCode);
                    if (city) city.checked = cityCb.checked;
                    if (chip) chip.selectAll = [...chip.cities.values()].every(function (c) { return c.checked; });
                    renderCoverageList();
                    return;
                }
                const selectAllCb = e.target.closest('[data-select-all-key]');
                if (selectAllCb) {
                    const chip = coverageChips.get(selectAllCb.dataset.selectAllKey);
                    if (chip) {
                        chip.selectAll = selectAllCb.checked;
                        chip.cities.forEach(function (c) { c.checked = selectAllCb.checked; });
                    }
                    renderCoverageList();
                }
            });
        }

        if (coverageAddBtn) {
            coverageAddBtn.addEventListener('click', function () {
                const opt = coverageAddSelect.selectedOptions[0];
                if (!opt || !opt.value) return;
                addCoverageChip(opt.value, opt.dataset.code, opt.dataset.type);
                coverageAddSelect.value = '';
            });
        }

        function autoSuggestCoverage() {
            if (coverageAutoSuggested || !addressState.regionName) return;
            coverageAutoSuggested = true;
            if (coverageSuggestNote) coverageSuggestNote.classList.remove('hidden');
            if (coverageRegionNameEl) coverageRegionNameEl.textContent = addressState.regionName;

            if (addressState.isNcrLike) {
                addCoverageChip(addressState.regionName, addressState.regionCode, 'region');
            } else if (addressState.provinceName) {
                addCoverageChip(addressState.provinceName, addressState.provinceCode, 'province');
            }
        }

        function serializeCoverage() {
            modal.querySelectorAll('[data-coverage-hidden]').forEach(function (el) { el.remove(); });
            const form = document.getElementById('logistics-register-form');
            coverageChips.forEach(function (chip) {
                const provinceInput = document.createElement('input');
                provinceInput.type = 'hidden';
                provinceInput.name = 'coverage_areas[]';
                provinceInput.value = chip.name;
                provinceInput.dataset.coverageHidden = '1';
                form.appendChild(provinceInput);

                const checkedCities = [...chip.cities.values()].filter(function (c) { return c.checked; }).map(function (c) { return c.name; });
                const citiesValue = (chip.citiesLoaded === true && chip.selectAll) ? 'ALL' : checkedCities.join('|');

                const cityInput = document.createElement('input');
                cityInput.type = 'hidden';
                cityInput.name = 'coverage_cities[' + chip.name + ']';
                cityInput.value = citiesValue;
                cityInput.dataset.coverageHidden = '1';
                form.appendChild(cityInput);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | ID AUTO-FILL — TEMPLATE / STUB, same graceful-fallback pattern as
        | before. Wire this up to a real ID-recognition service later.
        |--------------------------------------------------------------------------
        */

        const repIdFileInput = document.getElementById('logistics_rep_valid_id');
        const repIdFileNameEl = document.getElementById('logistics-rep-valid-id-name');
        const idDetectStatus = document.getElementById('logistics-id-detect-status');

        async function detectRepresentativeId(file) {
            if (!idDetectStatus) return;
            idDetectStatus.textContent = 'Reading your ID…';

            try {
                const formData = new FormData();
                formData.append('id_file', file);
                const csrfInput = document.querySelector('#logistics-register-form input[name="_token"]');

                // TODO: point this at the real detection endpoint once it exists.
                const res = await fetch('/logistics/detect-id', {
                    method: 'POST',
                    body: formData,
                    headers: csrfInput ? { 'X-CSRF-TOKEN': csrfInput.value } : {},
                });
                if (!res.ok) throw new Error('Detection endpoint not available yet');
                const data = await res.json();

                if (data.first_name) document.getElementById('logistics_rep_first_name').value = data.first_name;
                if (data.last_name) document.getElementById('logistics_rep_last_name').value = data.last_name;
                if (data.id_number) document.getElementById('logistics_rep_id_number').value = data.id_number;

                idDetectStatus.textContent = 'Details auto-filled from your ID — please double-check them below.';
            } catch (e) {
                // Expected for now since the backend endpoint isn't built yet.
                idDetectStatus.textContent = 'Upload received. (Auto-fill from ID scanning is still in progress — please fill in the fields below manually for now.)';
            }
        }

        if (repIdFileInput) {
            repIdFileInput.addEventListener('change', function () {
                if (this.files.length > 0 && repIdFileNameEl) {
                    repIdFileNameEl.textContent = this.files[0].name;
                    repIdFileNameEl.classList.remove('hidden');
                }
                if (this.files.length) detectRepresentativeId(this.files[0]);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EMAIL / OTP / RESEND — UX-only for now, same as before.
        |--------------------------------------------------------------------------
        */

        const verifyEmailDisplay = document.getElementById('logistics-verify-email-display');
        const otpEmailDisplay = document.getElementById('logistics-otp-email-display');
        const otpInputs = Array.from(document.querySelectorAll('#logistics-otp-boxes [data-otp-digit]'));
        const otpHidden = document.getElementById('logistics-otp-hidden');
        const otpErrorEl = document.getElementById('logistics-otp-error');
        const resendBtn = document.getElementById('logistics-resend-code');
        const resendLabel = document.getElementById('logistics-resend-label');
        const resendTimerEl = document.getElementById('logistics-resend-timer');

        function currentAccountEmail() {
            const emailField = document.getElementById('logistics_email');
            return emailField ? emailField.value.trim() : '';
        }

        // TODO: point this at the real send-verification-code endpoint once it
        // exists. For now it just updates the UI and starts the resend countdown.
        function sendVerificationCode() {
            const email = currentAccountEmail() || 'your email';
            if (verifyEmailDisplay) verifyEmailDisplay.textContent = email;
            if (otpEmailDisplay) otpEmailDisplay.textContent = email;
            startResendCountdown();
        }

        let resendInterval = null;

        function startResendCountdown() {
            if (!resendBtn || !resendLabel || !resendTimerEl) return;

            let secondsLeft = 30;

            resendBtn.disabled = true;
            resendBtn.classList.add('text-navy/30');
            resendBtn.classList.remove('text-teal-dark', 'hover:text-navy', 'cursor-pointer');
            resendLabel.textContent = 'Resend in';
            resendTimerEl.classList.remove('hidden');

            if (resendInterval) clearInterval(resendInterval);

            function render() {
                const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
                const secs = String(secondsLeft % 60).padStart(2, '0');
                resendTimerEl.textContent = mins + ':' + secs;
            }

            render();

            resendInterval = setInterval(function () {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendLabel.textContent = 'Resend code';
                    resendTimerEl.classList.add('hidden');
                    resendBtn.classList.remove('text-navy/30');
                    resendBtn.classList.add('text-teal-dark', 'hover:text-navy', 'cursor-pointer');
                    return;
                }
                render();
            }, 1000);
        }

        if (resendBtn) {
            resendBtn.addEventListener('click', function () {
                if (resendBtn.disabled) return;
                // TODO: real resend-code request goes here.
                otpInputs.forEach(function (input) { input.value = ''; input.classList.remove('otp-filled'); });
                if (otpInputs[0]) otpInputs[0].focus();
                startResendCountdown();
            });
        }

        otpInputs.forEach(function (input, index) {

            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);
                this.classList.toggle('otp-filled', this.value.length > 0);
                if (this.value && index < otpInputs.length - 1) otpInputs[index + 1].focus();
                if (otpErrorEl) otpErrorEl.classList.add('hidden');
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) otpInputs[index - 1].focus();
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, otpInputs.length);
                pasted.split('').forEach(function (digit, i) { if (otpInputs[i]) { otpInputs[i].value = digit; otpInputs[i].classList.add('otp-filled'); } });
                const nextEmpty = otpInputs.findIndex(function (box) { return !box.value; });
                (otpInputs[nextEmpty] || otpInputs[otpInputs.length - 1]).focus();
            });

        });

        function otpValue() {
            return otpInputs.map(function (input) { return input.value; }).join('');
        }

        function otpComplete() {
            return otpValue().length === otpInputs.length;
        }

        function serializeOtp() {
            if (otpHidden) otpHidden.value = otpValue();
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD — requirements checklist, strength meter, show/hide
        |--------------------------------------------------------------------------
        */

        const passwordUppercaseRegex = /[A-Z]/;
        const passwordLowercaseRegex = /[a-z]/;
        const passwordNumberRegex = /[0-9]/;

        function isValidPassword(v) {
            return v.length >= 8 && passwordUppercaseRegex.test(v) && passwordLowercaseRegex.test(v) && passwordNumberRegex.test(v);
        }

        function updatePasswordRequirements(value) {
            const checks = {
                length: value.length >= 8,
                uppercase: passwordUppercaseRegex.test(value),
                lowercase: passwordLowercaseRegex.test(value),
                number: passwordNumberRegex.test(value),
            };
            Object.keys(checks).forEach(function (key) {
                const item = document.querySelector('#logistics-password-requirements [data-req="' + key + '"]');
                if (!item) return;
                const dot = item.querySelector('.req-dot');
                const check = item.querySelector('.req-check');
                const satisfied = checks[key];
                item.classList.toggle('text-teal-dark', satisfied);
                item.classList.toggle('text-navy/40', !satisfied);
                dot.classList.toggle('bg-teal', satisfied);
                dot.classList.toggle('border-teal', satisfied);
                dot.classList.toggle('border-gray-border', !satisfied);
                check.classList.toggle('hidden', !satisfied);
            });

            const strengthFill = document.getElementById('logistics-password-strength-fill');
            const strengthLabel = document.getElementById('logistics-password-strength-label');
            if (strengthFill && strengthLabel) {
                const passedCount = Object.values(checks).filter(Boolean).length + (value.length >= 12 ? 1 : 0);
                const tiers = [
                    { min: 0, width: '0%', color: 'bg-gray-border', label: '' },
                    { min: 1, width: '25%', color: 'bg-red-400', label: 'Weak' },
                    { min: 2, width: '50%', color: 'bg-amber-400', label: 'Fair' },
                    { min: 3, width: '75%', color: 'bg-teal/70', label: 'Good' },
                    { min: 4, width: '100%', color: 'bg-teal', label: 'Strong' },
                ];
                const tier = value.length === 0 ? tiers[0] : [...tiers].reverse().find(function (t) { return passedCount >= t.min; });
                strengthFill.style.width = tier.width;
                strengthFill.className = 'h-full rounded-full transition-all duration-300 ' + tier.color;
                strengthLabel.textContent = tier.label;
            }
        }

        const passwordInput = document.getElementById('logistics_password');
        const passwordError = document.getElementById('logistics_password_error');
        const passwordConfirmInput = document.getElementById('logistics_password_confirmation');
        const passwordConfirmError = document.getElementById('logistics_password_confirmation_error');

        function passwordStepValid() {
            if (!passwordInput || !passwordConfirmInput) return true;
            const passOk = isValidPassword(passwordInput.value);
            const matchOk = passwordConfirmInput.value.length > 0 && passwordConfirmInput.value === passwordInput.value;
            passwordError.textContent = passOk ? '' : 'Password needs 8+ characters, an uppercase letter, a lowercase letter, and a number.';
            passwordError.classList.toggle('hidden', passOk);
            passwordConfirmError.textContent = matchOk ? '' : 'Passwords do not match.';
            passwordConfirmError.classList.toggle('hidden', matchOk);
            return passOk && matchOk;
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                updatePasswordRequirements(passwordInput.value);
                if (passwordConfirmInput.value) passwordStepValid();
            });
        }
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', passwordStepValid);
        }

        function setupPasswordToggle(inputEl, buttonEl) {
            if (!inputEl || !buttonEl) return;
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
                const caret = inputEl.value.length;
                inputEl.setSelectionRange(caret, caret);
            });
        }

        setupPasswordToggle(passwordInput, document.getElementById('logistics_toggle_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('logistics_toggle_password_confirmation'));


        /*
        |--------------------------------------------------------------------------
        | FIELD VALIDATORS
        |--------------------------------------------------------------------------
        */

        function isValidName(v) { return /^[^0-9]+$/.test(v.trim()); }
        function isValidBusinessName(v) { return /[A-Za-zÀ-ÖØ-öø-ÿÑñ]/.test(v); }
        function isValidPhone(v) { return /^[0-9+\-]+$/.test(v.trim()); }
        function isValidIdNumber(v) { return /^[A-Za-z0-9\-]+$/.test(v.trim()); }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function showFieldError(input, errorEl, message) {
            if (!errorEl) return;
            if (message) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
                if (input) {
                    input.classList.add('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.remove('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');
                }
            } else {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
                if (input) {
                    input.classList.remove('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.add('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');
                }
            }
        }

        function bindFieldValidator(id, testFn, message) {
            const input = document.getElementById(id);
            const errorEl = document.getElementById(id + '_error');
            if (!input || !errorEl) return;
            const check = function () {
                const val = input.value.trim();
                const bad = val.length > 0 && !testFn(val);
                showFieldError(input, errorEl, bad ? message : '');
            };
            input.addEventListener('input', check);
            input.addEventListener('blur', check);
        }

        bindFieldValidator('logistics_agreement_rep_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_company_name', isValidBusinessName, 'Business name needs at least one letter.');
        bindFieldValidator('logistics_rep_last_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_rep_first_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_contact_no', isValidPhone, 'Numbers only — only + and - are allowed as symbols.');
        bindFieldValidator('logistics_rep_id_number', isValidIdNumber, 'ID number can only contain letters, numbers, and hyphens.');
        bindFieldValidator('logistics_email', function (v) { return emailRegex.test(v); }, 'Please enter a valid email address.');


        /*
        |--------------------------------------------------------------------------
        | TERMS GATE (Step 1)
        |--------------------------------------------------------------------------
        */

        const termsScroll = document.getElementById('logistics-terms-scroll');
        const termsScrollWrap = document.getElementById('logistics-terms-scroll-wrap');
        const termsHint = document.getElementById('logistics-terms-scroll-hint');
        const termsCheckbox = document.getElementById('logistics_terms_agree');
        const step1ErrorEl = document.getElementById('logistics-step1-error');
        const signatureInput = document.getElementById('logistics_agreement_signature');
        const signatureNameEl = document.getElementById('logistics-agreement-signature-name');

        if (termsScroll && termsCheckbox) {
            const unlockCheckbox = function () {
                termsCheckbox.disabled = false;
                if (termsHint) termsHint.classList.add('hidden');
                if (termsScrollWrap) termsScrollWrap.classList.add('terms-at-bottom');
            };
            termsScroll.addEventListener('scroll', function () {
                const reachedBottom = termsScroll.scrollTop + termsScroll.clientHeight >= termsScroll.scrollHeight - 24;
                if (reachedBottom) {
                    unlockCheckbox();
                } else if (termsScrollWrap) {
                    termsScrollWrap.classList.remove('terms-at-bottom');
                }
            });
        }

        if (signatureInput) {
            signatureInput.addEventListener('change', function () {
                if (this.files.length && signatureNameEl) signatureNameEl.textContent = this.files[0].name;
            });
        }

        function termsAccepted() {
            const repNameInput = document.getElementById('logistics_agreement_rep_name');
            const repName = repNameInput.value.trim();
            const date = document.getElementById('logistics_agreement_date').value.trim();
            const hasSignatureFile = signatureInput && signatureInput.files && signatureInput.files.length > 0;
            return !!(termsCheckbox && termsCheckbox.checked && repName && date && hasSignatureFile && isValidName(repName));
        }


        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW (7 steps)
        |--------------------------------------------------------------------------
        */

        function getPanel(step) {
            return modal.querySelector('[data-step-panel="' + step + '"]');
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

        function populateReview() {
            modal.querySelectorAll('[data-review]').forEach(function (el) {
                const input = document.getElementById('logistics_' + el.dataset.review) || document.querySelector('#logistics-register-form [name="' + el.dataset.review + '"]');
                if (input && input.value) el.textContent = input.value;
            });

            const sigReview = document.getElementById('logistics-review-signature-file');
            if (sigReview) {
                sigReview.textContent = (signatureInput && signatureInput.files && signatureInput.files.length)
                    ? signatureInput.files[0].name
                    : '—';
            }

            const addressReview = document.getElementById('logistics-review-address');
            if (addressReview) {
                const parts = [
                    document.getElementById('logistics_unit_no') && document.getElementById('logistics_unit_no').value,
                    document.getElementById('logistics_street_no') && document.getElementById('logistics_street_no').value,
                    barangaySelect && barangaySelect.value,
                    municipalitySelect && municipalitySelect.value,
                    provinceSelect && provinceSelect.value,
                    regionSelect && regionSelect.value,
                ].filter(Boolean);
                addressReview.textContent = parts.length ? parts.join(', ') : '—';
            }

            const coverageReview = document.getElementById('logistics-review-coverage');
            if (coverageReview) {
                const names = [...coverageChips.values()].map(function (c) { return c.name; });
                coverageReview.textContent = names.length ? names.join(', ') : '—';
            }
        }

        function goToStep(step) {
            modal.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                const s = parseInt(panel.dataset.stepPanel, 10);
                panel.classList.toggle('hidden', s !== step);
            });

            updateProgressBar(step);

            if (step === 7) populateReview();

            if (dialog) {
                dialog.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function validateStep1() {
            if (!termsAccepted()) {
                if (step1ErrorEl) step1ErrorEl.classList.remove('hidden');
                termsScroll.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
            if (step1ErrorEl) step1ErrorEl.classList.add('hidden');
            return true;
        }

        function step2Valid() {
            const required = [
                'logistics_company_name', 'logistics_business_registration_no', 'logistics_line_of_business',
                'logistics_rep_last_name', 'logistics_rep_first_name', 'logistics_rep_sex', 'logistics_rep_birthday',
                'logistics_email', 'logistics_contact_no', 'logistics_rep_id_number',
                'logistics_region', 'logistics_province', 'logistics_municipality', 'logistics_barangay',
                'logistics_street_no', 'logistics_unit_no',
            ];
            for (const id of required) {
                const el = document.getElementById(id);
                if (!el || !el.value || !el.value.trim()) return false;
            }
            if (!repIdFileInput || !repIdFileInput.files || repIdFileInput.files.length === 0) return false;

            return isValidName(document.getElementById('logistics_rep_last_name').value)
                && isValidName(document.getElementById('logistics_rep_first_name').value)
                && isValidBusinessName(document.getElementById('logistics_company_name').value)
                && isValidPhone(document.getElementById('logistics_contact_no').value)
                && isValidIdNumber(document.getElementById('logistics_rep_id_number').value)
                && emailRegex.test(document.getElementById('logistics_email').value.trim());
        }

        function validateStep2() {
            const step2ErrorEl = document.getElementById('logistics-step2-error');
            const ok = step2Valid();
            if (!ok) {
                if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                getPanel(2).scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
            if (step2ErrorEl) step2ErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep4() {
            if (!otpComplete()) {
                if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                return false;
            }
            if (otpErrorEl) otpErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep6() {
            const permitInput = document.getElementById('logistics_business_permit');
            const hasPermit = permitInput && permitInput.files && permitInput.files.length > 0;
            const hasCoverage = coverageChips.size > 0;
            if (!hasCoverage || !hasPermit) {
                if (coverageErrorEl && !hasCoverage) coverageErrorEl.classList.remove('hidden');
                return false;
            }
            if (coverageErrorEl) coverageErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep7() {
            const certify = document.getElementById('logistics_certify');
            const certifyError = document.getElementById('logistics_certify_error');
            if (!certify.checked) {
                showFieldError(null, certifyError, 'Please certify that your information is accurate.');
                return false;
            }
            showFieldError(null, certifyError, '');
            return true;
        }

        document.getElementById('logistics-step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('logistics-step2-back').addEventListener('click', function () { goToStep(1); });
        document.getElementById('logistics-step2-next').addEventListener('click', function () {
            if (validateStep2()) {
                sendVerificationCode();
                goToStep(3);
            }
        });

        document.getElementById('logistics-step3-back').addEventListener('click', function () { goToStep(2); });
        document.getElementById('logistics-step3-next').addEventListener('click', function () {
            goToStep(4);
            if (otpInputs[0]) otpInputs[0].focus();
        });

        document.getElementById('logistics-step4-back').addEventListener('click', function () { goToStep(3); });
        document.getElementById('logistics-step4-next').addEventListener('click', function () {
            if (validateStep4()) {
                serializeOtp();
                goToStep(5);
            }
        });

        document.getElementById('logistics-step5-back').addEventListener('click', function () { goToStep(4); });
        document.getElementById('logistics-step5-next').addEventListener('click', function () {
            if (passwordStepValid()) {
                if (!coverageOptionsLoaded) {
                    coverageOptionsLoaded = true;
                    initCoverageAddOptions();
                }
                autoSuggestCoverage();
                goToStep(6);
            }
        });

        document.getElementById('logistics-step6-back').addEventListener('click', function () { goToStep(5); });
        document.getElementById('logistics-step6-next').addEventListener('click', function () {
            if (validateStep6()) {
                serializeCoverage();
                goToStep(7);
            }
        });

        document.getElementById('logistics-step7-back').addEventListener('click', function () { goToStep(6); });


        /*
        |--------------------------------------------------------------------------
        | FILE PICKERS — generic label-swap for business permit / accreditation
        |--------------------------------------------------------------------------
        */

        function setupSimpleFilePicker(inputId, nameElId) {
            const input = document.getElementById(inputId);
            const nameEl = document.getElementById(nameElId);
            if (!input || !nameEl) return;
            input.addEventListener('change', function () {
                if (this.files.length) {
                    nameEl.textContent = this.files[0].name;
                    nameEl.classList.remove('hidden');
                } else {
                    nameEl.textContent = '';
                    nameEl.classList.add('hidden');
                }
            });
        }

        setupSimpleFilePicker('logistics_business_permit', 'logistics-business-permit-name');
        setupSimpleFilePicker('logistics_accreditation_docs', 'logistics-accreditation-docs-name');


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT GUARD — safety net in case a step is bypassed
        |--------------------------------------------------------------------------
        */

        const registerForm = document.getElementById('logistics-register-form');

        registerForm.addEventListener('submit', function (e) {

            if (!termsAccepted()) {
                e.preventDefault();
                goToStep(1);
                if (step1ErrorEl) step1ErrorEl.classList.remove('hidden');
                return;
            }

            if (!step2Valid()) {
                e.preventDefault();
                goToStep(2);
                const step2ErrorEl = document.getElementById('logistics-step2-error');
                if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                return;
            }

            if (!otpComplete()) {
                e.preventDefault();
                goToStep(4);
                if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                return;
            }

            if (!passwordStepValid()) {
                e.preventDefault();
                goToStep(5);
                return;
            }

            if (!validateStep6()) {
                e.preventDefault();
                goToStep(6);
                return;
            }

            if (!validateStep7()) {
                e.preventDefault();
                goToStep(7);
                return;
            }

            serializeOtp();
            serializeCoverage();
        });


        // Initial visual state — safe even while the modal is still hidden.
        updateProgressBar(1);

    })();
</script>
@endpush