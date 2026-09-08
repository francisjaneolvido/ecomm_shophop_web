@extends('seller.partials.layout')

@section('title', 'Chat')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    |
    | Your backend teammate can later replace this collection with:
    | - real conversations/messages
    | - unread counts
    | - buyer online status
    | - order/product relationships
    | - attachments
    | - typing indicators / realtime messaging
    | - message delivery/read receipts
    */

    $conversations = collect($conversations ?? [
        [
            'id' => 1,
            'name' => 'Maricel Santos',
            'initials' => 'MS',
            'avatar' => null,
            'online' => true,
            'last_seen' => 'Online now',
            'last_message' => 'Pwede po ba bukas na po ma-deliver?',
            'time' => '10:24 AM',
            'unread' => 2,
            'pinned' => true,
            'order' => [
                'id' => 'ORD-10231',
                'status' => 'OUT_FOR_DELIVERY',
                'total' => 1780,
                'delivery_area' => 'Calamba, Laguna',
                'product' => 'Handwoven Rattan Basket',
                'variant' => 'Natural / Medium',
                'qty' => 2,
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=240&q=80',
            ],
            'messages' => [
                [
                    'from' => 'buyer',
                    'text' => 'Hi po, order ko po kelan po darating?',
                    'time' => '10:10 AM',
                    'status' => null,
                ],
                [
                    'from' => 'seller',
                    'text' => 'Hi Maricel! Ipa-pickup na po namin ngayon, dapat bukas dumating.',
                    'time' => '10:15 AM',
                    'status' => 'read',
                ],
                [
                    'from' => 'buyer',
                    'text' => 'Pwede po ba bukas na po ma-deliver?',
                    'time' => '10:24 AM',
                    'status' => null,
                ],
            ],
        ],

        [
            'id' => 2,
            'name' => 'Jonas Villareal',
            'initials' => 'JV',
            'avatar' => null,
            'online' => false,
            'last_seen' => 'Active 1h ago',
            'last_message' => 'Salamat po, natanggap ko na po.',
            'time' => 'Yesterday',
            'unread' => 0,
            'pinned' => false,
            'order' => [
                'id' => 'ORD-10224',
                'status' => 'COMPLETED',
                'total' => 1320,
                'delivery_area' => 'Los Baños, Laguna',
                'product' => 'Barako Coffee Beans 250g',
                'variant' => 'Dark Roast',
                'qty' => 3,
                'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=240&q=80',
            ],
            'messages' => [
                [
                    'from' => 'seller',
                    'text' => 'Good afternoon! Na-deliver na po yung parcel according sa rider.',
                    'time' => 'Yesterday, 4:50 PM',
                    'status' => 'read',
                ],
                [
                    'from' => 'buyer',
                    'text' => 'Salamat po, natanggap ko na po.',
                    'time' => 'Yesterday, 5:02 PM',
                    'status' => null,
                ],
                [
                    'from' => 'seller',
                    'text' => 'Thank you rin po! Sana magustuhan ninyo yung order.',
                    'time' => 'Yesterday, 5:05 PM',
                    'status' => 'read',
                ],
            ],
        ],

        [
            'id' => 3,
            'name' => 'Ella Marasigan',
            'initials' => 'EM',
            'avatar' => null,
            'online' => false,
            'last_seen' => 'Active yesterday',
            'last_message' => 'Ok po, aabangan ko po.',
            'time' => '2 days ago',
            'unread' => 0,
            'pinned' => false,
            'order' => [
                'id' => 'ORD-10218',
                'status' => 'READY_FOR_PICKUP',
                'total' => 1635,
                'delivery_area' => 'Calamba, Laguna',
                'product' => 'Capiz Shell Wall Lamp',
                'variant' => 'Warm White / Medium',
                'qty' => 1,
                'image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=240&q=80',
            ],
            'messages' => [
                [
                    'from' => 'buyer',
                    'text' => 'Maingat po sana sa lamp, gift ko po kasi.',
                    'time' => '2 days ago, 9:12 AM',
                    'status' => null,
                ],
                [
                    'from' => 'seller',
                    'text' => 'Noted po! Lalagyan po namin ng extra protection yung package.',
                    'time' => '2 days ago, 9:20 AM',
                    'status' => 'read',
                ],
                [
                    'from' => 'buyer',
                    'text' => 'Ok po, aabangan ko po.',
                    'time' => '2 days ago, 9:24 AM',
                    'status' => null,
                ],
            ],
        ],

        [
            'id' => 4,
            'name' => 'Anna Reyes',
            'initials' => 'AR',
            'avatar' => null,
            'online' => true,
            'last_seen' => 'Online now',
            'last_message' => 'May maliit pong gasgas sa item.',
            'time' => '3 days ago',
            'unread' => 1,
            'pinned' => false,
            'order' => [
                'id' => 'ORD-10198',
                'status' => 'COMPLETED',
                'total' => 1200,
                'delivery_area' => 'Pagsanjan, Laguna',
                'product' => 'Capiz Shell Wall Lamp',
                'variant' => 'Warm White / Medium',
                'qty' => 1,
                'image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=240&q=80',
            ],
            'messages' => [
                [
                    'from' => 'buyer',
                    'text' => 'Hello po, dumating na po yung lamp.',
                    'time' => '3 days ago, 3:05 PM',
                    'status' => null,
                ],
                [
                    'from' => 'buyer',
                    'text' => 'May maliit pong gasgas sa item.',
                    'time' => '3 days ago, 3:07 PM',
                    'status' => null,
                ],
            ],
        ],
    ]);

    $totalUnread = $conversations->sum('unread');
    $unreadConversations = $conversations->where('unread', '>', 0)->count();
    $onlineCount = $conversations->where('online', true)->count();

    $statusClasses = [
        'PLACED' => 'bg-coral/10 text-coral',
        'CONFIRMED' => 'bg-sky/10 text-sky',
        'PREPARING' => 'bg-yellow/20 text-amber-700',
        'READY_FOR_PICKUP' => 'bg-teal/10 text-teal-dark',
        'PICKED_UP' => 'bg-navy/10 text-navy',
        'OUT_FOR_DELIVERY' => 'bg-sky/10 text-sky',
        'DELIVERED' => 'bg-teal/10 text-teal-dark',
        'COMPLETED' => 'bg-teal-light text-teal-dark',
        'DELIVERY_FAILED' => 'bg-red-50 text-red-500',
        'RETURNED' => 'bg-red-50 text-red-500',
    ];
