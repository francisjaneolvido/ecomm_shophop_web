@extends('admin.layout')

@section('title', 'Account Registrations')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: alisin ito pag kinonekta na natin sa DB / controller.
        Yung mga variable names ($counts, $filter, $role, $registrations)
        ay sinadya na paret sa gagamitin natin galing sa controller
        mamaya, so drop-in lang siya once real data na yung gagamitin.
        Same pattern din ito ng ginamit natin sa Seller Compliance,
        Commission, at User Accounts pages.
    ========================================================= --}}
    @php
        use Illuminate\Pagination\LengthAwarePaginator;
        use Carbon\Carbon;

        $filter = request('filter', 'pending'); // pending, approved, rejected
        $role   = request('role', 'all');        // all, buyer, seller, logistics
        $sort   = request('sort', 'newest');

        $sampleRegistrations = collect([
            (object) [
                'id' => 1, 'initials' => 'JC', 'name' => 'Juan Cruz',
                'email' => 'juan.cruz@example.com', 'phone' => '+63 917 123 4567',
                'address' => 'Blk 12 Lot 4, Barangay San Isidro, Los Baños, Laguna',
                'role' => 'buyer', 'status' => 'pending',
                'sex' => 'Male', 'birthday' => Carbon::parse('1996-05-14'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subHours(5),
                'document' => 'Valid ID',
                'notes' => 'Kompleto ang requirements, unang beses mag-apply.',
                'documents' => [
                    ['label' => 'Valid ID', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Submitted Valid ID', 'time' => Carbon::now()->subHours(5)->diffForHumans()],
                    ['label' => 'Registered as buyer', 'time' => Carbon::now()->subHours(5)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 2, 'initials' => 'MS', 'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com', 'phone' => '+63 918 234 5678',
                'address' => '45 Mabini St., Poblacion, Calamba, Laguna',
                'role' => 'seller', 'status' => 'pending',
                'sex' => 'Female', 'birthday' => Carbon::parse('1990-11-02'),
                'business_name' => 'Santos Home Essentials', 'business_category' => 'Home & Living',
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subDays(1),
                'document' => 'DTI Business Permit, BIR Form 2303',
                'notes' => 'DTI at BIR complete na; Barangay permit lang ang pending.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Submitted BIR Form 2303', 'time' => Carbon::now()->subHours(20)->diffForHumans()],
                    ['label' => 'Submitted DTI Business Permit', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Registered as seller', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 3, 'initials' => 'RL', 'name' => 'Rodel Lim',
                'email' => 'rodel.lim@example.com', 'phone' => '+63 919 345 6789',
                'address' => '78 Rizal Ave., Bay, Laguna',
                'role' => 'logistics', 'status' => 'pending',
                'sex' => 'Male', 'birthday' => Carbon::parse('1988-03-21'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => 'Multicab', 'plate_number' => 'ABC 1234',
                'submitted_at' => Carbon::now()->subHours(14),
                'document' => "OR/CR, Driver's License",
                'notes' => 'Kompleto ang OR/CR at lisensya, naghihintay lang ng approval.',
                'documents' => [
                    ['label' => 'OR/CR', 'status' => 'submitted', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => "Submitted Driver's License", 'time' => Carbon::now()->subHours(14)->diffForHumans()],
                    ['label' => 'Submitted OR/CR', 'time' => Carbon::now()->subHours(15)->diffForHumans()],
                    ['label' => 'Registered as logistics partner', 'time' => Carbon::now()->subHours(15)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 4, 'initials' => 'AP', 'name' => 'Ana Pineda',
                'email' => 'ana.pineda@example.com', 'phone' => '+63 920 456 7890',
                'address' => '23 Bonifacio St., Bayog, Los Baños, Laguna',
                'role' => 'buyer', 'status' => 'approved',
                'sex' => 'Female', 'birthday' => Carbon::parse('1999-07-09'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subDays(4),
                'document' => 'Valid ID',
                'notes' => 'Na-approve matapos ma-verify ang ID.',
                'documents' => [
                    ['label' => 'Valid ID', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(3)->diffForHumans()],
                    ['label' => 'Submitted Valid ID', 'time' => Carbon::now()->subDays(4)->diffForHumans()],
                    ['label' => 'Registered as buyer', 'time' => Carbon::now()->subDays(4)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 5, 'initials' => 'GT', 'name' => 'Grace Tan',
                'email' => 'grace.tan@example.com', 'phone' => '+63 922 678 9012',
                'address' => '156 Real St., Sta. Cruz, Laguna',
                'role' => 'seller', 'status' => 'approved',
                'sex' => 'Female', 'birthday' => Carbon::parse('1994-09-18'),
                'business_name' => "Tan's Pasalubong Corner", 'business_category' => 'Food & Beverages',
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subDays(6),
                'document' => 'DTI, BIR, Barangay Permit',
                'notes' => 'Kompleto at valid lahat ng naisumiteng dokumento.',
                'documents' => [
                    ['label' => 'DTI Business Permit', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'BIR Form 2303', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'Barangay Business Permit', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(5)->diffForHumans()],
                    ['label' => 'Submitted all required documents', 'time' => Carbon::now()->subDays(6)->diffForHumans()],
                    ['label' => 'Registered as seller', 'time' => Carbon::now()->subDays(6)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 6, 'initials' => 'LF', 'name' => 'Liza Fernandez',
                'email' => 'liza.fernandez@example.com', 'phone' => '+63 924 890 1234',
                'address' => '67 Luna St., Victoria, Laguna',
                'role' => 'logistics', 'status' => 'approved',
                'sex' => 'Female', 'birthday' => Carbon::parse('1992-04-27'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => 'Delivery Van', 'plate_number' => 'XYZ 9081',
                'submitted_at' => Carbon::now()->subDays(9),
                'document' => "OR/CR, Driver's License",
                'notes' => 'Malinis ang record, na-approve agad.',
                'documents' => [
                    ['label' => 'OR/CR', 'status' => 'submitted', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(7)->diffForHumans()],
                    ['label' => 'Submitted OR/CR', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                    ['label' => 'Registered as logistics partner', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 7, 'initials' => 'KB', 'name' => 'Kevin Bautista',
                'email' => 'kevin.bautista@example.com', 'phone' => '+63 921 567 8901',
                'address' => '9 Del Pilar St., Bagong Silang, Cabuyao, Laguna',
                'role' => 'buyer', 'status' => 'rejected',
                'sex' => 'Male', 'birthday' => Carbon::parse('2001-01-30'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subDays(12),
                'document' => 'Valid ID',
                'notes' => 'Na-reject dahil sa hindi malinaw na larawan ng ID.',
                'documents' => [
                    ['label' => 'Valid ID', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Application rejected — unclear ID photo', 'time' => Carbon::now()->subDays(11)->diffForHumans()],
                    ['label' => 'Submitted Valid ID', 'time' => Carbon::now()->subDays(12)->diffForHumans()],
                    ['label' => 'Registered as buyer', 'time' => Carbon::now()->subDays(12)->diffForHumans()],
                ],
                'reports' => [
                    [
                        'type' => 'Unclear Document',
                        'description' => 'Malabo at hindi mabasa ang naisumiteng valid ID.',
                        'date' => Carbon::now()->subDays(11),
                    ],
                ],
            ],
            (object) [
                'id' => 8, 'initials' => 'CV', 'name' => 'Cathy Villanueva',
                'email' => 'cathy.villanueva@example.com', 'phone' => '+63 926 012 3456',
                'address' => '81 Quezon Ave., Nagcarlan, Laguna',
                'role' => 'seller', 'status' => 'rejected',
                'sex' => 'Female', 'birthday' => Carbon::parse('1989-10-11'),
                'business_name' => 'Villanueva Crafts & Gifts', 'business_category' => 'Arts & Crafts',
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subDays(18),
                'document' => 'Valid ID lang',
                'notes' => 'Na-reject dahil kulang ang DTI at BIR documents matapos ang follow-up.',
                'documents' => [
                    ['label' => 'Valid ID', 'status' => 'submitted', 'url' => '#'],
                    ['label' => 'DTI Business Permit', 'status' => 'missing', 'url' => null],
                    ['label' => 'BIR Form 2303', 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Application rejected — incomplete business documents', 'time' => Carbon::now()->subDays(15)->diffForHumans()],
                    ['label' => 'Reminder sent for missing documents', 'time' => Carbon::now()->subDays(17)->diffForHumans()],
                    ['label' => 'Registered as seller', 'time' => Carbon::now()->subDays(18)->diffForHumans()],
                ],
                'reports' => [
                    [
                        'type' => 'Incomplete Documents',
                        'description' => 'Hindi naisumite ang DTI Permit at BIR Form 2303 matapos ang 2 follow-up.',
                        'date' => Carbon::now()->subDays(15),
                    ],
                ],
            ],
            (object) [
                'id' => 9, 'initials' => 'DR', 'name' => 'Dennis Reyes',
                'email' => 'dennis.reyes@example.com', 'phone' => '+63 925 901 2345',
                'address' => '5 Mercado St., Alaminos, Laguna',
                'role' => 'buyer', 'status' => 'pending',
                'sex' => 'Male', 'birthday' => Carbon::parse('2003-06-15'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => null, 'plate_number' => null,
                'submitted_at' => Carbon::now()->subMinutes(40),
                'document' => 'Valid ID',
                'notes' => 'Kararegister lang.',
                'documents' => [
                    ['label' => 'Valid ID', 'status' => 'submitted', 'url' => '#'],
                ],
                'activity' => [
                    ['label' => 'Submitted Valid ID', 'time' => Carbon::now()->subMinutes(40)->diffForHumans()],
                    ['label' => 'Registered as buyer', 'time' => Carbon::now()->subMinutes(40)->diffForHumans()],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 10, 'initials' => 'EM', 'name' => 'Erwin Mendoza',
                'email' => 'erwin.mendoza@example.com', 'phone' => '+63 923 789 0123',
                'address' => '33 Aguinaldo St., Pila, Laguna',
                'role' => 'logistics', 'status' => 'pending',
                'sex' => 'Male', 'birthday' => Carbon::parse('1997-12-05'),
                'business_name' => null, 'business_category' => null,
                'vehicle_type' => 'Motorcycle', 'plate_number' => 'LGN 8842',
                'submitted_at' => Carbon::now()->subDays(2),
                'document' => "OR/CR",
                'notes' => 'OR/CR lang ang naisumite, hinihintay pa ang lisensya.',
                'documents' => [
                    ['label' => 'OR/CR', 'status' => 'submitted', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'status' => 'missing', 'url' => null],
                ],
                'activity' => [
                    ['label' => 'Submitted OR/CR', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Registered as logistics partner', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                ],
                'reports' => [],
            ],
        ]);

        // Totals for the summary cards — computed off the full sample set,
        // not the filtered/paginated view below.
        $pendingCount   = $sampleRegistrations->where('status', 'pending')->count();
        $approvedCount  = $sampleRegistrations->where('status', 'approved')->count();
        $rejectedCount  = $sampleRegistrations->where('status', 'rejected')->count();
        $buyerCount     = $sampleRegistrations->where('role', 'buyer')->count();
        $sellerCount    = $sampleRegistrations->where('role', 'seller')->count();
        $logisticsCount = $sampleRegistrations->where('role', 'logistics')->count();

        $counts = [
            'pending'   => $pendingCount,
            'approved'  => $approvedCount,
            'rejected'  => $rejectedCount,
            'buyer'     => $buyerCount,
            'seller'    => $sellerCount,
            'logistics' => $logisticsCount,
        ];

        // Status filter simulation, same idea as the other admin pages' tabs
        $filtered = match ($filter) {
            'approved' => $sampleRegistrations->where('status', 'approved'),
            'rejected' => $sampleRegistrations->where('status', 'rejected'),
            default    => $sampleRegistrations->where('status', 'pending'),
        };

        // Role filter simulation
        if ($role !== 'all') {
            $filtered = $filtered->where('role', $role);
        }

        // Search simulation — name, email, or role
        if ($search = request('search')) {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($r) use ($needle) {
                return str_contains(strtolower($r->name), $needle)
                    || str_contains(strtolower($r->email), $needle)
                    || str_contains(strtolower($r->role), $needle);
            });
        }

        // Sort simulation — stands in for an ->orderBy() once wired to the DB
        $filtered = match ($sort) {
            'oldest' => $filtered->sortBy('submitted_at'),
            'az'     => $filtered->sortBy(fn ($r) => strtolower($r->name)),
            'za'     => $filtered->sortByDesc(fn ($r) => strtolower($r->name)),
            default  => $filtered->sortByDesc('submitted_at'), // newest
        };

        $filtered = $filtered->values();

        $registrations = new LengthAwarePaginator(
            $filtered,
            $filtered->count(),
            8,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Style maps used by both the list rows AND the modal (JS side)
        $roleStyles = [
            'buyer'     => ['avatar' => 'bg-sky/10 text-sky', 'badge' => 'bg-sky/10 text-sky', 'icon' => 'shopping-bag', 'label' => 'Buyer'],
            'seller'    => ['avatar' => 'bg-coral/10 text-coral', 'badge' => 'bg-coral/10 text-coral', 'icon' => 'store', 'label' => 'Seller'],
            'logistics' => ['avatar' => 'bg-violet-100 text-violet-600', 'badge' => 'bg-violet-100 text-violet-600', 'icon' => 'truck', 'label' => 'Logistics'],
        ];

        $statusStyles = [
            'pending'  => ['dot' => 'bg-amber-600', 'text' => 'text-amber-700', 'bg' => 'bg-amber-100', 'label' => 'Pending Review'],
            'approved' => ['dot' => 'bg-mint-dark', 'text' => 'text-mint-dark', 'bg' => 'bg-mint/10', 'label' => 'Approved'],
            'rejected' => ['dot' => 'bg-coral', 'text' => 'text-coral', 'bg' => 'bg-coral/10', 'label' => 'Rejected'],
        ];

        // Flattened + string-formatted version so it can be @json()'d and used
        // by the modal JS (Carbon instances don't serialize cleanly on their
        // own, so we reformat them here before passing them to @json())
        $registrationsForJs = $sampleRegistrations->map(function ($r) {
            $submittedCount = collect($r->documents)->where('status', 'submitted')->count();
            $totalCount = count($r->documents);

            return [
                'id'                 => $r->id,
                'initials'           => $r->initials,
                'name'               => $r->name,
                'email'              => $r->email,
                'phone'              => $r->phone,
                'address'            => $r->address,
                'role'               => $r->role,
                'status'             => $r->status,
                'sex'                => $r->sex,
                'birthday'           => $r->birthday->format('M d, Y'),
                'age'                => $r->birthday->age,
                'business_name'      => $r->business_name,
                'business_category'  => $r->business_category,
                'vehicle_type'       => $r->vehicle_type,
                'plate_number'       => $r->plate_number,
                'submitted_at'       => $r->submitted_at->format('M d, Y g:ia') . ' · ' . $r->submitted_at->diffForHumans(),
                'reg_no'             => '#' . str_pad($r->id, 6, '0', STR_PAD_LEFT),
                'docs_summary'       => ['value' => $submittedCount . '/' . $totalCount, 'sub' => $submittedCount === $totalCount ? 'All documents complete' : ($totalCount - $submittedCount) . ' document(s) incomplete'],
                'notes'              => $r->notes,
                'activity'           => $r->activity,
                'documents'          => $r->documents,
                'reports'            => collect($r->reports ?? [])->map(fn ($rep) => [
                    'type'        => $rep['type'],
                    'description' => $rep['description'],
                    'date'        => $rep['date']->format('M d, Y') . ' · ' . $rep['date']->diffForHumans(),
                ])->values(),
            ];
        })->values();
    @endphp

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm text-mint-dark">
            {{ session('status') }}
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
                    <p class="text-xs text-slate-400 font-medium">Buyers</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $buyerCount }}</p>
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
                    <p class="text-2xl font-bold text-navy mt-1">{{ $sellerCount }}</p>
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
                    <p class="text-2xl font-bold text-navy mt-1">{{ $logisticsCount }}</p>
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
                Pending <span class="ml-1 opacity-70">({{ $pendingCount }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'approved', 'role' => $role, 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $filter === 'approved' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Approved <span class="ml-1 opacity-70">({{ $approvedCount }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'rejected', 'role' => $role, 'sort' => $sort]) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $filter === 'rejected' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Rejected <span class="ml-1 opacity-70">({{ $rejectedCount }})</span>
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
                    $rStyle = $roleStyles[$registration->role] ?? ['avatar' => 'bg-slate-100 text-slate-500', 'badge' => 'bg-slate-100 text-slate-500', 'icon' => 'user', 'label' => ucfirst($registration->role)];
                    $sStyle = $statusStyles[$registration->status] ?? $statusStyles['pending'];
                @endphp

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-4 hover:bg-slate-50/60 transition">

                    <div class="flex items-start sm:items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl {{ $rStyle['avatar'] }} flex items-center justify-center shrink-0">
                            <span class="text-sm font-bold">{{ $registration->initials }}</span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-navy truncate">{{ $registration->name }}</p>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $rStyle['badge'] }}">
                                    <x-dynamic-component :component="'lucide-' . $rStyle['icon']" class="w-3 h-3" />
                                    {{ $rStyle['label'] }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $registration->email }}</p>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                    <x-lucide-file-check class="w-3 h-3" />
                                    {{ $registration->document }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400">
                                    <x-lucide-clock class="w-3 h-3" />
                                    Applied {{ $registration->submitted_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:pl-14 lg:pl-0 shrink-0">

                        <button type="button" class="registration-view-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-navy transition"
                            data-registration-id="{{ $registration->id }}">
                            <x-lucide-eye class="w-3.5 h-3.5" />
                            View
                        </button>

                        @if ($registration->status === 'pending')
                            <button type="button" class="registration-approve-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition"
                                data-registration-id="{{ $registration->id }}">
                                <x-lucide-check class="w-3.5 h-3.5" />
                                Approve
                            </button>
                            <button type="button" class="registration-reject-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition"
                                data-registration-id="{{ $registration->id }}">
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
        Same chrome/pattern as Seller Compliance / User Accounts —
        accent bar, floating close circle, 2-column body. Populated
        straight from the hardcoded $registrationsForJs array below
        instead of an AJAX fetch, same as the other preview pages.
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
                            <div id="modalVehicleRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Vehicle</dt>
                                <dd id="modalVehicle" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div id="modalPlateRow" class="hidden justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Plate Number</dt>
                                <dd id="modalPlate" class="text-xs text-slate-700 text-right"></dd>
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
            <div id="modalFooter" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2"></div>

        </div>

    </div>


    {{-- =========================================================
        APPROVE / REJECT — CONFIRMATION MODAL
        Same reused pattern as Seller Compliance's verify/flag modal.
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


    {{-- =========================================================
        MODAL DATA + BEHAVIOR
        TODO: once this is on the DB, this can become a route-based
        fetch (fetch() → admin.registrations.show as JSON) instead of
        a hardcoded array — the open/close/populate logic stays the
        same. confirmProceedBtn should POST to the approve/reject
        endpoints (admin.users.approve / admin.users.reject) instead
        of just console.log.
    ========================================================= --}}
    <script>
        const registrationsData = @json($registrationsForJs);

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

        // Generic file icon lang — ginagamit sa placeholder document cards
        // (wala pang actual file/image, preview-only text details muna)
        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        /* -----------------------------------------------------------
           REGISTRATION DETAILS MODAL
        ----------------------------------------------------------- */
        const overlay  = document.getElementById('registrationModalOverlay');
        const panel    = document.getElementById('registrationModalPanel');
        const closeBtn = document.getElementById('registrationModalClose');
        const modalFooter = document.getElementById('modalFooter');

        let currentModalRegistration = null;

        function openRegistrationModal(id) {
            const r = registrationsData.find(x => x.id === id);
            if (!r) return;

            currentModalRegistration = r;

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

            document.getElementById('modalPhone').textContent = r.phone;
            document.getElementById('modalAddress').textContent = r.address;
            document.getElementById('modalRegNo').textContent = r.reg_no;
            document.getElementById('modalSubmittedAt').textContent = r.submitted_at;
            document.getElementById('modalNotes').textContent = r.notes;

            document.getElementById('modalSex').textContent = r.sex;
            document.getElementById('modalBirthday').textContent = r.birthday;
            document.getElementById('modalAge').textContent = r.age;

            const businessNameRow = document.getElementById('modalBusinessNameRow');
            const businessCategoryRow = document.getElementById('modalBusinessCategoryRow');
            const vehicleRow = document.getElementById('modalVehicleRow');
            const plateRow = document.getElementById('modalPlateRow');

            businessNameRow.className = r.business_name ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.business_name) document.getElementById('modalBusinessName').textContent = r.business_name;

            businessCategoryRow.className = r.business_category ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.business_category) document.getElementById('modalBusinessCategory').textContent = r.business_category;

            vehicleRow.className = r.vehicle_type ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.vehicle_type) document.getElementById('modalVehicle').textContent = r.vehicle_type;

            plateRow.className = r.plate_number ? 'flex justify-between gap-3' : 'hidden justify-between gap-3';
            if (r.plate_number) document.getElementById('modalPlate').textContent = r.plate_number;

            document.getElementById('modalDocsValue').textContent = r.docs_summary.value;
            document.getElementById('modalDocsSub').textContent = r.docs_summary.sub;

            // Recent activity
            const activityList = document.getElementById('modalActivity');
            activityList.innerHTML = '';
            (r.activity || []).forEach(item => {
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

            // Submitted documents — dashed-border preview cards
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

            // Footer actions — Approve/Reject if still pending, nothing otherwise
            modalFooter.innerHTML = '';
            if (r.status === 'pending') {
                const approveBtn = document.createElement('button');
                approveBtn.type = 'button';
                approveBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                approveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Approve';
                approveBtn.addEventListener('click', () => openConfirmModal('approve', r));

                const rejectBtn = document.createElement('button');
                rejectBtn.type = 'button';
                rejectBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 hover:-translate-y-0.5 transition-all duration-300';
                rejectBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg> Reject';
                rejectBtn.addEventListener('click', () => openConfirmModal('reject', r));

                modalFooter.appendChild(rejectBtn);
                modalFooter.appendChild(approveBtn);
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function closeRegistrationModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.registration-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openRegistrationModal(parseInt(btn.dataset.registrationId, 10)));
        });

        closeBtn.addEventListener('click', closeRegistrationModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeRegistrationModal(); });

        /* -----------------------------------------------------------
           APPROVE / REJECT — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay      = document.getElementById('confirmModalOverlay');
        const confirmPanel        = document.getElementById('confirmModalPanel');
        const confirmIconWrap     = document.getElementById('confirmIconWrap');
        const confirmTitle        = document.getElementById('confirmTitle');
        const confirmMessage      = document.getElementById('confirmMessage');
        const confirmProceedBtn   = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn    = document.getElementById('confirmCancelBtn');
        const confirmRejectFields = document.getElementById('confirmRejectFields');
        const confirmApproveFields= document.getElementById('confirmApproveFields');

        let activeConfirmAction = null;
        let activeConfirmRegistration = null;

        const checkIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        const flagIconSvg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>';

        function openConfirmModal(action, registration) {
            activeConfirmAction = action;
            activeConfirmRegistration = registration;

            confirmRejectFields.classList.toggle('hidden', action !== 'reject');
            confirmApproveFields.classList.toggle('hidden', action !== 'approve');

            if (action === 'approve') {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
                confirmIconWrap.innerHTML = checkIconSvg;
                confirmTitle.textContent = 'Approve this application?';
                confirmMessage.textContent = `Are you sure you want to approve ${registration.name}'s ${roleLabels[registration.role].toLowerCase()} account? This will grant them full platform access.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Approve';
                document.getElementById('confirmApproveNotes').value = '';
            } else {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral';
                confirmIconWrap.innerHTML = flagIconSvg;
                confirmTitle.textContent = 'Reject this application?';
                confirmMessage.textContent = `Reject ${registration.name}'s application? They will need to reapply or resubmit their documents.`;
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

        document.querySelectorAll('.registration-approve-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const r = registrationsData.find(x => x.id === parseInt(btn.dataset.registrationId, 10));
                if (r) openConfirmModal('approve', r);
            });
        });

        document.querySelectorAll('.registration-reject-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const r = registrationsData.find(x => x.id === parseInt(btn.dataset.registrationId, 10));
                if (r) openConfirmModal('reject', r);
            });
        });

        confirmProceedBtn.addEventListener('click', () => {
            if (!activeConfirmRegistration) return;

            // TODO: replace with actual requests to the backend, e.g.:
            // axios.post(`/admin/users/${activeConfirmRegistration.id}/approve`, { notes: ... });
            // axios.post(`/admin/users/${activeConfirmRegistration.id}/reject`, { reason: ..., notes: ... });
            if (activeConfirmAction === 'approve') {
                console.log('Approving registration', activeConfirmRegistration.id, 'notes:', document.getElementById('confirmApproveNotes').value);
            } else {
                console.log('Rejecting registration', activeConfirmRegistration.id,
                    'reason:', document.getElementById('confirmRejectReason').value,
                    'notes:', document.getElementById('confirmRejectNotes').value);
            }
            closeConfirmModal();
            closeRegistrationModal();
        });

        // Escape key — close whichever of the two modals is open
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeRegistrationModal();
        });
    </script>

@endsection