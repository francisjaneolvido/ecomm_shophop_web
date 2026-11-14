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
        // Existing Cart lines survive review changes but must render unavailable until approval returns.
        if (! $this->product || $this->product->compliance_status !== 'approved' || $this->product->status !== 'active') {
            return 0;
        }
        // Seller Inventory stores Product stock as the variant aggregate, so either row can cap a variant line.
        return $this->variant
            ? min((int) $this->variant->stock, (int) $this->product->stock)
            : (int) $this->product->stock;
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

        // Both models inherit Model::$attributes. Read the JSON column explicitly,
        // rather than accidentally displaying the variant's entire database row.
        $attributes = $this->variant->getAttribute('attributes');
        $choices = [];
        $internal = ['id', 'product_id', 'product_variant_id', 'sku', 'price', 'weight_kg',
            'stock', 'status', 'created_at', 'updated_at', 'deleted_at'];
        foreach (is_array($attributes) ? $attributes : [] as $key => $value) {
            if (in_array(strtolower((string) $key), $internal, true) ||
                (! is_string($value) && ! is_numeric($value)) || trim((string) $value) === '') {
                continue;
            }
            $label = is_int($key) ? '' : ucfirst(str_replace(['_', '-'], ' ', $key)).': ';
            $choices[] = $label.trim((string) $value);
        }

        return $choices ? implode(' · ', $choices) : ($this->variant->name ?: 'Standard');
    }
}
