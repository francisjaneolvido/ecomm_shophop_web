<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogisticsConsoleAccessBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_other_roles_cannot_enter_console(): void
    {
        // Role middleware remains the outer boundary on every operational page.
        $this->get(route('logistics.dashboard'))->assertRedirect(route('login'));
        foreach (['buyer', 'seller', 'admin'] as $role) {
            $this->actingAs($this->user($role))->get(route('logistics.dashboard'))->assertForbidden();
        }
    }

    public function test_public_application_remains_open_and_profileless_operator_is_denied(): void
    {
        // Registration stays public, but an approved role without its profile owns no operational data.
        $this->get(route('logistics.register'))->assertOk();
        $this->actingAs($this->user('logistics'))->get(route('logistics.dashboard'))->assertForbidden();
    }

    public function test_approved_partner_reaches_persisted_console_pages(): void
    {
        // Representative pages now require the registered partner row and normal migration graph.
        $operator = $this->user('logistics');
        DB::table('logistics_partners')->insert([
            'user_id' => $operator->id, 'agreement_rep_name' => 'Test', 'agreement_date' => '2026-09-24',
            'agreement_signature_path' => 'test.jpg', 'company_name' => 'Test Logistics',
            'business_registration_no' => 'TEST', 'line_of_business' => 'motorcycle_courier',
            'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'TEST', 'rep_sex' => 'male',
            'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789',
            'region' => 'Region IV', 'province' => 'Cavite', 'municipality' => 'Imus',
            'barangay' => 'Test', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg',
        ]);
        $this->actingAs($operator)->get(route('logistics.dashboard'))->assertOk();
        $this->get(route('logistics.riders.index'))->assertOk();
        $this->get(route('logistics.deliveries.board'))->assertOk();
        $this->get(route('logistics.reports.index'))->assertOk();
    }

    private function user(string $role): User
    {
        // Verified approved fixture accounts exercise production middleware rather than preview auth.
        $user = new User();
        $user->forceFill(['email' => uniqid($role, true).'@example.test', 'password' => 'unused',
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now()])->save();
        return $user;
    }
}
