# Rider batches: local migration result - 2026-11-08

**Completed sa local development database lang.** Walang production o shared deployment. User approval: local-only, may final safety review at recoverable backup.

## Target at result

- Environment: local; loopback host 127.0.0.1; database shophop; server sa parehong Windows computer.
- Database server: 10.4.32-MariaDB; Laravel mysql driver. InnoDB ang lahat ng original tables.
- Migration: 2026_11_08_000002_create_rider_batches; recorded as migration batch 25.
- Applied at: 2026-11-08T12:33:33+00:00 (UTC).
- Table count: 66 original -> 70 total. Walang unrelated pending migration na na-execute.
- Hash-verified na unchanged ang data at schema ng lahat ng 65 original business tables.
- Preserved ang lahat ng original migration-ledger rows; isang expected migration entry lang ang nadagdag.
- Riders: 2,427 unchanged. Sorting centers: 1,831 unchanged.

## Apat na intended tables

| Table | Records pagkatapos ng deployment | Purpose |
| --- | --- | --- |
| rider_batch_vehicle_profiles | 0 | Configurable vehicle/route capacity; existing Rider reference |
| rider_batches | 0 | Pickup/Delivery route schedule, state, company and Branch |
| rider_batch_items | 0 | Membership ng existing Delivery; stops, parcel measurements and outcomes |
| rider_batch_events | 0 | Batch transition/parcel-outcome audit |

Walang bagong rider/account/order/parcel system. Existing Delivery, PickupRequest, Order, company at Branch relationships pa rin ang ginagamit. Walang automatic enrollment, backfill, assignment o pagbabago sa existing operational records.

## Backup at recovery evidence

- Private backup directory: C:\Users\HP 15s\.codex\backups\shophop-rider-batches\20261108T121726Z
- SQL file: shophop-before-rider-batches.sql
- Size: 2,711,977 bytes.
- SHA-256: a7edc19d8270415573012677feddc9c850a610097efa1f5d895c465e92570791
- Actual restore verified sa hiwalay na local database: shophop_batch_verify_20261108_225a56fe.
- Lahat ng 66 restored original tables ay nagtugma ang row counts, complete data hashes at schema hashes bago ang clone migration.
- Retained ang verification database para sa audit; hindi ito nakalagay sa application .env/default connection.
- Nasa private backup directory ang pre/post table fingerprints, SQL write log, schema checks, clone JUnit report at local page/API checks.
- Hindi binago ang .env, passwords o credentials. Backup credentials were passed privately to child database clients, never printed or placed in command-line password arguments.

## Safety review

- Reviewed ang implementation doc, actual migration up/down, php artisan migrate:status at path-scoped SQL preview.
- Tanging intended four-table schema at migration ledger ang pinayagan ng execution write guard.
- Actual MariaDB/source checks: 11 foreign keys; referenced id columns are unsigned bigint; unique vehicle profile per rider, batch code, nullable active-rider claim, nullable reserved-parcel claim, at batch + Delivery pair.
- Compound indexes support rider/schedule/status at provider/Branch/status queries.
- Provider/Branch isolation is validated server-side; hindi frontend filtering lang.
- Reviewed ang down(): child-first removal ng four new tables only. Hindi nag-execute ng rollback/drop/reset sa development database.
- Test fixtures for MariaDB were confined to the restored verification DB and rolled back per test. Walang fixture rows na iniwan sa development database.

## Error encountered at forward recovery

1. Nahuli ng MariaDB ang generated index name na lampas sa 64-character limit. Pinalitan ito ng rb_company_branch_status_idx.
2. Sa unang verification attempt, nadala ng copied connection config ang original name=mysql. Dahil dito, dalawang intended NEW EMPTY tables ang nalikha sa local shophop bago huminto ang DDL, sa halip na sa verification DB. Na-report ito sa user agad pagkatapos makumpirma.
3. Kinumpirma sa complete hashes na walang nabago sa 66 original tables o records. Walang table drop/reset o data restoration na ginawa.
4. Na-correct ang verification connection name, may explicit SELECT DATABASE() target checks, at blocked lahat ng source writes habang clone validation. Child PHPUnit process also receives explicit isolated DB settings and asserts the physical target before fixture creation.
5. Na-review ang revised migration: fresh installs still create four tables; the known empty two-table partial state can finish its missing indexes and create the remaining two tables. Unexpected/populated partial states are rejected.
6. RiderBatchService now requires all four tables bago i-enable ang batch module.
7. Fresh migration and operational tests passed on the restored MariaDB DB. Saka kinumpleto ang local deployment forward-only, at muling na-verify ang original hashes.

Resolved ang migration error; walang pending migration failure sa final state.

## Verification pagkatapos ng migration

| Check | Result |
| --- | --- |
| Restored MariaDB integration suite | 16 passed, 207 assertions; transactions rolled back |
| Focused SQLite/workspace/scanner/Logistics Management regression suite | 22 passed, 278 assertions |
| Web batch polling JavaScript | 5 passed |
| Corrected PHP migration/service syntax | Passed |
| All original business records and schema | Unchanged hashes for all 65 tables |
| Migration ledger | Old rows unchanged; one intended new entry |
| Main Hub Transport Routes | HTTP 200 for two existing companies; original route records unchanged |
| Main Hub batch monitoring | HTTP 200 for two existing companies |
| Existing Branch planner | HTTP 200 for two existing Branch accounts |
| Existing Rider batch API | enabled=true for two existing riders; no automatic batches |

MariaDB tests cover Branch-owned pickup/delivery planning, multiple parcels/sellers/buyers, separate same-day routes, one active batch, duplicate parcel prevention, schedule/capacity checks, company/Branch/rider isolation, Main Hub read-only batch monitoring, manager/scanner permissions, QR custody, independent failures, physical Branch return, tracking state, COD and POD. Actual MariaDB unique-constraint rejection was verified for both active rider and reserved parcel claims.

Operational create/dispatch/outcome tests ran on the restored DB, hindi sa existing development orders. Development data verification used read-only pages/APIs and full table hashes.

An additional older LogisticsBranchUiRoutesTest reports one unrelated failure: it expects resources/views/logistics/sorting-centers/form.blade.php, which is absent in both HEAD and the current tree. That view/module was not modified for this migration. The other test in that file passed. This does not change the 22 passing focused regression tests reported above.

The full Flutter suite passed 65 tests during implementation; no Flutter source changed in this local migration task. Actual-phone end-to-end testing and simultaneous multi-worker race/load testing were not run. MariaDB uniqueness constraints were exercised directly, not inferred from SQLite.

## Paggamit

Sa Branch portal, buksan ang Rider Batches. I-configure ang actual vehicle capacity at official coverage ng rider bago gumawa ng route; piliin ang Pickup o Delivery, parcels, measurements, stops at schedule. Branch lang ang nagdi-dispatch at nagsasara ng batch. Company/Main Hub console monitors the batches; rider mobile receives the route details after refresh.

Application approval/activation, six-rider barangay quotas and controlled transfers remain separate work from the broader rider-management specification.
