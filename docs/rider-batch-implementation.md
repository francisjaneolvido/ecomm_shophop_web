# Rider batches: implementation and database review

Date: 2026-11-08. Laravel backend/web and Flutter mobile.

Local deployment completed on 2026-11-08, migration batch 25. See rider-batch-local-migration-2026-11-08.md for the verified backup, MariaDB integration tests, local data-preservation checks and resolved migration incident.
Branding: existing web teal (#21C3A6 / #18A98E), Poppins, existing rounded white mobile navigation and raised Scan.

## Implemented scope

One Rider -> One Active Batch -> Multiple Parcels -> Multiple Stops -> Independent Parcel Tracking.

- Pickup and Delivery are separate batch types using existing Rider, Delivery, PickupRequest and Order records.
- The assigned active station Branch plans, queues, dispatches, monitors, completes and cancels unstarted batches. An active Branch manager staff session can use the same planner. Province/main-center staff and scanner-only staff cannot manage batches.
- The company owner console supplies company-scoped, Branch-grouped operational monitoring. New monitoring endpoints are read only.
- A rider may have multiple queued batches with nonoverlapping schedules, including Pickup and Delivery on the same day. Actual activation also requires no other in-progress batch, including when an earlier route overruns.
- Vehicle limits are configured per actual rider vehicle: weight, volume, stops and estimated duration. A parcel-count limit is optional; no fixed six-parcel rule exists. Six riders per barangay is a different, still-pending lifecycle requirement.
- Unknown parcel size is rejected; it is not counted as zero volume. Weight is at least the existing shipping-weight snapshot when present.
- Plans require current provider/primary Branch, active and verified rider, availability, official service-area permission, schedule and parcel payment/state/custody eligibility.
- Dispatch rechecks vehicle, coverage, current Branch custody, address snapshots, return/linehaul conflicts and parcel state under locks.
- Each parcel retains existing QR/custody, tracking, COD, proof-of-delivery and failure handling. A failed pickup resolves only that batch item. A failed delivery requires a confirmed physical Branch QR return before batch completion/retry.
- Closed batches retain parcel outcome snapshots even if the same Delivery is retried later.
- Flutter Home/Tasks displays route cards without duplicate flat parcel cards. Details group stops, show independent outcomes/COD/progress, and link into the existing QR/POD actions. Queued and historical routes expose no batch mutation actions.
- Flutter and web refresh visible operational data every 15 seconds. This is foreground polling, not WebSocket push. Flutter pauses on background/covered routes; web pauses on hidden tabs, focused route controls and confirmation dialogs. Polling does not alter planner selections.

## Changed files

New web/backend:
- app/Models/Logistics/RiderBatch.php
- app/Models/Logistics/RiderBatchItem.php
- app/Models/Logistics/RiderBatchVehicleProfile.php
- app/Services/RiderBatchService.php
- app/Http/Controllers/branch/RiderBatchController.php
- app/Http/Controllers/Logistics/RiderBatchMonitoringController.php
- app/Http/Controllers/Api/MobileRiderBatchController.php
- database/migrations/2026_11_08_000002_create_rider_batches.php
- resources/views/branch/rider-batches/index.blade.php
- resources/views/logistics/rider-batches/index.blade.php
- resources/views/logistics/partials/rider-batch-card.blade.php
- resources/views/logistics/partials/rider-batch-routes.blade.php
- public/js/shophop-rider-batches.js
- tests/Feature/RiderBatchWorkflowTest.php
- tests/js/rider-batch-refresh.test.cjs
- docs/rider-batch-implementation.md

Existing integration points:
- routes/branch.php, routes/logistics.php, routes/api.php
- app/Http/Controllers/branch/DispatchController.php
- app/Http/Controllers/Logistics/DeliveryController.php
- app/Http/Controllers/Rider/DeliveryController.php
- app/Http/Controllers/Api/MobileRiderDeliveryController.php
- app/Services/RiderCustodyScanService.php
- app/Services/ParcelScanService.php
- app/Services/ParcelReturnService.php
- resources/views/branch/layout/app.blade.php
- resources/views/logistics/layouts/console.blade.php
- resources/views/logistics/staff/workspace.blade.php

Mobile:
- lib/features/rider/data/services/rider_batch_service.dart (new)
- lib/features/rider/presentation/screens/rider_batch_detail_screen.dart (new, includes history)
- lib/features/rider/presentation/screens/rider_home_screen.dart
- test/rider_batch_ui_test.dart (new)

Other pre-existing project changes, including earlier Buyer/Seller/UI work and deleted duplicate folders, are not part of this batch change.

## Database impact: review before live application

The migration creates four empty additive tables:

| Table | Purpose | Important constraints |
| --- | --- | --- |
| rider_batch_vehicle_profiles | Vehicle snapshot and configurable limits | One profile per existing rider; actor User/BranchStaff references |
| rider_batches | Route type, UTC schedule, state and capacity snapshot | Unique code; unique nullable active_rider_id; existing company/Branch/rider references |
| rider_batch_items | Existing Delivery membership, stop snapshot, measurements and outcome | Unique nullable reserved_delivery_id; unique batch + Delivery; retained historical Delivery reference |
| rider_batch_events | Append-only service transition/outcome audit | Existing batch reference; actor type/id and JSON details |

- No existing table columns, accounts, passwords, .env settings or seeders change.
- No existing rider/order/parcel rows are backfilled, deleted or reassigned by this migration.
- Nullable unique claims allow many queued/history rows but only one active route per rider and one open reservation per Delivery.
- Services serialize changes through rider row locks, lock parcels and use transactions/retries. Database uniqueness is a second barrier.
- Schedule columns use UTC datetime values, avoiding MySQL TIMESTAMP's 2038 schedule range.
- New foreign keys restrict deletion of referenced riders/Deliveries/Branches/companies. Actor user/staff references may become null if those actor records are deleted.
- Applied only to local development 127.0.0.1/shophop (MariaDB), batch 25, after the approved backup/review process. No production or shared database approval/deployment.
- Do not run a general migration command blindly: review other pending migrations first. Apply only the reviewed migration path after approval.
- Do not roll back these tables once operational history exists; retain audit/custody history and use a forward fix.
- The migration does not resolve the broader lifecycle/backfill, PSGC/citywide coverage or Main Hub designation decisions in the original audit.
- Index names stay within the MariaDB/MySQL identifier limit. A guarded forward-only path can complete the known empty two-table prefix from the interrupted local attempt; it cannot modify business tables or replay over populated/unexpected partial batch data. The batch service requires all four tables before enabling operations.

## API and web routes

All new routes use existing authenticated, role-scoped boundaries.

| Actor | Route | Behavior |
| --- | --- | --- |
| Branch User | GET/POST /branch/rider-batches (existing Branch prefix) | HTML/JSON progress or queue a route |
| Branch User | POST rider-batches/riders/{rider}/capacity | Configure actual vehicle limits |
| Branch User | POST rider-batches/{batch}/transition | dispatch / complete / cancel |
| Branch manager staff | Matching /staff/rider-batches routes on logistics host | Same operations, restricted to own Branch |
| Company owner | GET /rider-batches on logistics host | Read-only monitoring, Branch/status filters, JSON progress |
| Rider token | GET /api/mobile/rider/batches | Own queued/active routes; page, history=1 for closed |
| Rider token | GET /api/mobile/rider/batches/{batch} | Own route detail |
| Rider token | POST /api/mobile/rider/batches/{batch}/items/{item}/pickup-failure | Reason required; uncollected pickup only |

The existing mobile assignments response adds rider_batch_id, rider_batch_status and rider_batch_pickup_failed. This identifies all reserved parcels even when their route is on a later batch-list page.

## Rollout and operating sequence

1. Review the additive schema, database backup/retention procedures and other pending migrations.
2. Apply only the reviewed migration, then deploy the matching mobile build.
3. Assign verified riders to official Branch service areas using existing coverage tooling. Existing explicit citywide permissions remain valid; no fallback from missing official coverage is used for new batch work.
4. Resolve any older physical parcel/return custody. Configure vehicle capacity in the assigned Branch planner; this enrolls that rider into batch-controlled pickup/delivery assignment.
5. Select Pickup or Delivery, choose the configured rider, schedule and estimated duration. Select eligible parcels across pages and enter measurements/order. Selections persist only in that browser tab, separated by Branch and batch type.
6. Queue the batch. Branch dispatches within the scheduled window. Rider scans each parcel, performs the existing individual delivery/POD/COD actions, and returns required parcels to the Branch scanner.
7. Branch completes only after all individual outcomes and required handoffs are resolved. A future batch may then start. Cancelling a queued batch releases its reservations but preserves existing uncollected assignment records and audit history.

On an unmigrated environment, new navigation links are hidden and the mobile batch API returns enabled=false; existing workflows remain available. The local development database now has all four tables and the Rider batch API returns enabled=true. Existing unenrolled riders retain legacy assignment paths. Enrolled riders/reserved parcels cannot bypass Branch batch planning through the old company/single-parcel endpoints.

## Validation

- Full Flutter suite: 65 passed (includes 6 new batch tests and existing Buyer/Seller/navigation/scan-sound/Linehaul/Logistics checks).
- Post-migration rider batch, mobile workspace/scanner and Logistics Management route suites: 22 passed, 278 assertions. The workflow test asserts SQLite :memory: before creating fixtures.
- Web refresh JavaScript: 5 passed; visible refresh, expanded stops/totals, hidden/focused/dialog pauses, expired-session stop, recovery and browser back cache.
- Targeted Flutter analyzer: no issues. PHP syntax checks passed for all changed controllers/services/routes and the migration. Focused git diff whitespace check passed.
- MySQL SQL preview (migrate --pretend --path=database/migrations/2026_11_08_000002_create_rider_batches.php) completed without applying the migration.
- Post-migration local MariaDB verification: four intended empty tables, 65 original business-table data/schema hashes unchanged, 2,427 Riders and 1,831 sorting centers unchanged. Only the intended migration-ledger entry was added.
- Restored MariaDB integration suite: 16 passed, 207 assertions, including actual unique-constraint rejection and transaction-only fixtures.
- Tests cover same-day separate Pickup/Delivery batches, all capacity dimensions, multiple sellers/buyers, independent failure, verified Branch return, COD/POD, stale custody/address/coverage, queued overlaps, overrun activation denial, provider/Branch/rider isolation, scanner denial, database uniqueness and historical outcome preservation.
- No actual-phone end-to-end or simultaneous multi-worker race/load test was run. Transitions and database uniqueness were verified on both SQLite and the restored local MariaDB database; this is not a concurrency load-test claim.

## Remaining work from the broader rider-management specification

This is the focused batch/workload implementation. The complete registration/activation lifecycle is not claimed complete. The original audit still identifies:
- Separate application/operational statuses and reviewed legacy-state backfill.
- Preferred work area/Branch selection, Main Hub application/document review, reassignment confirmation and Branch Pending Activation.
- Configurable six ACTIVE UNIQUE riders per barangay/company across branches, audited overrides, citywide/PSGC migration and activation race validation.
- Full company Rider Monitoring with lifecycle/performance filters and Branch rider/availability administration.
- Audited suspension/controlled Branch transfers after parcel/return/COD reconciliation.
- Applicant status/notifications and availability controls in Flutter.

See rider-management-audit-2026-11-08.md. Those items require the lifecycle/backfill review described there; this new batch migration does not silently modify 2,427 existing Rider records.
