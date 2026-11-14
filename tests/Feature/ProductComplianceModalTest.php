<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductComplianceModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_review_queue_renders_quick_review_and_decision_dialogs(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $seller = User::forceCreate(['email' => 'seller@modal.test', 'password' => bcrypt('password'),
            'account_type' => 'seller', 'status' => 'approved']);
        $admin = User::forceCreate(['email' => 'admin@modal.test', 'password' => bcrypt('password'),
            'account_type' => 'admin', 'status' => 'approved']);
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Reviewable Shoes',
            'category' => 'Fashion', 'price' => 250, 'stock' => 8, 'status' => 'active',
            'compliance_status' => 'pending_review']);

        $this->actingAs($admin)->get(route('admin.compliance'))
            ->assertOk()->assertSee('Quick review')
            ->assertSee('review-product-'.$product->id)
            ->assertSee('productDecisionDialog')
            ->assertSee('Confirm rejection');
        $this->get(route('admin.compliance.show', $product))->assertOk()
            ->assertSee('complianceDetailDecision');
        $this->patch(route('admin.compliance.reject', $product), ['rejection_reason' => 'wrong_category'])
            ->assertRedirect();
        $this->assertSame('rejected', $product->fresh()->compliance_status);
        $this->patch(route('admin.compliance.approve', $product))->assertRedirect();
        $this->assertSame('approved', $product->fresh()->compliance_status);
    }
}
