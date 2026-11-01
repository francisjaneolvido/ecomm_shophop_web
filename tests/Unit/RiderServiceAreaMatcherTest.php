<?php

namespace Tests\Unit;

use App\Models\Logistics\OperationalServiceArea;
use App\Services\RiderServiceAreaService;
use PHPUnit\Framework\TestCase;

class RiderServiceAreaMatcherTest extends TestCase
{
    public function test_only_matching_province_city_barangay_and_role_qualify(): void
    {
        $matcher = new RiderServiceAreaService();
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'City of Calamba',
            'barangay' => 'Canlubang', 'service_type' => 'pickup',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'City of Calamba', 'Canlubang', 'pickup'));
        $this->assertTrue($matcher->matches($area, ' laguna ', 'city  of  calamba', 'canlubang', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'City of Calamba', 'Mayapa', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'City of Calamba', 'Canlubang', 'delivery'));
        $this->assertFalse($matcher->matches($area, 'Bulacan', 'City of Calamba', 'Canlubang', 'pickup'));
    }

    public function test_citywide_requires_explicit_assignment_and_correct_city(): void
    {
        $matcher = new RiderServiceAreaService();
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'Pila', 'barangay' => null,
            'service_type' => 'both',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Pila', 'San Antonio', 'delivery'));
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Pila', null, 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Lumban', 'San Antonio', 'delivery'));
    }
    public function test_only_available_matching_riders_are_ranked_and_exact_comes_first(): void
    {
        $matcher = new class extends RiderServiceAreaService {
            public function configured(int $centerId): bool { return $centerId === 7; }
        };
        $city = new \App\Models\Logistics\Rider(['sorting_center_id' => 7,
            'status' => 'active', 'availability_status' => 'available', 'name' => 'A Citywide']);
        $city->id = 11;
        $city->setRelation('operationalServiceAreas', collect([
            new OperationalServiceArea(['sorting_center_id'=>7,'province'=>'Laguna',
                'municipality'=>'Pila','barangay'=>null,'service_type'=>'both']),
        ]));
        $exact = new \App\Models\Logistics\Rider(['sorting_center_id' => 7,
            'status' => 'active', 'availability_status' => 'available', 'name' => 'Z Exact']);
        $exact->id = 22;
        $exact->setRelation('operationalServiceAreas', collect([
            new OperationalServiceArea(['sorting_center_id'=>7,'province'=>'Laguna',
                'municipality'=>'Pila','barangay'=>'San Antonio','service_type'=>'delivery']),
        ]));
        $away = clone $exact;
        $away->id = 33;
        $away->availability_status = 'unavailable';
        $result = $matcher->matchingRiders(collect([$city, $away, $exact]), 7,
            'Laguna', 'Pila', 'San Antonio', 'delivery');
        $this->assertSame([22, 11], $result->pluck('id')->all());
        $this->assertSame([], $matcher->matchingRiders(collect([$exact]), 7,
            'Laguna', 'Lumban', 'San Antonio', 'delivery')->pluck('id')->all());
    }

    public function test_exact_barangay_takes_precedence_over_citywide_and_never_crosses_city(): void
    {
        $matcher = new RiderServiceAreaService();
        $exact = new OperationalServiceArea(['province' => 'Laguna', 'municipality' => 'Pila',
            'barangay' => 'San Antonio', 'service_type' => 'both']);
        $citywide = new OperationalServiceArea(['province' => 'Laguna', 'municipality' => 'Pila',
            'barangay' => null, 'service_type' => 'both']);
        $this->assertSame(2, $matcher->matchPriority($exact, 'Laguna', 'Pila', 'San Antonio', 'delivery'));
        $this->assertSame(1, $matcher->matchPriority($citywide, 'Laguna', 'Pila', 'San Antonio', 'delivery'));
        $this->assertSame(0, $matcher->matchPriority($exact, 'Laguna', 'Pila', 'San Roque', 'pickup'));
        $this->assertSame(0, $matcher->matchPriority($citywide, 'Laguna', 'Lumban', 'San Antonio', 'delivery'));
        $this->assertSame(0, $matcher->matchPriority($exact, 'Laguna', 'Pila', 'San Antonio', 'returns'));
    }
}
