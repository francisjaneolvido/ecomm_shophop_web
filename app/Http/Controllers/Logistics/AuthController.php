<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('logistics_staff')->check()) {
            return redirect()->route($this->staffHomeRoute(Auth::guard('logistics_staff')->user()));
        }
        if (Auth::check() && Auth::user()->account_type === 'logistics') {
            return redirect()->route('logistics.dashboard');
        }

        return view('logistics.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only logistics partners and branch accounts may sign in here.
        // Buyers/sellers/admins cannot.
        $attempt = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'account_type' => ['logistics', 'branch'],
        ], $request->boolean('remember'));

        if (! $attempt) {
            // Branch operators are explicitly provisioned by an approved Logistics company.
            $staffAttempt = Auth::guard('logistics_staff')->attempt([
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'status' => 'active',
            ], $request->boolean('remember'));

            if ($staffAttempt) {
                $staff = Auth::guard('logistics_staff')->user();
                if (! $staff->activeForBranch((int) $staff->logistics_partner_id, (int) $staff->sorting_center_id)) {
                    Auth::guard('logistics_staff')->logout();
                    return back()->withErrors(['email' => 'Branch, staff account or company is inactive.']);
                }
                $request->session()->regenerate();

                // Main hub manager -> main console; province hub manager -> /hub;
                // scanners -> assigned branch workspace.
                return redirect()->route($this->staffHomeRoute($staff));
            }

            return back()
                ->withErrors(['email' => 'Incorrect email or password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status !== 'approved') {
            $status = $user->status;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $notice = match ($status) {
                'pending' => [
                    'type' => 'warning',
                    'title' => 'Approval Pending',
                    'message' => 'Your application is still waiting for administrator approval.',
                ],
                'rejected' => [
                    'type' => 'error',
                    'title' => 'Application Rejected',
                    'message' => 'Your application was not approved. Please contact ShopHop support.',
                ],
                'suspended' => [
                    'type' => 'error',
                    'title' => 'Account Suspended',
                    'message' => 'Your account is suspended. Please contact ShopHop support.',
                ],
                default => [
                    'type' => 'warning',
                    'title' => 'Account Unavailable',
                    'message' => 'Your account is currently unavailable for sign in.',
                ],
            };

            return redirect()->route('logistics.login')->with('login_notice', $notice);
        }

        // Branch account: straight to the branch console, no Google linking.
        if ($user->account_type === 'branch') {
            return redirect()->route('branch.dashboard');
        }

        // An OAuth callback may request one-time Google linking. Only the password-authenticated,
        // admin-approved owner of this exact logistics account can complete it.
        $pending = $request->session()->pull('logistics_google_link_pending');
        if (is_array($pending) && (int) ($pending['user_id'] ?? 0) === (int) $user->id
            && strcasecmp((string) ($pending['email'] ?? ''), $user->email) === 0
            && time() >= (int) ($pending['created_at'] ?? 0)
            && time() - (int) ($pending['created_at'] ?? 0) <= 600
            && is_string($pending['google_sub'] ?? null)
            && $pending['google_sub'] !== ''
        ) {
            try {
                DB::transaction(function () use ($user, $pending) {
                    $existingUserLink = DB::table('logistics_google_accounts')
                        ->where('user_id', $user->id)->lockForUpdate()->first();
                    $existingSubjectLink = DB::table('logistics_google_accounts')
                        ->where('google_sub', $pending['google_sub'])->lockForUpdate()->first();
                    if (! $existingUserLink && ! $existingSubjectLink) {
                        DB::table('logistics_google_accounts')->insert([
                            'user_id' => $user->id,
                            'google_sub' => $pending['google_sub'],
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                });
            } catch (Throwable $exception) {
                report($exception); // Standard password login still works, even if Google linking fails.
            }
        }

        return redirect()->route('logistics.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('logistics_staff')->logout();
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logistics.login');
    }

    /** Where a signed-in branch-staff account lands after login. */
    private function staffHomeRoute($staff): string
    {
        if ($staff->role !== 'manager') {
            return 'logistics.staff.workspace';
        }

        $center = $staff->sortingCenter;

        // Manager of the MAIN hub -> main logistics console.
        if ($center && $center->is_main) {
            return 'logistics.dashboard';
        }

        // Manager of a province hub -> province hub portal.
        return 'logistics.hub.dashboard';
    }
}