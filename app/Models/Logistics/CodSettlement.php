<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodSettlement extends Model
{
    // These ordered states describe cash custody, not Seller payout or general payment verification.
    public const COLLECTED = 'collected';
    public const REMITTED = 'remitted';
    public const RECONCILED = 'reconciled';

    protected $fillable = [
        'order_id', 'delivery_id', 'logistics_partner_id', 'expected_amount', 'collected_amount',
        'status', 'collected_by_rider_id', 'collected_at', 'remitted_by_rider_id', 'remitted_at',
        'received_amount', 'reconciled_by_user_id', 'reconciled_at',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2', 'collected_amount' => 'decimal:2',
        'received_amount' => 'decimal:2', 'collected_at' => 'datetime',
        'remitted_at' => 'datetime', 'reconciled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'collected_by_rider_id');
    }

    public function remitter(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'remitted_by_rider_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }
}
