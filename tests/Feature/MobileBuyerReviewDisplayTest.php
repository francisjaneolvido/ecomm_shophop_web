<?php

namespace Tests\Feature;

use App\Models\Buyer\Review\Review;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class MobileBuyerReviewDisplayTest extends TestCase
{
    use SellerWorkspaceFixtures;

    public function test_product_api_exposes_review_text_and_mixed_media_to_mobile_buyers(): void
    {
        $product = $this->product($this->seller());
        $buyer = $this->buyer();
        Review::create([
            'buyer_id' => $buyer->buyer->id, 'product_id' => $product->id,
            'rating' => 5, 'comment' => 'Photo and video review',
            'image_path' => 'reviews/photo.png',
            'media' => [
                ['type' => 'image', 'path' => 'reviews/photo.png'],
                ['type' => 'video', 'path' => 'reviews/video.mp4'],
            ],
        ]);
        Sanctum::actingAs($buyer);
        $this->getJson('/api/mobile/buyer/products/'.$product->id)->assertOk()
            ->assertJsonPath('data.reviews_data.0.comment', 'Photo and video review')
            ->assertJsonPath('data.reviews_data.0.rating', 5)
            ->assertJsonPath('data.reviews_data.0.media.0.type', 'image')
            ->assertJsonPath('data.reviews_data.0.media.0.url', asset('storage/reviews/photo.png'))
            ->assertJsonPath('data.reviews_data.0.media.1.type', 'video')
            ->assertJsonPath('data.reviews_data.0.media.1.url', asset('storage/reviews/video.mp4'));
    }
}
