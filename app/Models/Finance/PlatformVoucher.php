<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class PlatformVoucher extends Model
{
    protected $fillable = ['code','name','kind','type','value','min_spend','usage_limit','used_count','starts_at','ends_at','active'];
    protected $casts = ['active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime',
        'value' => 'decimal:2', 'min_spend' => 'decimal:2'];
    public function scopeAvailable($query)
    {
        return $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }
}
