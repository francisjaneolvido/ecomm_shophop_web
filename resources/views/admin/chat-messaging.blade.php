@extends('admin.layout')

@section('title', 'Chat / Messaging')

@section('content')
    <section id="adminChat" aria-labelledby="chatTitle" class="flex flex-col gap-4 h-[calc(100vh-130px)] min-h-[480px]">
        <div class="shrink-0">
            <h1 id="chatTitle" class="text-2xl font-bold text-navy">Chat / Messaging</h1>
            <p class="text-sm text-slate-500 mt-1">Support inbox for buyers, sellers and logistics partners.</p>
        </div>

        <div class="flex flex-1 min-h-0 bg-white rounded-2xl border border-gray-border overflow-hidden shadow-soft">

            {{-- ============ CONVERSATION LIST ============ --}}
            <aside id="chatListPane" class="w-full md:w-80 lg:w-96 shrink-0 md:border-r border-gray-border flex flex-col min-h-0">
                <div class="p-3 border-b border-gray-border space-y-2 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <x-lucide-search class="w-3.5 h-3.5 text-navy/35 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input id="chatSearch" type="text" autocomplete="off" placeholder="Search conversations..."
                                   class="w-full h-9 pl-9 pr-3 rounded-lg bg-gray-bg text-xs text-navy placeholder:text-navy/35 border border-transparent focus:outline-none focus:border-teal/40 focus:bg-white">
                        </div>
                        <button id="chatNewToggle" type="button" title="Start a new conversation"
                                class="h-9 px-3 rounded-lg bg-teal text-navy text-xs font-semibold flex items-center gap-1.5 hover:bg-teal-dark transition shrink-0">
                            <x-lucide-plus class="w-3.5 h-3.5" /> New
                        </button>
                    </div>

                    <div id="chatNewPanel" class="hidden rounded-xl border border-gray-border p-2.5 bg-white">
                        <input id="chatUserSearch" type="text" autocomplete="off"
                               placeholder="Find a buyer, seller or partner (name / email)"
                               class="w-full h-9 px-3 rounded-lg bg-gray-bg text-xs text-navy placeholder:text-navy/35 border border-transparent focus:outline-none focus:border-teal/40 focus:bg-white">
                        <div id="chatUserResults" class="mt-2 max-h-52 overflow-y-auto content-scrollbar"></div>
                    </div>
                </div>

                <div id="chatList" class="flex-1 overflow-y-auto content-scrollbar"></div>
            </aside>

            {{-- ============ THREAD ============ --}}
            <div id="chatThreadPane" class="flex-1 min-w-0 flex flex-col min-h-0 max-md:hidden">

                <div id="chatEmpty" class="flex-1 flex flex-col items-center justify-center text-center px-6">
                    <div class="w-12 h-12 rounded-2xl bg-gray-bg text-navy/30 flex items-center justify-center">
                        <x-lucide-messages-square class="w-5 h-5" />
                    </div>
                    <p class="text-sm font-semibold text-navy/60 mt-3">Select a conversation</p>
                    <p class="text-xs text-navy/35 mt-1">Or press “New” to message a buyer, seller or logistics partner.</p>
                </div>

                <div id="chatThread" class="hidden flex-1 flex-col min-h-0">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-border shrink-0">
                        <button id="chatBack" type="button" class="md:hidden w-8 h-8 rounded-lg hover:bg-gray-bg flex items-center justify-center text-navy/60" aria-label="Back to conversations">
                            <x-lucide-arrow-left class="w-4 h-4" />
                        </button>
                        <div id="chatHeadAvatar" class="w-9 h-9 rounded-full bg-gradient-to-br from-teal to-sky text-navy text-[11px] font-bold flex items-center justify-center shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p id="chatHeadName" class="text-sm font-semibold text-navy truncate"></p>
                            <p id="chatHeadMeta" class="text-[10px] text-navy/40 truncate"></p>
                        </div>
                    </div>

                    <div id="chatMessages" class="flex-1 overflow-y-auto content-scrollbar px-4 py-4 space-y-2 bg-gray-bg/60"></div>
                    <p id="chatNoMsgs" class="hidden text-center text-xs text-navy/35 py-3 -mt-2">No messages yet. Say hello!</p>

                    <form id="chatForm" class="border-t border-gray-border p-3 shrink-0">
                        <p id="chatError" class="hidden text-[11px] text-red-500 mb-2"></p>
                        <div class="flex items-end gap-2">
                            <textarea id="chatInput" rows="1" maxlength="2000" placeholder="Type a message... (Enter to send, Shift+Enter for new line)"
                                      class="flex-1 resize-none rounded-xl bg-gray-bg px-3.5 py-2.5 text-sm text-navy placeholder:text-navy/35 border border-transparent focus:outline-none focus:border-teal/40 focus:bg-white max-h-32"></textarea>
                            <button id="chatSend" type="submit"
                                    class="h-10 w-10 rounded-xl bg-teal text-navy flex items-center justify-center hover:bg-teal-dark transition disabled:opacity-50 shrink-0"
                                    aria-label="Send message">
                                <x-lucide-send class="w-4 h-4" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    const URLS = {
        conversations: @json(route('admin.chat.conversations')),
        users: @json(route('admin.chat.users')),
        base: @json(url('admin/chat/conversations')),
    };
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const THREAD_POLL_MS = 3000;
    const LIST_POLL_MS = 8000;

    const state = { activeId: null, lastId: 0, rendered: new Set(), convs: [], sending: false };

    const $ = (id) => document.getElementById(id);
    const el = (tag, cls, text) => {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined) n.textContent = text;
        return n;
    };

    const TYPE_STYLE = {
        buyer: 'bg-sky/15 text-sky',
        seller: 'bg-teal/15 text-teal-dark',
        logistics: 'bg-yellow/25 text-navy',
    };
    const TYPE_LABEL = { buyer: 'Buyer', seller: 'Seller', logistics: 'Logistics' };

    function fmtTime(iso) {
        const d = new Date(iso);
        const now = new Date();
        return d.toDateString() === now.toDateString()
            ? d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
            : d.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }

    function fmtFull(iso) {
        const d = new Date(iso);
        const time = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        return d.toDateString() === new Date().toDateString()
            ? time
            : d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ', ' + time;
    }

    async function api(url, method = 'GET', body = null) {
        const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        const init = { method, headers, credentials: 'same-origin' };
        if (method !== 'GET') {
            headers['Content-Type'] = 'application/json';
            headers['X-CSRF-TOKEN'] = CSRF;
            init.body = JSON.stringify(body || {});
        }
        const res = await fetch(url, init);
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = json.message || (json.errors ? Object.values(json.errors)[0][0] : 'Request failed.');
            const err = new Error(msg);
            err.status = res.status;
            throw err;
        }
        return json;
    }

    /* ---------------- conversation list ---------------- */

    function typeBadge(type) {
        return el('span', 'px-1.5 py-0.5 rounded-md text-[9px] font-bold ' + (TYPE_STYLE[type] || 'bg-gray-bg text-navy/50'), TYPE_LABEL[type] || type || '');
    }

    function renderList() {
        const list = $('chatList');
        list.replaceChildren();

        if (!state.convs.length) {
            list.appendChild(el('p', 'text-center text-xs text-navy/35 px-6 py-10', 'No conversations yet. Press “New” to start one.'));
            return;
        }

        state.convs.forEach((c) => {
            const btn = el('button', 'w-full text-left flex items-center gap-3 px-4 py-3 border-b border-gray-border/60 hover:bg-gray-bg transition ' + (c.id === state.activeId ? 'bg-teal-light' : ''));
            btn.type = 'button';

            btn.appendChild(el('div', 'w-10 h-10 rounded-full bg-gradient-to-br from-teal to-sky text-navy text-[11px] font-bold flex items-center justify-center shrink-0', c.initials));

            const body = el('div', 'min-w-0 flex-1');

            const top = el('div', 'flex items-center gap-2');
            top.appendChild(el('p', 'text-[13px] font-semibold text-navy truncate flex-1', c.name));
            if (c.last_message_at) top.appendChild(el('span', 'text-[10px] text-navy/35 shrink-0', fmtTime(c.last_message_at)));
            body.appendChild(top);

            const bottom = el('div', 'flex items-center gap-2 mt-0.5');
            const preview = c.last_message
                ? (c.last_sender_role === 'admin' ? 'You: ' : '') + c.last_message
                : 'No messages yet';
            bottom.appendChild(el('p', 'text-[11px] truncate flex-1 ' + (c.unread ? 'text-navy font-semibold' : 'text-navy/45'), preview));
            if (c.unread) bottom.appendChild(el('span', 'min-w-[18px] h-[18px] px-1 rounded-full bg-coral text-white text-[10px] font-bold flex items-center justify-center shrink-0', c.unread > 99 ? '99+' : String(c.unread)));
            body.appendChild(bottom);

            const meta = el('div', 'mt-1');
            meta.appendChild(typeBadge(c.account_type));
            body.appendChild(meta);

            btn.appendChild(body);
            btn.addEventListener('click', () => openConversation(c.id));
            list.appendChild(btn);
        });
    }

    async function loadList() {
        try {
            const q = $('chatSearch').value.trim();
            const res = await api(URLS.conversations + '?q=' + encodeURIComponent(q));
            state.convs = res.data;
            renderList();
        } catch (e) { /* keep the previous list on transient errors */ }
    }

    /* ---------------- thread ---------------- */

    function showThread(open) {
        $('chatListPane').classList.toggle('max-md:hidden', open);
        $('chatThreadPane').classList.toggle('max-md:hidden', !open);
        $('chatEmpty').classList.toggle('hidden', open);
        $('chatThread').classList.toggle('hidden', !open);
        $('chatThread').classList.toggle('flex', open);
    }

    function renderHeader(c) {
        $('chatHeadAvatar').textContent = c.initials;
        $('chatHeadName').textContent = c.name;
        $('chatHeadMeta').textContent = (TYPE_LABEL[c.account_type] || '') + (c.email ? ' · ' + c.email : '');
    }

    function bubble(m) {
        const mine = m.sender_role === 'admin';
        const row = el('div', 'flex ' + (mine ? 'justify-end' : 'justify-start'));
        const box = el('div', 'max-w-[78%] rounded-2xl px-3.5 py-2 ' + (mine ? 'bg-navy text-white rounded-br-md' : 'bg-white text-navy border border-gray-border rounded-bl-md'));
        box.appendChild(el('p', 'text-[13px] leading-relaxed whitespace-pre-wrap break-words', m.body));
        box.appendChild(el('p', 'text-[9px] mt-1 ' + (mine ? 'text-white/50 text-right' : 'text-navy/35'), fmtFull(m.created_at)));
        row.appendChild(box);
        return row;
    }

    // advance=false for our own just-sent message, so polling can't skip an
    // incoming message that landed between our last poll and our send.
    function appendMessages(list, forceScroll, advance = true) {
        const box = $('chatMessages');
        const nearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 120;

        list.forEach((m) => {
            if (state.rendered.has(m.id)) return;
            state.rendered.add(m.id);
            box.appendChild(bubble(m));
            if (advance && m.id > state.lastId) state.lastId = m.id;
        });

        $('chatNoMsgs').classList.toggle('hidden', state.rendered.size > 0);
        if (forceScroll || nearBottom) box.scrollTop = box.scrollHeight;
    }

    async function openConversation(id) {
        state.activeId = id;
        state.lastId = 0;
        state.rendered.clear();
        $('chatMessages').replaceChildren();
        $('chatError').classList.add('hidden');
        showThread(true);
        renderList();

        try {
            const res = await api(`${URLS.base}/${id}/messages?after=0`);
            if (state.activeId !== id) return;
            renderHeader(res.conversation);
            appendMessages(res.data, true);
            $('chatInput').focus();
            loadList();
        } catch (e) {
            $('chatError').textContent = e.message;
            $('chatError').classList.remove('hidden');
        }
    }

    async function pollThread() {
        if (document.hidden || !state.activeId) return;
        const id = state.activeId;
        try {
            const res = await api(`${URLS.base}/${id}/messages?after=${state.lastId}`);
            if (id === state.activeId) appendMessages(res.data, false);
        } catch (e) { /* try again next tick */ }
    }

    /* ---------------- sending ---------------- */

    const input = $('chatInput');

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 128) + 'px';
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            $('chatForm').requestSubmit();
        }
    });

    $('chatForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = input.value.trim();
        if (!body || !state.activeId || state.sending) return;

        state.sending = true;
        $('chatSend').disabled = true;
        $('chatError').classList.add('hidden');

        try {
            const res = await api(`${URLS.base}/${state.activeId}/messages`, 'POST', { body });
            input.value = '';
            input.style.height = 'auto';
            appendMessages([res.data], true, false);
            loadList();
        } catch (err) {
            $('chatError').textContent = err.message;
            $('chatError').classList.remove('hidden');
        } finally {
            state.sending = false;
            $('chatSend').disabled = false;
            input.focus();
        }
    });

    $('chatBack').addEventListener('click', () => {
        state.activeId = null;
        showThread(false);
        renderList();
    });

    /* ---------------- search + new conversation ---------------- */

    const debounce = (fn, ms) => {
        let t;
        return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    };

    $('chatSearch').addEventListener('input', debounce(loadList, 250));

    $('chatNewToggle').addEventListener('click', () => {
        const panel = $('chatNewPanel');
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) $('chatUserSearch').focus();
    });

    $('chatUserSearch').addEventListener('input', debounce(async () => {
        const q = $('chatUserSearch').value.trim();
        const box = $('chatUserResults');
        box.replaceChildren();
        if (q.length < 2) return;

        try {
            const res = await api(URLS.users + '?q=' + encodeURIComponent(q));
            if (!res.data.length) {
                box.appendChild(el('p', 'text-[11px] text-navy/40 text-center py-3', 'No approved account found.'));
                return;
            }
            res.data.forEach((u) => {
                const b = el('button', 'w-full text-left flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-gray-bg transition');
                b.type = 'button';
                b.appendChild(el('div', 'w-8 h-8 rounded-full bg-gradient-to-br from-teal to-sky text-navy text-[10px] font-bold flex items-center justify-center shrink-0', u.initials));
                const t = el('div', 'min-w-0 flex-1');
                t.appendChild(el('p', 'text-xs font-semibold text-navy truncate', u.name));
                t.appendChild(el('p', 'text-[10px] text-navy/40 truncate', u.email));
                b.appendChild(t);
                b.appendChild(typeBadge(u.account_type));
                b.addEventListener('click', async () => {
                    try {
                        const r = await api(URLS.conversations, 'POST', { user_id: u.id });
                        $('chatNewPanel').classList.add('hidden');
                        $('chatUserSearch').value = '';
                        box.replaceChildren();
                        $('chatSearch').value = '';
                        await loadList();
                        openConversation(r.data.id);
                    } catch (err) {
                        box.replaceChildren(el('p', 'text-[11px] text-red-500 text-center py-3', err.message));
                    }
                });
                box.appendChild(b);
            });
        } catch (err) {
            box.replaceChildren(el('p', 'text-[11px] text-red-500 text-center py-3', err.message));
        }
    }, 250));

    /* ---------------- boot ---------------- */

    loadList();
    setInterval(pollThread, THREAD_POLL_MS);
    setInterval(loadList, LIST_POLL_MS);
})();
</script>
@endpush