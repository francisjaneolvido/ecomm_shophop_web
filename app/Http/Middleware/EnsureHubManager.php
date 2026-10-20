<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Province hub portal (/hub) is only for active branch-staff accounts with the "manager" role
 * who belong to a province hub. Main hub managers go to the main console; scanners (and
 * everyone else) are sent back to their assigned branch workspace.
 */
class EnsureHubManager
{
    public function handle(Request $request, Closure $next)
    {
        $staff = Auth::guard('logistics_staff')->user();

        if (! $staff || $staff->role !== 'manager' || $staff->status !== 'active') {
            return redirect()->route('logistics.staff.workspace');
        }

        // Main hub managers do not belong in the province hub portal.
        if ($staff->sortingCenter?->is_main) {
            return redirect()->route('logistics.dashboard');
        }

        return $next($request);
    }
}