<?php

namespace App\Services;

use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CodDiscrepancyService
{
    public static function ready(): void
    {
        foreach (['cod_discrepancy_cases', 'cod_discrepancy_resolutions', 'cod_cash_adjustments'] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'Apply the COD discrepancy database update before recording financial actions.');
        }
    }

    public static function assertCollectionClear(int $collection): void
    {
        self::ready();
        if (DB::table('cod_discrepancy_cases')->where('cod_settlement_id', $collection)->where('status', '!=', 'resolved')->exists()) {
            throw ValidationException::withMessages(['settlement' => 'An unresolved COD discrepancy blocks remittance and settlement. Complete its authorized review first.']);
        }
    }

    public static function assertOrderClear(int $order): void
    {
        self::ready();
        $collection = CodSettlement::where('order_id', $order)->value('id');
        if ($collection) {
            self::assertCollectionClear((int) $collection);
        }
    }

    // Called with the collection locked by the cash verification transaction.
    public function record(CodSettlement $cash, int $center, User $receiver): void
    {
        self::ready();
        $existing = DB::table('cod_discrepancy_cases')->where('cod_settlement_id', $cash->id)->lockForUpdate()->first();
        if ($existing) {
            return;
        }
        $difference = FinanceMoney::minor($cash->received_amount) - FinanceMoney::minor($cash->expected_amount);
        $id = DB::table('cod_discrepancy_cases')->insertGetId([
            'reference' => (string) Str::uuid(), 'cod_settlement_id' => $cash->id,
            'logistics_partner_id' => $cash->logistics_partner_id, 'sorting_center_id' => $center,
            'opening_difference_minor' => $difference, 'status' => 'open', 'origin' => 'verification',
            'opened_by_user_id' => $receiver->id, 'opened_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        FinancialAudit::record('cod_discrepancy', $id, 'opened', 'user', $receiver->id,
            ['collection_id' => $cash->id, 'difference_minor' => $difference]);
    }

    public function authorize(User $actor, object $case): void
    {
        $actor = $actor->fresh();
        abort_unless($actor && $actor->status === 'approved', 403);
        if ($actor->account_type === 'admin') {
            return;
        }
        if ($actor->account_type === 'logistics') {
            abort_unless(DB::table('logistics_partners')->where('id', $case->logistics_partner_id)->where('user_id', $actor->id)->exists(), 404);
            return;
        }
        abort_unless($actor->account_type === 'branch', 403);
        abort_unless(SortingCenter::whereKey($case->sorting_center_id)->where('user_id', $actor->id)
            ->where('logistics_partner_id', $case->logistics_partner_id)->exists(), 404);
    }

    private function locked(User $actor, int $id): array
    {
        self::ready();
        $candidate = DB::table('cod_discrepancy_cases')->where('id', $id)->firstOrFail();
        $this->authorize($actor, $candidate);
        // Same cash -> case lock order as verification and settlement.
        $cash = CodSettlement::whereKey($candidate->cod_settlement_id)->lockForUpdate()->firstOrFail();
        $case = DB::table('cod_discrepancy_cases')->where('id', $id)->lockForUpdate()->firstOrFail();

        return [$cash, $case];
    }

    private function mutable(CodSettlement $cash, object $case): void
    {
        if ($case->status === 'resolved' || $cash->admin_remitted_at || $cash->admin_received_at) {
            $this->invalid('This cash record is finalized or already sent to ShopHop.');
        }
        if (DB::table('cod_remittance_items as i')->join('cod_remittance_batches as b', 'b.id', '=', 'i.batch_id')
            ->where('i.cod_settlement_id', $cash->id)->where('b.status', 'submitted')->exists()) {
            $this->invalid('Verify the pending supplemental handover first.');
        }
    }

    public function note(User $actor, int $id, array $data): void
    {
        $data = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $data);
        $data = Validator::make($data, [
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'evidence_reference' => ['required', 'string', 'min:4', 'max:160'],
        ])->validate();
        DB::transaction(function () use ($actor, $id, $data) {
            $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            [, $case] = $this->locked($actor, $id);
            if ($case->status === 'resolved') {
                $this->invalid('Resolved evidence is finalized.');
            }
            FinancialAudit::record('cod_discrepancy', $id, 'evidence_added', 'user', $actor->id, $data);
        }, 3);
    }

    public function propose(User $actor, int $id, array $data): int
    {
        $data = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $data);
        $data = Validator::make($data, [
            'kind' => ['required', 'in:balance_confirmed,overage_return'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'evidence_reference' => ['required', 'string', 'min:4', 'max:160'],
            'recipient' => ['required_if:kind,overage_return', 'nullable', 'string', 'min:4', 'max:160'],
        ])->validate();

        return DB::transaction(function () use ($actor, $id, $data) {
            $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            [$cash, $case] = $this->locked($actor, $id);
            abort_unless($actor->account_type === 'logistics', 403);
            $this->mutable($cash, $case);
            if ($case->status !== 'open') {
                $this->invalid('A resolution is already awaiting review or completion.');
            }
            if (FinanceMoney::minor($cash->collected_amount) !== FinanceMoney::minor($cash->expected_amount)) {
                $this->invalid('The Buyer collection differs from the Order total. This requires collection investigation; it cannot be waived as Rider earnings.');
            }
            $received = FinanceMoney::minor($cash->received_amount);
            $difference = $received - FinanceMoney::minor($cash->expected_amount);
            if (($data['kind'] === 'balance_confirmed' && ($difference !== 0 || $cash->status !== CodSettlement::RECONCILED))
                || ($data['kind'] === 'overage_return' && $difference <= 0)) {
                $this->invalid('Shortages require verified supplemental cash. An excess return requires a positive verified overage.');
            }
            $resolution = DB::table('cod_discrepancy_resolutions')->insertGetId([
                'case_id' => $id, 'kind' => $data['kind'], 'amount_minor' => max(0, $difference),
                'received_snapshot_minor' => $received, 'reason' => $data['reason'],
                'evidence_reference' => $data['evidence_reference'], 'recipient' => $data['recipient'] ?? null,
                'status' => 'pending', 'proposed_by_user_id' => $actor->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('cod_discrepancy_cases')->where('id', $id)->update(['status' => 'pending_approval', 'updated_at' => now()]);
            FinancialAudit::record('cod_discrepancy', $id, 'resolution_proposed', 'user', $actor->id,
                ['resolution_id' => $resolution, ...$data, 'amount_minor' => max(0, $difference)]);

            return $resolution;
        }, 3);
    }

    public function review(User $actor, int $id, int $resolution, string $decision, string $notes): void
    {
        $decision = trim($decision);
        $notes = trim($notes);
        Validator::make(['decision' => $decision, 'notes' => $notes], [
            'decision' => ['required', 'in:approve,reject'], 'notes' => ['required', 'string', 'min:10', 'max:2000'],
        ])->validate();
        DB::transaction(function () use ($actor, $id, $resolution, $decision, $notes) {
            $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            [$cash, $case] = $this->locked($actor, $id);
            abort_unless($actor->account_type === 'admin', 403);
            $row = DB::table('cod_discrepancy_resolutions')->where('id', $resolution)->where('case_id', $id)->lockForUpdate()->firstOrFail();
            abort_if((int) $row->proposed_by_user_id === (int) $actor->id, 403, 'The proposer cannot approve their own resolution.');
            $this->mutable($cash, $case);
            if ($case->status !== 'pending_approval' || $row->status !== 'pending') {
                $this->invalid('This resolution has already been reviewed.');
            }
            if ($decision === 'approve' && FinanceMoney::minor($cash->received_amount) !== (int) $row->received_snapshot_minor) {
                $this->invalid('Cash changed since the proposal. Review the current cash balance first.');
            }
            if ($decision === 'approve') {
                $difference = FinanceMoney::minor($cash->received_amount) - FinanceMoney::minor($cash->expected_amount);
                if (FinanceMoney::minor($cash->collected_amount) !== FinanceMoney::minor($cash->expected_amount)
                    || ($row->kind === 'balance_confirmed' && ($difference !== 0 || $cash->status !== CodSettlement::RECONCILED))
                    || ($row->kind === 'overage_return' && ($difference <= 0 || $difference !== (int) $row->amount_minor))) {
                    $this->invalid('Current cash no longer matches the proposed resolution.');
                }
            }
            $closing = $decision === 'approve' && $row->kind === 'balance_confirmed';
            DB::table('cod_discrepancy_resolutions')->where('id', $resolution)->update([
                'status' => $decision === 'reject' ? 'rejected' : ($closing ? 'completed' : 'approved'),
                'reviewed_by_user_id' => $actor->id, 'review_notes' => $notes, 'reviewed_at' => now(),
                'completed_by_user_id' => $closing ? $actor->id : null, 'completed_at' => $closing ? now() : null, 'updated_at' => now(),
            ]);
            DB::table('cod_discrepancy_cases')->where('id', $id)->update([
                'status' => $decision === 'reject' ? 'open' : ($closing ? 'resolved' : 'approved'),
                'resolved_at' => $closing ? now() : null, 'updated_at' => now(),
            ]);
            FinancialAudit::record('cod_discrepancy', $id, 'resolution_'.$decision, 'user', $actor->id,
                ['resolution_id' => $resolution, 'notes' => $notes]);
        }, 3);
    }

    public function recordReturn(User $actor, int $id, int $resolution, array $data): void
    {
        $data = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $data);
        $data = Validator::make($data, [
            'returned_amount' => ['required', 'decimal:0,2', 'gt:0', 'max:999999999'],
            'reference' => ['required', 'string', 'min:4', 'max:120'],
            'recipient_acknowledgment' => ['required', 'string', 'min:4', 'max:160'],
            'cash_returned' => ['required', 'accepted'],
        ])->validate();
        DB::transaction(function () use ($actor, $id, $resolution, $data) {
            $actor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            [$cash, $case] = $this->locked($actor, $id);
            abort_unless($actor->account_type === 'logistics', 403);
            $row = DB::table('cod_discrepancy_resolutions')->where('id', $resolution)->where('case_id', $id)->lockForUpdate()->firstOrFail();
            $amount = FinanceMoney::minor($data['returned_amount']);
            if ($row->status === 'completed') {
                $existing = DB::table('cod_cash_adjustments')->where('resolution_id', $resolution)->first();
                if ($existing && $amount === -(int) $existing->amount_minor && $existing->reference === $data['reference']
                    && $existing->recipient_acknowledgment === $data['recipient_acknowledgment']) {
                    return;
                }
                $this->invalid('The completed return is immutable.');
            }
            $this->mutable($cash, $case);
            if ($case->status !== 'approved' || $row->status !== 'approved' || $row->kind !== 'overage_return') {
                $this->invalid('ShopHop Admin must approve the overage return before it can be recorded.');
            }
            $received = FinanceMoney::minor($cash->received_amount);
            if ($received !== (int) $row->received_snapshot_minor || $amount !== (int) $row->amount_minor
                || $received - $amount !== FinanceMoney::minor($cash->expected_amount)
                || FinanceMoney::minor($cash->collected_amount) !== FinanceMoney::minor($cash->expected_amount)) {
                $this->invalid('Return amount or cash balance differs from the approved excess.');
            }
            if (DB::table('cod_cash_adjustments')->where('logistics_partner_id', $case->logistics_partner_id)->where('reference', $data['reference'])->exists()) {
                $this->invalid('That return reference was already recorded.');
            }
            $adjustment = DB::table('cod_cash_adjustments')->insertGetId([
                'resolution_id' => $resolution, 'cod_settlement_id' => $cash->id, 'logistics_partner_id' => $case->logistics_partner_id,
                'amount_minor' => -$amount, 'reference' => $data['reference'],
                'recipient_acknowledgment' => $data['recipient_acknowledgment'], 'recorded_by_user_id' => $actor->id, 'recorded_at' => now(),
            ]);
            // Running net custody changes; original verified items and custody movements stay immutable.
            $cash->update(['received_amount' => FinanceMoney::decimal($received - $amount),
                'status' => CodSettlement::RECONCILED, 'reconciled_at' => now(), 'reconciled_by_user_id' => $actor->id]);
            DB::table('cod_discrepancy_resolutions')->where('id', $resolution)->update([
                'status' => 'completed', 'completed_by_user_id' => $actor->id, 'completed_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('cod_discrepancy_cases')->where('id', $id)->update(['status' => 'resolved', 'resolved_at' => now(), 'updated_at' => now()]);
            FinancialAudit::record('cod_discrepancy', $id, 'overage_return_recorded', 'user', $actor->id,
                ['resolution_id' => $resolution, 'adjustment_id' => $adjustment, 'amount_minor' => -$amount, ...$data]);
        }, 3);
    }

    public function summaries(array $collections): Collection
    {
        self::ready();

        return DB::table('cod_discrepancy_cases')->whereIn('cod_settlement_id', $collections)->get()->keyBy('cod_settlement_id')
            ->map(fn ($case) => [
                'reference' => $case->reference, 'status' => $case->status,
                'opening_difference_minor' => $case->opening_difference_minor, 'resolved_at' => $case->resolved_at,
            ]);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['discrepancy' => $message]);
    }
}
