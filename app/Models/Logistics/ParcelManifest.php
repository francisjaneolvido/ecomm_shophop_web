<?php

namespace App\Models\Logistics;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParcelManifest extends Model
{
    public const PREPARED = 'prepared';
    public const LOADING = 'loading';
    public const READY = 'ready';
    public const DISPATCHED = 'dispatched';
    public const RECEIVED = 'received';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'code',
        'logistics_partner_id',
        'from_sorting_center_id',
        'to_sorting_center_id',
        'created_by_user_id',
        'transit_rider_id',
        'linehaul_trip_id',
        'status',
        'parcel_count',
        'note',
        'prepared_at',
        'dispatched_at',
    ];

    protected $casts = [
        'parcel_count' => 'integer',
        'prepared_at' => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    public function fromCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'from_sorting_center_id');
    }

    public function toCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'to_sorting_center_id');
    }

    public function transitRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'transit_rider_id');
    }


    public function linehaulTrip(): BelongsTo
    {
        return $this->belongsTo(LinehaulTrip::class, 'linehaul_trip_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(ParcelTransfer::class, 'manifest_id');
    }

    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(
            Delivery::class,
            'parcel_manifest_items',
            'manifest_id',
            'delivery_id'
        )->withTimestamps();
    }
}
