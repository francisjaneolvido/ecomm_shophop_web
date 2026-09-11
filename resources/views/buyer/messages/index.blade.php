{{-- Path: resources/views/buyer/messages/index.blade.php --}}

@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | TEMPORARY MESSAGE PREVIEW DATA
    |--------------------------------------------------------------------------
    | Replace this collection with real conversations/messages later.
    |
    | Suggested backend later:
    | conversations
    | conversation_participants
    | messages
    | message_attachments
    |
    | This page is intentionally frontend/demo-ready for now.
    */

    $conversations = collect([

        [
            'id' => 'tech-store',
            'type' => 'seller',
            'name' => 'ShopHop Tech Store',
            'slug' => 'shophop-tech-store',
            'initial' => 'S',
            'preferred' => true,
            'online' => true,
            'unread' => 2,
            'time' => '10:24 AM',
            'preview' => 'Your parcel is already out for delivery.',
            'order' => [
                'id' => 'SHP-2026-00125',
                'status' => 'Out for Delivery',
                'product' => 'Wireless Earbuds Pro with ENC Noise Reduction & Charging Case',
                'variant' => 'Black · Earbuds Only',
                'image' => 'images/hero/earbuds.jpg',
                'total' => 1299,
            ],
            'messages' => [
                [
                    'from' => 'system',
                    'body' => 'This conversation is connected to Order SHP-2026-00125.',
                    'time' => 'Sep 10 · 8:40 PM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'Hi! Your Wireless Earbuds Pro has already been handed over to the courier.',
                    'time' => 'Sep 10 · 8:41 PM',
                ],
                [
                    'from' => 'buyer',
                    'body' => 'Thank you. Do you know when it might arrive?',
                    'time' => 'Sep 10 · 8:45 PM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'The latest update shows it is already at the Calamba delivery hub. It should arrive today.',
                    'time' => '10:22 AM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'Your parcel is already out for delivery. You can also check the detailed tracking from My Orders.',
                    'time' => '10:24 AM',
                ],
            ],
        ],

        [
            'id' => 'stepup',
            'type' => 'seller',
            'name' => 'StepUp Footwear PH',
            'slug' => 'stepup-footwear-ph',
            'initial' => 'S',
            'preferred' => false,
            'online' => false,
            'unread' => 0,
            'time' => 'Yesterday',
            'preview' => 'Thank you for shopping with us!',
            'order' => [
                'id' => 'SHP-2026-00097',
                'status' => 'Delivered',
                'product' => 'Everyday Running Sneakers, Lightweight & Breathable',
                'variant' => 'White · Size 9',
                'image' => 'images/hero/sneaker.jpg',
                'total' => 1899,
            ],
            'messages' => [
                [
                    'from' => 'system',
                    'body' => 'Order SHP-2026-00097 was delivered successfully.',
                    'time' => 'Sep 7 · 4:37 PM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'Hi! We hope your sneakers arrived safely.',
                    'time' => 'Sep 7 · 5:02 PM',
                ],
                [
                    'from' => 'buyer',
                    'body' => 'Yes, I received the parcel. Thank you.',
                    'time' => 'Sep 7 · 5:10 PM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'Thank you for shopping with us! If there is any product concern, you can use Report Issue from My Orders.',
                    'time' => 'Yesterday',
                ],
            ],
        ],

        [
            'id' => 'support',
            'type' => 'support',
            'name' => 'ShopHop Support',
            'slug' => null,
            'initial' => 'H',
            'preferred' => false,
            'online' => true,
            'unread' => 1,
            'time' => '9:18 AM',
            'preview' => 'We received your report and it is under review.',
            'order' => [
                'id' => 'SHP-2026-00097',
                'status' => 'Report Submitted',
                'product' => 'Everyday Running Sneakers, Lightweight & Breathable',
                'variant' => 'White · Size 9',
                'image' => 'images/hero/sneaker.jpg',
                'total' => 1899,
            ],
            'messages' => [
                [
                    'from' => 'system',
                    'body' => 'A report was submitted for Order SHP-2026-00097.',
                    'time' => '9:12 AM',
                ],
                [
                    'from' => 'support',
                    'body' => 'Hello. We received your report and it is now under review by ShopHop Support.',
                    'time' => '9:16 AM',
                ],
                [
                    'from' => 'support',
                    'body' => 'If needed, you can send additional photos or details here. This report does not start a refund request.',
                    'time' => '9:18 AM',
                ],
            ],
        ],

        [
            'id' => 'hometech',
            'type' => 'seller',
            'name' => 'HomeTech Essentials',
            'slug' => 'hometech-essentials',
            'initial' => 'H',
            'preferred' => false,
            'online' => false,
            'unread' => 0,
            'time' => 'Sep 10',
            'preview' => 'We will prepare your order after payment verification.',
            'order' => [
                'id' => 'SHP-2026-00088',
                'status' => 'Payment Verification',
                'product' => 'Compact TWS Earbuds',
                'variant' => 'White',
                'image' => 'images/hero/earbuds.jpg',
                'total' => 999,
            ],
            'messages' => [
                [
                    'from' => 'seller',
                    'body' => 'Hello! We received your order.',
                    'time' => 'Sep 10 · 8:47 AM',
                ],
                [
                    'from' => 'seller',
                    'body' => 'We will prepare your order after ShopHop finishes verifying your GCash payment.',
                    'time' => 'Sep 10 · 8:49 AM',
                ],
            ],
        ],
    ]);

    $unreadConversationCount = $conversations->where('unread', '>', 0)->count();
    $totalUnreadMessages = $conversations->sum('unread');
