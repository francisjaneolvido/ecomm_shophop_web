<?php
namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\CenterRevenueLedgerEntry;
use App\Models\Finance\LogisticsOrderAllocation;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopHopFinance
{
    /**
     * Pure marketplace ledger calculation.
     * - Seller merchandise vouchers reduce Seller revenue before the 10% ShopHop commission.
     * - Seller-funded shipping vouchers are deducted from Seller payout, never from Logistics entitlement.
     * - ShopHop merchandise/free-shipping vouchers are platform-funded marketing expense.
     * - Logistics always receives the snapped gross shipping fee.
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
        $sellerBase = max(0, round($merchandise - $sellerDiscount, 2));
        $commissionRate = (float) MarketplaceRateSetting::current()->commission_percent;
        $commission = round($sellerBase * ($commissionRate / 100), 2);
        $sellerEarnings = max(0, round($sellerBase - $commission - $sellerShippingDiscount, 2));

        return [
            'commission' => $commission,
            'seller_earnings' => $sellerEarnings,
            'logistics_earnings' => round(max(0, $shippingFee), 2),
            'platform_net' => round($commission + $codFee - $platformDiscount - $shippingDiscount, 2),
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

        OrderFinancial::create([
            'order_id' => $order->id,
            'merchandise' => round($merchandise, 2),
            'seller_discount' => round($sellerDiscount, 2),
            'seller_shipping_discount' => round($sellerShippingDiscount, 2),
            'platform_discount' => round($platformDiscount, 2),
            'shipping_fee' => (float) $order->shipping_fee,
            'shipping_route_band' => $shippingQuote['route_band'] ?? null,
            'shipping_weight_kg' => round((float) ($shippingQuote['weight_kg'] ?? 0), 3),
            'shipping_base_fee' => round((float) ($shippingQuote['base_fee'] ?? 0), 2),
            'shipping_distance_fee' => round((float) ($shippingQuote['distance_fee'] ?? 0), 2),
            'shipping_weight_fee' => round((float) ($shippingQuote['weight_fee'] ?? 0), 2),
            'shipping_linehaul_fee' => round((float) ($shippingQuote['linehaul_fee'] ?? 0), 2),
            'shipping_express_surcharge' => round((float) ($shippingQuote['express_surcharge'] ?? 0), 2),
            'shipping_discount' => round($shippingDiscount, 2),
            'cod_fee' => (float) $order->cod_fee,
            ...$calculated,
        ]);
    }

    /**
     * Called only by explicit Admin settlement preparation; idempotent under locks.
     *
     * Returns true when an older Order had to receive a reconstructed financial
     * snapshot from its frozen Order/OrderItem amounts before settlement.
     */
    public function prepareForOrder(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->firstOrFail();
            [$financial, $legacyBackfilled] = $this->financialForSettlement($order);
            if (\App\Models\Logistics\ReturnCase::where('order_id', $order->id)->exists()) {
                throw ValidationException::withMessages(['order' => 'Return case exists; settlement is blocked.']);
            }
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['order' => 'This order has an unresolved settlement hold.']);
            }
            if ($order->status !== Order::STATUS_COMPLETED || ! $order->isPaymentEligible()) {
                throw ValidationException::withMessages(['order' => 'Order must be completed and payment eligible first.']);
            }

            $delivery = Delivery::with(['originSortingCenter', 'destinationSortingCenter', 'transfers.fromCenter', 'transfers.toCenter'])
                ->where('order_id', $order->id)->lockForUpdate()->first();
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
                if ($payee <= 0 || $amount < 0) {
                    throw ValidationException::withMessages(['order' => 'Invalid settlement beneficiary.']);
                }
                SettlementEntry::firstOrCreate(
                    ['order_id' => $orderId, 'kind' => $kind],
                    ['payee_id' => $payee, 'amount' => round($amount, 2), 'status' => 'pending']
                );
            }

            $rates = MarketplaceRateSetting::current();
            $remaining = max(0, (float) $financial->logistics_earnings);

            $pickupFee = 0.0;
            if ((int) $delivery->pickup_rider_id > 0) {
                $pickupFee = round(min($remaining, max(0, (float) $rates->pickup_rider_fee)), 2);
                $remaining = max(0, round($remaining - $pickupFee, 2));
                SettlementEntry::firstOrCreate(
                    ['order_id' => $orderId, 'kind' => 'pickup_rider'],
                    ['payee_id' => (int) $delivery->pickup_rider_id, 'amount' => $pickupFee, 'status' => 'pending']
                );
            }

            $deliveryRiderId = (int) ($delivery->delivered_rider_id ?: $delivery->delivery_rider_id);
            $deliveryFee = 0.0;
            if ($deliveryRiderId > 0) {
                $deliveryFee = round(min($remaining, max(0, (float) $rates->delivery_rider_fee)), 2);
                $remaining = max(0, round($remaining - $deliveryFee, 2));
                SettlementEntry::firstOrCreate(
                    ['order_id' => $orderId, 'kind' => 'delivery_rider'],
                    ['payee_id' => $deliveryRiderId, 'amount' => $deliveryFee, 'status' => 'pending']
                );
            }

            $originBranchAmount = 0.0;
            $destinationBranchAmount = 0.0;
            $originCenter = $delivery->originSortingCenter;
            $destinationCenter = $delivery->destinationSortingCenter;

            if ($originCenter) {
                $originBranchAmount = round(min($remaining, max(0, (float) $rates->origin_branch_fee)), 2);
                $remaining = max(0, round($remaining - $originBranchAmount, 2));
                $this->centerLedger($orderId, (int) $delivery->logistics_partner_id, (int) $originCenter->id,
                    'origin_handling', $originBranchAmount, 'Origin parcel receiving/sorting allocation');
            }

            if ($destinationCenter && (! $originCenter || (int) $destinationCenter->id !== (int) $originCenter->id)) {
                $destinationBranchAmount = round(min($remaining, max(0, (float) $rates->destination_branch_fee)), 2);
                $remaining = max(0, round($remaining - $destinationBranchAmount, 2));
                $this->centerLedger($orderId, (int) $delivery->logistics_partner_id, (int) $destinationCenter->id,
                    'destination_handling', $destinationBranchAmount, 'Destination receiving/last-mile allocation');
            }

            $hubIds = collect();
            foreach ($delivery->transfers->where('status', ParcelTransfer::RECEIVED) as $transfer) {
                foreach ([$transfer->fromCenter, $transfer->toCenter] as $center) {
                    if ($center && $center->type === 'province_hub') {
                        $hubIds->push((int) $center->id);
                    }
                }
            }
            $hubIds = $hubIds->unique()->values();
            $hubAmount = 0.0;
            foreach ($hubIds as $hubId) {
                if ($remaining <= 0) break;
                $amount = round(min($remaining, max(0, (float) $rates->province_hub_fee)), 2);
                $remaining = max(0, round($remaining - $amount, 2));
                $hubAmount = round($hubAmount + $amount, 2);
                $this->centerLedger($orderId, (int) $delivery->logistics_partner_id, (int) $hubId,
                    'hub_handling', $amount, 'Province Hub handling allocation');
            }

            // The shipping quote already reserves a linehaul component. It remains with the
            // Logistics company as a Linehaul pool; actual Driver earnings are created per completed Trip.
            $linehaulPool = round(min($remaining, max(0, (float) $financial->shipping_linehaul_fee)), 2);
            $remaining = max(0, round($remaining - $linehaulPool, 2));

            LogisticsOrderAllocation::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'logistics_partner_id' => (int) $delivery->logistics_partner_id,
                    'gross_shipping' => round((float) $financial->logistics_earnings, 2),
                    'pickup_rider_fee' => $pickupFee,
                    'delivery_rider_fee' => $deliveryFee,
                    'origin_branch_amount' => $originBranchAmount,
                    'destination_branch_amount' => $destinationBranchAmount,
                    'province_hub_count' => $hubIds->count(),
                    'province_hub_amount' => $hubAmount,
                    'linehaul_pool' => $linehaulPool,
                    'logistics_margin' => round($remaining, 2),
                ]
            );

            return $legacyBackfilled;
        }, 3);
    }

    /**
     * Patch-C compatibility for COD Orders completed before OrderFinancial
     * snapshots existed.
     *
     * We DO NOT recalculate an old Order using today's shipping rates or today's
     * vouchers. The reconstruction uses only frozen values already stored on the
     * Order and OrderItem rows. If those frozen amounts do not reconcile to the
     * amount the Buyer actually paid, Admin is stopped instead of inventing a
     * payout.
     *
     * @return array{0: OrderFinancial, 1: bool}
     */
    private function financialForSettlement(Order $order): array
    {
        $financial = OrderFinancial::where('order_id', $order->id)->lockForUpdate()->first();
        if ($financial) {
            return [$financial, false];
        }

        $order->loadMissing('items');

        $merchandise = round((float) $order->items->sum(
            fn ($item) => (float) $item->price * (int) $item->quantity
        ), 2);

        if ($merchandise <= 0) {
            throw ValidationException::withMessages([
                'order' => 'Historical settlement cannot be reconstructed because this Order has no frozen item price total.',
            ]);
        }

        $sellerDiscount = round(max(0, (float) $order->voucher_discount), 2);
        $sellerShippingDiscount = round(max(0, (float) $order->seller_shipping_discount), 2);
        $platformDiscount = round(max(0, (float) $order->platform_discount), 2);
        $shippingDiscount = round(max(0, (float) $order->shipping_discount), 2);
        $shippingFee = round(max(0, (float) $order->shipping_fee), 2);
        $codFee = round(max(0, (float) $order->cod_fee), 2);

        $reconstructedBuyerTotal = round(
            $merchandise
            + $shippingFee
            + $codFee
            - $sellerDiscount
            - $sellerShippingDiscount
            - $platformDiscount
            - $shippingDiscount,
            2
        );

        $storedBuyerTotal = round((float) $order->total_amount, 2);
        if (abs($reconstructedBuyerTotal - $storedBuyerTotal) > 0.02) {
            throw ValidationException::withMessages([
                'order' => sprintf(
                    'Historical Order #%d does not reconcile safely (stored total ₱%s vs frozen line-item total ₱%s). Review it manually before distribution.',
                    $order->id,
                    number_format($storedBuyerTotal, 2),
                    number_format($reconstructedBuyerTotal, 2)
                ),
            ]);
        }

        $calculated = self::breakdown(
            $merchandise,
            $sellerDiscount,
            $sellerShippingDiscount,
            $platformDiscount,
            $shippingFee,
            $shippingDiscount,
            $codFee
        );

        $financial = OrderFinancial::create([
            'order_id' => $order->id,
            'merchandise' => $merchandise,
            'seller_discount' => $sellerDiscount,
            'seller_shipping_discount' => $sellerShippingDiscount,
            'platform_discount' => $platformDiscount,
            'shipping_fee' => $shippingFee,

            // Historical Orders did not preserve the Patch-B quote components.
            // Keep them explicit as legacy instead of pretending today's rate
            // table was the rate used when the Buyer checked out.
            'shipping_route_band' => 'legacy',
            'shipping_weight_kg' => 0,
            'shipping_base_fee' => 0,
            'shipping_distance_fee' => 0,
            'shipping_weight_fee' => 0,
            'shipping_linehaul_fee' => 0,
            'shipping_express_surcharge' => 0,

            'shipping_discount' => $shippingDiscount,
            'cod_fee' => $codFee,
            ...$calculated,
        ]);

        return [$financial, true];
    }

    private function centerLedger(
        int $orderId,
        int $partnerId,
        int $centerId,
        string $kind,
        float $amount,
        string $note
    ): void {
        if ($amount <= 0) return;
        CenterRevenueLedgerEntry::firstOrCreate(
            ['order_id' => $orderId, 'sorting_center_id' => $centerId, 'kind' => $kind],
            [
                'logistics_partner_id' => $partnerId,
                'amount' => round($amount, 2),
                'status' => 'allocated',
                'note' => $note,
            ]
        );
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
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['settlement' => 'This order is on settlement hold.']);
            }
            if ($order->status !== Order::STATUS_COMPLETED || ! $order->isPaymentEligible()) {
                throw ValidationException::withMessages(['settlement' => 'Order is no longer eligible for settlement distribution.']);
            }
            if ($order->payment_method === 'cod') {
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
            if (! in_array($entry->kind, ['seller', 'logistics'], true) || $entry->status !== 'available') {
                throw ValidationException::withMessages(['settlement' => 'Only distributed/available Seller or Logistics payouts can be recorded as paid.']);
            }
            $financial = OrderFinancial::where('order_id', $entry->order_id)->lockForUpdate()->firstOrFail();
            if ($financial->settlement_hold) {
                throw ValidationException::withMessages(['settlement' => 'Order is on hold.']);
            }
            $entry->update([
                'status' => 'paid', 'payment_reference' => $reference,
                'paid_by_user_id' => $adminId, 'paid_at' => now(),
            ]);
        });
    }

    /** Logistics can record Pickup/Delivery Rider payout only after its own ShopHop settlement is paid. */
    public function markRiderPaid(int $entryId, int $partnerId, int $operatorId, string $reference): void
    {
        DB::transaction(function () use ($entryId, $partnerId, $operatorId, $reference) {
            $entry = SettlementEntry::whereKey($entryId)->lockForUpdate()->firstOrFail();
            if (! in_array($entry->kind, ['pickup_rider', 'delivery_rider'], true) || $entry->status !== 'available') {
                throw ValidationException::withMessages(['settlement' => 'Only available Rider earnings can be recorded as paid.']);
            }
            $logistics = SettlementEntry::where('order_id', $entry->order_id)
                ->where('kind', 'logistics')->lockForUpdate()->first();
            if (! $logistics || (int) $logistics->payee_id !== $partnerId || ! in_array($logistics->status, ['available', 'paid'], true)) {
                throw ValidationException::withMessages(['settlement' => 'ShopHop must distribute the Logistics settlement before Rider payout can be recorded.']);
            }
            $entry->update([
                'status' => 'paid', 'payment_reference' => $reference, 'paid_at' => now(),
                'paid_by_user_id' => $operatorId,
            ]);
        });
    }
}
