@extends('seller.partials.layout')

@section('title', 'Seller Chat')

@section('content')
<section id="sellerChat" aria-labelledby="chatTitle" class="mx-auto max-w-5xl space-y-4">
    <div>
        <h1 id="chatTitle" class="text-2xl font-bold text-navy">Chat / Messaging</h1>
        <p class="mt-1 text-sm text-navy/55">Conversations with your buyers. New messages appear automatically.</p>
    </div>

    <div class="grid h-[calc(100vh-14rem)] min-h-[28rem] overflow-hidden rounded-2xl border border-gray-border bg-white md:grid-cols-[280px_1fr]">

        {{-- Conversation list --}}
        <aside id="sellerConvPane" class="min-h-0 overflow-y-auto border-gray-border md:border-r">
            <ul id="sellerConvList" class="divide-y divide-gray-border/70"></ul>
            <p id="sellerConvEmpty" class="hidden p-6 text-center text-xs text-navy/40">
                No conversations yet. Buyers who message your products will appear here.
            </p>
        </aside>

        {{-- Thread --}}
        <section id="sellerThreadPane" class="hidden min-h-0 flex-col md:flex">
            <div class="flex items-center gap-2 border-b border-gray-border px-4 py-3">
                <button type="button" id="sellerThreadBack" aria-label="Back to conversations"
                        class="rounded-md p-1 text-navy/60 hover:bg-gray-bg md:hidden">
                    <x-lucide-arrow-left class="h-4 w-4" />
                </button>
                <div id="sellerThreadHeader" class="min-w-0 flex-1 truncate text-sm font-semibold text-navy">
                    Select a conversation
                </div>
            </div>

            <div id="sellerThreadBody" class="flex-1 space-y-2 overflow-y-auto bg-gray-bg/60 px-4 py-3 text-xs" aria-live="polite">
                <p id="sellerThreadHint" class="py-10 text-center text-[11px] text-navy/40">
                    Choose a conversation on the left to read and reply.
                </p>
            </div>

            <p id="sellerChatError" role="alert" class="hidden border-t border-red-200 bg-red-50 px-4 py-1.5 text-[11px] text-red-700"></p>

            <form id="sellerChatForm" class="hidden items-end gap-2 border-t border-gray-border bg-white p-3">
                <textarea id="sellerChatInput" rows="1" maxlength="1000" placeholder="Type a reply..."
                          class="max-h-24 min-h-9 flex-1 resize-none rounded-lg border border-gray-border px-3 py-2 text-xs focus:border-teal focus:outline-none"></textarea>
                <button type="submit" id="sellerChatSend"
                        class="h-9 rounded-lg bg-teal px-4 text-xs font-semibold text-white hover:bg-teal-dark disabled:opacity-60">Send</button>
            </form>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
    };
    const base = @json(url('/chat'));

    const convPane   = document.getElementById('sellerConvPane');
    const threadPane = document.getElementById('sellerThreadPane');
    const list       = document.getElementById('sellerConvList');
    const listEmpty  = document.getElementById('sellerConvEmpty');
    const header     = document.getElementById('sellerThreadHeader');
    const body       = document.getElementById('sellerThreadBody');
    const hint       = document.getElementById('sellerThreadHint');
    const errBox     = document.getElementById('sellerChatError');
    const form       = document.getElementById('sellerChatForm');
    const input      = document.getElementById('sellerChatInput');
    const sendBtn    = document.getElementById('sellerChatSend');
    const backBtn    = document.getElementById('sellerThreadBack');

    let activeId = null, lastId = 0;

    function showError(msg) { errBox.textContent = msg; errBox.classList.remove('hidden'); }
    function clearError()   { errBox.textContent = ''; errBox.classList.add('hidden'); }

    async function readError(r) {
        let d = null;
        try { d = await r.json(); } catch (e) {}
        if (d && d.errors) return Object.values(d.errors).flat()[0];
        if (d && d.message) return d.message;
        if (r.status === 419) return 'Session expired (419). Refresh the page and try again.';
        if (r.status === 401) return 'You are not logged in (401).';
        if (r.status === 403) return 'Not allowed (403).';
        if (r.status === 404) return 'Chat route not found (404). Run: php artisan optimize:clear';
        return 'Request failed (' + r.status + ').';
    }

    // Mobile: show either the list or the thread. Desktop (md+) always shows both.
    function showThreadPane(show) {
        convPane.classList.toggle('hidden', show);
        convPane.classList.add('md:block');
        threadPane.classList.toggle('hidden', !show);
        threadPane.classList.add('md:flex');
        if (show) threadPane.classList.add('flex');
        else threadPane.classList.remove('flex');
    }

    function bubble(m) {
        if (m.id <= lastId) return;
        lastId = m.id;
        if (hint && hint.parentNode) hint.remove();

        const row = document.createElement('div');
        row.className = 'flex ' + (m.mine ? 'justify-end' : 'justify-start');

        const b = document.createElement('div');
        b.className = 'max-w-[75%] rounded-2xl px-3 py-2 ' +
            (m.mine ? 'bg-teal text-white rounded-br-sm' : 'bg-white border border-gray-border text-navy rounded-bl-sm');

        const p = document.createElement('p');
        p.className = 'whitespace-pre-wrap break-words';
        p.textContent = m.body;

        const t = document.createElement('p');
        t.className = 'mt-0.5 text-[9px] opacity-60';
        t.textContent = m.time;

        b.append(p, t);
        row.appendChild(b);
        body.appendChild(row);
        body.scrollTop = body.scrollHeight;
    }

    async function loadConversations() {
        try {
            const r = await fetch(base + '/conversations', { headers });
            if (!r.ok) { showError(await readError(r)); return; }
            const d = await r.json();
            if (!d.ok) return;

            list.innerHTML = '';
            listEmpty.classList.toggle('hidden', d.conversations.length > 0);

            d.conversations.forEach(c => {
                const li  = document.createElement('li');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'block w-full px-4 py-3 text-left hover:bg-gray-bg ' +
                    (c.id === activeId ? 'bg-teal-light/60' : '');

                const top = document.createElement('div');
                top.className = 'flex items-center justify-between gap-2';

                const name = document.createElement('span');
                name.className = 'truncate text-xs font-semibold text-navy';
                name.textContent = c.name;
                top.appendChild(name);

                if (c.unread > 0 && c.id !== activeId) {
                    const u = document.createElement('span');
                    u.className = 'rounded-full bg-teal px-1.5 text-[9px] font-bold text-white';
                    u.textContent = c.unread;
                    top.appendChild(u);
                }

                const last = document.createElement('p');
                last.className = 'mt-0.5 truncate text-[10px] text-navy/45';
                last.textContent = c.last || 'No messages yet';

                const time = document.createElement('p');
                time.className = 'mt-0.5 text-[9px] text-navy/30';
                time.textContent = c.time || '';

                btn.append(top, last, time);
                btn.addEventListener('click', () => openThread(c.id, c.name));
                li.appendChild(btn);
                list.appendChild(li);
            });
        } catch (e) {}
    }

    async function openThread(id, name) {
        activeId = id;
        lastId = 0;
        clearError();
        body.innerHTML = '';
        header.textContent = name;
        form.classList.remove('hidden');
        form.classList.add('flex');
        showThreadPane(true);
        await pollThread();
        loadConversations();
        input.focus();
    }

    async function pollThread() {
        if (!activeId || document.hidden) return;
        try {
            const r = await fetch(base + '/' + activeId + '/messages?after=' + lastId, { headers });
            if (!r.ok) { showError(await readError(r)); return; }
            const d = await r.json();
            if (d.ok) d.messages.forEach(bubble);
        } catch (e) {}
    }

    backBtn.addEventListener('click', () => { showThreadPane(false); loadConversations(); });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || !activeId) return;

        sendBtn.disabled = true;
        try {
            const r = await fetch(base + '/' + activeId + '/send', {
                method: 'POST', headers, body: JSON.stringify({ body: text }),
            });
            if (!r.ok) {
                showError(await readError(r));
            } else {
                const d = await r.json();
                if (d.ok) { clearError(); bubble(d.message); input.value = ''; loadConversations(); }
                else showError(d.message || 'Message was not sent.');
            }
        } catch (err) {
            showError('Network error. Message was not sent.');
        }
        sendBtn.disabled = false;
        input.focus();
    });

    document.addEventListener('visibilitychange', () => { if (!document.hidden) { pollThread(); loadConversations(); } });

    loadConversations();
    setInterval(pollThread, 3000);
    setInterval(loadConversations, 6000);
})();
</script>
@endpush