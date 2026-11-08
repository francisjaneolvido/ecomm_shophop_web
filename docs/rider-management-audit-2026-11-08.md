# Rider Management: audit at proposed implementation

Audit date: 2026-11-08. Sakop: Laravel web/backend at Flutter mobile. Source of requirements: attached FINAL MULTI-LOGISTICS RIDER MANAGEMENT SYSTEM spec.

**Review stage pa lang.** Documentation lang ang binago sa audit na ito. Walang production code, migration, application record, credentials o .env na binago. Baseline tests use the existing testing configuration with SQLite :memory:, separate from the local MySQL connection.

Confirmed branding: existing web teal #21C3A6 / #18A98E, light teal #D4F5EE, at Poppins. Panatilihin ang shared rounded white mobile navigation at raised Scan.

## 1. Existing architecture na ire-reuse

- `app/Models/Logistics/Rider.php`: iisang Rider account; may `logistics_partner_id`, isang `sorting_center_id`, home-address fields, documents, `status`, at `availability_status`. Walang kailangang bagong pickup-rider o delivery-rider account/table.
- `app/Models/LogisticsPartner.php`: may `accepting_riders`, company profile, approval through its User, at company relationships.
- `SortingCenter`, `BranchStaff`, company User at branch User: existing hub/branch hierarchy at authentication guards. Kailangan ng consistent authorization resolver/policy para sa mga actor na ito; hindi bagong login system.
- `OperationalServiceArea`, `operational_area_rider`, at `RiderServiceAreaService`: branch service areas, official rider assignments, location matching, pickup/delivery/both semantics.
- `app/Http/Controllers/Branch/DispatchController.php`: existing branch-scoped pickup at delivery assignments.
- `RiderCustodyScanService`, MobileRiderDelivery/Scanner/Return/Cod controllers: QR, proof, return custody, COD at assignment history. I-e-extend ang eligibility checks, hindi ire-rewrite ang parcel lifecycle.
- `RiderRegistrationDocuments`: private document storage at scoped access. Hindi gagawing public URLs ang IDs/licenses.
- Existing geographic services at service-area data ang magiging source ng validated working-area options.

## 2. Audit findings

| Requirement | Current behavior | Kailangan |
| --- | --- | --- |
| Company selection | Mobile API filters approved providers accepting riders; registration stores selected company | Filter by preferred working area and actual operational service coverage too |
| Home vs work coverage | Home PSGC fields exist; no preferred branch/coverage submitted | Separate preferred work location, branch and barangay preferences |
| Application review | Pending, active, rejected, suspended share one status | Separate application and operational lifecycle |
| Approval vs activation | `Logistics/RiderController::approve` directly sets active/available; manual `store` also creates active riders | Main Hub approval must create pending activation, with Branch-controlled activation |
| Branch ownership | Branch dispatch is scoped; rider CRUD/coverage/activation stays in company console | Dedicated Branch Rider Management with server-enforced branch ownership |
| Company isolation | Existing rider management scopes by logistics_partner_id | Preserve and extend this to all new reads, writes, documents, history and notifications |
| Main Hub oversight | Company-wide rider page already has totals, search and a center filter | Grouped branch monitoring, application/history/profile views and more filters |
| Daily assignments | Both Branch and company-level Logistics assignment endpoints exist | Routine assignment authority belongs to Branch; old company endpoints must not bypass this |
| Availability | Stored, but no complete mobile toggle/status workflow | Online/offline/busy/on break, with server-enforced assignment eligibility |
| Coverage fallback | Service eligibility returns true for an unconfigured branch before checking availability | Remove this bypass for new assignments; preserve controlled handling of existing custody |
| Capacity | No provider/barangay quota or authorized override workflow found | Default 6 distinct active riders per provider/barangay across branches and task types |
| Concurrency | Parcel updates are transaction/row-lock protected | Also serialize rider eligibility/workload and capacity mutations to prevent cross-parcel/activation races |
| Branch transfer | Company updateArea checks active Delivery records, detaches old areas and directly changes branch | Approval workflow plus returns, transit custody and unsettled COD checks; no silent relocation |
| Pending mobile experience | Login primarily returns review messages; operational dashboard requires approved access | Restricted applicant session/status screen, document requests, branch-change confirmation and activation refresh |
| History/audit | Parcel events exist; registration_activities belongs to marketplace Users | Rider lifecycle audit must support Rider subjects and both User/BranchStaff actors |

`application_status`, `operational_status`, pending activation, capacity approvals and rider branch-transfer records are not present in the inspected rider schema/routes.

## 3. Local database readiness

Read-only schema/aggregate queries on the configured local MySQL database found:

- 2,427 riders, all currently status=active and availability_status=available.
- 1,831 centers: 85 province_hub, 1,746 station_hub. All currently have is_main=0.
- 2,289 operational service areas; 1,619 are citywide rows with barangay=NULL.
- 2,289 rider-area links; 763 riders have no official operational-area mapping.
- No riders without a primary branch, no rider/branch company mismatches, and no mismatched existing official area links were found by these aggregate checks.

