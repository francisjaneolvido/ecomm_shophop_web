<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelTransfer extends Model
{
    public const IN_TRANSIT = 'in_transit';
    public const RECEIVED = 'received';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'delivery_id',
        'manifest_id',
        'from_sorting_center_id',
        'to_sorting_center_id',
        'status',
        'transit_rider_id',
        'linehaul_trip_id',
        'departed_at',
        'dispatched_at',
        'received_at',
    ];

    protected $casts = [
        'departed_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function transitRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'transit_rider_id');
    }


    public function linehaulTrip(): BelongsTo
    {
        return $this->belongsTo(LinehaulTrip::class, 'linehaul_trip_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function manifest(): BelongsTo
    {
        return $this->belongsTo(ParcelManifest::class, 'manifest_id');
    }

    public function fromCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'from_sorting_center_id');
    }

    public function toCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'to_sorting_center_id');
    }
}
