<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogisticsOperationsDashboardV4Test extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_see_actionable_dashboard_without_seeded_or_fake_activity(): void
    {
        $user = new User();
        $user->forceFill([
            'name' => 'Logistics Operator',
            'email' => 'operator-v4@example.test',
            'password' => 'unused',
            'account_type' => 'logistics',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        DB::table('logistics_partners')->insert([
            'user_id' => $user->id,
            'agreement_rep_name' => 'Test Representative',
            'agreement_date' => now()->toDateString(),
            'agreement_signature_path' => 'test.jpg',
            'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST123',
            'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg',
            'rep_id_number' => 'TEST123',
            'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01',
            'contact_no' => '09123456789',
            'region' => 'Region IV-A',
            'province' => 'Cavite',
            'municipality' => 'Imus',
            'barangay' => 'Test',
            'street_no' => '1',
            'unit_no' => '1',
            'business_permit_path' => 'test.jpg',
        ]);

        $this->actingAs($user)->get(route('logistics.dashboard'))
            ->assertOk()
            ->assertSee('Parcel journey overview')
            ->assertSee('Needs your attention')
            ->assertSee('No shipment history yet')
            ->assertSee(route('logistics.deliveries.board'), false);

        $this->get(route('logistics.deliveries.board'))
            ->assertOk()
            ->assertSee('Pickup & Delivery Board', false)
            ->assertSee('id="pickup-queue"', false)
            ->assertSee('id="active-deliveries"', false);
    }
}
