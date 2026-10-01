<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Chat availability is an HTTP contract; account records do not establish messaging infrastructure.
class AdminChatJourneyTest extends TestCase
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

    // A protected destination must not present fabricated conversations as live communication.
    public function test_approved_admin_sees_truthful_unavailable_messaging(): void
    {
        $response = $this->actingAs($this->user())->get('/admin/chat')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[@data-conversation-trigger]')->length,
            'Admin Chat must not present fixture conversations as live records.');
        $response->assertSee('Live messaging is not available.')
            ->assertSee('No live conversations or messages are displayed.')
            ->assertSee('Messages cannot be sent or delivered from this page.')
            ->assertSee('Chat reports and user blocking are unavailable.')
            ->assertSee('Broadcast delivery and scheduled messages are unavailable.')
            ->assertSee('Attachments cannot be uploaded or sent.')
            ->assertSee('Account moderation remains a separate Admin workflow.');

        // Scope unsupported affordances to Chat so legitimate shared navigation and logout survive.
        $content = $xpath->query('//*[@id="adminChat"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="adminChat"]//*[self::form or self::button or self::input or self::select or self::textarea or self::table or self::script or self::a]')->length);
        foreach (['TechHub PH', "Aling Nena's Store", 'Maricel Santos', 'Maria Reyes',
            'Grace Fernandez', "Jomar's Repair Shop", 'Na-submit na ang report',
            'ay na-block na', 'Na-send ang broadcast', 'Naka-schedule na ang broadcast'] as $falseClaim) {
            $this->assertStringNotContainsString($falseClaim, $content->item(0)->textContent);
        }
    }

    // Guest access must retain the existing web authentication boundary.
    public function test_guest_cannot_open_chat(): void
    {
        $this->get('/admin/chat')->assertRedirect(route('login'));
    }

    // Marketplace roles and every unapproved Admin status retain their existing denials.
    public function test_wrong_roles_and_unapproved_admin_cannot_open_chat(): void
    {
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/admin/chat')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/admin/chat')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
        }
    }

    // Rider authentication is a separate guard, not a normal User role or Admin authorization.
    public function test_rider_guard_cannot_open_chat(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/admin/chat')->assertRedirect(route('login'));
    }

    // Missing communication contracts must fail instead of accepting pretend success writes.
    public function test_unsupported_chat_operations_cannot_succeed(): void
    {
        $this->actingAs($this->user());
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/admin/chat')->assertStatus(405)->assertSessionMissing('status');
            foreach (['send', 'messages', 'report', 'block', 'broadcast', 'schedule', 'archive', 'attachments',
                'conversations/1/messages', 'conversations/1/report', 'conversations/1/block'] as $action) {
                $this->{$method}('/admin/chat/'.$action)->assertNotFound()->assertSessionMissing('status');
            }
        }
    }

    // Old UI parameters cannot revive a fixture conversation or apparent communication action.
    public function test_old_chat_parameters_keep_the_destination_informational(): void
    {
        $this->actingAs($this->user())->get('/admin/chat?conversation=1&send=1&broadcast=1&schedule=1')
            ->assertOk()->assertSee('Live messaging is not available.')
            ->assertDontSee('TechHub PH')->assertDontSee('messageInput');
    }

    // Local TEST shares the truthful view and never grants a production preview route.
    public function test_local_preview_is_truthful_and_absent_from_production(): void
    {
        $this->get('/__dev/preview/admin/chat')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/admin/chat')->assertOk()
            ->assertSee('Live messaging is not available.')
            ->assertSee('Chat reports and user blocking are unavailable.')
            ->assertSee('Broadcast delivery and scheduled messages are unavailable.')
            ->assertSee('Attachments cannot be uploaded or sent.')
            ->assertDontSee('TechHub PH')->assertDontSee('messageInput');
    }

    // These fixtures prove authorization only and never invent communication records.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('admin').'@chat.test',
            'password' => 'ChatFixture123!', 'account_type' => 'admin',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
