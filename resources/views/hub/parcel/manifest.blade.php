@php
    $trip = $record->linehaulTrip;
    $created = optional($record->prepared_at ?: $record->created_at)?->format('M d, Y g:i A');
    $codTotal = $items->where('payment_method','cod')->sum('total_amount');
@endphp
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manifest {{ $record->code }}</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;color:#0F1B3D;margin:24px;font-size:12px}.head{display:flex;justify-content:space-between;gap:24px}.qr{display:flex;gap:16px;align-items:center}.muted{color:#5b6477}.meta{display:grid;grid-template-columns:repeat(2,1fr);gap:6px 24px;margin:18px 0}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{border:1px solid #cfd6de;padding:6px 8px;text-align:left}th{background:#f3f5f7;font-size:11px;text-transform:uppercase}.actions{margin-bottom:16px}.actions button,.actions a{font-size:13px;padding:8px 14px;border-radius:6px;border:1px solid #cfd6de;background:#fff;color:#0F1B3D;text-decoration:none;cursor:pointer}@media print{.no-print{display:none!important}body{margin:0}}
</style></head><body>
<div class="actions no-print"><button onclick="window.print()">Print manifest</button><a href="{{ route('logistics.hub.parcels') }}">Back</a></div>
<div class="head"><div><h1>ShopHop Linehaul Manifest</h1><p class="muted">{{ $record->code }} · {{ ucfirst(str_replace('_',' ',$record->status)) }}</p></div><div class="qr"><div id="manifestQr"></div><div><strong>MANIFEST QR</strong><div class="muted">Assigned Driver scans this to take custody of the complete load.</div><div style="margin-top:6px;font-weight:700">{{ $record->code }}</div></div></div></div>
<div class="meta">
<div><strong>From:</strong> {{ $center->name }}</div><div><strong>To:</strong> {{ $record->toCenter?->name ?? '—' }}</div>
<div><strong>Prepared:</strong> {{ $created }}</div><div><strong>Parcels:</strong> {{ $items->count() }}</div>
<div><strong>Trip:</strong> {{ $trip?->code ?? 'Not assigned yet' }}</div><div><strong>Vehicle:</strong> {{ $trip?->vehicle?->code ?? '—' }} {{ $trip?->vehicle?->plate_number ? '('.$trip->vehicle->plate_number.')' : '' }}</div>
<div><strong>Driver:</strong> {{ $trip?->driver?->name ?? '—' }}</div><div><strong>COD total:</strong> ₱{{ number_format($codTotal,2) }}</div>
</div>
<table><thead><tr><th>#</th><th>Tracking</th><th>Receiver</th><th>City</th><th>Loaded</th><th>Received</th></tr></thead><tbody>
@forelse($items as $i=>$item)<tr><td>{{ $i+1 }}</td><td>{{ $item->tracking_code }}</td><td>{{ $item->delivery_name ?? '—' }}</td><td>{{ $item->delivery_municipality_name ?? '—' }}</td><td>{{ $item->loaded_at ? 'YES' : 'NO' }}</td><td>{{ $item->received_at ? 'YES' : 'NO' }}</td></tr>@empty<tr><td colspan="6" class="muted">No parcels.</td></tr>@endforelse
</tbody></table>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
<script>if(typeof QRCode==='function'){new QRCode(document.getElementById('manifestQr'),{text:@json($record->code),width:112,height:112,correctLevel:QRCode.CorrectLevel.M});}</script>
</body></html>
