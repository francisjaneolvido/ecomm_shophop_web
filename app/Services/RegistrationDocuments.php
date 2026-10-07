<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegistrationDocuments
{
    // A single role/slot map owns uploads, Admin reads and legacy movement; unrelated assets never enter it.
    public const DISK = 'registration_documents';
    public const PROFILES = [
        'buyer' => [Buyer::class, 'buyer'],
        'seller' => [Seller::class, 'seller'],
        'logistics' => [LogisticsPartner::class, 'logisticsPartner'],
    ];
    public const SLOTS = [
        'buyer' => [
            'valid_id' => ['valid_id_path', 'Valid ID', 'valid_ids/buyers'],
        ],
        'seller' => [
            'valid_id' => ['valid_id_path', 'Valid ID', 'valid_ids/sellers'],
            'business_permit' => ['business_permit_path', 'Business Permit', 'business_permits/sellers'],
        ],
        'logistics' => [
            'rep_valid_id' => ['rep_valid_id_path', 'Representative Valid ID', 'valid_ids/logistics'],
            'business_permit' => ['business_permit_path', 'Business Permit', 'business_permits/logistics'],
            'accreditation_docs' => ['accreditation_docs_path', 'Accreditation Docs', 'accreditation_docs'],
            'agreement_signature' => ['agreement_signature_path', 'Agreement Signature', 'signatures'],
        ],
    ];

    public static function store(User $user, string $slot, UploadedFile $file): string
    {
        // Identity artifacts stay outside the web root and are bound to the new account and known slot.
        if (!isset(self::SLOTS[$user->account_type][$slot])) {
            throw new \InvalidArgumentException('Unknown registration document slot.');
        }
        return $file->store($user->id.'/'.$slot, self::DISK);
    }

    public static function profile(User $user): ?Model
    {
        // Only the authoritative relation for this account type may supply document columns.
        $relation = self::PROFILES[$user->account_type][1] ?? null;
        return $relation ? $user->{$relation} : null;
    }

    public static function scopedPath(User $user, string $slot): ?string
    {
        // Even a corrupted DB reference cannot cross user/slot boundaries or address another private asset.
        $field = self::SLOTS[$user->account_type][$slot][0] ?? null;
        $path = $field ? self::profile($user)?->{$field} : null;
        $prefix = $user->id.'/'.$slot.'/';
        return is_string($path) && str_starts_with($path, $prefix)
            && self::safeFilename(substr($path, strlen($prefix))) ? $path : null;
    }

    public static function safeFilename(string $name): bool
    {
        // Storage references contain one generated leaf; separators, traversal and absolute keys are excluded.
        return preg_match('/\A[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|pdf|webp)\z/i', $name) === 1;
    }

    public static function entries(User $user): array
    {
        // The browser receives only authenticated application URLs; unmigrated/missing files have no link.
        $profile = self::profile($user);
        $entries = [];
        foreach (self::SLOTS[$user->account_type] ?? [] as $slot => [$field, $label]) {
            $path = self::scopedPath($user, $slot);
            $available = $path && Storage::disk(self::DISK)->exists($path);
            $entries[] = [
                'label' => $label,
                'status' => $profile?->{$field} ? 'submitted' : 'missing',
                'url' => $available ? route('admin.registrations.documents.show', [$user, $slot]) : null,
                'mime' => $available ? Storage::disk(self::DISK)->mimeType($path) : null,
            ];
        }
        return $entries;
    }
}
