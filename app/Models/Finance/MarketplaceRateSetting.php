<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class MarketplaceRateSetting extends Model
{
    protected $fillable = [
        'commission_percent', 'base_shipping_fee', 'local_distance_fee',
        'same_province_distance_fee', 'interprovince_distance_fee',
        'same_province_linehaul_fee', 'interprovince_linehaul_fee',
        'included_weight_kg', 'extra_weight_fee_per_kg', 'express_surcharge_percent',
        'pickup_rider_fee', 'delivery_rider_fee', 'origin_branch_fee',
        'destination_branch_fee', 'province_hub_fee',
        'linehaul_branch_hub_driver_fee', 'linehaul_hub_hub_driver_fee',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:2',
        'base_shipping_fee' => 'decimal:2',
        'local_distance_fee' => 'decimal:2',
        'same_province_distance_fee' => 'decimal:2',
        'interprovince_distance_fee' => 'decimal:2',
        'same_province_linehaul_fee' => 'decimal:2',
        'interprovince_linehaul_fee' => 'decimal:2',
        'included_weight_kg' => 'decimal:3',
        'extra_weight_fee_per_kg' => 'decimal:2',
        'express_surcharge_percent' => 'decimal:2',
        'pickup_rider_fee' => 'decimal:2',
        'delivery_rider_fee' => 'decimal:2',
        'origin_branch_fee' => 'decimal:2',
        'destination_branch_fee' => 'decimal:2',
        'province_hub_fee' => 'decimal:2',
        'linehaul_branch_hub_driver_fee' => 'decimal:2',
        'linehaul_hub_hub_driver_fee' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'commission_percent' => 10,
            'base_shipping_fee' => 40,
            'local_distance_fee' => 20,
            'same_province_distance_fee' => 35,
            'interprovince_distance_fee' => 50,
            'same_province_linehaul_fee' => 25,
            'interprovince_linehaul_fee' => 50,
            'included_weight_kg' => 1,
            'extra_weight_fee_per_kg' => 15,
            'express_surcharge_percent' => 40,
            'pickup_rider_fee' => 20,
            'delivery_rider_fee' => 25,
            'origin_branch_fee' => 10,
            'destination_branch_fee' => 10,
            'province_hub_fee' => 10,
            'linehaul_branch_hub_driver_fee' => 500,
            'linehaul_hub_hub_driver_fee' => 900,
        ]);
    }
}
