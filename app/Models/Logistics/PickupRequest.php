<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupRequest extends Model
{
    public const REQUESTED = 'requested';
    public const ASSIGNED = 'assigned';
    public const PICKED_UP = 'picked_up';
    public const RECEIVED = 'received';

    protected $fillable = [
        'order_id',
        'logistics_partner_id',
        'origin_sorting_center_id',
        'status',
        'origin_province_code',
        'origin_province_name',
        'origin_municipality_code',
        'origin_municipality_name',
        'origin_barangay_code',
        'origin_barangay_name',
        'origin_street',
        'requested_at',
        'assigned_at',
        'picked_up_at',
        'received_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function originSortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'origin_sorting_center_id');
    }

    public function originAddressLabel(): string
    {
        return collect([
            $this->origin_street,
            $this->origin_barangay_name,
            $this->origin_municipality_name,
            $this->origin_province_name,
        ])->filter()->implode(', ');
    }
}
