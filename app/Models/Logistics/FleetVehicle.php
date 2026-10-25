<?php

namespace App\Models\Logistics;

use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FleetVehicle extends Model
{
    public const AVAILABLE = 'available';
    public const IN_TRANSIT = 'in_transit';
    public const MAINTENANCE = 'maintenance';
    public const INACTIVE = 'inactive';

    protected $fillable = [
        'logistics_partner_id', 'home_hub_id', 'code', 'plate_number', 'vehicle_type',
        'capacity_kg', 'volume_capacity_m3', 'owner_type', 'status', 'is_primary',
    ];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'volume_capacity_m3' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function homeHub(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'home_hub_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(LinehaulTrip::class, 'vehicle_id');
    }
}
