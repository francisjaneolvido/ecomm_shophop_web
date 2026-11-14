<?php

namespace Tests\Feature;

use Tests\TestCase;

class OversizedUploadResponseTest extends TestCase
{
    public function test_oversized_seller_upload_gets_a_helpful_413_response(): void
    {
        $this->withServerVariables(['CONTENT_LENGTH' => (string) PHP_INT_MAX])
            ->post('/seller/inventory')
            ->assertStatus(413)
            ->assertSee('Your upload is too large')
            ->assertSee('30 MB');
    }
}
