@php
    $created = optional($m->prepared_at ?: $m->created_at)?->format('M d, Y g:i A');
    $codTotal = $items->where('payment_method', 'cod')->sum('total_amount');
    $trip = $m->linehaulTrip;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manifest {{ $m->code }}</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;color:#0F1B3D;margin:24px;font-size:12px}.head{display:flex;justify-content:space-between;gap:24px;align-items:flex-start}.qr{display:flex;gap:16px;align-items:center}.muted{color:#5b6477}.meta{display:grid;grid-template-columns:repeat(2,1fr);gap:6px 24px;margin:18px 0}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{border:1px solid #cfd6de;padding:6px 8px;text-align:left}th{background:#f3f5f7;font-size:11px;text-transform:uppercase}.right{text-align:right}.sign{display:grid;grid-template-columns:repeat(2,1fr);gap:48px;margin-top:48px}.sign div{border-top:1px solid #0F1B3D;padding-top:6px}.actions{margin-bottom:16px}.actions button,.actions a{font-size:13px;padding:8px 14px;border-radius:6px;border:1px solid #cfd6de;background:#fff;color:#0F1B3D;text-decoration:none;cursor:pointer}@media print{.no-print{display:none!important}body{margin:0}}
</style>
</head>
<body>
<div class="actions no-print"><button onclick="window.print()">Print</button><a href="{{ route('branch.outbound.index') }}">Back</a></div>
<div class="head">
  <div><h1>ShopHop Linehaul Manifest</h1><p class="muted">{{ $m->code }} · {{ ucfirst(str_replace('_',' ',$m->status)) }}</p></div>
  <div class="qr"><div id="manifestQr"></div><div><strong>MANIFEST QR</strong><div class="muted">Driver scans this at origin to accept custody and depart. Destination Branch/Hub scans the same QR to acknowledge arrival.</div><div style="margin-top:6px;font-weight:700">{{ $m->code }}</div></div></div>
</div>
<div class="meta">
 <div><strong>From:</strong> {{ $center->name }}</div><div><strong>To:</strong> {{ $m->toCenter?->name ?? '—' }}</div>
 <div><strong>Prepared:</strong> {{ $created }}</div><div><strong>Parcels:</strong> {{ $items->count() }}</div>
 <div><strong>Trip:</strong> {{ $trip?->code ?? 'Not assigned yet' }}</div><div><strong>Vehicle:</strong> {{ $trip?->vehicle?->code ?? '—' }} {{ $trip?->vehicle?->plate_number ? '('.$trip->vehicle->plate_number.')' : '' }}</div>
 <div><strong>Driver:</strong> {{ $trip?->driver?->name ?? '—' }}</div><div><strong>COD total:</strong> ₱{{ number_format($codTotal,2) }}</div>
 @if($m->note)<div style="grid-column:1/-1"><strong>Note:</strong> {{ $m->note }}</div>@endif
</div>
<table>
<thead><tr><th>#</th><th>Tracking</th><th>Receiver</th><th>Final branch</th><th>Loaded</th><th class="right">COD</th></tr></thead>
<tbody>
@forelse($items as $i=>$item)
<tr><td>{{ $i+1 }}</td><td>{{ $item->tracking_code }}</td><td>{{ $item->delivery_name ?? '—' }}</td><td>{{ $item->final_branch ?? '—' }}</td><td>{{ $item->loaded_at ? 'YES' : 'NO' }}</td><td class="right">{{ $item->payment_method==='cod' ? '₱'.number_format($item->total_amount,2) : 'Paid' }}</td></tr>
@empty<tr><td colspan="6" class="muted">No parcels.</td></tr>@endforelse
</tbody>
</table>
<div class="sign"><div>Released by Branch / Name & signature</div><div>Received by Destination Center / Name & signature</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
<script>if(typeof QRCode==='function'){new QRCode(document.getElementById('manifestQr'),{text:@json($m->code),width:112,height:112,correctLevel:QRCode.CorrectLevel.M});}</script>
</body></html>
