@extends('layouts.app')

@section('title', 'Messages - ShopHop')

@section('content')
<div class="mx-auto max-w-5xl px-3 py-6">
    <h1 class="text-xl font-bold text-navy">Messages</h1>
    <p class="mt-1 text-xs text-navy/50">Conversations with your buyers and sellers. New messages appear automatically.</p>

    <div class="mt-4 grid h-[34rem] max-h-[75vh] overflow-hidden rounded-2xl border border-gray-border bg-white md:grid-cols-[260px_1fr]">
        {{-- Conversation list --}}
        <aside class="overflow-y-auto border-b border-gray-border md:border-b-0 md:border-r">
            <ul id="convList" class="divide-y divide-gray-border/70"></ul>
            <p id="convEmpty" class="hidden p-4 text-center text-xs text-navy/40">No conversations yet.</p>
        </aside>

        {{-- Thread --}}
        <section class="flex min-h-0 flex-col">
            <div id="threadHeader" class="border-b border-gray-border px-4 py-3 text-sm font-semibold text-navy">Select a conversation</div>
            <div id="threadBody" class="flex-1 space-y-2 overflow-y-auto bg-gray-bg/60 px-4 py-3 text-xs"></div>
            <form id="threadForm" class="hidden items-end gap-2 border-t border-gray-border p-3">
                <textarea id="threadInput" rows="1" maxlength="1000" placeholder="Type a reply..." class="max-h-24 min-h-9 flex-1 resize-none rounded-lg border border-gray-border px-3 py-2 text-xs focus:border-teal focus:outline-none"></textarea>
                <button type="submit" class="h-9 rounded-lg bg-teal px-4 text-xs font-semibold text-white hover:bg-teal-dark">Send</button>
            </form>
        </section>
    </div>
</div>
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

    const list      = document.getElementById('convList');
    const listEmpty = document.getElementById('convEmpty');
    const header    = document.getElementById('threadHeader');
    const body      = document.getElementById('threadBody');
    const form      = document.getElementById('threadForm');
    const input     = document.getElementById('threadInput');

    let activeId = null, lastId = 0;

    function bubble(m) {
        if (m.id <= lastId) return;
        lastId = m.id;

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
            const d = await r.json();
            if (!d.ok) return;

            list.innerHTML = '';
            listEmpty.classList.toggle('hidden', d.conversations.length > 0);

            d.conversations.forEach(c => {
                const li  = document.createElement('li');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'block w-full px-4 py-3 text-left hover:bg-gray-bg ' + (c.id === activeId ? 'bg-teal-light/40' : '');

                const top = document.createElement('div');
                top.className = 'flex items-center justify-between gap-2';

                const name = document.createElement('span');
                name.className = 'truncate text-xs font-semibold text-navy';
                name.textContent = c.name;
                top.appendChild(name);

                if (c.unread > 0) {
                    const u = document.createElement('span');
                    u.className = 'rounded-full bg-teal px-1.5 text-[9px] font-bold text-white';
                    u.textContent = c.unread;
                    top.appendChild(u);
                }

                const last = document.createElement('p');
                last.className = 'mt-0.5 truncate text-[10px] text-navy/45';
                last.textContent = c.last || 'No messages yet';

                btn.append(top, last);
                btn.addEventListener('click', () => openThread(c.id, c.name));
                li.appendChild(btn);
                list.appendChild(li);
            });
        } catch (e) {}
    }

    async function openThread(id, name) {
        activeId = id;
        lastId = 0;
        body.innerHTML = '';
        header.textContent = name;
        form.classList.remove('hidden');
        form.classList.add('flex');
        await pollThread();
        loadConversations();
        input.focus();
    }

    async function pollThread() {
        if (!activeId || document.hidden) return;
        try {
            const r = await fetch(base + '/' + activeId + '/messages?after=' + lastId, { headers });
            const d = await r.json();
            if (d.ok) d.messages.forEach(bubble);
        } catch (e) {}
    }

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || !activeId) return;
        try {
            const r = await fetch(base + '/' + activeId + '/send', { method: 'POST', headers, body: JSON.stringify({ body: text }) });
            const d = await r.json();
            if (r.ok && d.ok) { bubble(d.message); input.value = ''; loadConversations(); }
        } catch (err) {}
    });

    loadConversations();
    setInterval(pollThread, 3000);
    setInterval(loadConversations, 6000);
})();
</script>
@endpush