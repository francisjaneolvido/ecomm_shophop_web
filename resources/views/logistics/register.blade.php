{{-- resources/views/logistics/register.blade.php --}}

@extends('layouts.app')

@section('title', 'Become a Logistics Partner — ShopHop')

@section('content')

{{--
    ============================================================
    BACKEND NOTES — new / changed fields in this form
    ============================================================
    New fields the controller/validation/migration need to handle:
      - rep_id_number      (string, alphanumeric + "-")   — new
      - rep_valid_id       (file, moved from step 3 to step 2, unchanged)
      - region              (string)                        — new
      - province             (string, now select-driven, same name as before)
      - municipality        (string, now select-driven, same name as before)
      - barangay             (string, now select-driven, same name as before)
      - street_no            (string) — replaces old "street_address"
      - unit_no              (string) — replaces old "street_address"
      - coverage_areas[]     (string[] of province/region names — same name as before)
      - coverage_cities[{province}] (string, "ALL" or "|"-separated list of city/municipality names) — new
      - otp_code             (string, 6 digits) — new, from the account-verification steps
      - password / password_confirmation (string) — new, sets the partner's login password

    New steps added: Verify Email (3) → Enter Code (4) → Create Password (5),
    inserted between Company Details and Coverage & Documents. Like the ID
    auto-fill, these are UX-only for now — no code is actually sent or
    checked yet. You'll want to wire up:
      - POST to send the verification code (Step 2 → Step 3)
      - POST to verify the OTP (Step 4 → Step 5)
      - POST to resend the code (see the "Didn't get a code?" button)

    Also new: POST /logistics/detect-id — placeholder endpoint for ID
    auto-fill (see detectRepresentativeId() in the script below). It
    doesn't exist yet; the front end fails gracefully until it's built.
============================================================ --}}

