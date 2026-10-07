<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Product Review persistence does not establish Seller reply persistence or store-level feedback semantics.
class SellerFeedbackJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fixture destruction is allowed only on the explicit PHPUnit in-memory database.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // Reply controls must not promise posting or updating when neither operation can persist.
    public function test_approved_seller_is_not_offered_unsupported_reply_posting(): void
    {
        $response = $this->actingAs($this->user())->get('/seller/feedback')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//button[normalize-space(.)="Reply" or normalize-space(.)="Edit reply"]')->length,
            'Seller Feedback must not offer posting or updating replies without persistence.');
        foreach (['Seller feedback viewing is unavailable.', 'No live reviews, ratings or feedback history are displayed.',
            'This page does not load customer reviews for your products.', 'Seller replies cannot be posted, edited or stored here.',
            'Review moderation and helpful-vote actions are unavailable.', 'No Seller or store rating is calculated here.'] as $limit) {
            $response->assertSee($limit);
        }
        $content = $xpath->query('//*[@id="sellerFeedback"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="sellerFeedback"]//*[self::form or self::button or self::input or self::select or self::textarea or self::script]')->length);
        foreach (['Trisha Ang', 'Miguel Ortiz', 'Anna Reyes', 'Jessa Lim', 'Paolo Mendoza',
            'ORD-10210', 'Handwoven Rattan Basket', 'Your reply was posted.', 'Your reply was updated.',
            'Verified purchase', 'Just now'] as $falseFact) {
            $this->assertStringNotContainsString($falseFact, $content->item(0)->textContent);
        }
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('seller.feedback').'"]')->length);
    }

    // The stable destination still requires normal Seller authentication.
    public function test_guest_cannot_open_seller_feedback(): void
    {
        $this->get('/seller/feedback')->assertRedirect(route('login'));
    }

    // Existing role, approval and email-verification semantics remain authoritative.
    public function test_wrong_roles_and_unapproved_or_unverified_sellers_are_denied(): void
    {
        foreach (['buyer', 'admin', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/seller/feedback')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/seller/feedback')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
        $this->actingAs($this->user(['email_verified_at' => null]))->get('/seller/feedback')
            ->assertRedirect(route('seller.verify-email.show'))->assertSessionHas('seller_verification_user_id');
        $this->assertGuest('web');
    }

    // A separately authenticated Rider receives no marketplace Seller authorization.
    public function test_rider_guard_cannot_open_seller_feedback(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/seller/feedback')->assertRedirect(route('login'));
    }

    // Unsupported reply writes must remain non-successful and leave authentication state unchanged.
    public function test_unsupported_feedback_and_reply_operations_fail(): void
    {
        $this->actingAs($this->user());
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/seller/feedback')->assertStatus(405)->assertSessionMissing('status');
            $this->{$method}('/seller/feedback/1/reply')->assertNotFound()->assertSessionMissing('status');
        }
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    // Old review filtering and selection parameters cannot revive fixture reviews or reply controls.
    public function test_legacy_filters_remain_informational(): void
    {
        $this->actingAs($this->user());
        foreach (['all', '5', 'unreplied'] as $rating) {
            $this->get('/seller/feedback?rating='.$rating.'&search=Trisha&review=1&reply=1')
                ->assertOk()->assertSee('Seller feedback viewing is unavailable.')
                ->assertDontSee('Trisha Ang')->assertDontSee('saveReplyButton');
        }
    }

    // Local TEST shares the same read/reply limits without granting production Seller authorization.
    public function test_local_test_is_truthful_without_granting_seller_authorization(): void
    {
        $this->get('/__dev/preview/seller/feedback')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/seller/feedback')->assertOk()
            ->assertSee('Seller feedback viewing is unavailable.')
            ->assertSee('Seller replies cannot be posted, edited or stored here.')
            ->assertSee('No Seller or store rating is calculated here.')
            ->assertDontSee('Trisha Ang')->assertDontSee('saveReplyButton');
        $this->assertGuest('web');
        $this->get('/seller/feedback')->assertRedirect(route('login'));
    }

    // Access fixtures create only native Seller accounts, never reviews or replies.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('seller').'@feedback.test',
            'password' => 'MessagesFixture123!', 'account_type' => 'seller',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
