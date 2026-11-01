<?php
namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\PickupRiderAccrual;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ReturnCase;

/** Records a non-withdrawable rider fee at the confirmed FIRST-MILE branch handoff. */
class PickupRiderAccrualService
{
    public function recordAtVerifiedReceipt(Delivery $delivery, int $centerId): void
    {
        if ((int)$delivery->pickup_rider_id <= 0 || (int)$delivery->origin_sorting_center_id !== $centerId) return;
        if ($delivery->status !== Delivery::AT_SORTING_CENTER) return;
        // Source is a frozen order financial snapshot, never live COD money.
        $financial = OrderFinancial::query()->where('order_id',$delivery->order_id)->first();
        if (!$financial || $financial->settlement_hold) return;
        $order = Order::find($delivery->order_id);
        if (!$order || !$order->isPaymentEligible() || $order->status !== Order::STATUS_TO_RECEIVE) return;
        $fee = round(min(max(0,(float)$financial->logistics_earnings),
            max(0,(float)MarketplaceRateSetting::current()->pickup_rider_fee)),2);
        if ($fee <= 0) return;
        PickupRiderAccrual::firstOrCreate(['order_id'=>$delivery->order_id], [
            'delivery_id'=>$delivery->id,'logistics_partner_id'=>$delivery->logistics_partner_id,
            'rider_id'=>$delivery->pickup_rider_id,'received_center_id'=>$centerId,
            'amount'=>$fee,'status'=>'pending_release','earned_at'=>now(),
        ]);
    }

    /** Do not double count accruals that already have a formal settlement ledger entry. */
    public function unpaidPreview(int $partnerId, ?int $riderId=null): array
    {
        $query=PickupRiderAccrual::with('order')->where('logistics_partner_id',$partnerId)
            ->whereNotIn('order_id', SettlementEntry::query()->where('kind','pickup_rider')->select('order_id'));
        if ($riderId !== null) $query->where('rider_id',$riderId);
        $entries=$query->latest('id')->limit(100)->get()->map(function ($a) {
            $order=$a->order;
            $hold=!$order || !$order->isPaymentEligible() || $order->status === Order::STATUS_CANCELLED
                || $order->status === Order::STATUS_RETURNED
                || (bool)ReturnCase::where('order_id',$a->order_id)->exists();
            return ['order_id'=>$a->order_id,'rider_id'=>$a->rider_id,
                'amount'=>round((float)$a->amount,2),'status'=>$hold?'on_hold':'pending_release',
                'earned_at'=>$a->earned_at?->toIso8601String()];
        });
        return ['total_pending_release'=>round((float)$entries->where('status','pending_release')->sum('amount'),2),
            'total_on_hold'=>round((float)$entries->where('status','on_hold')->sum('amount'),2),
            'entries'=>$entries->values()->all()];
    }
}
