<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LogisticsPartnerBrandingV3Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_branding_routes_are_registered_and_company_owner_only(): void
    {
        $get = Route::getRoutes()->getByName('logistics.partner-profile.edit');
        $post = Route::getRoutes()->getByName('logistics.partner-profile.update');

        $this->assertNotNull($get);
        $this->assertNotNull($post);
        $this->assertContains('GET', $get->methods());
        $this->assertContains('POST', $post->methods());
        $this->assertContains('auth', $get->gatherMiddleware());
        $this->assertContains('approved.role:logistics', $get->gatherMiddleware());
        $this->assertContains('auth', $post->gatherMiddleware());
        $this->assertContains('approved.role:logistics', $post->gatherMiddleware());
    }

    public function test_logistics_public_landing_contains_playable_rider_video(): void
    {
        $this->get(route('logistics.home'))
            ->assertOk()
            ->assertSee('rider-registration-tutorial.mp4')
            ->assertSee('data-rider-qr', false);
    }

    public function test_existing_mobile_rider_partner_list_endpoint_still_exists(): void
    {
        $route = Route::getRoutes()->match(
            \Illuminate\Http\Request::create(
                'http://'.config('app.main_domain').'/api/mobile/rider/logistics-centers',
                'GET'
            )
        );
        $this->assertSame('api/mobile/rider/logistics-centers', $route->uri());
    }
}
