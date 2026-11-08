<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class SellerWorkspaceSyncTest extends TestCase
{
    use SellerWorkspaceFixtures;

    private function revision(): string
    {
        return $this->getJson('/catalog/revision')->assertOk()->assertHeader('Cache-Control', 'no-store, private')->json('revision');
    }

    public function test_web_create_admin_approve_edit_and_reapprove_reach_mobile_buyer(): void
    {
        $seller = $this->seller();
        $before = $this->revision();
        $this->actingAs($seller)->post('/seller/inventory', $this->productInput())->assertRedirect()->assertSessionHasNoErrors();
        $product = Product::sole();
        $this->assertSame('pending_review', $product->compliance_status);
        $this->assertSame($before, $this->revision());
        $admin = $this->user('admin');
        $this->actingAs($admin)->patch('/admin/seller-compliance/'.$product->id.'/approve')->assertRedirect();
        $approved = $this->revision();
        $this->assertNotSame($before, $approved);
        $this->get('/buyer/product/'.$product->id)->assertOk()->assertSee('New Lamp');
        $buyer = $this->buyer();
        Sanctum::actingAs($buyer);
        $this->getJson('/api/mobile/buyer/products/'.$product->id)->assertOk()->assertJsonPath('data.name', 'New Lamp');
        $this->actingAs($seller)->post('/seller/inventory', $this->productInput(['product_id'=>$product->id, 'name'=>'Edited Lamp', 'price'=>350]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('pending_review', $product->fresh()->compliance_status);
        $this->assertNotSame($approved, $this->revision());
        $this->get('/buyer/product/'.$product->id)->assertNotFound();
        $this->actingAs($admin)->patch('/admin/seller-compliance/'.$product->id.'/approve')->assertRedirect();
        $this->get('/buyer/product/'.$product->id)->assertOk()->assertSee('Edited Lamp');
        Sanctum::actingAs($buyer);
        $this->getJson('/api/mobile/buyer/products/'.$product->id)->assertOk()->assertJsonPath('data.name', 'Edited Lamp');
    }

    public function test_mobile_product_creation_waits_for_approval_then_reaches_buyer_web(): void
    {
        $seller = $this->seller();
        Sanctum::actingAs($seller);
        $this->postJson('/api/mobile/seller/inventory/products', $this->productInput())->assertOk();
        $product = Product::sole();
        $this->get('/buyer/product/'.$product->id)->assertNotFound();
        $this->actingAs($this->user('admin'))->patch('/admin/seller-compliance/'.$product->id.'/approve')->assertRedirect();
        $this->get('/buyer/product/'.$product->id)->assertOk()->assertSee('New Lamp');
        Sanctum::actingAs($seller);
        $this->postJson('/api/mobile/seller/inventory/products/'.$product->id, $this->productInput(['name'=>'Mobile edit']))->assertOk();
        $this->assertSame('pending_review', $product->fresh()->compliance_status);
        $this->get('/buyer/product/'.$product->id)->assertNotFound();
    }

    public function test_revision_detects_same_second_edits_and_stock_archive_changes(): void
    {
        $product = $this->product($this->seller());
        $first = $this->revision();
        $product->update(['price'=>101]);
        $second = $this->revision();
        $this->assertNotSame($first, $second);
        $product->update(['price'=>102]);
        $this->assertNotSame($second, $this->revision());
        $product->update(['stock'=>0]);
        $hidden = $this->revision();
        $this->assertNotSame($first, $hidden);
        $product->update(['stock'=>10, 'status'=>'archived']);
        $this->assertSame($hidden, $this->revision());
        $product->update(['status'=>'active']);
        $this->assertNotSame($hidden, $this->revision());
    }

    public function test_mobile_profile_saves_unicode_names_without_changing_other_identity_fields(): void
    {
        $seller = $this->seller();
        $other = $this->seller();
        Sanctum::actingAs($seller);
        $this->patchJson('/api/mobile/seller/profile', $this->profile(['first_name'=>'Niña', 'user_id'=>$other->id, 'business_name'=>'Forged Shop']))->assertOk();
        $this->getJson('/api/mobile/seller/profile')->assertOk()->assertJsonPath('data.first_name', 'Niña')->assertJsonPath('data.business_name', 'Test Shop');
        $this->assertSame('Maria', $other->seller->fresh()->first_name);
        $this->patchJson('/api/mobile/seller/profile', $this->profile(['contact_no'=>'123']))->assertUnprocessable();
        $this->assertSame('09171234567', $seller->seller->fresh()->contact_no);
    }

    public function test_web_and_mobile_password_changes_require_current_password_and_confirmation(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->patch('/seller/account/password', ['current_password'=>'wrong', 'password'=>'ReplacementPass123!', 'password_confirmation'=>'ReplacementPass123!'])
            ->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OriginalPassword123!', $seller->fresh()->password));
        $this->patch('/seller/account/password', ['current_password'=>'OriginalPassword123!', 'password'=>'ReplacementPass123!', 'password_confirmation'=>'ReplacementPass123!'])
            ->assertRedirect('/seller/account')->assertSessionHas('status', 'Password changed.');
        $this->assertTrue(Hash::check('ReplacementPass123!', $seller->fresh()->password));
        Sanctum::actingAs($seller->fresh());
        $this->patchJson('/api/mobile/seller/password', ['current_password'=>'ReplacementPass123!', 'password'=>'AnotherPassword123!', 'password_confirmation'=>'Mismatch'])->assertUnprocessable();
        $this->patchJson('/api/mobile/seller/password', ['current_password'=>'ReplacementPass123!', 'password'=>'AnotherPassword123!', 'password_confirmation'=>'AnotherPassword123!'])->assertOk();
        $this->assertTrue(Hash::check('AnotherPassword123!', $seller->fresh()->password));
    }

    public function test_buyer_pages_include_automatic_revision_refresh(): void
    {
        $product = $this->product($this->seller());
        $this->get('/buyer/product/'.$product->id)->assertOk()->assertSee('shophop-catalog-refresh.js');
        $this->actingAs($this->buyer())->get('/buyer/dashboard')->assertOk()->assertSee('shophop-catalog-refresh.js');
    }
}
