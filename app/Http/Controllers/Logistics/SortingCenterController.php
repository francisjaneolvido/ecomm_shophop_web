<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SortingCenterController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);
        $centers = $partner->sortingCenters()
            ->with(['coverageAreas', 'riders', 'user'])
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('logistics.sorting-centers.index', compact('partner', 'centers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $this->validatedCenter($request);
        $login = $this->validatedLogin($request);

        DB::transaction(function () use ($partner, $data, $login) {
            $center = $partner->sortingCenters()->create([
                ...$this->centerPayload($data),
                'code' => $this->nextCode($partner),
                'is_main' => false,
                'status' => 'active',
            ]);

            $this->syncCoverage($partner, $center, $data['coverage']);
            $this->provisionLogin($center, $login['login_email'], $login['login_password']);
        });

        return back()
            ->with('status', 'Sorting Center branch added.')
            ->with('new_credentials', [
                'email' => $login['login_email'],
                'password' => $login['login_password'],
            ]);
    }

    public function createLogin(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->findOrFail($center);
        $login = $this->validatedLogin($request);

        if ($ownedCenter->user_id) {
            return back()->withErrors(['sorting_center' => 'This branch already has a login.']);
        }

        DB::transaction(function () use ($ownedCenter, $login) {
            $this->provisionLogin($ownedCenter, $login['login_email'], $login['login_password']);
        });

        return back()
            ->with('status', 'Branch login created.')
            ->with('new_credentials', [
                'email' => $login['login_email'],
                'password' => $login['login_password'],
            ]);
    }

    public function resetPassword(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->with('user')->findOrFail($center);

        if (! $ownedCenter->user) {
            return back()->withErrors(['sorting_center' => 'This branch has no login yet.']);
        }

        $data = $request->validate([
            'login_password' => ['required', 'string', 'min:8', 'max:100'],
        ], [
            'login_password.required' => 'Maglagay ng bagong password para sa branch.',
            'login_password.min' => 'Ang password ay dapat hindi bababa sa 8 characters.',
        ]);

        $password = $data['login_password'];

        $ownedCenter->user->forceFill(['password' => Hash::make($password)])->save();

        return back()
            ->with('status', 'Branch password reset.')
            ->with('new_credentials', [
                'email' => $ownedCenter->user->email,
                'password' => $password,
            ]);
    }

    public function update(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->findOrFail($center);
        $data = $this->validatedCenter($request);

        DB::transaction(function () use ($partner, $ownedCenter, $data) {
            $ownedCenter->update($this->centerPayload($data));
            $this->syncCoverage($partner, $ownedCenter, $data['coverage']);
        });

        return back()->with('status', 'Sorting Center updated.');
    }

    public function toggle(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->findOrFail($center);

        if ($ownedCenter->is_main) {
            return back()->withErrors(['sorting_center' => 'The Main Sorting Center must remain active.']);
        }

        $next = $ownedCenter->status === 'active' ? 'inactive' : 'active';
        $ownedCenter->update(['status' => $next]);

        return back()->with('status', 'Sorting Center status updated.');
    }

    private function validatedCenter(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'contact_no' => ['nullable', 'string', 'max:30'],
            'region' => ['required', 'string', 'max:150'],
            'province' => ['required', 'string', 'max:150'],
            'municipality' => ['required', 'string', 'max:150'],
            'barangay' => ['required', 'string', 'max:150'],
            'street_no' => ['required', 'string', 'max:180'],
            'unit_no' => ['nullable', 'string', 'max:180'],
            'coverage' => ['required', 'array', 'min:1', 'max:20'],
            'coverage.*.area_name' => ['required', 'string', 'max:150'],
            'coverage.*.area_type' => ['required', 'in:province,region'],
            'coverage.*.cities' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validatedLogin(Request $request): array
    {
        $data = $request->validate([
            'login_email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'login_password' => ['required', 'string', 'min:8', 'max:100'],
        ]);

        $data['login_email'] = strtolower(trim($data['login_email']));

        return $data;
    }

    /**
     * Gumagawa ng user account para sa branch at ini-link sa sorting center.
     * ASSUMPTION: may sorting_centers.user_id column at ang users table ay may
     * account_type at status. I-check ang migration at EnsureApprovedRole.
     */
    private function provisionLogin(SortingCenter $center, string $email, string $password): void
    {
        $user = new User();
        $user->forceFill([
            'email' => $email,
            'password' => Hash::make($password),
            'account_type' => 'branch',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        $center->forceFill(['user_id' => $user->id])->save();
    }

    private function centerPayload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'contact_no' => filled($data['contact_no'] ?? null) ? trim($data['contact_no']) : null,
            'region' => trim($data['region']),
            'province' => trim($data['province']),
            'municipality' => trim($data['municipality']),
            'barangay' => trim($data['barangay']),
            'street_no' => trim($data['street_no']),
            'unit_no' => filled($data['unit_no'] ?? null) ? trim($data['unit_no']) : null,
        ];
    }

    private function syncCoverage(LogisticsPartner $partner, SortingCenter $center, array $coverage): void
    {
        $existingIds = $center->coverageAreas()->pluck('id');

        if ($existingIds->isNotEmpty()
            && DB::table('deliveries')->whereIn('destination_area_id', $existingIds)->exists()) {
            throw ValidationException::withMessages([
                'coverage' => 'Coverage used by an existing parcel cannot be replaced yet. Add the new branch/area first, then finish the active deliveries.',
            ]);
        }

        // Branch-owned coverage can be replaced when no persisted parcel depends on it.
        $center->coverageAreas()->delete();

        foreach ($coverage as $row) {
            $cities = trim((string) ($row['cities'] ?? ''));
            $cities = $cities === '' ? 'ALL' : collect(preg_split('/[|,]+/', $cities))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique(fn ($value) => strtolower($value))
                ->implode('|');

            $partner->coverageAreas()->create([
                'sorting_center_id' => $center->id,
                'area_name' => trim($row['area_name']),
                'area_type' => $row['area_type'],
                'cities' => $cities ?: 'ALL',
            ]);
        }
    }

    private function nextCode(LogisticsPartner $partner): string
    {
        $prefix = 'SC-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT).'-';
        $sequence = $partner->sortingCenters()->count() + 1;

        do {
            $code = $prefix.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($partner->sortingCenters()->where('code', $code)->exists());

        return $code;
    }

    private function partner(Request $request): LogisticsPartner
    {
        return LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }
}