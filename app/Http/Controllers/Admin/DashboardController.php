<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Finance\OrderFinancial;
use App\Models\Logistics\CodSettlement;
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

        $recentRegistrations = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->latest()
            ->take(5)
            ->get();

        // COD custody, straight from cod_settlements (no fabricated numbers).
        $bucket = fn ($query, string $column = 'expected_amount') => [
            'count'  => (clone $query)->count(),
            'amount' => (float) (clone $query)->sum($column),
        ];

        $cod = [
            // Cash collected by riders, not yet remitted to Logistics.
            'with_riders' => $bucket(
                CodSettlement::where('status', CodSettlement::COLLECTED)
            ),
            // Rider remitted / Logistics reconciled, but Logistics has not yet remitted to ShopHop.
            'with_logistics' => $bucket(
                CodSettlement::whereIn('status', [CodSettlement::REMITTED, CodSettlement::RECONCILED])
                    ->whereNull('admin_remitted_at')
            ),
            // Logistics says it remitted; Admin has not confirmed receipt yet.
            'awaiting_confirmation' => $bucket(
                CodSettlement::whereNotNull('admin_remitted_at')
                    ->whereNull('admin_received_at')
            ),
            // Admin-confirmed receipts.
            'received' => $bucket(
                CodSettlement::whereNotNull('admin_received_at'),
                'admin_received_amount'
            ),
        ];

        $pendingCodConfirmations = $cod['awaiting_confirmation']['count'];

        // Commission: same basis as the Finance page (completed orders only).
        $completedFinancials = OrderFinancial::whereHas(
            'order',
            fn ($q) => $q->where('status', Order::STATUS_COMPLETED)
        );
        $commissionTotal   = (float) (clone $completedFinancials)->sum('commission');
        $platformNetTotal  = (float) (clone $completedFinancials)->sum('platform_net');

        return view('admin.dashboard', compact(
            'pendingRegistrations',
            'activeUserAccounts',
            'recentRegistrations',
            'cod',
            'pendingCodConfirmations',
            'commissionTotal',
            'platformNetTotal',
        ));
    }
}