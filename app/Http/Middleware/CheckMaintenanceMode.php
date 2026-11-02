<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Only the marketplace goes into maintenance, not the logistics host.
        if ($request->getHost() !== config('app.main_domain')) {
            return $next($request);
        }

        $setting = PlatformSetting::current();

        if (! $setting->maintenance_mode) {
            return $next($request);
        }

        // Let admins reach the login and admin pages so they can turn maintenance off.
        if ($request->is('admin', 'admin/*', 'login', 'logout', 'up')) {
            return $next($request);
        }

        // Signed-in admins can browse the whole site normally.
        if ($request->user() && DB::table('admins')->where('user_id', $request->user()->id)->exists()) {
            return $next($request);
        }

        abort(503, $setting->maintenance_message ?: "We're under maintenance. Please check back soon.");
    }
}