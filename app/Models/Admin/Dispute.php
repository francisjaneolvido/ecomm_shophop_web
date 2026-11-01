<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $guarded = ['id'];

    public const STATUSES = [
        'open' => 'Open',
        'under_review' => 'Under Review',
        'awaiting_response' => 'Awaiting Response',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];

    public const CLOSED = ['resolved', 'rejected'];

    public const TYPES = [
        'return_refund' => 'Return / Refund',
        'delivery' => 'Delivery Problem',
        'cod' => 'COD Discrepancy',
        'product' => 'Product Complaint',
        'seller_conduct' => 'Seller Conduct',
        'account' => 'Account Report',
        'other' => 'Other',
    ];

    public const PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'];

    public const OUTCOMES = [
        'refund_buyer' => 'In favor of buyer (refund)',
        'favor_seller' => 'In favor of seller',
        'split' => 'Split / compromise',
        'no_action' => 'No action needed',
        'rejected' => 'Reject complaint (invalid)',
    ];

    // Classes reuse the same palette already used by the registration page.
    private const BADGES = [
        'open' => 'bg-amber-100 text-amber-700',
        'under_review' => 'bg-sky/10 text-sky',
        'awaiting_response' => 'bg-violet-100 text-violet-600',
        'resolved' => 'bg-mint/10 text-mint-dark',
        'rejected' => 'bg-coral/10 text-coral',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->oldest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::BADGES[$this->status] ?? 'bg-slate-100 text-slate-500';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getIsClosedAttribute(): bool
    {
        return in_array($this->status, self::CLOSED, true);
    }

    public function getAgeDaysAttribute(): int
    {
        $end = $this->resolved_at ?? now();

        return (int) $this->created_at->diffInDays($end);
    }
}