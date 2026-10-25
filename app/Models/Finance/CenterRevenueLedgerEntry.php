<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class CenterRevenueLedgerEntry extends Model
{
    protected $fillable = ['order_id','logistics_partner_id','sorting_center_id','kind','amount','status','posted_at','note'];
    protected $casts = ['posted_at' => 'datetime'];
    public function center() { return $this->belongsTo(\App\Models\Logistics\SortingCenter::class, 'sorting_center_id'); }
    public function order() { return $this->belongsTo(\App\Models\Buyer\Order\Order::class); }
}
