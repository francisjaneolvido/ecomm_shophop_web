<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rider sign in — ShopHop</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy">
<main class="mx-auto mt-16 max-w-md rounded-2xl bg-white p-8 shadow">
    <h1 class="text-2xl font-bold">Rider sign in</h1>
    <p class="mt-2 text-sm">Use credentials provisioned by your Logistics partner.</p>
    @if ($errors->any()) <p role="alert" class="mt-4 text-red-700">{{ $errors->first() }}</p> @endif
    {{-- The session guard resolves Rider identity; this form never accepts a Rider ID. --}}
    <form method="POST" action="{{ route('rider.login.store') }}" class="mt-6 space-y-4">@csrf
        <div><label for="email" class="block">Email</label><input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}" class="w-full rounded border p-2"></div>
        <div><label for="password" class="block">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded border p-2"></div>
        <button class="rounded bg-navy px-5 py-2 text-white">Sign in</button>
    </form>
</main></body></html>
