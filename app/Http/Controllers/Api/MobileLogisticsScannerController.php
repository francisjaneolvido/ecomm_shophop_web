<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Logistics\BranchStaff;
use App\Services\ParcelScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Logistics registration stays on Web; approved company account may use the scanning-only mobile workspace. */
class MobileLogisticsScannerController extends Controller
{
    public function branches(Request $request): JsonResponse
    {
        $partner = $this->partner($request);
        $actor = $request->user();
        $centers = $partner->sortingCenters()->where('status', 'active');
        if ($actor instanceof BranchStaff) $centers->whereKey($actor->sorting_center_id);
        return response()->json(['success' => true, 'status' => 'ok', 'message' => 'Active sorting centers loaded.', 'data' => [
            'company' => $partner->company_name,
            'operator' => $actor instanceof BranchStaff ? $actor->name : $partner->company_name,
            'branch_locked' => $actor instanceof BranchStaff,
            'branches' => $centers->orderByDesc('is_main')->orderBy('name')->get(['id','code','name','municipality','province','region']),
        ]]);
    }

    /** Confirm eligibility before a staff member physically accepts a parcel. */
    public function preview(Request $request, ParcelScanService $scanner): JsonResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'sorting_center_id' => ['required', 'integer'],
            'tracking_code' => ['required', 'string', 'max:60'],
        ]);
        $this->requireBranchAccess($request, $partner, (int) $data['sorting_center_id']);
        try {
            $parcel = $scanner->inspect((int) $partner->id, (int) $data['sorting_center_id'], $data['tracking_code']);
        } catch (ValidationException $exception) {
            return response()->json(['success' => false, 'status' => 'scan_conflict',
                'message' => collect($exception->errors())->flatten()->first() ?: 'Parcel cannot be received.'], 409);
        }
        return response()->json(['success' => true, 'status' => 'ready',
            'message' => 'Verify the physical parcel before confirming receipt.', 'data' => ['parcel' => $parcel]]);
    }

    /** Last scans at an authorized branch only; no buyer address or payment details. */
    public function history(Request $request): JsonResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['sorting_center_id' => ['required', 'integer']]);
        $branchId = (int) $data['sorting_center_id'];
        $this->requireBranchAccess($request, $partner, $branchId);

        $entries = DB::table('parcel_scan_events as scans')
            ->join('deliveries as deliveries', 'deliveries.id', '=', 'scans.delivery_id')
            ->where('deliveries.logistics_partner_id', $partner->id)
            ->where('scans.sorting_center_id', $branchId)
            ->orderByDesc('scans.scanned_at')
            ->limit(30)
            ->get(['deliveries.tracking_code', 'scans.event_type', 'scans.scanned_at'])
            ->map(fn ($row) => [
                'tracking_code' => $row->tracking_code,
                'event' => $row->event_type,
                'scanned_at' => $row->scanned_at,
            ]);

        return response()->json(['success' => true, 'status' => 'ok',
            'message' => 'Recent branch scans loaded.', 'data' => ['events' => $entries]]);
    }

    private function requireBranchAccess(Request $request, $partner, int $branchId): void
    {
        abort_unless($partner->sortingCenters()->where('status', 'active')->whereKey($branchId)->exists(),
            403, 'This is not an active branch of your Logistics company.');
        $actor = $request->user();
        if ($actor instanceof BranchStaff) {
            abort_unless($actor->activeForBranch((int) $partner->id, $branchId),
                403, 'Your staff account cannot access this branch.');
        }
    }

    public function receive(Request $request, ParcelScanService $scanner): JsonResponse
    {
        $partner = $this->partner($request);
        $actor = $request->user();
        $data = $request->validate([
            'sorting_center_id' => ['required','integer'],
            'tracking_code' => ['required','string','max:60'],
        ]);

        $this->requireBranchAccess($request, $partner, (int) $data['sorting_center_id']);
        try {
            $result = $scanner->receive(
                (int) $partner->id,
                (int) $data['sorting_center_id'],
                $actor instanceof User ? (int) $actor->id : (int) $partner->user_id,
                $data['tracking_code'],
                $actor instanceof BranchStaff ? $actor->id : null,
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false, 'status' => 'scan_conflict',
                'message' => collect($exception->errors())->flatten()->first() ?: 'Cannot receive parcel.',
                'errors' => $exception->errors(),
            ], 409);
        }

        return response()->json([
            'success' => true, 'status' => 'received',
            'message' => $result['event'] === 'pickup_received'
                ? 'Pickup parcel received and logged at this branch.'
                : 'Inter-branch transfer received and logged.',
            'data' => $result,
        ]);
    }

    private function partner(Request $request)
    {
        $user = $request->user();
        if ($user instanceof BranchStaff) {
            abort_unless($user->status === 'active'
                && $user->sortingCenter?->status === 'active'
                && $user->partner?->user?->status === 'approved', 403, 'Inactive staff or branch.');
            abort_unless((int) $user->sortingCenter->logistics_partner_id === (int) $user->logistics_partner_id,
                403, 'Invalid staff branch.');
            return $user->partner;
        }
        abort_unless($user instanceof User && $user->account_type === 'logistics'
            && $user->status === 'approved', 403, 'Approved Logistics company account required.');
        $partner = $user->logisticsPartner;
        abort_unless($partner, 403, 'Logistics profile unavailable.');
        return $partner;
    }
}
