<?php

namespace App\Models\Logistics;

use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/** Company-provisioned branch operator; never registered as an independent Logistics partner. */
class BranchStaff extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'logistics_branch_staff';

    protected $fillable = ['logistics_partner_id', 'sorting_center_id', 'name', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function sortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'sorting_center_id');
    }

    public function activeForBranch(int $partnerId, int $branchId): bool
    {
        return $this->status === 'active'
            && (int) $this->logistics_partner_id === $partnerId
            && (int) $this->sorting_center_id === $branchId
            && $this->sortingCenter?->status === 'active'
            && ! $this->sortingCenter?->archived_at
            && $this->partner?->user?->status === 'approved';
    }
}
