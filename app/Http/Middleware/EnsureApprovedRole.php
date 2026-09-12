<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | MUST BE LOGGED IN
        |--------------------------------------------------------------------------
        */

        if (! Auth::check()) {
            return redirect()
                ->route('home')
                ->with('open_modal', 'login')
                ->with('login_notice', [
                    'type' => 'warning',
                    'title' => 'Sign In Required',
                    'message' => 'Please sign in to access this page.',
                ]);
        }

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | CORRECT ACCOUNT ROLE
        |--------------------------------------------------------------------------
        |
        | Example:
        | Buyer cannot manually visit /seller/dashboard.
        |
        */

        if ($user->account_type !== $role) {
            abort(403, 'You are not authorized to access this page.');
        }


        /*
        |--------------------------------------------------------------------------
        | EMAIL MUST BE VERIFIED
        |--------------------------------------------------------------------------
        */

        if (
            in_array($role, ['buyer', 'seller'], true) &&
            is_null($user->email_verified_at)
        ) {
            $userId = $user->id;

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($role === 'buyer') {
                $request->session()->put(
                    'buyer_verification_user_id',
                    $userId
                );

                return redirect()
                    ->route('buyer.verify-email.show')
                    ->with(
                        'status',
                        'Please verify your email before accessing your buyer account.'
                    );
            }

            $request->session()->put(
                'seller_verification_user_id',
                $userId
            );

            return redirect()
                ->route('seller.verify-email.show')
                ->with(
                    'status',
                    'Please verify your email before accessing your seller account.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ACCOUNT MUST BE APPROVED
        |--------------------------------------------------------------------------
        */

        if ($user->status !== 'approved') {
            $status = $user->status;

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $notice = match ($status) {
                'pending' => [
                    'type' => 'warning',
                    'title' => 'Approval Pending',
                    'message' => 'Your account is still waiting for administrator approval.',
                ],

                'rejected' => [
                    'type' => 'error',
                    'title' => 'Application Rejected',
                    'message' => 'Your account application was not approved. Please contact ShopHop support for more information.',
                ],

                'suspended' => [
                    'type' => 'error',
                    'title' => 'Account Suspended',
                    'message' => 'Your ShopHop account is currently suspended. Please contact ShopHop support.',
                ],

                default => [
                    'type' => 'warning',
                    'title' => 'Account Unavailable',
                    'message' => 'Your account is currently unavailable.',
                ],
            };

            return redirect()
                ->route('home')
                ->with('login_notice', $notice);
        }


        /*
        |--------------------------------------------------------------------------
        | ACCESS GRANTED
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}