<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;

class AccountManagementController extends Controller
{
    /**
     * GET /admin/accounts
     */
    public function index()
    {
        $admins = Admin::with('user')
            ->whereHas('user', function ($q) {
                $q->where('account_type', 'admin');
            })
            ->orderByDesc('created_at')
            ->get();

        $counts = [
            'total'    => $admins->count(),
            'active'   => $admins->filter(fn ($a) => $a->user->status === 'approved')->count(),
            'disabled' => $admins->filter(fn ($a) => $a->user->status === 'suspended')->count(),
        ];

        return view('admin.account-management', [
            'admins' => $admins,
            'counts' => $counts,
        ]);
    }

    /**
     * PATCH /admin/accounts/{admin}/disable
     */
    public function disable(Admin $admin)
    {
        // Panatilihing hindi puwedeng i-disable ang sarili mong account
        if ($admin->user_id === auth()->id()) {
            return back()->with('status', 'Hindi mo pwedeng i-disable ang sarili mong account.');
        }

        $admin->user()->update(['status' => 'suspended']);

        return back()->with('status', "{$admin->full_name} has been disabled.");
    }

    /**
     * PATCH /admin/accounts/{admin}/enable
     */
    public function enable(Admin $admin)
    {
        $admin->user()->update(['status' => 'approved']);

        return back()->with('status', "{$admin->full_name} has been enabled.");
    }

    /**
     * Halimbawang store para sa "Add Admin Account" button.
     * POST /admin/accounts
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8',
            'role'       => 'required|in:super_admin,compliance_officer,support_staff',
        ]);

        $user = User::create([
            'email'        => $validated['email'],
            'password'     => bcrypt($validated['password']),
            'account_type' => 'admin',
            'status'       => 'approved',
        ]);

        Admin::create([
            'user_id'    => $user->id,
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'role'       => $validated['role'],
        ]);

        return back()->with('status', 'Admin account created.');
    }
}