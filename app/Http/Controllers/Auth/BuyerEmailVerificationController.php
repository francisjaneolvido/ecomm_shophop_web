<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\BuyerVerificationCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class BuyerEmailVerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->getVerificationUser($request);

        if (! $user) {
            return redirect('/')
                ->withErrors([
                    'email' => 'Your verification session has expired.',
                ]);
        }

        if ($user->email_verified_at) {
            return redirect('/')
                ->with(
                    'status',
                    'Your email is already verified.'
                );
        }

        return view('auth.modals.verify-email', [
            'email' => $this->maskEmail($user->email),
        ]);
    }


    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $user = $this->getVerificationUser($request);

        if (! $user) {
            return redirect('/')
                ->withErrors([
                    'email' => 'Your verification session has expired.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ALREADY VERIFIED
        |--------------------------------------------------------------------------
        */

        if ($user->email_verified_at) {
            $request->session()->forget(
                'buyer_verification_user_id'
            );

            return redirect('/')
                ->with(
                    'status',
                    'Your email is already verified.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GET OTP
        |--------------------------------------------------------------------------
        */

        $verification = EmailVerificationCode::where(
            'user_id',
            $user->id
        )->first();

        if (! $verification) {
            return back()
                ->withErrors([
                    'code' => 'No verification code was found. Please request a new code.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EXPIRED OTP
        |--------------------------------------------------------------------------
        */

        if ($verification->expires_at->isPast()) {
            return back()
                ->withErrors([
                    'code' => 'Your verification code has expired. Please request a new code.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MAX ATTEMPTS
        |--------------------------------------------------------------------------
        */

        if ($verification->attempts >= 5) {
            return back()
                ->withErrors([
                    'code' => 'Too many incorrect attempts. Please request a new verification code.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK OTP
        |--------------------------------------------------------------------------
        */

        if (! Hash::check(
            $validated['code'],
            $verification->code_hash
        )) {
            $verification->increment('attempts');

            return back()
                ->withErrors([
                    'code' => 'The verification code is incorrect.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EMAIL
        |--------------------------------------------------------------------------
        |
        | Save the verification timestamp first.
        | Only delete the OTP after the timestamp is confirmed in the database.
        |
        */

        try {
            DB::transaction(function () use ($user, $verification) {

                $user->forceFill([
                    'email_verified_at' => now(),
                ]);

                $user->saveOrFail();

                /*
                | Reload directly from database.
                */
                $user->refresh();

                /*
                | Do not continue if verification timestamp
                | somehow was not persisted.
                */
                if (is_null($user->email_verified_at)) {
                    throw new \RuntimeException(
                        'Email verification timestamp was not saved.'
                    );
                }

                /*
                | OTP can now safely be deleted.
                */
                $verification->delete();
            });

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'code' => 'We could not complete your email verification. Please try again.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR BUYER VERIFICATION SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->forget(
            'buyer_verification_user_id'
        );


        /*
        |--------------------------------------------------------------------------
        | WAIT FOR ADMIN APPROVAL
        |--------------------------------------------------------------------------
        */

        return redirect('/')
            ->with(
                'status',
                'Email verified successfully! Your registration is now waiting for administrator approval.'
            );
    }


    public function resend(Request $request): RedirectResponse
    {
        $user = $this->getVerificationUser($request);

        if (! $user) {
            return redirect('/')
                ->withErrors([
                    'email' => 'Your verification session has expired.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ALREADY VERIFIED
        |--------------------------------------------------------------------------
        */

        if ($user->email_verified_at) {
            $request->session()->forget(
                'buyer_verification_user_id'
            );

            return redirect('/')
                ->with(
                    'status',
                    'Your email is already verified.'
                );
        }


        $verification = EmailVerificationCode::where(
            'user_id',
            $user->id
        )->first();


        /*
        |--------------------------------------------------------------------------
        | RESEND COOLDOWN
        |--------------------------------------------------------------------------
        */

        if (
            $verification &&
            $verification->last_sent_at &&
            $verification->last_sent_at->gt(now()->subMinute())
        ) {
            return back()
                ->withErrors([
                    'code' => 'Please wait at least 60 seconds before requesting another code.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE NEW OTP
        |--------------------------------------------------------------------------
        */

        $code = (string) random_int(100000, 999999);


        /*
        |--------------------------------------------------------------------------
        | SAVE NEW OTP
        |--------------------------------------------------------------------------
        */

        EmailVerificationCode::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'last_sent_at' => now(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | SEND OTP
        |--------------------------------------------------------------------------
        */

        try {
            $user->notify(
                new BuyerVerificationCodeNotification($code)
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'code' => 'We could not send a new verification code. Please try again later.',
                ]);
        }


        return back()
            ->with(
                'status',
                'A new verification code has been sent to your email.'
            );
    }


    private function getVerificationUser(Request $request): ?User
    {
        $userId = $request->session()->get(
            'buyer_verification_user_id'
        );

        if (! $userId) {
            return null;
        }

        return User::where('id', $userId)
            ->where('account_type', 'buyer')
            ->first();
    }


    private function maskEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email, 2);

        $visibleLength = min(
            2,
            strlen($username)
        );

        $visible = substr(
            $username,
            0,
            $visibleLength
        );

        $hidden = str_repeat(
            '*',
            max(
                strlen($username) - $visibleLength,
                3
            )
        );

        return $visible . $hidden . '@' . $domain;
    }
}