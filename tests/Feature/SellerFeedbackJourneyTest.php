<?php

namespace Tests\Feature;

use App\Models\Buyer\Review\Review;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class SellerFeedbackJourneyTest extends TestCase
{
    use SellerWorkspaceFixtures;

    private function review($seller, string $comment = 'Great lamp', int $rating = 5): Review
    {
        return Review::create(['product_id' => $this->product($seller)->id,
            'buyer_id' => $this->buyer()->buyer->id, 'rating' => $rating, 'comment' => $comment]);
    }

    public function test_feedback_shows_real_owned_reviews_and_rating_summary(): void
    {
        $seller = $this->seller();
        $this->review($seller);
        $this->review($seller, 'Good quality', 3);
        $this->review($this->seller(), 'Other seller secret');
        $this->actingAs($seller)->get('/seller/feedback')->assertOk()
            ->assertSee('Great lamp')->assertSee('Good quality')->assertSee('Average 4/5')
            ->assertDontSee('Other seller secret')->assertSee('Save reply');
        $this->get('/seller/feedback?rating=5')->assertOk()->assertSee('Great lamp')->assertDontSee('Good quality');
        $this->get('/seller/feedback?search=Good')->assertOk()->assertSee('Good quality')->assertDontSee('Great lamp');
    }

    public function test_seller_reply_persists_and_is_visible_to_buyer_web_and_api(): void
    {
        $seller = $this->seller();
        $review = $this->review($seller);
        $this->actingAs($seller)->from('/seller/feedback')->patch('/seller/feedback/'.$review->id.'/reply', ['reply' => 'Thank you!'])
            ->assertRedirect('/seller/feedback')->assertSessionHas('status', 'Reply saved.');
        $this->assertSame('Thank you!', $review->fresh()->seller_reply);
        $this->assertNotNull($review->fresh()->seller_replied_at);
        $this->get('/buyer/product/'.$review->product_id)->assertOk()->assertSee('Thank you!');
        Sanctum::actingAs($this->buyer());
        $this->getJson('/api/mobile/buyer/products/'.$review->product_id)->assertOk()
            ->assertJsonPath('data.reviews_data.0.seller_reply', 'Thank you!');
    }

    public function test_other_seller_cannot_reply_to_or_read_owned_review(): void
    {
        $review = $this->review($this->seller(), 'Private review');
        $other = $this->seller();
        $this->actingAs($other)->patch('/seller/feedback/'.$review->id.'/reply', ['reply' => 'Forged'])->assertNotFound();
        Sanctum::actingAs($other);
        $this->patchJson('/api/mobile/seller/feedback/'.$review->id.'/reply', ['reply' => 'Forged'])->assertNotFound();
        $this->getJson('/api/mobile/seller/feedback')->assertOk()->assertJsonPath('data.total', 0);
        $this->assertNull($review->fresh()->seller_reply);
    }

    public function test_mobile_reply_updates_existing_reply_and_validates_input(): void
    {
        $seller = $this->seller();
        $review = $this->review($seller);
        Sanctum::actingAs($seller);
        foreach (['', '   ', str_repeat('a', 2001)] as $reply) {
            $this->patchJson('/api/mobile/seller/feedback/'.$review->id.'/reply', compact('reply'))->assertUnprocessable();
        }
        $this->patchJson('/api/mobile/seller/feedback/'.$review->id.'/reply', ['reply' => 'First reply'])->assertOk();
        $this->patchJson('/api/mobile/seller/feedback/'.$review->id.'/reply', ['reply' => 'Updated reply'])->assertOk();
        $this->getJson('/api/mobile/seller/feedback')->assertOk()->assertJsonPath('data.reviews.data.0.seller_reply', 'Updated reply');
        $this->assertSame('Updated reply', $review->fresh()->seller_reply);
    }

    public function test_feedback_routes_keep_authentication_role_and_approval_boundaries(): void
    {
        $this->get('/seller/feedback')->assertRedirect();
        $this->getJson('/api/mobile/seller/feedback')->assertUnauthorized();
        $this->actingAs($this->user('buyer'))->get('/seller/feedback')->assertForbidden();
        foreach ([['account_type'=>'buyer'], ['status'=>'pending'], ['email_verified_at'=>null]] as $changes) {
            Sanctum::actingAs($this->user('seller', $changes));
            $this->getJson('/api/mobile/seller/feedback')->assertForbidden();
        }
    }

    public function test_feedback_filters_are_validated_and_review_content_is_escaped(): void
    {
        $seller = $this->seller();
        $this->review($seller, '<script>alert(1)</script>');
        $this->actingAs($seller)->get('/seller/feedback')->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        Sanctum::actingAs($seller);
        $this->getJson('/api/mobile/seller/feedback?rating=7')->assertUnprocessable();
    }
}
