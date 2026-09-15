@extends('layouts.app')

@section('title', 'Local Account Previews - ShopHop')
@section('hideChrome', true)

@section('content')
<div class="min-h-screen bg-gray-bg px-4 py-8 sm:px-6 sm:py-12">
    <div class="mx-auto max-w-5xl">
        <header class="flex flex-col gap-5 border-b border-gray-border pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-teal-dark">Local development only</p>
                <h1 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">TEST MODE</h1>
                <p class="mt-1 text-sm font-semibold text-teal-dark">ShopHop role previews</p>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-navy/60">
                    These links render the repository's real Blade interfaces with their existing demo data. They do not authenticate an account or bypass production middleware.
                </p>
            </div>
            @include('partials.theme-toggle')
        </header>

        @php
            $accounts = [
                [
                    'role' => 'Buyer',
                    'email' => 'buyer@shophop.test',
                    'password' => 'Buyer1234',
                    'primary' => ['label' => 'Open Buyer Messages', 'route' => 'dev.preview.buyer.messages'],
                    'secondary' => ['label' => 'Storefront', 'route' => 'dev.preview.buyer.storefront'],
                ],
                [
                    'role' => 'Seller',
                    'email' => 'seller@shophop.test',
                    'password' => 'Seller1234',
                    'primary' => ['label' => 'Open Seller Dashboard', 'route' => 'dev.preview.seller.dashboard'],
                    'secondary' => ['label' => 'Messages', 'route' => 'dev.preview.seller.chat'],
                ],
                [
                    'role' => 'Admin',
                    'email' => 'admin@shophop.com',
                    'password' => 'Admin1234!',
                    'primary' => ['label' => 'Open Admin Chat', 'route' => 'dev.preview.admin.chat'],
                    'secondary' => ['label' => 'Dashboard', 'route' => 'dev.preview.admin.dashboard'],
                ],
                [
                    'role' => 'Logistics',
                    'email' => 'local preview',
                    'password' => 'guest-rendered',
                    'primary' => ['label' => 'Open Logistics Dashboard', 'route' => 'dev.preview.logistics.dashboard'],
                    'secondary' => ['label' => 'Use switcher', 'route' => 'dev.preview.logistics.dashboard'],
                ],
            ];
        @endphp

        <div class="grid gap-4 py-6 md:grid-cols-4">
            @foreach ($accounts as $account)
                <article class="flex flex-col border-t-4 border-teal bg-white p-5 shadow-soft">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-teal-dark">{{ $account['role'] }}</p>
                    <p class="mt-4 text-sm font-semibold text-navy">{{ $account['email'] }}</p>
                    <p class="mt-1 font-mono text-xs text-navy/45">{{ $account['password'] }}</p>
                    <p class="mt-4 text-xs leading-5 text-navy/55">TEST MODE only. Preview pages are guest-rendered and never represent authenticated production state.</p>

                    <div class="mt-6 flex flex-col gap-2">
                        <a href="{{ route($account['primary']['route']) }}" class="inline-flex min-h-11 items-center justify-center bg-teal px-4 py-2 text-xs font-bold text-white transition hover:bg-teal-dark">
                            {{ $account['primary']['label'] }}
                        </a>
                        <a href="{{ route($account['secondary']['route']) }}" class="inline-flex min-h-11 items-center justify-center border border-gray-border px-4 py-2 text-xs font-semibold text-navy transition hover:border-teal hover:text-teal-dark">
                            {{ $account['secondary']['label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
</div>
@endsection
