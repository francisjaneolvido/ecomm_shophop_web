<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;

/** Shared web registration normalization and uniqueness checks. */
final class RegistrationIdentity
{
    public static function email(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    public static function mobile(?string $mobile): string
    {
        $mobile = trim((string) $mobile);

        // +639XXXXXXXXX and 09XXXXXXXXX identify the same local subscriber.
        return preg_match('/^\+639\d{9}$/', $mobile)
            ? '0'.substr($mobile, 3)
            : $mobile;
    }

    public static function emailUnique(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if (DB::table('users')->whereRaw('LOWER(email) = ?', [self::email((string) $value)])->exists()) {
                $fail('This email address is already registered. Please sign in or finish verification.');
            }
        };
    }

    public static function mobileUnique(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            $mobile = self::mobile((string) $value);
            if (! preg_match('/^09\d{9}$/', $mobile)) {
                return; // The format rule will report the specific error.
            }

            $alternates = [$mobile, '+63'.substr($mobile, 1)];
            foreach (['buyers', 'sellers', 'logistics_partners', 'riders'] as $table) {
                if (DB::table($table)->whereIn('contact_no', $alternates)->exists()) {
                    $fail('This mobile number is already registered.');
                    return;
                }
            }
        };
    }

    public static function companyUnique(string $table, string $column, string $message): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail) use ($table, $column, $message): void {
            if (DB::table($table)->whereRaw('LOWER('.$column.') = ?', [strtolower(trim((string) $value))])->exists()) {
                $fail($message);
            }
        };
    }
}
