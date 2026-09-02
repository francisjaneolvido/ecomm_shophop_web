@extends('admin.layout')

@section('title', 'Chat / Messaging')

@section('content')

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-navy">Chat / Messaging</h1>
            <p class="text-sm text-slate-500 mt-1">Makipag-usap sa mga buyer at seller, o mag-broadcast ng announcement.</p>
        </div>

        {{-- Broadcast announcement trigger --}}
        <button type="button" onclick="openBroadcastModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-navy hover:opacity-90 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            Broadcast Announcement
        </button>
    </div>

    {{-- ============ CHAT PANEL ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden" style="height: calc(100vh - 220px);">
        <div class="flex h-full">

            {{-- ===== CONVERSATION LIST ===== --}}
            <div class="w-full sm:w-80 border-r border-slate-100 flex flex-col shrink-0">

                <div class="p-4 border-b border-slate-100 space-y-3">
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="searchInput" oninput="filterConversations()" placeholder="Search conversations..."
                            class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    </div>

                    {{-- Active / Archived tabs --}}
                    <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm font-medium">
                        <button type="button" onclick="showTab('active')" id="tabActiveBtn"
                            class="flex-1 py-1.5 rounded-lg bg-white text-navy shadow-sm transition">
                            Active
                            <span id="activeCountBadge" class="ml-1 text-[10px] font-bold text-white bg-coral rounded-full px-1.5 py-0.5">3</span>
                        </button>
                        <button type="button" onclick="showTab('archived')" id="tabArchivedBtn"
                            class="flex-1 py-1.5 rounded-lg text-slate-500 hover:text-navy transition">
                            Archived
                        </button>
                    </div>

                    {{-- Buyer / Seller filter --}}
                    <div id="filterChips" class="flex items-center gap-1 text-xs">
                        <button type="button" data-filter="all" onclick="filterByType('all', this)" class="filter-chip px-2.5 py-1 rounded-full bg-navy text-white">All</button>
                        <button type="button" data-filter="seller" onclick="filterByType('seller', this)" class="filter-chip px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Sellers</button>
                        <button type="button" data-filter="buyer" onclick="filterByType('buyer', this)" class="filter-chip px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Buyers</button>
                        <button type="button" data-filter="unread" onclick="filterByType('unread', this)" class="filter-chip px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">Unread</button>
                    </div>
                </div>

                {{-- ===== ACTIVE CONVERSATIONS ===== --}}
                {{-- Palitan mo na lang ito ng @foreach loop galing sa $conversations mo sa controller --}}
                <div id="activeList" class="flex-1 overflow-y-auto divide-y divide-slate-100">

                    {{-- ACTIVE CONVO --}}
                    <div class="convo-item group relative flex items-start gap-3 px-4 py-3 bg-mint/5 border-l-2 border-mint-dark cursor-pointer"
                        data-type="seller" data-unread="true" data-name="TechHub PH" data-initials="TH" data-avatar-class="bg-sky/15 text-sky"
                        data-status="Online" data-order="ORD-10432" onclick="openConversation(this)">
                        <div class="w-10 h-10 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-sky">TH</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="convo-name text-sm font-semibold text-navy truncate">TechHub PH</p>
                                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide text-sky bg-sky/10 rounded px-1.5 py-0.5">Seller</span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">2m</span>
                            </div>
                            <p class="convo-preview text-xs text-slate-500 truncate mt-0.5">Sir, kailan po ma-release yung payout namin?</p>
                            <p class="text-[10px] text-slate-400 mt-1">Order #ORD-10432</p>
                        </div>
                        {{-- Hover actions --}}
                        <div class="hidden group-hover:flex items-center gap-1 absolute top-2.5 right-2.5 bg-white rounded-lg shadow border border-slate-100 p-0.5">
                            <button type="button" title="Mark as unread" onclick="toggleUnread(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6" /></svg>
                            </button>
                            <button type="button" title="Archive" onclick="archiveConversation(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="convo-item group relative flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer"
                        data-type="seller" data-unread="false" data-name="Aling Nena's Store" data-initials="AN" data-avatar-class="bg-mint/15 text-mint-dark"
                        data-status="Offline" data-order="" onclick="openConversation(this)">
                        <div class="w-10 h-10 rounded-full bg-mint/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-mint-dark">AN</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="convo-name text-sm font-semibold text-navy truncate">Aling Nena's Store</p>
                                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide text-sky bg-sky/10 rounded px-1.5 py-0.5">Seller</span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">1h</span>
                            </div>
                            <p class="convo-preview text-xs text-slate-500 truncate mt-0.5">Salamat po sa pag-verify sa account namin!</p>
                        </div>
                        <div class="hidden group-hover:flex items-center gap-1 absolute top-2.5 right-2.5 bg-white rounded-lg shadow border border-slate-100 p-0.5">
                            <button type="button" title="Mark as unread" onclick="toggleUnread(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6" /></svg>
                            </button>
                            <button type="button" title="Archive" onclick="archiveConversation(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="convo-item group relative flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer"
                        data-type="buyer" data-unread="false" data-name="Maria Reyes" data-initials="MR" data-avatar-class="bg-slate-200 text-slate-500"
                        data-status="Offline" data-order="ORD-10298" onclick="openConversation(this)">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-slate-500">MR</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="convo-name text-sm font-semibold text-navy truncate">Maria Reyes</p>
                                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide text-coral bg-coral/10 rounded px-1.5 py-0.5">Buyer</span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">3h</span>
                            </div>
                            <p class="convo-preview text-xs text-slate-500 truncate mt-0.5">Paano po mag-file ng refund request?</p>
                            <p class="text-[10px] text-slate-400 mt-1">Order #ORD-10298</p>
                        </div>
                        <div class="hidden group-hover:flex items-center gap-1 absolute top-2.5 right-2.5 bg-white rounded-lg shadow border border-slate-100 p-0.5">
                            <button type="button" title="Mark as unread" onclick="toggleUnread(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6" /></svg>
                            </button>
                            <button type="button" title="Archive" onclick="archiveConversation(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ===== ARCHIVED CONVERSATIONS (hidden by default) ===== --}}
                {{-- Palitan mo rin ito ng @foreach loop galing sa $archivedConversations mo sa controller --}}
                <div id="archivedList" class="hidden flex-1 overflow-y-auto divide-y divide-slate-100">

                    <div class="convo-item group relative flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer opacity-75"
                        data-type="seller" data-unread="false" data-name="Jomar's Repair Shop" data-initials="JR" data-avatar-class="bg-coral/15 text-coral"
                        data-status="Offline" data-order="" onclick="openConversation(this)">
                        <div class="w-10 h-10 rounded-full bg-coral/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-coral">JR</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <p class="convo-name text-sm font-semibold text-navy truncate">Jomar's Repair Shop</p>
                                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide text-sky bg-sky/10 rounded px-1.5 py-0.5">Seller</span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">1d</span>
                            </div>
                            <p class="convo-preview text-xs text-slate-500 truncate mt-0.5">Follow up po sa compliance documents.</p>
                            <p class="text-[10px] text-slate-400 mt-1">Archived Aug 30</p>
                        </div>
                        <div class="hidden group-hover:flex items-center gap-1 absolute top-2.5 right-2.5 bg-white rounded-lg shadow border border-slate-100 p-0.5">
                            <button type="button" title="Unarchive / Restore" onclick="restoreConversation(this, event)" class="p-1.5 rounded-md hover:bg-slate-100 text-mint-dark">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M4 9a8 8 0 1113.6 5.7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="archivedEmptyState" class="hidden flex-col items-center justify-center text-center py-10 px-6 text-slate-400">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" />
                        </svg>
                        <p class="text-xs">Wala ng ibang naka-archive na chat.</p>
                    </div>

                </div>
            </div>

            {{-- ===== MESSAGE THREAD ===== --}}
            <div class="hidden sm:flex flex-1 flex-col">

                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div id="threadAvatar" class="w-9 h-9 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                            <span id="threadAvatarInitials" class="text-xs font-bold text-sky">TH</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <p id="threadName" class="text-sm font-semibold text-navy">TechHub PH</p>
                                <span class="text-[9px] font-bold uppercase tracking-wide text-sky bg-sky/10 rounded px-1.5 py-0.5">Seller</span>
                            </div>
                            <p id="threadStatus" class="text-xs text-mint-dark">Online</p>
                        </div>
                    </div>

                    {{-- Thread-level actions --}}
                    <div class="flex items-center gap-1">
                        {{-- 1. View related order --}}
                        <button type="button" id="viewOrderBtn" onclick="viewOrder()" title="View related order" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 2l-3 4v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4H6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 10a4 4 0 01-8 0" />
                            </svg>
                        </button>
                        {{-- 2. Mute notifications --}}
                        <button type="button" id="muteBtn" onclick="toggleMute()" title="Mute notifications" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53" />
                            </svg>
                        </button>
                        {{-- 3. More options --}}
                        <div class="relative">
                            <button type="button" onclick="toggleThreadMenu(event)" title="More options" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="5" cy="12" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="19" cy="12" r="1.5" />
                                </svg>
                            </button>
                            <div id="threadMenu" class="hidden absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-10 text-sm">
                                <button type="button" onclick="archiveCurrentConversation()" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-slate-600 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V4a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8M10 12h4" /></svg>
                                    Archive conversation
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

                <div id="messageThread" class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-slate-50/50">

                    <div class="flex justify-start">
                        <div class="max-w-md bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5">
                            <p class="text-sm text-navy">Magandang araw po! Sir, kailan po ma-release yung payout namin para sa nakaraang linggo?</p>
                            <p class="text-[10px] text-slate-400 mt-1">9:14 AM</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="max-w-md bg-mint-dark rounded-2xl rounded-tr-sm px-4 py-2.5">
                            <p class="text-sm text-white">Hi TechHub PH! Kinukumpirma ko lang po sa finance team, i-uupdate ko kayo within the day.</p>
                            <p class="text-[10px] text-mint/70 mt-1">9:20 AM &middot; Seen</p>
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <div class="max-w-md bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5">
                            <p class="text-sm text-navy">Salamat po sa update, sir!</p>
                            <p class="text-[10px] text-slate-400 mt-1">9:21 AM</p>
                        </div>
                    </div>

                </div>

                <div class="p-4 border-t border-slate-100">
                    {{-- Quick replies / canned responses --}}
                    <div id="quickReplies" class="flex items-center gap-2 mb-2 overflow-x-auto pb-1">
                        <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Payout is being processed</button>
                        <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Please send order number</button>
                        <button type="button" onclick="useQuickReply(this)" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Refund policy link</button>
                        <button type="button" onclick="addQuickReply()" class="shrink-0 text-xs px-3 py-1.5 rounded-full border border-dashed border-slate-300 text-slate-400 hover:bg-slate-50">+ Add reply</button>
                    </div>

                    {{-- Attached file preview --}}
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
                        <button type="button" onclick="sendMessage()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition shrink-0">
                            Send
                        </button>
                    </div>
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

                {{-- Specific / excluded user picker — lumalabas lang kapag "Specific users only" o "All users except..." ang napili --}}
                <div id="userPickerWrap" class="hidden">
                    <label id="userPickerLabel" class="text-xs font-semibold text-slate-500">Piliin ang users</label>

                    <div class="relative mt-1">
                        <input type="text" id="userPickerSearch" oninput="renderUserSuggestions()" onfocus="renderUserSuggestions()"
                            placeholder="Mag-search ng user (e.g. TechHub PH, Maria Reyes)..."
                            class="w-full px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        <div id="userSuggestions" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-40 overflow-y-auto z-20 text-sm"></div>
                    </div>

                    {{-- Selected user chips --}}
                    <div id="selectedUserChips" class="flex flex-wrap gap-1.5 mt-2"></div>
                    <p id="userPickerHint" class="text-[11px] text-slate-400 mt-1">Wala pang napiling user.</p>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Message</label>
                    <textarea id="broadcastMessage" rows="4" placeholder="Halimbawa: Magkakaroon ng scheduled maintenance sa Sept 5, 12AM-2AM."
                        class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none"></textarea>
                </div>

                {{-- Send now vs Schedule for later --}}
                <div>
                    <label class="text-xs font-semibold text-slate-500">Kailan ipapadala</label>
                    <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm font-medium mt-1">
                        <button type="button" onclick="setSendMode('now')" id="sendModeNowBtn"
                            class="flex-1 py-1.5 rounded-lg bg-white text-navy shadow-sm transition">
                            Send now
                        </button>
                        <button type="button" onclick="setSendMode('schedule')" id="sendModeScheduleBtn"
                            class="flex-1 py-1.5 rounded-lg text-slate-500 hover:text-navy transition">
                            Schedule for later
                        </button>
                    </div>

                    <div id="scheduleFields" class="hidden grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Date</label>
                            <input type="date" id="scheduleDate"
                                class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Time</label>
                            <input type="time" id="scheduleTime"
                                class="w-full mt-1 px-3 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="closeBroadcastModal()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">Cancel</button>
                <button type="button" id="sendBroadcastBtn" onclick="sendBroadcast()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-navy hover:opacity-90">Send Broadcast</button>
            </div>
        </div>
    </div>

    <script>
        let activeFilter = 'all';
        let isMuted = false;
        let currentOrder = 'ORD-10432';

        /* ---------- Toast helper ----------
           Gumagamit na ng global window.showToast(message, type, duration)
           na naka-render once sa app.blade.php layout. Walang sariling
           container/markup dito para iisa na lang ang toast system ng app.
        ---------------------------------------------------------------- */
        function toast(message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                // Fallback lang kung sakaling wala pang na-load ang layout partial.
                console.warn('showToast() not found — falling back to console.', message);
            }
        }

        /* ---------- Tabs: Active / Archived ---------- */
        function showTab(tab) {
            const activeList = document.getElementById('activeList');
            const archivedList = document.getElementById('archivedList');
            const activeBtn = document.getElementById('tabActiveBtn');
            const archivedBtn = document.getElementById('tabArchivedBtn');

            if (tab === 'active') {
                activeList.classList.remove('hidden');
                archivedList.classList.add('hidden');
                activeBtn.classList.add('bg-white', 'text-navy', 'shadow-sm');
                activeBtn.classList.remove('text-slate-500');
                archivedBtn.classList.remove('bg-white', 'text-navy', 'shadow-sm');
                archivedBtn.classList.add('text-slate-500');
            } else {
                archivedList.classList.remove('hidden');
                activeList.classList.add('hidden');
                archivedBtn.classList.add('bg-white', 'text-navy', 'shadow-sm');
                archivedBtn.classList.remove('text-slate-500');
                activeBtn.classList.remove('bg-white', 'text-navy', 'shadow-sm');
                activeBtn.classList.add('text-slate-500');
            }
            filterConversations();
        }

        /* ---------- Search ---------- */
        function filterConversations() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const visibleList = document.getElementById('activeList').classList.contains('hidden')
                ? document.getElementById('archivedList')
                : document.getElementById('activeList');

            visibleList.querySelectorAll('.convo-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const preview = item.querySelector('.convo-preview') ? item.querySelector('.convo-preview').textContent.toLowerCase() : '';
                const matchesSearch = !query || name.includes(query) || preview.includes(query);

                let matchesFilter = true;
                if (activeFilter === 'seller' || activeFilter === 'buyer') {
                    matchesFilter = item.dataset.type === activeFilter;
                } else if (activeFilter === 'unread') {
                    matchesFilter = item.dataset.unread === 'true';
                }

                item.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
            });
        }

        /* ---------- Buyer / Seller / Unread chip filter ---------- */
        function filterByType(type, btnEl) {
            activeFilter = type;
            document.querySelectorAll('.filter-chip').forEach(chip => {
                chip.classList.remove('bg-navy', 'text-white');
                chip.classList.add('bg-slate-100', 'text-slate-500');
            });
            btnEl.classList.remove('bg-slate-100', 'text-slate-500');
            btnEl.classList.add('bg-navy', 'text-white');
            filterConversations();
        }

        /* ---------- Select a conversation ---------- */
        function openConversation(el) {
            document.querySelectorAll('.convo-item').forEach(item => {
                item.classList.remove('bg-mint/5', 'border-l-2', 'border-mint-dark');
            });
            el.classList.add('bg-mint/5', 'border-l-2', 'border-mint-dark');

            // Update thread header to match selected conversation
            document.getElementById('threadName').textContent = el.dataset.name;
            document.getElementById('threadAvatarInitials').textContent = el.dataset.initials;
            document.getElementById('threadStatus').textContent = el.dataset.status;
            document.getElementById('threadStatus').className = el.dataset.status === 'Online' ? 'text-xs text-mint-dark' : 'text-xs text-slate-400';
            currentOrder = el.dataset.order || '';
            document.getElementById('viewOrderBtn').style.opacity = currentOrder ? '1' : '0.4';

            // Mark as read
            el.dataset.unread = 'false';
            el.classList.remove('font-semibold');

            toast('Kausap na si ' + el.dataset.name + '. (I-connect mo sa controller para i-load yung actual messages.)', 'info');
        }

        /* ---------- Mark as unread ---------- */
        function toggleUnread(el, event) {
            event.stopPropagation();
            const item = el.closest('.convo-item');
            const isUnread = item.dataset.unread === 'true';
            item.dataset.unread = isUnread ? 'false' : 'true';
            const nameEl = item.querySelector('.convo-name');
            nameEl.classList.toggle('text-mint-dark', !isUnread);
            toast(isUnread ? 'Na-mark as read.' : 'Na-mark as unread.', 'info');
            filterConversations();
        }

        /* ---------- Archive / Restore ---------- */
        function archiveConversation(el, event) {
            event.stopPropagation();
            const item = el.closest('.convo-item');
            document.getElementById('archivedList').prepend(item);
            item.querySelector('.hidden.group-hover\\:flex, [class*="group-hover"]');
            updateActiveCount();
            document.getElementById('archivedEmptyState').classList.add('hidden');
            toast('Na-archive ang chat kay ' + item.dataset.name + '.', 'success');
        }

        function restoreConversation(el, event) {
            event.stopPropagation();
            const item = el.closest('.convo-item');
            document.getElementById('activeList').prepend(item);
            updateActiveCount();
            toast('Na-restore ang chat kay ' + item.dataset.name + '.', 'success');
        }

        function archiveCurrentConversation() {
            const selected = document.querySelector('#activeList .convo-item.bg-mint\\/5');
            if (selected) {
                document.getElementById('archivedList').prepend(selected);
                updateActiveCount();
                toast('Na-archive ang kasalukuyang conversation.', 'success');
            } else {
                toast('Wala pang napiling conversation na i-a-archive.', 'warning');
            }
            document.getElementById('threadMenu').classList.add('hidden');
        }

        function updateActiveCount() {
            const count = document.querySelectorAll('#activeList .convo-item').length;
            document.getElementById('activeCountBadge').textContent = count;
        }

        /* ---------- Thread header actions ---------- */
        function viewOrder() {
            if (!currentOrder) {
                toast('Walang naka-link na order sa conversation na ito.', 'warning');
                return;
            }
            toast('Binubuksan ang Order #' + currentOrder + '... (i-link mo dito yung route mo papuntang orders.show)', 'info');
            // Example: window.location.href = "/admin/orders/" + currentOrder;
        }

        function toggleMute() {
            isMuted = !isMuted;
            const btn = document.getElementById('muteBtn');
            btn.classList.toggle('text-coral', isMuted);
            btn.classList.toggle('text-slate-500', !isMuted);
            toast(isMuted ? 'Naka-mute na ang notifications para dito.' : 'Naka-unmute na ang notifications.', 'info');
        }

        function toggleThreadMenu(event) {
            event.stopPropagation();
            document.getElementById('threadMenu').classList.toggle('hidden');
        }

        document.addEventListener('click', function (e) {
            const menu = document.getElementById('threadMenu');
            if (!menu.classList.contains('hidden') && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        function blockUser() {
            const name = document.getElementById('threadName').textContent;
            if (confirm('Sigurado ka bang gusto mong i-block si ' + name + '? Hindi na sila makakapag-send ng message sa iyo.')) {
                toast(name + ' ay na-block na.', 'error');
            }
            document.getElementById('threadMenu').classList.add('hidden');
        }

        function reportChat() {
            const name = document.getElementById('threadName').textContent;
            if (confirm('I-report ang conversation na ito kay ' + name + ' para sa review ng admin team?')) {
                toast('Na-submit na ang report. Susuriin ito ng compliance team.', 'warning');
            }
            document.getElementById('threadMenu').classList.add('hidden');
        }

        /* ---------- Quick replies ---------- */
        function useQuickReply(el) {
            const input = document.getElementById('messageInput');
            input.value = el.textContent.trim();
            input.focus();
        }

        function addQuickReply() {
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
        }

        /* ---------- Attach file ---------- */
        function triggerAttach() {
            document.getElementById('fileInput').click();
        }

        function showAttachedFile(input) {
            if (input.files && input.files.length > 0) {
                document.getElementById('attachFileName').textContent = input.files[0].name;
                document.getElementById('attachPreview').classList.remove('hidden');
            }
        }

        function removeAttachment() {
            document.getElementById('fileInput').value = '';
            document.getElementById('attachPreview').classList.add('hidden');
        }

        /* ---------- Send message ---------- */
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const attachPreview = document.getElementById('attachPreview');
            const hasAttachment = !attachPreview.classList.contains('hidden');
            const text = input.value.trim();

            if (!text && !hasAttachment) return;

            const thread = document.getElementById('messageThread');
            const now = new Date();
            const time = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

            const wrapper = document.createElement('div');
            wrapper.className = 'flex justify-end';
            wrapper.innerHTML = `
                <div class="max-w-md bg-mint-dark rounded-2xl rounded-tr-sm px-4 py-2.5">
                    ${text ? '<p class="text-sm text-white"></p>' : ''}
                    ${hasAttachment ? '<p class="text-xs text-white/80 italic mb-1">📎 ' + document.getElementById('attachFileName').textContent + '</p>' : ''}
                    <p class="text-[10px] text-mint/70 mt-1">${time} &middot; Sent</p>
                </div>`;
            if (text) {
                wrapper.querySelector('p.text-sm').textContent = text;
            }
            thread.appendChild(wrapper);
            thread.scrollTop = thread.scrollHeight;

            input.value = '';
            removeAttachment();
        }

        /* ---------- Broadcast modal ---------- */

        // Palitan mo na lang ito ng users galing sa database mo (id, name, role)
        const BROADCAST_USERS = [
            { id: 1, name: 'TechHub PH', role: 'Seller' },
            { id: 2, name: "Aling Nena's Store", role: 'Seller' },
            { id: 3, name: 'Maria Reyes', role: 'Buyer' },
            { id: 4, name: "Jomar's Repair Shop", role: 'Seller' },
            { id: 5, name: 'Carlo Villanueva', role: 'Buyer' },
            { id: 6, name: 'Grace Fernandez', role: 'Buyer' },
        ];
        let selectedBroadcastUsers = []; // array of user objects
        let broadcastSendMode = 'now'; // 'now' | 'schedule'

        function openBroadcastModal() {
            document.getElementById('broadcastModal').classList.remove('hidden');
        }

        function closeBroadcastModal() {
            document.getElementById('broadcastModal').classList.add('hidden');
            document.getElementById('userSuggestions').classList.add('hidden');
        }

        function setSendMode(mode) {
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

                // Default sa susunod na oras kung wala pang laman
                if (!document.getElementById('scheduleDate').value) {
                    const now = new Date();
                    document.getElementById('scheduleDate').value = now.toISOString().split('T')[0];
                }
            }
        }

        function onBroadcastTargetChange() {
            const target = document.getElementById('broadcastTarget').value;
            const wrap = document.getElementById('userPickerWrap');
            const label = document.getElementById('userPickerLabel');

            if (target === 'specific' || target === 'exclude') {
                wrap.classList.remove('hidden');
                label.textContent = target === 'specific'
                    ? 'Piliin lang ang mga user na padadalhan'
                    : 'Piliin ang mga user na i-e-exclude';
            } else {
                wrap.classList.add('hidden');
                selectedBroadcastUsers = [];
                document.getElementById('userPickerSearch').value = '';
                renderSelectedUserChips();
            }
        }

        function renderUserSuggestions() {
            const query = document.getElementById('userPickerSearch').value.toLowerCase();
            const box = document.getElementById('userSuggestions');
            const selectedIds = selectedBroadcastUsers.map(u => u.id);

            const matches = BROADCAST_USERS.filter(u =>
                !selectedIds.includes(u.id) && u.name.toLowerCase().includes(query)
            );

            if (matches.length === 0) {
                box.innerHTML = '<p class="px-3 py-2 text-slate-400 text-xs">Walang nahanap na user.</p>';
            } else {
                box.innerHTML = matches.map(u => `
                    <button type="button" onclick="selectBroadcastUser(${u.id})"
                        class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center justify-between">
                        <span class="text-navy">${u.name}</span>
                        <span class="text-[10px] uppercase tracking-wide text-slate-400">${u.role}</span>
                    </button>
                `).join('');
            }
            box.classList.remove('hidden');
        }

        function selectBroadcastUser(id) {
            const user = BROADCAST_USERS.find(u => u.id === id);
            if (user && !selectedBroadcastUsers.some(u => u.id === id)) {
                selectedBroadcastUsers.push(user);
                renderSelectedUserChips();
            }
            document.getElementById('userPickerSearch').value = '';
            document.getElementById('userSuggestions').classList.add('hidden');
        }

        function removeBroadcastUser(id) {
            selectedBroadcastUsers = selectedBroadcastUsers.filter(u => u.id !== id);
            renderSelectedUserChips();
        }

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
                </span>
            `).join('');
        }

        // Close the suggestions dropdown when clicking outside of it
        document.addEventListener('click', function (e) {
            const wrap = document.getElementById('userPickerWrap');
            const box = document.getElementById('userSuggestions');
            if (wrap && !wrap.contains(e.target)) {
                box.classList.add('hidden');
            }
        });

        function sendBroadcast() {
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

            // Scheduled send
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

                const formatted = scheduledAt.toLocaleString('en-PH', {
                    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                toast('Naka-schedule na ang broadcast sa ' + formatted + ' — ' + summary, 'success');
                // TODO: i-post ito sa controller mo (target mode, selected user IDs, message, scheduled_at)
                // para i-queue sa likod (e.g. Laravel scheduled job / queue:delay).
            } else {
                toast('Na-send ang broadcast — ' + summary, 'success');
                // TODO: i-post ito agad sa controller mo para i-dispatch ang notifications.
            }

            // Reset form
            document.getElementById('broadcastMessage').value = '';
            document.getElementById('scheduleDate').value = '';
            document.getElementById('scheduleTime').value = '';
            selectedBroadcastUsers = [];
            renderSelectedUserChips();
            setSendMode('now');
            closeBroadcastModal();
        }
    </script>

@endsection