@extends('admin.layout')

@section('title', 'Chat / Messaging')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | Hardcoded muna para sa UI/UX development. Palitan na lang ito ng
    | backend teammate mo ng galing-sa-controller na data:
    | - actual buyer/seller conversations + messages
    | - unread counts, online status (presence channel)
    | - related order (kapag buyer) o store details (kapag seller)
    | - pinned/archived state per admin user
    | - attachments, typing indicator, delivery/read receipts
    */

    $conversations = collect($conversations ?? [
        [
            'id' => 1,
            'name' => 'TechHub PH',
            'role' => 'seller',
            'initials' => 'TH',
            'online' => true,
            'last_seen' => 'Online now',
            'last_message' => 'Sir, kailan po ma-release yung payout namin?',
            'time' => '2m',
            'unread' => 2,
            'pinned' => true,
            'archived' => false,
            'store' => [
                'id' => 'SEL-1042',
                'category' => 'Electronics & Gadgets',
                'location' => 'Quezon City',
                'rating' => 4.8,
                'verified' => true,
                'products_count' => 124,
                'joined' => 'Member since Jan 2024',
                'pending_payout' => 8450,
            ],
            'messages' => [
                ['from' => 'seller', 'text' => 'Magandang araw po! Sir, kailan po ma-release yung payout namin para sa nakaraang linggo?', 'time' => '9:14 AM', 'status' => null],
                ['from' => 'admin', 'text' => 'Hi TechHub PH! Kinukumpirma ko lang po sa finance team, i-uupdate ko kayo within the day.', 'time' => '9:20 AM', 'status' => 'read'],
                ['from' => 'seller', 'text' => 'Salamat po sa update, sir!', 'time' => '9:21 AM', 'status' => null],
            ],
        ],

        [
            'id' => 2,
            'name' => "Aling Nena's Store",
            'role' => 'seller',
            'initials' => 'AN',
            'online' => false,
            'last_seen' => 'Active 1h ago',
            'last_message' => 'Salamat po sa pag-verify sa account namin!',
            'time' => '1h',
            'unread' => 0,
            'pinned' => false,
            'archived' => false,
            'store' => [
                'id' => 'SEL-0871',
                'category' => 'Home-baked Goods',
                'location' => 'Batangas City',
                'rating' => 4.6,
                'verified' => true,
                'products_count' => 58,
                'joined' => 'Member since Mar 2023',
                'pending_payout' => 0,
            ],
            'messages' => [
                ['from' => 'admin', 'text' => 'Hi Aling Nena! Na-verify na po namin ang inyong seller account.', 'time' => 'Yesterday, 3:10 PM', 'status' => 'read'],
                ['from' => 'seller', 'text' => 'Salamat po sa pag-verify sa account namin!', 'time' => 'Yesterday, 3:15 PM', 'status' => null],
            ],
        ],

        [
            'id' => 3,
            'name' => 'Maricel Santos',
            'role' => 'buyer',
            'initials' => 'MS',
            'online' => true,
            'last_seen' => 'Online now',
            'last_message' => 'Pwede po ba bukas na po ma-deliver?',
            'time' => '10:24 AM',
            'unread' => 1,
            'pinned' => true,
            'archived' => false,
            'order' => [
                'id' => 'ORD-10231',
                'status' => 'OUT_FOR_DELIVERY',
                'total' => 1780,
                'delivery_area' => 'Calamba, Laguna',
                'product' => 'Handwoven Rattan Basket',
                'variant' => 'Natural / Medium',
                'qty' => 2,
            ],
            'messages' => [
                ['from' => 'buyer', 'text' => 'Hi po, order ko po kelan po darating?', 'time' => '10:10 AM', 'status' => null],
                ['from' => 'admin', 'text' => 'Hi Maricel! Ipa-pickup na po namin ngayon, dapat bukas dumating.', 'time' => '10:15 AM', 'status' => 'read'],
                ['from' => 'buyer', 'text' => 'Pwede po ba bukas na po ma-deliver?', 'time' => '10:24 AM', 'status' => null],
            ],
        ],

        [
            'id' => 4,
            'name' => 'Maria Reyes',
            'role' => 'buyer',
            'initials' => 'MR',
            'online' => false,
            'last_seen' => 'Active 3h ago',
            'last_message' => 'Paano po mag-file ng refund request?',
            'time' => '3h',
            'unread' => 0,
            'pinned' => false,
            'archived' => false,
            'order' => [
                'id' => 'ORD-10298',
                'status' => 'REFUND_REQUESTED',
                'total' => 950,
                'delivery_area' => 'Los Baños, Laguna',
                'product' => 'Ceramic Vase Set',
                'variant' => 'Blue / Set of 2',
                'qty' => 1,
            ],
            'messages' => [
                ['from' => 'buyer', 'text' => 'Paano po mag-file ng refund request?', 'time' => '2:40 PM', 'status' => null],
                ['from' => 'admin', 'text' => 'Hi Maria! Pwede niyo pong i-submit ang refund form sa Orders tab, i-attach niyo lang po yung photo ng item.', 'time' => '2:48 PM', 'status' => 'read'],
            ],
        ],

        [
            'id' => 5,
            'name' => 'Grace Fernandez',
            'role' => 'buyer',
            'initials' => 'GF',
            'online' => true,
            'last_seen' => 'Online now',
            'last_message' => 'May tanong lang po ako sa aking order.',
            'time' => '20m',
            'unread' => 1,
            'pinned' => false,
            'archived' => false,
            'order' => [
                'id' => 'ORD-10305',
                'status' => 'PREPARING',
                'total' => 640,
                'delivery_area' => 'Imus, Cavite',
                'product' => 'Woven Placemat Set',
                'variant' => 'Beige / Set of 4',
                'qty' => 1,
            ],
            'messages' => [
                ['from' => 'buyer', 'text' => 'Magandang araw po! May tanong lang po ako sa aking order.', 'time' => '9:50 AM', 'status' => null],
            ],
        ],

        [
            'id' => 6,
            'name' => "Jomar's Repair Shop",
            'role' => 'seller',
            'initials' => 'JR',
            'online' => false,
            'last_seen' => 'Active 1d ago',
            'last_message' => 'Follow up po sa compliance documents.',
            'time' => 'Archived Aug 30',
            'unread' => 0,
            'pinned' => false,
            'archived' => true,
            'store' => [
                'id' => 'SEL-0333',
                'category' => 'Electronics Repair',
                'location' => 'Dasmariñas, Cavite',
                'rating' => 4.2,
                'verified' => false,
                'products_count' => 12,
                'joined' => 'Member since Nov 2022',
                'pending_payout' => 0,
            ],
            'messages' => [
                ['from' => 'seller', 'text' => 'Follow up po sa compliance documents.', 'time' => 'Aug 29, 4:00 PM', 'status' => null],
            ],
        ],
    ]);

    $totalUnread = $conversations->sum('unread');
    $onlineCount = $conversations->where('online', true)->count();
    $activeCount = $conversations->where('archived', false)->count();
    $archivedCount = $conversations->where('archived', true)->count();

    $orderStatusClasses = [
        'PLACED' => 'bg-coral/10 text-coral',
        'CONFIRMED' => 'bg-sky/10 text-sky',
        'PREPARING' => 'bg-amber-50 text-amber-600',
        'READY_FOR_PICKUP' => 'bg-mint/10 text-mint-dark',
        'PICKED_UP' => 'bg-navy/10 text-navy',
        'OUT_FOR_DELIVERY' => 'bg-sky/10 text-sky',
        'DELIVERED' => 'bg-mint/10 text-mint-dark',
        'COMPLETED' => 'bg-mint/15 text-mint-dark',
        'DELIVERY_FAILED' => 'bg-red-50 text-red-500',
        'RETURNED' => 'bg-red-50 text-red-500',
        'REFUND_REQUESTED' => 'bg-coral/10 text-coral',
    ];

    // Broadcast target list — galing sa parehong demo conversations, iisa na lang ang source of truth.
    $broadcastUsers = $conversations->map(fn ($c) => [
        'id' => $c['id'],
        'name' => $c['name'],
        'role' => ucfirst($c['role']),
    ])->values();
