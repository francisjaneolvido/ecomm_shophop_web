@extends('admin.layout')

@section('title', 'Account Registrations')

@section('content')

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-navy">Account Registrations</h1>
                <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                    Review and verify new Buyer, Seller, and Logistics account applications.
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-400">
                <x-lucide-shield-check class="w-4 h-4 text-mint-dark" />
                <span>Identity and account verification</span>
            </div>
        </div>
    </div>

    {{-- ============ SUMMARY CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Pending Review</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $counts['pending'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <x-lucide-clock-3 class="w-5 h-5" />
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Buyers</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $counts['buyer'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky/10 text-sky flex items-center justify-center">
                    <x-lucide-shopping-bag class="w-5 h-5" />
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Sellers</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $counts['seller'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center">
                    <x-lucide-store class="w-5 h-5" />
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Logistics</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $counts['logistics'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                    <x-lucide-truck class="w-5 h-5" />
                </div>
            </div>
        </div>

    </div>

    {{-- ============ STATUS TABS + ROLE PILLS + SORT + SEARCH ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'pending', 'role' => $role, 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $filter === 'pending' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Pending <span class="ml-1 opacity-70">({{ $counts['pending'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'approved', 'role' => $role, 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $filter === 'approved' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Approved <span class="ml-1 opacity-70">({{ $counts['approved'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'rejected', 'role' => $role, 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $filter === 'rejected' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Rejected <span class="ml-1 opacity-70">({{ $counts['rejected'] }})</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">

            {{-- ROLE PILLS --}}
            <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-50">
                <a href="{{ request()->fullUrlWithQuery(['filter' => $filter, 'role' => 'all', 'sort' => $sort]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $role === 'all' ? 'bg-white text-navy shadow-sm' : 'text-slate-500 hover:bg-white' }}">
                    All
                </a>
                <a href="{{ request()->fullUrlWithQuery(['filter' => $filter, 'role' => 'buyer', 'sort' => $sort]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $role === 'buyer' ? 'bg-white text-navy shadow-sm' : 'text-slate-500 hover:bg-white' }}">
                    Buyers
                </a>
                <a href="{{ request()->fullUrlWithQuery(['filter' => $filter, 'role' => 'seller', 'sort' => $sort]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $role === 'seller' ? 'bg-white text-navy shadow-sm' : 'text-slate-500 hover:bg-white' }}">
                    Sellers
                </a>
                <a href="{{ request()->fullUrlWithQuery(['filter' => $filter, 'role' => 'logistics', 'sort' => $sort]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $role === 'logistics' ? 'bg-white text-navy shadow-sm' : 'text-slate-500 hover:bg-white' }}">
                    Logistics
                </a>
            </div>

            {{-- SORT --}}
            <form method="GET" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" onchange="this.form.submit()"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Name (Z–A)</option>
                </select>
            </form>

            {{-- SEARCH --}}
            <form method="GET" class="relative w-full sm:w-64">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search applicant..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-navy placeholder:text-slate-400 focus:outline-none focus:border-mint focus:ring-2 focus:ring-mint/10 transition">
            </form>

        </div>

    </div>

    {{-- ============ REGISTRATIONS LIST ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100">
            <div>
                <h2 class="font-semibold text-navy text-sm">
                    {{ $filter === 'approved' ? 'Approved Applications' : ($filter === 'rejected' ? 'Rejected Applications' : 'Pending Applications') }}
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Review submitted information and documents before approval.</p>
            </div>
            <span class="text-xs text-slate-400 shrink-0">{{ $registrations->total() }} total</span>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($registrations as $registration)

                @php
                    $roleStyles = [
                        'buyer'     => ['avatar' => 'bg-sky/10 text-sky', 'badge' => 'bg-sky/10 text-sky', 'icon' => 'shopping-bag', 'label' => 'Buyer'],
                        'seller'    => ['avatar' => 'bg-coral/10 text-coral', 'badge' => 'bg-coral/10 text-coral', 'icon' => 'store', 'label' => 'Seller'],
                        'logistics' => ['avatar' => 'bg-violet-100 text-violet-600', 'badge' => 'bg-violet-100 text-violet-600', 'icon' => 'truck', 'label' => 'Logistics'],
                    ];
                    $rStyle = $roleStyles[$registration->account_type] ?? ['avatar' => 'bg-slate-100 text-slate-500', 'badge' => 'bg-slate-100 text-slate-500', 'icon' => 'user', 'label' => ucfirst($registration->account_type)];
                @endphp

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-4 hover:bg-slate-50/60 transition">

                    <div class="flex items-start sm:items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl {{ $rStyle['avatar'] }} flex items-center justify-center shrink-0">
                            <span class="text-sm font-bold">{{ $registration->initials }}</span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-navy truncate">{{ $registration->display_name }}</p>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $rStyle['badge'] }}">
                                    <x-dynamic-component :component="'lucide-' . $rStyle['icon']" class="w-3 h-3" />
                                    {{ $rStyle['label'] }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $registration->email }}</p>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                    <x-lucide-file-check class="w-3 h-3" />
                                    {{ $documentLabels[$registration->account_type] ?? 'Documents' }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                    <x-lucide-clock class="w-3 h-3" />
                                    Applied {{ $registration->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:pl-14 lg:pl-0 shrink-0">

                        <button type="button" class="registration-view-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition"
                            data-show-url="{{ route('admin.registrations.show', $registration) }}">
                            <x-lucide-eye class="w-3.5 h-3.5" />
                            View
                        </button>

                        @if ($registration->status === 'pending')
                            <button type="button" class="registration-approve-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition"
                                data-id="{{ $registration->id }}"
                                data-name="{{ $registration->display_name }}"
                                data-role="{{ $rStyle['label'] }}"
                                data-approve-url="{{ route('admin.users.approve', $registration) }}">
                                <x-lucide-check class="w-3.5 h-3.5" />
                                Approve
                            </button>
                            <button type="button" class="registration-reject-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition"
                                data-id="{{ $registration->id }}"
                                data-name="{{ $registration->display_name }}"
                                data-reject-url="{{ route('admin.users.reject', $registration) }}">
                                <x-lucide-x class="w-3.5 h-3.5" />
                                Reject
                            </button>
                        @elseif ($registration->status === 'approved')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-mint/10 text-mint-dark text-xs font-semibold">
                                <x-lucide-circle-check class="w-3.5 h-3.5" />
                                Approved
                            </span>
                        @elseif ($registration->status === 'rejected')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-coral/5 text-coral text-xs font-semibold">
                                <x-lucide-circle-x class="w-3.5 h-3.5" />
                                Rejected
                            </span>
                        @endif

                    </div>

                </div>

            @empty

                <div class="px-5 py-14 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <x-lucide-user-search class="w-5 h-5" />
                    </div>
                    <p class="text-sm font-semibold text-navy mt-3">No applications found</p>
                    <p class="text-xs text-slate-400 mt-1">Try changing the status, role, or search filter.</p>
                </div>

            @endforelse

        </div>

        {{-- ============ PAGINATION ============ --}}
        @if ($registrations->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $registrations->withQueryString()->links() }}
            </div>
        @endif

    </div>


    {{-- =========================================================
        REGISTRATION DETAILS MODAL
        Populated via fetch() from admin.registrations.show (JSON).
    ========================================================= --}}
    <div id="registrationModalOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="registrationModalPanel" class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button type="button" id="registrationModalClose" aria-label="Close"
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition">
                <x-lucide-x class="w-4 h-4" />
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-mint/15 flex items-center justify-center shrink-0">
                        <span id="modalInitials" class="text-sm font-bold text-mint-dark"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">ACCOUNT REGISTRATION</p>
                        <h2 id="modalName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                        <p id="modalEmail" class="text-xs text-slate-500 truncate mt-0.5"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span id="modalRoleBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"></span>
                    <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full">
                        <span id="modalStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                        <span id="modalStatusLabel"></span>
                    </span>
                </div>
            </div>

            {{-- BODY --}}
            <div id="modalBody" class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- LEFT COLUMN --}}
                <div class="space-y-4">

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Contact Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Phone</dt>
                                <dd id="modalPhone" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Address</dt>
                                <dd id="modalAddress" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Registration Details</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Sex</dt>
                                <dd id="modalSex" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Birthday</dt>
                                <dd id="modalBirthday" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Age</dt>
                                <dd id="modalAge" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="modalBusinessNameRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Business Name</dt>
                                <dd id="modalBusinessName" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="modalBusinessCategoryRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Line of Business</dt>
                                <dd id="modalBusinessCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Application Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Reference No.</dt>
                                <dd id="modalRegNo" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Submitted</dt>
                                <dd id="modalSubmittedAt" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div id="modalNotesWrap" class="hidden">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Admin Notes</p>
                        <p id="modalNotes" class="text-xs text-slate-600 leading-relaxed"></p>
                    </div>

                    <div id="modalRejectionWrap" class="hidden bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-coral mb-1.5">Rejection Reason</p>
                        <p id="modalRejectionReason" class="text-xs text-slate-600 leading-relaxed"></p>
                    </div>

                    <div class="bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-3">
                            <x-lucide-flag class="w-3.5 h-3.5 text-coral" />
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-coral">Reports &amp; Flags</p>
                        </div>
                        <div id="modalReports" class="space-y-2.5"></div>
                    </div>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-4">

                    <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs text-slate-500">Documents Submitted</p>
                            <p id="modalDocsValue" class="text-xl font-bold text-navy mt-0.5"></p>
                        </div>
                        <p id="modalDocsSub" class="text-[11px] text-slate-400 text-right max-w-[50%]"></p>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Submitted Documents</p>
                        <div id="modalDocuments" class="grid grid-cols-3 gap-3"></div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Recent Activity</p>
                        <ul id="modalActivity" class="space-y-3"></ul>
                    </div>

                </div>

            </div>

            {{-- LOADING STATE --}}
            <div id="modalLoading" class="hidden px-6 py-16 text-center text-sm text-slate-400">
                Loading application details...
            </div>

            {{-- FOOTER --}}
            <div id="modalFooter" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2"></div>

        </div>

    </div>


    {{-- =========================================================
        APPROVE / REJECT — CONFIRMATION MODAL
    ========================================================= --}}
    <div id="confirmModalOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="confirmModalPanel" class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150 p-6">

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            {{-- Reject-only fields --}}
            <div id="confirmRejectFields" class="hidden mb-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Reason for rejection</label>
                    <select id="confirmRejectReason" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40">
                        <option value="incomplete">Incomplete / missing documents</option>
                        <option value="unclear">Unclear or unreadable document</option>
                        <option value="mismatch">Information does not match documents</option>
                        <option value="suspicious">Suspicious or fraudulent information</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Notes</label>
                    <textarea id="confirmRejectNotes" rows="3" placeholder="Enter the details of the issue..."
                        class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40"></textarea>
                </div>
            </div>

            {{-- Approve-only field --}}
            <div id="confirmApproveFields" class="hidden mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Approval notes (optional)</label>
                <textarea id="confirmApproveNotes" rows="3" placeholder="e.g. All submitted documents are complete and valid."
                    class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-mint/40"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" id="confirmCancelBtn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" id="confirmProceedBtn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white transition-all duration-300"></button>
            </div>

        </div>

    </div>

    {{-- Hidden forms — actually submitted to the backend on confirm --}}
    <form id="approveForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="notes" id="approveFormNotes">
    </form>
    <form id="rejectForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="reason" id="rejectFormReason">
        <input type="hidden" name="notes" id="rejectFormNotes">
    </form>


    {{-- =========================================================
        MODAL DATA + BEHAVIOR — real fetch() + real form submits
    ========================================================= --}}
    <script>
        const roleBadgeClasses = {
            buyer:     'text-sky bg-sky/10',
            seller:    'text-coral bg-coral/10',
            logistics: 'text-violet-600 bg-violet-100',
        };
        const roleLabels = { buyer: 'Buyer', seller: 'Seller', logistics: 'Logistics' };

        const statusBadge = {
            pending:  { dot: 'bg-amber-600', text: 'text-amber-700', bg: 'bg-amber-100', label: 'Pending Review' },
            approved: { dot: 'bg-mint-dark', text: 'text-mint-dark', bg: 'bg-mint/10',   label: 'Approved' },
            rejected: { dot: 'bg-coral',     text: 'text-coral',     bg: 'bg-coral/10',  label: 'Rejected' },
        };

        const docStatusBadge = {
            submitted: { text: 'text-mint-dark', bg: 'bg-mint/15', label: 'Submitted' },
            missing:   { text: 'text-coral', bg: 'bg-coral/10', label: 'Missing' },
        };

        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        /* -----------------------------------------------------------
           REGISTRATION DETAILS MODAL — fetched via AJAX
        ----------------------------------------------------------- */
        const overlay     = document.getElementById('registrationModalOverlay');
        const panel       = document.getElementById('registrationModalPanel');
        const closeBtn    = document.getElementById('registrationModalClose');
        const modalFooter = document.getElementById('modalFooter');
        const modalBody   = document.getElementById('modalBody');
        const modalLoading = document.getElementById('modalLoading');

        let currentModalRegistration = null;

        async function openRegistrationModal(showUrl) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));

            modalBody.classList.add('hidden');
            modalLoading.classList.remove('hidden');
            modalFooter.innerHTML = '';

            let r;
            try {
                const res = await fetch(showUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Request failed');
                r = await res.json();
            } catch (err) {
                modalLoading.textContent = 'Failed to load application details. Please try again.';
                return;
            }

            currentModalRegistration = r;
            populateModal(r);

            modalLoading.classList.add('hidden');
            modalBody.classList.remove('hidden');
        }

        function populateModal(r) {
            document.getElementById('modalInitials').textContent = r.initials;
            document.getElementById('modalName').textContent = r.name;
            document.getElementById('modalEmail').textContent = r.email;

            const roleBadge = document.getElementById('modalRoleBadge');
            roleBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + (roleBadgeClasses[r.role] || 'text-slate-500 bg-slate-100');
            roleBadge.textContent = roleLabels[r.role] || r.role;

            const sBadge = statusBadge[r.status] || statusBadge.pending;
            document.getElementById('modalStatusBadge').className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            document.getElementById('modalStatusDot').className = 'w-1.5 h-1.5 rounded-full ' + sBadge.dot;
            document.getElementById('modalStatusLabel').textContent = sBadge.label;

            document.getElementById('modalPhone').textContent = r.phone || '—';
            document.getElementById('modalAddress').textContent = r.address || '—';
            document.getElementById('modalRegNo').textContent = r.reg_no;
            document.getElementById('modalSubmittedAt').textContent = r.submitted_at;

            document.getElementById('modalSex').textContent = r.sex || '—';
            document.getElementById('modalBirthday').textContent = r.birthday || '—';
            document.getElementById('modalAge').textContent = r.age ?? '—';

            const businessNameRow = document.getElementById('modalBusinessNameRow');
            const businessCategoryRow = document.getElementById('modalBusinessCategoryRow');

            businessNameRow.className = r.business_name ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.business_name) document.getElementById('modalBusinessName').textContent = r.business_name;

            businessCategoryRow.className = r.business_category ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.business_category) document.getElementById('modalBusinessCategory').textContent = r.business_category;

            const notesWrap = document.getElementById('modalNotesWrap');
            if (r.notes) {
                notesWrap.classList.remove('hidden');
                document.getElementById('modalNotes').textContent = r.notes;
            } else {
                notesWrap.classList.add('hidden');
            }

            const rejectionWrap = document.getElementById('modalRejectionWrap');
            if (r.status === 'rejected' && r.rejection_reason) {
                rejectionWrap.classList.remove('hidden');
                document.getElementById('modalRejectionReason').textContent = r.rejection_reason.replace(/_/g, ' ');
            } else {
                rejectionWrap.classList.add('hidden');
            }

            document.getElementById('modalDocsValue').textContent = r.docs_summary.value;
            document.getElementById('modalDocsSub').textContent = r.docs_summary.sub;

            const activityList = document.getElementById('modalActivity');
            activityList.innerHTML = '';
            if (!r.activity || r.activity.length === 0) {
                activityList.innerHTML = '<li class="text-xs text-slate-400">No activity recorded yet.</li>';
            } else {
                r.activity.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'flex gap-2.5';
                    li.innerHTML = `
                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-mint-dark shrink-0"></span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700">${item.label}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${item.time}</p>
                        </div>
                    `;
                    activityList.appendChild(li);
                });
            }

            const reportsWrap = document.getElementById('modalReports');
            reportsWrap.innerHTML = '';
            if (!r.reports || r.reports.length === 0) {
                reportsWrap.innerHTML = '<p class="text-xs text-slate-400">No reports or flags on file.</p>';
            } else {
                r.reports.forEach(rep => {
                    const item = document.createElement('div');
                    item.className = 'flex items-start gap-2.5';
                    item.innerHTML = `
                        <span class="mt-0.5 shrink-0 text-[10px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">${rep.type}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700 leading-snug">${rep.description}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${rep.date}</p>
                        </div>
                    `;
                    reportsWrap.appendChild(item);
                });
            }

            const docsGrid = document.getElementById('modalDocuments');
            docsGrid.innerHTML = '';
            (r.documents || []).forEach(doc => {
                const dBadge = docStatusBadge[doc.status] || docStatusBadge.missing;
                const isMissing = doc.status === 'missing';
                const wrapper = document.createElement(isMissing ? 'div' : 'a');
                if (!isMissing) {
                    wrapper.href = doc.url || '#';
                    wrapper.target = '_blank';
                    wrapper.rel = 'noopener noreferrer';
                }
                wrapper.className = 'block group';
                wrapper.innerHTML = `
                    <div class="w-full aspect-square rounded-lg border border-dashed ${isMissing ? 'border-slate-200 bg-slate-100/60' : 'border-slate-300 bg-slate-50/60'} flex flex-col items-center justify-center gap-1 text-slate-400 ${isMissing ? '' : 'cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark'}">
                        ${docFileIconSvg}
                        <span class="text-[9px] font-medium uppercase tracking-wide">${isMissing ? 'No File' : 'Preview'}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 text-center truncate ${isMissing ? '' : 'transition group-hover:text-mint-dark'}">${doc.label}</p>
                    <div class="flex justify-center mt-1">
                        <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full ${dBadge.bg} ${dBadge.text}">${dBadge.label}</span>
                    </div>
                `;
                docsGrid.appendChild(wrapper);
            });

            modalFooter.innerHTML = '';
            if (r.status === 'pending') {
                const approveBtn = document.createElement('button');
                approveBtn.type = 'button';
                approveBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                approveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Approve';
                approveBtn.addEventListener('click', () => openConfirmModal('approve', { id: r.id, name: r.name, role: roleLabels[r.role] }));

                const rejectBtn = document.createElement('button');
                rejectBtn.type = 'button';
                rejectBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 hover:-translate-y-0.5 transition-all duration-300';
                rejectBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg> Reject';
                rejectBtn.addEventListener('click', () => openConfirmModal('reject', { id: r.id, name: r.name, role: roleLabels[r.role] }));

                modalFooter.appendChild(rejectBtn);
                modalFooter.appendChild(approveBtn);
            }
        }

        function closeRegistrationModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.registration-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openRegistrationModal(btn.dataset.showUrl));
        });

        closeBtn.addEventListener('click', closeRegistrationModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeRegistrationModal(); });

        /* -----------------------------------------------------------
           APPROVE / REJECT — CONFIRMATION MODAL
           Submits real hidden <form>s (full page reload, session flash)
        ----------------------------------------------------------- */
        const confirmOverlay       = document.getElementById('confirmModalOverlay');
        const confirmPanel         = document.getElementById('confirmModalPanel');
        const confirmIconWrap      = document.getElementById('confirmIconWrap');
        const confirmTitle         = document.getElementById('confirmTitle');
        const confirmMessage       = document.getElementById('confirmMessage');
        const confirmProceedBtn    = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn     = document.getElementById('confirmCancelBtn');
        const confirmRejectFields  = document.getElementById('confirmRejectFields');
        const confirmApproveFields = document.getElementById('confirmApproveFields');

        const approveForm = document.getElementById('approveForm');
        const rejectForm  = document.getElementById('rejectForm');

        // URL templates — ':id' gets swapped for the real id at submit time.
        const approveUrlTemplate = "{{ route('admin.users.approve', ['user' => '__ID__']) }}";
        const rejectUrlTemplate  = "{{ route('admin.users.reject', ['user' => '__ID__']) }}";

        let activeConfirmAction = null;
        let activeConfirmTarget = null;

        const checkIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        const flagIconSvg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>';

        function openConfirmModal(action, target) {
            activeConfirmAction = action;
            activeConfirmTarget = target; // { id, name, role }

            confirmRejectFields.classList.toggle('hidden', action !== 'reject');
            confirmApproveFields.classList.toggle('hidden', action !== 'approve');

            if (action === 'approve') {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
                confirmIconWrap.innerHTML = checkIconSvg;
                confirmTitle.textContent = 'Approve this application?';
                confirmMessage.textContent = `Are you sure you want to approve ${target.name}'s ${target.role.toLowerCase()} account? This will grant them full platform access.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Approve';
                document.getElementById('confirmApproveNotes').value = '';
            } else {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral';
                confirmIconWrap.innerHTML = flagIconSvg;
                confirmTitle.textContent = 'Reject this application?';
                confirmMessage.textContent = `Reject ${target.name}'s application? They will need to reapply or resubmit their documents.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Reject';
                document.getElementById('confirmRejectReason').value = 'incomplete';
                document.getElementById('confirmRejectNotes').value = '';
            }

            confirmOverlay.classList.remove('hidden');
            confirmOverlay.classList.add('flex');
            requestAnimationFrame(() => confirmPanel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function closeConfirmModal() {
            confirmPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                confirmOverlay.classList.add('hidden');
                confirmOverlay.classList.remove('flex');
            }, 150);
        }

        confirmCancelBtn.addEventListener('click', closeConfirmModal);
        confirmOverlay.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

        // Row-level Approve/Reject buttons — already have their real URLs from blade
        document.querySelectorAll('.registration-approve-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openConfirmModal('approve', {
                    id: btn.dataset.id,
                    name: btn.dataset.name,
                    role: 'Buyer', // label not critical here; message still reads fine
                    url: btn.dataset.approveUrl,
                });
            });
        });

        document.querySelectorAll('.registration-reject-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openConfirmModal('reject', {
                    id: btn.dataset.id,
                    name: btn.dataset.name,
                    role: 'Buyer',
                    url: btn.dataset.rejectUrl,
                });
            });
        });

        confirmProceedBtn.addEventListener('click', () => {
            if (!activeConfirmTarget) return;

            if (activeConfirmAction === 'approve') {
                approveForm.action = activeConfirmTarget.url || approveUrlTemplate.replace('__ID__', activeConfirmTarget.id);
                document.getElementById('approveFormNotes').value = document.getElementById('confirmApproveNotes').value;
                approveForm.submit();
            } else {
                rejectForm.action = activeConfirmTarget.url || rejectUrlTemplate.replace('__ID__', activeConfirmTarget.id);
                document.getElementById('rejectFormReason').value = document.getElementById('confirmRejectReason').value;
                document.getElementById('rejectFormNotes').value = document.getElementById('confirmRejectNotes').value;
                rejectForm.submit();
            }
        });

        // Escape key — close whichever of the two modals is open
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeRegistrationModal();
        });
    </script>

@endsection