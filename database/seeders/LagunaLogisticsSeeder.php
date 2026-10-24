<?php

namespace Database\Seeders;

use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Demo logistics network for Laguna:
 *   1 approved Logistics partner -> 1 Laguna Province Hub -> 1 Station Hub per city/municipality
 *   -> 1 Rider per barangay of that city/municipality (barangay list from the PSGC API).
 * Safe to run more than once: anything that already exists is skipped.
 *
 * Run:  php artisan db:seed --class=LagunaLogisticsSeeder
 */
class LagunaLogisticsSeeder extends Seeder
{
    private const PASSWORD = 'Password123';
    private const PARTNER_EMAIL = 'laguna.logistics@example.com';
    private const HUB_MANAGER_EMAIL = 'laguna.provincehub@example.com';

    private const REGION = 'CALABARZON (Region IV-A)';
    private const REGION_CODE = '040000000';
    private const PROVINCE = 'Laguna';
    private const PROVINCE_CODE = '043400000';
    private const PSGC_BASE = 'https://psgc.gitlab.io/api';

    /** Isang hash lang para sa lahat ng rider (mabagal ang bcrypt kung bawat rider). */
    private ?string $passwordHash = null;

    /** All 30 cities and municipalities of Laguna. Spelling must match the address dropdown. */
    private const CITIES = [
        'Alaminos', 'Bay', 'Biñan', 'Cabuyao', 'Calamba', 'Calauan', 'Cavinti', 'Famy',
        'Kalayaan', 'Liliw', 'Los Baños', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena',
        'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal',
        'San Pablo', 'San Pedro', 'Santa Cruz', 'Santa Maria', 'Santa Rosa', 'Siniloan', 'Victoria',
    ];

    public function run(): void
    {
        // Demo accounts use a shared, publicly documented password: never create them in production.
        if (app()->environment('production')) {
            $this->command?->warn('LagunaLogisticsSeeder skipped: demo accounts are not created in production.');
            return;
        }

        $created = 0;
        $riders = 0;

        // HTTP muna bago ang transaction para hindi hawak ang DB habang naghihintay sa API.
        $psgc = $this->psgcBarangays();

        DB::transaction(function () use (&$created, &$riders, $psgc) {
            $partner = $this->partner();
            $provinceHub = $this->provinceHub($partner);

            foreach (self::CITIES as $index => $city) {
                if ($this->stationHub($partner, $provinceHub, $city, $index + 1)) {
                    $created++;
                }
            }

            $stations = SortingCenter::where('parent_id', $provinceHub->id)
                ->where('type', 'station_hub')
                ->whereNull('archived_at')
                ->get();

            foreach ($stations as $station) {
                $this->command?->line("Seeding riders: {$station->municipality}");

                $city = $psgc[$this->key($station->municipality)] ?? null;

                if (! $city) {
                    $this->command?->warn("No PSGC barangays found for {$station->municipality}; skipped riders.");
                    continue;
                }

                foreach ($city['barangays'] as $barangay) {
                    if ($this->rider($partner, $station, $city['code'], $barangay)) {
                        $riders++;
                    }
                }
            }
        });

        $this->command?->info("Laguna logistics ready. {$created} new station hub(s), {$riders} new rider(s) created.");
        $this->command?->line('Partner login: '.self::PARTNER_EMAIL.' / '.self::PASSWORD);
        $this->command?->line('Province hub manager: '.self::HUB_MANAGER_EMAIL.' / '.self::PASSWORD);
        $this->command?->line('Station hub logins: laguna.<city>@example.com / '.self::PASSWORD.' (e.g. laguna.los-banos@example.com)');
        $this->command?->line('Rider logins: rider.<city>.<barangay>@example.com / '.self::PASSWORD.' (e.g. rider.san-pablo.<barangay>@example.com)');
    }

