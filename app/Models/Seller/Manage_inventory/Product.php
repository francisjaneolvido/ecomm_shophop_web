<?php

namespace App\Models\Seller\Manage_inventory;

use App\Models\Buyer\Order\OrderItem;
use App\Models\Buyer\Review\Review;
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
        'price',
        'discount',
        'stock',
        'low_stock_threshold',
        'description',
        'has_variants',
        'status',
        'image',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'price' => 'decimal:2',
        'discount' => 'integer',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id');
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