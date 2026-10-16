<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Rider;
use App\Services\ParcelReturnService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MobileRiderReturnController extends Controller
{
    private function rider(Request $request): Rider
    {
        $user=$request->user();
        abort_unless($user instanceof Rider,403);
        $rider=$user->fresh(['partner.user']);
        abort_unless($rider && $rider->status==='active' && $rider->email_verified_at
            && $rider->partner?->user?->status==='approved',403);
        return $rider;
    }
    public function index(Request $request): JsonResponse
    {
        $rider=$this->rider($request);
        $cases=ReturnCase::with(['order.seller','branch','delivery'])
            ->where('logistics_partner_id',$rider->logistics_partner_id)
            ->where('return_rider_id',$rider->id)->latest()->limit(50)->get();
        return response()->json(['success'=>true,'data'=>['returns'=>$cases->map(fn($c)=>[
            'id'=>$c->id,'order_id'=>$c->order_id,'status'=>$c->status,
            'tracking_code'=>$c->delivery?->tracking_code,'seller'=>$c->order?->seller?->business_name,
            'branch'=>$c->branch?->name,'reason'=>$c->reason,
        ])->values()]]);
    }
    public function pickup(Request $request,int $case,ParcelReturnService $returns): JsonResponse
    {
        $rider=$this->rider($request);
        $returns->riderCollect($rider->id,$rider->logistics_partner_id,$case);
        return response()->json(['success'=>true,'message'=>'Return parcel collection confirmed. Deliver it to the Seller.']);
    }
    public function handover(Request $request,int $case,ParcelReturnService $returns): JsonResponse
    {
        $rider=$this->rider($request);
        $data=$request->validate(['proof'=>['required','image','mimes:jpg,jpeg,png,webp','max:5120']]);
        $path=$data['proof']->store('private-return-proofs','local');
        try {
            $returns->riderHandover($rider->id,$rider->logistics_partner_id,$case,$path);
        } catch (\Throwable $exception) { Storage::disk('local')->delete($path); throw $exception; }
        return response()->json(['success'=>true,'message'=>'Handover proof saved. Waiting for Seller receipt confirmation.']);
    }
}
