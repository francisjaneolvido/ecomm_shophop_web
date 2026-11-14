<?php

namespace App\Models\Seller\Manage_inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'attributes',
        'image_id',
        'sku',
        'price',
        'weight_kg',
        'stock',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }

    public function displayLabel(): string
    {
        $attributes = $this->getAttribute('attributes');
        $choices = [];
        $internal = ['id', 'product_id', 'product_variant_id', 'sku', 'price', 'weight_kg',
            'stock', 'status', 'image_id', 'created_at', 'updated_at', 'deleted_at'];
        foreach (is_array($attributes) ? $attributes : [] as $key => $value) {
            if (in_array(strtolower((string) $key), $internal, true) ||
                (! is_string($value) && ! is_numeric($value)) || trim((string) $value) === '') {
                continue;
            }
            $label = is_int($key) ? '' : ucfirst(str_replace(['_', '-'], ' ', $key)).': ';
            $choices[] = $label.trim((string) $value);
        }

        return $choices ? implode(' · ', $choices) : ($this->name ?: 'Standard');
    }
}