    private function partner(): LogisticsPartner
    {
        $user = User::where('email', self::PARTNER_EMAIL)->first();

        if (! $user) {
            $user = new User();
            $user->forceFill([
                'email' => self::PARTNER_EMAIL,
                'password' => Hash::make(self::PASSWORD),
                'account_type' => 'logistics',
                'status' => 'approved',
                'email_verified_at' => now(),
                'reviewed_at' => now(),
                'notes' => 'Seeded demo Logistics partner (LagunaLogisticsSeeder).',
            ])->save();
        }

        $partner = LogisticsPartner::where('user_id', $user->id)->first();
        if ($partner) {
            return $partner;
        }

        $partner = new LogisticsPartner();
        $partner->forceFill([
            'user_id' => $user->id,
            'agreement_rep_name' => 'Laguna Logistics Rep',
            'agreement_date' => now()->toDateString(),
            'agreement_signature_path' => 'test/signature_placeholder.png',
            'company_name' => 'LagunaGo Logistics',
            'business_registration_no' => 'SEED-LAGUNA-0001',
            'line_of_business' => 'motorcycle_courier',
            'rep_last_name' => 'Rep',
            'rep_first_name' => 'Laguna',
            'rep_middle_initial' => null,
            'rep_valid_id_path' => 'test/valid_id_placeholder.jpg',
            'rep_id_number' => 'SEED-ID-0000001',
            'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01',
            'contact_no' => '09175550100',
            'region' => self::REGION,
            'province' => self::PROVINCE,
            'municipality' => 'Santa Cruz',
            'barangay' => 'Poblacion',
            'street_no' => '1 Provincial Road',
            'unit_no' => 'Unit 1', // NOT NULL on logistics_partners
            'business_permit_path' => 'test/business_permit_placeholder.jpg',
            'accreditation_docs_path' => null,
        ])->save();

        return $partner;
    }

    private function provinceHub(LogisticsPartner $partner): SortingCenter
    {
        $hub = SortingCenter::where('logistics_partner_id', $partner->id)
            ->where('type', 'province_hub')
            ->where('province', self::PROVINCE)
            ->whereNull('archived_at')
            ->first();

        if (! $hub) {
            $hub = SortingCenter::create([
                'logistics_partner_id' => $partner->id,
                'user_id' => null,
                'type' => 'province_hub',
                'parent_id' => null,
                'code' => $this->uniqueCode('SC-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT).'-01'),
                'name' => 'Laguna Province Hub',
                'is_main' => false,
                'status' => 'active',
                'contact_no' => null,
                'region' => self::REGION,
                'province' => self::PROVINCE,
                'municipality' => 'Santa Cruz',
                'barangay' => 'Poblacion',
                'street_no' => '1 Provincial Road',
                'unit_no' => null,
            ]);
        }

        // The province hub manager signs in through the hub portal (branch staff).
        if (! $hub->staff()->where('role', 'manager')->exists()) {
            $hub->staff()->create([
                'logistics_partner_id' => $partner->id,
                'name' => 'Laguna Hub Manager',
                'email' => self::HUB_MANAGER_EMAIL,
                'password' => self::PASSWORD,
                'role' => 'manager',
                'status' => 'active',
            ]);
        }

        return $hub;
    }

    /** @return bool true when a new station hub was created */
    private function stationHub(LogisticsPartner $partner, SortingCenter $provinceHub, string $city, int $sequence): bool
    {
        $existing = SortingCenter::where('parent_id', $provinceHub->id)
            ->where('type', 'station_hub')
            ->where('municipality', $city)
            ->whereNull('archived_at')
            ->first();

        if ($existing) {
            $this->ensureCoverage($existing);
            return false;
        }

        $email = 'laguna.'.Str::slug($city).'@example.com';

        $account = User::where('email', $email)->first();
        if (! $account) {
            $account = new User();
            $account->forceFill([
                'email' => $email,
                'password' => Hash::make(self::PASSWORD),
                'account_type' => 'branch',
                'status' => 'approved',
                'email_verified_at' => now(),
                'reviewed_at' => now(),
                'notes' => 'Seeded station hub login (LagunaLogisticsSeeder).',
            ])->save();
        }

        $station = SortingCenter::create([
            'logistics_partner_id' => $partner->id,
            'user_id' => $account->id,
            'type' => 'station_hub',
            'parent_id' => $provinceHub->id,
            'code' => $this->uniqueCode($provinceHub->code.'-S'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT)),
            'name' => $city.' Branch',
            'is_main' => false,
            'status' => 'active',
            'contact_no' => null,
            'region' => self::REGION,
            'province' => self::PROVINCE,
            'municipality' => $city,
            'barangay' => 'Poblacion',
            'street_no' => 'Hub Road',
            'unit_no' => null,
        ]);

        $this->ensureCoverage($station);

        return true;
    }

