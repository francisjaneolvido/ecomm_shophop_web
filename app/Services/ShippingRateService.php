<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Seller;
use Illuminate\Support\Collection;

class ShippingRateService
{
    public function quote(Seller $seller, Buyer $buyer, Collection $items, string $method = 'standard'): array
    {
        $rates = MarketplaceRateSetting::current();
        $band = $this->routeBand($seller, $buyer);
        $weight = round($items->sum(function ($item) {
            if (is_array($item)) {
                $unitWeight = (float) ($item['weight_kg'] ?? 0.5);
                $quantity = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            } else {
                $variantWeight = $item->variant?->weight_kg;
                $unitWeight = $variantWeight !== null ? (float) $variantWeight : (float) ($item->product?->weight_kg ?? 0.5);
                $quantity = (int) ($item->quantity ?? 1);
            }
            return max(0.05, $unitWeight) * max(1, $quantity);
        }), 3);

        $distanceFee = match ($band) {
            'local' => (float) $rates->local_distance_fee,
            'same_province' => (float) $rates->same_province_distance_fee,
            default => (float) $rates->interprovince_distance_fee,
        };
        $linehaulFee = match ($band) {
            'local' => 0.0,
            'same_province' => (float) $rates->same_province_linehaul_fee,
            default => (float) $rates->interprovince_linehaul_fee,
        };

        $extraWeight = max(0, $weight - (float) $rates->included_weight_kg);
        $weightUnits = (int) ceil($extraWeight - 0.000001);
        $weightFee = round($weightUnits * (float) $rates->extra_weight_fee_per_kg, 2);
        $standard = round((float) $rates->base_shipping_fee + $distanceFee + $linehaulFee + $weightFee, 2);
        $express = round($standard + ($standard * ((float) $rates->express_surcharge_percent / 100)), 2);
        $selected = $method === 'express' ? $express : $standard;
        $expressSurcharge = $method === 'express' ? round($express - $standard, 2) : 0.0;

        return [
            'route_band' => $band,
            'route_label' => match ($band) {
                'local' => 'Same municipality / local route',
                'same_province' => 'Same province / inter-branch route',
                default => 'Inter-province route',
            },
            'weight_kg' => $weight,
            'base_fee' => round((float) $rates->base_shipping_fee, 2),
            'distance_fee' => round($distanceFee, 2),
            'weight_fee' => $weightFee,
            'linehaul_fee' => round($linehaulFee, 2),
            'express_surcharge' => $expressSurcharge,
            'standard_fee' => $standard,
            'express_fee' => $express,
            'selected_fee' => $selected,
            'included_weight_kg' => (float) $rates->included_weight_kg,
            'extra_weight_fee_per_kg' => (float) $rates->extra_weight_fee_per_kg,
        ];
    }

    private function routeBand(Seller $seller, Buyer $buyer): string
    {
        if ($this->sameLocation($seller->municipality_code, $buyer->municipality_code,
            $seller->municipality_name, $buyer->municipality_name)) {
            return 'local';
        }

        if ($this->sameLocation($seller->province_code, $buyer->province_code,
            $seller->province_name, $buyer->province_name)) {
            return 'same_province';
        }

        return 'interprovince';
    }

    private function sameLocation(?string $aCode, ?string $bCode, ?string $aName, ?string $bName): bool
    {
        $aCode = trim((string) $aCode);
        $bCode = trim((string) $bCode);
        if ($aCode !== '' && $bCode !== '') {
            return $aCode === $bCode;
        }

        $normalize = static fn (?string $value) => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $value)));
        $a = $normalize($aName);
        $b = $normalize($bName);
        return $a !== '' && $a === $b;
    }
}
