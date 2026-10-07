<?php

namespace App\Services;

use App\Models\Logistics\Rider;
use Illuminate\Http\UploadedFile;

class RiderRegistrationDocuments
{
    public const DISK = 'registration_documents';

    public const SLOTS = [
        'or_cr' => ['or_cr_path', 'OR/CR'],
        'id_or_license' => ['id_or_license_path', "ID / Driver's License"],
    ];

    public static function store(Rider $rider, string $slot, UploadedFile $file): string
    {
        if (! isset(self::SLOTS[$slot])) {
            throw new \InvalidArgumentException('Unknown rider registration document slot.');
        }

        return $file->store('riders/'.$rider->id.'/'.$slot, self::DISK);
    }

    public static function scopedPath(Rider $rider, string $slot): ?string
    {
        $field = self::SLOTS[$slot][0] ?? null;
        $path = $field ? $rider->{$field} : null;
        $prefix = 'riders/'.$rider->id.'/'.$slot.'/';

        if (! is_string($path) || ! str_starts_with($path, $prefix)) {
            return null;
        }

        $filename = substr($path, strlen($prefix));

        return preg_match('/\A[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|pdf|webp)\z/i', $filename) === 1
            ? $path
            : null;
    }
}
