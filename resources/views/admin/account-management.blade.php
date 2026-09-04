@extends('admin.layout')

@section('title', 'Account Management')

@section('content')

    @php
        $roleStyles = [
            'super_admin'         => ['badge' => 'text-navy bg-navy/10', 'label' => 'Super Admin'],
            'compliance_officer'  => ['badge' => 'text-sky bg-sky/10', 'label' => 'Compliance Officer'],
            'support_staff'       => ['badge' => 'text-coral bg-coral/10', 'label' => 'Support Staff'],
        ];

        $avatarStyles = [
            'super_admin'        => 'bg-gradient-to-br from-mint to-sky',
            'compliance_officer' => 'bg-sky/15',
            'support_staff'      => 'bg-coral/15',
        ];

        $avatarTextStyles = [
            'super_admin'        => 'text-navy',
            'compliance_officer' => 'text-sky',
            'support_staff'      => 'text-coral',
        ];
    @endphp

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">
            <p class="font-semibold mb-1">May kulang o maling laman sa form:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-navy">Account Management</h1>
            <p class="text-sm text-slate-500 mt-1">Pamahalaan ang mga admin at staff accounts ng ShopHop.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('logistics.register') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-navy border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h6m-3-3v6M9 12a4 4 0 100-8 4 4 0 000 8zm-6 8a6 6 0 0112 0H3z" />
                </svg>
                Add Logistics Partner Account
            </a>
            <button
                type="button"
                id="openAddAdminBtn"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Admin Account
            </button>
        </div>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['total'] ?? 0 }}</p>
                    <p class="text-xs text-slate-500">Total Admin Accounts</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['active'] ?? 0 }}</p>
                    <p class="text-xs text-slate-500">Active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['disabled'] ?? 0 }}</p>
                    <p class="text-xs text-slate-500">Disabled</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ ADMIN ACCOUNTS TABLE ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Admin</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Role</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Last Active</th>
                        <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    @forelse ($admins as $admin)

                        @php
                            $roleStyle = $roleStyles[$admin->role]
                                ?? ['badge' => 'text-slate-500 bg-slate-100', 'label' => ucfirst($admin->role)];

                            $isActive = $admin->user->status === 'approved';
                            $isSelf   = $admin->user_id === auth()->id();
                        @endphp

                        <tr class="hover:bg-slate-50/60 transition">

                            {{-- ADMIN --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $avatarStyles[$admin->role] ?? 'bg-slate-200' }}"
                                    >
                                        <span class="text-xs font-bold {{ $avatarTextStyles[$admin->role] ?? 'text-slate-500' }}">
                                            {{ $admin->initials }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-navy truncate">
                                            {{ $admin->full_name }}

                                            @if ($isSelf)
                                                <span class="text-[10px] font-semibold text-mint-dark bg-mint/10 px-1.5 py-0.5 rounded-full ml-1">You</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-500 truncate">{{ $admin->user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- ROLE --}}
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleStyle['badge'] }}">
                                    {{ $roleStyle['label'] }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-5 py-4">
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                        <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Disabled
                                    </span>
                                @endif
                            </td>

                            {{-- LAST ACTIVE --}}
                            <td class="px-5 py-4 text-slate-500">
                                {{ $admin->last_active_at ? $admin->last_active_at->diffForHumans() : 'Never' }}
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="edit-admin-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50"
                                        data-admin-id="{{ $admin->id }}"
                                    >
                                        Edit
                                    </button>

                                    @unless ($isSelf)
                                        @if ($isActive)
                                            <form method="POST" action="{{ route('admin.accounts.disable', $admin->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5"
                                                >
                                                    Disable
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.accounts.enable', $admin->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90"
                                                >
                                                    Enable
                                                </button>
                                            </form>
                                        @endif
                                    @endunless
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <p class="text-sm font-semibold text-navy">No admin accounts found</p>
                                <p class="text-xs text-slate-400 mt-1">Click "Add Admin Account" to create one.</p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

    </div>


    {{-- =========================================================
        ADD ADMIN ACCOUNT MODAL
    ========================================================= --}}
    <div
        id="addAdminOverlay"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="addAdminPanel"
            class="relative w-full max-w-md
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150"
        >

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button
                type="button"
                id="addAdminClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20
                       w-9 h-9
                       rounded-full
                       bg-slate-100
                       text-navy/45
                       flex items-center justify-center
                       hover:bg-mint/10
                       hover:text-mint-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-mint/15
                       transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <form method="POST" action="{{ route('admin.accounts.store') }}" class="px-6 pt-8 pb-6">
                @csrf

                <h2 class="text-lg font-bold text-navy mb-1 pr-8">Add Admin Account</h2>
                <p class="text-xs text-slate-500 mb-5">Gagawa ng bagong admin/staff account na may access sa ShopHop admin panel.</p>

                <div class="space-y-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">First Name</label>
                            <input
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                required
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Last Name</label>
                            <input
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                required
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Password</label>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Minimum 8 characters.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Role</label>
                        <select
                            name="role"
                            required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                        >
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                            <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="compliance_officer" {{ old('role') === 'compliance_officer' ? 'selected' : '' }}>Compliance Officer</option>
                            <option value="support_staff" {{ old('role') === 'support_staff' ? 'selected' : '' }}>Support Staff</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-2 mt-6">
                    <button
                        type="button"
                        id="addAdminCancel"
                        class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition"
                    >
                        Create Account
                    </button>
                </div>

            </form>

        </div>

    </div>


    <script>
        (function () {
            const openBtn   = document.getElementById('openAddAdminBtn');
            const overlay   = document.getElementById('addAdminOverlay');
            const panel     = document.getElementById('addAdminPanel');
            const closeBtn  = document.getElementById('addAdminClose');
            const cancelBtn = document.getElementById('addAdminCancel');

            function openModal() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                requestAnimationFrame(() => {
                    panel.classList.remove('translate-y-2', 'opacity-0');
                });
            }

            function closeModal() {
                panel.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }, 150);
            }

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !overlay.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Kung may validation errors mula sa server (matapos mag-submit
            // ng form na may mali), i-bukas ulit agad yung modal para makita
            // ni admin yung mga error kasabay ng form niya.
            @if ($errors->any())
                openModal();
            @endif
        })();
    </script>

@endsection