@endphp


<style>
    #sellerChat {
        height: calc(100vh - 150px);
        min-height: 620px;
    }

    #sellerChat .chat-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 44, 63, .14) transparent;
    }

    #sellerChat .chat-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #sellerChat .chat-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(15, 44, 63, .14);
        border-radius: 999px;
    }

    #sellerChat [hidden],
    #chatOrderModal[hidden] {
        display: none !important;
    }

    .conversation-active {
        background: rgba(46, 207, 166, .08);
        box-shadow: inset 3px 0 0 #2ECFA6;
    }

    .chat-filter-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    @media (max-width: 767px) {
        #sellerChat {
            height: calc(100vh - 120px);
            min-height: 520px;
        }

        #chatListPanel.mobile-hidden,
        #chatThreadPanel.mobile-hidden {
            display: none !important;
        }

        #chatListPanel,
        #chatThreadPanel {
            width: 100%;
        }
    }
</style>


<div class="space-y-4">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div class="min-w-0">

                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-teal"></span>

                    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-teal-dark">
                        Buyer Messaging
                    </p>
                </div>


                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Chat
                </h1>


                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                    Answer buyer questions, discuss order concerns, and keep conversations
                    connected to the correct order.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <div class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg bg-coral/10 text-[10px] font-bold text-coral">
                    <x-lucide-message-circle-more class="w-3.5 h-3.5" />
                    {{ $totalUnread }} unread
                </div>


                <div class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg bg-teal/10 text-[10px] font-bold text-teal-dark">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                    {{ $onlineCount }} online
                </div>

            </div>

        </div>
    </section>


    {{-- =========================================================
        CHAT WORKSPACE
    ========================================================= --}}
    <div
        id="sellerChat"
        class="bg-white border border-gray-border rounded-xl overflow-hidden flex shadow-sm"
    >

        {{-- =====================================================
            CONVERSATION LIST
        ===================================================== --}}
        <aside
            id="chatListPanel"
            class="w-full md:w-80 xl:w-96 shrink-0 border-r border-gray-border flex flex-col"
        >

            {{-- List header --}}
            <div class="p-3 border-b border-gray-border">

                <div class="flex items-center justify-between gap-3">

                    <div>

                        <p class="text-sm font-bold text-navy">
                            Conversations
                        </p>

                        <p class="text-[10px] text-navy/35 mt-0.5">
                            {{ $conversations->count() }} total · {{ $unreadConversations }} unread
                        </p>

                    </div>


                    <button
                        type="button"
                        id="chatClearSearch"
                        class="text-[10px] font-semibold text-navy/35 hover:text-teal-dark transition"
                    >
                        Clear
                    </button>

                </div>


                <div class="relative mt-3">

                    <x-lucide-search class="w-4 h-4 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />

                    <input
                        type="text"
                        id="chatSearchInput"
                        placeholder="Search buyer, order or message..."
                        class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div class="flex items-center gap-1 mt-2 overflow-x-auto">

                    <button
                        type="button"
                        data-chat-filter="all"
                        class="chat-filter chat-filter-active h-8 px-3 rounded-lg border border-transparent text-[11px] font-semibold whitespace-nowrap transition"
                    >
                        All
                    </button>


                    <button
                        type="button"
                        data-chat-filter="unread"
                        class="chat-filter h-8 px-3 rounded-lg border border-transparent text-[11px] font-semibold text-navy/45 hover:bg-gray-bg whitespace-nowrap transition"
                    >
                        Unread
                    </button>


                    <button
                        type="button"
                        data-chat-filter="online"
                        class="chat-filter h-8 px-3 rounded-lg border border-transparent text-[11px] font-semibold text-navy/45 hover:bg-gray-bg whitespace-nowrap transition"
                    >
                        Online
                    </button>


                    <button
                        type="button"
                        data-chat-filter="pinned"
                        class="chat-filter h-8 px-3 rounded-lg border border-transparent text-[11px] font-semibold text-navy/45 hover:bg-gray-bg whitespace-nowrap transition"
                    >
                        Pinned
                    </button>

                </div>

            </div>


            {{-- Conversation rows --}}
            <div
                id="conversationList"
                class="flex-1 overflow-y-auto chat-scrollbar"
            >

                @foreach ($conversations as $index => $conversation)

                    @php
                        $searchText = strtolower(
                            $conversation['name'] . ' ' .
                            $conversation['last_message'] . ' ' .
                            ($conversation['order']['id'] ?? '') . ' ' .
                            ($conversation['order']['product'] ?? '')
                        );

                        $conversationJson = json_encode(
                            $conversation,
                            JSON_HEX_APOS | JSON_HEX_QUOT
                        );
                    @endphp


                    <button
                        type="button"
                        data-conversation-trigger
                        data-conversation-id="{{ $conversation['id'] }}"
                        data-unread="{{ $conversation['unread'] > 0 ? '1' : '0' }}"
                        data-online="{{ $conversation['online'] ? '1' : '0' }}"
                        data-pinned="{{ $conversation['pinned'] ? '1' : '0' }}"
                        data-search="{{ $searchText }}"
                        data-conversation='{{ $conversationJson }}'
                        class="conversation-row w-full flex items-start gap-3 px-3 py-3.5 border-b border-gray-border/60 text-left hover:bg-gray-bg/60 transition
                            {{ $index === 0 ? 'conversation-active' : '' }}"
                    >

                        {{-- Avatar --}}
                        <div class="relative shrink-0">

                            <div class="w-10 h-10 rounded-full bg-navy/10 text-navy flex items-center justify-center text-[11px] font-bold overflow-hidden">

                                @if (!empty($conversation['avatar']))

                                    <img
                                        src="{{ $conversation['avatar'] }}"
                                        alt="{{ $conversation['name'] }}"
                                        class="w-full h-full object-cover"
                                    >

                                @else

                                    {{ $conversation['initials'] }}

                                @endif

                            </div>


                            @if ($conversation['online'])

                                <span class="absolute right-0 bottom-0 w-3 h-3 rounded-full bg-teal border-2 border-white"></span>

                            @endif

                        </div>


                        <div class="min-w-0 flex-1">

                            <div class="flex items-center justify-between gap-2">

                                <div class="min-w-0 flex items-center gap-1.5">

                                    <p class="text-xs font-semibold text-navy truncate">
                                        {{ $conversation['name'] }}
                                    </p>


                                    @if ($conversation['pinned'])

                                        <x-lucide-pin class="w-3 h-3 text-navy/25 shrink-0" />

                                    @endif

                                </div>


                                <span class="text-[10px] text-navy/30 shrink-0">
                                    {{ $conversation['time'] }}
                                </span>

                            </div>


                            <div class="flex items-center justify-between gap-2 mt-1">

                                <p
                                    data-row-last-message
                                    class="text-[11px] {{ $conversation['unread'] > 0 ? 'font-semibold text-navy/70' : 'text-navy/45' }} truncate"
                                >
                                    {{ $conversation['last_message'] }}
                                </p>


                                @if ($conversation['unread'] > 0)

                                    <span
                                        data-row-unread
                                        class="text-[9px] font-bold bg-coral text-white min-w-4 h-4 px-1 rounded-full flex items-center justify-center shrink-0"
                                    >
                                        {{ $conversation['unread'] }}
                                    </span>

                                @endif

                            </div>


                            @if (!empty($conversation['order']))

                                <div class="mt-1.5 flex items-center gap-1.5">

                                    <x-lucide-package class="w-3 h-3 text-navy/25 shrink-0" />

                                    <span class="text-[9px] font-semibold text-navy/30 truncate">
                                        {{ $conversation['order']['id'] }}
                                        ·
                                        {{ $conversation['order']['product'] }}
                                    </span>

                                </div>

                            @endif

                        </div>

                    </button>

                @endforeach


                <div
                    id="chatNoResults"
                    hidden
                    class="py-12 px-4 text-center"
                >

                    <div class="w-10 h-10 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-search-x class="w-4 h-4" />
                    </div>

                    <p class="text-xs font-semibold text-navy/50 mt-3">
                        No conversations found
                    </p>

                    <p class="text-[10px] text-navy/30 mt-1">
                        Try another search or filter.
                    </p>

                </div>

            </div>

        </aside>


        {{-- =====================================================
            MESSAGE THREAD
        ===================================================== --}}
        <main
            id="chatThreadPanel"
            class="flex-1 flex-col hidden md:flex min-w-0"
        >

            {{-- Thread header --}}
            <div class="flex items-center justify-between gap-3 px-3 sm:px-4 py-3 border-b border-gray-border">

                <div class="flex items-center gap-2.5 min-w-0">

                    <button
                        type="button"
                        id="mobileBackToChats"
                        class="md:hidden w-8 h-8 rounded-lg flex items-center justify-center text-navy/50 hover:bg-gray-bg shrink-0"
                    >
                        <x-lucide-arrow-left class="w-4 h-4" />
                    </button>


                    <div class="relative shrink-0">

                        <div
                            id="threadAvatar"
                            class="w-9 h-9 rounded-full bg-navy/10 text-navy flex items-center justify-center text-[11px] font-bold"
                        ></div>

                        <span
                            id="threadOnlineDot"
                            hidden
                            class="absolute right-0 bottom-0 w-3 h-3 rounded-full bg-teal border-2 border-white"
                        ></span>

                    </div>


                    <div class="min-w-0">

                        <p
                            id="threadName"
                            class="text-xs sm:text-sm font-bold text-navy truncate"
                        ></p>

                        <p
                            id="threadPresence"
                            class="text-[10px] text-navy/35 mt-0.5"
                        ></p>

                    </div>

                </div>


                <div class="flex items-center gap-1">

                    <button
                        type="button"
                        id="openOrderContext"
                        class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:bg-gray-bg hover:text-navy transition"
                    >
                        <x-lucide-package-search class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Order Details</span>
                    </button>


                    <button
                        type="button"
                        id="toggleChatInfo"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg hover:text-navy transition"
                    >
                        <x-lucide-info class="w-4 h-4" />
                    </button>

                </div>

            </div>


            {{-- Order context strip --}}
            <div
                id="threadOrderStrip"
                class="px-3 sm:px-4 py-2.5 border-b border-gray-border bg-gray-bg/30"
            >

                <div class="flex items-center gap-3">

                    <div
                        id="threadProductImageWrap"
                        class="w-10 h-10 rounded-lg border border-gray-border bg-white overflow-hidden shrink-0"
                    >

                        <img
                            id="threadProductImage"
                            src=""
                            alt=""
                            class="w-full h-full object-cover"
                        >

                    </div>


                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <p
                                id="threadOrderId"
                                class="text-[11px] font-bold text-navy"
                            ></p>

                            <span
                                id="threadOrderStatus"
                                class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                            ></span>

                        </div>


                        <p
                            id="threadOrderProduct"
                            class="text-[10px] text-navy/40 mt-0.5 truncate"
                        ></p>

                    </div>


                    <p
                        id="threadOrderTotal"
                        class="text-xs font-bold text-navy shrink-0"
                    ></p>

                </div>

            </div>


            {{-- Messages --}}
            <div
                id="threadMessages"
                class="flex-1 overflow-y-auto chat-scrollbar px-3 sm:px-4 py-4 space-y-3 bg-white"
            ></div>


            {{-- Quick replies --}}
            <div class="px-3 sm:px-4 pt-2 border-t border-gray-border bg-white">

                <div
                    id="quickReplies"
                    class="flex items-center gap-2 overflow-x-auto pb-2 chat-scrollbar"
                >

                    @foreach ([
                        'Noted po, thank you!',
                        'I’ll check your order po.',
                        'Your order is already being prepared.',
                        'I’ll update you once the rider picks it up.',
                    ] as $quickReply)

                        <button
                            type="button"
                            data-quick-reply="{{ $quickReply }}"
                            class="h-8 px-3 rounded-full border border-gray-border bg-white text-[10px] font-semibold text-navy/50 hover:border-teal/30 hover:text-teal-dark whitespace-nowrap transition"
                        >
                            {{ $quickReply }}
                        </button>

                    @endforeach

                </div>

            </div>


            {{-- Composer --}}
            <form
                id="chatSendForm"
                class="px-3 sm:px-4 py-3 border-t border-gray-border bg-white"
            >

                <div class="flex items-end gap-2">

                    <button
                        type="button"
                        id="demoAttachButton"
                        class="w-9 h-9 rounded-lg border border-gray-border text-navy/40 flex items-center justify-center hover:bg-gray-bg hover:text-navy shrink-0 transition"
                        title="Attach file"
                    >
                        <x-lucide-paperclip class="w-4 h-4" />
                    </button>


                    <div class="flex-1 relative">

                        <textarea
                            id="chatMessageInput"
                            rows="1"
                            maxlength="1000"
                            placeholder="Type a message..."
                            class="w-full min-h-9 max-h-28 px-3 py-2 pr-12 rounded-xl border border-gray-border text-xs text-navy placeholder:text-navy/30 resize-none focus:outline-none focus:border-teal/50"
                        ></textarea>


                        <span
                            id="messageCharacterCount"
                            class="absolute right-2.5 bottom-2 text-[9px] text-navy/20"
                        >
                            0
                        </span>

                    </div>


                    <button
                        type="submit"
                        id="chatSendButton"
                        disabled
                        class="w-9 h-9 rounded-lg bg-navy hover:bg-navy/90 text-white flex items-center justify-center shrink-0 transition disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <x-lucide-send class="w-4 h-4" />
                    </button>

                </div>


                <p class="text-[9px] text-navy/25 mt-1.5 ml-11">
                    Enter to send · Shift + Enter for a new line
                </p>

            </form>

        </main>


        {{-- =====================================================
            INFO / ORDER SIDE PANEL
        ===================================================== --}}
        <aside
            id="chatInfoPanel"
            hidden
            class="w-72 shrink-0 border-l border-gray-border bg-white flex flex-col"
        >

            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-border">

                <p class="text-xs font-bold text-navy">
                    Conversation Info
                </p>


                <button
                    type="button"
                    id="closeChatInfo"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg"
                >
                    <x-lucide-x class="w-3.5 h-3.5" />
                </button>

            </div>


            <div class="flex-1 overflow-y-auto chat-scrollbar p-4 space-y-5">

                {{-- Buyer --}}
                <div class="text-center">

                    <div
                        id="infoAvatar"
                        class="w-14 h-14 mx-auto rounded-full bg-navy/10 text-navy flex items-center justify-center text-sm font-bold"
                    ></div>


                    <p
                        id="infoName"
                        class="text-sm font-bold text-navy mt-2"
                    ></p>


                    <p
                        id="infoPresence"
                        class="text-[10px] text-navy/35 mt-0.5"
                    ></p>

                </div>


                {{-- Order --}}
                <div>

                    <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35 mb-2">
                        Related Order
                    </p>


                    <button
                        type="button"
                        id="infoOrderCard"
                        class="w-full rounded-xl border border-gray-border p-3 text-left hover:border-teal/30 hover:bg-teal/5 transition"
                    >

                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 rounded-lg bg-gray-bg border border-gray-border overflow-hidden shrink-0">

                                <img
                                    id="infoProductImage"
                                    src=""
                                    alt=""
                                    class="w-full h-full object-cover"
                                >

                            </div>


                            <div class="min-w-0 flex-1">

                                <p
                                    id="infoOrderId"
                                    class="text-[11px] font-bold text-navy"
                                ></p>

                                <p
                                    id="infoProductName"
                                    class="text-[10px] text-navy/45 mt-0.5 truncate"
                                ></p>

                                <p
                                    id="infoOrderArea"
                                    class="text-[9px] text-navy/30 mt-0.5 truncate"
                                ></p>

                            </div>

                        </div>


                        <div class="mt-3 pt-3 border-t border-gray-border flex items-center justify-between gap-2">

                            <span
                                id="infoOrderStatus"
                                class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                            ></span>

                            <span
                                id="infoOrderTotal"
                                class="text-[11px] font-bold text-navy"
                            ></span>

                        </div>

                    </button>

                </div>


                {{-- Helpful reminders --}}
                <div>

                    <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35 mb-2">
                        Messaging Tips
                    </p>


                    <div class="space-y-2">

                        <div class="rounded-lg bg-gray-bg p-2.5">

                            <p class="text-[10px] text-navy/50 leading-relaxed">
                                Keep order updates clear and avoid promising exact courier delivery times unless confirmed.
                            </p>

                        </div>


                        <div class="rounded-lg bg-gray-bg p-2.5">

                            <p class="text-[10px] text-navy/50 leading-relaxed">
                                For damaged-item concerns, acknowledge the issue and keep the order reference visible.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>


