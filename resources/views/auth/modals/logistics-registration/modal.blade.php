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
    UI/UX PASS: improved modal spacing, larger tap targets, softer dialog
    corners/shadow, wider content column, and more consistent field gaps.
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
    class="fixed inset-0 z-100 hidden items-stretch sm:items-center justify-center sm:p-4 md:p-6
           opacity-0 transition-opacity duration-300 ease-out"
    aria-hidden="true"
>
    {{-- Backdrop --}}
    <button
        type="button"
        data-logistics-registration-modal-close
        aria-label="Close logistics registration"
        class="absolute inset-0 w-full h-full
               bg-navy/45 backdrop-blur-[5px]
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
               sm:max-h-[calc(100vh-2rem)]
               sm:max-w-5xl lg:max-w-6xl xl:max-w-7xl
               overflow-y-auto
               sm:rounded-4xl
               bg-white
               border-0 sm:border sm:border-white/80
               shadow-2xl shadow-navy/25
               opacity-0 scale-95 translate-y-3
               transition-all duration-300 ease-out"
    >

        {{-- Back --}}
        <button
            type="button"
            data-logistics-registration-modal-back
            aria-label="Back to account type"
            class="absolute top-4 left-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-white/90 backdrop-blur
                   border border-gray-border/70
                   shadow-sm shadow-navy/10
                   text-navy/55
                   flex items-center justify-center
                   hover:bg-teal-light hover:border-teal/20
                   hover:text-teal-dark hover:-translate-y-0.5
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition-all duration-200"
        >
            <x-lucide-arrow-left class="w-4 h-4" />
        </button>

        {{-- Close --}}
        <button
            type="button"
            data-logistics-registration-modal-close
            aria-label="Close logistics registration"
            class="absolute top-4 right-4 z-20
                   w-11 h-11
                   rounded-full
                   bg-white/90 backdrop-blur
                   border border-gray-border/70
                   shadow-sm shadow-navy/10
                   text-navy/55
                   flex items-center justify-center
                   hover:bg-teal-light hover:border-teal/20
                   hover:text-teal-dark hover:-translate-y-0.5
                   focus:outline-none
                   focus:ring-4 focus:ring-teal/15
                   transition-all duration-200"
        >
            <x-lucide-x class="w-4 h-4" />
        </button>

        {{-- Accent --}}
        <div class="hidden sm:block h-1.5 bg-linear-to-r from-teal/70 via-teal to-teal-dark"></div>

        <p id="logistics-registration-modal-title" class="sr-only">Logistics Partner Registration</p>


        {{-- =====================================================
            SPLIT CONTENT — 30 / 70, same ratio as buyer/seller
        ====================================================== --}}
        <div class="grid lg:grid-cols-[3fr_7fr]">


            @include('auth.modals.shared.registration-side-panel', [
                'roleIcon' => 'lucide-truck',
                'eyebrow' => 'PARTNER WITH SHOPHOP',
                'title' => 'Deliver more.',
                'highlight' => 'Reach farther.',
                'description' => 'Register your fleet as a logistics partner and manage coverage, deliveries, documents, and payouts in one place.',
                'features' => [
                    ['icon' => 'lucide-map', 'title' => 'Choose Your Coverage', 'description' => 'Serve the cities and areas you know best'],
                    ['icon' => 'lucide-route', 'title' => 'Manage Deliveries', 'description' => 'Keep pickups and delivery work organized'],
                    ['icon' => 'lucide-shield-check', 'title' => 'Verified Partnership', 'description' => 'Operate as an approved ShopHop partner'],
                ],
                'stats' => [
                    ['value' => '500+', 'label' => 'Partner Fleets'],
                    ['value' => '80+', 'label' => 'Cities Covered'],
                    ['value' => '4.7', 'label' => 'Rating', 'star' => true],
                ],
            ])

            {{-- =================================================
                RIGHT REGISTRATION PANEL (70%)
            ================================================== --}}
            <div
                class="bg-linear-to-b from-white via-white to-gray-bg/30
                       px-5 sm:px-8 lg:px-10 xl:px-12
                       py-6 sm:py-8 lg:py-10"
            >

                <div id="logistics-registration-panel" class="max-w-2xl mx-auto lg:py-1">


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
                    <div class="mb-7">

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

                        <p class="text-teal-dark text-[11px] font-bold tracking-[0.12em] mb-2">
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
                            class="grid items-center min-w-140 sm:min-w-0"
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
                            class="grid mt-2 min-w-140 sm:min-w-0"
                            style="grid-template-columns: auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto 1fr auto;"
                        >
                            @foreach (['Terms', 'Company', 'Verify', 'Code', 'Password', 'Coverage', 'Review'] as $i => $label)
                                @if ($i > 0)
                                    <div></div>
                                @endif
                                <p
                                    class="step-label max-w-16 mx-auto text-center text-[10px] sm:text-[11px] leading-tight {{ $i === 0 ? 'text-navy font-semibold' : 'text-navy/30 font-medium' }}"
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

                        <div id="logistics-step-viewport" class="rounded-2xl bg-white">


                        {{-- =========================================
                            STEP 1 — TERMS & AGREEMENT
                        ========================================== --}}
                        <div data-step-panel="1">

                            <div class="flex items-start gap-3 bg-teal-light/60 text-teal-dark text-xs rounded-xl px-3.5 py-2.5 mb-4">
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
                                     class="h-40 sm:h-44 overflow-y-auto border border-gray-border/70 rounded-2xl p-4 sm:p-5 bg-gray-bg/40 space-y-4 text-xs text-navy/75 leading-relaxed">

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

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="logistics_agreement_rep_name" class="block text-xs font-semibold text-navy mb-2">
                                            Authorized representative — full name <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <x-lucide-user class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            <input type="text" name="agreement_rep_name" id="logistics_agreement_rep_name" value="{{ old('agreement_rep_name') }}" required
                                                   placeholder="Juan Dela Cruz"
                                                   class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        </div>
                                        <p id="logistics_agreement_rep_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                    </div>
                                    <div>
                                        <label for="logistics_agreement_date" class="block text-xs font-semibold text-navy mb-2">
                                            Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            <input type="date" name="agreement_date" id="logistics_agreement_date" value="{{ old('agreement_date', now()->toDateString()) }}" required
                                                   class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-2 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_agreement_signature" class="block text-xs font-semibold text-navy mb-2">
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

                                    {{-- Uploaded e-signature preview --}}
                                    <div
                                        id="logistics-agreement-signature-preview-card"
                                        class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                                <img
                                                    id="logistics-agreement-signature-preview-image"
                                                    src=""
                                                    alt="Uploaded e-signature preview"
                                                    class="hidden h-full w-full object-contain"
                                                >
                                                <div id="logistics-agreement-signature-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                                    <x-lucide-file-text class="w-6 h-6" />
                                                    <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                                <p id="logistics-agreement-signature-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <button
                                                        type="button"
                                                        id="logistics-agreement-signature-preview-view"
                                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                                    >
                                                        <x-lucide-maximize-2 class="w-3 h-3" />
                                                        View file
                                                    </button>

                                                    <button
                                                        type="button"
                                                        id="logistics-agreement-signature-preview-change"
                                                        class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                                    >
                                                        Choose another
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p id="logistics-step1-error" class="hidden text-xs text-red-500 font-medium mt-3 items-center gap-1.5">
                                <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                                Please read the agreement, then check the box and complete your name, date, and signature upload to continue.
                            </p>

                            <div class="flex items-center gap-3 mt-6">
                                <button
                                    type="button"
                                    id="logistics-step1-next"
                                    class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300"
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

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-building-2 class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Company Details</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div class="sm:col-span-2">
                                    <label for="logistics_company_name" class="block text-xs font-semibold text-navy mb-2">
                                        Company / business name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-store class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="text" name="company_name" id="logistics_company_name" value="{{ old('company_name') }}" required
                                               placeholder="e.g. J&amp;T Express — Cavite Hub"
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_company_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_business_registration_no" class="block text-xs font-semibold text-navy mb-2">
                                        Business registration no. <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="business_registration_no" id="logistics_business_registration_no" value="{{ old('business_registration_no') }}" required
                                           placeholder="DTI / SEC / CDA number"
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_business_registration_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_line_of_business" class="block text-xs font-semibold text-navy mb-2">
                                        Line of business <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select name="line_of_business" id="logistics_line_of_business" required
                                                class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
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
                                    <label for="logistics_rep_last_name" class="block text-xs font-semibold text-navy mb-2">Authorized representative — last name</label>
                                    <input type="text" name="rep_last_name" id="logistics_rep_last_name" value="{{ old('rep_last_name') }}"
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_rep_last_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_rep_first_name" class="block text-xs font-semibold text-navy mb-2">First name</label>
                                    <input type="text" name="rep_first_name" id="logistics_rep_first_name" value="{{ old('rep_first_name') }}"
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_rep_first_name_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                {{-- Representative ID + ID number --}}
                                <div class="sm:col-span-2">
                                    <label for="logistics_rep_valid_id" class="block text-xs font-semibold text-navy mb-2">
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

                                    {{-- Uploaded representative valid ID preview --}}
                                    <div
                                        id="logistics-rep-valid-id-preview-card"
                                        class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                                <img
                                                    id="logistics-rep-valid-id-preview-image"
                                                    src=""
                                                    alt="Uploaded representative valid ID preview"
                                                    class="hidden h-full w-full object-contain"
                                                >
                                                <div id="logistics-rep-valid-id-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                                    <x-lucide-file-text class="w-6 h-6" />
                                                    <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                                <p id="logistics-rep-valid-id-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <button
                                                        type="button"
                                                        id="logistics-rep-valid-id-preview-view"
                                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                                    >
                                                        <x-lucide-maximize-2 class="w-3 h-3" />
                                                        View file
                                                    </button>

                                                    <button
                                                        type="button"
                                                        id="logistics-rep-valid-id-preview-change"
                                                        class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                                    >
                                                        Choose another
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="logistics_rep_id_number" class="block text-xs font-semibold text-navy mb-2">
                                        ID number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rep_id_number" id="logistics_rep_id_number" value="{{ old('rep_id_number') }}" required
                                           placeholder="e.g. N01-23-456789"
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p class="text-[11px] text-navy/40 mt-1">Letters and numbers only (hyphens okay) — matches the ID uploaded above.</p>
                                    <p id="logistics_rep_id_number_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                <div>
                                    <label for="logistics_rep_sex" class="block text-xs font-semibold text-navy mb-2">Sex <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select name="rep_sex" id="logistics_rep_sex" required
                                                class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition appearance-none">
                                            <option value="">Select</option>
                                            <option value="male" @selected(old('rep_sex') === 'male')>Male</option>
                                            <option value="female" @selected(old('rep_sex') === 'female')>Female</option>
                                        </select>
                                        <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                    </div>
                                </div>
                                <div>
                                    <label for="logistics_rep_birthday" class="block text-xs font-semibold text-navy mb-2">Birthday <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-calendar class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="date" name="rep_birthday" id="logistics_rep_birthday" value="{{ old('rep_birthday') }}" required
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-2 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_email" class="block text-xs font-semibold text-navy mb-2">E-mail <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-mail class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="email" name="email" id="logistics_email" value="{{ old('email') }}" required
                                               placeholder="ops@company.com"
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_email_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_contact_no" class="block text-xs font-semibold text-navy mb-2">Contact no. <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <x-lucide-phone class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="tel" name="contact_no" id="logistics_contact_no" value="{{ old('contact_no') }}" required
                                               inputmode="tel" placeholder="+63"
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-4 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    </div>
                                    <p id="logistics_contact_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                                {{-- Address cascade --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-navy mb-2">
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
                                                        class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none">
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
                                                        class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none">
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
                                                        class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none">
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
                                                        class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 pr-9 py-3 text-sm text-navy outline-none disabled:bg-gray-bg disabled:text-navy/35 hover:border-navy/25 focus:border-teal focus:ring-4 focus:ring-teal/15 transition appearance-none">
                                                    <option value="">Select city/municipality first</option>
                                                </select>
                                                <x-lucide-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                            </div>
                                            <p id="logistics_barangay_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="logistics_street_no" class="block text-xs font-semibold text-navy mb-2">
                                        Street no. / name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="street_no" id="logistics_street_no" value="{{ old('street_no') }}" required
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_street_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>
                                <div>
                                    <label for="logistics_unit_no" class="block text-xs font-semibold text-navy mb-2">
                                        Unit / house no. <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="unit_no" id="logistics_unit_no" value="{{ old('unit_no') }}" required
                                           class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 py-3 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                    <p id="logistics_unit_no_error" class="hidden text-[11px] text-red-500 mt-1"></p>
                                </div>

                            </div>

                            <p id="logistics-step2-error" class="text-xs text-red-500 font-medium mt-3 flex items-center gap-1.5">
                                <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                                Please complete all required fields correctly — including your full business
                                address and representative ID — before continuing.
                            </p>

                            <div class="flex items-center gap-3 mt-6">
                                <button type="button" id="logistics-step2-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step2-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
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
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step3-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 4 — ENTER CODE (OTP)
                        ========================================== --}}
                        <div data-step-panel="4" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Enter verification code</p>
                            </div>

                            <p class="text-xs text-navy/50 mb-4">
                                Enter the 6-digit code we sent to
                                <span class="font-semibold text-navy" id="logistics-otp-email-display">—</span>.
                            </p>

                            <div class="flex items-center justify-between gap-2 sm:gap-3" id="logistics-otp-boxes">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                                    class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border/80 bg-white shadow-sm text-navy outline-none focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
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
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step4-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 5 — CREATE PASSWORD
                        ========================================== --}}
                        <div data-step-panel="5" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
                                <x-lucide-lock class="w-4 h-4 text-teal-dark" />
                                <p class="text-sm font-semibold text-navy">Create your login password</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>
                                    <label for="logistics_password" class="block text-xs font-semibold text-navy mb-2">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="password" name="password" id="logistics_password" minlength="8" required
                                               autocomplete="new-password" placeholder="Minimum 8 characters"
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-11 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
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
                                    <label for="logistics_password_confirmation" class="block text-xs font-semibold text-navy mb-2">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-lucide-lock class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/30" />
                                        <input type="password" name="password_confirmation" id="logistics_password_confirmation" minlength="8" required
                                               autocomplete="new-password" placeholder="Re-enter password"
                                               class="w-full min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm pl-11 pr-11 py-3 text-sm text-navy placeholder:text-navy/30 outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
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
                                 class="mt-4 rounded-xl border border-gray-border/70 bg-gray-bg p-3 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5">

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
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step5-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
                                    Next
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </button>
                            </div>

                        </div>


                        {{-- =========================================
                            STEP 6 — COVERAGE & DOCUMENTS
                        ========================================== --}}
                        <div data-step-panel="6" class="hidden">

                            <div class="flex items-center gap-2 mb-4">
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
                                        class="flex-1 min-h-12 rounded-xl border border-gray-border/80 bg-white shadow-sm px-4 text-sm text-navy outline-none hover:border-navy/20 focus:border-teal focus:ring-4 focus:ring-teal/10 transition">
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

                            <div class="grid sm:grid-cols-2 gap-4 mt-5">
                                <div>
                                    <label for="logistics_business_permit" class="block text-xs font-semibold text-navy mb-2">
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
                                        <input type="file" name="business_permit" id="logistics_business_permit" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden" required>
                                    </label>
                                    <p id="logistics_business_permit_error" class="hidden text-[11px] text-red-500 mt-1"></p>

                                    {{-- Uploaded business permit preview --}}
                                    <div
                                        id="logistics-business-permit-preview-card"
                                        class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                                <img
                                                    id="logistics-business-permit-preview-image"
                                                    src=""
                                                    alt="Uploaded business permit preview"
                                                    class="hidden h-full w-full object-contain"
                                                >
                                                <div id="logistics-business-permit-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                                    <x-lucide-file-text class="w-6 h-6" />
                                                    <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                                <p id="logistics-business-permit-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <button
                                                        type="button"
                                                        id="logistics-business-permit-preview-view"
                                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                                    >
                                                        <x-lucide-maximize-2 class="w-3 h-3" />
                                                        View file
                                                    </button>

                                                    <button
                                                        type="button"
                                                        id="logistics-business-permit-preview-change"
                                                        class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                                    >
                                                        Choose another
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="logistics_accreditation_docs" class="block text-xs font-semibold text-navy mb-2">
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
                                        <input type="file" name="accreditation_docs" id="logistics_accreditation_docs" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden">
                                    </label>

                                    {{-- Uploaded accreditation document preview --}}
                                    <div
                                        id="logistics-accreditation-docs-preview-card"
                                        class="hidden mt-3 rounded-2xl border border-gray-border/70 bg-white p-3 shadow-sm"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-bg/80 ring-1 ring-gray-border/60">
                                                <img
                                                    id="logistics-accreditation-docs-preview-image"
                                                    src=""
                                                    alt="Uploaded accreditation document preview"
                                                    class="hidden h-full w-full object-contain"
                                                >
                                                <div id="logistics-accreditation-docs-preview-pdf" class="hidden flex-col items-center justify-center text-center text-teal-dark">
                                                    <x-lucide-file-text class="w-6 h-6" />
                                                    <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">PDF</span>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">Preview ready</p>
                                                <p id="logistics-accreditation-docs-preview-status" class="mt-1 truncate text-xs font-semibold text-navy">Uploaded file</p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <button
                                                        type="button"
                                                        id="logistics-accreditation-docs-preview-view"
                                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 text-[10.5px] font-bold text-white hover:bg-navy/90 focus:outline-none focus:ring-4 focus:ring-navy/10 transition"
                                                    >
                                                        <x-lucide-maximize-2 class="w-3 h-3" />
                                                        View file
                                                    </button>

                                                    <button
                                                        type="button"
                                                        id="logistics-accreditation-docs-preview-change"
                                                        class="inline-flex min-h-8 items-center justify-center rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold text-teal-dark hover:bg-teal-light hover:text-navy transition"
                                                    >
                                                        Choose another
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mt-6">
                                <button type="button" id="logistics-step6-back"
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="button" id="logistics-step6-next"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
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
                                        class="inline-flex items-center justify-center gap-2 border border-gray-border/80 text-navy text-sm font-semibold min-h-12 px-6 py-3 rounded-xl hover:bg-gray-bg transition">
                                    <x-lucide-arrow-left class="w-4 h-4" />
                                    Back
                                </button>
                                <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold min-h-12 px-5 py-3 rounded-xl shadow-md shadow-teal/20 hover:-translate-y-0.5 transition-all duration-300">
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


