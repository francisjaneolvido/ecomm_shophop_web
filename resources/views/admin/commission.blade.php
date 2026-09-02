@extends('admin.layout')

@section('title', 'Commission')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: remove this once we connect it to the DB / controller.
        Variable names ($counts, $filter, $commissions, $topSellers,
        $summaryTotals) are intentionally matched to what we'll use
        from the controller later, so it's a drop-in replacement
        once real data is wired up.
    ========================================================= --}}
    @php
        use Illuminate\Pagination\LengthAwarePaginator;
        use Carbon\Carbon;

        $filter = request('filter', 'all');   // all, paid, pending, disputed
        $sort   = request('sort', 'newest');

        $commissionRate = 0.10;

        $sampleCommissions = collect([
            (object) [
                'id' => 1, 'order_no' => '#10432', 'seller_name' => 'TechHub PH', 'seller_initials' => 'TH',
                'buyer_name' => 'Marc Villanueva', 'sale_amount' => 2499.00, 'commission_amount' => 249.90,
                'status' => 'paid', 'created_at' => Carbon::now()->subHours(3), 'paid_at' => Carbon::now()->subHours(1),
                'payout_ref' => 'PYT-000891', 'notes' => 'Released together with the seller\'s weekly payout batch.',
                'proof_url' => null, 'proof_name' => 'payout-891-receipt.pdf',
            ],
            (object) [
                'id' => 2, 'order_no' => '#10431', 'seller_name' => "Aling Nena's Store", 'seller_initials' => 'AN',
                'buyer_name' => 'Jennifer Reyes', 'sale_amount' => 780.00, 'commission_amount' => 78.00,
                'status' => 'pending', 'created_at' => Carbon::now()->subHours(9), 'paid_at' => null,
                'payout_ref' => null, 'notes' => 'Awaiting the seller\'s next scheduled payout cycle.',
                'proof_url' => null, 'proof_name' => null,
            ],
            (object) [
                'id' => 3, 'order_no' => '#10430', 'seller_name' => "Jomar's Repair Shop", 'seller_initials' => 'JR',
                'buyer_name' => 'Paulo Santos', 'sale_amount' => 1150.00, 'commission_amount' => 115.00,
                'status' => 'pending', 'created_at' => Carbon::now()->subHours(14), 'paid_at' => null,
                'payout_ref' => null, 'notes' => 'Order was only marked completed yesterday; still within the holding period.',
                'proof_url' => null, 'proof_name' => null,
            ],
            (object) [
                'id' => 4, 'order_no' => '#10429', 'seller_name' => 'TechHub PH', 'seller_initials' => 'TH',
                'buyer_name' => 'Grace Lim', 'sale_amount' => 3200.00, 'commission_amount' => 320.00,
                'status' => 'paid', 'created_at' => Carbon::now()->subDay(), 'paid_at' => Carbon::now()->subHours(20),
                'payout_ref' => 'PYT-000891', 'notes' => 'Released together with the seller\'s weekly payout batch.',
                'proof_url' => null, 'proof_name' => 'payout-891-receipt.pdf',
            ],
            (object) [
                'id' => 5, 'order_no' => '#10428', 'seller_name' => 'Cavite Home Essentials', 'seller_initials' => 'CH',
                'buyer_name' => 'Ronald Cruz', 'sale_amount' => 4590.00, 'commission_amount' => 459.00,
                'status' => 'paid', 'created_at' => Carbon::now()->subDays(1), 'paid_at' => Carbon::now()->subHours(22),
                'payout_ref' => 'PYT-000890', 'notes' => null,
                'proof_url' => null, 'proof_name' => 'payout-890-screenshot.png',
            ],
            (object) [
                'id' => 6, 'order_no' => '#10427', 'seller_name' => 'Better Bites Bakery', 'seller_initials' => 'BB',
                'buyer_name' => 'Angela Torres', 'sale_amount' => 650.00, 'commission_amount' => 65.00,
                'status' => 'disputed', 'created_at' => Carbon::now()->subDays(2), 'paid_at' => null,
                'payout_ref' => null, 'notes' => 'Buyer filed a refund request; commission is on hold pending resolution.',
                'proof_url' => null, 'proof_name' => null,
            ],
            (object) [
                'id' => 7, 'order_no' => '#10426', 'seller_name' => "Aling Nena's Store", 'seller_initials' => 'AN',
                'buyer_name' => 'Kevin Aquino', 'sale_amount' => 320.00, 'commission_amount' => 32.00,
                'status' => 'paid', 'created_at' => Carbon::now()->subDays(2), 'paid_at' => Carbon::now()->subDays(1),
                'payout_ref' => 'PYT-000888', 'notes' => null,
                'proof_url' => null, 'proof_name' => 'payout-888-receipt.pdf',
            ],
            (object) [
                'id' => 8, 'order_no' => '#10425', 'seller_name' => 'QuickFix Auto Parts', 'seller_initials' => 'QF',
                'buyer_name' => 'Michael Ramos', 'sale_amount' => 5200.00, 'commission_amount' => 520.00,
                'status' => 'pending', 'created_at' => Carbon::now()->subDays(3), 'paid_at' => null,
                'payout_ref' => null, 'notes' => 'Seller account is under compliance review — payout is held until cleared.',
                'proof_url' => null, 'proof_name' => null,
            ],
            (object) [
                'id' => 9, 'order_no' => '#10424', 'seller_name' => 'Lush Garden Supplies', 'seller_initials' => 'LG',
                'buyer_name' => 'Diana Flores', 'sale_amount' => 990.00, 'commission_amount' => 99.00,
                'status' => 'paid', 'created_at' => Carbon::now()->subDays(4), 'paid_at' => Carbon::now()->subDays(3),
                'payout_ref' => 'PYT-000885', 'notes' => null,
                'proof_url' => null, 'proof_name' => 'payout-885-receipt.pdf',
            ],
            (object) [
                'id' => 10, 'order_no' => '#10423', 'seller_name' => 'Metro Print Solutions', 'seller_initials' => 'MP',
                'buyer_name' => 'Ferdinand Cruz', 'sale_amount' => 1800.00, 'commission_amount' => 180.00,
                'status' => 'paid', 'created_at' => Carbon::now()->subDays(5), 'paid_at' => Carbon::now()->subDays(4),
                'payout_ref' => 'PYT-000882', 'notes' => null,
                'proof_url' => null, 'proof_name' => 'payout-882-receipt.pdf',
            ],
            (object) [
                'id' => 11, 'order_no' => '#10422', 'seller_name' => 'TechHub PH', 'seller_initials' => 'TH',
                'buyer_name' => 'Sheila Mercado', 'sale_amount' => 899.00, 'commission_amount' => 89.90,
                'status' => 'pending', 'created_at' => Carbon::now()->subDays(6), 'paid_at' => null,
                'payout_ref' => null, 'notes' => null,
                'proof_url' => null, 'proof_name' => null,
            ],
            (object) [
                'id' => 12, 'order_no' => '#10421', 'seller_name' => "Jomar's Repair Shop", 'seller_initials' => 'JR',
                'buyer_name' => 'Anton Garcia', 'sale_amount' => 430.00, 'commission_amount' => 43.00,
                'status' => 'disputed', 'created_at' => Carbon::now()->subDays(7), 'paid_at' => null,
                'payout_ref' => null, 'notes' => 'Item reported as not-as-described; investigation ongoing.',
                'proof_url' => null, 'proof_name' => null,
            ],
        ]);

        // Totals for the stat cards — computed off the full sample set,
        // not the filtered/paginated view below.
        $totalCommissionThisMonth = $sampleCommissions->sum('commission_amount');
        $grossSalesThisMonth      = $sampleCommissions->sum('sale_amount');
        $transactionsThisMonth    = $sampleCommissions->count();
        $pendingPayoutTotal       = $sampleCommissions->where('status', 'pending')->sum('commission_amount');

        $counts = [
            'all'      => $sampleCommissions->count(),
            'paid'     => $sampleCommissions->where('status', 'paid')->count(),
            'pending'  => $sampleCommissions->where('status', 'pending')->count(),
            'disputed' => $sampleCommissions->where('status', 'disputed')->count(),
        ];

        // ============ COMMISSION SUMMARY (also the basis of the PDF export) ============
        $summaryTotals = collect(['paid', 'pending', 'disputed'])->mapWithKeys(function ($status) use ($sampleCommissions) {
            $rows = $sampleCommissions->where('status', $status);
            return [$status => [
                'count'  => $rows->count(),
                'amount' => $rows->sum('commission_amount'),
            ]];
        });
        $summaryGrandTotal = $sampleCommissions->sum('commission_amount');
        $summaryStyles = [
            'paid'     => ['bar' => 'bg-mint-dark',   'dot' => 'bg-mint-dark',   'text' => 'text-mint-dark',  'label' => 'Paid'],
            'pending'  => ['bar' => 'bg-yellow-500',  'dot' => 'bg-yellow-500',  'text' => 'text-yellow-700', 'label' => 'Pending Payout'],
            'disputed' => ['bar' => 'bg-coral',       'dot' => 'bg-coral',       'text' => 'text-coral',      'label' => 'Disputed'],
        ];

        // Filter simulation, same idea as Seller Compliance's tabs
        $filtered = match ($filter) {
            'paid'     => $sampleCommissions->where('status', 'paid'),
            'pending'  => $sampleCommissions->where('status', 'pending'),
            'disputed' => $sampleCommissions->where('status', 'disputed'),
            default    => $sampleCommissions,
        };

        // Search simulation — order number or seller name
        if ($search = request('search')) {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($c) use ($needle) {
                return str_contains(strtolower($c->order_no), $needle)
                    || str_contains(strtolower($c->seller_name), $needle);
            });
        }

        // Sort simulation — stands in for ->orderBy() once wired to the DB
        $filtered = match ($sort) {
            'oldest'  => $filtered->sortBy('created_at'),
            'highest' => $filtered->sortByDesc('commission_amount'),
            'lowest'  => $filtered->sortBy('commission_amount'),
            default   => $filtered->sortByDesc('created_at'), // newest
        };

        $filtered = $filtered->values();

        $commissions = new LengthAwarePaginator(
            $filtered,
            $filtered->count(),
            8,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Style map used by both the table rows AND the modal (JS side)
        $statusStyles = [
            'paid'     => ['badge' => 'text-mint-dark bg-mint/15', 'dot' => 'bg-mint-dark', 'label' => 'Paid'],
            'pending'  => ['badge' => 'text-yellow-700 bg-yellow/20', 'dot' => 'bg-yellow-600', 'label' => 'Pending Payout'],
            'disputed' => ['badge' => 'text-coral bg-coral/10', 'dot' => 'bg-coral', 'label' => 'Disputed'],
        ];

        // Top Commission Contributors — grouped + summed from the full set
        $topSellers = $sampleCommissions
            ->groupBy('seller_name')
            ->map(function ($group, $name) {
                return (object) [
                    'name'     => $name,
                    'initials' => $group->first()->seller_initials,
                    'total'    => $group->sum('commission_amount'),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Flattened + string-formatted version for the modal JS
        $commissionsForJs = $sampleCommissions->map(function ($c) use ($commissionRate) {
            return [
                'id'                => $c->id,
                'order_no'          => $c->order_no,
                'seller_name'       => $c->seller_name,
                'seller_initials'   => $c->seller_initials,
                'buyer_name'        => $c->buyer_name,
                'sale_amount'       => number_format($c->sale_amount, 2),
                'commission_rate'   => ($commissionRate * 100) . '%',
                'commission_amount' => number_format($c->commission_amount, 2),
                'status'            => $c->status,
                'created_at'        => $c->created_at->format('M d, Y g:ia') . ' · ' . $c->created_at->diffForHumans(),
                'paid_at'           => $c->paid_at ? $c->paid_at->format('M d, Y g:ia') . ' · ' . $c->paid_at->diffForHumans() : null,
                'payout_ref'        => $c->payout_ref,
                'notes'             => $c->notes,
                'proof_name'        => $c->proof_name,
            ];
        })->values();
    @endphp


    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-navy">Commission ({{ $commissionRate * 100 }}%)</h1>
            <p class="text-sm text-slate-500 mt-1">Subaybayan ang kita ng platform mula sa mga completed na transactions.</p>
        </div>

        {{-- ============ DOWNLOAD PDF ============
             TODO: point this to a real export route once the backend
             is wired up, e.g.:
               Route::get('/admin/commissions/export-pdf', [CommissionController::class, 'exportPdf'])
             The controller should re-run the same filter/sort/search
             query (not just the current page) against barryvdh/laravel-dompdf
             (composer require barryvdh/laravel-dompdf), rendering a
             print-friendly Blade view that mirrors the summary +
             table below. Query string is preserved here so the export
             respects whatever filter/search/sort is active on screen. --}}
        <a href="{{ route('admin.commissions.export-pdf', request()->query()) }}"
           class="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-sm font-semibold text-white bg-navy hover:opacity-90 transition shrink-0">
            <x-lucide-file-down class="w-4 h-4" />
            Download Report (PDF)
        </a>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center">
                    <x-lucide-percent class="w-5 h-5 text-yellow-600" />
                </div>
                <span class="text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full">+12% vs last month</span>
            </div>
            <p class="text-2xl font-bold text-navy">₱{{ number_format($totalCommissionThisMonth, 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Commission This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center">
                    <x-lucide-trending-up class="w-5 h-5 text-mint-dark" />
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">₱{{ number_format($grossSalesThisMonth, 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Gross Sales This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center">
                    <x-lucide-receipt class="w-5 h-5 text-sky" />
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">{{ $transactionsThisMonth }}</p>
            <p class="text-xs text-slate-500 mt-1">Transactions This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center">
                    <x-lucide-clock class="w-5 h-5 text-coral" />
                </div>
                <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">Unpaid</span>
            </div>
            <p class="text-2xl font-bold text-navy">₱{{ number_format($pendingPayoutTotal, 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Pending Payout Deductions</p>
        </div>

    </div>

    {{-- ============ COMMISSION SUMMARY ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-navy text-sm">Commission Summary</h2>
            <p class="text-xs text-slate-400">Total: <span class="font-semibold text-navy">₱{{ number_format($summaryGrandTotal, 2) }}</span></p>
        </div>

        {{-- Stacked breakdown bar --}}
        <div class="w-full h-3 rounded-full overflow-hidden bg-slate-100 flex mb-4">
            @foreach ($summaryTotals as $status => $data)
                @php
                    $pct = $summaryGrandTotal > 0 ? round(($data['amount'] / $summaryGrandTotal) * 100, 1) : 0;
                @endphp
                @if ($pct > 0)
                    <div class="{{ $summaryStyles[$status]['bar'] }} h-full" style="width: {{ $pct }}%" title="{{ $summaryStyles[$status]['label'] }}: {{ $pct }}%"></div>
                @endif
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($summaryTotals as $status => $data)
                @php
                    $pct = $summaryGrandTotal > 0 ? round(($data['amount'] / $summaryGrandTotal) * 100, 1) : 0;
                @endphp
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $summaryStyles[$status]['dot'] }}"></span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold {{ $summaryStyles[$status]['text'] }}">{{ $summaryStyles[$status]['label'] }} · {{ $pct }}%</p>
                        <p class="text-xs text-slate-500">₱{{ number_format($data['amount'], 2) }} across {{ $data['count'] }} {{ Str::plural('entry', $data['count']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ FILTER TABS + SORT + SEARCH ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'all', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'all' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                All <span class="ml-1 opacity-70">({{ $counts['all'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'paid', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'paid' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Paid <span class="ml-1 opacity-70">({{ $counts['paid'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'pending', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'pending' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Pending <span class="ml-1 opacity-70">({{ $counts['pending'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'disputed', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'disputed' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Disputed <span class="ml-1 opacity-70">({{ $counts['disputed'] }})</span>
            </a>
        </div>

        <div class="flex items-center gap-2 w-full xl:w-auto">

            {{-- SORT --}}
            <form method="GET" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" onchange="this.form.submit()"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="highest" {{ $sort === 'highest' ? 'selected' : '' }}>Highest commission</option>
                    <option value="lowest" {{ $sort === 'lowest' ? 'selected' : '' }}>Lowest commission</option>
                </select>
            </form>

            {{-- SEARCH --}}
            <form method="GET" class="relative w-full xl:w-72">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by order # or seller..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
            </form>

        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============ COMMISSION ENTRIES TABLE ============ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-navy text-sm">Commission Entries</h2>
                <span class="text-xs text-slate-400">{{ $commissions->total() }} total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Order</th>
                            <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Seller</th>
                            <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Sale Amount</th>
                            <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Commission</th>
                            <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Status</th>
                            <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($commissions as $c)
                            @php $style = $statusStyles[$c->status] ?? $statusStyles['pending']; @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 font-medium text-navy">{{ $c->order_no }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $c->seller_name }}</td>
                                <td class="px-5 py-4 text-right text-navy">₱{{ number_format($c->sale_amount, 2) }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-mint-dark">₱{{ number_format($c->commission_amount, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $style['badge'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                        {{ $style['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" class="commission-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50" data-commission-id="{{ $c->id }}">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <x-lucide-inbox class="w-5 h-5" />
                                    </div>
                                    <p class="text-sm font-semibold text-navy mt-3">No commission entries found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try a different filter or search term.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============ PAGINATION ============ --}}
            @if ($commissions->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $commissions->withQueryString()->links() }}
                </div>
            @endif
        </div>

        {{-- ============ TOP EARNING SELLERS ============ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 h-fit">
            <h2 class="font-semibold text-navy text-sm mb-4">Top Commission Contributors</h2>

            <div class="space-y-4">
                @foreach ($topSellers as $seller)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-sky">{{ $seller->initials }}</span>
                            </div>
                            <p class="text-sm font-medium text-navy truncate">{{ $seller->name }}</p>
                        </div>
                        <p class="text-sm font-semibold text-navy shrink-0">₱{{ number_format($seller->total, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>


    {{-- =========================================================
        COMMISSION DETAILS MODAL
        Same chrome/pattern as the Seller Compliance details modal.
    ========================================================= --}}
    <div id="commissionModalOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="commissionModalPanel" class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button type="button" id="commissionModalClose" aria-label="Close"
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
                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">COMMISSION ENTRY</p>
                        <h2 id="modalOrderNo" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                        <p id="modalSellerName" class="text-xs text-slate-500 truncate mt-0.5"></p>
                    </div>
                </div>

                <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full">
                    <span id="modalStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                    <span id="modalStatusLabel"></span>
                </span>
            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 space-y-4">

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Order Details</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Buyer</dt>
                            <dd id="modalBuyerName" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Order Date</dt>
                            <dd id="modalCreatedAt" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Commission Breakdown</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Sale Amount</dt>
                            <dd id="modalSaleAmount" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Commission Rate</dt>
                            <dd id="modalRate" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0 font-semibold">Commission Amount</dt>
                            <dd id="modalCommissionAmount" class="text-xs font-semibold text-mint-dark text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Payout Information</p>
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Payout Reference</dt>
                            <dd id="modalPayoutRef" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Paid On</dt>
                            <dd id="modalPaidAt" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                {{-- Proof of payment/resolution preview, if one was already
                     uploaded for this entry --}}
                <div id="modalProofWrap" class="hidden bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Attached Proof</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 text-slate-400">
                            <x-lucide-file-check-2 class="w-5 h-5" />
                        </div>
                        <p id="modalProofName" class="text-xs text-slate-700 truncate"></p>
                    </div>
                </div>

                <div id="modalNotesWrap">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Notes</p>
                    <p id="modalNotes" class="text-xs text-slate-600 leading-relaxed"></p>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                <button type="button" id="modalActionBtn" class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold transition-all duration-300"></button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        MARK AS PAID / RESOLVE — CONFIRMATION MODAL
        Reused pattern from Seller Compliance's confirm modal, now
        with a proof-of-payment / proof-of-resolution upload.
    ========================================================= --}}
    <div id="confirmModalOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="confirmModalPanel" class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150 p-6 max-h-[90vh] overflow-y-auto">

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Payout reference</label>
                <input type="text" id="confirmPayoutRef" placeholder="e.g. PYT-000892"
                    class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-mint/40">
            </div>

            {{-- ============ PROOF OF PAYMENT / RESOLUTION UPLOAD ============ --}}
            <div class="mb-4">
                <label id="confirmProofLabel" class="block text-xs font-semibold text-slate-500 mb-1.5">Proof of Payment</label>

                <input type="file" id="confirmProofInput" accept="image/png,image/jpeg,image/webp,application/pdf" class="hidden">

                <div id="proofDropzone"
                     class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center cursor-pointer hover:border-mint hover:bg-mint/5 transition"
                     onclick="document.getElementById('confirmProofInput').click()">
                    <div id="proofEmptyState" class="flex flex-col items-center gap-1.5 text-slate-400">
                        <x-lucide-upload-cloud class="w-6 h-6" />
                        <p class="text-xs font-medium text-slate-500">Click to upload receipt or screenshot</p>
                        <p class="text-[10px] text-slate-400">PNG, JPG, or PDF · Max 5MB</p>
                    </div>

                    <div id="proofFilledState" class="hidden flex items-center gap-3 text-left">
                        <div id="proofThumbWrap" class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                            <img id="proofThumbImg" class="hidden w-full h-full object-cover" alt="Proof preview">
                            <x-lucide-file-text id="proofThumbIcon" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="proofFileName" class="text-xs font-medium text-navy truncate"></p>
                            <p id="proofFileSize" class="text-[10px] text-slate-400"></p>
                        </div>
                        <button type="button" id="proofRemoveBtn" class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:bg-coral/10 hover:text-coral transition">
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
                <p id="proofError" class="hidden text-[11px] text-coral mt-1.5"></p>
            </div>

            <div id="confirmVerifyNotesWrap" class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Notes (optional)</label>
                <textarea id="confirmVerifyNotes" rows="2" placeholder="Add any remarks about this resolution..."
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


    {{-- =========================================================
        MODAL DATA + BEHAVIOR
        TODO: once this is on the DB, this can become a route-based
        fetch (admin.commissions.show as JSON) instead of a hardcoded
        array. confirmProceedBtn should POST as multipart/form-data
        (FormData, since a file is attached) to the mark-paid /
        resolve endpoint instead of just console.log.
    ========================================================= --}}
    <script>
        const commissionsData = @json($commissionsForJs);
        const MAX_PROOF_BYTES = 5 * 1024 * 1024; // 5MB

        const statusBadge = {
            paid:     { dot: 'bg-mint-dark',   text: 'text-mint-dark',   bg: 'bg-mint/15',    label: 'Paid' },
            pending:  { dot: 'bg-yellow-600',  text: 'text-yellow-700',  bg: 'bg-yellow/20',  label: 'Pending Payout' },
            disputed: { dot: 'bg-coral',       text: 'text-coral',       bg: 'bg-coral/10',   label: 'Disputed' },
        };

        /* -----------------------------------------------------------
           COMMISSION DETAILS MODAL
        ----------------------------------------------------------- */
        const overlay  = document.getElementById('commissionModalOverlay');
        const panel    = document.getElementById('commissionModalPanel');
        const closeBtn = document.getElementById('commissionModalClose');
        const modalActionBtn = document.getElementById('modalActionBtn');

        let currentModalCommission = null;

        function openCommissionModal(id) {
            const c = commissionsData.find(x => x.id === id);
            if (!c) return;

            currentModalCommission = c;

            document.getElementById('modalInitials').textContent = c.seller_initials;
            document.getElementById('modalOrderNo').textContent = c.order_no;
            document.getElementById('modalSellerName').textContent = c.seller_name;

            const sBadge = statusBadge[c.status] || statusBadge.pending;
            document.getElementById('modalStatusBadge').className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            document.getElementById('modalStatusDot').className = 'w-1.5 h-1.5 rounded-full ' + sBadge.dot;
            document.getElementById('modalStatusLabel').textContent = sBadge.label;

            document.getElementById('modalBuyerName').textContent = c.buyer_name;
            document.getElementById('modalCreatedAt').textContent = c.created_at;
            document.getElementById('modalSaleAmount').textContent = '₱' + c.sale_amount;
            document.getElementById('modalRate').textContent = c.commission_rate;
            document.getElementById('modalCommissionAmount').textContent = '₱' + c.commission_amount;
            document.getElementById('modalPayoutRef').textContent = c.payout_ref || '—';
            document.getElementById('modalPaidAt').textContent = c.paid_at || '—';

            const proofWrap = document.getElementById('modalProofWrap');
            if (c.proof_name) {
                document.getElementById('modalProofName').textContent = c.proof_name;
                proofWrap.classList.remove('hidden');
            } else {
                proofWrap.classList.add('hidden');
            }

            const notesWrap = document.getElementById('modalNotesWrap');
            const notesEl = document.getElementById('modalNotes');
            if (c.notes) {
                notesEl.textContent = c.notes;
                notesWrap.classList.remove('hidden');
            } else {
                notesWrap.classList.add('hidden');
            }

            // Dynamic footer action based on status
            if (c.status === 'pending') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Mark as Paid';
                modalActionBtn.style.display = '';
            } else if (c.status === 'disputed') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Resolve & Mark Paid';
                modalActionBtn.style.display = '';
            } else {
                modalActionBtn.style.display = 'none';
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function closeCommissionModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.commission-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openCommissionModal(parseInt(btn.dataset.commissionId, 10)));
        });

        closeBtn.addEventListener('click', closeCommissionModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeCommissionModal(); });

        modalActionBtn.addEventListener('click', () => {
            if (!currentModalCommission) return;
            openConfirmModal(currentModalCommission);
        });

        /* -----------------------------------------------------------
           MARK AS PAID / RESOLVE — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay    = document.getElementById('confirmModalOverlay');
        const confirmPanel      = document.getElementById('confirmModalPanel');
        const confirmIconWrap   = document.getElementById('confirmIconWrap');
        const confirmTitle      = document.getElementById('confirmTitle');
        const confirmMessage    = document.getElementById('confirmMessage');
        const confirmProceedBtn = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn  = document.getElementById('confirmCancelBtn');
        const confirmProofLabel = document.getElementById('confirmProofLabel');

        const proofInput      = document.getElementById('confirmProofInput');
        const proofEmptyState = document.getElementById('proofEmptyState');
        const proofFilledState= document.getElementById('proofFilledState');
        const proofThumbImg   = document.getElementById('proofThumbImg');
        const proofThumbIcon  = document.getElementById('proofThumbIcon');
        const proofFileName   = document.getElementById('proofFileName');
        const proofFileSize   = document.getElementById('proofFileSize');
        const proofRemoveBtn  = document.getElementById('proofRemoveBtn');
        const proofError      = document.getElementById('proofError');

        let activeConfirmCommission = null;
        let selectedProofFile = null;

        const checkIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

        function resetProofField() {
            selectedProofFile = null;
            proofInput.value = '';
            proofError.classList.add('hidden');
            proofError.textContent = '';
            proofFilledState.classList.add('hidden');
            proofFilledState.classList.remove('flex');
            proofEmptyState.classList.remove('hidden');
            proofThumbImg.classList.add('hidden');
            proofThumbImg.src = '';
            proofThumbIcon.classList.remove('hidden');
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        proofInput.addEventListener('change', () => {
            const file = proofInput.files[0];
            if (!file) return;

            if (file.size > MAX_PROOF_BYTES) {
                proofError.textContent = 'File is too large. Please upload a file under 5MB.';
                proofError.classList.remove('hidden');
                proofInput.value = '';
                return;
            }

            selectedProofFile = file;
            proofError.classList.add('hidden');

            proofFileName.textContent = file.name;
            proofFileSize.textContent = formatFileSize(file.size);

            if (file.type.startsWith('image/')) {
                proofThumbImg.src = URL.createObjectURL(file);
                proofThumbImg.classList.remove('hidden');
                proofThumbIcon.classList.add('hidden');
            } else {
                proofThumbImg.classList.add('hidden');
                proofThumbIcon.classList.remove('hidden');
            }

            proofEmptyState.classList.add('hidden');
            proofFilledState.classList.remove('hidden');
            proofFilledState.classList.add('flex');
        });

        proofRemoveBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetProofField();
        });

        function openConfirmModal(commission) {
            activeConfirmCommission = commission;
            resetProofField();

            const isDispute = commission.status === 'disputed';

            confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
            confirmIconWrap.innerHTML = checkIconSvg;
            confirmTitle.textContent = isDispute ? 'Resolve and mark as paid?' : 'Mark this commission as paid?';
            confirmMessage.textContent = isDispute
                ? `Confirm that the dispute on order ${commission.order_no} has been resolved and the commission can be released.`
                : `Confirm that the commission for order ${commission.order_no} has been released to the platform's payout ledger.`;
            confirmProofLabel.textContent = isDispute ? 'Proof of Resolution' : 'Proof of Payment';
            confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
            confirmProceedBtn.textContent = isDispute ? 'Confirm Resolved' : 'Confirm Paid';
            document.getElementById('confirmPayoutRef').value = '';
            document.getElementById('confirmVerifyNotes').value = '';

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

        confirmProceedBtn.addEventListener('click', () => {
            if (!activeConfirmCommission) return;

            // TODO: replace with an actual multipart request to the backend, e.g.:
            // const formData = new FormData();
            // formData.append('payout_ref', document.getElementById('confirmPayoutRef').value);
            // formData.append('notes', document.getElementById('confirmVerifyNotes').value);
            // if (selectedProofFile) formData.append('proof', selectedProofFile);
            // axios.post(`/admin/commissions/${activeConfirmCommission.id}/resolve`, formData);
            console.log('Resolving commission', activeConfirmCommission.id, {
                payout_ref: document.getElementById('confirmPayoutRef').value,
                notes: document.getElementById('confirmVerifyNotes').value,
                proof: selectedProofFile ? selectedProofFile.name : null,
            });

            closeConfirmModal();
            closeCommissionModal();
        });

        // Escape key — close whichever of the two modals is open
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeCommissionModal();
        });
    </script>

@endsection