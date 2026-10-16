@extends('admin.layout')
@section('title','Return Refund Review')
@section('content')
<div class="space-y-5">
 <div><h1 class="text-2xl font-bold text-navy">Returns & Manual Refunds</h1><p class="text-sm text-slate-500 mt-1">Refunds are recorded only after Seller confirms receiving the returned parcel. Demo payment mode: no automatic money transfer.</p></div>
 <a class="text-sm text-teal-700 underline" href="{{route('admin.commission')}}">Back to ShopHop Finances</a>
 @if(session('status'))<div class="rounded-xl bg-teal-50 p-4 text-sm text-teal-800">{{session('status')}}</div>@endif
 @if($errors->any())<div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800">{{$errors->first()}}</div>@endif
 <div class="space-y-3">@forelse($cases as $case)
 <div class="rounded-xl border bg-white p-5">
  <div class="flex flex-wrap items-center justify-between gap-2"><strong>Order #{{$case->order_id}} · {{$case->order?->seller?->business_name}}</strong><span class="text-xs rounded-full bg-slate-100 px-3 py-1">{{str_replace('_',' ',ucfirst($case->status))}}</span></div>
  <p class="text-xs text-slate-500 mt-2">Return reason: {{$case->reason}}</p>
  <p class="text-xs mt-2">Payment: <strong>{{strtoupper($case->order?->payment_method ?? '')}}</strong> · Required refund: <strong>₱{{number_format((float)$case->refund_amount,2)}}</strong> · Status: <strong>{{$case->refund_status}}</strong></p>
  @if($case->status === \App\Models\Logistics\ReturnCase::SELLER_RECEIVED && $case->refund_status === 'pending')
   <form method="POST" action="{{route('admin.returns.refund-paid',$case)}}" class="mt-3 flex flex-wrap items-end gap-2">@csrf
    <label class="text-xs">Manual refund transaction reference<input name="reference" required minlength="4" maxlength="120" class="block rounded border p-2 mt-1" placeholder="DEMO-REF-0001"></label>
    <button class="rounded bg-teal-700 px-4 py-2 text-sm font-semibold text-white" onclick="return confirm('Confirm manual refund record? This does not transfer money.')">Record refund paid</button>
   </form>
  @elseif($case->refund_status === 'paid')
   <p class="mt-2 text-xs text-teal-700">Refund recorded {{$case->refunded_at?->format('M j, Y')}} · Ref: {{$case->refund_reference}}</p>
  @else
   <p class="mt-2 text-xs text-amber-700">No refund action available until Seller confirms receipt (or COD had no payment).</p>
  @endif
 </div>@empty<div class="rounded-xl bg-white border p-5 text-sm text-slate-500">No return cases have been created.</div>@endforelse</div>
 {{$cases->links()}}
</div>
@endsection
