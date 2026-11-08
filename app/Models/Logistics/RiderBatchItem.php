<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class RiderBatchItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['outcome_snapshot' => 'array', 'stop' => 'array', 'pickup_failed_at' => 'datetime', 'weight_kg' => 'decimal:3', 'volume_m3' => 'decimal:6'];

    public function batch()
    {
        return $this->belongsTo(RiderBatch::class, 'rider_batch_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
