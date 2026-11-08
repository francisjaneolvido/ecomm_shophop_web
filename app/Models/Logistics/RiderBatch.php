<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class RiderBatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['capacity_snapshot' => 'array', 'scheduled_start' => 'datetime', 'scheduled_end' => 'datetime',
        'started_at' => 'datetime', 'completed_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(RiderBatchItem::class)->orderBy('stop_sequence')->orderBy('id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function branch()
    {
        return $this->belongsTo(SortingCenter::class, 'sorting_center_id');
    }
}
