<?php
namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterislandCargoTransfer extends Model
{
    public const BOOKED = 'booked';
    public const IN_TRANSIT = 'in_transit';
    public const ARRIVED = 'arrived';
    public const COMPLETED = 'completed';

    protected $fillable = ['manifest_id','logistics_partner_id','route_link_id','mode',
        'carrier_name','booking_reference','status','booked_by_user_id','departed_at','arrived_at','completed_at'];
    protected $casts = ['departed_at'=>'datetime','arrived_at'=>'datetime','completed_at'=>'datetime'];
    public function manifest(): BelongsTo { return $this->belongsTo(ParcelManifest::class); }
    public function routeLink(): BelongsTo { return $this->belongsTo(RouteLink::class); }
    public function transfers(): HasMany { return $this->hasMany(ParcelTransfer::class,'cargo_transfer_id'); }
}
