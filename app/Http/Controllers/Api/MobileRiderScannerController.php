<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Services\RiderCustodyScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MobileRiderScannerController extends Controller
{
    public function preview(Request $request, RiderCustodyScanService $scanner): JsonResponse
    {
        $rider = $this->rider($request);
        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:60'],
        ]);

        try {
            $handoff = $scanner->inspect($rider, $data['tracking_code']);
        } catch (ValidationException $exception) {
            return $this->conflict($exception, 'Parcel is not ready for a Rider custody scan.');
        }

        return response()->json([
            'success' => true,
            'status' => 'ready',
            'message' => 'Parcel verified. Confirm only after the physical handoff.',
            'data' => ['handoff' => $handoff],
        ]);
    }

    public function confirm(Request $request, RiderCustodyScanService $scanner): JsonResponse
    {
        $rider = $this->rider($request);
        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:60'],
            'expected_action' => ['required', 'string', 'in:seller_pickup,delivery_handoff'],
        ]);

        try {
            $result = $scanner->confirm($rider, $data['tracking_code'], $data['expected_action']);
        } catch (ValidationException $exception) {
            return $this->conflict($exception, 'Custody scan could not be confirmed.');
        }

        return response()->json([
            'success' => true,
            'status' => 'confirmed',
            'message' => $result['message'],
            'data' => ['handoff' => $result],
        ]);
    }

    private function rider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403, 'Rider login required.');

        $rider = $actor->fresh(['partner.user', 'sortingCenter']);
        abort_unless(
            $rider
                && $rider->status === 'active'
                && $rider->email_verified_at
                && $rider->partner?->user?->status === 'approved'
                && $rider->sorting_center_id,
            403,
            'Active Rider with an assigned Logistics center required.',
        );

        return $rider;
    }

    private function conflict(ValidationException $exception, string $fallback): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => 'scan_conflict',
            'message' => collect($exception->errors())->flatten()->first() ?: $fallback,
            'errors' => $exception->errors(),
        ], 409);
    }
}
