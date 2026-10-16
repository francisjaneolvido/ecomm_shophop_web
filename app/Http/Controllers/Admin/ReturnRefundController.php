<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ReturnCase;
use App\Services\ParcelReturnService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnRefundController extends Controller
{
    public function index(): View
    {
        $cases=ReturnCase::with(['order.seller','order.buyer','delivery','returnRider'])
            ->latest()->paginate(30);
        return view('admin.finance.returns',compact('cases'));
    }
    public function paid(Request $request,int $case,ParcelReturnService $returns): RedirectResponse
    {
        $data=$request->validate(['reference'=>['required','string','min:4','max:120']]);
        $returns->recordRefund($case,(int)$request->user()->id,$data['reference']);
        return back()->with('status','Manual demo refund recorded. No actual money was transferred by ShopHop.');
    }
}