{{-- =============================================================
    ORDER DETAILS MODAL
============================================================= --}}
<div
    id="chatOrderModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div
        data-close-chat-order
        class="absolute inset-0 bg-navy/45"
    ></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Related Order
                </p>

                <p
                    id="modalOrderId"
                    class="text-[11px] text-navy/40 mt-0.5"
                ></p>

            </div>


            <button
                type="button"
                data-close-chat-order
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5 space-y-4">

            <div class="flex items-center gap-3">

                <div class="w-16 h-16 rounded-xl border border-gray-border bg-gray-bg overflow-hidden shrink-0">

                    <img
                        id="modalProductImage"
                        src=""
                        alt=""
                        class="w-full h-full object-cover"
                    >

                </div>


                <div class="min-w-0">

                    <p
                        id="modalProductName"
                        class="text-sm font-bold text-navy"
                    ></p>

                    <p
                        id="modalVariant"
                        class="text-[11px] text-navy/45 mt-1"
                    ></p>

                    <p
                        id="modalQuantity"
                        class="text-[10px] text-navy/35 mt-0.5"
                    ></p>

                </div>

            </div>


            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl bg-gray-bg p-3">

                    <p class="text-[10px] text-navy/35">
                        Order Status
                    </p>

                    <span
                        id="modalOrderStatus"
                        class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold"
                    ></span>

                </div>


                <div class="rounded-xl bg-gray-bg p-3">

                    <p class="text-[10px] text-navy/35">
                        Order Total
                    </p>

                    <p
                        id="modalOrderTotal"
                        class="text-sm font-bold text-navy mt-1"
                    ></p>

                </div>

            </div>


            <div class="rounded-xl border border-gray-border p-3">

                <p class="text-[10px] text-navy/35">
                    Delivery Area
                </p>

                <p
                    id="modalDeliveryArea"
                    class="text-xs font-semibold text-navy mt-1"
                ></p>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
    DEMO TOAST
