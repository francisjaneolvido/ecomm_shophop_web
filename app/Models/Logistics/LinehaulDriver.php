<?php

namespace App\Models\Logistics;

use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class LinehaulDriver extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'logistics_partner_id', 'name', 'contact_no', 'license_no', 'license_expiry',
        'email', 'password', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'password' => 'hashed',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(LinehaulTrip::class, 'driver_id');
    }
}
