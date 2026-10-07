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
        $partner = LogisticsPartner::with('coverageAreas')->where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
        $counts = Delivery::where('logistics_partner_id', $partner->id)
            ->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');

        $stats = [
            ['label' => 'Pickup requests', 'value' => DeliveryController::readyFor($partner)->count()],
            ['label' => 'Pickup assigned', 'value' => ($counts[Delivery::PICKUP_ASSIGNED] ?? 0) + ($counts[Delivery::PICKUP_ACCEPTED] ?? 0)],
            ['label' => 'Incoming to sorting', 'value' => $counts[Delivery::PICKED_UP] ?? 0],
            ['label' => 'At sorting center', 'value' => $counts[Delivery::AT_SORTING_CENTER] ?? 0],
            ['label' => 'Sorted / awaiting Rider', 'value' => $counts[Delivery::SORTED] ?? 0],
            ['label' => 'Out for delivery', 'value' => $counts[Delivery::OUT_FOR_DELIVERY] ?? 0],
            ['label' => 'Delivery failed', 'value' => $counts[Delivery::DELIVERY_FAILED] ?? 0],
            ['label' => 'Delivered', 'value' => $counts[Delivery::DELIVERED] ?? 0],
            ['label' => 'Active Sorting Centers', 'value' => SortingCenter::where('logistics_partner_id', $partner->id)->where('status', 'active')->count()],
            ['label' => 'Active Riders', 'value' => Rider::where('logistics_partner_id', $partner->id)->where('status', 'active')->count()],
        ];

        return view('logistics.dashboard', compact('stats'));
    }
}
