<?php

namespace App\Services;

use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/** Operational coverage is separate from broad marketing/PSGC geographical coverage. */
class RiderServiceAreaService
{
    private array $configuredByCenter = [];

    public static function normalize(?string $value): string
    {
        $clean = preg_replace('/\s+/u', ' ', trim((string) $value));
        return function_exists('mb_strtolower') ? mb_strtolower($clean) : strtolower($clean);
    }

    /** Treat PSGC "City of Calamba" and checkout "Calamba" as the same city. */
    public static function normalizeCity(?string $value): string
    {
        $city = Str::ascii(self::normalize($value));
        $city = (string) preg_replace('/^(?:city|municipality) of\s+/iu', '', $city);
        return trim((string) preg_replace('/\s+city$/iu', '', $city));
    }

    /** Normalize common labels, not different barangays: Brgy. 1 == Barangay 1,
     *  but Barangay 7 != Bucal and Barangay 1 != Barangay 2. */
    public static function normalizeBarangay(?string $value): string
    {
        $barangay = Str::ascii(self::normalize($value));
        $barangay = (string) preg_replace('/^(?:barangay|brgy|bgy)\.?\s+(?:no\.?\s*)?/iu', '', $barangay);
        return trim((string) preg_replace('/\s+/', ' ', $barangay));
    }

    public static function locationKey(?string $province, string $municipality, ?string $barangay, string $type): string
    {
        return hash('sha256', implode('|', [self::normalize($province), self::normalize($municipality),
            self::normalize($barangay), $type]));
    }

    public function configured(int $centerId): bool
    {
        if ($centerId <= 0) return false;
        if (! array_key_exists($centerId, $this->configuredByCenter)) {
            $this->configuredByCenter[$centerId] = OperationalServiceArea::where('sorting_center_id', $centerId)->exists();
        }
        return $this->configuredByCenter[$centerId];
    }

    /** Only rider IDs explicitly assigned to a matching branch service area qualify. */
    public function eligible(Rider $rider, ?int $centerId, ?string $province,
        ?string $municipality, ?string $barangay, string $task): bool
    {
        $centerId = (int) $centerId;
        if (! $centerId || (int) $rider->sorting_center_id !== $centerId || $rider->status !== 'active') {
            return false;
        }
        // Legacy: preserve already-working assignments until this branch is configured.
        if (! $this->configured($centerId)) return true;
        if ($rider->availability_status !== 'available') return false;
        if (self::normalize($municipality) === '') return false;

        $areas = $rider->relationLoaded('operationalServiceAreas')
            ? $rider->operationalServiceAreas
            : $rider->operationalServiceAreas()->where('operational_service_areas.sorting_center_id', $centerId)->get();
        return $areas->contains(fn (OperationalServiceArea $area) =>
            (int) $area->sorting_center_id === $centerId
            && $this->matches($area, $province, $municipality, $barangay, $task));
    }

    public function matches(OperationalServiceArea $area, ?string $province,
        ?string $municipality, ?string $barangay, string $task): bool
    {
        // Operational service-area roles are deliberately restricted to pickup and delivery.
        // A `both` area grants these two roles, not arbitrary tasks such as returns.
        if (! in_array($task, ['pickup', 'delivery'], true)) return false;
        if ($area->service_type !== 'both' && $area->service_type !== $task) return false;
        if (self::normalizeCity($area->municipality) !== self::normalizeCity($municipality)) return false;
        if (self::normalize($area->province) !== self::normalize($province)) return false;
        // A citywide row is explicit permission for every barangay in that city.
        return $area->barangay === null || ($barangay !== null && self::normalize($barangay) !== ''
            && self::normalizeBarangay($area->barangay) === self::normalizeBarangay($barangay));
    }

    /** Exact barangay has higher preference than an explicitly assigned citywide area.
     * A rank is only a suggestion. The caller must revalidate the chosen Rider on write. */
    public function matchPriority(OperationalServiceArea $area, ?string $province,
        ?string $municipality, ?string $barangay, string $task): int
    {
        if (! $this->matches($area, $province, $municipality, $barangay, $task)) return 0;
        return $area->barangay === null ? 1 : 2;
    }

    /** Branch and partner dashboard share this exact, deterministic recommendation rule. */
    public function matchingRiders(Collection $riders, ?int $centerId, ?string $province,
        ?string $municipality, ?string $barangay, string $task): Collection
    {
        $id = (int) $centerId;
        $configured = $this->configured($id);
        return $riders->filter(fn ($rider) => $rider instanceof Rider
            && $this->eligible($rider, $id, $province, $municipality, $barangay, $task))
            ->sort(function (Rider $a, Rider $b) use ($configured, $id, $province, $municipality, $barangay, $task) {
                $score = function (Rider $r) use ($configured, $id, $province, $municipality, $barangay, $task): int {
                    if (! $configured) {
                        return self::normalize($r->barangay_name) !== ''
                            && self::normalize($r->barangay_name) === self::normalize($barangay) ? 2 : 0;
                    }
                    $areas = $r->relationLoaded('operationalServiceAreas')
                        ? $r->operationalServiceAreas
                        : $r->operationalServiceAreas()->where('operational_service_areas.sorting_center_id', $id)->get();
                    return (int) $areas->filter(fn (OperationalServiceArea $area) => (int) $area->sorting_center_id === $id)
                        ->map(fn (OperationalServiceArea $area) => $this->matchPriority($area,
                            $province, $municipality, $barangay, $task))->max();
                };
                return $score($b) <=> $score($a)
                    ?: strcasecmp((string) $a->name, (string) $b->name)
                    ?: ((int) $a->id <=> (int) $b->id);
            })->values();
    }

    public function pickupEligible(Rider $rider, ?int $centerId, $seller): bool
    {
        return $seller !== null && $this->eligible($rider, $centerId,
            $seller->province_name, $seller->municipality_name, $seller->barangay_name, 'pickup');
    }

    public function deliveryEligible(Rider $rider, ?int $centerId, $order): bool
    {
        return $order !== null && $this->eligible($rider, $centerId,
            $order->delivery_province_name, $order->delivery_municipality_name,
            $order->delivery_barangay_name, 'delivery');
    }

    public function branchCovers(int $centerId, ?string $province, ?string $municipality,
        ?string $barangay, string $task): bool
    {
        if (! $this->configured($centerId)) return true;
        return OperationalServiceArea::where('sorting_center_id', $centerId)
            ->whereIn('service_type', ['both', $task])->get()
            ->contains(fn (OperationalServiceArea $area) => $this->matches($area,
                $province, $municipality, $barangay, $task));
    }
}
