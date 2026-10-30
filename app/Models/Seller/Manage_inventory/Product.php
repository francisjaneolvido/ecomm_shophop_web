<?php

namespace App\Models\Seller\Manage_inventory;

use App\Models\Buyer\Order\OrderItem;
use App\Models\Buyer\Review\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'seller_id',
        'name',
        'sku',
        'category',
        'subcategory',
        'price',
        'weight_kg',
        'discount',
        'stock',
        'low_stock_threshold',
        'description',
        'has_variants',
        'status',
        'image',
        'video_path',
        'media_first',
        'media_order',
        // Submission and Admin review persist one lifecycle alongside independent inventory status.
        'compliance_status', 'submitted_at', 'reviewed_at', 'reviewed_by', 'rejection_reason', 'rejection_notes',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'media_order' => 'array',
        'price' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'discount' => 'integer',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        // Review timestamps are dates while reviewer identity remains the canonical users.id.
        'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'reviewed_by' => 'integer',
    ];

    /**
     * Always prefer the seller-selected primary gallery image over the old
     * products.image column. Existing image consumers (Buyer Web and API)
     * continue to work without duplicating image-selection rules.
     */
    public function getImageAttribute(?string $legacyPath): ?string
    {
        $images = $this->relationLoaded('images')
            ? $this->getRelation('images')
            : $this->images()->get();

        if (! $this->relationLoaded('images')) {
            $this->setRelation('images', $images);
        }

        $primary = $images->firstWhere('is_primary', true)
            ?? $images->sortBy('id')->first();

        return $primary?->image_path ?: $legacyPath;
    }

    public function getVideoUrlAttribute(): ?string
    {
        $path = trim((string) ($this->attributes['video_path'] ?? ''));
        return $path === '' ? null : asset('storage/' . ltrim($path, '/'));
    }

    /** First media chosen by the Seller; old listings default to a photo. */
    public function getIsVideoFirstAttribute(): bool
    {
        return $this->media_first === 'video' && ! empty($this->getRawOriginal('video_path'));
    }

    /**
     * One ordered image/video gallery for Buyer Web, Flutter and Admin.
     * Keys are stable image:<id> or video; legacy products are still supported.
     */
    public function getOrderedMediaAttribute(): array
    {
        $photos = $this->relationLoaded('images')
            ? $this->getRelation('images') : $this->images()->get();
        $imageRows = $photos->keyBy(fn ($image) => 'image:'.$image->id);
        $fallback = $photos->sortByDesc('is_primary')->values()
            ->map(fn ($image) => 'image:'.$image->id)->all();
        $videoPath = trim((string) $this->getRawOriginal('video_path'));
        if ($videoPath !== '') {
            if ($this->media_first === 'video') array_unshift($fallback, 'video');
            else $fallback[] = 'video';
        }
        $sequence = is_array($this->media_order) && $this->media_order !== []
            ? array_merge($this->media_order, $fallback) : $fallback;
        $result = [];
        foreach (array_unique($sequence) as $key) {
            if ($key === 'video' && $videoPath !== '') {
                $result[] = ['key' => 'video', 'type' => 'video',
                    'url' => asset('storage/'.ltrim($videoPath, '/'))];
            } elseif (isset($imageRows[$key])) {
                $result[] = ['key' => $key, 'type' => 'image',
                    'url' => asset('storage/'.ltrim($imageRows[$key]->image_path, '/'))];
            }
        }
        if ($result === [] && $photos->isEmpty() && $this->getRawOriginal('image')) {
            $result[] = ['key' => 'legacy', 'type' => 'image',
                'url' => asset('storage/'.ltrim($this->getRawOriginal('image'), '/'))];
        }
        return $result;
    }

    /** Never trust submitted image IDs: resolve exclusively inside this product. */
    public function saveMediaSequence(?string $sequence, array $newImageIds = []): void
    {
        $wanted = json_decode($sequence ?? '', true);
        if (!is_array($wanted)) $wanted = [];
        $ids = $this->images()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allowed = array_fill_keys(array_map(fn ($id) => 'image:'.$id, $ids), true);
        if ($this->getRawOriginal('video_path')) $allowed['video'] = true;
        $ordered = [];
        foreach (array_slice($wanted, 0, 12) as $token) {
            if (!is_string($token)) continue;
            if (preg_match('/^new:(\d+)$/', $token, $match)) {
                $id = $newImageIds[(int) $match[1]] ?? null;
                $token = $id ? 'image:'.$id : '';
            }
            if (isset($allowed[$token]) && !in_array($token, $ordered, true)) $ordered[] = $token;
        }
        // Preserve images the seller did not explicitly reorder (e.g. older Flutter client).
        foreach ($this->ordered_media as $item) {
            if (isset($allowed[$item['key']]) && !in_array($item['key'], $ordered, true)) {
                $ordered[] = $item['key'];
            }
        }
        foreach (array_keys($allowed) as $token) {
            if (!in_array($token, $ordered, true)) $ordered[] = $token;
        }
        $firstImage = collect($ordered)->first(fn ($key) => str_starts_with($key, 'image:'));
        if ($firstImage) {
            $this->images()->update(['is_primary' => false]);
            $this->images()->whereKey((int) substr($firstImage, 6))->update(['is_primary' => true]);
        }
        $this->forceFill(['media_order' => $ordered,
            'media_first' => ($ordered[0] ?? '') === 'video' ? 'video' : 'image'])->save();
    }

    public function scopePubliclyDiscoverable(Builder $query): Builder
    {
        // Buyer discovery and public Search must never drift into separate catalogues.
        return $query
            ->where('status', 'active')
            ->where('compliance_status', 'approved')
            ->where('stock', '>', 0);
    }

    public function seller(): BelongsTo
    {
        // Inventory stores the seller's users.id; resolve the profile by its user_id, not sellers.id.
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id', 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'voucher_product');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
