<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Return everything the person filled up during registration, for the
     * "View" modal. Field set depends on account_type.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['buyer', 'seller', 'logisticsPartner.coverageAreas']);

        $fileUrl = fn (?string $path) => $path ? Storage::url($path) : null;

        $data = [
            'id' => $user->id,
            'email' => $user->email,
            'account_type' => $user->account_type,
            'status' => $user->status,
            'display_name' => $user->display_name,
            'created_at' => $user->created_at->format('M d, Y g:i A'),
        ];

        if ($user->account_type === 'buyer' && $user->buyer) {
            $b = $user->buyer;
            $data['fields'] = [
                ['label' => 'First Name', 'value' => $b->first_name],
                ['label' => 'Last Name', 'value' => $b->last_name],
                ['label' => 'Middle Initial', 'value' => $b->middle_initial],
                ['label' => 'Sex', 'value' => $b->sex],
                ['label' => 'Contact No.', 'value' => $b->contact_no],
                ['label' => 'Birthday', 'value' => $b->birthday->format('M d, Y')],
                ['label' => 'Province', 'value' => $b->province_name],
                ['label' => 'Municipality/City', 'value' => $b->municipality_name],
                ['label' => 'Barangay', 'value' => $b->barangay_name],
                ['label' => 'Street Address', 'value' => $b->street_address],
            ];
            $data['files'] = [
                ['label' => 'Valid ID', 'url' => $fileUrl($b->valid_id_path)],
            ];
        }

        if ($user->account_type === 'seller' && $user->seller) {
            $s = $user->seller;
            $data['fields'] = [
                ['label' => 'First Name', 'value' => $s->first_name],
                ['label' => 'Last Name', 'value' => $s->last_name],
                ['label' => 'Middle Initial', 'value' => $s->middle_initial],
                ['label' => 'Sex', 'value' => $s->sex],
                ['label' => 'Contact No.', 'value' => $s->contact_no],
                ['label' => 'Birthday', 'value' => $s->birthday->format('M d, Y')],
                ['label' => 'Province', 'value' => $s->province_name],
                ['label' => 'Municipality/City', 'value' => $s->municipality_name],
                ['label' => 'Barangay', 'value' => $s->barangay_name],
                ['label' => 'Street Address', 'value' => $s->street_address],
                ['label' => 'Business Name', 'value' => $s->business_name],
                ['label' => 'Business Category', 'value' => $s->business_category],
            ];
            $data['files'] = [
                ['label' => 'Valid ID', 'url' => $fileUrl($s->valid_id_path)],
                ['label' => 'Business Permit', 'url' => $fileUrl($s->business_permit_path)],
            ];
        }

        if ($user->account_type === 'logistics' && $user->logisticsPartner) {
            $l = $user->logisticsPartner;
            $data['fields'] = [
                ['label' => 'Company Name', 'value' => $l->company_name],
                ['label' => 'Business Registration No.', 'value' => $l->business_registration_no],
                ['label' => 'Line of Business', 'value' => str_replace('_', ' ', $l->line_of_business)],
                ['label' => 'Representative Name', 'value' => trim("{$l->rep_first_name} {$l->rep_last_name}")],
                ['label' => 'Representative ID No.', 'value' => $l->rep_id_number],
                ['label' => 'Representative Sex', 'value' => $l->rep_sex],
                ['label' => 'Representative Birthday', 'value' => $l->rep_birthday->format('M d, Y')],
                ['label' => 'Contact No.', 'value' => $l->contact_no],
                ['label' => 'Region', 'value' => $l->region],
                ['label' => 'Province', 'value' => $l->province],
                ['label' => 'Municipality/City', 'value' => $l->municipality],
                ['label' => 'Barangay', 'value' => $l->barangay],
                ['label' => 'Street No.', 'value' => $l->street_no],
                ['label' => 'Unit No.', 'value' => $l->unit_no],
                ['label' => 'Agreement Signed By', 'value' => $l->agreement_rep_name],
                ['label' => 'Agreement Date', 'value' => $l->agreement_date->format('M d, Y')],
                ['label' => 'Coverage Areas', 'value' => $l->coverageAreas->pluck('area_name')->join(', ') ?: '—'],
            ];
            $data['files'] = [
                ['label' => 'Representative Valid ID', 'url' => $fileUrl($l->rep_valid_id_path)],
                ['label' => 'Business Permit', 'url' => $fileUrl($l->business_permit_path)],
                ['label' => 'Accreditation Docs', 'url' => $fileUrl($l->accreditation_docs_path)],
                ['label' => 'Agreement Signature', 'url' => $fileUrl($l->agreement_signature_path)],
            ];
        }

        return response()->json($data);
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