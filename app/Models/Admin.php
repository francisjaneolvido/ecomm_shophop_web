<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'role',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name, 0, 1);
        $last  = mb_substr($this->last_name, 0, 1);

        return mb_strtoupper($first . $last);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin'         => 'Super Admin',
            'compliance_officer'  => 'Compliance Officer',
            'support_staff'       => 'Support Staff',
            default               => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }
}