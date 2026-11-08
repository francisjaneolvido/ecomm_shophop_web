@extends('branch.layout.app')
@section('title','Branch Dashboard')
@section('content')
<div class="space-y-5">
  <header class="sh-page-header"><div><p class="eyebrow">{{ $center->name }}</p><h1 class="title">Branch Dashboard</h1><p class="description">Your live first-mile, receiving, dispatch and last-mile operations at a glance.</p></div>
    <div class="flex gap-2 flex-wrap"><a class="sh-btn-soft" href="{{ route('branch.parcels.index') }}">Parcel Tracking</a><a class="sh-btn" href="{{ route('branch.scanner') }}">Scan QR</a></div></header>
  @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
  <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
    @foreach([['Pending seller pickup',$stats['pickups'],'branch.dispatch.index','pickups'],['Awaiting receipt',$stats['to_receive'],'branch.transfers.index',null],['Ready for delivery',$stats['to_assign'],'branch.dispatch.index','deliveries'],['Delivery exceptions',$stats['failed'],'branch.parcels.index',null]] as [$label,$value,$routeName,$stage])
    <a class="sh-stat block hover:border-teal/40" href="{{ route($routeName,$stage ? ['stage'=>$stage] : []) }}"><div class="sh-stat-label">{{ $label }}</div><div class="sh-stat-value">{{ number_format($value) }}</div><div class="sh-stat-note">Open work queue →</div></a>
    @endforeach
  </section>
  @if(count($alerts)>0)<div class="space-y-2">@foreach($alerts as $alert)<div class="ops-inline-alert"><strong>{{ $alert['title'] ?? 'Action required' }}</strong><span class="ml-2">{{ $alert['detail'] ?? '' }}</span></div>@endforeach</div>@endif
  <div class="grid gap-4 xl:grid-cols-2">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Receiving priority</h2><p class="ops-panel-note">Confirm only parcels physically present at this branch.</p></div><a class="sh-btn-soft" href="{{ route('branch.transfers.index') }}">See transfers</a></div>
      <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Tracking</th><th>Source</th><th>Ready since</th><th>Action</th></tr></thead><tbody>
        @forelse($awaiting as $a)
        <tr><td><strong>{{ $a['tracking'] }}</strong></td><td>{{ $a['from'] }}<div class="ops-row-sub">{{ $a['label'] }}</div></td><td>{{ $a['when'] }}</td><td><a class="ops-action" href="{{ route('branch.scanner') }}">Scan to receive</a></td></tr>
        @empty<tr><td colspan="4" class="ops-empty">No parcels currently awaiting receipt.</td></tr>@endforelse
      </tbody></table></div></section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Ready to assign</h2><p class="ops-panel-note">Sorted parcels waiting for an eligible delivery rider.</p></div><a class="sh-btn-soft" href="{{ route('branch.dispatch.index',['stage'=>'deliveries']) }}">Open Dispatch</a></div>
      <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Tracking</th><th>Buyer</th><th>COD</th></tr></thead><tbody>
        @forelse($toAssign as $p)<tr><td><strong>{{ $p['tracking'] }}</strong></td><td>{{ $p['receiver'] ?? '—' }}<div class="ops-row-sub">{{ $p['barangay'] ?? '' }}</div></td><td>{{ $p['cod'] !== null ? '₱'.number_format($p['cod'],2):'Prepaid' }}</td></tr>
        @empty<tr><td colspan="3" class="ops-empty">No parcels awaiting rider assignment.</td></tr>@endforelse
      </tbody></table></div></section>
  </div>
  <section class="grid grid-cols-2 gap-3 md:grid-cols-4">
    @foreach([['At branch',$stats['at_branch']],['Out for delivery',$stats['out_for_delivery']],['Delivered today',$stats['delivered_today']],['Pending pickups',$stats['pickups']]] as [$name,$value])
      <div class="sh-stat"><p class="sh-stat-label">{{ $name }}</p><p class="sh-stat-value">{{ number_format($value) }}</p></div>
    @endforeach
  </section>
</div>
@endsection
