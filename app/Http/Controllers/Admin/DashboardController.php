<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingRegistrations = User::where('status', 'pending')->count();

        $activeUserAccounts = User::where('status', 'approved')
            ->where('account_type', '!=', 'admin')
            ->count();

        // Dispute and Commission domains are unavailable; only persisted registration/account metrics are supplied.

        $recentRegistrations = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingRegistrations',
            'activeUserAccounts',
            // Availability is disclosed in Blade without fabricated dispute counts or calculated earnings.
            'recentRegistrations',
        ));
    }
}
