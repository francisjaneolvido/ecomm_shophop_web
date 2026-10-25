<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileRiderTransitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->rider($request);

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Branch and Hub transfers are handled by Linehaul Drivers and fleet vehicles.',
            'data' => ['transfers' => []],
        ]);
    }

    public function depart(Request $request, int $transfer): JsonResponse
    {
        $this->rider($request);

        return response()->json([
            'success' => false,
            'status' => 'qr_required',
            'message' => 'Transit departure requires the Rider camera QR custody scan at the origin center.',
        ], 409);
    }

    public function earnings(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        $entries = SettlementEntry::where('payee_id', $rider->id)
            ->whereIn('kind', ['pickup_rider', 'delivery_rider'])
            ->whereHas('order.delivery', fn ($query) => $query->where('logistics_partner_id', $rider->logistics_partner_id))
            ->latest()->limit(100)->get();
        return response()->json(['success' => true, 'status' => 'ok', 'message' => 'Rider earnings loaded.', 'data' => [
            'pending' => round((float) $entries->where('status', 'pending')->sum('amount'), 2),
            'available' => round((float) $entries->where('status', 'available')->sum('amount'), 2),
            'paid' => round((float) $entries->where('status', 'paid')->sum('amount'), 2),
            'entries' => $entries->map(fn ($entry) => [
                'order_id' => $entry->order_id,
                'kind' => $entry->kind,
                'amount' => (float) $entry->amount,
                'status' => $entry->status,
                'distribution_reference' => $entry->distribution_reference,
                'payment_reference' => $entry->payment_reference,
                'distributed_at' => optional($entry->distributed_at)?->toIso8601String(),
                'paid_at' => optional($entry->paid_at)?->toIso8601String(),
            ])->values(),
        ]]);
    }

    private function rider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403, 'Rider login required.');
        $rider = $actor->fresh(['partner.user']);
        abort_unless($rider && $rider->status === 'active' && $rider->email_verified_at
            && $rider->partner?->user?->status === 'approved', 403, 'Active Rider required.');
        return $rider;
    }
}
