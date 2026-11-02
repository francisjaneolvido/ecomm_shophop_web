<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    private const CACHE_KEY = 'platform_settings_v2';

    protected $fillable = [
        'name', 'description', 'support_email', 'support_phone',
        'maintenance_mode', 'maintenance_message',
    ];

    protected $casts = ['maintenance_mode' => 'boolean'];

    public static function current(): self
    {
        // Cache plain array data only, never the model object itself.
        $attributes = Cache::get(self::CACHE_KEY);

        if (! is_array($attributes) || ! isset($attributes['id'])) {
            $attributes = static::query()
                ->firstOrCreate(['id' => 1], ['name' => 'ShopHop'])
                ->getAttributes();

            Cache::forever(self::CACHE_KEY, $attributes);
        }

        return (new static)->newFromBuilder($attributes);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }
}