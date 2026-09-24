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
