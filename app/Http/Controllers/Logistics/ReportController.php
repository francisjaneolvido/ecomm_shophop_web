<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        // TODO: replace with a real aggregation over the $from – $to range,
        // scoped to the logged-in Logistics Partner.

        $summary = [
            'total_deliveries' => 4812,
            'on_time_rate' => 96.4,
            'gross_fees' => 361900,
            'commission' => 36190,
        ];

        $riders = [
            ['name' => 'Miguel Reyes', 'deliveries' => 214, 'on_time' => 98, 'earnings' => 16050, 'status' => 'paid'],
            ['name' => 'Angela Cruz', 'deliveries' => 189, 'on_time' => 97, 'earnings' => 14175, 'status' => 'paid'],
            ['name' => 'Jerome Delos Santos', 'deliveries' => 171, 'on_time' => 94, 'earnings' => 12825, 'status' => 'pending'],
        ];

        return view('logistics.reports', compact('from', 'to', 'summary', 'riders'));
    }

    public function export(Request $request): Response
    {
        // TODO: stream a CSV built from the same rows shown in index(),
        // filtered by $request->date('from') / $request->date('to').
        abort(501, 'CSV export not implemented yet.');
    }
}
