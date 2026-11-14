<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload too large | ShopHop</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f8fafc; color: #0f172a; }
        .box { max-width: 30rem; padding: 2rem; text-align: center; }
        .code { font-size: .75rem; font-weight: 700; letter-spacing: .1em; color: #64748b; }
        h1 { font-size: 1.5rem; margin: .5rem 0; }
        p { color: #475569; line-height: 1.6; margin: .75rem 0 0; }
        a { display: inline-block; margin-top: 1.5rem; padding: .75rem 1.25rem; border-radius: .75rem;
            background: #0d9488; color: white; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <main class="box">
        <div class="code">413 · CONTENT TOO LARGE</div>
        <h1>Your upload is too large</h1>
        @if (request()->is('seller/inventory'))
            <p>The server could not receive all the selected files. For product listings, use up to 8 photos (5 MB each) and one MP4 video (30 MB).</p>
        @else
            <p>The server could not receive all the selected files. Try a smaller upload.</p>
        @endif
        <p>If your files meet those limits, ask the site administrator to raise the server's total upload limit.</p>
        <a href="{{ request()->is('seller/inventory') ? url('/seller/inventory') : url('/') }}">{{ request()->is('seller/inventory') ? 'Back to inventory' : 'Back to home' }}</a>
    </main>
</body>
</html>
