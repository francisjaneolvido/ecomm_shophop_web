<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    public const PICKUP_ASSIGNED = 'assigned';
    public const PICKUP_ACCEPTED = 'pickup_accepted';
    public const PICKED_UP = 'picked_up';
    public const AT_SORTING_CENTER = 'at_sorting_center';
    public const SORTED = 'sorted';
    public const DELIVERY_ASSIGNED = 'delivery_assigned';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const DELIVERY_FAILED = 'delivery_failed';
    public const RETURNED = 'returned';

    // Order owns Buyer-facing grouping; Delivery owns the detailed courier/sorting lifecycle.
    protected $fillable = [
        'order_id',
        'tracking_code',
        'logistics_partner_id',
        'origin_sorting_center_id',
        'current_sorting_center_id',
        'destination_sorting_center_id',
        'destination_area_id',
        'rider_id',
        'delivery_rider_id',
        'status',
        'assigned_at',
        'pickup_accepted_at',
        'picked_up_at',
        'at_sorting_center_at',
        'sorted_at',
        'delivery_assigned_at',
        'out_for_delivery_at',
        'in_transit_at',
        'delivered_at',
        'delivery_failed_at',
        'failure_reason',
        'returned_at',
        'delivery_attempts',
        'pickup_rider_id',
        'transit_rider_id',
        'delivered_rider_id',
        'proof_path',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'pickup_accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'at_sorting_center_at' => 'datetime',
        'sorted_at' => 'datetime',
        'delivery_assigned_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_failed_at' => 'datetime',
        'returned_at' => 'datetime',
        'delivery_attempts' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Current rider responsible for the next Rider-owned action. */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function deliveryRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'delivery_rider_id');
    }

    public function destinationArea(): BelongsTo
    {
        return $this->belongsTo(LogisticsCoverageArea::class, 'destination_area_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function codSettlement(): HasOne
    {
        return $this->hasOne(CodSettlement::class);
    }


    public function originSortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'origin_sorting_center_id');
    }

    public function currentSortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'current_sorting_center_id');
    }

    public function destinationSortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'destination_sorting_center_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(ParcelTransfer::class)->orderBy('id');
    }


    public function failureReports(): HasMany
    {
        return $this->hasMany(DeliveryFailureReport::class)->orderBy('reported_at')->orderBy('id');
    }

    public function pickupRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'pickup_rider_id');
    }

    public function deliveredRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'delivered_rider_id');
    }

    public function statusLabel(): string
    {
        if ($this->status === self::SORTED) {
            $activeTransfer = $this->relationLoaded('transfers')
                ? $this->transfers->firstWhere('status', ParcelTransfer::IN_TRANSIT)
                : $this->transfers()->with('toCenter')->where('status', ParcelTransfer::IN_TRANSIT)->latest('id')->first();

            if ($activeTransfer) {
                $destination = $activeTransfer->toCenter?->name;
                return $destination ? 'In Transfer to '.$destination : 'In Center Transfer';
            }

            if ($this->current_sorting_center_id && $this->destination_sorting_center_id
                && (int) $this->current_sorting_center_id !== (int) $this->destination_sorting_center_id) {
                return 'Sorted for Center Transfer';
            }
        }

        return match ($this->status) {
            self::PICKUP_ASSIGNED => 'Pickup Rider Assigned',
            self::PICKUP_ACCEPTED => 'Pickup Accepted',
            self::PICKED_UP => 'Picked Up',
            self::AT_SORTING_CENTER => 'At Sorting Center',
            self::SORTED => 'Sorted',
            self::DELIVERY_ASSIGNED => 'Assigned to Delivery Rider',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::DELIVERY_FAILED => 'Delivery Failed / Reported',
            // Kept only for historical rows created before ShopHop removed return processing.
            self::RETURNED => 'Legacy Returned Record',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
