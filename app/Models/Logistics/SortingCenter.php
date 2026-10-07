<?php

namespace App\Models\Logistics;

use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SortingCenter extends Model
{
    protected $fillable = [
        'logistics_partner_id',
        'code',
        'name',
        'is_main',
        'status',
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
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function coverageAreas(): HasMany
    {
        return $this->hasMany(LogisticsCoverageArea::class, 'sorting_center_id');
    }

    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class, 'sorting_center_id');
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
