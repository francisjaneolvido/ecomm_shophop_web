<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminModerationJourneyTest extends TestCase
{
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            '2026_08_29_000002_create_sellers_table.php',
            '2026_08_29_000003_create_logistics_partners_table.php',
            '2026_08_29_000004_create_logistics_coverage_areas_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        Route::middleware(['web', 'auth', 'approved.role:buyer'])
            ->get('/_moderation-access-probe', fn () => response('allowed'));
    }

    public function test_registration_queue_and_detail_use_persisted_eligible_applicants(): void
    {
        $admin = $this->user('admin', 'approved');
        $pending = $this->user('buyer', 'pending');
        $this->buyerProfile($pending);
        $unverified = $this->user('buyer', 'pending', false);
        $approved = $this->user('seller', 'approved');

        $this->actingAs($admin)->get(route('admin.registrations'))
            ->assertOk()
            ->assertSee($pending->email)
            ->assertDontSee($unverified->email)
            ->assertDontSee($approved->email);

        $this->get(route('admin.registrations.show', $pending))
            ->assertOk()
            ->assertJsonPath('id', $pending->id)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('name', 'Applicant Buyer')
            ->assertJsonPath('docs_summary.value', '1/1')
            ->assertJsonPath('documents.0.status', 'submitted')
            ->assertJsonPath('documents.0.url', config('filesystems.disks.public.url').'/registration/valid-id.png');
    }

    public function test_approve_and_reject_change_only_eligible_pending_users_and_refresh_admin_views(): void
    {
        $admin = $this->user('admin', 'approved');
        $approved = $this->user('buyer', 'pending');
        $profile = $this->buyerProfile($approved);
        $rejected = $this->user('buyer', 'pending');
        $originalPassword = $approved->password;

        $this->actingAs($admin)->from(route('admin.registrations'))
            ->post(route('admin.users.approve', $approved))
            ->assertRedirect(route('admin.registrations'))
            ->assertSessionHas('status');

        $approved->refresh();
        $this->assertSame('approved', $approved->status);
        $this->assertSame('buyer', $approved->account_type);
        $this->assertSame($originalPassword, $approved->password);
        $this->assertSame($profile->id, $approved->buyer->id);
        $this->get(route('admin.registrations'))->assertDontSee($approved->email);
        $this->get(route('admin.registrations', ['filter' => 'approved']))->assertSee($approved->email);
        $this->get(route('admin.registrations.show', $approved))->assertJsonPath('status', 'approved');
        $this->get(route('admin.users'))->assertSee($approved->email);
        $this->actingAs($approved)->get('/_moderation-access-probe')->assertOk();

        $this->actingAs($admin)->from(route('admin.registrations'))
            ->post(route('admin.users.reject', $rejected))
            ->assertRedirect(route('admin.registrations'))
            ->assertSessionHas('status');
        $this->assertSame('rejected', $rejected->fresh()->status);
        $this->assertSame('buyer', $rejected->account_type);
        $this->get(route('admin.registrations', ['filter' => 'rejected']))->assertSee($rejected->email);
        $this->get(route('admin.registrations.show', $rejected))->assertJsonPath('status', 'rejected');
        $this->get(route('admin.users'))->assertDontSee($rejected->email);
        $this->actingAs($rejected->fresh())->get('/_moderation-access-probe')->assertRedirect(route('home'));
    }

    public function test_seller_and_logistics_review_use_their_persisted_profile_documents(): void
    {
        $admin = $this->user('admin', 'approved');
        $seller = $this->user('seller', 'pending');
        $logistics = $this->user('logistics', 'pending', false);
        $sellerProfile = Seller::query()->create([
            'user_id' => $seller->id,
            'first_name' => 'Seller', 'last_name' => 'Applicant',
            'sex' => 'Female', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => 'P1', 'province_name' => 'Province',
            'municipality_code' => 'M1', 'municipality_name' => 'Municipality',
            'barangay_code' => 'B1', 'barangay_name' => 'Barangay',
            'street_address' => '2 Main Street', 'business_name' => 'Seller Shop',
            'business_category' => 'Retail', 'valid_id_path' => 'ids/seller.png',
            'business_permit_path' => 'permits/seller.png',
        ]);
        $logisticsProfile = LogisticsPartner::query()->create([
            'user_id' => $logistics->id,
            'agreement_rep_name' => 'Logistics Applicant', 'agreement_date' => '2026-01-01',
            'agreement_signature_path' => 'agreements/logistics.png',
            'company_name' => 'Logistics Company', 'business_registration_no' => 'REG-1',
            'line_of_business' => 'motorcycle_courier', 'rep_last_name' => 'Applicant',
            'rep_first_name' => 'Logistics', 'rep_valid_id_path' => 'ids/logistics.png',
            'rep_id_number' => 'ID-1', 'rep_sex' => 'male', 'rep_birthday' => '1990-01-01',
            'contact_no' => '09123456789', 'region' => 'Region', 'province' => 'Province',
            'municipality' => 'Municipality', 'barangay' => 'Barangay',
            'street_no' => '2', 'unit_no' => '1', 'business_permit_path' => 'permits/logistics.png',
            'accreditation_docs_path' => null,
        ]);

        $this->actingAs($admin)->get(route('admin.registrations'))
            ->assertSee($seller->email)->assertSee($logistics->email);
        $this->get(route('admin.registrations.show', $seller))
            ->assertJsonPath('name', 'Seller Applicant')
            ->assertJsonPath('docs_summary.value', '2/2');
        $this->get(route('admin.registrations.show', $logistics))
            ->assertJsonPath('name', 'Logistics Company')
            ->assertJsonPath('docs_summary.value', '3/4')
            ->assertJsonPath('documents.2.status', 'missing');

        $this->from(route('admin.registrations'))->post(route('admin.users.approve', $logistics))
            ->assertSessionHas('status');
        $this->assertSame('approved', $logistics->fresh()->status);
        $this->assertSame($logisticsProfile->id, $logistics->logisticsPartner->id);
        $this->assertSame($sellerProfile->id, $seller->seller->id);
    }

    public function test_suspend_and_reactivate_preserve_identity_and_change_protected_access(): void
    {
        $admin = $this->user('admin', 'approved');
        $buyer = $this->user('buyer', 'approved');
        $this->buyerProfile($buyer);
        $email = $buyer->email;

        $this->actingAs($admin)->from(route('admin.users'))
            ->post(route('admin.users.suspend', $buyer))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('status');
        $this->assertSame('suspended', $buyer->fresh()->status);
        $this->assertSame($email, $buyer->email);
        $suspendedList = $this->get(route('admin.users', ['filter' => 'suspended']));
        $suspendedList->assertSee($email);
        $this->assertTrue($suspendedList->viewData('usersForJs')->firstWhere('id', $buyer->id)['verified']);
        $this->assertSame(
            config('filesystems.disks.public.url').'/registration/valid-id.png',
            $suspendedList->viewData('usersForJs')->firstWhere('id', $buyer->id)['documents'][0]['url']
        );
        $this->get(route('admin.users.show', $buyer))->assertJsonPath('status', 'suspended');
        $this->actingAs($buyer->fresh())->get('/_moderation-access-probe')->assertRedirect(route('home'));

        $this->actingAs($admin)->from(route('admin.users'))
            ->post(route('admin.users.reactivate', $buyer))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('status');
        $this->assertSame('approved', $buyer->fresh()->status);
        $this->get(route('admin.users.show', $buyer))->assertJsonPath('status', 'approved');
        $this->actingAs($buyer->fresh())->get('/_moderation-access-probe')->assertOk();
    }

    public function test_invalid_transitions_and_admin_targets_cannot_be_moderated(): void
    {
        $admin = $this->user('admin', 'approved');
        $otherAdmin = $this->user('admin', 'approved');
        $active = $this->user('buyer', 'approved');
        $pending = $this->user('buyer', 'pending');
        $rejected = $this->user('buyer', 'rejected');
        $unverified = $this->user('buyer', 'pending', false);

        foreach ([
            ['approve', $active],
            ['reject', $active],
            ['suspend', $pending],
            ['reactivate', $active],
            ['reactivate', $rejected],
            ['approve', $unverified],
            ['suspend', $otherAdmin],
            ['suspend', $admin],
        ] as [$action, $target]) {
            $before = $target->status;
            $this->actingAs($admin)->from(route('admin.users'))
                ->post(route('admin.users.'.$action, $target))
                ->assertRedirect(route('admin.users'))
                ->assertSessionHasErrors('moderation');
            $this->assertSame($before, $target->fresh()->status);
        }

        $this->followingRedirects()->actingAs($admin)
            ->post(route('admin.users.suspend', $otherAdmin))
            ->assertSee('Only active Buyer, Seller, or Logistics accounts can be suspended.');
        $this->get(route('admin.users.show', $otherAdmin))->assertNotFound();
        $this->get(route('admin.registrations.show', $otherAdmin))->assertNotFound();
    }

    public function test_non_admin_cannot_invoke_moderation_mutation(): void
    {
        $buyer = $this->user('buyer', 'approved');
        $pending = $this->user('buyer', 'pending');

        $this->actingAs($buyer)->post(route('admin.users.approve', $pending))->assertForbidden();
        $this->assertSame('pending', $pending->fresh()->status);
    }

    private function user(string $role, string $status, bool $verified = true): User
    {
        $this->sequence++;
        $user = new User();
        $user->forceFill([
            'email' => "moderation-{$this->sequence}@example.test",
            'password' => 'password-hash-for-test',
            'account_type' => $role,
            'status' => $status,
            'email_verified_at' => $verified ? now() : null,
        ]);
        $user->save();

        return $user;
    }

    private function buyerProfile(User $user): Buyer
    {
        return Buyer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Applicant',
            'last_name' => 'Buyer',
            'sex' => 'Male',
            'contact_no' => '09123456789',
            'birthday' => '1990-01-01',
            'province_code' => 'P1',
            'province_name' => 'Province',
            'municipality_code' => 'M1',
            'municipality_name' => 'Municipality',
            'barangay_code' => 'B1',
            'barangay_name' => 'Barangay',
            'street_address' => '1 Main Street',
            'valid_id_path' => 'registration/valid-id.png',
        ]);
    }
}
