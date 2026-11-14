<?php

namespace App\Models\Buyer\Review;

use App\Models\Buyer;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'buyer_id',
        'rating',
        'variant_label',
        'comment',
        'image_path',
        'media',
        'helpful_count',
    ];

    protected $casts = [
        'seller_replied_at' => 'datetime',
        'rating' => 'integer',
        'media' => 'array',
        'helpful_count' => 'integer',
    ];

    /** Public review attachments; old single-photo reviews remain readable. */
    public function publicMedia(): array
    {
        $media = collect($this->media ?? [])->filter(fn ($entry) => is_array($entry)
            && in_array($entry['type'] ?? '', ['image', 'video'], true)
            && is_string($entry['path'] ?? null) && str_starts_with($entry['path'], 'reviews/'))
            ->map(fn ($entry) => ['type' => $entry['type'], 'url' => asset('storage/'.$entry['path'])])->values()->all();
        if ($media === [] && $this->image_path) {
            $media[] = ['type' => 'image', 'url' => asset('storage/'.ltrim($this->image_path, '/'))];
        }

        return $media;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }
}
