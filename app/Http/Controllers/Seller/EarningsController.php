<?php
namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use App\Models\Finance\SettlementEntry;
use Illuminate\Http\Request;
class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;
        abort_unless($seller, 403);
        $query = SettlementEntry::with('order')->where('kind', 'seller')->where('payee_id', $seller->id);
        return view('seller.earnings', [
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            'entries' => $query->orderByDesc('id')->paginate(25),
        ]);
    }
}
