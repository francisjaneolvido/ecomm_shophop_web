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

        // TODO: replace with real counts once a disputes table and an
        // orders/commissions table exist.
        $openDisputes = 0;
        $commissionThisMonth = 0;

        $recentRegistrations = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingRegistrations',
            'activeUserAccounts',
            'openDisputes',
            'commissionThisMonth',
            'recentRegistrations',
        ));
    }
}