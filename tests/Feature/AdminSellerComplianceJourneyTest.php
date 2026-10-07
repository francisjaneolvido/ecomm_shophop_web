<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Seller compliance availability is an HTTP contract, separate from persisted registration moderation.
class AdminSellerComplianceJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Refuse destructive fixture setup unless PHPUnit has its isolated in-memory database.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // An approved Admin must not see fixture Sellers as live compliance review subjects.
    public function test_approved_admin_sees_truthful_compliance_availability(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/seller-compliance')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " seller-view-btn ")]')->length,
            'Seller Compliance must not present fixture Sellers as live compliance subjects.');
        $response->assertSee('A separate Seller legal/compliance workflow is not available.')
            ->assertSee('No live Seller compliance records are displayed here.')
            ->assertSee('Seller document verification and re-verification are unavailable here.')
            ->assertSee('Compliance decisions, flags, case history and reports are not recorded here.')
            ->assertSee('Document uploads and downloads, sanctions and compliance notifications are unavailable here.')
            ->assertSee('No Seller is verified or flagged, no decision is saved, and no account or listing status is changed from this page.')
            ->assertSee('Use Account Registrations in the Admin navigation')
            ->assertSee('Account approval and email verification do not establish legal compliance.')
            ->assertSee('This page does not create another approval status.')
            ->assertSee('Product Compliance is a separate product review workflow.');

        // Only informational content belongs here; existing shell navigation/logout retain their own contracts.
        $content = $xpath->query('//*[@id="adminSellerCompliance"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminSellerCompliance"]//*[self::form or self::button or self::input or self::select or self::textarea or self::table or self::script or self::a]')->length);
        foreach (['TechHub PH', "Aling Nena's Store", "Jomar's Repair Shop", 'Cavite Home Essentials',
            'Better Bites Bakery', 'QuickFix Auto Parts', 'Lush Garden Supplies', 'Metro Print Solutions',
            'DTI Business Permit', 'BIR Form 2303', 'Barangay Business Permit', 'Barangay Clearance',
            'Verified by admin', 'Incomplete Documents', 'Expired Permit', 'Reminder sent for missing documents',
            'Confirm Verify', 'Confirm Flag', 'unlock full store access', 'listings will be suspended',
            '/storage/', 'valid_ids/', 'business_permits/'] as $falseFact) {
            $this->assertStringNotContainsString($falseFact, $content->item(0)->textContent);
        }
        $response->assertDontSee('sellersData')->assertDontSee('confirmProceedBtn')->assertDontSee('modalDocuments');
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('admin.registrations').'"]')->length);
    }

    // Guests remain outside the authenticated Admin destination.
    public function test_guest_cannot_open_seller_compliance(): void
    {
        $this->get('/admin/seller-compliance')->assertRedirect(route('login'));
    }

    // Role and approval denials are the existing Admin authorization contract.
    public function test_wrong_roles_and_unapproved_admin_cannot_open_seller_compliance(): void
    {
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/seller-compliance')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/seller-compliance')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
        }
    }

    // Rider authentication uses the real separate guard and grants no marketplace Admin access.
    public function test_rider_guard_cannot_open_seller_compliance(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/seller-compliance')->assertRedirect(route('login'));
    }

    // Unsupported compliance and former suggested action URLs must fail without changing account state.
    public function test_unsupported_compliance_operations_cannot_succeed(): void
    {
        $this->actingAs($this->user());
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/admin/seller-compliance')->assertStatus(405)->assertSessionMissing('status');
            foreach (['verify', 'flag', 'review', 'documents', 'approve', 'reject', 'request-documents',
                'mark-compliant', 'mark-non-compliant', 'suspend', 'resolve'] as $action) {
                $this->{$method}('/admin/seller-compliance/'.$action)->assertNotFound()->assertSessionMissing('status');
            }
            foreach (['verify', 'flag'] as $action) {
                $this->{$method}('/admin/sellers/1/'.$action)->assertNotFound()->assertSessionMissing('status');
            }
        }
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    // Legacy filters cannot revive fixture documents, history or compliance decisions.
    public function test_old_filters_keep_seller_compliance_informational(): void
    {
        $this->actingAs($this->user());
        foreach (['pending', 'verified', 'flagged'] as $filter) {
            $this->get('/admin/seller-compliance?filter='.$filter.'&search=TechHub&sort=oldest&verify=1')
                ->assertOk()->assertSee('A separate Seller legal/compliance workflow is not available.')
                ->assertDontSee('TechHub PH')->assertDontSee('sellersData')->assertDontSee('confirmProceedBtn');
        }
    }

    // Local TEST renders the same limits, while non-local registration exposes no TEST route.
    public function test_local_preview_is_truthful_and_absent_outside_local(): void
    {
        $this->get('/__dev/preview/admin/compliance')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/admin/compliance')->assertOk()
            ->assertSee('A separate Seller legal/compliance workflow is not available.')
            ->assertSee('Compliance decisions, flags, case history and reports are not recorded here.')
            ->assertSee('This page does not create another approval status.')
            ->assertDontSee('TechHub PH')->assertDontSee('sellersData')->assertDontSee('confirmProceedBtn');
    }

    // Authentication fixtures establish access only; no legal-compliance records are invented.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@compliance.test',
            'password' => 'ComplianceFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
