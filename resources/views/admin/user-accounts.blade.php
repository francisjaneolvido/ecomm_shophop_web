@extends('admin.layout')

@section('title', 'User Accounts')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">User Accounts</h1>
        <p class="text-sm text-slate-500 mt-1">Buyer, Seller & Logistics Account Management.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">
            {{ session('status') }}
        </div>
    @endif

    {{-- ============ SEARCH + FILTER TABS ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.users') }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'all' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                All <span class="ml-1 opacity-70">({{ $counts['all'] }})</span>
            </a>
            <a href="{{ route('admin.users', ['filter' => 'buyers']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'buyers' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Buyers <span class="ml-1 opacity-70">({{ $counts['buyers'] }})</span>
            </a>
            <a href="{{ route('admin.users', ['filter' => 'sellers']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'sellers' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Sellers <span class="ml-1 opacity-70">({{ $counts['sellers'] }})</span>
            </a>
            <a href="{{ route('admin.users', ['filter' => 'pending']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'pending' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Pending <span class="ml-1 opacity-70">({{ $counts['pending'] }})</span>
            </a>
            <a href="{{ route('admin.users', ['filter' => 'suspended']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'suspended' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Suspended <span class="ml-1 opacity-70">({{ $counts['suspended'] }})</span>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users') }}" class="relative w-full sm:w-64">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-white border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
        </form>
    </div>

    {{-- ============ USER ACCOUNTS TABLE ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">User</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Role</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Joined</th>
                        <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-mint/15 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-mint-dark">{{ $user->initials }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-navy truncate">{{ $user->display_name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $roleColors = [
                                        'buyer' => 'text-sky bg-sky/10',
                                        'seller' => 'text-coral bg-coral/10',
                                        'logistics' => 'text-amber-600 bg-amber-100',
                                    ];
                                @endphp
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleColors[$user->account_type] ?? 'text-slate-500 bg-slate-100' }}">
                                    {{ ucfirst($user->account_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusDot = [
                                        'approved' => 'bg-mint-dark',
                                        'pending' => 'bg-amber-500',
                                        'suspended' => 'bg-coral',
                                        'rejected' => 'bg-slate-400',
                                    ];
                                    $statusText = [
                                        'approved' => 'text-mint-dark',
                                        'pending' => 'text-amber-600',
                                        'suspended' => 'text-coral',
                                        'rejected' => 'text-slate-500',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $statusText[$user->status] ?? 'text-slate-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot[$user->status] ?? 'bg-slate-400' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        data-view-user="{{ $user->id }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50"
                                    >
                                        View
                                    </button>

                                    @if ($user->status === 'pending')
                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reject', $user) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                                Reject
                                            </button>
                                        </form>
                                    @elseif ($user->status === 'suspended')
                                        <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                                                Reactivate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                                Suspend
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                No accounts found for this filter.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- ============ PAGINATION ============ --}}
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>

    </div>


    {{-- =========================================================
        VIEW DETAILS MODAL
        One shared modal — filled in dynamically via JS depending
        on which "View" button was clicked.
    ========================================================= --}}
    <div
        id="user-details-modal"
        class="fixed inset-0 z-100 hidden items-center justify-center p-4"
        aria-hidden="true"
    >
        <button
            type="button"
            data-user-details-close
            aria-label="Close"
            class="absolute inset-0 w-full h-full bg-navy/50 backdrop-blur-sm cursor-default"
        ></button>

        <div class="relative z-10 w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 sticky top-0 bg-white">
                <div>
                    <p id="user-details-name" class="font-bold text-navy text-base">—</p>
                    <p id="user-details-meta" class="text-xs text-slate-500 mt-0.5">—</p>
                </div>
                <button
                    type="button"
                    data-user-details-close
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500"
                >
                    &times;
                </button>
            </div>

            <div class="p-5">
                <div id="user-details-loading" class="text-sm text-slate-400 text-center py-6">
                    Loading...
                </div>

                <dl id="user-details-fields" class="hidden divide-y divide-slate-100 text-sm"></dl>

                <div id="user-details-files" class="hidden mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Uploaded Documents</p>
                    <div id="user-details-files-list" class="flex flex-col gap-2"></div>
                </div>
            </div>

        </div>
    </div>

<script>
    (function () {
        const modal = document.getElementById('user-details-modal');
        const nameEl = document.getElementById('user-details-name');
        const metaEl = document.getElementById('user-details-meta');
        const loadingEl = document.getElementById('user-details-loading');
        const fieldsEl = document.getElementById('user-details-fields');
        const filesEl = document.getElementById('user-details-files');
        const filesListEl = document.getElementById('user-details-files-list');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
        }

        function resetModal() {
            nameEl.textContent = '—';
            metaEl.textContent = '—';
            fieldsEl.innerHTML = '';
            filesListEl.innerHTML = '';
            fieldsEl.classList.add('hidden');
            filesEl.classList.add('hidden');
            loadingEl.classList.remove('hidden');
        }

        async function loadUser(userId) {
            resetModal();
            openModal();

            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();

                nameEl.textContent = data.display_name;
                metaEl.textContent = `${data.email} · ${data.account_type.charAt(0).toUpperCase() + data.account_type.slice(1)} · ${data.status.charAt(0).toUpperCase() + data.status.slice(1)} · Joined ${data.created_at}`;

                (data.fields || []).forEach(function (field) {
                    const row = document.createElement('div');
                    row.className = 'py-2.5 flex justify-between gap-4';
                    row.innerHTML = `
                        <span class="text-slate-500">${field.label}</span>
                        <span class="font-semibold text-navy text-right">${field.value || '—'}</span>
                    `;
                    fieldsEl.appendChild(row);
                });

                (data.files || []).forEach(function (file) {
                    if (!file.url) return;
                    const link = document.createElement('a');
                    link.href = file.url;
                    link.target = '_blank';
                    link.className = 'flex items-center justify-between px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-sm text-navy';
                    link.innerHTML = `<span>${file.label}</span><span class="text-mint-dark text-xs font-semibold">View file →</span>`;
                    filesListEl.appendChild(link);
                });

                loadingEl.classList.add('hidden');
                fieldsEl.classList.remove('hidden');
                if (filesListEl.children.length > 0) {
                    filesEl.classList.remove('hidden');
                }
            } catch (error) {
                loadingEl.textContent = 'Unable to load this account\'s details.';
            }
        }

        document.addEventListener('click', function (event) {
            const viewTrigger = event.target.closest('[data-view-user]');
            if (viewTrigger) {
                loadUser(viewTrigger.dataset.viewUser);
                return;
            }

            const closeTrigger = event.target.closest('[data-user-details-close]');
            if (closeTrigger) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });
    })();
</script>

@endsection