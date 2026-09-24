<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\LogisticsPartner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // The approved profile owns all counters; former sample revenue and performance figures have no source.
        $partner = LogisticsPartner::with('coverageAreas')->where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
        $counts = Delivery::where('logistics_partner_id', $partner->id)
            ->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');
        $stats = [
            ['label' => 'Ready in coverage', 'value' => DeliveryController::readyFor($partner)->count()],
            ['label' => 'Assigned', 'value' => $counts['assigned'] ?? 0],
            ['label' => 'Active delivery', 'value' => ($counts['picked_up'] ?? 0) + ($counts['in_transit'] ?? 0)],
            ['label' => 'Delivered', 'value' => $counts['delivered'] ?? 0],
            ['label' => 'Active Riders', 'value' => Rider::where('logistics_partner_id', $partner->id)->where('status', 'active')->count()],
        ];

        return view('logistics.dashboard', compact('stats'));
    }
}
