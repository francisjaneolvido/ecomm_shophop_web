<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Services\FinanceMoney;
use App\Services\CodDiscrepancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MobileRiderCodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        if ($unavailable = $this->unavailableResponse()) {
            return $unavailable;
        }
        $query = CodSettlement::where('collected_by_rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id);
        $openCases = DB::table('cod_discrepancy_cases')->where('status', '!=', 'resolved')->select('cod_settlement_id');
        $cash = (clone $query)->where(fn ($q) => $q->whereIn('status', ['collected', 'discrepancy'])
            ->orWhereIn('id', clone $openCases))->orderByDesc('id')->get()
            ->concat((clone $query)->orderByDesc('id')->limit(100)->get())->unique('id')->values();
        $collected = FinanceMoney::minor((string) ((clone $query)->sum('collected_amount') ?: '0'));
        $received = FinanceMoney::minor((string) ((clone $query)->sum('received_amount') ?: '0'));
        $batches = DB::table('cod_remittance_batches')->where('rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id)->orderByDesc('id')->limit(100)->get();
        $items = DB::table('cod_remittance_items')->whereIn('batch_id', $batches->pluck('id'))->get()->groupBy('batch_id');
        $caseSummaries = app(CodDiscrepancyService::class)->summaries($cash->pluck('id')
            ->merge($items->flatten()->pluck('cod_settlement_id'))->unique()->all());
        $facilityNames = SortingCenter::whereIn('id', $batches->pluck('sorting_center_id'))->pluck('name', 'id');
        $facilities = SortingCenter::where('logistics_partner_id', $rider->logistics_partner_id)
            ->where('status', 'active')->get(['id', 'name']);
        $pending = DB::table('cod_remittance_items as i')->join('cod_remittance_batches as b', 'i.batch_id', '=', 'b.id')
            ->where('b.rider_id', $rider->id)->where('b.logistics_partner_id', $rider->logistics_partner_id)->where('b.status', 'submitted')->pluck('i.cod_settlement_id');
        $handoverDetails = DB::table('cod_remittance_items as i')
            ->join('cod_remittance_batches as b', 'i.batch_id', '=', 'b.id')
            ->leftJoin('sorting_centers as c', 'c.id', '=', 'b.sorting_center_id')
            ->where('b.rider_id', $rider->id)->where('b.logistics_partner_id', $rider->logistics_partner_id)
            ->whereIn('i.cod_settlement_id', $cash->pluck('id'))->orderByDesc('b.id')
            ->get(['i.cod_settlement_id', 'c.name as receiving_facility', 'b.status as handover_status'])
            ->unique('cod_settlement_id')->keyBy('cod_settlement_id');

        return response()->json(['success' => true, 'status' => 'ok', 'data' => [
            'summary' => [
                'to_collect' => FinanceMoney::decimal((int) (Delivery::where('logistics_partner_id', $rider->logistics_partner_id)->where('delivery_rider_id', $rider->id)
                    ->whereIn('status', [Delivery::DELIVERY_ASSIGNED, Delivery::OUT_FOR_DELIVERY])->whereHas('order', fn ($q) => $q->where('payment_method', 'cod')->where('status', Order::STATUS_TO_RECEIVE))
                    ->with('order')->get()->sum(fn ($d) => FinanceMoney::minor($d->order->total_amount)))),
                'collected' => FinanceMoney::decimal($collected),
                'remitted' => FinanceMoney::decimal($received),
                'outstanding' => FinanceMoney::decimal(FinanceMoney::minor((string) ((clone $query)->selectRaw('COALESCE(SUM(CASE WHEN collected_amount > COALESCE(received_amount, 0) THEN collected_amount - COALESCE(received_amount, 0) ELSE 0 END), 0) as liability')->value('liability') ?: '0'))),
                'discrepancies' => (clone $query)->where(fn ($q) => $q->where('status', 'discrepancy')
                    ->orWhereIn('id', clone $openCases))->count(),
            ],
            'facilities' => $facilities,
            'batches' => $batches->map(fn ($batch) => [...(array) $batch, 'receiving_facility' => $facilityNames->get($batch->sorting_center_id),
                'items' => $items->get($batch->id, collect()),
                'discrepancy_cases' => $items->get($batch->id, collect())->map(fn ($item) => $caseSummaries->get($item->cod_settlement_id))->filter()->values()]),
            'collections' => $cash->map(fn ($row) => [
                'collection_id' => $row->id, 'delivery_id' => $row->delivery_id, 'order_id' => $row->order_id,
                'expected_amount' => $row->expected_amount, 'amount' => $row->collected_amount,
                'discrepancy_case' => $caseSummaries->get($row->id),
                'received_amount' => $row->received_amount ?? '0.00',
                'outstanding' => FinanceMoney::decimal(max(0, FinanceMoney::minor($row->collected_amount) - FinanceMoney::minor($row->received_amount))),
                'status' => $row->status, 'collected_at' => $row->collected_at?->toIso8601String(),
                'remitted_at' => $row->remitted_at?->toIso8601String(),
                'receipt_verified' => $row->reconciled_at !== null,
                'receipt_verified_at' => $row->reconciled_at?->toIso8601String(),
                'logistics_remitted_at' => $row->admin_remitted_at?->toIso8601String(),
                'shophop_received_at' => $row->admin_received_at?->toIso8601String(),
                'platform_receipt_status' => match (true) {
                    $row->status === 'discrepancy'
                        || (($caseSummaries->get($row->id)['status'] ?? 'resolved') !== 'resolved') => 'under_review',
                    $row->status !== CodSettlement::RECONCILED => 'not_ready',
                    $row->admin_received_at !== null => 'confirmed',
                    $row->admin_remitted_at !== null => 'awaiting_confirmation',
                    default => 'with_logistics',
                },
                'receiving_facility' => $handoverDetails->get($row->id)?->receiving_facility,
                'handover_status' => $handoverDetails->get($row->id)?->handover_status
                    ?? ($row->reconciled_at ? 'verified' : ($row->remitted_at ? 'submitted' : null)),
                'can_remit' => in_array($row->status, ['collected', 'discrepancy'], true)
                    && ! $pending->contains($row->id)
                    && FinanceMoney::minor($row->collected_amount) > FinanceMoney::minor($row->received_amount),
            ])->values(),
        ]]);
    }

    public function remit(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->rider($request);
        if ($unavailable = $this->unavailableResponse()) {
            return $unavailable;
        }

        return DB::transaction(function () use ($rider, $delivery) {
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('collected_by_rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)
                ->lockForUpdate()->firstOrFail();
            if (DB::table('cod_remittance_items')->where('cod_settlement_id', $cash->id)->exists()) {
                throw ValidationException::withMessages(['settlement' => 'Use the remittance batch workflow for this collection.']);
            }
            if ($cash->status !== CodSettlement::COLLECTED || $cash->remitted_at) {
                return response()->json(['success' => false, 'status' => 'stale',
                    'message' => 'Already remitted. Refresh your COD list.'], 409);
            }
            if (CodSettlement::whereKey($cash->id)->where('status', CodSettlement::COLLECTED)
                ->whereNull('remitted_at')->update([
                    'status' => CodSettlement::REMITTED,
                    'remitted_by_rider_id' => $rider->id,
                    'remitted_at' => now(),
                ]) !== 1) {
                return response()->json(['success' => false, 'status' => 'stale',
                    'message' => 'COD status changed; refresh first.'], 409);
            }

            return response()->json(['success' => true, 'status' => 'ok',
                'message' => 'Rider cash handover recorded. Logistics must confirm actual receipt.']);
        });
    }

    private function unavailableResponse(): ?JsonResponse
    {
        foreach (['cod_remittance_batches', 'cod_remittance_items', 'cash_custody_movements', 'cod_discrepancy_cases', 'cod_discrepancy_resolutions', 'cod_cash_adjustments'] as $table) {
            if (! Schema::hasTable($table)) {
                return response()->json([
                    'success' => false,
                    'status' => 'financial_setup_required',
                    'message' => 'COD remittance is not available yet. Please contact your logistics administrator.',
                ], 503);
            }
        }

        return null;
    }

    private function rider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403);
        $rider = $actor->fresh(['partner.user']);
        // Suspended Riders retain the ability to return previously collected cash.
        abort_unless($rider && in_array($rider->status, ['active', 'suspended'], true)
            && $rider->partner?->user?->status === 'approved', 403);

        return $rider;
    }
}
