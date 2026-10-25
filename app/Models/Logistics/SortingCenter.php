<?php

namespace App\Models\Logistics;

use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SortingCenter extends Model
{
    protected $fillable = [
        'logistics_partner_id',
        'user_id',
        'type',
        'parent_id',
        'code',
        'name',
        'is_main',
        'status',
        'deactivated_at', 'archived_at',
        'contact_no',
        'region',
        'province',
        'municipality',
        'barangay',
        'street_no',
        'unit_no',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'deactivated_at' => 'datetime', 'archived_at' => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    /** Branch login account. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Province hub of a station hub. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Station hubs under a province hub. */
    public function stationHubs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function coverageAreas(): HasMany
    {
        return $this->hasMany(LogisticsCoverageArea::class, 'sorting_center_id');
    }

    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class, 'sorting_center_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(BranchStaff::class, 'sorting_center_id');
    }


    public function fleetVehicles(): HasMany
    {
        return $this->hasMany(FleetVehicle::class, 'home_hub_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(ParcelTransfer::class, 'from_sorting_center_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(ParcelTransfer::class, 'to_sorting_center_id');
    }

    public function addressLabel(): string
    {
        return collect([
            $this->unit_no,
            $this->street_no,
            $this->barangay,
            $this->municipality,
            $this->province,
            $this->region,
        ])->filter()->implode(', ');
    }
}