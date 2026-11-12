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
        $status = in_array($request->query('status'), ['pending', 'available', 'paid'], true) ? $request->query('status') : 'all';
        $counts = (clone $query)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status');
        return view('seller.earnings', [
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'available' => (clone $query)->where('status', 'available')->sum('amount'),
            'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            'status' => $status,
            'counts' => $counts,
            'entries' => $query->when($status !== 'all', fn ($q) => $q->where('status', $status))->orderByDesc('id')->paginate(25)->withQueryString(),
        ]);
    }
}
