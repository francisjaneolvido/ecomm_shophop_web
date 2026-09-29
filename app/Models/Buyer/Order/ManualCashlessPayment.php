<?php

namespace App\Models\Buyer\Order;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualCashlessPayment extends Model
{
    public const AWAITING_PROOF = 'awaiting_proof';
    public const PENDING_REVIEW = 'pending_review';
    public const REJECTED = 'rejected';
    public const VERIFIED = 'verified';
    // Closure terminates every explicitly linked Seller Order and cannot return to review.
    public const CANCELLED = 'cancelled';
    public const EXPIRED = 'expired';

    protected $fillable = ['buyer_id', 'checkout_group_id', 'status', 'reference',
        'normalized_reference', 'receipt_path', 'submitted_at', 'reviewer_user_id',
        'decision', 'reviewed_at', 'rejection_reason', 'expires_at', 'closed_at',
        'closed_by_user_id', 'closure_reason'];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime',
        'expires_at' => 'datetime', 'closed_at' => 'datetime'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function orders(): HasMany
    {
        // The explicit FK owns membership; a copied checkout UUID cannot attach another Order.
        return $this->hasMany(Order::class, 'manual_cashless_payment_id');
    }

    public function expectedAmount(): string
    {
        // Only linked Orders matching Buyer/group invariants contribute persisted totals.
        return number_format((float) $this->orders()->where('buyer_id', $this->buyer_id)
            ->where('checkout_group_id', $this->checkout_group_id)
            ->sum('total_amount'), 2, '.', '');
    }
}
