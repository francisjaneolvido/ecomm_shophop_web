<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - ShopHop Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0F2C3F',
                        'navy-light': '#173B52',
                        mint: '#2ECFA6',
                        'mint-dark': '#22B593',
                        sky: '#4AA8E0',
                        yellow: '#F7C948',
                        coral: '#FF9142',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-100">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-navy text-slate-300 flex flex-col shrink-0">

            <!-- Logo -->
            <div class="h-16 flex items-center gap-2.5 px-6 border-b border-white/10">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-mint to-mint-dark flex items-center justify-center shadow-lg shadow-mint/20">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 7h12l1 13a1 1 0 01-1 1H6a1 1 0 01-1-1L6 7z" fill="white" fill-opacity="0.95"/>
                        <path d="M9 7a3 3 0 016 0" stroke="white" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold leading-tight text-[15px]">Shop<span class="text-mint">Hop</span></p>
                    <p class="text-[9px] uppercase tracking-wider text-slate-400 font-medium">Admin Panel</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition
                   {{ request()->routeIs('admin.dashboard') ? 'bg-mint/15 text-mint' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.registrations') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.registrations') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v4m2-2h-4" />
                    </svg>
                    <span class="leading-tight">Account Registrations</span>
                </a>

                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.users') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />
                    </svg>
                    User Accounts
                </a>

                <a href="{{ route('admin.compliance') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.compliance') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="leading-tight">Seller Compliance</span>
                </a>

                <a href="{{ route('admin.disputes') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.disputes') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span class="leading-tight">Complaints &amp; Disputes</span>
                    <span class="ml-auto text-[10px] bg-coral text-white font-bold px-1.5 py-0.5 rounded-full">3</span>
                </a>

                <a href="{{ route('admin.commission') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.commission') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Commission (10%)
                </a>

                <a href="{{ route('admin.reports') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.reports') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                    </svg>
                    Reports
                </a>

                <a href="{{ route('admin.chat') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.chat') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Chat / Messaging
                </a>

                <a href="{{ route('admin.settings') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.settings') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="leading-tight">Platform Settings</span>
                </a>

                <a href="{{ route('admin.accounts') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition
                   {{ request()->routeIs('admin.accounts') ? 'bg-mint/15 text-mint font-semibold' : 'hover:bg-white/5 hover:text-white text-slate-300 font-medium' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="leading-tight">Account Management</span>
                </a>

            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-white/10">
                <a href="{{ route('admin.logout') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 hover:text-coral transition text-sm font-medium text-slate-300">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- MAIN COLUMN -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- TOPBAR -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0">

                <div class="relative w-80 max-w-full">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" placeholder="Search users, orders, reports..."
                        class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                </div>

                <div class="flex items-center gap-4">

                    <button class="relative p-2 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-coral rounded-full ring-2 ring-white"></span>
                    </button>

                    <div class="w-px h-6 bg-slate-200"></div>

                    <div class="flex items-center gap-3 cursor-pointer">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-mint to-sky flex items-center justify-center font-semibold text-sm text-navy">CJ</div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-navy leading-tight">Carl Jasper</p>
                            <p class="text-xs text-slate-500 leading-tight">admin@shophop.com</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>