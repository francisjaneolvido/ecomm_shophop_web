<?php

namespace Tests\Unit;

use App\Models\Buyer\Order\Order;
use App\Models\LogisticsCoverageArea;
use App\Services\PhilippineDeliveryCoverage;
use Tests\TestCase;

class PhilippineDeliveryCoverageTest extends TestCase
{
    private PhilippineDeliveryCoverage $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new PhilippineDeliveryCoverage();
    }

    public function test_ncr_without_province_keeps_structured_city_instead_of_comma_parsing(): void
    {
        $order = new Order([
            'delivery_region_name' => 'National Capital Region (NCR)',
            'delivery_province_name' => null,
            'delivery_municipality_name' => 'Quezon City',
            'delivery_address' => '52 Street, Barangay 1, Quezon City',
        ]);
        $area = $this->area('region', 'NCR', 'Quezon City|Manila');
        $this->assertSame([null, 'Quezon City', 'National Capital Region (NCR)'], $this->matcher->destination($order));
        $this->assertTrue($this->matcher->coversOrder($area, $order));
        $this->assertFalse($this->matcher->coversOrder($this->area('region', 'Region II (Cagayan Valley)', 'ALL'), $order));
    }

    public function test_ncr_accepts_city_of_manila_and_legacy_ncr_province_alias(): void
    {
        $order = new Order([
            'delivery_region_name' => '',
            'delivery_province_name' => 'National Capital Region (NCR)',
            'delivery_municipality_name' => 'City of Manila',
        ]);
        $this->assertTrue($this->matcher->coversOrder($this->area('region', 'NCR', 'Manila'), $order));
        $this->assertFalse($this->matcher->coversOrder($this->area('region', 'Region II (Cagayan Valley)', 'Manila'), $order));
    }

    public function test_calabarzon_province_and_region_matching_do_not_accept_wrong_city_or_region(): void
    {
        $order = new Order([
            'delivery_region_name' => 'Region IV-A (CALABARZON)',
            'delivery_province_name' => 'Laguna',
            'delivery_municipality_name' => 'Santa Cruz',
        ]);
        $this->assertTrue($this->matcher->coversOrder($this->area('province', 'Laguna', 'Santa Cruz|Pagsanjan'), $order));
        $this->assertTrue($this->matcher->coversOrder($this->area('region', 'CALABARZON', 'Santa Cruz'), $order));
        $this->assertFalse($this->matcher->coversOrder($this->area('province', 'Cavite', 'Santa Cruz'), $order));
        $this->assertFalse($this->matcher->coversOrder($this->area('region', 'Region II (Cagayan Valley)', 'Santa Cruz'), $order));
    }

    public function test_legacy_region_free_text_requires_proof_of_region_not_just_city_name(): void
    {
        $order = new Order(['delivery_address' => '1 Street, Brgy 1, Santa Cruz, Laguna']);
        $this->assertSame(['Laguna', 'Santa Cruz', null], $this->matcher->destination($order));
        $this->assertTrue($this->matcher->coversOrder($this->area('province', 'Laguna', 'Santa Cruz'), $order));
        $this->assertFalse($this->matcher->coversOrder($this->area('region', 'Region II (Cagayan Valley)', 'Santa Cruz'), $order));
    }

    public function test_legacy_ncr_with_region_but_no_city_snapshot_uses_last_address_part(): void
    {
        $order = new Order([
            'delivery_region_name' => 'NCR',
            'delivery_address' => 'Road, Barangay 10, Quezon City',
        ]);
        $this->assertSame([null, 'Quezon City', 'NCR'], $this->matcher->destination($order));
        $this->assertTrue($this->matcher->coversOrder($this->area('region', 'National Capital Region (NCR)', 'ALL'), $order));
    }

    public function test_overlap_detection_handles_ncr_aliases_and_city_of_prefixes(): void
    {
        $this->assertTrue($this->matcher->overlaps(
            $this->area('region', 'NCR', 'City of Manila'),
            $this->area('region', 'National Capital Region (NCR)', 'Manila|Quezon City'),
        ));
        $this->assertFalse($this->matcher->overlaps(
            $this->area('region', 'NCR', 'Manila'),
            $this->area('region', 'Region II (Cagayan Valley)', 'Manila'),
        ));
        $this->assertFalse($this->matcher->overlaps(
            $this->area('province', 'Laguna', 'Santa Cruz'),
            $this->area('province', 'Laguna', 'Pagsanjan'),
        ));
    }

    public function test_inactive_assigned_branch_is_not_available_for_routing(): void
    {
        $partner = new \App\Models\LogisticsPartner();
        $partner->id = 7;
        $center = new \App\Models\Logistics\SortingCenter([
            'logistics_partner_id' => 7, 'status' => 'inactive',
        ]);
        $area = new LogisticsCoverageArea([
            'logistics_partner_id' => 7, 'sorting_center_id' => 20,
            'area_type' => 'region', 'area_name' => 'NCR', 'cities' => 'ALL',
        ]);
        $area->setRelation('sortingCenter', $center);
        $this->assertFalse($this->matcher->availableForPartner($area, $partner));
        $center->status = 'active';
        $this->assertTrue($this->matcher->availableForPartner($area, $partner));
    }

    private function area(string $type, string $name, string $cities): LogisticsCoverageArea
    {
        return new LogisticsCoverageArea(['area_type' => $type, 'area_name' => $name, 'cities' => $cities]);
    }
}
