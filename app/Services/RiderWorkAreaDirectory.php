<?php

namespace App\Services;

use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/** Reuses the same nine-digit PSGC directory as the existing ShopHop address selectors. */
class RiderWorkAreaDirectory
{
    private function rows(string $path): array
    {
        return Cache::remember('rider-psgc-v1:'.sha1($path), now()->addDay(), function () use ($path) {
            $response = Http::acceptJson()->timeout(20)->get('https://psgc.gitlab.io/api'.$path);
            if (! $response->successful() || ! is_array($response->json())) {
                throw ValidationException::withMessages(['coverage' => 'The shared location directory is unavailable. Retry; unverified locations are not accepted.']);
            }

            return $response->json();
        });
    }

    public static function key(array $place): string
    {
        return hash('sha256', $place['municipality_code'].'|'.$place['barangay_code']);
    }

    public function branch(int $company, int $id): SortingCenter
    {
        return SortingCenter::where('logistics_partner_id', $company)->where('type', 'station_hub')->where('is_main', false)
            ->where('status', 'active')->whereNull('archived_at')->whereHas('partner.user', fn ($q) => $q->where('status', 'approved'))->findOrFail($id);
    }

    public function covers($area, array $place, string $task): bool
    {
        $copy = clone $area;
        if (($place['region_code'] ?? null) === '130000000' && (! $copy->province || in_array(RiderServiceAreaService::normalize($copy->province), ['metro manila', 'ncr', 'national capital region'], true))) {
            $copy->province = $place['province'] ?: 'Metro Manila';
        }

        return app(RiderServiceAreaService::class)->matches($copy, $place['province'] ?: null, $place['municipality'], $place['barangay'], $task);
    }

    public function coverage(SortingCenter $branch): array
    {
        $areas = OperationalServiceArea::where('logistics_partner_id', $branch->logistics_partner_id)->where('sorting_center_id', $branch->id)->get();
        if ($areas->isEmpty()) {
            return [];
        }
        $provinces = collect($this->rows('/provinces/'))->keyBy('code');
        $cities = $this->rows('/cities-municipalities/');
        $out = [];
        foreach ($cities as $city) {
            $cityName = $city['name'] ?? '';
            $province = $provinces->get($city['provinceCode'] ?? '');
            $provinceName = $province['name'] ?? (($city['regionCode'] ?? '') === '130000000' ? 'Metro Manila' : null);
            $possible = $areas->filter(fn ($a) => RiderServiceAreaService::normalizeCity($a->municipality) === RiderServiceAreaService::normalizeCity($cityName)
                && (! $a->province || RiderServiceAreaService::normalize($a->province) === RiderServiceAreaService::normalize($provinceName)
                    || (($city['regionCode'] ?? '') === '130000000' && in_array(RiderServiceAreaService::normalize($a->province), ['ncr', 'metro manila', 'national capital region'], true))));
            if ($possible->isEmpty()) {
                continue;
            }
            foreach ($this->rows('/cities-municipalities/'.$city['code'].'/barangays/') as $barangay) {
                $p = ['region_code' => $city['regionCode'] ?? null, 'province_code' => $city['provinceCode'] ?? null, 'province' => $provinceName,
                    'municipality_code' => $city['code'], 'municipality' => $cityName, 'barangay_code' => $barangay['code'], 'barangay' => $barangay['name']];
                if (! $possible->contains(fn ($a) => $this->covers($a, $p, 'pickup')) || ! $possible->contains(fn ($a) => $this->covers($a, $p, 'delivery'))) {
                    continue;
                }
                $p['key'] = self::key($p);
                $p['label'] = implode(', ', array_filter([$p['barangay'], $p['municipality'], $p['province']]));
                $out[$p['key']] = $p;
            }
        }
        uasort($out, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $out;
    }

    public function selected(SortingCenter $branch, array $keys): array
    {
        $keys = array_values(array_unique($keys));
        if (! $keys) {
            throw ValidationException::withMessages(['coverage' => 'Choose at least one official barangay supporting pickup and delivery.']);
        }
        $all = $this->coverage($branch);
        $out = [];
        foreach ($keys as $key) {
            if (! is_string($key) || ! isset($all[$key])) {
                throw ValidationException::withMessages(['coverage' => 'A selected barangay is not an authorized pickup and delivery location for this Branch.']);
            }
            $out[] = $all[$key];
        }

        return $out;
    }

    public function options(?int $companyId = null, ?int $branchId = null): array
    {
        $companies = LogisticsPartner::where('accepting_riders', true)->whereHas('user', fn ($q) => $q->where('status', 'approved'))
            ->whereHas('sortingCenters', fn ($q) => $q->where('type', 'station_hub')->where('is_main', false)->where('status', 'active')->whereNull('archived_at')
                ->whereHas('operationalServiceAreas'))->orderBy('company_name')->get(['id', 'company_name', 'province', 'municipality']);
        if ($companyId && ! $companies->contains('id', $companyId)) {
            abort(404);
        }
        $branches = $companyId ? SortingCenter::where('logistics_partner_id', $companyId)->where('type', 'station_hub')->where('is_main', false)
            ->where('status', 'active')->whereNull('archived_at')->whereHas('operationalServiceAreas')->orderBy('name')->get() : collect();
        $branch = $branchId ? $this->branch((int) $companyId, $branchId) : null;

        return ['companies' => $companies, 'branches' => $branches->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'address' => $b->addressLabel()]),
            'coverage' => $branch ? array_values($this->coverage($branch)) : []];
    }
}
