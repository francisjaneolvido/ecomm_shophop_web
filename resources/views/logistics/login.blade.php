<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logistics Partner Sign In | ShopHop</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center;
               font-family: system-ui, sans-serif; background: #f3f4f6; }
        .card { width: 100%; max-width: 400px; background: #fff; padding: 32px;
                border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .card img { height: 40px; margin-bottom: 8px; }
        h1 { font-size: 20px; margin: 0 0 20px; }
        label { display: block; font-size: 14px; margin: 12px 0 4px; }
        input[type=email], input[type=password] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        button { width: 100%; margin-top: 20px; padding: 11px; border: 0; border-radius: 8px;
                 background: #f97316; color: #fff; font-weight: 600; cursor: pointer; }
        .notice { padding: 10px 12px; border-radius: 8px; font-size: 14px; margin-bottom: 14px; }
        .warning { background: #fef3c7; color: #92400e; }
        .error { background: #fee2e2; color: #991b1b; }
        .links { margin-top: 16px; font-size: 14px; text-align: center; }
        .links a { color: #f97316; }
    </style>
</head>
<body>
<div class="card">
    <img src="{{ asset('images/logo.png') }}" alt="ShopHop">
    <h1>Logistics Partner Sign In</h1>

    @if (session('login_notice'))
        @php($n = session('login_notice'))
        <div class="notice {{ $n['type'] }}">
            <strong>{{ $n['title'] }}</strong><br>{{ $n['message'] }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('logistics.login.submit') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label style="display:flex;gap:6px;align-items:center;">
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>

        <button type="submit">Sign In</button>
    </form>

    <div class="links">
        Wala pang account? <a href="{{ config('app.url') }}">Mag-apply sa main site</a>
    </div>
</div>
</body>
</html>