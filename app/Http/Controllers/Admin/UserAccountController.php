<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LogisticsApplicationDecisionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
// Account review uses the same private record/slot links as registration review.
use App\Services\RegistrationDocuments;
use Illuminate\View\View;

class UserAccountController extends Controller
{
    /**
     * Approved & suspended Buyer/Seller/Logistics accounts.
     * (Pending/rejected applications live on the separate
     * Account Registrations page.)
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $sort = $request->query('sort', 'newest');
        $search = $request->query('search');

        $base = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('account_type', '!=', 'admin')
            ->whereIn('status', ['approved', 'suspended']);

        $counts = [
            'all' => (clone $base)->count(),
            'buyers' => (clone $base)->where('account_type', 'buyer')->count(),
            'sellers' => (clone $base)->where('account_type', 'seller')->count(),
            'logistics' => (clone $base)->where('account_type', 'logistics')->count(),
            'suspended' => (clone $base)->where('status', 'suspended')->count(),
        ];

        $query = clone $base;

        $query->when($filter === 'buyers', fn ($q) => $q->where('account_type', 'buyer'))
            ->when($filter === 'sellers', fn ($q) => $q->where('account_type', 'seller'))
            ->when($filter === 'logistics', fn ($q) => $q->where('account_type', 'logistics'))
            ->when($filter === 'suspended', fn ($q) => $q->where('status', 'suspended'));

        $all = $query->get();

        if ($search) {
            $needle = strtolower($search);
            $all = $all->filter(fn (User $u) => str_contains(strtolower($u->display_name), $needle)
                || str_contains(strtolower($u->email), $needle));
        }

        $all = (match ($sort) {
            'oldest' => $all->sortBy('created_at'),
            'az' => $all->sortBy(fn (User $u) => strtolower($u->display_name)),
            'za' => $all->sortByDesc(fn (User $u) => strtolower($u->display_name)),
            default => $all->sortByDesc('created_at'),
        })->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $users = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $usersForJs = $users->getCollection()->map(fn (User $u) => $this->toJsUser($u))->values();

        return view('admin.user-accounts', compact('users', 'filter', 'sort', 'counts', 'usersForJs'));
    }

    /**
     * Shape a User (with relations already loaded) into the flat structure
     * the details modal's JavaScript expects.
     */
    private function toJsUser(User $user): array
    {
        // Resolve all sensitive document links from the account profile, never from public storage URLs.
        $documents = RegistrationDocuments::entries($user);

        $phone = null;
        $address = null;
        $sex = null;
        $birthday = null;
        $age = null;
        $businessName = null;
        $businessCategory = null;

        if ($user->account_type === 'buyer' && $user->buyer) {
            $b = $user->buyer;
            $phone = $b->contact_no;
            $address = collect([$b->street_address, $b->barangay_name, $b->municipality_name, $b->province_name])
                ->filter()->implode(', ');
            $sex = $b->sex;
            $birthday = $b->birthday?->format('M d, Y');
            $age = $b->birthday?->age;
        }

        if ($user->account_type === 'seller' && $user->seller) {
            $s = $user->seller;
            $phone = $s->contact_no;
            $address = collect([$s->street_address, $s->barangay_name, $s->municipality_name, $s->province_name])
                ->filter()->implode(', ');
            $sex = $s->sex;
            $birthday = $s->birthday?->format('M d, Y');
            $age = $s->birthday?->age;
            $businessName = $s->business_name;
            $businessCategory = $s->business_category;
        }

        if ($user->account_type === 'logistics' && $user->logisticsPartner) {
            $l = $user->logisticsPartner;
            $phone = $l->contact_no;
            $address = collect([$l->unit_no, $l->street_no, $l->barangay, $l->municipality, $l->province, $l->region])
                ->filter()->implode(', ');
            $sex = $l->rep_sex;
            $birthday = $l->rep_birthday?->format('M d, Y');
            $age = $l->rep_birthday?->age;
            $businessName = $l->company_name;
            $businessCategory = str_replace('_', ' ', $l->line_of_business ?? '');
        }

        return [
            'id' => $user->id,
            'initials' => $user->initials,
            'display_name' => $user->display_name,
            'email' => $user->email,
            'phone' => $phone,
            'address' => $address ?: null,
            'account_type' => $user->account_type,
            'status' => $user->status,
            'verified' => $user->email_verified_at !== null,
            'sex' => $sex,
            'birthday' => $birthday,
            'age' => $age,
            'business_name' => $businessName,
            'business_category' => $businessCategory,
            'joined' => $user->created_at->format('M d, Y').' · '.$user->created_at->diffForHumans(),
            'last_login' => $user->last_login_at?->diffForHumans() ?? 'Never logged in yet',
            'account_no' => '#'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            // Not backed by real tables yet — the modal shows an honest
            // "not tracked yet" placeholder for these instead of fake numbers.
            'stats' => null,
            'notes' => null,
            'activity' => [],
            'documents' => array_values(array_filter($documents, fn ($d) => $d['url'] !== null)),
            'reports' => [],
        ];
    }

