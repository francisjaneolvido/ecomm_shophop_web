<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Commission closure is an approved-Admin informational HTTP contract, with no accounting fixtures.
class AdminCommissionJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fail closed before destructive setup if PHPUnit loses its isolated database configuration.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // An Admin must see explicit unavailable behavior without fabricated figures or financial action affordances.
    public function test_approved_admin_can_open_a_truthful_commission_surface_without_accounting_tables(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/commission');
        $response->assertOk()
            ->assertSee('Commission accounting is not yet available.')
            ->assertSee('This page does not represent live commission, payout or accounting records.')
            ->assertSee('PDF and report export are unavailable.')
            ->assertSee('Marking commissions as paid or resolved is unavailable.')
            ->assertDontSee('Download Report (PDF)')
            ->assertDontSee('Mark as Paid')
            ->assertDontSee('Confirm Paid')
            ->assertDontSee('Confirm Resolved')
            ->assertDontSee('TechHub PH')
            ->assertDontSee('PYT-000891')
            ->assertDontSee('Commission (10%)')
            ->assertDontSee('admin.commissions.export-pdf');

        // Inspect only the journey content, excluding legitimate shared-shell forms and navigation.
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="adminCommission"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminCommission"]//form | //*[@id="adminCommission"]//button | //*[@id="adminCommission"]//input | //*[@id="adminCommission"]//table | //*[@id="adminCommission"]//script | //*[@id="adminCommission"]//a')->length);
    }

    // The existing guest gate must still require normal authentication before exposing the Admin page.
    public function test_guest_cannot_open_commission(): void
    {
        $this->get('/admin/commission')->assertRedirect(route('login'));
    }

    // Role and approval denials must survive replacement of the financial preview with informational content.
    public function test_non_admin_and_unapproved_admin_cannot_open_commission(): void
    {
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/commission')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/commission')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
        }
    }

    // A pre-authenticated Rider guard is separate from the marketplace web identity required by Admin routes.
    public function test_rider_guard_does_not_grant_admin_commission_access(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/commission')->assertRedirect(route('login'));
    }

    // No compatibility export or financial write endpoint may substitute a fabricated successful operation.
    public function test_unsupported_exports_and_financial_mutations_cannot_claim_completion(): void
    {
        $this->actingAs($this->user());
        $this->get('/admin/commissions/export-pdf')->assertNotFound();
        $this->post('/admin/commission', ['status' => 'paid'])->assertStatus(405)->assertSessionMissing('status');
        foreach (['mark-paid', 'resolve'] as $action) {
            $this->post('/admin/commissions/1/'.$action, ['payout_ref' => 'UNSUPPORTED'])
                ->assertNotFound()->assertSessionMissing('status');
        }
    }

    // Old preview query parameters cannot restore a fixture ledger, a financial result or an export response.
    public function test_old_filters_and_action_parameters_keep_the_surface_informational(): void
    {
        $this->actingAs($this->user())->get('/admin/commission?filter=paid&sort=highest&search=TechHub&export=pdf&status=resolved')
            ->assertOk()->assertSee('Commission accounting is not yet available.')
            ->assertDontSee('TechHub PH')->assertDontSee('Confirm Resolved')
            ->assertDontSee('report generated')->assertDontSee('PDF exported successfully');
    }

    // Users alone establish the real access identity; no Commission, Order or settlement schema is fabricated.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@commission.test',
            'password' => 'CommissionFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
