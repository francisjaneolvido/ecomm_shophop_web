<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerAccountJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }

    public function test_approved_buyer_updates_only_their_core_profile_and_sees_it_on_next_get(): void
    {
        $buyer = $this->createBuyer('avery@account.test');
        $other = $this->createBuyer('other@account.test');

        $this->actingAs($buyer)
            ->patch(route('buyer.settings.profile.update'), $this->profileInput([
                'first_name' => 'Morgan',
                'middle_initial' => null,
                'sex' => 'Female',
                'contact_no' => '09179998888',
                'birthday' => '1991-05-12',
                'user_id' => $other->id,
                'account_type' => 'admin',
                'status' => 'suspended',
                'email' => 'other@account.test',
            ]))
            ->assertRedirect(route('buyer.profile'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('buyers', [
            'user_id' => $buyer->id,
            'first_name' => 'Morgan',
            'last_name' => 'Buyer',
            'middle_initial' => null,
            'sex' => 'Female',
            'contact_no' => '09179998888',
        ]);
        $this->assertSame('1991-05-12', $buyer->fresh()->buyer->birthday->toDateString());
        $this->assertDatabaseHas('buyers', ['user_id' => $other->id, 'first_name' => 'Avery']);

        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'email' => 'avery@account.test',
            'account_type' => 'buyer',
            'status' => 'approved',
        ]);

        $this->get(route('buyer.profile'))
            ->assertOk()
            ->assertSee('Morgan Buyer')
            ->assertSee('09179998888')
            ->assertSee('1991-05-12');

        $this->patch(route('buyer.settings.profile.update'), $this->profileInput([
            'sex' => 'Prefer not to say',
        ]))->assertSessionHasErrors('sex');
    }

    public function test_invalid_profile_input_changes_nothing_and_non_buyers_cannot_write(): void
    {
        $buyer = $this->createBuyer('avery@account.test');
        $other = $this->createBuyer('other@account.test');

        $this->actingAs($buyer)
            ->patch(route('buyer.settings.profile.update'), $this->profileInput([
                'first_name' => 'Changed',
                'contact_no' => 'invalid',
            ]))
            ->assertSessionHasErrors('contact_no');

        $this->assertDatabaseHas('buyers', [
            'user_id' => $buyer->id,
            'first_name' => 'Avery',
            'contact_no' => '09171234567',
        ]);
        $this->assertDatabaseHas('buyers', ['user_id' => $other->id, 'first_name' => 'Avery']);

        $seller = new User();
        $seller->forceFill([
            'email' => 'seller@account.test',
            'password' => 'unused',
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        $this->actingAs($seller)
            ->patch(route('buyer.settings.profile.update'), $this->profileInput())
            ->assertForbidden();
    }

    public function test_stale_product_preview_is_not_a_buyer_product_route(): void
    {
        $buyer = $this->createBuyer('avery@account.test');

        $this->actingAs($buyer)
            ->get('/buyer/product-preview')
            ->assertNotFound();
    }

    private function profileInput(array $overrides = []): array
    {
        return array_replace([
            'first_name' => 'Avery',
            'last_name' => 'Buyer',
            'middle_initial' => 'Q',
            'sex' => 'Male',
            'contact_no' => '09171234567',
            'birthday' => '1992-06-17',
        ], $overrides);
    }

    private function createBuyer(string $email): User
    {
        $user = new User();
        $user->forceFill([
            'email' => $email,
            'password' => 'unused',
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
            'valid_id_path' => 'buyer-ids/test.pdf',
        ]);

        return $user;
    }
}
