{{-- resources/views/logistics/riders/index.blade.php --}}

@extends('logistics.layouts.logistics')

@section('title', 'Riders — ShopHop Logistics')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Riders</h1>
        <p class="text-navy/55 text-sm mt-1">Review applications and manage your active fleet.</p>
    </div>
</div>

{{-- =========================================================
    TABS
========================================================= --}}
<div class="flex gap-5 mb-6 border-b border-gray-border overflow-x-auto whitespace-nowrap">
    <button type="button" data-tab-btn="applications" class="shrink-0 px-1 pb-3 -mb-px text-sm font-semibold border-b-2 border-teal text-teal-dark">
        Pending applications
        <span class="ml-1 text-[11px] font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full">{{ count($applications) }}</span>
    </button>
    <button type="button" data-tab-btn="active" class="shrink-0 px-1 pb-3 -mb-px text-sm font-semibold border-b-2 border-transparent text-navy/50 hover:text-navy transition">
        Active riders
        <span class="ml-1 text-[11px] font-bold bg-gray-bg text-navy/50 px-2 py-0.5 rounded-full">{{ count($activeRiders) }}</span>
    </button>
</div>

{{-- =========================================================
    SEARCH
========================================================= --}}
<div class="mb-4">
    <div class="relative max-w-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
             class="absolute left-3 top-1/2 -translate-y-1/2 text-navy/35 pointer-events-none">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
        </svg>
        <input type="text" id="rider-search" placeholder="Search by rider name…"
               class="w-full pl-9 pr-9 py-2.5 text-sm border border-gray-border rounded-xl focus:outline-none focus:ring-2 focus:ring-teal/30 focus:border-teal">
        <button type="button" id="rider-search-clear" aria-label="Clear search"
                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-navy/35 hover:text-navy hover:bg-gray-bg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
</div>

{{-- =========================================================
    APPLICATIONS TAB
========================================================= --}}
<div data-tab-panel="applications" class="bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg">
                    <th class="px-6 py-3">Applicant</th>
                    <th class="px-6 py-3">Vehicle</th>
                    <th class="px-6 py-3">Documents</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-border">
                @forelse ($applications as $rider)
                    <tr data-rider-name="{{ $rider['name'] }}">
                        <td class="px-6 py-4 font-semibold text-navy whitespace-nowrap">
                            {{ $rider['name'] }}
                            <div data-interview-badge="{{ $rider['id'] }}" class="hidden mt-1"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-navy font-medium">{{ $rider['vehicle'] }}</p>
                            <p class="text-xs text-navy/45">{{ $rider['plate_number'] ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach ($rider['docs'] as $doc => $ok)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold pl-2 {{ $ok ? 'pr-1' : 'pr-2' }} py-1 rounded-md whitespace-nowrap {{ $ok ? 'bg-gray-bg text-navy/70' : 'border border-red-200 text-red-600' }}">
                                        {{ $doc }} {{ $ok ? '✓' : 'missing' }}
                                        @if ($ok)
                                            <button type="button"
                                                data-view-doc
                                                data-doc-name="{{ $doc }}"
                                                data-doc-url="{{ $rider['doc_files'][$doc] ?? '' }}"
                                                class="ml-0.5 w-5 h-5 flex items-center justify-center rounded hover:bg-white text-navy/45 hover:text-teal-dark transition"
                                                aria-label="View {{ $doc }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2 flex-wrap">
                                <button type="button" data-chat-btn data-name="{{ $rider['name'] }}" data-id="app-{{ $rider['id'] }}"
                                    class="text-xs font-semibold text-navy/60 border border-navy/15 hover:bg-gray-bg px-3 py-1.5 rounded-full transition whitespace-nowrap" aria-label="Chat with {{ $rider['name'] }}">
                                    <svg class="inline -mt-0.5" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.4H4l3-3.5A8.4 8.4 0 1 1 21 11.5Z"/></svg>
                                </button>
                                <button type="button" data-review-btn data-rider-id="{{ $rider['id'] }}"
                                    class="text-xs font-semibold text-navy border border-navy/20 hover:bg-gray-bg px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                    Review
                                </button>
                                <button type="button" data-interview-btn data-action="schedule" data-rider-id="{{ $rider['id'] }}"
                                    class="text-xs font-semibold text-navy border border-navy/20 hover:bg-gray-bg px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                    Schedule interview
                                </button>
                                <button type="button" data-approve-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}"
                                    id="approve-btn-{{ $rider['id'] }}" disabled title="Schedule and complete an interview first"
                                    class="text-xs font-semibold bg-teal/40 text-white px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-not-allowed">
                                    Approve
                                </button>
                                <button type="button" data-disapprove-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}"
                                    class="text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                    Disapprove
                                </button>
                            </div>

                            {{-- real forms, submitted via JS after confirmation --}}
                            <form id="approve-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.approve', $rider['id']) }}" class="hidden">
                                @csrf
                            </form>
                            <form id="disapprove-form-{{ $rider['id'] }}" method="POST" action="{{ route('logistics.riders.disapprove', $rider['id']) }}" class="hidden">
                                @csrf
                                <input type="hidden" name="reason" data-disapprove-reason-input>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-navy/40 text-sm">No pending applications right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================================================
    ACTIVE RIDERS TAB
