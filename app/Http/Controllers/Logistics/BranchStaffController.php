<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\SortingCenter;
use App\Models\Logistics\Rider;
use App\Models\User;
use App\Models\LogisticsPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class BranchStaffController extends Controller
{
    public function store(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $branch = $partner->sortingCenters()->whereNull('archived_at')->findOrFail($center);
        $data = $request->validate([
            'name' => ['required','string','min:2','max:150'],
            'email' => ['required','email','max:190'],
            'role' => ['required',Rule::in(['manager','scanner'])],
        ]);
        $email = strtolower(trim($data['email']));
        $this->assertNewEmail($email);
        $password = Str::password(18);
        $branch->staff()->create([
            'logistics_partner_id' => $partner->id,
            'name' => trim($data['name']),
            'email' => $email,
            'password' => $password,
            'role' => $data['role'],
            'status' => 'active',
        ]);
        return back()->with('status', 'Branch staff created. Save the temporary password securely; it appears only once.')
            ->with('new_staff_credentials', ['email' => $email, 'password' => $password]);
    }

    public function toggle(Request $request, int $staff): RedirectResponse
    {
        $partner = $this->partner($request);
        $operator = $partner->branchStaff()->findOrFail($staff);
        if ($operator->sortingCenter?->archived_at) {
            return back()->withErrors(['staff' => 'Restore this branch before changing staff accounts.']);
        }
        $target = $operator->status === 'active' ? 'inactive' : 'active';
        if ($target === 'active' && $operator->sortingCenter?->status !== 'active') {
            return back()->withErrors(['staff' => 'Activate the assigned branch first.']);
        }
        if ($target === 'inactive' && $operator->role === 'manager'
            && $operator->sortingCenter?->status === 'active'
            && ! $operator->sortingCenter->staff()->where('role', 'manager')->where('status', 'active')
                ->where('id', '!=', $operator->id)->exists()) {
            return back()->withErrors(['staff' => 'Assign another active manager before disabling the last branch manager.']);
        }
        DB::transaction(function () use ($operator, $target) {
            $operator->update(['status' => $target]);
            if ($target === 'inactive') $operator->tokens()->delete();
        });
        return back()->with('status', 'Branch staff status updated.');
    }

    public function resetPassword(Request $request, int $staff): RedirectResponse
    {
        $partner = $this->partner($request);
        $operator = $partner->branchStaff()->findOrFail($staff);
        if ($operator->sortingCenter?->archived_at) {
            return back()->withErrors(['staff' => 'Restore this branch before resetting staff credentials.']);
        }
        $password = Str::password(18);
        DB::transaction(function () use ($operator, $password) {
            $operator->update(['password' => $password]);
            $operator->tokens()->delete();
        });
        return back()->with('status', 'Temporary password regenerated; existing mobile sessions revoked.')
            ->with('new_staff_credentials', ['email' => $operator->email, 'password' => $password]);
    }

    /**
     * Main hub sets a specific password for a province hub manager / branch staff.
     * Same ownership + archive checks as resetPassword(); password is hashed by the model cast.
     */
    public function setPassword(Request $request, int $staff): RedirectResponse
    {
        $partner = $this->partner($request);
        $operator = $partner->branchStaff()->findOrFail($staff);
        if ($operator->sortingCenter?->archived_at) {
            return back()->withErrors(['staff' => 'Restore this branch before changing staff credentials.']);
        }

        $data = $request->validateWithBag('setPassword', [
            'password' => ['required', 'string', 'max:100', Password::min(8)->letters()->numbers()],
        ]);

        DB::transaction(function () use ($operator, $data) {
            $operator->update(['password' => $data['password']]);
            $operator->tokens()->delete();
        });

        return back()->with('status', "Password updated for {$operator->name}; existing mobile sessions revoked.")
            ->with('new_staff_credentials', ['email' => $operator->email, 'password' => $data['password']]);
    }

    public static function assertNewEmail(string $email): void
    {
        if (User::where('email', $email)->exists()
            || Rider::where('email', $email)->exists()
            || BranchStaff::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Email is already registered to a ShopHop account.']);
        }
    }

    private function partner(Request $request): LogisticsPartner
    {
        return LogisticsPartner::where('user_id', $request->user()->id)->firstOrFail();
    }
}