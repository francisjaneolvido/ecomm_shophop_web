<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class OrderFinancial extends Model
{
    protected $fillable = ['order_id','merchandise','seller_discount','platform_discount','shipping_fee',
        'shipping_discount','cod_fee','commission','seller_earnings','logistics_earnings','platform_net',
        'settlement_hold','hold_reason'];
    protected $casts = ['settlement_hold' => 'boolean'];
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
