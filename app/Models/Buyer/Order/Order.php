<?php

namespace App\Models\Buyer\Order;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Voucher;
// Delivery adds Logistics milestones to the shared Order without becoming a second Buyer status store.
use App\Models\Logistics\Delivery;
use App\Models\Logistics\CodSettlement;
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
            self::STATUS_TO_SHIP => 'To Ship',
            // Buyer and Seller read the same status; readiness does not claim a Rider has collected the parcel.
            self::STATUS_PREPARING => 'Preparing',
            self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
            self::STATUS_TO_RECEIVE => 'To Receive',
            self::STATUS_COMPLETED => 'Delivered',
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
            self::STATUS_TO_SHIP => 'Awaiting seller fulfillment.',
            // These Seller states report preparation only; neither proves courier pickup or delivery.
            self::STATUS_PREPARING => 'The seller is preparing this order.',
            // Assignment is visible after persistence, while Seller readiness alone cannot imply collection.
            self::STATUS_READY_FOR_PICKUP => $this->delivery?->status === 'assigned'
                ? 'Rider assigned; pickup is pending.' : 'Ready for pickup; courier assignment and pickup are pending Logistics.',
            // Only an attached persisted Delivery can justify pickup or transit messaging.
            self::STATUS_TO_RECEIVE => match ($this->delivery?->status) {
                'picked_up' => 'Package picked up by Logistics.',
                'in_transit' => 'Package in transit with Logistics.',
                default => 'Courier details are unavailable.',
            },
            // Completion records only a status; there is no issue-report action or delivery proof yet.
            self::STATUS_COMPLETED => 'Order marked delivered.',
            self::STATUS_CANCELLED => 'This order was cancelled.',
            default => '',
        };
    }

    /**
     * Simple linear progress steps used by the order card UI.
     */
    public function progressSteps(): array
    {
        // Terminal cancellation wins over unresolved payment progress.
        if ($this->status === self::STATUS_CANCELLED) {
            return [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Cancelled', 'done' => true],
            ];
        }
        // The payment step is incomplete until the one group decision is verified.
        if ($this->payment_method === 'online' && ! $this->isPaymentEligible()) {
            return [['label' => 'Order Placed', 'done' => true], ['label' => 'Payment', 'done' => false]];
        }

        $order = [
            // COD is collected on delivery, so a completed Payment step would misstate its current status.
            ...($this->payment_method === 'cod' ? [] : [self::STATUS_TO_PAY]),
            self::STATUS_TO_SHIP,
            // Preparation and readiness precede any Logistics-owned shipment or receipt state.
            self::STATUS_PREPARING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ];

        $labels = [
            self::STATUS_TO_PAY => 'Payment',
            // Buyer progress must distinguish waiting, preparation, and readiness from shipped parcels.
            self::STATUS_TO_SHIP => 'Awaiting Seller',
            self::STATUS_PREPARING => 'Preparing',
            self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
            self::STATUS_TO_RECEIVE => 'Shipped',
            self::STATUS_COMPLETED => 'Delivered',
        ];

        $currentIndex = array_search($this->status, $order, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;

        $steps = [
            ['label' => 'Order Placed', 'done' => true],
        ];

        foreach ($order as $index => $key) {
            $steps[] = [
                'label' => $labels[$key],
                'done' => $index <= $currentIndex,
            ];
        }

        return $steps;
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
        return in_array($this->status, [self::STATUS_PREPARING, self::STATUS_READY_FOR_PICKUP], true)
            ? self::STATUS_TO_SHIP
            : $this->status;
    }

    public function canReport(): bool
    {
        // No order report persistence or submission route exists, regardless of status.
        return false;
    }
}
