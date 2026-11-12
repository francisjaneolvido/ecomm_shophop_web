@extends('admin.layout')

@section('title', 'Complaints & Disputes')

@section('content')

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">{{ $errors->first() }}</div>
    @endif

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy">Complaints &amp; Disputes</h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">Track, review, and resolve complaints between buyers, sellers, riders, and logistics partners.</p>
        </div>
        <button type="button" id="newCaseToggle"
            class="admin-btn admin-btn-primary">
            + New Case
        </button>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @php
            $cards = [
                ['Open', $counts['open'], 'bg-amber-100 text-amber-600', 'clock-3'],
                ['Under Review', $counts['under_review'], 'bg-sky/10 text-sky', 'eye'],
                ['Awaiting Response', $counts['awaiting_response'], 'bg-violet-100 text-violet-600', 'clock'],
                ['Closed', $counts['resolved'], 'bg-mint/10 text-mint-dark', 'circle-check'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $tone, $icon])
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">{{ $label }}</p>
                        <p class="text-2xl font-bold text-navy mt-1">{{ $value }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl {{ $tone }} flex items-center justify-center">
                        <x-dynamic-component :component="'lucide-'.$icon" class="w-5 h-5" />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
            <p class="text-xs text-slate-400 font-medium">Avg. Resolution Time</p>
            <p class="text-2xl font-bold text-navy mt-1">{{ $counts['avg_hours'] === null ? '—' : $counts['avg_hours'].' h' }}</p>
        </div>
    </div>

    {{-- NEW CASE FORM --}}
    <div id="newCasePanel" class="{{ $errors->any() && old('subject') ? '' : 'hidden' }} bg-white border border-slate-200 rounded-2xl p-5 mb-6">
        <h2 class="font-semibold text-navy text-sm mb-1">Log a new case</h2>
        <p class="text-[11px] text-slate-400 mb-4">Complainant and respondent are optional; use their account email to link them.</p>
        <form method="POST" action="{{ route('admin.disputes.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Type</label>
                <select name="type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy bg-white">
                    @foreach (\App\Models\Admin\Dispute::TYPES as $k => $v)
                        <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Priority</label>
                <select name="priority" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy bg-white">
                    @foreach (\App\Models\Admin\Dispute::PRIORITIES as $k => $v)
                        <option value="{{ $k }}" @selected(old('priority', 'normal') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-navy mb-1.5">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="160" required
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy">
            </div>
            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Order # (optional)</label>
                <input type="number" name="order_id" value="{{ old('order_id') }}" min="1"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy">
            </div>
            <div></div>
            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Complainant email (optional)</label>
                <input type="email" name="complainant_email" value="{{ old('complainant_email') }}"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy">
            </div>
            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Respondent email (optional)</label>
                <input type="email" name="respondent_email" value="{{ old('respondent_email') }}"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-navy mb-1.5">Description</label>
                <textarea name="description" rows="4" maxlength="5000" required
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-navy">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="admin-btn admin-btn-teal">Create Case</button>
            </div>
        </form>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="flex flex-col lg:flex-row lg:items-center gap-3 mb-5">
        <select name="status" onchange="this.form.submit()" class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium">
            <option value="all">All statuses</option>
            @foreach (\App\Models\Admin\Dispute::STATUSES as $k => $v)
                <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <select name="type" onchange="this.form.submit()" class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium">
            <option value="all">All types</option>
            @foreach (\App\Models\Admin\Dispute::TYPES as $k => $v)
                <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <div class="relative w-full lg:w-72">
            <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Case no., subject, email, order #..."
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-navy placeholder:text-slate-400 focus:outline-none focus:border-mint">
        </div>
        @if ($status !== 'all' || $type !== 'all' || $search !== '')
            <a href="{{ route('admin.disputes') }}" class="text-xs font-semibold text-slate-500 hover:text-navy">Clear filters</a>
        @endif
    </form>

    {{-- LIST --}}
    <div class="ops-panel bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Cases</h2>
            <span class="text-xs text-slate-400">{{ $disputes->total() }} total</span>
        </div>

        <div class="admin-table-scroll">
          <table class="min-w-[760px]">
            <thead><tr><th>Case / Subject</th><th>Type / Order</th><th>Participants</th><th>Status</th><th>Filed / Age</th><th class="text-right">Action</th></tr></thead>
            <tbody>
              @forelse ($disputes as $d)
              <tr>
                <td><strong class="text-navy">{{ $d->case_no }}</strong><div class="mt-1 max-w-[250px] text-xs text-slate-600">{{ $d->subject }}</div></td>
                <td>{{ $d->type_label }}<div class="mt-1 text-[11px] text-slate-500">{{ $d->order_id ? 'Order #'.$d->order_id : '—' }}</div></td>
                <td><div class="max-w-[220px] text-xs">{{ $d->complainant?->display_name ?? 'No complainant' }}</div><div class="text-[11px] text-slate-500">→ {{ $d->respondent?->display_name ?? 'No respondent' }}</div></td>
                <td><span class="admin-status {{ $d->is_closed ? 'admin-status-ok' : ($d->priority === 'high' ? 'admin-status-danger' : 'admin-status-warn') }}">{{ $d->status_label }}</span>@if($d->priority === 'high')<div class="mt-1 text-[10px] font-semibold text-red-600">High priority</div>@endif</td>
                <td class="text-xs">{{ $d->created_at->format('M j, Y') }}<div class="mt-1 text-slate-500">{{ $d->age_days }} day(s)</div></td>
                <td><div class="admin-row-actions"><a data-console-details href="{{ route('admin.disputes.show', $d) }}" class="admin-btn">View case</a></div></td>
              </tr>
              @empty<tr><td colspan="6" class="admin-empty">{{ $counts['all'] === 0 ? 'No complaints have been logged yet.' : 'No cases match the selected filters.' }}</td></tr>@endforelse
            </tbody>
          </table>
        </div>

        @if ($disputes->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $disputes->links() }}</div>
        @endif
    </div>

    <script>
        document.getElementById('newCaseToggle').addEventListener('click', () => {
            document.getElementById('newCasePanel').classList.toggle('hidden');
        });
    </script>

@endsection