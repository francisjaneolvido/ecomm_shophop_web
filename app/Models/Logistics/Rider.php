<?php

namespace App\Models\Logistics;

use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
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
        'region_code',
        'region_name',
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

    public function lifecycleProfile()
    {
        return $this->hasOne(RiderLifecycleProfile::class);
    }

    public function managementProfile(): ?RiderLifecycleProfile
    {
        if ($this->relationLoaded('lifecycleProfile')) {
            return $this->lifecycleProfile;
        }
        if (! Schema::hasTable('rider_lifecycle_profiles')) {
            return null;
        }

        return $this->lifecycleProfile()->first();
    }

    public function getApplicationStatusAttribute(): string
    {
        return $this->managementProfile()?->application_status ?? match ($this->status) {
            'pending' => 'pending_review','rejected' => 'rejected',default => 'approved'
        };
    }

    public function getOperationalStatusAttribute(): string
    {
        return $this->managementProfile()?->operational_status ?? match ($this->status) {
            'active' => 'active','suspended' => 'suspended','rejected' => 'inactive',default => 'pending_activation'
        };
    }

    public function canOperate(): bool
    {
        $p = $this->managementProfile();

        return $this->status === 'active' && (! $p || ($p->application_status === 'approved' && $p->operational_status === 'active' && ! $p->assignment_confirmation_required));
    }

    public function availabilityState(): string
    {
        if (! $this->canOperate()) {
            return 'offline';
        }
        if ($this->availability_status === 'busy') {
            return 'busy';
        }
        if (in_array($this->availability_status, ['available', 'online'], true)) {
            return 'online';
        }

        return $this->managementProfile()?->availability_state === 'on_break' ? 'on_break' : 'offline';
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(LogisticsPartner::class, 'logistics_partner_id');
    }

    public function sortingCenter(): BelongsTo
    {
        return $this->belongsTo(SortingCenter::class, 'sorting_center_id');
    }

    public function coverageArea(): BelongsTo
    {
        return $this->belongsTo(LogisticsCoverageArea::class, 'coverage_area_id');
    }

    public function operationalServiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(OperationalServiceArea::class, 'operational_area_rider',
            'rider_id', 'operational_service_area_id');
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
