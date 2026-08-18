<!DOCTYPE html>
{{-- Path in your project: resources/views/layouts/app.blade.php --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ShopHop — Hop In. Shop More.')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans text-navy bg-white">

    {{-- Reusable navbar partial --}}
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    {{-- Reusable footer partial --}}
    @include('partials.footer')

    @stack('scripts')
</body>
</html>