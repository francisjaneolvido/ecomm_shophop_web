<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileRiderCodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        $cash = CodSettlement::where('collected_by_rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->orderByDesc('id')->limit(100)->get();
        return response()->json(['success' => true, 'status' => 'ok',
            'data' => ['collections' => $cash->map(fn ($row) => [
                'delivery_id' => $row->delivery_id,
                'order_id' => $row->order_id,
                'amount' => (string) $row->collected_amount,
                'status' => $row->status,
                'collected_at' => $row->collected_at?->toIso8601String(),
                'remitted_at' => $row->remitted_at?->toIso8601String(),
                'can_remit' => $row->status === CodSettlement::COLLECTED && ! $row->remitted_at,
            ])->values()]]);
    }

    public function remit(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->rider($request);
        return DB::transaction(function () use ($rider, $delivery) {
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('collected_by_rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)
                ->lockForUpdate()->firstOrFail();
            if ($cash->status !== CodSettlement::COLLECTED || $cash->remitted_at) {
                return response()->json(['success' => false, 'status' => 'stale',
                    'message' => 'Already remitted. Refresh your COD list.'], 409);
            }
            if (CodSettlement::whereKey($cash->id)->where('status', CodSettlement::COLLECTED)
                ->whereNull('remitted_at')->update([
                    'status' => CodSettlement::REMITTED,
                    'remitted_by_rider_id' => $rider->id,
                    'remitted_at' => now(),
                ]) !== 1) {
                return response()->json(['success' => false, 'status' => 'stale',
                    'message' => 'COD status changed; refresh first.'], 409);
            }
            return response()->json(['success' => true, 'status' => 'ok',
                'message' => 'Rider cash handover recorded. Logistics must confirm actual receipt.']);
        });
    }

    private function rider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403);
        $rider = $actor->fresh(['partner.user']);
        // Suspended Riders retain the ability to return previously collected cash.
        abort_unless($rider && in_array($rider->status, ['active','suspended'], true)
            && $rider->partner?->user?->status === 'approved', 403);
        return $rider;
    }
}
