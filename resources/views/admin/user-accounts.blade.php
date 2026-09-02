@extends('admin.layout')

@section('title', 'User Accounts')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: alisin ito pag kinonekta na natin sa DB / controller.
        Yung mga variable names ($counts, $filter, $users) ay
        sinadya na paret sa gagamitin natin galing sa controller
        mamaya, so drop-in lang siya once real data na yung gagamitin.
    ========================================================= --}}
    @php
        use Illuminate\Pagination\LengthAwarePaginator;
        use Carbon\Carbon;

        $filter = request('filter', 'all');
        $sort   = request('sort', 'newest');

        $counts = [
            'all'       => 42,
            'buyers'    => 21,
            'sellers'   => 12,
            'logistics' => 5,
            'riders'    => 4,
            'suspended' => 3,
        ];

        $sampleUsers = collect([
            (object) [
                'id' => 1, 'initials' => 'JC', 'display_name' => 'Juan Cruz',
                'email' => 'juan.cruz@example.com', 'phone' => '+63 917 123 4567',
                'address' => 'Blk 12 Lot 4, Barangay San Isidro, Los Baños, Laguna',
                'account_type' => 'buyer', 'status' => 'approved', 'verified' => true,
                'sex' => 'Male', 'birthday' => Carbon::parse('1996-05-14'),
                'created_at' => Carbon::now()->subDays(2), 'last_login' => Carbon::now()->subHours(3),
                'stats' => ['label' => 'Orders Placed', 'value' => 18, 'sub' => '₱24,350 total spent'],
                'notes' => 'Regular buyer, walang naitalang report o violation.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subHours(3)->diffForHumans()],
                    ['label' => 'Placed an order (₱1,240)', 'time' => Carbon::now()->subHours(20)->diffForHumans()],
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 2, 'initials' => 'MS', 'display_name' => 'Maria Santos',
                'email' => 'maria.santos@example.com', 'phone' => '+63 918 234 5678',
                'address' => '45 Mabini St., Poblacion, Calamba, Laguna',
                'account_type' => 'seller', 'status' => 'active', 'verified' => true,
                'sex' => 'Female', 'birthday' => Carbon::parse('1990-11-02'),
                'business_name' => "Santos Home Essentials", 'business_category' => 'Home & Living',
                'created_at' => Carbon::now()->subDays(10), 'last_login' => Carbon::now()->subDays(1),
                'stats' => ['label' => 'Products Listed', 'value' => 34, 'sub' => '4.8★ seller rating'],
                'notes' => 'Verified seller account, complete ang business documents.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                    ['label' => 'Added a new product listing', 'time' => Carbon::now()->subDays(2)->diffForHumans()],
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(10)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                    ['label' => 'Business Permit', 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 3, 'initials' => 'RL', 'display_name' => 'Rodel Lim',
                'email' => 'rodel.lim@example.com', 'phone' => '+63 919 345 6789',
                'address' => '78 Rizal Ave., Bay, Laguna',
                'account_type' => 'logistics', 'status' => 'suspended', 'verified' => true,
                'sex' => 'Male', 'birthday' => Carbon::parse('1988-03-21'),
                'vehicle_type' => 'Multicab', 'plate_number' => 'ABC 1234',
                'created_at' => Carbon::now()->subDays(25), 'last_login' => Carbon::now()->subDays(6),
                'stats' => ['label' => 'Deliveries Handled', 'value' => 210, 'sub' => '2 late-delivery reports'],
                'notes' => 'Suspended dahil sa paulit-ulit na late delivery reports.',
                'activity' => [
                    ['label' => 'Account suspended', 'time' => Carbon::now()->subDays(6)->diffForHumans()],
                    ['label' => 'Late-delivery report filed', 'time' => Carbon::now()->subDays(8)->diffForHumans()],
                    ['label' => 'Completed a delivery', 'time' => Carbon::now()->subDays(10)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'url' => '#'],
                ],
                'reports' => [
                    [
                        'type' => 'Late Delivery',
                        'description' => 'Ikatlong beses na ma-late ang delivery ngayong buwan.',
                        'date' => Carbon::now()->subDays(8),
                    ],
                    [
                        'type' => 'Late Delivery',
                        'description' => 'Reklamo ng customer dahil sa 2-oras na delay.',
                        'date' => Carbon::now()->subDays(15),
                    ],
                ],
            ],
            (object) [
                'id' => 4, 'initials' => 'AP', 'display_name' => 'Ana Pineda',
                'email' => 'ana.pineda@example.com', 'phone' => '+63 920 456 7890',
                'address' => '23 Bonifacio St., Bayog, Los Baños, Laguna',
                'account_type' => 'rider', 'status' => 'approved', 'verified' => true,
                'sex' => 'Female', 'birthday' => Carbon::parse('1999-07-09'),
                'vehicle_type' => 'Motorcycle', 'plate_number' => 'NKP 5567',
                'created_at' => Carbon::now()->subDays(40), 'last_login' => Carbon::now()->subHours(10),
                'stats' => ['label' => 'Trips Completed', 'value' => 156, 'sub' => '4.9★ rider rating'],
                'notes' => 'Consistent 5-star ratings, walang open complaints.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subHours(10)->diffForHumans()],
                    ['label' => 'Completed a delivery trip', 'time' => Carbon::now()->subHours(14)->diffForHumans()],
                    ['label' => 'Rated 5 stars by a customer', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 5, 'initials' => 'KB', 'display_name' => 'Kevin Bautista',
                'email' => 'kevin.bautista@example.com', 'phone' => '+63 921 567 8901',
                'address' => '9 Del Pilar St., Bagong Silang, Cabuyao, Laguna',
                'account_type' => 'buyer', 'status' => 'disabled', 'verified' => false,
                'sex' => 'Male', 'birthday' => Carbon::parse('2001-01-30'),
                'created_at' => Carbon::now()->subDays(60), 'last_login' => Carbon::now()->subDays(45),
                'stats' => ['label' => 'Orders Placed', 'value' => 2, 'sub' => '₱890 total spent'],
                'notes' => 'Na-disable ang account dahil sa unverified na email.',
                'activity' => [
                    ['label' => 'Account disabled (unverified email)', 'time' => Carbon::now()->subDays(45)->diffForHumans()],
                    ['label' => 'Placed an order', 'time' => Carbon::now()->subDays(50)->diffForHumans()],
                    ['label' => 'Registered account', 'time' => Carbon::now()->subDays(60)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                ],
                'reports' => [
                    [
                        'type' => 'Unverified Email',
                        'description' => 'Hindi na-verify ang email address matapos ang 30 araw, kaya na-disable ang account.',
                        'date' => Carbon::now()->subDays(45),
                    ],
                ],
            ],
            (object) [
                'id' => 6, 'initials' => 'GT', 'display_name' => 'Grace Tan',
                'email' => 'grace.tan@example.com', 'phone' => '+63 922 678 9012',
                'address' => '156 Real St., Sta. Cruz, Laguna',
                'account_type' => 'seller', 'status' => 'approved', 'verified' => true,
                'sex' => 'Female', 'birthday' => Carbon::parse('1994-09-18'),
                'business_name' => "Tan's Pasalubong Corner", 'business_category' => 'Food & Beverages',
                'created_at' => Carbon::now()->subDays(3), 'last_login' => Carbon::now()->subHours(1),
                'stats' => ['label' => 'Products Listed', 'value' => 12, 'sub' => '5.0★ seller rating'],
                'notes' => 'Bagong seller, mabilis mag-restock.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subHours(1)->diffForHumans()],
                    ['label' => 'Listed a new product', 'time' => Carbon::now()->subHours(5)->diffForHumans()],
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(3)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                    ['label' => 'Business Permit', 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 7, 'initials' => 'EM', 'display_name' => 'Erwin Mendoza',
                'email' => 'erwin.mendoza@example.com', 'phone' => '+63 923 789 0123',
                'address' => '33 Aguinaldo St., Pila, Laguna',
                'account_type' => 'rider', 'status' => 'suspended', 'verified' => true,
                'sex' => 'Male', 'birthday' => Carbon::parse('1997-12-05'),
                'vehicle_type' => 'Motorcycle', 'plate_number' => 'LGN 8842',
                'created_at' => Carbon::now()->subDays(15), 'last_login' => Carbon::now()->subDays(8),
                'stats' => ['label' => 'Trips Completed', 'value' => 41, 'sub' => '3 customer complaints'],
                'notes' => 'Suspended habang iniimbestigahan yung mga complaints.',
                'activity' => [
                    ['label' => 'Account suspended pending investigation', 'time' => Carbon::now()->subDays(8)->diffForHumans()],
                    ['label' => 'Customer complaint filed', 'time' => Carbon::now()->subDays(9)->diffForHumans()],
                    ['label' => 'Completed a delivery trip', 'time' => Carbon::now()->subDays(10)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'url' => '#'],
                ],
                'reports' => [
                    [
                        'type' => 'Customer Complaint',
                        'description' => 'Naiulat na bastos ang pakikitungo sa customer habang naghahatid.',
                        'date' => Carbon::now()->subDays(9),
                    ],
                    [
                        'type' => 'Customer Complaint',
                        'description' => 'Mali ang na-deliver na item, hindi tugma sa order.',
                        'date' => Carbon::now()->subDays(12),
                    ],
                    [
                        'type' => 'Customer Complaint',
                        'description' => 'Hindi sumunod sa delivery instructions ng customer.',
                        'date' => Carbon::now()->subDays(14),
                    ],
                ],
            ],
            (object) [
                'id' => 8, 'initials' => 'LF', 'display_name' => 'Liza Fernandez',
                'email' => 'liza.fernandez@example.com', 'phone' => '+63 924 890 1234',
                'address' => '67 Luna St., Victoria, Laguna',
                'account_type' => 'logistics', 'status' => 'active', 'verified' => true,
                'sex' => 'Female', 'birthday' => Carbon::parse('1992-04-27'),
                'vehicle_type' => 'Delivery Van', 'plate_number' => 'XYZ 9081',
                'created_at' => Carbon::now()->subDays(7), 'last_login' => Carbon::now()->subHours(5),
                'stats' => ['label' => 'Deliveries Handled', 'value' => 89, 'sub' => 'On-time rate: 97%'],
                'notes' => 'Malinis na track record, walang reports.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subHours(5)->diffForHumans()],
                    ['label' => 'Delivery marked on-time', 'time' => Carbon::now()->subHours(9)->diffForHumans()],
                    ['label' => 'Account approved by admin', 'time' => Carbon::now()->subDays(7)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => '#'],
                    ['label' => "ID / Driver's License", 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 9, 'initials' => 'DR', 'display_name' => 'Dennis Reyes',
                'email' => 'dennis.reyes@example.com', 'phone' => '+63 925 901 2345',
                'address' => '5 Mercado St., Alaminos, Laguna',
                'account_type' => 'buyer', 'status' => 'approved', 'verified' => true,
                'sex' => 'Male', 'birthday' => Carbon::parse('2003-06-15'),
                'created_at' => Carbon::now()->subDays(1), 'last_login' => Carbon::now()->subMinutes(20),
                'stats' => ['label' => 'Orders Placed', 'value' => 1, 'sub' => '₱450 total spent'],
                'notes' => 'Kararegister lang, unang order pa lang.',
                'activity' => [
                    ['label' => 'Logged in', 'time' => Carbon::now()->subMinutes(20)->diffForHumans()],
                    ['label' => 'Placed first order', 'time' => Carbon::now()->subHours(2)->diffForHumans()],
                    ['label' => 'Registered account', 'time' => Carbon::now()->subDays(1)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                ],
                'reports' => [],
            ],
            (object) [
                'id' => 10, 'initials' => 'CV', 'display_name' => 'Cathy Villanueva',
                'email' => 'cathy.villanueva@example.com', 'phone' => '+63 926 012 3456',
                'address' => '81 Quezon Ave., Nagcarlan, Laguna',
                'account_type' => 'seller', 'status' => 'suspended', 'verified' => false,
                'sex' => 'Female', 'birthday' => Carbon::parse('1989-10-11'),
                'business_name' => 'Villanueva Crafts & Gifts', 'business_category' => 'Arts & Crafts',
                'created_at' => Carbon::now()->subDays(33), 'last_login' => Carbon::now()->subDays(20),
                'stats' => ['label' => 'Products Listed', 'value' => 6, 'sub' => '2.1★ seller rating'],
                'notes' => 'Suspended dahil sa mga customer complaints tungkol sa item quality.',
                'activity' => [
                    ['label' => 'Account suspended (item quality complaints)', 'time' => Carbon::now()->subDays(20)->diffForHumans()],
                    ['label' => 'Customer complaint filed', 'time' => Carbon::now()->subDays(22)->diffForHumans()],
                    ['label' => 'Listed a new product', 'time' => Carbon::now()->subDays(28)->diffForHumans()],
                ],
                'documents' => [
                    ['label' => 'Valid ID', 'url' => '#'],
                    ['label' => 'Business Permit', 'url' => '#'],
                ],
                'reports' => [
                    [
                        'type' => 'Item Quality',
                        'description' => 'Sira na ang natanggap na produkto ng customer.',
                        'date' => Carbon::now()->subDays(22),
                    ],
                    [
                        'type' => 'Item Quality',
                        'description' => 'Hindi tugma sa larawan ang aktwal na natanggap na item.',
                        'date' => Carbon::now()->subDays(26),
                    ],
                ],
            ],
        ]);

        // Simple filter simulation lang, para makita mo rin gumagana yung tabs
        $filtered = match ($filter) {
            'buyers'    => $sampleUsers->where('account_type', 'buyer'),
            'sellers'   => $sampleUsers->where('account_type', 'seller'),
            'logistics' => $sampleUsers->where('account_type', 'logistics'),
            'riders'    => $sampleUsers->where('account_type', 'rider'),
            'suspended' => $sampleUsers->where('status', 'suspended'),
            default     => $sampleUsers,
        };

        // Search simulation — same idea, filters by name or email
        if ($search = request('search')) {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($u) use ($needle) {
                return str_contains(strtolower($u->display_name), $needle)
                    || str_contains(strtolower($u->email), $needle);
            });
        }

        // Sort simulation — stands in for an ->orderBy() once this is
        // wired to the DB. sortBy/sortByDesc don't reindex keys, hence
        // the ->values() right after.
        $filtered = match ($sort) {
            'oldest' => $filtered->sortBy('created_at'),
            'az'     => $filtered->sortBy(fn ($u) => strtolower($u->display_name)),
            'za'     => $filtered->sortByDesc(fn ($u) => strtolower($u->display_name)),
            default  => $filtered->sortByDesc('created_at'), // newest
        };

        $filtered = $filtered->values();

        $users = new LengthAwarePaginator(
            $filtered,
            $filtered->count(),
            10,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Style maps ginagamit both ng table rows AT ng modal (JS side)
        $roleStyles = [
            'buyer'     => ['class' => 'text-sky bg-sky/10', 'icon' => 'shopping-bag', 'label' => 'Buyer'],
            'seller'    => ['class' => 'text-coral bg-coral/10', 'icon' => 'store', 'label' => 'Seller'],
            'logistics' => ['class' => 'text-violet-600 bg-violet-100', 'icon' => 'truck', 'label' => 'Logistics'],
            'rider'     => ['class' => 'text-amber-600 bg-amber-100', 'icon' => 'bike', 'label' => 'Rider'],
        ];

        $statusStyles = [
            'approved'  => ['dot' => 'bg-mint-dark', 'text' => 'text-mint-dark', 'bg' => 'bg-mint/10', 'label' => 'Active'],
            'active'    => ['dot' => 'bg-mint-dark', 'text' => 'text-mint-dark', 'bg' => 'bg-mint/10', 'label' => 'Active'],
            'suspended' => ['dot' => 'bg-coral', 'text' => 'text-coral', 'bg' => 'bg-coral/10', 'label' => 'Suspended'],
            'disabled'  => ['dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'bg' => 'bg-slate-100', 'label' => 'Disabled'],
        ];

        // Flattened + string-formatted version para ma-json() at magamit sa modal JS
        // (Carbon instances hindi diretsong nase-serialize nang maganda, kaya
        // ipreformat na natin dito bago ilagay sa @json())
        $usersForJs = $sampleUsers->map(function ($u) {
            return [
                'id'           => $u->id,
                'initials'     => $u->initials,
                'display_name' => $u->display_name,
                'email'        => $u->email,
                'phone'        => $u->phone,
                'address'      => $u->address,
                'account_type' => $u->account_type,
                'status'       => $u->status,
                'verified'     => $u->verified,
                'sex'          => $u->sex,
                'birthday'     => $u->birthday->format('M d, Y'),
                'age'          => $u->birthday->age,
                'business_name'     => $u->business_name ?? null,
                'business_category' => $u->business_category ?? null,
                'vehicle_type' => $u->vehicle_type ?? null,
                'plate_number' => $u->plate_number ?? null,
                'joined'       => $u->created_at->format('M d, Y') . ' · ' . $u->created_at->diffForHumans(),
                'last_login'   => $u->last_login->diffForHumans(),
                'account_no'   => '#' . str_pad($u->id, 6, '0', STR_PAD_LEFT),
                'stats'        => $u->stats,
                'notes'        => $u->notes,
                'activity'     => $u->activity,
                'documents'    => $u->documents,
                'reports'      => collect($u->reports ?? [])->map(fn ($r) => [
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

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-navy">
                    User Accounts
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage approved Buyer, Seller, Logistics, and Rider accounts.
                </p>
            </div>

        </div>

    </div>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">

        {{-- All Users --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Total Users
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['all'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-navy/10 text-navy
                           flex items-center justify-center"
                >
                    <x-lucide-users class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Buyers --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Buyers
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['buyers'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-sky/10 text-sky
                           flex items-center justify-center"
                >
                    <x-lucide-shopping-bag class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Sellers --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Sellers
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['sellers'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-coral/10 text-coral
                           flex items-center justify-center"
                >
                    <x-lucide-store class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Logistics --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Logistics
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['logistics'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-violet-100 text-violet-600
                           flex items-center justify-center"
                >
                    <x-lucide-truck class="w-5 h-5" />
                </div>

            </div>

        </div>


        {{-- Riders --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs text-slate-400 font-medium">
                        Riders
                    </p>

                    <p class="text-2xl font-bold text-navy mt-1">
                        {{ $counts['riders'] ?? 0 }}
                    </p>
                </div>

                <div
                    class="w-10 h-10
                           rounded-xl
                           bg-amber-100 text-amber-600
                           flex items-center justify-center"
                >
                    <x-lucide-bike class="w-5 h-5" />
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        FILTER + SORT + SEARCH
    ========================================================= --}}
    <div
        class="flex flex-col xl:flex-row
               xl:items-center
               xl:justify-between
               gap-4
               mb-5"
    >

        {{-- FILTER TABS --}}
        <div class="flex items-center gap-1 flex-wrap">

            {{-- All --}}
            <a
                href="{{ route('admin.users', ['sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'all'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                All

                <span class="ml-1 opacity-70">
                    ({{ $counts['all'] ?? 0 }})
                </span>
            </a>


            {{-- Buyers --}}
            <a
                href="{{ route('admin.users', ['filter' => 'buyers', 'sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'buyers'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Buyers

                <span class="ml-1 opacity-70">
                    ({{ $counts['buyers'] ?? 0 }})
                </span>
            </a>


            {{-- Sellers --}}
            <a
                href="{{ route('admin.users', ['filter' => 'sellers', 'sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'sellers'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Sellers

                <span class="ml-1 opacity-70">
                    ({{ $counts['sellers'] ?? 0 }})
                </span>
            </a>


            {{-- Logistics --}}
            <a
                href="{{ route('admin.users', ['filter' => 'logistics', 'sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'logistics'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Logistics

                <span class="ml-1 opacity-70">
                    ({{ $counts['logistics'] ?? 0 }})
                </span>
            </a>


            {{-- Riders --}}
            <a
                href="{{ route('admin.users', ['filter' => 'riders', 'sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'riders'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Riders

                <span class="ml-1 opacity-70">
                    ({{ $counts['riders'] ?? 0 }})
                </span>
            </a>


            {{-- Suspended --}}
            <a
                href="{{ route('admin.users', ['filter' => 'suspended', 'sort' => $sort]) }}"
                class="px-4 py-2
                       rounded-xl
                       text-xs sm:text-sm
                       font-semibold
                       transition
                       {{
                           $filter === 'suspended'
                               ? 'bg-navy text-white'
                               : 'text-slate-500 hover:bg-slate-100'
                       }}"
            >
                Suspended

                <span class="ml-1 opacity-70">
                    ({{ $counts['suspended'] ?? 0 }})
                </span>
            </a>

        </div>


        {{-- SORT + SEARCH --}}
        <div class="flex items-center gap-2 w-full xl:w-auto">

            {{-- SORT --}}
            <form method="GET" action="{{ route('admin.users') }}" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="text-sm
                           rounded-xl
                           border border-slate-200
                           px-3 py-2.5
                           bg-white
                           text-slate-600
                           font-medium
                           focus:outline-none
                           focus:ring-2
                           focus:ring-mint/20
                           transition"
                >
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Name (Z–A)</option>
                </select>
            </form>


            {{-- SEARCH --}}
            <form
                method="GET"
                action="{{ route('admin.users') }}"
                class="relative w-full xl:w-72"
            >

                <input
                    type="hidden"
                    name="filter"
                    value="{{ $filter }}"
                >

                <input
                    type="hidden"
                    name="sort"
                    value="{{ $sort }}"
                >


                <x-lucide-search
                    class="w-4 h-4
                           absolute left-3 top-1/2
                           -translate-y-1/2
                           text-slate-400"
                />


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                    class="w-full
                           pl-9 pr-4
                           py-2.5
                           text-sm
                           rounded-xl
                           bg-white
                           border border-slate-200
                           text-navy
                           placeholder:text-slate-400
                           focus:border-mint
                           focus:outline-none
                           focus:ring-2
                           focus:ring-mint/20
                           transition"
                >

            </form>

        </div>

    </div>


    {{-- =========================================================
        USER ACCOUNTS TABLE
    ========================================================= --}}
    <div
        class="bg-white
               rounded-2xl
               border border-slate-200
               overflow-hidden"
    >

        {{-- TABLE HEADER / INFO --}}
        <div
            class="flex items-center justify-between
                   px-5 py-4
                   border-b border-slate-100"
        >

            <div>
                <h2 class="font-semibold text-navy text-sm">
                    User Directory
                </h2>

                <p class="text-[11px] text-slate-400 mt-0.5">
                    Approved platform accounts and their current status.
                </p>
            </div>

            <span class="text-xs text-slate-400">
                {{ $users->total() }} total
            </span>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="bg-slate-50 border-b border-slate-100">

                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            User
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Role
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Status
                        </th>


                        <th
                            class="text-left
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide"
                        >
                            Joined
                        </th>


                        <th
                            class="text-right
                                   font-semibold
                                   text-slate-500
                                   px-5 py-3
                                   text-xs
                                   uppercase
                                   tracking-wide
                                   w-[196px]"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($users as $user)

                        @php
                            // Ginagamit na yung $roleStyles / $statusStyles na na-define
                            // sa itaas (bago yung table), para iisa lang ang source of
                            // truth ng badge colors — parehas gamit ng row at ng modal.
                            $roleStyle = $roleStyles[$user->account_type]
                                ?? ['class' => 'text-slate-500 bg-slate-100', 'icon' => 'user', 'label' => ucfirst($user->account_type)];

                            $statusStyle = $statusStyles[$user->status]
                                ?? ['dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'bg' => 'bg-slate-100', 'label' => ucfirst($user->status)];
                        @endphp


                        <tr class="hover:bg-slate-50/60 transition">

                            {{-- USER --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-10 h-10
                                               rounded-full
                                               bg-mint/15
                                               flex items-center justify-center
                                               shrink-0"
                                    >
                                        <span class="text-xs font-bold text-mint-dark">
                                            {{ $user->initials }}
                                        </span>
                                    </div>


                                    <div class="min-w-0">

                                        <p
                                            class="font-semibold
                                                   text-navy
                                                   truncate"
                                        >
                                            {{ $user->display_name }}
                                        </p>


                                        <p
                                            class="text-xs
                                                   text-slate-500
                                                   truncate
                                                   mt-0.5"
                                        >
                                            {{ $user->email }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- ROLE --}}
                            <td class="px-5 py-4">

                                <span
                                    class="inline-flex
                                           items-center gap-1.5
                                           text-xs
                                           font-semibold
                                           px-2.5 py-1
                                           rounded-full
                                           {{ $roleStyle['class'] }}"
                                >

                                    <x-dynamic-component
                                        :component="'lucide-' . $roleStyle['icon']"
                                        class="w-3.5 h-3.5"
                                    />

                                    {{ ucfirst($user->account_type) }}

                                </span>

                            </td>


                            {{-- STATUS --}}
                            <td class="px-5 py-4">

                                <span
                                    class="inline-flex
                                           items-center gap-1.5
                                           text-xs font-semibold
                                           {{ $statusStyle['text'] }}"
                                >

                                    <span
                                        class="w-1.5 h-1.5
                                               rounded-full
                                               {{ $statusStyle['dot'] }}"
                                    ></span>

                                    {{ $statusStyle['label'] }}

                                </span>

                            </td>


                            {{-- JOINED --}}
                            <td class="px-5 py-4">

                                <div>

                                    <p class="text-sm text-slate-600">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </p>

                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>

                                </div>

                            </td>


                            {{-- ACTIONS
                                Fixed-width slots (not just `justify-end` on a
                                loose flex row) so the View button lands in the
                                exact same column on every row — before, rows
                                with only one button (disabled accounts) shifted
                                "View" further right than rows with two buttons,
                                so the column looked crooked going down the table.
                            --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center justify-end gap-2 w-[196px] ml-auto">

                                    {{-- Slot 1: Suspend / Reactivate (or empty spacer) --}}
                                    <div class="w-[112px] flex justify-end">

                                        @if ($user->status === 'suspended')

                                            <button
                                                type="button"
                                                class="reactivate-btn
                                                       action-btn
                                                       h-8 w-full
                                                       inline-flex items-center justify-center gap-1.5
                                                       px-3
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-white
                                                       bg-mint-dark
                                                       hover:opacity-90
                                                       transition"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->display_name }}"
                                            >
                                                <x-lucide-rotate-ccw class="w-3.5 h-3.5" />
                                                Reactivate
                                            </button>

                                        @elseif (
                                            $user->status === 'approved'
                                            || $user->status === 'active'
                                        )

                                            <button
                                                type="button"
                                                class="suspend-btn
                                                       action-btn
                                                       h-8 w-full
                                                       inline-flex items-center justify-center gap-1.5
                                                       px-3
                                                       rounded-lg
                                                       text-xs font-semibold
                                                       text-coral
                                                       border border-coral/30
                                                       hover:bg-coral/5
                                                       transition"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->display_name }}"
                                            >
                                                <x-lucide-ban class="w-3.5 h-3.5" />
                                                Suspend
                                            </button>

                                        @endif

                                    </div>


                                    {{-- Slot 2: View — always in the same spot --}}
                                    <div class="w-[76px] flex justify-end">

                                        <button
                                            type="button"
                                            class="user-view-btn
                                                   action-btn
                                                   h-8 w-full
                                                   inline-flex items-center justify-center gap-1.5
                                                   px-3
                                                   rounded-lg
                                                   text-xs font-semibold
                                                   text-slate-500
                                                   border border-slate-200
                                                   hover:bg-slate-50
                                                   hover:text-navy
                                                   transition"
                                            data-user-id="{{ $user->id }}"
                                        >
                                            <x-lucide-eye class="w-3.5 h-3.5" />
                                            View
                                        </button>

                                    </div>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-5 py-14 text-center"
                            >

                                <div
                                    class="w-12 h-12
                                           mx-auto
                                           rounded-2xl
                                           bg-slate-100
                                           text-slate-400
                                           flex items-center justify-center"
                                >
                                    <x-lucide-users-round class="w-5 h-5" />
                                </div>


                                <p
                                    class="text-sm
                                           font-semibold
                                           text-navy
                                           mt-3"
                                >
                                    No accounts found
                                </p>


                                <p
                                    class="text-xs
                                           text-slate-400
                                           mt-1"
                                >
                                    Try changing the filter or search keyword.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================================================
            PAGINATION
        ========================================================= --}}
        @if ($users->hasPages())

            <div
                class="px-5 py-4
                       border-t border-slate-100"
            >
                {{ $users->withQueryString()->links() }}
            </div>

        @endif

    </div>


    {{-- =========================================================
        USER DETAILS MODAL
        (dating separate "view" page, ginawa nang modal para hindi
        na lumipat ng page — data pinopopulate via JS mula sa
        hardcoded $usersForJs sa taas)

        Header chrome (floating close circle + thin accent bar)
        follows the buyer-registration-modal pattern — just the
        chrome, not the split-screen layout, since this modal
        doesn't need a left artistic panel.
    ========================================================= --}}
    <div
        id="userModalOverlay"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="userModalPanel"
            class="relative w-full max-w-3xl
                   max-h-[90vh]
                   overflow-y-auto
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150"
        >

            {{-- Accent bar — matches the modal chrome used elsewhere --}}
            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            {{-- Close — floating circle button, same shape/spot as the
                 registration modals --}}
            <button
                type="button"
                id="userModalClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20
                       w-10 h-10
                       rounded-full
                       bg-slate-100
                       text-navy/45
                       flex items-center justify-center
                       hover:bg-mint/10
                       hover:text-mint-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-mint/15
                       transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-12 h-12 rounded-2xl bg-mint/15 flex items-center justify-center shrink-0">
                        <span id="modalInitials" class="text-sm font-bold text-mint-dark"></span>
                    </div>

                    <div class="min-w-0">

                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">
                            ACCOUNT DETAILS
                        </p>

                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="modalName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>

                            <span
                                id="modalVerifiedBadge"
                                class="hidden inline-flex items-center gap-1 text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full shrink-0"
                            >
                                <x-lucide-badge-check class="w-3 h-3" />
                                Verified
                            </span>
                        </div>

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


            {{-- BODY — 2 columns sa desktop (stack sa mobile) para kasya
                 lahat ng details nang hindi na kailangang mag-scroll pa --}}
            <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- LEFT COLUMN --}}
                <div class="space-y-4">

                    {{-- Contact info --}}
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

                    {{-- Registration details — Sex/Birthday/Age base sa
                         registration form fields ng lahat ng roles, plus
                         role-specific rows (Business info para sa seller,
                         Vehicle info para sa logistics/rider) na naka-hide
                         by default, ipapakita na lang ng JS kung applicable --}}
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

                    {{-- Account info --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Account Information</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Account ID</dt>
                                <dd id="modalAccountNo" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Joined</dt>
                                <dd id="modalJoined" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Last Login</dt>
                                <dd id="modalLastLogin" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Admin Notes</p>
                        <p id="modalNotes" class="text-xs text-slate-600 leading-relaxed"></p>
                    </div>

                    {{-- Reports & Flags — dahilan kung bakit may current
                         status ang account (suspend/disable reasons, complaints,
                         atbp). Naka-hide kapag walang laman ang array. --}}
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

                    {{-- Role stats — compact horizontal card na lang, hindi na
                         malaking centered block, para makatipid ng space --}}
                    <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div>
                            <p id="modalStatsLabel" class="text-xs text-slate-500"></p>
                            <p id="modalStatsValue" class="text-xl font-bold text-navy mt-0.5"></p>
                        </div>
                        <p id="modalStatsSub" class="text-[11px] text-slate-400 text-right max-w-[50%]"></p>
                    </div>

                    {{-- Submitted documents — placeholder previews lang, wala pang
                         actual file/image (hardcoded text details muna hanggang
                         hindi pa naka-connect sa DB / file storage). Bawat card
                         ay isang link na nagbubukas ng buong document sa bagong
                         tab (target="_blank") sa JS. --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Submitted Documents</p>
                        <div id="modalDocuments" class="grid grid-cols-3 gap-3"></div>
                    </div>

                    {{-- Recent activity — hardcoded timeline lang para sa
                         UI preview, wala pang totoong audit-log data --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Recent Activity</p>
                        <ul id="modalActivity" class="space-y-3"></ul>
                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end">

                <button
                    type="button"
                    id="modalActionBtn"
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold transition-all duration-300"
                ></button>

            </div>

        </div>

    </div>


    {{-- =========================================================
        SUSPEND / REACTIVATE — CONFIRMATION MODAL
        Isa lang itong modal, ginagamit kahit saan pa galing ang
        click (row action o details modal) — depende na lang sa
        action/id/name na ipinapasa sa openConfirmModal().
    ========================================================= --}}
    <div
        id="confirmModalOverlay"
        class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="confirmModalPanel"
            class="w-full max-w-sm
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150
                   p-6"
        >

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-6"></p>

            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    id="confirmCancelBtn"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmProceedBtn"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white transition-all duration-300"
                ></button>
            </div>

        </div>

    </div>


    {{-- Shared hidden form — pinapalitan lang ng JS yung `action` bago i-submit --}}
    <form id="actionForm" method="POST" action="">
        @csrf
    </form>


    {{-- =========================================================
        MODAL DATA + BEHAVIOR
        TODO: pag naka-DB na, puwede nang gawing route-based fetch
        (fetch() → admin.users.show as JSON) sa halip na hardcoded
        array — yung open/close/populate logic mananatili pareho.
    ========================================================= --}}
    <script>
        const usersData = @json($usersForJs);

        const roleBadgeClasses = {
            buyer:     'text-sky bg-sky/10',
            seller:    'text-coral bg-coral/10',
            logistics: 'text-violet-600 bg-violet-100',
            rider:     'text-amber-600 bg-amber-100',
        };
        const roleLabels = { buyer: 'Buyer', seller: 'Seller', logistics: 'Logistics', rider: 'Rider' };

        const statusBadge = {
            approved:  { dot: 'bg-mint-dark', text: 'text-mint-dark', bg: 'bg-mint/10',  label: 'Active' },
            active:    { dot: 'bg-mint-dark', text: 'text-mint-dark', bg: 'bg-mint/10',  label: 'Active' },
            suspended: { dot: 'bg-coral',     text: 'text-coral',     bg: 'bg-coral/10', label: 'Suspended' },
            disabled:  { dot: 'bg-slate-400', text: 'text-slate-500', bg: 'bg-slate-100', label: 'Disabled' },
        };

        // Route templates — '0' ang placeholder na papalitan natin ng tunay na id
        const suspendUrlTemplate    = "{{ route('admin.users.suspend', 0) }}";
        const reactivateUrlTemplate = "{{ route('admin.users.reactivate', 0) }}";

        function buildActionUrl(action, id) {
            const tpl = action === 'suspend' ? suspendUrlTemplate : reactivateUrlTemplate;
            return tpl.replace('/0', '/' + id);
        }

        // Generic file icon lang — ginagamit sa placeholder document cards
        // (wala pang actual file/image, preview-only text details muna)
        const docFileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

        /* -----------------------------------------------------------
           USER DETAILS MODAL
        ----------------------------------------------------------- */
        const overlay  = document.getElementById('userModalOverlay');
        const panel    = document.getElementById('userModalPanel');
        const closeBtn = document.getElementById('userModalClose');
        const modalActionBtn = document.getElementById('modalActionBtn');

        let currentModalUser = null;

        function openUserModal(id) {
            const user = usersData.find(u => u.id === id);
            if (!user) return;

            currentModalUser = user;

            document.getElementById('modalInitials').textContent = user.initials;
            document.getElementById('modalName').textContent = user.display_name;
            document.getElementById('modalEmail').textContent = user.email;
            document.getElementById('modalVerifiedBadge').classList.toggle('hidden', !user.verified);

            const roleBadge = document.getElementById('modalRoleBadge');
            roleBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + (roleBadgeClasses[user.account_type] || 'text-slate-500 bg-slate-100');
            roleBadge.textContent = roleLabels[user.account_type] || user.account_type;

            const sBadge = statusBadge[user.status] || { dot: 'bg-slate-400', text: 'text-slate-500', bg: 'bg-slate-100', label: user.status };
            document.getElementById('modalStatusBadge').className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            document.getElementById('modalStatusDot').className = 'w-1.5 h-1.5 rounded-full ' + sBadge.dot;
            document.getElementById('modalStatusLabel').textContent = sBadge.label;

            document.getElementById('modalPhone').textContent = user.phone;
            document.getElementById('modalAddress').textContent = user.address;
            document.getElementById('modalAccountNo').textContent = user.account_no;
            document.getElementById('modalJoined').textContent = user.joined;
            document.getElementById('modalLastLogin').textContent = user.last_login;

            document.getElementById('modalStatsValue').textContent = user.stats.value;
            document.getElementById('modalStatsLabel').textContent = user.stats.label;
            document.getElementById('modalStatsSub').textContent = user.stats.sub;

            document.getElementById('modalNotes').textContent = user.notes;

            // Registration details — Sex/Birthday/Age lagi, tapos role-specific
            // rows (Business info / Vehicle info) na lang ang naka-toggle
            document.getElementById('modalSex').textContent = user.sex;
            document.getElementById('modalBirthday').textContent = user.birthday;
            document.getElementById('modalAge').textContent = user.age;

            const businessNameRow = document.getElementById('modalBusinessNameRow');
            const businessCategoryRow = document.getElementById('modalBusinessCategoryRow');
            const vehicleRow = document.getElementById('modalVehicleRow');
            const plateRow = document.getElementById('modalPlateRow');

            if (user.business_name) {
                businessNameRow.className = 'flex justify-between gap-3';
                document.getElementById('modalBusinessName').textContent = user.business_name;
            } else {
                businessNameRow.className = 'hidden justify-between gap-3';
            }

            if (user.business_category) {
                businessCategoryRow.className = 'flex justify-between gap-3';
                document.getElementById('modalBusinessCategory').textContent = user.business_category;
            } else {
                businessCategoryRow.className = 'hidden justify-between gap-3';
            }

            if (user.vehicle_type) {
                vehicleRow.className = 'flex justify-between gap-3';
                document.getElementById('modalVehicle').textContent = user.vehicle_type;
            } else {
                vehicleRow.className = 'hidden justify-between gap-3';
            }

            if (user.plate_number) {
                plateRow.className = 'flex justify-between gap-3';
                document.getElementById('modalPlate').textContent = user.plate_number;
            } else {
                plateRow.className = 'hidden justify-between gap-3';
            }

            // Recent activity — hardcoded timeline lang (preview data)
            const activityList = document.getElementById('modalActivity');
            activityList.innerHTML = '';
            (user.activity || []).forEach(item => {
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

            // Reports & Flags — dahilan kung bakit may current status ang
            // account (late deliveries, complaints, unverified email, atbp)
            const reportsWrap = document.getElementById('modalReports');
            reportsWrap.innerHTML = '';
            if (!user.reports || user.reports.length === 0) {
                reportsWrap.innerHTML = '<p class="text-xs text-slate-400">No reports or flags on file.</p>';
            } else {
                user.reports.forEach(r => {
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

            // Submitted documents — placeholder preview cards lang (walang
            // actual file/image na ipapakita, text label lang muna hanggang
            // hindi pa naka-connect sa DB / file storage). Bawat card ay
            // <a target="_blank"> para magbukas ng bagong tab sa pag-click.
            const docsGrid = document.getElementById('modalDocuments');
            docsGrid.innerHTML = '';
            (user.documents || []).forEach(doc => {
                const card = document.createElement('a');
                card.href = doc.url || '#';
                card.target = '_blank';
                card.rel = 'noopener noreferrer';
                card.className = 'block group';
                card.innerHTML = `
                    <div class="w-full aspect-square rounded-lg border border-dashed border-slate-300 bg-slate-50/60 flex flex-col items-center justify-center gap-1 text-slate-400 cursor-pointer transition group-hover:border-mint group-hover:bg-mint/5 group-hover:text-mint-dark">
                        ${docFileIconSvg}
                        <span class="text-[9px] font-medium uppercase tracking-wide">Preview</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 text-center truncate transition group-hover:text-mint-dark">${doc.label}</p>
                `;
                docsGrid.appendChild(card);
            });

            // Suspend / Reactivate button, depende sa current status
            if (user.status === 'suspended') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg> Reactivate Account';
                modalActionBtn.style.display = '';
            } else if (user.status === 'approved' || user.status === 'active') {
                modalActionBtn.className = 'h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 hover:-translate-y-0.5 transition-all duration-300';
                modalActionBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg> Suspend Account';
                modalActionBtn.style.display = '';
            } else {
                modalActionBtn.style.display = 'none';
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeUserModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.user-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openUserModal(parseInt(btn.dataset.userId, 10)));
        });

        closeBtn.addEventListener('click', closeUserModal);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeUserModal();
        });

        modalActionBtn.addEventListener('click', () => {
            if (!currentModalUser) return;
            const action = currentModalUser.status === 'suspended' ? 'reactivate' : 'suspend';
            openConfirmModal(action, currentModalUser.id, currentModalUser.display_name);
        });

        /* -----------------------------------------------------------
           SUSPEND / REACTIVATE — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay     = document.getElementById('confirmModalOverlay');
        const confirmPanel       = document.getElementById('confirmModalPanel');
        const confirmIconWrap    = document.getElementById('confirmIconWrap');
        const confirmTitle       = document.getElementById('confirmTitle');
        const confirmMessage     = document.getElementById('confirmMessage');
        const confirmProceedBtn  = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn   = document.getElementById('confirmCancelBtn');
        const actionForm         = document.getElementById('actionForm');

        const banIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>';
        const rotateIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>';

        function openConfirmModal(action, id, name) {
            actionForm.action = buildActionUrl(action, id);

            if (action === 'suspend') {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/10 text-coral';
                confirmIconWrap.innerHTML = banIconSvg;
                confirmTitle.textContent = 'Suspend this account?';
                confirmMessage.textContent = `${name} won't be able to log in or use the platform until you reactivate the account.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Suspend Account';
            } else {
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/10 text-mint-dark';
                confirmIconWrap.innerHTML = rotateIconSvg;
                confirmTitle.textContent = 'Reactivate this account?';
                confirmMessage.textContent = `${name} will regain full access to their account right away.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Reactivate Account';
            }

            confirmOverlay.classList.remove('hidden');
            confirmOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                confirmPanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeConfirmModal() {
            confirmPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                confirmOverlay.classList.add('hidden');
                confirmOverlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.suspend-btn, .reactivate-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id     = parseInt(btn.dataset.userId, 10);
                const name   = btn.dataset.userName;
                const action = btn.classList.contains('suspend-btn') ? 'suspend' : 'reactivate';
                openConfirmModal(action, id, name);
            });
        });

        confirmCancelBtn.addEventListener('click', closeConfirmModal);
        confirmOverlay.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) closeConfirmModal();
        });

        confirmProceedBtn.addEventListener('click', () => {
            // Totoong POST papunta sa suspend/reactivate route — pagbalik
            // lang ng page (redirect back with session status) ang mangyayari.
            actionForm.submit();
        });

        // Escape key — isara alinman sa dalawang modal na nakabukas
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeUserModal();
        });
    </script>

@endsection