{{-- resources/views/logistics/register.blade.php --}}

@extends('layouts.app')

@section('title', 'Become a Logistics Partner — ShopHop')

@section('content')

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
                @foreach (['Terms & Agreement', 'Company Details', 'Coverage & Documents', 'Review & Submit'] as $i => $label)
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
                                   placeholder="Juan Dela Cruz"
                                   class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
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
                           placeholder="e.g. J&amp;T Express — Cavite Hub"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
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
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-navy mb-1.5">First name</label>
                    <input type="text" name="rep_first_name" value="{{ old('rep_first_name') }}"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
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
                           placeholder="+63"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Business address <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input type="text" name="province" value="{{ old('province') }}" placeholder="Province" required
                               class="border border-gray-border rounded-xl px-3 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <input type="text" name="municipality" value="{{ old('municipality') }}" placeholder="Municipality" required
                               class="border border-gray-border rounded-xl px-3 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                        <input type="text" name="barangay" value="{{ old('barangay') }}" placeholder="Barangay" required
                               class="border border-gray-border rounded-xl px-3 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">Street, house / unit no.</label>
                    <input type="text" name="street_address" value="{{ old('street_address') }}"
                           class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
                </div>
            </div>

            {{-- STEP 3 — Coverage & documents --}}
            <div data-step="3" class="hidden grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-2">
                        Coverage areas serviced <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['Metro Manila', 'Cavite', 'Laguna', 'Batangas', 'Rizal'] as $i => $area)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="coverage_areas[]" value="{{ $area }}" class="peer hidden" {{ $i < 2 ? 'checked' : '' }}>
                                <span class="inline-flex text-xs font-semibold px-3.5 py-2 rounded-full border border-gray-border text-navy/60 peer-checked:bg-teal-light peer-checked:text-teal-dark peer-checked:border-teal/30 transition">
                                    {{ $area }}
                                </span>
                            </label>
                        @endforeach
                    </div>
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
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-navy mb-1.5">
                        Representative's valid ID <span class="text-red-500">*</span>
                    </label>
                    <label class="flex items-center gap-2 border border-dashed border-gray-border rounded-xl px-4 py-3 text-sm text-navy/50 cursor-pointer hover:border-teal/40 hover:text-navy/70 transition">
                        <x-lucide-upload class="w-4 h-4 shrink-0" />
                        <span data-file-label="rep_valid_id">Drag file or browse</span>
                        <input type="file" name="rep_valid_id" data-file-input="rep_valid_id" class="hidden" required>
                    </label>
                </div>
            </div>

            {{-- STEP 4 — Review & submit --}}
            <div data-step="4" class="hidden">
                <div class="bg-gray-bg rounded-2xl p-6 space-y-3 text-sm">
                    <p class="text-xs font-semibold text-navy/50 uppercase tracking-wide mb-1">Review before submitting</p>
                    <div class="flex justify-between"><span class="text-navy/55">Company</span><span class="font-semibold text-navy" data-review="company_name">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Registration no.</span><span class="font-semibold text-navy" data-review="business_registration_no">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">E-mail</span><span class="font-semibold text-navy" data-review="email">—</span></div>
                    <div class="flex justify-between"><span class="text-navy/55">Contact no.</span><span class="font-semibold text-navy" data-review="contact_no">—</span></div>
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

        // ---- Terms gate (Step 1) ----
        const termsScroll = wizard.querySelector('[data-terms-scroll]');
        const termsHint = wizard.querySelector('[data-terms-scroll-hint]');
        const termsCheckbox = wizard.querySelector('[data-terms-checkbox]');
        const termsError = wizard.querySelector('[data-terms-error]');
        const signatureInput = wizard.querySelector('[name="agreement_signature"]');

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
            return !!(termsCheckbox && termsCheckbox.checked && repName && date && hasSignatureFile);
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
            }
        }

        nextBtn.addEventListener('click', function () {
            if (current === 1 && !termsAccepted()) {
                if (termsError) termsError.classList.remove('hidden');
                wizard.querySelector('[data-terms-scroll]').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (termsError) termsError.classList.add('hidden');

            if (current < total) {
                current++;
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
            if (!termsAccepted()) {
                e.preventDefault();
                current = 1;
                render();
                if (termsError) termsError.classList.remove('hidden');
                wizard.querySelector('[data-terms-scroll]').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        render();
    });
</script>

@endsection