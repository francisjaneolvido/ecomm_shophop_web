<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class SettlementEntry extends Model
{
    protected $fillable = ['order_id','kind','payee_id','amount','status','distribution_reference','distributed_at','distributed_by_user_id','payment_reference','paid_at','paid_by_user_id'];
    protected $casts = ['distributed_at' => 'datetime', 'paid_at' => 'datetime'];
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
