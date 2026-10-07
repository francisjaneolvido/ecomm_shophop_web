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
        'sorting_center_id',
        'area_name',
        'area_type',
        'cities',
    ];

    public function logisticsPartner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class);
    }

    public function sortingCenter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Logistics\SortingCenter::class, 'sorting_center_id');
    }
}