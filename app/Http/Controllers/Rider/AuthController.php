<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        // Rider credentials have a dedicated entry point without changing marketplace account registration.
        return view('rider.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        // A web User session cannot also enter the Rider route area; submitted Rider IDs play no role.
        if (Auth::guard('web')->check()) {
            abort(403);
        }
        $rider = Rider::with('partner.user')->where('email', $credentials['email'])->first();
        if (! $rider || $rider->status !== 'active' || $rider->partner?->user?->status !== 'approved'
            || ! Auth::guard('rider')->attempt($credentials)) {
            return back()->withErrors(['email' => 'Rider credentials are unavailable.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->route('rider.deliveries.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Logout invalidates the Rider session token before another actor uses this browser.
        Auth::guard('rider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('rider.login');
    }
}
