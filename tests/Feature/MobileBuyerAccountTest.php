<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileBuyerAccountTest extends TestCase
{
    private User $buyerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('account_type');
            $table->string('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_initial')->nullable();
            $table->string('sex');
            $table->string('contact_no');
            $table->date('birthday');
            $table->string('street_address')->default('Saved delivery address');
            $table->timestamps();
        });
        $this->buyerUser = User::create([
            'email' => 'buyer@example.com', 'password' => 'OldPassword1!',
            'account_type' => 'buyer', 'status' => 'approved',
        ]);
        $this->buyerUser->forceFill(['email_verified_at' => now(), 'remember_token' => 'remembered'])->save();
        Buyer::create(['user_id' => $this->buyerUser->id, ...$this->profile()]);
    }

    private function profile(): array
    {
        return ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'middle_initial' => 'A',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1998-04-15'];
    }

    public function test_profile_reads_and_updates_only_the_authenticated_buyer(): void
    {
        $other = Buyer::create(['user_id' => 999, ...$this->profile()]);
        Sanctum::actingAs($this->buyerUser);
        $this->getJson('/api/mobile/buyer/profile')->assertOk()
            ->assertJsonPath('data.first_name', 'Juan')
            ->assertJsonPath('data.email', 'buyer@example.com')
            ->assertJsonPath('data.birthday', '1998-04-15')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.valid_id_path');
        $this->patchJson('/api/mobile/buyer/profile', [
            ...$this->profile(), 'first_name' => 'María', 'buyer_id' => $other->id,
            'email' => 'intruder@example.com', 'status' => 'suspended',
            'street_address' => 'Do not overwrite',
        ])->assertOk()->assertJsonPath('data.first_name', 'María');
        $this->assertSame('María', $this->buyerUser->buyer()->first()->first_name);
        $this->assertSame('Juan', $other->fresh()->first_name);
        $this->assertSame('buyer@example.com', $this->buyerUser->fresh()->email);
        $this->assertSame('approved', $this->buyerUser->fresh()->status);
        $this->assertSame('Saved delivery address', $this->buyerUser->buyer()->first()->street_address);
        $this->getJson('/api/mobile/me')->assertOk()->assertJsonPath('data.first_name', 'María');
    }

    public function test_profile_validation_preserves_saved_values(): void
    {
        Sanctum::actingAs($this->buyerUser);
        $this->patchJson('/api/mobile/buyer/profile', [
            ...$this->profile(), 'first_name' => '', 'contact_no' => '123',
            'birthday' => now()->addDay()->toDateString(), 'sex' => 'Invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'contact_no', 'birthday', 'sex']);
        $this->assertSame('Juan', $this->buyerUser->buyer()->first()->first_name);
        $this->assertSame('09123456789', $this->buyerUser->buyer()->first()->contact_no);
    }

    public function test_password_change_checks_current_password_confirmation_and_strength(): void
    {
        Sanctum::actingAs($this->buyerUser);
        $url = '/api/mobile/buyer/password';
        $this->patchJson($url, ['current_password' => 'Wrong1!', 'password' => 'NewPassword2!',
            'password_confirmation' => 'NewPassword2!'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson($url, ['current_password' => 'OldPassword1!', 'password' => 'NewPassword2!',
            'password_confirmation' => 'Different2!'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->patchJson($url, ['current_password' => 'OldPassword1!', 'password' => 'weak',
            'password_confirmation' => 'weak'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertTrue(Hash::check('OldPassword1!', $this->buyerUser->fresh()->password));
        $longPassword = 'Aa1!'.str_repeat('é', 35);
        $this->patchJson($url, ['current_password' => 'OldPassword1!', 'password' => $longPassword,
            'password_confirmation' => $longPassword])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->patchJson($url, ['current_password' => 'OldPassword1!', 'password' => 'NewPassword2!',
            'password_confirmation' => 'NewPassword2!'])->assertOk()->assertJsonPath('success', true);
        $this->assertTrue(Hash::check('NewPassword2!', $this->buyerUser->fresh()->password));
        $this->assertFalse(Hash::check('OldPassword1!', $this->buyerUser->fresh()->password));
        $this->assertNull($this->buyerUser->fresh()->remember_token);
    }

    public function test_guest_and_ineligible_accounts_cannot_access_settings(): void
    {
        $this->getJson('/api/mobile/buyer/profile')->assertUnauthorized();
        $this->patchJson('/api/mobile/buyer/profile', $this->profile())->assertUnauthorized();
        $this->patchJson('/api/mobile/buyer/password', [])->assertUnauthorized();
        foreach ([['account_type' => 'seller'], ['status' => 'suspended'], ['email_verified_at' => null]] as $change) {
            $actor = $this->buyerUser->fresh();
            $actor->forceFill($change);
            Sanctum::actingAs($actor);
            $this->getJson('/api/mobile/buyer/profile')->assertForbidden();
            $this->patchJson('/api/mobile/buyer/profile', $this->profile())->assertForbidden();
            $this->patchJson('/api/mobile/buyer/password', [])->assertForbidden();
        }
    }
}
