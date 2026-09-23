<?php

namespace App\Models\Buyer\Cart;

use App\Models\Buyer;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'buyer_id',
        'product_id',
        'product_variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function availableStock(): int
    {
        return (int) ($this->variant?->stock ?? $this->product->stock);
    }

    public function unitPrice(): float
    {
        if ($this->variant) {
            return (float) $this->variant->price;
        }

        $price = (float) $this->product->price;
        $discount = (int) ($this->product->discount ?? 0);

        return $discount > 0
            ? round($price * (1 - $discount / 100), 2)
            : $price;
    }

    public function originalPrice(): ?float
    {
        if ($this->variant) {
            return null;
        }

        $discount = (int) ($this->product->discount ?? 0);

        return $discount > 0 ? (float) $this->product->price : null;
    }

    public function variantLabel(): string
    {
        if (! $this->variant) {
            return 'Standard';
        }

        $attributes = $this->variant->attributes ?? [];

        if (is_array($attributes) && count($attributes) > 0) {
            return implode(', ', array_values($attributes));
        }

        return $this->variant->name ?? 'Standard';
    }
}