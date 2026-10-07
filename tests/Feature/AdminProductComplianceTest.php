<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_shows_only_active_products_for_the_selected_tab_and_counts_match(): void
    {
        $seller = $this->user('seller');
        $this->product($seller, 'Pending Lamp', 'pending_review');
        $this->product($seller, 'Approved Lamp', 'approved');
        $this->product($seller, 'Rejected Lamp', 'rejected');
        $this->product($seller, 'Archived Pending Lamp', 'pending_review', 'archived');

        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->get(route('admin.compliance'))
            ->assertOk()
            ->assertSee('Pending Lamp')
            ->assertDontSee('Approved Lamp')
            ->assertDontSee('Rejected Lamp')
            ->assertDontSee('Archived Pending Lamp');

        $this->assertSame(
            ['pending' => 1, 'approved' => 1, 'rejected' => 1],
            $response->viewData('counts')
        );

        $this->actingAs($admin)->get(route('admin.compliance', ['filter' => 'approved']))
            ->assertOk()
            ->assertSee('Approved Lamp')
            ->assertDontSee('Pending Lamp');
    }

    public function test_approving_a_pending_product_makes_it_publicly_discoverable(): void
    {
        $seller = $this->user('seller');
        $admin = $this->user('admin');
        $product = $this->product($seller, 'Approve Me', 'pending_review');

        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.approve', $product))
            ->assertSessionHas('status');

        $product->refresh();
        $this->assertSame('approved', $product->compliance_status);
        $this->assertSame($admin->id, (int) $product->reviewed_by);
        $this->assertNotNull($product->reviewed_at);
        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
    }

    public function test_rejecting_requires_a_valid_reason_and_stores_it_for_the_seller(): void
    {
        $seller = $this->user('seller');
        $admin = $this->user('admin');
        $product = $this->product($seller, 'Reject Me', 'pending_review');
        $url = route('admin.compliance.products.reject', $product);

        $this->actingAs($admin)->post($url, [])
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)->post($url, ['reason' => 'not-a-real-reason'])
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)->post($url, ['reason' => 'other'])
            ->assertSessionHasErrors('notes');

        $this->assertSame('pending_review', $product->fresh()->compliance_status);

        $this->actingAs($admin)->post($url, [
            'reason' => 'wrong_category',
            'notes' => 'This is a kitchen item listed under Electronics.',
        ])->assertSessionHas('status');

        $product->refresh();
        $this->assertSame('rejected', $product->compliance_status);
        $this->assertSame('wrong_category', $product->rejection_reason);
        $this->assertSame('This is a kitchen item listed under Electronics.', $product->rejection_notes);
        $this->assertSame($admin->id, (int) $product->reviewed_by);
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
    }

    public function test_an_approved_product_can_be_taken_down(): void
    {
        $seller = $this->user('seller');
        $admin = $this->user('admin');
        $product = $this->product($seller, 'Live Product', 'approved');

        $this->assertTrue(Product::publiclyDiscoverable()->whereKey($product->id)->exists());

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.reject', $product), ['reason' => 'prohibited_item'])
            ->assertSessionHas('status');

        $this->assertSame('rejected', $product->fresh()->compliance_status);
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product->id)->exists());
    }

    public function test_invalid_transitions_are_refused_without_changing_the_product(): void
    {
        $seller = $this->user('seller');
        $admin = $this->user('admin');

        $rejected = $this->product($seller, 'Already Rejected', 'rejected');
        $approved = $this->product($seller, 'Already Approved', 'approved');
        $archived = $this->product($seller, 'Archived One', 'pending_review', 'archived');

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.approve', $rejected))
            ->assertSessionHasErrors('moderation');

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.approve', $approved))
            ->assertSessionHasErrors('moderation');

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.approve', $archived))
            ->assertSessionHasErrors('moderation');

        $this->actingAs($admin)
            ->post(route('admin.compliance.products.reject', $rejected), ['reason' => 'poor_images'])
            ->assertSessionHasErrors('moderation');

        $this->assertSame('rejected', $rejected->fresh()->compliance_status);
        $this->assertSame('approved', $approved->fresh()->compliance_status);
        $this->assertSame('pending_review', $archived->fresh()->compliance_status);
    }

    public function test_non_admins_cannot_review_products(): void
    {
        $seller = $this->user('seller');
        $product = $this->product($seller, 'Protected Product', 'pending_review');

        $response = $this->actingAs($seller)
            ->post(route('admin.compliance.products.approve', $product));

        $this->assertContains($response->getStatusCode(), [302, 403, 404]);
        $this->assertSame('pending_review', $product->fresh()->compliance_status);

        $response = $this->actingAs($seller)->get(route('admin.compliance'));
        $this->assertNotSame(200, $response->getStatusCode());
    }

    private function user(string $type): User
    {
        $user = new User();
        $user->forceFill([
            'email' => $type . '-' . uniqid() . '@compliance.test',
            'password' => 'not-used-by-this-test',
            'account_type' => $type,
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function product(User $seller, string $name, string $compliance, string $status = 'active'): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'name' => $name,
            'sku' => strtoupper(str_replace(' ', '-', $name)),
            'category' => 'Home & Living',
            'description' => 'Compliance review test product',
            'price' => '500.00',
            'discount' => 0,
            'stock' => 5,
            'has_variants' => false,
            'status' => $status,
            'compliance_status' => $compliance,
            'submitted_at' => now(),
        ]);
    }
}