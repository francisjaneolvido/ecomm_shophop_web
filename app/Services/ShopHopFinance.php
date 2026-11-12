<?php

namespace App\Services;

use App\Models\Admin\Dispute;
use App\Models\Buyer\Order\Order;
use App\Models\Finance\CenterRevenueLedgerEntry;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ReturnCase;
use App\Models\LogisticsPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopHopFinance
{
    /**
     * Pure marketplace ledger calculation.
     * - Seller merchandise vouchers reduce Seller revenue before the configured product commission.
     * - Seller-funded shipping vouchers are deducted from Seller payout, never from Logistics entitlement.
     * - ShopHop merchandise/free-shipping vouchers are platform-funded marketing expense.
     * - Logistics receives the snapped shipping fee less the configured shipping commission.
     */
    public static function breakdown(
        float $merchandise,
        float $sellerDiscount,
        float $sellerShippingDiscount,
        float $platformDiscount,
        float $shippingFee,
        float $shippingDiscount,
        float $codFee
    ): array {
        $policy = app(PlatformFinancialPolicyService::class)->active();
        $base = max(0, FinanceMoney::minor($merchandise) - FinanceMoney::minor($sellerDiscount));
        $shipping = max(0, FinanceMoney::minor($shippingFee));
        $commission = FinanceMoney::percent($base, $policy['product_percent']);
        $shippingCommission = FinanceMoney::percent($shipping, $policy['shipping_percent']);
        $seller = $base - $commission - FinanceMoney::minor($sellerShippingDiscount);
        if ($seller < 0) {
            throw ValidationException::withMessages(['discount' => 'Seller shipping discount exceeds the Seller allocation.']);
        }

        return [
            'commission' => FinanceMoney::decimal($commission),
            'shipping_commission' => FinanceMoney::decimal($shippingCommission),
            'seller_earnings' => FinanceMoney::decimal($seller),
            'logistics_earnings' => FinanceMoney::decimal($shipping - $shippingCommission),
            'platform_net' => FinanceMoney::decimal($commission + $shippingCommission + FinanceMoney::minor($codFee)
                - FinanceMoney::minor($platformDiscount) - FinanceMoney::minor($shippingDiscount)),
            'product_rate_snapshot' => $policy['product_percent'],
            'shipping_rate_snapshot' => $policy['shipping_percent'],
            'financial_model' => 'hybrid',
            'platform_rule_id' => $policy['id'],
            'seller_hold_days' => $policy['seller_hold_days'],
        ];
    }

    /** Amounts are snapped at checkout, independent of future voucher/rate updates. */
    public function snapshot(
        Order $order,
        float $merchandise,
        float $sellerDiscount,
        float $sellerShippingDiscount,
        float $platformDiscount,
        float $shippingDiscount,
        array $shippingQuote = []
    ): void {
        $calculated = self::breakdown(
            $merchandise,
            $sellerDiscount,
            $sellerShippingDiscount,
            $platformDiscount,
            (float) $order->shipping_fee,
            $shippingDiscount,
            (float) $order->cod_fee
        );

        $allocated = FinanceMoney::minor($calculated['seller_earnings']) + FinanceMoney::minor($calculated['logistics_earnings']) + FinanceMoney::minor($calculated['platform_net']);
        if ($allocated !== FinanceMoney::minor($order->total_amount)) {
            throw ValidationException::withMessages(['financial' => 'Allocations do not reconcile to the authoritative Buyer total.']);
        }

        OrderFinancial::create([
            'order_id' => $order->id,
            'merchandise' => FinanceMoney::decimal(FinanceMoney::minor($merchandise)),
            'seller_discount' => FinanceMoney::decimal(FinanceMoney::minor($sellerDiscount)),
            'seller_shipping_discount' => FinanceMoney::decimal(FinanceMoney::minor($sellerShippingDiscount)),
            'platform_discount' => FinanceMoney::decimal(FinanceMoney::minor($platformDiscount)),
            'shipping_fee' => FinanceMoney::decimal(FinanceMoney::minor($order->shipping_fee)),
            'shipping_route_band' => $shippingQuote['route_band'] ?? null,
            'shipping_weight_kg' => round((float) ($shippingQuote['weight_kg'] ?? 0), 3),
            'shipping_base_fee' => FinanceMoney::decimal(FinanceMoney::minor($shippingQuote['base_fee'] ?? 0)),
            'shipping_distance_fee' => FinanceMoney::decimal(FinanceMoney::minor($shippingQuote['distance_fee'] ?? 0)),
            'shipping_weight_fee' => FinanceMoney::decimal(FinanceMoney::minor($shippingQuote['weight_fee'] ?? 0)),
            'shipping_linehaul_fee' => FinanceMoney::decimal(FinanceMoney::minor($shippingQuote['linehaul_fee'] ?? 0)),
            'shipping_express_surcharge' => FinanceMoney::decimal(FinanceMoney::minor($shippingQuote['express_surcharge'] ?? 0)),
            'shipping_discount' => FinanceMoney::decimal(FinanceMoney::minor($shippingDiscount)),
            'cod_fee' => FinanceMoney::decimal(FinanceMoney::minor($order->cod_fee)),
            ...$calculated,
        ]);
    }

    /**
     * Called only by explicit Admin settlement preparation; idempotent under locks.
     *
     * Preserves existing financial snapshots and separates partner personnel compensation.
     */
    public function prepareForOrder(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->firstOrFail();
            [$financial, $legacyBackfilled] = $this->financialForSettlement($order);
            if (ReturnCase::where('order_id', $order->id)->exists()) {
                throw ValidationException::withMessages(['order' => 'Return case exists; settlement is blocked.']);
            }
            if (Dispute::where('order_id', $order->id)->whereNotIn('status', Dispute::CLOSED)->exists()) {
                throw ValidationException::withMessages(['settlement' => 'An unresolved order dispute blocks settlement.']);
            }
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['order' => 'This order has an unresolved settlement hold.']);
            }
            if (! app(PlatformFinancialPolicyService::class)->eligible($order, $financial)) {
                throw ValidationException::withMessages(['order' => 'Order must be completed and payment eligible first.']);
            }

            $delivery = Delivery::with(['originSortingCenter', 'destinationSortingCenter', 'transfers.fromCenter', 'transfers.toCenter'])
                ->where('order_id', $order->id)->lockForUpdate()->first();
            if (! $delivery || $delivery->status !== Delivery::DELIVERED || ! $delivery->logistics_partner_id) {
                throw ValidationException::withMessages(['order' => 'Verified delivered logistics assignment required.']);
            }

            if ($order->payment_method === 'cod') {
                CodDiscrepancyService::assertOrderClear((int) $order->id);
                $cod = CodSettlement::where('order_id', $order->id)->lockForUpdate()->first();
                if (! $cod || $cod->status !== CodSettlement::RECONCILED
                    || ! $cod->admin_remitted_at || ! $cod->admin_received_at
                    || abs((float) $cod->received_amount - (float) $cod->expected_amount) > 0.001
                    || abs((float) $cod->admin_received_amount - (float) $cod->expected_amount) > 0.001) {
                    throw ValidationException::withMessages(['order' => 'COD must be fully reconciled before releasing payouts.']);
                }
            }

            foreach ([
                ['seller', (int) $order->seller_id, $financial->seller_earnings],
                ['logistics', (int) $delivery->logistics_partner_id, $financial->logistics_earnings],
            ] as [$kind, $payee, $amount]) {
                if ($payee <= 0 || $amount < 0) {
                    throw ValidationException::withMessages(['order' => 'Invalid settlement beneficiary.']);
                }
                SettlementEntry::firstOrCreate(
                    ['order_id' => $orderId, 'kind' => $kind],
                    ['payee_id' => $payee, 'amount' => FinanceMoney::decimal(FinanceMoney::minor($amount)), 'status' => 'pending']
                );
            }

            // Gross partner allocation is business revenue, never an automatic personnel/facility split.
            // Partner compensation is recorded at verified service events under partner-owned policies.
            return $legacyBackfilled;
        }, 3);
    }

    /** Historical snapshots are preserved; missing historical rates require audited review. */
    private function financialForSettlement(Order $order): array
    {
        $financial = OrderFinancial::where('order_id', $order->id)->lockForUpdate()->first();
        if ($financial) {
            return [$financial, false];
        }

        throw ValidationException::withMessages([
            'order' => 'Historical financial snapshot is missing. Reconstruct it through an audited review; current rates cannot be applied to historical orders.',
        ]);
    }

    /**
     * ShopHop releases a prepared Order settlement into beneficiary ledgers.
     * This is a ledger distribution only; it does not claim an external bank/e-wallet transfer happened.
     */
    public function distributeOrder(int $orderId, int $adminId): string
    {
        return DB::transaction(function () use ($orderId, $adminId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $financial = OrderFinancial::where('order_id', $orderId)->lockForUpdate()->firstOrFail();
            if (Dispute::where('order_id', $order->id)->whereNotIn('status', Dispute::CLOSED)->exists()) {
                throw ValidationException::withMessages(['settlement' => 'An unresolved order dispute blocks settlement.']);
            }
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['settlement' => 'This order is on settlement hold.']);
            }
            if (! app(PlatformFinancialPolicyService::class)->eligible($order, $financial)) {
                throw ValidationException::withMessages(['settlement' => 'Order is no longer eligible for settlement distribution.']);
            }
            if ($order->payment_method === 'cod') {
                CodDiscrepancyService::assertOrderClear((int) $order->id);
                $cod = CodSettlement::where('order_id', $orderId)->lockForUpdate()->first();
                if (! $cod || ! $cod->admin_received_at
                    || abs((float) $cod->admin_received_amount - (float) $cod->expected_amount) > .001) {
                    throw ValidationException::withMessages(['settlement' => 'ShopHop must first confirm the full COD remittance.']);
                }
            }

            $entries = SettlementEntry::where('order_id', $orderId)->lockForUpdate()->get();
            $seller = $entries->firstWhere('kind', 'seller');
            $logistics = $entries->firstWhere('kind', 'logistics');
            if (! $seller || ! $logistics) {
                throw ValidationException::withMessages(['settlement' => 'Prepare this order settlement before distribution.']);
            }
            if ($entries->where('status', 'paid')->isNotEmpty()) {
                throw ValidationException::withMessages(['settlement' => 'A payout is already recorded; distribution can no longer be changed.']);
            }
            if ($entries->where('status', 'pending')->isEmpty()) {
                throw ValidationException::withMessages(['settlement' => 'This order has already been distributed.']);
            }

            $reference = 'DIST-'.now()->format('Ymd-His').'-'.$orderId;
            SettlementEntry::where('order_id', $orderId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'available',
                    'distribution_reference' => $reference,
                    'distributed_at' => now(),
                    'distributed_by_user_id' => $adminId,
                    'updated_at' => now(),
                ]);

            CenterRevenueLedgerEntry::where('order_id', $orderId)
                ->where('status', 'allocated')
                ->update(['status' => 'posted', 'posted_at' => now(), 'updated_at' => now()]);

            FinancialAudit::record('order_settlement', $orderId, 'distributed', 'user', $adminId, ['reference' => $reference]);

            return $reference;
        }, 3);
    }

    /** Admin marks Seller/Logistics money manually transferred; no wallet transfer occurs. */
    public function markAdminPaid(int $entryId, int $adminId, string $reference): void
    {
        DB::transaction(function () use ($entryId, $adminId, $reference) {
            $candidate = SettlementEntry::whereKey($entryId)->firstOrFail();
            $order = Order::whereKey($candidate->order_id)->lockForUpdate()->firstOrFail();
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            $financial = OrderFinancial::where('order_id', $order->id)->lockForUpdate()->firstOrFail();
            if (! app(PlatformFinancialPolicyService::class)->eligible($order, $financial)) {
                throw ValidationException::withMessages(['settlement' => 'Order is no longer payment/receipt eligible.']);
            }
            if ($order->payment_method === 'cod') {
                CodDiscrepancyService::assertOrderClear((int) $order->id);
                $cod = CodSettlement::where('order_id', $order->id)->lockForUpdate()->first();
                if (! $cod || ! $cod->admin_received_at
                    || abs((float) $cod->admin_received_amount - (float) $cod->expected_amount) > .001) {
                    throw ValidationException::withMessages(['settlement' => 'ShopHop Admin must confirm receipt of COD cash before payouts.']);
                }
            }
            if (! in_array($entry->kind, ['seller', 'logistics'], true) || $entry->status !== 'available') {
                throw ValidationException::withMessages(['settlement' => 'Only distributed/available Seller or Logistics payouts can be recorded as paid.']);
            }
            $financial = OrderFinancial::where('order_id', $entry->order_id)->lockForUpdate()->firstOrFail();
            if (Dispute::where('order_id', $order->id)->whereNotIn('status', Dispute::CLOSED)->exists()) {
                throw ValidationException::withMessages(['settlement' => 'An unresolved order dispute blocks settlement.']);
            }
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['settlement' => 'Order is on hold.']);
            }
            $entry->update([
                'status' => 'paid', 'payment_reference' => $reference,
                'paid_by_user_id' => $adminId, 'paid_at' => now(),
            ]);
            FinancialAudit::record('settlement_entry', $entryId, 'paid', 'user', $adminId, ['amount' => $entry->amount, 'reference' => $reference]);
        });
    }

    public function approveLegacyRider(int $entryId, int $partnerId, int $operatorId): void
    {
        DB::transaction(function () use ($entryId, $partnerId, $operatorId) {
            abort_unless(LogisticsPartner::whereKey($partnerId)->where('user_id', $operatorId)->exists(), 403);
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            abort_unless(Delivery::where('order_id', $entry->order_id)->where('logistics_partner_id', $partnerId)->exists(), 404);
            if (! in_array($entry->kind, ['pickup_rider', 'delivery_rider'], true) || $entry->status !== 'pending') {
                throw ValidationException::withMessages(['settlement' => 'Only pending historical Rider obligations can be approved.']);
            }
            $entry->update(['status' => 'available', 'distribution_reference' => 'PARTNER-APPROVAL-'.$entryId,
                'distributed_at' => now(), 'distributed_by_user_id' => $operatorId]);
            FinancialAudit::record('legacy_rider_settlement', $entryId, 'partner_approved', 'user', $operatorId, ['amount' => $entry->amount]);
        });
    }

    /** Pay historically recorded Rider obligations from Partner funds without a Seller settlement gate. */
    public function markRiderPaid(int $entryId, int $partnerId, int $operatorId, string $reference): void
    {
        DB::transaction(function () use ($entryId, $partnerId, $operatorId, $reference) {
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            if (! in_array($entry->kind, ['pickup_rider', 'delivery_rider'], true) || $entry->status !== 'available') {
                throw ValidationException::withMessages(['settlement' => 'Only available Rider earnings can be recorded as paid.']);
            }
            $delivery = Delivery::where('order_id', $entry->order_id)->where('logistics_partner_id', $partnerId)->first();
            abort_unless($delivery && LogisticsPartner::whereKey($partnerId)->where('user_id', $operatorId)->exists(), 403);
            $entry->update([
                'status' => 'paid', 'payment_reference' => $reference, 'paid_at' => now(),
                'paid_by_user_id' => $operatorId,
            ]);
            FinancialAudit::record('legacy_rider_settlement', $entryId, 'paid', 'user', $operatorId, ['amount' => $entry->amount, 'reference' => $reference]);
        });
    }
}
