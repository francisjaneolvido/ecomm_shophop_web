<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    // Partner ownership and availability are persisted eligibility facts, not board UI state.
    protected $fillable = ['logistics_partner_id', 'name', 'vehicle_type', 'status'];
}
