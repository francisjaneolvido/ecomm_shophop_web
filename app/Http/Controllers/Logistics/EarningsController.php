<?php
namespace App\Http\Controllers\Logistics;
use App\Http\Controllers\Controller;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\SortingCenter;
use App\Models\Buyer\Order\Order;
use Illuminate\Support\Facades\DB;
use App\Services\ShopHopFinance;
use Illuminate\Http\Request;
class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = (int) $request->user()->logisticsPartner?->id;
        abort_unless($partnerId, 403);
        $query = SettlementEntry::where('kind', 'logistics')->where('payee_id', $partnerId);
        $orderIds = (clone $query)->pluck('order_id');
        $branches = SortingCenter::where('logistics_partner_id', $partnerId)
            ->orderByDesc('is_main')->orderBy('name')->get();
        $branchActivity = $branches->map(function ($branch) use ($partnerId) {
            $totals = DB::table('deliveries')
                ->join('orders', 'orders.id', '=', 'deliveries.order_id')
                ->join('order_financials', 'order_financials.order_id', '=', 'orders.id')
                ->where('deliveries.logistics_partner_id', $partnerId)
                ->where('deliveries.destination_sorting_center_id', $branch->id)
                ->where('deliveries.status', \App\Models\Logistics\Delivery::DELIVERED)
                ->where('orders.status', Order::STATUS_COMPLETED)
                ->selectRaw('COUNT(*) as completed_orders, COALESCE(SUM(order_financials.logistics_earnings),0) as allocated_shipping')
                ->first();
            return [
                'name' => $branch->name,
                'code' => $branch->code,
                'completed_orders' => (int) ($totals->completed_orders ?? 0),
                'shipping_attributed' => (float) ($totals->allocated_shipping ?? 0),
                'scans' => DB::table('parcel_scan_events')->where('sorting_center_id', $branch->id)->count(),
            ];
        });
        return view('logistics.earnings', [
            'branchActivity' => $branchActivity,
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            'entries' => $query->orderByDesc('id')->paginate(25),
            'riderEntries' => SettlementEntry::whereIn('order_id', $orderIds)
                ->where(function ($q) { $q->whereIn('kind', ['pickup_rider', 'delivery_rider'])->orWhere('kind', 'like', 'transit_%'); })
                ->orderByDesc('id')->paginate(25, ['*'], 'rider_page'),
        ]);
    }
    public function payRider(Request $request, int $entry, ShopHopFinance $finance)
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:120']]);
        $partnerId = (int) $request->user()->logisticsPartner?->id;
        abort_unless($partnerId, 403);
        $finance->markRiderPaid($entry, $partnerId, (int) $request->user()->id, trim($data['reference']));
        return back()->with('status', 'Rider payout recorded. No external transfer was made.');
    }
}
