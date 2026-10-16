<?php
namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Services\ParcelReturnService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnController extends Controller
{
    private function partner(Request $request): LogisticsPartner
    { return LogisticsPartner::where('user_id',$request->user()->id)->firstOrFail(); }
    public function index(Request $request): View
    {
        $partner=$this->partner($request);
        $cases=ReturnCase::with(['order.seller','delivery','branch','returnRider'])
            ->where('logistics_partner_id',$partner->id)->latest()->paginate(25);
        $branches=SortingCenter::where('logistics_partner_id',$partner->id)->where('status','active')->orderBy('name')->get();
        $riders=Rider::where('logistics_partner_id',$partner->id)->where('status','active')
            ->whereNotNull('email_verified_at')->orderBy('name')->get();
        return view('logistics.returns.index',compact('cases','branches','riders'));
    }
    public function initiate(Request $request,int $delivery,ParcelReturnService $returns): RedirectResponse
    {
        $input=$request->validate(['reason'=>['required','string','min:10','max:1000']]);
        $returns->initiate($this->partner($request)->id,$delivery,$request->user()->id,$input['reason']);
        return redirect()->route('logistics.returns.index')->with('status','Return initiated. Wait for parcel to physically arrive at the branch.');
    }
    public function receive(Request $request,int $case,ParcelReturnService $returns): RedirectResponse
    {
        $input=$request->validate(['branch_id'=>['required','integer'],'tracking_code'=>['required','string','max:60']]);
        $returns->receiveBranch($this->partner($request)->id,$case,$request->user()->id,(int)$input['branch_id'],$input['tracking_code']);
        return back()->with('status','Return parcel physically received and recorded at branch.');
    }
    public function assign(Request $request,int $case,ParcelReturnService $returns): RedirectResponse
    {
        $input=$request->validate(['rider_id'=>['required','integer']]);
        $returns->assignRider($this->partner($request)->id,$case,(int)$input['rider_id']);
        return back()->with('status','Return Rider assigned. Rider must confirm pickup on Mobile.');
    }
}
