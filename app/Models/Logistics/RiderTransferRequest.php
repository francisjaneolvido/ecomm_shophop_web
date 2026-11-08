<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class RiderTransferRequest extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['decided_at' => 'datetime'];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(SortingCenter::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(SortingCenter::class, 'to_branch_id');
    }
}
