<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ShopHop Logistics — partner registration, delivery operations, and sorting center management.">
    <title>@yield('title', 'ShopHop Logistics — Deliver with us')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Shared public Logistics theme. No Vite rebuild needed for this stylesheet. */
        :root { --lp-ink:#0F1B3D; --lp-teal:#21C3A6; --lp-forest:#18A98E; --lp-mint:#D4F5EE; --lp-sand:#F3F5F7; }
        *,*::before,*::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body.lp-site { margin:0; color:var(--lp-ink); background:#fff; font-family:'Poppins',sans-serif; font-size:13px; }
        .lp-wrap { width:min(calc(100% - clamp(32px,7.5vw,144px)),1500px); margin-inline:auto; }
        .lp-header { position:sticky; top:0; z-index:70; background:rgba(255,255,255,.97); border-bottom:1px solid #E2E6EA; backdrop-filter:blur(14px); }
        .lp-header-inner { min-height:60px; display:flex; align-items:center; justify-content:space-between; gap:18px; }
        .lp-brand { display:flex; align-items:center; gap:8px; color:var(--lp-ink); text-decoration:none; font-weight:800; letter-spacing:-.025em; font-size:15px; line-height:1.04; }
        .lp-brand img { width:36px; height:36px; object-fit:contain; flex:none; }
        .lp-brand small { display:block; font-size:7px; color:var(--lp-forest); letter-spacing:.16em; margin-top:4px; font-weight:700; }
        .lp-header-links { display:flex; align-items:center; flex-wrap:wrap; justify-content:flex-end; gap:8px 22px; }
        .lp-header-links a { font-size:11px; font-weight:600; color:var(--lp-ink); text-decoration:none; }
        .lp-header-links a:hover,.lp-footer a:hover { color:var(--lp-forest); }
        .lp-button { display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:10px 18px;border:1px solid transparent;border-radius:12px;text-decoration:none;text-align:center;font-family:inherit;font-size:12px;font-weight:700;line-height:1.3;transition:background .15s,transform .15s,box-shadow .15s; cursor:pointer; }
        .lp-button:hover { transform:translateY(-1px); }
        .lp-button-primary { color:#fff!important;background:#21C3A6;box-shadow:0 7px 18px #21C3A625; }
        .lp-button-primary:hover { background:#18A98E; }
        .lp-button-light { color:#0F1B3D!important;background:#fff; }
        .lp-button-outline { background:transparent;color:#fff!important;border-color:#ffffff80; }
        .lp-button-ink { background:var(--lp-ink);color:#fff!important; }
        .lp-button-pill { border-radius:999px; }
        .lp-footer { background:#0F1B3D;color:#d9e8ef; padding:27px 0; margin-top:auto; }
        .lp-footer-inner {display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;font-size:11px;line-height:1.7;}
        .lp-footer a {color:#d9e8ef;text-decoration:none;}
        .lp-footer-nav {display:flex;gap:20px;flex-wrap:wrap;}
        .lp-site-main {flex:1; min-width:0;}
        .lp-form-control {width:100%;min-height:41px;border:1px solid #E2E6EA;border-radius:12px;padding:10px 13px;font:inherit;font-size:12px;background:#fff;color:#0F1B3D;outline:none;transition:border-color .15s,box-shadow .15s;}
        .lp-form-control:focus {border-color:#21C3A6;box-shadow:0 0 0 3px #21C3A621;}
        .lp-form-label {display:block;font-size:11px;font-weight:600;color:var(--lp-ink);margin:0 0 7px;}
        @media (max-width:760px) { .lp-header-inner{min-height:60px;}.lp-header-links{gap:10px;}.lp-header-links .lp-optional{display:none;}.lp-header-links .lp-button{min-height:36px;padding:9px 13px;font-size:11px;}.lp-brand{font-size:14px;}.lp-brand img{width:32px;height:32px;} }
        @media (max-width:390px) {.lp-header-links .lp-marketplace{display:none;}.lp-header-links{gap:7px;}}
    </style>
    @stack('styles')
</head>
<body class="lp-site" style="min-height:100vh;display:flex;flex-direction:column">
    @include('logistics.partials.public-header')
    <main class="lp-site-main" id="main-content">@yield('content')</main>
    @include('logistics.partials.public-footer')
    @stack('scripts')
</body>
</html>