    /** The city of the station hub is its delivery coverage (the observer does this too; this is the fallback). */
    private function ensureCoverage(SortingCenter $station): void
    {
        if (DB::table('logistics_coverage_areas')->where('sorting_center_id', $station->id)->exists()) {
            return;
        }

        DB::table('logistics_coverage_areas')->insert([
            'logistics_partner_id' => $station->logistics_partner_id,
            'sorting_center_id' => $station->id,
            'area_type' => 'province',
            'area_name' => self::PROVINCE,
            'cities' => $station->municipality,
            'created_at' => now(),
        ]);
    }

    /** @return bool true when a new rider was created */
    private function rider(LogisticsPartner $partner, SortingCenter $station, string $cityCode, array $barangay): bool
    {
        // Isang rider per barangay per station hub (idempotent).
        if (Rider::where('sorting_center_id', $station->id)->where('barangay_code', $barangay['code'])->exists()) {
            return false;
        }

        $slug = 'rider.'.Str::slug($station->municipality).'.'.Str::slug($barangay['name']);
        $email = $slug.'@example.com';

        if (Rider::where('email', $email)->exists() || User::where('email', $email)->exists()) {
            // May kapangalang barangay sa parehong city: idagdag ang PSGC code para unique.
            $email = $slug.'-'.$barangay['code'].'@example.com';
        }

        $coverageId = DB::table('logistics_coverage_areas')
            ->where('sorting_center_id', $station->id)
            ->value('id');

        $rider = new Rider();
        $rider->forceFill([
            'logistics_partner_id' => $partner->id,
            'sorting_center_id' => $station->id,
            'coverage_area_id' => $coverageId,
            'name' => 'Rider '.$barangay['name'],
            'first_name' => 'Rider',
            'last_name' => $barangay['name'],
            'vehicle_type' => 'Motorcycle',
            'region_code' => self::REGION_CODE,
            'region_name' => self::REGION,
            'province_code' => self::PROVINCE_CODE,
            'province_name' => self::PROVINCE,
            'municipality_code' => $cityCode,
            'municipality_name' => $station->municipality,
            'barangay_code' => $barangay['code'],
            'barangay_name' => $barangay['name'],
            'status' => 'active',
            'availability_status' => 'available',
            'email' => $email,
            'password' => $this->passwordHash ??= Hash::make(self::PASSWORD), // hindi na ire-hash ulit ng cast
            'email_verified_at' => now(),
            'applied_at' => now(),
            'approved_at' => now(),
            'terms_accepted_at' => now(),
        ])->save();

        return true;
    }

    /** Normalize para magtugma ang "City of Biñan" (PSGC) at "Biñan" (dropdown mo). */
    private function key(string $name): string
    {
        $name = preg_replace('/^(city of)\s+|\s+city$/i', '', trim($name));

        return Str::of(Str::ascii($name))->lower()->squish()->toString();
    }

    /**
     * Barangays ng bawat city/municipality ng Laguna mula sa PSGC API.
     *
     * @return array<string, array{code: string, barangays: array<int, array{code: string, name: string}>}>
     */
    private function psgcBarangays(): array
    {
        $http = Http::timeout(30)->retry(3, 500);

        $cities = $http
            ->get(self::PSGC_BASE.'/provinces/'.self::PROVINCE_CODE.'/cities-municipalities.json')
            ->throw()
            ->json();

        $result = [];
        foreach ($cities as $c) {
            $this->command?->line("Fetching barangays: {$c['name']}");

            $list = $http
                ->get(self::PSGC_BASE."/cities-municipalities/{$c['code']}/barangays.json")
                ->throw()
                ->json();

            $result[$this->key($c['name'])] = [
                'code' => $c['code'],
                'barangays' => collect($list)
                    ->map(fn ($b) => ['code' => $b['code'], 'name' => $b['name']])
                    ->sortBy('name')
                    ->values()
                    ->all(),
            ];
        }

        return $result;
    }

    private function uniqueCode(string $base): string
    {
        $code = $base;
        $suffix = 2;
        while (SortingCenter::where('code', $code)->exists()) {
            $code = $base.'-'.$suffix++;
        }

        return $code;
    }
}