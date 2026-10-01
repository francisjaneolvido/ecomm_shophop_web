@extends('admin.layout')

@section('title', 'Complaints & Disputes')

@section('content')
    {{-- No case contract exists; fixture cases, evidence and browser-only decisions cannot represent persisted disputes. --}}
    <section id="adminDisputes" aria-labelledby="disputesTitle" class="space-y-6">
        <div>
            <h1 id="disputesTitle" class="text-2xl font-bold text-navy">Complaints &amp; Disputes</h1>
            <p class="text-sm text-slate-500 mt-1">Complaint and dispute availability on ShopHop.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
            <h2 class="text-lg font-semibold text-navy mt-4">Complaint and dispute case management is not available.</h2>
            <p class="text-sm text-slate-500 mt-2">No live complaint or dispute cases are displayed here.</p>

            {{-- Omit unsupported controls so a local payload or success toast cannot imply a recorded decision. --}}
            <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
                <li>Case evidence is not persisted here; evidence uploads and downloads are unavailable.</li>
                <li>Resolution recording and resolution history are unavailable.</li>
                <li>Mediation, case assignment and escalation are unavailable.</li>
                <li>Refunds, payment reversals and financial settlements are unavailable here.</li>
                <li>Dispute penalties and account actions are unavailable here.</li>
            </ul>
            <p class="text-sm text-slate-500 mt-4">No decision is saved, no case is resolved or closed, and no party is notified from this page.</p>
        </div>

        {{-- Orders and payment review do not authorize dispute refunds; account moderation remains its own workflow. --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-navy">Separate Admin workflows</h2>
            <p class="text-sm text-slate-500 mt-2">Orders, payment review and account moderation remain separate workflows. Nothing on this page changes Orders, payments or account status.</p>
        </div>
    </section>
@endsection
