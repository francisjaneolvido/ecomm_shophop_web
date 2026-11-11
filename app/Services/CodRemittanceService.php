<?php

namespace App\Services;

use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CodRemittanceService
{
    public function submit(Rider $rider, int $centerId, string $key, string $reference, array $items): object
    {
        return DB::transaction(function () use ($rider, $centerId, $key, $reference, $items) {
            // Serialize all submissions by this collector, including different idempotency keys.
            $rider = Rider::whereKey($rider->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($rider->status, ['active', 'suspended'], true)
                && $rider->partner?->user?->status === 'approved', 403);
            $center = SortingCenter::whereKey($centerId)->where('logistics_partner_id', $rider->logistics_partner_id)
                ->where('status', 'active')->firstOrFail();
            $ids = array_column($items, 'collection_id');
            if (! $ids || count($ids) !== count(array_unique($ids))) {
                $this->invalid('Choose distinct COD collections.');
            }
            $existing = DB::table('cod_remittance_batches')->where('rider_id', $rider->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                $original = DB::table('cod_remittance_items')->where('batch_id', $existing->id)->pluck('submitted_minor', 'cod_settlement_id')->all();
                $retry = [];
                foreach ($items as $item) {
                    $retry[$item['collection_id']] = FinanceMoney::minor($item['amount']);
                }
                ksort($retry);
                ksort($original);
                if ($existing->sorting_center_id != $centerId || $existing->receipt_reference !== $reference || $original != $retry) {
                    $this->invalid('Idempotency key was already used for a different handover.');
                }

                return $existing;
            }
            $expected = 0;
            $submitted = 0;
            $allocations = [];
            sort($ids);
            $collections = CodSettlement::whereIn('id', $ids)->where('collected_by_rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($collections->count() === count($ids), 404);
            foreach ($items as $item) {
                $cash = $collections[$item['collection_id']];
                $pending = DB::table('cod_remittance_items as i')->join('cod_remittance_batches as b', 'b.id', '=', 'i.batch_id')
                    ->where('i.cod_settlement_id', $cash->id)->where('b.status', 'submitted')->exists();
                if ($pending || $cash->admin_received_at || $cash->status === CodSettlement::RECONCILED
                    || ($cash->remitted_at && ! DB::table('cod_remittance_items')->where('cod_settlement_id', $cash->id)->exists())) {
                    $this->invalid('A collection already has a pending or completed handover.');
                }
                // Cash liability is the entire collection. Earnings never enter this equation.
                $remaining = FinanceMoney::minor($cash->collected_amount) - FinanceMoney::minor($cash->received_amount);
                $amount = FinanceMoney::minor($item['amount']);
                if ($remaining <= 0 || $amount <= 0) {
                    $this->invalid('Collection has no outstanding cash, or the submitted amount is invalid.');
                }
                $expected += $remaining;
                $submitted += $amount;
                $allocations[] = ['cod_settlement_id' => $cash->id, 'expected_minor' => $remaining, 'submitted_minor' => $amount];
            }
            $id = DB::table('cod_remittance_batches')->insertGetId([
                'reference' => (string) Str::uuid(), 'logistics_partner_id' => $rider->logistics_partner_id,
                'rider_id' => $rider->id, 'sorting_center_id' => $center->id, 'idempotency_key' => $key,
                'expected_minor' => $expected, 'submitted_minor' => $submitted, 'status' => 'submitted',
                'receipt_reference' => $reference, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($allocations as $allocation) {
                DB::table('cod_remittance_items')->insert(['batch_id' => $id, ...$allocation]);
            }
            foreach ($collections as $cash) {
                $cash->update(['status' => CodSettlement::REMITTED,
                    'remitted_by_rider_id' => $rider->id, 'remitted_at' => now()]);
            }
            FinancialAudit::record('cod_batch', $id, 'submitted', 'rider', $rider->id, ['items' => $allocations, 'submitted_minor' => $submitted]);

            return DB::table('cod_remittance_batches')->find($id);
        }, 3);
    }

    public function verify(User $user, int $batchId, array $amounts): object
    {
        return DB::transaction(function () use ($user, $batchId, $amounts) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->status === 'approved' && in_array($user->account_type, ['logistics', 'branch'], true), 403);
            $batch = DB::table('cod_remittance_batches')->where('id', $batchId)->lockForUpdate()->firstOrFail();
            $center = SortingCenter::findOrFail($batch->sorting_center_id);
            $ownsPartner = $user->account_type === 'logistics' && DB::table('logistics_partners')
                ->where('id', $batch->logistics_partner_id)->where('user_id', $user->id)->exists();
            abort_unless($ownsPartner || ($user->account_type === 'branch' && (int) $center->user_id === (int) $user->id), 403);
            abort_unless($center->status === 'active', 403);
            $items = DB::table('cod_remittance_items')->where('batch_id', $batchId)->orderBy('cod_settlement_id')->get();
            if (count($amounts) !== $items->count() || count(array_unique(array_column($amounts, 'collection_id'))) !== $items->count()) {
                $this->invalid('Count cash and provide exactly one verified amount for every collection.');
            }
            $verified = [];
            foreach ($amounts as $amount) {
                $value = FinanceMoney::minor($amount['amount']);
                if ($value < 0) {
                    $this->invalid('Cash received cannot be negative.');
                }
                $verified[(int) $amount['collection_id']] = $value;
            }
            $total = 0;
            $hasDifference = false;
            foreach ($items as $item) {
                if (! array_key_exists($item->cod_settlement_id, $verified)) {
                    $this->invalid('Collection does not belong to this batch.');
                }
                $total += $verified[$item->cod_settlement_id];
                $hasDifference = $hasDifference || $verified[$item->cod_settlement_id] !== (int) $item->expected_minor;
            }
            if ($batch->verified_at) {
                foreach ($items as $item) {
                    if ((int) $item->verified_minor !== $verified[$item->cod_settlement_id]) {
                        $this->invalid('Verified receipts are immutable. Submit a supplemental handover for a shortage.');
                    }
                }

                return $batch;
            }
            $difference = $total - $batch->expected_minor;
            foreach ($items as $item) {
                $cash = CodSettlement::whereKey($item->cod_settlement_id)->lockForUpdate()->firstOrFail();
                $received = FinanceMoney::minor($cash->received_amount) + $verified[$cash->id];
                $balanced = $received === FinanceMoney::minor($cash->collected_amount)
                    && $cash->collected_amount === $cash->expected_amount;
                DB::table('cod_remittance_items')->where('id', $item->id)->update(['verified_minor' => $verified[$cash->id]]);
                $cash->update(['received_amount' => FinanceMoney::decimal($received),
                    'status' => $balanced ? CodSettlement::RECONCILED : 'discrepancy',
                    'reconciled_by_user_id' => $user->id, 'reconciled_at' => $balanced ? now() : null]);
            }
            DB::table('cod_remittance_batches')->where('id', $batchId)->update([
                'verified_minor' => $total, 'difference_minor' => $difference,
                'status' => $hasDifference ? 'discrepancy' : 'reconciled',
                'verified_by_user_id' => $user->id, 'verified_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('cash_custody_movements')->insert([
                'batch_id' => $batchId, 'from_rider_id' => $batch->rider_id,
                'to_sorting_center_id' => $batch->sorting_center_id, 'received_by_user_id' => $user->id,
                'amount_minor' => $total, 'reference' => $batch->receipt_reference, 'received_at' => now(),
            ]);
            FinancialAudit::record('cod_batch', $batchId, 'verified', 'user', $user->id,
                ['verified_minor' => $total, 'difference_minor' => $difference, 'allocations' => $verified]);

            return DB::table('cod_remittance_batches')->find($batchId);
        }, 3);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['remittance' => $message]);
    }
}
