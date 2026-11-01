<?php

namespace Database\Seeders;

use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Models\User;
use App\Services\RiderServiceAreaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Optional, non-destructive DEMO extension of NationwideDemoLogisticsSeeder.
 * One individually accessible station per PSGC city/municipality; in pilot cities,
 * multiple riders partition the actual PSGC barangays instead of pretending one
 * motorcycle covers the entire province. Other cities get ONE clearly-labeled
 * citywide demo rider (not a real-world service guarantee).
 *
 * Seed only on disposable local/test data, AFTER NationwideDemoLogisticsSeeder:
 * php artisan db:seed --class=NationwideDetailedNetworkSeeder
 */
class NationwideDetailedNetworkSeeder extends Seeder
{
    private const API = 'https://psgc.gitlab.io/api';
    private const PARTNER_EMAIL = 'nationwide.logistics@example.com';
    private const DEMO_PASSWORD = 'Password123';
    private const PILOT_CITIES = ['calamba', 'pila', 'lumban', 'quezon', 'malolos', 'bacolod', 'davao'];

    private string $passwordHash;
    private array $counters = ['stations' => 0, 'riders' => 0, 'areas' => 0, 'skipped' => 0];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo network seeding is forbidden outside local/testing.');
        }
        $partner = LogisticsPartner::query()->whereHas('user', fn ($q) => $q->where('email', self::PARTNER_EMAIL))->first();
        if (! $partner || $partner->user?->status !== 'approved') {
            throw new RuntimeException('Seed and approve NationwideDemoLogisticsSeeder first. Existing partner data was not touched.');
        }
        foreach (['operational_service_areas', 'operational_area_rider', 'sorting_centers', 'riders'] as $table) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                throw new RuntimeException("Missing {$table}; run php artisan migrate before seeding.");
            }
        }

        // All network calls and geographic validation happen BEFORE database writes.
        // No guessed/barangay placeholder address records are inserted when PSGC is unavailable.
        $http = Http::timeout(55)->retry(2, 500)->acceptJson();
        $provinces = collect($http->get(self::API.'/provinces/')->throw()->json())->keyBy('code');
        $regions = collect($http->get(self::API.'/regions/')->throw()->json())->keyBy('code');
        $cities = collect($http->get(self::API.'/cities-municipalities/')->throw()->json())
            ->filter(fn ($r) => is_array($r) && filled($r['code'] ?? null) && filled($r['name'] ?? null))
            ->unique('code')->sortBy('code')->values();
        if ($provinces->count() < 70 || $regions->count() < 15 || $cities->count() < 1450) {
            throw new RuntimeException('PSGC payload is incomplete. No changes were made.');
        }
        // Detect a mismatch between this PSGC snapshot and the existing
        // nationwide province hubs BEFORE creating any branch accounts.
        $requiredHubCodes = $cities->pluck('provinceCode')->filter()->unique()
            ->map(fn ($code) => 'NW-'.(string) $code.'-H')->values()->all();
        $presentHubCodes = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->whereIn('code', $requiredHubCodes)->pluck('code')->all();
        $missingHubCodes = array_diff($requiredHubCodes, $presentHubCodes);
        if ($missingHubCodes) {
            throw new RuntimeException('Nationwide demo province hubs are missing: '
                .implode(', ', array_slice($missingHubCodes, 0, 8))
                .'. Reconcile NationwideDemoLogisticsSeeder first; no city branches have been written.');
        }
        $pilotLists = [];
        foreach ($cities as $city) {
            if (! in_array($this->normalCity((string) $city['name']), self::PILOT_CITIES, true)) continue;
            $code = (string) $city['code'];
            $rows = collect($http->get(self::API."/cities-municipalities/{$code}/barangays.json")->throw()->json())
                ->filter(fn ($b) => is_array($b) && filled($b['code'] ?? null) && filled($b['name'] ?? null))
                ->unique('code')->sortBy('code')->values()->all();
            if (count($rows) < 1) throw new RuntimeException('Missing PSGC barangays for '.$city['name'].'; no changes made.');
            $pilotLists[$code] = $rows;
        }
        if (filter_var(env('SHOPHOP_NW_DEMO_DRY_RUN', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->command?->info('DRY RUN: PSGC verified '.$provinces->count().' provinces, '
                .$cities->count().' cities/municipalities, '.count($pilotLists).' pilot barangay sets.');
            $this->command?->warn('No database records created. Remove SHOPHOP_NW_DEMO_DRY_RUN before live local seeding.');
            return;
        }
        $this->passwordHash = Hash::make(self::DEMO_PASSWORD); // hash once only

        // Process per center. A subsequent run resumes safely and does not change
        // passwords, current custody, archived centers, existing rider assignments,
        // or area IDs. The original province wildcard station is retained for history.
        foreach ($cities as $index => $city) {
            if ($index % 100 === 0) {
                $this->command?->line('Configuring city demos '.($index + 1).' / '.$cities->count().'…');
            }
            DB::transaction(function () use ($partner, $city, $provinces, $regions, $pilotLists): void {
                $this->addCity($partner, $city, $provinces, $regions, $pilotLists);
            }, 2);
        }
        $this->command?->info('Nationwide DEMO city network: '.json_encode($this->counters));
        $this->command?->line('Branch: nw.<PSGC-city-code>.branch@example.com / '.self::DEMO_PASSWORD);
        $this->command?->line('Citywide Rider: nw.<PSGC-city-code>.rider@example.com / '.self::DEMO_PASSWORD);
        $this->command?->line('Pilot Riders: nw.<PSGC-city-code>.rider1@example.com through rider3 / '.self::DEMO_PASSWORD);
        $this->command?->warn('Virtual DEMO staffing only. This does NOT prove physical service, carrier connections, or rider capacity.');
    }

    private function addCity(LogisticsPartner $partner, array $city, $provinces, $regions, array $pilots): void
    {
        $cityCode = (string) $city['code'];
        $regionCode = (string) ($city['regionCode'] ?? '');
        $provinceCode = (string) ($city['provinceCode'] ?? '');
        $provinceRow = $provinces->get($provinceCode);
        $regionRow = $regions->get($regionCode);
        if (!$regionRow || ($provinceCode !== '' && !$provinceRow)) {
            throw new RuntimeException("Unrecognized PSGC province/region for {$cityCode}. Existing centers remain unchanged.");
        }
        $province = $provinceRow ? (string) $provinceRow['name'] : '';
        $region = (string) $regionRow['name'];
        $name = $this->cityLabel((string) $city['name']);

        // National Capital Region has no province. Some independent/highly
        // urbanized cities similarly use a regional transit hub when PSGC has no
        // parent province; NEVER attach these to an invented province.
        $hubCode = $province !== '' ? 'NW-'.$provinceCode.'-H'
            : ($regionCode === '130000000' ? 'NW-NCR-H' : 'NW-R-'.$regionCode.'-H');
        $hub = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->where('code', $hubCode)->first();
        if (!$hub && $province !== '') {
            throw new RuntimeException("Province hub {$hubCode} missing. Run NationwideDemoLogisticsSeeder first.");
        }
        if (!$hub) {
            $hub = SortingCenter::query()->firstOrCreate([
                'logistics_partner_id' => $partner->id, 'code' => $hubCode,
            ], [
                'name' => $region.' DEMO Regional Gateway', 'type' => 'province_hub',
                'parent_id' => null, 'user_id' => null, 'is_main' => false,
                'status' => 'active', 'region' => $region, 'province' => '',
                'municipality' => $name, 'barangay' => 'DEMO',
                'street_no' => 'VIRTUAL DEMO - no physical facility',
            ]);
        }
        if ($hub->status !== 'active' || $hub->archived_at) {
            $this->counters['skipped']++; return;
        }
        // Never reset a hub's existing manager or other credentials.
        if ($hub->wasRecentlyCreated) {
            $hub->staff()->firstOrCreate(['email' => 'nw.'.$regionCode.'.hub@example.com'], [
                'logistics_partner_id' => $partner->id, 'name' => $region.' DEMO Hub Manager',
                'password' => self::DEMO_PASSWORD, 'role' => 'manager', 'status' => 'active',
            ]);
        }

        $code = 'NW-C-'.$cityCode;
        $email = 'nw.'.$cityCode.'.branch@example.com';
        // Existing partner-operated non-placeholder city branches take priority.
        // Do NOT create a duplicate competing operational branch in their city.
        $manualCenter = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->where('type', 'station_hub')->where('municipality', $name)
            ->where('province', $province)->where('code', '!=', $code)
            ->where('code', 'not like', 'NW-%-S')->whereNull('archived_at')
            ->where('status', 'active')->first();
        if ($manualCenter) { $this->counters['skipped']++; return; }
        $station = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->where('code', $code)->first();
        if ($station && ($station->status !== 'active' || $station->archived_at)) {
            $this->counters['skipped']++; return;
        }
        $user = User::where('email', $email)->first();
        if ($user && $user->account_type !== 'branch') {
            throw new RuntimeException('Demo branch email belongs to another role: '.$email);
        }
        if (!$user) {
            $user = new User();
            $user->forceFill([
                'email' => $email, 'password' => $this->passwordHash,
                'account_type' => 'branch', 'status' => 'approved',
                'email_verified_at' => now(), 'reviewed_at' => now(),
                'notes' => 'Nationwide virtual DEMO city branch; no actual warehouse',
            ])->save();
        }
        if (!$station) {
            $station = SortingCenter::create([
                'logistics_partner_id' => $partner->id, 'code' => $code,
                'user_id' => $user->id, 'parent_id' => $hub->id,
                'type' => 'station_hub', 'name' => $name.' DEMO Branch',
                'status' => 'active', 'is_main' => false,
                'region' => $region, 'province' => $province,
                'municipality' => $name, 'barangay' => 'DEMO',
                'street_no' => 'VIRTUAL DEMO - no physical branch',
            ]);
            $this->counters['stations']++;
        } elseif ((int) $station->user_id !== (int) $user->id
            || (int) $station->parent_id !== (int) $hub->id) {
            throw new RuntimeException('Existing demo station ownership/parent differs: '.$code.'; review manually.');
        }

        // Observer creates a city-specific geographic area. Do not replace its ID:
        // old orders and riders may reference this coverage row.
        $coverage = $station->coverageAreas()->first();
        if (!$coverage) {
            $coverage = $partner->coverageAreas()->create([
                'sorting_center_id' => $station->id, 'area_type' => $province !== '' ? 'province' : 'region',
                'area_name' => $province !== '' ? $province : $region,
                'cities' => $name,
            ]);
        }

        $barangays = $pilots[$cityCode] ?? null;
        if ($barangays !== null) {
            $groups = [[], [], []];
            foreach ($barangays as $i => $brgy) $groups[$i % 3][] = $brgy;
            foreach ($groups as $index => $rows) {
                if (!$rows) continue;
                $rider = $this->rider($partner, $station, $coverage->id, $region, $regionCode,
                    $province, $provinceCode, $name, $cityCode, $index+1);
                foreach ($rows as $brgy) {
                    $area = $this->area($partner, $station, $province, $name, (string) $brgy['name']);
                    $rider->operationalServiceAreas()->syncWithoutDetaching([$area->id]);
                }
            }
        } else {
            // One demo rider covers a full municipality; this is **not** a rider
            // in every barangay and must never be advertised as real capacity.
            $rider = $this->rider($partner, $station, $coverage->id, $region, $regionCode,
                $province, $provinceCode, $name, $cityCode, null);
            $area = $this->area($partner, $station, $province, $name, null);
            $rider->operationalServiceAreas()->syncWithoutDetaching([$area->id]);
        }
    }

    private function rider(LogisticsPartner $partner, SortingCenter $station, int $coverageId,
        string $region, string $regionCode, string $province, string $provinceCode,
        string $name, string $cityCode, ?int $suffix): Rider
    {
        $email = 'nw.'.$cityCode.'.rider'.($suffix ?? '').'@example.com';
        $rider = Rider::where('email', $email)->first();
        if ($rider && ((int) $rider->logistics_partner_id !== (int) $partner->id
            || (int) $rider->sorting_center_id !== (int) $station->id)) {
            throw new RuntimeException('Existing demo rider belongs to another partner/branch: '.$email);
        }
        if (!$rider) {
            $rider = new Rider();
            $rider->forceFill([
                'logistics_partner_id' => $partner->id,
                'sorting_center_id' => $station->id, 'coverage_area_id' => $coverageId,
                'name' => $name.' DEMO Rider'.($suffix === null ? '' : ' '.$suffix),
                'first_name' => 'Demo', 'last_name' => 'Rider',
                'vehicle_type' => 'Motorcycle', 'region_code' => $regionCode,
                'region_name' => $region, 'province_code' => $provinceCode,
                'province_name' => $province, 'municipality_code' => $cityCode,
                'municipality_name' => $name, 'barangay_code' => '',
                'barangay_name' => $suffix === null ? 'Citywide DEMO' : 'Assigned barangays',
                'status' => 'active', 'availability_status' => 'available',
                'email' => $email, 'password' => $this->passwordHash,
                'email_verified_at' => now(), 'applied_at' => now(),
                'approved_at' => now(), 'terms_accepted_at' => now(),
            ])->save();
            $this->counters['riders']++;
        }
        return $rider;
    }

    private function area(LogisticsPartner $partner, SortingCenter $station,
        string $province, string $name, ?string $barangay): OperationalServiceArea
    {
        $key = RiderServiceAreaService::locationKey($province, $name, $barangay, 'both');
        $area = OperationalServiceArea::query()->firstOrCreate([
            'sorting_center_id' => $station->id, 'location_key' => $key,
        ], [
            'logistics_partner_id' => $partner->id, 'province' => $province ?: null,
            'municipality' => $name, 'barangay' => $barangay,
            'service_type' => 'both',
        ]);
        if ($area->wasRecentlyCreated) $this->counters['areas']++;
        return $area;
    }

    private function normalCity(string $value): string
    {
        return trim((string) preg_replace('/\s+city$/i', '', strtolower(Str::ascii($this->cityLabel($value)))));
    }

    private function cityLabel(string $raw): string
    {
        $raw = trim($raw);
        return trim((string) preg_replace('/^City of\s+/iu', '', $raw));
    }
}
