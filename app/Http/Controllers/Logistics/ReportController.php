<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\LogisticsPartner;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        // Reports use partner-owned Delivery facts; no revenue, SLA, or Rider score can be inferred from them.
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $query = Delivery::with(['order', 'rider'])->where('logistics_partner_id', $partner->id);
        if (isset($data['from'])) {
            $query->whereDate('assigned_at', '>=', $data['from']);
        }
        if (isset($data['to'])) {
            $query->whereDate('assigned_at', '<=', $data['to']);
        }
        $deliveries = $query->latest()->get();
        $counts = $deliveries->countBy('status');

        return view('logistics.reports', compact('deliveries', 'counts', 'data'));
    }

    public function exportPdf(): Response
    {
        // An export engine and verified report format are absent; an explicit 501 avoids a fake PDF.
        abort(501, 'PDF report export is unavailable.');
    }

    public function exportRiderPdf(string $rider): Response
    {
        // Rider PDF remains unavailable even though delivery counts now persist.
        abort(501, 'Rider PDF export is unavailable.');
    }
}
