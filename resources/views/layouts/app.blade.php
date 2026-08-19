<!DOCTYPE html>
{{-- Path: resources/views/layouts/app.blade.php --}}

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

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


<body class="text-navy bg-white">


    {{-- ========================================
        NAVBAR
    ========================================= --}}

    @include('partials.navbar')


    {{-- ========================================
        MAIN CONTENT
    ========================================= --}}

    <main>
        @yield('content')
    </main>


    {{-- ========================================
        FOOTER
    ========================================= --}}

    @include('partials.footer')


    {{-- ========================================
        PAGE-SPECIFIC SCRIPTS
    ========================================= --}}

    @stack('scripts')


</body>

</html>