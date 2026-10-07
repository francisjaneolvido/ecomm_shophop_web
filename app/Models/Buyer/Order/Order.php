<?php

namespace App\Models\Buyer\Order;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Voucher;
// Delivery adds Logistics milestones to the shared Order without becoming a second Buyer status store.
use App\Models\Logistics\Delivery;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\PickupRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'buyer_id',
        'checkout_group_id',
        'seller_id',
        'status',
        'total_amount',
        'shipping_method',
        'shipping_fee',
        'payment_method',
        'manual_cashless_payment_id',
        'cod_fee',
        'voucher_id',
        'voucher_discount',
        'note',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'gcash_reference',
        'gcash_proof_path',
        'tracking_code',
        'delivery_region_code',
        'delivery_region_name',
        'delivery_province_code',
        'delivery_province_name',
        'delivery_municipality_code',
        'delivery_municipality_name',
        'delivery_barangay_code',
        'delivery_barangay_name',
        'delivery_street',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'voucher_discount' => 'decimal:2',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        // One Seller Order has one Logistics assignment; checkout_group_id never merges packages.
        return $this->hasOne(Delivery::class);
    }

    public function pickupRequest(): HasOne
    {
        return $this->hasOne(PickupRequest::class);
    }

    public function codSettlement(): HasOne
    {
        // Missing relation on an old completed COD Order means cash settlement was never recorded.
        return $this->hasOne(CodSettlement::class);
    }

    public function manualCashlessPayment(): BelongsTo
    {
        // Explicit membership plus Buyer/group checks keep a copied UUID from releasing a forged Order.
        return $this->belongsTo(ManualCashlessPayment::class, 'manual_cashless_payment_id');
    }

    public function isPaymentEligible(): bool
    {
        // A historical non-COD row cannot enter fulfillment without this verified group contract.
        if ($this->payment_method !== 'online') {
            return $this->payment_method === 'cod';
        }

        $payment = $this->manualCashlessPayment;
        return $this->payment_method === 'online' && $payment
            && (int) $payment->buyer_id === (int) $this->buyer_id
            && $payment->checkout_group_id === $this->checkout_group_id
            && $payment->status === ManualCashlessPayment::VERIFIED;
    }

    public function scopePaymentEligible(Builder $query): Builder
    {
        // Queue queries use the same Buyer-scoped rule as individual Order actions.
        return $query->where(function (Builder $query) {
            $query->where('payment_method', 'cod')->orWhere(function (Builder $query) {
                $query->where('payment_method', 'online')->whereHas('manualCashlessPayment', function (Builder $payment) {
                    $payment->where('status', ManualCashlessPayment::VERIFIED)
                        ->whereColumn('manual_cashless_payments.buyer_id', 'orders.buyer_id')
                        ->whereColumn('manual_cashless_payments.checkout_group_id', 'orders.checkout_group_id');
                });
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status helpers
    |--------------------------------------------------------------------------
    */

    public const STATUS_TO_PAY = 'to-pay';
    public const STATUS_TO_SHIP = 'to-ship';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    // Seller preview stages become persisted Order states; to-receive remains beyond the Logistics handoff.
    public const STATUS_PREPARING = 'PREPARING';
    public const STATUS_READY_FOR_PICKUP = 'READY_FOR_PICKUP';
    public const STATUS_TO_RECEIVE = 'to-receive';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function statusLabel(): string
    {
        // A closed Online group has a real terminal Order status, even though it is never payment eligible.
        if ($this->status === self::STATUS_CANCELLED) {
            return 'Cancelled';
        }
        // An unverified Online Order remains placed, but Buyer and Seller must see payment state first.
        if ($this->payment_method === 'online' && ! $this->isPaymentEligible()) {
            return match ($this->manualCashlessPayment?->status) {
                ManualCashlessPayment::AWAITING_PROOF => 'Awaiting Payment Proof',
                ManualCashlessPayment::PENDING_REVIEW => 'Pending Payment Verification',
                ManualCashlessPayment::REJECTED => 'Payment Rejected / Resubmission Required',
                default => 'Payment Verification Unavailable',
            };
        }
        return match ($this->status) {
            self::STATUS_TO_PAY => 'To Pay',
            self::STATUS_TO_SHIP => 'Placed',
            self::STATUS_CONFIRMED => 'Confirmed',
            // Buyer and Seller read the same status; readiness does not claim a Rider has collected the parcel.
            self::STATUS_PREPARING => 'Preparing',
            self::STATUS_READY_FOR_PICKUP => $this->delivery?->statusLabel() ?? 'Ready for Pickup',
            self::STATUS_TO_RECEIVE => $this->delivery?->statusLabel() ?? 'To Receive',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusNote(): string
    {
        // The payment preserves the closure reason while the Order stops showing proof instructions.
        if ($this->status === self::STATUS_CANCELLED) {
            return $this->manualCashlessPayment?->status === ManualCashlessPayment::EXPIRED
                ? 'The payment deadline passed and this order expired.' : 'This order was cancelled.';
        }
        // Seller fulfillment messaging cannot conceal an unresolved Buyer payment review.
        if ($this->payment_method === 'online' && ! $this->isPaymentEligible()) {
            return 'Submit or correct payment proof for ShopHop Admin review.';
        }
        return match ($this->status) {
            // Status alone does not prove payment verification, packing, or courier movement.
            self::STATUS_TO_PAY => 'Online payment verification is unavailable.',
            self::STATUS_TO_SHIP => 'Order placed. Waiting for the seller to accept it.',
            self::STATUS_CONFIRMED => 'The seller accepted this order and will prepare it next.',
            // These Seller states report preparation only; neither proves courier pickup or delivery.
            self::STATUS_PREPARING => 'The seller is preparing this order.',
            // Assignment is visible after persistence, while Seller readiness alone cannot imply collection.
            self::STATUS_READY_FOR_PICKUP => match ($this->delivery?->status) {
                Delivery::PICKUP_ASSIGNED => 'Pickup Rider assigned; acceptance and pickup are pending.',
                Delivery::PICKUP_ACCEPTED => 'Pickup Rider accepted the assignment and is heading to the Seller.',
                default => 'Ready for pickup; courier assignment and pickup are pending Logistics.',
            },
            // Only an attached persisted Delivery can justify pickup or transit messaging.
            self::STATUS_TO_RECEIVE => $this->deliveryStatusNote(),
            self::STATUS_COMPLETED => 'Buyer confirmed receipt. Transaction completed.',
            self::STATUS_CANCELLED => 'This order was cancelled.',
            default => '',
        };
    }

    private function deliveryStatusNote(): string
    {
        $delivery = $this->delivery;
        if (! $delivery) {
            return 'Courier details are unavailable.';
        }

        if ($delivery->status === Delivery::SORTED) {
            $delivery->loadMissing([
                'transfers.fromCenter',
                'transfers.toCenter',
                'currentSortingCenter',
                'destinationSortingCenter',
            ]);

            $activeTransfer = $delivery->transfers->firstWhere(
                'status',
                \App\Models\Logistics\ParcelTransfer::IN_TRANSIT
            );

            if ($activeTransfer) {
                $from = $activeTransfer->fromCenter?->name ?? 'current sorting center';
                $to = $activeTransfer->toCenter?->name ?? 'next sorting center';

                return "Parcel is in center transfer from {$from} to {$to}.";
            }

            if ($delivery->currentSortingCenter && $delivery->destinationSortingCenter
                && (int) $delivery->current_sorting_center_id === (int) $delivery->destination_sorting_center_id) {
                return 'Parcel arrived at the destination sorting center and is ready for local rider assignment.';
            }

            if ($delivery->currentSortingCenter && $delivery->destinationSortingCenter) {
                return 'Parcel was sorted and is waiting for transfer to '.$delivery->destinationSortingCenter->name.'.';
            }

            return 'Parcel was sorted according to its destination area.';
        }

        return match ($delivery->status) {
            Delivery::PICKED_UP => 'Pickup Rider collected the parcel and is bringing it to the sorting center.',
            Delivery::AT_SORTING_CENTER => 'Parcel was scanned and received at the sorting center.',
            Delivery::DELIVERY_ASSIGNED => 'A delivery Rider was assigned for the destination area.',
            Delivery::OUT_FOR_DELIVERY => 'Your parcel is out for delivery.',
            Delivery::DELIVERED => 'Rider marked the parcel delivered. Please confirm receipt to complete the order.',
            Delivery::DELIVERY_FAILED => 'The delivery attempt was reported as failed. Logistics can review the report and schedule another attempt.',
            Delivery::PICKUP_ASSIGNED => 'Pickup Rider assigned; collection from the Seller is pending.',
            Delivery::PICKUP_ACCEPTED => 'Pickup Rider accepted the assignment and is heading to the Seller.',
            default => 'Courier details are unavailable.',
        };
    }

    /**
     * Auditable parcel journey shared by Buyer and Seller views.
     * Only persisted timestamps are shown; no synthetic movement is invented.
     */
    public function trackingTimeline(): array
    {
        $this->loadMissing([
            'pickupRequest.partner',
            'pickupRequest.originSortingCenter',
            'delivery.rider',
            'delivery.pickupRider',
            'delivery.deliveryRider',
            'delivery.originSortingCenter',
            'delivery.currentSortingCenter',
            'delivery.destinationSortingCenter',
            'delivery.transfers.fromCenter',
            'delivery.transfers.toCenter',
            'delivery.failureReports.rider',
        ]);

        $events = [];
        $add = static function ($time, string $label, string $note, string $type = 'normal') use (&$events): void {
            if (! $time) {
                return;
            }

            $events[] = [
                'time' => $time,
                'label' => $label,
                'note' => $note,
                'type' => $type,
            ];
        };

        $add($this->created_at, 'Order placed', 'Buyer placed the order.');

        if ($pickup = $this->pickupRequest) {
            $partner = $pickup->partner?->company_name ?? 'Logistics / Sorting Center';
            $origin = $pickup->originSortingCenter?->name;
            $add(
                $pickup->requested_at,
                'Pickup requested',
                $origin
                    ? "Pickup requested from {$partner}; origin center: {$origin}."
                    : "Pickup requested from {$partner}."
            );
        }

        if ($delivery = $this->delivery) {
            $add(
                $delivery->assigned_at,
                'Pickup Rider assigned',
                ($delivery->pickupRider?->name ?? $delivery->rider?->name ?? 'Rider').' was assigned for Seller pickup.'
            );
            $add(
                $delivery->pickup_accepted_at,
                'Pickup accepted',
                'The pickup Rider accepted the assignment.'
            );
            $add(
                $delivery->picked_up_at,
                'Parcel picked up',
                'Parcel collected from the Seller and headed to '.($delivery->originSortingCenter?->name ?? 'the sorting center').'.'
            );
            $add(
                $delivery->at_sorting_center_at,
                'Arrived at sorting center',
                'Parcel scanned at '.($delivery->originSortingCenter?->name ?? 'the origin sorting center').'.'
            );
            $add(
                $delivery->sorted_at,
                'Parcel sorted',
                $delivery->destinationSortingCenter
                    ? 'Destination sorting center: '.$delivery->destinationSortingCenter->name.'.'
                    : 'Parcel sorted according to destination area.'
            );

            foreach ($delivery->transfers as $transfer) {
                $from = $transfer->fromCenter?->name ?? 'Sorting Center';
                $to = $transfer->toCenter?->name ?? 'next Sorting Center';

                $add(
                    $transfer->dispatched_at,
                    'Center transfer dispatched',
                    "{$from} → {$to}.",
                    'transfer'
                );
                $add(
                    $transfer->received_at,
                    'Center transfer received',
                    "Parcel scanned and received at {$to}.",
                    'transfer'
                );
            }

            $add(
                $delivery->delivery_assigned_at,
                'Delivery Rider assigned',
                ($delivery->deliveryRider?->name ?? 'A Rider').' was assigned for final delivery.'
            );
            $add(
                $delivery->out_for_delivery_at,
                'Out for delivery',
                'Parcel left the destination sorting center for the Buyer.'
            );

            foreach ($delivery->failureReports as $report) {
                $add(
                    $report->reported_at,
                    'Delivery attempt '.$report->attempt_no.' failed',
                    $report->reason,
                    'failed'
                );
            }

            $add(
                $delivery->delivered_at,
                'Delivered',
                'Rider recorded successful delivery.',
                'success'
            );
        }

        if ($this->status === self::STATUS_COMPLETED) {
            $add($this->updated_at, 'Order completed', 'Buyer confirmed receipt.', 'success');
        }

        usort($events, static function (array $a, array $b): int {
            return $a['time']->getTimestamp() <=> $b['time']->getTimestamp();
        });

        return $events;
    }

    /**
     * Simple linear progress steps used by the order card UI.
     */
    public function progressSteps(): array
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Cancelled', 'done' => true],
            ];
        }

        if ($this->payment_method === 'online' && ! $this->isPaymentEligible()) {
            return [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Payment', 'done' => false],
            ];
        }

        $sellerStarted = in_array($this->status, [
            self::STATUS_TO_SHIP,
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ], true);
        $confirmed = in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ], true);
        $preparing = in_array($this->status, [
            self::STATUS_PREPARING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ], true);
        $ready = in_array($this->status, [
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ], true);
        $inTransit = in_array($this->status, [self::STATUS_TO_RECEIVE, self::STATUS_COMPLETED], true);
        $delivered = $this->status === self::STATUS_COMPLETED
            || $this->delivery?->status === Delivery::DELIVERED;
        $completed = $this->status === self::STATUS_COMPLETED;

        return [
            ['label' => 'Order Placed', 'done' => true],
            ...($this->payment_method === 'cod' ? [] : [['label' => 'Payment', 'done' => true]]),
            ['label' => 'Seller Confirmed', 'done' => $confirmed],
            ['label' => 'Preparing', 'done' => $preparing],
            ['label' => 'Ready for Pickup', 'done' => $ready],
            ['label' => 'In Transit', 'done' => $inTransit],
            ['label' => 'Delivered', 'done' => $delivered],
            ['label' => 'Completed', 'done' => $completed],
        ];
    }

    public function buyerStatusGroup(): string
    {
        // Closed groups move to Cancelled instead of remaining actionable in To Pay.
        if ($this->status === self::STATUS_CANCELLED) {
            return self::STATUS_CANCELLED;
        }
        // Buyer tabs keep unresolved Online Orders in To Pay despite their placed Order status.
        if ($this->payment_method === 'online' && ! $this->isPaymentEligible()) {
            return self::STATUS_TO_PAY;
        }
        // Seller preparation stays in Buyer's To Ship group until Logistics actually advances the Order.
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PREPARING, self::STATUS_READY_FOR_PICKUP], true)
            ? self::STATUS_TO_SHIP
            : $this->status;
    }

    public function canReport(): bool
    {
        // No order report persistence or submission route exists, regardless of status.
        return false;
    }
}
