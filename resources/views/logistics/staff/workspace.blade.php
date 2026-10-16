<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Assigned Branch — ShopHop Logistics</title>
<style>body{font-family:system-ui,-apple-system,sans-serif;background:#f3f7f8;color:#162c45;margin:0}header{background:#0f2942;color:white;padding:18px max(18px,calc((100vw - 980px)/2));display:flex;align-items:center;justify-content:space-between;gap:12px}main{max-width:980px;margin:24px auto;padding:0 16px}.card{background:white;border:1px solid #dce6e7;border-radius:18px;padding:22px;margin:14px 0;box-shadow:0 5px 24px #142c4410}h1{font-size:26px;margin:0 0 5px}h2{font-size:18px;margin:0 0 15px}.muted{color:#627787;font-size:14px}label{display:block;font-weight:600;margin:12px 0 6px;font-size:13px}input{display:block;padding:11px 12px;max-width:400px;width:95%;border:1px solid #c7d7db;border-radius:10px}button{background:#178c77;color:white;border:0;border-radius:10px;padding:11px 16px;cursor:pointer}header button{background:#ffffff26}table{width:100%;border-collapse:collapse;font-size:14px}td,th{text-align:left;padding:11px;border-bottom:1px solid #e3e9ed}.notice{background:#e2f7ef;padding:12px;border-radius:10px}.error{background:#fff1f0;padding:12px;border-radius:10px;color:#922}</style></head>
<body><header><strong>ShopHop · Assigned Branch Workspace</strong><form method="POST" action="{{ route('logistics.logout') }}">@csrf<button>Sign out</button></form></header>
<main><div class="card"><p class="muted">{{ $staff->partner->company_name }} · {{ ucfirst($staff->role) }}</p><h1>{{ $staff->sortingCenter->name }}</h1>
<p class="muted">{{ $staff->sortingCenter->code }} · {{ $staff->sortingCenter->addressLabel() }}</p><p>Logged in as <strong>{{ $staff->name }}</strong> ({{ $staff->email }}).</p>
<p class="muted">You are assigned only to this branch. Use the ShopHop Mobile scanner with these same credentials. Company-level earnings, branch creation, and Rider administration stay with the main Logistics owner.</p></div>
@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
@if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
<div class="card"><h2>Recent branch scans</h2><div style="overflow-x:auto"><table><thead><tr><th>Tracking code</th><th>Event</th><th>Time</th></tr></thead><tbody>
@forelse($recentScans as $scan)<tr><td>{{ $scan->tracking_code }}</td><td>{{ str_replace('_',' ',ucfirst($scan->event_type)) }}</td><td>{{ $scan->scanned_at }}</td></tr>
@empty<tr><td colspan="3" class="muted">No scans yet.</td></tr>@endforelse</tbody></table></div></div>
<div class="card"><h2>Change your temporary password</h2><form method="POST" action="{{ route('logistics.staff.password') }}">@csrf
<label for="current_password">Current password</label><input type="password" name="current_password" id="current_password" required autocomplete="current-password">
<label for="password">New password (8+ characters, mixed case, number and symbol)</label><input type="password" name="password" id="password" required autocomplete="new-password">
<label for="password_confirmation">Confirm new password</label><input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"><p><button type="submit">Update password</button></p></form></div></main></body></html>
