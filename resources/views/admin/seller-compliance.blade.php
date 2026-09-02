@extends('admin.layout')

@section('title', 'Seller Compliance')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: remove this once we connect it to the DB / controller.
        The variable names ($counts, $filter, $sellers) are intentionally
        matched to what we'll use from the controller later, so it's a
        drop-in replacement once real data is wired up.
    ========================================================= --}}
    @php
        use Illuminate\Pagination\LengthAwarePaginator;
        use Carbon\Carbon;

        $filter = request('filter', 'pending');
        $sort   = request('sort', 'newest');

        // Totals as if they came from a DB count query — intentionally
        // larger than what's in $sampleSellers (only 8), since in
        // practice we wouldn't load every row just to count them.
        $counts = [
            'pending'  => 19,
            'verified' => 312,
            'flagged'  => 4,
        ];

        $sampleSellers = collect([
            (object) [
                'id' => 1, 'initials' => 'TH', 'business_name' => 'TechHub PH',
                'owner_name' => 'Tomas Herrera', 'business_type' => 'Sole Proprietorship',
                'category' => 'Electronics & Gadgets',
                'email' => 'techhub.ph@example.com', 'phone' => '+63 917 001 2233',
                'address' => 'Unit 4B, Pilar Complex, Los Baños, Laguna',
                'status' => 'pending', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(3), 'updated_at' => Carbon::now()->subHours(6),
                'notes' => 'DTI and BIR are complete; only the Barangay Business Permit is still pending.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Submitted BIR Form 2303', 'time' => Carbon::now()->subHours(6)->diffForHumans()],
                    ['label' => 'Submitted DTI Business Permit', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(3)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 2, 'initials' => 'AN', 'business_name' => "Aling Nena's Store",
                'owner_name' => 'Elena Bautista', 'business_type' => 'Sole Proprietorship',
                'category' => 'Sari-Sari / General Merchandise',
                'email' => 'alingnenastore@example.com', 'phone' => '+63 918 112 3344',
                'address' => 'Purok 3, Barangay San Antonio, Bay, Laguna',
                'status' => 'pending', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(1), 'updated_at' => Carbon::now()->subHours(20),
                'notes' => 'New applicant; only the Barangay permit has been submitted so far.',
                'documents' => [
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'DTI Business Permit', 'status' => 'missing', 'url' => null],
                    ['label' => 'BIR Form 2303', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Submitted Barangay Business Permit', 'time' => Carbon::now()->subHours(20)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 3, 'initials' => 'JR', 'business_name' => "Jomar's Repair Shop",
                'owner_name' => 'Jomar Reyes', 'business_type' => 'Sole Proprietorship',
                'category' => 'Electronics Repair Services',
                'email' => 'jomarrepairshop@example.com', 'phone' => '+63 919 223 4455',
                'address' => '12 Rizal St., Poblacion, Calamba, Laguna',
                'status' => 'flagged', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(14), 'updated_at' => Carbon::now()->subDays(4),
                'notes' => 'Has repeatedly failed to submit complete DTI/BIR documents despite several follow-ups.',
                'documents' => [
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'DTI Business Permit', 'status' => 'missing', 'url' => null],
                    ['label' => 'BIR Form 2303', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Flagged for incomplete documents', 'time' => Carbon::now()->subDays(4)->diffForHumans()],
                    ['label' => 'Reminder sent for missing documents', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(14)->diffForHumans()],
                ],
                'reports' => [
                    [
                        'type' => 'Incomplete Documents',
                        'description' => 'DTI Permit and BIR Form 2303 still have not been submitted after 2 follow-up notices.',
                        'date' => Carbon::now()->subDays(4),
                    ],
                ],
            ],
            (object) [
                'id' => 4, 'initials' => 'CH', 'business_name' => 'Cavite Home Essentials',
                'owner_name' => 'Carla Herrera', 'business_type' => 'Partnership',
                'category' => 'Home & Living',
                'email' => 'cavitehomeessentials@example.com', 'phone' => '+63 920 334 5566',
                'address' => '88 Aguinaldo Hwy, Imus, Cavite',
                'status' => 'pending', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subHours(10),
                'notes' => 'DTI and Barangay documents are complete; only BIR verification is pending.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'pending', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Submitted BIR Form 2303 (for verification)', 'time' => Carbon::now()->subHours(10)->diffForHumans()],
                    ['label' => 'Submitted Barangay Business Permit', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 5, 'initials' => 'BB', 'business_name' => 'Better Bites Bakery',
                'owner_name' => 'Bianca Buenaventura', 'business_type' => 'Sole Proprietorship',
                'category' => 'Food & Beverages',
                'email' => 'betterbitesbakery@example.com', 'phone' => '+63 921 445 6677',
                'address' => '5 Mercado St., Sta. Rosa, Laguna',
                'status' => 'verified', 'verified' => true,
                'submitted_at' => Carbon::now()->subDays(20), 'updated_at' => Carbon::now()->subDays(18),
                'notes' => 'All submitted documents are complete and valid. Already a verified seller.',
                'documents' => [
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Clearance', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Verified by admin', 'time' => Carbon::now()->subDays(18)->diffForHumans()],
                    ['label' => 'Submitted all required documents', 'time' => Carbon::now()->subDays(19)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(20)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 6, 'initials' => 'QF', 'business_name' => 'QuickFix Auto Parts',
                'owner_name' => 'Quennie Flores', 'business_type' => 'Corporation',
                'category' => 'Automotive Parts & Accessories',
                'email' => 'quickfixautoparts@example.com', 'phone' => '+63 922 556 7788',
                'address' => '101 EDSA Extension, Biñan, Laguna',
                'status' => 'flagged', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(30), 'updated_at' => Carbon::now()->subDays(2),
                'notes' => 'Barangay Business Permit has expired; awaiting resubmission.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Flagged — expired Barangay permit', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Barangay permit expired', 'time' => Carbon::now()->subDays(5)->diffForHumans()],
                    ['label' => 'Verified by admin', 'time' => Carbon::now()->subDays(30)->diffForHumans()],
                ],
                'reports' => [
                    [
                        'type' => 'Expired Permit',
                        'description' => 'Barangay Business Permit expired last month and has not yet been renewed.',
                        'date' => Carbon::now()->subDays(2),
                    ],
                ],
            ],
            (object) [
                'id' => 7, 'initials' => 'LG', 'business_name' => 'Lush Garden Supplies',
                'owner_name' => 'Liwayway Garcia', 'business_type' => 'Sole Proprietorship',
                'category' => 'Garden & Outdoor',
                'email' => 'lushgardensupplies@example.com', 'phone' => '+63 923 667 8899',
                'address' => '17 Maharlika Rd., San Pablo, Laguna',
                'status' => 'pending', 'verified' => false,
                'submitted_at' => Carbon::now()->subDays(4), 'updated_at' => Carbon::now()->subHours(15),
                'notes' => 'DTI and Barangay permits are complete; currently under BIR verification.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'pending', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Submitted BIR Form 2303 (for verification)', 'time' => Carbon::now()->subHours(15)->diffForHumans()],
                    ['label' => 'Submitted DTI & Barangay permits', 'time' => Carbon::now()->subDays(3)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(4)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 8, 'initials' => 'MP', 'business_name' => 'Metro Print Solutions',
                'owner_name' => 'Marco Pascual', 'business_type' => 'Corporation',
                'category' => 'Printing & Office Supplies',
                'email' => 'metroprintsolutions@example.com', 'phone' => '+63 924 778 9900',
                'address' => 'Bldg. 3, Sto. Tomas Business Park, Sto. Tomas, Batangas',
                'status' => 'verified', 'verified' => true,
                'submitted_at' => Carbon::now()->subDays(45), 'updated_at' => Carbon::now()->subDays(40),
                'notes' => 'All business documents are complete and valid. Already a verified seller.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Verified by admin', 'time' => Carbon::now()->subDays(40)->diffForHumans()],
                    ['label' => 'Submitted all required documents', 'time' => Carbon::now()->subDays(43)->diffForHumans()],
                    ['label' => 'Applied as seller', 'time' => Carbon::now()->subDays(45)->diffForHumans()],
                ],
                'reports' => [],
            ],
        ]);

        // Simple filter simulation, so you can see the tabs working too
        $filtered = match ($filter) {
            'verified' => $sampleSellers->where('status', 'verified'),
            'flagged'  => $sampleSellers->where('status', 'flagged'),
            default    => $sampleSellers->where('status', 'pending'),
        };

        // Search simulation — filters by business name, owner name, or email
        if ($search = request('search')) {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($s) use ($needle) {
                return str_contains(strtolower($s->business_name), $needle)
                    || str_contains(strtolower($s->owner_name), $needle)
                    || str_contains(strtolower($s->email), $needle);
            });
        }

        // Sort simulation — same idea, stands in for an ->orderBy() once
        // this is wired to the DB. sortBy/sortByDesc don't reindex keys,
        // hence the ->values() right after.
        $filtered = match ($sort) {
            'oldest' => $filtered->sortBy('submitted_at'),
            'az'     => $filtered->sortBy(fn ($s) => strtolower($s->business_name)),
            'za'     => $filtered->sortByDesc(fn ($s) => strtolower($s->business_name)),
            default  => $filtered->sortByDesc('submitted_at'), // newest
        };

        $filtered = $filtered->values();

        $sellers = new LengthAwarePaginator(
            $filtered,
            $filtered->count(),
            10,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Style map used by both the list rows AND the modal (JS side)
        $statusStyles = [
            'pending'  => ['avatar' => 'text-sky bg-sky/15', 'badge' => 'text-yellow-700 bg-yellow/20', 'dot' => 'bg-yellow-600', 'label' => 'Documents under review'],
            'verified' => ['avatar' => 'text-mint-dark bg-mint/15', 'badge' => 'text-mint-dark bg-mint/15', 'dot' => 'bg-mint-dark', 'label' => 'Verified'],
            'flagged'  => ['avatar' => 'text-coral bg-coral/15', 'badge' => 'text-coral bg-coral/10', 'dot' => 'bg-coral', 'label' => 'Non-compliant'],
        ];

        // Flattened + string-formatted version so it can be @json()'d and used
        // by the modal JS (Carbon instances don't serialize cleanly on their
        // own, so we reformat them here before passing them to @json())
        $sellersForJs = $sampleSellers->map(function ($s) {
            $submittedCount = collect($s->documents)->where('status', 'submitted')->count();
            $totalCount = count($s->documents);

            return [
                'id'            => $s->id,
                'initials'      => $s->initials,
                'business_name' => $s->business_name,
                'owner_name'    => $s->owner_name,
                'business_type' => $s->business_type,
                'category'      => $s->category,
                'email'         => $s->email,
                'phone'         => $s->phone,
                'address'       => $s->address,
                'status'        => $s->status,
                'verified'      => $s->verified,
                'submitted_at'  => $s->submitted_at->format('M d, Y') . ' · ' . $s->submitted_at->diffForHumans(),
                'updated_at'    => $s->updated_at->diffForHumans(),
                'seller_no'     => '#' . str_pad($s->id, 6, '0', STR_PAD_LEFT),
                'docs_summary'  => ['value' => $submittedCount . '/' . $totalCount, 'sub' => $submittedCount === $totalCount ? 'All documents complete' : ($totalCount - $submittedCount) . ' document(s) incomplete'],
                'notes'         => $s->notes,
                'activity'      => $s->activity,
                'documents'     => $s->documents,
                'reports'       => collect($s->reports ?? [])->map(fn ($r) => [
                    'type'        => $r['type'],
                    'description' => $r['description'],
                    'date'        => $r['date']->format('M d, Y') . ' · ' . $r['date']->diffForHumans(),
                ])->values(),
            ];
        })->values();
    @endphp


    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Seller Compliance</h1>
        <p class="text-sm text-slate-500 mt-1">Verify seller documents and product compliance.</p>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <x-lucide-badge-check class="w-5 h-5 text-mint-dark" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['verified'] }}</p>
                    <p class="text-xs text-slate-500">Verified Sellers</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                    <x-lucide-clock class="w-5 h-5 text-yellow-600" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['pending'] }}</p>
                    <p class="text-xs text-slate-500">Pending Review</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <x-lucide-alert-triangle class="w-5 h-5 text-coral" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['flagged'] }}</p>
                    <p class="text-xs text-slate-500">Flagged / Non-compliant</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ FILTER TABS + SORT + SEARCH ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'pending', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'pending' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Pending Review <span class="ml-1 opacity-70">({{ $counts['pending'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'verified', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'verified' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Verified <span class="ml-1 opacity-70">({{ $counts['verified'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'flagged', 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'flagged' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Flagged <span class="ml-1 opacity-70">({{ $counts['flagged'] }})</span>
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
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Business name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Business name (Z–A)</option>
                </select>
            </form>

            {{-- SEARCH --}}
            <form method="GET" class="relative w-full xl:w-72">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by business, owner, or email..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
            </form>

        </div>

    </div>

    {{-- ============ COMPLIANCE LIST ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">
                {{ $filter === 'verified' ? 'Verified Sellers' : ($filter === 'flagged' ? 'Flagged Sellers' : 'Sellers Awaiting Review') }}
            </h2>
            <span class="text-xs text-slate-400">{{ $sellers->total() }} total</span>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($sellers as $seller)

                @php
                    $style = $statusStyles[$seller->status] ?? $statusStyles['pending'];
                    $docSummary = collect($seller->documents)->map(fn ($d) => $d['label'])->join(', ');
                @endphp

                <div class="flex items-center justify-between gap-4 px-5 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $style['avatar'] }}">
                            <span class="text-sm font-bold">{{ $seller->initials }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-navy truncate">{{ $seller->business_name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $docSummary }}</p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $style['badge'] }}">
                                    {{ $style['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" class="seller-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50" data-seller-id="{{ $seller->id }}">
                            View Documents
                        </button>
                        @if ($seller->status !== 'verified')
                            <button type="button" class="seller-verify-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90" data-seller-id="{{ $seller->id }}">
                                Verify
                            </button>
                        @endif
                        @if ($seller->status !== 'flagged')
                            <button type="button" class="seller-flag-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5" data-seller-id="{{ $seller->id }}">
                                Flag
                            </button>
                        @endif
                    </div>
                </div>

            @empty

                <div class="px-5 py-14 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <x-lucide-users-round class="w-5 h-5" />
                    </div>
                    <p class="text-sm font-semibold text-navy mt-3">No sellers found</p>
                    <p class="text-xs text-slate-400 mt-1">Try a different filter tab.</p>
                </div>

            @endforelse

        </div>

        {{-- ============ PAGINATION ============ --}}
        @if ($sellers->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $sellers->withQueryString()->links() }}
            </div>
        @endif

    </div>


    {{-- =========================================================
        SELLER DETAILS MODAL
        Same chrome/pattern as the User Accounts details modal —
        accent bar, floating close circle, 2-column body, dashed
        document preview cards.
    ========================================================= --}}
    <div id="sellerModalOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="sellerModalPanel" class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button type="button" id="sellerModalClose" aria-label="Close"
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
                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">SELLER COMPLIANCE</p>

                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="modalName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                            <span id="modalVerifiedBadge" class="hidden inline-flex items-center gap-1 text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full shrink-0">
                                <x-lucide-badge-check class="w-3 h-3" />
                                Verified
                            </span>
                        </div>

                        <p id="modalEmail" class="text-xs text-slate-500 truncate mt-0.5"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full">
                        <span id="modalStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                        <span id="modalStatusLabel"></span>
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
                                <dd id="modalPhone" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Address</dt>
                                <dd id="modalAddress" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Business Details</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Owner</dt>
                                <dd id="modalOwner" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Business Type</dt>
                                <dd id="modalBusinessType" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Category</dt>
                                <dd id="modalCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Compliance Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Seller ID</dt>
                                <dd id="modalSellerNo" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Submitted</dt>
                                <dd id="modalSubmittedAt" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Last Updated</dt>
                                <dd id="modalUpdatedAt" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Admin Notes</p>
                        <p id="modalNotes" class="text-xs text-slate-600 leading-relaxed"></p>
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

                    {{-- Submitted documents — placeholder previews only, no
                         actual file/image yet (hardcoded text details for now
                         until this is connected to the DB / file storage). --}}
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

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                <button type="button" id="modalActionBtn" class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold transition-all duration-300"></button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        VERIFY / FLAG — CONFIRMATION MODAL
        Just one modal, reused no matter where the click comes from
        (row action or the details modal) — it just depends on the
        action/id/name passed into openConfirmModal().
    ========================================================= --}}
    <div id="confirmModalOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="confirmModalPanel" class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150 p-6">

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            {{-- Flag-only fields --}}
            <div id="confirmFlagFields" class="hidden mb-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Reason for flagging</label>
                    <select id="confirmFlagReason" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40">
                        <option value="incomplete">Incomplete / missing documents</option>
                        <option value="expired">Expired permit or license</option>
                        <option value="suspicious">Suspicious or fraudulent information</option>
                        <option value="product">Product compliance issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Notes</label>
                    <textarea id="confirmFlagNotes" rows="3" placeholder="Enter the details of the issue..."
                        class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40"></textarea>
                </div>
            </div>

            {{-- Verify-only field --}}
            <div id="confirmVerifyFields" class="hidden mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Verification notes (optional)</label>
                <textarea id="confirmVerifyNotes" rows="3" placeholder="e.g. All submitted documents are complete and valid."
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
        fetch (fetch() → admin.sellers.show as JSON) instead of a
        hardcoded array — the open/close/populate logic stays the same.
        Same goes for confirmProceedBtn — it should POST to the
        verify/flag endpoints instead of just console.log.
    ========================================================= --}}
    <script>
        const sellersData = @json($sellersForJs);

        const statusBadge = {
            pending:  { dot: 'bg-yellow-600', text: 'text-yellow-700', bg: 'bg-yellow/20', label: 'Documents under review' },
            verified: { dot: 'bg-mint-dark',  text: 'text-mint-dark',  bg: 'bg-mint/15',   label: 'Verified' },
            flagged:  { dot: 'bg-coral',      text: 'text-coral',      bg: 'bg-coral/10',  label: 'Non-compliant' },
        };

        const docStatusBadge = {
            submitted: { text: 'text-mint-dark', bg: 'bg-mint/15', label: 'Submitted' },
            pending:   { text: 'text-yellow-700', bg: 'bg-yellow/20', label: 'Pending' },
            missing:   { text: 'text-coral', bg: 'bg-coral/10', label: 'Missing' },
        };

        // Just a generic file icon — used on the placeholder document cards
        // (no actual file/image yet, preview-only text details for now)
        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        /* -----------------------------------------------------------
           SELLER DETAILS MODAL
        ----------------------------------------------------------- */
        const overlay  = document.getElementById('sellerModalOverlay');
        const panel    = document.getElementById('sellerModalPanel');
        const closeBtn = document.getElementById('sellerModalClose');
        const modalActionBtn = document.getElementById('modalActionBtn');

        let currentModalSeller = null;

        function openSellerModal(id) {
            const seller = sellersData.find(s => s.id === id);
            if (!seller) return;

            currentModalSeller = seller;

            document.getElementById('modalInitials').textContent = seller.initials;
            document.getElementById('modalName').textContent = seller.business_name;
            document.getElementById('modalEmail').textContent = seller.email;
            document.getElementById('modalVerifiedBadge').classList.toggle('hidden', !seller.verified);

            const sBadge = statusBadge[seller.status] || statusBadge.pending;
            document.getElementById('modalStatusBadge').className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            document.getElementById('modalStatusDot').className = 'w-1.5 h-1.5 rounded-full ' + sBadge.dot;
            document.getElementById('modalStatusLabel').textContent = sBadge.label;

            document.getElementById('modalPhone').textContent = seller.phone;
            document.getElementById('modalAddress').textContent = seller.address;
            document.getElementById('modalOwner').textContent = seller.owner_name;
            document.getElementById('modalBusinessType').textContent = seller.business_type;
            document.getElementById('modalCategory').textContent = seller.category;
            document.getElementById('modalSellerNo').textContent = seller.seller_no;
            document.getElementById('modalSubmittedAt').textContent = seller.submitted_at;
            document.getElementById('modalUpdatedAt').textContent = seller.updated_at;
            document.getElementById('modalNotes').textContent = seller.notes;

            document.getElementById('modalDocsValue').textContent = seller.docs_summary.value;
            document.getElementById('modalDocsSub').textContent = seller.docs_summary.sub;

            // Recent activity
            const activityList = document.getElementById('modalActivity');
            activityList.innerHTML = '';
            (seller.activity || []).forEach(item => {
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

            // Reports & Flags
            const reportsWrap = document.getElementById('modalReports');
            reportsWrap.innerHTML = '';
            if (!seller.reports || seller.reports.length === 0) {
                reportsWrap.innerHTML = '<p class="text-xs text-slate-400">No reports or flags on file.</p>';
            } else {
                seller.reports.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'flex items-start gap-2.5';
                    item.innerHTML = `
                        <span class="mt-0.5 shrink-0 text-[10px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">${r.type}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700 leading-snug">${r.description}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">${r.date}</p>
                        </div>
                    `;
                    reportsWrap.appendChild(item);
                });
            }

            // Submitted documents — dashed-border preview cards, each with a
            // status badge underneath. Missing docs have no link and are muted.
            const docsGrid = document.getElementById('modalDocuments');
            docsGrid.innerHTML = '';
            (seller.documents || []).forEach(doc => {
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

            // Dynamic footer action — Verify if not yet verified, Flag if
            // already verified (so there's still a way to report an issue later)
            if (seller.status !== 'verified') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Verify Seller';
                modalActionBtn.style.display = '';
            } else {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg> Flag Seller';
                modalActionBtn.style.display = '';
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function closeSellerModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.seller-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openSellerModal(parseInt(btn.dataset.sellerId, 10)));
        });

        closeBtn.addEventListener('click', closeSellerModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeSellerModal(); });

        modalActionBtn.addEventListener('click', () => {
            if (!currentModalSeller) return;
            const action = currentModalSeller.status === 'verified' ? 'flag' : 'verify';
            openConfirmModal(action, currentModalSeller.id, currentModalSeller.business_name);
        });

        /* -----------------------------------------------------------
           VERIFY / FLAG — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay    = document.getElementById('confirmModalOverlay');
        const confirmPanel      = document.getElementById('confirmModalPanel');
        const confirmIconWrap   = document.getElementById('confirmIconWrap');
        const confirmTitle      = document.getElementById('confirmTitle');
        const confirmMessage    = document.getElementById('confirmMessage');
        const confirmProceedBtn = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn  = document.getElementById('confirmCancelBtn');
        const confirmFlagFields   = document.getElementById('confirmFlagFields');
        const confirmVerifyFields = document.getElementById('confirmVerifyFields');

        let activeConfirmAction = null;
        let activeConfirmSellerId = null;

        const checkIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        const flagIconSvg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>';

        function openConfirmModal(action, id, name) {
            activeConfirmAction = action;
            activeConfirmSellerId = id;

            confirmFlagFields.classList.toggle('hidden', action !== 'flag');
            confirmVerifyFields.classList.toggle('hidden', action !== 'verify');

            if (action === 'verify') {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
                confirmIconWrap.innerHTML = checkIconSvg;
                confirmTitle.textContent = 'Verify this seller?';
                confirmMessage.textContent = `Are you sure you want to verify ${name}? This will unlock full store access for this seller.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Verify';
                document.getElementById('confirmVerifyNotes').value = '';
            } else {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral';
                confirmIconWrap.innerHTML = flagIconSvg;
                confirmTitle.textContent = 'Flag this seller?';
                confirmMessage.textContent = `Flag ${name} as non-compliant? Their listings will be suspended until the issue is resolved.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Flag';
                document.getElementById('confirmFlagReason').value = 'incomplete';
                document.getElementById('confirmFlagNotes').value = '';
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

        document.querySelectorAll('.seller-verify-btn').forEach(btn => {
            btn.addEventListener('click', () => openConfirmModal('verify', parseInt(btn.dataset.sellerId, 10), btn.closest('.divide-y > div')?.querySelector('p.font-semibold')?.textContent?.trim() || 'this seller'));
        });

        document.querySelectorAll('.seller-flag-btn').forEach(btn => {
            btn.addEventListener('click', () => openConfirmModal('flag', parseInt(btn.dataset.sellerId, 10), btn.closest('.divide-y > div')?.querySelector('p.font-semibold')?.textContent?.trim() || 'this seller'));
        });

        confirmCancelBtn.addEventListener('click', closeConfirmModal);
        confirmOverlay.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

        confirmProceedBtn.addEventListener('click', () => {
            // TODO: replace with an actual request to the backend, e.g.:
            // axios.post(`/admin/sellers/${activeConfirmSellerId}/${activeConfirmAction}`, { ...payload });
            if (activeConfirmAction === 'verify') {
                console.log('Verifying seller', activeConfirmSellerId, 'notes:', document.getElementById('confirmVerifyNotes').value);
            } else {
                console.log('Flagging seller', activeConfirmSellerId,
                    'reason:', document.getElementById('confirmFlagReason').value,
                    'notes:', document.getElementById('confirmFlagNotes').value);
            }
            closeConfirmModal();
            closeSellerModal();
        });

        // Escape key — close whichever of the two modals is open
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeSellerModal();
        });
    </script>

@endsection