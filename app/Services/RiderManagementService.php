<?php

namespace App\Services;

use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\RiderBatchItem;
use App\Models\Logistics\RiderLifecycleProfile;
use App\Models\Logistics\RiderTransferRequest;
use App\Models\LogisticsPartner;
use App\Models\User;
use App\Notifications\RiderApplicationDecisionNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/** Lifecycle writes serialize on the company row, including cross-Branch capacity decisions. */
class RiderManagementService
{
    public function __construct(
        private RiderManagementAccess $access,
        private RiderWorkAreaDirectory $directory,
    ) {}

    public function available(): bool
    {
        foreach (['rider_lifecycle_profiles', 'rider_barangay_capacities', 'rider_capacity_overrides',
            'rider_transfer_requests', 'rider_work_schedules', 'rider_management_events'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function requireAvailable(): void
    {
        abort_unless($this->available(), 503, 'Rider Management is awaiting the reviewed database migration.');
    }

    private function fail(string $message, string $field = 'rider'): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    private function transaction(array $scope, int $id, callable $callback): mixed
    {
        $this->requireAvailable();

        return DB::transaction(function () use ($scope, $id, $callback) {
            $company = LogisticsPartner::whereKey($scope['company_id'])->lockForUpdate()->firstOrFail();
            $owner = User::whereKey($company->user_id)->lockForUpdate()->first(['id', 'status']);
            abort_unless($owner?->status === 'approved', 403);
            $rider = Rider::where('logistics_partner_id', $company->id)->lockForUpdate()->findOrFail($id);

            return $callback($rider, $this->profile($rider));
        }, 3);
    }

    public function profile(Rider $rider): RiderLifecycleProfile
    {
        $existing = RiderLifecycleProfile::where('rider_id', $rider->id)->lockForUpdate()->first();
        if ($existing) {
            return $existing;
        }

        return RiderLifecycleProfile::create(['rider_id' => $rider->id,
            'preferred_branch_id' => $rider->sorting_center_id,
            'legacy_onboarding' => in_array($rider->status, ['active', 'suspended'], true),
            'preferred_coverage' => [],
            'application_status' => match ($rider->status) {
                'pending' => 'pending_review', 'rejected' => 'rejected', default => 'approved',
            },
            'operational_status' => match ($rider->status) {
                'active' => 'active', 'suspended' => 'suspended', default => 'pending_activation',
            },
            'availability_state' => $rider->availability_status === 'available' ? 'online' : 'offline',
        ]);
    }

    public function event(Rider $rider, array $scope, string $event, array $details = []): void
    {
        DB::table('rider_management_events')->insert([
            'logistics_partner_id' => $rider->logistics_partner_id, 'sorting_center_id' => $rider->sorting_center_id,
            'rider_id' => $rider->id, 'event' => $event, 'actor_type' => $scope['actor_type'],
            'actor_id' => $scope['actor_id'], 'details' => json_encode($details, JSON_THROW_ON_ERROR), 'created_at' => now(),
        ]);
    }

    public function registerPreferences(Rider $rider, int $branchId, array $keys): void
    {
        $scope = ['company_id' => (int) $rider->logistics_partner_id, 'actor_type' => 'rider', 'actor_id' => $rider->id];
        $this->transaction($scope, $rider->id, function (Rider $locked, RiderLifecycleProfile $profile) use ($branchId, $keys, $scope) {
            abort_unless($locked->partner->accepting_riders, 422);
            $branch = $this->directory->branch((int) $locked->logistics_partner_id, $branchId);
            $coverage = $this->directory->selected($branch, $keys);
            $profile->update(['preferred_branch_id' => $branch->id, 'preferred_coverage' => $coverage, 'legacy_onboarding' => false]);
            $this->event($locked, $scope, 'application_submitted', ['preferred_branch_id' => $branch->id, 'coverage' => $coverage]);
        });
    }

    public function review(array $scope, int $id, string $decision, array $data): Rider
    {
        $this->access->main($scope);

        $reviewed = $this->transaction($scope, $id, function (Rider $rider, RiderLifecycleProfile $profile) use ($scope, $decision, $data) {
            if (! in_array($decision, ['under_review', 'needs_documents', 'approve', 'reject'], true)) {
                $this->fail('Invalid review action.');
            }
            if (! in_array($profile->application_status, ['pending_review', 'under_review', 'needs_documents'], true)) {
                $this->fail('This application is no longer awaiting a review.');
            }
            if ($decision === 'approve') {
                if ($profile->document_request) {
                    $this->fail('The requested documents must be resubmitted before approval.');
                }
                if (! $rider->email_verified_at || ! $rider->or_cr_path || ! $rider->id_or_license_path) {
                    $this->fail('Verified email, vehicle registration and an ID/license are required before approval.');
                }
                $branch = $this->directory->branch((int) $rider->logistics_partner_id, (int) ($data['branch_id'] ?? 0));
                if (! $this->directory->coverage($branch)) {
                    $this->fail('The confirmed Branch has no authorized pickup and delivery barangays.', 'branch_id');
                }
                $changed = (int) $profile->preferred_branch_id !== (int) $branch->id;
                $profile->update(['application_status' => 'approved', 'operational_status' => 'pending_activation',
                    'assignment_confirmation_required' => $changed, 'document_request' => null]);
                $rider->forceFill(['sorting_center_id' => $branch->id, 'status' => 'pending',
                    'availability_status' => 'offline', 'approved_at' => now(), 'rejected_at' => null, 'rejection_reason' => null])->save();
            } elseif ($decision === 'needs_documents') {
                $message = trim($data['reason'] ?? '');
                $slots = array_values(array_unique($data['documents'] ?? []));
                if (! $message || ! $slots || array_diff($slots, ['or_cr', 'id_or_license'])) {
                    $this->fail('Specify the required document(s) and a message for the applicant.', 'documents');
                }
                $profile->update(['application_status' => 'needs_documents',
                    'document_request' => ['message' => $message, 'documents' => $slots, 'requested_at' => now()->toIso8601String()]]);
            } elseif ($decision === 'reject') {
                $reason = trim($data['reason'] ?? '');
                if (! $reason) {
                    $this->fail('A rejection reason is required.', 'reason');
                }
                $profile->update(['application_status' => 'rejected', 'operational_status' => 'inactive', 'document_request' => null]);
                $rider->forceFill(['status' => 'rejected', 'availability_status' => 'offline',
                    'rejected_at' => now(), 'rejection_reason' => $reason])->save();
            } else {
                if ($profile->document_request) {
                    $this->fail('Await the requested documents before marking this application under review.');
                }
                $profile->update(['application_status' => 'under_review']);
            }
            $this->event($rider, $scope, 'application_'.$decision, [
                'branch_id' => $rider->sorting_center_id, 'reason' => $data['reason'] ?? null,
                'documents' => $data['documents'] ?? [], 'confirmation_required' => $profile->assignment_confirmation_required,
            ]);

            return $rider->fresh();
        });
        if (in_array($decision, ['approve', 'reject'], true) && $reviewed->email_verified_at) {
            DB::afterCommit(function () use ($reviewed, $decision) {
                try {
                    $reviewed->notify(new RiderApplicationDecisionNotification(
                        $decision === 'approve', $reviewed->partner->company_name,
                        $reviewed->rejection_reason, true));
                } catch (\Throwable $exception) {
                    report($exception); // A mail outage does not roll back a persisted review decision.
                }
            });
        }

        return $reviewed;
    }

    public function confirmBranch(Rider $rider, int $branchId, array $keys): void
    {
        $scope = ['company_id' => (int) $rider->logistics_partner_id, 'actor_type' => 'rider', 'actor_id' => $rider->id];
        $this->transaction($scope, $rider->id, function (Rider $r, RiderLifecycleProfile $p) use ($branchId, $keys, $scope) {
            if ($p->application_status !== 'approved' || ! $p->assignment_confirmation_required
                || (int) $r->sorting_center_id !== $branchId) {
                $this->fail('The assigned Branch changed. Refresh your application.');
            }
            $branch = $this->directory->branch((int) $r->logistics_partner_id, $branchId);
            $coverage = $this->directory->selected($branch, $keys);
            $p->update(['preferred_branch_id' => $branchId, 'preferred_coverage' => $coverage,
                'assignment_confirmation_required' => false]);
            $this->event($r, $scope, 'assigned_branch_confirmed', ['branch_id' => $branchId, 'coverage' => $coverage]);
        });
    }

    public function resubmitDocuments(Rider $rider, array $paths): void
    {
        $scope = ['company_id' => (int) $rider->logistics_partner_id, 'actor_type' => 'rider', 'actor_id' => $rider->id];
        $this->transaction($scope, $rider->id, function (Rider $r, RiderLifecycleProfile $p) use ($paths, $scope) {
            if ($p->application_status !== 'needs_documents') {
                $this->fail('No additional documents are currently requested.');
            }
            $required = $p->document_request['documents'] ?? [];
            foreach ($required as $slot) {
                if (empty($paths[$slot])) {
                    $this->fail('Upload every requested document.', $slot);
                }
            }
            foreach ($paths as $slot => $path) {
                if (! in_array($slot, ['or_cr', 'id_or_license'], true)) {
                    $this->fail('Unsupported document.');
                }
                $r->setAttribute($slot.'_path', $path);
            }
            $r->save();
            $p->update(['application_status' => 'pending_review', 'document_request' => null]);
            $this->event($r, $scope, 'documents_resubmitted', ['documents' => array_keys($paths)]);
        });
    }

    /** Active UNIQUE people across all company Branches; pickup/delivery pivots never double count. */
    public function capacity(int $companyId, array $place, ?int $excludingRider = null): array
    {
        return $this->capacities($companyId, [$place], $excludingRider)[RiderWorkAreaDirectory::key($place)];
    }

    public function capacities(int $companyId, array $places, ?int $excludingRider = null): array
    {
        $keys = array_map(fn ($p) => RiderWorkAreaDirectory::key($p), $places);
        $limits = DB::table('rider_barangay_capacities')->where('logistics_partner_id', $companyId)
            ->whereIn('location_key', $keys)->pluck('rider_limit', 'location_key');
        $riders = Rider::with(['lifecycleProfile', 'operationalServiceAreas'])
            ->where('logistics_partner_id', $companyId)->where('status', 'active')
            ->when($excludingRider, fn ($q) => $q->whereKeyNot($excludingRider))->get()
            ->filter(fn (Rider $r) => $r->canOperate());
        $out = [];
        foreach ($places as $place) {
            $key = RiderWorkAreaDirectory::key($place);
            $ids = $riders->filter(fn (Rider $r) => $r->operationalServiceAreas->contains(fn ($a) => (int) $a->logistics_partner_id === $companyId && (int) $a->sorting_center_id === (int) $r->sorting_center_id
                && ($this->directory->covers($a, $place, 'pickup') || $this->directory->covers($a, $place, 'delivery'))))
                ->pluck('id')->unique()->values()->all();
            $out[$key] = ['key' => $key, 'limit' => (int) ($limits[$key] ?? 6), 'active_count' => count($ids), 'rider_ids' => $ids];
        }

        return $out;
    }

    private function assignCoverage(Rider $rider, array $places): void
    {
        $ids = [];
        $existing = OperationalServiceArea::where('logistics_partner_id', $rider->logistics_partner_id)
            ->where('sorting_center_id', $rider->sorting_center_id)->get();
        foreach ($places as $place) {
            // Reuse exact existing permissions even when stored city/Province labels use aliases.
            // A citywide permission must not silently grant every barangay to a new applicant.
            $exact = $existing->filter(fn ($a) => $a->barangay !== null);
            $pickup = $exact->first(fn ($a) => $this->directory->covers($a, $place, 'pickup'));
            $delivery = $exact->first(fn ($a) => $this->directory->covers($a, $place, 'delivery'));
            if ($pickup && $delivery) {
                $ids[] = $pickup->id;
                $ids[] = $delivery->id;

                continue;
            }
            $key = RiderServiceAreaService::locationKey($place['province'], $place['municipality'], $place['barangay'], 'both');
            $area = OperationalServiceArea::firstOrCreate([
                'logistics_partner_id' => $rider->logistics_partner_id,
                'sorting_center_id' => $rider->sorting_center_id, 'location_key' => $key,
            ], ['province' => $place['province'], 'municipality' => $place['municipality'],
                'barangay' => $place['barangay'], 'service_type' => 'both']);
            $ids[] = $area->id;
            $existing->push($area);
        }
        $rider->operationalServiceAreas()->sync(array_values(array_unique($ids)));
    }

    public function activate(array $scope, int $id, array $keys): Rider
    {
        return $this->transaction($scope, $id, function (Rider $r, RiderLifecycleProfile $p) use ($scope, $keys) {
            $branch = $this->access->branch($scope, $r);
            if ($p->application_status !== 'approved' || ! in_array($p->operational_status, ['pending_activation', 'inactive', 'active'], true)
                || $p->assignment_confirmation_required) {
                $this->fail('Main Hub approval and rider acceptance of the assigned Branch are required.');
            }
            if (! $r->email_verified_at || (! $p->legacy_onboarding && (! $r->or_cr_path || ! $r->id_or_license_path))) {
                $this->fail('Verified email and required registration documents are missing.');
            }
            $this->assertClear($r);
            $places = $this->directory->selected($branch, $keys);
            $capacities = $this->capacities($scope['company_id'], $places, $r->id);
            foreach ($places as $place) {
                $capacity = $capacities[RiderWorkAreaDirectory::key($place)];
                $override = DB::table('rider_capacity_overrides')->where('logistics_partner_id', $scope['company_id'])
                    ->where('rider_id', $r->id)->where('location_key', $capacity['key'])->where('active', true)->exists();
                if ($capacity['active_count'] >= $capacity['limit'] && ! $override) {
                    $this->fail($place['label'].' has reached its active rider capacity. Main Hub must approve an individual override with a reason.', 'coverage');
                }
            }
            $this->assignCoverage($r, $places);
            $p->update(['operational_status' => 'active', 'availability_state' => 'online', 'accepted_coverage' => $places,
                'activated_at' => now(), 'deactivated_at' => null]);
            $r->forceFill(['status' => 'active', 'availability_status' => 'available'])->save();
            $this->event($r, $scope, 'branch_activated', ['coverage' => $places]);

            return $r->fresh();
        });
    }

    public function setCapacity(array $scope, int $riderId, array $data): void
    {
        $this->access->main($scope);
        $this->transaction($scope, $riderId, function (Rider $r) use ($scope, $data) {
            $branch = $this->directory->branch($scope['company_id'], (int) ($r->sorting_center_id ?: $r->managementProfile()?->preferred_branch_id));
            $places = $this->directory->selected($branch, [$data['location_key'] ?? '']);
            $place = $places[0];
            $key = RiderWorkAreaDirectory::key($place);
            $reason = trim($data['reason'] ?? '');
            if (! $reason) {
                $this->fail('A recorded reason is required.', 'reason');
            }
            if (($data['action'] ?? '') === 'limit') {
                $limit = filter_var($data['rider_limit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
                if ($limit === false) {
                    $this->fail('The rider limit must be zero or a positive integer.', 'rider_limit');
                }
                DB::table('rider_barangay_capacities')->updateOrInsert(
                    ['logistics_partner_id' => $scope['company_id'], 'location_key' => $key],
                    ['location' => json_encode($place), 'rider_limit' => $limit,
                        'updated_by_type' => $scope['actor_type'], 'updated_by_id' => $scope['actor_id'],
                        'created_at' => now(), 'updated_at' => now()]);
            } elseif (in_array($data['action'] ?? '', ['grant', 'revoke'], true)) {
                DB::table('rider_capacity_overrides')->updateOrInsert(['rider_id' => $r->id, 'location_key' => $key],
                    ['logistics_partner_id' => $scope['company_id'], 'reason' => $reason,
                        'active' => $data['action'] === 'grant', 'approved_by_type' => $scope['actor_type'],
                        'approved_by_id' => $scope['actor_id'], 'created_at' => now(), 'updated_at' => now()]);
            } else {
                $this->fail('Invalid capacity action.');
            }
            $this->event($r, $scope, 'capacity_'.$data['action'], ['location' => $place,
                'reason' => $reason, 'rider_limit' => $data['rider_limit'] ?? null]);
        });
    }

    public function updateAccountDetails(array $scope, int $id, array $data): void
    {
        $this->access->main($scope);
        $this->transaction($scope, $id, function (Rider $r) use ($scope, $data) {
            if ($data['vehicle_type'] !== $r->vehicle_type || ($data['plate_number'] ?? null) !== $r->plate_number) {
                $this->assertClear($r);
            }
            $r->update($data);
            $this->event($r, $scope, 'account_details_updated', ['fields' => array_keys($data)]);
        });
    }

    public function setOperationalStatus(array $scope, int $id, string $status, string $reason): void
    {
        $this->transaction($scope, $id, function (Rider $r, RiderLifecycleProfile $p) use ($scope, $status, $reason) {
            if (! trim($reason)) {
                $this->fail('A recorded reason is required.', 'reason');
            }
            if ($scope['role'] === 'main') {
                if (! in_array($status, ['suspended', 'pending_activation'], true)) {
                    $this->fail('Main Hub can suspend or release for Branch reactivation.');
                }
                if ($status === 'pending_activation' && $p->operational_status !== 'suspended') {
                    $this->fail('This rider is not suspended.');
                }
            } else {
                $this->access->branch($scope, $r);
                if ($status !== 'inactive' || $p->operational_status === 'suspended') {
                    $this->fail('Branch can deactivate active riders; Main Hub controls compliance suspensions.');
                }
            }
            if ($p->application_status !== 'approved') {
                $this->fail('Only approved riders can change operational status.');
            }
            // Main Hub may immediately suspend operations while custody/cash records remain intact.
            if (! ($scope['role'] === 'main' && $status === 'suspended')) {
                $this->assertClear($r);
            }
            $p->update(['operational_status' => $status, 'availability_state' => 'offline', 'deactivated_at' => now()]);
            $r->forceFill(['status' => $status === 'pending_activation' ? 'pending' : 'suspended', 'availability_status' => 'offline'])->save();
            $this->event($r, $scope, 'operational_'.$status, ['reason' => trim($reason)]);
        });
    }

    public function availability(array $scope, int $id, string $state): void
    {
        $this->transaction($scope, $id, function (Rider $r, RiderLifecycleProfile $p) use ($scope, $state) {
            if (($scope['actor_type'] ?? '') !== 'rider') {
                $this->access->branch($scope, $r);
            } else {
                abort_unless((int) $scope['actor_id'] === (int) $r->id, 403);
            }
            if (! $r->canOperate()) {
                $this->fail('Only activated riders can change availability.');
            }
            if (! in_array($state, ['online', 'offline', 'on_break'], true)) {
                $this->fail('Invalid availability.');
            }
            // A rider must finish active custody before going offline or taking a break.
            if ($state !== 'online') {
                $this->assertClear($r, false);
            } elseif (RiderBatch::where('rider_id', $r->id)->where('status', 'in_progress')->exists()) {
                $this->fail('Availability is busy while a batch is in progress.');
            }
            $p->update(['availability_state' => $state]);
            $r->update(['availability_status' => $state === 'online' ? 'available' : 'offline']);
            $this->event($r, $scope, 'availability_changed', ['state' => $state]);
        });
    }

    public function schedule(array $scope, int $id, array $data): void
    {
        $this->transaction($scope, $id, function (Rider $r) use ($scope, $data) {
            $branch = $this->access->branch($scope, $r);
            if (! $r->canOperate()) {
                $this->fail('Activate the rider before managing work schedules.');
            }
            if (! empty($data['cancel_id'])) {
                $schedule = DB::table('rider_work_schedules')->where('rider_id', $r->id)->where('sorting_center_id', $branch->id)
                    ->where('logistics_partner_id', $scope['company_id'])->where('status', 'active')->find($data['cancel_id']);
                abort_unless($schedule, 404);
                if (RiderBatch::where('rider_id', $r->id)->whereIn('status', ['queued', 'in_progress'])
                    ->where('scheduled_start', '>=', $schedule->starts_at)->where('scheduled_end', '<=', $schedule->ends_at)->exists()) {
                    $this->fail('Resolve batches scheduled in this work window before cancelling it.');
                }
                DB::table('rider_work_schedules')->where('id', $schedule->id)->update(['status' => 'cancelled', 'updated_at' => now()]);
                $this->event($r, $scope, 'schedule_cancelled', ['schedule_id' => $schedule->id]);

                return;
            }
            try {
                $start = Carbon::parse($data['starts_at'] ?? '')->utc();
                $end = Carbon::parse($data['ends_at'] ?? '')->utc();
            } catch (\Throwable) {
                $this->fail('Provide a valid work window.', 'starts_at');
            }
            if ($start->isPast() || $end->lte($start)) {
                $this->fail('Work schedules must start in the future and end after they start.', 'starts_at');
            }
            if (DB::table('rider_work_schedules')->where('rider_id', $r->id)->where('status', 'active')
                ->where('starts_at', '<', $end)->where('ends_at', '>', $start)->exists()) {
                $this->fail('This work window overlaps another schedule.');
            }
            // Introducing a schedule must not strand existing queued batches outside the rider's work windows.
            $windows = DB::table('rider_work_schedules')->where('rider_id', $r->id)->where('status', 'active')->get();
            foreach (RiderBatch::where('rider_id', $r->id)->whereIn('status', ['queued', 'in_progress'])->get() as $batch) {
                if (! ($batch->scheduled_start->gte($start) && $batch->scheduled_end->lte($end))
                    && ! $windows->contains(fn ($w) => $batch->scheduled_start->gte(Carbon::parse($w->starts_at)) && $batch->scheduled_end->lte(Carbon::parse($w->ends_at)))) {
                    $this->fail('An existing batch is outside the proposed work windows.');
                }
            }
            $sid = DB::table('rider_work_schedules')->insertGetId(['logistics_partner_id' => $scope['company_id'],
                'sorting_center_id' => $branch->id, 'rider_id' => $r->id, 'starts_at' => $start, 'ends_at' => $end,
                'status' => 'active', 'created_by_type' => $scope['actor_type'], 'created_by_id' => $scope['actor_id'],
                'created_at' => now(), 'updated_at' => now()]);
            $this->event($r, $scope, 'schedule_created', ['schedule_id' => $sid, 'starts_at' => $start->toIso8601String(), 'ends_at' => $end->toIso8601String()]);
        });
    }

    public function assertSchedule(Rider $rider, CarbonInterface $start, CarbonInterface $end): void
    {
        if (! $this->available()) {
            return;
        }
        // Batch planning may have waited on this rider after an earlier consistent read.
        // FOR UPDATE reads the committed schedule after the rider lock is acquired.
        $windows = DB::table('rider_work_schedules')->where('rider_id', $rider->id)
            ->where('status', 'active')->lockForUpdate()->get();
        if ($windows->isEmpty()) {
            return;
        }
        if (! $windows->contains(fn ($w) => (int) $w->logistics_partner_id === (int) $rider->logistics_partner_id
            && (int) $w->sorting_center_id === (int) $rider->sorting_center_id
            && $start->gte(Carbon::parse($w->starts_at, 'UTC'))
            && $end->lte(Carbon::parse($w->ends_at, 'UTC')))) {
            $this->fail('The entire batch must fit inside an official Branch work schedule.', 'scheduled_start');
        }
    }

    public function requestTransfer(array $scope, int $id, int $target, string $reason): RiderTransferRequest
    {
        return $this->transaction($scope, $id, function (Rider $r, RiderLifecycleProfile $p) use ($scope, $target, $reason) {
            $from = $this->access->branch($scope, $r);
            $to = $this->directory->branch($scope['company_id'], $target);
            if ($from->id === $to->id || ! trim($reason)) {
                $this->fail('Choose another company Branch and provide a reason.');
            }
            if ($p->application_status !== 'approved') {
                $this->fail('Only approved riders can be transferred.');
            }
            if (RiderTransferRequest::where('pending_rider_id', $r->id)->exists()) {
                $this->fail('A transfer request is already pending.');
            }
            $transfer = RiderTransferRequest::create(['logistics_partner_id' => $scope['company_id'], 'rider_id' => $r->id,
                'pending_rider_id' => $r->id, 'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
                'status' => 'pending', 'reason' => trim($reason), 'requested_by_type' => $scope['actor_type'], 'requested_by_id' => $scope['actor_id']]);
            $this->event($r, $scope, 'transfer_requested', ['transfer_id' => $transfer->id, 'to_branch_id' => $to->id, 'reason' => trim($reason)]);

            return $transfer;
        });
    }

    public function decideTransfer(array $scope, int $transferId, bool $approve, string $reason): void
    {
        $this->access->main($scope);
        $request = RiderTransferRequest::where('logistics_partner_id', $scope['company_id'])->findOrFail($transferId);
        $this->transaction($scope, $request->rider_id, function (Rider $r, RiderLifecycleProfile $p) use ($scope, $request, $approve, $reason) {
            $transfer = RiderTransferRequest::lockForUpdate()->findOrFail($request->id);
            if ($transfer->status !== 'pending' || (int) $r->sorting_center_id !== (int) $transfer->from_branch_id) {
                $this->fail('This transfer is no longer valid.');
            }
            if (! trim($reason)) {
                $this->fail('A decision reason is required.', 'reason');
            }
            if ($approve) {
                $this->assertClear($r);
                $this->directory->branch($scope['company_id'], $transfer->to_branch_id);
                if ($p->application_status !== 'approved' || $p->operational_status === 'suspended') {
                    $this->fail('Resolve compliance suspension before transferring this rider.');
                }
                $r->operationalServiceAreas()->detach();
                DB::table('rider_work_schedules')->where('rider_id', $r->id)->where('status', 'active')->update(['status' => 'cancelled', 'updated_at' => now()]);
                // Old overrides cannot silently authorize a different Branch's coverage.
                DB::table('rider_capacity_overrides')->where('rider_id', $r->id)->update(['active' => false, 'updated_at' => now()]);
                $r->forceFill(['sorting_center_id' => $transfer->to_branch_id, 'coverage_area_id' => null,
                    'status' => 'pending', 'availability_status' => 'offline'])->save();
                $p->update(['operational_status' => 'pending_activation', 'availability_state' => 'offline',
                    'accepted_coverage' => null, 'assignment_confirmation_required' => true, 'deactivated_at' => now()]);
            }
            $transfer->update(['status' => $approve ? 'approved' : 'rejected', 'pending_rider_id' => null,
                'decision_reason' => trim($reason), 'decided_by_type' => $scope['actor_type'],
                'decided_by_id' => $scope['actor_id'], 'decided_at' => now()]);
            $this->event($r, $scope, $approve ? 'transfer_approved' : 'transfer_rejected', [
                'transfer_id' => $transfer->id, 'from_branch_id' => $transfer->from_branch_id,
                'to_branch_id' => $transfer->to_branch_id, 'reason' => trim($reason)]);
        });
    }

    /** Historical rider columns do not imply custody once a parcel has a verified Branch receipt. */
    public function assertClear(Rider $rider, bool $includeQueued = true): void
    {
        if (RiderBatch::where('rider_id', $rider->id)->whereIn('status', $includeQueued ? ['queued', 'in_progress'] : ['in_progress'])->lockForUpdate()->first(['id'])) {
            $this->fail('Complete or cancel outstanding rider batches first.');
        }
        $deliveries = Delivery::where('logistics_partner_id', $rider->logistics_partner_id)->where(function ($q) use ($rider) {
            $q->where('rider_id', $rider->id)->orWhere('delivery_rider_id', $rider->id)->orWhere('pickup_rider_id', $rider->id);
        })->whereIn('status', [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED, Delivery::PICKED_UP,
            Delivery::DELIVERY_ASSIGNED, Delivery::OUT_FOR_DELIVERY, Delivery::DELIVERY_FAILED])->lockForUpdate()->get();
        $queued = ! $includeQueued ? RiderBatchItem::whereHas('batch', fn ($q) => $q->where('rider_id', $rider->id)->where('status', 'queued'))
            ->whereNotNull('reserved_delivery_id')->lockForUpdate()->pluck('delivery_id')->all() : [];
        foreach ($deliveries as $d) {
            if (! $includeQueued && in_array($d->status, [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED, Delivery::DELIVERY_ASSIGNED], true)
                && in_array($d->id, $queued)) {
                continue;
            }
            if ($d->status === Delivery::DELIVERY_FAILED && app(RiderBatchService::class)->receivedFailure($d)) {
                continue;
            }
            $this->fail('Resolve assigned parcels and verify their physical Branch receipt first.');
        }
        if (ReturnCase::where('return_rider_id', $rider->id)->whereIn('status', ['assigned', 'in_transit', 'awaiting_seller'])->lockForUpdate()->first(['id'])
            || ParcelTransfer::where('transit_rider_id', $rider->id)->where('status', ParcelTransfer::IN_TRANSIT)->lockForUpdate()->first(['id'])) {
            $this->fail('Resolve outstanding parcel custody and transfers first.');
        }
        if (CodSettlement::where('collected_by_rider_id', $rider->id)->where('status', CodSettlement::COLLECTED)->lockForUpdate()->first(['id'])) {
            $this->fail('Hand over and record outstanding COD cash before changing this rider assignment.');
        }
    }

    public function applicationData(Rider $rider): array
    {
        $rider = $rider->fresh(['partner', 'sortingCenter', 'lifecycleProfile', 'operationalServiceAreas']);
        $p = $rider->managementProfile();
        $branch = $rider->sortingCenter;

        return [
            'rider_id' => $rider->id, 'name' => $rider->name, 'email' => $rider->email, 'account_type' => 'rider',
            'company' => ['id' => $rider->logistics_partner_id, 'name' => $rider->partner?->company_name],
            'application_status' => $rider->application_status, 'operational_status' => $rider->operational_status,
            'availability' => $rider->availabilityState(), 'can_operate' => $rider->canOperate(),
            'branch' => $branch ? ['id' => $branch->id, 'name' => $branch->name, 'address' => $branch->addressLabel()] : null,
            'preferred_branch_id' => $p?->preferred_branch_id, 'preferred_coverage' => $p?->preferred_coverage ?? [],
            'official_coverage' => $rider->operationalServiceAreas->map(fn ($a) => ['id' => $a->id, 'label' => $a->label()])->values(),
            'assignment_confirmation_required' => $p?->assignment_confirmation_required ?? false,
            'document_request' => $p?->document_request, 'rejection_reason' => $rider->rejection_reason,
            'schedules' => DB::table('rider_work_schedules')->where('rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)->where('sorting_center_id', $rider->sorting_center_id)
                ->where('status', 'active')->where('ends_at', '>', now())->orderBy('starts_at')->get(['id', 'starts_at', 'ends_at']),
            'outstanding_cod' => CodSettlement::where('collected_by_rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)->where('status', CodSettlement::COLLECTED)
                ->get(['delivery_id', 'order_id', 'collected_amount'])->map(fn ($cash) => [
                    'delivery_id' => $cash->delivery_id, 'order_id' => $cash->order_id, 'amount' => (string) $cash->collected_amount,
                    'can_remit' => in_array($rider->status, ['active', 'suspended'], true),
                ]),
            'history' => DB::table('rider_management_events')->where('rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)->latest('id')->limit(30)
                ->get(['id', 'event', 'details', 'created_at'])->map(function ($row) {
                    $row->details = json_decode($row->details, true);

                    return $row;
                }),
        ];
    }
}
