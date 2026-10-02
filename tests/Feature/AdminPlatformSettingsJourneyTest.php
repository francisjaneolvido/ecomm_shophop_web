<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// The existing settings destination is informational until a real authorized configuration contract exists.
class AdminPlatformSettingsJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Destructive setup must fail closed if PHPUnit loses its isolated in-memory database.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // An Admin cannot be offered a Save operation that has no configuration persistence contract.
    public function test_approved_admin_is_not_offered_unsupported_configuration_saving(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/settings')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//main//button[contains(., "Save Changes")]')->length,
            'Platform Settings must not advertise saving without an authorized persistence contract.');
    }

    // The content must expose only availability, with no fabricated platform values or dead settings navigation.
    public function test_settings_destination_is_informational_without_configuration_tables(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/settings')->assertOk()
            ->assertSee('Platform configuration changes are unavailable here.')
            ->assertSee('No live platform configuration is displayed or saved on this page.')
            ->assertSee('Maintenance mode cannot be changed here.')
            ->assertSee('Commission rates and payout schedules cannot be configured here.')
            ->assertSee('Platform notification preferences and security configuration are unavailable.')
            ->assertDontSee('Save Changes')->assertDontSee('Cancel')->assertDontSee('admin@shophop.com');
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="adminPlatformSettings"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminPlatformSettings"]//*[self::form or self::input or self::textarea or self::select or self::button or self::a or self::script]')->length);
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('admin.settings').'"]')->length);
    }

    // Native authentication, role and approval checks remain authoritative for this protected destination.
    public function test_guest_wrong_roles_and_unapproved_admins_cannot_open_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect(route('login'));
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/settings')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/settings')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
    }

    // The actual separate Rider guard cannot substitute for an approved Admin web identity.
    public function test_rider_guard_does_not_authorize_admin_settings(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/settings')->assertRedirect(route('login'));
    }

    // Neither the real settings URL nor hypothetical legacy paths may claim a successful unsupported write.
    public function test_unsupported_configuration_writes_fail(): void
    {
        $this->actingAs($this->user());
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/admin/settings', ['maintenance' => true])->assertStatus(405)->assertSessionMissing('status');
            $this->{$method}('/admin/platform-settings', ['maintenance' => true])->assertNotFound()->assertSessionMissing('status');
        }
        foreach (['/admin/settings', '/admin/platform-settings'] as $base) {
            foreach (['post', 'patch'] as $method) {
                $this->{$method}($base.'/save', ['commission' => 99])->assertNotFound()->assertSessionMissing('status');
            }
            foreach (['maintenance', 'commission', 'payout'] as $action) {
                $this->patch($base.'/'.$action, ['value' => 'unsupported'])->assertNotFound()->assertSessionMissing('status');
            }
        }
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    // Local TEST shares the same limitations, stays absent outside local, and grants no protected Admin access.
    public function test_local_preview_is_truthful_and_does_not_authorize_admin_access(): void
    {
        $this->get('/__dev/preview/admin/settings')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/admin/settings')->assertOk()
            ->assertSee('Platform configuration changes are unavailable here.')
            ->assertDontSee('Save Changes')->assertDontSee('value="admin@shophop.com"', false);
        $this->assertGuest('web');
        $this->get('/admin/settings')->assertRedirect(route('login'));
    }

    // Access fixtures represent native Admin identities only, never persisted platform configuration.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@platform-settings.test',
            'password' => 'SettingsFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
