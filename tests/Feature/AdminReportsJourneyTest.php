<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Reports is an approved-Admin informational destination, without a report or accounting schema.
class AdminReportsJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fail closed before destructive setup if the isolated PHPUnit database configuration is lost.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // Rendering Reports must not present fixture records as generated business history.
    public function test_approved_admin_sees_no_fabricated_report_history(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/reports')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[@data-report-row]')->length, 'Reports must not show fabricated generated history.');
        $response->assertSee('Business report generation is not available.')
            ->assertSee('Report history is not persisted.')
            ->assertSee('PDF and CSV export are not available.')
            ->assertSee('No reports are generated, saved or downloaded from this page.')
            ->assertSee('Registration moderation remains a separate review workflow.');

        // Scope action/metric assertions to Reports, preserving unrelated shared-shell navigation and forms.
        $content = $xpath->query('//*[@id="adminReports"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminReports"]//*[self::form or self::button or self::input or self::select or self::textarea or self::table or self::script or self::a]')->length);
        foreach (['Monthly Sales Summary', 'Commission Breakdown', 'Seller Performance', 'Disputes Log',
            'Generated just now', 'Generated 3 hours ago', 'Recent Reports', '1.2 MB', '₱'] as $fixture) {
            $this->assertStringNotContainsString($fixture, $content->item(0)->textContent);
        }
        foreach (['generateReportBtn', 'report-download-btn', 'reportModalOverlay', 'reportsData',
            'new Blob', 'createObjectURL', 'Math.random'] as $fakeBehavior) {
            $response->assertDontSee($fakeBehavior);
        }
    }

    // Guests must authenticate through the existing route boundary.
    public function test_guest_cannot_open_reports(): void
    {
        $this->get('/admin/reports')->assertRedirect(route('login'));
    }

    // Marketplace roles and unapproved Admin accounts retain their real access denials.
    public function test_wrong_roles_and_unapproved_admin_cannot_open_reports(): void
    {
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/reports')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/reports')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
        }
    }

    // Rider identity uses a separate guard and cannot authorize an Admin destination.
    public function test_rider_guard_does_not_grant_reports_access(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/reports')->assertRedirect(route('login'));
    }

    // Unsupported writes and export URLs remain absent instead of manufacturing completion responses.
    public function test_report_writes_generation_and_exports_cannot_succeed(): void
    {
        $this->actingAs($this->user());
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/admin/reports', ['type' => 'sales_summary'])->assertStatus(405)->assertSessionMissing('status');
        }
        $this->post('/admin/reports/generate')->assertNotFound()->assertSessionMissing('status');
        foreach (['/admin/reports/download', '/admin/reports/1/download', '/admin/reports/export',
            '/admin/reports/export-pdf', '/admin/reports/export-csv'] as $path) {
            $this->get($path)->assertNotFound();
            $this->post($path)->assertNotFound()->assertSessionMissing('status');
        }
    }

    // Legacy preview parameters cannot revive generation, fixture history or exports.
    public function test_old_report_parameters_keep_the_page_informational(): void
    {
        $this->actingAs($this->user())->get('/admin/reports?type=sales_summary&generate=1&download=1&format=PDF')
            ->assertOk()->assertSee('Business report generation is not available.')
            ->assertDontSee('Generated just now')->assertDontSee('Monthly Sales Summary')->assertDontSee('generateReportBtn');
    }

    // Local TEST renders the same truthful view and remains absent from production routing.
    public function test_local_preview_is_truthful_and_not_a_production_route(): void
    {
        $this->get('/__dev/preview/admin/reports')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/admin/reports')->assertOk()
            ->assertSee('Business report generation is not available.')
            ->assertSee('Report history is not persisted.')
            ->assertSee('PDF and CSV export are not available.')
            ->assertDontSee('Monthly Sales Summary')->assertDontSee('generateReportBtn')->assertDontSee('report-download-btn');
    }

    // Authentication fixtures establish access only; no report records or business amounts are invented.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@reports.test',
            'password' => 'ReportsFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
