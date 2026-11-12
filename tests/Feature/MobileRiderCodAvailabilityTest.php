<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MobileRiderCodController;
use App\Models\Logistics\Rider;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileRiderCodAvailabilityTest extends TestCase
{
    private function assertUnavailableWhenMissing(string $missingTable): void
    {
        Schema::shouldReceive('hasTable')->andReturnUsing(fn ($table) => $table !== $missingTable);
        $partner = new LogisticsPartner;
        $partner->setRelation('user', new User(['status' => 'approved']));
        $rider = \Mockery::mock(Rider::class)->makePartial();
        $rider->status = 'active';
        $rider->setRelation('partner', $partner);
        $rider->shouldReceive('fresh')->with(['partner.user'])->twice()->andReturnSelf();
        $request = Request::create('/api/mobile/rider/cod', 'GET');
        $request->setUserResolver(fn () => $rider);
        $controller = new MobileRiderCodController;

        foreach ([$controller->index($request), $controller->remit($request, 123)] as $response) {
            $this->assertSame(503, $response->getStatusCode());
            $data = $response->getData(true);
            $this->assertFalse($data['success']);
            $this->assertSame('financial_setup_required', $data['status']);
            $this->assertStringNotContainsString('SQLSTATE', $data['message']);
            $this->assertStringNotContainsString($missingTable, $data['message']);
        }
    }

    public function test_missing_remittance_batches_returns_safe_unavailable_response(): void
    {
        $this->assertUnavailableWhenMissing('cod_remittance_batches');
    }

    public function test_partially_deployed_remittance_items_returns_safe_unavailable_response(): void
    {
        $this->assertUnavailableWhenMissing('cod_remittance_items');
    }

    public function test_missing_custody_table_does_not_allow_financial_writes(): void
    {
        $this->assertUnavailableWhenMissing('cash_custody_movements');
    }

    public function test_missing_cod_discrepancy_cases_returns_safe_unavailable_response(): void
    {
        $this->assertUnavailableWhenMissing('cod_discrepancy_cases');
    }

    public function test_missing_cod_discrepancy_resolutions_returns_safe_unavailable_response(): void
    {
        $this->assertUnavailableWhenMissing('cod_discrepancy_resolutions');
    }

    public function test_missing_cod_cash_adjustments_returns_safe_unavailable_response(): void
    {
        $this->assertUnavailableWhenMissing('cod_cash_adjustments');
    }
}
