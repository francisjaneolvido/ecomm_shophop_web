<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Real role HTTP seams and isolated persisted rows protect compliance across the commerce journey.
class ProductComplianceJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_catalogue_requires_approval_active_state_and_stock(): void
    {
        // Direct IDs and independent inventory state must never bypass the public catalogue authority.
        $seller = $this->seller();
        $eligible = $this->product($seller);
        foreach (['pending_review', 'rejected'] as $state) {
            $this->product($seller, $state);
        }
        $this->product($seller, 'approved', 'archived');
        $this->product($seller, 'approved', 'active', 0);
        $this->assertSame([$eligible->id], Product::publiclyDiscoverable()->pluck('id')->all());
    }

    public function test_direct_detail_related_merchandise_and_shop_count_use_the_shared_catalogue(): void
    {
        // Route binding resolves hidden IDs, so detail and recommendations must enforce eligibility again.
        $seller = $this->seller();
        $buyer = $this->buyer();
        $eligible = $this->product($seller);
        $related = $this->product($seller);
        $hidden = [$this->product($seller, 'pending_review'), $this->product($seller, 'rejected'),
            $this->product($seller, 'approved', 'archived'), $this->product($seller, 'approved', 'active', 0)];
        $this->actingAs($buyer->user)->get(route('buyer.product.show', $eligible))->assertOk()
            ->assertViewHas('relatedProducts', fn ($rows) => array_column($rows, 'id') === [$related->id])
            ->assertViewHas('product', fn ($row) => $row['seller_products'] === 2);
        foreach ($hidden as $product) {
            $this->get(route('buyer.product.show', $product))->assertNotFound();
        }
    }

    public function test_cart_rejects_forged_hidden_adds_and_stale_quantity_updates(): void
    {
        // Cart entries can outlive approval, so both ingress and later updates must reread persisted state.
        $seller = $this->seller();
        $buyer = $this->buyer();
        $approved = $this->product($seller);
        $this->actingAs($buyer->user)->postJson(route('buyer.cart.add'), ['product_id' => $approved->id, 'qty' => 1])->assertOk();
        foreach (['pending_review', 'rejected'] as $state) {
            $hidden = $this->product($seller, $state);
            $this->postJson(route('buyer.cart.add'), ['product_id' => $hidden->id])->assertUnprocessable();
        }
        $archived = $this->product($seller, 'approved', 'archived');
        $this->postJson(route('buyer.cart.add'), ['product_id' => $archived->id])->assertUnprocessable();
        $line = CartItem::sole();
        $approved->forceFill(['compliance_status' => 'pending_review'])->save();
        $this->patchJson(route('buyer.cart.update', $line->id), ['qty' => 2])->assertUnprocessable();
        $this->get(route('buyer.cart'))->assertOk()->assertViewHas('cartGroups', fn ($groups) => $groups[0]['items'][0]['stock'] === 0);
        $this->assertSame(1, $line->fresh()->quantity);
        $this->assertSame(1, CartItem::count());
    }

    public function test_checkout_rechecks_revoked_approval_without_financial_side_effects(): void
    {
        // Approval can change after selection; GET and locked placement must preserve every purchase invariant.
        $seller = $this->seller();
        $buyer = $this->buyer();
        $product = $this->product($seller);
        $this->actingAs($buyer->user)->postJson(route('buyer.cart.add'), ['product_id' => $product->id])->assertOk();
        $line = CartItem::sole();
        $this->get(route('buyer.cart.checkout', ['items' => [$line->id]]))->assertOk();
        $voucher = \App\Models\Seller\Manage_inventory\Voucher::create(['seller_id' => $seller->user_id,
            'name' => 'Compliance discount', 'code' => 'COMPLIANCE20', 'type' => 'percent', 'value' => 20,
            'min_order_amount' => 0, 'usage_limit' => 5, 'used_count' => 0, 'status' => 'active']);
        $voucher->products()->attach($product);
        foreach (['pending_review', 'rejected'] as $state) {
            $product->forceFill(['compliance_status' => $state])->save();
            foreach (['cod', 'online'] as $payment) {
                $this->post(route('buyer.checkout.place'), ['items' => [$line->id => ['quantity' => 1]],
                    'shipping_method' => [$seller->id => 'standard'], 'payment_method' => $payment,
                    'voucher_code' => 'COMPLIANCE20'])->assertSessionHasErrors('items');
                foreach (['orders', 'order_items', 'manual_cashless_payments'] as $table) {
                    $this->assertDatabaseCount($table, 0);
                }
                $this->assertSame(5, $product->fresh()->stock);
                $this->assertSame(0, $voucher->fresh()->used_count);
                $this->assertSame(1, $line->fresh()->quantity);
            }
            // The display boundary must fail too, independently of placement's financial gate.
            $this->get(route('buyer.cart.checkout', ['items' => [$line->id]]))
                ->assertRedirect(route('buyer.cart'))->assertSessionHasErrors('items');
        }
    }

    public function test_seller_creation_and_full_edit_reset_review_history(): void
    {
        // A content submission invalidates human review; inventory operations retain that independent decision.
        $seller = $this->seller();
        $payload = ['name' => 'Seller compliance submission', 'category' => 'Home & Living', 'price' => 100,
            'stock' => 5, 'description' => 'Seller submitted description', 'has_variants' => 0];
        $this->actingAs($seller->user)->post(route('seller.inventory.products.store'), $payload)
            ->assertRedirect(route('seller.inventory'));
        $product = Product::sole();
        $this->assertSame('pending_review', $product->compliance_status);
        $this->assertNotNull($product->submitted_at);
        $this->assertNull($product->reviewed_by);
        $this->assertSame('active', $product->status);
        $this->get(route('seller.inventory'))->assertOk()->assertSee($payload['name']);
        foreach (['approved', 'rejected'] as $state) {
            $product->forceFill(['compliance_status' => $state, 'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subDay(), 'reviewed_by' => $this->user('admin')->id,
                'rejection_reason' => 'wrong_category', 'rejection_notes' => 'Old notes'])->save();
            $this->post(route('seller.inventory.products.store'), $payload + ['product_id' => $product->id])
                ->assertRedirect(route('seller.inventory'));
            $product->refresh();
            $this->assertSame('pending_review', $product->compliance_status);
            $this->assertTrue($product->submitted_at->greaterThan(now()->subMinute()));
            foreach (['reviewed_at', 'reviewed_by', 'rejection_reason', 'rejection_notes'] as $field) {
                $this->assertNull($product->$field, $field);
            }
        }
    }

    public function test_admin_review_authority_and_rejection_validation(): void
    {
        // Review pages and mutations must share the approved-Admin boundary with existing Payment Review.
        $seller = $this->seller();
        $product = $this->product($seller, 'pending_review');
        $this->get('/admin/product-compliance')->assertRedirect(route('login'));
        $this->patch('/admin/product-compliance/'.$product->id.'/approve')->assertRedirect(route('login'));
        $this->actingAs($seller->user)->get('/admin/product-compliance')->assertForbidden();
        $this->patch('/admin/product-compliance/'.$product->id.'/reject', ['rejection_reason' => 'other'])->assertForbidden();
        $admin = $this->user('admin');
        $this->actingAs($admin)->get('/admin/product-compliance')->assertOk()->assertSee($product->name);
        $this->get('/admin/product-compliance/'.$product->id)->assertOk()->assertSee($product->name);
        foreach ([[], ['rejection_reason' => 'invented']] as $payload) {
            $this->patch('/admin/product-compliance/'.$product->id.'/reject', $payload)->assertSessionHasErrors('rejection_reason');
        }
        $this->patch('/admin/product-compliance/'.$product->id.'/reject', ['rejection_reason' => 'wrong_category',
            'rejection_notes' => 'Correct the category'])->assertRedirect();
        $product->refresh();
        $this->assertSame('rejected', $product->compliance_status);
        $this->assertSame($admin->id, $product->reviewed_by);
        $this->assertNotNull($product->reviewed_at);
        $this->assertSame('Correct the category', $product->rejection_notes);
        $product->update(['status' => 'archived']);
        $this->patch('/admin/product-compliance/'.$product->id.'/approve')->assertRedirect()
            ->assertSessionHas('status', 'Product approved. Buyer visibility also requires active status and available stock.');
        $product->refresh();
        $this->assertSame('approved', $product->compliance_status);
        $this->assertSame($admin->id, $product->reviewed_by);
        $this->assertSame('archived', $product->status);
        $this->assertNull($product->rejection_reason);
        $this->assertNull($product->rejection_notes);
        $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product)->exists());
    }

    public function test_stock_and_archive_operations_preserve_compliance_and_submission_history(): void
    {
        // Stock and archive transitions are independent of content review, including restore of rejected merchandise.
        $seller = $this->seller();
        $this->actingAs($seller->user);
        foreach (['approved', 'pending_review', 'rejected'] as $state) {
            $product = $this->product($seller, $state);
            $product->forceFill(['submitted_at' => now()->subDay()])->save();
            $submitted = $product->submitted_at->toDateTimeString();
            $this->patch(route('seller.inventory.products.stock', $product), ['stock' => 8])->assertRedirect();
            $this->assertSame($state, $product->fresh()->compliance_status);
            $this->assertSame($submitted, $product->fresh()->submitted_at->toDateTimeString());
            $this->patch(route('seller.inventory.products.archive', $product))->assertRedirect();
            $this->assertFalse(Product::publiclyDiscoverable()->whereKey($product)->exists());
            $this->patch(route('seller.inventory.products.archive', $product))->assertRedirect();
            $this->assertSame($state, $product->fresh()->compliance_status);
            $this->assertSame($state === 'approved', Product::publiclyDiscoverable()->whereKey($product)->exists());
        }
    }

    public function test_admin_queue_queries_pagination_and_shell_preserve_payment_review(): void
    {
        // Queue search and stable sorting retain query state while current shell anchors keep both review journeys reachable.
        $seller = $this->seller();
        foreach (range(1, 12) as $i) {
            $this->product($seller, 'pending_review')->update(['name' => 'Queue Product', 'sku' => 'QUEUE-'.$i]);
        }
        $this->product($seller, 'approved');
        $this->product($seller, 'rejected');
        $response = $this->actingAs($this->user('admin'))->get(route('admin.product-compliance.index',
            ['filter' => 'pending_review', 'sort' => 'az', 'search' => 'Compliance Shop', 'page' => 2]))
            ->assertOk()->assertViewHas('counts', ['pending_review' => 12, 'approved' => 1, 'rejected' => 1])
            ->assertViewHas('products', fn ($rows) => $rows->pluck('id')->all() === [11, 12] && str_contains($rows->url(1), 'search=Compliance'));
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        foreach (['admin.payments.index', 'admin.product-compliance.index', 'admin.accounts', 'admin.settings', 'home'] as $name) {
            $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route($name).'"]')->length, $name);
        }
        foreach (['index', 'show', 'verify', 'reject', 'cancel'] as $action) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.payments.'.$action));
        }
        $this->get(route('admin.product-compliance.index', ['filter' => 'approved', 'search' => 'No matching name']))
            ->assertOk()->assertSee('No matching Products');
    }

    public function test_seller_full_edit_payload_is_decodable_from_rendered_inventory(): void
    {
        // The real edit button must carry usable Product identity; double escaping previously opened an empty create form.
        $seller = $this->seller();
        $product = $this->product($seller, 'rejected');
        $response = $this->actingAs($seller->user)->get(route('seller.inventory'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $button = (new \DOMXPath($document))->query('//button[@data-product]')->item(0);
        $payload = json_decode($button->getAttribute('data-product'), true);
        $this->assertIsArray($payload);
        $this->assertSame($product->id, $payload['id']);
        $this->assertSame($product->name, $payload['name']);
    }

    private function user(string $role): User
    {
        // Approved real users exercise middleware rather than preview authentication.
        return User::forceCreate(['email' => uniqid($role).'@compliance.test', 'password' => bcrypt('Compliance123!'),
            'account_type' => $role, 'status' => 'approved', 'email_verified_at' => now()]);
    }

    private function profile(User $user): array
    {
        // Required registration fields keep disposable fixtures valid under the production schema.
        return ['user_id' => $user->id, 'first_name' => 'Compliance', 'last_name' => 'Test', 'sex' => 'Male',
            'contact_no' => '09123456789', 'birthday' => '1990-01-01', 'province_code' => '01', 'province_name' => 'Province',
            'municipality_code' => '01', 'municipality_name' => 'City', 'barangay_code' => '01', 'barangay_name' => 'Barangay',
            'street_address' => '1 Test Street', 'valid_id_path' => 'test.jpg'];
    }

    private function seller(): Seller
    {
        // Product ownership uses users.id while checkout shipping groups use sellers.id.
        return Seller::create($this->profile($this->user('seller')) + ['business_name' => 'Compliance Shop',
            'business_category' => 'Home', 'business_permit_path' => 'permit.jpg']);
    }

    private function buyer(): Buyer
    {
        // A persisted Buyer profile is required for canonical Cart and Checkout ownership.
        return Buyer::create($this->profile($this->user('buyer')));
    }

    private function product(Seller $seller, string $compliance = 'approved', string $status = 'active', int $stock = 5): Product
    {
        // Eligibility is explicit fixture data, never inferred from the new pending database default.
        $product = Product::create(['seller_id' => $seller->user_id, 'name' => uniqid('Compliance product '),
            'category' => 'Home & Living', 'description' => 'Compliance merchandise', 'price' => 100,
            'stock' => $stock, 'status' => $status]);
        $product->forceFill(['compliance_status' => $compliance])->save();
        return $product;
    }
}
