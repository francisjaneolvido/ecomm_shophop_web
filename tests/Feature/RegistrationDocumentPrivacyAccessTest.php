<?php

namespace Tests\Feature;

use App\Models\User;
// Actual Rider-session evidence uses its separate authenticatable model, not a fictional web role.
use App\Models\Logistics\Rider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
// Failure injection is confined to the filesystem boundary; HTTP and command behavior remain real.
use Illuminate\Filesystem\FilesystemAdapter;
use Mockery;
// SQLite failure triggers exercise real transaction rollback without mocking persistence logic.
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationDocumentPrivacyAccessTest extends TestCase
{
    protected function setUp(): void
    {
        // All upload and review evidence uses isolated schema and fake disks, never normal documents.
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            '2026_08_29_000002_create_sellers_table.php',
            '2026_08_29_000003_create_logistics_partners_table.php',
            '2026_08_29_000004_create_logistics_coverage_areas_table.php',
            '2026_09_11_125812_create_email_verification_codes_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Storage::fake('public');
        Storage::fake('registration_documents');
        Notification::fake();
        // Minimal Rider credential schema is enough to exercise the real separate session guard.
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logistics_partner_id');
            $table->string('name');
            $table->string('vehicle_type');
            $table->string('status');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
    }

    public function test_buyer_registration_writes_identity_only_to_private_storage(): void
    {
        // The real registration HTTP seam must persist an ID without placing any file on public storage.
        $this->post(route('register.store'), $this->buyerInput())
            ->assertSessionHasNoErrors()->assertRedirect(route('buyer.verify-email.show'));
        $buyer = User::where('email', 'buyer@example.test')->firstOrFail()->buyer;
        Storage::disk('public')->assertDirectoryEmpty('/');
        Storage::disk('registration_documents')->assertExists($buyer->valid_id_path);
        $this->assertStringStartsWith($buyer->user_id.'/valid_id/', $buyer->valid_id_path);
        $this->assertSame('pending', $buyer->user->status);
        $this->assertNull($buyer->user->email_verified_at);
    }

    public function test_seller_registration_writes_both_documents_privately(): void
    {
        // Seller registration retains its two required documents and pending email-verification lifecycle.
        $input = $this->buyerInput();
        $input['email'] = 'seller@example.test';
        $input['business_name'] = 'Test Store';
        $input['business_category'] = config('shophop_categories.0.name');
        $input['business_permit'] = UploadedFile::fake()->create('permit.pdf', 1, 'application/pdf');
        $this->post(route('seller.register.store'), $input)
            ->assertSessionHasNoErrors()->assertRedirect(route('seller.verify-email.show'));
        $user = User::where('email', $input['email'])->firstOrFail();
        foreach (['valid_id', 'business_permit'] as $slot) {
            $path = $user->seller->{$slot.'_path'};
            Storage::disk('registration_documents')->assertExists($path);
            $this->assertStringStartsWith($user->id.'/'.$slot.'/', $path);
        }
        Storage::disk('public')->assertDirectoryEmpty('/');
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->email_verified_at);
    }

    public function test_logistics_registration_writes_all_four_documents_privately(): void
    {
        // Logistics keeps required signature/ID/permit and optional accreditation on the same private contract.
        $input = $this->logisticsInput();
        $this->post(route('logistics.register.store'), $input)
            ->assertSessionHasNoErrors()->assertRedirect(route('home'));
        $user = User::where('email', $input['email'])->firstOrFail();
        foreach (['agreement_signature', 'rep_valid_id', 'business_permit', 'accreditation_docs'] as $slot) {
            $path = $user->logisticsPartner->{$slot.'_path'};
            Storage::disk('registration_documents')->assertExists($path);
            $this->assertStringStartsWith($user->id.'/'.$slot.'/', $path);
        }
        Storage::disk('public')->assertDirectoryEmpty('/');
        $this->assertSame('pending', $user->status);
        $this->assertSame(1, $user->logisticsPartner->coverageAreas()->count());
    }

    public function test_mobile_buyer_registration_uses_the_same_private_identity_contract(): void
    {
        // Mobile registration writes the same Buyer column and must not bypass web-upload privacy.
        $this->postJson('/api/mobile/buyer/register', $this->buyerInput())
            ->assertCreated()->assertJsonPath('status', 'email_unverified');
        $user = User::where('email', 'buyer@example.test')->firstOrFail();
        Storage::disk('public')->assertDirectoryEmpty('/');
        Storage::disk('registration_documents')->assertExists($user->buyer->valid_id_path);
        $this->assertStringStartsWith($user->id.'/valid_id/', $user->buyer->valid_id_path);
    }

    public function test_admin_review_returns_only_protected_record_slot_urls_and_streams_content(): void
    {
        // Detail JSON feeds the existing modal; following its URL must return the selected fixture privately.
        $buyer = $this->registeredBuyer();
        $path = $buyer->buyer->valid_id_path;
        Storage::disk('registration_documents')->put($path, "%PDF-1.4\nBuyer A fixture\n%%EOF");
        $this->actingAs($this->admin(), 'web');
        $detail = $this->get(route('admin.registrations.show', $buyer))->assertOk();
        $url = url('/admin/registration/'.$buyer->id.'/documents/valid_id');
        $detail->assertJsonPath('documents.0.url', $url)->assertDontSee('/storage/', false)->assertDontSee($path, false);
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame("%PDF-1.4\nBuyer A fixture\n%%EOF", $response->streamedContent());
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertFalse($response->headers->has('Location'));
        // The response filename names the record/slot rather than exposing the stored filesystem key.
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('registration-'.$buyer->id.'-valid_id.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_document_access_denies_guest_wrong_web_roles_and_disallowed_admin_statuses(): void
    {
        // Document access must reuse the canonical Admin boundary, including status revocation.
        $buyer = $this->registeredBuyer();
        $url = '/admin/registration/'.$buyer->id.'/documents/valid_id';
        // The outer auth middleware redirects a guest to login before approved.role runs.
        $this->get($url)->assertRedirect(route('login'));
        foreach (['buyer', 'seller', 'logistics'] as $role) {
            $this->actingAs($this->admin($role), 'web')->get($url)->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $admin = $this->admin('admin', $status);
            $this->actingAs($admin, 'web')->get($url)->assertRedirect(route('home'));
            $this->assertFalse(Auth::guard('web')->check());
        }
    }

    public function test_actual_rider_guard_does_not_authorize_admin_document_access(): void
    {
        // A real Rider session must not satisfy auth:web even when its partner is approved.
        $buyer = $this->registeredBuyer();
        $this->post(route('logistics.register.store'), $this->logisticsInput())->assertSessionHasNoErrors();
        $operator = User::where('email', 'logistics@example.test')->firstOrFail();
        $operator->update(['status' => 'approved']);
        $rider = Rider::create(['logistics_partner_id' => $operator->logisticsPartner->id,
            'name' => 'Test Rider', 'vehicle_type' => 'Motorcycle', 'status' => 'active',
            'email' => 'rider@example.test', 'password' => bcrypt('Password123')]);
        // Native Rider login retains web as the default guard, exactly as a real Admin-route request does.
        $this->post(route('rider.login.store'), ['email' => $rider->email, 'password' => 'Password123'])
            ->assertRedirect(route('rider.deliveries.index'));
        $this->get('/admin/registration/'.$buyer->id.'/documents/valid_id')->assertRedirect(route('login'));
        $this->assertTrue(Auth::guard('rider')->check());
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_record_slot_binding_rejects_other_paths_types_and_missing_files(): void
    {
        // Both URL manipulation and corrupted stored references must fail without resolving another record's file.
        $a = $this->registeredBuyer();
        $input = $this->buyerInput();
        $input['email'] = 'buyer-b@example.test';
        $this->post(route('register.store'), $input)->assertSessionHasNoErrors();
        $b = User::where('email', $input['email'])->firstOrFail();
        $b->forceFill(['email_verified_at' => now()])->save();
        Storage::disk('registration_documents')->put($b->buyer->valid_id_path, 'Buyer B only');
        $this->actingAs($this->admin(), 'web');
        $url = '/admin/registration/'.$a->id.'/documents/valid_id';
        $response = $this->get($url.'?path='.urlencode($b->buyer->valid_id_path).'&disk=public')->assertOk();
        $this->assertNotSame('Buyer B only', $response->streamedContent());
        foreach (['business_permit', 'rep_valid_id', 'unknown', '..', '%2E%2E%2Fsecret.pdf'] as $slot) {
            $this->get('/admin/registration/'.$a->id.'/documents/'.$slot)->assertNotFound();
        }
        $this->get('/admin/registration/999999/documents/valid_id')->assertNotFound();
        // An eligible User without its authoritative role profile has no document to resolve.
        $this->get('/admin/registration/'.$this->admin('buyer', 'pending')->id.'/documents/valid_id')->assertNotFound();
        foreach ([$b->buyer->valid_id_path, '../secret.pdf', '/etc/passwd', 'valid_ids/buyers/legacy.pdf'] as $path) {
            $a->buyer->update(['valid_id_path' => $path]);
            $this->get($url)->assertNotFound();
        }
        $a->buyer->update(['valid_id_path' => $a->id.'/valid_id/missing.pdf']);
        Storage::disk('public')->put($a->id.'/valid_id/missing.pdf', 'No public fallback');
        $this->get($url)->assertNotFound();
        $this->get(route('admin.registrations.show', $a))->assertJsonPath('documents.0.url', null);
        $b->forceFill(['email_verified_at' => null])->save();
        $this->get('/admin/registration/'.$b->id.'/documents/valid_id')->assertNotFound();
        $this->get('/admin/registration/'.$this->admin()->id.'/documents/valid_id')->assertNotFound();
    }

    public function test_legacy_migration_moves_known_fields_verifies_bytes_and_is_idempotent(): void
    {
        // Only authoritative registration references move; unrelated public assets and absent references survive.
        $buyer = $this->registeredBuyer();
        $legacy = 'valid_ids/buyers/legacy.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        $content = "%PDF-1.4\nLegacy fixture\n%%EOF";
        Storage::disk('public')->put($legacy, $content);
        Storage::disk('public')->put('products/unrelated.png', 'Public product fixture');
        $this->artisan('registration-documents:migrate', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        Storage::disk('public')->assertExists($legacy);
        $this->artisan('registration-documents:migrate')->assertSuccessful();
        $path = $buyer->buyer->fresh()->valid_id_path;
        $this->assertSame($buyer->id.'/valid_id/legacy.pdf', $path);
        $this->assertSame($content, Storage::disk('registration_documents')->get($path));
        Storage::disk('public')->assertMissing($legacy);
        Storage::disk('public')->assertExists('products/unrelated.png');
        $this->artisan('registration-documents:migrate')->assertSuccessful();
        $this->assertSame($path, $buyer->buyer->fresh()->valid_id_path);
        $this->actingAs($this->admin(), 'web')->get('/admin/registration/'.$buyer->id.'/documents/valid_id')
            ->assertOk();
    }

    public function test_legacy_migration_reports_missing_invalid_and_duplicate_references_without_corruption(): void
    {
        // Missing files, path traversal and ambiguous shared legacy references require operator investigation.
        $buyer = $this->registeredBuyer();
        foreach (['valid_ids/buyers/missing.pdf', '../outside.pdf', 'avatars/unrelated.pdf'] as $path) {
            $buyer->buyer->update(['valid_id_path' => $path]);
            $this->artisan('registration-documents:migrate')->assertFailed();
            $this->assertSame($path, $buyer->buyer->fresh()->valid_id_path);
        }
        $input = $this->buyerInput();
        $input['email'] = 'duplicate@example.test';
        $this->post(route('register.store'), $input)->assertSessionHasNoErrors();
        $other = User::where('email', $input['email'])->firstOrFail();
        $legacy = 'valid_ids/buyers/shared.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        $other->buyer->update(['valid_id_path' => $legacy]);
        Storage::disk('public')->put($legacy, 'Shared fixture');
        $this->artisan('registration-documents:migrate')->assertFailed();
        Storage::disk('public')->assertExists($legacy);
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        $this->assertSame($legacy, $other->buyer->fresh()->valid_id_path);
    }

    public function test_failed_private_write_preserves_public_source_and_database_reference(): void
    {
        // A failed destination write must leave the only copy and authoritative row unchanged.
        $buyer = $this->registeredBuyer();
        $legacy = 'valid_ids/buyers/failure.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        Storage::disk('public')->put($legacy, 'Only copy');
        // Keep the fake public boundary intact while injecting a private write failure.
        $public = Storage::disk('public');
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->andReturn(false);
        $disk->shouldReceive('put')->andReturn(false);
        Storage::partialMock()->shouldReceive('disk')->with('registration_documents')->andReturn($disk);
        Storage::shouldReceive('disk')->with('public')->andReturn($public);
        $this->artisan('registration-documents:migrate')->assertFailed();
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        Storage::disk('public')->assertExists($legacy);
    }

    public function test_migration_verification_and_destination_conflict_preserve_the_source(): void
    {
        // Byte mismatch or an existing conflicting private file must never remove the only valid public copy.
        $buyer = $this->registeredBuyer();
        $legacy = 'valid_ids/buyers/conflict.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        $destination = $buyer->id.'/valid_id/conflict.pdf';
        Storage::disk('public')->put($legacy, 'Original');
        Storage::disk('registration_documents')->put($destination, 'Different');
        $this->artisan('registration-documents:migrate')->assertFailed();
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        Storage::disk('public')->assertExists($legacy);
        $this->assertSame('Different', Storage::disk('registration_documents')->get($destination));

        // A storage backend claiming write success still has to return identical bytes before row update or deletion.
        $public = Storage::disk('public');
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->andReturn(false);
        $disk->shouldReceive('put')->andReturn(true);
        $disk->shouldReceive('get')->andReturn('Corrupted');
        Storage::partialMock()->shouldReceive('disk')->with('registration_documents')->andReturn($disk);
        Storage::shouldReceive('disk')->with('public')->andReturn($public);
        $this->artisan('registration-documents:migrate')->assertFailed();
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        $public->assertExists($legacy);
    }

    public function test_failed_public_cleanup_keeps_verified_private_reference_and_rerun_finishes(): void
    {
        // Public deletion failure preserves both copies and the committed private reference for safe retry.
        $buyer = $this->registeredBuyer();
        $legacy = 'valid_ids/buyers/retry.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        $public = Storage::disk('public');
        $private = Storage::disk('registration_documents');
        // Retain the real fake-storage manager so the retry can restore the boundary after failure injection.
        $manager = Storage::getFacadeRoot();
        $public->put($legacy, 'Retry content');
        $broken = Mockery::mock(FilesystemAdapter::class);
        $broken->shouldReceive('exists')->with($legacy)->andReturn(true);
        $broken->shouldReceive('get')->with($legacy)->andReturn('Retry content');
        $broken->shouldReceive('delete')->with($legacy)->andReturn(false);
        Storage::partialMock()->shouldReceive('disk')->with('public')->andReturn($broken);
        Storage::shouldReceive('disk')->with('registration_documents')->andReturn($private);
        $this->artisan('registration-documents:migrate')->assertFailed();
        $path = $buyer->buyer->fresh()->valid_id_path;
        $this->assertSame($buyer->id.'/valid_id/retry.pdf', $path);
        $this->assertSame('Retry content', $private->get($path));
        $public->assertExists($legacy);

        // Retry uses the same record/slot-derived legacy location, with no arbitrary storage scan.
        Storage::swap($manager);
        Storage::set('public', $public);
        Storage::set('registration_documents', $private);
        $this->artisan('registration-documents:migrate')->assertSuccessful();
        $public->assertMissing($legacy);
        $this->assertSame($path, $buyer->buyer->fresh()->valid_id_path);
    }

    public function test_all_role_slots_remain_reviewable_and_cross_type_references_fail_closed(): void
    {
        // Seller and Logistics retain every existing review document while slots cannot borrow another type's file.
        $input = $this->buyerInput();
        $input['email'] = 'seller@example.test';
        $input['business_name'] = 'Test Store';
        $input['business_category'] = config('shophop_categories.0.name');
        $input['business_permit'] = UploadedFile::fake()->create('permit.pdf', 1, 'application/pdf');
        $this->post(route('seller.register.store'), $input)->assertSessionHasNoErrors();
        $this->post(route('logistics.register.store'), $this->logisticsInput())->assertSessionHasNoErrors();
        $seller = User::where('email', 'seller@example.test')->firstOrFail();
        $seller->forceFill(['email_verified_at' => now()])->save();
        $logistics = User::where('email', 'logistics@example.test')->firstOrFail();
        $this->actingAs($this->admin(), 'web');
        foreach ([[$seller, ['valid_id', 'business_permit']], [$logistics, ['rep_valid_id', 'business_permit', 'accreditation_docs', 'agreement_signature']]] as [$user, $slots]) {
            $detail = $this->get(route('admin.registrations.show', $user))->assertOk();
            foreach ($slots as $i => $slot) {
                $url = '/admin/registration/'.$user->id.'/documents/'.$slot;
                $detail->assertJsonPath('documents.'.$i.'.url', url($url));
                $this->get($url)->assertOk();
            }
            $detail->assertDontSee('/storage/', false);
        }
        $logistics->logisticsPartner->update(['business_permit_path' => $seller->seller->business_permit_path]);
        $this->get('/admin/registration/'.$logistics->id.'/documents/business_permit')->assertNotFound();
        $this->get('/admin/registration/'.$seller->id.'/documents/rep_valid_id')->assertNotFound();
        $seller->seller->update(['valid_id_path' => $seller->seller->business_permit_path]);
        $this->get('/admin/registration/'.$seller->id.'/documents/valid_id')->assertNotFound();
    }

    public function test_existing_document_validation_and_optional_accreditation_are_preserved(): void
    {
        // Required/type/size constraints still reject invalid IDs, while optional Logistics accreditation stays optional.
        $input = $this->buyerInput();
        unset($input['valid_id']);
        $this->post(route('register.store'), $input)->assertSessionHasErrors('valid_id');
        $input['valid_id'] = UploadedFile::fake()->create('unsafe.html', 1, 'text/html');
        $this->post(route('register.store'), $input)->assertSessionHasErrors('valid_id');
        $input['valid_id'] = UploadedFile::fake()->create('huge.pdf', 5121, 'application/pdf');
        $this->post(route('register.store'), $input)->assertSessionHasErrors('valid_id');
        $this->assertSame(0, User::count());
        Storage::disk('public')->assertDirectoryEmpty('/');
        Storage::disk('registration_documents')->assertDirectoryEmpty('/');
        $input = $this->logisticsInput();
        unset($input['accreditation_docs']);
        $this->post(route('logistics.register.store'), $input)->assertSessionHasNoErrors();
        $user = User::where('email', $input['email'])->firstOrFail();
        $this->assertNull($user->logisticsPartner->accreditation_docs_path);
        $this->actingAs($this->admin(), 'web')->get(route('admin.registrations.show', $user))
            ->assertJsonPath('documents.2.status', 'missing')->assertJsonPath('documents.2.url', null);
    }

    public function test_failed_database_update_retains_source_and_retry_adopts_verified_copy(): void
    {
        // A committed reference is required before public removal; DB failure leaves the legacy path and source intact.
        $buyer = $this->registeredBuyer();
        $legacy = 'valid_ids/buyers/dbfailure.pdf';
        $buyer->buyer->update(['valid_id_path' => $legacy]);
        Storage::disk('public')->put($legacy, 'Original DB failure fixture');
        DB::unprepared("CREATE TRIGGER reject_document_update BEFORE UPDATE OF valid_id_path ON buyers BEGIN SELECT RAISE(ABORT, 'Fixture write failure'); END");
        $this->artisan('registration-documents:migrate')->assertFailed();
        $this->assertSame($legacy, $buyer->buyer->fresh()->valid_id_path);
        Storage::disk('public')->assertExists($legacy);
        $this->assertSame('Original DB failure fixture', Storage::disk('registration_documents')->get($buyer->id.'/valid_id/dbfailure.pdf'));
        DB::unprepared('DROP TRIGGER reject_document_update');
        $this->artisan('registration-documents:migrate')->assertSuccessful();
        Storage::disk('public')->assertMissing($legacy);
        $this->assertSame($buyer->id.'/valid_id/dbfailure.pdf', $buyer->buyer->fresh()->valid_id_path);
    }

    public function test_legacy_migration_covers_every_seller_and_logistics_document_column(): void
    {
        // All six non-Buyer columns migrate through their exact historical directories, including optional accreditation.
        $input = $this->buyerInput();
        $input['email'] = 'seller@example.test';
        $input['business_name'] = 'Test Store';
        $input['business_category'] = config('shophop_categories.0.name');
        $input['business_permit'] = UploadedFile::fake()->create('permit.pdf', 1, 'application/pdf');
        $this->post(route('seller.register.store'), $input)->assertSessionHasNoErrors();
        $this->post(route('logistics.register.store'), $this->logisticsInput())->assertSessionHasNoErrors();
        $seller = User::where('email', 'seller@example.test')->firstOrFail();
        $logistics = User::where('email', 'logistics@example.test')->firstOrFail();
        $references = [
            [$seller, $seller->seller, 'valid_id', 'valid_ids/sellers/identity.pdf'],
            [$seller, $seller->seller, 'business_permit', 'business_permits/sellers/permit.pdf'],
            [$logistics, $logistics->logisticsPartner, 'rep_valid_id', 'valid_ids/logistics/representative.pdf'],
            [$logistics, $logistics->logisticsPartner, 'business_permit', 'business_permits/logistics/permit.pdf'],
            [$logistics, $logistics->logisticsPartner, 'accreditation_docs', 'accreditation_docs/accreditation.pdf'],
            [$logistics, $logistics->logisticsPartner, 'agreement_signature', 'signatures/signature.pdf'],
        ];
        foreach ($references as [$user, $profile, $slot, $path]) {
            $profile->update([$slot.'_path' => $path]);
            Storage::disk('public')->put($path, 'Fixture '.$slot);
        }
        $this->artisan('registration-documents:migrate')->assertSuccessful();
        foreach ($references as [$user, $profile, $slot, $path]) {
            $destination = $user->id.'/'.$slot.'/'.basename($path);
            $this->assertSame($destination, $profile->fresh()->{$slot.'_path'});
            $this->assertSame('Fixture '.$slot, Storage::disk('registration_documents')->get($destination));
            Storage::disk('public')->assertMissing($path);
        }
        $this->artisan('registration-documents:migrate')->assertSuccessful();
    }

    private function registeredBuyer(): User
    {
        // Email verification makes this disposable applicant eligible for the canonical registration queue.
        $this->post(route('register.store'), $this->buyerInput())->assertSessionHasNoErrors();
        $user = User::where('email', 'buyer@example.test')->firstOrFail();
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    private function admin(string $role = 'admin', string $status = 'approved'): User
    {
        // Persisted native identities exercise middleware rather than bypassing authorization in the test.
        $user = new User();
        $user->forceFill(['email' => uniqid($role).'@example.test', 'password' => 'Password123',
            'account_type' => $role, 'status' => $status, 'email_verified_at' => now()])->save();
        return $user;
    }

    private function logisticsInput(): array
    {
        // Registration fixtures include no personal documents and preserve the existing coverage/OTP inputs.
        return [
            'terms_agree' => '1', 'agreement_rep_name' => 'Test Representative', 'agreement_date' => '2026-01-01',
            'company_name' => 'Test Logistics', 'business_registration_no' => 'REG-1',
            'line_of_business' => 'motorcycle_courier', 'rep_id_number' => 'ID-1',
            'rep_sex' => 'male', 'rep_birthday' => '1990-01-01', 'email' => 'logistics@example.test',
            'contact_no' => '09123456789', 'region' => 'Region', 'province' => 'Province',
            'municipality' => 'Municipality', 'barangay' => 'Barangay', 'street_no' => '1', 'unit_no' => '1',
            'otp_code' => '123456', 'password' => 'Password123', 'password_confirmation' => 'Password123',
            'coverage_areas' => ['Province'],
            'agreement_signature' => UploadedFile::fake()->create('signature.pdf', 1, 'application/pdf'),
            'rep_valid_id' => UploadedFile::fake()->create('id.pdf', 1, 'application/pdf'),
            'business_permit' => UploadedFile::fake()->create('permit.pdf', 1, 'application/pdf'),
            'accreditation_docs' => UploadedFile::fake()->create('accreditation.pdf', 1, 'application/pdf'),
        ];
    }

    private function buyerInput(): array
    {
        // A tiny generated PDF exercises existing validation without genuine identity content.
        return [
            'first_name' => 'Test', 'last_name' => 'Buyer', 'sex' => 'Male',
            'email' => 'buyer@example.test', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => 'P1', 'province_name' => 'Province',
            'municipality_code' => 'M1', 'municipality_name' => 'Municipality',
            'barangay_code' => 'B1', 'barangay_name' => 'Barangay', 'street_address' => '1 Test Street',
            'valid_id' => UploadedFile::fake()->create('id.pdf', 1, 'application/pdf'),
            'password' => 'Password123', 'password_confirmation' => 'Password123', 'terms' => '1',
        ];
    }
}
