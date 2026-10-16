<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ReturnCase;
use App\Services\ParcelReturnService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReturnController extends Controller
{
    private function owned(Request $request,int $case): ReturnCase
    {
        $seller=$request->user()->seller ?? abort(403);
        return ReturnCase::whereKey($case)->whereHas('order',fn($query)=>$query->where('seller_id',$seller->id))->firstOrFail();
    }
    public function confirm(Request $request,int $case,ParcelReturnService $returns): RedirectResponse
    {
        $this->owned($request,$case);
        $data=$request->validate(['physically_received'=>['required','accepted']]);
        $returns->sellerAcknowledge((int)$request->user()->seller->id,$case);
        return redirect()->route('seller.orders.show',$this->owned($request,$case)->order_id)
            ->with('status','Returned parcel receipt confirmed. Please inspect items before manually updating inventory.');
    }
    public function proof(Request $request,int $case): BinaryFileResponse
    {
        $return=$this->owned($request,$case);
        abort_unless($return->handover_proof_path && Storage::disk('local')->exists($return->handover_proof_path),404);
        return response()->file(Storage::disk('local')->path($return->handover_proof_path));
    }
}
