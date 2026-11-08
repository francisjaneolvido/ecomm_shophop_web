@php
    $chatSellerId   = $sellerId ?? null;
    $chatSellerName = $sellerName ?? 'Seller';
@endphp

<div id="shopChat" class="fixed right-3 bottom-16 lg:bottom-5 lg:right-5 z-[55]">
    {{-- Chat window --}}
    <div id="shopChatWindow" class="hidden w-[min(20rem,calc(100vw-1.5rem))] h-[26rem] max-h-[70vh] flex-col overflow-hidden rounded-2xl border border-gray-border bg-white shadow-2xl">
        <div class="flex items-center justify-between bg-teal px-3.5 py-2.5 text-white">
            <div class="min-w-0">
                <p class="truncate text-[11px] font-bold">{{ $chatSellerName }}</p>
                <p class="text-[9px] opacity-80">Seller · replies appear here</p>
            </div>
            <button type="button" id="shopChatClose" aria-label="Close chat" class="rounded-md p-1 hover:bg-white/15">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        <div id="shopChatBody" class="flex-1 space-y-2 overflow-y-auto bg-gray-bg/60 px-3 py-3 text-[11px]" aria-live="polite">
            <p id="shopChatEmpty" class="py-6 text-center text-[10px] text-navy/40">Say hi to the seller. Ask about the product, stock, or shipping.</p>
        </div>

        <p id="shopChatError" role="alert" class="hidden border-t border-red-200 bg-red-50 px-3 py-1.5 text-[10px] text-red-700"></p>

        <form id="shopChatForm" class="flex items-end gap-2 border-t border-gray-border bg-white p-2.5">
            <textarea id="shopChatInput" rows="1" maxlength="1000" placeholder="Type a message..." class="max-h-24 min-h-9 flex-1 resize-none rounded-lg border border-gray-border px-2.5 py-2 text-[11px] focus:border-teal focus:outline-none"></textarea>
            <button type="submit" id="shopChatSend" class="h-9 rounded-lg bg-teal px-3 text-[11px] font-semibold text-white hover:bg-teal-dark disabled:opacity-60">Send</button>
        </form>
    </div>

    {{-- Launcher bubble --}}
    <button type="button" id="shopChatLauncher" class="ml-auto mt-2 flex h-11 items-center gap-2 rounded-full bg-teal px-4 text-[11px] font-semibold text-white shadow-lg hover:bg-teal-dark">
        <x-lucide-message-circle class="h-4 w-4" />
        Chat
    </button>
</div>

