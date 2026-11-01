@extends('logistics.layouts.console')
@section('title','Transport Routes')
@section('content')
<div class="space-y-4">
  <header class="sh-page-header"><div><p class="eyebrow">Nationwide network · Safe operations</p><h1 class="title">Transport Connections</h1><p class="description">Configure real connected transport legs. ROAD hub transfers require verified roads; SEA/AIR cargo requires verified carrier links and separate Hub custody confirmation. One direction = one transport mode.</p></div></header>
  @if(session('status'))<div class="p-3 rounded-lg bg-teal-50 text-teal-900 text-sm" role="status">{{session('status')}}</div>@endif
  @if($errors->any())<div class="p-3 rounded-lg bg-red-50 text-red-800 text-sm" role="alert">{{ $errors->first() }}</div>@endif
  <section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Add transport connection</h2>
    <form method="post" action="{{route('logistics.route-network.store')}}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">@csrf
      <label class="text-xs font-semibold">From center <span class="text-red-600">*</span><select class="sh-input mt-1 w-full" name="from_center_id" required><option value="">Select origin</option>@foreach($centers as $center)<option value="{{$center->id}}" @selected(old('from_center_id')==$center->id)>{{$center->name}}</option>@endforeach</select></label>
      <label class="text-xs font-semibold">To center <span class="text-red-600">*</span><select class="sh-input mt-1 w-full" name="to_center_id" required><option value="">Select destination</option>@foreach($centers as $center)<option value="{{$center->id}}" @selected(old('to_center_id')==$center->id)>{{$center->name}}</option>@endforeach</select></label>
      <label class="text-xs font-semibold">Transport <span class="text-red-600">*</span><select class="sh-input mt-1 w-full" name="mode" required><option value="road">Road</option><option value="sea">Sea / ferry</option><option value="air">Air</option></select></label>
      <label class="text-xs font-semibold">Operations notes <span class="text-red-600">*</span><input name="notes" class="sh-input mt-1 w-full" minlength="12" maxlength="500" required placeholder="Carrier, gateway or schedule" value="{{old('notes')}}"></label>
      <div class="md:col-span-2 xl:col-span-4 flex flex-wrap items-center justify-between gap-2"><span class="text-xs text-slate-500">Directional links only. For Laguna → Mindanao, choose SEA/AIR for cross-island legs. To correct an un-used road link: Suspend, then submit this route again as SEA/AIR.</span><button class="sh-btn">Add proposed connection</button></div>
    </form>
  </section>
  <section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-2">Network links</h2><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>From → To</th><th>Mode</th><th>Notes</th><th>Status</th><th>Action</th></tr></thead><tbody>
    @forelse($links as $link)<tr><td>{{$link->fromCenter?->name ?? 'Removed'}} → {{$link->toCenter?->name ?? 'Removed'}}</td><td>{{ucfirst($link->mode)}}</td><td>{{$link->notes}}</td><td><span class="rounded-lg px-2 py-1 text-xs {{$link->status==='verified'?'bg-teal-50 text-teal-800':'bg-amber-50 text-amber-800'}}">{{ucfirst($link->status)}}</span></td><td>
      @if($link->status!=='verified')<form method="post" action="{{route('logistics.route-network.verify',$link->id)}}" class="inline-flex gap-1 flex-wrap" data-confirm-title="Verify actual transport route?" data-confirm-message="Only confirm when the carrier, gateway and handoff are truly operational." data-confirm-button="Verify route">@csrf<input type="hidden" name="confirmation" value="VERIFY_ACTUAL_TRANSPORT"><button class="sh-btn-soft text-xs">Verify</button></form>@endif
      @if($link->status!=='suspended')<form method="post" action="{{route('logistics.route-network.suspend',$link->id)}}" class="inline-flex ml-2" data-confirm-title="Suspend connection?" data-confirm-message="The route will no longer appear as verified." data-confirm-button="Suspend">@csrf<button class="sh-btn-soft text-xs">Suspend</button></form>@endif
    </td></tr>@empty<tr><td colspan="5" class="py-6 text-slate-500">No routes configured yet. Propose one for your actual operations.</td></tr>@endforelse
  </tbody></table></div><div class="mt-3">{{$links->links()}}</div></section>
</div>
@endsection