@endphp


@section('title', 'Messages - ShopHop')
@section('hideChrome', true)


@push('styles')
<style>
    /* Messages readability pass */
    #messageConversationList .conversation-row {
        min-height: 76px;
    }

    #messageConversationList,
    #messageChatArea {
        font-size: 14px;
    }

    .messages-scroll {
        scroll-behavior: smooth;
    }

    .message-input::placeholder {
        color: rgba(15, 27, 61, 0.34);
    }

    @media (max-width: 1023px) {
        #messageConversationList .conversation-row {
            min-height: 72px;
        }
    }
</style>
@endpush


@section('content')

@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">

        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45">

            <a
                href="{{ route('buyer.dashboard') }}"
                class="hover:text-teal-dark transition"
            >
                Home
            </a>

            <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />

            <span class="text-navy font-medium">
                Messages
            </span>

        </nav>

    </div>
</div>


{{-- =========================================================
    MESSAGES
========================================================= --}}
<section class="bg-gray-bg/75 min-h-[calc(100vh-90px)] py-4 sm:py-5">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">

            <div>

                <div class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-teal-dark mb-1.5">

                    <x-lucide-message-circle class="w-3 h-3" />

                    Conversations

                </div>


                <p
                    role="heading"
                    aria-level="1"
                    class="text-[24px] sm:text-[28px] font-bold leading-none tracking-tight text-navy"
                >
                    Messages
                </p>


                <p class="text-[12px] sm:text-[12.5px] text-navy/50 mt-1.5">

                    Chat with sellers and ShopHop Support about your purchases.

                </p>

            </div>


            @if ($totalUnreadMessages > 0)

                <div class="inline-flex items-center gap-1.5 self-start sm:self-auto
                            rounded-full bg-teal-light px-2.5 py-1.5
                            text-[10px] font-semibold text-teal-dark">

                    <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>

                    {{ $totalUnreadMessages }}
                    {{ \Illuminate\Support\Str::plural('unread message', $totalUnreadMessages) }}

                </div>

            @endif

        </div>


        {{-- =====================================================
            CHAT SHELL
        ====================================================== --}}
        <div class="bg-white border border-gray-border rounded-2xl shadow-sm overflow-hidden
                    min-h-162.5 lg:h-[calc(100vh-180px)] lg:min-h-155">

            <div class="grid lg:grid-cols-[340px_minmax(0,1fr)] h-full">


                {{-- =================================================
                    CONVERSATION LIST
                ================================================== --}}
                <aside
                    id="messageConversationList"
                    class="border-r border-gray-border/80 flex flex-col min-h-162.5 lg:min-h-0"
                >

                    {{-- Sidebar header --}}
                    <div class="p-3 border-b border-gray-border/80">

                        <div class="flex items-center justify-between gap-2">

                            <div>

                                <p class="text-[14px] font-bold text-navy">
                                    Inbox
                                </p>

                                <p class="text-[10px] text-navy/40 mt-0.5">
                                    {{ $conversations->count() }} conversations
                                </p>

                            </div>


                            <button
                                type="button"
                                id="markMessagesReadBtn"
                                class="text-[9.5px] font-semibold text-teal-dark hover:text-navy transition"
                            >
                                Mark all read
                            </button>

                        </div>


                        {{-- Search --}}
                        <div class="relative mt-3">

                            <x-lucide-search
                                class="absolute left-3 top-1/2 -translate-y-1/2
                                       w-3.5 h-3.5 text-navy/30 pointer-events-none"
                            />


                            <input
                                id="conversationSearch"
                                type="search"
                                placeholder="Search seller or message..."
                                class="w-full h-9 rounded-xl bg-gray-bg/80 border border-gray-border
                                       pl-8.5 pr-3 text-[11px] text-navy
                                       placeholder:text-navy/30 outline-none
                                       focus:border-teal focus:bg-white focus:ring-1 focus:ring-teal/10 transition"
                            >

                        </div>


                        {{-- Filters --}}
                        <div class="flex gap-1.5 mt-2.5 overflow-x-auto [&::-webkit-scrollbar]:hidden">

                            <button
                                type="button"
                                class="conversation-filter h-7 px-2.5 rounded-lg bg-teal text-white
                                       text-[10px] font-semibold transition shrink-0"
                                data-conversation-filter="all"
                            >
                                All
                            </button>

                            <button
                                type="button"
                                class="conversation-filter h-7 px-2.5 rounded-lg
                                       bg-gray-bg text-navy/50 hover:text-teal-dark
                                       text-[10px] font-semibold transition shrink-0"
                                data-conversation-filter="unread"
                            >
                                Unread
                            </button>

                            <button
                                type="button"
                                class="conversation-filter h-7 px-2.5 rounded-lg
                                       bg-gray-bg text-navy/50 hover:text-teal-dark
                                       text-[10px] font-semibold transition shrink-0"
                                data-conversation-filter="support"
                            >
                                Support
                            </button>

                        </div>

                    </div>


                    {{-- Conversation rows --}}
                    <div
                        id="conversationRows"
                        class="flex-1 overflow-y-auto"
                    >

                        @foreach ($conversations as $index => $conversation)

                            <button
                                type="button"
                                class="conversation-row w-full text-left px-3 py-3.5
                                       border-b border-gray-border/70
                                       hover:bg-gray-bg/65 transition
                                       {{ $index === 0 ? 'bg-teal-light/25' : 'bg-white' }}"
                                data-conversation-id="{{ $conversation['id'] }}"
                                data-conversation-type="{{ $conversation['type'] }}"
                                data-conversation-unread="{{ $conversation['unread'] > 0 ? '1' : '0' }}"
                                data-conversation-search="{{ strtolower(
                                    $conversation['name'] . ' ' .
                                    $conversation['preview'] . ' ' .
                                    ($conversation['order']['id'] ?? '')
                                ) }}"
                            >

                                <div class="flex gap-2.5">

                                    {{-- Avatar --}}
                                    <div class="relative shrink-0">

                                        <span
                                            class="w-10 h-10 rounded-full
                                                   {{ $conversation['type'] === 'support'
                                                        ? 'bg-navy text-white'
                                                        : 'bg-teal-light text-teal-dark' }}
                                                   flex items-center justify-center
                                                   text-[12px] font-bold border border-gray-border/60"
                                        >
                                            {{ $conversation['initial'] }}
                                        </span>


                                        @if ($conversation['online'])

                                            <span class="absolute right-0 bottom-0 w-3 h-3 rounded-full
                                                         bg-teal border-2 border-white"></span>

                                        @endif

                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div class="flex items-start gap-2">

                                            <div class="min-w-0 flex-1">

                                                <div class="flex items-center gap-1.5 min-w-0">

                                                    <p class="text-[11.5px] font-semibold text-navy truncate">
                                                        {{ $conversation['name'] }}
                                                    </p>


                                                    @if ($conversation['preferred'])

                                                        <span class="shrink-0 text-[6.5px] font-bold
                                                                     bg-teal text-white px-1 py-0.5 rounded">
                                                            Preferred
                                                        </span>

                                                    @endif

                                                </div>

                                            </div>


                                            <span class="text-[9px] text-navy/35 shrink-0">
                                                {{ $conversation['time'] }}
                                            </span>

                                        </div>


                                        <div class="flex items-end gap-2 mt-1">

                                            <p
                                                class="conversation-preview min-w-0 flex-1 truncate
                                                       text-[10.5px]
                                                       {{ $conversation['unread'] > 0
                                                            ? 'font-semibold text-navy/65'
                                                            : 'text-navy/40' }}"
                                            >
                                                {{ $conversation['preview'] }}
                                            </p>


                                            @if ($conversation['unread'] > 0)

                                                <span
                                                    class="conversation-unread-badge min-w-4 h-4 px-1 rounded-full
                                                           bg-teal text-white text-[7px] font-bold
                                                           flex items-center justify-center shrink-0"
                                                >
                                                    {{ $conversation['unread'] }}
                                                </span>

                                            @endif

                                        </div>


                                        @if (! empty($conversation['order']))

                                            <div class="flex items-center gap-1 mt-1.5 text-[9px] text-navy/35">

                                                <x-lucide-package class="w-2.5 h-2.5" />

                                                {{ $conversation['order']['id'] }}

                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </button>

                        @endforeach


                        <div
                            id="conversationEmptyState"
                            class="hidden px-4 py-12 text-center"
                        >

                            <x-lucide-message-circle class="w-7 h-7 text-navy/20 mx-auto" />

                            <p class="text-[10.5px] font-semibold text-navy/50 mt-2">
                                No conversations found
                            </p>

                            <p class="text-[8.5px] text-navy/30 mt-1">
                                Try another search or filter.
                            </p>

                        </div>

                    </div>

                </aside>


                {{-- =================================================
                    CHAT PANELS
                ================================================== --}}
                <main
                    id="messageChatArea"
                    class="hidden lg:block min-w-0 h-full"
                >

                    @foreach ($conversations as $index => $conversation)

                        <section
                            class="conversation-panel {{ $index === 0 ? '' : 'hidden' }}
                                   h-full min-h-162.5 lg:min-h-0 flex flex-col"
                            data-conversation-panel="{{ $conversation['id'] }}"
                        >

                            {{-- Chat header --}}
                            <div class="px-3 sm:px-4 py-2.5 border-b border-gray-border/80">

                                <div class="flex items-center gap-2.5">

                                    {{-- Mobile back --}}
                                    <button
                                        type="button"
                                        class="mobile-chat-back lg:hidden w-8 h-8 rounded-lg
                                               flex items-center justify-center
                                               text-navy/45 hover:bg-gray-bg hover:text-teal-dark transition"
                                    >
                                        <x-lucide-chevron-left class="w-4 h-4" />
                                    </button>


                                    <span
                                        class="w-9 h-9 rounded-full
                                               {{ $conversation['type'] === 'support'
                                                    ? 'bg-navy text-white'
                                                    : 'bg-teal-light text-teal-dark' }}
                                               flex items-center justify-center
                                               text-[12px] font-bold shrink-0"
                                    >
                                        {{ $conversation['initial'] }}
                                    </span>


                                    <div class="min-w-0 flex-1">

                                        <div class="flex items-center gap-1.5">

                                            <p class="text-[13px] font-bold text-navy truncate">
                                                {{ $conversation['name'] }}
                                            </p>


                                            @if ($conversation['preferred'])

                                                <span class="text-[6.5px] font-bold bg-teal text-white px-1.5 py-0.5 rounded">
                                                    Preferred
                                                </span>

                                            @endif

                                        </div>


                                        <p class="text-[10px] text-navy/40 mt-0.5">

                                            @if ($conversation['online'])

                                                <span class="text-teal-dark font-medium">
                                                    Online now
                                                </span>

                                            @elseif ($conversation['type'] === 'support')

                                                ShopHop customer support

                                            @else

                                                Seller conversation

                                            @endif

                                        </p>

                                    </div>


                                    <div class="flex items-center gap-1.5 shrink-0">

                                        @if ($conversation['type'] === 'seller')

                                            <a
                                                href="{{ Route::has('buyer.store.show') && $conversation['slug']
                                                    ? route('buyer.store.show', $conversation['slug'])
                                                    : '#' }}"
                                                class="hidden sm:inline-flex h-8 px-2.5 rounded-lg
                                                       border border-gray-border
                                                       text-[9.5px] font-semibold text-navy/55
                                                       hover:border-teal/40 hover:text-teal-dark transition
                                                       items-center gap-1"
                                            >
                                                <x-lucide-store class="w-3 h-3" />
                                                View Shop
                                            </a>

                                        @endif


                                        <a
                                            href="{{ Route::has('buyer.orders')
                                                ? route('buyer.orders')
                                                : url('/buyer/orders') }}"
                                            class="h-8 px-2.5 rounded-lg bg-teal-light
                                                   text-[9.5px] font-semibold text-teal-dark
                                                   hover:bg-teal hover:text-white transition
                                                   inline-flex items-center gap-1"
                                        >
                                            <x-lucide-package class="w-3 h-3" />
                                            Orders
                                        </a>

                                    </div>

                                </div>

                            </div>


                            {{-- Order context card --}}
                            @if (! empty($conversation['order']))

                                <div class="px-3 sm:px-4 py-2.5 bg-gray-bg/55 border-b border-gray-border/80">

                                    <div class="flex items-center gap-2.5 bg-white border border-gray-border
                                                rounded-xl px-2.5 py-2">

                                        <img
                                            src="{{ asset($conversation['order']['image']) }}"
                                            alt="{{ $conversation['order']['product'] }}"
                                            class="w-10 h-10 rounded-lg object-cover bg-gray-bg border border-gray-border shrink-0"
                                        >


                                        <div class="min-w-0 flex-1">

                                            <div class="flex items-center gap-1.5">

                                                <span class="text-[8.5px] font-bold uppercase tracking-wide
                                                             text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">
                                                    {{ $conversation['order']['status'] }}
                                                </span>


                                                <span class="text-[9px] text-navy/35">
                                                    {{ $conversation['order']['id'] }}
                                                </span>

                                            </div>


                                            <p class="text-[11px] font-semibold text-navy truncate mt-1">
                                                {{ $conversation['order']['product'] }}
                                            </p>


                                            <p class="text-[9.5px] text-navy/40 truncate mt-0.5">
                                                {{ $conversation['order']['variant'] }}
                                                · ₱{{ number_format($conversation['order']['total']) }}
                                            </p>

                                        </div>


                                        <a
                                            href="{{ Route::has('buyer.orders')
                                                ? route('buyer.orders')
                                                : url('/buyer/orders') }}"
                                            class="shrink-0 text-[9.5px] font-semibold text-teal-dark hover:text-navy transition"
                                        >
                                            View Order
                                        </a>

                                    </div>

                                </div>

                            @endif


                            {{-- Messages --}}
                            <div
                                class="messages-scroll flex-1 overflow-y-auto bg-[#FBFCFD] px-3 sm:px-4 py-4 space-y-3"
                            >

                                <div class="flex justify-center">

                                    <span class="px-2 py-1 rounded-full bg-gray-bg
                                                 text-[9px] text-navy/35">
                                        Conversation preview
                                    </span>

                                </div>


                                @foreach ($conversation['messages'] as $message)

                                    @if ($message['from'] === 'system')

                                        <div class="flex justify-center">

                                            <div class="max-w-[85%] rounded-xl bg-gray-bg border border-gray-border
                                                        px-3 py-2 text-center">

                                                <p class="text-[11.5px] leading-relaxed text-navy/50">
                                                    {{ $message['body'] }}
                                                </p>

                                                <p class="text-[8.5px] text-navy/30 mt-1">
                                                    {{ $message['time'] }}
                                                </p>

                                            </div>

                                        </div>


                                    @elseif ($message['from'] === 'buyer')

                                        <div class="flex justify-end">

                                            <div class="max-w-[82%] sm:max-w-[70%]">

                                                <div class="rounded-2xl rounded-br-md bg-teal
                                                            px-3 py-2.5 text-white">

                                                    <p class="text-[11.5px] leading-relaxed">
                                                        {{ $message['body'] }}
                                                    </p>

                                                </div>


                                                <div class="flex items-center justify-end gap-1 mt-1">

                                                    <span class="text-[8.5px] text-navy/30">
                                                        {{ $message['time'] }}
                                                    </span>

                                                    <x-lucide-check class="w-2.5 h-2.5 text-teal-dark" />

                                                </div>

                                            </div>

                                        </div>


                                    @else

                                        <div class="flex justify-start">

                                            <div class="max-w-[82%] sm:max-w-[70%]">

                                                <div class="rounded-2xl rounded-bl-md bg-white border border-gray-border
                                                            px-3 py-2.5 shadow-sm">

                                                    <p class="text-[11.5px] leading-relaxed text-navy/70">
                                                        {{ $message['body'] }}
                                                    </p>

                                                </div>


                                                <p class="text-[8.5px] text-navy/30 mt-1 ml-1">
                                                    {{ $message['time'] }}
                                                </p>

                                            </div>

                                        </div>

                                    @endif

                                @endforeach

                            </div>


                            {{-- Attachment preview --}}
                            <div
                                class="message-attachment-preview hidden px-3 sm:px-4 py-2
                                       border-t border-gray-border bg-white"
                            >

                                <div class="inline-flex items-center gap-2 max-w-full
                                            rounded-lg bg-gray-bg border border-gray-border
                                            px-2.5 py-1.5">

                                    <x-lucide-camera class="w-3 h-3 text-teal-dark shrink-0" />

                                    <span class="attachment-file-name text-[10px] text-navy/50 truncate"></span>

                                    <button
                                        type="button"
                                        class="clear-attachment-btn w-5 h-5 rounded
                                               flex items-center justify-center
                                               text-navy/35 hover:text-red-500 transition"
                                    >
                                        <x-lucide-x class="w-3 h-3" />
                                    </button>

                                </div>

                            </div>


                            {{-- Composer --}}
                            <form
                                class="message-composer border-t border-gray-border/80 bg-white
                                       px-3 sm:px-4 py-2.5"
                                data-message-form="{{ $conversation['id'] }}"
                            >

                                <div class="flex items-end gap-2">

                                    <label
                                        class="w-10 h-10 rounded-xl bg-gray-bg
                                               text-navy/45 hover:text-teal-dark hover:bg-teal-light
                                               flex items-center justify-center shrink-0
                                               cursor-pointer transition"
                                        title="Attach image"
                                    >

                                        <x-lucide-camera class="w-4 h-4" />

                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="message-attachment-input hidden"
                                        >

                                    </label>


                                    <div class="min-w-0 flex-1">

                                        <textarea
                                            rows="1"
                                            maxlength="1000"
                                            placeholder="Write a message..."
                                            class="message-input block w-full min-h-10 max-h-28 resize-none overflow-y-auto
                                                   rounded-xl border border-gray-border bg-gray-bg/65
                                                   px-3.5 py-2.5 text-[12px] leading-4.5 text-navy
                                                   placeholder:text-navy/30 outline-none
                                                   focus:bg-white focus:border-teal focus:ring-1 focus:ring-teal/10"
                                        ></textarea>

                                    </div>


                                    <button
                                        type="submit"
                                        class="w-10 h-10 rounded-xl bg-teal hover:bg-teal-dark
                                               text-white flex items-center justify-center shrink-0
                                               transition"
                                        aria-label="Send message"
                                    >
                                        <x-lucide-send class="w-4 h-4" />
                                    </button>

                                </div>


                                <div class="flex items-center justify-between gap-2 mt-1.5 px-1">

                                    <p class="text-[8.5px] text-navy/30">
                                        Do not share passwords, OTPs, or payment PINs.
                                    </p>


                                    <span class="message-character-count text-[8.5px] text-navy/30">
                                        0 / 1000
                                    </span>

                                </div>

                            </form>

                        </section>

                    @endforeach

                </main>

            </div>

        </div>

    </div>

