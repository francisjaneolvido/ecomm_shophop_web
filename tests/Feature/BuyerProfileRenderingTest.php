<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerProfileRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        $this->runMigrations([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            // Buyer Orders now queries persisted orders; keep this access test's disposable schema current.
            'Buyer/2026_09_12_102227_create_orders_table.php',
        ]);
    }

    public function test_approved_buyer_profile_renders_persisted_buyer_details(): void
    {
        $buyer = $this->createBuyer();

        $this->actingAs($buyer)
            ->get(route('buyer.profile'))
            ->assertOk()
            ->assertSee('Avery Buyer')
            ->assertSee('09171234567')
            ->assertSee('1992-06-17')
            ->assertSee('avery.buyer@profile.test');
    }

    public function test_buyer_orders_and_profile_require_an_approved_buyer(): void
    {
        $buyer = $this->createBuyer();

        $this->get(route('buyer.orders'))
            ->assertRedirect(route('login'));

        $this->get(route('buyer.profile'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->createNonBuyer())
            ->get(route('buyer.orders'))
            ->assertForbidden();

        $this->actingAs($this->createNonBuyer('second-seller@profile.test'))
            ->get(route('buyer.profile'))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->get(route('buyer.orders'))
            ->assertOk();
    }

    public function test_rendered_profile_keeps_supported_hooks_and_a_parseable_initializer(): void
    {
        // Validate the rendered client boundary: Blade compilation alone cannot catch a broken initializer.
        $response = $this->actingAs($this->createBuyer())->get(route('buyer.profile'))->assertOk();
        foreach (['data-tab-btn="address"', 'data-tab-content="profile"', 'id="settingsMobileSelect"',
            'id="birthdayInput"', 'id="ageInput"', 'data-confirm-action="logout"', 'id="confirmCancelBtn"',
            'id="confirmActionBtn"', 'id="logoutForm"', route('buyer.settings.profile.update')] as $hook) {
            $response->assertSee($hook, false);
        }

        preg_match_all('/<script\b[^>]*>(.*?)<\/script>/si', $response->getContent(), $scripts);
        $profileScripts = array_values(array_filter($scripts[1], fn ($script) => str_contains($script, 'const CONFIRM_ACTIONS')));
        $this->assertCount(1, $profileScripts);
        $syntax = new \Symfony\Component\Process\Process(['node', '--check'], base_path());
        $syntax->setInput($profileScripts[0]);
        $syntax->run();
        $this->assertTrue($syntax->isSuccessful(), $syntax->getErrorOutput());
    }

    public function test_profile_preserves_role_approval_verification_and_rider_guard_boundaries(): void
    {
        // Exercise Profile's own route so restored client behavior cannot mask a changed access boundary.
        $user = $this->createBuyer();
        foreach (['seller', 'admin', 'logistics'] as $role) {
            $user->forceFill(['account_type' => $role])->save();
            $this->actingAs($user)->get(route('buyer.profile'))->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $user->forceFill(['account_type' => 'buyer', 'status' => $status])->save();
            $this->actingAs($user)->get(route('buyer.profile'))
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
        $user->forceFill(['status' => 'approved', 'email_verified_at' => null])->save();
        $this->actingAs($user)->get(route('buyer.profile'))->assertRedirect(route('buyer.verify-email.show'));
        $this->assertGuest('web');

        // A genuine separate Rider identity must not satisfy the web Buyer guard.
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 991, 'name' => 'Profile Guard Rider', 'status' => 'active']);
        $this->actingAs($rider, 'rider');
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $this->assertAuthenticatedAs($rider, 'rider');
        $this->get(route('buyer.profile'))->assertRedirect(route('login'));
    }

    private function createBuyer(): User
    {
        $user = new User();

        $user->forceFill([
            'email' => 'avery.buyer@profile.test',
            'password' => 'not-used-by-this-test',
            'account_type' => 'buyer',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        Buyer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Avery',
            'last_name' => 'Buyer',
            'middle_initial' => 'Q',
            'sex' => 'Male',
            'contact_no' => '09171234567',
            'birthday' => '1992-06-17',
            'province_code' => '04',
            'province_name' => 'CALABARZON',
            'municipality_code' => '0434',
            'municipality_name' => 'Bacoor',
            'barangay_code' => '043405',
            'barangay_name' => 'Molino III',
            'street_address' => '12 Market Street',
            'valid_id_path' => 'buyer-ids/avery-buyer.pdf',
        ]);

        return $user;
    }

    private function createNonBuyer(string $email = 'seller@profile.test'): User
    {
        $user = new User();

        $user->forceFill([
            'email' => $email,
            'password' => 'not-used-by-this-test',
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function runMigrations(array $paths): void
    {
        foreach ($paths as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }
}
