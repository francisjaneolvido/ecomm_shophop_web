@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    {{-- No generalized reporting contract exists; replace fixture history and simulated actions with availability. --}}
    <section id="adminReports" aria-labelledby="reportsTitle" class="space-y-6">
        <div>
            <h1 id="reportsTitle" class="text-2xl font-bold text-navy">Reports</h1>
            <p class="text-sm text-slate-500 mt-1">Report availability on ShopHop.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
            <h2 class="text-lg font-semibold text-navy mt-4">Business report generation is not available.</h2>
            <p class="text-sm text-slate-500 mt-2">The Reports destination is available, but it does not generate or display live business reports.</p>

            {{-- Omitting generation and download controls prevents local state or dummy files from implying completed reports. --}}
            <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
                <li>Report history is not persisted.</li>
                <li>PDF and CSV export are not available.</li>
                <li>No reports are generated, saved or downloaded from this page.</li>
            </ul>
        </div>

        {{-- Registration review, Orders and payment custody do not establish generalized reporting or accounting authority. --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-navy">Separate Admin workflows</h2>
            <p class="text-sm text-slate-500 mt-2">Registration moderation remains a separate review workflow.</p>
            <p class="text-sm text-slate-500 mt-2">Orders, COD handling and manual cashless payment review do not provide business report generation, commission accounting or Seller payout reporting here.</p>
        </div>
    </section>
@endsection
