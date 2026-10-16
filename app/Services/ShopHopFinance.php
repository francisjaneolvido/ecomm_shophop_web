<?php
namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopHopFinance
{
    /** Pure ledger calculation. ShopHop funds platform discounts; seller/logistics remain whole. */
    public static function breakdown(
        float $merchandise, float $sellerDiscount, float $platformDiscount,
        float $shippingFee, float $shippingDiscount, float $codFee
    ): array {
        $sellerBase = max(0, round($merchandise - $sellerDiscount, 2));
        $commission = round($sellerBase * ((float) config('shophop_finance.commission_percent', 10) / 100), 2);
        return [
            'commission' => $commission,
            'seller_earnings' => round($sellerBase - $commission, 2),
            'logistics_earnings' => round($shippingFee, 2),
            'platform_net' => round($commission + $codFee - $platformDiscount - $shippingDiscount, 2),
        ];
    }

    /** Amounts are snapped at checkout, independent of future voucher/rate updates. */
    public function snapshot(Order $order, float $merchandise, float $sellerDiscount, float $platformDiscount, float $shippingDiscount): void
    {
        $calculated = self::breakdown($merchandise, $sellerDiscount, $platformDiscount,
            (float) $order->shipping_fee, $shippingDiscount, (float) $order->cod_fee);
        OrderFinancial::create([
            'order_id' => $order->id, 'merchandise' => round($merchandise, 2),
            'seller_discount' => round($sellerDiscount, 2), 'platform_discount' => round($platformDiscount, 2),
            'shipping_fee' => (float) $order->shipping_fee, 'shipping_discount' => round($shippingDiscount, 2),
            'cod_fee' => (float) $order->cod_fee,
            ...$calculated,
        ]);
    }

    /** Called ONLY by explicit admin action; idempotent under locks and DB unique(order,kind). */
    public function prepareForOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $financial = OrderFinancial::where('order_id', $order->id)->lockForUpdate()->first();
            if (! $financial) {
                throw ValidationException::withMessages(['order' => 'This Order predates financial snapshots; manual reconciliation required.']);
            }
            if (\App\Models\Logistics\ReturnCase::where('order_id',$order->id)->exists()) {
                throw ValidationException::withMessages(['order' => 'Return case exists; settlement is blocked.']);
            }
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['order' => 'This order has an unresolved settlement hold.']);
            }
            if ($order->status !== Order::STATUS_COMPLETED || ! $order->isPaymentEligible()) {
                throw ValidationException::withMessages(['order' => 'Order must be completed and payment eligible first.']);
            }
            $delivery = Delivery::where('order_id', $order->id)->lockForUpdate()->first();
            if (! $delivery || $delivery->status !== Delivery::DELIVERED || ! $delivery->logistics_partner_id) {
                throw ValidationException::withMessages(['order' => 'Verified delivered logistics assignment required.']);
            }
            if ($order->payment_method === 'cod') {
                $cod = CodSettlement::where('order_id', $order->id)->lockForUpdate()->first();
                if (! $cod || $cod->status !== CodSettlement::RECONCILED
                    || ! $cod->admin_remitted_at || ! $cod->admin_received_at
                    || abs((float) $cod->received_amount - (float) $cod->expected_amount) > 0.001
                    || abs((float) $cod->admin_received_amount - (float) $cod->expected_amount) > 0.001) {
                    throw ValidationException::withMessages(['order' => 'COD must be fully reconciled before releasing payouts.']);
                }
            }
            foreach ([
                ['seller', (int) $order->seller_id, (float) $financial->seller_earnings],
                ['logistics', (int) $delivery->logistics_partner_id, (float) $financial->logistics_earnings],
            ] as [$kind, $payee, $amount]) {
                if ($payee <= 0 || $amount < 0) throw ValidationException::withMessages(['order' => 'Invalid settlement beneficiary.']);
                SettlementEntry::firstOrCreate(['order_id' => $orderId, 'kind' => $kind],
                    ['payee_id' => $payee, 'amount' => round($amount, 2), 'status' => 'pending']);
            }
            // Rider fees are funded from logistics revenue, not the ShopHop product commission.
            // Only assign if the actual rider/role is recorded. Each fee is capped by remaining shipping revenue.
            $remaining = (float) $financial->logistics_earnings;
            foreach ([
                ['pickup_rider', (int) $delivery->pickup_rider_id, (float) config('shophop_finance.pickup_rider_fee', 20)],
                ['delivery_rider', (int) ($delivery->delivered_rider_id ?: $delivery->delivery_rider_id), (float) config('shophop_finance.delivery_rider_fee', 30)],
            ] as [$kind, $riderId, $rate]) {
                if ($riderId <= 0) continue;
                $amount = round(min($remaining, max(0, $rate)), 2);
                $remaining -= $amount;
                SettlementEntry::firstOrCreate(['order_id' => $orderId, 'kind' => $kind],
                    ['payee_id' => $riderId, 'amount' => $amount, 'status' => 'pending']);
            }
            // Transit Rider is paid only for a real, received transfer. Multiple legs have
            // separate unique kind keys. The combined Rider costs never exceed shipping revenue.
            $receivedTransfers = $delivery->transfers()->where('status', \App\Models\Logistics\ParcelTransfer::RECEIVED)
                ->whereNotNull('transit_rider_id')->orderBy('id')->get();
            foreach ($receivedTransfers as $transfer) {
                if ($remaining <= 0) break;
                $amount = round(min($remaining, max(0, (float) config('shophop_finance.transit_rider_fee', 40))), 2);
                $remaining = max(0, round($remaining - $amount, 2));
                SettlementEntry::firstOrCreate(['order_id' => $orderId, 'kind' => 'transit_'.$transfer->id], [
                    'payee_id' => (int) $transfer->transit_rider_id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }
        });
    }

    /** Admin marks seller/logistics money manually transferred; no wallet transfer occurs. */
    public function markAdminPaid(int $entryId, int $adminId, string $reference): void
    {
        DB::transaction(function () use ($entryId, $adminId, $reference) {
            $candidate = SettlementEntry::whereKey($entryId)->firstOrFail();
            $order = Order::whereKey($candidate->order_id)->lockForUpdate()->firstOrFail();
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            if ($order->status !== Order::STATUS_COMPLETED || ! $order->isPaymentEligible()) {
                throw ValidationException::withMessages(['settlement' => 'Order is no longer payment/receipt eligible.']);
            }
            if ($order->payment_method === 'cod') {
                $cod = CodSettlement::where('order_id', $order->id)->lockForUpdate()->first();
                if (! $cod || ! $cod->admin_received_at
                    || abs((float) $cod->admin_received_amount - (float) $cod->expected_amount) > .001) {
                    throw ValidationException::withMessages(['settlement' => 'ShopHop Admin must confirm receipt of COD cash before payouts.']);
                }
            }
            if (! in_array($entry->kind, ['seller', 'logistics'], true) || $entry->status !== 'pending') {
                throw ValidationException::withMessages(['settlement' => 'Only pending Seller/Logistics payouts can be recorded.']);
            }
            $financial = OrderFinancial::where('order_id', $entry->order_id)->lockForUpdate()->firstOrFail();
            if ($financial->settlement_hold) throw ValidationException::withMessages(['settlement' => 'Order is on hold.']);
            $entry->update(['status' => 'paid', 'payment_reference' => $reference, 'paid_by_user_id' => $adminId, 'paid_at' => now()]);
        });
    }

    /** A Logistics partner can only mark its own Rider's fee after its own settlement is paid. */
    public function markRiderPaid(int $entryId, int $partnerId, int $operatorId, string $reference): void
    {
        DB::transaction(function () use ($entryId, $partnerId, $operatorId, $reference) {
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            if (! (in_array($entry->kind, ['pickup_rider', 'delivery_rider'], true)
                || str_starts_with($entry->kind, 'transit_')) || $entry->status !== 'pending') {
                throw ValidationException::withMessages(['settlement' => 'Rider fee already settled or invalid.']);
            }
            $logistics = SettlementEntry::where('order_id', $entry->order_id)
                ->where('kind', 'logistics')->lockForUpdate()->first();
            if (! $logistics || (int) $logistics->payee_id !== $partnerId || $logistics->status !== 'paid') {
                throw ValidationException::withMessages(['settlement' => 'Logistics payout must be recorded as paid before rider settlement.']);
            }
            if (str_starts_with($entry->kind, 'transit_')) {
                $transferId = (int) substr($entry->kind, strlen('transit_'));
                if (! \App\Models\Logistics\ParcelTransfer::whereKey($transferId)
                    ->whereHas('delivery', fn ($query) => $query->where('order_id', $entry->order_id)->where('logistics_partner_id', $partnerId))
                    ->where('transit_rider_id', $entry->payee_id)
                    ->where('status', \App\Models\Logistics\ParcelTransfer::RECEIVED)->exists()) {
                    throw ValidationException::withMessages(['settlement' => 'Transit Rider/transfer not eligible for payout.']);
                }
            }
            $entry->update(['status' => 'paid', 'payment_reference' => $reference, 'paid_at' => now(), 'paid_by_user_id' => $operatorId]);
        });
    }
}
