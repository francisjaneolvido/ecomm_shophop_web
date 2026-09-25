<?php

namespace App\Models\Logistics;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rider extends Authenticatable
{
    // Riders use Laravel sessions without entering the shared users.account_type enum; credentials stay hidden on serialization.
    protected $fillable = ['logistics_partner_id', 'name', 'vehicle_type', 'status', 'email', 'password'];
    protected $hidden = ['password'];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\LogisticsPartner::class, 'logistics_partner_id');
    }
}
