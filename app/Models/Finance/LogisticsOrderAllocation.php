<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class LogisticsOrderAllocation extends Model
{
    protected $fillable = ['order_id','logistics_partner_id','gross_shipping','pickup_rider_fee','delivery_rider_fee',
        'origin_branch_amount','destination_branch_amount','province_hub_count','province_hub_amount','linehaul_pool','logistics_margin'];
    protected $casts = ['province_hub_count' => 'integer'];
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
