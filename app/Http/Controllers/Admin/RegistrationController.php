<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        $documentLabels = [
            'buyer' => 'Valid ID',
            'seller' => 'Valid ID & Business Permit',
            'logistics' => 'Business Permit & Agreement',
        ];

        $registrations = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->display_name,
                'email' => $user->email,
                'role' => $user->account_type,
                'status' => $user->status,
                'initials' => $user->initials,
                'submitted' => $user->created_at->diffForHumans(),
                'document' => $documentLabels[$user->account_type] ?? 'Documents',
            ]);

        return view('admin.registration', compact('registrations'));
    }
}