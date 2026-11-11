<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/** Decimal input/output; integer centavos and half-up rounding for all postings. */
final class FinanceMoney
{
    public static function minor(string|int|float|null $value): int
    {
        $text = (string) ($value ?? '0');
        if (! preg_match('/^(-?)(\d{1,11})(?:\.(\d{1,2}))?$/D', $text, $parts)) {
            throw ValidationException::withMessages(['amount' => 'Use a decimal amount with at most two decimal places.']);
        }
        $amount = ((int) $parts[2] * 100) + (int) str_pad($parts[3] ?? '', 2, '0');

        return ($parts[1] === '-' ? -1 : 1) * $amount;
    }

    public static function decimal(int $minor): string
    {
        return ($minor < 0 ? '-' : '').intdiv(abs($minor), 100).'.'.str_pad((string) (abs($minor) % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function percent(int $minor, string|int|float $percent): int
    {
        $basisPoints = self::minor($percent);
        if ($minor < 0 || $basisPoints < 0 || $basisPoints > 10000) {
            throw ValidationException::withMessages(['rate' => 'Rate must be between zero and 100 percent.']);
        }

        return intdiv($minor * $basisPoints + 5000, 10000);
    }
}
