<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $center = $request->user()->sortingCenter;
        $status = $request->query('status', 'in_transit');

        // TODO: palitan ng totoong query sa ParcelTransfer, naka-scope sa $center->id.
        // Sample data pa lang ito.
        return view('branch.transfers.index', $this->sample($status) + [
            'center' => $center,
            'status' => $status,
        ]);
    }

    public function receive(Request $request, int $transfer)
    {
        $data = $request->validate([
            'received_count' => ['required', 'integer', 'min:0'],
            'parcel_ids'     => ['nullable', 'array'],
            'parcel_ids.*'   => ['string', 'max:100'],
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        // TODO (kapag nakita ko na ang ParcelTransfer model/migration):
        // 1. Hanapin ang transfer na sa branch na ito lang ($request->user()->sortingCenter).
        // 2. Siguraduhing in_transit pa ang status nito.
        // 3. Kung received_count != expected -> status 'discrepancy' at kailangan ang 'note'.
        // 4. Kung tugma -> status 'received', i-update ang parcels sa 'at_branch'.
        // 5. Magtala ng parcel_events (sino, kailan, ilan).

        return back()->with('status', 'Transfer received (sample).');
    }

    /**
     * Para sa /preview/branch/transfers (walang login).
     */
    public function preview(Request $request)
    {
        $status = $request->query('status', 'in_transit');

        return view('branch.transfers.index', $this->sample($status) + ['status' => $status]);
    }

    private function sample(string $status): array
    {
        $all = [
            [
                'id' => 1, 'code' => 'TR-0012', 'from' => 'Main hub', 'parcels' => 3,
                'sent_at' => '2 hrs ago', 'status' => 'in_transit',
                'items' => [
                    ['tracking' => 'SH-100234', 'receiver' => 'Juan Dela Cruz', 'barangay' => 'Poblacion', 'cod' => 450],
                    ['tracking' => 'SH-100235', 'receiver' => 'Maria Santos', 'barangay' => 'San Pablo Norte', 'cod' => null],
                    ['tracking' => 'SH-100236', 'receiver' => 'Pedro Reyes', 'barangay' => 'Santo Angel', 'cod' => 1200],
                ],
            ],
            [
                'id' => 2, 'code' => 'TR-0013', 'from' => 'Main hub', 'parcels' => 8,
                'sent_at' => '30 mins ago', 'status' => 'in_transit', 'items' => [],
            ],
            [
                'id' => 3, 'code' => 'TR-0011', 'from' => 'Main hub', 'parcels' => 20,
                'sent_at' => 'Yesterday', 'status' => 'received',
                'received_count' => 20, 'received_at' => 'Today, 8:15 AM',
            ],
            [
                'id' => 4, 'code' => 'TR-0010', 'from' => 'Main hub', 'parcels' => 15,
                'sent_at' => '2 days ago', 'status' => 'discrepancy',
                'received_count' => 13, 'received_at' => 'Yesterday, 4:40 PM',
                'note' => '2 parcels ang hindi dumating, naka-report na sa main hub.',
            ],
        ];

        $list = $status === 'all'
            ? $all
            : array_values(array_filter($all, fn ($t) => $t['status'] === $status));

        return [
            'transfers' => $list,
            'counts' => ['in_transit' => 2, 'received_today' => 1, 'discrepancy' => 1],
        ];
    }
}