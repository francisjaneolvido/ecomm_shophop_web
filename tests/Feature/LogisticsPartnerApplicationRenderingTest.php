<?php

namespace Tests\Feature;

use Tests\TestCase;

class LogisticsPartnerApplicationRenderingTest extends TestCase
{
    public function test_guest_can_view_a_clean_logistics_partner_application_form(): void
    {
        $this->get(route('logistics.register'))
            ->assertOk()
            ->assertViewIs('logistics.register')
            ->assertSee('data-wizard', false)
            ->assertSee('method="POST"', false)
            ->assertDontSee('@endsectiongit add .')
            ->assertDontSee('git add .')
            ->assertDontSee('@endsection');
    }
}
