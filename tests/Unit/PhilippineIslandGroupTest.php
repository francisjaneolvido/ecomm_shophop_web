<?php
namespace Tests\Unit;

use App\Models\Logistics\SortingCenter;
use App\Services\PhilippineIslandGroup;
use PHPUnit\Framework\TestCase;

class PhilippineIslandGroupTest extends TestCase
{
    public function test_psgc_region_names_and_cross_group_checks(): void
    {
        $groups=new PhilippineIslandGroup();
        $laguna=new SortingCenter(['region'=>'Region IV-A (CALABARZON)']);
        $davao=new SortingCenter(['region'=>'Region XI (Davao Region)']);
        $cebu=new SortingCenter(['region'=>'Region VII (Central Visayas)']);
        $negros=new SortingCenter(['region'=>'Negros Island Region (NIR)']);
        $bulacan=new SortingCenter(['region'=>'Region III (Central Luzon)']);
        $this->assertSame('luzon',$groups->forCenter($laguna));
        $this->assertSame('visayas',$groups->forCenter($cebu));
        $this->assertSame('visayas',$groups->forCenter($negros));
        $this->assertSame('mindanao',$groups->forCenter($davao));
        $this->assertTrue($groups->isCrossGroup($laguna,$davao));
        $this->assertTrue($groups->isCrossGroup($laguna,$cebu));
        $this->assertFalse($groups->isCrossGroup($laguna,$bulacan));
        $this->assertNull($groups->forCenter(new SortingCenter(['region'=>'Unknown'])));
    }
}
