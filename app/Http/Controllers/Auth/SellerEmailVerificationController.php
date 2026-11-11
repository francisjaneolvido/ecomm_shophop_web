<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SellerVerificationCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerEmailVerificationController extends Controller
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

        return view('auth.modals.seller-verify-email', [
            'email' => $this->maskEmail($user->email),
            'retryAfter' => app(\App\Services\AccountVerificationCodes::class)->retryAfter($user),
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
                'seller_verification_user_id'
            );

            return redirect('/')
                ->with(
                    'status',
                    'Your email is already verified.'
                );
        }


        try {
            app(\App\Services\AccountVerificationCodes::class)->verify($user, $validated['code']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email verification failed.', ['exception' => $e::class]);
            return back()->withErrors(['code' => 'We could not complete your email verification. Please try again.']);
        }

        $request->session()->forget(
            'seller_verification_user_id'
        );


        /*
        |--------------------------------------------------------------------------
        | WAIT FOR ADMIN APPROVAL
        |--------------------------------------------------------------------------
        */

        return redirect('/')
            ->with(
                'status',
                'Email verified successfully! Your seller registration is now waiting for administrator approval.'
            );
    }


    public function resend(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $user = $this->getVerificationUser($request);
        if (! $user || $user->email_verified_at) {
            $message = 'Your verification session is no longer active. Please sign in to continue.';
            return $request->expectsJson()
                ? response()->json(['message' => $message], 419)
                : redirect('/')->withErrors(['email' => $message]);
        }

        $codes = app(\App\Services\AccountVerificationCodes::class);
        try {
            $codes->send($user, SellerVerificationCodeNotification::class);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = $e->errors()['code'][0];
            return $request->expectsJson()
                ? response()->json(['message' => $message, 'retry_after' => $codes->retryAfter($user)], 429)
                : back()->withErrors(['code' => $message]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Verification dispatch failed.', ['exception' => $e::class]);
            $message = 'Email delivery is currently unavailable. Please try again later or contact support.';
            return $request->expectsJson()
                ? response()->json(['message' => $message], 503)
                : back()->withErrors(['code' => $message]);
        }

        $message = 'Verification email accepted for sending. Check your inbox and spam folder.';
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'retry_after' => $codes->retryAfter($user)])
            : back()->with('status', $message);
    }


    private function getVerificationUser(Request $request): ?User
    {
        $userId = $request->session()->get(
            'seller_verification_user_id'
        );

        if (! $userId) {
            return null;
        }

        return User::where('id', $userId)
            ->where('account_type', 'seller')
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