<?php
namespace App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
class DemoTransaction extends Model
{
    protected $table = 'demo_payment_transactions';
    protected $fillable = ['manual_cashless_payment_id', 'reference', 'amount', 'simulated_at'];
    protected $casts = ['amount' => 'decimal:2', 'simulated_at' => 'datetime'];
}
