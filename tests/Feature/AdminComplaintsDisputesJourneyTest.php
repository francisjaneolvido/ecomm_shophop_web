<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Dispute availability is an HTTP contract; Orders and account records do not establish case handling.
class AdminComplaintsDisputesJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fail closed before destructive fixture setup if PHPUnit loses its isolated database.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // An approved Admin must not be offered fabricated cases as live dispute records.
    public function test_approved_admin_sees_truthful_unavailable_disputes(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/complaints-disputes')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " dispute-view-btn ")]')->length,
            'Admin Complaints / Disputes must not present fixture cases as live records.');
        $response->assertSee('Complaint and dispute case management is not available.')
            ->assertSee('No live complaint or dispute cases are displayed here.')
            ->assertSee('Case evidence is not persisted here; evidence uploads and downloads are unavailable.')
            ->assertSee('Resolution recording and resolution history are unavailable.')
            ->assertSee('Mediation, case assignment and escalation are unavailable.')
            ->assertSee('Refunds, payment reversals and financial settlements are unavailable here.')
            ->assertSee('Dispute penalties and account actions are unavailable here.')
            ->assertSee('No decision is saved, no case is resolved or closed, and no party is notified from this page.')
            ->assertSee('Nothing on this page changes Orders, payments or account status.');

        // Keep legitimate shell navigation/logout while rejecting every unsupported dispute affordance.
        $content = $xpath->query('//*[@id="adminDisputes"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminDisputes"]//*[self::form or self::button or self::input or self::select or self::textarea or self::table or self::script or self::a]')->length);
        foreach (['Maria Reyes', 'Jonas Dela Cruz', 'Carla Mendoza', 'Ella Cruz', 'Paolo Villar',
            'Sheena Ong', 'Miguel Torres', 'Karen Bautista', 'Photo of item received',
            'Proof of payment', 'Screenshot of receipt', 'Photo of size tag',
            'Resolution recorded', 'Refunded Buyer', 'Replaced / Reshipped Item',
            'Favored Seller (Claim Denied)', 'This case has already been resolved.'] as $falseClaim) {
            $this->assertStringNotContainsString($falseClaim, $content->item(0)->textContent);
        }
        $response->assertDontSee('mediateSubmitBtn')->assertDontSee('disputesData')
            ->assertDontSee('mediateRefundAmount')->assertDontSee('toastNotification');
    }

    // Guest access must retain the existing web authentication boundary.
    public function test_guest_cannot_open_disputes(): void
    {
        $this->get('/admin/complaints-disputes')->assertRedirect(route('login'));
    }

    // Marketplace roles and every unapproved Admin status retain their existing denials.
    public function test_wrong_roles_and_unapproved_admin_cannot_open_disputes(): void
    {
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/complaints-disputes')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/complaints-disputes')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
        }
    }

    // Rider authentication is a separate guard, not a normal User role or Admin authorization.
    public function test_rider_guard_cannot_open_disputes(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/complaints-disputes')->assertRedirect(route('login'));
    }

    // Missing dispute contracts must fail instead of accepting pretend success writes.
    public function test_unsupported_disputes_operations_cannot_succeed(): void
    {
        $this->actingAs($this->user());
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/admin/complaints-disputes')->assertStatus(405)->assertSessionMissing('status');
            foreach (['resolve', 'resolution', 'refund', 'close', 'evidence', 'escalate', 'mediate', 'assign', 'penalty'] as $action) {
                $this->{$method}('/admin/complaints-disputes/'.$action)->assertNotFound()->assertSessionMissing('status');
            }
            // The previous UI's suggested mediation URL is absent too; no compatibility success route is added.
            $this->{$method}('/admin/disputes/1/mediate')->assertNotFound()->assertSessionMissing('status');
        }
    }

    // Old fixture filters cannot restore records or a browser-only resolution workflow.
    public function test_old_dispute_parameters_keep_the_destination_informational(): void
    {
        $this->actingAs($this->user());
        foreach (['open', 'mediation', 'resolved'] as $filter) {
            $this->get('/admin/complaints-disputes?filter='.$filter.'&search=TechHub&sort=oldest&resolve=1')
                ->assertOk()->assertSee('Complaint and dispute case management is not available.')
                ->assertDontSee('TechHub PH')->assertDontSee('mediateSubmitBtn')->assertDontSee('disputesData');
        }
    }

    // Local TEST shares the truthful view and never grants a production preview route.
    public function test_local_preview_is_truthful_and_absent_from_production(): void
    {
        $this->get('/__dev/preview/admin/disputes')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/admin/disputes')->assertOk()
            ->assertSee('Complaint and dispute case management is not available.')
            ->assertSee('Resolution recording and resolution history are unavailable.')
            ->assertSee('Refunds, payment reversals and financial settlements are unavailable here.')
            ->assertSee('Case evidence is not persisted here; evidence uploads and downloads are unavailable.')
            ->assertDontSee('TechHub PH')->assertDontSee('mediateSubmitBtn');
    }

    // These fixtures prove authorization only and never invent dispute records.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@disputes.test',
            'password' => 'DisputesFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
