<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class OrderFinancial extends Model
{
    protected $fillable = ['order_id','merchandise','seller_discount','seller_shipping_discount','platform_discount','shipping_fee',
        'shipping_route_band','shipping_weight_kg','shipping_base_fee','shipping_distance_fee','shipping_weight_fee','shipping_linehaul_fee','shipping_express_surcharge',
        'shipping_discount','cod_fee','commission','seller_earnings','logistics_earnings','platform_net',
        'settlement_hold','hold_reason'];
    protected $casts = ['settlement_hold' => 'boolean'];
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
