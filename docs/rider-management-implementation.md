# Rider Management: implementation and migration review

Report date: 2026-11-09 (Asia/Manila). Scope: remaining rider enrollment, Main Hub review, Branch activation, capacity and controlled transfers in Laravel and Flutter.

**Status: the approved six-table migration is now applied to the LOCAL development database only. Automated lifecycle and regression verification passed on both restored MariaDB and in-memory SQLite, including concurrent capacity and transfer checks. Full device acceptance is still pending; see docs/rider-management-local-migration-2026-11-09.md.**

## Implemented workflow

Rider Registration -> Selected Logistics Company -> Preferred Branch and Barangays -> Main Hub Review -> Branch Pending Activation -> Branch Activation -> Pickup/Delivery Batches -> Main Hub Monitoring.

- Registration retains the existing home address, vehicle, OR/CR, ID/license and email verification requirements. Preferred work Branch and barangays are separate from home address.
- Provider and Branch choices are company-scoped; working barangays come from the Branch's existing operational service areas and the same PSGC directory used by the existing address selectors. Coverage must support both pickup and delivery.
- One Rider identity handles both task types. No additional pickup-rider/delivery-rider account system was created.
- Main Hub can mark under review, request specific replacement documents, approve with a confirmed primary Branch, or reject with a reason. Approval leaves the Rider pending and offline; it does not activate operations.
- A changed primary Branch requires the Rider's explicit acceptance and preferred coverage confirmation in mobile.
- Branch activates approved riders after confirming official barangays. It manages availability, work schedules, existing vehicle/batch configuration and daily assignment.
- Main Hub has the company directory, active riders grouped by Branch, filters, existing parcel outcomes, history, capacity decisions, compliance suspension/release and transfer decisions.
- Province Hubs and scanner staff cannot review or activate riders. Existing approved company owner access remains available; Main Hub center accounts/managers and Branch manager staff are handled by the shared authorization resolver.
- Existing approval/rejection emails are retained after commit. The new approval email explicitly says Branch activation is still required. Automated tests use fake/array mail only.

## Capacity and transfers

- Default soft limit: six unique active riders per barangay per logistics company, across every Branch. Pickup and delivery share the same slots. Offline/busy states do not create additional slots.
- Existing citywide permissions are included when counting active staffing. Duplicate pickup/delivery area links count as one person.
- Main Hub can configure a barangay limit, grant an individual Rider/location override, or revoke that override. A reason and authenticated actor are recorded.
- Activation/re-coverage, capacity decisions, status changes and transfers serialize on the provider and Rider. Current locking reads protect lifecycle state, outstanding custody/COD and work-window checks.
- A Branch requests a same-company transfer; Main Hub decides. Approval requires resolution of queued/active batches, outstanding delivery/pickup/return/transit custody and unremitted COD.
- Transfer clears the old official assignments, cancels old managed schedules and overrides, then leaves the new Branch assignment pending Rider acceptance and Branch activation.
- Main Hub can suspend operations immediately without deleting custody or cash records. Suspended collectors retain the narrow existing right to return their own previously collected COD; they cannot resume fulfillment.
- Official coverage reuses matching existing exact area rows, including city-name aliases. New narrow barangay rows are created only when needed instead of attaching a whole-city permission to a new applicant. NCR/Metro Manila labels match the existing parcel eligibility rules.
- Work windows use Philippine time in the web form and UTC storage. Configured shifts constrain the entire batch, including dispatch; an expired configured shift does not silently allow unscheduled work.

## Flutter and web integration

- Verified applicants receive an application-only session and can see review status, document requests, assigned Branch, confirmation choices, official coverage, schedules and history.
- Branch activation is followed by an operational token exchange. Application-only tokens remain blocked from task APIs even if the account was just activated. Remember me behavior is retained.
- Document replacements stay in private Rider-scoped storage. Application responses do not publish document paths.
- The application page includes the existing cash-return exception when the Rider has outstanding recorded COD.
- Mobile application state and web directory rows/Branch counts refresh every 15 seconds while visible. Background pages, edited web controls and busy mobile actions pause refresh. Connection failures retain the existing view; browser back-cache resumes polling.
- This is foreground polling, not WebSocket push.
- Web uses existing teal/Poppins layouts and components. Existing sidebar, navigation, header and working batch screens were not redesigned.
- Main company entry: existing Riders navigation, or /rider-management.
- Branch entry: the new content link on the existing Branch dashboard.
- Staff entry: /staff/rider-management; eligible manager logins land there.
- Existing company profile/service-area settings remain accessible from the company settings link, /riders?settings=1.

## Preserved existing behavior and data

The existing batch/manifest, Delivery, operational-area/pivot, QR/custody, proof, COD and parcel tracking structures remain authoritative. The changes do not introduce replacement order, parcel, transport route, COD or Rider identity tables.

Main Hub Transport Routes, Province Hub parcel operations, existing mobile batch screens and seeders were not rebuilt. Branch uses the existing Rider Batches workspace for new assignments after lifecycle deployment. Existing held parcels retain their original safe custody completion paths.

