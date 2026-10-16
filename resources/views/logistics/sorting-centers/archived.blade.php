@extends('logistics.layouts.console')
@section('title', 'Archived Branches — ShopHop Logistics')
@section('content')
<div class="mx-auto max-w-5xl space-y-5">
  <a class="text-xs font-bold text-teal-dark" href="{{ route('logistics.sorting-centers.index') }}">← Branch directory</a>
  <header><p class="eyebrow">LOGISTICS NETWORK</p><h1 class="title">Archived Branches</h1><p class="mt-2 text-sm text-navy/60">Archived facilities are not available for routing or scanning, but their historical parcels, payouts and scan records remain intact.</p></header>
  @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
  <div class="sh-surface divide-y divide-gray-border">
    @forelse($centers as $center)
    <article class="flex flex-wrap items-center justify-between gap-3 p-5">
      <div><h2 class="font-bold text-navy">{{ $center->name }}</h2><p class="mt-1 text-xs text-navy/55">{{ $center->code }} · {{ $center->addressLabel() }}</p><p class="mt-1 text-xs text-navy/45">Archived {{ $center->archived_at?->format('M j, Y') }}</p></div>
      <form method="POST" action="{{ route('logistics.sorting-centers.restore', $center) }}" data-confirm-title="Restore branch as draft?" data-confirm-message="The branch will remain inactive until you review its manager and coverage." data-confirm-button="Restore branch">
        @csrf <button class="sh-btn-soft" type="submit">Restore branch</button>
      </form>
    </article>
    @empty<div class="p-10 text-center text-sm text-navy/55">No archived branches. Deactivation does not remove a branch immediately.</div>@endforelse
  </div>
</div>
@endsection
