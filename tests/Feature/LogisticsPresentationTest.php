<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class LogisticsPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_logistics_landing_and_apply_button_exist(): void
    {
        $this->get(route('logistics.home'))
            ->assertOk()
            ->assertSee('Apply as Logistics Partner')
            ->assertSee(route('logistics.register'), false);
    }

    public function test_original_logistics_application_route_and_fields_are_preserved(): void
    {
        $this->get(route('logistics.register'))
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="otp_code"', false)
            ->assertSee('name="rep_valid_id"', false)
            ->assertSee(route('logistics.register.store'), false);
    }

    public function test_logistics_login_has_password_and_google_actions(): void
    {
        $this->get(route('logistics.login'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Continue with Google')
            ->assertSee(route('logistics.login.submit'), false)
            ->assertSee(route('logistics.google.redirect'), false);
    }

    public function test_application_and_email_otp_routes_still_target_original_backend(): void
    {
        $routes = [
            'logistics.register' => ['GET', 'App\\Http\\Controllers\\Logistics\\RegistrationController@create'],
            'logistics.register.store' => ['POST', 'App\\Http\\Controllers\\Logistics\\RegistrationController@store'],
            'logistics.verification.send' => ['POST', 'App\\Http\\Controllers\\Logistics\\RegistrationController@sendVerificationCode'],
            'logistics.verification.verify' => ['POST', 'App\\Http\\Controllers\\Logistics\\RegistrationController@verifyVerificationCode'],
        ];
        foreach ($routes as $name => [$method, $controller]) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertContains($method, $route->methods());
            $this->assertSame($controller, $route->getActionName());
        }
    }

    public function test_unconfigured_google_signin_shows_a_friendly_notice(): void
    {
        config()->set('logistics_google.client_id', null);
        config()->set('logistics_google.client_secret', null);
        $this->get(route('logistics.google.redirect'))
            ->assertRedirect(route('logistics.login'))
            ->assertSessionHas('login_notice');
    }

    public function test_google_callback_rejects_requests_without_valid_session_state(): void
    {
        $this->get(route('logistics.google.callback', ['code' => 'fake', 'state' => 'invalid']))
            ->assertRedirect(route('logistics.login'))
            ->assertSessionHas('login_notice');
    }
}
