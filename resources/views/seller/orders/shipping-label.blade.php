<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shipping Label · {{ $order->tracking_code }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f4; color: #0f2c3f; font-family: Arial, sans-serif; }
        .toolbar { max-width: 820px; margin: 20px auto 0; display: flex; gap: 10px; justify-content: flex-end; }
        .toolbar a,.toolbar button { border: 0; border-radius: 8px; padding: 10px 16px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .toolbar a { background: white; color: #0f2c3f; border: 1px solid #d9e0e4; }
        .toolbar button { background: #18bfa5; color: white; }
        .label { width: 780px; min-height: 500px; margin: 12px auto 30px; background: white; border: 2px solid #0f2c3f; padding: 22px; }
        .head { display: flex; align-items: start; justify-content: space-between; border-bottom: 2px solid #0f2c3f; padding-bottom: 14px; }
        .brand { font-size: 25px; font-weight: 900; }
        .muted { color: #55707f; font-size: 12px; }
        .tracking { font-size: 24px; font-weight: 900; letter-spacing: 2px; text-align: right; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #0f2c3f; margin-top: 18px; }
        .cell { min-height: 135px; padding: 14px; border-right: 1px solid #0f2c3f; }
        .cell:last-child { border-right: 0; }
        .eyebrow { font-size: 10px; font-weight: 800; letter-spacing: 1px; color: #55707f; text-transform: uppercase; }
        .name { margin-top: 7px; font-size: 17px; font-weight: 800; }
        .address { margin-top: 5px; font-size: 13px; line-height: 1.45; }
        .meta { margin-top: 18px; display: grid; grid-template-columns: repeat(3,1fr); border: 1px solid #0f2c3f; }
        .meta > div { padding: 12px; border-right: 1px solid #0f2c3f; }
        .meta > div:last-child { border-right: 0; }
        .items { margin-top: 18px; width: 100%; border-collapse: collapse; font-size: 12px; }
        .items th,.items td { border: 1px solid #cdd7dc; padding: 8px; text-align: left; }
        .footer { margin-top: 16px; padding-top: 12px; border-top: 1px dashed #8296a1; font-size: 11px; color: #55707f; }
        @media print { body { background: white; } .toolbar { display:none; } .label { margin:0; width:100%; border:2px solid #000; } }
    </style>
</head>
<body>
<div class="toolbar">
    <a href="{{ route('seller.orders.show', $order) }}">Back to Order</a>
    <button type="button" onclick="window.print()">Print Label</button>
</div>
<section class="label">
    <div class="head">
        <div>
            <div class="brand">ShopHop</div>
            <div class="muted">Seller parcel shipping label</div>
        </div>
        <div>
            <div class="eyebrow" style="text-align:right">Tracking Code</div>
            <div class="tracking">{{ $order->tracking_code }}</div>
            <div class="muted" style="text-align:right">Order #{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="grid">
        <div class="cell">
            <div class="eyebrow">From · Seller</div>
            <div class="name">{{ $order->seller?->business_name ?: trim(($order->seller?->first_name ?? '').' '.($order->seller?->last_name ?? '')) }}</div>
            <div class="address">{{ collect([$order->seller?->street_address, $order->seller?->barangay_name, $order->seller?->municipality_name, $order->seller?->province_name])->filter()->implode(', ') }}</div>
            <div class="muted" style="margin-top:6px">{{ $order->seller?->contact_no }}</div>
        </div>
        <div class="cell">
            <div class="eyebrow">To · Buyer</div>
            <div class="name">{{ $order->delivery_name ?: 'Buyer' }}</div>
            <div class="address">{{ $order->delivery_address }}</div>
            <div class="muted" style="margin-top:6px">{{ $order->delivery_phone }}</div>
        </div>
    </div>

    <div class="meta">
        <div><div class="eyebrow">Logistics</div><strong>{{ $order->pickupRequest?->partner?->company_name ?? 'Pending selection' }}</strong></div>
        <div><div class="eyebrow">Origin Center</div><strong>{{ $order->pickupRequest?->originSortingCenter?->name ?? 'Assigned after pickup request' }}</strong></div>
        <div><div class="eyebrow">Payment</div><strong>{{ strtoupper($order->payment_method ?? '—') }}</strong></div>
    </div>

    <table class="items">
        <thead><tr><th>Item</th><th>Variant</th><th>Qty</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr><td>{{ $item->product?->name ?? 'Product' }}</td><td>{{ $item->variantLabel() }}</td><td>{{ $item->quantity }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer">Attach this label securely before Rider handoff. Logistics/Riders should verify the tracking code at parcel custody changes.</div>
</section>
</body>
</html>
