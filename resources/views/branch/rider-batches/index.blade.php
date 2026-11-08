@extends('branch.layout.app')
@section('title','Rider Batches')
@section('content')
<div class="ops-module space-y-5">
    <header class="ops-hero"><div><span class="eyebrow">Branch operations</span><h1>Rider batches</h1>
        <p class="description">One active route per rider. Multiple parcels and stops; individual QR, proof and parcel outcomes.</p></div></header>
    @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error" role="alert">{{ $errors->first() }}</div>@endif
    <details class="ops-panel p-4"><summary class="cursor-pointer font-semibold text-sm">Vehicle & route capacity</summary>
        <p class="mt-2 text-xs text-slate-500">Set limits for the rider's actual vehicle. Parcel count is optional; weight, volume, stops and duration are required. Close open batches before changing these limits.</p>
        <form id="capacity-form" method="POST" class="grid gap-3 mt-4 md:grid-cols-3" data-url="{{ route($routePrefix.'.capacity',['rider'=>'RIDER_ID']) }}">
            @csrf
            <label class="sh-field"><span class="sh-label">Rider</span><select id="capacity-rider" class="sh-input" required><option value="">Choose rider</option>@foreach($riders as $rider)<option value="{{ $rider->id }}">{{ $rider->name }} &middot; {{ $rider->vehicle_type }} {{ $rider->plate_number }}</option>@endforeach</select></label>
            @foreach(['max_weight_kg'=>'Max weight (kg)','max_volume_m3'=>'Max volume (m3)','max_stops'=>'Max stops','max_duration_minutes'=>'Max duration (minutes)','max_parcels'=>'Max parcels (optional)'] as $key=>$label)
                <label class="sh-field"><span class="sh-label">{{ $label }}</span><input name="{{ $key }}" type="number" class="sh-input" min="{{ $key==='max_volume_m3'?'0.000001':($key==='max_weight_kg'?'0.001':'1') }}" step="{{ in_array($key,['max_weight_kg','max_volume_m3'])?'any':'1' }}" @required($key!=='max_parcels')></label>
            @endforeach
            <button type="submit" class="sh-btn-primary">Save vehicle capacity</button>
        </form>
    </details>
    <section class="ops-panel p-4 space-y-4">
        <h2 class="font-semibold">Plan a {{ $type }} batch</h2>
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="sh-field"><span class="sh-label">Batch type</span><select class="sh-input" name="type"><option value="pickup" @selected($type==='pickup')>Pickup</option><option value="delivery" @selected($type==='delivery')>Delivery</option></select></label>
            <label class="sh-field"><span class="sh-label">Tracking code</span><input class="sh-input" name="q" value="{{ request('q') }}" placeholder="Search parcels"></label>
            <button class="sh-btn-soft">Find parcels</button>
        </form>
        <form id="batch-plan" action="{{ route($routePrefix.'.store') }}" class="space-y-4">
            @csrf<input type="hidden" name="type" value="{{ $type }}">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <label class="sh-field"><span class="sh-label">Rider</span><select name="rider_id" class="sh-input" required><option value="">Choose configured rider</option>@foreach($riders as $rider)@if($profiles->has($rider->id))<option value="{{ $rider->id }}">{{ $rider->name }} &middot; {{ $rider->availability_status }}</option>@endif @endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Route start (your local time)</span><input name="start" type="datetime-local" class="sh-input" required></label>
                <label class="sh-field"><span class="sh-label">Route end (your local time)</span><input name="end" type="datetime-local" class="sh-input" required></label>
                <label class="sh-field"><span class="sh-label">Estimated minutes, including Branch return</span><input name="estimated_minutes" type="number" min="1" step="1" class="sh-input" required></label>
            </div>
            <p class="text-xs text-slate-500">Select parcels across pages, then set route order in the selection list. Parcels for the same stop are grouped. Enter measured weight and volume; the server rechecks coverage, eligibility and capacity.</p>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-500"><th class="p-2">Select</th><th class="p-2">Parcel / stop</th><th class="p-2">Weight (kg)</th><th class="p-2">Volume (m3)</th></tr></thead><tbody>
            @forelse($orders as $order)
                <tr data-parcel="{{ $order->id }}" class="border-t border-gray-border">
                    <td class="p-2"><input type="checkbox" aria-label="Select {{ $order->tracking_code }}"></td>
                    <td class="p-2"><strong class="font-medium">{{ $order->tracking_code }}</strong><p class="text-xs text-slate-500">{{ $type==='pickup'?$order->seller?->business_name:$order->delivery_name }}</p><p class="text-xs text-slate-500">{{ $type==='pickup'?$order->pickupRequest?->originAddressLabel():$order->delivery_address }}</p></td>
                    <td class="p-2"><input data-weight type="number" min="0.001" step="0.001" value="{{ $order->financial?->shipping_weight_kg?:'' }}" class="sh-input" aria-label="Weight for {{ $order->tracking_code }}"></td>
                    <td class="p-2"><input data-volume type="number" min="0.000001" step="0.000001" class="sh-input" aria-label="Volume for {{ $order->tracking_code }}"></td>
                </tr>
            @empty<tr><td colspan="4" class="p-4 text-slate-500">No eligible unreserved parcels match this page.</td></tr>@endforelse
            </tbody></table></div>
            <div class="rounded-lg border border-gray-border p-3"><div class="flex items-center justify-between gap-2"><strong class="text-sm">Selected route parcels</strong><button type="button" id="clear-parcels" class="sh-btn-soft">Clear selection</button></div><p class="text-xs text-slate-500 mt-1">Selections stay in this browser tab while you search or change page. Smaller route-order numbers are visited first.</p><ol id="selected-parcels" class="space-y-2 mt-3"></ol></div>
            <p id="batch-message" role="status" aria-live="polite" class="text-sm"></p>
            <button class="sh-btn-primary" type="submit">Queue selected parcels</button>
        </form>
        {{ $orders->links() }}
    </section>
    <section class="space-y-3"><h2 class="font-semibold">Routes & progress</h2>
        <p data-batch-sync-status role="status" class="text-xs text-slate-500">Updates every 15 seconds while this page is visible.</p>
        <div id="rider-batch-routes" class="space-y-3" data-live-url="{{ request()->fullUrl() }}">
            @include('logistics.partials.rider-batch-routes',['canManage'=>true])
        </div>
    </section>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/shophop-rider-batches.js') }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const profiles=@json($profiles->map(fn($p)=>$p->limits));
    const capacity=document.getElementById('capacity-form');
    const rider=document.getElementById('capacity-rider');
    rider.addEventListener('change',()=>{capacity.action=capacity.dataset.url.replace('RIDER_ID',rider.value);for(const input of capacity.querySelectorAll('input[type=number]'))input.value=profiles[rider.value]?.[input.name]??'';});
    capacity.addEventListener('submit',e=>{if(!rider.value)e.preventDefault();});
    const form=document.getElementById('batch-plan');const message=document.getElementById('batch-message');

    const key='shophop-batch-selection-{{ $center->id }}-{{ $type }}';
    let selected=new Map();
    try { selected=new Map(JSON.parse(sessionStorage.getItem(key)||'[]')); } catch {}
    const tray=document.getElementById('selected-parcels');
    function persist(){try{sessionStorage.setItem(key,JSON.stringify([...selected]));}catch{}}
    function paint(){
        tray.replaceChildren();
        if(!selected.size){const li=document.createElement('li');li.textContent='No parcels selected.';li.className='text-xs text-slate-500';tray.append(li);}
        for(const [id,item] of [...selected].sort((a,b)=>a[1].route_order-b[1].route_order)){
            const li=document.createElement('li');li.className='flex flex-wrap items-center gap-2 text-xs';
            const text=document.createElement('span');text.className='flex-1';text.textContent=item.tracking+' / '+item.weight_kg+' kg / '+item.volume_m3+' m3';
            const input=document.createElement('input');input.type='number';input.min='1';input.step='1';input.value=item.route_order;input.className='sh-input';input.style.width='5rem';input.setAttribute('aria-label','Route order for '+item.tracking);
            input.addEventListener('change',()=>{item.route_order=Math.max(1,Math.floor(Number(input.value)||1));persist();paint();});
            const remove=document.createElement('button');remove.type='button';remove.className='sh-btn-soft';remove.textContent='Remove';
            remove.addEventListener('click',()=>{selected.delete(id);const row=[...form.querySelectorAll('[data-parcel]')].find(row=>Number(row.dataset.parcel)===id);if(row)row.querySelector('input[type=checkbox]').checked=false;persist();paint();});
            li.append(text,input,remove);tray.append(li);
        }
    }
    for(const row of form.querySelectorAll('[data-parcel]')){
        const id=Number(row.dataset.parcel),check=row.querySelector('input[type=checkbox]'),weight=row.querySelector('[data-weight]'),volume=row.querySelector('[data-volume]');
        const saved=selected.get(id);if(saved){check.checked=true;weight.value=saved.weight_kg;volume.value=saved.volume_m3;}
        function update(){
            if(check.checked){selected.set(id,{order_id:id,tracking:row.querySelector('strong').textContent,weight_kg:Number(weight.value),volume_m3:Number(volume.value),route_order:selected.get(id)?.route_order??Math.max(0,...[...selected.values()].map(v=>v.route_order))+1});}
            else selected.delete(id);
            persist();paint();
        }
        check.addEventListener('change',update);weight.addEventListener('input',update);volume.addEventListener('input',update);
    }
    document.getElementById('clear-parcels').addEventListener('click',()=>{selected.clear();for(const input of form.querySelectorAll('input[type=checkbox]'))input.checked=false;persist();paint();});
    paint();
    form.addEventListener('submit',async e=>{
        e.preventDefault();if(!form.reportValidity())return;
        const parcels=[...selected.values()].sort((a,b)=>a.route_order-b.route_order).map(({order_id,weight_kg,volume_m3})=>({order_id,weight_kg,volume_m3}));
        if(!parcels.length){message.textContent='Select at least one parcel.';return;}
        const button=form.querySelector('button[type=submit]');button.disabled=true;message.textContent='Checking route and capacity...';
        try{
            const response=await fetch(form.action,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},
                body:JSON.stringify({type:form.elements.type.value,rider_id:Number(form.elements.rider_id.value),scheduled_start:new Date(form.elements.start.value).toISOString(),scheduled_end:new Date(form.elements.end.value).toISOString(),estimated_minutes:Number(form.elements.estimated_minutes.value),parcels})});
            const body=await response.json();if(!response.ok){message.textContent=Object.values(body.errors??{}).flat().join(' ')||body.message||'Could not plan batch.';return;}sessionStorage.removeItem(key);location.reload();
        }catch{message.textContent='Unable to save this batch. Your selections are still available; check the connection and retry.';}
        finally{button.disabled=false;}
    });
});
</script>
@endpush
