<?php

namespace App\Models\Seller\Manage_inventory;

use App\Models\Buyer\Order\OrderItem;
use App\Models\Buyer\Review\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const COMPLIANCE_PENDING = 'pending_review';
    public const COMPLIANCE_APPROVED = 'approved';
    public const COMPLIANCE_REJECTED = 'rejected';

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

        // Admin product review
        'compliance_status',
        'rejection_reason',
        'rejection_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'price' => 'decimal:2',
        'discount' => 'integer',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function scopePubliclyDiscoverable(Builder $query): Builder
    {
        // Buyer discovery and public Search must never drift into separate catalogues.
        // A product is only visible once an admin has approved it.
        return $query
            ->where('status', 'active')
            ->where('compliance_status', self::COMPLIANCE_APPROVED)
            ->where('stock', '>', 0);
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->where('compliance_status', self::COMPLIANCE_PENDING);
    }

    public function scopeComplianceApproved(Builder $query): Builder
    {
        return $query->where('compliance_status', self::COMPLIANCE_APPROVED);
    }

    public function scopeComplianceRejected(Builder $query): Builder
    {
        return $query->where('compliance_status', self::COMPLIANCE_REJECTED);
    }

    public function isComplianceApproved(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_APPROVED;
    }

    public function seller(): BelongsTo
    {
        // Inventory stores the seller's users.id; resolve the profile by its user_id, not sellers.id.
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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