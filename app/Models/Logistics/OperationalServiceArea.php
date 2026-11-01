<?php

namespace App\Models\Logistics;

use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OperationalServiceArea extends Model
{
    protected $fillable = ['logistics_partner_id', 'sorting_center_id', 'province', 'municipality',
        'barangay', 'service_type', 'location_key'];

    public function sortingCenter(): BelongsTo { return $this->belongsTo(SortingCenter::class); }
    public function partner(): BelongsTo { return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id'); }
    public function riders(): BelongsToMany
    {
        return $this->belongsToMany(Rider::class, 'operational_area_rider', 'operational_service_area_id', 'rider_id');
    }

    public function label(): string
    {
        return collect([$this->barangay ?: 'All barangays', $this->municipality, $this->province])
            ->filter()->implode(', ');
    }
}
