<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Finance\LinehaulDriverEarning;
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


    public function earnings(Request $request): JsonResponse
    {
        $driver = $this->driver($request);
        $query = LinehaulDriverEarning::with(['trip.fromCenter', 'trip.toCenter'])
            ->where('driver_id', $driver->id)
            ->where('logistics_partner_id', $driver->logistics_partner_id);

        $entries = (clone $query)->latest('id')->limit(100)->get()->map(fn (LinehaulDriverEarning $earning) => [
            'id' => $earning->id,
            'trip_code' => $earning->trip?->code,
            'from' => $earning->trip?->fromCenter?->name,
            'to' => $earning->trip?->toCenter?->name,
            'amount' => (float) $earning->amount,
            'status' => $earning->status,
            'payment_reference' => $earning->payment_reference,
            'completed_at' => optional($earning->trip?->completed_at)?->toIso8601String(),
            'available_at' => optional($earning->available_at)?->toIso8601String(),
            'paid_at' => optional($earning->paid_at)?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Linehaul earnings loaded.',
            'data' => [
                'pending' => (float) (clone $query)->where('status', 'pending')->sum('amount'),
                'available' => (float) (clone $query)->where('status', 'available')->sum('amount'),
                'paid' => (float) (clone $query)->where('status', 'paid')->sum('amount'),
                'entries' => $entries,
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

        $message = $manifest['action'] === 'depart'
            ? 'Manifest verified. Confirm custody only after the complete load is physically on your assigned vehicle.'
            : ($manifest['trip_status'] === LinehaulTrip::IN_TRANSIT
                ? 'Trip is in transit. Destination Branch/Hub must scan the Manifest QR to acknowledge arrival.'
                : 'No Driver action is required for this manifest.');

        return response()->json([
            'success' => true,
            'status' => 'ready',
            'message' => $message,
            'data' => ['manifest' => $manifest],
        ]);
    }

    /** Driver authority stops at departure. Arrival belongs to the destination center. */
    public function confirm(Request $request, LinehaulService $linehaul): JsonResponse
    {
        $driver = $this->driver($request);
        $data = $request->validate([
            'manifest_code' => ['required', 'string', 'max:80'],
            'action' => ['required', 'string', 'in:depart'],
        ]);

        try {
            $result = $linehaul->depart($driver, $data['manifest_code']);
        } catch (ValidationException $exception) {
            return $this->conflict($exception, 'Linehaul departure could not be confirmed.');
        }

        return response()->json([
            'success' => true,
            'status' => 'in_transit',
            'message' => 'Manifest custody accepted. Trip is in transit; the destination Branch/Hub must scan this Manifest QR on arrival.',
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
