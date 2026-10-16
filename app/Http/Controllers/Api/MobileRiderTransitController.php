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
        $rider = $this->rider($request);
        $transfers = ParcelTransfer::with(['delivery.order', 'fromCenter', 'toCenter'])
            ->where('transit_rider_id', $rider->id)
            ->whereHas('delivery', fn ($query) => $query->where('logistics_partner_id', $rider->logistics_partner_id))
            ->latest()->limit(100)->get();
        return response()->json(['success' => true, 'status' => 'ok', 'message' => 'Transit assignments loaded.', 'data' => [
            'transfers' => $transfers->map(fn ($transfer) => [
                'id' => $transfer->id,
                'tracking_code' => $transfer->delivery?->tracking_code,
                'status' => $transfer->status,
                'departed_at' => optional($transfer->departed_at)?->toIso8601String(),
                'received_at' => optional($transfer->received_at)?->toIso8601String(),
                'from' => $transfer->fromCenter?->name,
                'to' => $transfer->toCenter?->name,
                'can_depart' => $transfer->status === ParcelTransfer::IN_TRANSIT && ! $transfer->departed_at,
            ])->values(),
        ]]);
    }

    public function depart(Request $request, int $transfer): JsonResponse
    {
        $rider = $this->rider($request);
        return DB::transaction(function () use ($rider, $transfer) {
            $job = ParcelTransfer::with('delivery.order')->where('transit_rider_id', $rider->id)
                ->whereKey($transfer)->lockForUpdate()->firstOrFail();
            $delivery = Delivery::where('logistics_partner_id', $rider->logistics_partner_id)
                ->whereKey($job->delivery_id)->lockForUpdate()->firstOrFail();
            if ($job->status !== ParcelTransfer::IN_TRANSIT || $job->departed_at
                || (int) $job->from_sorting_center_id !== (int) $rider->sorting_center_id
                || (int) $job->from_sorting_center_id !== (int) $delivery->current_sorting_center_id
                || $delivery->status !== Delivery::SORTED
                || $delivery->order?->status !== Order::STATUS_TO_RECEIVE) {
                return response()->json(['success' => false, 'status' => 'stale',
                    'message' => 'Transit assignment changed or is not ready. Refresh your tasks.'], 409);
            }
            $job->update(['departed_at' => now()]);
            return response()->json(['success' => true, 'status' => 'departed',
                'message' => 'Transit departure confirmed. Destination branch must scan the parcel on arrival.',
                'data' => ['transfer_id' => $job->id]]);
        });
    }

    public function earnings(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        $entries = SettlementEntry::where('payee_id', $rider->id)
            ->where(function ($query) { $query->whereIn('kind', ['pickup_rider', 'delivery_rider'])->orWhere('kind', 'like', 'transit_%'); })
            ->whereHas('order.delivery', fn ($query) => $query->where('logistics_partner_id', $rider->logistics_partner_id))
            ->latest()->limit(100)->get();
        return response()->json(['success' => true, 'status' => 'ok', 'message' => 'Rider earnings loaded.', 'data' => [
            'pending' => round((float) $entries->where('status', 'pending')->sum('amount'), 2),
            'paid' => round((float) $entries->where('status', 'paid')->sum('amount'), 2),
            'entries' => $entries->map(fn ($entry) => [
                'order_id' => $entry->order_id, 'kind' => $entry->kind, 'amount' => (float) $entry->amount,
                'status' => $entry->status, 'paid_at' => optional($entry->paid_at)?->toIso8601String(),
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
