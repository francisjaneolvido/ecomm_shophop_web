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
use RuntimeException;

/**
 * Opt-in DEMO-ONLY nationwide *administrative* footprint: one partner, one province
 * hub + station + rider per province, and one NCR station. Coverage is provincial
 * wildcard for simulation, not a representation of physical service capability.
 *
 * Run manually: php artisan db:seed --class=NationwideDemoLogisticsSeeder
 * Not part of DatabaseSeeder, never run in production.
 */
class NationwideDemoLogisticsSeeder extends Seeder
{
    private const PASSWORD = 'Password123';
    private const EMAIL = 'nationwide.logistics@example.com';
    private const API = 'https://psgc.gitlab.io/api';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Nationwide demo logistics may only be seeded in local/testing.');
        }

        // The PSGC call is outside the transaction; a failed/incomplete response cannot
        // create a partial network. PHP's CA bundle must be configured for HTTPS.
        $response = Http::timeout(50)->retry(2, 500)->get(self::API.'/provinces/');
        $response->throw();
        $provinces = collect($response->json())
            ->filter(fn ($item) => is_array($item) && ! empty($item['code']) && ! empty($item['name']))
            ->unique('code')->values();
        if ($provinces->count() < 70) {
            throw new RuntimeException('PSGC province list is unexpectedly incomplete; no database changes made.');
        }

        $regionResponse = Http::timeout(50)->retry(2, 500)->get(self::API.'/regions/');
        $regionResponse->throw();
        $regions = collect($regionResponse->json())->keyBy('code');

        DB::transaction(function () use ($provinces, $regions): void {
            $partner = $this->partner();
            foreach ($provinces as $province) {
                $name = trim($province['name']);
                $code = $province['code'];
                $regionCode = (string) ($province['regionCode'] ?? '');
                $region = trim((string) ($regions->get($regionCode)['name'] ?? 'Philippines'));
                $this->provinceNetwork($partner, $name, (string) $code, $region, $regionCode);
            }

            // NCR has no province, and matches by region name instead.
            $this->provinceNetwork($partner, 'Metro Manila', 'NCR', 'NCR', '130000000', true);
        });

        $this->command?->info('Nationwide DEMO network ready: '.($provinces->count()).' provinces + NCR; one provincial hub, station, and rider per area.');
        $this->command?->line('Partner: '.self::EMAIL.' / '.self::PASSWORD);
        $this->command?->line('Branch login: nationwide.<province-slug>.branch@example.com / '.self::PASSWORD);
        $this->command?->line('Hub manager: nationwide.<province-slug>.hub@example.com / '.self::PASSWORD);
        $this->command?->line('Rider: nationwide.<province-slug>.rider@example.com / '.self::PASSWORD);
        $this->command?->warn('DEMO SIMULATION ONLY: This does NOT create actual drivers, vehicles, delivery commitments or operational coverage.');
    }

    private function partner(): LogisticsPartner
    {
        $user = User::where('email', self::EMAIL)->first();
        if ($user && $user->account_type !== 'logistics') {
            throw new RuntimeException('Nationwide demo email belongs to another role.');
        }
        if (! $user) {
            $user = new User();
            $user->forceFill([
                'email' => self::EMAIL,
                'password' => Hash::make(self::PASSWORD),
                'account_type' => 'logistics', 'status' => 'approved',
                'email_verified_at' => now(), 'reviewed_at' => now(),
                'notes' => 'Nationwide DEMO only - not real authorized delivery coverage',
            ])->save();
        }
        $existing = LogisticsPartner::where('user_id', $user->id)->first();
        if ($existing) return $existing;
        $partner = new LogisticsPartner();
        $partner->forceFill([
            'user_id' => $user->id,
            'agreement_rep_name' => 'Demo Representative', 'agreement_date' => now()->toDateString(),
            'agreement_signature_path' => 'test/demo-signature.png',
            'company_name' => 'ShopHop Nationwide DEMO Logistics',
            'business_registration_no' => 'DEMO-NATIONWIDE-0001',
            'line_of_business' => 'motorcycle_courier',
            'rep_last_name' => 'Tester', 'rep_first_name' => 'Nationwide',
            'rep_valid_id_path' => 'test/demo-id.jpg', 'rep_id_number' => 'DEMO-0001',
            'rep_sex' => 'male', 'rep_birthday' => '1990-01-01', 'contact_no' => '09175550999',
            'region' => 'NCR', 'province' => 'Metro Manila', 'municipality' => 'Quezon City',
            'barangay' => 'Poblacion', 'street_no' => '1 Demo Road', 'unit_no' => 'Unit 1',
            'business_permit_path' => 'test/demo-business-permit.jpg',
        ])->save();
        return $partner;
    }

    private function provinceNetwork(LogisticsPartner $partner, string $name, string $code,
                                     string $region, string $regionCode, bool $ncr = false): void
    {
        $slug = Str::slug($name);
        $prefix = 'NW-'.($ncr ? 'NCR' : $code);
        $location = [
            'region' => $region, 'province' => $ncr ? '' : $name, 'municipality' => $ncr ? 'Quezon City' : 'Poblacion',
            'barangay' => 'Poblacion', 'street_no' => '1 Demo Hub Road', 'unit_no' => null,
        ];
        $hub = SortingCenter::firstOrCreate([
            'logistics_partner_id' => $partner->id, 'code' => $prefix.'-H',
        ], array_merge($location, [
            'type' => 'province_hub', 'parent_id' => null, 'user_id' => null,
            'name' => $name.' DEMO Province Hub', 'is_main' => false,
            'status' => 'active',
        ]));
        // Never revive an existing archived/inactive center just by re-seeding.
        if ($hub->status !== 'active' || $hub->archived_at) return;
        $managerEmail = 'nationwide.'.$slug.'.hub@example.com';
        $staff = $hub->staff()->where('email', $managerEmail)->first();
        if (! $staff) {
            $hub->staff()->create([
                'logistics_partner_id' => $partner->id,
                'name' => $name.' Demo Hub Manager',
                'email' => $managerEmail, 'password' => self::PASSWORD,
                'role' => 'manager', 'status' => 'active',
            ]);
        }

        $branchEmail = 'nationwide.'.$slug.'.branch@example.com';
        $user = User::where('email', $branchEmail)->first();
        if ($user && $user->account_type !== 'branch') {
            throw new RuntimeException('Branch email collision: '.$branchEmail);
        }
        if (! $user) {
            $user = new User();
            $user->forceFill([
                'email' => $branchEmail, 'password' => Hash::make(self::PASSWORD),
                'account_type' => 'branch', 'status' => 'approved',
                'email_verified_at' => now(), 'reviewed_at' => now(),
                'notes' => 'Nationwide DEMO province station account',
            ])->save();
        }
        $station = SortingCenter::firstOrCreate([
            'logistics_partner_id' => $partner->id, 'code' => $prefix.'-S',
        ], array_merge($location, [
            'type' => 'station_hub', 'parent_id' => $hub->id,
            'user_id' => $user->id, 'name' => $name.' Demo Station',
            'is_main' => false, 'status' => 'active',
        ]));
        if ($station->status !== 'active' || $station->archived_at) return;
        if ((int) $station->user_id !== (int) $user->id) {
            // Existing relation may be in use: do not seize ownership.
            throw new RuntimeException('Existing station login differs for '.$name.'; resolve manually.');
        }
        $area = DB::table('logistics_coverage_areas')
            ->where('sorting_center_id', $station->id)->first();
        if (! $area) {
            $areaId = DB::table('logistics_coverage_areas')->insertGetId([
                'logistics_partner_id' => $partner->id,
                'sorting_center_id' => $station->id,
                'area_type' => $ncr ? 'region' : 'province',
                'area_name' => $ncr ? 'NCR' : $name,
                'cities' => 'all', 'created_at' => now(),
            ]);
        } else {
            $areaId = (int) $area->id;
            // SortingCenterObserver pre-populates a single-city area on creation.
            // For *new demo stations only*, override this with province-wide
            // simulation coverage; never broaden a pre-existing station's area.
            if ($station->wasRecentlyCreated) {
                DB::table('logistics_coverage_areas')->where('id', $areaId)->update([
                    'area_type' => $ncr ? 'region' : 'province',
                    'area_name' => $ncr ? 'NCR' : $name,
                    'cities' => 'all',
                ]);
            }
        }
        $riderEmail = 'nationwide.'.$slug.'.rider@example.com';
        if (! Rider::where('email', $riderEmail)->exists()) {
            $rider = new Rider();
            $rider->forceFill([
                'logistics_partner_id' => $partner->id,
                'sorting_center_id' => $station->id,
                'coverage_area_id' => $areaId,
                'name' => $name.' Demo Rider', 'first_name' => 'Demo', 'last_name' => 'Rider',
                'vehicle_type' => 'Motorcycle',
                'region_code' => $regionCode, 'region_name' => $region,
                'province_code' => $code, 'province_name' => $name,
                'municipality_code' => '', 'municipality_name' => $station->municipality,
                'barangay_code' => '', 'barangay_name' => 'Poblacion',
                'status' => 'active', 'availability_status' => 'available',
                'email' => $riderEmail, 'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(), 'applied_at' => now(),
                'approved_at' => now(), 'terms_accepted_at' => now(),
            ])->save();
        }
    }
}