============================================================= --}}
<div
    id="chatToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>

    <div class="flex items-start gap-3">

        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-info class="w-4 h-4" />
        </div>


        <div>

            <p class="text-xs font-bold text-navy">
                Chat demo
            </p>

            <p
                id="chatToastMessage"
                class="text-[11px] text-navy/45 mt-0.5"
            ></p>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const conversations =
        @json($conversations->values());


    const statusClasses = {
        PLACED: 'bg-coral/10 text-coral',
        CONFIRMED: 'bg-sky/10 text-sky',
        PREPARING: 'bg-yellow/20 text-amber-700',
        READY_FOR_PICKUP: 'bg-teal/10 text-teal-dark',
        PICKED_UP: 'bg-navy/10 text-navy',
        OUT_FOR_DELIVERY: 'bg-sky/10 text-sky',
        DELIVERED: 'bg-teal/10 text-teal-dark',
        COMPLETED: 'bg-teal-light text-teal-dark',
        DELIVERY_FAILED: 'bg-red-50 text-red-500',
        RETURNED: 'bg-red-50 text-red-500'
    };


    const rows =
        Array.from(
            document.querySelectorAll('[data-conversation-trigger]')
        );


    const listPanel =
        document.getElementById('chatListPanel');

    const threadPanel =
        document.getElementById('chatThreadPanel');

    const infoPanel =
        document.getElementById('chatInfoPanel');


    const threadMessages =
        document.getElementById('threadMessages');

    const threadName =
        document.getElementById('threadName');

    const threadAvatar =
        document.getElementById('threadAvatar');

    const threadPresence =
        document.getElementById('threadPresence');

    const threadOnlineDot =
        document.getElementById('threadOnlineDot');


    const searchInput =
        document.getElementById('chatSearchInput');

    const noResults =
        document.getElementById('chatNoResults');


    const messageInput =
        document.getElementById('chatMessageInput');

    const sendButton =
        document.getElementById('chatSendButton');

    const characterCount =
        document.getElementById('messageCharacterCount');


    const orderModal =
        document.getElementById('chatOrderModal');


    const toast =
        document.getElementById('chatToast');

    const toastMessage =
        document.getElementById('chatToastMessage');


    let activeId =
        conversations[0]?.id ?? null;

    let activeFilter =
        'all';

    let toastTimer =
        null;


    function money(value) {

        return '₱' +
            Number(value || 0)
                .toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
    }


    function getConversation(id) {

        return conversations.find(
            conversation =>
                Number(conversation.id) === Number(id)
        );
    }


    function showToast(message) {

        if (!toast) {
            return;
        }


        toastMessage.textContent =
            message;


        toast.hidden =
            false;


        if (toastTimer) {
            clearTimeout(toastTimer);
        }


        toastTimer =
            setTimeout(function () {

                toast.hidden =
                    true;

            }, 3000);
    }


    function setStatusBadge(element, status) {

        if (!element) {
            return;
        }


        const normalized =
            String(status || '')
                .toUpperCase();


        element.className =
            'px-2 py-0.5 rounded-full text-[9px] font-bold ' +
            (
                statusClasses[normalized] ||
                'bg-navy/10 text-navy/50'
            );


        element.textContent =
            normalized
                .replaceAll('_', ' ');
    }


    function markConversationRead(id) {

        const convo =
            getConversation(id);


        if (!convo) {
            return;
        }


        convo.unread =
            0;


        const row =
            rows.find(
                item =>
                    Number(item.dataset.conversationId) === Number(id)
            );


        if (!row) {
            return;
        }


        row.dataset.unread =
            '0';


        row
            .querySelector('[data-row-unread]')
            ?.remove();


        const lastMessage =
            row.querySelector('[data-row-last-message]');


        if (lastMessage) {

            lastMessage.classList.remove(
                'font-semibold',
                'text-navy/70'
            );


            lastMessage.classList.add(
                'text-navy/45'
            );
        }
    }


    function renderMessage(message) {

        const bubble =
            document.createElement('div');


        const isSeller =
            message.from === 'seller';


        bubble.className =
            'flex ' +
            (isSeller ? 'justify-end' : 'justify-start');


        const statusText =
            isSeller && message.status
                ? (
                    message.status === 'read'
                        ? 'Read'
                        : 'Sent'
                )
                : '';


        bubble.innerHTML = `
            <div class="max-w-[82%] sm:max-w-[72%]">

                <div class="
                    ${isSeller ? 'bg-navy text-white' : 'bg-gray-bg text-navy'}
                    rounded-2xl
                    ${isSeller ? 'rounded-br-md' : 'rounded-bl-md'}
                    px-3 py-2.5
                ">

                    <p class="text-xs leading-relaxed whitespace-pre-wrap break-words"></p>

                </div>

                <div class="
                    flex items-center gap-1 mt-1
                    ${isSeller ? 'justify-end' : 'justify-start'}
                ">

                    <span class="text-[9px] text-navy/30">
                        ${message.time || ''}
                    </span>

                    ${
                        statusText
                            ? `
                                <span class="text-[9px] text-navy/25">
                                    · ${statusText}
                                </span>
                            `
                            : ''
                    }

                </div>

            </div>
        `;


        bubble
            .querySelector('p')
            .textContent =
                message.text || '';


        threadMessages.appendChild(
            bubble
        );
    }


    function renderThread(id) {

        const convo =
            getConversation(id);


        if (!convo) {
            return;
        }


        activeId =
            Number(id);


        markConversationRead(
            activeId
        );


        rows.forEach(function (row) {

            row.classList.toggle(
                'conversation-active',
                Number(row.dataset.conversationId) === activeId
            );
        });


        threadName.textContent =
            convo.name;


        threadAvatar.textContent =
            convo.initials ||
            convo.name
                .charAt(0)
                .toUpperCase();


        threadPresence.textContent =
            convo.last_seen || '';


        threadPresence.className =
            'text-[10px] mt-0.5 ' +
            (
                convo.online
                    ? 'text-teal-dark'
                    : 'text-navy/35'
            );


        threadOnlineDot.hidden =
            !convo.online;


        threadMessages.innerHTML =
            '';


        /*
         * Demo day separator.
         */
        const separator =
            document.createElement('div');


        separator.className =
            'flex items-center gap-3 py-1';


        separator.innerHTML = `
            <div class="h-px bg-gray-border flex-1"></div>
            <span class="text-[9px] font-semibold text-navy/25">
                Conversation
            </span>
            <div class="h-px bg-gray-border flex-1"></div>
        `;


        threadMessages.appendChild(
            separator
        );


        (convo.messages || [])
            .forEach(
                renderMessage
            );


        updateOrderContext(
            convo
        );


        updateInfoPanel(
            convo
        );


        threadMessages.scrollTop =
            threadMessages.scrollHeight;


        /*
         * Mobile: hide list and show thread.
         */
        if (
            window.innerWidth < 768
        ) {

            listPanel.classList.add(
                'mobile-hidden'
            );


            threadPanel.classList.remove(
                'mobile-hidden',
                'hidden'
            );


            threadPanel.classList.add(
                'flex'
            );
        }
    }


    function updateOrderContext(convo) {

        const order =
            convo.order || {};


        document
            .getElementById('threadOrderId')
            .textContent =
                order.id || 'No linked order';


        setStatusBadge(
            document.getElementById('threadOrderStatus'),
            order.status
        );


        document
            .getElementById('threadOrderProduct')
            .textContent =
                [
                    order.product,
                    order.variant,
                    order.qty
                        ? 'Qty ' + order.qty
                        : null
                ]
                    .filter(Boolean)
                    .join(' · ');


        document
            .getElementById('threadOrderTotal')
            .textContent =
                money(order.total);


        const image =
            document.getElementById('threadProductImage');

        const imageWrap =
            document.getElementById('threadProductImageWrap');


        if (order.image) {

            image.src =
                order.image;

            imageWrap.hidden =
                false;

        } else {

            image.removeAttribute(
                'src'
            );

            imageWrap.hidden =
                true;
        }
    }


    function updateInfoPanel(convo) {

        const order =
            convo.order || {};


        document
            .getElementById('infoAvatar')
            .textContent =
                convo.initials ||
                convo.name?.charAt(0) ||
                '';


        document
            .getElementById('infoName')
            .textContent =
                convo.name || '';


        document
            .getElementById('infoPresence')
            .textContent =
                convo.last_seen || '';


        document
            .getElementById('infoOrderId')
            .textContent =
                order.id || 'No linked order';


        document
            .getElementById('infoProductName')
            .textContent =
                [
                    order.product,
                    order.variant
                ]
                    .filter(Boolean)
                    .join(' · ');


        document
            .getElementById('infoOrderArea')
            .textContent =
                order.delivery_area || '';


        document
            .getElementById('infoOrderTotal')
            .textContent =
                money(order.total);


        setStatusBadge(
            document.getElementById('infoOrderStatus'),
            order.status
        );


        const image =
            document.getElementById('infoProductImage');


        if (order.image) {

            image.src =
                order.image;

        } else {

            image.removeAttribute(
                'src'
            );
        }
    }


    /* ---------------------------------------------------------
       CONVERSATION FILTERS
    --------------------------------------------------------- */
    function rowMatchesFilter(row) {

        if (
            activeFilter === 'all'
        ) {
            return true;
        }


        if (
            activeFilter === 'unread'
        ) {
            return row.dataset.unread === '1';
        }


        if (
            activeFilter === 'online'
        ) {
            return row.dataset.online === '1';
        }


        if (
            activeFilter === 'pinned'
        ) {
            return row.dataset.pinned === '1';
        }


        return true;
    }


    function applyConversationFilters() {

        const query =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();


        let shown =
            0;


        rows.forEach(function (row) {

            const filterMatch =
                rowMatchesFilter(row);


            const searchMatch =
                !query ||
                (row.dataset.search || '')
                    .includes(query);


            const show =
                filterMatch &&
                searchMatch;


            row.hidden =
                !show;


            if (show) {
                shown++;
            }
        });


        noResults.hidden =
            shown !== 0;


        document
            .querySelectorAll('[data-chat-filter]')
            .forEach(function (button) {

                const active =
                    button.dataset.chatFilter === activeFilter;


                button.classList.toggle(
                    'chat-filter-active',
                    active
                );


                button.classList.toggle(
                    'text-navy/45',
                    !active
                );


                button.classList.toggle(
                    'hover:bg-gray-bg',
                    !active
                );
            });
    }


    document
        .querySelectorAll('[data-chat-filter]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    activeFilter =
                        button.dataset.chatFilter || 'all';


                    applyConversationFilters();
                }
            );
        });


    searchInput?.addEventListener(
        'input',
        applyConversationFilters
    );


    document
        .getElementById('chatClearSearch')
        ?.addEventListener(
            'click',
            function () {

                activeFilter =
                    'all';


                if (searchInput) {
                    searchInput.value = '';
                }


                applyConversationFilters();
            }
        );


    /* ---------------------------------------------------------
       OPEN CONVERSATION
    --------------------------------------------------------- */
    rows.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                renderThread(
                    Number(button.dataset.conversationId)
                );
            }
        );
    });


    /* ---------------------------------------------------------
       COMPOSER
    --------------------------------------------------------- */
    function refreshComposer() {

        const text =
            messageInput?.value || '';


        const length =
            text.length;


        if (characterCount) {
            characterCount.textContent =
                length;
        }


        if (sendButton) {
            sendButton.disabled =
                text.trim().length === 0;
        }


        if (messageInput) {

            messageInput.style.height =
                'auto';


            messageInput.style.height =
                Math.min(
                    messageInput.scrollHeight,
                    112
                ) + 'px';
        }
    }


    messageInput?.addEventListener(
        'input',
        refreshComposer
    );


    messageInput?.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Enter' &&
                !event.shiftKey
            ) {

                event.preventDefault();


                document
                    .getElementById('chatSendForm')
                    ?.requestSubmit();
            }
        }
    );


    document
        .getElementById('chatSendForm')
        ?.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();


                const text =
                    (messageInput?.value || '')
                        .trim();


                if (
                    !text ||
                    activeId === null
                ) {
                    return;
                }


                const convo =
                    getConversation(
                        activeId
                    );


                if (!convo) {
                    return;
                }


                convo.messages.push({
                    from: 'seller',
                    text: text,
                    time: 'Just now',
                    status: 'sent'
                });


                convo.last_message =
                    text;


                convo.time =
                    'Just now';


                const row =
                    rows.find(
                        item =>
                            Number(item.dataset.conversationId) === activeId
                    );


                if (row) {

                    const last =
                        row.querySelector('[data-row-last-message]');


                    if (last) {
                        last.textContent =
                            text;
                    }
                }


                messageInput.value =
                    '';


                refreshComposer();


                renderThread(
                    activeId
                );
            }
        );


    /* ---------------------------------------------------------
       QUICK REPLIES
    --------------------------------------------------------- */
    document
        .querySelectorAll('[data-quick-reply]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    if (!messageInput) {
                        return;
                    }


                    messageInput.value =
                        button.dataset.quickReply || '';


                    refreshComposer();


                    messageInput.focus();
                }
            );
        });


    /* ---------------------------------------------------------
       ATTACHMENT DEMO
    --------------------------------------------------------- */
    document
        .getElementById('demoAttachButton')
        ?.addEventListener(
            'click',
            function () {

                showToast(
                    'Attachment UI is ready. Your backend teammate can connect image/file uploads later.'
                );
            }
        );


    /* ---------------------------------------------------------
       INFO PANEL
    --------------------------------------------------------- */
    function toggleInfoPanel(force) {

        const shouldOpen =
            typeof force === 'boolean'
                ? force
                : infoPanel.hidden;


        infoPanel.hidden =
            !shouldOpen;
    }


    document
        .getElementById('toggleChatInfo')
        ?.addEventListener(
            'click',
            function () {

                toggleInfoPanel();
            }
        );


    document
        .getElementById('closeChatInfo')
        ?.addEventListener(
            'click',
            function () {

                toggleInfoPanel(false);
            }
        );


    /* ---------------------------------------------------------
       ORDER MODAL
    --------------------------------------------------------- */
    function openOrderModal() {

        const convo =
            getConversation(activeId);


        if (!convo?.order) {
            return;
        }


        const order =
            convo.order;


        document
            .getElementById('modalOrderId')
            .textContent =
                order.id || '';


        document
            .getElementById('modalProductName')
            .textContent =
                order.product || '';


        document
            .getElementById('modalVariant')
            .textContent =
                order.variant || 'Default variant';


        document
            .getElementById('modalQuantity')
            .textContent =
                'Quantity: ' +
                (order.qty || 0);


        document
            .getElementById('modalOrderTotal')
            .textContent =
                money(order.total);


        document
            .getElementById('modalDeliveryArea')
            .textContent =
                order.delivery_area || '';


        setStatusBadge(
            document.getElementById('modalOrderStatus'),
            order.status
        );


        const image =
            document.getElementById('modalProductImage');


        if (order.image) {

            image.src =
                order.image;

        } else {

            image.removeAttribute(
                'src'
            );
        }


        orderModal.hidden =
            false;


        document.body.style.overflow =
            'hidden';
    }


    document
        .getElementById('openOrderContext')
        ?.addEventListener(
            'click',
            openOrderModal
        );


    document
        .getElementById('infoOrderCard')
        ?.addEventListener(
            'click',
            openOrderModal
        );


    document
        .querySelectorAll('[data-close-chat-order]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    orderModal.hidden =
                        true;


                    document.body.style.overflow =
                        '';
                }
            );
        });


    /* ---------------------------------------------------------
       MOBILE BACK
    --------------------------------------------------------- */
    document
        .getElementById('mobileBackToChats')
        ?.addEventListener(
            'click',
            function () {

                threadPanel.classList.add(
                    'mobile-hidden'
                );


                listPanel.classList.remove(
                    'mobile-hidden'
                );
            }
        );


    /* ---------------------------------------------------------
       ESCAPE
    --------------------------------------------------------- */
    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                !orderModal.hidden
            ) {

                orderModal.hidden =
                    true;


                document.body.style.overflow =
                    '';
            }
        }
    );


    /* ---------------------------------------------------------
       INITIAL STATE
    --------------------------------------------------------- */
    applyConversationFilters();
    refreshComposer();


    if (
        activeId !== null &&
        window.innerWidth >= 768
    ) {

        renderThread(
            activeId
        );
    }


    window.addEventListener(
        'resize',
        function () {

            if (
                window.innerWidth >= 768
            ) {

                listPanel.classList.remove(
                    'mobile-hidden'
                );


                threadPanel.classList.remove(
                    'mobile-hidden'
                );


                if (
                    activeId !== null
                ) {

                    threadPanel.classList.remove(
                        'hidden'
                    );


                    threadPanel.classList.add(
                        'flex'
                    );
                }
            }
        }
    );
});
</script>
@endpush

@endsection
