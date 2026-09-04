@extends('seller.partials.layout')

@section('title', 'Chat')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $conversations = collect($conversations ?? [
        [
            'id' => 1,
            'name' => 'Maricel Santos',
            'last_message' => 'Pwede po ba bukas na po ma-deliver?',
            'time' => '10:24 AM',
            'unread' => 2,
            'messages' => [
                ['from' => 'buyer', 'text' => 'Hi po, order ko po kelan po darating?', 'time' => '10:10 AM'],
                ['from' => 'seller', 'text' => 'Hi Maricel! Ipa-pickup na po namin ngayon, dapat bukas dumating.', 'time' => '10:15 AM'],
                ['from' => 'buyer', 'text' => 'Pwede po ba bukas na po ma-deliver?', 'time' => '10:24 AM'],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Jonas Villareal',
            'last_message' => 'Salamat po, natanggap ko na po.',
            'time' => 'Yesterday',
            'unread' => 0,
            'messages' => [
                ['from' => 'buyer', 'text' => 'Salamat po, natanggap ko na po.', 'time' => 'Yesterday'],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Ella Marasigan',
            'last_message' => 'Ok po, aabangan ko po.',
            'time' => '2 days ago',
            'unread' => 0,
            'messages' => [
                ['from' => 'buyer', 'text' => 'Ok po, aabangan ko po.', 'time' => '2 days ago'],
            ],
        ],
    ]);
@endphp


<style>
    #sellerChat { height: calc(100vh - 140px); min-height: 480px; }

    #sellerChat .chat-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 44, 63, .14) transparent;
    }

    #sellerChat .chat-scrollbar::-webkit-scrollbar { width: 6px; }
    #sellerChat .chat-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(15, 44, 63, .14);
        border-radius: 999px;
    }
</style>


<div id="sellerChat" class="bg-white border border-gray-border rounded-xl overflow-hidden flex">

    {{-- =========================================================
        CONVERSATION LIST
    ========================================================= --}}
    <div class="w-full sm:w-72 shrink-0 border-r border-gray-border flex flex-col" id="chatListPanel">

        <div class="p-3 border-b border-gray-border">
            <div class="relative">
                <x-lucide-search class="w-3.5 h-3.5 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    placeholder="Search conversations..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs
                           focus:outline-none focus:border-teal/50"
                >
            </div>
        </div>

        <div class="flex-1 overflow-y-auto chat-scrollbar">
            @foreach ($conversations as $index => $conversation)
                <button
                    type="button"
                    data-conversation-trigger
                    data-conversation-id="{{ $conversation['id'] }}"
                    class="w-full flex items-start gap-2.5 px-3 py-3 border-b border-gray-border/60 text-left hover:bg-gray-bg/60 transition-colors {{ $index === 0 ? 'bg-teal-light/40' : '' }}"
                >
                    <div class="w-9 h-9 rounded-full bg-navy/10 text-navy flex items-center justify-center text-[10px] font-bold shrink-0">
                        {{ mb_strtoupper(mb_substr($conversation['name'], 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[11px] font-semibold text-navy truncate">{{ $conversation['name'] }}</p>
                            <span class="text-[9px] text-navy/35 shrink-0">{{ $conversation['time'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-[10px] text-navy/45 truncate">{{ $conversation['last_message'] }}</p>
                            @if ($conversation['unread'] > 0)
                                <span class="text-[9px] font-semibold bg-coral text-white w-4 h-4 rounded-full flex items-center justify-center shrink-0">
                                    {{ $conversation['unread'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

    </div>


    {{-- =========================================================
        MESSAGE THREAD
    ========================================================= --}}
    <div class="flex-1 flex-col hidden sm:flex" id="chatThreadPanel">

        <div class="flex items-center gap-2.5 px-4 py-3 border-b border-gray-border">
            <div class="w-8 h-8 rounded-full bg-navy/10 text-navy flex items-center justify-center text-[10px] font-bold" id="threadAvatar"></div>
            <p class="text-xs font-semibold text-navy" id="threadName"></p>
        </div>

        <div class="flex-1 overflow-y-auto chat-scrollbar px-4 py-4 space-y-3" id="threadMessages"></div>

        <form id="chatSendForm" class="flex items-center gap-2 px-3 py-3 border-t border-gray-border">
            <input
                type="text"
                id="chatMessageInput"
                placeholder="Type a message..."
                class="flex-1 h-9 px-3 rounded-lg border border-gray-border text-xs
                       focus:outline-none focus:border-teal/50"
            >
            <button
                type="submit"
                class="w-9 h-9 rounded-lg bg-navy hover:bg-navy/90 text-white flex items-center justify-center shrink-0 transition-colors"
            >
                <x-lucide-send class="w-3.5 h-3.5" />
            </button>
        </form>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conversations = @json($conversations);
    const threadMessages = document.getElementById('threadMessages');
    const threadName = document.getElementById('threadName');
    const threadAvatar = document.getElementById('threadAvatar');
    const threadPanel = document.getElementById('chatThreadPanel');
    let activeId = conversations[0]?.id ?? null;

    function renderThread(id) {
        const convo = conversations.find(c => c.id === id);
        if (!convo) return;

        activeId = id;
        threadName.textContent = convo.name;
        threadAvatar.textContent = convo.name.charAt(0).toUpperCase();

        threadMessages.innerHTML = '';
        convo.messages.forEach(function (message) {
            const bubble = document.createElement('div');
            const isSeller = message.from === 'seller';

            bubble.className = 'flex ' + (isSeller ? 'justify-end' : 'justify-start');
            bubble.innerHTML = `
                <div class="max-w-[75%] ${isSeller ? 'bg-navy text-white' : 'bg-gray-bg text-navy'} rounded-xl px-3 py-2">
                    <p class="text-[11px]">${message.text}</p>
                    <p class="text-[9px] mt-1 ${isSeller ? 'text-white/50' : 'text-navy/35'}">${message.time}</p>
                </div>
            `;
            threadMessages.appendChild(bubble);
        });

        threadMessages.scrollTop = threadMessages.scrollHeight;
        threadPanel.classList.remove('hidden');
        threadPanel.classList.add('flex');
    }

    document.querySelectorAll('[data-conversation-trigger]').forEach(function (button) {
        button.addEventListener('click', function () {
            renderThread(parseInt(button.getAttribute('data-conversation-id'), 10));
        });
    });

    document.getElementById('chatSendForm')?.addEventListener('submit', function (event) {
        event.preventDefault();

        const input = document.getElementById('chatMessageInput');
        const text = input.value.trim();
        if (!text || activeId === null) return;

        const convo = conversations.find(c => c.id === activeId);
        convo.messages.push({ from: 'seller', text: text, time: 'Just now' });
        input.value = '';
        renderThread(activeId);
    });

    if (activeId !== null && window.innerWidth >= 640) {
        renderThread(activeId);
    }
});
</script>
@endpush

@endsection