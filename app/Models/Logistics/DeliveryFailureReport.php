<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFailureReport extends Model
{
    protected $fillable = [
        'delivery_id',
        'order_id',
        'logistics_partner_id',
        'rider_id',
        'attempt_no',
        'reason',
        'reported_at',
    ];

    protected $casts = [
        'attempt_no' => 'integer',
        'reported_at' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
