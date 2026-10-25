<?php
namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Finance\CenterRevenueLedgerEntry;
use App\Models\Finance\LinehaulDriverEarning;
use App\Models\Finance\LogisticsOrderAllocation;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\SortingCenter;
use App\Services\ShopHopFinance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $ledger = CenterRevenueLedgerEntry::where('logistics_partner_id', $partnerId)
                ->where('sorting_center_id', $branch->id);
            return [
                'name' => $branch->name,
                'code' => $branch->code,
                'type' => $branch->type,
                'handled_orders' => (clone $ledger)->distinct('order_id')->count('order_id'),
                'allocated_revenue' => (float) (clone $ledger)->sum('amount'),
                'scans' => DB::table('parcel_scan_events')->where('sorting_center_id', $branch->id)->count(),
            ];
        });

        $allocations = LogisticsOrderAllocation::where('logistics_partner_id', $partnerId);
        $linehaulQuery = LinehaulDriverEarning::with(['driver', 'trip.fromCenter', 'trip.toCenter'])
            ->where('logistics_partner_id', $partnerId);

        return view('logistics.earnings', [
            'branchActivity' => $branchActivity,
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'available' => (clone $query)->where('status', 'available')->sum('amount'),
            'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            'entries' => $query->orderByDesc('id')->paginate(25),
            'riderEntries' => SettlementEntry::whereIn('order_id', $orderIds)
                ->whereIn('kind', ['pickup_rider', 'delivery_rider'])
                ->orderByDesc('id')->paginate(25, ['*'], 'rider_page'),
            'allocationTotals' => [
                'gross_shipping' => (float) (clone $allocations)->sum('gross_shipping'),
                'pickup_riders' => (float) (clone $allocations)->sum('pickup_rider_fee'),
                'delivery_riders' => (float) (clone $allocations)->sum('delivery_rider_fee'),
                'branches' => (float) ((clone $allocations)->selectRaw('COALESCE(SUM(origin_branch_amount + destination_branch_amount), 0) AS total')->value('total') ?? 0),
                'hubs' => (float) (clone $allocations)->sum('province_hub_amount'),
                'linehaul_pool' => (float) (clone $allocations)->sum('linehaul_pool'),
                'margin' => (float) (clone $allocations)->sum('logistics_margin'),
            ],
            'linehaulPending' => (float) (clone $linehaulQuery)->where('status', 'pending')->sum('amount'),
            'linehaulAvailable' => (float) (clone $linehaulQuery)->where('status', 'available')->sum('amount'),
            'linehaulPaid' => (float) (clone $linehaulQuery)->where('status', 'paid')->sum('amount'),
            'linehaulEntries' => $linehaulQuery->orderByDesc('id')->paginate(25, ['*'], 'linehaul_page'),
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

    public function payLinehaulDriver(Request $request, int $earning)
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:120']]);
        $partnerId = (int) $request->user()->logisticsPartner?->id;
        abort_unless($partnerId, 403);

        DB::transaction(function () use ($earning, $partnerId, $request, $data) {
            $entry = LinehaulDriverEarning::whereKey($earning)
                ->where('logistics_partner_id', $partnerId)
                ->lockForUpdate()->firstOrFail();
            if ($entry->status !== 'available' || ! $entry->trip || $entry->trip->status !== \App\Models\Logistics\LinehaulTrip::COMPLETED) {
                throw ValidationException::withMessages(['earning' => 'Only available earnings from a completed Linehaul Trip can be marked paid.']);
            }
            $entry->update([
                'status' => 'paid',
                'payment_reference' => trim($data['reference']),
                'paid_at' => now(),
                'paid_by_user_id' => (int) $request->user()->id,
            ]);
        });

        return back()->with('status', 'Linehaul Driver payout recorded.');
    }
}
