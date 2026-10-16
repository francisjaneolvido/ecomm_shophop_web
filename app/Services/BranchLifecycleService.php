<?php

namespace App\Services;

use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchLifecycleService
{
    public const ARCHIVE_AFTER_DAYS = 30;

    public function blockers(SortingCenter $center): array
    {
        $reasons = [];
        if ($center->is_main) $reasons[] = 'The main sorting hub cannot be archived.';
        if ($center->status !== 'inactive') $reasons[] = 'Deactivate the branch first.';
        if (Delivery::where('logistics_partner_id', $center->logistics_partner_id)
            ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
            ->where(fn ($q) => $q->where('origin_sorting_center_id', $center->id)
                ->orWhere('current_sorting_center_id', $center->id)
                ->orWhere('destination_sorting_center_id', $center->id))->exists()) {
            $reasons[] = 'Unfinished parcels are still assigned to this branch.';
        }
        if (ParcelTransfer::where('status', ParcelTransfer::IN_TRANSIT)
            ->where(fn ($q) => $q->where('from_sorting_center_id', $center->id)
                ->orWhere('to_sorting_center_id', $center->id))->exists()) {
            $reasons[] = 'Inter-branch transfers are still in transit.';
        }
        if ($center->riders()->exists()) {
            $reasons[] = 'Reassign Riders to another branch before archiving.';
        }
        return $reasons;
    }

    public function archive(int $id, ?int $partnerId = null, bool $requireDue = false): bool
    {
        return DB::transaction(function () use ($id, $partnerId, $requireDue) {
            $query = SortingCenter::whereKey($id)->lockForUpdate();
            if ($partnerId !== null) $query->where('logistics_partner_id', $partnerId);
            $center = $query->firstOrFail();
            if ($center->archived_at) return false;
            if ($requireDue && (! $center->deactivated_at || $center->deactivated_at->gt(now()->subDays(self::ARCHIVE_AFTER_DAYS)))) {
                return false;
            }
            $blockers = $this->blockers($center);
            if ($blockers) {
                if ($requireDue) return false; // due but unsafe: keep it visible for the operator
                throw ValidationException::withMessages(['branch' => implode(' ', $blockers)]);
            }
            // Do not delete the row or children: past parcel scans and finance attribution must survive.
            $center->staff->each(fn ($staff) => $staff->tokens()->delete());
            $center->update(['archived_at' => now(), 'status' => 'inactive']);
            return true;
        });
    }

    public function restore(int $id, int $partnerId): void
    {
        DB::transaction(function () use ($id, $partnerId) {
            $center = SortingCenter::whereKey($id)->where('logistics_partner_id', $partnerId)
                ->lockForUpdate()->firstOrFail();
            if (! $center->archived_at || $center->is_main) {
                throw ValidationException::withMessages(['branch' => 'Only archived non-main branches can be restored.']);
            }
            // Restored branches are drafts: manager and coverage must be reviewed before activation.
            $center->update(['archived_at' => null, 'deactivated_at' => null, 'status' => 'inactive']);
        });
    }
}
