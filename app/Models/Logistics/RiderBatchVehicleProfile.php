<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class RiderBatchVehicleProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['limits' => 'array'];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}
