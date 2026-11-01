<?php

namespace Tests\Unit;

use App\Models\Logistics\OperationalServiceArea;
use App\Services\RiderServiceAreaService;
use PHPUnit\Framework\TestCase;

class NationwideDemoAreaMatchingTest extends TestCase
{
    public function test_psgc_and_checkout_city_spelling_are_compatible(): void
    {
        $matcher = new RiderServiceAreaService();
        $area = new OperationalServiceArea([
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => 'Canlubang',
            'service_type' => 'both',
        ]);
        $this->assertTrue($matcher->matches($area, 'Laguna', 'City of Calamba', 'Canlubang', 'pickup'));
        $this->assertTrue($matcher->matches($area, 'Laguna', 'Calamba City', 'Canlubang', 'delivery'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Calamba', 'Mayapa', 'pickup'));
    }

    public function test_citywide_demo_rider_qualifies_any_barangay_in_its_own_city(): void
    {
        $matcher = new RiderServiceAreaService();
        $area = new OperationalServiceArea([
            'province' => 'Bulacan', 'municipality' => 'Malolos', 'barangay' => null,
            'service_type' => 'both',
        ]);
        $this->assertTrue($matcher->matches($area, 'Bulacan', 'City of Malolos', 'Santisima Trinidad', 'pickup'));
        $this->assertFalse($matcher->matches($area, 'Laguna', 'Pila', 'Santisima Trinidad', 'delivery'));
    }
}
