<?php

namespace App\Models\Buyer\Order;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Human-readable variant text, e.g. "Black · Size 9".
     */
    public function variantLabel(): string
    {
        if (! $this->variant) {
            return 'Standard';
        }

        if (! empty($this->variant->name)) {
            return $this->variant->name;
        }

        $attributes = $this->variant->attributes ?? [];

        if (is_array($attributes) && count($attributes) > 0) {
            return collect($attributes)->implode(' · ');
        }

        return 'Standard';
    }
}