@endphp

<style>
    #adminChat {
        height: calc(100vh - 220px);
        min-height: 560px;
    }

    #adminChat .chat-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 44, 63, .14) transparent;
    }

    #adminChat .chat-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #adminChat .chat-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(15, 44, 63, .14);
        border-radius: 999px;
    }

    .conversation-active {
        background: rgba(46, 207, 166, .08);
        box-shadow: inset 2px 0 0 #0F9D74;
    }

    .chat-filter-active {
        background: #0F2C3F;
        color: #ffffff;
    }

    #threadOptionsMenu[hidden],
    #chatOrderModal[hidden],
    #chatStoreModal[hidden] {
        display: none !important;
    }

    @media (max-width: 767px) {
        #adminChat {
            height: calc(100vh - 170px);
            min-height: 480px;
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

    {{-- ============ PAGE HEADER ============ --}}
    <section class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-navy">Chat / Messaging</h1>
            <p class="text-sm text-slate-500 mt-1">Makipag-usap sa mga buyer at seller, o mag-broadcast ng announcement.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg bg-coral/10 text-[11px] font-bold text-coral">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-4.5 7.5 8.5 8.5 0 01-9-1L3 21l1.9-5.7A8.38 8.38 0 013 11.5a8.5 8.5 0 018.5-8.5h.5a8.48 8.48 0 018 8v.5z" />
                </svg>
                <span id="statUnreadCount">{{ $totalUnread }}</span> unread
            </div>

            <div class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg bg-mint/10 text-[11px] font-bold text-mint-dark">
                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span>
                <span id="statOnlineCount">{{ $onlineCount }}</span> online
            </div>

            <button type="button" onclick="openBroadcastModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-navy hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                Broadcast Announcement
            </button>
        </div>
    </section>

    {{-- ============ CHAT PANEL ============ --}}
    <div id="adminChat" class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex shadow-sm">

        {{-- ===== CONVERSATION LIST ===== --}}
        <aside id="chatListPanel" class="w-full sm:w-80 border-r border-slate-100 flex flex-col shrink-0">

            <div class="p-4 border-b border-slate-100 space-y-3">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="chatSearchInput" placeholder="Search buyer, seller, or order..."
                        class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                </div>

                {{-- Active / Archived tabs --}}
                <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm font-medium">
                    <button type="button" data-chat-view="active" id="tabActiveBtn"
                        class="chat-view-tab flex-1 py-1.5 rounded-lg bg-white text-navy shadow-sm transition">
                        Active
                        <span id="activeCountBadge" class="ml-1 text-[10px] font-bold text-white bg-coral rounded-full px-1.5 py-0.5">{{ $activeCount }}</span>
                    </button>
                    <button type="button" data-chat-view="archived" id="tabArchivedBtn"
                        class="chat-view-tab flex-1 py-1.5 rounded-lg text-slate-500 hover:text-navy transition">
                        Archived
                        <span id="archivedCountBadge" class="ml-1 text-[10px] font-bold text-slate-500 bg-slate-200 rounded-full px-1.5 py-0.5">{{ $archivedCount }}</span>
                    </button>
                </div>

                {{-- Filter chips --}}
                <div id="filterChips" class="flex items-center gap-1 text-xs flex-wrap">
                    <button type="button" data-chat-filter="all" class="chat-filter chat-filter-active px-2.5 py-1 rounded-full">All</button>
                    <button type="button" data-chat-filter="seller" class="chat-filter px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Sellers</button>
                    <button type="button" data-chat-filter="buyer" class="chat-filter px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Buyers</button>
                    <button type="button" data-chat-filter="unread" class="chat-filter px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Unread</button>
                    <button type="button" data-chat-filter="pinned" class="chat-filter px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Pinned</button>
                </div>
            </div>

            {{-- Conversation rows --}}
            <div id="conversationList" class="flex-1 overflow-y-auto chat-scrollbar divide-y divide-slate-100">

                @foreach ($conversations as $conversation)
                    @php
                        $searchText = strtolower(
                            $conversation['name'] . ' ' .
                            $conversation['role'] . ' ' .
                            $conversation['last_message'] . ' ' .
                            ($conversation['order']['id'] ?? '') . ' ' .
                            ($conversation['order']['product'] ?? '') . ' ' .
                            ($conversation['store']['id'] ?? '')
                        );
                    @endphp

                    <div data-conversation-trigger role="button" tabindex="0"
                        data-conversation-id="{{ $conversation['id'] }}"
                        data-unread="{{ $conversation['unread'] > 0 ? '1' : '0' }}"
                        data-online="{{ $conversation['online'] ? '1' : '0' }}"
                        data-pinned="{{ $conversation['pinned'] ? '1' : '0' }}"
                        data-archived="{{ $conversation['archived'] ? '1' : '0' }}"
                        data-role="{{ $conversation['role'] }}"
                        data-search="{{ $searchText }}"
                        class="convo-item group relative flex items-start gap-3 px-4 py-3 pr-14 cursor-pointer hover:bg-slate-50 transition {{ $loop->first ? 'conversation-active' : '' }}">

                        {{-- Hover actions --}}
                        <div class="hidden group-hover:flex items-center gap-1 absolute top-2.5 right-2.5 bg-white rounded-lg shadow border border-slate-100 p-0.5 z-10">
                            <button type="button" title="Pin conversation" onclick="toggleRowPin(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a5 5 0 00-5 5c0 4 5 10 5 10s5-6 5-10a5 5 0 00-5-5z" />
                                    <circle cx="12" cy="7" r="2" />
                                </svg>
                            </button>
                            <button type="button" title="Mark as unread" onclick="toggleRowUnread(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6" /></svg>
                            </button>
                            <button type="button" title="Archive" onclick="toggleRowArchive(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" />
                                </svg>
                            </button>
                        </div>

                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $conversation['role'] === 'seller' ? 'bg-sky/15 text-sky' : 'bg-coral/15 text-coral' }}">
                                <span class="text-xs font-bold">{{ $conversation['initials'] }}</span>
                            </div>
                            @if ($conversation['online'])
                                <span class="absolute right-0 bottom-0 w-2.5 h-2.5 rounded-full bg-mint-dark border-2 border-white"></span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="convo-name text-sm font-semibold {{ $conversation['unread'] > 0 ? 'text-mint-dark' : 'text-navy' }} truncate">{{ $conversation['name'] }}</p>
                                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5 {{ $conversation['role'] === 'seller' ? 'text-sky bg-sky/10' : 'text-coral bg-coral/10' }}">{{ $conversation['role'] }}</span>
                                    <svg data-row-pin-icon class="w-3 h-3 text-amber-500 shrink-0 {{ $conversation['pinned'] ? '' : 'hidden' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2a5 5 0 00-5 5c0 4 5 10 5 10s5-6 5-10a5 5 0 00-5-5z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $conversation['time'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <p data-row-last-message class="convo-preview text-xs truncate {{ $conversation['unread'] > 0 ? 'font-semibold text-navy' : 'text-slate-500' }}">{{ $conversation['last_message'] }}</p>
                                @if ($conversation['unread'] > 0)
                                    <span data-row-unread class="text-[9px] font-bold bg-coral text-white min-w-4 h-4 px-1 rounded-full flex items-center justify-center shrink-0">{{ $conversation['unread'] }}</span>
                                @endif
                            </div>
                            @if ($conversation['role'] === 'buyer' && !empty($conversation['order']))
                                <p class="text-[10px] text-slate-400 mt-1">Order #{{ $conversation['order']['id'] }}</p>
                            @elseif ($conversation['role'] === 'seller' && !empty($conversation['store']))
                                <p class="text-[10px] text-slate-400 mt-1">Store ID {{ $conversation['store']['id'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div id="chatNoResults" hidden class="py-12 px-4 text-center text-slate-400">
                    <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-xs">Walang nahanap na conversation.</p>
                </div>
            </div>
        </aside>

        {{-- ===== MESSAGE THREAD ===== --}}
        <main id="chatThreadPanel" class="hidden sm:flex flex-1 flex-col min-w-0">

            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="mobileBackToChats" class="sm:hidden w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>

                    <div class="relative shrink-0">
                        <div id="threadAvatar" class="w-9 h-9 rounded-full flex items-center justify-center">
                            <span id="threadAvatarInitials" class="text-xs font-bold"></span>
                        </div>
                        <span id="threadOnlineDot" hidden class="absolute right-0 bottom-0 w-3 h-3 rounded-full bg-mint-dark border-2 border-white"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <p id="threadName" class="text-sm font-semibold text-navy truncate"></p>
                            <span id="threadRoleBadge" class="shrink-0 text-[9px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5"></span>
                        </div>
                        <p id="threadPresence" class="text-xs mt-0.5"></p>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" id="contextBtn" onclick="openContextModal()" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                        <x-lucide-package-search id="contextBtnIconOrder" class="w-4 h-4" />
                        <x-lucide-store id="contextBtnIconStore" class="w-4 h-4 hidden" />
                    </button>

                    <button type="button" id="muteBtn" onclick="toggleMute()" title="Mute notifications" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg id="muteIconOff" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53" />
                        </svg>
                        <svg id="muteIconOn" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <button type="button" id="infoBtn" onclick="toggleConvoInfo()" title="Conversation info" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 11h1v5h1" />
                        </svg>
                    </button>

                    <div class="relative">
                        <button type="button" onclick="toggleThreadMenu(event)" title="More options" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="19" cy="12" r="1.5" /></svg>
                        </button>
                        <div id="threadMenu" class="hidden absolute right-0 mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-10 text-sm">
                            <button type="button" onclick="togglePinCurrent()" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2a5 5 0 00-5 5c0 4 5 10 5 10s5-6 5-10a5 5 0 00-5-5z" /><circle cx="12" cy="7" r="2" /></svg>
                                <span id="threadPinLabel">Pin conversation</span>
                            </button>
                            <button type="button" onclick="archiveCurrentConversation()" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" /></svg>
                                <span id="threadArchiveLabel">Archive conversation</span>
                            </button>
                            <button type="button" onclick="blockUser()" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Block user
                            </button>
                            <button type="button" onclick="reportChat()" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-coral flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.25 3h.008v.008h-.008V15z" /></svg>
                                Report / Flag chat
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order context strip (buyer) --}}
            <div id="orderStrip" class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/60 cursor-pointer hover:bg-slate-100/70 transition" onclick="openContextModal()">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p id="orderStripId" class="text-xs font-bold text-navy"></p>
                            <span id="orderStripStatus" class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase"></span>
                        </div>
                        <p id="orderStripProduct" class="text-[11px] text-slate-400 mt-0.5 truncate"></p>
                    </div>
                    <p id="orderStripTotal" class="text-sm font-bold text-navy shrink-0"></p>
                </div>
            </div>

            {{-- Store context strip (seller) --}}
            <div id="storeStrip" class="hidden px-4 py-2.5 border-b border-slate-100 bg-slate-50/60 cursor-pointer hover:bg-slate-100/70 transition" onclick="openContextModal()">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 text-sky">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M4 9v9a2 2 0 002 2h3v-6h6v6h3a2 2 0 002-2V9M4 9h16" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p id="storeStripName" class="text-xs font-bold text-navy truncate"></p>
                            <svg id="storeStripVerified" class="w-3.5 h-3.5 text-mint-dark shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p id="storeStripMeta" class="text-[11px] text-slate-400 mt-0.5 truncate"></p>
                    </div>
                    <p id="storeStripPayout" class="text-[10px] font-bold text-coral shrink-0"></p>
                </div>
            </div>

            <div id="messageThread" class="flex-1 overflow-y-auto chat-scrollbar px-5 py-4 space-y-4 bg-slate-50/50"></div>

            {{-- Composer --}}
            <div class="p-4 border-t border-slate-100">
                <div id="quickReplies" class="flex items-center gap-2 mb-2 overflow-x-auto pb-1 chat-scrollbar">
                    <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Payout is being processed</button>
                    <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Please send order number</button>
                    <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Refund policy link</button>
                    <button type="button" onclick="addQuickReply()" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-dashed border-slate-300 text-slate-400 hover:bg-slate-50">+ Add reply</button>
                </div>

                <div id="attachPreview" class="hidden mb-2 inline-flex items-center gap-2 text-xs bg-slate-100 text-slate-600 px-2.5 py-1.5 rounded-lg">
                    <span id="attachFileName"></span>
                    <button type="button" onclick="removeAttachment()" class="text-slate-400 hover:text-coral font-bold">&times;</button>
                </div>

                <div class="flex items-center gap-2">
                    <input type="file" id="fileInput" class="hidden" onchange="showAttachedFile(this)">
                    <button type="button" onclick="triggerAttach()" title="Attach file / image" class="p-2.5 rounded-xl text-slate-400 hover:bg-slate-100 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.44 11.05l-9.19 9.19a5 5 0 01-7.07-7.07l9.19-9.19a3.5 3.5 0 014.95 4.95l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" />
                        </svg>
                    </button>
                    <input type="text" id="messageInput" onkeydown="if(event.key==='Enter'){sendMessage()}" placeholder="Type a message..."
                        class="flex-1 px-4 py-2.5 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    <button type="button" onclick="sendMessage()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition shrink-0">Send</button>
                </div>
            </div>
        </main>

        {{-- ===== CONVERSATION INFO PANEL ===== --}}
        <aside id="convoInfoPanel" hidden class="hidden lg:flex flex-col w-72 shrink-0 border-l border-slate-100 overflow-y-auto chat-scrollbar">
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-navy">Conversation Info</h3>
                <button type="button" onclick="toggleConvoInfo(false)" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="flex flex-col items-center text-center px-4 py-6 border-b border-slate-100">
                <div class="relative">
                    <div id="infoAvatar" class="w-16 h-16 rounded-full flex items-center justify-center">
                        <span id="infoAvatarInitials" class="text-lg font-bold"></span>
                    </div>
                    <span id="infoOnlineDot" hidden class="absolute right-0 bottom-0 w-3.5 h-3.5 rounded-full bg-mint-dark border-2 border-white"></span>
                </div>
                <p id="infoName" class="mt-3 text-sm font-bold text-navy"></p>
                <span id="infoRoleBadge" class="mt-1.5 inline-block text-[9px] font-bold uppercase tracking-wide rounded px-2 py-0.5"></span>
                <p id="infoPresence" class="text-xs mt-1"></p>
            </div>

            {{-- Related order (buyer) --}}
            <div id="infoOrderSection" class="px-4 py-4 border-b border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Related Order</p>
                <button type="button" onclick="openContextModal()" class="w-full border border-slate-200 rounded-xl p-3 flex gap-3 text-left hover:bg-slate-50 transition">
                    <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p id="infoOrderId" class="text-sm font-semibold text-navy"></p>
                        <p id="infoOrderProduct" class="text-xs text-slate-500 truncate"></p>
                        <p id="infoOrderLocation" class="text-[11px] text-slate-400"></p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span id="infoOrderStatus" class="text-[9px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5"></span>
                            <span id="infoOrderPrice" class="text-xs font-bold text-navy"></span>
                        </div>
                    </div>
                </button>
            </div>

            {{-- Store info (seller) --}}
            <div id="infoStoreSection" class="hidden px-4 py-4 border-b border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Store Info</p>
                <button type="button" onclick="openContextModal()" class="w-full border border-slate-200 rounded-xl p-3 flex gap-3 text-left hover:bg-slate-50 transition">
                    <div class="w-12 h-12 rounded-lg bg-sky/10 flex items-center justify-center shrink-0 text-sky">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M4 9v9a2 2 0 002 2h3v-6h6v6h3a2 2 0 002-2V9M4 9h16" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            <p id="infoStoreName" class="text-sm font-semibold text-navy truncate"></p>
                            <svg id="infoStoreVerified" class="w-3.5 h-3.5 text-mint-dark shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p id="infoStoreLocation" class="text-[11px] text-slate-400"></p>
                        <div class="flex items-center gap-2 mt-1.5 text-[11px] text-slate-500">
                            <span id="infoStoreRating" class="inline-flex items-center gap-0.5">
                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14l-5-4.87 6.91-1.01L12 2z" /></svg>
                            </span>
                            <span id="infoStoreProducts"></span>
                        </div>
                    </div>
                </button>
            </div>

            {{-- Messaging tips --}}
            <div class="px-4 py-4">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Messaging Tips</p>
                <div id="infoTipsList" class="space-y-2"></div>
            </div>
        </aside>
    </div>

    {{-- ============ ORDER DETAILS MODAL ============ --}}
    <div id="chatOrderModal" hidden class="fixed inset-0 bg-navy/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <p class="text-base font-bold text-navy">Related Order</p>
                    <p id="modalOrderId" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeContextModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <p id="modalProductName" class="text-sm font-bold text-navy"></p>
                    <p id="modalVariant" class="text-xs text-slate-500 mt-1"></p>
                    <p id="modalQuantity" class="text-[11px] text-slate-400 mt-0.5"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] text-slate-400">Order Status</p>
                        <span id="modalOrderStatus" class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] text-slate-400">Order Total</p>
                        <p id="modalOrderTotal" class="text-sm font-bold text-navy mt-1"></p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Delivery Area</p>
                    <p id="modalDeliveryArea" class="text-xs font-semibold text-navy mt-1"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ STORE DETAILS MODAL ============ --}}
    <div id="chatStoreModal" hidden class="fixed inset-0 bg-navy/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <p class="text-base font-bold text-navy">Store Details</p>
                    <p id="storeModalId" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeContextModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-2">
                    <p id="storeModalName" class="text-sm font-bold text-navy"></p>
                    <svg id="storeModalVerified" class="w-4 h-4 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p id="storeModalMeta" class="text-xs text-slate-500"></p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] text-slate-400">Rating</p>
                        <p id="storeModalRating" class="text-sm font-bold text-navy mt-1"></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] text-slate-400">Products Listed</p>
                        <p id="storeModalProducts" class="text-sm font-bold text-navy mt-1"></p>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Pending Payout</p>
                    <p id="storeModalPayout" class="text-sm font-bold text-navy mt-1"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ BROADCAST ANNOUNCEMENT MODAL ============ --}}
    <div id="broadcastModal" class="hidden fixed inset-0 bg-navy/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6">
            <h3 class="text-lg font-bold text-navy mb-1">Broadcast Announcement</h3>
            <p class="text-sm text-slate-500 mb-4">Magpadala ng mensahe sa lahat ng buyer, seller, o sa specific na grupo.</p>

            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Send to</label>
                    <select id="broadcastTarget" onchange="onBroadcastTargetChange()" class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        <option value="all">All users</option>
                        <option value="sellers">All sellers</option>
                        <option value="buyers">All buyers</option>
                        <option value="verified_sellers">Verified sellers only</option>
                        <option value="specific">Specific users only</option>
                        <option value="exclude">All users except...</option>
                    </select>
                </div>

                <div id="userPickerWrap" class="hidden">
                    <label id="userPickerLabel" class="text-xs font-semibold text-slate-500">Piliin ang users</label>
                    <div class="relative mt-1">
                        <input type="text" id="userPickerSearch" oninput="renderUserSuggestions()" onfocus="renderUserSuggestions()"
                            placeholder="Mag-search ng user (e.g. TechHub PH, Maria Reyes)..."
                            class="w-full px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        <div id="userSuggestions" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-40 overflow-y-auto z-20 text-sm"></div>
                    </div>
                    <div id="selectedUserChips" class="flex flex-wrap gap-1.5 mt-2"></div>
                    <p id="userPickerHint" class="text-[11px] text-slate-400 mt-1">Wala pang napiling user.</p>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Message</label>
                    <textarea id="broadcastMessage" rows="4" placeholder="Halimbawa: Magkakaroon ng scheduled maintenance sa Sept 5, 12AM-2AM."
                        class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Kailan ipapadala</label>
                    <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm font-medium mt-1">
                        <button type="button" onclick="setSendMode('now')" id="sendModeNowBtn" class="flex-1 py-1.5 rounded-lg bg-white text-navy shadow-sm transition">Send now</button>
                        <button type="button" onclick="setSendMode('schedule')" id="sendModeScheduleBtn" class="flex-1 py-1.5 rounded-lg text-slate-500 hover:text-navy transition">Schedule for later</button>
                    </div>
                    <div id="scheduleFields" class="hidden grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Date</label>
                            <input type="date" id="scheduleDate" class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Time</label>
                            <input type="time" id="scheduleTime" class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="closeBroadcastModal()" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">Cancel</button>
                <button type="button" id="sendBroadcastBtn" onclick="sendBroadcast()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-navy hover:opacity-90">Send Broadcast</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---------------------------------------------------------
       DATA — galing sa @php block sa taas, i-json lang para magamit sa JS.
       Ito na ang single source of truth; papalitan na lang ng backend
       teammate mo ng real Eloquent data (props/controller) balang araw.
    --------------------------------------------------------- */
    const conversations = @json($conversations->values());
    const orderStatusClasses = @json($orderStatusClasses);
    const BROADCAST_USERS = @json($broadcastUsers);

    const rows = Array.from(document.querySelectorAll('[data-conversation-trigger]'));
    const listPanel = document.getElementById('chatListPanel');
    const threadPanel = document.getElementById('chatThreadPanel');
    const infoPanel = document.getElementById('convoInfoPanel');
    const messageThread = document.getElementById('messageThread');
    const searchInput = document.getElementById('chatSearchInput');
    const noResults = document.getElementById('chatNoResults');

    const MESSAGING_TIPS = {
        buyer: [
            'Keep order updates clear and avoid promising exact courier delivery times unless confirmed.',
            'For damaged or missing item concerns, acknowledge the issue and keep the order reference visible.',
        ],
        seller: [
            'Confirm payout requests against the finance dashboard before giving a release date.',
            'Remind sellers to keep compliance documents updated to avoid delisting.',
        ],
    };

    let activeId = conversations.find(c => !c.archived)?.id ?? conversations[0]?.id ?? null;
    let activeFilter = 'all';
    let activeView = 'active';
    let infoOpen = false;

    /* ---------- Small helpers ---------- */
    function money(value) {
        return '₱' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getConversation(id) {
        return conversations.find(c => Number(c.id) === Number(id));
    }

    function getRow(id) {
        return rows.find(r => Number(r.dataset.conversationId) === Number(id));
    }

    function toast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            console.warn('showToast() not found — falling back to console.', message);
        }
    }

    function setStatusBadge(el, status) {
        if (!el) return;
        const normalized = String(status || '').toUpperCase();
        el.className = 'px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ' + (orderStatusClasses[normalized] || 'bg-navy/10 text-navy/50');
        el.textContent = normalized.replaceAll('_', ' ');
    }

    /* ---------- Header stat pills ---------- */
    function updateStatCounts() {
        let unread = 0, online = 0;
        conversations.forEach(c => {
            if (c.unread > 0) unread++;
            if (c.online) online++;
        });
        document.getElementById('statUnreadCount').textContent = unread;
        document.getElementById('statOnlineCount').textContent = online;
    }

    function refreshViewCounts() {
        const activeCount = conversations.filter(c => !c.archived).length;
        const archivedCount = conversations.filter(c => c.archived).length;
        document.getElementById('activeCountBadge').textContent = activeCount;
        document.getElementById('archivedCountBadge').textContent = archivedCount;
    }

    /* ---------- Mark as read / unread ---------- */
    function setUnreadDot(row, unreadCount) {
        const nameEl = row.querySelector('.convo-name');
        const previewEl = row.querySelector('.convo-preview');
        let badge = row.querySelector('[data-row-unread]');

        if (unreadCount > 0) {
            nameEl.classList.add('text-mint-dark');
            nameEl.classList.remove('text-navy');
            previewEl.classList.add('font-semibold', 'text-navy');
            previewEl.classList.remove('text-slate-500');
            if (!badge) {
                badge = document.createElement('span');
                badge.setAttribute('data-row-unread', '');
                badge.className = 'text-[9px] font-bold bg-coral text-white min-w-4 h-4 px-1 rounded-full flex items-center justify-center shrink-0';
                previewEl.parentElement.appendChild(badge);
            }
            badge.textContent = unreadCount;
        } else {
            nameEl.classList.remove('text-mint-dark');
            nameEl.classList.add('text-navy');
            previewEl.classList.remove('font-semibold', 'text-navy');
            previewEl.classList.add('text-slate-500');
            badge?.remove();
        }
    }

    function markConversationRead(id) {
        const convo = getConversation(id);
        const row = getRow(id);
        if (!convo || !row) return;
        convo.unread = 0;
        row.dataset.unread = '0';
        setUnreadDot(row, 0);
        updateStatCounts();
    }

    function toggleRowUnread(el, event) {
        event.stopPropagation();
        const row = el.closest('[data-conversation-trigger]');
        const id = Number(row.dataset.conversationId);
        const convo = getConversation(id);
        if (!convo) return;

        const isUnread = row.dataset.unread === '1';
        if (isUnread) {
            markConversationRead(id);
            toast('Na-mark as read.', 'info');
        } else {
            convo.unread = Math.max(convo.unread || 0, 1);
            row.dataset.unread = '1';
            setUnreadDot(row, convo.unread);
            updateStatCounts();
            toast('Na-mark as unread.', 'info');
        }
        applyConversationFilters();
    }

    /* ---------- Pin ---------- */
    function setPinIndicator(row, pinned) {
        row.querySelector('[data-row-pin-icon]')?.classList.toggle('hidden', !pinned);
    }

    function togglePin(id) {
        const convo = getConversation(id);
        const row = getRow(id);
        if (!convo || !row) return;

        convo.pinned = !convo.pinned;
        row.dataset.pinned = convo.pinned ? '1' : '0';
        setPinIndicator(row, convo.pinned);

        if (convo.pinned) {
            document.getElementById('conversationList').prepend(row);
        }

        applyConversationFilters();
        toast(convo.pinned ? ('Na-pin ang chat kay ' + convo.name + '.') : ('Na-unpin ang chat kay ' + convo.name + '.'), 'success');
    }

    function toggleRowPin(el, event) {
        event.stopPropagation();
        const row = el.closest('[data-conversation-trigger]');
        togglePin(Number(row.dataset.conversationId));
    }

    function togglePinCurrent() {
        if (activeId === null) return;
        togglePin(activeId);
        updateThreadMenuLabels();
        document.getElementById('threadMenu').classList.add('hidden');
    }

    /* ---------- Archive / restore ---------- */
    function setConversationArchived(id, archived) {
        const convo = getConversation(id);
        const row = getRow(id);
        if (!convo || !row) return;
        convo.archived = archived;
        row.dataset.archived = archived ? '1' : '0';
        refreshViewCounts();
        applyConversationFilters();
    }

    function toggleRowArchive(el, event) {
        event.stopPropagation();
        const row = el.closest('[data-conversation-trigger]');
        const id = Number(row.dataset.conversationId);
        const convo = getConversation(id);
        if (!convo) return;
        setConversationArchived(id, !convo.archived);
        toast(convo.archived ? ('Na-archive ang chat kay ' + convo.name + '.') : ('Na-restore ang chat kay ' + convo.name + '.'), 'success');
    }

    function archiveCurrentConversation() {
        if (activeId === null) return;
        const convo = getConversation(activeId);
        setConversationArchived(activeId, !convo.archived);
        updateThreadMenuLabels();
        toast(convo.archived ? 'Na-archive ang kasalukuyang conversation.' : 'Na-restore ang conversation.', 'success');
        document.getElementById('threadMenu').classList.add('hidden');
    }

    function updateThreadMenuLabels() {
        const convo = getConversation(activeId);
        if (!convo) return;
        document.getElementById('threadPinLabel').textContent = convo.pinned ? 'Unpin conversation' : 'Pin conversation';
        document.getElementById('threadArchiveLabel').textContent = convo.archived ? 'Unarchive conversation' : 'Archive conversation';
    }

    /* ---------- Rendering a message bubble ---------- */
    function renderMessage(message) {
        const isAdmin = message.from === 'admin';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex ' + (isAdmin ? 'justify-end' : 'justify-start');
        wrapper.innerHTML = `
            <div class="max-w-md ${isAdmin ? 'bg-mint-dark' : 'bg-white border border-slate-200'} rounded-2xl ${isAdmin ? 'rounded-tr-sm' : 'rounded-tl-sm'} px-4 py-2.5">
                <p class="text-sm ${isAdmin ? 'text-white' : 'text-navy'}"></p>
                <p class="text-[10px] ${isAdmin ? 'text-mint/70' : 'text-slate-400'} mt-1"></p>
            </div>`;
        wrapper.querySelector('p:first-of-type').textContent = message.text || '';
        wrapper.querySelector('p:last-of-type').textContent = message.time + (isAdmin && message.status === 'read' ? ' · Seen' : '');
        messageThread.appendChild(wrapper);
    }

    /* ---------- Open a conversation ---------- */
    function updateContextButton(convo) {
        const isSeller = convo.role === 'seller';
        document.getElementById('contextBtnIconOrder').classList.toggle('hidden', isSeller);
        document.getElementById('contextBtnIconStore').classList.toggle('hidden', !isSeller);
        document.getElementById('contextBtn').title = isSeller ? 'View store' : 'View related order';
        document.getElementById('orderStrip').classList.toggle('hidden', isSeller);
        document.getElementById('storeStrip').classList.toggle('hidden', !isSeller);
    }

    function updateContextStrip(convo) {
        if (convo.role === 'buyer' && convo.order) {
            const order = convo.order;
            document.getElementById('orderStripId').textContent = 'Order #' + order.id;
            setStatusBadge(document.getElementById('orderStripStatus'), order.status);
            document.getElementById('orderStripProduct').textContent = [order.product, order.variant, order.qty ? ('Qty ' + order.qty) : null].filter(Boolean).join(' · ');
            document.getElementById('orderStripTotal').textContent = money(order.total);
        } else if (convo.role === 'seller' && convo.store) {
            const store = convo.store;
            document.getElementById('storeStripName').textContent = store.category;
            document.getElementById('storeStripVerified').classList.toggle('hidden', !store.verified);
            document.getElementById('storeStripMeta').textContent = store.location + ' · ' + store.products_count + ' products · ⭐ ' + store.rating;
            document.getElementById('storeStripPayout').textContent = store.pending_payout > 0 ? (money(store.pending_payout) + ' pending') : '';
        }
    }

    function updateInfoPanel(convo) {
        document.getElementById('infoAvatar').className = 'w-16 h-16 rounded-full flex items-center justify-center ' + (convo.role === 'seller' ? 'bg-sky/15 text-sky' : 'bg-coral/15 text-coral');
        document.getElementById('infoAvatarInitials').textContent = convo.initials;
        document.getElementById('infoName').textContent = convo.name;

        const roleBadge = document.getElementById('infoRoleBadge');
        roleBadge.textContent = convo.role;
        roleBadge.className = 'mt-1.5 inline-block text-[9px] font-bold uppercase tracking-wide rounded px-2 py-0.5 ' + (convo.role === 'seller' ? 'text-sky bg-sky/10' : 'text-coral bg-coral/10');

        document.getElementById('infoOnlineDot').hidden = !convo.online;
        const presence = document.getElementById('infoPresence');
        presence.textContent = convo.online ? 'Online now' : convo.last_seen;
        presence.className = 'text-xs mt-1 ' + (convo.online ? 'text-mint-dark' : 'text-slate-400');

        const orderSection = document.getElementById('infoOrderSection');
        const storeSection = document.getElementById('infoStoreSection');

        if (convo.role === 'buyer' && convo.order) {
            orderSection.classList.remove('hidden');
            storeSection.classList.add('hidden');
            const order = convo.order;
            document.getElementById('infoOrderId').textContent = order.id;
            document.getElementById('infoOrderProduct').textContent = order.product;
            document.getElementById('infoOrderLocation').textContent = order.delivery_area;
            setStatusBadge(document.getElementById('infoOrderStatus'), order.status);
            document.getElementById('infoOrderPrice').textContent = money(order.total);
        } else if (convo.role === 'seller' && convo.store) {
            storeSection.classList.remove('hidden');
            orderSection.classList.add('hidden');
            const store = convo.store;
            document.getElementById('infoStoreName').textContent = convo.name;
            document.getElementById('infoStoreVerified').classList.toggle('hidden', !store.verified);
            document.getElementById('infoStoreLocation').textContent = store.location;
            const ratingEl = document.getElementById('infoStoreRating');
            ratingEl.innerHTML = ratingEl.innerHTML.split('</svg>')[0] + '</svg> ' + store.rating;
            document.getElementById('infoStoreProducts').textContent = store.products_count + ' products';
        } else {
            orderSection.classList.add('hidden');
            storeSection.classList.add('hidden');
        }

        const tips = MESSAGING_TIPS[convo.role] || MESSAGING_TIPS.buyer;
        document.getElementById('infoTipsList').innerHTML = tips.map(t => `<div class="text-xs bg-slate-50 border border-slate-100 rounded-lg p-2.5 text-slate-600">${t}</div>`).join('');
    }

    function updateMuteButton(convo) {
        const isMuted = !!convo.muted;
        document.getElementById('muteIconOn').classList.toggle('hidden', isMuted);
        document.getElementById('muteIconOff').classList.toggle('hidden', !isMuted);
        document.getElementById('muteBtn').classList.toggle('text-coral', isMuted);
        document.getElementById('muteBtn').classList.toggle('text-slate-500', !isMuted);
    }

    function openConversation(id) {
        const convo = getConversation(id);
        if (!convo) return;

        activeId = Number(id);
        markConversationRead(activeId);

        rows.forEach(row => row.classList.toggle('conversation-active', Number(row.dataset.conversationId) === activeId));

        document.getElementById('threadAvatar').className = 'w-9 h-9 rounded-full flex items-center justify-center ' + (convo.role === 'seller' ? 'bg-sky/15 text-sky' : 'bg-coral/15 text-coral');
        document.getElementById('threadAvatarInitials').textContent = convo.initials;
        document.getElementById('threadName').textContent = convo.name;

        const threadRoleBadge = document.getElementById('threadRoleBadge');
        threadRoleBadge.textContent = convo.role;
        threadRoleBadge.className = 'shrink-0 text-[9px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5 ' + (convo.role === 'seller' ? 'text-sky bg-sky/10' : 'text-coral bg-coral/10');

        document.getElementById('threadOnlineDot').hidden = !convo.online;
        const presence = document.getElementById('threadPresence');
        presence.textContent = convo.online ? 'Online now' : convo.last_seen;
        presence.className = 'text-xs mt-0.5 ' + (convo.online ? 'text-mint-dark' : 'text-slate-400');

        messageThread.innerHTML = '';
        (convo.messages || []).forEach(renderMessage);
        messageThread.scrollTop = messageThread.scrollHeight;

        updateContextButton(convo);
        updateContextStrip(convo);
        updateMuteButton(convo);
        updateInfoPanel(convo);
        updateThreadMenuLabels();

        if (window.innerWidth < 640) {
            listPanel.classList.add('mobile-hidden');
            threadPanel.classList.remove('hidden');
            threadPanel.classList.add('flex');
        }
    }

    rows.forEach(row => {
        row.addEventListener('click', () => openConversation(Number(row.dataset.conversationId)));
        row.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openConversation(Number(row.dataset.conversationId));
            }
        });
    });

    /* ---------- Filters / search / views ---------- */
    function rowMatchesView(row) {
        return activeView === 'archived' ? row.dataset.archived === '1' : row.dataset.archived !== '1';
    }

    function rowMatchesFilter(row) {
        if (activeFilter === 'all') return true;
        if (activeFilter === 'unread') return row.dataset.unread === '1';
        if (activeFilter === 'pinned') return row.dataset.pinned === '1';
        if (activeFilter === 'seller' || activeFilter === 'buyer') return row.dataset.role === activeFilter;
        return true;
    }

    function applyConversationFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();
        let shown = 0;

        rows.forEach(row => {
            const show = rowMatchesView(row) && rowMatchesFilter(row) && (!query || (row.dataset.search || '').includes(query));
            row.style.display = show ? '' : 'none';
            if (show) shown++;
        });

        noResults.hidden = shown !== 0;
    }

    document.querySelectorAll('[data-chat-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            activeFilter = btn.dataset.chatFilter;
            document.querySelectorAll('[data-chat-filter]').forEach(b => {
                b.classList.remove('chat-filter-active', 'bg-slate-100', 'text-slate-500');
                b.classList.add('bg-slate-100', 'text-slate-500');
            });
            btn.classList.remove('bg-slate-100', 'text-slate-500');
            btn.classList.add('chat-filter-active');
            applyConversationFilters();
        });
    });

    document.querySelectorAll('[data-chat-view]').forEach(btn => {
        btn.addEventListener('click', () => {
            activeView = btn.dataset.chatView;
            const activeTabBtn = document.getElementById('tabActiveBtn');
            const archivedTabBtn = document.getElementById('tabArchivedBtn');
            const isActive = activeView === 'active';
            activeTabBtn.classList.toggle('bg-white', isActive);
            activeTabBtn.classList.toggle('text-navy', isActive);
            activeTabBtn.classList.toggle('shadow-sm', isActive);
            activeTabBtn.classList.toggle('text-slate-500', !isActive);
            archivedTabBtn.classList.toggle('bg-white', !isActive);
            archivedTabBtn.classList.toggle('text-navy', !isActive);
            archivedTabBtn.classList.toggle('shadow-sm', !isActive);
            archivedTabBtn.classList.toggle('text-slate-500', isActive);
            applyConversationFilters();
        });
    });

    searchInput.addEventListener('input', applyConversationFilters);

    /* ---------- Conversation info panel ---------- */
    window.toggleConvoInfo = function (force) {
        infoOpen = typeof force === 'boolean' ? force : !infoOpen;
        infoPanel.hidden = !infoOpen;
        infoPanel.classList.toggle('lg:flex', infoOpen);
    };

    /* ---------- Thread menu ---------- */
    window.toggleThreadMenu = function (event) {
        event.stopPropagation();
        updateThreadMenuLabels();
        document.getElementById('threadMenu').classList.toggle('hidden');
    };

    document.addEventListener('click', function (e) {
        const menu = document.getElementById('threadMenu');
        if (!menu.classList.contains('hidden') && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    window.archiveCurrentConversation = archiveCurrentConversation;
    window.togglePinCurrent = togglePinCurrent;
    window.toggleRowUnread = toggleRowUnread;
    window.toggleRowArchive = toggleRowArchive;
    window.toggleRowPin = toggleRowPin;

    window.blockUser = function () {
        const convo = getConversation(activeId);
        const name = convo?.name || 'this user';
        document.getElementById('threadMenu').classList.add('hidden');
        if (confirm('Sigurado ka bang gusto mong i-block si ' + name + '? Hindi na sila makakapag-send ng message sa iyo.')) {
            toast(name + ' ay na-block na.', 'error');
        }
    };

    window.reportChat = function () {
        const convo = getConversation(activeId);
        const name = convo?.name || 'this conversation';
        document.getElementById('threadMenu').classList.add('hidden');
        if (confirm('I-report ang conversation na ito kay ' + name + ' para sa review ng admin team?')) {
            toast('Na-submit na ang report. Susuriin ito ng compliance team.', 'warning');
        }
    };

    window.toggleMute = function () {
        const convo = getConversation(activeId);
        if (!convo) return;
        convo.muted = !convo.muted;
        updateMuteButton(convo);
        toast(convo.muted ? 'Naka-mute na ang notifications para dito.' : 'Naka-unmute na ang notifications.', 'info');
    };

    /* ---------- Order / Store context modal ---------- */
    window.openContextModal = function () {
        const convo = getConversation(activeId);
        if (!convo) return;

        if (convo.role === 'buyer' && convo.order) {
            const order = convo.order;
            document.getElementById('modalOrderId').textContent = order.id;
            document.getElementById('modalProductName').textContent = order.product;
            document.getElementById('modalVariant').textContent = order.variant || 'Default variant';
            document.getElementById('modalQuantity').textContent = 'Quantity: ' + (order.qty || 0);
            document.getElementById('modalOrderTotal').textContent = money(order.total);
            document.getElementById('modalDeliveryArea').textContent = order.delivery_area;
            setStatusBadge(document.getElementById('modalOrderStatus'), order.status);
            document.getElementById('chatOrderModal').hidden = false;
        } else if (convo.role === 'seller' && convo.store) {
            const store = convo.store;
            document.getElementById('storeModalId').textContent = 'Store ID ' + store.id;
            document.getElementById('storeModalName').textContent = convo.name;
            document.getElementById('storeModalVerified').classList.toggle('hidden', !store.verified);
            document.getElementById('storeModalMeta').textContent = store.category + ' · ' + store.location + ' · ' + store.joined;
            document.getElementById('storeModalRating').textContent = '⭐ ' + store.rating;
            document.getElementById('storeModalProducts').textContent = store.products_count;
            document.getElementById('storeModalPayout').textContent = store.pending_payout > 0 ? money(store.pending_payout) : 'No pending payout';
            document.getElementById('chatStoreModal').hidden = false;
        }
        document.body.style.overflow = 'hidden';
    };

    window.closeContextModal = function () {
        document.getElementById('chatOrderModal').hidden = true;
        document.getElementById('chatStoreModal').hidden = true;
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            window.closeContextModal();
        }
    });

    /* ---------- Quick replies ---------- */
    window.useQuickReply = function (el) {
        const input = document.getElementById('messageInput');
        input.value = el.textContent.trim();
        input.focus();
    };

    window.addQuickReply = function () {
        const text = prompt('I-type ang bagong quick reply:');
        if (text && text.trim()) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.setAttribute('onclick', 'useQuickReply(this)');
            btn.className = 'shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50';
            btn.textContent = text.trim();
            document.getElementById('quickReplies').insertBefore(btn, document.getElementById('quickReplies').lastElementChild);
            toast('Naidagdag ang quick reply.', 'success');
        }
    };

    /* ---------- Attach file ---------- */
    window.triggerAttach = function () {
        document.getElementById('fileInput').click();
    };

    window.showAttachedFile = function (input) {
        if (input.files && input.files.length > 0) {
            document.getElementById('attachFileName').textContent = input.files[0].name;
            document.getElementById('attachPreview').classList.remove('hidden');
        }
    };

    window.removeAttachment = function () {
        document.getElementById('fileInput').value = '';
        document.getElementById('attachPreview').classList.add('hidden');
    };

    /* ---------- Send message ---------- */
    window.sendMessage = function () {
        const input = document.getElementById('messageInput');
        const attachPreview = document.getElementById('attachPreview');
        const hasAttachment = !attachPreview.classList.contains('hidden');
        const text = input.value.trim();
        if (!text && !hasAttachment) return;
        if (activeId === null) return;

        const convo = getConversation(activeId);
        if (!convo) return;

        const now = new Date();
        const time = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
        const finalText = hasAttachment ? (text ? text + ' 📎 ' + document.getElementById('attachFileName').textContent : '📎 ' + document.getElementById('attachFileName').textContent) : text;

        convo.messages.push({ from: 'admin', text: finalText, time: time, status: 'sent' });
        convo.last_message = finalText;
        convo.time = time;

        const row = getRow(activeId);
        if (row) {
            row.querySelector('[data-row-last-message]').textContent = finalText;
        }

        input.value = '';
        window.removeAttachment();
        openConversation(activeId);
    };

    /* ---------- Mobile back ---------- */
    document.getElementById('mobileBackToChats')?.addEventListener('click', function () {
        threadPanel.classList.add('hidden');
        threadPanel.classList.remove('flex');
        listPanel.classList.remove('mobile-hidden');
    });

    /* =============================================================
       BROADCAST ANNOUNCEMENT MODAL
    ============================================================= */
    let selectedBroadcastUsers = [];
    let broadcastSendMode = 'now';

    window.openBroadcastModal = function () {
        document.getElementById('broadcastModal').classList.remove('hidden');
    };

    window.closeBroadcastModal = function () {
        document.getElementById('broadcastModal').classList.add('hidden');
        document.getElementById('userSuggestions').classList.add('hidden');
    };

    window.setSendMode = function (mode) {
        broadcastSendMode = mode;
        const nowBtn = document.getElementById('sendModeNowBtn');
        const scheduleBtn = document.getElementById('sendModeScheduleBtn');
        const fields = document.getElementById('scheduleFields');
        const sendBtn = document.getElementById('sendBroadcastBtn');

        if (mode === 'now') {
            nowBtn.classList.add('bg-white', 'text-navy', 'shadow-sm');
            nowBtn.classList.remove('text-slate-500');
            scheduleBtn.classList.remove('bg-white', 'text-navy', 'shadow-sm');
            scheduleBtn.classList.add('text-slate-500');
            fields.classList.add('hidden');
            sendBtn.textContent = 'Send Broadcast';
        } else {
            scheduleBtn.classList.add('bg-white', 'text-navy', 'shadow-sm');
            scheduleBtn.classList.remove('text-slate-500');
            nowBtn.classList.remove('bg-white', 'text-navy', 'shadow-sm');
            nowBtn.classList.add('text-slate-500');
            fields.classList.remove('hidden');
            sendBtn.textContent = 'Schedule Broadcast';
            if (!document.getElementById('scheduleDate').value) {
                document.getElementById('scheduleDate').value = new Date().toISOString().split('T')[0];
            }
        }
    };

    window.onBroadcastTargetChange = function () {
        const target = document.getElementById('broadcastTarget').value;
        const wrap = document.getElementById('userPickerWrap');
        const label = document.getElementById('userPickerLabel');

        if (target === 'specific' || target === 'exclude') {
            wrap.classList.remove('hidden');
            label.textContent = target === 'specific' ? 'Piliin lang ang mga user na padadalhan' : 'Piliin ang mga user na i-e-exclude';
        } else {
            wrap.classList.add('hidden');
            selectedBroadcastUsers = [];
            document.getElementById('userPickerSearch').value = '';
            renderSelectedUserChips();
        }
    };

    function renderUserSuggestions() {
        const query = document.getElementById('userPickerSearch').value.toLowerCase();
        const box = document.getElementById('userSuggestions');
        const selectedIds = selectedBroadcastUsers.map(u => u.id);
        const matches = BROADCAST_USERS.filter(u => !selectedIds.includes(u.id) && u.name.toLowerCase().includes(query));

        if (matches.length === 0) {
            box.innerHTML = '<p class="px-3 py-2 text-slate-400 text-xs">Walang nahanap na user.</p>';
        } else {
            box.innerHTML = matches.map(u => `
                <button type="button" onclick="selectBroadcastUser(${u.id})" class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center justify-between">
                    <span class="text-navy">${u.name}</span>
                    <span class="text-[10px] uppercase tracking-wide text-slate-400">${u.role}</span>
                </button>`).join('');
        }
        box.classList.remove('hidden');
    }
    window.renderUserSuggestions = renderUserSuggestions;

    window.selectBroadcastUser = function (id) {
        const user = BROADCAST_USERS.find(u => u.id === id);
        if (user && !selectedBroadcastUsers.some(u => u.id === id)) {
            selectedBroadcastUsers.push(user);
            renderSelectedUserChips();
        }
        document.getElementById('userPickerSearch').value = '';
        document.getElementById('userSuggestions').classList.add('hidden');
    };

    window.removeBroadcastUser = function (id) {
        selectedBroadcastUsers = selectedBroadcastUsers.filter(u => u.id !== id);
        renderSelectedUserChips();
    };

    function renderSelectedUserChips() {
        const container = document.getElementById('selectedUserChips');
        const hint = document.getElementById('userPickerHint');
        if (selectedBroadcastUsers.length === 0) {
            container.innerHTML = '';
            hint.classList.remove('hidden');
            return;
        }
        hint.classList.add('hidden');
        container.innerHTML = selectedBroadcastUsers.map(u => `
            <span class="inline-flex items-center gap-1.5 text-xs bg-mint/10 text-mint-dark px-2.5 py-1 rounded-full">
                ${u.name}
                <button type="button" onclick="removeBroadcastUser(${u.id})" class="text-mint-dark/60 hover:text-coral font-bold leading-none">&times;</button>
            </span>`).join('');
    }

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('userPickerWrap');
        const box = document.getElementById('userSuggestions');
        if (wrap && !wrap.contains(e.target)) {
            box.classList.add('hidden');
        }
    });

    window.sendBroadcast = function () {
        const target = document.getElementById('broadcastTarget').value;
        const targetLabel = document.getElementById('broadcastTarget').selectedOptions[0].textContent;
        const message = document.getElementById('broadcastMessage').value.trim();

        if (!message) {
            toast('I-type muna ang broadcast message.', 'warning');
            return;
        }
        if ((target === 'specific' || target === 'exclude') && selectedBroadcastUsers.length === 0) {
            toast('Pumili ng kahit isang user muna.', 'warning');
            return;
        }

        let summary = targetLabel;
        if (target === 'specific') {
            summary = 'Specific users: ' + selectedBroadcastUsers.map(u => u.name).join(', ');
        } else if (target === 'exclude') {
            summary = 'All users except: ' + selectedBroadcastUsers.map(u => u.name).join(', ');
        }

        if (broadcastSendMode === 'schedule') {
            const dateVal = document.getElementById('scheduleDate').value;
            const timeVal = document.getElementById('scheduleTime').value;
            if (!dateVal || !timeVal) {
                toast('Pumili ng date at time para sa schedule.', 'warning');
                return;
            }
            const scheduledAt = new Date(dateVal + 'T' + timeVal);
            if (scheduledAt <= new Date()) {
                toast('Pumili ng petsa/oras na nasa hinaharap pa.', 'warning');
                return;
            }
            const formatted = scheduledAt.toLocaleString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            toast('Naka-schedule na ang broadcast sa ' + formatted + ' — ' + summary, 'success');
            // TODO: i-post ito sa controller mo (target mode, selected user IDs, message, scheduled_at) para i-queue.
        } else {
            toast('Na-send ang broadcast — ' + summary, 'success');
            // TODO: i-post ito agad sa controller mo para i-dispatch ang notifications.
        }

        document.getElementById('broadcastMessage').value = '';
        document.getElementById('scheduleDate').value = '';
        document.getElementById('scheduleTime').value = '';
        selectedBroadcastUsers = [];
        renderSelectedUserChips();
        window.setSendMode('now');
        window.closeBroadcastModal();
    };

    /* ---------- Initial state ---------- */
    updateStatCounts();
    refreshViewCounts();
    applyConversationFilters();
    if (activeId !== null && window.innerWidth >= 640) {
        openConversation(activeId);
    }
});
</script>
@endpush

@endsection