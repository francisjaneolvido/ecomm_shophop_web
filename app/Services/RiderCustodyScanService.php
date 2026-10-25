<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Authoritative Rider-side QR custody transitions.
 *
 * One permanent ShopHop parcel QR is scanned whenever physical custody moves to a Rider:
 * Seller -> Pickup Rider and Destination Branch -> Delivery Rider. Linehaul uses Manifest QR, not Rider parcel scans.
 */
class RiderCustodyScanService
{
    public const SELLER_PICKUP = 'seller_pickup';
    public const DELIVERY_HANDOFF = 'delivery_handoff';

    public const EVENT_SELLER_PICKUP = 'seller_handoff';
    public const EVENT_DELIVERY_HANDOFF = 'delivery_rider_handoff';

    public function inspect(Rider $rider, string $trackingCode): array
    {
        $code = $this->trackingCode($trackingCode);
        $rider->loadMissing(['partner.user', 'sortingCenter']);
        $this->assertActiveRider($rider);

        $delivery = Delivery::with([
            'order.seller',
            'order.items.product',
            'order.items.variant',
            'originSortingCenter',
            'currentSortingCenter',
            'destinationSortingCenter',
        ])
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->where('tracking_code', $code)
            ->first();

        if (! $delivery) {
            $this->explainMissingDelivery($rider, $code);
        }

        return $this->resolveHandoff($rider, $delivery, false);
    }

    public function confirm(Rider $rider, string $trackingCode, ?string $expectedAction = null): array
    {
        $code = $this->trackingCode($trackingCode);

        return DB::transaction(function () use ($rider, $code, $expectedAction) {
            $lockedRider = Rider::with(['partner.user', 'sortingCenter'])
                ->whereKey($rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)
                ->lockForUpdate()
                ->firstOrFail();
            $this->assertActiveRider($lockedRider);

            $delivery = Delivery::with([
                'order.seller',
                'order.items.product',
                'order.items.variant',
                'originSortingCenter',
                'currentSortingCenter',
                'destinationSortingCenter',
            ])
                ->where('logistics_partner_id', $lockedRider->logistics_partner_id)
                ->where('tracking_code', $code)
                ->lockForUpdate()
                ->first();

            if (! $delivery) {
                $this->explainMissingDelivery($lockedRider, $code);
            }

            $handoff = $this->resolveHandoff($lockedRider, $delivery, true);
            if ($expectedAction && ! hash_equals($handoff['action'], trim($expectedAction))) {
                throw ValidationException::withMessages([
                    'tracking_code' => 'The parcel workflow changed after preview. Scan it again before confirming custody.',
                ]);
            }

            return match ($handoff['action']) {
                self::SELLER_PICKUP => $this->confirmSellerPickup($lockedRider, $delivery, $handoff),
                self::DELIVERY_HANDOFF => $this->confirmDeliveryHandoff($lockedRider, $delivery, $handoff),
                default => throw ValidationException::withMessages([
                    'tracking_code' => 'No Rider custody action is available for this parcel.',
                ]),
            };
        }, 3);
    }

    /**
     * Give the Rider an actionable workflow message when a QR exists but no Delivery
     * has been created inside this Rider's Logistics network yet.
     */
    private function explainMissingDelivery(Rider $rider, string $trackingCode): never
    {
        $order = Order::with([
            'pickupRequest.partner',
            'pickupRequest.originSortingCenter',
            'logisticsPartner',
        ])->where('tracking_code', $trackingCode)->first();

        if (! $order) {
            throw ValidationException::withMessages([
                'tracking_code' => 'No ShopHop parcel was found for this QR. Verify that you scanned the tracking QR printed on the waybill.',
            ]);
        }

        $pickup = $order->pickupRequest;
        if (! $pickup) {
            throw ValidationException::withMessages([
                'tracking_code' => 'This waybill exists, but the Seller has not requested pickup yet. Ask the Seller to Mark Ready & Request Pickup first.',
            ]);
        }

        if ((int) $pickup->logistics_partner_id !== (int) $rider->logistics_partner_id) {
            $partnerName = $pickup->partner?->company_name ?: $order->logisticsPartner?->company_name ?: 'another Logistics provider';
            throw ValidationException::withMessages([
                'tracking_code' => 'This parcel belongs to '.$partnerName.', not your Rider Logistics network.',
            ]);
        }

        if ($pickup->status === PickupRequest::REQUESTED) {
            $centerName = $pickup->originSortingCenter?->name ?: 'the origin branch';
            throw ValidationException::withMessages([
                'tracking_code' => 'Pickup is requested and waiting for Rider assignment at '.$centerName.'. The branch must assign the pickup before you scan it.',
            ]);
        }

        if ($pickup->status === PickupRequest::ASSIGNED) {
            throw ValidationException::withMessages([
                'tracking_code' => 'This pickup is marked assigned, but its Rider delivery record is missing. Refresh Branch Dispatch and reassign the pickup before scanning.',
            ]);
        }

        throw ValidationException::withMessages([
            'tracking_code' => 'This parcel is not currently awaiting a custody handoff to your Rider account. Refresh Today tasks before scanning.',
        ]);
    }

