@extends('admin.layout')

@section('title', 'Seller Compliance')

@section('content')
    {{-- No Seller compliance contract exists; fixture Sellers, documents and browser-only decisions cannot stand in for persisted reviews. --}}
    <section id="adminSellerCompliance" aria-labelledby="sellerComplianceTitle" class="space-y-6">
        <div>
            <h1 id="sellerComplianceTitle" class="text-2xl font-bold text-navy">Seller Compliance</h1>
            <p class="text-sm text-slate-500 mt-1">Seller compliance availability on ShopHop.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
            <h2 class="text-lg font-semibold text-navy mt-4">A separate Seller legal/compliance workflow is not available.</h2>
            <p class="text-sm text-slate-500 mt-2">No live Seller compliance records are displayed here.</p>

            {{-- Omit unsupported verification, flags and history rather than implying a saved decision or account sanction. --}}
            <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
                <li>Seller document verification and re-verification are unavailable here.</li>
                <li>Compliance decisions, flags, case history and reports are not recorded here.</li>
                <li>Document uploads and downloads, sanctions and compliance notifications are unavailable here.</li>
            </ul>
            <p class="text-sm text-slate-500 mt-4">No Seller is verified or flagged, no decision is saved, and no account or listing status is changed from this page.</p>
        </div>

        {{-- Registration moderation owns account decisions and submitted documents; approval is not legal compliance and no document access is added here. --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-navy">Registration review remains separate</h2>
            <p class="text-sm text-slate-500 mt-2">Use Account Registrations in the Admin navigation for existing registration review and submitted registration documents. Account moderation remains in its existing workflow.</p>
            <p class="text-sm text-slate-500 mt-2">Account approval and email verification do not establish legal compliance. This page does not create another approval status.</p>
            <p class="text-sm text-slate-500 mt-2">Product Compliance is a separate product review workflow.</p>
        </div>
    </section>
@endsection
