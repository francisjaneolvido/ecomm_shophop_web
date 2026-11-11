<?php

namespace Tests\Unit;

use App\Models\Logistics\OperationalServiceArea;
use App\Services\RiderServiceAreaService;
use PHPUnit\Framework\TestCase;

class RiderBarangayAliasTest extends TestCase
{
    public function test_safe_barangay_prefix_variations_are_equivalent(): void
    {
        $matcher = new RiderServiceAreaService;
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'Calamba',
            'barangay' => 'Barangay 1', 'service_type' => 'both',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba City', 'Brgy. 1', 'pickup'));
        $this->assertTrue($matcher->matches($area, 'Laguna', 'City of Calamba', 'Barangay No. 1', 'delivery'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 2', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Bucal', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Bulacan', 'Calamba', 'Brgy. 1', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Pila', 'Brgy. 1', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Brgy. 1', 'returns'));
    }

    public function test_names_are_not_coerced_to_numbered_barangays(): void
    {
        $matcher = new RiderServiceAreaService;
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'Calamba',
            'barangay' => 'Bucal', 'service_type' => 'delivery',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba', 'Brgy. Bucal', 'delivery'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 7', 'delivery'));
    }

    public function test_numbered_poblacion_labels_match_the_same_short_barangay_label(): void
    {
        $matcher = new RiderServiceAreaService;
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'Calamba',
            'barangay' => 'Barangay 1 (Pob.)', 'service_type' => 'pickup',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba City', 'Barangay 1', 'pickup'));
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba', 'Brgy. 1 (Poblacion)', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 2', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 2 (Pob.)', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Los Banos', 'Barangay 1', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Batangas', 'Calamba', 'Barangay 1', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 1', 'delivery'));
        $area->barangay = 'Barangay 1';
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 1 (Pob.)', 'pickup'));
        $area->barangay = 'San Jose (Pob.)';
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'San Jose', 'pickup'));
        $area->barangay = 'Barangay 1 (Upper)';
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Barangay 1', 'pickup'));
    }

    public function test_poblacion_alias_preserves_branch_and_availability_restrictions(): void
    {
        $matcher = new class extends RiderServiceAreaService {
            public function configured(int $centerId): bool { return true; }
        };
        $rider = new \App\Models\Logistics\Rider([
            'name' => 'Covered rider', 'sorting_center_id' => 7,
            'status' => 'active', 'availability_status' => 'available',
        ]);
        $rider->id = 1;
        $rider->setRelation('lifecycleProfile', null);
        $rider->setRelation('operationalServiceAreas', collect([
            new OperationalServiceArea([
                'sorting_center_id' => 7, 'province' => 'Laguna', 'municipality' => 'Calamba',
                'barangay' => 'Barangay 1 (Pob.)', 'service_type' => 'both',
            ]),
        ]));
        $offline = clone $rider;
        $offline->id = 2;
        $offline->availability_status = 'offline';
        $otherBranch = clone $rider;
        $otherBranch->id = 3;
        $otherBranch->sorting_center_id = 8;
        $suspended = clone $rider;
        $suspended->id = 4;
        $suspended->status = 'suspended';
        $matches = $matcher->matchingRiders(collect([$rider, $offline, $otherBranch, $suspended]),
            7, 'Laguna', 'Calamba City', 'Barangay 1', 'pickup');
        $this->assertSame([1], $matches->pluck('id')->all());
    }

}