No backfill or automatic changes to existing Rider records are performed by the migration. Existing active accounts derive compatible lifecycle states until management metadata is needed. Legacy onboarding is preserved so existing active records are not forced through a new registration simply to reconfirm coverage. New registrations retain required documents.

No .env values or existing credentials were changed. No local/shared database reset, destructive seeder or database wipe was run.

## New migration: applied to local development after approval

File: database/migrations/2026_11_08_000003_create_rider_management_tables.php

Exactly six new tables:

| Table | Purpose |
| --- | --- |
| rider_lifecycle_profiles | Application, activation, preferences and confirmation metadata for existing Rider identities |
| rider_barangay_capacities | Provider/barangay staffing limit, default 6 |
| rider_capacity_overrides | Individually approved override and reason |
| rider_transfer_requests | Controlled Branch transfer and one-pending-request claim |
| rider_work_schedules | Branch work windows |
| rider_management_events | Company/Rider lifecycle audit trail |

Migration safety review:

- up() only creates these six tables. There are no existing-table/column changes, record updates/deletes, backfills or seed calls.
- SQL preview contains 6 CREATE TABLE statements and 24 constraint/index additions against those new tables only.
- Sixteen foreign keys use restricted deletion and reference existing providers, Riders and centers.
- Unique constraints cover one profile per Rider, one provider/location capacity, one Rider/location override and one pending transfer claim per Rider.
- Short explicit names are used for compound indexes. Largest generated identifier in the SQL preview: 54 characters.
- Application authorization and provider-scoped validation enforce tenant ownership; individual foreign keys do not replace these checks.
- down() drops only the six new tables in dependency order. It was not executed. It removes new management metadata and is not an undo operation for Rider/order actions performed after deployment.
- The existing four-table batch migration remains applied as batch 25. Before source application only the management migration was pending; it is now applied as batch 26, with no migrations left pending.
- Applied after explicit approval and final safety verification: six new empty tables and one migration ledger entry, batch 26. The source now has 76 tables; all 70 original schemas and all pre-existing records are unchanged except the intended ledger addition.
- No production/shared database migration is authorized.

Reviewed SQL: docs/rider-management-migration-preview.sql

Migration SHA256: 8a9bb1e9423ff1278fda7825377011241bf3b77587a5f78e17fa0ea5dbc140cb

## Backup and local database verification

Local source: 127.0.0.1 / shophop, MariaDB 10.4.32. Local environment and server hostname were checked.

Private backup directory:
C:/Users/HP 15s/.codex/backups/shophop-rider-management/20261108T145301Z

Backup: shophop-before-rider-management.sql
Bytes: 2,719,047
SHA256: 2058cff4abf67b4b5d56c45692c75418bb28df626b844f8d46fce308b11e6bcc

The directory is restricted to the current Windows user and SYSTEM. Credentials were passed privately to the matching MariaDB backup/import process; they were not written to the report or command line.

A new isolated restore database was created: shophop_management_restore_20261108_a4146fb7

All 70 original table schemas and all row fingerprints matched after restore. The source schemas, records and migration ledger remained unchanged during backup/preview/restore verification. At the backup-review stage the management migration was not yet applied. After explicit approval it was validated on this restore and then applied only to the local source. The original dump and review snapshot remain unchanged as pre-migration evidence.

Private evidence: review.json, artisan-migrate-pretend.txt and backend-final-tests.xml in the backup directory.

## Verification results

| Check | Result |
| --- | --- |
| Scoped Laravel lifecycle, batch, COD and private-proof suite | 51 passed, 555 assertions; 0 failures/errors |
| Full Flutter suite | 73 passed |
| Flutter analyzer: Rider/auth/session code and new test | No issues found |
| New and existing web live-refresh tests | 10 passed |
| Migration SQL preview | Passed, six new tables only |
| Fresh backup restore | Passed, all 70 original schemas/data match |
| Source management migration | Applied locally; 76 tables; original schemas/records unchanged |
| Restored MariaDB workflow/regression tests | 51 passed / 555 assertions |
| Concurrent MariaDB activation and duplicate transfer races | Both passed |
| Read-only existing Main Hub/Branch pages | 10 passed; source unchanged |
| Live Flutter services against isolated Laravel HTTP API | Passed, without API-response mocks |
| Device acceptance | Android build/install passed; live UI check blocked by repeated Android System UI hangs; no physical phone connected |

The Laravel run includes RiderManagementWorkflowTest, RiderBatchWorkflowTest, CodCollectionRemittanceJourneyTest and RiderDeliveryEvidenceJourneyTest. It uses SQLite :memory: with explicit process environment overrides and private/fake document storage.

The complete automated journey registers and verifies a new Rider, reviews and activates the application through web routes, runs multi-seller pickup and multi-buyer delivery batches for that Rider, verifies QR handoffs and physical Branch receipts, records private POD and COD, and verifies company batch monitoring.

