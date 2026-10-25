<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\SortingCenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $center = SortingCenter::where('user_id', $request->user()->id)
            ->where('status', 'active')->whereNull('archived_at')->first();
        abort_unless($center, 403, 'Walang aktibong branch na naka-link sa account mo.');

        // Papunta sa branch: (a) pickup na dala ng rider, (b) transfers na umalis na
        $pickedUp = Delivery::with('order.seller')
            ->where('origin_sorting_center_id', $center->id)
            ->where('status', Delivery::PICKED_UP);

        $transfersIn = ParcelTransfer::with(['delivery', 'fromCenter', 'linehaulTrip'])
            ->where('to_sorting_center_id', $center->id)
            ->where('status', ParcelTransfer::IN_TRANSIT)
            ->where(function ($q) {
                $q->whereHas('linehaulTrip', fn ($trip) => $trip->whereIn('status', ['arrived', 'completed']))
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('linehaul_trip_id')
                            ->where(fn ($legacyState) => $legacyState
                                ->whereNull('transit_rider_id')
                                ->orWhereNotNull('departed_at'));
                    });
            });

        $awaiting = (clone $pickedUp)->latest('picked_up_at')->limit(10)->get()->map(fn ($d) => [
            'tracking' => $d->tracking_code,
            'label'    => 'Pickup mula sa seller',
            'from'     => $d->order?->seller?->business_name ?? 'Seller',
            'when'     => $d->picked_up_at?->diffForHumans() ?? '—',
        ])->concat(
            (clone $transfersIn)->latest('dispatched_at')->limit(10)->get()->map(fn ($t) => [
                'tracking' => $t->delivery?->tracking_code,
                'label'    => 'Transfer',
                'from'     => $t->fromCenter?->name ?? 'Hub',
                'when'     => $t->dispatched_at?->diffForHumans() ?? '—',
            ])
        )->filter(fn ($a) => $a['tracking'])->values();

        $pendingPickups = PickupRequest::where('origin_sorting_center_id', $center->id)
            ->where('status', PickupRequest::REQUESTED)->count();

        $atBranch = Delivery::where('current_sorting_center_id', $center->id)
            ->whereIn('status', [Delivery::AT_SORTING_CENTER, Delivery::SORTED])->count();

        $toAssignQuery = Delivery::with('order')
            ->where('destination_sorting_center_id', $center->id)
            ->where('current_sorting_center_id', $center->id)
            ->whereIn('status', [Delivery::SORTED, Delivery::DELIVERY_FAILED]);

        $outForDelivery = Delivery::where('destination_sorting_center_id', $center->id)
            ->whereIn('status', [Delivery::DELIVERY_ASSIGNED, Delivery::OUT_FOR_DELIVERY])->count();

        $deliveredToday = Delivery::where('destination_sorting_center_id', $center->id)
            ->where('status', Delivery::DELIVERED)->whereDate('delivered_at', today())->count();

        $failed = Delivery::where('destination_sorting_center_id', $center->id)
            ->where('status', Delivery::DELIVERY_FAILED)->count();

        $stats = [
            'pickups'          => $pendingPickups,
            'to_receive'       => (clone $pickedUp)->count() + (clone $transfersIn)->count(),
            'at_branch'        => $atBranch,
            'to_assign'        => (clone $toAssignQuery)->count(),
            'out_for_delivery' => $outForDelivery,
            'delivered_today'  => $deliveredToday,
            'failed'           => $failed,
            'cod_with_riders'  => 0, // saka na kapag ginawa na ang rider/COD part
        ];

        $toAssign = $toAssignQuery->latest('updated_at')->limit(5)->get()->map(fn ($d) => [
            'tracking' => $d->tracking_code,
            'receiver' => $d->order?->delivery_name,
            'barangay' => $d->order?->delivery_barangay_name ?: $d->order?->delivery_address,
            'cod'      => $d->order && $d->order->payment_method === 'cod' ? (float) $d->order->total_amount : null,
        ]);

        $riders = []; // saka na

        $alerts = [];
        if ($failed > 0) {
            $alerts[] = ['level' => 'warning', 'title' => "{$failed} parcel(s) failed delivery", 'detail' => 'Kailangan ng retry o return.'];
        }
        if ($pendingPickups > 0) {
            $alerts[] = ['level' => 'info', 'title' => "{$pendingPickups} pickup request(s) naghihintay ng rider", 'detail' => 'I-assign sa Logistics board.'];
        }

        return view('branch.dashboard.index', compact('center', 'stats', 'awaiting', 'toAssign', 'riders', 'alerts'));
    }
}