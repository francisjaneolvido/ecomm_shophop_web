<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
// A deliberate disposable database failure verifies that save feedback is never emitted before persistence.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Exercise the approved Seller HTTP boundary against disposable schema, never runtime account data.
class SellerAccountJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Destructive fixture setup must fail closed if PHPUnit ever loses its in-memory configuration.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000002_create_sellers_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // A saved profile must survive the next GET and cannot target another Seller or alter reviewed registration facts.
    public function test_seller_saves_their_core_profile_and_sees_persisted_values_on_next_get(): void
    {
        $seller = $this->seller('owner@account.test');
        $other = $this->seller('other@account.test');
        $original = $seller->seller->only(['business_name', 'business_category', 'street_address', 'valid_id_path', 'business_permit_path']);

        $this->actingAs($seller)->patch('/seller/account', $this->profile([
            'first_name' => 'Morgan',
            'middle_initial' => null,
            'sex' => 'Female',
            'contact_no' => '09179998888',
            'birthday' => '1991-05-12',
            'seller_id' => $other->seller->id,
            'user_id' => $other->id,
            'email' => 'replacement@account.test',
            'status' => 'suspended',
            'account_type' => 'admin',
            'business_name' => 'Unreviewed replacement',
            'business_category' => 'Unsupported category',
            'street_address' => 'Replacement address',
            'valid_id_path' => 'replacement.pdf',
            'business_permit_path' => 'replacement.pdf',
            'password' => 'ReplacementPassword123!',
        ]))->assertRedirect('/seller/account')->assertSessionHas('status', 'Profile changes saved.');

        $this->assertDatabaseHas('sellers', ['user_id' => $seller->id, 'first_name' => 'Morgan',
            'middle_initial' => null, 'sex' => 'Female', 'contact_no' => '09179998888']);
        $this->assertSame('1991-05-12', $seller->seller->fresh()->birthday->format('Y-m-d'));
        $this->assertSame($original, $seller->seller->fresh()->only(array_keys($original)));
        $this->assertSame('Avery', $other->seller->fresh()->first_name);
        $this->assertSame('owner@account.test', $seller->fresh()->email);
        $this->assertSame('approved', $seller->fresh()->status);
        $this->assertSame('seller', $seller->fresh()->account_type);
        $this->assertTrue(password_verify('OriginalPassword123!', $seller->fresh()->password));

        $this->get('/seller/account')->assertOk()->assertSee('Morgan')->assertSee('09179998888')
            ->assertSee('1991-05-12')->assertSee('Registered Shop')->assertSee('owner@account.test')
            ->assertDontSee('Andrea Local Finds')->assertDontSee('government-id.pdf')
            ->assertDontSee('demoSaveDocument')->assertDontSee('type="file"', false)
            ->assertSee('Unavailable Account Features');
    }

    // Server validation must preserve every persisted field and return real errors with the attempted input.
    public function test_invalid_profile_is_not_saved_and_errors_survive_the_redirect(): void
    {
        $seller = $this->seller('validation@account.test');
        $before = $seller->seller->getAttributes();
        $this->actingAs($seller)->from('/seller/account')->patch('/seller/account', $this->profile([
            'first_name' => str_repeat('A', 101), 'last_name' => '123', 'middle_initial' => 'LONG',
            'sex' => 'Unsupported', 'contact_no' => '123', 'birthday' => now()->addDay()->format('Y-m-d'),
        ]))->assertRedirect('/seller/account')->assertSessionHasErrors([
            'first_name', 'last_name', 'middle_initial', 'sex', 'contact_no', 'birthday',
        ])->assertSessionMissing('status');

        $this->assertSame($before, $seller->seller->fresh()->getAttributes());
        // Carry the browser's session cookie across the redirect rather than starting an unrelated session.
        $this->withCookie(config('session.cookie'), session()->getId())->get('/seller/account')
            ->assertOk()->assertSee('Profile changes were not saved.')
            ->assertSee('value="123"', false)->assertSee('aria-invalid="true"', false)
            ->assertDontSee('Profile changes saved.');
    }

    // Both account methods retain the existing guest, role, approval and email-verification boundaries.
    public function test_account_access_requires_an_approved_verified_seller(): void
    {
        $this->get('/seller/account')->assertRedirect();
        $this->patch('/seller/account', $this->profile())->assertRedirect();
        foreach (['buyer', 'admin', 'logistics'] as $role) {
            $user = $this->seller($role.'@account.test', ['account_type' => $role], false);
            $this->actingAs($user)->get('/seller/account')->assertForbidden();
            $this->patch('/seller/account', $this->profile())->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $user = $this->seller($status.'@account.test', ['status' => $status]);
            $this->actingAs($user)->get('/seller/account')->assertRedirect();
            $this->actingAs($user)->patch('/seller/account', $this->profile(['first_name' => 'Denied']))->assertRedirect();
            $this->assertSame('Avery', $user->seller->fresh()->first_name);
        }
        $user = $this->seller('unverified@account.test', ['email_verified_at' => null]);
        $this->actingAs($user)->get('/seller/account')->assertRedirect(route('seller.verify-email.show'));
        $this->actingAs($user)->patch('/seller/account', $this->profile(['first_name' => 'Denied']))
            ->assertRedirect(route('seller.verify-email.show'));
        $this->assertSame('Avery', $user->seller->fresh()->first_name);
    }

    // A malformed registration cannot be replaced by demo data or targeted through a submitted profile ID.
    public function test_missing_seller_profile_fails_closed_for_reads_and_writes(): void
    {
        $user = $this->seller('missing@account.test', [], false);
        $other = $this->seller('existing@account.test');
        $this->actingAs($user)->get('/seller/account')->assertNotFound();
        $this->patch('/seller/account', $this->profile(['seller_id' => $other->seller->id]))->assertNotFound();
        $this->assertSame('Avery', $other->seller->fresh()->first_name);
    }

    // A real storage failure must propagate as failure, leave data intact, and never flash saved feedback.
    public function test_failed_persistence_cannot_claim_success(): void
    {
        $user = $this->seller('failure@account.test');
        DB::unprepared("CREATE TRIGGER reject_profile_update BEFORE UPDATE ON sellers BEGIN SELECT RAISE(ABORT, 'fixture write failure'); END");
        $this->actingAs($user)->patch('/seller/account', $this->profile(['first_name' => 'Unstored']))
            ->assertStatus(500)->assertSessionMissing('status');
        $this->assertSame('Avery', $user->seller->fresh()->first_name);
    }

    // Local TEST navigation must resolve to real authorized account data rather than fabricate a personal profile.
    public function test_local_account_preview_redirects_to_the_authenticated_account_route(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->get('/__dev/preview/seller/account')->assertRedirect('/seller/account');
    }

    // Fixtures use the same persisted Seller shape as registration, including private document references.
    private function seller(string $email, array $overrides = [], bool $withProfile = true): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => $email, 'password' => 'OriginalPassword123!',
            'account_type' => 'seller', 'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();
        if ($withProfile) {
            Seller::create(array_merge($this->profile(), ['user_id' => $user->id,
                'province_code' => '04', 'province_name' => 'CALABARZON',
                'municipality_code' => '0410', 'municipality_name' => 'Calamba',
                'barangay_code' => '041001', 'barangay_name' => 'Real', 'street_address' => '1 Registered Street',
                'business_name' => 'Registered Shop', 'business_category' => 'Electronics & Gadgets',
                'valid_id_path' => 'private/identity.pdf', 'business_permit_path' => 'private/permit.pdf']));
        }

        return $user;
    }

    // Only the existing core profile fields form the editable account contract.
    private function profile(array $overrides = []): array
    {
        return array_replace(['first_name' => 'Avery', 'last_name' => 'Seller', 'middle_initial' => 'Q',
            'sex' => 'Male', 'contact_no' => '09171234567', 'birthday' => '1992-06-17'], $overrides);
    }
}
