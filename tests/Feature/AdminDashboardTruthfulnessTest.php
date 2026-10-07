<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Dashboard disclosure is tested through approved Admin HTTP rendering, without invented financial domains.
class AdminDashboardTruthfulnessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fail closed before destructive fixture setup if the isolated PHPUnit connection is lost.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            '2026_08_29_000002_create_sellers_table.php',
            '2026_08_29_000003_create_logistics_partners_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // No dispute or Commission persistence exists; every dashboard entry must disclose availability instead of live state.
    public function test_approved_admin_sees_availability_instead_of_fabricated_business_state(): void
    {
        $response = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $content = $xpath->query('//*[@id="adminDashboard"]')->item(0);
        $this->assertNotNull($content);
        $text = preg_replace('/\s+/', ' ', $content->textContent);
        $unsupported = array_values(array_filter([
            'No urgent cases', 'Needs resolution', '0 open', 'Open Complaints & Disputes',
            'Commission This Month', 'Platform earnings', 'platform earnings', '10%',
            'Resolve Disputes', 'All clear',
        ], fn ($claim) => str_contains($text, $claim)));
        $this->assertSame([], $unsupported, 'Unsupported live dashboard claims must be absent.');
        $response->assertSee('Dispute workflow unavailable')->assertSee('Commission accounting unavailable');
        foreach (['admin.disputes', 'admin.commission'] as $route) {
            $links = $xpath->query('//*[@id="adminDashboard"]//a[@href="'.route($route).'"]');
            $this->assertGreaterThan(0, $links->length);
            foreach ($links as $link) {
                $this->assertDoesNotMatchRegularExpression('/(?:₱|\b\d+(?:\.\d+)?\s*(?:%|open)\b)/u', $link->textContent);
            }
        }
    }

    // Persisted registration and account counts, recent rows and their real destinations must survive disclosure changes.
    public function test_real_admin_metrics_and_existing_destinations_remain_available(): void
    {
        $admin = $this->user();
        $pending = $this->user(['account_type' => 'buyer', 'status' => 'pending']);
        $this->user(['account_type' => 'seller', 'status' => 'pending']);
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->user(['account_type' => $role]);
        }

        foreach ([[2, 3], [1, 4]] as $iteration => [$pendingCount, $activeCount]) {
            // A changed persisted approval must alter the visible counts, without manufacturing dispute or earnings state.
            if ($iteration === 1) {
                $pending->update(['status' => 'approved']);
            }
            $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
                ->assertSee($pending->email)->assertSee('Recent Registrations')->assertSee('Registration Snapshot');
            $document = new \DOMDocument();
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            foreach (['Pending Registrations' => $pendingCount, 'Active User Accounts' => $activeCount] as $label => $count) {
                $nodes = $xpath->query('//*[@id="adminDashboard"]//p[normalize-space()="'.$label.'"]/preceding-sibling::p[1]');
                $this->assertSame(1, $nodes->length);
                $this->assertSame((string) $count, trim($nodes->item(0)->textContent));
            }
        }

        // The dashboard's existing destinations remain real GET routes; no compatibility route or backend is added.
        foreach (['admin.registrations', 'admin.users', 'admin.disputes', 'admin.commission',
            'admin.reports', 'admin.compliance', 'admin.chat', 'admin.settings'] as $route) {
            $this->assertGreaterThan(0, $xpath->query('//*[@id="adminDashboard"]//a[@href="'.route($route).'"]')->length);
            $this->get(route($route))->assertOk();
        }
    }

    // Empty registrations establish only a registration state, never absence of urgent disputes across the platform.
    public function test_empty_registration_queue_is_explicitly_scoped(): void
    {
        $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('No pending registrations')->assertDontSee('All clear')->assertDontSee('No urgent cases');
    }

    // Both dashboards share the canonical marketplace guard; wrong roles and unapproved accounts cannot bypass it.
    public function test_dashboard_guest_role_approval_and_email_boundaries_are_preserved(): void
    {
        foreach (['admin', 'seller'] as $role) {
            $url = route($role.'.dashboard');
            $this->get($url)->assertRedirect(route('login'));
            foreach (array_diff(['buyer', 'seller', 'admin', 'logistics'], [$role]) as $wrongRole) {
                $this->actingAs($this->user(['account_type' => $wrongRole]))->get($url)->assertForbidden();
            }
            foreach (['pending', 'rejected', 'suspended'] as $status) {
                $this->actingAs($this->user(['account_type' => $role, 'status' => $status]))->get($url)
                    ->assertRedirect(route('home'))->assertSessionHas('login_notice');
                $this->assertGuest('web');
            }
        }
        // Seller email verification remains required before dashboard/profile reads; Admin has no equivalent email gate.
        $this->actingAs($this->user(['account_type' => 'seller', 'email_verified_at' => null]))
            ->get(route('seller.dashboard'))->assertRedirect(route('seller.verify-email.show'))
            ->assertSessionHas('seller_verification_user_id');
        $this->assertGuest('web');
        $this->actingAs($this->user(['email_verified_at' => null]))->get(route('admin.dashboard'))->assertOk();
    }

    // An actual authenticated Rider model on its separate guard cannot authorize either marketplace dashboard.
    public function test_rider_guard_cannot_open_either_dashboard(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        foreach (['admin', 'seller'] as $role) {
            $this->get(route($role.'.dashboard'))->assertRedirect(route('login'));
        }
    }

    // Users establish real access only; no dispute, Commission or settlement fixtures are needed.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace([
            'email' => uniqid('dashboard').'@example.test', 'password' => 'DashboardFixture123!',
            'account_type' => 'admin', 'status' => 'approved', 'email_verified_at' => now(),
        ], $overrides))->save();

        return $user;
    }
}
