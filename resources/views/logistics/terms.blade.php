{{-- resources/views/logistics/terms.blade.php --}}

@extends('layouts.app')

@section('title', 'Courier Terms & Agreement — ShopHop')

@section('content')

{{-- =========================================================
    INTRO
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg">
    <div class="absolute -top-28 -right-28 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-teal/10"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-center">
        <div class="inline-flex items-center gap-2 bg-teal-light text-teal-dark px-3.5 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium mb-5 sm:mb-6">
            <x-lucide-file-text class="w-3.5 h-3.5" />
            Courier Partner Reference
        </div>

        <h1 class="text-navy mb-4">
            Courier Terms <span class="text-teal">&amp; Agreement</span>
        </h1>

        <p class="text-navy/65 text-sm sm:text-base lg:text-lg leading-relaxed max-w-xl mx-auto">
            This is the full Courier Terms &amp; Agreement referenced in your
            application. It governs your partnership with ShopHop once your
            application is approved.
        </p>
    </div>
</section>

{{-- =========================================================
    TERMS DOCUMENT
========================================================= --}}
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <p class="text-xs text-navy/45 mb-8">Last updated: {{ now()->format('F d, Y') }}</p>

        <p class="text-sm text-navy/75 leading-relaxed mb-10">
            This Courier Partner Agreement ("Agreement") is entered into between
            ShopHop, Inc. ("ShopHop," "we," "us") and the logistics company or sole
            proprietor completing the Logistics Partner application ("Courier
            Partner," "you"). By checking the acceptance box, providing your
            e-signature, and submitting your application, you agree to be bound by
            the terms set out below, and this Agreement takes effect upon ShopHop's
            approval of your application.
        </p>

        <div class="space-y-10">

            {{-- 1 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-handshake class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">1. Partnership Terms</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        Accreditation as a ShopHop Logistics Partner is granted at ShopHop's
                        sole discretion and is non-exclusive, non-transferable, and limited to
                        the coverage areas and service lines approved on your application.
                        This Agreement does not create an employment, joint venture, franchise,
                        or agency relationship between ShopHop and the Courier Partner, its
                        riders, drivers, or staff. The Courier Partner remains an independent
                        contractor responsible for its own workforce, vehicles, and business
                        operations. Accreditation is valid for twelve (12) months from approval
                        and is subject to renewal, re-verification of documents, and continued
                        compliance with this Agreement.
                    </p>
                </div>
            </div>

            {{-- 2 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-clipboard-list class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">2. Courier Responsibilities</h3>
                    <ul class="list-disc pl-5 space-y-1.5 text-sm text-navy/75 leading-relaxed">
                        <li>Maintain a sufficient, trained, and properly licensed rider/driver pool to service accepted orders within the coverage areas declared on your application.</li>
                        <li>Ensure every rider or driver carries a valid government ID and, where applicable, a valid driver's license and vehicle registration at all times while on active delivery.</li>
                        <li>Keep all vehicles roadworthy, insured as required by law, and fitted with any ShopHop-issued delivery bag, sticker, or identification requested for order security.</li>
                        <li>Promptly update ShopHop on changes to fleet capacity, coverage areas, business address, or authorized representatives.</li>
                        <li>Provide riders with basic handling training, including care of fragile, perishable, and high-value parcels.</li>
                    </ul>
                </div>
            </div>

            {{-- 3 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-truck class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">3. Service &amp; Delivery Standards</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The Courier Partner agrees to pick up orders within the timeframe
                        shown on the ShopHop dashboard and to meet or exceed the on-time
                        delivery rate, pickup success rate, and order-completion rate
                        thresholds published in the Logistics Partner Handbook, which is
                        incorporated into this Agreement by reference. Parcels must be handled
                        with reasonable care, kept upright and dry where indicated, and
                        delivered only to the recipient or an authorized receiver named on the
                        order. Any delay, route deviation, or inability to complete a delivery
                        must be reported through the ShopHop partner console as soon as
                        reasonably possible so the seller and buyer can be notified.
                    </p>
                </div>
            </div>

            {{-- 4 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-wallet class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">4. Fees &amp; Payment Terms</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        Delivery fees payable to the Courier Partner are computed based on
                        the rate card applicable to the Courier Partner's line of business and
                        coverage areas, as shown on the partner console. ShopHop will issue
                        payouts on a bi-monthly cycle (every 1st–15th and 16th–end of month),
                        net of any applicable platform fees, penalties, or adjustments arising
                        from this Agreement. The Courier Partner is solely responsible for
                        remitting any taxes due on income earned through the platform.
                        Disputed line items must be raised through the partner console within
                        fifteen (15) calendar days of the corresponding payout statement, after
                        which the statement is deemed accepted.
                    </p>
                </div>
            </div>

            {{-- 5 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-banknote class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">5. COD &amp; Remittance</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        Where the Courier Partner collects Cash-on-Delivery ("COD") payments
                        on ShopHop's behalf, such funds are held in trust for the seller and
                        must be remitted in full, less only the pre-agreed COD handling fee, no
                        later than three (3) banking days after successful delivery. The
                        Courier Partner must maintain accurate COD logs reconciled against the
                        ShopHop dashboard and must promptly flag any discrepancy. Repeated
                        late or short remittance is treated as a material breach and may result
                        in COD privileges being suspended, in which case the Courier Partner
                        may be limited to prepaid orders only.
                    </p>
                </div>
            </div>

            {{-- 6 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-package-x class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">6. Lost / Damaged Package Liability</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The Courier Partner is liable for the declared value of a parcel (up to
                        the cap stated in the Logistics Partner Handbook) if it is lost, stolen,
                        or damaged while in the Courier Partner's custody, unless caused by the
                        seller's defective packaging, an act of the buyer, or a force majeure
                        event. Claims must be reported within forty-eight (48) hours of
                        discovery, supported by proof of custody and, where relevant, incident
                        or police reports. Approved claims are deducted from the Courier
                        Partner's next payout or invoiced directly if the payout is
                        insufficient. Courier Partners are encouraged to carry their own cargo
                        or liability insurance.
                    </p>
                </div>
            </div>

            {{-- 7 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-rotate-ccw class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">7. Returns &amp; Failed Deliveries</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        For orders that are refused, undeliverable, or subject to a valid
                        return, the Courier Partner must attempt redelivery or pickup according
                        to the timeline in the Handbook and must return the parcel to the
                        seller's nominated hub within five (5) calendar days of the failed
                        attempt. Failed-delivery attempts must be logged with a reason code and,
                        where applicable, photo or geotagged proof of the attempt. Parcels that
                        cannot be returned due to Courier Partner negligence are treated as lost
                        parcels under Section 6.
                    </p>
                </div>
            </div>

            {{-- 8 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-lock class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">8. Data Privacy &amp; Confidentiality</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The Courier Partner will receive personal data of buyers and sellers
                        (names, addresses, contact numbers) solely to complete deliveries, and
                        agrees to process this data in accordance with the Data Privacy Act of
                        2012 and its implementing rules. Such data must not be copied, stored
                        beyond what is operationally necessary, or used for any purpose outside
                        fulfilling ShopHop orders — including marketing, resale, or solicitation
                        of buyers or sellers off-platform. Riders and staff with access to buyer
                        or seller information must be bound by confidentiality obligations at
                        least as protective as those in this Agreement. The Courier Partner must
                        notify ShopHop within twenty-four (24) hours of discovering any data
                        breach involving ShopHop order data.
                    </p>
                </div>
            </div>

            {{-- 9 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-ban class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">9. Prohibited Items</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The Courier Partner must refuse pickup of, and must not knowingly
                        transport, any parcel containing: illegal drugs or drug paraphernalia;
                        firearms, ammunition, or explosives; counterfeit or pirated goods;
                        live animals (unless separately contracted); hazardous, flammable, or
                        corrosive materials; human remains or body parts; or any item prohibited
                        under Philippine law or postal/courier regulations. Suspected prohibited
                        items must be reported to ShopHop and, where required by law, to the
                        appropriate authorities. ShopHop bears no liability for losses arising
                        from a Courier Partner knowingly transporting a prohibited item.
                    </p>
                </div>
            </div>

            {{-- 10 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-badge-check class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">10. Compliance Requirements</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The Courier Partner represents that it holds and will maintain all
                        permits, licenses, and registrations required to operate a courier or
                        freight business in its declared coverage areas (e.g., DTI/SEC/CDA
                        registration, LTFRB/LTO requirements where applicable, and local
                        business permits), and will provide updated copies to ShopHop upon
                        renewal or request. The Courier Partner must comply with all applicable
                        labor, tax, traffic, and consumer protection laws with respect to its
                        own riders, drivers, and vehicles. ShopHop may conduct periodic audits
                        or request supporting documents to verify continued compliance.
                    </p>
                </div>
            </div>

            {{-- 11 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-octagon-alert class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">11. Suspension &amp; Termination</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        ShopHop may suspend or terminate a Courier Partner's accreditation,
                        with or without prior notice for serious violations (including fraud,
                        COD misappropriation, repeated lost/damaged parcels, or transporting
                        prohibited items), or with fifteen (15) days' written notice for other
                        material breaches that remain uncured. The Courier Partner may terminate
                        this Agreement with thirty (30) days' written notice, provided all
                        pending deliveries, COD remittances, and outstanding claims are settled.
                        Sections 6, 8, and 12 survive termination.
                    </p>
                </div>
            </div>

            {{-- 12 --}}
            <div class="flex gap-4">
                <div class="shrink-0 w-9 h-9 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-scale class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-navy font-bold mb-2">12. Dispute Resolution</h3>
                    <p class="text-sm text-navy/75 leading-relaxed">
                        The parties will first attempt to resolve any dispute arising from this
                        Agreement in good faith through the ShopHop Partner Support channel
                        within thirty (30) days. Disputes not resolved informally will be
                        referred to mediation, and if still unresolved, to binding arbitration
                        or the proper courts of the Philippines, as applicable, with venue
                        proper to ShopHop's principal place of business. This Agreement is
                        governed by the laws of the Republic of the Philippines.
                    </p>
                </div>
            </div>

        </div>

        {{-- Return to application --}}
        <div class="border-t border-gray-border mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-navy/45 text-center sm:text-left">
                You've reached the end of the Courier Terms &amp; Agreement.
            </p>
            <a href="{{ route('logistics.register') }}"
               class="w-full sm:w-auto text-center bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-7 py-3 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20 flex items-center justify-center gap-2">
                <x-lucide-arrow-left class="w-4 h-4" />
                Return to Application
            </a>
        </div>

    </div>
</section>

@endsection