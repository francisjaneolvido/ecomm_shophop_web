<?php

namespace Tests\Feature;

use App\Models\Admin\Dispute;
use App\Models\Seller\Manage_inventory\Product;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class BuyerDiscoveryJourneyTest extends TestCase
{
    use SellerWorkspaceFixtures;

    public function test_suggestions_show_only_available_products_and_discounted_prices(): void
    {
        $seller = $this->seller();
        $visible = $this->product($seller, ['name' => 'Reading Lamp', 'price' => 200, 'discount' => 10]);
        foreach ([['compliance_status'=>'pending_review'], ['compliance_status'=>'rejected'], ['stock'=>0], ['status'=>'archived']] as $change) {
            $this->product($seller, array_merge(['name'=>'Hidden Lamp'], $change));
        }
        $response = $this->getJson('/search/suggestions?q=LAMP')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame([$visible->id], array_column($response->json('products'), 'id'));
        $response->assertJsonPath('products.0.price', 180)->assertJsonPath('products.0.url', route('buyer.product.show', $visible));
        $this->assertGuest();
    }

    public function test_suggestions_match_subcategories_and_return_real_category_destinations(): void
    {
        $product = $this->product($this->seller(), ['name'=>'Desk Light', 'subcategory'=>'Furniture & Decor']);
        $response = $this->getJson('/search/suggestions?q=furniture')->assertOk();
        $this->assertContains($product->id, array_column($response->json('products'), 'id'));
        $this->assertContains(route('buyer.category.show', 'home-and-garden'), array_column($response->json('categories'), 'url'));
        $this->get('/search?search=furniture')->assertOk()->assertSee('Desk Light');
    }

    public function test_default_recommendations_are_bounded_and_query_input_is_validated(): void
    {
        $seller = $this->seller();
        for ($i=0; $i<9; $i++) $this->product($seller, ['name'=>'Lamp '.$i]);
        $response = $this->getJson('/search/suggestions')->assertOk();
        $this->assertCount(6, $response->json('products'));
        $this->assertCount(4, $response->json('categories'));
        $this->getJson('/search/suggestions?q='.str_repeat('x', 101))->assertUnprocessable();
        $this->getJson('/search/suggestions?q[]=lamp')->assertUnprocessable();
    }

    public function test_removed_category_directory_redirects_and_filtered_product_browsing_remains(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller, ['name' => 'Available desk lamp']);
        $this->product($seller, ['compliance_status' => 'pending_review']);
        $this->product($seller, ['stock' => 0]);
        $this->get('/buyer/categories')->assertStatus(301)->assertRedirect(route('search.index'));
        $this->get('/buyer/category/home-and-garden?sub=furniture-decor')->assertOk()->assertSee($product->name)
            ->assertDontSee(route('buyer.category.index'), false);
        $this->assertCount(14, config('shophop_categories'));
    }

    public function test_storefront_and_search_have_no_removed_directory_links(): void
    {
        $this->product($this->seller());
        foreach (['/', '/search', '/help'] as $url) {
            $this->get($url)->assertOk()->assertDontSee(route('buyer.category.index'), false)
                ->assertDontSee('Browse Categories')->assertDontSee('Browse categories');
        }
        $this->assertFalse(view()->exists('buyer.category.index'));
    }

    public function test_help_center_has_real_answers_and_working_navigation_for_guests_and_buyers(): void
    {
        $this->get('/help')->assertOk()->assertSee('Frequently asked questions')->assertSee('Where can I check my order status?')
            ->assertSee('Sign in as a buyer to submit a request')->assertSee(route('search.index'), false);
        $this->actingAs($this->buyer())->get('/buyer/profile')->assertOk()->assertSee('How can we help?')
            ->assertSee(route('buyer.help'), false)->assertDontSee('Buyer Orders is a frontend preview.');
        $this->get('/buyer/help')->assertOk()->assertSee('Submit support request')->assertSee('Your Support Requests');
    }

    public function test_support_report_persists_in_the_admin_queue_with_server_owned_identity_and_status(): void
    {
        $buyer = $this->buyer();
        $other = $this->buyer();
        $this->actingAs($buyer)->post('/buyer/help/requests', [
            'type'=>'other', 'subject'=>'Checkout button problem', 'description'=>'The checkout button does not respond when I open my cart.',
            'filed_by'=>$other->id, 'against_user_id'=>$other->id, 'order_id'=>999, 'status'=>'resolved', 'priority'=>'high',
        ])->assertRedirect(route('buyer.help').'#support-requests')->assertSessionHas('help_status');
        $report = Dispute::sole();
        $this->assertSame($buyer->id, $report->filed_by);
        $this->assertSame('open', $report->status);
        $this->assertSame('normal', $report->priority);
        $this->assertNull($report->against_user_id);
        $this->assertNull($report->order_id);
        $this->assertStringStartsWith('DSP-', $report->case_no);
        $this->get('/buyer/help')->assertOk()->assertSee($report->case_no)->assertSee('Checkout button problem');
        $this->actingAs($this->user('admin'))->get('/admin/complaints-disputes')->assertOk()->assertSee($report->case_no)->assertSee('Checkout button problem');
    }

    public function test_report_history_is_private_to_the_buyer_and_internal_notes_are_not_exposed(): void
    {
        $buyer = $this->buyer();
        $other = $this->buyer();
        Dispute::create(['filed_by'=>$other->id, 'type'=>'other', 'subject'=>'Other buyer private issue', 'description'=>'Private details.', 'status'=>'open']);
        Dispute::create(['filed_by'=>$buyer->id, 'type'=>'other', 'subject'=>'My saved issue', 'description'=>'My details.', 'status'=>'resolved', 'resolution_note'=>'Internal-only resolution note']);
        $this->actingAs($buyer)->get('/buyer/help')->assertOk()->assertSee('My saved issue')->assertSee('Resolved')
            ->assertDontSee('Other buyer private issue')->assertDontSee('Internal-only resolution note');
    }

    public function test_invalid_reports_cannot_persist_and_seller_and_guest_writes_are_denied(): void
    {
        $this->post('/buyer/help/requests', [])->assertRedirect();
        $this->actingAs($this->user('seller'))->post('/buyer/help/requests', [])->assertForbidden();
        $this->actingAs($this->buyer())->post('/buyer/help/requests', ['type'=>'unsupported', 'subject'=>'x', 'description'=>'short'])
            ->assertSessionHasErrors(['type','subject','description']);
        $this->assertDatabaseCount('disputes', 0);
    }

    public function test_search_result_price_and_product_link_match_the_suggestion(): void
    {
        $product = $this->product($this->seller(), ['name'=>'Discounted Lamp', 'price'=>200, 'discount'=>10]);
        $this->get('/search?search=Discounted')->assertOk()->assertSee('180.00')->assertSee(route('buyer.product.show', $product), false);
    }
}
