<?php

namespace App\Models\Finance;

use App\Models\Buyer\Order\Order;
use Illuminate\Database\Eloquent\Model;

class OrderFinancial extends Model
{
    protected $fillable = ['order_id', 'merchandise', 'seller_discount', 'seller_shipping_discount', 'platform_discount', 'shipping_fee',
        'shipping_route_band', 'shipping_weight_kg', 'shipping_base_fee', 'shipping_distance_fee', 'shipping_weight_fee', 'shipping_linehaul_fee', 'shipping_express_surcharge',
        'shipping_discount', 'cod_fee', 'commission', 'seller_earnings', 'logistics_earnings', 'platform_net',
        'settlement_hold', 'hold_reason', 'product_rate_snapshot', 'shipping_rate_snapshot', 'shipping_commission', 'financial_model', 'platform_rule_id', 'seller_hold_days'];

    protected $casts = ['settlement_hold' => 'boolean', 'product_rate_snapshot' => 'decimal:2', 'shipping_rate_snapshot' => 'decimal:2', 'shipping_commission' => 'decimal:2', 'seller_earnings' => 'decimal:2', 'logistics_earnings' => 'decimal:2', 'platform_net' => 'decimal:2'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
