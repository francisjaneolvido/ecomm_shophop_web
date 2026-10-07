{{-- Path: resources/views/buyer/messages/index.blade.php --}}

@extends('layouts.app')

{{-- Keep the existing Buyer destination and shell; messaging has no delivery or persistence contract. --}}
@section('title', 'Messages - ShopHop')
@section('hideChrome', true)

@push('styles')
<style>
    /* Preserve the semantic theme surfaces and contained shell without an empty chat/composer viewport. */
    .buyer-messages-availability {
        background:
            radial-gradient(circle at 88% 6%, color-mix(in srgb, var(--sh-brand) 11%, transparent), transparent 26rem),
            var(--sh-page);
        color: var(--sh-text);
    }

    .buyer-messages-availability .messages-shell {
        min-width: 0;
        width: 100%;
        max-width: 100%;
        background: var(--sh-surface);
        border-color: color-mix(in srgb, var(--sh-border) 88%, var(--sh-brand));
        box-shadow: 0 24px 64px -38px color-mix(in srgb, var(--sh-text) 42%, transparent);
        overflow-wrap: anywhere;
    }

    .buyer-messages-availability .messages-muted {
        color: var(--sh-muted);
    }

    /* Keep the existing Buyer title scale despite the shared document heading defaults. */
    .buyer-messages-availability h1 {
        font-size: 24px;
        line-height: 1;
    }

    .buyer-messages-availability h2 {
        font-size: 16px;
        line-height: 1.4;
    }

    @media (min-width: 640px) {
        .buyer-messages-availability h1 {
            font-size: 28px;
        }

        .buyer-messages-availability h2 {
            font-size: 18px;
        }
    }
</style>
@endpush

@section('content')

@include('buyer.partials.navbar-buyer')

{{-- Preserve the existing navigation and keyboard focus conventions outside the unavailable messaging surface. --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />
            <span class="text-navy font-medium" aria-current="page">Messages</span>
        </nav>
    </div>
</div>

{{-- Fixtures and browser-only Send/read/attachment success cannot represent this Buyer's account communication. --}}
<section id="buyerMessages" aria-labelledby="buyerMessagesTitle"
         class="buyer-messages-availability min-h-[calc(100vh-90px)] py-4 sm:py-5">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <div class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-teal-dark mb-1.5">
                <x-lucide-message-circle class="w-3 h-3" />
                Messaging availability
            </div>
            <h1 id="buyerMessagesTitle" class="text-[24px] sm:text-[28px] font-bold leading-none tracking-tight">Messages</h1>
        </div>

        {{-- A single informational card stays in normal mobile flow; no hidden detail pane or sending affordance remains. --}}
        <div class="messages-shell border rounded-2xl p-5 sm:p-6">
            <h2 class="text-[16px] sm:text-[18px] font-semibold">Live messaging is unavailable.</h2>
            <p class="messages-muted text-[12px] sm:text-[12.5px] leading-relaxed mt-2">
                No live conversations or account message history are displayed.
            </p>
            <ul class="messages-muted list-disc pl-5 space-y-2 text-[12px] sm:text-[12.5px] leading-relaxed mt-4">
                <li>Messages are not sent or stored here.</li>
                <li>Conversation history is not persisted here.</li>
                <li>Read/unread state, delivery/read receipts and live presence are unavailable.</li>
                <li>Attachments cannot be uploaded or sent here.</li>
            </ul>
        </div>
    </div>
</section>

@include('partials.footer')

@endsection
