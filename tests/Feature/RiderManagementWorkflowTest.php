<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Notifications\RiderApplicationDecisionNotification;
use App\Services\ParcelScanService;
use App\Services\RiderBatchService;
use App\Services\RiderCustodyScanService;
use App\Services\RiderManagementAccess;
use App\Services\RiderManagementService;
use App\Services\RiderRegistrationDocuments;
use App\Services\RiderServiceAreaService;
use App\Services\RiderWorkAreaDirectory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class RiderManagementWorkflowTest extends TestCase
{
    use SellerWorkspaceFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Cache::flush();
        Http::preventStrayRequests();
        Http::fake([
            'https://psgc.gitlab.io/api/provinces/' => Http::response([['code' => '043400000', 'name' => 'Laguna']], 200),
            'https://psgc.gitlab.io/api/cities-municipalities/' => Http::response([['code' => '043405000', 'name' => 'City of Calamba', 'provinceCode' => '043400000', 'regionCode' => '040000000']], 200),
            'https://psgc.gitlab.io/api/cities-municipalities/043405000/barangays/' => Http::response([
                ['code' => '043405001', 'name' => 'Real'], ['code' => '043405002', 'name' => 'Mayapa']], 200),
        ]);
    }

    private function network(): array
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $owner = $this->user('logistics');
        $partner = DB::table('logistics_partners')->insertGetId(['user_id' => $owner->id,
            'agreement_rep_name' => 'Test', 'agreement_date' => '2026-11-08', 'agreement_signature_path' => 'test.jpg',
            'company_name' => 'Batch Test Logistics', 'business_registration_no' => uniqid('BATCH'),
            'line_of_business' => 'motorcycle_courier', 'rep_valid_id_path' => 'test.jpg', 'rep_id_number' => 'Test',
            'rep_sex' => 'male', 'rep_birthday' => '1990-01-01', 'contact_no' => '09123456789', 'region' => 'IV-A',
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'business_permit_path' => 'test.jpg']);
        $user = $this->user('branch');
        $branch = SortingCenter::create(['logistics_partner_id' => $partner, 'user_id' => $user->id, 'name' => 'Batch Branch',
            'code' => uniqid('BR'), 'type' => 'station_hub', 'status' => 'active', 'region' => 'IV-A', 'province' => 'Laguna',
            'municipality' => 'Calamba', 'barangay' => 'Real', 'street_no' => '1', 'unit_no' => '1', 'contact_no' => '09123456789']);
        $rider = Rider::create(['logistics_partner_id' => $partner, 'sorting_center_id' => $branch->id, 'name' => 'Batch Rider',
            'email' => uniqid('rider').'@example.test', 'password' => 'TestPassword123!', 'email_verified_at' => now(),
            'vehicle_type' => 'Motorcycle', 'plate_number' => 'TEST-123', 'status' => 'active', 'availability_status' => 'available']);
        $area = OperationalServiceArea::create(['logistics_partner_id' => $partner, 'sorting_center_id' => $branch->id,
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => 'Real', 'service_type' => 'both',
            'location_key' => RiderServiceAreaService::locationKey('Laguna', 'Calamba', 'Real', 'both')]);
        $rider->operationalServiceAreas()->attach($area);
        $service = app(RiderBatchService::class);
        $service->configure($branch, $rider->id, ['max_weight_kg' => 100, 'max_volume_m3' => 2, 'max_stops' => 30,
            'max_duration_minutes' => 240, 'max_parcels' => null], 'user', $user->id);

        $rider->update(['or_cr_path' => 'test/orcr.pdf', 'id_or_license_path' => 'test/id.pdf']);

        return compact('owner', 'partner', 'user', 'branch', 'rider', 'service');
    }

    private function scope(array $n, string $role = 'main'): array
    {
        return app(RiderManagementAccess::class)->actor($n[$role === 'main' ? 'owner' : 'user']);
    }

    private function key(array $n): string
    {
        return array_key_first(app(RiderWorkAreaDirectory::class)->coverage($n['branch']));
    }

    private function applicant(array $n): Rider
    {
        $r = Rider::create(['logistics_partner_id' => $n['partner'], 'name' => 'Applicant', 'email' => uniqid('app').'@example.test',
            'password' => 'TestPassword123!', 'email_verified_at' => now(), 'vehicle_type' => 'Motorcycle', 'plate_number' => 'NEW123',
            'or_cr_path' => 'test/orcr.pdf', 'id_or_license_path' => 'test/id.pdf', 'status' => 'pending', 'availability_status' => 'offline']);
        app(RiderManagementService::class)->registerPreferences($r, $n['branch']->id, [$this->key($n)]);

        return $r->fresh();
    }

    private function invalid(callable $call): void
    {
        try {
            $call();
            $this->fail('Expected lifecycle validation rejection.');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }

    public function test_main_hub_approval_does_not_activate_and_branch_controls_activation(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $this->assertSame('pending', $r->fresh()->status);
        $this->assertSame('approved', $r->fresh()->application_status);
        $this->assertSame('pending_activation', $r->fresh()->operational_status);
        $this->assertFalse($r->fresh()->canOperate());
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->assertTrue($r->fresh()->canOperate());
        $this->assertSame('both', $r->fresh()->operationalServiceAreas()->first()->service_type);
        $this->assertSame(3, DB::table('rider_management_events')->where('rider_id', $r->id)->count());
    }

    public function test_six_unique_slots_are_shared_across_branches_and_tasks_with_individual_override(): void
    {
        $n = $this->network();
        $s = app(RiderManagementService::class);
        $key = $this->key($n);
        $branch2 = $n['branch']->replicate();
        $branch2->user_id = null;
        $branch2->code = uniqid('SECOND');
        $branch2->name = 'Second Branch';
        $branch2->save();
        $area = OperationalServiceArea::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $branch2->id,
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => null, 'service_type' => 'pickup',
            'location_key' => RiderServiceAreaService::locationKey('Laguna', 'Calamba', null, 'pickup')]);
        $area2 = OperationalServiceArea::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $branch2->id,
            'province' => 'Laguna', 'municipality' => 'Calamba', 'barangay' => null, 'service_type' => 'delivery',
            'location_key' => RiderServiceAreaService::locationKey('Laguna', 'Calamba', null, 'delivery')]);
        for ($i = 0; $i < 5; $i++) {
            $r = $n['rider']->replicate();
            $r->email = uniqid('slot').'@example.test';
            $r->sorting_center_id = $branch2->id;
            $r->save();
            $r->operationalServiceAreas()->attach([$area->id, $area2->id]);
        }
        $r = $this->applicant($n);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $place = app(RiderWorkAreaDirectory::class)->coverage($n['branch'])[$key];
        $this->assertSame(6, $s->capacity($n['partner'], $place)['active_count']);
        $this->invalid(fn () => $s->activate($this->scope($n, 'branch'), $r->id, [$key]));
        $this->invalid(fn () => $s->setCapacity($this->scope($n), $r->id, ['action' => 'grant', 'location_key' => $key, 'reason' => '']));
        $s->setCapacity($this->scope($n), $r->id, ['action' => 'grant', 'location_key' => $key, 'reason' => 'Peak-season additional approved rider']);
        $s->activate($this->scope($n, 'branch'), $r->id, [$key]);
        $this->assertSame(7, $s->capacity($n['partner'], $place)['active_count']);
        $next = $this->applicant($n);
        $s->review($this->scope($n), $next->id, 'approve', ['branch_id' => $n['branch']->id]);
        $this->invalid(fn () => $s->activate($this->scope($n, 'branch'), $next->id, [$key]));
    }

    public function test_company_scoping_and_province_exclusion_apply_to_actual_hub_accounts(): void
    {
        $n = $this->network();
        $foreign = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        try {
            $s->review($this->scope($foreign), $r->id, 'approve', ['branch_id' => $foreign['branch']->id]);
            $this->fail('Foreign application leaked.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }
        $n['branch']->update(['type' => 'province_hub']);
        try {
            $this->scope($n, 'branch');
            $this->fail('Province Hub must not review riders.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $n['branch']->update(['is_main' => true]);
        $this->assertSame('main', $this->scope($n, 'branch')['role']);
        $staff = BranchStaff::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'name' => 'Main Manager', 'email' => uniqid('staff').'@example.test', 'password' => 'TestPassword123!', 'role' => 'manager', 'status' => 'active']);
        $this->assertSame('main', app(RiderManagementAccess::class)->actor($staff)['role']);
        $staff->update(['role' => 'scanner']);
        try {
            app(RiderManagementAccess::class)->actor($staff);
            $this->fail('Scanner gained management permission.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_document_request_resubmission_and_changed_branch_confirmation(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'needs_documents', ['reason' => 'Please upload a legible registration', 'documents' => ['or_cr']]);
        $this->assertSame('needs_documents', $r->fresh()->application_status);
        $this->invalid(fn () => $s->resubmitDocuments($r, []));
        $s->resubmitDocuments($r, ['or_cr' => 'test/new-orcr.pdf']);
        $this->assertSame('pending_review', $r->fresh()->application_status);
        $r->lifecycleProfile()->update(['preferred_branch_id' => null]);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $this->invalid(fn () => $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]));
        $s->confirmBranch($r, $n['branch']->id, [$this->key($n)]);
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->assertTrue($r->fresh()->canOperate());
    }

    public function test_transfer_requires_clear_work_and_new_branch_activation(): void
    {
        $n = $this->network();
        $s = app(RiderManagementService::class);
        $r = $n['rider'];
        $to = $n['branch']->replicate();
        $to->user_id = null;
        $to->code = uniqid('DEST');
        $to->name = 'Destination';
        $to->save();
        $req = $s->requestTransfer($this->scope($n, 'branch'), $r->id, $to->id, 'Rider relocation');
        $this->invalid(fn () => $s->requestTransfer($this->scope($n, 'branch'), $r->id, $to->id, 'Duplicate'));
        RiderBatch::create(['code' => uniqid('RB'), 'logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'rider_id' => $r->id, 'type' => 'pickup', 'status' => 'queued', 'scheduled_start' => now()->addHour(), 'scheduled_end' => now()->addHours(2),
            'estimated_minutes' => 30, 'capacity_snapshot' => []]);
        $this->invalid(fn () => $s->decideTransfer($this->scope($n), $req->id, true, 'Approved relocation'));
        RiderBatch::where('rider_id', $r->id)->update(['status' => 'cancelled']);
        $s->decideTransfer($this->scope($n), $req->id, true, 'Approved relocation');
        $this->assertSame($to->id, $r->fresh()->sorting_center_id);
        $this->assertFalse($r->fresh()->canOperate());
        $this->assertSame(0, $r->operationalServiceAreas()->count());
        $this->assertTrue($r->fresh()->managementProfile()->assignment_confirmation_required);
        $this->assertNull($req->fresh()->pending_rider_id);
    }

    public function test_legacy_riders_stay_operational_without_profile_backfill(): void
    {
        $n = $this->network();
        $this->assertNull($n['rider']->managementProfile());
        $this->assertTrue($n['rider']->canOperate());
        $this->assertSame('approved', $n['rider']->application_status);
        $this->assertSame('active', $n['rider']->operational_status);
        $this->assertSame(0, DB::table('rider_lifecycle_profiles')->count());
    }

    public function test_management_pages_render_and_branch_cannot_review_or_use_main_actions(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $this->actingAs($n['owner'])->get(route('logistics.rider-management.index', ['rider' => $r->id]))
            ->assertOk()->assertSee('Approve for Branch activation');
        $this->actingAs($n['user'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'approve', 'branch_id' => $n['branch']->id])->assertForbidden();
        app(RiderManagementService::class)->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $this->actingAs($n['user'])->get(route('logistics.rider-management.index', ['rider' => $r->id, 'state' => 'pending_activation']))
            ->assertOk()->assertSee('Confirm coverage');
        $this->actingAs($n['owner'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'activate', 'coverage' => [$this->key($n)]])->assertForbidden();
        $this->actingAs($n['user'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'activate', 'coverage' => [$this->key($n)]])->assertOk();
    }

    public function test_application_only_token_is_blocked_from_operations_even_after_activation_until_exchange(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $login = $this->postJson('/api/mobile/login', ['email' => $r->email, 'password' => 'TestPassword123!'])
            ->assertOk()->assertJsonPath('status', 'rider_application');
        $token = $login->json('data.token');
        $this->assertNotEmpty($token);
        $this->withToken($token)->getJson('/api/mobile/rider/application')->assertOk()
            ->assertJsonPath('data.application_status', 'pending_review');
        $this->withToken($token)->getJson('/api/mobile/rider/batches')->assertForbidden();
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/mobile/rider/batches')->assertForbidden();
        $session = $this->withToken($token)->postJson('/api/mobile/rider/application/session')->assertOk();
        $this->assertNotSame($token, $session->json('data.token'));
        $this->app['auth']->forgetGuards();
        $this->withToken($session->json('data.token'))->getJson('/api/mobile/rider/batches')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => explode('|', $token)[0]]);
    }

    public function test_work_options_and_required_preferred_coverage_are_scoped_to_company(): void
    {
        $n = $this->network();
        $other = $this->network();
        DB::table('logistics_partners')->whereIn('id', [$n['partner'], $other['partner']])->update(['accepting_riders' => true]);
        $this->getJson('/api/mobile/rider/work-options?company_id='.$n['partner'])->assertOk()
            ->assertJsonCount(1, 'data.branches')->assertJsonPath('data.branches.0.id', $n['branch']->id);
        $this->getJson('/api/mobile/rider/work-options?company_id='.$n['partner'].'&branch_id='.$other['branch']->id)->assertNotFound();
        $this->postJson('/api/mobile/rider/register', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['preferred_branch_id', 'preferred_coverage']);
    }

    public function test_schedules_and_availability_are_branch_owned_and_audited(): void
    {
        $n = $this->network();
        $s = app(RiderManagementService::class);
        $r = $n['rider'];
        $s->schedule($this->scope($n, 'branch'), $r->id, ['starts_at' => now()->addHour()->toIso8601String(), 'ends_at' => now()->addHours(3)->toIso8601String()]);
        $this->invalid(fn () => $s->schedule($this->scope($n, 'branch'), $r->id, ['starts_at' => now()->addHours(2)->toIso8601String(), 'ends_at' => now()->addHours(4)->toIso8601String()]));
        $s->assertSchedule($r, now()->addHour(), now()->addHours(2));
        $this->invalid(fn () => $s->assertSchedule($r, now()->addHours(4), now()->addHours(5)));
        $s->availability($this->scope($n, 'branch'), $r->id, 'on_break');
        $this->assertSame('on_break', $r->fresh()->availabilityState());
        $this->assertSame('offline', $r->fresh()->availability_status);
        $s->availability($this->scope($n, 'branch'), $r->id, 'online');
        $this->assertSame('available', $r->fresh()->availability_status);
        $this->assertDatabaseHas('rider_management_events', ['rider_id' => $r->id, 'event' => 'availability_changed']);
    }

    public function test_real_registration_verification_approval_activation_and_mobile_sync_flow(): void
    {
        Notification::fake();
        Storage::fake(RiderRegistrationDocuments::DISK);
        $n = $this->network();
        DB::table('logistics_partners')->where('id', $n['partner'])->update(['accepting_riders' => true]);
        $email = uniqid('registered').'@example.test';
        $data = ['logistics_partner_id' => $n['partner'], 'preferred_branch_id' => $n['branch']->id, 'preferred_coverage' => [$this->key($n)],
            'first_name' => 'Test', 'last_name' => 'Applicant', 'sex' => 'Male', 'email' => $email, 'contact_no' => '09123456789', 'birthday' => '1998-01-01',
            'province_code' => '043400000', 'province_name' => 'Laguna', 'municipality_code' => '043405000', 'municipality_name' => 'City of Calamba',
            'barangay_code' => '043405001', 'barangay_name' => 'Real', 'street_address' => '1 Home Street', 'vehicle_type' => 'Motorcycle', 'plate_number' => 'TEST-777',
            'or_cr' => UploadedFile::fake()->create('registration.pdf', 2, 'application/pdf'),
            'id_or_license' => UploadedFile::fake()->create('license.pdf', 2, 'application/pdf'),
            'password' => 'TestPassword123!', 'password_confirmation' => 'TestPassword123!', 'terms' => '1'];
        $this->postJson('/api/mobile/rider/register', $data)->assertCreated()->assertJsonPath('status', 'email_unverified');
        $r = Rider::where('email', $email)->firstOrFail();
        $this->assertNull($r->sorting_center_id);
        $this->assertSame($n['branch']->id, $r->managementProfile()->preferred_branch_id);
        Storage::disk(RiderRegistrationDocuments::DISK)->assertExists($r->or_cr_path);
        DB::table('rider_email_verification_codes')->where('rider_id', $r->id)->update(['code_hash' => Hash::make('123456')]);
        $this->postJson('/api/mobile/rider/verify-email', ['email' => $email, 'code' => '123456'])->assertOk();
        $this->postJson('/api/mobile/login', ['email' => $email, 'password' => 'TestPassword123!'])
            ->assertOk()->assertJsonPath('status', 'rider_application');
        $this->actingAs($n['owner'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'approve', 'branch_id' => $n['branch']->id])->assertOk()->assertJsonPath('data.rider.can_operate', false);
        $this->actingAs($n['user'])->get(route('logistics.rider-management.index', ['state' => 'pending_activation']))
            ->assertOk()->assertSee($email);
        $this->actingAs($n['user'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'activate', 'coverage' => [$this->key($n)]])->assertOk()->assertJsonPath('data.rider.can_operate', true);
        $this->actingAs($n['owner'])->getJson(route('logistics.rider-management.index'))
            ->assertOk()->assertJsonPath('data.active_by_branch.'.$n['branch']->id, 2);
        Sanctum::actingAs($r->fresh(), ['*']);
        $this->getJson('/api/mobile/rider/application')->assertOk()
            ->assertJsonPath('data.operational_status', 'active')->assertJsonPath('data.official_coverage.0.label', 'Real, Calamba, Laguna');
        $this->getJson('/api/mobile/rider/batches')->assertOk();
        Storage::fake('local');
        $r = $r->fresh();
        $n['service']->configure($n['branch'], $r->id, ['max_weight_kg' => 100, 'max_volume_m3' => 2,
            'max_stops' => 20, 'max_duration_minutes' => 120], 'user', $n['user']->id);
        $orders = [];
        for ($i = 0; $i < 2; $i++) {
            $seller = $this->seller();
            $buyer = $this->buyer();
            $order = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id,
                'logistics_partner_id' => $n['partner'], 'status' => Order::STATUS_READY_FOR_PICKUP,
                'total_amount' => 100, 'payment_method' => 'cod', 'shipping_method' => 'standard',
                'delivery_name' => 'Buyer '.$buyer->id, 'delivery_phone' => '09123456789', 'delivery_address' => '1 Street, Real, Calamba, Laguna',
                'delivery_province_name' => 'Laguna', 'delivery_municipality_name' => 'Calamba', 'delivery_barangay_name' => 'Real']);
            $order->update(['tracking_code' => 'SHP-20261108-'.str_pad($order->id, 6, '0', STR_PAD_LEFT)]);
            PickupRequest::create(['order_id' => $order->id, 'logistics_partner_id' => $n['partner'],
                'origin_sorting_center_id' => $n['branch']->id, 'status' => 'requested', 'requested_at' => now(),
                'origin_province_name' => 'Laguna', 'origin_municipality_name' => 'Calamba', 'origin_barangay_name' => 'Real', 'origin_street' => '1 Seller Street']);
            $orders[] = $order;
        }
        $payload = ['rider_id' => $r->id, 'type' => 'pickup', 'scheduled_start' => now()->subMinute()->toIso8601String(),
            'scheduled_end' => now()->addHour()->toIso8601String(), 'estimated_minutes' => 30,
            'parcels' => array_map(fn ($o) => ['order_id' => $o->id, 'weight_kg' => 1, 'volume_m3' => 0.01], $orders)];
        $pickup = $this->actingAs($n['user'], 'web')->postJson(route('branch.rider-batches.store'), $payload)
            ->assertCreated()->json('data.batch');
        $this->postJson(route('branch.rider-batches.transition', $pickup['id']), ['action' => 'dispatch'])->assertOk();
        foreach ($orders as $order) {
            app(RiderCustodyScanService::class)->confirm($r, $order->tracking_code, RiderCustodyScanService::SELLER_PICKUP);
            app(ParcelScanService::class)->receive($n['partner'], $n['branch']->id, $n['user']->id, $order->tracking_code);
        }
        $this->postJson(route('branch.rider-batches.transition', $pickup['id']), ['action' => 'complete'])->assertOk();
        $coverage = LogisticsCoverageArea::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'area_name' => 'Laguna', 'area_type' => 'province', 'cities' => 'Calamba']);
        foreach ($orders as $order) {
            $this->actingAs($n['owner'], 'web')->post(route('logistics.deliveries.sort', $order->delivery->id),
                ['destination_area_id' => $coverage->id])->assertRedirect();
        }
        $payload['type'] = 'delivery';
        $deliveryBatch = $this->actingAs($n['user'], 'web')->postJson(route('branch.rider-batches.store'), $payload)
            ->assertCreated()->json('data.batch');
        $this->postJson(route('branch.rider-batches.transition', $deliveryBatch['id']), ['action' => 'dispatch'])->assertOk();
        foreach ($orders as $order) {
            app(RiderCustodyScanService::class)->confirm($r, $order->tracking_code, RiderCustodyScanService::DELIVERY_HANDOFF);
            Sanctum::actingAs($r->fresh(), ['*']);
            $this->postJson('/api/mobile/rider/assignments/'.$order->delivery->id.'/complete', [
                'cash_collected' => true, 'proof' => UploadedFile::fake()->create('proof.jpg', 2, 'image/jpeg')])->assertOk();
            $this->assertNotNull($order->delivery->fresh()->proof_path);
        }
        $this->actingAs($n['user'], 'web')->postJson(route('branch.rider-batches.transition', $deliveryBatch['id']), ['action' => 'complete'])->assertOk();
        $this->assertSame(2, RiderBatch::where('rider_id', $r->id)->where('status', 'completed')->count());
        $this->assertSame(2, CodSettlement::where('collected_by_rider_id', $r->id)->count());
        $this->actingAs($n['owner'], 'web')->get(route('logistics.rider-batches.index'))->assertOk()
            ->assertSee($pickup['code'])->assertSee($deliveryBatch['code']);

    }

    public function test_document_request_cannot_be_bypassed_and_uploaded_replacements_are_private(): void
    {
        Storage::fake(RiderRegistrationDocuments::DISK);
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'needs_documents', ['documents' => ['id_or_license'], 'reason' => 'Upload a clearer license']);
        $this->invalid(fn () => $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]));
        $this->invalid(fn () => $s->review($this->scope($n), $r->id, 'under_review', []));
        Sanctum::actingAs($r, ['rider:application']);
        $this->postJson('/api/mobile/rider/application/documents', [
            'id_or_license' => UploadedFile::fake()->create('new-license.pdf', 2, 'application/pdf')])
            ->assertOk()->assertJsonPath('data.application_status', 'pending_review');
        $path = $r->fresh()->id_or_license_path;
        $this->assertStringStartsWith('riders/'.$r->id.'/id_or_license/', $path);
        $this->assertStringNotContainsString($path, json_encode($s->applicationData($r)));
        Storage::disk(RiderRegistrationDocuments::DISK)->assertExists($path);
    }

    public function test_transfer_blocks_unremitted_cod_and_legacy_parcel_custody(): void
    {
        $n = $this->network();
        $s = app(RiderManagementService::class);
        $r = $n['rider'];
        $to = $n['branch']->replicate();
        $to->user_id = null;
        $to->code = uniqid('TO');
        $to->save();
        $request = $s->requestTransfer($this->scope($n, 'branch'), $r->id, $to->id, 'Safe relocation');
        $seller = $this->seller();
        $buyer = $this->buyer();
        $o = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id,
            'logistics_partner_id' => $n['partner'], 'status' => 'to_receive', 'total_amount' => 100, 'payment_method' => 'cod', 'shipping_method' => 'standard']);
        $d = Delivery::create(['order_id' => $o->id, 'logistics_partner_id' => $n['partner'],
            'rider_id' => $r->id, 'assigned_at' => now(), 'status' => Delivery::OUT_FOR_DELIVERY]);
        $this->invalid(fn () => $s->decideTransfer($this->scope($n), $request->id, true, 'Ready'));
        $d->update(['status' => Delivery::DELIVERED]);
        $cod = CodSettlement::create(['order_id' => $o->id, 'delivery_id' => $d->id, 'logistics_partner_id' => $n['partner'],
            'expected_amount' => 100, 'collected_amount' => 100, 'status' => 'collected', 'collected_by_rider_id' => $r->id, 'collected_at' => now()]);
        $this->invalid(fn () => $s->decideTransfer($this->scope($n), $request->id, true, 'Ready'));
        $cod->update(['status' => 'remitted', 'remitted_by_rider_id' => $r->id, 'remitted_at' => now()]);
        $s->decideTransfer($this->scope($n), $request->id, true, 'Cash and custody resolved');
        $this->assertSame($to->id, $r->fresh()->sorting_center_id);
        $this->assertSame('delivered', $d->fresh()->status);
        $this->assertSame('remitted', $cod->fresh()->status);
    }

    public function test_capacity_limit_configuration_and_branch_suspension_permissions(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $s->setCapacity($this->scope($n), $r->id, ['action' => 'limit', 'location_key' => $this->key($n), 'rider_limit' => 1, 'reason' => 'Reduced staffing limit']);
        $this->invalid(fn () => $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]));
        $s->setCapacity($this->scope($n), $r->id, ['action' => 'limit', 'location_key' => $this->key($n), 'rider_limit' => 3, 'reason' => 'Increased authorized staffing']);
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $s->setOperationalStatus($this->scope($n), $r->id, 'suspended', 'Compliance review');
        $this->invalid(fn () => $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]));
        $this->invalid(fn () => $s->setOperationalStatus($this->scope($n, 'branch'), $r->id, 'inactive', 'Bypass'));
        $s->setOperationalStatus($this->scope($n), $r->id, 'pending_activation', 'Review resolved');
        $this->assertFalse($r->fresh()->canOperate());
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->assertTrue($r->fresh()->canOperate());
    }

    public function test_legacy_active_riders_can_reconfirm_coverage_without_rebuilding_onboarding(): void
    {
        $n = $this->network();
        $r = $n['rider'];
        $r->update(['or_cr_path' => null, 'id_or_license_path' => null]);
        app(RiderManagementService::class)->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->assertTrue($r->fresh()->canOperate());
        $this->assertTrue($r->fresh()->managementProfile()->legacy_onboarding);
    }

    public function test_expired_configured_shift_does_not_silently_allow_unscheduled_batches(): void
    {
        $n = $this->network();
        $s = app(RiderManagementService::class);
        $s->schedule($this->scope($n, 'branch'), $n['rider']->id, [
            'starts_at' => now()->addHour()->toIso8601String(), 'ends_at' => now()->addHours(2)->toIso8601String()]);
        $this->travel(3)->hours();
        $this->invalid(fn () => $s->assertSchedule($n['rider'], now(), now()->addHour()));
    }

    public function test_live_directory_never_leaks_other_company_and_old_approval_routes_cannot_activate(): void
    {
        $n = $this->network();
        $other = $this->network();
        $r = $this->applicant($n);
        $this->actingAs($n['owner'])->getJson(route('logistics.riders.index'))
            ->assertOk()->assertSee($r->email)->assertDontSee($other['rider']->email);
        $this->actingAs($n['owner'])->postJson(route('logistics.riders.approve', $r->id), ['branch_id' => $n['branch']->id])->assertRedirect();
        $this->assertFalse($r->fresh()->canOperate());
        $this->assertSame('pending_activation', $r->fresh()->operational_status);
        $this->actingAs($n['owner'])->postJson(route('logistics.riders.area', $r->id), ['sorting_center_id' => $other['branch']->id])
            ->assertUnprocessable();
        $this->assertSame($n['branch']->id, $r->fresh()->sorting_center_id);
    }

    public function test_main_suspension_preserves_cash_custody_and_allows_only_own_cash_return(): void
    {
        $n = $this->network();
        $r = $n['rider'];
        $s = app(RiderManagementService::class);
        $seller = $this->seller();
        $buyer = $this->buyer();
        $o = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id,
            'logistics_partner_id' => $n['partner'], 'status' => 'to_receive', 'total_amount' => 100, 'payment_method' => 'cod', 'shipping_method' => 'standard']);
        $d = Delivery::create(['order_id' => $o->id, 'logistics_partner_id' => $n['partner'],
            'rider_id' => $r->id, 'assigned_at' => now(), 'status' => 'delivered']);
        $cash = CodSettlement::create(['order_id' => $o->id, 'delivery_id' => $d->id, 'logistics_partner_id' => $n['partner'],
            'expected_amount' => 100, 'collected_amount' => 100, 'status' => 'collected', 'collected_by_rider_id' => $r->id, 'collected_at' => now()]);
        $s->setOperationalStatus($this->scope($n), $r->id, 'suspended', 'Immediate compliance hold');
        $this->assertSame('collected', $cash->fresh()->status);
        Sanctum::actingAs($r->fresh(), ['rider:application']);
        $this->getJson('/api/mobile/rider/application')->assertOk()->assertJsonPath('data.outstanding_cod.0.delivery_id', $d->id);
        $this->getJson('/api/mobile/rider/batches')->assertForbidden();
        $this->getJson('/api/mobile/rider/cod')->assertOk();
        $this->postJson('/api/mobile/rider/cod/'.$d->id.'/remit')->assertOk();
        $this->postJson('/api/mobile/rider/cod/'.$d->id.'/remit')->assertConflict();
        $this->assertSame('remitted', $cash->fresh()->status);
        $this->assertSame('delivered', $d->fresh()->status);
        $other = $this->network();
        Sanctum::actingAs($other['rider'], ['*']);
        $this->postJson('/api/mobile/rider/cod/'.$d->id.'/remit')->assertNotFound();
    }

    public function test_existing_approved_company_accounts_keep_their_administrative_access(): void
    {
        $n = $this->network();
        $n['owner']->update(['email_verified_at' => null]);
        $this->assertSame('main', $this->scope($n)['role']);
    }

    public function test_company_cannot_assign_daily_work_through_old_single_parcel_endpoints(): void
    {
        $n = $this->network();
        $this->actingAs($n['owner'])->postJson(route('logistics.deliveries.assign', 999), ['rider_id' => $n['rider']->id])
            ->assertUnprocessable()->assertJsonValidationErrors('rider');
        $this->actingAs($n['owner'])->postJson(route('logistics.deliveries.assign-delivery', 999), ['rider_id' => $n['rider']->id])
            ->assertUnprocessable()->assertJsonValidationErrors('rider');
    }

    public function test_rejection_and_transfer_rejection_keep_company_and_branch_ownership(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'reject', ['reason' => 'Documents do not meet company requirements']);
        $this->assertSame('rejected', $r->fresh()->application_status);
        $this->assertFalse($r->fresh()->canOperate());
        $this->actingAs($n['user'])->postJson(route('logistics.rider-management.action', $r->id),
            ['action' => 'activate', 'coverage' => [$this->key($n)]])->assertNotFound();
        $this->postJson('/api/mobile/login', ['email' => $r->email, 'password' => 'TestPassword123!'])
            ->assertOk()->assertJsonPath('status', 'rider_application');
        $to = $n['branch']->replicate();
        $to->user_id = null;
        $to->code = uniqid('REJECT');
        $to->save();
        $transfer = $s->requestTransfer($this->scope($n, 'branch'), $n['rider']->id, $to->id, 'Relocation request');
        $s->decideTransfer($this->scope($n), $transfer->id, false, 'Destination staffing does not require a transfer');
        $this->assertSame('rejected', $transfer->fresh()->status);
        $this->assertSame($n['branch']->id, $n['rider']->fresh()->sorting_center_id);
        $this->assertNull($transfer->fresh()->pending_rider_id);
    }

    public function test_old_pickup_coverage_shortcut_cannot_bypass_official_coverage_or_capacity(): void
    {
        $n = $this->network();
        $this->actingAs($n['owner'])->postJson(route('logistics.deliveries.pickup-coverage.enable', 999),
            ['rider_id' => $n['rider']->id, 'confirm_coverage' => true])->assertUnprocessable();
        $this->assertSame(1, $n['rider']->operationalServiceAreas()->count());
    }

    public function test_activation_reuses_existing_exact_coverage_instead_of_creating_alias_duplicates(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $existing = $n['rider']->operationalServiceAreas()->firstOrFail();
        $before = OperationalServiceArea::count();
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $this->assertSame($before, OperationalServiceArea::count());
        $this->assertSame($existing->id, $r->operationalServiceAreas()->firstOrFail()->id);
    }

    public function test_metro_manila_work_coverage_remains_eligible_for_ncr_parcel_addresses(): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Cache::flush();
        Http::fake([
            'https://psgc.gitlab.io/api/provinces/' => Http::response([], 200),
            'https://psgc.gitlab.io/api/cities-municipalities/' => Http::response([
                ['code' => '133900000', 'name' => 'City of Manila', 'provinceCode' => '', 'regionCode' => '130000000']], 200),
            'https://psgc.gitlab.io/api/cities-municipalities/133900000/barangays/' => Http::response([
                ['code' => '133900001', 'name' => 'Barangay 1']], 200),
        ]);
        $n = $this->network();
        $n['branch']->update(['province' => 'Metro Manila', 'municipality' => 'City of Manila', 'region' => 'NCR']);
        $n['rider']->operationalServiceAreas()->first()->update(['province' => 'NCR', 'municipality' => 'City of Manila', 'barangay' => 'Barangay 1']);
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $s->activate($this->scope($n, 'branch'), $r->id, [$this->key($n)]);
        $areas = app(RiderServiceAreaService::class);
        $this->assertTrue($areas->eligible($r->fresh(), $n['branch']->id, 'Metro Manila', 'Manila', 'Barangay 1', 'pickup'));
        $this->assertTrue($areas->eligible($r->fresh(), $n['branch']->id, 'NCR', 'Manila', 'Barangay 1', 'delivery'));
        $this->assertSame(1, OperationalServiceArea::count());
    }

    public function test_preserved_vehicle_settings_cannot_change_during_open_batch(): void
    {
        $n = $this->network();
        $r = $n['rider'];
        $s = app(RiderManagementService::class);
        RiderBatch::create(['code' => uniqid('VEHICLE'), 'logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'rider_id' => $r->id, 'type' => 'pickup', 'status' => 'queued', 'scheduled_start' => now()->addHour(),
            'scheduled_end' => now()->addHours(2), 'estimated_minutes' => 30, 'capacity_snapshot' => []]);
        $s->updateAccountDetails($this->scope($n), $r->id, ['name' => 'Updated Rider', 'contact_no' => '09123456789',
            'vehicle_type' => $r->vehicle_type, 'plate_number' => $r->plate_number]);
        $this->assertSame('Updated Rider', $r->fresh()->name);
        $this->invalid(fn () => $s->updateAccountDetails($this->scope($n), $r->id, ['name' => 'Updated Rider', 'contact_no' => '09123456789',
            'vehicle_type' => 'Van', 'plate_number' => $r->plate_number]));
        $this->assertSame('Motorcycle', $r->fresh()->vehicle_type);
    }

    public function test_review_email_distinguishes_main_approval_from_branch_activation(): void
    {
        $n = $this->network();
        $pending = (new RiderApplicationDecisionNotification(true, 'Test Logistics', null, true))
            ->toMail($n['rider']);
        $text = implode(' ', $pending->introLines);
        $this->assertStringContainsString('approved by Main Hub', $text);
        $this->assertStringContainsString('Branch must confirm', $text);
        $this->assertStringNotContainsString('You can now sign in', $text);
        $legacy = (new RiderApplicationDecisionNotification(true, 'Test Logistics'))->toMail($n['rider']);
        $this->assertStringContainsString('receive pickup or delivery assignments', implode(' ', $legacy->introLines));
    }

    public function test_legacy_riders_keep_existing_custody_but_new_assignments_must_use_batches(): void
    {
        $n = $this->network();
        $legacy = Rider::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'name' => 'Legacy active rider', 'email' => uniqid('legacy').'@example.test', 'password' => 'TestPassword123!',
            'email_verified_at' => now(), 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'availability_status' => 'available']);
        $this->assertNull($legacy->managementProfile());
        $this->assertTrue($legacy->canOperate());
        $this->invalid(fn () => $n['service']->guardLegacyAssignment($legacy));
        $seller = $this->seller();
        $buyer = $this->buyer();
        $o = Order::create(['seller_id' => $seller->seller->id, 'buyer_id' => $buyer->buyer->id, 'logistics_partner_id' => $n['partner'],
            'status' => Order::STATUS_TO_RECEIVE, 'total_amount' => 100, 'payment_method' => 'cod', 'shipping_method' => 'standard']);
        $delivery = Delivery::create(['order_id' => $o->id, 'logistics_partner_id' => $n['partner'], 'rider_id' => $legacy->id,
            'delivery_rider_id' => $legacy->id, 'assigned_at' => now(), 'status' => Delivery::OUT_FOR_DELIVERY]);
        $n['service']->assertParcelAllowed($legacy, $delivery, true);
        $this->assertSame(Delivery::OUT_FOR_DELIVERY, $delivery->fresh()->status);
    }

    public function test_staff_portal_supports_branch_activation_and_main_hub_review_but_not_scanners_or_province_hubs(): void
    {
        $n = $this->network();
        $r = $this->applicant($n);
        $s = app(RiderManagementService::class);
        $s->review($this->scope($n), $r->id, 'approve', ['branch_id' => $n['branch']->id]);
        $staff = BranchStaff::create(['logistics_partner_id' => $n['partner'], 'sorting_center_id' => $n['branch']->id,
            'name' => 'Branch Manager', 'email' => uniqid('manager').'@example.test', 'password' => 'TestPassword123!', 'role' => 'manager', 'status' => 'active']);
        $this->actingAs($staff, 'logistics_staff')->get(route('logistics.staff.rider-management.index', ['rider' => $r->id]))
            ->assertOk()->assertSee('Confirm coverage');
        $this->postJson(route('logistics.staff.rider-management.action', $r->id), ['action' => 'activate', 'coverage' => [$this->key($n)]])
            ->assertOk()->assertJsonPath('data.rider.can_operate', true);
        $main = $n['branch']->replicate();
        $main->user_id = null;
        $main->code = uniqid('MAIN');
        $main->is_main = true;
        $main->type = 'province_hub';
        $main->save();
        $staff->update(['sorting_center_id' => $main->id]);
        $pending = $this->applicant($n);
        $this->actingAs($staff->fresh(), 'logistics_staff')->get(route('logistics.staff.rider-management.index', ['rider' => $pending->id]))
            ->assertOk()->assertSee('Approve for Branch activation');
        $this->postJson(route('logistics.staff.rider-management.action', $pending->id), ['action' => 'approve', 'branch_id' => $n['branch']->id])
            ->assertOk()->assertJsonPath('data.rider.can_operate', false);
        $main->update(['is_main' => false]);
        $this->get(route('logistics.staff.rider-management.index'))->assertForbidden();
        $main->update(['is_main' => true]);
        $staff->update(['role' => 'scanner']);
        $this->actingAs($staff->fresh(), 'logistics_staff')->get(route('logistics.staff.rider-management.index'))->assertForbidden();
    }
}
