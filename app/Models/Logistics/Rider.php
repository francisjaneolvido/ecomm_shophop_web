<?php

namespace App\Models\Logistics;

use App\Models\LogisticsCoverageArea;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Rider extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Account review status and operational availability are deliberately separate.
    protected $fillable = [
        'logistics_partner_id',
        'sorting_center_id',
        'coverage_area_id',
        'name',
        'first_name',
        'last_name',
        'middle_initial',
        'sex',
        'contact_no',
        'birthday',
        'province_code',
        'province_name',
        'municipality_code',
        'municipality_name',
        'barangay_code',
        'barangay_name',
        'street_address',
        'vehicle_type',
        'plate_number',
        'or_cr_path',
        'id_or_license_path',
        'status',
        'availability_status',
        'rejection_reason',
        'applied_at',
        'approved_at',
        'rejected_at',
        'terms_accepted_at',
        'email',
        'email_verified_at',
        'password',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'email_verified_at' => 'datetime',
            'applied_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\LogisticsPartner::class, 'logistics_partner_id');
    }

    public function sortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'sorting_center_id');
    }

    public function coverageArea(): BelongsTo
    {
        return $this->belongsTo(LogisticsCoverageArea::class, 'coverage_area_id');
    }

    public function emailVerificationCode()
    {
        return $this->hasOne(RiderEmailVerificationCode::class);
    }

    public function applicationAddress(): string
    {
        return collect([
            $this->street_address,
            $this->barangay_name,
            $this->municipality_name,
            $this->province_name,
        ])->filter()->implode(', ');
    }
}
