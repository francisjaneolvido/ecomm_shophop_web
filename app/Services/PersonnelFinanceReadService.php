<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PersonnelFinanceReadService
{
    public function earnings(int $partner, string $type, int $personnel): array
    {
        $query = DB::table('partner_personnel_earnings')->where('logistics_partner_id', $partner)
            ->where('personnel_type', $type)->where('personnel_id', $personnel);
        $total = fn (string $status) => FinanceMoney::decimal((int) (clone $query)->where('status', $status)->sum('amount_minor'));

        $policies = DB::table('partner_compensation_policies')->where('logistics_partner_id', $partner)
            ->where('personnel_type', $type)->where('personnel_id', $personnel)->get()->keyBy('id');

        return [
            'pending' => $total('pending'), 'available' => $total('approved'), 'paid' => $total('paid'),
            'entries' => (clone $query)->orderByDesc('id')->limit(100)->get()->map(fn ($e) => [
                'id' => $e->id, 'kind' => $e->event_key, 'event_key' => $e->event_key,
                'amount' => FinanceMoney::decimal($e->amount_minor), 'status' => $e->status,
                'compensation_type' => $policies->get($e->policy_id)?->type,
                'schedule' => $policies->get($e->policy_id)?->schedule,
                'payment_reference' => $e->payment_reference, 'paid_at' => $e->paid_at,
                'distribution_reference' => null, 'order_id' => json_decode($e->evidence, true)['order_id'] ?? null,
            ]),
            'policies' => DB::table('partner_compensation_policies')->where('logistics_partner_id', $partner)
                ->where('personnel_type', $type)->where('personnel_id', $personnel)->orderByDesc('effective_from')->get(),
        ];
    }
}
