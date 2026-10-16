<?php

namespace App\Models\Logistics;

use App\Models\Buyer\Order\Order;
use Illuminate\Database\Eloquent\Model;

class ReturnCase extends Model
{
    public const REQUESTED = 'requested';
    public const AT_BRANCH = 'at_branch';
    public const ASSIGNED = 'assigned';
    public const IN_TRANSIT = 'in_transit';
    public const AWAITING_SELLER = 'awaiting_seller';
    public const SELLER_RECEIVED = 'seller_received';

    protected $fillable = ['order_id','delivery_id','logistics_partner_id','status','reason',
        'return_rider_id','return_branch_id','requested_by_user_id','received_by_user_id',
        'branch_received_at','rider_collected_at','handover_proof_path','handover_submitted_at',
        'seller_received_at','refund_status','refund_amount','refund_reference','refunded_by_user_id','refunded_at'];
    protected $casts = ['branch_received_at'=>'datetime','rider_collected_at'=>'datetime',
        'handover_submitted_at'=>'datetime','seller_received_at'=>'datetime','refunded_at'=>'datetime',
        'refund_amount'=>'decimal:2'];
    public function order() { return $this->belongsTo(Order::class); }
    public function delivery() { return $this->belongsTo(Delivery::class); }
    public function returnRider() { return $this->belongsTo(Rider::class, 'return_rider_id'); }
    public function branch() { return $this->belongsTo(SortingCenter::class, 'return_branch_id'); }
}
