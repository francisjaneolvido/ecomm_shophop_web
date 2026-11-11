<?php

namespace Tests\Feature;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\BuyerVerificationCodeNotification;
use App\Notifications\SellerVerificationCodeNotification;
use App\Services\VerificationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SignupVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('These tests require an isolated in-memory database.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->freezeTime();
    }

    private function pending(string $role): User
    {
        $user = User::create(['email' => $role.'@example.com', 'password' => Hash::make('Test123!'),
            'account_type' => $role, 'status' => 'pending']);
        $this->withSession([$role.'_verification_user_id' => $user->id]);
        return $user;
    }

    private function seedCode(User $user, array $overrides = []): EmailVerificationCode
    {
        return EmailVerificationCode::create(array_merge(['user_id' => $user->id,
            'code_hash' => Hash::make('123456'), 'attempts' => 0,
            'expires_at' => now()->addMinutes(5), 'last_sent_at' => now()->subSeconds(61)], $overrides));
    }

    public function test_resend_rotates_code_and_verifies_without_duplicate_accounts(): void
    {
        foreach (['buyer' => BuyerVerificationCodeNotification::class, 'seller' => SellerVerificationCodeNotification::class] as $role => $notification) {
            $user = $this->pending($role);
            $old = $this->seedCode($user)->code_hash;
            $this->postJson(route($role.'.verify-email.resend'))->assertOk()->assertJsonPath('retry_after', 60);
            $record = EmailVerificationCode::where('user_id', $user->id)->firstOrFail();
            $this->assertNotSame($old, $record->code_hash);
            $sent = Notification::sent($user, $notification)->first();
            $code = collect($sent->toMail($user)->introLines)->first(fn ($line) => preg_match('/^\\d{6}$/', $line));
            $this->assertNotNull($code);
            $this->assertTrue(Hash::check($code, $record->code_hash));
            $this->assertSame(0, $record->attempts);
            $this->assertTrue($record->expires_at->betweenIncluded(now()->addMinutes(4), now()->addMinutes(5)));
            $this->post(route($role.'.verify-email.verify'), ['code' => $code])->assertRedirect('/');
            $this->assertNotNull($user->fresh()->email_verified_at);
            $this->assertSame('pending', $user->fresh()->status);
            $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
        }
        $this->assertDatabaseCount('users', 2);
    }

    public function test_incorrect_expired_and_exhausted_codes_are_rejected(): void
    {
        foreach (['buyer', 'seller'] as $role) {
            $user = $this->pending($role);
            $record = $this->seedCode($user);
            $this->from(route($role.'.verify-email.show'))->post(route($role.'.verify-email.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
            $this->assertSame(1, $record->fresh()->attempts);
            $record->update(['expires_at' => now()->subSecond()]);
            $this->post(route($role.'.verify-email.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
            $record->update(['expires_at' => now()->addMinute(), 'attempts' => 5]);
            $this->post(route($role.'.verify-email.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
            $this->assertNull($user->fresh()->email_verified_at);
        }
    }

    public function test_cooldown_survives_reload_and_does_not_send_twice(): void
    {
        foreach (['buyer', 'seller'] as $role) {
            $user = $this->pending($role);
            $this->seedCode($user, ['last_sent_at' => now()->subSeconds(45)]);
            $this->get(route($role.'.verify-email.show'))->assertOk()->assertViewHas('retryAfter', 15);
            $this->postJson(route($role.'.verify-email.resend'))->assertStatus(429)->assertJsonPath('retry_after', 15);
        }
        Notification::assertNothingSent();
    }

    public function test_invalid_session_and_verified_or_wrong_role_accounts_cannot_resend(): void
    {
        $this->postJson(route('buyer.verify-email.resend'))->assertStatus(419);
        $user = $this->pending('buyer');
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->postJson(route('buyer.verify-email.resend'))->assertStatus(419);
        $this->withSession(['seller_verification_user_id' => $user->id])->postJson(route('seller.verify-email.resend'))->assertStatus(419);
        Notification::assertNothingSent();
    }

    public function test_failed_delivery_preserves_previous_code_and_allows_retry(): void
    {
        $user = $this->pending('buyer');
        $old = $this->seedCode($user)->code_hash;
        $this->mock(VerificationMail::class)->shouldReceive('send')->once()->andThrow(new \RuntimeException('Transport unavailable'));
        $this->postJson(route('buyer.verify-email.resend'))->assertStatus(503);
        $this->assertSame($old, $user->emailVerificationCode()->firstOrFail()->code_hash);
        $this->assertSame(0, app(\App\Services\AccountVerificationCodes::class)->remaining($user));
    }

    public function test_account_send_limit_survives_cooldown_and_ip_changes(): void
    {
        $user = $this->pending('buyer');
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('buyer.verify-email.resend'))->assertOk();
            $this->travel(61)->seconds();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.25'])->postJson(route('buyer.verify-email.resend'))->assertStatus(429);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('email_verification_codes', 1);
    }

    public function test_log_mailer_is_reported_as_failure_and_does_not_log_codes(): void
    {
        $this->pending('buyer');
        config(['mail.default' => 'log']);
        $this->postJson(route('buyer.verify-email.resend'))->assertStatus(503)->assertJsonMissing(['retry_after' => 60]);
        $this->assertDatabaseCount('email_verification_codes', 0);
        Notification::assertNothingSent();
    }

    public function test_silent_log_fallback_is_rejected(): void
    {
        config(['mail.default' => 'failover']);
        $this->expectException(\RuntimeException::class);
        app(VerificationMail::class)->ensureDeliverable();
    }

    public function test_notification_renders_and_reaches_in_memory_mail_transport(): void
    {
        Notification::swap(new \Illuminate\Notifications\ChannelManager($this->app));
        app()->forgetInstance('mail.manager');
        $user = $this->pending('buyer');
        app(VerificationMail::class)->send($user, new BuyerVerificationCodeNotification('123456'));
        $this->assertCount(1, Mail::mailer('array')->getSymfonyTransport()->messages());
    }

    public function test_logistics_resend_cooldown_and_mail_failure_preserve_session_code(): void
    {
        $state = ['email' => 'logistics@example.com', 'code_hash' => Hash::make('123456'),
            'last_sent_at' => now()->subSeconds(45)->timestamp, 'expires_at' => now()->addMinutes(5)->timestamp,
            'attempts' => 0, 'verified' => false];
        $this->withSession(['logistics_email_verification' => $state]);
        $this->postJson(route('logistics.verification.send'), ['email' => $state['email'], 'resend' => true])
            ->assertStatus(429)->assertJsonPath('retry_after', 15);
        $this->travel(16)->seconds();
        config(['mail.default' => 'log']);
        $this->postJson(route('logistics.verification.send'), ['email' => $state['email'], 'resend' => true])->assertStatus(500)
            ->assertSessionHas('logistics_email_verification.code_hash', $state['code_hash']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_old_code_is_rejected_after_resend(): void
    {
        foreach (['buyer', 'seller'] as $role) {
            $user = $this->pending($role);
            $this->seedCode($user);
            $this->postJson(route($role.'.verify-email.resend'))->assertOk();
            $this->post(route($role.'.verify-email.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
            $this->assertNull($user->fresh()->email_verified_at);
        }
    }

    public function test_missing_code_and_invalid_input_are_rejected(): void
    {
        $this->pending('buyer');
        $this->post(route('buyer.verify-email.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
        $this->postJson(route('buyer.verify-email.verify'), ['code' => 'abc'])->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_logistics_rotates_code_and_verifies_without_creating_account(): void
    {
        $input = ['email' => 'logistics@example.com'];
        $this->postJson(route('logistics.verification.send'), $input)->assertOk()->assertJsonPath('retry_after', 60);
        $old = session('logistics_email_verification.code_hash');
        $this->travel(61)->seconds();
        $this->postJson(route('logistics.verification.send'), $input + ['resend' => true])->assertOk();
        $this->assertNotSame($old, session('logistics_email_verification.code_hash'));
        $notification = Notification::sent(new \Illuminate\Notifications\AnonymousNotifiable, \App\Notifications\LogisticsVerificationCodeNotification::class)->last();
        $this->assertNotNull($notification);
        // Read the latest recorded notification without exposing the code in output.
        $mail = $notification->toMail(new \Illuminate\Notifications\AnonymousNotifiable);
        $code = collect($mail->introLines)->first(fn ($line) => preg_match('/^\\d{6}$/', $line));
        $this->postJson(route('logistics.verification.verify'), $input + ['code' => $code])->assertOk();
        $this->assertTrue(session('logistics_email_verification.verified'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_logistics_resend_requires_a_pending_session(): void
    {
        $this->postJson(route('logistics.verification.send'), ['email' => 'logistics@example.com', 'resend' => true])->assertStatus(419);
        Notification::assertNothingSent();
    }

    public function test_signup_views_render_with_accessible_resend_and_all_registration_variants(): void
    {
        $this->pending('buyer');
        $response = $this->get(route('buyer.verify-email.show'))->assertOk()
            ->assertSee('buyer-registration-dialog', false)->assertSee('seller-registration-dialog', false)
            ->assertSee('aria-live', false);
        $logistics = $this->get(route('logistics.register'))->assertOk()->assertSee('data-wizard', false);
        $this->pending('seller');
        $sellerResponse = $this->get(route('seller.verify-email.show'))->assertOk();
        if ($directory = getenv('SHOPHOP_UI_FIXTURE_DIR')) {
            if (! is_dir($directory)) mkdir($directory, 0777, true);
            file_put_contents($directory.'/signup.html', $response->getContent());
            file_put_contents($directory.'/logistics.html', $logistics->getContent());
            file_put_contents($directory.'/seller.html', $sellerResponse->getContent());
        }
    }


    public function test_login_recovers_pending_verification_and_preserves_approval_rules(): void
    {
        foreach (['buyer', 'seller'] as $role) {
            $user = $this->pending($role);
            $credentials = ['email' => $user->email, 'password' => 'Test123!'];
            $this->post(route('login.store'), $credentials)->assertRedirect(route($role.'.verify-email.show'))
                ->assertSessionHas($role.'_verification_user_id', $user->id);
            $this->assertGuest();
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->post(route('login.store'), $credentials)->assertRedirect(route('home'))
                ->assertSessionHas('login_notice.title', 'Approval Pending');
            $this->assertGuest();
            $user->update(['status' => 'approved']);
            $this->post(route('login.store'), $credentials)->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }
    }


    public function test_failed_initial_registration_mail_can_be_retried_without_registering_again(): void
    {
        \Illuminate\Support\Facades\Storage::fake('registration_documents');
        foreach (['buyer', 'seller'] as $index => $role) {
            config(['mail.default' => 'log']);
            $input = [
                'first_name' => 'Maria', 'last_name' => 'Santos', 'sex' => 'Female',
                'email' => $role.'-registration@example.com', 'contact_no' => '0917123456'.$index,
                'birthday' => now()->subYears(20)->toDateString(),
                'province_code' => '040000000', 'province_name' => 'Laguna',
                'municipality_code' => '043400000', 'municipality_name' => 'San Pablo City',
                'barangay_code' => '043401001', 'barangay_name' => 'Bagong Bayan', 'street_address' => '123 Main Street',
                'password' => 'Registration@2026', 'password_confirmation' => 'Registration@2026', 'terms' => '1',
                'valid_id' => \Illuminate\Http\UploadedFile::fake()->create('id.pdf', 10, 'application/pdf'),
                'business_name' => 'Maria Test Shop', 'business_category' => 'Home and Garden',
                'business_permit' => \Illuminate\Http\UploadedFile::fake()->create('permit.pdf', 10, 'application/pdf'),
            ];
            $this->post($role === 'buyer' ? route('register.store') : '/seller/register', $input)
                ->assertRedirect(route($role.'.verify-email.show'))->assertSessionHasErrors('code');
            $user = User::where('email', $input['email'])->firstOrFail();
            $this->assertSame('pending', $user->status);
            $this->assertNull($user->email_verified_at);
            $this->assertSame(0, $user->emailVerificationCode()->count());
            config(['mail.default' => 'array']);
            $this->postJson(route($role.'.verify-email.resend'))->assertOk();
            $this->assertSame(1, $user->emailVerificationCode()->count());
        }
        $this->assertDatabaseCount('users', 2);
    }

}
