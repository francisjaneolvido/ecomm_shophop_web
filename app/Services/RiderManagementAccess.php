<?php

namespace App\Services;

use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderManagementAccess
{
    public function request(Request $request): array
    {
        $actor = $request->routeIs('logistics.staff.*') ? Auth::guard('logistics_staff')->user() : $request->user();

        return $this->actor($actor);
    }

    public function actor($actor): array
    {
        abort_unless($actor instanceof User || $actor instanceof BranchStaff, 403);
        $actor = $actor->fresh();
        $center = null;
        if ($actor instanceof BranchStaff) {
            abort_unless($actor->role === 'manager' && $actor->activeForBranch((int) $actor->logistics_partner_id, (int) $actor->sorting_center_id), 403);
            $center = $actor->sortingCenter;
            $company = $actor->partner;
            $type = 'staff';
        } else {
            abort_unless($actor->status === 'approved', 403);
            if ($actor->account_type === 'logistics') {
                $company = $actor->logisticsPartner;
            } else {
                abort_unless($actor->account_type === 'branch', 403);
                $center = SortingCenter::where('user_id', $actor->id)->firstOrFail();
                $company = $center->partner;
            }
            $type = 'user';
        }
        abort_unless($company && $company->user?->status === 'approved', 403);
        if ($center) {
            abort_unless($center->status === 'active' && ! $center->archived_at
                && ($center->is_main || $center->type === 'station_hub'), 403);
            $role = $center->is_main ? 'main' : 'branch';
        } else {
            $role = 'main';
        } // Existing approved company owner retains Main Hub administrative authority.

        return ['company_id' => (int) $company->id, 'role' => $role, 'branch_id' => $role === 'branch' ? (int) $center->id : null,
            'actor_type' => $type, 'actor_id' => (int) $actor->id, 'center' => $center, 'company' => $company];
    }

    public function main(array $scope): void
    {
        abort_unless($scope['role'] === 'main', 403);
    }

    public function branch(array $scope, ?Rider $rider = null): SortingCenter
    {
        abort_unless($scope['role'] === 'branch', 403);
        $branch = SortingCenter::where('logistics_partner_id', $scope['company_id'])->where('type', 'station_hub')
            ->where('is_main', false)->where('status', 'active')->whereNull('archived_at')->findOrFail($scope['branch_id']);
        if ($rider) {
            abort_unless((int) $rider->logistics_partner_id === $scope['company_id'] && (int) $rider->sorting_center_id === (int) $branch->id, 404);
        }

        return $branch;
    }
}