Additional checks cover capacity across Branches/tasks, individual overrides and reasons, transfer approval/rejection and custody/COD blocking, staff/Main Hub/Province/scanner permissions, token rotation, legacy accounts/custody, area reuse, NCR matching, work windows, vehicle edit safety and private evidence authorization.

Earlier failures from obsolete manual-activation, web handoff and COD wording/redirect expectations were corrected in the affected regression fixtures. Their ownership, amount, immutable-proof and duplicate-submission checks remain in place. The final scoped suites have no failing tests.

This is not a claim that the entire Laravel repository suite was run: unrelated pre-existing test-discovery/view-contract issues noted in earlier audits remain outside this task.

## Changed files in this implementation phase

Existing dirty changes from earlier work were preserved. The lists below describe this Rider Management phase.

### New web files

- `database/migrations/2026_11_08_000003_create_rider_management_tables.php`
- `app/Models/Logistics/RiderLifecycleProfile.php`
- `app/Models/Logistics/RiderTransferRequest.php`
- `app/Services/RiderManagementAccess.php`
- `app/Services/RiderManagementService.php`
- `app/Services/RiderWorkAreaDirectory.php`
- `app/Http/Middleware/EnsureRiderOperational.php`
- `app/Http/Controllers/Logistics/RiderManagementController.php`
- `app/Http/Controllers/Api/MobileRiderManagementController.php`
- `resources/views/logistics/rider-management/index.blade.php`
- `resources/views/logistics/rider-management/rows.blade.php`
- `resources/views/logistics/rider-management/staff-layout.blade.php`
- `public/js/shophop-rider-management.js`
- `tests/Feature/RiderManagementWorkflowTest.php`
- `tests/js/rider-management-refresh.test.cjs`
- `docs/rider-management-migration-preview.sql`

- docs/rider-management-implementation.md (this report)

### Updated web files

- `app/Models/Logistics/Rider.php`
- `app/Services/RiderBatchService.php`
- `app/Services/RiderServiceAreaService.php`
- `app/Http/Controllers/Api/MobileAuthController.php`
- `app/Http/Controllers/Api/MobileRiderAuthController.php`
- `app/Http/Controllers/Logistics/RiderController.php`
- `app/Http/Controllers/Logistics/AuthController.php`
- `app/Http/Controllers/Logistics/DeliveryController.php`
- `app/Http/Controllers/Logistics/PickupCoverageController.php`
- `app/Http/Middleware/EnsureActiveRider.php`
- `app/Notifications/RiderApplicationDecisionNotification.php`
- `bootstrap/app.php`
- `routes/api.php`
- `routes/logistics.php`
- `resources/views/branch/dashboard/index.blade.php`
- `resources/views/logistics/staff/workspace.blade.php`
- `tests/Feature/RiderBatchWorkflowTest.php`
- `tests/Feature/RiderDeliveryEvidenceJourneyTest.php`
- `tests/Feature/CodCollectionRemittanceJourneyTest.php`

### New Flutter files

- `lib/features/rider/data/services/rider_management_service.dart`
- `lib/features/rider/registration/widgets/rider_work_preferences.dart`
- `lib/features/rider/presentation/screens/rider_application_status_screen.dart`
- `test/rider_management_test.dart`

### Updated Flutter files

- `lib/features/rider/registration/screens/rider_registration_screen.dart`
- `lib/features/rider/presentation/screens/rider_home_screen.dart`
- `lib/features/auth/data/services/auth_service.dart`
- `lib/features/auth/presentation/screens/login_screen.dart`
- `lib/core/session/auth_session_service.dart`

## Post-migration verification and remaining acceptance

The user approved applying this specific migration to the local development database only, subject to final safety checks. Those checks passed. The six-table migration was tested on the exact restored MariaDB copy before source application. Only its explicit path was run; no unrelated migrations, reset commands, seeders, existing-table changes or backfills ran.

Post-migration backend tests passed: 51 tests / 555 assertions on isolated SQLite. The same 51 tests / 555 assertions passed on restored MariaDB with transaction-only fixtures. The full Flutter suite passed (73 tests), the scoped analyzer reported no issues, and all 10 JavaScript refresh tests passed.

Concurrent MariaDB workers verified that only one applicant wins the sixth staffing slot and only one duplicate transfer request is persisted. Ten read-only source page checks covered existing Main Hub/company and Branch management/batch views and Main Hub Transport Routes. Source schemas and original records remained unchanged.

The Android debug app built and installed, but Android System UI and Pixel Launcher hangs prevented reliable live interaction, including after cold boot with lower memory/CPU settings. The test emulator was stopped without wiping its data. No physical phone is connected. A separate live HTTP integration test using the actual Flutter services and isolated Laravel API passed approval, activation, restricted-session exchange/revocation, existing batch access, identity and availability checks. Device UI acceptance and physical camera/QR/POD checks remain pending.

The new mobile enrollment flow and backend lifecycle changes should be deployed together in any future separately authorized release. Production was not modified; mobile uses its production API by default, so local verification requires an explicit API_BASE_URL build/run override.

Detailed results, private evidence references and remaining acceptance items: docs/rider-management-local-migration-2026-11-09.md.
