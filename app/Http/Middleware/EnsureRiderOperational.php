<?php

namespace App\Http\Middleware;

use App\Models\Logistics\Rider;
use Closure;
use Illuminate\Http\Request;

class EnsureRiderOperational
{
    public function handle(Request $request, Closure $next)
    {
        $rider = $request->user();
        abort_unless($rider instanceof Rider, 403);
        $rider = $rider->fresh(['partner.user', 'sortingCenter']);
        abort_unless($rider && $rider->canOperate() && $rider->email_verified_at
            && $rider->partner?->user?->status === 'approved', 403);
        if ($rider->managementProfile()) {
            abort_unless($rider->sortingCenter && $rider->sortingCenter->status === 'active' && ! $rider->sortingCenter->archived_at
                && (int) $rider->sortingCenter->logistics_partner_id === (int) $rider->logistics_partner_id, 403);
        }
        $token = $request->user()->currentAccessToken();
        abort_if($token && ! $request->user()->tokenCan('rider:operations') && ! $request->user()->tokenCan('*'), 403);

        return $next($request);
    }
}
