<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class LinehaulDriverEarning extends Model
{
    protected $fillable = ['linehaul_trip_id','logistics_partner_id','driver_id','amount','status','available_at','payment_reference','paid_at','paid_by_user_id'];
    protected $casts = ['available_at' => 'datetime', 'paid_at' => 'datetime'];
    public function trip() { return $this->belongsTo(\App\Models\Logistics\LinehaulTrip::class, 'linehaul_trip_id'); }
    public function driver() { return $this->belongsTo(\App\Models\Logistics\LinehaulDriver::class, 'driver_id'); }
}
