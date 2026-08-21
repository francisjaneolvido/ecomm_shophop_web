@extends('admin.layout')

@section('title', 'Platform Settings')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Platform Settings</h1>
        <p class="text-sm text-slate-500 mt-1">I-configure ang mga pangkalahatang setting ng ShopHop platform.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============ SETTINGS NAV (side tabs) ============ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-3 h-fit">
            <a href="#general" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-mint/15 text-mint-dark font-semibold text-sm mb-1">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                General
            </a>
            <a href="#commission" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-slate-500 font-medium text-sm mb-1">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Commission Rate
            </a>
            <a href="#notifications" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-slate-500 font-medium text-sm mb-1">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifications
            </a>
            <a href="#security" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-slate-500 font-medium text-sm">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Security
            </a>
        </div>

        {{-- ============ SETTINGS FORM ============ --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- GENERAL --}}
            <div id="general" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-semibold text-navy text-sm mb-4">General Information</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Platform Name</label>
                        <input type="text" value="ShopHop"
                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Support Email</label>
                        <input type="email" value="admin@shophop.com"
                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Platform Description</label>
                        <textarea rows="3"
                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">Ang ShopHop ay isang online marketplace para sa mga lokal na negosyo sa Pilipinas.</textarea>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <div>
                            <p class="text-sm font-medium text-navy">Maintenance Mode</p>
                            <p class="text-xs text-slate-500">I-off ang platform para sa lahat maliban sa admin.</p>
                        </div>
                        <button type="button" class="w-11 h-6 rounded-full bg-slate-300 relative transition">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- COMMISSION --}}
            <div id="commission" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-semibold text-navy text-sm mb-4">Commission Rate</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Default Commission (%)</label>
                        <input type="number" value="10"
                            class="w-full sm:w-40 text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                        <p class="text-xs text-slate-400 mt-1">Ito ang porsyentong ikakaltas sa bawat successful na sale.</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Payout Schedule</label>
                        <select class="w-full sm:w-60 text-sm border border-slate-200 rounded-xl px-3 py-2.5 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                            <option>Weekly</option>
                            <option>Bi-weekly</option>
                            <option>Monthly</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- NOTIFICATIONS --}}
            <div id="notifications" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-semibold text-navy text-sm mb-4">Notification Preferences</h2>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <p class="text-sm text-navy">New seller registrations</p>
                        <button type="button" class="w-11 h-6 rounded-full bg-mint-dark relative transition">
                            <span class="absolute top-0.5 right-0.5 w-5 h-5 bg-white rounded-full shadow transition"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <p class="text-sm text-navy">New disputes filed</p>
                        <button type="button" class="w-11 h-6 rounded-full bg-mint-dark relative transition">
                            <span class="absolute top-0.5 right-0.5 w-5 h-5 bg-white rounded-full shadow transition"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <p class="text-sm text-navy">Weekly summary email</p>
                        <button type="button" class="w-11 h-6 rounded-full bg-slate-300 relative transition">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SAVE BAR --}}
            <div class="flex justify-end gap-3">
                <button class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                    Cancel
                </button>
                <button class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition">
                    Save Changes
                </button>
            </div>

        </div>

    </div>

@endsection