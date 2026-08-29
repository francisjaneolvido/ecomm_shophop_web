<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogisticsPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agreement_rep_name',
        'agreement_date',
        'agreement_signature_path',
        'company_name',
        'business_registration_no',
        'line_of_business',
        'rep_last_name',
        'rep_first_name',
        'rep_valid_id_path',
        'rep_id_number',
        'rep_sex',
        'rep_birthday',
        'contact_no',
        'region',
        'province',
        'municipality',
        'barangay',
        'street_no',
        'unit_no',
        'business_permit_path',
        'accreditation_docs_path',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'rep_birthday' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverageAreas(): HasMany
    {
        return $this->hasMany(LogisticsCoverageArea::class);
    }
}