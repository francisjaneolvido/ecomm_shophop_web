<?php
namespace App\Services;

use App\Models\Logistics\SortingCenter;

/**
 * High-confidence PSGC region-to-island-group classification for transport safety.
 * An island group is not a drivable island. Road feasibility still requires a
 * separately verified operational route; don't infer a ferry from a place name.
 */
class PhilippineIslandGroup
{
    public function forCenter(?SortingCenter $center): ?string
    {
        if (! $center) return null;
        $region = strtolower(trim((string) $center->region));
        if ($region === '') return null;
        // Standard PSGC region labels, Roman numerals and full names supported.
        $tests = [
            'visayas' => ['/negros island region|\bnir\b/', '/western visayas|region\s*vi\b/', '/central visayas|region\s*vii\b/', '/eastern visayas|region\s*viii\b/'],
            'mindanao' => ['/zamboanga peninsula|region\s*ix\b/', '/northern mindanao|region\s*x\b/', '/davao region|region\s*xi\b/', '/soccsksargen|region\s*xii\b/', '/caraga|region\s*xiii\b/', '/bangsamoro|barmm|armm/'],
            'luzon' => ['/national capital|metro manila|\bncr\b/', '/cordillera|\bcar\b/', '/ilocos|region\s*i\b/', '/cagayan valley|region\s*ii\b/', '/central luzon|region\s*iii\b/', '/calabarzon|region\s*iv[- ]?a\b/', '/mimaropa|region\s*iv[- ]?b\b/', '/bicol|region\s*v\b/'],
        ];
        foreach ($tests as $group => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $region)) return $group;
            }
        }
        return null;
    }

    /** Cross-group movements must never be assigned to one land-only driver. */
    public function isCrossGroup(SortingCenter $from, SortingCenter $to): bool
    {
        $a = $this->forCenter($from);
        $b = $this->forCenter($to);
        return $a !== null && $b !== null && $a !== $b;
    }
}
