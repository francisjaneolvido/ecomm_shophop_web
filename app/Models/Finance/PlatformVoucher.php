<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class PlatformVoucher extends Model
{
    protected $fillable = ['code','name','kind','type','value','min_spend','usage_limit','used_count','starts_at','ends_at','active','max_discount','per_buyer_limit','budget_amount','spent_amount'];
    protected $casts = ['active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime',
        'value' => 'decimal:2', 'min_spend' => 'decimal:2',
        'max_discount' => 'decimal:2', 'budget_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2', 'per_buyer_limit' => 'integer'];
    public function scopeAvailable($query)
    {
        return $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->where(fn ($q) => $q->whereNull('budget_amount')->orWhereColumn('spent_amount', '<', 'budget_amount'));
    }
}