{{-- =========================================================
    INTRO
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg">
    <div class="absolute -top-28 -right-28 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-teal/10"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-center">
        <div class="inline-flex items-center gap-2 bg-teal-light text-teal-dark px-3.5 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium mb-5 sm:mb-6">
            <span class="w-2 h-2 rounded-full bg-teal"></span>
            Partner with ShopHop
        </div>

        <h1 class="text-navy mb-4">
            Deliver for <span class="text-teal">Every Seller</span> on ShopHop.
        </h1>

        <p class="text-navy/65 text-sm sm:text-base lg:text-lg leading-relaxed max-w-xl mx-auto">
            Register your fleet as an accredited Logistics Partner. Once approved,
            your riders apply directly under your company and you manage every
            pickup, delivery, and payout from your own console.
        </p>
    </div>
</section>

{{-- =========================================================
    APPLICATION FORM
========================================================= --}}
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="flex items-center gap-3 bg-teal-light text-teal-dark text-sm font-medium px-4 py-3 rounded-xl mb-8">
                <x-lucide-check-circle-2 class="w-4 h-4 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('logistics.register.store') }}" enctype="multipart/form-data" data-wizard>
            @csrf

            {{-- Step tracker --}}
            <div class="flex items-center mb-10">
                @foreach (['Terms & Agreement', 'Company Details', 'Verify Email', 'Enter Code', 'Create Password', 'Coverage & Documents', 'Review & Submit'] as $i => $label)
                    <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                        <div class="flex flex-col items-center gap-2">
                            <div data-step-circle="{{ $i + 1 }}"
                                 class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $i === 0 ? 'bg-teal text-white' : 'bg-gray-bg text-navy/40 border border-gray-border' }}">
                                {{ $i + 1 }}
                            </div>
                            <span data-step-label="{{ $i + 1 }}"
                                  class="text-[11px] font-semibold text-center max-w-24 {{ $i === 0 ? 'text-navy' : 'text-navy/40' }}">
                                {{ $label }}
                            </span>
                        </div>
                        @unless ($loop->last)
                            <div class="flex-1 h-0.5 bg-gray-border mx-2 -mt-5"></div>
                        @endunless
                    </div>
                @endforeach
            </div>

            {{-- STEP 1 — Terms & Agreement (gate) --}}
            <div data-step="1">
                <div class="flex items-start gap-3 bg-teal-light/60 text-teal-dark text-xs sm:text-sm rounded-xl px-4 py-3 mb-4">
                    <x-lucide-info class="w-4 h-4 shrink-0 mt-0.5" />
                    <span>Please read the Courier Terms &amp; Agreement in full. You'll need to
                        scroll to the end before you can accept and continue.</span>
                </div>

                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-navy/50 uppercase tracking-wide">Courier Terms &amp; Agreement</p>
                    <a href="{{ route('logistics.terms') }}" target="_blank" rel="noopener"
                       class="text-xs font-semibold text-teal-dark hover:text-teal underline underline-offset-2 flex items-center gap-1">
                        See full terms
                        <x-lucide-external-link class="w-3 h-3" />
                    </a>
                </div>

                <div data-terms-scroll
                     class="h-72 sm:h-80 overflow-y-auto border border-gray-border rounded-2xl p-5 sm:p-6 bg-gray-bg/40 space-y-5 text-xs sm:text-sm text-navy/75 leading-relaxed">

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
                        This is a summary. The <a href="{{ route('logistics.terms') }}" target="_blank" rel="noopener" class="text-teal-dark underline underline-offset-2">full Courier Terms &amp; Agreement</a> governs in the event of any conflict.
                    </p>
                </div>

                <p data-terms-scroll-hint class="text-[11px] text-navy/45 mt-2 flex items-center gap-1.5">
                    <x-lucide-arrow-down class="w-3.5 h-3.5" />
                    Scroll to the end of the agreement to unlock the checkbox below.
                </p>

                <div class="bg-gray-bg rounded-2xl p-5 sm:p-6 mt-5 space-y-5">
                    <label class="flex items-start gap-3 text-sm text-navy">
                        <input type="checkbox" name="terms_agree" id="terms_agree" required disabled
                               data-terms-checkbox
                               class="mt-0.5 w-4 h-4 accent-teal disabled:opacity-40">
                        <span>
                            I confirm that I have read and understood the ShopHop Courier Terms
                            &amp; Agreement in full, and I agree, on behalf of the company named
                            in this application, to be bound by its terms. <span class="text-red-500">*</span>
                        </span>
                    </label>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-navy mb-1.5">
                                Authorized representative — full name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="agreement_rep_name" value="{{ old('agreement_rep_name') }}" required
                                   pattern="[^0-9]*" title="Names should not contain numbers."
                                   placeholder="Juan Dela Cruz"
                                   class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            <p class="text-[11px] text-navy/40 mt-1">Letters only — no numbers.</p>
                            <p class="hidden text-xs text-red-500 mt-1" data-client-error="agreement_rep_name">Please remove any numbers from the name.</p>
                            @error('agreement_rep_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-navy mb-1.5">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="agreement_date" value="{{ old('agreement_date', now()->toDateString()) }}" required
                                   class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy mb-1.5">
                            E-signature <span class="text-red-500">*</span>
                        </label>
                        <label class="flex items-center gap-2 border border-dashed border-gray-border rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal/40 hover:text-navy/70 transition bg-white">
                            <x-lucide-pen-line class="w-4 h-4 shrink-0" />
                            <span data-file-label="agreement_signature">Upload a photo or scan of your signature</span>
                            <input type="file" name="agreement_signature" data-file-input="agreement_signature"
                                   accept="image/png,image/jpeg,image/webp,application/pdf" class="hidden" required>
                        </label>
                        <p class="text-[11px] text-navy/45 mt-1.5">
                            Accepted formats: JPG, PNG, WEBP, or PDF, up to 5MB. You may upload a
                            photo of your handwritten signature or a scanned signed copy. This
                            constitutes a legally binding electronic signature under the
                            Electronic Commerce Act of 2000.
                        </p>
                        @error('agreement_signature') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p data-terms-error class="hidden text-xs text-red-500 font-medium mt-3 flex items-center gap-1.5">
                    <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                    Please read the agreement, then check the box and complete your name, date, and signature upload to continue.
                </p>
            </div>

            {{-- STEP 2 — Company details --}}
            <div data-step="2" class="hidden grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Company / business name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                           pattern="(?=.*[A-Za-zÀ-ÖØ-öø-ÿÑñ]).+" title="Business name must contain at least one letter."
                           placeholder="e.g. J&amp;T Express — Cavite Hub"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    <p class="text-[11px] text-navy/40 mt-1">Numbers and special characters are okay, but the name can't be made up of only numbers/symbols.</p>
                    <p class="hidden text-xs text-red-500 mt-1" data-client-error="company_name">Business name needs at least one letter.</p>
                    @error('company_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Business registration no. <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="business_registration_no" value="{{ old('business_registration_no') }}" required
                           placeholder="DTI / SEC / CDA number"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    @error('business_registration_no') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Line of business <span class="text-red-500">*</span>
                    </label>
                    <select name="line_of_business" required
                            class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select</option>
                        <option value="motorcycle_courier" @selected(old('line_of_business') === 'motorcycle_courier')>Motorcycle courier</option>
                        <option value="van_truck_freight" @selected(old('line_of_business') === 'van_truck_freight')>Van / truck freight</option>
                        <option value="same_day" @selected(old('line_of_business') === 'same_day')>Same-day delivery</option>
                        <option value="other" @selected(old('line_of_business') === 'other')>Other</option>
                    </select>
                    @error('line_of_business') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">Authorized representative — last name</label>
                    <input type="text" name="rep_last_name" value="{{ old('rep_last_name') }}"
                           pattern="[^0-9]*" title="Names should not contain numbers."
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    <p class="hidden text-xs text-red-500 mt-1" data-client-error="rep_last_name">Please remove any numbers from the name.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">First name</label>
                    <input type="text" name="rep_first_name" value="{{ old('rep_first_name') }}"
                           pattern="[^0-9]*" title="Names should not contain numbers."
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    <p class="hidden text-xs text-red-500 mt-1" data-client-error="rep_first_name">Please remove any numbers from the name.</p>
                </div>

                {{-- Representative ID — moved here from Step 3, plus a new ID number field --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Representative's valid ID <span class="text-red-500">*</span>
                    </label>
                    <label class="flex items-center gap-2 border border-dashed border-gray-border rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal/40 hover:text-navy/70 transition">
                        <x-lucide-upload class="w-4 h-4 shrink-0" />
                        <span data-file-label="rep_valid_id">Drag file or browse</span>
                        <input type="file" name="rep_valid_id" data-file-input="rep_valid_id"
                               accept="image/png,image/jpeg,image/webp,application/pdf" class="hidden" required>
                    </label>
                    <p class="text-[11px] text-navy/45 mt-1.5" data-id-detect-status>
                        We'll try to read your name and ID number off this automatically once
                        it's uploaded. <span class="text-navy/35">(Auto-fill is a work in progress — please double-check the fields below either way.)</span>
                    </p>
                    @error('rep_valid_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        ID number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="rep_id_number" value="{{ old('rep_id_number') }}" required
                           data-id-autofill="id_number"
                           pattern="[A-Za-z0-9\-]+" title="ID number can contain letters, numbers, and hyphens only."
                           placeholder="e.g. N01-23-456789"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    <p class="text-[11px] text-navy/40 mt-1">Letters and numbers only (hyphens okay) — matches the ID uploaded above.</p>
                    <p class="hidden text-xs text-red-500 mt-1" data-client-error="rep_id_number">ID number can only contain letters, numbers, and hyphens.</p>
                    @error('rep_id_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">Sex <span class="text-red-500">*</span></label>
                    <select name="rep_sex" required
                            class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <option value="">Select</option>
                        <option value="male" @selected(old('rep_sex') === 'male')>Male</option>
                        <option value="female" @selected(old('rep_sex') === 'female')>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">Birthday <span class="text-red-500">*</span></label>
                    <input type="date" name="rep_birthday" value="{{ old('rep_birthday') }}" required
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="ops@company.com"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">Contact no. <span class="text-red-500">*</span></label>
                    <input type="tel" name="contact_no" value="{{ old('contact_no') }}" required
                           pattern="[0-9+\-]+" title="Numbers only — + and - are allowed, no letters or other characters."
                           inputmode="tel"
                           placeholder="+63"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    <p class="hidden text-xs text-red-500 mt-1" data-client-error="contact_no">Numbers only — only + and - are allowed as symbols.</p>
                </div>

                {{-- Business address — cascading Region → Province → City/Municipality → Barangay --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Business address <span class="text-red-500">*</span>
                    </label>
                    <p class="text-[11px] text-navy/45 mb-2">
                        Pick your region first — province, city/municipality, and barangay choices
                        narrow down automatically and are kept alphabetically sorted. (You can also
                        just start typing inside a dropdown to jump to a name.)
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Region <span class="text-red-500">*</span></label>
                            <select name="region" data-address-level="region" required
                                    class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal disabled:bg-gray-bg disabled:text-navy/40">
                                <option value="">Loading regions…</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Province <span class="text-red-500">*</span></label>
                            <select name="province" data-address-level="province" required disabled
                                    class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal disabled:bg-gray-bg disabled:text-navy/40">
                                <option value="">Select region first</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">City / Municipality <span class="text-red-500">*</span></label>
                            <select name="municipality" data-address-level="municipality" required disabled
                                    class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal disabled:bg-gray-bg disabled:text-navy/40">
                                <option value="">Select province first</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-navy/50 mb-1">Barangay <span class="text-red-500">*</span></label>
                            <select name="barangay" data-address-level="barangay" required disabled
                                    class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal disabled:bg-gray-bg disabled:text-navy/40">
                                <option value="">Select city/municipality first</option>
                            </select>
                        </div>
                    </div>
                    @error('region') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @error('province') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @error('municipality') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @error('barangay') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Street no. / name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="street_no" value="{{ old('street_no') }}" required
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    @error('street_no') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Unit / house no. <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="unit_no" value="{{ old('unit_no') }}" required
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    @error('unit_no') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <p data-step2-error class="sm:col-span-2 hidden text-xs text-red-500 font-medium mt-1 flex items-center gap-1.5">
                    <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                    Please complete all required fields correctly — including your full business
                    address and representative ID — before continuing.
                </p>
            </div>

            {{-- STEP 3 — Verify Email (interstitial) --}}
            <div data-step="3" class="hidden">

                <div class="flex flex-col items-center text-center py-4">

                    <div class="w-16 h-16 rounded-2xl bg-teal-light flex items-center justify-center text-teal-dark mb-5">
                        <x-lucide-mail-check class="w-8 h-8" />
                    </div>

                    <p class="text-navy font-bold text-lg">Verify your email</p>

                    <p class="text-sm text-navy/50 mt-2 leading-relaxed max-w-sm">
                        We're sending a 6-digit verification code to
                        <span class="font-semibold text-navy" data-verify-email-display>—</span>.
                        This will also be your login e-mail for the Partner Console.
                    </p>

                    <p class="text-[11px] text-navy/40 mt-3">
                        Wrong email? Go back to Company Details to update it.
                    </p>

                </div>

            </div>

            {{-- STEP 4 — Enter Code (OTP) --}}
            <div data-step="4" class="hidden">

                <div class="flex items-center gap-2 mb-4">
                    <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                    <p class="text-sm font-semibold text-navy">Enter verification code</p>
                </div>

                <p class="text-xs text-navy/50 mb-4">
                    Enter the 6-digit code we sent to
                    <span class="font-semibold text-navy" data-otp-email-display>—</span>.
                </p>

                <div class="flex items-center justify-between gap-2 sm:gap-3">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-otp-digit
                        class="otp-box w-full aspect-square text-center text-lg sm:text-xl font-bold rounded-xl border border-gray-border bg-white text-navy outline-none focus:border-teal focus:ring-2 focus:ring-teal/40 transition">

                </div>

                <input type="hidden" name="otp_code" data-otp-hidden value="">

                <p data-otp-error class="hidden text-xs text-red-500 font-medium mt-2">
                    Please enter the full 6-digit code.
                </p>

                <div class="flex items-center justify-between mt-4">

                    <p class="text-[11px] text-navy/40">
                        Didn't get a code?
                    </p>

                    <button type="button" data-resend-code disabled
                        class="text-[11px] font-semibold text-navy/30 transition">
                        <span data-resend-label>Resend in</span> <span data-resend-timer>00:30</span>
                    </button>

                </div>

            </div>

            {{-- STEP 5 — Create Password --}}
            <div data-step="5" class="hidden">

                <div class="flex items-center gap-2 mb-4">
                    <x-lucide-lock class="w-4 h-4 text-teal-dark" />
                    <p class="text-sm font-semibold text-navy">Create your login password</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-xs font-semibold text-navy mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" minlength="8" required
                                   autocomplete="new-password" placeholder="Minimum 8 characters"
                                   data-account-password
                                   class="w-full border border-gray-border rounded-xl px-4 py-3 pr-11 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal transition">
                            <button type="button" data-toggle-password="password"
                                    aria-label="Show password" aria-pressed="false"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40 hover:text-navy transition">
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
                        <p class="hidden text-xs text-red-500 mt-1" data-client-error="password"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy mb-1.5">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" minlength="8" required
                                   autocomplete="new-password" placeholder="Re-enter password"
                                   data-account-password-confirmation
                                   class="w-full border border-gray-border rounded-xl px-4 py-3 pr-11 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal transition">
                            <button type="button" data-toggle-password="password_confirmation"
                                    aria-label="Show password" aria-pressed="false"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/40 hover:text-navy transition">
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
                        <p class="hidden text-xs text-red-500 mt-1" data-client-error="password_confirmation"></p>
                    </div>

                </div>

                {{-- Password requirements checklist --}}
                <div class="mt-4 rounded-xl border border-gray-border bg-gray-bg p-4 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2" data-password-requirements>

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

                </div>

            </div>

            {{-- STEP 6 — Coverage & documents --}}
            <div data-step="6" class="hidden grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-2">
                        Coverage areas serviced <span class="text-red-500">*</span>
                    </label>
                    <p class="hidden text-[11px] text-teal-dark mb-3" data-coverage-suggestion-note>
                        Suggested based on your business region (<span data-coverage-region-name></span>)
                        — remove anything you don't cover, or add more provinces below.
                    </p>

                    <div data-coverage-list class="space-y-3 mb-4">
                        <p class="text-xs text-navy/40" data-coverage-empty>
                            No coverage areas yet. Set your business region/province in Step 2, or add one below.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <select data-coverage-add-select
                                class="flex-1 border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                            <option value="">Loading provinces…</option>
                        </select>
                        <button type="button" data-coverage-add-btn
                                class="shrink-0 bg-navy text-white text-sm font-semibold px-5 py-3 rounded-xl hover:bg-navy/90 transition">
                            + Add province
                        </button>
                    </div>
                    <p data-coverage-error class="hidden text-xs text-red-500 font-medium mt-2">
                        Add at least one coverage area before continuing.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Business permit <span class="text-red-500">*</span>
                    </label>
                    <label class="flex items-center gap-2 border border-dashed border-gray-border rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal/40 hover:text-navy/70 transition">
                        <x-lucide-upload class="w-4 h-4 shrink-0" />
                        <span data-file-label="business_permit">Drag file or browse</span>
                        <input type="file" name="business_permit" data-file-input="business_permit" class="hidden" required>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">Accreditation / franchise docs</label>
                    <label class="flex items-center gap-2 border border-dashed border-gray-border rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal/40 hover:text-navy/70 transition">
                        <x-lucide-upload class="w-4 h-4 shrink-0" />
                        <span data-file-label="accreditation_docs">Drag file or browse</span>
                        <input type="file" name="accreditation_docs" data-file-input="accreditation_docs" class="hidden">
                    </label>
                </div>
            </div>

            {{-- STEP 7 — Review & submit --}}
            <div data-step="7" class="hidden">
                <div class="bg-gray-bg rounded-2xl p-6 space-y-3 text-sm">
                    <p class="text-xs font-semibold text-navy/50 uppercase tracking-wide mb-1">Review before submitting</p>
                    <div class="flex justify-between"><span class="text-navy/55">Company</span><span class="font-semibold text-navy" data-review="company_name">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Registration no.</span><span class="font-semibold text-navy" data-review="business_registration_no">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Representative ID no.</span><span class="font-semibold text-navy" data-review="rep_id_number">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">E-mail</span><span class="font-semibold text-navy" data-review="email">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Contact no.</span><span class="font-semibold text-navy" data-review="contact_no">—</span></div>

                    <div class="flex justify-between pt-3 border-t border-gray-border">
                        <span class="text-navy/55">Email verification</span>
                        <span class="font-semibold text-teal-dark flex items-center gap-1.5" data-review-otp>
                            <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                            Code entered
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-navy/55">Login password</span>
                        <span class="font-semibold text-teal-dark flex items-center gap-1.5" data-review-password>
                            <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                            Set
                        </span>
                    </div>

                    <div class="pt-3 border-t border-gray-border">
                        <span class="text-navy/55 block mb-1">Business address</span>
                        <span class="font-semibold text-navy block" data-review-address>—</span>
                    </div>
                    <div>
                        <span class="text-navy/55 block mb-1">Coverage areas</span>
                        <span class="font-semibold text-navy block" data-review-coverage>—</span>
                    </div>

                    <div class="flex justify-between pt-3 border-t border-gray-border">
                        <span class="text-navy/55">Terms &amp; Agreement</span>
                        <span class="font-semibold text-teal-dark flex items-center gap-1.5" data-review-terms>
                            <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                            Accepted
                        </span>
                    </div>
                    <div class="flex justify-between"><span class="text-navy/55">Signed by</span><span class="font-semibold text-navy" data-review="agreement_rep_name">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Date signed</span><span class="font-semibold text-navy" data-review="agreement_date">—</span></div>
                    <div class="flex justify-between">
                        <span class="text-navy/55">Signature file</span>
                        <span class="font-semibold text-navy" data-review-signature-file>—</span>
                    </div>
                </div>

                <label class="flex items-start gap-2.5 mt-6 text-xs text-navy/65">
                    <input type="checkbox" required class="mt-0.5">
                    I certify that the information and documents provided are accurate, and I
                    understand ShopHop will review this application before approval.
                </label>
            </div>

            {{-- Navigation --}}
            <div class="flex justify-between gap-3 mt-8">
                <button type="button" data-wizard-back
                        class="hidden border-2 border-navy text-navy hover:bg-navy hover:text-white text-sm font-semibold px-6 py-3 rounded-full transition-all duration-300">
                    Back
                </button>
                <div class="flex-1"></div>
                <button type="button" data-wizard-next
                        class="bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-7 py-3 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20">
                    Continue
                </button>
                <button type="submit" data-wizard-submit
                        class="hidden bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-7 py-3 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20">
                    Submit application
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizard = document.querySelector('[data-wizard]');
        if (!wizard) return;

        const steps = wizard.querySelectorAll('[data-step]');
        const backBtn = wizard.querySelector('[data-wizard-back]');
        const nextBtn = wizard.querySelector('[data-wizard-next]');
        const submitBtn = wizard.querySelector('[data-wizard-submit]');
        let current = 1;
        const total = steps.length;

        // =====================================================
        // PSGC (Philippine Standard Geographic Code) API
        // Public, read-only, no API key.
        //
        // Uses https://psgc.gitlab.io/api — the same API already used
        // (and confirmed working) on the customer registration page —
        // instead of psgc.cloud, which was returning "couldn't load"
        // errors for region data.
        //
        // Confirmed-working endpoints (trailing slash, matches the
        // customer registration form exactly):
        //   GET /provinces/
        //   GET /provinces/{province_code}/cities-municipalities/
        //   GET /cities-municipalities/{city_code}/barangays/
        //   GET /regions/{region_code}/            (single region)
        //
        // There's no confirmed bare "list all regions" endpoint on this
        // API, so — since the 17 PH regions are official, stable
        // government codes that essentially never change (unlike city/
        // barangay boundaries) — they're hardcoded below instead of
        // fetched. Each province record already carries its regionCode,
        // so provinces are still filtered live from the real API data.
        // =====================================================
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
            allCitiesMunicipalities: null, // lazy-loaded fallback for province-less regions (e.g. NCR)
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
            // Static, stable reference data — no network call needed.
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

        // Fallback for province-less regions (currently just NCR). This API
        // doesn't expose a confirmed "cities by region" endpoint, so this
        // loads the full nationwide cities/municipalities list once (cached
        // after that) and filters client-side. If you deploy this, it's
        // worth doing one manual test of selecting NCR as the region to
        // confirm the field names below still match the live API.
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

        function fillSelect(selectEl, items, placeholder) {
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

        // ---- Address cascade (Step 2) ----
        const regionSelect = wizard.querySelector('[data-address-level="region"]');
        const provinceSelect = wizard.querySelector('[data-address-level="province"]');
        const municipalitySelect = wizard.querySelector('[data-address-level="municipality"]');
        const barangaySelect = wizard.querySelector('[data-address-level="barangay"]');

        const addressState = { regionCode: '', regionName: '', provinceCode: '', provinceName: '', isNcrLike: false };

        async function initRegions() {
            try {
                const regions = await loadRegions();
                fillSelect(regionSelect, regions, 'Select region');
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
                    const provinces = await loadProvinces(addressState.regionCode);
                    if (provinces.length === 0) {
                        // Province-less region (e.g. NCR) — cities/municipalities sit directly under the region.
                        addressState.isNcrLike = true;
                        addressState.provinceCode = '';
                        addressState.provinceName = addressState.regionName;
                        provinceSelect.innerHTML = '<option value="' + addressState.regionName + '">' + addressState.regionName + ' (no provinces)</option>';
                        provinceSelect.value = addressState.regionName;
                        provinceSelect.disabled = true;

                        municipalitySelect.innerHTML = '<option value="">Loading cities/municipalities…</option>';
                        const cities = await loadCitiesByRegion(addressState.regionCode);
                        fillSelect(municipalitySelect, cities, 'Select city/municipality');
                        municipalitySelect.disabled = false;
                    } else {
                        addressState.isNcrLike = false;
                        fillSelect(provinceSelect, provinces, 'Select province');
                        provinceSelect.disabled = false;
                    }
                } catch (e) {
                    provinceSelect.innerHTML = '<option value="">Couldn\'t load provinces</option>';
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
                    fillSelect(municipalitySelect, cities, 'Select city/municipality');
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
                    fillSelect(barangaySelect, brgys, 'Select barangay');
                    barangaySelect.disabled = false;
                } catch (e) {
                    barangaySelect.innerHTML = '<option value="">Couldn\'t load barangays</option>';
                }
            });
        }

        if (regionSelect) initRegions();

        // ---- Coverage areas builder (Step 3) ----
        const coverageListEl = wizard.querySelector('[data-coverage-list]');
        const coverageEmptyEl = wizard.querySelector('[data-coverage-empty]');
        const coverageAddSelect = wizard.querySelector('[data-coverage-add-select]');
        const coverageAddBtn = wizard.querySelector('[data-coverage-add-btn]');
        const coverageErrorEl = wizard.querySelector('[data-coverage-error]');
        const coverageSuggestNote = wizard.querySelector('[data-coverage-suggestion-note]');
        const coverageRegionNameEl = wizard.querySelector('[data-coverage-region-name]');

        const coverageChips = new Map(); // key -> { key, name, code, type: 'province'|'region', cities: Map(code -> {name, checked}), citiesLoaded, selectAll }
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

                // Province-less regions (e.g. NCR) still need to be addable as a coverage area.
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
                wrap.className = 'border border-gray-border rounded-xl p-4';
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
                        '<span class="text-sm font-bold text-navy break-words">' + chip.name + '</span>' +
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
            wizard.querySelectorAll('[data-coverage-hidden]').forEach(function (el) { el.remove(); });
            coverageChips.forEach(function (chip) {
                const provinceInput = document.createElement('input');
                provinceInput.type = 'hidden';
                provinceInput.name = 'coverage_areas[]';
                provinceInput.value = chip.name;
                provinceInput.dataset.coverageHidden = '1';
                wizard.appendChild(provinceInput);

                const checkedCities = [...chip.cities.values()].filter(function (c) { return c.checked; }).map(function (c) { return c.name; });
                const citiesValue = (chip.citiesLoaded === true && chip.selectAll) ? 'ALL' : checkedCities.join('|');

                const cityInput = document.createElement('input');
                cityInput.type = 'hidden';
                cityInput.name = 'coverage_cities[' + chip.name + ']';
                cityInput.value = citiesValue;
                cityInput.dataset.coverageHidden = '1';
                wizard.appendChild(cityInput);
            });
        }

        // ---- ID auto-fill (Step 2) ----
        // TEMPLATE / STUB. Wire this up to a real ID-recognition service later
        // (in-house OCR endpoint, or a provider like AWS Textract / Azure Form
        // Recognizer / Veriff). The endpoint below almost certainly doesn't exist
        // yet — the request is expected to fail for now, and we fall back to
        // manual entry cleanly when it does.
        const repIdFileInput = wizard.querySelector('[data-file-input="rep_valid_id"]');
        const idDetectStatus = wizard.querySelector('[data-id-detect-status]');

        async function detectRepresentativeId(file) {
            if (!idDetectStatus) return;
            idDetectStatus.textContent = 'Reading your ID…';

            try {
                const formData = new FormData();
                formData.append('id_file', file);
                const csrfInput = wizard.querySelector('input[name="_token"]');

                // TODO: point this at the real detection endpoint once it exists.
                const res = await fetch('/logistics/detect-id', {
                    method: 'POST',
                    body: formData,
                    headers: csrfInput ? { 'X-CSRF-TOKEN': csrfInput.value } : {},
                });
                if (!res.ok) throw new Error('Detection endpoint not available yet');
                const data = await res.json();

                if (data.first_name) wizard.querySelector('[name="rep_first_name"]').value = data.first_name;
                if (data.last_name) wizard.querySelector('[name="rep_last_name"]').value = data.last_name;
                if (data.id_number) wizard.querySelector('[name="rep_id_number"]').value = data.id_number;

                idDetectStatus.textContent = 'Details auto-filled from your ID — please double-check them below.';
            } catch (e) {
                // Expected for now since the backend endpoint isn't built yet.
                idDetectStatus.textContent = 'Upload received. (Auto-fill from ID scanning is still in progress — please fill in the fields below manually for now.)';
            }
        }

        if (repIdFileInput) {
            repIdFileInput.addEventListener('change', function () {
                const label = wizard.querySelector('[data-file-label="rep_valid_id"]');
                if (label && repIdFileInput.files.length) label.textContent = repIdFileInput.files[0].name;
                if (repIdFileInput.files.length) detectRepresentativeId(repIdFileInput.files[0]);
            });
        }

        // ---- Verify Email / OTP / Password (Steps 3-5) ----
        // UX-only for now, same as the ID auto-fill above: nothing here
        // actually sends or checks a real code yet.
        const verifyEmailDisplay = wizard.querySelector('[data-verify-email-display]');
        const otpEmailDisplay = wizard.querySelector('[data-otp-email-display]');
        const otpInputs = Array.from(wizard.querySelectorAll('[data-otp-digit]'));
        const otpHidden = wizard.querySelector('[data-otp-hidden]');
        const otpErrorEl = wizard.querySelector('[data-otp-error]');
        const resendBtn = wizard.querySelector('[data-resend-code]');
        const resendLabel = resendBtn ? resendBtn.querySelector('[data-resend-label]') : null;
        const resendTimerEl = resendBtn ? resendBtn.querySelector('[data-resend-timer]') : null;

        function currentAccountEmail() {
            const emailField = wizard.querySelector('[name="email"]');
            return emailField ? emailField.value.trim() : '';
        }

        // TODO: point this at the real send-verification-code endpoint once
        // it exists. For now it just updates the UI and starts the resend
        // countdown, same graceful-stub pattern as detectRepresentativeId().
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
                otpInputs.forEach(function (input) { input.value = ''; });
                if (otpInputs[0]) otpInputs[0].focus();
                startResendCountdown();
            });
        }

        otpInputs.forEach(function (input, index) {

            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);
                if (this.value && index < otpInputs.length - 1) otpInputs[index + 1].focus();
                if (otpErrorEl) otpErrorEl.classList.add('hidden');
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) otpInputs[index - 1].focus();
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, otpInputs.length);
                pasted.split('').forEach(function (digit, i) { if (otpInputs[i]) otpInputs[i].value = digit; });
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

        // ---- Password step ----
        function isValidPassword(v) {
            return v.length >= 8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /[0-9]/.test(v);
        }

        function updatePasswordRequirements(value) {
            const checks = {
                length: value.length >= 8,
                uppercase: /[A-Z]/.test(value),
                lowercase: /[a-z]/.test(value),
                number: /[0-9]/.test(value),
            };
            Object.keys(checks).forEach(function (key) {
                const item = wizard.querySelector('[data-password-requirements] [data-req="' + key + '"]');
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
        }

        const accountPasswordInput = wizard.querySelector('[data-account-password]');
        const accountPasswordConfirmInput = wizard.querySelector('[data-account-password-confirmation]');

        function passwordStepValid() {
            if (!accountPasswordInput || !accountPasswordConfirmInput) return true;
            const passOk = isValidPassword(accountPasswordInput.value);
            const matchOk = accountPasswordConfirmInput.value.length > 0 && accountPasswordConfirmInput.value === accountPasswordInput.value;
            wizard.querySelector('[data-client-error="password"]').classList.toggle('hidden', passOk);
            wizard.querySelector('[data-client-error="password_confirmation"]').classList.toggle('hidden', matchOk);
            if (!passOk) wizard.querySelector('[data-client-error="password"]').textContent = 'Password needs 8+ characters, an uppercase letter, a lowercase letter, and a number.';
            if (!matchOk) wizard.querySelector('[data-client-error="password_confirmation"]').textContent = 'Passwords do not match.';
            return passOk && matchOk;
        }

        if (accountPasswordInput) {
            accountPasswordInput.addEventListener('input', function () {
                updatePasswordRequirements(accountPasswordInput.value);
                if (accountPasswordConfirmInput.value) passwordStepValid();
            });
        }
        if (accountPasswordConfirmInput) {
            accountPasswordConfirmInput.addEventListener('input', passwordStepValid);
        }

        // Generic show/hide toggle for the two password fields on this step.
        wizard.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            const targetName = btn.dataset.togglePassword;
            const input = wizard.querySelector('[name="' + targetName + '"]');
            if (!input) return;
            const showIcon = btn.querySelector('.password-icon-show');
            const hideIcon = btn.querySelector('.password-icon-hide');
            btn.addEventListener('click', function () {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                showIcon.style.display = isHidden ? 'none' : '';
                hideIcon.style.display = isHidden ? '' : 'none';
                btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                input.focus();
                const caret = input.value.length;
                input.setSelectionRange(caret, caret);
            });
        });

        // ---- Field format validators ----
        function isValidName(v) { return /^[^0-9]+$/.test(v.trim()); }
        function isValidBusinessName(v) { return /[A-Za-zÀ-ÖØ-öø-ÿÑñ]/.test(v); }
        function isValidPhone(v) { return /^[0-9+\-]+$/.test(v.trim()); }
        function isValidIdNumber(v) { return /^[A-Za-z0-9\-]+$/.test(v.trim()); }

        function bindFieldValidator(name, testFn) {
            const input = wizard.querySelector('[name="' + name + '"]');
            const errorEl = wizard.querySelector('[data-client-error="' + name + '"]');
            if (!input || !errorEl) return;
            const check = function () {
                const val = input.value.trim();
                const bad = val.length > 0 && !testFn(val);
                errorEl.classList.toggle('hidden', !bad);
            };
            input.addEventListener('input', check);
            input.addEventListener('blur', check);
        }

        bindFieldValidator('agreement_rep_name', isValidName);
        bindFieldValidator('company_name', isValidBusinessName);
        bindFieldValidator('rep_last_name', isValidName);
        bindFieldValidator('rep_first_name', isValidName);
        bindFieldValidator('contact_no', isValidPhone);
        bindFieldValidator('rep_id_number', isValidIdNumber);

        function step2Valid() {
            const required = [
                'company_name', 'business_registration_no', 'line_of_business',
                'rep_last_name', 'rep_first_name', 'rep_sex', 'rep_birthday',
                'email', 'contact_no', 'rep_id_number',
                'region', 'province', 'municipality', 'barangay',
                'street_no', 'unit_no',
            ];
            for (const name of required) {
                const el = wizard.querySelector('[name="' + name + '"]');
                if (!el || !el.value || !el.value.trim()) return false;
            }
            const repIdFile = wizard.querySelector('[name="rep_valid_id"]');
            if (!repIdFile || !repIdFile.files || repIdFile.files.length === 0) return false;

            return isValidName(wizard.querySelector('[name="rep_last_name"]').value)
                && isValidName(wizard.querySelector('[name="rep_first_name"]').value)
                && isValidBusinessName(wizard.querySelector('[name="company_name"]').value)
                && isValidPhone(wizard.querySelector('[name="contact_no"]').value)
                && isValidIdNumber(wizard.querySelector('[name="rep_id_number"]').value);
        }

        // ---- Terms gate (Step 1) ----
        const termsScroll = wizard.querySelector('[data-terms-scroll]');
        const termsHint = wizard.querySelector('[data-terms-scroll-hint]');
        const termsCheckbox = wizard.querySelector('[data-terms-checkbox]');
        const termsError = wizard.querySelector('[data-terms-error]');
        const signatureInput = wizard.querySelector('[name="agreement_signature"]');
        const step2ErrorEl = wizard.querySelector('[data-step2-error]');

        if (termsScroll && termsCheckbox) {
            const unlockCheckbox = function () {
                termsCheckbox.disabled = false;
                if (termsHint) termsHint.classList.add('hidden');
            };
            termsScroll.addEventListener('scroll', function () {
                const reachedBottom = termsScroll.scrollTop + termsScroll.clientHeight >= termsScroll.scrollHeight - 24;
                if (reachedBottom) unlockCheckbox();
            });
            if (termsScroll.scrollHeight <= termsScroll.clientHeight + 24) unlockCheckbox();
        }

        function termsAccepted() {
            const repName = wizard.querySelector('[name="agreement_rep_name"]').value.trim();
            const date = wizard.querySelector('[name="agreement_date"]').value.trim();
            const hasSignatureFile = signatureInput && signatureInput.files && signatureInput.files.length > 0;
            return !!(termsCheckbox && termsCheckbox.checked && repName && date && hasSignatureFile && isValidName(repName));
        }

        function render() {
            steps.forEach(function (panel) {
                panel.classList.toggle('hidden', Number(panel.dataset.step) !== current);
            });

            wizard.querySelectorAll('[data-step-circle]').forEach(function (circle) {
                const n = Number(circle.dataset.stepCircle);
                const label = wizard.querySelector('[data-step-label="' + n + '"]');
                circle.classList.remove('bg-teal', 'text-white', 'bg-navy', 'bg-gray-bg', 'text-navy/40', 'border', 'border-gray-border');

                if (n < current) {
                    circle.classList.add('bg-navy', 'text-white');
                    circle.textContent = '✓';
                } else if (n === current) {
                    circle.classList.add('bg-teal', 'text-white');
                    circle.textContent = n;
                } else {
                    circle.classList.add('bg-gray-bg', 'text-navy/40', 'border', 'border-gray-border');
                    circle.textContent = n;
                }

                if (label) label.classList.toggle('text-navy', n <= current);
                if (label) label.classList.toggle('text-navy/40', n > current);
            });

            backBtn.classList.toggle('hidden', current === 1);
            nextBtn.classList.toggle('hidden', current === total);
            submitBtn.classList.toggle('hidden', current !== total);

            if (current === total) {
                wizard.querySelectorAll('[data-review]').forEach(function (el) {
                    const input = wizard.querySelector('[name="' + el.dataset.review + '"]');
                    if (input && input.value) el.textContent = input.value;
                });

                const sigReview = wizard.querySelector('[data-review-signature-file]');
                if (sigReview) {
                    sigReview.textContent = (signatureInput && signatureInput.files && signatureInput.files.length)
                        ? signatureInput.files[0].name
                        : '—';
                }

                const addressReview = wizard.querySelector('[data-review-address]');
                if (addressReview) {
                    const parts = [
                        wizard.querySelector('[name="unit_no"]') && wizard.querySelector('[name="unit_no"]').value,
                        wizard.querySelector('[name="street_no"]') && wizard.querySelector('[name="street_no"]').value,
                        wizard.querySelector('[name="barangay"]') && wizard.querySelector('[name="barangay"]').value,
                        wizard.querySelector('[name="municipality"]') && wizard.querySelector('[name="municipality"]').value,
                        wizard.querySelector('[name="province"]') && wizard.querySelector('[name="province"]').value,
                        wizard.querySelector('[name="region"]') && wizard.querySelector('[name="region"]').value,
                    ].filter(Boolean);
                    addressReview.textContent = parts.length ? parts.join(', ') : '—';
                }

                const coverageReview = wizard.querySelector('[data-review-coverage]');
                if (coverageReview) {
                    const names = [...coverageChips.values()].map(function (c) { return c.name; });
                    coverageReview.textContent = names.length ? names.join(', ') : '—';
                }
            }
        }

        nextBtn.addEventListener('click', function () {
            if (current === 1 && !termsAccepted()) {
                if (termsError) termsError.classList.remove('hidden');
                wizard.querySelector('[data-terms-scroll]').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (termsError) termsError.classList.add('hidden');

            if (current === 2 && !step2Valid()) {
                if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                wizard.querySelector('[data-step="2"]').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (step2ErrorEl) step2ErrorEl.classList.add('hidden');

            if (current === 4 && !otpComplete()) {
                if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                return;
            }
            if (otpErrorEl) otpErrorEl.classList.add('hidden');

            if (current === 5 && !passwordStepValid()) {
                return;
            }

            if (current === 6 && coverageChips.size === 0) {
                if (coverageErrorEl) coverageErrorEl.classList.remove('hidden');
                return;
            }
            if (coverageErrorEl) coverageErrorEl.classList.add('hidden');

            if (current < total) {
                current++;

                if (current === 3) {
                    // Entering "Verify Email" — kick off the (stubbed) send-code call.
                    sendVerificationCode();
                }
                if (current === 4) {
                    if (otpInputs[0]) otpInputs[0].focus();
                }
                if (current === 5) {
                    serializeOtp();
                }
                if (current === 6) {
                    if (!coverageOptionsLoaded) {
                        coverageOptionsLoaded = true;
                        initCoverageAddOptions();
                    }
                    autoSuggestCoverage();
                }
                if (current === 7) {
                    serializeCoverage();
                }

                render();
                window.scrollTo({ top: wizard.offsetTop - 40, behavior: 'smooth' });
            }
        });

        backBtn.addEventListener('click', function () {
            if (current > 1) {
                current--;
                render();
                window.scrollTo({ top: wizard.offsetTop - 40, behavior: 'smooth' });
            }
        });

        wizard.querySelectorAll('[data-file-input]').forEach(function (input) {
            input.addEventListener('change', function () {
                const label = wizard.querySelector('[data-file-label="' + input.dataset.fileInput + '"]');
                if (label && input.files.length) label.textContent = input.files[0].name;
            });
        });

        // Also guard actual form submission in case someone bypasses the wizard nav
        wizard.addEventListener('submit', function (e) {
            if (!termsAccepted() || !step2Valid() || !otpComplete() || !passwordStepValid() || coverageChips.size === 0) {
                e.preventDefault();
                if (!termsAccepted()) {
                    current = 1;
                    if (termsError) termsError.classList.remove('hidden');
                } else if (!step2Valid()) {
                    current = 2;
                    if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                } else if (!otpComplete()) {
                    current = 4;
                    if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                } else if (!passwordStepValid()) {
                    current = 5;
                } else {
                    current = 6;
                    if (coverageErrorEl) coverageErrorEl.classList.remove('hidden');
                }
                render();
                wizard.querySelector('[data-step="' + current + '"]').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            serializeOtp();
            serializeCoverage();
        });

        render();
    });
</script>

@endsectiongit add .
git commit -m "Update logistics features"
git push origin user/logistics