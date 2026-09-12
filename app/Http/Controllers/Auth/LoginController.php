<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE LOGIN
        |--------------------------------------------------------------------------
        */

        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $remember = $request->boolean('remember');


        /*
        |--------------------------------------------------------------------------
        | ATTEMPT LOGIN
        |--------------------------------------------------------------------------
        */

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => 'Incorrect email or password.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | EMAIL VERIFICATION CHECK
        |--------------------------------------------------------------------------
        |
        | Buyer and Seller only.
        |
        | Kapag hindi pa talaga verified ang email, saka lang pupunta
        | sa OTP verification page.
        |
        */

        if (
            in_array($user->account_type, ['buyer', 'seller'], true) &&
            is_null($user->email_verified_at)
        ) {
            $userId = $user->id;
            $accountType = $user->account_type;

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($accountType === 'buyer') {
                $request->session()->put(
                    'buyer_verification_user_id',
                    $userId
                );

                return redirect()
                    ->route('buyer.verify-email.show')
                    ->with(
                        'status',
                        'Please complete your email verification before signing in.'
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
                    'Please complete your email verification before signing in.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ADMIN APPROVAL CHECK
        |--------------------------------------------------------------------------
        |
        | Important:
        | Dito na tayo kapag verified na ang email.
        |
        | Pending account:
        | - HINDI babalik sa OTP page
        | - HINDI makakapasok sa dashboard
        | - Makakakita ng warning toast
        |
        */

        if (
            $user->account_type !== 'admin' &&
            $user->status !== 'approved'
        ) {
            $status = $user->status;

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();


            /*
            |--------------------------------------------------------------------------
            | PENDING ADMIN APPROVAL
            |--------------------------------------------------------------------------
            */

            if ($status === 'pending') {
                return redirect()
                    ->route('home')
                    ->with('login_notice', [
                        'type' => 'warning',
                        'title' => 'Approval Pending',
                        'message' => 'Your email has been verified successfully, but your account is still waiting for administrator approval. You will be able to sign in once your application has been approved.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | REJECTED
            |--------------------------------------------------------------------------
            */

            if ($status === 'rejected') {
                return redirect()
                    ->route('home')
                    ->with('login_notice', [
                        'type' => 'error',
                        'title' => 'Application Rejected',
                        'message' => 'Your account application was not approved. Please contact ShopHop support if you need more information.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | SUSPENDED
            |--------------------------------------------------------------------------
            */

            if ($status === 'suspended') {
                return redirect()
                    ->route('home')
                    ->with('login_notice', [
                        'type' => 'error',
                        'title' => 'Account Suspended',
                        'message' => 'Your ShopHop account is currently suspended. Please contact ShopHop support for assistance.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('home')
                ->with('login_notice', [
                    'type' => 'warning',
                    'title' => 'Account Unavailable',
                    'message' => 'Your account is currently unavailable for sign in.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESSFUL LOGIN
        |--------------------------------------------------------------------------
        */

        return match ($user->account_type) {
            'admin' =>
                redirect()->route('admin.dashboard'),

            'buyer' =>
                redirect()->route('buyer.dashboard'),

            'seller' =>
                redirect()->route('seller.dashboard'),

            'logistics' =>
                redirect()->route('logistics.dashboard'),

            default =>
                redirect()->route('home'),
        };
    }


    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}