    private function resolveHandoff(Rider $rider, Delivery $delivery, bool $lockTransfer): array
    {
        $order = $delivery->order;
        if (! $order) {
            throw ValidationException::withMessages(['tracking_code' => 'Parcel order record is unavailable.']);
        }

        // Seller -> Pickup Rider. The physical QR confirmation accepts an assigned pickup and records collection atomically.
        if (in_array($delivery->status, [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED], true)
            && (int) $delivery->rider_id === (int) $rider->id
            && $order->status === Order::STATUS_READY_FOR_PICKUP
            && $order->isPaymentEligible()) {
            $center = $this->center($delivery->origin_sorting_center_id, $rider->logistics_partner_id);
            if ((int) $rider->sorting_center_id !== (int) $center->id) {
                throw ValidationException::withMessages([
                    'tracking_code' => 'This pickup belongs to a different origin branch.',
                ]);
            }

            $seller = trim((string) ($order->seller?->business_name ?: $order->seller?->first_name ?: 'Seller'));

            return [
                'action' => self::SELLER_PICKUP,
                'event' => self::EVENT_SELLER_PICKUP,
                'tracking_code' => $delivery->tracking_code,
                'title' => 'Seller → Pickup Rider',
                'from' => $seller !== '' ? $seller : 'Seller',
                'to' => $center->name,
                'center_id' => $center->id,
                'center' => $center->name,
                'delivery_status' => $delivery->status,
                'instruction' => 'Verify the waybill below, then confirm only when the labeled parcel is physically in your possession. This one confirmation accepts the pickup assignment and records Seller → Rider custody.',
                'confirm_label' => 'Accept & Pick Up',
                'parcel_transfer_id' => null,
                'waybill' => $this->waybill($rider, $delivery),
            ];
        }

        // Destination Branch -> Delivery Rider. Scan proves branch release before Out for Delivery.
        if ($delivery->status === Delivery::DELIVERY_ASSIGNED
            && (int) $delivery->delivery_rider_id === (int) $rider->id
            && (int) $delivery->rider_id === (int) $rider->id
            && $order->status === Order::STATUS_TO_RECEIVE
            && $order->isPaymentEligible()) {
            $centerId = $delivery->current_sorting_center_id ?: $delivery->destination_sorting_center_id;
            $center = $this->center($centerId, $rider->logistics_partner_id);

            if ($delivery->destination_sorting_center_id
                && (int) $delivery->destination_sorting_center_id !== (int) $center->id) {
                throw ValidationException::withMessages([
                    'tracking_code' => 'Parcel has not reached its destination branch yet.',
                ]);
            }
            if ((int) $rider->sorting_center_id !== (int) $center->id) {
                throw ValidationException::withMessages([
                    'tracking_code' => 'This delivery belongs to a different destination branch.',
                ]);
            }

            return [
                'action' => self::DELIVERY_HANDOFF,
                'event' => self::EVENT_DELIVERY_HANDOFF,
                'tracking_code' => $delivery->tracking_code,
                'title' => 'Branch → Delivery Rider',
                'from' => $center->name,
                'to' => 'Buyer delivery route',
                'center_id' => $center->id,
                'center' => $center->name,
                'delivery_status' => $delivery->status,
                'instruction' => 'Confirm only after the destination branch physically releases this parcel to you. It will become Out for Delivery.',
                'confirm_label' => 'Accept parcel for delivery',
                'parcel_transfer_id' => null,
                'waybill' => $this->waybill($rider, $delivery),
            ];
        }

        throw ValidationException::withMessages([
            'tracking_code' => 'This parcel is not awaiting a custody handoff to your Rider account. Refresh Today tasks and scan the assigned parcel.',
        ]);
    }

