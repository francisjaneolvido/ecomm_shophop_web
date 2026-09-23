<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private const ROLES = ['buyer', 'seller', 'logistics'];

    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'pending');
        $role = $request->get('role', 'all');
        $sort = $request->get('sort', 'newest');
        $search = $request->get('search');

        $base = User::with([
        'buyer',
        'seller',
        'logisticsPartner',
    ])
    ->whereIn('account_type', self::ROLES)
    ->where(function ($query) {

        /*
        |--------------------------------------------------------------------------
        | Buyer / Seller
        |--------------------------------------------------------------------------
        |
        | Huwag ipakita sa admin hangga't hindi verified ang email.
        |
        */

        $query->where(function ($q) {
            $q->whereIn('account_type', [
                    'buyer',
                    'seller',
                ])
                ->whereNotNull('email_verified_at');
        })

        /*
        |--------------------------------------------------------------------------
        | Logistics
        |--------------------------------------------------------------------------
        |
        | Wala pa tayong real email OTP flow sa logistics,
        | kaya visible muna sila normally.
        |
        */

        ->orWhere(
            'account_type',
            'logistics'
        );
    });

        // Summary card counts — hango sa buong non-admin user set, hindi lang sa current filter
        $counts = [
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'buyer' => (clone $base)->where('account_type', 'buyer')->count(),
            'seller' => (clone $base)->where('account_type', 'seller')->count(),
            'logistics' => (clone $base)->where('account_type', 'logistics')->count(),
        ];

        $query = (clone $base)->where('status', $filter);

        if ($role !== 'all') {
            $query->where('account_type', $role);
        }

        if ($search) {
            $needle = "%{$search}%";
            $query->where(function ($q) use ($needle) {
                $q->where('email', 'like', $needle)
                    ->orWhereHas('buyer', fn ($b) => $b->where('first_name', 'like', $needle)->orWhere('last_name', 'like', $needle))
                    ->orWhereHas('seller', fn ($s) => $s->where('first_name', 'like', $needle)->orWhere('last_name', 'like', $needle)->orWhere('business_name', 'like', $needle))
                    ->orWhereHas('logisticsPartner', fn ($l) => $l->where('company_name', 'like', $needle));
            });
        }

        $filtered = $query->get();

        // Sort — sa collection level dahil "name" ay derived (buyer/seller/logistics profile)
        $filtered = (match ($sort) {
    'oldest' => $filtered->sortBy('created_at'),
    'az' => $filtered->sortBy(fn ($u) => strtolower($u->display_name)),
    'za' => $filtered->sortByDesc(fn ($u) => strtolower($u->display_name)),
    default => $filtered->sortByDesc('created_at'), // newest
})->values();

        $page = $request->get('page', 1);
        $perPage = 8;
        $registrations = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $documentLabels = [
            'buyer' => 'Valid ID',
            'seller' => 'Valid ID & Business Permit',
            'logistics' => 'Business Permit & Agreement',
        ];

        return view('admin.registration', [
            'registrations' => $registrations,
            'counts' => $counts,
            'filter' => $filter,
            'role' => $role,
            'sort' => $sort,
            'documentLabels' => $documentLabels,
        ]);
    }

    /**
     * AJAX endpoint — full detail payload for the "View" modal.
     */
    public function show(User $user): JsonResponse
    {
        // The detail endpoint must use the same eligible registration set as the queue, not any guessed User id.
        abort_unless(
            in_array($user->account_type, self::ROLES, true)
                && in_array($user->status, ['pending', 'approved', 'rejected'], true)
                && ($user->account_type === 'logistics' || $user->email_verified_at !== null),
            404
        );

        $user->load(['buyer', 'seller', 'logisticsPartner']);

        // Registration files live on role profiles; User has no document or decision-history relation.
        $files = match ($user->account_type) {
            'buyer' => ['Valid ID' => $user->buyer?->valid_id_path],
            'seller' => [
                'Valid ID' => $user->seller?->valid_id_path,
                'Business Permit' => $user->seller?->business_permit_path,
            ],
            'logistics' => [
                'Representative Valid ID' => $user->logisticsPartner?->rep_valid_id_path,
                'Business Permit' => $user->logisticsPartner?->business_permit_path,
                'Accreditation Docs' => $user->logisticsPartner?->accreditation_docs_path,
                'Agreement Signature' => $user->logisticsPartner?->agreement_signature_path,
            ],
        };
        $docs = collect($files)->map(fn ($path, $label) => [
            'label' => $label,
            'status' => $path ? 'submitted' : 'missing',
            'url' => $path ? Storage::disk('public')->url($path) : null,
        ])->values();
        $submittedCount = collect($docs)->where('status', 'submitted')->count();
        $totalCount = $docs->count();

        $profile = match ($user->account_type) {
            'buyer' => $user->buyer,
            'seller' => $user->seller,
            'logistics' => $user->logisticsPartner,
            default => null,
        };

        return response()->json([
            'id' => $user->id,
            'initials' => $user->initials,
            'name' => $user->display_name,
            'email' => $user->email,
            'role' => $user->account_type,
            'status' => $user->status,
            'phone' => $profile?->contact_no,
            'sex' => $profile?->sex ?? $profile?->rep_sex,
            'birthday' => $profile?->birthday?->format('M d, Y') ?? $profile?->rep_birthday?->format('M d, Y'),
            'age' => $profile?->birthday?->age ?? $profile?->rep_birthday?->age,
            'address' => $this->buildAddress($user),
            'business_name' => $user->seller?->business_name ?? $user->logisticsPartner?->company_name,
            'business_category' => $user->seller?->business_category
                ?? ($user->logisticsPartner ? str($user->logisticsPartner->line_of_business)->replace('_', ' ')->title() : null),
            'reg_no' => '#'.str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'submitted_at' => $user->created_at->format('M d, Y g:ia').' · '.$user->created_at->diffForHumans(),
            'docs_summary' => [
                'value' => "{$submittedCount}/{$totalCount}",
                'sub' => $submittedCount === $totalCount ? 'All documents complete' : ($totalCount - $submittedCount).' document(s) incomplete',
            ],
            'documents' => $docs,
            'notes' => null,
            'rejection_reason' => null,
            'activity' => [],
            'reports' => [],
        ]);
    }

    private function buildAddress(User $user): ?string
    {
        return match ($user->account_type) {
            'buyer' => $user->buyer
                ? "{$user->buyer->street_address}, {$user->buyer->barangay_name}, {$user->buyer->municipality_name}, {$user->buyer->province_name}"
                : null,
            'seller' => $user->seller
                ? "{$user->seller->street_address}, {$user->seller->barangay_name}, {$user->seller->municipality_name}, {$user->seller->province_name}"
                : null,
            'logistics' => $user->logisticsPartner
                ? "{$user->logisticsPartner->unit_no} {$user->logisticsPartner->street_no}, {$user->logisticsPartner->barangay}, {$user->logisticsPartner->municipality}, {$user->logisticsPartner->province}"
                : null,
            default => null,
        };
    }
}
