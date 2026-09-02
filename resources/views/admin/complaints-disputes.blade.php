@extends('admin.layout')

@section('title', 'Complaints & Disputes')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: alisin ito pag kinonekta na natin sa DB / controller.
        Dinagdagan ko yung dataset (Open + In Mediation + Resolved)
        para may laman din yung ibang tabs pag sinubukan mong
        i-switch — sa totoong DB version, ito na yung papalitan ng
        Dispute::with(['order','buyer','seller'])->query() na may
        ->where('status', $filter) atbp.

        NOTE sa mga links/forms sa ibaba: ginamit ko yung
        url()->current() + http_build_query() sa halip na route()
        kasi hindi ko alam yung exact route name ng page na 'to.
        Kung may named route ka na (hal. admin.disputes), pwede
        mong palitan ng route('admin.disputes', [...]) katulad
        nung ginawa sa User Accounts page — parehong idea lang,
        query string driven filtering.
    ========================================================= --}}
    @php
        use Carbon\Carbon;

        $filter = request('filter', 'open');
        $sort   = request('sort', 'newest');

        $disputes = collect([

            // ---------------- OPEN ----------------
            (object) [
                'id' => 1, 'status' => 'open',
                'order_no' => '#10432',
                'priority' => 'high',
                'category' => 'Wrong Item Received',
                'description' => 'Item received ay iba sa na-order — mali ang model. Umorder ako ng iPhone 13 case pero natanggap ko ay Samsung case. Gusto ko po ng replacement o refund.',
                'filed_at' => Carbon::now()->subMinutes(32),
                'order' => ['amount' => 1290, 'date' => Carbon::now()->subDays(4), 'payment_method' => 'GCash'],
                'buyer' => (object) ['name' => 'Maria Reyes', 'initials' => 'MR', 'email' => 'maria.reyes@example.com', 'phone' => '+63 917 552 3391', 'address' => 'Blk 5 Lot 22, Barangay Uno, Bacoor, Cavite'],
                'seller' => (object) ['name' => 'TechHub PH', 'initials' => 'TH', 'email' => 'support@techhubph.com', 'phone' => '+63 928 774 1123', 'category' => 'Electronics & Gadgets'],
                'evidence' => [
                    ['label' => 'Photo of item received', 'url' => '#'],
                    ['label' => 'Screenshot of order', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subMinutes(32)->diffForHumans()],
                    ['label' => 'Order marked as delivered', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Order placed', 'time' => Carbon::now()->subDays(4)->diffForHumans()],
                ],
            ],
            (object) [
                'id' => 2, 'status' => 'open',
                'order_no' => '#10398',
                'priority' => 'medium',
                'category' => 'Late Delivery / Refund',
                'description' => 'Late delivery, hindi na-refund agad. Sabi ng seller magre-refund pero 3 araw na walang balita. Paki-follow up po.',
                'filed_at' => Carbon::now()->subHours(2),
                'order' => ['amount' => 640, 'date' => Carbon::now()->subDays(6), 'payment_method' => 'Cash on Delivery'],
                'buyer' => (object) ['name' => 'Jonas Dela Cruz', 'initials' => 'JD', 'email' => 'jonas.delacruz@example.com', 'phone' => '+63 920 441 8827', 'address' => '12 Rizal St., Imus, Cavite'],
                'seller' => (object) ['name' => "Aling Nena's Store", 'initials' => 'AN', 'email' => 'alingnenastore@example.com', 'phone' => '+63 917 220 5567', 'category' => 'Home & Grocery'],
                'evidence' => [
                    ['label' => 'Chat screenshot with seller', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subHours(2)->diffForHumans()],
                    ['label' => 'Buyer requested refund', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Order delivered (5 days late)', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                ],
            ],
            (object) [
                'id' => 3, 'status' => 'open',
                'order_no' => '#10375',
                'priority' => 'high',
                'category' => 'Item Not Received',
                'description' => 'Payment nakuha pero walang natanggap na item. Naka-mark na "Delivered" ang order pero wala talagang dumating sa akin.',
                'filed_at' => Carbon::now()->subHours(5),
                'order' => ['amount' => 2450, 'date' => Carbon::now()->subDays(9), 'payment_method' => 'Credit/Debit Card'],
                'buyer' => (object) ['name' => 'Carla Mendoza', 'initials' => 'CM', 'email' => 'carla.mendoza@example.com', 'phone' => '+63 915 332 7741', 'address' => '88 Aguinaldo Hwy, Dasmariñas, Cavite'],
                'seller' => (object) ['name' => 'TechHub PH', 'initials' => 'TH', 'email' => 'support@techhubph.com', 'phone' => '+63 928 774 1123', 'category' => 'Electronics & Gadgets'],
                'evidence' => [
                    ['label' => 'Proof of payment', 'url' => '#'],
                    ['label' => 'Delivery tracking screenshot', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subHours(5)->diffForHumans()],
                    ['label' => 'Order marked as delivered by rider', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Payment confirmed', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                ],
            ],

            // ---------------- IN MEDIATION ----------------
            (object) [
                'id' => 4, 'status' => 'mediation',
                'order_no' => '#10412',
                'priority' => 'medium',
                'category' => 'Damaged Item',
                'description' => 'Sira na ang natanggap na item — may bitak ang glass container pagdating. Kasalukuyang kinukumpirma ng seller kung packaging issue o delivery mishandling.',
                'filed_at' => Carbon::now()->subDays(1),
                'assigned_admin' => 'Rico Santos',
                'mediation_started_at' => Carbon::now()->subHours(10),
                'order' => ['amount' => 890, 'date' => Carbon::now()->subDays(5), 'payment_method' => 'GCash'],
                'buyer' => (object) ['name' => 'Ella Cruz', 'initials' => 'EC', 'email' => 'ella.cruz@example.com', 'phone' => '+63 916 774 2210', 'address' => '9 Kalayaan Ave., Dasmariñas, Cavite'],
                'seller' => (object) ['name' => 'GreenLeaf Organics', 'initials' => 'GL', 'email' => 'hello@greenleaforganics.ph', 'phone' => '+63 917 655 9021', 'category' => 'Food & Beverages'],
                'evidence' => [
                    ['label' => 'Photo of damaged item', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Admin started mediation', 'time' => Carbon::now()->subHours(10)->diffForHumans()],
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Order delivered', 'time' => Carbon::now()->subDays(3)->diffForHumans()],
                ],
            ],
            (object) [
                'id' => 5, 'status' => 'mediation',
                'order_no' => '#10365',
                'priority' => 'high',
                'category' => 'Unauthorized Charge',
                'description' => 'May extra charge sa order ko na hindi ko inaprubahan — dagdag na delivery fee na hindi naka-disclose bago ma-confirm ang order.',
                'filed_at' => Carbon::now()->subDays(2),
                'assigned_admin' => 'Rico Santos',
                'mediation_started_at' => Carbon::now()->subDays(1),
                'order' => ['amount' => 1750, 'date' => Carbon::now()->subDays(10), 'payment_method' => 'Credit/Debit Card'],
                'buyer' => (object) ['name' => 'Paolo Villar', 'initials' => 'PV', 'email' => 'paolo.villar@example.com', 'phone' => '+63 919 220 7734', 'address' => '3 Molino Blvd., Bacoor, Cavite'],
                'seller' => (object) ['name' => 'QuickFix Appliance Repair', 'initials' => 'QF', 'email' => 'bookings@quickfixappliance.ph', 'phone' => '+63 927 331 8842', 'category' => 'Home Services'],
                'evidence' => [
                    ['label' => 'Screenshot of receipt', 'url' => '#'],
                    ['label' => 'Screenshot of order confirmation', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Admin started mediation', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Service completed', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                ],
            ],

            // ---------------- RESOLVED ----------------
            (object) [
                'id' => 6, 'status' => 'resolved',
                'order_no' => '#10290',
                'priority' => 'medium',
                'category' => 'Late Delivery',
                'description' => 'Umabot ng 6 araw ang delay sa delivery kaysa sa napagkasunduang date, walang paunang abiso mula sa seller.',
                'filed_at' => Carbon::now()->subDays(6),
                'resolution' => 'refund_buyer',
                'resolution_notes' => 'Na-refund ang buyer ng buong halaga dahil sa 6 araw na pagka-late at kakulangan ng paunang abiso.',
                'resolved_by' => 'Rico Santos',
                'resolved_at' => Carbon::now()->subDays(2),
                'order' => ['amount' => 1150, 'date' => Carbon::now()->subDays(14), 'payment_method' => 'GCash'],
                'buyer' => (object) ['name' => 'Sheena Ong', 'initials' => 'SO', 'email' => 'sheena.ong@example.com', 'phone' => '+63 918 442 3391', 'address' => '21 Tirona Hwy, Bacoor, Cavite'],
                'seller' => (object) ['name' => 'Bloom & Petals', 'initials' => 'BP', 'email' => 'orders@bloomandpetals.ph', 'phone' => '+63 926 118 4470', 'category' => 'Flowers & Gifts'],
                'evidence' => [
                    ['label' => 'Chat screenshot with seller', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Case resolved — buyer refunded', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Admin started mediation', 'time' => Carbon::now()->subDays(5)->diffForHumans()],
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subDays(6)->diffForHumans()],
                ],
            ],
            (object) [
                'id' => 7, 'status' => 'resolved',
                'order_no' => '#10254',
                'priority' => 'low',
                'category' => 'Size Mismatch',
                'description' => 'Mali ang natanggap na size ng damit — S ang inorder pero M ang natanggap.',
                'filed_at' => Carbon::now()->subDays(9),
                'resolution' => 'replace_item',
                'resolution_notes' => 'Pumayag ang seller na palitan ng tamang size, walang additional charge sa buyer.',
                'resolved_by' => 'Rico Santos',
                'resolved_at' => Carbon::now()->subDays(5),
                'order' => ['amount' => 520, 'date' => Carbon::now()->subDays(16), 'payment_method' => 'Cash on Delivery'],
                'buyer' => (object) ['name' => 'Miguel Torres', 'initials' => 'MT', 'email' => 'miguel.torres@example.com', 'phone' => '+63 921 774 5512', 'address' => '14 Aguinaldo Hwy, Imus, Cavite'],
                'seller' => (object) ['name' => 'Urban Thread Apparel', 'initials' => 'UT', 'email' => 'support@urbanthread.ph', 'phone' => '+63 917 992 3345', 'category' => 'Fashion & Apparel'],
                'evidence' => [
                    ['label' => 'Photo of size tag', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Case resolved — item replaced', 'time' => Carbon::now()->subDays(5)->diffForHumans()],
                    ['label' => 'Admin started mediation', 'time' => Carbon::now()->subDays(8)->diffForHumans()],
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                ],
            ],
            (object) [
                'id' => 8, 'status' => 'resolved',
                'order_no' => '#10199',
                'priority' => 'high',
                'category' => 'Item Not as Described',
                'description' => 'Sabi ng buyer hindi tugma sa listing photos at specs ang natanggap na item.',
                'filed_at' => Carbon::now()->subDays(15),
                'resolution' => 'favor_seller',
                'resolution_notes' => 'Napatunayang tugma ang natanggap na item sa listing photos at specs batay sa ibinigay na ebidensya; na-deny ang claim.',
                'resolved_by' => 'Rico Santos',
                'resolved_at' => Carbon::now()->subDays(11),
                'order' => ['amount' => 3200, 'date' => Carbon::now()->subDays(20), 'payment_method' => 'Credit/Debit Card'],
                'buyer' => (object) ['name' => 'Karen Bautista', 'initials' => 'KB', 'email' => 'karen.bautista@example.com', 'phone' => '+63 915 220 9987', 'address' => '5 Molino Blvd., Bacoor, Cavite'],
                'seller' => (object) ['name' => 'TechHub PH', 'initials' => 'TH', 'email' => 'support@techhubph.com', 'phone' => '+63 928 774 1123', 'category' => 'Electronics & Gadgets'],
                'evidence' => [
                    ['label' => 'Listing screenshot', 'url' => '#'],
                    ['label' => "Buyer's photo of item", 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Case resolved — claim denied', 'time' => Carbon::now()->subDays(11)->diffForHumans()],
                    ['label' => 'Admin started mediation', 'time' => Carbon::now()->subDays(14)->diffForHumans()],
                    ['label' => 'Buyer filed a complaint', 'time' => Carbon::now()->subDays(15)->diffForHumans()],
                ],
            ],
        ]);

        $counts = [
            'open'      => $disputes->where('status', 'open')->count(),
            'mediation' => $disputes->where('status', 'mediation')->count(),
            'resolved'  => $disputes->where('status', 'resolved')->count(),
        ];

        // Style maps — iisang source of truth ng badge colors, ginagamit
        // both ng list row AT ng modal (JS side, see $disputesForJs)
        $priorityStyles = [
            'high'   => ['icon_bg' => 'bg-coral/15', 'icon_text' => 'text-coral', 'badge' => 'text-coral bg-coral/10', 'label' => 'High priority'],
            'medium' => ['icon_bg' => 'bg-yellow/20', 'icon_text' => 'text-yellow-600', 'badge' => 'text-yellow-700 bg-yellow/20', 'label' => 'Medium priority'],
            'low'    => ['icon_bg' => 'bg-slate-100', 'icon_text' => 'text-slate-500', 'badge' => 'text-slate-500 bg-slate-100', 'label' => 'Low priority'],
        ];

        $statusStyles = [
            'open'      => ['badge' => 'text-coral bg-coral/10', 'label' => 'Open'],
            'mediation' => ['badge' => 'text-yellow-700 bg-yellow/20', 'label' => 'In Mediation'],
            'resolved'  => ['badge' => 'text-mint-dark bg-mint/10', 'label' => 'Resolved'],
        ];

        // ---- FILTER simulation (tab) — same idea as $filtered sa User Accounts ----
        $filtered = match ($filter) {
            'mediation' => $disputes->where('status', 'mediation'),
            'resolved'  => $disputes->where('status', 'resolved'),
            default     => $disputes->where('status', 'open'), // 'open'
        };

        // ---- SEARCH simulation — order no., buyer, seller, o category ----
        if ($search = request('search')) {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($d) use ($needle) {
                return str_contains(strtolower($d->order_no), $needle)
                    || str_contains(strtolower($d->buyer->name), $needle)
                    || str_contains(strtolower($d->seller->name), $needle)
                    || str_contains(strtolower($d->category), $needle);
            });
        }

        // ---- SORT simulation ----
        $priorityRank = ['high' => 3, 'medium' => 2, 'low' => 1];
        $filtered = match ($sort) {
            'oldest'         => $filtered->sortBy(fn ($d) => $d->filed_at),
            'priority_high'  => $filtered->sortByDesc(fn ($d) => $priorityRank[$d->priority] ?? 0),
            'priority_low'   => $filtered->sortBy(fn ($d) => $priorityRank[$d->priority] ?? 0),
            default          => $filtered->sortByDesc(fn ($d) => $d->filed_at), // newest
        };

        $filtered = $filtered->values();

        // Flattened version ng BUONG dataset (hindi lang $filtered) para
        // kahit anong dispute i-click sa modal, may data — parehong
        // approach ng $usersForJs sa User Accounts page.
        $disputesForJs = $disputes->map(function ($d) {
            return [
                'id' => $d->id,
                'status' => $d->status,
                'order_no' => $d->order_no,
                'priority' => $d->priority,
                'category' => $d->category,
                'description' => $d->description,
                'filed' => $d->filed_at->diffForHumans(),
                'order' => [
                    'amount' => $d->order['amount'],
                    'amount_formatted' => '₱' . number_format($d->order['amount'], 2),
                    'date' => $d->order['date']->format('M d, Y'),
                    'payment_method' => $d->order['payment_method'],
                ],
                'buyer' => $d->buyer,
                'seller' => $d->seller,
                'evidence' => $d->evidence,
                'activity' => $d->activity,
                'assigned_admin' => $d->assigned_admin ?? null,
                'resolution' => $d->resolution ?? null,
                'resolution_notes' => $d->resolution_notes ?? null,
                'resolved_by' => $d->resolved_by ?? null,
                'resolved_at' => isset($d->resolved_at) ? $d->resolved_at->format('M d, Y') : null,
            ];
        })->values();
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Complaints &amp; Disputes</h1>
        <p class="text-sm text-slate-500 mt-1">Suriin at lutasin ang mga isyu sa pagitan ng buyer at seller.</p>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['open'] }}</p>
                    <p class="text-xs text-slate-500">Open / Urgent</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['mediation'] }}</p>
                    <p class="text-xs text-slate-500">In Mediation</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['resolved'] }}</p>
                    <p class="text-xs text-slate-500">Resolved This Month</p>
                </div>
            </div>
        </div>

    </div>

    {{-- =========================================================
        FILTER TABS + SORT + SEARCH
        Query-string driven, kagaya ng User Accounts page — switching
        tabs resets ang search (parehong behavior nung sample), pero
        sort naman ay carried over.
    ========================================================= --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        {{-- FILTER TABS --}}
        <div class="flex items-center gap-2 flex-wrap">

            <a
                href="{{ url()->current() }}?{{ http_build_query(['filter' => 'open', 'sort' => $sort]) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'open' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}"
            >
                Open <span class="ml-1 opacity-70">({{ $counts['open'] }})</span>
            </a>

            <a
                href="{{ url()->current() }}?{{ http_build_query(['filter' => 'mediation', 'sort' => $sort]) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'mediation' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}"
            >
                In Mediation <span class="ml-1 opacity-70">({{ $counts['mediation'] }})</span>
            </a>

            <a
                href="{{ url()->current() }}?{{ http_build_query(['filter' => 'resolved', 'sort' => $sort]) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'resolved' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}"
            >
                Resolved <span class="ml-1 opacity-70">({{ $counts['resolved'] }})</span>
            </a>

        </div>

        {{-- SORT + SEARCH --}}
        <div class="flex items-center gap-2 w-full xl:w-auto">

            {{-- SORT --}}
            <form method="GET" action="{{ url()->current() }}" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                >
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="priority_high" {{ $sort === 'priority_high' ? 'selected' : '' }}>Priority (High to Low)</option>
                    <option value="priority_low" {{ $sort === 'priority_low' ? 'selected' : '' }}>Priority (Low to High)</option>
                </select>
            </form>

            {{-- SEARCH --}}
            <form method="GET" action="{{ url()->current() }}" class="relative w-full xl:w-72">

                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by order no, buyer, or seller..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                >

            </form>

        </div>

    </div>

    {{-- ============ DISPUTES LIST ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">
                {{ $statusStyles[$filter]['label'] ?? 'Open' }} Complaints
            </h2>
            <span class="text-xs text-slate-400">{{ $filtered->count() }} total</span>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($filtered as $dispute)

                @php
                    $pStyle = $priorityStyles[$dispute->priority] ?? $priorityStyles['low'];
                    $sStyle = $statusStyles[$dispute->status] ?? $statusStyles['open'];
                @endphp

                <div class="flex items-start justify-between gap-4 px-5 py-4">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl {{ $pStyle['icon_bg'] }} flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 {{ $pStyle['icon_text'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-navy">Order {{ $dispute->order_no }}</p>
                                <span class="text-[11px] font-semibold {{ $pStyle['badge'] }} px-2 py-0.5 rounded-full">{{ $pStyle['label'] }}</span>
                                <span class="text-[11px] font-semibold {{ $sStyle['badge'] }} px-2 py-0.5 rounded-full">{{ $sStyle['label'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Buyer: {{ $dispute->buyer->name }} vs Seller: {{ $dispute->seller->name }}</p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $dispute->category }}. Filed {{ $dispute->filed_at->diffForHumans() }}.
                                @if ($dispute->status === 'resolved')
                                    Resolved {{ $dispute->resolved_at->diffForHumans() }}.
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            class="dispute-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50"
                            data-dispute-id="{{ $dispute->id }}"
                        >
                            View Details
                        </button>
                        @if ($dispute->status !== 'resolved')
                            <button
                                type="button"
                                class="dispute-mediate-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90"
                                data-dispute-id="{{ $dispute->id }}"
                            >
                                {{ $dispute->status === 'mediation' ? 'Continue' : 'Mediate' }}
                            </button>
                        @endif
                    </div>
                </div>

            @empty

                <div class="px-5 py-14 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-navy mt-3">No complaints found</p>
                    <p class="text-xs text-slate-400 mt-1">Try changing the filter or search keyword.</p>
                </div>

            @endforelse

        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–{{ $filtered->count() }} of {{ $filtered->count() }}</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>›</button>
            </div>
        </div>

    </div>


    {{-- =========================================================
        DISPUTE DETAILS MODAL
    ========================================================= --}}
    <div
        id="disputeModalOverlay"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="disputeModalPanel"
            class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150"
        >

            <div id="disputeModalAccent" class="h-1.5 bg-coral rounded-t-2xl"></div>

            <button
                type="button"
                id="disputeModalClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">

                <div class="flex items-center gap-3 mb-4">

                    <div id="disputeModalIconWrap" class="w-12 h-12 rounded-2xl bg-coral/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">DISPUTE DETAILS</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="disputeModalOrderNo" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                            <span id="disputeModalPriorityBadge" class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0"></span>
                            <span id="disputeModalStatusBadge" class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <span id="disputeModalCategory"></span> · Filed <span id="disputeModalFiled"></span>
                        </p>
                    </div>

                </div>

            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- LEFT COLUMN --}}
                <div class="space-y-4">

                    {{-- Buyer info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-3">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Buyer</p>
                        </div>
                        <p id="disputeModalBuyerName" class="text-sm font-semibold text-navy"></p>
                        <dl class="space-y-2 mt-2">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Email</dt>
                                <dd id="disputeModalBuyerEmail" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Phone</dt>
                                <dd id="disputeModalBuyerPhone" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Address</dt>
                                <dd id="disputeModalBuyerAddress" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Seller info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-3">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Seller</p>
                        </div>
                        <p id="disputeModalSellerName" class="text-sm font-semibold text-navy"></p>
                        <dl class="space-y-2 mt-2">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Email</dt>
                                <dd id="disputeModalSellerEmail" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Phone</dt>
                                <dd id="disputeModalSellerPhone" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Category</dt>
                                <dd id="disputeModalSellerCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Complaint description --}}
                    <div class="bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-2">
                            <svg class="w-3.5 h-3.5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-coral">Complaint</p>
                        </div>
                        <p id="disputeModalDescription" class="text-xs text-slate-700 leading-relaxed"></p>
                    </div>

                    {{-- Resolution — lumalabas lang kapag "resolved" na yung
                         status (see toggle sa JS) --}}
                    <div id="disputeModalResolutionBox" class="hidden bg-mint/5 border border-mint/20 rounded-xl p-4">
                        <div class="flex items-center gap-1.5 mb-2">
                            <svg class="w-3.5 h-3.5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-mint-dark">Resolution</p>
                        </div>
                        <p id="disputeModalResolutionType" class="text-xs font-semibold text-navy mb-1"></p>
                        <p id="disputeModalResolutionNotes" class="text-xs text-slate-700 leading-relaxed"></p>
                        <p id="disputeModalResolvedBy" class="text-[10px] text-slate-400 mt-2"></p>
                    </div>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-4">

                    {{-- Order info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Order Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Amount</dt>
                                <dd id="disputeModalOrderAmount" class="text-xs font-semibold text-navy text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Order Date</dt>
                                <dd id="disputeModalOrderDate" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Payment Method</dt>
                                <dd id="disputeModalPaymentMethod" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="disputeModalAssignedRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Assigned To</dt>
                                <dd id="disputeModalAssigned" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Evidence --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Evidence Submitted</p>
                        <div id="disputeModalEvidence" class="grid grid-cols-3 gap-3"></div>
                    </div>

                    {{-- Activity timeline --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Timeline</p>
                        <ul id="disputeModalActivity" class="space-y-3"></ul>
                    </div>

                </div>

            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <p id="disputeModalResolvedFooterNote" class="hidden text-xs text-slate-400 mr-auto"></p>
                <button
                    type="button"
                    id="disputeModalMediateBtn"
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path>
                    </svg>
                    <span id="disputeModalMediateBtnLabel">Start Mediation</span>
                </button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        MEDIATION MODAL
        Frontend/UI lang muna ito — walang totoong POST pa.
        TODO: kapag naka-DB na, i-POST dito yung resolution + notes
        papunta sa route na katulad ng admin.disputes.mediate.
    ========================================================= --}}
    <div
        id="mediateModalOverlay"
        class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="mediateModalPanel"
            class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150"
        >

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button
                type="button"
                id="mediateModalClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
                <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">MEDIATION</p>
                <h2 id="mediateModalOrderNo" class="text-xl font-bold text-navy"></h2>
                <p id="mediateModalParties" class="text-xs text-slate-500 mt-0.5"></p>
            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 space-y-5">

                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Complaint Summary</p>
                    <p id="mediateModalSummary" class="text-xs text-slate-600 leading-relaxed"></p>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2.5">Choose Resolution</p>
                    <div id="mediateOptions" class="grid grid-cols-2 gap-2.5">

                        <button type="button" data-value="refund_buyer" class="mediate-option-btn text-left p-3 rounded-xl border border-slate-200 hover:border-mint transition">
                            <svg class="w-4 h-4 text-mint-dark mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 17.01"></polyline>
                            </svg>
                            <p class="text-xs font-semibold text-navy">Refund Buyer</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Full or partial refund</p>
                        </button>

                        <button type="button" data-value="replace_item" class="mediate-option-btn text-left p-3 rounded-xl border border-slate-200 hover:border-mint transition">
                            <svg class="w-4 h-4 text-sky mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <p class="text-xs font-semibold text-navy">Replace / Reship</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Seller sends new item</p>
                        </button>

                        <button type="button" data-value="favor_seller" class="mediate-option-btn text-left p-3 rounded-xl border border-slate-200 hover:border-mint transition">
                            <svg class="w-4 h-4 text-slate-500 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                            </svg>
                            <p class="text-xs font-semibold text-navy">Favor Seller</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Deny the claim</p>
                        </button>

                        <button type="button" data-value="escalate" class="mediate-option-btn text-left p-3 rounded-xl border border-slate-200 hover:border-mint transition">
                            <svg class="w-4 h-4 text-yellow-600 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                            <p class="text-xs font-semibold text-navy">Escalate</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Needs senior review</p>
                        </button>

                    </div>
                </div>

                <div id="mediateRefundRow" class="hidden">
                    <label for="mediateRefundAmount" class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5 block">Refund Amount (₱)</label>
                    <input
                        type="number"
                        id="mediateRefundAmount"
                        min="0"
                        step="0.01"
                        class="w-full px-3 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition"
                    >
                </div>

                <div>
                    <label for="mediateNotes" class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5 block">Mediation Notes</label>
                    <textarea
                        id="mediateNotes"
                        rows="3"
                        placeholder="Ilagay dito ang basehan ng desisyon — para sa audit trail at para malaman ng buyer/seller kung bakit ganito ang naging resolution."
                        class="w-full px-3 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition resize-none"
                    ></textarea>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button
                    type="button"
                    id="mediateCancelBtn"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    id="mediateSubmitBtn"
                    disabled
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark opacity-40 cursor-not-allowed transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    Submit Resolution
                </button>
            </div>

        </div>

    </div>


    {{-- Toast --}}
    <div
        id="toastNotification"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[70] hidden items-center gap-2 px-4 py-3 rounded-xl bg-navy text-white text-xs font-semibold shadow-lg translate-y-2 opacity-0 transition duration-150"
    >
        <svg class="w-4 h-4 text-mint shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toastMessage"></span>
    </div>


    {{-- =========================================================
        MODAL DATA + BEHAVIOR
        TODO: pag naka-DB na, puwede nang gawing route-based fetch
        (fetch() → admin.disputes.show as JSON) sa halip na hardcoded
        array — yung open/close/populate logic mananatili pareho.
    ========================================================= --}}
    <script>
        const disputesData = @json($disputesForJs);

        const priorityBadge = {
            high:   { badge: 'text-coral bg-coral/10', icon_bg: 'bg-coral/15', icon_text: 'text-coral', accent: 'bg-coral', label: 'High priority' },
            medium: { badge: 'text-yellow-700 bg-yellow/20', icon_bg: 'bg-yellow/20', icon_text: 'text-yellow-600', accent: 'bg-yellow-500', label: 'Medium priority' },
            low:    { badge: 'text-slate-500 bg-slate-100', icon_bg: 'bg-slate-100', icon_text: 'text-slate-500', accent: 'bg-slate-400', label: 'Low priority' },
        };

        const statusBadge = {
            open:      { badge: 'text-coral bg-coral/10', label: 'Open' },
            mediation: { badge: 'text-yellow-700 bg-yellow/20', label: 'In Mediation' },
            resolved:  { badge: 'text-mint-dark bg-mint/10', label: 'Resolved' },
        };

        const resolutionLabels = {
            refund_buyer: 'Refunded Buyer',
            replace_item: 'Replaced / Reshipped Item',
            favor_seller: 'Favored Seller (Claim Denied)',
            escalate: 'Escalated for Senior Review',
        };

        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        let currentDisputeId = null;
        let selectedResolution = null;

        /* -----------------------------------------------------------
           DISPUTE DETAILS MODAL
        ----------------------------------------------------------- */
        const disputeOverlay  = document.getElementById('disputeModalOverlay');
        const disputePanel    = document.getElementById('disputeModalPanel');
        const disputeCloseBtn = document.getElementById('disputeModalClose');
        const disputeMediateBtn = document.getElementById('disputeModalMediateBtn');
        const disputeMediateBtnLabel = document.getElementById('disputeModalMediateBtnLabel');
        const disputeResolvedFooterNote = document.getElementById('disputeModalResolvedFooterNote');

        function openDisputeModal(id) {
            const d = disputesData.find(x => x.id === id);
            if (!d) return;

            currentDisputeId = id;
            const p = priorityBadge[d.priority] || priorityBadge.low;
            const s = statusBadge[d.status] || statusBadge.open;

            document.getElementById('disputeModalAccent').className = 'h-1.5 rounded-t-2xl ' + p.accent;

            const iconWrap = document.getElementById('disputeModalIconWrap');
            iconWrap.className = 'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ' + p.icon_bg;
            iconWrap.querySelector('svg').setAttribute('class', 'w-5 h-5 ' + p.icon_text);

            document.getElementById('disputeModalOrderNo').textContent = 'Order ' + d.order_no;

            const pBadge = document.getElementById('disputeModalPriorityBadge');
            pBadge.className = 'inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0 ' + p.badge;
            pBadge.textContent = p.label;

            const sBadge = document.getElementById('disputeModalStatusBadge');
            sBadge.className = 'inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0 ' + s.badge;
            sBadge.textContent = s.label;

            document.getElementById('disputeModalCategory').textContent = d.category;
            document.getElementById('disputeModalFiled').textContent = d.filed;

            document.getElementById('disputeModalBuyerName').textContent = d.buyer.name;
            document.getElementById('disputeModalBuyerEmail').textContent = d.buyer.email;
            document.getElementById('disputeModalBuyerPhone').textContent = d.buyer.phone;
            document.getElementById('disputeModalBuyerAddress').textContent = d.buyer.address;

            document.getElementById('disputeModalSellerName').textContent = d.seller.name;
            document.getElementById('disputeModalSellerEmail').textContent = d.seller.email;
            document.getElementById('disputeModalSellerPhone').textContent = d.seller.phone;
            document.getElementById('disputeModalSellerCategory').textContent = d.seller.category;

            document.getElementById('disputeModalDescription').textContent = d.description;

            document.getElementById('disputeModalOrderAmount').textContent = d.order.amount_formatted;
            document.getElementById('disputeModalOrderDate').textContent = d.order.date;
            document.getElementById('disputeModalPaymentMethod').textContent = d.order.payment_method;

            const assignedRow = document.getElementById('disputeModalAssignedRow');
            if (d.assigned_admin) {
                assignedRow.className = 'flex justify-between gap-3';
                document.getElementById('disputeModalAssigned').textContent = d.assigned_admin;
            } else {
                assignedRow.className = 'hidden justify-between gap-3';
            }

            const resolutionBox = document.getElementById('disputeModalResolutionBox');
            if (d.status === 'resolved' && d.resolution) {
                resolutionBox.classList.remove('hidden');
                document.getElementById('disputeModalResolutionType').textContent = resolutionLabels[d.resolution] || d.resolution;
                document.getElementById('disputeModalResolutionNotes').textContent = d.resolution_notes || '';
                document.getElementById('disputeModalResolvedBy').textContent = 'Resolved by ' + (d.resolved_by || 'admin') + ' on ' + (d.resolved_at || '');
            } else {
                resolutionBox.classList.add('hidden');
            }

            const evidenceGrid = document.getElementById('disputeModalEvidence');
            evidenceGrid.innerHTML = '';
            (d.evidence || []).forEach(ev => {
                const card = document.createElement('a');
                card.href = ev.url || '#';
                card.target = '_blank';
                card.rel = 'noopener noreferrer';
                card.className = 'block group';
                card.innerHTML = `
                    <div class="w-full aspect-square rounded-lg border border-dashed border-slate-300 bg-slate-50/60 flex flex-col items-center justify-center gap-1 text-slate-400 cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark">
                        ${docFileIconSvg}
                        <span class="text-[9px] font-medium uppercase tracking-wide">Preview</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 text-center truncate transition group-hover:text-mint-dark">${ev.label}</p>
                `;
                evidenceGrid.appendChild(card);
            });
            if (!d.evidence || d.evidence.length === 0) {
                evidenceGrid.innerHTML = '<p class="text-xs text-slate-400 col-span-3">No evidence submitted.</p>';
            }

            const activityList = document.getElementById('disputeModalActivity');
            activityList.innerHTML = '';
            (d.activity || []).forEach(item => {
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

            // Footer — palitan depende sa status: open/mediation puwede pang
            // i-mediate, resolved wala nang action, note na lang.
            if (d.status === 'resolved') {
                disputeMediateBtn.classList.add('hidden');
                disputeResolvedFooterNote.classList.remove('hidden');
                disputeResolvedFooterNote.textContent = 'This case has already been resolved.';
            } else {
                disputeMediateBtn.classList.remove('hidden');
                disputeResolvedFooterNote.classList.add('hidden');
                disputeMediateBtnLabel.textContent = d.status === 'mediation' ? 'Continue Mediation' : 'Start Mediation';
            }

            disputeOverlay.classList.remove('hidden');
            disputeOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                disputePanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeDisputeModal() {
            disputePanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                disputeOverlay.classList.add('hidden');
                disputeOverlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.dispute-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openDisputeModal(parseInt(btn.dataset.disputeId, 10)));
        });

        disputeCloseBtn.addEventListener('click', closeDisputeModal);
        disputeOverlay.addEventListener('click', (e) => {
            if (e.target === disputeOverlay) closeDisputeModal();
        });

        disputeMediateBtn.addEventListener('click', () => {
            if (currentDisputeId !== null) openMediateModal(currentDisputeId);
        });

        /* -----------------------------------------------------------
           MEDIATION MODAL
        ----------------------------------------------------------- */
        const mediateOverlay = document.getElementById('mediateModalOverlay');
        const mediatePanel   = document.getElementById('mediateModalPanel');
        const mediateCloseBtn = document.getElementById('mediateModalClose');
        const mediateSubmitBtn = document.getElementById('mediateSubmitBtn');
        const mediateNotes = document.getElementById('mediateNotes');
        const mediateRefundRow = document.getElementById('mediateRefundRow');
        const mediateRefundAmount = document.getElementById('mediateRefundAmount');

        function openMediateModal(id) {
            const d = disputesData.find(x => x.id === id);
            if (!d) return;

            currentDisputeId = id;
            selectedResolution = null;

            document.getElementById('mediateModalOrderNo').textContent = 'Order ' + d.order_no;
            document.getElementById('mediateModalParties').textContent = d.buyer.name + ' vs ' + d.seller.name;
            document.getElementById('mediateModalSummary').textContent = d.category + ' — ' + d.description;

            mediateRefundAmount.value = d.order.amount;
            mediateRefundRow.classList.add('hidden');
            mediateNotes.value = '';

            document.querySelectorAll('.mediate-option-btn').forEach(btn => {
                btn.classList.remove('border-mint-dark', 'bg-mint/5', 'ring-2', 'ring-mint/20');
                btn.classList.add('border-slate-200');
            });

            updateMediateSubmitState();

            mediateOverlay.classList.remove('hidden');
            mediateOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                mediatePanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeMediateModal() {
            mediatePanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                mediateOverlay.classList.add('hidden');
                mediateOverlay.classList.remove('flex');
            }, 150);
        }

        function updateMediateSubmitState() {
            const ready = !!selectedResolution && mediateNotes.value.trim().length > 0;
            mediateSubmitBtn.disabled = !ready;
            mediateSubmitBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark transition ' + (ready ? 'opacity-100 hover:opacity-90' : 'opacity-40 cursor-not-allowed');
        }

        document.querySelectorAll('.dispute-mediate-btn').forEach(btn => {
            btn.addEventListener('click', () => openMediateModal(parseInt(btn.dataset.disputeId, 10)));
        });

        document.querySelectorAll('.mediate-option-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedResolution = btn.dataset.value;

                document.querySelectorAll('.mediate-option-btn').forEach(b => {
                    b.classList.remove('border-mint-dark', 'bg-mint/5', 'ring-2', 'ring-mint/20');
                    b.classList.add('border-slate-200');
                });
                btn.classList.remove('border-slate-200');
                btn.classList.add('border-mint-dark', 'bg-mint/5', 'ring-2', 'ring-mint/20');

                mediateRefundRow.classList.toggle('hidden', selectedResolution !== 'refund_buyer');
                updateMediateSubmitState();
            });
        });

        mediateNotes.addEventListener('input', updateMediateSubmitState);

        mediateCloseBtn.addEventListener('click', closeMediateModal);
        mediateOverlay.addEventListener('click', (e) => {
            if (e.target === mediateOverlay) closeMediateModal();
        });
        document.getElementById('mediateCancelBtn').addEventListener('click', closeMediateModal);

        mediateSubmitBtn.addEventListener('click', () => {
            if (mediateSubmitBtn.disabled || currentDisputeId === null) return;

            // TODO: palitan ito ng totoong POST papunta sa backend, e.g.
            // fetch(`/admin/disputes/${currentDisputeId}/mediate`, { method: 'POST', body: ... })
            const payload = {
                dispute_id: currentDisputeId,
                resolution: selectedResolution,
                refund_amount: selectedResolution === 'refund_buyer' ? mediateRefundAmount.value : null,
                notes: mediateNotes.value.trim(),
            };
            console.log('Mediation resolution (UI only, wala pang backend):', payload);

            closeMediateModal();
            closeDisputeModal();
            showToast('Resolution recorded. (UI preview only — connect this to the backend.)');
        });

        /* -----------------------------------------------------------
           TOAST
        ----------------------------------------------------------- */
        const toast = document.getElementById('toastNotification');
        const toastMessage = document.getElementById('toastMessage');
        let toastTimeout = null;

        function showToast(message) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            toast.classList.add('flex');
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('hidden');
                    toast.classList.remove('flex');
                }, 150);
            }, 3200);
        }

        // Escape key — isara ang pinaka-taas na bukas na modal
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!mediateOverlay.classList.contains('hidden')) closeMediateModal();
            else if (!disputeOverlay.classList.contains('hidden')) closeDisputeModal();
        });
    </script>

@endsection