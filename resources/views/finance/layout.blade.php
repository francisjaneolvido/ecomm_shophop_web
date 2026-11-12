<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · ShopHop</title>
    @vite(['resources/css/app.css'])
@include('partials.theme-head')
@include('partials.brand-assets')
</head>
<body style="background:#FBF6EE;margin:0;padding:24px">
    <main>@yield('content')</main>
</body>
</html>