========================================================= --}}
<div data-tab-panel="active" class="hidden bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg">
                    <th class="px-6 py-3">Rider</th>
                    <th class="px-6 py-3">Zone</th>
                    <th class="px-6 py-3">Completion</th>
                    <th class="px-6 py-3">Rating</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-border">
                @forelse ($activeRiders as $rider)
                    <tr data-rider-row data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}" class="cursor-pointer hover:bg-gray-bg/60 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-navy">{{ $rider['name'] }}</p>
                            <p class="text-xs text-navy/45">{{ $rider['vehicle'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-navy/60 whitespace-nowrap">{{ $rider['zone'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['completion'] }}%</td>
                        <td class="px-6 py-4 text-navy/60">★ {{ number_format($rider['rating'], 1) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap {{ $rider['status'] === 'active' ? 'bg-teal-light text-teal-dark' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($rider['status']) }}
                            </span>
                            @if (!empty($rider['warnings']))
                                <span class="ml-1 text-[10px] font-bold bg-red-50 text-red-600 px-1.5 py-0.5 rounded-full align-middle">{{ count($rider['warnings']) }} flag{{ count($rider['warnings']) > 1 ? 's' : '' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 flex-wrap" onclick="event.stopPropagation()">
                                <button type="button" data-chat-btn data-name="{{ $rider['name'] }}" data-id="active-{{ $rider['id'] }}"
                                    class="text-xs font-semibold text-navy/60 border border-navy/15 hover:bg-gray-bg px-3 py-1.5 rounded-full transition whitespace-nowrap" aria-label="Chat with {{ $rider['name'] }}">
                                    <svg class="inline -mt-0.5" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.4H4l3-3.5A8.4 8.4 0 1 1 21 11.5Z"/></svg>
                                </button>
                                <button type="button" data-warning-btn data-rider-id="{{ $rider['id'] }}"
                                    class="text-xs font-semibold text-amber-700 border border-amber-200 hover:bg-amber-50 px-3 py-1.5 rounded-full transition whitespace-nowrap" aria-label="Warn {{ $rider['name'] }}">
                                    <svg class="inline -mt-0.5" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                </button>
                                <button type="button" data-status-btn data-rider-id="{{ $rider['id'] }}" data-rider-name="{{ $rider['name'] }}" data-rider-status="{{ $rider['status'] }}"
                                    class="text-xs font-semibold border border-navy/20 hover:bg-gray-bg text-navy px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
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
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-navy/40 text-sm">No active riders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================================================
    MODALS
========================================================= --}}

{{-- Document Viewer Modal --}}
<div id="modal-doc-viewer" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border sticky top-0 bg-white">
            <h3 id="doc-viewer-title" class="font-bold text-navy text-sm">Document</h3>
            <button type="button" class="modal-close text-navy/40 hover:text-navy" data-close="modal-doc-viewer" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5">
            <div id="doc-viewer-body" class="bg-gray-bg rounded-xl min-h-[300px] flex items-center justify-center overflow-hidden">
                <p class="text-navy/40 text-sm">No file available.</p>
            </div>
        </div>
    </div>
</div>

{{-- Review Application Modal --}}
<div id="modal-review" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border sticky top-0 bg-white z-10">
            <div>
                <h3 class="font-bold text-navy text-base">Rider registration</h3>
                <p id="review-submitted-at" class="text-xs text-navy/45 mt-0.5"></p>
                <p id="review-interview-status" class="text-xs mt-1"></p>
            </div>
            <button type="button" class="text-navy/40 hover:text-navy" data-close="modal-review" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5 space-y-5">
            <div>
                <h4 class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-2">Personal details</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="review-personal"></div>
            </div>

            <div>
                <h4 class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-2">Address</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="review-address"></div>
            </div>

            <div>
                <h4 class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-2">Vehicle</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="review-vehicle"></div>
            </div>

            <div>
                <h4 class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-2">Documents submitted</h4>
                <div class="flex gap-2 flex-wrap" id="review-docs"></div>
            </div>

            <div class="bg-gray-bg rounded-xl p-3.5 text-xs text-navy/60 leading-relaxed">
                After submitting the registration, the applicant is waiting for the administrator's approval, which will be sent to their email.
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-border sticky bottom-0 bg-white flex flex-wrap justify-end gap-2">
            <button type="button" id="review-chat-btn" class="text-xs font-semibold text-navy/60 border border-navy/15 hover:bg-gray-bg px-3.5 py-2 rounded-full transition">
                Chat applicant
            </button>
            <button type="button" id="review-schedule-btn" class="text-xs font-semibold text-navy border border-navy/20 hover:bg-gray-bg px-3.5 py-2 rounded-full transition">
                Schedule interview
            </button>
            <button type="button" id="review-disapprove-btn" class="text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 px-3.5 py-2 rounded-full transition">
                Disapprove
            </button>
            <button type="button" id="review-approve-btn" disabled title="Schedule and complete an interview first"
                class="text-xs font-semibold bg-teal/40 text-white px-3.5 py-2 rounded-full transition cursor-not-allowed">
                Approve
            </button>
        </div>
    </div>
</div>

{{-- Schedule Interview Modal (UX only — walang function pa) --}}
<div id="modal-schedule" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-[60] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <h3 class="font-bold text-navy text-sm">Schedule interview</h3>
            <button type="button" class="text-navy/40 hover:text-navy" data-close="modal-schedule" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 space-y-3">
            <p class="text-xs text-navy/50" id="schedule-for-name"></p>
            <div>
                <label class="block text-xs font-semibold text-navy/60 mb-1">Date</label>
                <input type="date" id="schedule-date" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-navy/60 mb-1">Time</label>
                <input type="time" id="schedule-time" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-navy/60 mb-1">Notes for applicant (optional)</label>
                <textarea id="schedule-notes" rows="2" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm resize-none" placeholder="e.g. Bring original IDs"></textarea>
            </div>
            <p class="text-[11px] text-navy/35">An interview invite will be emailed to the applicant.</p>
        </div>
        <div class="px-5 py-4 border-t border-gray-border flex justify-end gap-2">
            <button type="button" class="text-xs font-semibold text-navy/60 px-3.5 py-2 rounded-full hover:bg-gray-bg" data-close="modal-schedule">Cancel</button>
            <button type="button" id="schedule-send-btn" class="text-xs font-semibold bg-teal hover:bg-teal-dark text-white px-3.5 py-2 rounded-full transition">Send invite</button>
        </div>
    </div>
</div>

{{-- Approve Confirm Modal --}}
<div id="modal-confirm-approve" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-[60] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
        <h3 class="font-bold text-navy text-sm mb-1">Approve applicant?</h3>
        <p class="text-xs text-navy/55 mb-4">
            <span id="approve-confirm-name" class="font-semibold text-navy"></span> will be notified via email and added to your active fleet.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" class="text-xs font-semibold text-navy/60 px-3.5 py-2 rounded-full hover:bg-gray-bg" data-close="modal-confirm-approve">Cancel</button>
            <button type="button" id="approve-confirm-btn" class="text-xs font-semibold bg-teal hover:bg-teal-dark text-white px-4 py-2 rounded-full transition">Yes, approve</button>
        </div>
    </div>
</div>

{{-- Disapprove Confirm Modal (with reason) --}}
<div id="modal-confirm-disapprove" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-[60] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
        <h3 class="font-bold text-navy text-sm mb-1">Disapprove applicant?</h3>
        <p class="text-xs text-navy/55 mb-3">
            <span id="disapprove-confirm-name" class="font-semibold text-navy"></span> will receive an email with the reason below.
        </p>
        <label class="block text-xs font-semibold text-navy/60 mb-1">Reason <span class="text-red-500">*</span></label>
        <textarea id="disapprove-reason" rows="3" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm resize-none" placeholder="e.g. Incomplete or unreadable documents submitted"></textarea>
        <p id="disapprove-reason-error" class="hidden text-[11px] text-red-600 mt-1">Reason is required.</p>
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" class="text-xs font-semibold text-navy/60 px-3.5 py-2 rounded-full hover:bg-gray-bg" data-close="modal-confirm-disapprove">Cancel</button>
            <button type="button" id="disapprove-confirm-btn" class="text-xs font-semibold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full transition">Yes, disapprove</button>
        </div>
    </div>
</div>

{{-- Suspend / Activate Confirm Modal --}}
<div id="modal-confirm-status" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
        <h3 id="status-confirm-title" class="font-bold text-navy text-sm mb-1">Suspend rider?</h3>
        <p class="text-xs text-navy/55 mb-4" id="status-confirm-body"></p>
        <div class="flex justify-end gap-2">
            <button type="button" class="text-xs font-semibold text-navy/60 px-3.5 py-2 rounded-full hover:bg-gray-bg" data-close="modal-confirm-status">Cancel</button>
            <button type="button" id="status-confirm-btn" class="text-xs font-semibold bg-navy hover:bg-navy/90 text-white px-4 py-2 rounded-full transition">Confirm</button>
        </div>
    </div>
</div>

{{-- Rider Detail Modal (Active riders) --}}
<div id="modal-rider-detail" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border sticky top-0 bg-white z-10">
            <div>
                <h3 id="detail-rider-name" class="font-bold text-navy text-base"></h3>
                <p id="detail-rider-sub" class="text-xs text-navy/45 mt-0.5"></p>
            </div>
            <button type="button" class="text-navy/40 hover:text-navy" data-close="modal-rider-detail" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex gap-4 px-5 pt-4 border-b border-gray-border overflow-x-auto whitespace-nowrap">
            <button type="button" data-detail-tab-btn="history" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-teal text-teal-dark">Delivery history</button>
            <button type="button" data-detail-tab-btn="documents" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-transparent text-navy/50 hover:text-navy">Documents</button>
            <button type="button" data-detail-tab-btn="warnings" class="shrink-0 pb-3 -mb-px text-xs font-semibold border-b-2 border-transparent text-navy/50 hover:text-navy">Warnings</button>
        </div>

        <div class="p-5">
            {{-- History panel --}}
            <div data-detail-tab-panel="history">
                <div class="flex justify-end mb-3">
                    <select id="history-filter" class="text-xs border border-gray-border rounded-lg px-2.5 py-1.5">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="month">By month</option>
                    </select>
                </div>
                <div id="history-list" class="space-y-2"></div>
            </div>

            {{-- Documents panel --}}
            <div data-detail-tab-panel="documents" class="hidden">
                <div id="documents-list" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
            </div>

            {{-- Warnings panel --}}
            <div data-detail-tab-panel="warnings" class="hidden">
                <div id="warnings-list" class="space-y-2"></div>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-border sticky bottom-0 bg-white flex flex-wrap justify-end gap-2">
            <button type="button" id="detail-chat-btn" class="text-xs font-semibold text-navy/60 border border-navy/15 hover:bg-gray-bg px-3.5 py-2 rounded-full transition">
                Chat rider
            </button>
            <button type="button" id="detail-status-btn" class="text-xs font-semibold border border-navy/20 hover:bg-gray-bg text-navy px-3.5 py-2 rounded-full transition"></button>
        </div>
    </div>
</div>

{{-- Chat Modal (client-side placeholder — walang backend/websocket pa) --}}
<div id="modal-chat" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-[60] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm h-[520px] flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <h3 id="chat-with-name" class="font-bold text-navy text-sm"></h3>
            <button type="button" class="text-navy/40 hover:text-navy" data-close="modal-chat" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-2 bg-gray-bg/40">
            <p class="text-center text-navy/35 text-xs mt-6">No messages yet. Say hi 👋</p>
        </div>
        <div class="p-3 border-t border-gray-border flex gap-2">
            <input type="text" id="chat-input" placeholder="Type a message…" class="flex-1 border border-gray-border rounded-full px-4 py-2 text-sm">
            <button type="button" id="chat-send-btn" class="w-9 h-9 flex items-center justify-center rounded-full bg-teal hover:bg-teal-dark text-white shrink-0" aria-label="Send">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Warning Modal — may suggestions based sa performance ng rider --}}
<div id="modal-warning" class="modal-overlay fixed inset-0 bg-navy/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <div>
                <h3 class="font-bold text-navy text-sm">Issue warning</h3>
                <p id="warning-for-name" class="text-xs text-navy/45 mt-0.5"></p>
            </div>
            <button type="button" class="text-navy/40 hover:text-navy" data-close="modal-warning" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div>
                <p class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-2">Suggested, based on performance</p>
                <div id="warning-suggestions" class="space-y-1.5"></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy/60 mb-1">Violation type</label>
                <select id="warning-type" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm">
                    <option value="">Select a type…</option>
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
                <label class="block text-xs font-semibold text-navy/60 mb-1">Severity</label>
                <div class="flex gap-2">
                    <label class="flex-1">
                        <input type="radio" name="warning-severity" value="minor" class="peer sr-only" checked>
                        <span class="block text-center text-xs font-semibold py-2 rounded-lg border border-gray-border text-navy/60 peer-checked:bg-amber-50 peer-checked:border-amber-300 peer-checked:text-amber-700 cursor-pointer transition">Minor</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="warning-severity" value="major" class="peer sr-only">
                        <span class="block text-center text-xs font-semibold py-2 rounded-lg border border-gray-border text-navy/60 peer-checked:bg-red-50 peer-checked:border-red-300 peer-checked:text-red-600 cursor-pointer transition">Major</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy/60 mb-1">Details</label>
                <textarea id="warning-details" rows="3" class="w-full border border-gray-border rounded-lg px-3 py-2 text-sm resize-none" placeholder="e.g. 3 late deliveries this week, customer flagged rudeness"></textarea>
                <p id="warning-details-error" class="hidden text-[11px] text-red-600 mt-1">Please select a type and add details.</p>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-border flex justify-end gap-2">
            <button type="button" class="text-xs font-semibold text-navy/60 px-3.5 py-2 rounded-full hover:bg-gray-bg" data-close="modal-warning">Cancel</button>
            <button type="button" id="warning-confirm-btn" class="text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-full transition">Issue warning</button>
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
                    badge.className = 'inline-block text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full mt-1';
                } else if (state.status === 'completed') {
                    badge.textContent = 'Interview completed ✓';
                    badge.className = 'inline-block text-[10px] font-semibold text-teal-dark bg-teal-light px-2 py-0.5 rounded-full mt-1';
                } else {
                    badge.textContent = '';
                    badge.className = 'hidden';
                }
            }

            const interviewBtn = document.querySelector('[data-interview-btn][data-rider-id="' + id + '"]');
            if (interviewBtn) {
                if (state.status === 'scheduled') {
                    interviewBtn.textContent = 'Mark interview done';
                    interviewBtn.dataset.action = 'mark-done';
                    interviewBtn.classList.remove('text-navy', 'border-navy/20');
                    interviewBtn.classList.add('text-teal-dark', 'border-teal/40', 'bg-teal-light/40');
                } else if (state.status === 'completed') {
                    interviewBtn.textContent = 'Reschedule interview';
                    interviewBtn.dataset.action = 'schedule';
                    interviewBtn.classList.remove('text-teal-dark', 'border-teal/40', 'bg-teal-light/40');
                    interviewBtn.classList.add('text-navy', 'border-navy/20');
                } else {
                    interviewBtn.textContent = 'Schedule interview';
                    interviewBtn.dataset.action = 'schedule';
                }
            }

            const approveBtn = document.getElementById('approve-btn-' + id);
            const canApprove = state.status === 'completed';
            if (approveBtn) {
                approveBtn.disabled = !canApprove;
                approveBtn.title = canApprove ? '' : 'Schedule and complete an interview first';
                approveBtn.classList.toggle('bg-teal', canApprove);
                approveBtn.classList.toggle('hover:bg-teal-dark', canApprove);
                approveBtn.classList.toggle('bg-teal/40', !canApprove);
                approveBtn.classList.toggle('cursor-not-allowed', !canApprove);
            }

            // Kung bukas yung Review modal para dito, i-sync din agad.
            if (currentReviewId === id) {
                const reviewApproveBtn = document.getElementById('review-approve-btn');
                const statusLine = document.getElementById('review-interview-status');
                if (reviewApproveBtn) {
                    reviewApproveBtn.disabled = !canApprove;
                    reviewApproveBtn.title = canApprove ? '' : 'Schedule and complete an interview first';
                    reviewApproveBtn.classList.toggle('bg-teal', canApprove);
                    reviewApproveBtn.classList.toggle('hover:bg-teal-dark', canApprove);
                    reviewApproveBtn.classList.toggle('bg-teal/40', !canApprove);
                    reviewApproveBtn.classList.toggle('cursor-not-allowed', !canApprove);
                }
                if (statusLine) {
                    if (state.status === 'scheduled') {
                        statusLine.textContent = 'Interview scheduled for ' + state.date + ' ' + state.time + '.';
                        statusLine.className = 'text-xs mt-1 text-amber-700';
                    } else if (state.status === 'completed') {
                        statusLine.textContent = 'Interview completed — ready to approve.';
                        statusLine.className = 'text-xs mt-1 text-teal-dark';
                    } else {
                        statusLine.textContent = 'No interview scheduled yet.';
                        statusLine.className = 'text-xs mt-1 text-navy/40';
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
                    b.classList.remove('border-teal', 'text-teal-dark');
                    b.classList.add('border-transparent', 'text-navy/50');
                });
                btn.classList.add('border-teal', 'text-teal-dark');
                btn.classList.remove('border-transparent', 'text-navy/50');
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
        }
        function closeModal(id) {
            const el = document.getElementById(id);
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
                success: 'bg-teal text-white',
                error: 'bg-red-600 text-white',
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
                body.innerHTML = '<p class="text-navy/40 text-sm p-10">No file available.</p>';
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
        // Review modal
        // -----------------------------------------------------------
        let currentReviewId = null;

        function field(label, value) {
            return `<div><p class="text-[10px] font-semibold text-navy/40 uppercase tracking-wide">${label}</p><p class="text-sm text-navy mt-0.5">${value ?? '—'}</p></div>`;
        }

        function openReview(riderId) {
            const r = appsById[riderId];
            if (!r) return;
            currentReviewId = riderId;

            document.getElementById('review-submitted-at').textContent = r.submitted_at
                ? `Submitted ${r.submitted_at}` : '';

            document.getElementById('review-personal').innerHTML = [
                field('Last name', r.last_name),
                field('First name', r.first_name),
                field('Middle initial', r.middle_initial),
                field('Sex', r.sex),
                field('Email', r.email),
                field('Contact no.', r.contact_no),
                field('Birthday', r.birthday),
                field('Age', r.age),
            ].join('');

            document.getElementById('review-address').innerHTML = [
                field('Province', r.province),
                field('Municipality', r.municipality),
                field('Barangay', r.barangay),
                field('Street', r.street),
                field('House number', r.house_number),
            ].join('');

            document.getElementById('review-vehicle').innerHTML = [
                field('Vehicle', r.vehicle),
                field('Plate number', r.plate_number),
            ].join('');

            const docs = r.docs || {};
            document.getElementById('review-docs').innerHTML = Object.keys(docs).map(function (doc) {
                const ok = docs[doc];
                const url = (r.doc_files || {})[doc] || '';
                return `<span class="inline-flex items-center gap-1 text-[10px] font-semibold pl-2 ${ok ? 'pr-1' : 'pr-2'} py-1 rounded-md ${ok ? 'bg-gray-bg text-navy/70' : 'border border-red-200 text-red-600'}">
                    ${doc} ${ok ? '✓' : 'missing'}
                    ${ok ? `<button type="button" class="review-doc-view ml-0.5 w-5 h-5 flex items-center justify-center rounded hover:bg-white text-navy/45 hover:text-teal-dark" data-doc-name="${doc}" data-doc-url="${url}" aria-label="View ${doc}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>` : ''}
                </span>`;
            }).join('');

            document.querySelectorAll('.review-doc-view').forEach(function (btn) {
                btn.addEventListener('click', function () { openDocViewer(btn.dataset.docName, btn.dataset.docUrl); });
            });

            renderInterviewState(riderId);

            openModal('modal-review');
        }

        document.querySelectorAll('[data-review-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () { openReview(btn.dataset.riderId); });
        });

        document.getElementById('review-approve-btn').addEventListener('click', function () {
            const r = appsById[currentReviewId];
            openApproveConfirm(currentReviewId, r.name);
        });
        document.getElementById('review-disapprove-btn').addEventListener('click', function () {
            const r = appsById[currentReviewId];
            openDisapproveConfirm(currentReviewId, r.name);
        });
        document.getElementById('review-schedule-btn').addEventListener('click', function () {
            const r = appsById[currentReviewId];
            document.getElementById('schedule-for-name').textContent = `For ${r.name}`;
            document.getElementById('schedule-date').value = '';
            document.getElementById('schedule-time').value = '';
            document.getElementById('schedule-notes').value = '';
            openModal('modal-schedule');
        });
        document.getElementById('review-chat-btn').addEventListener('click', function () {
            const r = appsById[currentReviewId];
            openChat(r.name, 'app-' + currentReviewId);
        });

        // Schedule interview — UX only, walang actual email function pa
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
            closeModal('modal-review');
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
                closeModal('modal-review');
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
                container.innerHTML = '<p class="text-navy/40 text-sm text-center py-8">No delivery history yet.</p>';
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
                    return `<div class="mb-3"><p class="text-[11px] font-bold text-navy/45 uppercase tracking-wide mb-1.5">${month}</p>${rows}</div>`;
                }).join('');
            } else {
                container.innerHTML = list.map(historyRow).join('');
            }
        }

        function historyRow(d) {
            const statusColor = d.status === 'Delivered' ? 'bg-teal-light text-teal-dark' : 'bg-red-50 text-red-600';
            return `<div class="flex items-center justify-between border border-gray-border rounded-lg px-3.5 py-2.5">
                <div>
                    <p class="text-sm font-semibold text-navy">${d.order_id || '—'}</p>
                    <p class="text-xs text-navy/45">${d.customer || '—'} · ${d.date || '—'}</p>
                </div>
                <span class="text-[11px] font-semibold px-2 py-1 rounded-full ${statusColor}">${d.status || '—'}</span>
            </div>`;
        }

        function renderDocuments(riderId) {
            const r = activeById[riderId];
            const docs = r.documents || [];
            const container = document.getElementById('documents-list');
            if (!docs.length) {
                container.innerHTML = '<p class="text-navy/40 text-sm text-center py-8 col-span-full">No documents on file.</p>';
                return;
            }
            container.innerHTML = docs.map(function (doc) {
                return `<button type="button" class="detail-doc-view border border-gray-border rounded-lg p-3 text-left hover:bg-gray-bg transition" data-doc-name="${doc.label}" data-doc-url="${doc.url || ''}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-navy/40 mb-1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    <p class="text-xs font-semibold text-navy">${doc.label}</p>
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
                container.innerHTML = '<p class="text-navy/40 text-sm text-center py-8">No violations on record.</p>';
                return;
            }
            const severityColor = { minor: 'bg-amber-50 text-amber-700', major: 'bg-red-50 text-red-600' };
            container.innerHTML = warnings.map(function (w) {
                return `<div class="border border-gray-border rounded-lg px-3.5 py-2.5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-navy">${w.type || '—'}</p>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${severityColor[w.severity] || severityColor.minor}">${(w.severity || 'minor').toUpperCase()}</span>
                    </div>
                    <p class="text-xs text-navy/50 mt-1">${w.details || ''}</p>
                    <p class="text-[11px] text-navy/35 mt-1">${w.date || ''}</p>
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

            // reset inner tabs to first
            document.querySelectorAll('[data-detail-tab-btn]').forEach(function (b, i) {
                const active = i === 0;
                b.classList.toggle('border-teal', active);
                b.classList.toggle('text-teal-dark', active);
                b.classList.toggle('border-transparent', !active);
                b.classList.toggle('text-navy/50', !active);
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
                    b.classList.remove('border-teal', 'text-teal-dark');
                    b.classList.add('border-transparent', 'text-navy/50');
                });
                btn.classList.add('border-teal', 'text-teal-dark');
                btn.classList.remove('border-transparent', 'text-navy/50');
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
                box.innerHTML = '<p class="text-center text-navy/35 text-xs mt-6">No messages yet. Say hi 👋</p>';
                return;
            }
            box.innerHTML = messages.map(function (m) {
                return `<div class="flex ${m.from === 'me' ? 'justify-end' : 'justify-start'}">
                    <div class="${m.from === 'me' ? 'bg-teal text-white' : 'bg-white border border-gray-border text-navy'} text-sm px-3.5 py-2 rounded-2xl max-w-[75%]">${m.text}</div>
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

    document.querySelectorAll('tr[data-rider-name]').forEach(function (row) {
        const match = row.dataset.riderName.toLowerCase().includes(term);
        row.classList.toggle('hidden', !match);
    });

    // show a lightweight "no results" row per visible panel when search yields nothing
    document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
        const rows = panel.querySelectorAll('tr[data-rider-name]');
        if (!rows.length) return;
        const anyVisible = Array.from(rows).some(r => !r.classList.contains('hidden'));
        let noResultsRow = panel.querySelector('[data-no-results-row]');
        if (!anyVisible && term.length > 0) {
            if (!noResultsRow) {
                const tbody = panel.querySelector('tbody');
                const colspan = panel.querySelectorAll('thead th').length;
                noResultsRow = document.createElement('tr');
                noResultsRow.setAttribute('data-no-results-row', '');
                noResultsRow.innerHTML = `<td colspan="${colspan}" class="px-6 py-10 text-center text-navy/40 text-sm">No riders match "${riderSearch.value}".</td>`;
                tbody.appendChild(noResultsRow);
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
            return `<p class="text-xs text-navy/45 bg-gray-bg rounded-lg px-3 py-2">${s.reason}</p>`;
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

    openModal('modal-warning');
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
        closeModal('modal-warning');
        closeModal('modal-rider-detail');
        form.requestSubmit ? form.requestSubmit() : form.submit();
        showToast('Warning issued.', 'info');
    }
});
</script>

@endsection