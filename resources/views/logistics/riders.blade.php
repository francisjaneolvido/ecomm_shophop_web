{{-- resources/views/logistics/riders.blade.php --}}

@extends('logistics.layouts')

@section('title', 'Riders — ShopHop Logistics')

@section('content')

@php
    use Illuminate\Support\Collection;

    $appsCollection    = collect($applications);
    $activeCollection  = collect($activeRiders);

    $pendingCount   = $appsCollection->count();
    $activeCount    = $activeCollection->where('status', 'active')->count();
    $suspendedCount = $activeCollection->where('status', '!=', 'active')->count();
    $flaggedCount   = $activeCollection->filter(fn ($r) => !empty($r['warnings'] ?? []))->count();

    $initialsOf = function (?string $name) {
        $parts = collect(explode(' ', trim((string) $name)))->filter();
        return $parts->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    };
@endphp

{{-- =========================================================
    PAGE HEADER
========================================================= --}}
<div class="mb-6">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy">Riders</h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Review rider applications and manage your active delivery fleet.
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-400">
            <x-lucide-truck class="w-4 h-4 text-mint-dark" />
            <span>Fleet applications and performance</span>
        </div>
    </div>
</div>

{{-- ============ SUMMARY CARDS ============ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pending Applications</p>
                <p class="text-2xl font-bold text-navy mt-1">{{ $pendingCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <x-lucide-clock-3 class="w-5 h-5" />
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-slate-400 font-medium">Active Riders</p>
                <p class="text-2xl font-bold text-navy mt-1">{{ $activeCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-mint/10 text-mint-dark flex items-center justify-center">
                <x-lucide-bike class="w-5 h-5" />
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-slate-400 font-medium">Suspended</p>
                <p class="text-2xl font-bold text-navy mt-1">{{ $suspendedCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                <x-lucide-pause-circle class="w-5 h-5" />
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-slate-400 font-medium">Flagged</p>
                <p class="text-2xl font-bold text-navy mt-1">{{ $flaggedCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center">
                <x-lucide-flag class="w-5 h-5" />
            </div>
        </div>
    </div>

</div>

{{-- ============ TABS + SEARCH ============ --}}
<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" data-tab-btn="applications"
            class="tab-pill-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition bg-navy text-white">
            Pending Applications <span class="ml-1 opacity-70">({{ $pendingCount }})</span>
        </button>
        <button type="button" data-tab-btn="active"
            class="tab-pill-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition text-slate-500 hover:bg-slate-100">
            Active Riders <span class="ml-1 opacity-70">({{ $activeCount }})</span>
        </button>
    </div>

    {{-- SEARCH --}}
    <form class="relative w-full sm:w-64" onsubmit="return false;">
        <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
        <input type="text" id="rider-search" placeholder="Search by rider name..."
            class="w-full pl-9 pr-9 py-2 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-navy placeholder:text-slate-400 focus:outline-none focus:border-mint focus:ring-2 focus:ring-mint/10 transition">
        <button type="button" id="rider-search-clear" aria-label="Clear search"
            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-navy hover:bg-slate-100 transition">
            <x-lucide-x class="w-3 h-3" />
        </button>
    </form>

</div>

{{-- =========================================================
    APPLICATIONS TAB
========================================================= --}}
<div data-tab-panel="applications" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100">
        <div>
            <h2 class="font-semibold text-navy text-sm">Pending Applications</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Review rider details and documents before approval.</p>
        </div>
        <span class="text-xs text-slate-400 shrink-0">{{ $pendingCount }} total</span>
    </div>

    <div class="divide-y divide-slate-100" data-rows-container>

        @forelse ($applications as $rider)

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-4 hover:bg-slate-50/60 transition"
                 data-rider-name="{{ $rider['name'] }}">

                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold">{{ $initialsOf($rider['name']) }}</span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-navy truncate">{{ $rider['name'] }}</p>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-600">
                                <x-lucide-bike class="w-3 h-3" />
                                Rider
                            </span>
                            <div data-interview-badge="{{ $rider['id'] }}" class="hidden"></div>
                        </div>

                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $rider['vehicle'] }} · {{ $rider['plate_number'] ?? '—' }}</p>

                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                            @foreach ($rider['docs'] as $doc => $ok)
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold pl-2 {{ $ok ? 'pr-1' : 'pr-2' }} py-1 rounded-md whitespace-nowrap {{ $ok ? 'bg-slate-100 text-slate-600' : 'border border-coral/30 text-coral' }}">
                                    {{ $doc }} {{ $ok ? '✓' : 'missing' }}
                                    @if ($ok)
                                        <button type="button"
                                            data-view-doc
                                            data-doc-name="{{ $doc }}"
                                            data-doc-url="{{ $rider['doc_files'][$doc] ?? '' }}"
                                            class="ml-0.5 w-5 h-5 flex items-center justify-center rounded hover:bg-white text-slate-400 hover:text-mint-dark transition"
                                            aria-label="View {{ $doc }}">
                                            <x-lucide-eye class="w-3 h-3" />
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:pl-14 lg:pl-0 shrink-0 flex-wrap">

                    <button type="button" data-chat-btn data-name="{{ $rider['name'] }}" data-id="app-{{ $rider['id'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition"
                        aria-label="Chat with {{ $rider['name'] }}">
                        <x-lucide-message-circle class="w-3.5 h-3.5" />
                        Chat
                    </button>

                    {{-- "View" button (dating "Review") — binuksan ang View modal --}}
                    <button type="button" data-view-btn data-rider-id="{{ $rider['id'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                        <x-lucide-eye class="w-3.5 h-3.5" />
                        View
                    </button>

                    <button type="button" data-interview-btn data-action="schedule" data-rider-id="{{ $rider['id'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                        <x-lucide-calendar class="w-3.5 h-3.5" />
                        Schedule interview
                    </button>

                    <button type="button" data-approve-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}"
                        id="approve-btn-{{ $rider['id'] }}" disabled title="Schedule and complete an interview first"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark/40 cursor-not-allowed transition">
                        <x-lucide-check class="w-3.5 h-3.5" />
                        Approve
                    </button>

                    <button type="button" data-disapprove-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition">
                        <x-lucide-x class="w-3.5 h-3.5" />
                        Disapprove
                    </button>
                </div>

                <form id="approve-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.approve', $rider['id']) }}" class="hidden">
                    @csrf
                </form>
                <form id="disapprove-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.disapprove', $rider['id']) }}" class="hidden">
                    @csrf
                    <input type="hidden" name="reason" data-disapprove-reason-input>
                </form>

            </div>

        @empty

            <div class="px-5 py-14 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                    <x-lucide-user-search class="w-5 h-5" />
                </div>
                <p class="text-sm font-semibold text-navy mt-3">No pending applications</p>
                <p class="text-xs text-slate-400 mt-1">New rider applications will show up here.</p>
            </div>

        @endforelse

    </div>
</div>

{{-- =========================================================
    ACTIVE RIDERS TAB
========================================================= --}}
<div data-tab-panel="active" class="hidden bg-white rounded-2xl border border-slate-200 overflow-hidden">

    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100">
        <div>
            <h2 class="font-semibold text-navy text-sm">Active Riders</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Monitor performance, zone assignment, and account status.</p>
        </div>
        <span class="text-xs text-slate-400 shrink-0">{{ $activeCollection->count() }} total</span>
    </div>

    <div class="divide-y divide-slate-100" data-rows-container>

        @forelse ($activeRiders as $rider)

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-4 hover:bg-slate-50/60 transition cursor-pointer"
                 data-rider-row data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}">

                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold">{{ $initialsOf($rider['name']) }}</span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-navy truncate">{{ $rider['name'] }}</p>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $rider['status'] === 'active' ? 'bg-mint/10 text-mint-dark' : 'bg-amber-100 text-amber-700' }}">
                                <x-dynamic-component :component="$rider['status'] === 'active' ? 'lucide-circle-check' : 'lucide-pause-circle'" class="w-3 h-3" />
                                {{ ucfirst($rider['status']) }}
                            </span>
                            @if (!empty($rider['warnings']))
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-coral/10 text-coral px-2 py-0.5 rounded-full">
                                    <x-lucide-flag class="w-3 h-3" />
                                    {{ count($rider['warnings']) }} flag{{ count($rider['warnings']) > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $rider['vehicle'] }} · {{ $rider['zone'] }}</p>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                <x-lucide-package-check class="w-3 h-3" />
                                {{ $rider['completion'] }}% completion
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                <x-lucide-star class="w-3 h-3" />
                                {{ number_format($rider['rating'], 1) }} rating
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:pl-14 lg:pl-0 shrink-0" onclick="event.stopPropagation()">

                    <button type="button" data-chat-btn data-name="{{ $rider['name'] }}" data-id="active-{{ $rider['id'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition"
                        aria-label="Chat with {{ $rider['name'] }}">
                        <x-lucide-message-circle class="w-3.5 h-3.5" />
                        Chat
                    </button>

                    <button type="button" data-warning-btn data-rider-id="{{ $rider['id'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 border border-amber-200 hover:bg-amber-50 transition"
                        aria-label="Warn {{ $rider['name'] }}">
                        <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                        Warn
                    </button>

                    <button type="button" data-status-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}" data-rider-status="{{ $rider['status'] }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                        <x-dynamic-component :component="$rider['status'] === 'active' ? 'lucide-pause' : 'lucide-play'" class="w-3.5 h-3.5" />
                        {{ $rider['status'] === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                </div>

                <form id="suspend-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.suspend', $rider['id']) }}" class="hidden">@csrf</form>
                <form id="activate-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.activate', $rider['id']) }}" class="hidden">@csrf</form>
                <form id="warn-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.warn', $rider['id']) }}" class="hidden">
                    @csrf
                    <input type="hidden" name="type" data-warn-type-input>
                    <input type="hidden" name="severity" data-warn-severity-input>
                    <input type="hidden" name="details" data-warn-details-input>
                </form>

            </div>

        @empty

            <div class="px-5 py-14 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                    <x-lucide-bike class="w-5 h-5" />
                </div>
                <p class="text-sm font-semibold text-navy mt-3">No active riders yet</p>
                <p class="text-xs text-slate-400 mt-1">Approved riders will appear here.</p>
            </div>

        @endforelse

    </div>
</div>

{{-- =========================================================
    MODALS
========================================================= --}}

{{-- Document Viewer Modal --}}
<div id="modal-doc-viewer" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>
        <button type="button" class="modal-close absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition" data-close="modal-doc-viewer" aria-label="Close">
            <x-lucide-x class="w-4 h-4" />
        </button>
        <div class="px-6 pt-8 pb-2">
            <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">DOCUMENT</p>
            <h3 id="doc-viewer-title" class="font-bold text-navy text-lg">Document</h3>
        </div>
        <div class="p-6">
            <div id="doc-viewer-body" class="bg-slate-50 rounded-xl min-h-[300px] flex items-center justify-center overflow-hidden">
                <p class="text-slate-400 text-sm">No file available.</p>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================
    VIEW APPLICATION MODAL
    Ginaya ang exact chrome ng Account Registrations details
    modal — accent bar, avatar initials, role/status badges,
    2-column body (Contact / Registration / Application info /
    Notes / Reports sa kaliwa; Docs count / Docs grid / Activity
    sa kanan), floating footer actions.
========================================================= --}}
<div id="modal-view" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">

    <div id="viewModalPanel" class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

        <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

        <button type="button" class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition" data-close="modal-view" aria-label="Close">
            <x-lucide-x class="w-4 h-4" />
        </button>

        {{-- HEADER --}}
        <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-mint/15 flex items-center justify-center shrink-0">
                    <span id="viewInitials" class="text-sm font-bold text-mint-dark"></span>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">RIDER APPLICATION</p>
                    <h2 id="viewName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                    <p id="viewEmail" class="text-xs text-slate-500 truncate mt-0.5"></p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-violet-100 text-violet-600">
                    Rider
                </span>
                <span id="viewStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full">
                    <span id="viewStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                    <span id="viewStatusLabel"></span>
                </span>
            </div>
        </div>

        {{-- BODY --}}
        <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- LEFT COLUMN --}}
            <div class="space-y-4">

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Contact Information</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Phone</dt>
                            <dd id="viewPhone" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Address</dt>
                            <dd id="viewAddress" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Registration Details</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Sex</dt>
                            <dd id="viewSex" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Birthday</dt>
                            <dd id="viewBirthday" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Age</dt>
                            <dd id="viewAge" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Vehicle</dt>
                            <dd id="viewVehicle" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Plate Number</dt>
                            <dd id="viewPlate" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Application Information</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Reference No.</dt>
                            <dd id="viewRegNo" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Submitted</dt>
                            <dd id="viewSubmittedAt" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Admin Notes</p>
                    <p id="viewNotes" class="text-xs text-slate-600 leading-relaxed"></p>
                </div>

                <div class="bg-coral/5 border border-coral/15 rounded-xl p-4">
                    <div class="flex items-center gap-1.5 mb-3">
                        <x-lucide-flag class="w-3.5 h-3.5 text-coral" />
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-coral">Reports &amp; Flags</p>
                    </div>
                    <div id="viewReports" class="space-y-2.5"></div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-4">

                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">Documents Submitted</p>
                        <p id="viewDocsValue" class="text-xl font-bold text-navy mt-0.5"></p>
                    </div>
                    <p id="viewDocsSub" class="text-[11px] text-slate-400 text-right max-w-[50%]"></p>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Submitted Documents</p>
                    <div id="viewDocuments" class="grid grid-cols-3 gap-3"></div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Recent Activity</p>
                    <ul id="viewActivity" class="space-y-3"></ul>
                </div>

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="px-6 py-4 border-t border-slate-100 flex flex-wrap justify-end gap-2">
            <button type="button" id="view-chat-btn" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                <x-lucide-message-circle class="w-3.5 h-3.5" />
                Chat applicant
            </button>
            <button type="button" id="view-schedule-btn" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                <x-lucide-calendar class="w-3.5 h-3.5" />
                Schedule interview
            </button>
            <button type="button" id="view-disapprove-btn" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition">
                <x-lucide-x class="w-3.5 h-3.5" />
                Disapprove
            </button>
            <button type="button" id="view-approve-btn" disabled title="Schedule and complete an interview first"
                class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark/40 cursor-not-allowed transition">
                <x-lucide-check class="w-3.5 h-3.5" />
                Approve
            </button>
        </div>

    </div>

</div>

{{-- Schedule Interview Modal --}}
<div id="modal-schedule" class="modal-overlay fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="relative w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h3 class="font-bold text-navy text-sm">Schedule interview</h3>
            <button type="button" class="text-slate-400 hover:text-navy" data-close="modal-schedule" aria-label="Close">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
        <div class="px-5 pb-5 space-y-3">
            <p class="text-xs text-slate-500" id="schedule-for-name"></p>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Date</label>
                <input type="date" id="schedule-date" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Time</label>
                <input type="time" id="schedule-time" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Notes for applicant (optional)</label>
                <textarea id="schedule-notes" rows="2" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-mint/20" placeholder="e.g. Bring original IDs"></textarea>
            </div>
            <p class="text-[11px] text-slate-400">An interview invite will be emailed to the applicant.</p>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition" data-close="modal-schedule">Cancel</button>
            <button type="button" id="schedule-send-btn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition">Send invite</button>
        </div>
    </div>
</div>

{{-- Approve Confirm Modal --}}
<div id="modal-confirm-approve" class="modal-overlay fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark">
            <x-lucide-check class="w-5 h-5" />
        </div>
        <h3 class="text-base font-bold text-navy mb-1.5">Approve applicant?</h3>
        <p class="text-sm text-slate-500 leading-relaxed mb-4">
            <span id="approve-confirm-name" class="font-semibold text-navy"></span> will be notified via email and added to your active fleet.
        </p>
        <div class="flex items-center justify-end gap-2">
            <button type="button" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition" data-close="modal-confirm-approve">Cancel</button>
            <button type="button" id="approve-confirm-btn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition">Yes, approve</button>
        </div>
    </div>
</div>

{{-- Disapprove Confirm Modal --}}
<div id="modal-confirm-disapprove" class="modal-overlay fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral">
            <x-lucide-x class="w-5 h-5" />
        </div>
        <h3 class="text-base font-bold text-navy mb-1.5">Disapprove applicant?</h3>
        <p class="text-sm text-slate-500 leading-relaxed mb-3">
            <span id="disapprove-confirm-name" class="font-semibold text-navy"></span> will receive an email with the reason below.
        </p>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Reason <span class="text-coral">*</span></label>
        <textarea id="disapprove-reason" rows="3" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-coral/30" placeholder="e.g. Incomplete or unreadable documents submitted"></textarea>
        <p id="disapprove-reason-error" class="hidden text-[11px] text-coral mt-1">Reason is required.</p>
        <div class="flex items-center justify-end gap-2 mt-4">
            <button type="button" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition" data-close="modal-confirm-disapprove">Cancel</button>
            <button type="button" id="disapprove-confirm-btn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition">Yes, disapprove</button>
        </div>
    </div>
</div>

{{-- Suspend / Activate Confirm Modal --}}
<div id="modal-confirm-status" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6">
        <div id="status-confirm-icon-wrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-amber-100 text-amber-600">
            <x-lucide-pause-circle class="w-5 h-5" />
        </div>
        <h3 id="status-confirm-title" class="text-base font-bold text-navy mb-1.5">Suspend rider?</h3>
        <p class="text-sm text-slate-500 leading-relaxed mb-4" id="status-confirm-body"></p>
        <div class="flex items-center justify-end gap-2">
            <button type="button" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition" data-close="modal-confirm-status">Cancel</button>
            <button type="button" id="status-confirm-btn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-navy hover:opacity-90 transition">Confirm</button>
        </div>
    </div>
</div>

{{-- Rider Detail Modal (Active riders) --}}
<div id="modal-rider-detail" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl">

        <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

        <button type="button" class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition" data-close="modal-rider-detail" aria-label="Close">
            <x-lucide-x class="w-4 h-4" />
        </button>

        <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
            <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">ACTIVE RIDER</p>
            <h2 id="detail-rider-name" class="text-xl sm:text-2xl font-bold text-navy"></h2>
            <p id="detail-rider-sub" class="text-xs text-slate-500 mt-0.5"></p>
        </div>

        <div class="flex gap-5 px-6 pt-4 border-b border-slate-100 overflow-x-auto whitespace-nowrap">
            <button type="button" data-detail-tab-btn="history" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-mint-dark text-mint-dark">Delivery history</button>
            <button type="button" data-detail-tab-btn="documents" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-navy transition">Documents</button>
            <button type="button" data-detail-tab-btn="warnings" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-navy transition">Warnings</button>
        </div>

        <div class="px-6 py-5">
            {{-- History panel --}}
            <div data-detail-tab-panel="history">
                <div class="flex justify-end mb-3">
                    <select id="history-filter" class="text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 text-slate-600 focus:outline-none focus:ring-2 focus:ring-mint/20">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="month">By month</option>
                    </select>
                </div>
                <div id="history-list" class="space-y-2"></div>
            </div>

            {{-- Documents panel --}}
            <div data-detail-tab-panel="documents" class="hidden">
                <div id="documents-list" class="grid grid-cols-3 gap-3"></div>
            </div>

            {{-- Warnings panel --}}
            <div data-detail-tab-panel="warnings" class="hidden">
                <div id="warnings-list" class="space-y-2"></div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex flex-wrap justify-end gap-2">
            <button type="button" id="detail-chat-btn" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition">
                <x-lucide-message-circle class="w-3.5 h-3.5" />
                Chat rider
            </button>
            <button type="button" id="detail-status-btn" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition"></button>
        </div>
    </div>
</div>

{{-- Chat Modal --}}
<div id="modal-chat" class="modal-overlay fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="w-full max-w-sm h-[520px] bg-white rounded-2xl border border-slate-200 shadow-xl flex flex-col overflow-hidden">
        <div class="h-1.5 bg-mint-dark"></div>
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 id="chat-with-name" class="font-bold text-navy text-sm"></h3>
            <button type="button" class="text-slate-400 hover:text-navy" data-close="modal-chat" aria-label="Close">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50/60">
            <p class="text-center text-slate-400 text-xs mt-6">No messages yet. Say hi 👋</p>
        </div>
        <div class="p-3 border-t border-slate-100 flex gap-2">
            <input type="text" id="chat-input" placeholder="Type a message..." class="flex-1 rounded-full border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mint/20">
            <button type="button" id="chat-send-btn" class="w-9 h-9 flex items-center justify-center rounded-full bg-mint-dark hover:opacity-90 text-white shrink-0 transition" aria-label="Send">
                <x-lucide-send class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>
</div>

{{-- Warning Modal --}}
<div id="modal-warning" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
        <div class="h-1.5 bg-amber-500"></div>
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-navy text-sm">Issue warning</h3>
                <p id="warning-for-name" class="text-xs text-slate-500 mt-0.5"></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-navy" data-close="modal-warning" aria-label="Close">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2">Suggested, based on performance</p>
                <div id="warning-suggestions" class="space-y-1.5"></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Violation type</label>
                <select id="warning-type" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <option value="">Select a type...</option>
                    <option value="Late Delivery">Late Delivery</option>
                    <option value="No-Show / Unfulfilled Delivery">No-Show / Unfulfilled Delivery</option>
                    <option value="Customer Complaint">Customer Complaint</option>
                    <option value="Damaged / Mishandled Package">Damaged / Mishandled Package</option>
                    <option value="Falsified Proof of Delivery">Falsified Proof of Delivery</option>
                    <option value="Reckless Driving Report">Reckless Driving Report</option>
                    <option value="Unauthorized Route Deviation">Unauthorized Route Deviation</option>
                    <option value="Repeated Violations">Repeated Violations</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Severity</label>
                <div class="flex gap-2">
                    <label class="flex-1">
                        <input type="radio" name="warning-severity" value="minor" class="peer sr-only" checked>
                        <span class="block text-center text-xs font-semibold py-2 rounded-lg border border-slate-200 text-slate-500 peer-checked:bg-amber-100 peer-checked:border-amber-300 peer-checked:text-amber-700 cursor-pointer transition">Minor</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="warning-severity" value="major" class="peer sr-only">
                        <span class="block text-center text-xs font-semibold py-2 rounded-lg border border-slate-200 text-slate-500 peer-checked:bg-coral/10 peer-checked:border-coral/30 peer-checked:text-coral cursor-pointer transition">Major</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Details</label>
                <textarea id="warning-details" rows="3" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="e.g. 3 late deliveries this week, customer flagged rudeness"></textarea>
                <p id="warning-details-error" class="hidden text-[11px] text-coral mt-1">Please select a type and add details.</p>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition" data-close="modal-warning">Cancel</button>
            <button type="button" id="warning-confirm-btn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-amber-600 hover:opacity-90 transition">Issue warning</button>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container" class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2 items-end"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // -----------------------------------------------------------
        // Raw data from the backend
        // -----------------------------------------------------------
        const APPLICATIONS = @json($applications);
        const ACTIVE_RIDERS = @json($activeRiders);
        const appsById = Object.fromEntries(APPLICATIONS.map(r => [r.id, r]));
        const activeById = Object.fromEntries(ACTIVE_RIDERS.map(r => [r.id, r]));

        function initialsOf(name) {
            return (name || '').trim().split(/\s+/).slice(0, 2).map(p => p.charAt(0).toUpperCase()).join('');
        }

        // -----------------------------------------------------------
        // Interview gating — kailangan munang "completed" ang interview
        // bago mag-enable ang Approve button.
        // -----------------------------------------------------------
        const interviewState = {}; // { [riderId]: { status: 'scheduled'|'completed', date, time } }
        let pendingScheduleId = null;

        function renderInterviewState(id) {
            const state = interviewState[id] || { status: 'none' };

            const badge = document.querySelector('[data-interview-badge="' + id + '"]');
            if (badge) {
                if (state.status === 'scheduled') {
                    badge.textContent = 'Interview: ' + state.date + ' ' + state.time;
                    badge.className = 'inline-block text-[10px] font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full';
                } else if (state.status === 'completed') {
                    badge.textContent = 'Interview completed ✓';
                    badge.className = 'inline-block text-[10px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full';
                } else {
                    badge.textContent = '';
                    badge.className = 'hidden';
                }
            }

            const interviewBtn = document.querySelector('[data-interview-btn][data-rider-id="' + id + '"]');
            if (interviewBtn) {
                if (state.status === 'scheduled') {
                    interviewBtn.innerHTML = interviewBtn.innerHTML.replace(/Schedule interview|Reschedule interview/, 'Mark interview done');
                    interviewBtn.dataset.action = 'mark-done';
                    interviewBtn.classList.remove('text-slate-500', 'border-slate-200');
                    interviewBtn.classList.add('text-mint-dark', 'border-mint/40', 'bg-mint/5');
                } else if (state.status === 'completed') {
                    interviewBtn.innerHTML = interviewBtn.innerHTML.replace(/Mark interview done|Schedule interview/, 'Reschedule interview');
                    interviewBtn.dataset.action = 'schedule';
                    interviewBtn.classList.remove('text-mint-dark', 'border-mint/40', 'bg-mint/5');
                    interviewBtn.classList.add('text-slate-500', 'border-slate-200');
                } else {
                    interviewBtn.innerHTML = interviewBtn.innerHTML.replace(/Mark interview done|Reschedule interview/, 'Schedule interview');
                    interviewBtn.dataset.action = 'schedule';
                }
            }

            const approveBtn = document.getElementById('approve-btn-' + id);
            const canApprove = state.status === 'completed';
            if (approveBtn) {
                approveBtn.disabled = !canApprove;
                approveBtn.title = canApprove ? '' : 'Schedule and complete an interview first';
                approveBtn.classList.toggle('bg-mint-dark', canApprove);
                approveBtn.classList.toggle('hover:opacity-90', canApprove);
                approveBtn.classList.toggle('bg-mint-dark/40', !canApprove);
                approveBtn.classList.toggle('cursor-not-allowed', !canApprove);
            }

            if (currentViewId === id) {
                const viewApproveBtn = document.getElementById('view-approve-btn');
                const statusBadge = document.getElementById('viewStatusBadge');
                const statusDot = document.getElementById('viewStatusDot');
                const statusLabel = document.getElementById('viewStatusLabel');

                if (viewApproveBtn) {
                    viewApproveBtn.disabled = !canApprove;
                    viewApproveBtn.title = canApprove ? '' : 'Schedule and complete an interview first';
                    viewApproveBtn.classList.toggle('bg-mint-dark', canApprove);
                    viewApproveBtn.classList.toggle('hover:opacity-90', canApprove);
                    viewApproveBtn.classList.toggle('bg-mint-dark/40', !canApprove);
                    viewApproveBtn.classList.toggle('cursor-not-allowed', !canApprove);
                }

                if (statusBadge && statusDot && statusLabel) {
                    if (state.status === 'scheduled') {
                        statusBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700';
                        statusDot.className = 'w-1.5 h-1.5 rounded-full bg-amber-600';
                        statusLabel.textContent = 'Interview Scheduled';
                    } else if (state.status === 'completed') {
                        statusBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-mint/10 text-mint-dark';
                        statusDot.className = 'w-1.5 h-1.5 rounded-full bg-mint-dark';
                        statusLabel.textContent = 'Interview Completed';
                    } else {
                        statusBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700';
                        statusDot.className = 'w-1.5 h-1.5 rounded-full bg-amber-600';
                        statusLabel.textContent = 'Pending Review';
                    }
                }
            }
        }

        document.querySelectorAll('[data-interview-btn]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const id = btn.dataset.riderId;
                const action = btn.dataset.action || 'schedule';

                if (action === 'mark-done') {
                    const existing = interviewState[id] || {};
                    interviewState[id] = { status: 'completed', date: existing.date, time: existing.time };
                    renderInterviewState(id);
                    showToast('Interview marked as completed. You can now approve this applicant.', 'success');
                    return;
                }

                pendingScheduleId = id;
                const r = appsById[id];
                document.getElementById('schedule-for-name').textContent = 'For ' + r.name;
                document.getElementById('schedule-date').value = '';
                document.getElementById('schedule-time').value = '';
                document.getElementById('schedule-notes').value = '';
                openModal('modal-schedule');
            });
        });

        // -----------------------------------------------------------
        // Outer tabs (Applications / Active)
        // -----------------------------------------------------------
        const tabButtons = document.querySelectorAll('[data-tab-btn]');
        const tabPanels = document.querySelectorAll('[data-tab-panel]');
        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabButtons.forEach(function (b) {
                    b.classList.remove('bg-navy', 'text-white');
                    b.classList.add('text-slate-500', 'hover:bg-slate-100');
                });
                btn.classList.add('bg-navy', 'text-white');
                btn.classList.remove('text-slate-500', 'hover:bg-slate-100');
                tabPanels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.tabPanel !== btn.dataset.tabBtn);
                });
            });
        });

        // -----------------------------------------------------------
        // Generic modal open/close
        // -----------------------------------------------------------
        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.classList.add('flex');
            el.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            const panel = el.querySelector('.translate-y-2');
            if (panel) requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            const panel = el.querySelector('.translate-y-2') || el.querySelector('[id$="Panel"]');
            el.classList.add('hidden');
            el.classList.remove('flex');
            el.setAttribute('aria-hidden', 'true');
            const anyOpen = document.querySelector('.modal-overlay.flex');
            if (!anyOpen) document.body.style.overflow = '';
        }
        document.querySelectorAll('[data-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { closeModal(btn.dataset.close); });
        });
        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal(overlay.id);
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.flex').forEach(function (el) { closeModal(el.id); });
            }
        });

        // -----------------------------------------------------------
        // Toast
        // -----------------------------------------------------------
        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-mint-dark text-white',
                error: 'bg-coral text-white',
                info: 'bg-navy text-white',
            };
            const toast = document.createElement('div');
            toast.className = `${colors[type] || colors.info} text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg transition-all duration-300 opacity-0 translate-y-2`;
            toast.textContent = message;
            document.getElementById('toast-container').appendChild(toast);
            requestAnimationFrame(function () {
                toast.classList.remove('opacity-0', 'translate-y-2');
            });
            setTimeout(function () {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(function () { toast.remove(); }, 300);
            }, 3200);
        }

        // -----------------------------------------------------------
        // Document viewer
        // -----------------------------------------------------------
        function openDocViewer(name, url) {
            document.getElementById('doc-viewer-title').textContent = name;
            const body = document.getElementById('doc-viewer-body');
            if (!url) {
                body.innerHTML = '<p class="text-slate-400 text-sm p-10">No file available.</p>';
            } else if (url.toLowerCase().endsWith('.pdf')) {
                body.innerHTML = `<iframe src="${url}" class="w-full h-[70vh]"></iframe>`;
            } else {
                body.innerHTML = `<img src="${url}" alt="${name}" class="max-w-full max-h-[70vh] mx-auto">`;
            }
            openModal('modal-doc-viewer');
        }
        document.querySelectorAll('[data-view-doc]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openDocViewer(btn.dataset.docName, btn.dataset.docUrl);
            });
        });

        // -----------------------------------------------------------
        // VIEW modal (dating Review modal — ginaya ang Account
        // Registrations details modal layout)
        // -----------------------------------------------------------
        let currentViewId = null;

        function buildAddress(r) {
            return [r.house_number, r.street, r.barangay, r.municipality, r.province]
                .filter(Boolean)
                .join(', ') || '—';
        }

        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        function openView(riderId) {
            const r = appsById[riderId];
            if (!r) return;
            currentViewId = riderId;

            const fullName = r.name || [r.first_name, r.middle_initial, r.last_name].filter(Boolean).join(' ');

            document.getElementById('viewInitials').textContent = initialsOf(fullName);
            document.getElementById('viewName').textContent = fullName;
            document.getElementById('viewEmail').textContent = r.email || '';

            document.getElementById('viewPhone').textContent = r.contact_no || '—';
            document.getElementById('viewAddress').textContent = buildAddress(r);

            document.getElementById('viewSex').textContent = r.sex || '—';
            document.getElementById('viewBirthday').textContent = r.birthday || '—';
            document.getElementById('viewAge').textContent = r.age ?? '—';
            document.getElementById('viewVehicle').textContent = r.vehicle || '—';
            document.getElementById('viewPlate').textContent = r.plate_number || '—';

            document.getElementById('viewRegNo').textContent = '#' + String(r.id).padStart(6, '0');
            document.getElementById('viewSubmittedAt').textContent = r.submitted_at || '—';

            document.getElementById('viewNotes').textContent = r.notes || 'No additional notes.';

            // Reports & Flags — walang report data pa sa applications, empty state muna
            const reportsWrap = document.getElementById('viewReports');
            reportsWrap.innerHTML = (r.reports && r.reports.length)
                ? r.reports.map(function (rep) {
                    return `<div class="flex items-start gap-2.5">
                        <span class="mt-0.5 shrink-0 text-[10px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">${rep.type}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700 leading-snug">${rep.description}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${rep.date}</p>
                        </div>
                    </div>`;
                }).join('')
                : '<p class="text-xs text-slate-400">No reports or flags on file.</p>';

            // Documents Submitted count + grid
            const docs = r.docs || {};
            const docKeys = Object.keys(docs);
            const submittedCount = docKeys.filter(k => docs[k]).length;
            const totalCount = docKeys.length;

            document.getElementById('viewDocsValue').textContent = submittedCount + '/' + totalCount;
            document.getElementById('viewDocsSub').textContent = submittedCount === totalCount
                ? 'All documents complete'
                : (totalCount - submittedCount) + ' document(s) incomplete';

            const docsGrid = document.getElementById('viewDocuments');
            docsGrid.innerHTML = docKeys.map(function (doc) {
                const ok = docs[doc];
                const url = (r.doc_files || {})[doc] || '';
                const wrapperTag = ok ? 'a' : 'div';
                const attrs = ok ? `href="${url || '#'}" target="_blank" rel="noopener noreferrer"` : '';
                return `<${wrapperTag} ${attrs} class="block group">
                    <div class="w-full aspect-square rounded-lg border border-dashed ${ok ? 'border-slate-300 bg-slate-50/60' : 'border-slate-200 bg-slate-100/60'} flex flex-col items-center justify-center gap-1 text-slate-400 ${ok ? 'cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark' : ''}">
                        ${docFileIconSvg}
                        <span class="text-[9px] font-medium uppercase tracking-wide">${ok ? 'Preview' : 'No File'}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 text-center truncate ${ok ? 'transition group-hover:text-mint-dark' : ''}">${doc}</p>
                    <div class="flex justify-center mt-1">
                        <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full ${ok ? 'bg-mint/15 text-mint-dark' : 'bg-coral/10 text-coral'}">${ok ? 'Submitted' : 'Missing'}</span>
                    </div>
                </${wrapperTag}>`;
            }).join('');

            // Recent Activity — dynamic, based on submitted docs + registration
            const activityList = document.getElementById('viewActivity');
            const activityItems = docKeys.filter(k => docs[k]).map(function (doc) {
                return { label: 'Submitted ' + doc, time: r.submitted_at || '' };
            });
            activityItems.push({ label: 'Registered as rider', time: r.submitted_at || '' });

            activityList.innerHTML = activityItems.map(function (item) {
                return `<li class="flex gap-2.5">
                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-mint-dark shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-700">${item.label}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">${item.time}</p>
                    </div>
                </li>`;
            }).join('');

            renderInterviewState(riderId);

            openModal('modal-view');
        }

        document.querySelectorAll('[data-view-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () { openView(btn.dataset.riderId); });
        });

        document.getElementById('view-approve-btn').addEventListener('click', function () {
            const r = appsById[currentViewId];
            openApproveConfirm(currentViewId, r.name);
        });
        document.getElementById('view-disapprove-btn').addEventListener('click', function () {
            const r = appsById[currentViewId];
            openDisapproveConfirm(currentViewId, r.name);
        });
        document.getElementById('view-schedule-btn').addEventListener('click', function () {
            const r = appsById[currentViewId];
            document.getElementById('schedule-for-name').textContent = `For ${r.name}`;
            document.getElementById('schedule-date').value = '';
            document.getElementById('schedule-time').value = '';
            document.getElementById('schedule-notes').value = '';
            openModal('modal-schedule');
        });
        document.getElementById('view-chat-btn').addEventListener('click', function () {
            const r = appsById[currentViewId];
            openChat(r.name, 'app-' + currentViewId);
        });

        // Schedule interview — UX only
        document.getElementById('schedule-send-btn').addEventListener('click', function () {
            const date = document.getElementById('schedule-date').value;
            const time = document.getElementById('schedule-time').value;
            if (!date || !time) {
                showToast('Please set a date and time first.', 'error');
                return;
            }
            if (pendingScheduleId) {
                interviewState[pendingScheduleId] = { status: 'scheduled', date, time };
                renderInterviewState(pendingScheduleId);
            }
            closeModal('modal-schedule');
            showToast('Interview invite queued — email sending isn\'t wired up yet.', 'info');
        });

        // -----------------------------------------------------------
        // Approve / Disapprove confirm
        // -----------------------------------------------------------
        let pendingApproveId = null;
        let pendingDisapproveId = null;

        function openApproveConfirm(id, name) {
            pendingApproveId = id;
            document.getElementById('approve-confirm-name').textContent = name;
            openModal('modal-confirm-approve');
        }
        function openDisapproveConfirm(id, name) {
            pendingDisapproveId = id;
            document.getElementById('disapprove-confirm-name').textContent = name;
            document.getElementById('disapprove-reason').value = '';
            document.getElementById('disapprove-reason-error').classList.add('hidden');
            openModal('modal-confirm-disapprove');
        }

        document.querySelectorAll('[data-approve-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () { openApproveConfirm(btn.dataset.riderId, btn.dataset.riderName); });
        });
        document.querySelectorAll('[data-disapprove-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () { openDisapproveConfirm(btn.dataset.riderId, btn.dataset.riderName); });
        });

        document.getElementById('approve-confirm-btn').addEventListener('click', function () {
            const form = document.getElementById('approve-form-' + pendingApproveId);
            closeModal('modal-confirm-approve');
            closeModal('modal-view');
            if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
            showToast('Applicant approved.', 'success');
        });

        document.getElementById('disapprove-confirm-btn').addEventListener('click', function () {
            const reason = document.getElementById('disapprove-reason').value.trim();
            if (!reason) {
                document.getElementById('disapprove-reason-error').classList.remove('hidden');
                return;
            }
            const form = document.getElementById('disapprove-form-' + pendingDisapproveId);
            if (form) {
                form.querySelector('[data-disapprove-reason-input]').value = reason;
                closeModal('modal-confirm-disapprove');
                closeModal('modal-view');
                form.requestSubmit ? form.requestSubmit() : form.submit();
                showToast('Applicant disapproved.', 'error');
            }
        });

        // -----------------------------------------------------------
        // Active rider — row click opens detail modal
        // -----------------------------------------------------------
        let currentDetailId = null;

        function renderHistory(riderId, mode) {
            const r = activeById[riderId];
            let list = (r.deliveries || []).slice();

            if (mode === 'oldest') {
                list.sort(function (a, b) { return new Date(a.date) - new Date(b.date); });
            } else if (mode === 'newest') {
                list.sort(function (a, b) { return new Date(b.date) - new Date(a.date); });
            } else if (mode === 'month') {
                list.sort(function (a, b) { return new Date(b.date) - new Date(a.date); });
            }

            const container = document.getElementById('history-list');

            if (!list.length) {
                container.innerHTML = '<p class="text-slate-400 text-sm text-center py-8">No delivery history yet.</p>';
                return;
            }

            if (mode === 'month') {
                const groups = {};
                list.forEach(function (d) {
                    const key = new Date(d.date).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
                    groups[key] = groups[key] || [];
                    groups[key].push(d);
                });
                container.innerHTML = Object.keys(groups).map(function (month) {
                    const rows = groups[month].map(historyRow).join('');
                    return `<div class="mb-3"><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">${month}</p>${rows}</div>`;
                }).join('');
            } else {
                container.innerHTML = list.map(historyRow).join('');
            }
        }

        function historyRow(d) {
            const statusColor = d.status === 'Delivered' ? 'bg-mint/10 text-mint-dark' : 'bg-coral/10 text-coral';
            return `<div class="flex items-center justify-between border border-slate-200 rounded-lg px-3.5 py-2.5">
                <div>
                    <p class="text-sm font-semibold text-navy">${d.order_id || '—'}</p>
                    <p class="text-xs text-slate-400">${d.customer || '—'} · ${d.date || '—'}</p>
                </div>
                <span class="text-[11px] font-semibold px-2 py-1 rounded-full ${statusColor}">${d.status || '—'}</span>
            </div>`;
        }

        function renderDocuments(riderId) {
            const r = activeById[riderId];
            const docs = r.documents || [];
            const container = document.getElementById('documents-list');
            if (!docs.length) {
                container.innerHTML = '<p class="text-slate-400 text-sm text-center py-8 col-span-full">No documents on file.</p>';
                return;
            }
            container.innerHTML = docs.map(function (doc) {
                return `<button type="button" class="detail-doc-view group block" data-doc-name="${doc.label}" data-doc-url="${doc.url || ''}">
                    <div class="w-full aspect-square rounded-lg border border-dashed border-slate-300 bg-slate-50/60 flex flex-col items-center justify-center gap-1 text-slate-400 cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark">
                        ${docFileIconSvg}
                        <span class="text-[9px] font-medium uppercase tracking-wide">Preview</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 text-center truncate transition group-hover:text-mint-dark">${doc.label}</p>
                </button>`;
            }).join('');
            document.querySelectorAll('.detail-doc-view').forEach(function (btn) {
                btn.addEventListener('click', function () { openDocViewer(btn.dataset.docName, btn.dataset.docUrl); });
            });
        }

        function renderWarnings(riderId) {
            const r = activeById[riderId];
            const warnings = r.warnings || [];
            const container = document.getElementById('warnings-list');
            if (!warnings.length) {
                container.innerHTML = '<p class="text-slate-400 text-sm text-center py-8">No violations on record.</p>';
                return;
            }
            const severityColor = { minor: 'bg-amber-100 text-amber-700', major: 'bg-coral/10 text-coral' };
            container.innerHTML = warnings.map(function (w) {
                return `<div class="border border-slate-200 rounded-lg px-3.5 py-2.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-navy">${w.type || '—'}</p>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${severityColor[w.severity] || severityColor.minor}">${(w.severity || 'minor').toUpperCase()}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">${w.details || ''}</p>
                    <p class="text-[11px] text-slate-400 mt-1">${w.date || ''}</p>
                </div>`;
            }).join('');
        }

        function openRiderDetail(riderId) {
            const r = activeById[riderId];
            if (!r) return;
            currentDetailId = riderId;

            document.getElementById('detail-rider-name').textContent = r.name;
            document.getElementById('detail-rider-sub').textContent = `${r.vehicle || '—'} · ${r.zone || '—'} · ★ ${Number(r.rating || 0).toFixed(1)}`;

            renderHistory(riderId, document.getElementById('history-filter').value);
            renderDocuments(riderId);
            renderWarnings(riderId);

            const statusBtn = document.getElementById('detail-status-btn');
            statusBtn.textContent = r.status === 'active' ? 'Suspend rider' : 'Activate rider';

            document.querySelectorAll('[data-detail-tab-btn]').forEach(function (b, i) {
                const active = i === 0;
                b.classList.toggle('border-mint-dark', active);
                b.classList.toggle('text-mint-dark', active);
                b.classList.toggle('border-transparent', !active);
                b.classList.toggle('text-slate-400', !active);
            });
            document.querySelectorAll('[data-detail-tab-panel]').forEach(function (p, i) {
                p.classList.toggle('hidden', i !== 0);
            });

            openModal('modal-rider-detail');
        }

        document.querySelectorAll('[data-rider-row]').forEach(function (row) {
            row.addEventListener('click', function () { openRiderDetail(row.dataset.riderId); });
        });

        document.getElementById('history-filter').addEventListener('change', function () {
            renderHistory(currentDetailId, this.value);
        });

        document.querySelectorAll('[data-detail-tab-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('[data-detail-tab-btn]').forEach(function (b) {
                    b.classList.remove('border-mint-dark', 'text-mint-dark');
                    b.classList.add('border-transparent', 'text-slate-400');
                });
                btn.classList.add('border-mint-dark', 'text-mint-dark');
                btn.classList.remove('border-transparent', 'text-slate-400');
                document.querySelectorAll('[data-detail-tab-panel]').forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.detailTabPanel !== btn.dataset.detailTabBtn);
                });
            });
        });

        document.getElementById('detail-chat-btn').addEventListener('click', function () {
            const r = activeById[currentDetailId];
            openChat(r.name, 'active-' + currentDetailId);
        });

        document.getElementById('detail-status-btn').addEventListener('click', function () {
            const r = activeById[currentDetailId];
            openStatusConfirm(currentDetailId, r.name, r.status);
        });

        // -----------------------------------------------------------
        // Suspend / Activate confirm
        // -----------------------------------------------------------
        let pendingStatusId = null;
        let pendingStatusAction = null;

        function openStatusConfirm(id, name, currentStatus) {
            pendingStatusId = id;
            pendingStatusAction = currentStatus === 'active' ? 'suspend' : 'activate';

            const iconWrap = document.getElementById('status-confirm-icon-wrap');
            iconWrap.className = pendingStatusAction === 'suspend'
                ? 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-amber-100 text-amber-600'
                : 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';

            document.getElementById('status-confirm-title').textContent =
                pendingStatusAction === 'suspend' ? 'Suspend rider?' : 'Activate rider?';
            document.getElementById('status-confirm-body').innerHTML =
                pendingStatusAction === 'suspend'
                    ? `<span class="font-semibold text-navy">${name}</span> will be temporarily removed from active dispatch.`
                    : `<span class="font-semibold text-navy">${name}</span> will be reinstated and able to receive deliveries again.`;
            openModal('modal-confirm-status');
        }

        document.querySelectorAll('[data-status-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openStatusConfirm(btn.dataset.riderId, btn.dataset.riderName, btn.dataset.riderStatus);
            });
        });

        document.getElementById('status-confirm-btn').addEventListener('click', function () {
            const formId = (pendingStatusAction === 'suspend' ? 'suspend-form-' : 'activate-form-') + pendingStatusId;
            const form = document.getElementById(formId);
            closeModal('modal-confirm-status');
            closeModal('modal-rider-detail');
            if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
            showToast(pendingStatusAction === 'suspend' ? 'Rider suspended.' : 'Rider activated.', pendingStatusAction === 'suspend' ? 'error' : 'success');
        });

        // -----------------------------------------------------------
        // Chat (client-side placeholder — no backend yet)
        // -----------------------------------------------------------
        const chatHistory = {};

        function openChat(name, key) {
            document.getElementById('chat-with-name').textContent = name;
            document.getElementById('chat-input').dataset.chatKey = key;
            renderChat(key);
            openModal('modal-chat');
            document.getElementById('chat-input').focus();
        }

        function renderChat(key) {
            const messages = chatHistory[key] || [];
            const box = document.getElementById('chat-messages');
            if (!messages.length) {
                box.innerHTML = '<p class="text-center text-slate-400 text-xs mt-6">No messages yet. Say hi 👋</p>';
                return;
            }
            box.innerHTML = messages.map(function (m) {
                return `<div class="flex ${m.from === 'me' ? 'justify-end' : 'justify-start'}">
                    <div class="${m.from === 'me' ? 'bg-mint-dark text-white' : 'bg-white border border-slate-200 text-navy'} text-sm px-3.5 py-2 rounded-2xl max-w-[75%]">${m.text}</div>
                </div>`;
            }).join('');
            box.scrollTop = box.scrollHeight;
        }

        document.querySelectorAll('[data-chat-btn]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openChat(btn.dataset.name, btn.dataset.id);
            });
        });

        function sendChat() {
            const input = document.getElementById('chat-input');
            const key = input.dataset.chatKey;
            const text = input.value.trim();
            if (!text) return;
            chatHistory[key] = chatHistory[key] || [];
            chatHistory[key].push({ from: 'me', text });
            input.value = '';
            renderChat(key);
        }
        document.getElementById('chat-send-btn').addEventListener('click', sendChat);
        document.getElementById('chat-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendChat();
        });
    });

    // -----------------------------------------------------------
    // Search
    // -----------------------------------------------------------
    const riderSearch = document.getElementById('rider-search');
    const riderSearchClear = document.getElementById('rider-search-clear');

    function filterRiders() {
        const term = riderSearch.value.trim().toLowerCase();
        riderSearchClear.classList.toggle('hidden', term.length === 0);

        document.querySelectorAll('[data-rider-name]').forEach(function (row) {
            const match = row.dataset.riderName.toLowerCase().includes(term);
            row.classList.toggle('hidden', !match);
        });

        document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
            const rows = panel.querySelectorAll('[data-rider-name]');
            if (!rows.length) return;
            const container = panel.querySelector('[data-rows-container]');
            const anyVisible = Array.from(rows).some(r => !r.classList.contains('hidden'));
            let noResultsRow = panel.querySelector('[data-no-results-row]');
            if (!anyVisible && term.length > 0) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('div');
                    noResultsRow.setAttribute('data-no-results-row', '');
                    noResultsRow.className = 'px-5 py-14 text-center text-slate-400 text-sm';
                    noResultsRow.textContent = `No riders match "${riderSearch.value}".`;
                    container.appendChild(noResultsRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        });
    }

    riderSearch.addEventListener('input', filterRiders);
    riderSearchClear.addEventListener('click', function () {
        riderSearch.value = '';
        filterRiders();
        riderSearch.focus();
    });

    // -----------------------------------------------------------
    // Warning — with performance-based suggestions
    // -----------------------------------------------------------
    let pendingWarningId = null;

    function suggestWarnings(r) {
        const suggestions = [];

        if (typeof r.completion === 'number' && r.completion < 90) {
            suggestions.push({
                type: 'Late Delivery',
                reason: `Completion rate is ${r.completion}%, below the 90% target.`,
            });
        }
        if (typeof r.rating === 'number' && r.rating < 4.0) {
            suggestions.push({
                type: 'Customer Complaint',
                reason: `Average rating is ${r.rating.toFixed(1)}★, below the 4.0 threshold.`,
            });
        }
        if ((r.warnings || []).length >= 2) {
            suggestions.push({
                type: 'Repeated Violations',
                reason: `Rider already has ${r.warnings.length} recorded violations on file.`,
            });
        }
        if (!suggestions.length) {
            suggestions.push({
                type: null,
                reason: 'No performance red flags detected — pick a violation type manually if you still want to log one.',
            });
        }
        return suggestions;
    }

    function openWarning(riderId) {
        const activeById = window.__activeByIdCache || (window.__activeByIdCache = Object.fromEntries(@json($activeRiders).map(r => [r.id, r])));
        const r = activeById[riderId];
        if (!r) return;
        pendingWarningId = riderId;

        document.getElementById('warning-for-name').textContent = r.name;
        document.getElementById('warning-type').value = '';
        document.getElementById('warning-details').value = '';
        document.getElementById('warning-details-error').classList.add('hidden');
        document.querySelector('input[name="warning-severity"][value="minor"]').checked = true;

        const suggestions = suggestWarnings(r);
        document.getElementById('warning-suggestions').innerHTML = suggestions.map(function (s) {
            if (!s.type) {
                return `<p class="text-xs text-slate-400 bg-slate-50 rounded-lg px-3 py-2">${s.reason}</p>`;
            }
            return `<button type="button" class="warning-suggestion-chip w-full text-left bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 hover:bg-amber-100 transition" data-suggest-type="${s.type}">
                <p class="text-xs font-semibold text-amber-800">${s.type}</p>
                <p class="text-[11px] text-amber-700/80 mt-0.5">${s.reason}</p>
            </button>`;
        }).join('');

        document.querySelectorAll('.warning-suggestion-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                document.getElementById('warning-type').value = chip.dataset.suggestType;
                const details = document.getElementById('warning-details');
                if (!details.value.trim()) {
                    details.value = chip.querySelector('p:nth-child(2)').textContent;
                }
            });
        });

        document.getElementById('modal-warning').classList.remove('hidden');
        document.getElementById('modal-warning').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    document.querySelectorAll('[data-warning-btn]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            openWarning(btn.dataset.riderId);
        });
    });

    document.getElementById('warning-confirm-btn').addEventListener('click', function () {
        const type = document.getElementById('warning-type').value;
        const details = document.getElementById('warning-details').value.trim();
        const severity = document.querySelector('input[name="warning-severity"]:checked').value;

        if (!type || !details) {
            document.getElementById('warning-details-error').classList.remove('hidden');
            return;
        }

        const form = document.getElementById('warn-form-' + pendingWarningId);
        if (form) {
            form.querySelector('[data-warn-type-input]').value = type;
            form.querySelector('[data-warn-severity-input]').value = severity;
            form.querySelector('[data-warn-details-input]').value = details;
            document.getElementById('modal-warning').classList.add('hidden');
            document.getElementById('modal-warning').classList.remove('flex');
            document.getElementById('modal-rider-detail').classList.add('hidden');
            document.getElementById('modal-rider-detail').classList.remove('flex');
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }
    });
</script>

@endsection