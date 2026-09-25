<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRider
{
    public function handle(Request $request, Closure $next): Response
    {
        // Each request rechecks the persisted Rider and partner state, so suspension revokes an open session.
        if (Auth::guard('web')->check()) {
            abort(403);
        }
        if (! Auth::guard('rider')->check()) {
            return redirect()->route('rider.login');
        }
        // Laravel caches the guard model in a request lifecycle; reload persisted eligibility for suspension.
        $rider = Auth::guard('rider')->user()->fresh(['partner.user']);
        if (! $rider || $rider->status !== 'active' || ! $rider->email
            || ! $rider->password || $rider->partner?->user?->status !== 'approved') {
            abort(403);
        }

        return $next($request);
    }
}
