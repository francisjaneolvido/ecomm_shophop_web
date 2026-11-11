<?php

namespace App\Http\Controllers;

use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Models\User;
use App\Services\CodRemittanceService;
use App\Services\FinanceMoney;
use App\Services\FinancialAudit;
use App\Services\PartnerCompensationService;
use App\Services\PlatformFinancialPolicyService;
use App\Services\ShopHopFinance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HybridFinanceController extends Controller
{
    private function actor(Request $request): User|Rider
    {
        $actor = $request->user() ?? $request->user('rider');
        abort_unless($actor instanceof User || $actor instanceof Rider, 403);
        if ($actor instanceof Rider) {
            abort_unless(in_array($actor->fresh()?->status, ['active', 'suspended'], true)
                && $actor->partner?->user?->status === 'approved', 403);
        } else {
            abort_unless($actor->fresh()?->status === 'approved' && in_array($actor->account_type, ['admin', 'logistics', 'branch'], true), 403);
        }

        return $actor;
    }

    public function index(Request $request)
    {
        $actor = $this->actor($request);
        $isRider = $actor instanceof Rider;
        $isPartner = ! $isRider && $actor->account_type === 'logistics';
        $partnerId = $isRider ? $actor->logistics_partner_id : ($isPartner ? LogisticsPartner::where('user_id', $actor->id)->firstOrFail()->id : null);
        $batchQuery = DB::table('cod_remittance_batches');
        if ($isRider) {
            $batchQuery->where('rider_id', $actor->id)->where('logistics_partner_id', $partnerId);
        } elseif ($isPartner) {
            $batchQuery->where('logistics_partner_id', $partnerId);
        } elseif ($actor->account_type === 'branch') {
            $batchQuery->whereIn('sorting_center_id', SortingCenter::where('user_id', $actor->id)->select('id'));
        }
        $batches = $batchQuery->orderByDesc('id')->paginate(20);
        $items = DB::table('cod_remittance_items')->whereIn('batch_id', $batches->pluck('id'))->get()->groupBy('batch_id');
        $collections = $isRider ? DB::table('cod_settlements')->where('collected_by_rider_id', $actor->id)
            ->where('logistics_partner_id', $partnerId)->whereIn('status', ['collected', 'discrepancy'])->get() : collect();
        $centers = $isRider ? SortingCenter::where('logistics_partner_id', $partnerId)->where('status', 'active')->get() : collect();
        $policies = $isPartner ? DB::table('partner_compensation_policies')->where('logistics_partner_id', $partnerId)->orderByDesc('id')->get() : collect();
        $earnings = $isPartner ? DB::table('partner_personnel_earnings')->where('logistics_partner_id', $partnerId)->orderByDesc('id')->paginate(25, ['*'], 'earnings_page') : null;
        $personnel = $isPartner ? DB::table('riders')->where('logistics_partner_id', $partnerId)->get(['id', 'name']) : collect();
        $drivers = $isPartner ? DB::table('linehaul_drivers')->where('logistics_partner_id', $partnerId)->get(['id', 'name']) : collect();
        $rules = ! $isRider && $actor->account_type === 'admin' ? DB::table('platform_financial_rules')->orderByDesc('effective_at')->get() : collect();
        $expenses = $isPartner ? DB::table('partner_operating_expenses')->where('logistics_partner_id', $partnerId)->orderByDesc('id')->paginate(25, ['*'], 'expenses_page') : null;

        $audit = ! $isRider && $actor->account_type === 'admin' ? DB::table('financial_audit_events')->orderByDesc('id')->paginate(30, ['*'], 'audit_page') : null;

        return view('finance.hybrid', compact('actor', 'isRider', 'isPartner', 'batches', 'items', 'collections', 'centers', 'policies', 'earnings', 'personnel', 'drivers', 'rules', 'expenses', 'audit'));
    }

    public function approveLegacy(Request $request, int $earning, ShopHopFinance $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $partner = LogisticsPartner::where('user_id', $actor->id)->firstOrFail();
        $service->approveLegacyRider($earning, $partner->id, $actor->id);

        return back()->with('status', 'Historical Rider obligation approved by its Partner, independently of Seller settlement.');
    }

    public function closePolicy(Request $request, int $policy)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $partner = LogisticsPartner::where('user_id', $actor->id)->firstOrFail();
        $data = $request->validate(['effective_until' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']]);
        DB::transaction(function () use ($actor, $partner, $policy, $data) {
            LogisticsPartner::whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $row = DB::table('partner_compensation_policies')->where('id', $policy)->where('logistics_partner_id', $partner->id)->lockForUpdate()->firstOrFail();
            if ($row->effective_until || $data['effective_until'] < $row->effective_from) {
                throw ValidationException::withMessages(['policy' => 'Only an open-ended policy may receive a prospective end date after its start.']);
            }
            DB::table('partner_compensation_policies')->where('id', $policy)->update(['effective_until' => $data['effective_until'], 'updated_at' => now()]);
            FinancialAudit::record('compensation_policy', $policy, 'prospective_end_set', 'user', $actor->id, $data);
        });

        return back()->with('status', 'Prospective policy end recorded. Create the replacement policy starting after this date.');
    }

    public function platformRule(Request $request, PlatformFinancialPolicyService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'admin', 403);
        $data = $request->validate([
            'product_percent' => ['required', 'decimal:0,2', 'min:0', 'max:100'],
            'shipping_percent' => ['required', 'decimal:0,2', 'min:0', 'max:100'],
            'seller_hold_days' => ['required', 'integer', 'min:0', 'max:365'],
            'effective_at' => ['required', 'date', 'after:now', 'unique:platform_financial_rules,effective_at'],
        ]);
        $data['effective_at'] = Carbon::parse($data['effective_at'])->format('Y-m-d H:i:s');
        $service->schedule($actor, $data);

        return back()->with('status', 'Prospective platform financial rule scheduled. Previous order snapshots are unchanged.');
    }

    public function expense(Request $request)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $partner = LogisticsPartner::where('user_id', $actor->id)->firstOrFail();
        $data = $request->validate([
            'category' => ['required', Rule::in(['hub_operations', 'branch_operations', 'fuel', 'transport', 'maintenance', 'sorting', 'other'])],
            'amount' => ['required', 'decimal:0,2', 'gt:0', 'max:999999999'],
            'incurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'evidence_reference' => ['required', 'string', 'min:4', 'max:160', Rule::unique('partner_operating_expenses', 'evidence_reference')->where('logistics_partner_id', $partner->id)],
            'payment_reference' => ['nullable', 'string', 'min:4', 'max:120'],
        ]);
        DB::transaction(function () use ($actor, $partner, $data) {
            $id = DB::table('partner_operating_expenses')->insertGetId([
                'logistics_partner_id' => $partner->id, 'category' => $data['category'],
                'amount_minor' => FinanceMoney::minor($data['amount']), 'incurred_on' => $data['incurred_on'],
                'evidence_reference' => $data['evidence_reference'], 'payment_reference' => $data['payment_reference'] ?? null,
                'created_by_user_id' => $actor->id, 'created_at' => now(),
            ]);
            FinancialAudit::record('partner_expense', $id, 'recorded', 'user', $actor->id, $data);
        });

        return back()->with('status', 'Partner operating expense recorded separately from COD and personnel compensation.');
    }

    public function submit(Request $request, CodRemittanceService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof Rider, 403);
        $data = $request->validate([
            'sorting_center_id' => ['required', 'integer'], 'idempotency_key' => ['required', 'string', 'max:80'],
            'reference' => ['required', 'string', 'min:4', 'max:120'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.collection_id' => ['required', 'integer', 'distinct'],
            'items.*.amount' => ['required', 'decimal:0,2', 'gt:0', 'max:999999999'],
        ]);
        $batch = $service->submit($actor, $data['sorting_center_id'], $data['idempotency_key'], $data['reference'], $data['items']);

        return $request->expectsJson() ? response()->json(['success' => true, 'data' => ['batch' => $batch], 'message' => 'Handover submitted; receiver must count and verify cash.'])
            : back()->with('status', 'Handover submitted; receiver must count and verify cash.');
    }

    public function verify(Request $request, int $batch, CodRemittanceService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User, 403);
        $data = $request->validate(['items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.collection_id' => ['required', 'integer', 'distinct'],
            'items.*.amount' => ['required', 'decimal:0,2', 'min:0', 'max:999999999']]);
        $service->verify($actor, $batch, $data['items']);

        return back()->with('status', 'Cash counted and receipt recorded. Differences remain under review.');
    }

    public function policy(Request $request, PartnerCompensationService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $data = $request->validate([
            'personnel_type' => ['required', Rule::in(['rider', 'linehaul_driver'])], 'personnel_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(PartnerCompensationService::TYPES)], 'rate' => ['required', 'decimal:0,2', 'min:0', 'max:999999999'],
            'schedule' => ['required', 'string', 'max:160'], 'effective_from' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'effective_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:effective_from'],
        ]);
        $service->createPolicy($actor, $data);

        return back()->with('status', 'Partner compensation policy created. COD obligations are unchanged.');
    }

    public function payroll(Request $request, PartnerCompensationService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $data = $request->validate(['policy_id' => ['required', 'integer'], 'period' => ['required', 'date_format:Y-m'],
            'minutes' => ['required', 'integer', 'min:0', 'max:44640'], 'incentive' => ['required', 'decimal:0,2', 'min:0', 'max:999999999'],
            'evidence' => ['required', 'string', 'min:4', 'max:1000']]);
        $service->payroll($actor, $data['policy_id'], $data['period'], $data['minutes'], $data['incentive'], $data['evidence']);

        return back()->with('status', 'Payroll recorded for partner approval.');
    }

    public function approve(Request $request, int $earning, PartnerCompensationService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $service->approve($actor, $earning);

        return back()->with('status', 'Earning approved; payment has not been recorded.');
    }

    public function paid(Request $request, int $earning, PartnerCompensationService $service)
    {
        $actor = $this->actor($request);
        abort_unless($actor instanceof User && $actor->account_type === 'logistics', 403);
        $data = $request->validate(['reference' => ['required', 'string', 'min:4', 'max:120'], 'payment_confirmed' => ['required', 'accepted']]);
        $service->paid($actor, $earning, $data['reference']);

        return back()->with('status', 'Confirmed external payment recorded.');
    }
}
