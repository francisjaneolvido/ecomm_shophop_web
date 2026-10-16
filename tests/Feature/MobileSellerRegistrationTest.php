<?php

namespace Tests\Feature;

use App\Models\EmailVerificationCode;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\SellerVerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileSellerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('registration_documents');
    }

    private function input(array $changes = []): array
    {
        return array_replace([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'middle_initial' => 'A',
            'sex' => 'Female',
            'email' => 'seller01@example.com',
            'contact_no' => '+639171234567',
            'birthday' => now()->subYears(20)->toDateString(),
            'province_code' => '040000000',
            'province_name' => 'Laguna',
            'municipality_code' => '043400000',
            'municipality_name' => 'San Pablo City',
            'barangay_code' => '043401001',
            'barangay_name' => 'Bagong Bayan',
            'street_address' => '123 Sampaguita Street',
            'business_name' => 'Maria Home Essentials',
            'business_category' => 'Home and Garden',
            'valid_id' => UploadedFile::fake()->create('seller-id.pdf', 120, 'application/pdf'),
            'business_permit' => UploadedFile::fake()->create('permit.pdf', 120, 'application/pdf'),
            'password' => 'Seller@2026',
            'password_confirmation' => 'Seller@2026',
            'terms' => '1',
        ], $changes);
    }

    private function register(array $changes = [])
    {
        return $this->post('/api/mobile/seller/register', $this->input($changes), [
            'Accept' => 'application/json',
        ]);
    }

    public function test_mobile_seller_creates_the_same_pending_profile_and_private_documents_as_web(): void
    {
        $this->register(['email' => ' Seller01@Example.Com '])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'email_unverified')
            ->assertJsonPath('data.account_type', 'seller');

        $user = User::where('email', 'seller01@example.com')->firstOrFail();
        $seller = Seller::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('seller', $user->account_type);
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('09171234567', $user->registration_contact_no);
        $this->assertSame('09171234567', $seller->contact_no);
        $this->assertSame('Maria Home Essentials', $seller->business_name);
        $this->assertStringStartsWith($user->id.'/valid_id/', $seller->valid_id_path);
        $this->assertStringStartsWith($user->id.'/business_permit/', $seller->business_permit_path);
        Storage::disk('registration_documents')->assertExists([
            $seller->valid_id_path,
            $seller->business_permit_path,
        ]);
        $this->assertTrue($user->emailVerificationCode->expires_at->betweenIncluded(now()->addMinutes(4), now()->addMinutes(5)));
        Notification::assertSentTo($user, SellerVerificationCodeNotification::class);
    }

    public function test_mobile_seller_rejects_duplicate_email_and_mobile_from_web_marketplace(): void
    {
        $this->register()->assertCreated();
        $this->register([
            'email' => 'SELLER01@example.com',
            'contact_no' => '09179998877',
            'business_name' => 'Another Shop',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->register([
            'email' => 'another@example.com',
            'contact_no' => '09171234567',
            'business_name' => 'Another Shop',
        ])->assertUnprocessable()->assertJsonValidationErrors('contact_no');
        $this->assertDatabaseCount('sellers', 1);
    }

    public function test_mobile_seller_requires_age_password_category_and_both_documents(): void
    {
        $this->register([
            'birthday' => now()->subYears(17)->toDateString(),
            'password' => 'Seller2026',
            'password_confirmation' => 'Seller2026',
            'business_category' => 'Not a real category',
            'business_permit' => null,
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'birthday', 'password', 'business_category', 'business_permit',
        ]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_seller_otp_is_verified_but_account_stays_pending(): void
    {
        $this->register()->assertCreated();
        $user = User::where('email', 'seller01@example.com')->firstOrFail();
        EmailVerificationCode::where('user_id', $user->id)->update([
            'code_hash' => Hash::make('123456'),
        ]);
        $this->postJson('/api/mobile/seller/verify-email', [
            'email' => strtoupper($user->email),
            'code' => '000000',
        ])->assertUnprocessable();
        $this->assertSame(1, $user->emailVerificationCode()->first()->attempts);
        $this->postJson('/api/mobile/seller/verify-email', [
            'email' => $user->email,
            'code' => '123456',
        ])->assertOk()->assertJsonPath('status', 'pending');
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->emailVerificationCode);
    }

    public function test_mobile_seller_expired_code_is_rejected_and_resend_has_cooldown(): void
    {
        $this->register()->assertCreated();
        $user = User::where('email', 'seller01@example.com')->firstOrFail();
        $this->postJson('/api/mobile/seller/verify-email/resend', [
            'email' => $user->email,
        ])->assertStatus(429)->assertJsonPath('status', 'cooldown');

        EmailVerificationCode::where('user_id', $user->id)->update([
            'code_hash' => Hash::make('123456'),
            'last_sent_at' => now()->subMinutes(2),
            'expires_at' => now()->subSecond(),
        ]);
        $this->postJson('/api/mobile/seller/verify-email', [
            'email' => $user->email, 'code' => '123456',
        ])->assertUnprocessable();

        $this->postJson('/api/mobile/seller/verify-email/resend', [
            'email' => $user->email,
        ])->assertOk()->assertJsonPath('status', 'email_unverified');
        $this->assertSame(0, $user->emailVerificationCode()->first()->attempts);
    }
}
