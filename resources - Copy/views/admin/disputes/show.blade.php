@extends('admin.layout')

@section('title', $dispute->case_no.' - Dispute')

@section('content')

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">{{ $errors->first() }}</div>
    @endif

    <a href="{{ route('admin.disputes') }}" class="text-xs font-semibold text-slate-500 hover:text-navy">&larr; Back to Complaints &amp; Disputes</a>

    {{-- HEADER --}}
    <div class="mt-3 mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-slate-400">{{ $dispute->case_no }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $dispute->status_badge }}">{{ $dispute->status_label }}</span>
                @if ($dispute->priority === 'high')
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-coral/10 text-coral">High priority</span>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-navy mt-1">{{ $dispute->subject }}</h1>
            <p class="text-xs text-slate-400 mt-1">
                {{ $dispute->type_label }} · Filed {{ $dispute->created_at->format('M d, Y g:ia') }}
                · {{ $dispute->is_closed ? 'Closed in' : 'Open for' }} {{ $dispute->age_days }} day(s)
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: details + timeline --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Description</p>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $dispute->description }}</p>
            </div>

            @if ($dispute->is_closed)
                <div class="bg-mint/5 border border-mint/30 rounded-2xl p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-mint-dark mb-2">Resolution</p>
                    <p class="text-sm font-semibold text-navy">{{ \App\Models\Admin\Dispute::OUTCOMES[$dispute->resolution_outcome] ?? $dispute->resolution_outcome }}</p>
                    <p class="text-sm text-slate-700 mt-2 whitespace-pre-line">{{ $dispute->resolution_note }}</p>
                    <p class="text-[11px] text-slate-400 mt-3">
                        By {{ $dispute->resolver?->display_name ?? 'Unknown' }} · {{ $dispute->resolved_at?->format('M d, Y g:ia') }}
                    </p>
                    <form method="POST" action="{{ route('admin.disputes.reopen', $dispute) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-600 border border-slate-200 hover:bg-white transition">Reopen case</button>
                    </form>
                    @if (in_array($dispute->resolution_outcome, ['refund_buyer', 'split'], true))
                        <p class="text-[11px] text-slate-400 mt-3">Note: closing a case does not move money. Process the actual refund in Returns &amp; Refunds.</p>
                    @endif
                </div>
            @endif

            {{-- TIMELINE / NOTES --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Timeline &amp; Internal Notes</p>
                <ul class="space-y-3 mb-4">
                    @forelse ($dispute->messages as $m)
                        <li class="flex gap-2.5">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-mint-dark shrink-0"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $m->body }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $m->author?->display_name ?? 'System' }} · {{ $m->created_at->format('M d, Y g:ia') }}
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">No notes yet.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.disputes.message', $dispute) }}">
                    @csrf
                    <textarea name="body" rows="3" maxlength="3000" required placeholder="Add an internal note (visible to admins only)..."
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy focus:outline-none focus:border-mint"></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-navy hover:opacity-90 transition">Add note</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: parties + actions --}}
        <div class="space-y-5">

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Parties</p>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-slate-400">Complainant</dt>
                        <dd class="text-sm text-navy font-semibold">{{ $dispute->complainant?->display_name ?? '—' }}</dd>
                        @if ($dispute->complainant)
                            <dd class="text-[11px] text-slate-400">{{ ucfirst($dispute->complainant->account_type) }} · {{ $dispute->complainant->email }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Respondent</dt>
                        <dd class="text-sm text-navy font-semibold">{{ $dispute->respondent?->display_name ?? '—' }}</dd>
                        @if ($dispute->respondent)
                            <dd class="text-[11px] text-slate-400">{{ ucfirst($dispute->respondent->account_type) }} · {{ $dispute->respondent->email }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Order</dt>
                        <dd class="text-sm text-navy font-semibold">{{ $dispute->order_id ? '#'.$dispute->order_id : '—' }}</dd>
                    </div>
                </dl>
            </div>

            @unless ($dispute->is_closed)
                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Update status</p>
                    <form method="POST" action="{{ route('admin.disputes.status', $dispute) }}" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy bg-white">
                            @foreach (['open', 'under_review', 'awaiting_response'] as $s)
                                <option value="{{ $s }}" @selected($dispute->status === $s)>{{ \App\Models\Admin\Dispute::STATUSES[$s] }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="h-9 px-4 rounded-full text-xs font-semibold text-white bg-sky hover:opacity-90 transition">Save</button>
                    </form>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Resolve / close case</p>
                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-3"
                          onsubmit="return confirm('Close this case? You can reopen it later.')">
                        @csrf
                        <select name="resolution_outcome" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy bg-white">
                            <option value="">Select outcome...</option>
                            @foreach (\App\Models\Admin\Dispute::OUTCOMES as $k => $v)
                                <option value="{{ $k }}" @selected(old('resolution_outcome') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        <textarea name="resolution_note" rows="4" maxlength="3000" required placeholder="Resolution note (explain the decision)..."
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy focus:outline-none focus:border-mint">{{ old('resolution_note') }}</textarea>
                        <button type="submit" class="w-full h-9 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition">Close case</button>
                    </form>
                </div>
            @endunless
        </div>
    </div>

@endsection