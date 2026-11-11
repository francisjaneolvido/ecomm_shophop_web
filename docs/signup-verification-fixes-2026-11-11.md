# ShopHop signup at account verification fixes ? 11 Nobyembre 2026

Na-implement ang responsive signup at verification/resend fixes sa Laravel project. Hindi binago ang `.env`, SMTP credentials, schema, o existing accounts. May unrelated work nang nasa working tree; hindi iyon ni-revert.

## Mga root cause

- **Email:** `MAIL_MAILER=log` ang local configuration. Bago ang fix, nire-render at isinusulat lang ng Laravel ang email sa local server logs, kasama ang code; walang inbox delivery. Dahil walang exception ang log transport, maling ?sent? success ang ipinapakita. Synchronous ang Buyer/Seller/Logistics notifications; hindi missing queue worker ang sanhi.
- **Resend:** fixed na 60-second timer ang Buyer/Seller page sa bawat reload, kahit iba na ang actual server cooldown. Wala ring request loading/network feedback. Ini-overwrite ang active code bago mag-send, kaya kapag pumalya ang mail, napapalitan pa rin ang code at nagsisimula ang cooldown. Sa Logistics, ang resend result ay nasa previous, hidden email step, at kulang ang protection laban sa repeated clicks.
- **Layout:** may sobrang top spacing at floating controls na maaaring mag-overlap; kailangan ng viewport-aware dialog limits at grid minimum-width constraints. Nakumpirma sa Chrome ang horizontal overflow ng anim na Logistics OTP inputs sa 320px; inayos ang flex sizing ng inputs.

## Mga binago

- Preserved ang ShopHop branding, colors, typography, fields at multi-step registration. Dynamic viewport height, natural vertical scrolling, mobile control clearance at minimum-width constraints ang inilapat sa shared modals. Walang pinilit na maliit na form fields.
- Shared Buyer/Seller code service: cryptographically secure six-digit code, hashed storage, five-minute expiry, five incorrect attempts, row locks at transactional verification. Successful resend replaces the previous code and resets attempts. Failed send preserves the previous code and its expiry/cooldown.
- Server-backed 60-second resend cooldown, five send attempts per account per ten minutes, existing IP resend limits, at 20 verification requests per minute. JSON responses include cooldown information; frontend also honors `Retry-After`.
- Resend sends the CSRF-protected form to its existing endpoint, shows loading/success/error state, rejects duplicate clicks, and handles network errors and expired sessions. Hindi kailangang gumawa ng panibagong account pagkatapos ng failed initial email.
- Logistics session is validated for resend; state is replaced only after mail transport accepts the notification. Both standalone form and modal show feedback in the OTP step.
- Verification dispatch rejects local-only `log` mail and non-test `array` mail, including silent log fallbacks. Generic delivery errors are shown; diagnostic warnings contain the exception class only. Codes and provider exception details are not sent to the browser.
- Verified email still requires administrator approval; existing Buyer/Seller login and Rider registration rules are preserved.

## Changed files

Backend:

- `app/Services/AccountVerificationCodes.php` (new)
- `app/Services/VerificationMail.php` (new)
- `app/Http/Controllers/Auth/BuyerRegistrationController.php`
- `app/Http/Controllers/Auth/SellerRegistrationController.php`
- `app/Http/Controllers/Auth/BuyerEmailVerificationController.php`
- `app/Http/Controllers/Auth/SellerEmailVerificationController.php`
- `app/Http/Controllers/Logistics/RegistrationController.php`
- `routes/web.php` (Buyer verification throttle only; existing unrelated edits preserved)
- `routes/seller.php`

Views:

- `resources/views/auth/modals/shared/registration-side-panel.blade.php`
- `resources/views/auth/modals/shared/resend-script.blade.php` (new)
- `resources/views/auth/modals/buyer-registration/modal.blade.php`
- `resources/views/auth/modals/verify-email.blade.php`
- `resources/views/auth/modals/seller-verify-email.blade.php`
- `resources/views/auth/modals/logistics-registration/modal.blade.php`
- `resources/views/auth/modals/logistics-registration/scripts.blade.php`
- `resources/views/logistics/auth/register.blade.php`

Tests/evidence:

- `tests/Feature/SignupVerificationFlowTest.php` (new)
- `docs/signup-verification-browser-results-2026-11-11.json` (new)
- This report.

## Validation

- **27 tests, 222 assertions passed:** `SignupVerificationFlowTest`, `AccountRegistrationBlackboxTest`, and `MobileSellerRegistrationTest`. In-memory SQLite and fake/in-memory mail only. Includes code generation, notification rendering through the array transport, correct/incorrect/expired/exhausted codes, old code invalidation, cooldown, account rate limits, session/role validation, delivery failure rollback, pending account creation, no duplicates, login/approval rules, and retry after initial mail failure.
- **Rider regression passed:** `RiderManagementWorkflowTest::test_real_registration_verification_approval_activation_and_mobile_sync_flow` (42 assertions).
- **Browser:** headless Chrome checked all 4 Buyer, 5 Seller and 7 Logistics steps at 320?568, 390?844, 768?1024, 1366?768 and 1440?900. All fields/buttons were reachable without horizontal clipping. Buyer/Seller network, expired-session and cooldown feedback passed; Buyer loading/duplicate-click behavior passed. No uncaught JavaScript exceptions. Synthetic rendered pages and intercepted fetch were used, not live backend submissions. See browser JSON for results.
- `npm run build` passed. PHP GD was enabled only for the test process with `-d extension=gd`; no machine PHP configuration was changed.
- Existing Logistics presentation test has an unrelated wording mismatch: it expects ?Apply as Logistics Partner?, while the unchanged landing page uses ?Apply as Partner?. That landing page/test was not edited.

## Manual mail configuration still required

Inbox receipt cannot work while the configured mailer is `log`. An authorized operator must select a delivery-capable mailer and configure the intended provider using its approved settings. Keep credentials private. No provider was replaced and no credentials were changed by this fix.

After the approved configuration change, clear stale Laravel configuration (`php artisan config:clear`, then rebuild config cache if used by the deployment), and perform one controlled registration/resend using a mailbox you own. Confirm the message in the intended inbox or test mail catcher, then verify that code. Provider acceptance alone does not prove inbox delivery; check provider delivery events and spam filtering if needed. No real SMTP/inbox delivery was tested in this session.

Existing pending Buyer/Seller accounts can sign in to recover their verification session, then resend after mail is configured. No migrations or data cleanup are required.
