<?php

namespace App\Models\Logistics;

use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinehaulTrip extends Model
{
    public const SCHEDULED = 'scheduled';
    public const IN_TRANSIT = 'in_transit';
    public const ARRIVED = 'arrived';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'code', 'logistics_partner_id', 'from_sorting_center_id', 'to_sorting_center_id',
        'vehicle_id', 'driver_id', 'assigned_by_user_id', 'status', 'note',
        'departed_at', 'arrived_at', 'completed_at',
    ];

    protected $casts = [
        'departed_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function partner(): BelongsTo { return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id'); }
    public function fromCenter(): BelongsTo { return $this->belongsTo(SortingCenter::class, 'from_sorting_center_id'); }
    public function toCenter(): BelongsTo { return $this->belongsTo(SortingCenter::class, 'to_sorting_center_id'); }
    public function vehicle(): BelongsTo { return $this->belongsTo(FleetVehicle::class, 'vehicle_id'); }
    public function driver(): BelongsTo { return $this->belongsTo(LinehaulDriver::class, 'driver_id'); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
    public function manifests(): HasMany { return $this->hasMany(ParcelManifest::class, 'linehaul_trip_id'); }
    public function transfers(): HasMany { return $this->hasMany(ParcelTransfer::class, 'linehaul_trip_id'); }
}
