<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;

/**
 * Single destination/coverage rule shared by Seller pickup partner selection and
 * Logistics sorting. No external geocoding requests or database writes occur here.
 */
class PhilippineDeliveryCoverage
{
    /** @return array{0: ?string, 1: ?string, 2: ?string} Province, municipality/city, region. */
    public function destination(Order $order): array
    {
        $province = trim((string) $order->delivery_province_name);
        $city = trim((string) $order->delivery_municipality_name);
        $region = trim((string) $order->delivery_region_name);

        // The city snapshot is the authoritative structured value. NCR legitimately
        // has no province; never overwrite its city by parsing the address label.
        if ($city !== '') {
            return [$province ?: null, $city, $region ?: null];
        }

        // Legacy orders without structured municipality snapshots. The address
        // label historically ended in `city, province` outside NCR. If a known
        // NCR region survives, the last segment is the city instead.
        $parts = array_values(array_filter(array_map('trim', explode(',', (string) $order->delivery_address))));
        if ($region !== '' && $this->sameRegion($region, 'NCR') && count($parts) >= 2) {
            return [null, end($parts), $region];
        }
        if (count($parts) < 2) {
            return [null, null, $region ?: null];
        }
        return [$province ?: $parts[count($parts) - 1], $parts[count($parts) - 2], $region ?: null];
    }

    public function coversOrder(LogisticsCoverageArea $area, Order $order): bool
    {
        [$province, $city, $region] = $this->destination($order);
        return $city !== null && $this->matches($area, $province, $city, $region);
    }

    public function matches(LogisticsCoverageArea $area, ?string $province, string $city, ?string $region): bool
    {
        if (! in_array($area->area_type, ['province', 'region'], true) || trim($city) === '') {
            return false;
        }

        $cities = array_values(array_filter(array_map('trim', preg_split('/[|,]+/', (string) $area->cities))));
        $allCities = $cities === [] || in_array('all', array_map([$this, 'normalize'], $cities), true);
        if (! $allCities && ! in_array($this->normalizeCity($city), array_map([$this, 'normalizeCity'], $cities), true)) {
            return false;
        }

        if ($area->area_type === 'province') {
            return $province !== null && $this->normalize($area->area_name) === $this->normalize($province);
        }
        // Regions may be saved as "Region IV-A (CALABARZON)" or "CALABARZON".
        // For legacy NCR records, the historical "province" field may hold NCR.
        if ($region !== null && trim($region) !== '') {
            return $this->sameRegion($area->area_name, $region);
        }
        if ($province !== null && $this->sameRegion($area->area_name, $province)) {
            return true;
        }

        // Do not match arbitrary cities to an unrelated region simply because
        // that region enumerates them. Without region metadata, this is ambiguous
        // (e.g. the same city name occurs in several provinces/regions).
        return false;
    }

    /** Check same-level coverage overlap for branch activation. Cross-level region/province
     *  conflicts still require a real PSGC mapping and are flagged for manual review. */
    public function overlaps(LogisticsCoverageArea $left, LogisticsCoverageArea $right): bool
    {
        if ($left->area_type !== $right->area_type) return false;
        if ($left->area_type === 'region') {
            if (! $this->sameRegion($left->area_name, $right->area_name)) return false;
        } elseif ($left->area_type === 'province') {
            if ($this->normalize($left->area_name) !== $this->normalize($right->area_name)) return false;
        } else {
            return false;
        }
        $a = array_values(array_filter(array_map('trim', preg_split('/[|,]+/', (string) $left->cities))));
        $b = array_values(array_filter(array_map('trim', preg_split('/[|,]+/', (string) $right->cities))));
        if ($a === [] || $b === [] || in_array('all', array_map([$this, 'normalize'], $a), true)
            || in_array('all', array_map([$this, 'normalize'], $b), true)) return true;
        return array_intersect(array_map([$this, 'normalizeCity'], $a),
            array_map([$this, 'normalizeCity'], $b)) !== [];
    }

    public function availableForPartner(LogisticsCoverageArea $area, LogisticsPartner $partner): bool
    {
        if ((int) $area->logistics_partner_id !== (int) $partner->id) {
            return false;
        }
        if ($area->sorting_center_id !== null) {
            return $area->sortingCenter?->status === 'active'
                && (int) $area->sortingCenter->logistics_partner_id === (int) $partner->id;
        }
        // Pre-multi-branch coverage may be unassigned; it belongs to the main hub
        // only if that hub is operating.
        return $partner->sortingCenters()->where('is_main', true)->where('status', 'active')->exists();
    }

    public function sameRegion(?string $a, ?string $b): bool
    {
        $left = $this->regionAliases($a);
        $right = $this->regionAliases($b);
        return $left !== [] && $right !== [] && array_intersect($left, $right) !== [];
    }

    private function regionAliases(?string $value): array
    {
        $raw = trim((string) $value);
        if ($raw === '') return [];
        if (preg_match('/\bncr\b|national capital region|metro manila/i', $raw)) {
            return ['ncr'];
        }
        if (preg_match('/\bnir\b|negros island region/i', $raw)) {
            return ['nir'];
        }
        $aliases = [$this->normalize($raw)];
        if (preg_match('/^(.*?)\s*\(([^()]+)\)\s*$/', $raw, $groups)) {
            $aliases[] = $this->normalize($groups[1]);
            $aliases[] = $this->normalize($groups[2]);
        }
        return array_values(array_unique(array_filter($aliases)));
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
    }

    private function normalizeCity(string $value): string
    {
        $value = $this->normalize($value);
        $value = preg_replace('/^(?:city|municipality) of\s+/i', '', $value);
        return trim(preg_replace('/\s+city$/i', '', $value));
    }
}