    /**
     * Return everything the person filled up during registration, for the
     * "View" modal. Field set depends on account_type.
     */
    public function show(User $user): JsonResponse
    {
        abort_unless($this->isModeratable($user, ['approved', 'suspended']), 404);
        $user->load(['buyer', 'seller', 'logisticsPartner.coverageAreas']);

        // Later account detail retains its eligibility while sharing protected document URLs and MIME metadata.
        $files = RegistrationDocuments::entries($user);

        $data = [
            'id' => $user->id,
            'email' => $user->email,
            'account_type' => $user->account_type,
            'status' => $user->status,
            'display_name' => $user->display_name,
            'created_at' => $user->created_at->format('M d, Y g:i A'),
            'files' => $files,
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
        }

        if ($user->account_type === 'logistics' && $user->logisticsPartner) {
            $l = $user->logisticsPartner;
            $data['fields'] = [
                ['label' => 'Company Name', 'value' => $l->company_name],
                ['label' => 'Business Registration No.', 'value' => $l->business_registration_no],
                ['label' => 'Line of Business', 'value' => str_replace('_', ' ', $l->line_of_business)],
                ['label' => 'Representative Name', 'value' => trim("{$l->rep_first_name} {$l->rep_middle_initial} {$l->rep_last_name}")],
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
        }

        return response()->json($data);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        if (! $this->isModeratable($user, ['pending'], true)) {
            return back()->withErrors(['moderation' => 'Only eligible pending registrations can be approved.']);
        }

        $user->forceFill([
            'status' => 'approved',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ])->save();

        $mailSent = $this->sendLogisticsDecisionEmail($user, true);
        $message = "{$user->display_name}'s account has been approved.";
        if ($user->account_type === 'logistics' && ! $mailSent) {
            $message .= ' The approval was saved, but the notification email could not be sent. Check the mail logs.';
        }

        return back()->with('status', $message);
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        if (! $this->isModeratable($user, ['pending'], true)) {
            return back()->withErrors(['moderation' => 'Only eligible pending registrations can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'rejection_reason' => trim($validated['rejection_reason']),
        ])->save();

        $mailSent = $this->sendLogisticsDecisionEmail($user, false);
        $message = "{$user->display_name}'s account has been rejected.";
        if ($user->account_type === 'logistics' && ! $mailSent) {
            $message .= ' The rejection was saved, but the notification email could not be sent. Check the mail logs.';
        }

        return back()->with('status', $message);
    }

    private function sendLogisticsDecisionEmail(User $user, bool $approved): bool
    {
        if ($user->account_type !== 'logistics') {
            return true;
        }

        try {
            $user->notify(new LogisticsApplicationDecisionNotification(
                approved: $approved,
                rejectionReason: $user->rejection_reason,
            ));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public function suspend(User $user): RedirectResponse
    {
        if (! $this->isModeratable($user, ['approved'])) {
            return back()->withErrors(['moderation' => 'Only active Buyer, Seller, or Logistics accounts can be suspended.']);
        }

        $user->update(['status' => 'suspended']);

        return back()->with('status', "{$user->display_name}'s account has been suspended.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        if (! $this->isModeratable($user, ['suspended'])) {
            return back()->withErrors(['moderation' => 'Only suspended Buyer, Seller, or Logistics accounts can be reactivated.']);
        }

        $user->update(['status' => 'approved']);

        return back()->with('status', "{$user->display_name}'s account has been reactivated.");
    }

    private function isModeratable(User $user, array $statuses, bool $registration = false): bool
    {
        // Registration decisions require verified Buyer/Seller email; later account transitions do not.
        return in_array($user->account_type, ['buyer', 'seller', 'logistics'], true)
            && in_array($user->status, $statuses, true)
            && (! $registration || $user->email_verified_at !== null);
    }
}
