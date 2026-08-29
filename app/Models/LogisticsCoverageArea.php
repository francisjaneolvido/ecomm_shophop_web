<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsCoverageArea extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'logistics_partner_id',
        'area_name',
        'area_type',
        'cities',
    ];

    public function logisticsPartner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class);
    }
}