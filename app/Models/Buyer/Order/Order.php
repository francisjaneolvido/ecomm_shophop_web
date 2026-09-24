<?php

namespace App\Models\Buyer\Order;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /*
    |--------------------------------------------------------------------------
    | Status helpers
    |--------------------------------------------------------------------------
    */

    public const STATUS_TO_PAY = 'to-pay';
    public const STATUS_TO_SHIP = 'to-ship';
    public const STATUS_TO_RECEIVE = 'to-receive';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_TO_PAY => 'To Pay',
            self::STATUS_TO_SHIP => 'To Ship',
            self::STATUS_TO_RECEIVE => 'To Receive',
            self::STATUS_COMPLETED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusNote(): string
    {
        return match ($this->status) {
            // Status alone does not prove payment verification, packing, or courier movement.
            self::STATUS_TO_PAY => 'Online payment verification is unavailable.',
            self::STATUS_TO_SHIP => 'Awaiting seller fulfillment.',
            self::STATUS_TO_RECEIVE => 'Courier details are unavailable.',
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
        if ($this->status === self::STATUS_CANCELLED) {
            return [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Cancelled', 'done' => true],
            ];
        }

        $order = [
            self::STATUS_TO_PAY,
            self::STATUS_TO_SHIP,
            self::STATUS_TO_RECEIVE,
            self::STATUS_COMPLETED,
        ];

        $labels = [
            self::STATUS_TO_PAY => 'Payment',
            self::STATUS_TO_SHIP => 'Preparing',
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

    public function canReport(): bool
    {
        // No order report persistence or submission route exists, regardless of status.
        return false;
    }
}
