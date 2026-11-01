<?php
namespace App\Models\Logistics;
use Illuminate\Database\Eloquent\Model;
class RouteLink extends Model
{
    protected $table = 'logistics_route_links';
    protected $fillable = ['logistics_partner_id','from_center_id','to_center_id','mode','status','notes','verified_at','verified_by_user_id'];
    protected $casts = ['verified_at'=>'datetime'];
    public function fromCenter() { return $this->belongsTo(SortingCenter::class, 'from_center_id'); }
    public function toCenter() { return $this->belongsTo(SortingCenter::class, 'to_center_id'); }
}