@push('scripts')
<script>
(function () {
    const sellerId  = @json($chatSellerId);
    const isGuest   = @json(! auth()->check());
    const loginUrl  = @json(route('login'));
    const chatBase  = @json(url('/chat'));
    const urls = {
        open:     chatBase + '/open',
        messages: id => chatBase + '/' + id + '/messages',
        send:     id => chatBase + '/' + id + '/send',
    };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());

    const win      = document.getElementById('shopChatWindow');
    const launcher = document.getElementById('shopChatLauncher');
    const closeBtn = document.getElementById('shopChatClose');
    const body     = document.getElementById('shopChatBody');
    const empty    = document.getElementById('shopChatEmpty');
    const errBox   = document.getElementById('shopChatError');
    const form     = document.getElementById('shopChatForm');
    const input    = document.getElementById('shopChatInput');
    const sendBtn  = document.getElementById('shopChatSend');

    let chatId = null, lastId = 0, timer = null, isOpen = false;

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
    };

    function showError(msg) {
        errBox.textContent = msg;
        errBox.classList.remove('hidden');
    }
    function clearError() {
        errBox.textContent = '';
        errBox.classList.add('hidden');
    }

    // Turns any failed response into a readable message.
    async function readError(r) {
        let d = null;
        try { d = await r.json(); } catch (e) {}
        if (d && d.errors) return Object.values(d.errors).flat()[0];
        if (d && d.message) return d.message;
        if (r.status === 419) return 'Session expired (419). Refresh the page and try again.';
        if (r.status === 401) return 'You are not logged in (401).';
        if (r.status === 403) return 'Not allowed (403).';
        if (r.status === 404) return 'Chat route not found (404). Run: php artisan optimize:clear';
        if (r.status === 503) return 'Site is in maintenance mode (503).';
        return 'Request failed (' + r.status + ').';
    }

    function addMessage(m) {
        if (m.id <= lastId) return;
        lastId = m.id;
        if (empty && empty.parentNode) empty.remove();

        const row = document.createElement('div');
        row.className = 'flex ' + (m.mine ? 'justify-end' : 'justify-start');

        const bubble = document.createElement('div');
        bubble.className = 'max-w-[80%] rounded-2xl px-3 py-2 ' +
            (m.mine ? 'bg-teal text-white rounded-br-sm' : 'bg-white border border-gray-border text-navy rounded-bl-sm');

        const text = document.createElement('p');
        text.className = 'whitespace-pre-wrap break-words';
        text.textContent = m.body;

        const time = document.createElement('p');
        time.className = 'mt-0.5 text-[8px] opacity-60';
        time.textContent = m.time;

        bubble.append(text, time);
        row.appendChild(bubble);
        body.appendChild(row);
        body.scrollTop = body.scrollHeight;
    }

    async function poll() {
        if (!chatId || document.hidden) return;
        try {
            const r = await fetch(urls.messages(chatId) + '?after=' + lastId, { headers });
            if (!r.ok) { showError(await readError(r)); return; }
            const d = await r.json();
            if (d.ok) d.messages.forEach(addMessage);
        } catch (e) { showError('Network error while loading messages.'); }
    }

    async function ensureChat() {
        if (chatId) return true;

        if (!sellerId) {
            showError('This product has no seller_id, so chat cannot start.');
            return false;
        }

        try {
            const r = await fetch(urls.open, { method: 'POST', headers, body: JSON.stringify({ seller_id: sellerId }) });
            if (!r.ok) { showError(await readError(r)); return false; }
            const d = await r.json();
            if (!d.ok) { showError(d.message || 'Could not open chat.'); return false; }
            chatId = d.chat_id;
            clearError();
            return true;
        } catch (e) {
            showError('Network error while opening chat.');
            return false;
        }
    }

    async function openChat() {
        if (isGuest) { window.location.href = loginUrl; return; }
        isOpen = true;
        win.classList.remove('hidden');
        win.classList.add('flex');
        if (await ensureChat()) {
            await poll();
            clearInterval(timer);
            timer = setInterval(poll, 3000);
        }
        input.focus();
    }

    function closeChat() {
        isOpen = false;
        win.classList.add('hidden');
        win.classList.remove('flex');
        clearInterval(timer);
    }

    launcher.addEventListener('click', () => isOpen ? closeChat() : openChat());
    closeBtn.addEventListener('click', closeChat);
    document.querySelectorAll('[data-open-shop-chat]').forEach(b => b.addEventListener('click', openChat));

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        // Retry opening the chat if it failed earlier.
        if (!chatId && !(await ensureChat())) return;

        sendBtn.disabled = true;
        try {
            const r = await fetch(urls.send(chatId), { method: 'POST', headers, body: JSON.stringify({ body: text }) });
            if (!r.ok) {
                showError(await readError(r));
            } else {
                const d = await r.json();
                if (d.ok) { clearError(); addMessage(d.message); input.value = ''; }
                else showError(d.message || 'Message was not sent.');
            }
        } catch (err) {
            showError('Network error. Message was not sent.');
        }
        sendBtn.disabled = false;
        input.focus();
    });

    document.addEventListener('visibilitychange', () => { if (isOpen && !document.hidden) poll(); });
})();
</script>
@endpush