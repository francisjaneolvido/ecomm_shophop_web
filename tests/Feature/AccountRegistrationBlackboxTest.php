<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Test subset derived from TC-BUY/TC-SEL/TC-LOG in Account Creation workbook. */
class AccountRegistrationBlackboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('registration_documents');
    }

    private function buyerInput(array $overrides = []): array
    {
        return array_replace([
            'first_name' => 'Juan Miguel',
            'last_name' => 'Dela Cruz',
            'middle_initial' => 'M',
            'sex' => 'Male',
            'email' => 'buyer01@example.com',
            'contact_no' => '09171234567',
            'birthday' => now()->subYears(20)->toDateString(),
            'province_code' => '040000000',
            'province_name' => 'Laguna',
            'municipality_code' => '043400000',
            'municipality_name' => 'San Pablo City',
            'barangay_code' => '043401001',
            'barangay_name' => 'Bagong Bayan',
            'street_address' => '1 Main Street',
            'valid_id' => UploadedFile::fake()->image('id.png'),
            'password' => 'Mamimili@2026',
            'password_confirmation' => 'Mamimili@2026',
            'terms' => '1',
        ], $overrides);
    }

    public function test_buyer_email_and_mobile_are_normalized_and_otp_expires_in_five_minutes(): void
    {
        $this->post(route('register.store'), $this->buyerInput([
            'email' => '  BUYER01@EXAMPLE.COM  ',
            'contact_no' => '+639171234567',
        ]))->assertRedirect(route('buyer.verify-email.show'));

        $user = User::where('email', 'buyer01@example.com')->firstOrFail();
        $this->assertSame('09171234567', $user->registration_contact_no);
        $this->assertSame('09171234567', $user->buyer->contact_no);
        $this->assertTrue($user->emailVerificationCode->expires_at->betweenIncluded(now()->addMinutes(4), now()->addMinutes(5)));
    }

    public function test_buyer_rejects_underage_and_invalid_name_lengths(): void
    {
        foreach ([
            ['birthday' => now()->subYears(18)->addDay()->toDateString()],
            ['first_name' => 'J'],
            ['last_name' => str_repeat('A', 51)],
        ] as $changes) {
            $field = array_key_first($changes);
            $this->post(route('register.store'), $this->buyerInput($changes))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_buyer_requires_special_character_and_blocks_case_insensitive_email_duplicate(): void
    {
        $existing = User::create([
            'email' => 'EXISTING@EXAMPLE.COM',
            'password' => 'somehash', 'account_type' => 'buyer', 'status' => 'pending',
        ]);
        $this->post(route('register.store'), $this->buyerInput([
            'email' => 'existing@example.com',
        ]))->assertSessionHasErrors('email');

        $this->post(route('register.store'), $this->buyerInput([
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ]))->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_buyer_rejects_existing_phone_across_accounts_and_plus63_spelling(): void
    {
        $this->post(route('register.store'), $this->buyerInput())
            ->assertRedirect(route('buyer.verify-email.show'));
        $this->post(route('register.store'), $this->buyerInput([
            'email' => 'buyer02@example.com',
            'contact_no' => '+639171234567',
        ]))->assertSessionHasErrors('contact_no');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_logistics_rejects_invalid_mobile_and_underage_representative(): void
    {
        $this->post(route('logistics.register.store'), [
            'email' => 'logistics@example.com',
            'contact_no' => '12345678901234',
            'rep_birthday' => now()->subYears(17)->toDateString(),
        ])->assertSessionHasErrors(['contact_no', 'rep_birthday'], null, 'logisticsRegistration');
        $this->assertDatabaseCount('users', 0);
    }
}
