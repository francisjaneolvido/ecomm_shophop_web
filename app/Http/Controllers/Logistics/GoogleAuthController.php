<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Google sign-in ONLY for already registered and admin-approved Logistics partners.
 * First-time linking requires a normal password sign-in to that existing account.
 * No account creation, no modification to Logistics applications or mobile APIs.
 */
class GoogleAuthController extends Controller
{
    private function callbackUrl(): string
    {
        return config('logistics_google.redirect_uri') ?: route('logistics.google.callback');
    }

    private function isConfigured(): bool
    {
        return filled(config('logistics_google.client_id'))
            && filled(config('logistics_google.client_secret'));
    }

    private function notice(string $message): RedirectResponse
    {
        return redirect()->route('logistics.login')->with('login_notice', [
            'type' => 'error', 'title' => 'Google Sign-In', 'message' => $message,
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->notice('Google sign-in is not configured yet. Please use email and password.');
        }

        $state = Str::random(48);
        $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $request->session()->put('logistics_google_oauth', [
            'state' => $state,
            'verifier' => $verifier,
            'created_at' => time(),
        ]);

        $parameters = http_build_query([
            'client_id' => config('logistics_google.client_id'),
            'redirect_uri' => $this->callbackUrl(),
            'response_type' => 'code',
            'scope' => 'openid email',
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$parameters);
    }

    public function callback(Request $request): RedirectResponse
    {
        $flow = $request->session()->pull('logistics_google_oauth');
        if (! is_array($flow)
            || ! is_string($request->query('state'))
            || ! hash_equals((string) ($flow['state'] ?? ''), $request->query('state'))
            || time() - (int) ($flow['created_at'] ?? 0) > 600
            || time() < (int) ($flow['created_at'] ?? 0)
        ) {
            return $this->notice('Google session expired or invalid. Please try again.');
        }

        if ($request->filled('error')) {
            return $this->notice('Google sign-in was cancelled or declined.');
        }
        $code = $request->query('code');
        if (! is_string($code) || $code === '' || ! $this->isConfigured()) {
            return $this->notice('Google did not return a valid sign-in code.');
        }

        try {
            // Authorization-code exchange is server-to-server; credentials are never sent to the browser.
            $token = Http::asForm()->timeout(12)->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('logistics_google.client_id'),
                'client_secret' => config('logistics_google.client_secret'),
                'redirect_uri' => $this->callbackUrl(),
                'grant_type' => 'authorization_code',
                'code_verifier' => (string) ($flow['verifier'] ?? ''),
            ]);
            if (! $token->successful() || ! is_string($token->json('access_token'))) {
                return $this->notice('Google could not complete sign-in. Please try again.');
            }
            // Google authenticates the OAuth access token at its HTTPS UserInfo endpoint.
            $profile = Http::withToken($token->json('access_token'))
                ->timeout(12)->get('https://openidconnect.googleapis.com/v1/userinfo');
            if (! $profile->successful()) {
                return $this->notice('Could not verify the Google account. Please try again.');
            }
        } catch (Throwable $exception) {
            report($exception);
            return $this->notice('Google is temporarily unavailable. Please use email and password.');
        }

        $subject = $profile->json('sub');
        $email = $profile->json('email');
        $verified = $profile->json('email_verified');
        if (! is_string($subject) || $subject === '' || strlen($subject) > 255
            || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || $verified !== true) {
            return $this->notice('Google did not provide a verified email address.');
        }

        try {
            $linked = DB::table('logistics_google_accounts')->where('google_sub', $subject)->first();
            if ($linked) {
                $user = User::whereKey($linked->user_id)
                    ->where('account_type', 'logistics')->first();
                if (! $user || strcasecmp($user->email, $email) !== 0
                    || $user->status !== 'approved') {
                    return $this->notice('This logistics account is not approved, or its email has changed. Contact support.');
                }
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('logistics.dashboard');
            }

            $user = User::where('email', $email)->where('account_type', 'logistics')->first();
            if (! $user) {
                return $this->notice('No logistics partner account matches this Google email. Apply first or use your registered email.');
            }
            if ($user->status !== 'approved') {
                return $this->notice('Your logistics application must be approved before you can sign in.');
            }
            // Linking by email alone is unsafe. Require existing password login once.
            $request->session()->put('logistics_google_link_pending', [
                'user_id' => $user->id,
                'email' => $email,
                'google_sub' => $subject,
                'created_at' => time(),
            ]);
            return redirect()->route('logistics.login')->with('login_notice', [
                'type' => 'warning',
                'title' => 'One-Time Account Linking',
                'message' => 'Sign in using your existing ShopHop Logistics email and password once to securely link Google. Next time, Continue with Google will sign you in.',
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return $this->notice('Google linking is not available yet. Ask the administrator to run the database migration.');
        }
    }
}
