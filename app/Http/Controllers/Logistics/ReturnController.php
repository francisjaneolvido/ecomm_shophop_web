<?php
namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Delivery;
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
        $partner = $this->partner($request);
        $data = $request->validate(['q' => ['nullable','string','max:100'],
            'status' => ['nullable','in:requested,at_branch,assigned,in_transit,awaiting_seller,seller_received'],
            'exception_q' => ['nullable','string','max:100']]);
        $search = trim($data['q'] ?? '');
        $status = $data['status'] ?? '';
        $base = ReturnCase::where('logistics_partner_id', $partner->id);
        $summary = (clone $base)->selectRaw('status, COUNT(*) AS total')->groupBy('status')
            ->pluck('total', 'status');
        if ($status !== '') $base->where('status', $status);
        if ($search !== '') {
            $base->where(function ($query) use ($search) {
                $query->whereHas('delivery', fn ($d) => $d->where('tracking_code', 'like', '%'.$search.'%'))
                    ->orWhereHas('order.seller', fn ($s) => $s->where('business_name', 'like', '%'.$search.'%'));
                if (ctype_digit($search)) $query->orWhere('order_id', (int) $search)->orWhere('id', (int) $search);
            });
        }
        $cases = $base->with(['order.seller','delivery','branch','returnRider'])
            ->latest()->paginate(25)->withQueryString();
        // Exceptions are failed deliveries still awaiting a retry/return decision.
        $exceptionsBase = Delivery::where('logistics_partner_id', $partner->id)
            ->where('status', Delivery::DELIVERY_FAILED)
            ->whereDoesntHave('returnCase');
        $exceptionCount = (clone $exceptionsBase)->count();
        $exceptionSearch = trim($data['exception_q'] ?? '');
        if ($exceptionSearch !== '') {
            $exceptionsBase->where(function ($q) use ($exceptionSearch) {
                $q->where('tracking_code', 'like', '%'.$exceptionSearch.'%')
                    ->orWhereHas('order.seller', fn ($s) => $s->where('business_name', 'like', '%'.$exceptionSearch.'%'));
                if (ctype_digit($exceptionSearch)) $q->orWhere('order_id', (int) $exceptionSearch);
            });
        }
        $exceptions = $exceptionsBase->with(['order.seller','destinationSortingCenter'])
            ->latest('delivery_failed_at')->paginate(20, ['*'], 'exception_page')->withQueryString();
        $branches = SortingCenter::where('logistics_partner_id', $partner->id)
            ->where('status','active')->orderBy('name')->get();
        $riders = Rider::where('logistics_partner_id', $partner->id)->where('status','active')
            ->whereNotNull('email_verified_at')->orderBy('name')->get();
        return view('logistics.returns.index', compact('cases','branches','riders','summary','status','search','exceptions','exceptionCount','exceptionSearch'));
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
