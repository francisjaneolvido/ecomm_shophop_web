@extends('admin.layout')

@section('title', 'Chat / Messaging')

@section('content')
    {{-- No messaging contract exists; fixture conversations and browser mutations cannot represent live records. --}}
    <section id="adminChat" aria-labelledby="chatTitle" class="space-y-6">
        <div>
            <h1 id="chatTitle" class="text-2xl font-bold text-navy">Chat / Messaging</h1>
            <p class="text-sm text-slate-500 mt-1">Messaging availability on ShopHop.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
            <h2 class="text-lg font-semibold text-navy mt-4">Live messaging is not available.</h2>
            <p class="text-sm text-slate-500 mt-2">No live conversations or messages are displayed.</p>

            {{-- Omit unsupported actions so local state, toasts and browser dates cannot imply server completion. --}}
            <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
                <li>Messages cannot be sent or delivered from this page.</li>
                <li>Chat reports and user blocking are unavailable.</li>
                <li>Broadcast delivery and scheduled messages are unavailable.</li>
                <li>Attachments cannot be uploaded or sent.</li>
            </ul>
            <p class="text-sm text-slate-500 mt-4">Nothing is sent, reported, blocked, broadcast, scheduled or uploaded from this page.</p>
        </div>

        {{-- Account moderation does not establish Chat reports, block lists or communication delivery. --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-navy">Separate Admin workflows</h2>
            <p class="text-sm text-slate-500 mt-2">Account moderation remains a separate Admin workflow.</p>
        </div>
    </section>
@endsection
