<?php

namespace Tests\Unit;

use App\Models\Logistics\OperationalServiceArea;
use App\Services\RiderServiceAreaService;
use PHPUnit\Framework\TestCase;

class PickupCoverageRuleTest extends TestCase
{
    public function test_exact_seller_barangay_pickup_is_not_citywide_or_delivery_permission(): void
    {
        $rules = new RiderServiceAreaService();
        $area = new OperationalServiceArea([
            'province' => 'Laguna',
            'municipality' => 'Calamba City',
            'barangay' => 'Barangay 1',
            'service_type' => 'pickup',
        ]);
        $this->assertSame(2, $rules->matchPriority($area, 'Laguna', 'City of Calamba', 'Brgy. 1', 'pickup'));
        $this->assertFalse($rules->matches($area, 'Laguna', 'Calamba', 'Barangay 7', 'pickup'));
        $this->assertFalse($rules->matches($area, 'Laguna', 'Calamba', 'Bucal', 'pickup'));
        $this->assertFalse($rules->matches($area, 'Laguna', 'Calamba', 'Barangay 1', 'delivery'));
        $this->assertFalse($rules->matches($area, 'Laguna', 'Pila', 'Barangay 1', 'pickup'));
        $this->assertFalse($rules->matches($area, 'Batangas', 'Calamba', 'Barangay 1', 'pickup'));
        $this->assertFalse($rules->matches($area, 'Laguna', 'Calamba', 'Barangay 1', 'returns'));
    }

    public function test_location_hash_does_not_merge_pickup_and_delivery_or_other_barangays(): void
    {
        $a = RiderServiceAreaService::locationKey('Laguna', 'Calamba City', 'Barangay 1', 'pickup');
        $this->assertNotSame($a, RiderServiceAreaService::locationKey('Laguna', 'Calamba City', 'Barangay 1', 'delivery'));
        $this->assertNotSame($a, RiderServiceAreaService::locationKey('Laguna', 'Calamba City', 'Barangay 2', 'pickup'));
    }
}