{{-- =========================================================
    LOGISTICS UPLOADED FILE PREVIEW
========================================================= --}}
<div
    id="logistics-file-preview-modal"
    class="fixed inset-0 z-140 hidden items-center justify-center p-4 sm:p-6"
    aria-hidden="true"
>
    <button
        type="button"
        data-logistics-file-preview-close
        aria-label="Close uploaded file preview"
        class="absolute inset-0 h-full w-full bg-navy/80 backdrop-blur-sm"
    ></button>

    <div class="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-white/15 bg-white shadow-2xl shadow-navy/30">
        <div class="flex items-center justify-between gap-4 border-b border-gray-border/70 px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-teal-dark">Uploaded file preview</p>
                <p id="logistics-file-preview-name" class="mt-0.5 truncate text-sm font-semibold text-navy">Uploaded file</p>
            </div>

            <button
                type="button"
                data-logistics-file-preview-close
                aria-label="Close preview"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-bg text-navy/45 hover:bg-teal-light hover:text-teal-dark focus:outline-none focus:ring-4 focus:ring-teal/15 transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="flex min-h-[50vh] flex-1 items-center justify-center overflow-auto bg-gray-bg/80 p-3 sm:p-5">
            <img
                id="logistics-file-preview-full-image"
                src=""
                alt="Full uploaded file preview"
                class="hidden max-h-[75vh] max-w-full rounded-2xl bg-white object-contain shadow-sm"
            >

            <iframe
                id="logistics-file-preview-pdf-frame"
                title="Uploaded PDF preview"
                src=""
                class="hidden h-[70vh] w-full rounded-2xl border border-gray-border/70 bg-white"
            ></iframe>
        </div>
    </div>
</div>

