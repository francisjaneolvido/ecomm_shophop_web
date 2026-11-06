<?php

namespace Tests\Feature;

use App\Models\Logistics\RouteLink;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use App\Models\User;
use App\Services\LogisticsRoutingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Focused, isolated regression scenarios for the exact courier filter used by
 * Buyer Web/Mobile checkout previews, order placement and Seller handovers.
 * Only runs against PHPUnit's disposable :memory: database.
 */
class CheckoutVerifiedNetworkEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('status');
            $t->timestamps();
        });
        Schema::create('logistics_partners', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('company_name');
            $t->timestamps();
        });
        Schema::create('sorting_centers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('logistics_partner_id');
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->string('type');
            $t->string('name');
            $t->string('code')->nullable();
            $t->string('status')->default('active');
            $t->boolean('is_main')->default(false);
            $t->string('region')->nullable();
            $t->string('province')->nullable();
            $t->string('municipality')->nullable();
            $t->timestamp('archived_at')->nullable();
            $t->timestamps();
        });
        Schema::create('logistics_coverage_areas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('logistics_partner_id');
            $t->unsignedBigInteger('sorting_center_id')->nullable();
            $t->string('area_type');
            $t->string('area_name');
            $t->text('cities')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('logistics_route_links', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('logistics_partner_id');
            $t->unsignedBigInteger('from_center_id');
            $t->unsignedBigInteger('to_center_id');
            $t->string('mode');
            $t->string('status');
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_same_station_and_same_parent_hub_require_no_interhub_link(): void
    {
        $p = $this->partner();
        $hub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $pila = $this->center($p, 'station_hub', 'Pila Branch', 'CALABARZON', 'Laguna', $hub->id);
        $calamba = $this->center($p, 'station_hub', 'Calamba Branch', 'CALABARZON', 'Laguna', $hub->id);
        $this->area($p, $pila, 'Laguna', 'Pila');
        $this->area($p, $calamba, 'Laguna', 'Calamba');

        $routing = app(LogisticsRoutingService::class);
        $this->assertCount(1, $routing->eligiblePartnersFor($this->seller('Laguna', 'Pila'), $this->destination('Laguna', 'Pila')));
        $this->assertCount(1, $routing->eligiblePartnersFor($this->seller('Laguna', 'Pila'), $this->destination('Laguna', 'Calamba')));
    }

    public function test_ncr_to_laguna_requires_verified_directional_road_path(): void
    {
        $p = $this->partner();
        $ncrHub = $this->center($p, 'province_hub', 'NCR Hub', 'NCR', 'NCR');
        $lagunaHub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $qc = $this->center($p, 'station_hub', 'Quezon City', 'NCR', 'NCR', $ncrHub->id);
        $santaRosa = $this->center($p, 'station_hub', 'Santa Rosa', 'CALABARZON', 'Laguna', $lagunaHub->id);
        $this->area($p, $qc, 'NCR', 'Quezon City');
        $this->area($p, $santaRosa, 'Laguna', 'Santa Rosa');

        $seller = $this->seller('NCR', 'Quezon City', 'NCR');
        $destination = $this->destination('Laguna', 'Santa Rosa', 'CALABARZON');
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));

        $link = $this->link($p, $ncrHub, $lagunaHub, 'road', 'proposed');
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
        $link->update(['status' => 'verified']); // No verified_at is still unusable.
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
        $link->update(['verified_at' => now()]);
        $this->assertCount(1, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
        $link->update(['status' => 'suspended']);
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
    }

    public function test_luzon_to_visayas_and_mindanao_rejects_road_and_accepts_verified_sea_air(): void
    {
        $p = $this->partner();
        $lagunaHub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $pila = $this->center($p, 'station_hub', 'Pila Branch', 'CALABARZON', 'Laguna', $lagunaHub->id);
        $cebuHub = $this->center($p, 'province_hub', 'Cebu Hub', 'Central Visayas', 'Cebu');
        $cebu = $this->center($p, 'station_hub', 'Cebu City', 'Central Visayas', 'Cebu', $cebuHub->id);
        $davaoHub = $this->center($p, 'province_hub', 'Davao Hub', 'Davao Region', 'Davao del Sur');
        $davao = $this->center($p, 'station_hub', 'Davao City', 'Davao Region', 'Davao del Sur', $davaoHub->id);
        $this->area($p, $pila, 'Laguna', 'Pila');
        $this->area($p, $cebu, 'Cebu', 'Cebu City');
        $this->area($p, $davao, 'Davao del Sur', 'Davao City');
        $seller = $this->seller('Laguna', 'Pila');
        $cebuAddress = $this->destination('Cebu', 'Cebu City', 'Central Visayas');
        $davaoAddress = $this->destination('Davao del Sur', 'Davao City', 'Davao Region');

        $roadCebu = $this->link($p, $lagunaHub, $cebuHub, 'road', 'verified', now());
        $roadDavao = $this->link($p, $lagunaHub, $davaoHub, 'road', 'verified', now());
        $routing = app(LogisticsRoutingService::class);
        $this->assertCount(0, $routing->eligiblePartnersFor($seller, $cebuAddress));
        $this->assertCount(0, $routing->eligiblePartnersFor($seller, $davaoAddress));
        $roadCebu->update(['mode' => 'sea']);
        $roadDavao->update(['mode' => 'air']);
        $this->assertCount(1, $routing->eligiblePartnersFor($seller, $cebuAddress));
        $this->assertCount(1, $routing->eligiblePartnersFor($seller, $davaoAddress));

        // The direct Mindanao leg disappears, but an actually verified two-leg
        // Luzon -> Visayas -> Mindanao hub route remains a valid network path.
        $roadDavao->update(['status' => 'suspended']);
        $this->assertCount(0, $routing->eligiblePartnersFor($seller, $davaoAddress));
        $this->link($p, $cebuHub, $davaoHub, 'sea', 'verified', now());
        $this->assertCount(1, $routing->eligiblePartnersFor($seller, $davaoAddress));
    }

    public function test_verified_reverse_direction_does_not_authorize_the_requested_direction(): void
    {
        $p = $this->partner();
        $ncrHub = $this->center($p, 'province_hub', 'NCR Hub', 'NCR', 'NCR');
        $lagunaHub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $qc = $this->center($p, 'station_hub', 'QC', 'NCR', 'NCR', $ncrHub->id);
        $calamba = $this->center($p, 'station_hub', 'Calamba', 'CALABARZON', 'Laguna', $lagunaHub->id);
        $this->area($p, $qc, 'NCR', 'Quezon City');
        $this->area($p, $calamba, 'Laguna', 'Calamba');
        $this->link($p, $lagunaHub, $ncrHub, 'road', 'verified', now());

        $routing = app(LogisticsRoutingService::class);
        $this->assertCount(0, $routing->eligiblePartnersFor(
            $this->seller('NCR', 'Quezon City', 'NCR'),
            $this->destination('Laguna', 'Calamba')
        ));
        $this->assertCount(1, $routing->eligiblePartnersFor(
            $this->seller('Laguna', 'Calamba'),
            $this->destination('NCR', 'Quezon City', 'NCR')
        ));
    }

    public function test_verified_direct_station_road_bypasses_hubs_only_on_same_island_group(): void
    {
        $p = $this->partner();
        $ncrHub = $this->center($p, 'province_hub', 'NCR Hub', 'NCR', 'NCR');
        $lagunaHub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $qc = $this->center($p, 'station_hub', 'QC Branch', 'NCR', 'NCR', $ncrHub->id);
        $santaRosa = $this->center($p, 'station_hub', 'Santa Rosa Branch', 'CALABARZON', 'Laguna', $lagunaHub->id);
        $this->area($p, $qc, 'NCR', 'Quezon City');
        $this->area($p, $santaRosa, 'Laguna', 'Santa Rosa');
        $seller = $this->seller('NCR', 'Quezon City', 'NCR');
        $destination = $this->destination('Laguna', 'Santa Rosa', 'CALABARZON');
        $this->link($p, $qc, $santaRosa, 'road', 'verified', now());
        $this->assertCount(1, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
    }

    public function test_broken_or_unrelated_network_is_not_eligible(): void
    {
        $p = $this->partner();
        $lagunaHub = $this->center($p, 'province_hub', 'Laguna Hub', 'CALABARZON', 'Laguna');
        $pila = $this->center($p, 'station_hub', 'Pila', 'CALABARZON', 'Laguna', $lagunaHub->id);
        $cebuHub = $this->center($p, 'province_hub', 'Cebu Hub', 'Central Visayas', 'Cebu');
        $cebu = $this->center($p, 'station_hub', 'Cebu City', 'Central Visayas', 'Cebu', $cebuHub->id);
        $this->area($p, $pila, 'Laguna', 'Pila');
        $this->area($p, $cebu, 'Cebu', 'Cebu City');
        $seller = $this->seller('Laguna', 'Pila');
        $destination = $this->destination('Cebu', 'Cebu City', 'Central Visayas');
        $this->link($p, $lagunaHub, $cebuHub, 'sea', 'verified', now());
        $this->assertCount(1, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));

        $cebuHub->update(['status' => 'inactive']);
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
        $cebuHub->update(['status' => 'active']);
        $cebu->update(['archived_at' => now()]);
        $this->assertCount(0, app(LogisticsRoutingService::class)->eligiblePartnersFor($seller, $destination));
    }

    private function partner(): LogisticsPartner
    {
        $user = new User();
        $user->forceFill(['status' => 'approved'])->save();
        return LogisticsPartner::create(['user_id' => $user->id, 'company_name' => 'Test Logistics']);
    }

    private function center(LogisticsPartner $partner, string $type, string $name, string $region, string $province, ?int $parent = null): SortingCenter
    {
        return SortingCenter::create([
            'logistics_partner_id' => $partner->id, 'type' => $type,
            'name' => $name, 'status' => 'active', 'region' => $region, 'province' => $province,
            'municipality' => $type === 'station_hub' ? $name : null,
            'parent_id' => $parent,
        ]);
    }

    private function area(LogisticsPartner $partner, SortingCenter $station, string $province, string $city): void
    {
        LogisticsCoverageArea::create([
            'logistics_partner_id' => $partner->id, 'sorting_center_id' => $station->id,
            'area_type' => $province === 'NCR' ? 'region' : 'province',
            'area_name' => $province, 'cities' => $city,
        ]);
    }

    private function seller(string $province, string $municipality, string $region = 'CALABARZON'): Seller
    {
        $seller = new Seller();
        $seller->forceFill([
            'province_name' => $province, 'municipality_name' => $municipality,
            'region_name' => $region,
        ]);
        return $seller;
    }

    private function destination(string $province, string $city, string $region = 'CALABARZON'): array
    {
        return [
            'delivery_province_name' => $province,
            'delivery_municipality_name' => $city,
            'delivery_region_name' => $region,
            'delivery_address' => $city.', '.$province,
        ];
    }

    private function link(LogisticsPartner $partner, SortingCenter $from, SortingCenter $to, string $mode, string $status, $verifiedAt = null): RouteLink
    {
        return RouteLink::create([
            'logistics_partner_id' => $partner->id,
            'from_center_id' => $from->id,
            'to_center_id' => $to->id,
            'mode' => $mode,
            'status' => $status,
            'verified_at' => $verifiedAt,
        ]);
    }
}