These are local-data observations, not a production audit or proof that demo staffing represents actual operating capacity.

**Migration implications:**

1. Confirm each company's Main Hub/admin ownership explicitly. Do not silently choose the first branch as Main Hub or let Province Hub approval substitute for it. Existing company-owner console can be reused for company-level administration.
2. Backfill lifecycle fields without revoking existing valid rider access or fabricating historical approvals. Record the migration source for legacy state; retain all existing email/document eligibility checks.
3. Resolve canonical barangay identities before enabling the quota. Citywide rows must count toward every barangay they cover; they must not bypass the quota or be counted as a fake single barangay. Reuse the existing geographic data and report ambiguous/unmapped locations for review.
4. Report existing over-capacity/missing-coverage cases. Do not delete coverage, deactivate riders or automatically reject applications to make counts fit.
5. Keep approval/activation rollout coordinated with all scanner, assignment and mobile gates. No live migration until the schema/backfill plan is reviewed.

## 4. Proposed states and authority

Application: pending_review -> under_review -> needs_additional_documents / approved / rejected.

Operations: pending_activation / active / inactive / suspended / transferred. Availability: online / offline / busy / on_break. An approved active rider may be offline and still occupy a barangay slot.

- Main Hub/company administrators: own-company application decisions, branch confirmations, monitoring, capacity overrides, serious suspension and transfer approvals. Reasons/history required for exceptional actions.
- Assigned station/branch manager: approved riders in that branch, onboarding, official coverage, activation/deactivation, availability/schedules and daily assignment management.
- Province Hub and scanner-only staff: no rider registration/activation approval powers.
- Rider: own application/documents/confirmation, availability and authorized assignments. No client-supplied company or branch ID grants authority.

Material branch reassignment requires rider confirmation before operational activation. Provider approval is not enough for assignment eligibility. A suspended rider's existing COD-return path must not be stranded by the new gates.

## 5. Proposed database extensions (not created/applied)

Reuse `riders` and existing official coverage/assignment tables.

- Add separate lifecycle fields, preferred branch/work-barangay preferences, branch-confirmation fields and activation metadata to riders. Keep the home address separate. Retain a compatibility mapping for legacy status readers during rollout.
- Add append-only rider management events: actor, company/branch scope, before/after state, reason and timestamp. Existing User-only registration_activities is not sufficient for rider lifecycle events.
- Add controlled rider branch-transfer requests with from/to branch, approval/consent state, handover resolution and history.
- Add per-provider canonical barangay limits, unique on provider + barangay identity, default capacity 6.
- Add scoped capacity-override authorizations with target rider/coverage, mandatory reason, approver and consumption/audit information.
- Add schedule/workload metadata only after the conflict policy is reviewed; do not duplicate Delivery or parcel-assignment records.

Capacity counts DISTINCT rider IDs across branches, excluding pending/rejected/inactive/suspended riders and ignoring pickup-vs-delivery duplication. All activation, reactivation, coverage expansion and transfer paths must use the same guard. Lock quota rows in a stable order, recheck under transaction, and enforce unique constraints. Applications can wait or select alternative coverage when full.

## 6. Focused implementation phases and affected files

Exact migration filenames and any additional files will be announced before each implementation batch.

**Phase 1: lifecycle, authorization and safe compatibility.**

Existing web files: `app/Models/Logistics/Rider.php`, `app/Http/Controllers/Logistics/RiderController.php`, `app/Http/Controllers/Api/MobileAuthController.php`, `app/Http/Middleware/EnsureActiveRider.php`, and affected operational rider guards. Planned additions: `app/Policies/RiderManagementPolicy.php`, `app/Services/RiderLifecycleService.php`, rider audit model and additive lifecycle/history migration(s).

Outcome: approval differs from activation; Main Hub, Branch, Province Hub and scanner permissions are explicit; existing valid parcel/COD workflows remain compatible.

**Phase 2: registration and applicant experience.**

Web: `app/Http/Controllers/Api/MobileRiderAuthController.php`, `app/Services/RiderRegistrationDocuments.php`, `routes/api.php`, a scoped applicant controller and reviewed preference fields.

Mobile: `lib/features/auth/data/services/auth_service.dart`, `lib/features/rider/registration/screens/rider_registration_screen.dart`, `lib/features/rider/registration/steps/rider_details_step.dart`, `lib/features/auth/presentation/screens/login_screen.dart`, and a new `lib/features/rider/presentation/screens/rider_application_status_screen.dart`.

Outcome: geographic company/branch choices, separate preferred coverage, private document requests, and rider confirmation of material location changes. Pending applicants receive limited application access, never operational API access.

**Phase 3: Branch activation, coverage and Main Hub monitoring.**

Web: `routes/branch.php`, `routes/logistics.php`, `resources/views/branch/layout/app.blade.php`, `resources/views/logistics/riders/index.blade.php`, company console navigation, and new Branch rider controller/views. Add the reviewed quota/override service and migrations.