    private function confirmSellerPickup(Rider $rider, Delivery $delivery, array $handoff): array
    {
        $scannedAt = now();
        $orderUpdated = Order::whereKey($delivery->order_id)
            ->where('status', Order::STATUS_READY_FOR_PICKUP)
            ->paymentEligible()
            ->update(['status' => Order::STATUS_TO_RECEIVE]);

        $deliveryUpdates = [
            'status' => Delivery::PICKED_UP,
            'picked_up_at' => $scannedAt,
            'pickup_rider_id' => $rider->id,
        ];
        if (! $delivery->pickup_accepted_at) {
            $deliveryUpdates['pickup_accepted_at'] = $scannedAt;
        }

        $deliveryUpdated = Delivery::whereKey($delivery->id)
            ->whereIn('status', [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED])
            ->where('rider_id', $rider->id)
            ->update($deliveryUpdates);

        if ($orderUpdated !== 1 || $deliveryUpdated !== 1) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Pickup state changed while scanning. Refresh and scan the parcel again.',
            ]);
        }

        PickupRequest::where('order_id', $delivery->order_id)
            ->whereIn('status', [PickupRequest::ASSIGNED, PickupRequest::PICKED_UP])
            ->update([
                'status' => PickupRequest::PICKED_UP,
                'picked_up_at' => $scannedAt,
            ]);

        $this->logRiderEvent($rider, $delivery, $handoff);

        return $this->result(
            $delivery,
            $handoff,
            Delivery::PICKED_UP,
            'Pickup accepted and Seller handoff recorded from the waybill QR. You now have custody; bring the parcel to the origin branch.'
        );
    }

    private function confirmDeliveryHandoff(Rider $rider, Delivery $delivery, array $handoff): array
    {
        $updated = Delivery::whereKey($delivery->id)
            ->where('status', Delivery::DELIVERY_ASSIGNED)
            ->where('rider_id', $rider->id)
            ->where('delivery_rider_id', $rider->id)
            ->update([
                'status' => Delivery::OUT_FOR_DELIVERY,
                'out_for_delivery_at' => now(),
                'in_transit_at' => now(),
            ]);

        if ($updated !== 1) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Delivery assignment changed while scanning. Refresh and scan the parcel again.',
            ]);
        }

        $this->logRiderEvent($rider, $delivery, $handoff);

        return $this->result($delivery, $handoff, Delivery::OUT_FOR_DELIVERY, 'Branch handoff scanned. Parcel is now Out for Delivery to the Buyer.');
    }

    private function waybill(Rider $rider, Delivery $delivery): array
    {
        $delivery->loadMissing([
            'order.seller',
            'order.items.product',
            'order.items.variant',
            'originSortingCenter',
            'destinationSortingCenter',
        ]);

        $order = $delivery->order;
        $seller = $order?->seller;
        $sellerName = trim((string) ($seller?->business_name ?: trim(($seller?->first_name ?? '').' '.($seller?->last_name ?? ''))));
        $sellerAddress = collect([
            $seller?->street_address,
            $seller?->barangay_name,
            $seller?->municipality_name,
            $seller?->province_name,
        ])->filter()->implode(', ');

        return [
            'order_number' => $order ? '#'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) : '—',
            'tracking_code' => $delivery->tracking_code,
            'logistics' => $rider->partner?->company_name ?? 'ShopHop Logistics',
            'origin_center' => $delivery->originSortingCenter?->name,
            'destination_center' => $delivery->destinationSortingCenter?->name,
            'seller_name' => $sellerName !== '' ? $sellerName : 'Seller',
            'seller_address' => $sellerAddress,
            'seller_phone' => $seller?->contact_no,
            'buyer_name' => $order?->delivery_name ?: 'Buyer',
            'buyer_address' => $order?->delivery_address,
            'buyer_phone' => $order?->delivery_phone,
            'payment_method' => strtoupper((string) ($order?->payment_method ?? '—')),
            'cod_amount' => $order?->payment_method === 'cod' ? (string) $order->total_amount : null,
            'shipping_fee' => $order ? (string) $order->shipping_fee : null,
            'item_count' => $order ? (int) $order->items->sum('quantity') : 0,
            'items' => $order ? $order->items->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Product',
                'variant' => $item->variantLabel(),
                'quantity' => (int) $item->quantity,
            ])->values()->all() : [],
        ];
    }

    private function logRiderEvent(Rider $rider, Delivery $delivery, array $handoff): void
    {
        $ownerUserId = (int) ($rider->partner?->user_id ?? 0);
        if ($ownerUserId <= 0) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Logistics owner account is unavailable; custody cannot be recorded safely.',
            ]);
        }

        DB::table('parcel_scan_events')->insert([
            'delivery_id' => $delivery->id,
            'sorting_center_id' => (int) $handoff['center_id'],
            'operator_user_id' => $ownerUserId,
            'operator_staff_id' => null,
            'operator_rider_id' => $rider->id,
            'parcel_transfer_id' => $handoff['parcel_transfer_id'],
            'event_type' => $handoff['event'],
            'scanned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function result(Delivery $delivery, array $handoff, string $status, string $message): array
    {
        return [
            'delivery_id' => $delivery->id,
            'tracking_code' => $handoff['tracking_code'],
            'action' => $handoff['action'],
            'event' => $handoff['event'],
            'delivery_status' => $status,
            'from' => $handoff['from'],
            'to' => $handoff['to'],
            'center' => $handoff['center'],
            'message' => $message,
        ];
    }

    private function center(?int $centerId, int $partnerId): SortingCenter
    {
        if (! $centerId) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Required sorting center is missing for this handoff.',
            ]);
        }

        $center = SortingCenter::whereKey($centerId)
            ->where('logistics_partner_id', $partnerId)
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();

        if (! $center) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Required sorting center is inactive or unavailable.',
            ]);
        }

        return $center;
    }

    private function trackingCode(string $trackingCode): string
    {
        $code = strtoupper(trim($trackingCode));
        if (! preg_match('/^SHP-[0-9]{8}-[0-9]{6,}$/', $code)) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Scan a valid ShopHop parcel QR label.',
            ]);
        }

        return $code;
    }

    private function assertActiveRider(Rider $rider): void
    {
        if ($rider->status !== 'active'
            || ! $rider->email_verified_at
            || ! $rider->partner
            || $rider->partner?->user?->status !== 'approved'
            || ! $rider->sorting_center_id) {
            throw ValidationException::withMessages([
                'rider' => 'Your Rider account or assigned Logistics center is not active.',
            ]);
        }
    }
}
