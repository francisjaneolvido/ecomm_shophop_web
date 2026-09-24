<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    // Order owns Buyer-facing status; this record owns partner, Rider, and event times.
    protected $fillable = ['order_id', 'logistics_partner_id', 'rider_id', 'status', 'assigned_at', 'picked_up_at', 'in_transit_at', 'delivered_at'];

    protected $casts = ['assigned_at' => 'datetime', 'picked_up_at' => 'datetime', 'in_transit_at' => 'datetime', 'delivered_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
