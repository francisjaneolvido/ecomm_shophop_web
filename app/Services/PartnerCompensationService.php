<?php

namespace App\Services;

use App\Models\Logistics\Delivery;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\Rider;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PartnerCompensationService
{
    public const TYPES = ['per_delivery', 'per_pickup', 'per_trip', 'fixed_salary', 'hourly', 'base_incentive'];

    private function partner(User $actor): LogisticsPartner
    {
        abort_unless($actor->fresh()?->status === 'approved' && $actor->account_type === 'logistics', 403);

        return LogisticsPartner::where('user_id', $actor->id)->firstOrFail();
    }

    public function createPolicy(User $actor, array $data): object
    {
        return DB::transaction(function () use ($actor, $data) {
            $partner = $this->partner($actor);
            LogisticsPartner::whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $type = $data['personnel_type'];
            abort_unless(in_array($type, ['rider', 'linehaul_driver'], true), 422);
            $model = $type === 'rider' ? Rider::class : LinehaulDriver::class;
            $model::whereKey($data['personnel_id'])->where('logistics_partner_id', $partner->id)->firstOrFail();
            $rate = FinanceMoney::minor($data['rate']);
            if ($rate < 0 || ! in_array($data['type'], self::TYPES, true)
                || ($type === 'rider' && $data['type'] === 'per_trip')
                || ($type === 'linehaul_driver' && in_array($data['type'], ['per_pickup', 'per_delivery'], true))) {
                $this->invalid('Choose a valid compensation arrangement and a nonnegative rate.');
            }
            $overlap = DB::table('partner_compensation_policies')->where('logistics_partner_id', $partner->id)
                ->where('personnel_type', $type)->where('personnel_id', $data['personnel_id'])->where('type', $data['type'])
                ->where(fn ($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>=', $data['effective_from']));
            if (! empty($data['effective_until'])) {
                $overlap->where('effective_from', '<=', $data['effective_until']);
            }
            if ($overlap->exists()) {
                $this->invalid('Effective dates overlap an existing policy of this type. Policies are immutable; configure a non-overlapping period.');
            }
            $id = DB::table('partner_compensation_policies')->insertGetId([
                'logistics_partner_id' => $partner->id, 'personnel_type' => $type, 'personnel_id' => $data['personnel_id'],
                'type' => $data['type'], 'rate_minor' => $rate, 'schedule' => $data['schedule'],
                'effective_from' => $data['effective_from'], 'effective_until' => $data['effective_until'] ?? null,
                'created_by_user_id' => $actor->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            FinancialAudit::record('compensation_policy', $id, 'created', 'user', $actor->id, $data);

            return DB::table('partner_compensation_policies')->find($id);
        });
    }

    public function delivery(Delivery $delivery): void
    {
        $delivery = $delivery->fresh();
        if ($delivery->status !== Delivery::DELIVERED || ! $delivery->proof_path || ! $delivery->delivered_at || ! $delivery->delivered_rider_id) {
            return;
        }
        $this->serviceEvent((int) $delivery->logistics_partner_id, 'rider', (int) $delivery->delivered_rider_id,
            'per_delivery', 'delivery:'.$delivery->id, $delivery->delivered_at, ['delivery_id' => $delivery->id, 'order_id' => $delivery->order_id]);
    }

    public function pickup(Delivery $delivery, int $centerId): void
    {
        if ($delivery->status !== Delivery::AT_SORTING_CENTER || (int) $delivery->origin_sorting_center_id !== $centerId || ! $delivery->pickup_rider_id) {
            return;
        }
        $this->serviceEvent((int) $delivery->logistics_partner_id, 'rider', (int) $delivery->pickup_rider_id,
            'per_pickup', 'pickup:'.$delivery->id, now(), ['delivery_id' => $delivery->id, 'order_id' => $delivery->order_id, 'receiving_center_id' => $centerId]);
    }

    public function trip(LinehaulTrip $trip): void
    {
        if ($trip->status !== LinehaulTrip::COMPLETED || ! $trip->completed_at) {
            return;
        }
        $this->serviceEvent((int) $trip->logistics_partner_id, 'linehaul_driver', (int) $trip->driver_id,
            'per_trip', 'trip:'.$trip->id, $trip->completed_at, ['trip_id' => $trip->id]);
    }

    private function serviceEvent(int $partner, string $personnelType, int $personnel, string $type, string $key, $at, array $evidence): void
    {
        abort_unless(Schema::hasTable('partner_compensation_policies') && Schema::hasTable('partner_personnel_earnings')
            && Schema::hasTable('financial_audit_events'), 503,
            'Financial setup is incomplete. Apply the financial database update before confirming this handover.');

        DB::transaction(function () use ($partner, $personnelType, $personnel, $type, $key, $at, $evidence) {
            LogisticsPartner::whereKey($partner)->lockForUpdate()->firstOrFail();
            $policy = DB::table('partner_compensation_policies')->where('logistics_partner_id', $partner)
                ->where('personnel_type', $personnelType)->where('personnel_id', $personnel)->where('type', $type)
                ->whereDate('effective_from', '<=', $at)->where(fn ($q) => $q->whereNull('effective_until')->orWhereDate('effective_until', '>=', $at))->first();
            if ($policy) {
                $this->insertEarning($policy, $key, $policy->rate_minor, $evidence);
            }
        });
    }

    /** Partner attests approved work time or contractual monthly payroll, independently of Seller funds. */
    public function payroll(User $actor, int $policyId, string $period, int $minutes, string $incentive, string $evidence): object
    {
        return DB::transaction(function () use ($actor, $policyId, $period, $minutes, $incentive, $evidence) {
            $partner = $this->partner($actor);
            LogisticsPartner::whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $policy = DB::table('partner_compensation_policies')->where('id', $policyId)->where('logistics_partner_id', $partner->id)->firstOrFail();
            if (! in_array($policy->type, ['fixed_salary', 'hourly', 'base_incentive'], true) || ! preg_match('/^\d{4}-\d{2}$/D', $period)) {
                $this->invalid('Use a monthly payroll policy and YYYY-MM period.');
            }
            $date = Carbon::createFromFormat('!Y-m', $period);
            if ($date->format('Y-m') !== $period || $date->copy()->endOfMonth()->isFuture()
                || $date->toDateString() < $policy->effective_from
                || ($policy->effective_until && $date->copy()->endOfMonth()->toDateString() > $policy->effective_until)) {
                $this->invalid('Payroll needs a completed calendar month fully covered by the policy. Partial-month proration requires a separate agreed policy.');
            }
            $bonus = FinanceMoney::minor($incentive);
            if ($bonus < 0 || ($policy->type !== 'base_incentive' && $bonus !== 0)
                || ($policy->type === 'hourly' && ($minutes <= 0 || $minutes > 44640))) {
                $this->invalid('Invalid approved hours or incentive.');
            }
            $amount = $policy->type === 'hourly' ? intdiv($policy->rate_minor * $minutes + 30, 60) : $policy->rate_minor + $bonus;

            return $this->insertEarning($policy, 'payroll:'.$policy->personnel_type.':'.$policy->personnel_id.':'.$policy->type.':'.$period,
                $amount, ['period' => $period, 'approved_minutes' => $minutes, 'incentive_minor' => $bonus, 'evidence' => $evidence, 'recorded_by' => $actor->id]);
        });
    }

    private function insertEarning(object $policy, string $key, int $amount, array $evidence): object
    {
        $existing = DB::table('partner_personnel_earnings')->where('logistics_partner_id', $policy->logistics_partner_id)->where('event_key', $key)->first();
        if ($existing) {
            if ($existing->amount_minor != $amount || $existing->policy_id != $policy->id) {
                $this->invalid('This work event already has an immutable earning.');
            }

            return $existing;
        }
        $id = DB::table('partner_personnel_earnings')->insertGetId([
            'policy_id' => $policy->id, 'logistics_partner_id' => $policy->logistics_partner_id,
            'personnel_type' => $policy->personnel_type, 'personnel_id' => $policy->personnel_id,
            'event_key' => $key, 'amount_minor' => $amount, 'status' => 'pending',
            'evidence' => json_encode($evidence, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
        ]);
        FinancialAudit::record('personnel_earning', $id, 'calculated', 'system', 0, ['policy_id' => $policy->id, 'amount_minor' => $amount, ...$evidence]);

        return DB::table('partner_personnel_earnings')->find($id);
    }

    public function approve(User $actor, int $id): void
    {
        $this->transition($actor, $id, 'pending', 'approved', null);
    }

    public function paid(User $actor, int $id, string $reference): void
    {
        if (strlen(trim($reference)) < 4) {
            $this->invalid('Actual payment reference is required.');
        }
        $this->transition($actor, $id, 'approved', 'paid', trim($reference));
    }

    private function transition(User $actor, int $id, string $from, string $to, ?string $reference): void
    {
        DB::transaction(function () use ($actor, $id, $from, $to, $reference) {
            $partner = $this->partner($actor);
            $entry = DB::table('partner_personnel_earnings')->where('id', $id)->where('logistics_partner_id', $partner->id)->lockForUpdate()->firstOrFail();
            if ($entry->status !== $from) {
                $this->invalid('Earning status changed. Refresh before continuing.');
            }
            $values = ['status' => $to, 'updated_at' => now()];
            if ($to === 'approved') {
                $values += ['approved_by_user_id' => $actor->id, 'approved_at' => now()];
            } else {
                $values += ['paid_by_user_id' => $actor->id, 'paid_at' => now(), 'payment_reference' => $reference];
            }
            DB::table('partner_personnel_earnings')->where('id', $id)->update($values);
            FinancialAudit::record('personnel_earning', $id, $to, 'user', $actor->id, ['from' => $from, 'reference' => $reference, 'amount_minor' => $entry->amount_minor]);
        });
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['compensation' => $message]);
    }
}
