<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\PickupRiderAccrual;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ReturnCase;

/** Records a non-withdrawable rider fee at the confirmed FIRST-MILE branch handoff. */
class PickupRiderAccrualService
{
    public function recordAtVerifiedReceipt(Delivery $delivery, int $centerId): void
    {
        app(PartnerCompensationService::class)->pickup($delivery, $centerId);
    }

    /** Do not double count accruals that already have a formal settlement ledger entry. */
    public function unpaidPreview(int $partnerId, ?int $riderId = null): array
    {
        $query = PickupRiderAccrual::with('order')->where('logistics_partner_id', $partnerId)
            ->whereNotIn('order_id', SettlementEntry::query()->where('kind', 'pickup_rider')->select('order_id'));
        if ($riderId !== null) {
            $query->where('rider_id', $riderId);
        }
        $entries = $query->latest('id')->limit(100)->get()->map(function ($a) {
            $order = $a->order;
            $hold = ! $order || ! $order->isPaymentEligible() || $order->status === Order::STATUS_CANCELLED
                || $order->status === Order::STATUS_RETURNED
                || (bool) ReturnCase::where('order_id', $a->order_id)->exists();

            return ['order_id' => $a->order_id, 'rider_id' => $a->rider_id,
                'amount' => round((float) $a->amount, 2), 'status' => $hold ? 'on_hold' : 'pending_release',
                'earned_at' => $a->earned_at?->toIso8601String()];
        });

        return ['total_pending_release' => round((float) $entries->where('status', 'pending_release')->sum('amount'), 2),
            'total_on_hold' => round((float) $entries->where('status', 'on_hold')->sum('amount'), 2),
            'entries' => $entries->values()->all()];
    }
}
