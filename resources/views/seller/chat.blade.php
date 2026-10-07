@extends('seller.partials.layout')

@section('title', 'Seller Chat')

@section('content')
{{-- No messaging, persistence or moderation backend exists; the destination must not simulate account activity. --}}
<section id="sellerChat" aria-labelledby="chatTitle" class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 id="chatTitle" class="text-2xl font-bold text-navy">Seller Chat</h1>
        <p class="mt-1 text-sm text-navy/55">Messaging availability on ShopHop.</p>
    </div>

    {{-- Explicit limitations replace fixtures and false successful actions in both authenticated and local TEST views. --}}
    <div class="rounded-xl border border-gray-border bg-white p-5 sm:p-6">
        <span class="inline-flex rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold text-teal-dark">Unavailable</span>
        <h2 class="mt-4 text-lg font-semibold text-navy">Live messaging is unavailable.</h2>
        <p class="mt-2 text-sm text-navy/60">No live conversations or account message history are displayed.</p>
        <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-navy/60">
            <li>Messages are not sent or stored here.</li>
            <li>Conversation history is not persisted here.</li>
            <li>Read/unread and archive state are unavailable.</li>
            <li>Attachments cannot be uploaded or stored here.</li>
            <li>Blocking and reporting users or conversations are unavailable.</li>
            <li>Delivery/read receipts and live presence are unavailable.</li>
        </ul>
    </div>
</section>
@endsection
