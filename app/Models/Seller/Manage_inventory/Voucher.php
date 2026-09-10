<?php

namespace App\Models\Seller\Manage_inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'seller_id',
        'name',
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'voucher_product');
    }
}