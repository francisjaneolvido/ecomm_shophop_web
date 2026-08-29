<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'password', 'account_type', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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
        $words = preg_split('/\s+/', trim($name));
        $words = array_filter($words);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }
}