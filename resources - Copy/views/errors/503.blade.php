<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Under Maintenance</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f8fafc; color: #0f172a; }
        .box { max-width: 28rem; padding: 2rem; text-align: center; }
        .code { font-size: .75rem; font-weight: 700; letter-spacing: .1em; color: #94a3b8; }
        h1 { font-size: 1.5rem; margin: .5rem 0; }
        p { color: #475569; line-height: 1.6; margin: .5rem 0 0; }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">503</div>
        <h1>We'll be right back</h1>
        <p>{{ $exception->getMessage() ?: "We're under maintenance. Please check back soon." }}</p>
    </div>
</body>
</html>