<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class PickupRiderAccrual extends Model
{
    protected $table = 'pickup_rider_accruals';
    protected $fillable = ['order_id','delivery_id','logistics_partner_id','rider_id','received_center_id','amount','status','earned_at'];
    protected $casts = ['earned_at'=>'datetime', 'amount'=>'decimal:2'];
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
