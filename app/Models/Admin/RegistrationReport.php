<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationReport extends Model
{
    protected $table = 'registration_reports';

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'type', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}