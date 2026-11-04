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
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        $statuses = [Delivery::PICKUP_ASSIGNED,Delivery::PICKUP_ACCEPTED,Delivery::PICKED_UP,
            Delivery::AT_SORTING_CENTER,Delivery::SORTED,Delivery::DELIVERY_ASSIGNED,
            Delivery::OUT_FOR_DELIVERY,Delivery::DELIVERY_FAILED,Delivery::DELIVERED,
            Delivery::RETURN_REQUESTED,Delivery::RETURN_AT_BRANCH,Delivery::RETURN_ASSIGNED,
            Delivery::RETURN_IN_TRANSIT,Delivery::RETURN_AWAITING_SELLER,Delivery::RETURNED];
        $selectedStatus = $data['status'] ?? '';
        if (! in_array($selectedStatus, $statuses, true)) $selectedStatus = '';
        $search = trim($data['q'] ?? '');
        $base = Delivery::where('logistics_partner_id', $partner->id);
        if (! empty($data['from'])) $base->whereDate('assigned_at', '>=', $data['from']);
        if (! empty($data['to'])) $base->whereDate('assigned_at', '<=', $data['to']);
        // KPI counts represent every record in the selected date range, not only the displayed page.
        $counts = (clone $base)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $total = $counts->sum();
        if ($selectedStatus !== '') $base->where('status', $selectedStatus);
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', '%'.$search.'%')
                    ->orWhereHas('rider', fn ($r) => $r->where('name', 'like', '%'.$search.'%'));
                if (ctype_digit($search)) $q->orWhere('order_id', (int) $search);
            });
        }
        $deliveries = $base->with(['order','rider','originSortingCenter','destinationSortingCenter'])
            ->latest()->paginate(25)->withQueryString();
        return view('logistics.reports.index', compact('deliveries','counts','data','statuses','selectedStatus','search','total'));
    }

    /** CSV is an actual export of partner-owned delivery facts, never a synthetic report. */
    public function exportCsv(Request $request): Response
    {
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'from' => ['nullable','date'], 'to' => ['nullable','date','after_or_equal:from'],
            'q' => ['nullable','string','max:100'], 'status' => ['nullable','string','max:40'],
        ]);
        $query = Delivery::where('logistics_partner_id', $partner->id);
        if (! empty($data['from'])) $query->whereDate('assigned_at', '>=', $data['from']);
        if (! empty($data['to'])) $query->whereDate('assigned_at', '<=', $data['to']);
        $validStatuses = [Delivery::PICKUP_ASSIGNED,Delivery::PICKUP_ACCEPTED,Delivery::PICKED_UP,
            Delivery::AT_SORTING_CENTER,Delivery::SORTED,Delivery::DELIVERY_ASSIGNED,
            Delivery::OUT_FOR_DELIVERY,Delivery::DELIVERY_FAILED,Delivery::DELIVERED,
            Delivery::RETURN_REQUESTED,Delivery::RETURN_AT_BRANCH,Delivery::RETURN_ASSIGNED,
            Delivery::RETURN_IN_TRANSIT,Delivery::RETURN_AWAITING_SELLER,Delivery::RETURNED];
        if (in_array($data['status'] ?? '', $validStatuses, true)) $query->where('status', $data['status']);
        $search = trim($data['q'] ?? '');
        if ($search !== '') $query->where(function ($q) use ($search) {
            $q->where('tracking_code','like','%'.$search.'%')
                ->orWhereHas('rider', fn ($r) => $r->where('name','like','%'.$search.'%'));
            if (ctype_digit($search)) $q->orWhere('order_id',(int)$search);
        });
        $filename = 'shophop-deliveries-'.now()->format('Ymd-His').'.csv';
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Order', 'Tracking', 'Status', 'Rider', 'Origin', 'Destination', 'Assigned At', 'Delivered At']);
            // Escape values starting with spreadsheet formula operators to avoid CSV injection.
            $safe = static function ($value) {
                $value = (string) ($value ?? '');
                return preg_match('/^[\x00-\x20]*[=+@\-]/u', $value) ? "'".$value : $value;
            };
            foreach ($query->with(['rider','originSortingCenter','destinationSortingCenter'])->lazyById(300) as $delivery) {
                fputcsv($out, array_map($safe, [
                    $delivery->order_id, $delivery->tracking_code, $delivery->status,
                    $delivery->rider?->name, $delivery->originSortingCenter?->name,
                    $delivery->destinationSortingCenter?->name,
                    $delivery->assigned_at?->format('Y-m-d H:i:s'),
                    $delivery->delivered_at?->format('Y-m-d H:i:s'),
                ]));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
