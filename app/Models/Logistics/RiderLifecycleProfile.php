<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class RiderLifecycleProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['preferred_coverage' => 'array', 'accepted_coverage' => 'array', 'document_request' => 'array',
        'legacy_onboarding' => 'boolean', 'assignment_confirmation_required' => 'boolean', 'activated_at' => 'datetime', 'deactivated_at' => 'datetime'];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function preferredBranch()
    {
        return $this->belongsTo(SortingCenter::class, 'preferred_branch_id');
    }
}
