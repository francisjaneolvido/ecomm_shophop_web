<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // TODO: scope all of this to the logged-in Logistics Partner,
        // e.g. auth()->user()->logisticsPartner()->...

        $stats = [
            ['label' => 'Active riders', 'value' => 142, 'trend' => '+6 this week', 'tone' => 'up'],
            ['label' => 'Deliveries today', 'value' => 318, 'trend' => 'On track', 'tone' => 'up'],
            ['label' => 'In transit', 'value' => 57, 'trend' => 'Peak hour', 'tone' => 'warn'],
            ['label' => 'Pending applications', 'value' => 9, 'trend' => 'Needs review', 'tone' => 'warn'],
        ];

        $weeklyDeliveries = [
            ['label' => 'Mon', 'value' => 52],
            ['label' => 'Tue', 'value' => 68],
            ['label' => 'Wed', 'value' => 44],
            ['label' => 'Thu', 'value' => 78],
            ['label' => 'Fri', 'value' => 60],
            ['label' => 'Sat', 'value' => 90],
            ['label' => 'Sun', 'value' => 71],
        ];

        $topRiders = [
            ['name' => 'Miguel R.', 'deliveries' => 14],
            ['name' => 'Angela C.', 'deliveries' => 12],
            ['name' => 'Jerome D.', 'deliveries' => 11],
            ['name' => 'Kaye P.', 'deliveries' => 9],
        ];

        $pendingApplications = [
            ['name' => 'Miguel Reyes', 'vehicle' => 'Motorcycle · NGA-2231', 'complete' => true],
            ['name' => 'Angela Cruz', 'vehicle' => 'Motorcycle · KLM-8842', 'complete' => true],
            ['name' => 'Jerome Delos Santos', 'vehicle' => 'Tricycle · TRC-0917', 'complete' => false],
        ];

        return view('logistics.dashboard', compact(
            'stats',
            'weeklyDeliveries',
            'topRiders',
            'pendingApplications'
        ));
    }
}
