<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAccountController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $query = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->latest();

        $query->when($filter === 'buyers', fn ($q) => $q->where('account_type', 'buyer'))
            ->when($filter === 'sellers', fn ($q) => $q->where('account_type', 'seller'))
            ->when($filter === 'logistics', fn ($q) => $q->where('account_type', 'logistics'))
            ->when($filter === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($filter === 'suspended', fn ($q) => $q->where('status', 'suspended'));

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => User::where('account_type', '!=', 'admin')->count(),
            'buyers' => User::where('account_type', 'buyer')->count(),
            'sellers' => User::where('account_type', 'seller')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];

        return view('admin.user-accounts', compact('users', 'filter', 'counts'));
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);

        return back()->with('status', "{$user->display_name}'s account has been approved.");
    }

    public function reject(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);

        return back()->with('status', "{$user->display_name}'s account has been rejected.");
    }

    public function suspend(User $user): RedirectResponse
    {
        $user->update(['status' => 'suspended']);

        return back()->with('status', "{$user->display_name}'s account has been suspended.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);

        return back()->with('status', "{$user->display_name}'s account has been reactivated.");
    }
}