<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $partner = LogisticsPartner::with('coverageAreas')
            ->where('user_id', $request->user()->id)
            ->first() ?? abort(403, 'Logistics profile unavailable.');

        // Every query below is scoped to the currently authenticated Logistics partner.
        $counts = Delivery::where('logistics_partner_id', $partner->id)
            ->selectRaw('status, COUNT(*) total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pickupRequests = DeliveryController::readyFor($partner)->count();
        $activeCenters = SortingCenter::where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->count();
        $activeRiders = Rider::where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->count();
        $pendingRiderReviews = Rider::where('logistics_partner_id', $partner->id)
            ->where('status', 'pending')->whereNotNull('email_verified_at')->count();

        // Retain the existing stats contract for any other code that uses this view data.
        $stats = [
            ['label' => 'Pickup requests', 'value' => $pickupRequests],
            ['label' => 'Pickup assigned', 'value' => ($counts[Delivery::PICKUP_ASSIGNED] ?? 0) + ($counts[Delivery::PICKUP_ACCEPTED] ?? 0)],
            ['label' => 'Incoming to sorting', 'value' => $counts[Delivery::PICKED_UP] ?? 0],
            ['label' => 'At sorting center', 'value' => $counts[Delivery::AT_SORTING_CENTER] ?? 0],
            ['label' => 'Sorted / awaiting Rider', 'value' => $counts[Delivery::SORTED] ?? 0],
            ['label' => 'Out for delivery', 'value' => $counts[Delivery::OUT_FOR_DELIVERY] ?? 0],
            ['label' => 'Delivery failed', 'value' => $counts[Delivery::DELIVERY_FAILED] ?? 0],
            ['label' => 'Delivered', 'value' => $counts[Delivery::DELIVERED] ?? 0],
            ['label' => 'Active Sorting Centers', 'value' => $activeCenters],
            ['label' => 'Active Riders', 'value' => $activeRiders],
        ];

        $overview = [
            'pickup_requests' => $pickupRequests,
            'pickup_assigned' => (int) ($counts[Delivery::PICKUP_ASSIGNED] ?? 0) + (int) ($counts[Delivery::PICKUP_ACCEPTED] ?? 0),
            'inbound' => (int) ($counts[Delivery::PICKED_UP] ?? 0),
            'at_center' => (int) ($counts[Delivery::AT_SORTING_CENTER] ?? 0),
            'sorted' => (int) ($counts[Delivery::SORTED] ?? 0),
            'delivery_assigned' => (int) ($counts[Delivery::DELIVERY_ASSIGNED] ?? 0),
            'out_for_delivery' => (int) ($counts[Delivery::OUT_FOR_DELIVERY] ?? 0),
            'failed' => (int) ($counts[Delivery::DELIVERY_FAILED] ?? 0),
            'delivered' => (int) ($counts[Delivery::DELIVERED] ?? 0),
            'centers' => $activeCenters,
            'riders' => $activeRiders,
            'rider_reviews' => $pendingRiderReviews,
            'today_delivered' => Delivery::where('logistics_partner_id', $partner->id)
                ->whereDate('delivered_at', now()->toDateString())->count(),
        ];

        $recentDeliveries = Delivery::with(['order.seller', 'rider', 'transfers.toCenter'])
            ->where('logistics_partner_id', $partner->id)
            ->orderByDesc('updated_at')->limit(6)->get();

        return view('logistics.dashboard.index', compact('partner', 'stats', 'overview', 'recentDeliveries'));
    }
}
