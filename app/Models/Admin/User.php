<?php

namespace App\Models;

use App\Models\Admin\RegistrationActivity;
use App\Models\Admin\RegistrationReport;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'email', 'password', 'account_type', 'status',
    'reviewed_by', 'reviewed_at', 'notes', 'rejection_reason',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function buyer(): HasOne
    {
        return $this->hasOne(Buyer::class);
    }

    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class);
    }

    public function logisticsPartner(): HasOne
    {
        return $this->hasOne(LogisticsPartner::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(RegistrationActivity::class)->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(RegistrationReport::class)->latest();
    }

    /**
     * A human-friendly name for admin tables, regardless of account type.
     * Falls back to the e-mail when no profile name is available.
     */
    public function getDisplayNameAttribute(): string
    {
        return match ($this->account_type) {
            'buyer' => trim(($this->buyer?->first_name ?? '').' '.($this->buyer?->last_name ?? '')) ?: $this->email,
            'seller' => trim(($this->seller?->first_name ?? '').' '.($this->seller?->last_name ?? '')) ?: $this->email,
            'logistics' => $this->logisticsPartner?->company_name ?? $this->email,
            default => $this->email,
        };
    }

    /**
     * Two-letter initials for the little avatar circle in admin tables.
     */
    public function getInitialsAttribute(): string
    {
        $name = $this->display_name;
        $words = array_filter(preg_split('/\s+/', trim($name)));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Normalized list of role-specific submitted documents.
     * Each doc: label, path (nullable), status (submitted|missing).
     */
    public function getRegistrationDocumentsAttribute(): array
    {
        return match ($this->account_type) {
            'buyer' => [
                $this->docEntry('Valid ID', $this->buyer?->valid_id_path),
            ],
            'seller' => [
                $this->docEntry('Valid ID', $this->seller?->valid_id_path),
                $this->docEntry('Business Permit', $this->seller?->business_permit_path),
            ],
            'logistics' => [
                $this->docEntry('Representative Valid ID', $this->logisticsPartner?->rep_valid_id_path),
                $this->docEntry('Business Permit', $this->logisticsPartner?->business_permit_path),
                $this->docEntry('Signed Agreement', $this->logisticsPartner?->agreement_signature_path),
                $this->docEntry('Accreditation Docs', $this->logisticsPartner?->accreditation_docs_path),
            ],
            default => [],
        };
    }

    private function docEntry(string $label, ?string $path): array
    {
        return [
            'label' => $label,
            'path' => $path,
            'status' => $path ? 'submitted' : 'missing',
            'url' => $path ? Storage::disk('public')->url($path) : null,
        ];
    }
}