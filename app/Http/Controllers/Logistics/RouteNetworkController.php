<?php
namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\RouteLink;
use App\Models\Logistics\SortingCenter;
use App\Services\PhilippineIslandGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RouteNetworkController extends Controller
{
    private function partnerId(Request $request): int
    {
        $id=(int)($request->user()?->logisticsPartner?->id ?? 0);
        abort_unless($id, 403); return $id;
    }
    public function index(Request $request): View
    {
        $partnerId=$this->partnerId($request);
        $centers=SortingCenter::where('logistics_partner_id',$partnerId)->where('status','active')
            ->whereNull('archived_at')->orderBy('name')->get();
        $links=RouteLink::with(['fromCenter','toCenter'])->where('logistics_partner_id',$partnerId)
            ->latest()->paginate(30);
        return view('logistics.route-network.index', compact('centers','links'));
    }
    public function store(Request $request): RedirectResponse
    {
        $partnerId=$this->partnerId($request);
        $data=$request->validate([
            'from_center_id'=>['required','integer'], 'to_center_id'=>['required','integer','different:from_center_id'],
            'mode'=>['required',Rule::in(['road','sea','air'])],
            'notes'=>['required','string','min:12','max:500'],
        ]);
        foreach (['from_center_id','to_center_id'] as $field) {
            abort_unless(SortingCenter::where('logistics_partner_id',$partnerId)
                ->where('status','active')->whereNull('archived_at')->whereKey($data[$field])->exists(),422);
        }
        DB::transaction(function () use ($partnerId,$data) {
            $link=RouteLink::where('logistics_partner_id',$partnerId)
                ->where('from_center_id',$data['from_center_id'])
                ->where('to_center_id',$data['to_center_id'])->lockForUpdate()->first();
            if ($link) {
                $activeRoadTrip=\App\Models\Logistics\LinehaulTrip::where('logistics_partner_id',$partnerId)
                    ->where('from_sorting_center_id',$data['from_center_id'])
                    ->where('to_sorting_center_id',$data['to_center_id'])
                    ->whereIn('status',['scheduled','in_transit','arrived'])->exists();
                if ($link->status === 'verified' || $activeRoadTrip || \App\Models\Logistics\InterislandCargoTransfer::where('route_link_id',$link->id)->exists()) {
                    throw ValidationException::withMessages(['route'=>'Cannot replace a verified or historically used link, or a link with active road trips. Suspend/reconcile affected operations first.']);
                }
                $link->update(['mode'=>$data['mode'],'notes'=>$data['notes'],
                    'status'=>'proposed','verified_at'=>null,'verified_by_user_id'=>null]);
                return;
            }
            RouteLink::create(['logistics_partner_id'=>$partnerId,
                'from_center_id'=>$data['from_center_id'],'to_center_id'=>$data['to_center_id'],
                'mode'=>$data['mode'],'notes'=>$data['notes'],'status'=>'proposed']);
        },3);
        return back()->with('status','Connection proposed. Verify the physical carrier/transport arrangement before dispatch.');
    }
    public function verify(Request $request, int $link): RedirectResponse
    {
        $id=$this->partnerId($request);
        $request->validate(['confirmation'=>['required',Rule::in(['VERIFY_ACTUAL_TRANSPORT'])]]);
        DB::transaction(function() use($link,$id,$request) {
            $edge=RouteLink::where('logistics_partner_id',$id)->lockForUpdate()->findOrFail($link);
            abort_unless($edge->fromCenter?->status==='active' && $edge->toCenter?->status==='active',422);
            $islands=app(PhilippineIslandGroup::class);
            if ($edge->fromCenter?->type === 'province_hub' && $edge->toCenter?->type === 'province_hub' &&
                (! $islands->forCenter($edge->fromCenter) || ! $islands->forCenter($edge->toCenter))) {
                throw ValidationException::withMessages(['route'=>'Cannot verify hub transport: source/destination PSGC region not recognized.']);
            }
            if ($islands->isCrossGroup($edge->fromCenter, $edge->toCenter) && $edge->mode === 'road') {
                throw ValidationException::withMessages(['route'=>'Luzon/Visayas/Mindanao cross-group leg cannot be ROAD only. Configure a Sea/Air connection or route through verified transport gateways.']);
            }
            $edge->update(['status'=>'verified','verified_at'=>now(),'verified_by_user_id'=>$request->user()->id]);
        });
        return back()->with('status','Transport connection verified for route preview. Manifest workflow still requires separate checks.');
    }
    public function suspend(Request $request, int $link): RedirectResponse
    {
        $id=$this->partnerId($request);
        RouteLink::where('logistics_partner_id',$id)->whereKey($link)->update(['status'=>'suspended','verified_at'=>null]);
        return back()->with('status','Route connection suspended.');
    }
}
