<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function codSettlement(): HasOne
    {
        // Delivery proof and cash settlement remain distinct one-to-one records.
        return $this->hasOne(CodSettlement::class);
    }

    public function pickupRider(): BelongsTo
    {
        // Event actor relationships remain historical facts even if assignment data changes later.
        return $this->belongsTo(Rider::class, 'pickup_rider_id');
    }

    public function deliveredRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'delivered_rider_id');
    }
}
