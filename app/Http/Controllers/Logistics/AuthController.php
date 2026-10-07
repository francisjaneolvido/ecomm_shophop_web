<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->account_type === 'logistics') {
            return redirect()->route('logistics.dashboard');
        }

        return view('logistics.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Ang account_type ay extra na kondisyon, kaya buyer/seller/admin ay hindi makakapasok dito.
        $attempt = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'account_type' => 'logistics',
        ], $request->boolean('remember'));

        if (! $attempt) {
            return back()
                ->withErrors(['email' => 'Incorrect email or password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status !== 'approved') {
            $status = $user->status;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $notice = match ($status) {
                'pending' => [
                    'type' => 'warning',
                    'title' => 'Approval Pending',
                    'message' => 'Your application is still waiting for administrator approval.',
                ],
                'rejected' => [
                    'type' => 'error',
                    'title' => 'Application Rejected',
                    'message' => 'Your application was not approved. Please contact ShopHop support.',
                ],
                'suspended' => [
                    'type' => 'error',
                    'title' => 'Account Suspended',
                    'message' => 'Your account is suspended. Please contact ShopHop support.',
                ],
                default => [
                    'type' => 'warning',
                    'title' => 'Account Unavailable',
                    'message' => 'Your account is currently unavailable for sign in.',
                ],
            };

            return redirect()->route('logistics.login')->with('login_notice', $notice);
        }

        return redirect()->route('logistics.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logistics.login');
    }
}