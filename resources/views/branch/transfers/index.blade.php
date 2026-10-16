@extends('branch.layout.app')

@section('title', 'Incoming Transfers')

@section('content')
@php
    // Data contract mula sa controller. May safe defaults para hindi mag-error.
    $counts = array_merge(['in_transit' => 0, 'received_today' => 0, 'discrepancy' => 0], $counts ?? []);
    $activeStatus = $status ?? request('status', 'in_transit');

    $link = fn (string $name, $param = null) => \Illuminate\Support\Facades\Route::has($name)
        ? ($param !== null ? route($name, $param) : route($name))
        : '#';

    $tabs = [
        'in_transit'  => 'Awaiting',
        'received'    => 'Received',
        'discrepancy' => 'Count mismatch',
        'all'         => 'All',
    ];

    $statusBadge = [
        'in_transit'  => ['sh-badge-info', 'In transit'],
        'received'    => ['sh-badge-success', 'Received'],
        'discrepancy' => ['sh-badge-warning', 'Count mismatch'],
    ];
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name ?? 'Your branch' }}</p>
            <h1 class="title">Incoming Transfers</h1>
            <p class="description">Mga batch na ipinadala ng main hub papunta sa branch mo. Bilangin ang parcels pagdating at i-check ang bawat natanggap bago kumpirmahin.</p>
        </div>
    </header>

    {{-- Stats --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="sh-stat">
            <p class="sh-stat-label">Awaiting receipt</p>
            <p class="sh-stat-value">{{ number_format($counts['in_transit']) }}</p>
            <p class="sh-stat-note">Batches na papunta pa lang</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Received today</p>
            <p class="sh-stat-value">{{ number_format($counts['received_today']) }}</p>
            <p class="sh-stat-note">Na-confirm ngayong araw</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Count mismatch</p>
            <p class="sh-stat-value">{{ number_format($counts['discrepancy']) }}</p>
            <p class="sh-stat-note">Kulang o sobra ang bilang</p>
        </div>
    </section>

    {{-- Filter tabs --}}
    <nav class="flex flex-wrap gap-2" aria-label="Transfer status filter">
        @foreach ($tabs as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}"
               class="{{ $activeStatus === $key ? 'sh-btn' : 'sh-btn-ghost' }}"
               @if ($activeStatus === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    {{-- Transfers --}}
    <div class="grid gap-4">
        @forelse (($transfers ?? []) as $transfer)
            @php
                $t = (object) $transfer;
                $items = collect($t->items ?? []);
                $total = (int) ($t->parcels ?? $items->count());
                $state = $t->status ?? 'in_transit';
                [$badgeClass, $badgeLabel] = $statusBadge[$state] ?? ['sh-badge-neutral', ucfirst(str_replace('_', ' ', $state))];
            @endphp

            <article class="sh-surface sh-card-pad">
                <div class="sh-toolbar">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-navy">{{ $t->code ?? '—' }}</h2>
                            <span class="sh-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/50">
                            Galing {{ $t->from ?? 'Main hub' }} · {{ number_format($total) }} parcels · Ipinadala {{ $t->sent_at ?? '—' }}
                        </p>
                    </div>

                    @if ($state !== 'in_transit')
                        <div class="text-right text-xs text-navy/50">
                            <p class="font-semibold text-navy">{{ number_format($t->received_count ?? 0) }} / {{ number_format($total) }} natanggap</p>
                            <p class="mt-0.5">{{ $t->received_at ?? '' }}</p>
                        </div>
                    @endif
                </div>

                @if ($state === 'in_transit')
                    <details class="mt-4 overflow-hidden rounded-2xl border border-gray-border bg-gray-bg/45">
                        <summary class="sh-summary flex items-center justify-between gap-3 px-4 py-3.5">
                            <div>
                                <p class="text-sm font-bold text-navy">Receive this batch</p>
                                <p class="mt-1 text-xs text-navy/45">I-check ang bawat parcel na dumating.</p>
                            </div>
                            <span class="sh-btn-soft">Open manifest</span>
                        </summary>

                        <form method="POST"
                              action="{{ $link('branch.transfers.receive', $t->id ?? null) }}"
                              data-receive-form
                              data-total="{{ $total }}"
                              data-confirm-title="Confirm receipt?"
                              data-confirm-message="Kukumpirmahin mo na ang mga naka-check na parcels ay natanggap ng branch."
                              data-confirm-button="Confirm receipt"
                              class="grid gap-4 border-t border-gray-border px-4 py-4 sm:px-5">
                            @csrf

                            @if ($items->isNotEmpty())
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold text-navy">
                                        Counted: <span data-counted>0</span> / {{ $total }}
                                    </p>
                                    <button type="button" data-select-all class="sh-btn-soft">Select all</button>
                                </div>

                                <div class="grid max-h-72 gap-2 overflow-y-auto pr-1">
                                    @foreach ($items as $item)
                                        @php $p = (object) $item; @endphp
                                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-border bg-white px-4 py-3 hover:border-teal/40">
                                            <input type="checkbox" name="parcel_ids[]" value="{{ $p->tracking ?? '' }}" class="h-4 w-4 rounded border-gray-border text-teal focus:ring-teal">
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-semibold text-navy">{{ $p->tracking ?? '—' }}</span>
                                                <span class="block truncate text-xs text-navy/50">{{ $p->receiver ?? '' }}{{ ! empty($p->barangay) ? ' · '.$p->barangay : '' }}</span>
                                            </span>
                                            @if (! empty($p->cod))
                                                <span class="sh-badge sh-badge-neutral shrink-0">COD ₱{{ number_format($p->cod, 2) }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                <input type="hidden" name="received_count" value="0" data-received-count>
                            @else
                                {{-- Walang manifest: manual na bilang na lang --}}
                                <label class="sh-field max-w-xs">
                                    <span class="sh-label">Counted parcels (kabuuan: {{ $total }})</span>
                                    <input type="number" name="received_count" min="0" max="{{ $total }}" required data-received-count class="sh-input">
                                </label>
                            @endif

                            <label class="sh-field" data-note-wrap hidden>
                                <span class="sh-label">Dahilan ng kulang o sobrang bilang (required)</span>
                                <textarea name="note" rows="3" maxlength="500" placeholder="Hal. 2 parcels ang hindi dumating, may sirang box, atbp." class="sh-textarea"></textarea>
                            </label>

                            <div class="flex justify-end">
                                <button class="sh-btn">Confirm receipt</button>
                            </div>
                        </form>
                    </details>
                @elseif (! empty($t->note))
                    <div class="mt-4 rounded-2xl border border-gray-border bg-white px-4 py-3">
                        <p class="sh-label">Note</p>
                        <p class="mt-1 text-sm text-navy/70">{{ $t->note }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="sh-empty">
                <p class="text-sm font-semibold text-navy">Walang transfer dito</p>
                <p class="mt-1 text-xs text-navy/45">Kapag may ipinadala ang main hub, lalabas dito.</p>
            </div>
        @endforelse
    </div>

    @if (isset($transfers) && is_object($transfers) && method_exists($transfers, 'links'))
        <div>{{ $transfers->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-receive-form]').forEach(function (form) {
    const total = Number(form.dataset.total || 0);
    const boxes = Array.from(form.querySelectorAll('input[name="parcel_ids[]"]'));
    const counted = form.querySelector('[data-counted]');
    const countField = form.querySelector('[data-received-count]');
    const noteWrap = form.querySelector('[data-note-wrap]');
    const note = noteWrap ? noteWrap.querySelector('textarea') : null;
    const selectAll = form.querySelector('[data-select-all]');

    function sync() {
        const n = boxes.length
            ? boxes.filter(function (b) { return b.checked; }).length
            : Number(countField && countField.value || 0);

        if (boxes.length && countField) countField.value = n;
        if (counted) counted.textContent = n;

        const touched = boxes.length ? true : (countField && countField.value !== '');
        const mismatch = touched && n !== total;

        if (noteWrap) noteWrap.hidden = !mismatch;
        if (note) note.required = mismatch;
    }

    boxes.forEach(function (b) { b.addEventListener('change', sync); });
    countField && countField.addEventListener('input', sync);

    selectAll && selectAll.addEventListener('click', function () {
        const allChecked = boxes.every(function (b) { return b.checked; });
        boxes.forEach(function (b) { b.checked = !allChecked; });
        selectAll.textContent = allChecked ? 'Select all' : 'Clear all';
        sync();
    });

    sync();
});
</script>
@endpush