<?php

namespace App\Services;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AccountVerificationCodes
{
    public function remaining(User $user): int
    {
        $sent = EmailVerificationCode::where('user_id', $user->id)->value('last_sent_at');
        return $sent ? max(0, 60 - (now()->timestamp - \Illuminate\Support\Carbon::parse($sent)->timestamp)) : 0;
    }

    public function retryAfter(User $user): int
    {
        $key = 'account-verification-send:'.$user->id;
        return max($this->remaining($user), RateLimiter::tooManyAttempts($key, 5) ? RateLimiter::availableIn($key) : 0);
    }

    public function send(User $user, string $notificationClass): void
    {
        DB::transaction(function () use ($user, $notificationClass) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->email_verified_at) {
                throw ValidationException::withMessages(['code' => 'This email has already been verified.']);
            }
            $remaining = $this->remaining($user);
            if ($remaining > 0) {
                throw ValidationException::withMessages(['code' => "Please wait {$remaining} seconds before requesting another code."]);
            }
            $key = 'account-verification-send:'.$user->id;
            if (RateLimiter::tooManyAttempts($key, 5)) {
                throw ValidationException::withMessages(['code' => 'Too many code requests. Please try again later.']);
            }
            RateLimiter::hit($key, 600);
            $oldHash = EmailVerificationCode::where('user_id', $user->id)->value('code_hash');
            do {
                $code = (string) random_int(100000, 999999);
            } while ($oldHash && Hash::check($code, $oldHash));
            EmailVerificationCode::updateOrCreate(['user_id' => $user->id], [
                'code_hash' => Hash::make($code), 'attempts' => 0,
                'expires_at' => now()->addMinutes(5), 'last_sent_at' => now(),
            ]);
            // On transport failure the transaction preserves the previous active code.
            app(VerificationMail::class)->send($user, new $notificationClass($code));
        });
    }

    public function verify(User $user, string $code): void
    {
        $error = DB::transaction(function () use ($user, $code) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $verification = EmailVerificationCode::where('user_id', $user->id)->lockForUpdate()->first();
            if ($lockedUser->email_verified_at) {
                return 'Your email is already verified.';
            }
            if (! $verification) {
                return 'No verification code was found. Please request a new code.';
            }
            if ($verification->expires_at->lte(now())) {
                return 'Your verification code has expired. Please request a new code.';
            }
            if ($verification->attempts >= 5) {
                return 'Too many incorrect attempts. Please request a new verification code.';
            }
            if (! Hash::check($code, $verification->code_hash)) {
                $verification->increment('attempts');
                return 'The verification code is incorrect.';
            }
            $lockedUser->forceFill(['email_verified_at' => now()])->saveOrFail();
            $verification->delete();
            return null;
        });
        // Throw after committing so incorrect attempt counters cannot roll back.
        if ($error) {
            throw ValidationException::withMessages(['code' => $error]);
        }
    }

}
