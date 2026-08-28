<!DOCTYPE html>
{{-- Path: resources/views/layouts/app.blade.php --}}

<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- Matches navy brand color in mobile browser chrome / PWA task switcher --}}
    <meta name="theme-color" content="#0B1B33">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>
        @yield('title', 'ShopHop — Hop In. Shop More.')
    </title>


    {{-- ========================================
        GOOGLE FONTS
        ShopHop uses Poppins throughout
    ========================================= --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- ========================================
        VITE
    ========================================= --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


    {{-- Page-specific styles --}}
    @stack('styles')

</head>


<body class="min-h-screen bg-white text-navy font-poppins antialiased selection:bg-teal/20 selection:text-navy">


    {{-- ========================================
        NAVBAR
        Skipped when the page sets @section('hideChrome', true)
        e.g. the registration page.
    ========================================= --}}

    @unless (View::hasSection('hideChrome'))
        @include('partials.navbar')
    @endunless


    {{-- ========================================
        MAIN CONTENT
    ========================================= --}}

    <main class="min-h-[60vh]">
        @yield('content')
    </main>


    {{-- ========================================
        FOOTER
        Skipped when the page sets @section('hideChrome', true)
        e.g. the registration page.
    ========================================= --}}

    @unless (View::hasSection('hideChrome'))
        @include('partials.footer')
    @endunless


    {{-- ========================================
        AUTH MODALS
        Available site-wide (login, account type,
        buyer/seller/logistics registration). Each
        modal starts hidden and is only shown when
        opened via its click trigger or a
        `shophop:open-*-modal` custom event, so it's
        safe to always include them here.
    ========================================= --}}

    @include('auth.modals.login-modal')
    @include('auth.modals.account-type-modal')
    @include('auth.modals.buyer-registration-modal')
    @include('auth.modals.seller-registration-modal')


    {{-- ========================================
        PAGE-SPECIFIC SCRIPTS
    ========================================= --}}

    @stack('scripts')


</body>

</html>