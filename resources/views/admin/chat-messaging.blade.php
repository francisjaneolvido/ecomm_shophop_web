@extends('admin.layout')

@section('title', 'Chat / Messaging')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Chat / Messaging</h1>
        <p class="text-sm text-slate-500 mt-1">Makipag-usap sa mga buyer at seller, o mag-broadcast ng announcement.</p>
    </div>

    {{-- ============ CHAT PANEL ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden" style="height: calc(100vh - 220px);">
        <div class="flex h-full">

            {{-- ===== CONVERSATION LIST ===== --}}
            <div class="w-full sm:w-80 border-r border-slate-100 flex flex-col shrink-0">

                <div class="p-4 border-b border-slate-100">
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" placeholder="Search conversations..."
                            class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    </div>
                </div>

                {{-- Palitan mo na lang ito ng @foreach loop galing sa $conversations mo sa controller --}}
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100">

                    {{-- ACTIVE CONVO --}}
                    <div class="flex items-start gap-3 px-4 py-3 bg-mint/5 border-l-2 border-mint-dark cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-sky">TH</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-navy truncate">TechHub PH</p>
                                <span class="text-[10px] text-slate-400 shrink-0">2m</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">Sir, kailan po ma-release yung payout namin?</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-mint/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-mint-dark">AN</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-navy truncate">Aling Nena's Store</p>
                                <span class="text-[10px] text-slate-400 shrink-0">1h</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">Salamat po sa pag-verify sa account namin!</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-slate-500">MR</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-navy truncate">Maria Reyes</p>
                                <span class="text-[10px] text-slate-400 shrink-0">3h</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">Paano po mag-file ng refund request?</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-coral/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-coral">JR</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-navy truncate">Jomar's Repair Shop</p>
                                <span class="text-[10px] text-slate-400 shrink-0">1d</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">Follow up po sa compliance documents.</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ===== MESSAGE THREAD ===== --}}
            <div class="hidden sm:flex flex-1 flex-col">

                <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                        <span class="text-xs font-bold text-sky">TH</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-navy">TechHub PH</p>
                        <p class="text-xs text-mint-dark">Online</p>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-slate-50/50">

                    <div class="flex justify-start">
                        <div class="max-w-md bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5">
                            <p class="text-sm text-navy">Magandang araw po! Sir, kailan po ma-release yung payout namin para sa nakaraang linggo?</p>
                            <p class="text-[10px] text-slate-400 mt-1">9:14 AM</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="max-w-md bg-mint-dark rounded-2xl rounded-tr-sm px-4 py-2.5">
                            <p class="text-sm text-white">Hi TechHub PH! Kinukumpirma ko lang po sa finance team, i-uupdate ko kayo within the day.</p>
                            <p class="text-[10px] text-mint/70 mt-1">9:20 AM</p>
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <div class="max-w-md bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5">
                            <p class="text-sm text-navy">Salamat po sa update, sir!</p>
                            <p class="text-[10px] text-slate-400 mt-1">9:21 AM</p>
                        </div>
                    </div>

                </div>

                <div class="p-4 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="Type a message..."
                            class="flex-1 px-4 py-2.5 text-sm rounded-xl bg-slate-100 border border-transparent focus:bg-white focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                        <button class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition shrink-0">
                            Send
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection