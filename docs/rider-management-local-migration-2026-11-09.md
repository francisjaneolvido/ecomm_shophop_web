# Rider Management local migration and post-migration verification

Date: 2026-11-09 (Asia/Manila).

## Result and authorized scope

The approved migration `2026_11_08_000003_create_rider_management_tables.php` was applied successfully to LOCAL development `127.0.0.1 / shophop`, MariaDB 10.4.32. This is not a production or shared deployment and is not a claim of full physical-device acceptance.

- Migration exit code: 0. Ledger batch: 26. Only the intended migration path ran.
- Source tables: 70 -> 76. Existing batch tables remain unchanged.
- All 69 original non-ledger table schemas and record hashes match the verified pre-migration backup. The migrations table has exactly one intended new ledger entry; its old rows are unchanged. Its normal auto-increment metadata advances with this insert.
- Six new tables are empty on the source. No backfill, account changes, credentials/.env changes, existing-record edits/deletes, destructive commands or seeders ran.
- Final migration status: no pending migrations.

## Created tables

| Table | Function |
| --- | --- |
| rider_lifecycle_profiles | Enrollment, Main Hub approval, Branch activation and work preferences |
| rider_barangay_capacities | Provider/barangay soft limit, default six unique active riders |
| rider_capacity_overrides | Individual authorized override with recorded reason |
| rider_transfer_requests | Controlled same-company Branch transfers and unique pending claim |
| rider_work_schedules | Branch-owned Rider work windows |
| rider_management_events | Lifecycle audit history |

These extend existing riders, providers and sorting centers. They do not replace or duplicate `rider_batch_vehicle_profiles`, `rider_batches`, `rider_batch_items`, `rider_batch_events`, orders, deliveries, parcel tracking, QR/custody, Transport Routes or COD.

Sixteen foreign keys use RESTRICT deletion. Four unique constraints cover one lifecycle profile per Rider, one company/location limit, one Rider/location override, and one pending transfer claim per Rider. Four explicit compound indexes support lifecycle, company transfer, schedule and event queries. Foreign keys validate references; application authorization, company-scoped queries and locking transactions validate that Rider and Branch belong to the same provider.

The migration preview contained exactly six CREATE TABLE and 24 ALTER statements, all against the six new tables. Actual writes were those 30 DDL statements plus one migration ledger insert. A write guard rejected unrelated statements.

Rollback code was inspected, not executed: it drops only the six new tables in dependency order. It removes management metadata and is not a safe substitute for undoing operational actions after deployment. No automatic rollback or destructive recovery was performed.

## Backup and safety evidence

Private directory (restricted to the current Windows user and SYSTEM):
`C:/Users/HP 15s/.codex/backups/shophop-rider-management/20261108T145301Z`

Backup: `shophop-before-rider-management.sql`, 2,719,047 bytes.
SHA256: `2058cff4abf67b4b5d56c45692c75418bb28df626b844f8d46fce308b11e6bcc`.
Migration SHA256: `8a9bb1e9423ff1278fda7825377011241bf3b77587a5f78e17fa0ea5dbc140cb`.

The backup was imported into a new isolated database, `shophop_management_restore_20261108_a4146fb7`. All 70 restored schemas and record hashes matched exactly. Before source migration the backup checksum, migration checksum and all source/restore fingerprints were verified again. The server hostname, loopback address, application local environment, connection alias and physical SELECT DATABASE() target were checked.

Only the intended migration was pending. Both `migrate:status` and `migrate --pretend --path=database/migrations/2026_11_08_000003_create_rider_management_tables.php` were captured. The migration then ran on the restore first; all original data and schemas stayed intact. Source writes were prohibited throughout restore tests. Source application was allowed only after the MariaDB workflow suite passed.

Private evidence files include:
- `final-preflight.json`, `final-migrate-status.txt`, `final-migrate-pretend.txt`.
- `clone-management-migration.json`, `clone-runtime-results.json`, `mariadb-runtime-tests.xml`.
- `local-management-migration.json`, `local-post-migration-fingerprint.json`, `final-local-verification.json`.
- `postmigration-backend-tests.xml`, `postmigration-flutter-tests.txt`.
- `mariadb-concurrency-results.json`, `postmigration-http-results.json`, `flutter-live-api-results.json`.

The original pre-migration `review.json` remains a historical snapshot; post-migration state is in the newer evidence files. Private scripts guard source writes and physical database targets. Credentials are not stored in this report.

## Tests and working workflows

