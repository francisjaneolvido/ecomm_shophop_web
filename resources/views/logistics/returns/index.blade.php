@extends('logistics.layouts.console')
@section('title', 'Return-to-Seller Queue')
@section('content')
<div class="space-y-5">
  <header class="sh-page-header"><div><p class="eyebrow">Operations · Exceptions</p><h1 class="title">Return-to-Seller Queue</h1>
    <p class="description">Only failed deliveries can enter this queue. Record real physical handoffs at every stage. Seller receipt and Admin refund are separate actions.</p></div>
    <a class="sh-btn-ghost" href="{{ route('logistics.deliveries.board') }}">Back to Parcels</a></header>
  @if(session('status'))<div class="sh-alert-success" role="status">{{session('status')}}</div>@endif
  @if($errors->any())<div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{$errors->first()}}</div>@endif
  <div class="grid gap-3 sm:grid-cols-3">
   @foreach([['1','Request return','Decide against retry and open a return case.'],['2','Receive & dispatch','Check tracking code, branch receipt and assign a Return Rider.'],['3','Seller confirmation','Rider uploads proof; Seller acknowledges; Admin handles prepaid refund.']] as $step)
   <div class="sh-surface sh-card-pad"><p class="text-teal-dark text-xs font-bold">STEP {{ $step[0] }}</p><h2 class="font-semibold text-navy">{{$step[1]}}</h2><p class="text-xs text-navy/60 mt-1">{{$step[2]}}</p></div>@endforeach
  </div>
  <section class="sh-ui-filter" aria-label="Filter return cases"><div class="sh-ui-filter-title"><strong>Return cases</strong><span>Search the currently displayed page.</span></div><label class="sh-ui-search"><span class="sr-only">Search return</span><input type="search" class="sh-input" data-ui-list-search="returnCases" placeholder="Order, tracking, seller"></label><span class="sh-ui-count" data-ui-count="returnCases" aria-live="polite"></span></section>
  <div id="returnCases" class="sh-ui-record-stack space-y-3" data-ui-collection>
  @forelse($cases as $case)
  <section class="sh-surface sh-card-pad space-y-4 sh-ui-record" data-ui-record>
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div><h2 class="font-bold text-navy">Return #{{ $case->id }} · Order #{{ $case->order_id }}</h2>
        <p class="text-xs text-navy/60">Seller: {{ $case->order?->seller?->business_name ?? 'Unknown' }} · Tracking: <strong>{{ $case->delivery?->tracking_code }}</strong></p></div>
      <span class="sh-badge">{{ str_replace('_',' ',ucwords($case->status,'_')) }}</span>
    </div>
    <div class="rounded-xl bg-gray-bg/60 p-3 text-xs text-navy/80"><strong>Reason:</strong> {{ $case->reason }}</div>
    @if($case->status === \App\Models\Logistics\ReturnCase::REQUESTED)
      <form method="POST" action="{{route('logistics.returns.receive',$case)}}" class="grid gap-3 sm:grid-cols-3" data-confirm-title="Confirm return parcel receipt?" data-confirm-message="Confirm the package was physically received at the selected branch and the tracking code matches." data-confirm-button="Confirm receipt" data-confirm-subject="Return #{{ $case->id }} · Order #{{ $case->order_id }}">@csrf
      <label class="sh-field"><span class="sh-label">Receiving branch</span><select name="branch_id" required class="sh-select"><option value="">Choose branch</option>@foreach($branches as $branch)<option value="{{$branch->id}}" @selected($branch->id === $case->delivery?->destination_sorting_center_id)>{{$branch->name}}</option>@endforeach</select></label>
      <label class="sh-field"><span class="sh-label">Scan/enter tracking code</span><input name="tracking_code" required class="sh-input" placeholder="{{$case->delivery?->tracking_code}}"></label>
      <div class="self-end"><button class="sh-btn">Confirm physical branch receipt</button></div></form>
    @elseif($case->status === \App\Models\Logistics\ReturnCase::AT_BRANCH)
      <form method="POST" action="{{route('logistics.returns.assign',$case)}}" class="grid gap-3 sm:grid-cols-[1fr_auto]" data-confirm-title="Assign return rider?" data-confirm-message="Assign the selected rider to collect and return this parcel to the seller." data-confirm-button="Assign rider" data-confirm-subject="Return #{{ $case->id }} · Order #{{ $case->order_id }}">@csrf
      <label class="sh-field"><span class="sh-label">Return Rider — {{ $case->branch?->name }}</span><select required name="rider_id" class="sh-select"><option value="">Choose Rider</option>
      @foreach($riders->where('sorting_center_id',$case->return_branch_id) as $rider)<option value="{{$rider->id}}">{{$rider->name}}</option>@endforeach</select></label><div class="self-end"><button class="sh-btn">Assign Return Rider</button></div></form>
    @elseif($case->status === \App\Models\Logistics\ReturnCase::ASSIGNED)
      <p class="text-sm text-navy/70">Assigned to <strong>{{$case->returnRider?->name}}</strong>. Rider will confirm collection on mobile.</p>
    @elseif($case->status === \App\Models\Logistics\ReturnCase::IN_TRANSIT)
      <p class="text-sm text-navy/70">Rider {{$case->returnRider?->name}} confirmed pickup. Waiting for Seller handover proof.</p>
    @elseif($case->status === \App\Models\Logistics\ReturnCase::AWAITING_SELLER)
      <p class="text-sm text-navy/70">Handover proof submitted. Seller must confirm physical receipt in Seller Web → Order Details.</p>
    @else
      <p class="text-sm text-navy/70">Seller confirmed return at {{$case->seller_received_at?->format('M j, Y g:i A')}}. Refund: <strong>{{ str_replace('_',' ',ucfirst($case->refund_status)) }}</strong>.</p>
    @endif
    <p class="text-xs text-amber-700">Stock is not automatically restored. Manual inspection is required. No payout is released for a returned order.</p>
  </section>
  @empty<div class="sh-surface sh-card-pad text-center text-navy/60">No return cases yet. Start from a failed delivery on Parcel Operations.</div>@endforelse
  </div>
  {{$cases->links()}}
</div>
@endsection
