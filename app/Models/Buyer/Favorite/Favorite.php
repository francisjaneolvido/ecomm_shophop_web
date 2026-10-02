<?php

namespace App\Models\Buyer\Favorite;

use App\Models\Buyer;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $table = 'favorites';

    protected $fillable = [
        'buyer_id',
        'product_id',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