| Verification | Result |
| --- | --- |
| Restored MariaDB: lifecycle, batches, COD and Rider evidence suites | 51 tests / 555 assertions passed |
| Post-migration SQLite in-memory backend rerun | 51 tests / 555 assertions passed |
| Post-migration full Flutter suite | 73 passed |
| Additional live Flutter-service/Laravel HTTP integration | 1 passed; actual HTTP responses, no API-response mocks |
| Flutter Rider/auth/session analyzer | No issues found |
| Post-migration JavaScript management/batch refresh suites | 10 passed |
| Source read-only Main Hub/company and Branch pages | 10 HTTP 200 checks; no source mutations |
| Two concurrent activations competing for sixth slot | One accepted, one capacity rejection; count remained six |
| Two concurrent transfer requests for the same Rider | One accepted, one rejection; one pending transfer persisted |
| Android debug build and installation | Passed |
| Live emulator/physical-device acceptance | Blocked by repeated Android System UI/Launcher hangs, including after cold boot; no physical phone connected |

The automated end-to-end journey exercises actual registration uploads and verification endpoints, company-scoped preferred Branch/barangay selection, Main Hub approval without activation, Branch Pending Activation, Branch activation, mobile application state, multi-seller pickup and multi-buyer delivery batches, QR custody handoffs/Branch receipts, independent parcel outcomes, private POD, COD collection and Main Hub monitoring.

Additional checks cover cross-Branch company-wide staffing counts, shared pickup/delivery slots, overrides/reasons, multi-barangay coverage, schedule/availability controls, suspended Rider cash return, controlled transfer approval/rejection, held custody/COD blocking, application-only token restrictions and rotation, document resubmission/privacy, Main Hub/Branch/staff/Province/scanner permissions, cross-company isolation and legacy accounts.

The source page checks cover existing company Rider Management, Rider Batch monitoring and Transport Routes plus Branch Rider Management and Batch planning, across two companies and two Branches. Actual mutating workflow fixtures ran only on isolated databases. MariaDB suite fixtures were rolled back; separately labeled concurrent/device fixtures remain only in the restore.

The first restored-database test run had one fixture failure: it selected the first existing Buyer/Seller instead of the test order's owners. Only the private restore fixture was corrected to use the order's owners. The final rerun passed all 51 tests; no application code change was needed.

The full Laravel repository suite was not run. Earlier documented unrelated test-discovery/view-contract issues remain outside this scope. The tested scoped suites have no final failures.

## Device verification and remaining acceptance

No physical Android phone was connected. The existing `Medium_Phone_API_36.1` emulator was started, and the actual Flutter app built/installed with an explicit local API override. A private loopback API on port 8011 targets the isolated restore, blocks source writes, uses array mail/cache and deterministic PSGC responses, and keeps test documents in private storage.

The initial private API used the older XAMPP PHP binary and returned HTTP 500 due to Composer's PHP requirement. It was restarted using the same PHP executable as the passing backend suite; the API responded successfully. This was a verification setup issue; no project config or credentials were changed.

Android Pixel Launcher/System UI repeatedly showed ANR warnings, preventing reliable login/status interaction. A cold boot with lower memory/CPU settings briefly rendered the app, but System UI hung again. The test emulator was stopped without wiping its data. Neither live device UI approval/activation synchronization nor physical camera QR/POD behavior is marked passed. Evidence includes emulator screenshots and emulator-login-window.xml with the Android System UI ANR dialog.

A separate private Flutter test then exercised the actual AuthService, RiderManagementService and RiderBatchService against the live isolated HTTP API. It passed company-scoped options, application-only login, pending review, Main Hub approval without activation, Branch activation visible through subsequent actual HTTP reads, continued rejection of restricted-token operations, operational token exchange, old-token revocation, existing batch API access, identity, availability round trip and logout. No API-response mocks were used. This verifies the Flutter service/backend integration and complements the 73 automated tests, while remaining distinct from live device UI/camera acceptance.

The PSGC directory and email delivery were faked in isolated lifecycle/device tests to avoid external dependencies and outbound messages. Live external directory availability and actual email receipt were not verified in this phase. Foreground synchronization uses the existing 15-second polling, not WebSocket push.

Before full device sign-off, verify a stable emulator or connected phone can log in as an applicant, see a real Main Hub decision and Branch activation through polling, exchange its restricted session, open pickup/delivery batches, and complete camera/QR/POD flows. Keep those acceptance fixtures isolated or explicitly labeled in local development.

For the real local source backend on port 8000, use `flutter run -d <android-device-id> --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/mobile` on an emulator. A USB phone can use adb reverse plus a localhost override. Do not distribute the isolated port-8011 verification build as a release. Mobile's production default API was not changed.

## Files changed during this post-approval phase

Only documentation was edited in the project during this phase:
- `docs/rider-management-implementation.md` (updated applied/tested status).
- `docs/rider-management-local-migration-2026-11-09.md` (this report).

Private safety scripts, test copies and evidence were created under the backup directory. The previously implemented feature files and full changed-file inventory are listed in `docs/rider-management-implementation.md`. Existing unrelated work, working navigation/batch screens, .env, credentials, production and shared databases were preserved.

The temporary verification API and test emulator were stopped after testing. The restored databases, original backup, private fixtures and test evidence were retained for review. No verification server was left pointing at a shared or production database.