Outcome: Branch Pending Activation/Active/Inactive lists, audited official coverage/activation, shared capacity enforcement, grouped Main Hub branch monitoring with province/branch/barangay/status/name filters, and profiles/history/performance from existing delivery data.

**Phase 4: availability, workload and transfers.**

Web: `app/Services/RiderServiceAreaService.php`, `app/Http/Controllers/Branch/DispatchController.php`, `app/Http/Controllers/Logistics/DeliveryController.php`, `app/Http/Controllers/Logistics/ReturnController.php` and relevant return/custody services; reviewed workload/transfer services, endpoints and metadata.

Mobile: `lib/features/rider/presentation/screens/rider_home_screen.dart`, rider API/model services and registration/status screens as needed.

Outcome: daily operations owned by Branch, online/offline/break controls, no unsafe cross-company/branch assignment, safe transfer after custody/COD resolution and destination-branch activation. Pickup and Delivery remain task types on one Rider account.

**Phase 5: acceptance and regression testing.**

Extend existing coverage, role-boundary, registration and parcel journey tests; add focused rider-management/capacity/transfer tests and Flutter applicant/availability tests. Avoid replacing the existing working QR/POD/COD test contracts.

## 7. API direction and compatibility

- Extend existing GET `/api/mobile/rider/logistics-centers` with validated preferred-work-area filters. Add provider-scoped branch/coverage options; registration revalidates all choices server-side.
- Extend the existing registration endpoint with preferred branch/coverage fields; retain a clear upgrade/validation response for older clients rather than silently assigning a random branch.
- Add authenticated own-application status, document submission and branch-confirmation endpoints. Applicant sessions have limited abilities; operational controllers still check persisted activation and scope.
- Extend profile data with application_status, operational_status, availability, assigned company/branch and official coverage.
- Add an authenticated availability endpoint with allowed transitions. Offline/break status prevents new assignments and does not silently erase already held custody.
- Add Branch-scoped management/assignment endpoints and Main Hub application/monitoring/override/transfer endpoints. Retain existing parcel scanning/proof/COD contracts.
- Mobile refresh is currently foreground polling (15 seconds for Rider), not WebSocket push. Reuse polling for status updates first; no claim of push realtime without a tested broadcast implementation.

## 8. Baseline verification and acceptance plan

Baseline command run during this audit:

```powershell
php artisan test --compact tests/Unit/RiderServiceAreaMatcherTest.php tests/Unit/RiderBarangayAliasTest.php tests/Feature/MobileRoleWorkspaceContractTest.php tests/Feature/MobileScannerRouteContractTest.php tests/Feature/LogisticsConsoleAccessBoundaryTest.php
```

Result: **14/15 existing tests passed; 92 assertions.** One existing boundary test expects `http://localhost:8000/login`, while the application redirects logistics guests to `http://logistics.localhost:8000/sign-in`. This is a baseline expectation mismatch; no test/controller was changed for it. The requested new workflow is not implemented or validated by these baseline results.

Acceptance cases after implementation:

1. Applicant selects A; only authorized A Main Hub reviewers can access the application and documents.
2. B and other branches cannot read or mutate it, including direct-ID/API requests.
3. Main Hub approval does not grant operational access.
4. Correct assigned Branch sees Pending Activation; material branch changes require rider confirmation.
5. Branch finalizes coverage and activates; Province Hub/scanner-only roles are denied.
6. Main Hub monitoring updates under the correct Branch with truthful counts.
7. One Rider can receive pickup and delivery work under the reviewed workload rules.
8. Conflicting, unavailable and unauthorized-coverage assignments are rejected server-side.
9. Capacity is shared across branches/task types; duplicate coverage does not double-count.
10. Concurrent activation at 5/6 permits only one normal additional rider; approved override is scoped and audited.
11. Main Hub cannot use routine Branch-only assignment endpoints.
12. Transfer requires approval and custody/returns/COD resolution, removes old coverage atomically, and requires destination activation.
13. Flutter accurately shows application, operational state, availability, branch, coverage and assignments.
14. Citywide areas and existing over-limit/missing-mapping records cannot bypass new capacity rules.
15. Regression: QR replay/stale writes, POD, tracking, returns, COD remittance and physical handoff requirements remain intact.

SQLite tests verify policy/transition logic; concurrency locks must also be exercised against an isolated MySQL test database, never inferred from SQLite behavior alone.

## 9. Review decisions before implementation

- Approve the additive state model/backfill strategy and company Main Hub designation approach before migrations are written/applied to live data.
- Confirm the conflict rule: multiple queued parcels and pickup/delivery on the same day should remain possible; whether a rider can batch active pickups/deliveries, and what constitutes an overlapping run/schedule, needs an explicit rule. Do not implement a blanket one-parcel limit as an assumption.
- Review the citywide/PSGC coverage migration and handling of existing missing coverage before strict quota enforcement.

Recommended first implementation batch: Phase 1 only, then verify its migration and compatibility in an isolated test database before continuing with registration and Branch activation.
