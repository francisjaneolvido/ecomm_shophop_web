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
}
