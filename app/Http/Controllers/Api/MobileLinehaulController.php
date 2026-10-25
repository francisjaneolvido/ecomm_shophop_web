<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Services\LinehaulService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MobileLinehaulController extends Controller
{
    public function trips(Request $request): JsonResponse
    {
        $driver = $this->driver($request);
        $trips = LinehaulTrip::with(['fromCenter', 'toCenter', 'vehicle', 'manifests'])
            ->where('driver_id', $driver->id)
            ->where('logistics_partner_id', $driver->logistics_partner_id)
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (LinehaulTrip $trip) => [
                'id' => $trip->id,
                'code' => $trip->code,
                'status' => $trip->status,
                'from' => $trip->fromCenter?->name,
                'to' => $trip->toCenter?->name,
                'vehicle_code' => $trip->vehicle?->code,
                'plate_number' => $trip->vehicle?->plate_number,
                'vehicle_type' => $trip->vehicle?->vehicle_type,
                'manifest_code' => $trip->manifests->first()?->code,
                'parcel_count' => (int) ($trip->manifests->sum('parcel_count')),
                'departed_at' => optional($trip->departed_at)?->toIso8601String(),
                'arrived_at' => optional($trip->arrived_at)?->toIso8601String(),
                'completed_at' => optional($trip->completed_at)?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Linehaul trips loaded.',
            'data' => [
                'driver' => ['id' => $driver->id, 'name' => $driver->name],
                'trips' => $trips,
            ],
        ]);
    }

    public function preview(Request $request, LinehaulService $linehaul): JsonResponse
    {
        $driver = $this->driver($request);
        $data = $request->validate(['manifest_code' => ['required', 'string', 'max:80']]);

        try {
            $manifest = $linehaul->previewForDriver($driver, $data['manifest_code']);
        } catch (ValidationException $exception) {
            return $this->conflict($exception, 'Manifest is not available for this Driver.');
        }

        return response()->json([
            'success' => true,
            'status' => 'ready',
            'message' => 'Manifest verified. Confirm the physical Linehaul handoff.',
            'data' => ['manifest' => $manifest],
        ]);
    }

    public function confirm(Request $request, LinehaulService $linehaul): JsonResponse
    {
        $driver = $this->driver($request);
        $data = $request->validate([
            'manifest_code' => ['required', 'string', 'max:80'],
            'action' => ['required', 'string', 'in:depart,arrive'],
        ]);

        try {
            $result = $data['action'] === 'depart'
                ? $linehaul->depart($driver, $data['manifest_code'])
                : $linehaul->arrive($driver, $data['manifest_code']);
        } catch (ValidationException $exception) {
            return $this->conflict($exception, 'Linehaul action could not be confirmed.');
        }

        return response()->json([
            'success' => true,
            'status' => $data['action'] === 'depart' ? 'in_transit' : 'arrived',
            'message' => $data['action'] === 'depart'
                ? 'Manifest custody accepted. Linehaul trip is now in transit.'
                : 'Arrival recorded. Destination staff may now scan the parcels during unloading.',
            'data' => ['manifest' => $result],
        ]);
    }

    private function driver(Request $request): LinehaulDriver
    {
        $actor = $request->user();
        abort_unless($actor instanceof LinehaulDriver, 403, 'Linehaul Driver login required.');
        $driver = $actor->fresh(['partner.user']);
        abort_unless($driver && $driver->status === 'active' && $driver->partner?->user?->status === 'approved',
            403, 'Active Linehaul Driver required.');
        return $driver;
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
