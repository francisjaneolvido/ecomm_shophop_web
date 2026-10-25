<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use App\Services\ParcelScanService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransferController extends Controller
{
    /**
     * Real incoming transfer board for the logged-in branch/hub.
     *
     * ParcelTransfer is one physical parcel. parcel_manifests groups those
     * transfers into the batch created by the sending branch/hub.
     */
    public function index(Request $request): View
    {
        $center = $this->center($request);
        $status = (string) $request->query('status', 'awaiting');

        if (! in_array($status, ['awaiting', 'preparing', 'received', 'all'], true)) {
            $status = 'awaiting';
        }

        $base = ParcelTransfer::query()
            ->with([
                'manifest',
                'delivery.order',
                'fromCenter',
                'toCenter',
                'transitRider',
                'linehaulTrip.vehicle',
                'linehaulTrip.driver',
            ])
            ->where('to_sorting_center_id', $center->id)
            ->whereHas('delivery', fn ($query) => $query
                ->where('logistics_partner_id', $center->logistics_partner_id));

        // New Linehaul transfers become receivable only after the assigned
        // Driver records arrival. Legacy Rider/company transfers remain readable.
        $receivable = fn ($query) => $query
            ->where('status', ParcelTransfer::IN_TRANSIT)
            ->where(function ($q) {
                $q->whereHas('linehaulTrip', fn ($trip) => $trip->whereIn('status', ['arrived', 'completed']))
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('linehaul_trip_id')
                            ->where(fn ($state) => $state
                                ->whereNull('transit_rider_id')
                                ->orWhereNotNull('departed_at'));
                    });
            });

        $preparing = fn ($query) => $query
            ->where('status', ParcelTransfer::IN_TRANSIT)
            ->where(function ($q) {
                $q->whereHas('linehaulTrip', fn ($trip) => $trip->whereIn('status', ['scheduled', 'in_transit']))
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('linehaul_trip_id')
                            ->whereNotNull('transit_rider_id')
                            ->whereNull('departed_at');
                    });
            });

        $counts = [
            'awaiting' => (clone $base)->where($receivable)->count(),
            'preparing' => (clone $base)->where($preparing)->count(),
            'received_today' => (clone $base)
                ->where('status', ParcelTransfer::RECEIVED)
                ->whereDate('received_at', today())
                ->count(),
            'all' => (clone $base)->count(),
        ];

        $transfers = clone $base;

        if ($status === 'awaiting') {
            $transfers->where($receivable);
        } elseif ($status === 'preparing') {
            $transfers->where($preparing);
        } elseif ($status === 'received') {
            $transfers->where('status', ParcelTransfer::RECEIVED);
        }

        $transfers = $transfers
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('branch.transfers.index', compact(
            'center',
            'status',
            'counts',
            'transfers'
        ));
    }

    /**
     * Receive one physical parcel at the logged-in branch/hub.
     *
     * This reuses ParcelScanService, the same source of truth used by the
     * mobile Logistics scanner, so Web and Mobile cannot apply different
     * hand-off rules.
     */
    public function scan(Request $request, ParcelScanService $scanner)
    {
        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:60'],
        ]);

        $center = $this->center($request);

        try {
            // Friendly validation first, then receive() revalidates again
            // inside its locked transaction before changing parcel state.
            $scanner->inspect(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                $data['tracking_code']
            );

            $result = $scanner->receive(
                (int) $center->logistics_partner_id,
                (int) $center->id,
                (int) $request->user()->id,
                $data['tracking_code']
            );
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        $message = $result['event'] === 'pickup_received'
            ? "Parcel {$result['tracking_code']} received from the pickup rider."
            : "Transfer parcel {$result['tracking_code']} received at {$result['branch']}.";

        return back()->with('status', $message);
    }

    private function center(Request $request): SortingCenter
    {
        $center = SortingCenter::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();

        abort_unless($center, 403, 'Walang aktibong branch na naka-link sa account mo.');

        return $center;
    }
}
