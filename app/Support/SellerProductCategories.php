<?php

namespace App\Support;

/** Single source of truth: the category tree used at registration and Buyer category browsing. */
final class SellerProductCategories
{
    public static function forBusiness(?string $registered): array
    {
        foreach (config('shophop_categories', []) as $group) {
            if (strcasecmp(trim((string) ($group['name'] ?? '')), trim((string) $registered)) === 0) {
                return array_values((array) ($group['subcategories'] ?? []));
            }
        }
        // Fail closed for unsupported/legacy registered categories. Admin can correct
        // category explicitly, rather than allowing arbitrary cross-business listing.
        return [];
    }
}