</section>


{{-- Toast --}}
<div
    id="messagesToast"
    class="fixed left-1/2 bottom-5 z-110
           -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none
           bg-navy text-white text-[10px] font-medium
           px-3.5 py-2.5 rounded-xl shadow-xl transition-all duration-300"
></div>


@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const conversationRows =
        Array.from(
            document.querySelectorAll('.conversation-row')
        );

    const conversationPanels =
        Array.from(
            document.querySelectorAll('.conversation-panel')
        );

    const conversationSearch =
        document.getElementById('conversationSearch');

    const filterButtons =
        document.querySelectorAll('.conversation-filter');

    const emptyState =
        document.getElementById('conversationEmptyState');

    const conversationList =
        document.getElementById('messageConversationList');

    const chatArea =
        document.getElementById('messageChatArea');

    const markAllReadBtn =
        document.getElementById('markMessagesReadBtn');

    const toast =
        document.getElementById('messagesToast');

    let activeConversationId =
        conversationRows[0]?.dataset.conversationId || null;

    let activeFilter = 'all';


    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    function showToast(message) {

        if (!toast) {
            return;
        }

        toast.textContent =
            message;

        toast.classList.remove(
            'translate-y-6',
            'opacity-0'
        );

        toast.classList.add(
            'translate-y-0',
            'opacity-100'
        );

        clearTimeout(
            showToast.timer
        );

        showToast.timer =
            setTimeout(function () {

                toast.classList.remove(
                    'translate-y-0',
                    'opacity-100'
                );

                toast.classList.add(
                    'translate-y-6',
                    'opacity-0'
                );

            }, 2200);
    }


    /*
    |--------------------------------------------------------------------------
    | SCROLL CHAT TO BOTTOM
    |--------------------------------------------------------------------------
    */

    function scrollPanelToBottom(panel) {

        const messages =
            panel?.querySelector('.messages-scroll');

        if (!messages) {
            return;
        }

        messages.scrollTop =
            messages.scrollHeight;
    }


    /*
    |--------------------------------------------------------------------------
    | OPEN CONVERSATION
    |--------------------------------------------------------------------------
    */

    function openConversation(id, pushMobile = true) {

        activeConversationId =
            id;


        conversationRows.forEach(function (row) {

            const active =
                row.dataset.conversationId === id;

            row.classList.toggle(
                'bg-teal-light/25',
                active
            );

            row.classList.toggle(
                'bg-white',
                !active
            );


            if (active) {

                row.dataset.conversationUnread =
                    '0';

                row.querySelector(
                    '.conversation-unread-badge'
                )?.remove();

                row.querySelector(
                    '.conversation-preview'
                )?.classList.remove(
                    'font-semibold',
                    'text-navy/65'
                );

                row.querySelector(
                    '.conversation-preview'
                )?.classList.add(
                    'text-navy/40'
                );
            }
        });


        let activePanel = null;


        conversationPanels.forEach(function (panel) {

            const active =
                panel.dataset.conversationPanel === id;

            panel.classList.toggle(
                'hidden',
                !active
            );

            if (active) {
                activePanel = panel;
            }
        });


        if (
            pushMobile &&
            window.innerWidth < 1024
        ) {

            conversationList.classList.add(
                'hidden'
            );

            chatArea.classList.remove(
                'hidden'
            );
        }


        requestAnimationFrame(function () {
            scrollPanelToBottom(activePanel);
        });
    }


    conversationRows.forEach(function (row) {

        row.addEventListener('click', function () {

            openConversation(
                row.dataset.conversationId
            );
        });
    });


    /*
    |--------------------------------------------------------------------------
    | MOBILE BACK
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.mobile-chat-back')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                if (window.innerWidth >= 1024) {
                    return;
                }

                chatArea.classList.add(
                    'hidden'
                );

                conversationList.classList.remove(
                    'hidden'
                );
            });
        });


    /*
    |--------------------------------------------------------------------------
    | SEARCH / FILTER
    |--------------------------------------------------------------------------
    */

    function filterConversations() {

        const query =
            (conversationSearch?.value || '')
                .trim()
                .toLowerCase();

        let visibleCount = 0;


        conversationRows.forEach(function (row) {

            const matchesSearch =
                !query ||
                (row.dataset.conversationSearch || '')
                    .includes(query);


            const matchesFilter =
                activeFilter === 'all' ||
                (
                    activeFilter === 'unread' &&
                    row.dataset.conversationUnread === '1'
                ) ||
                (
                    activeFilter === 'support' &&
                    row.dataset.conversationType === 'support'
                );


            const visible =
                matchesSearch &&
                matchesFilter;


            row.classList.toggle(
                'hidden',
                !visible
            );


            if (visible) {
                visibleCount++;
            }
        });


        emptyState?.classList.toggle(
            'hidden',
            visibleCount !== 0
        );
    }


    conversationSearch?.addEventListener(
        'input',
        filterConversations
    );


    filterButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            activeFilter =
                button.dataset.conversationFilter;


            filterButtons.forEach(function (item) {

                item.classList.remove(
                    'bg-teal',
                    'text-white'
                );

                item.classList.add(
                    'bg-gray-bg',
                    'text-navy/50'
                );
            });


            button.classList.remove(
                'bg-gray-bg',
                'text-navy/50'
            );

            button.classList.add(
                'bg-teal',
                'text-white'
            );


            filterConversations();
        });
    });


    /*
    |--------------------------------------------------------------------------
    | MARK ALL READ
    |--------------------------------------------------------------------------
    */

    markAllReadBtn?.addEventListener('click', function () {

        conversationRows.forEach(function (row) {

            row.dataset.conversationUnread =
                '0';

            row.querySelector(
                '.conversation-unread-badge'
            )?.remove();

            const preview =
                row.querySelector(
                    '.conversation-preview'
                );

            preview?.classList.remove(
                'font-semibold',
                'text-navy/65'
            );

            preview?.classList.add(
                'text-navy/40'
            );
        });


        markAllReadBtn.textContent =
            'All read';

        markAllReadBtn.disabled =
            true;

        markAllReadBtn.classList.add(
            'opacity-50',
            'cursor-default'
        );


        filterConversations();

        showToast(
            'All conversations marked as read.'
        );
    });


    /*
    |--------------------------------------------------------------------------
    | MESSAGE INPUT / ATTACHMENTS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.conversation-panel')
        .forEach(function (panel) {

            const form =
                panel.querySelector('.message-composer');

            const input =
                panel.querySelector('.message-input');

            const count =
                panel.querySelector('.message-character-count');

            const attachmentInput =
                panel.querySelector('.message-attachment-input');

            const attachmentPreview =
                panel.querySelector('.message-attachment-preview');

            const attachmentName =
                panel.querySelector('.attachment-file-name');

            const clearAttachment =
                panel.querySelector('.clear-attachment-btn');

            const messagesScroll =
                panel.querySelector('.messages-scroll');


            let attachmentFile = null;


            input?.addEventListener('input', function () {

                count.textContent =
                    input.value.length +
                    ' / 1000';


                input.style.height =
                    '40px';

                input.style.height =
                    Math.max(
                        40,
                        Math.min(
                            input.scrollHeight,
                            112
                        )
                    ) + 'px';
            });


            attachmentInput?.addEventListener('change', function () {

                attachmentFile =
                    attachmentInput.files?.[0] || null;


                if (!attachmentFile) {

                    attachmentPreview?.classList.add(
                        'hidden'
                    );

                    return;
                }


                attachmentName.textContent =
                    attachmentFile.name;


                attachmentPreview?.classList.remove(
                    'hidden'
                );
            });


            clearAttachment?.addEventListener('click', function () {

                attachmentFile =
                    null;

                attachmentInput.value =
                    '';

                attachmentPreview?.classList.add(
                    'hidden'
                );
            });


            input?.addEventListener('keydown', function (event) {

                if (
                    event.key === 'Enter' &&
                    !event.shiftKey
                ) {

                    event.preventDefault();

                    form?.requestSubmit();
                }
            });


            form?.addEventListener('submit', function (event) {

                event.preventDefault();


                const body =
                    input.value.trim();


                if (
                    !body &&
                    !attachmentFile
                ) {
                    return;
                }


                const wrapper =
                    document.createElement('div');

                wrapper.className =
                    'flex justify-end';


                const bubbleGroup =
                    document.createElement('div');

                bubbleGroup.className =
                    'max-w-[82%] sm:max-w-[70%]';


                const bubble =
                    document.createElement('div');

                bubble.className =
                    'rounded-2xl rounded-br-md bg-teal px-3 py-2.5 text-white';


                if (attachmentFile) {

                    const image =
                        document.createElement('img');

                    image.src =
                        URL.createObjectURL(
                            attachmentFile
                        );

                    image.alt =
                        'Attached image';

                    image.className =
                        'w-full max-w-[240px] rounded-xl mb-2 object-cover';

                    bubble.appendChild(
                        image
                    );
                }


                if (body) {

                    const paragraph =
                        document.createElement('p');

                    paragraph.className =
                        'text-[11.5px] leading-relaxed';

                    paragraph.textContent =
                        body;

                    bubble.appendChild(
                        paragraph
                    );
                }


                const meta =
                    document.createElement('div');

                meta.className =
                    'flex items-center justify-end gap-1 mt-1';


                const time =
                    document.createElement('span');

                time.className =
                    'text-[8.5px] text-navy/30';

                time.textContent =
                    'Just now';


                meta.appendChild(
                    time
                );


                bubbleGroup.appendChild(
                    bubble
                );

                bubbleGroup.appendChild(
                    meta
                );

                wrapper.appendChild(
                    bubbleGroup
                );

                messagesScroll.appendChild(
                    wrapper
                );


                const conversationId =
                    panel.dataset.conversationPanel;


                const row =
                    document.querySelector(
                        '.conversation-row[data-conversation-id="' +
                        conversationId +
                        '"]'
                    );


                const preview =
                    row?.querySelector(
                        '.conversation-preview'
                    );


                if (preview) {

                    preview.textContent =
                        body ||
                        'Sent a photo';

                    preview.classList.remove(
                        'font-semibold',
                        'text-navy/65'
                    );

                    preview.classList.add(
                        'text-navy/40'
                    );
                }


                input.value =
                    '';

                input.style.height =
                    '40px';

                count.textContent =
                    '0 / 1000';


                attachmentFile =
                    null;

                attachmentInput.value =
                    '';

                attachmentPreview?.classList.add(
                    'hidden'
                );


                requestAnimationFrame(function () {

                    messagesScroll.scrollTop =
                        messagesScroll.scrollHeight;
                });
            });

        });


    /*
    |--------------------------------------------------------------------------
    | INITIAL STATE
    |--------------------------------------------------------------------------
    */

    if (activeConversationId) {

        openConversation(
            activeConversationId,
            false
        );
    }

});
</script>
@endpush
