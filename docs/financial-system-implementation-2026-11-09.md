# ShopHop hybrid financial system implementation ? November 9, 2026

## Delivery status

The core COD, settlement allocation, and partner compensation workflows are implemented in the existing Laravel and Flutter applications. **The entire specification is not yet complete.** Refund allocation/reversal policies, arbitrary bank/custodian transfers, delegated Hub financial permissions, and prepaid payment integration remain outside the implemented workflows. This report distinguishes working functionality from those limitations.

No production database migration, money transfer, deployment, release APK build, or historical financial backfill was executed. Existing working-tree content in the mobile device-acceptance document was left untouched.

## A. Architecture review and implementation map

| Area | Existing implementation reused | Changes |
| --- | --- | --- |
| Authentication | Web users, approved roles, Rider guard, Sanctum, operational token scopes | Financial actions verify the existing actor and partner/facility ownership; no new roles |
| Orders/checkout | BuyerCheckoutService, frozen Order amounts, OrderFinancial | Product/shipping rate snapshots, exact-centavo allocations, allocation-total validation |
| COD | cod_settlements, delivery proof, collector identity, existing remittance routes | Multi-order batches, independently counted per-order cash, partial handovers, differences, custody receipts |
| Settlements | SettlementEntry, Admin prepare/distribute/manual-payment actions | Seller/partner allocations stay separate from personnel; historical snapshots retained; dispute checks |
| Pickup operations | Verified first-mile facility receipt through ParcelScanService | Partner policy creates pickup earnings immediately, independent of Seller settlement |
| Delivery operations | Web/mobile successful delivery and proof capture | Partner delivery policy creates one earning per verified delivery |
| Linehaul | Completed trips after verified receipt of transferred parcels | Partner trip policy replaces automatic platform-wide Driver wage generation |
| Personnel earnings | Historical Rider settlements, pickup accruals, Driver earnings | New partner-owned policy/earning records; historical obligations retained and not recalculated |
| Web | Admin commission/COD screens, Logistics earnings/remittances, Branch navigation | Role-scoped financial workspace, batch verification, policies, payroll, expenses, audit history |
| Flutter | Existing session, HTTP client, Rider COD/earnings and Linehaul screens | Authoritative summaries, multi-order handover, receipt history and separate partner compensation |

The former settlement preparation automatically split shipping among Riders, branches and Hubs. That automatic split is removed for newly prepared orders. The Logistics Partner shipping allocation is its gross contractual allocation, rather than an automatic employee payroll distribution. Existing recorded allocations remain unchanged.

There are no new background jobs, queues, duplicate role systems or standalone wallet modules. Service-event earnings are recorded synchronously within the existing verified operational transaction. New table access uses transaction services/query builders; existing financial Eloquent models were extended.

## B. Implemented functionality

### COD collections, batches and receipt verification

- Successful COD delivery preserves the authoritative Order total and proof. An optional actual_collected_amount can record an observed amount separately; it never modifies the Order total. Prepaid/failed deliveries do not automatically create COD collections.
- A Rider selects one or more owned collections and an active receiving facility belonging to the same Partner. Each item declares the actual handed-over cash.
- Server-derived expected cash is the outstanding collected cash, with no commission deduction.
- Every collection remains linked to its original Order and collector. The batch has a unique reference, receipt/evidence reference and idempotency key.
- One collector lock serializes submissions. Repeating the same key/payload returns the same batch. A different key cannot reserve an already submitted collection. Changed replay payloads are rejected.
- Only the owning approved Logistics User or the approved Branch User assigned to the receiving facility can verify cash. The Rider cannot verify their own batch. Platform Admin has oversight, not a shortcut to count cash at an unrelated facility.
- Receivers enter actual counted cash separately for each collection. Receipt verification is transactional and replay-safe.
- A shortage remains owed. A later supplemental handover can settle it without rewriting the original deficient batch.
- Overage remains a discrepancy and is not recognized as platform revenue. Per-order differences remain visible even when the batch's aggregate difference is zero.
- Liability totals do not use another Order's overage to erase a shortage.
- Each verified batch creates one custody movement with the source Rider, destination facility, receiving User, actual amount, reference and timestamp.
- Existing single-order remittance routes remain available for legacy clients. They cannot bypass a collection's new batch verification.
- The existing Partner-to-platform remittance and independent Admin receipt actions now append sender/receiver audit events.

### Platform and settlement calculation

- FinanceMoney parses decimal amounts into integer centavos and rounds percentage calculations half up.
- Product commissions apply to merchandise after Seller merchandise discounts. Seller shipping discounts remain Seller-funded. Platform promotions remain platform-funded.
- Shipping commissions are configurable separately; the Partner receives shipping less the shipping commission.
- Each new snapshot preserves product rate, shipping rate, shipping commission, policy ID and Seller hold days.
- An allocation must equal the persisted Buyer total before its financial snapshot is accepted. A Seller shipping discount that would exceed the Seller allocation is rejected for review rather than silently creating an unfunded payout.
- The existing default product commission is retained; new shipping commission defaults to zero until configured. The demonstration 5%/15% rates appear in tests, not production seed data.
- Admin can schedule prospective product/shipping rules with effective timestamps. The latest active scheduled rule takes precedence over fallback marketplace settings.
- Buyer confirmation satisfies Seller eligibility. Alternatively, a positive snapped hold period permits eligibility after the delivery date plus that period. Zero preserves the existing Buyer-confirmation requirement.
- Existing COD funding confirmation, successful delivery, unresolved return cases, manual holds and unresolved disputes continue to gate settlement.
- Seller/Partner prepare, distribution and actual payment remain distinct stages. Distribution is a ledger release and does not claim a bank transfer.
- Admin dashboard fees include both product and shipping fees and exclude held, cancelled, returned and unresolved disputed orders from allocated totals. Seller web reports and CSV exports use frozen per-order fees/allocations instead of a hardcoded 10% estimate; unreviewed historical amounts remain explicitly unknown. Seller shipping-voucher caps use the active effective-dated product commission.
- New preparation creates only Seller and Logistics settlement entries, protected by the existing Order/kind unique constraint.
- Missing historical financial snapshots fail closed. Today's rates are never used to invent historical commission snapshots.

### Partner compensation and operating expenses

- A Partner creates policies only for its own Rider or Linehaul Driver, with effective dates and a payout schedule description.
- Supported arrangements: per delivery, per pickup, per trip, fixed monthly salary, hourly, and monthly base plus incentives.
- Policy periods for the same person/type cannot overlap. A Partner may set a prospective final date on an open policy and create its replacement afterward. Existing earning amounts remain frozen.
- No applicable service policy means no automatic commission. Platform-wide legacy Rider/Driver fee settings no longer generate new personnel earnings.
- Actual verified pickup, delivery and completed-trip events have stable event keys. Replaying an event cannot generate another earning.
- Monthly payroll uses a completed calendar month fully covered by the policy. Hourly compensation uses approved integer minutes. Base/incentive amounts are recorded separately in evidence.
- Earnings start pending, require Partner approval, then may be marked paid only with an actual external payment reference and explicit web confirmation.
- These records do not read Seller hold status, Buyer confirmation, COD funds, or release of the Partner's platform settlement.
- Historical pending Rider settlement obligations can be approved and paid from Partner funds without platform/Seller settlement release. Existing amounts are preserved.
- Operating costs use separate Partner-scoped records for Hub/Branch operations, fuel, transport, maintenance, sorting and other costs. Evidence references are unique per Partner. Incurred costs and confirmed externally paid costs remain distinguishable.
- Personnel compensation must not be entered again as a generic operating expense. No remaining-revenue figure is represented as final profit.

## C. Laravel backend inventory

### Additive migrations

1. `2026_11_09_000001_add_hybrid_financial_workflows.php`
   - Adds shipping commission configuration and Order financial rate/model snapshots.
   - Creates financial_audit_events, cod_remittance_batches, cod_remittance_items, cash_custody_movements, partner_compensation_policies and partner_personnel_earnings.
2. `2026_11_09_000002_add_financial_policy_history.php`
   - Creates platform_financial_rules and partner_operating_expenses.
   - Adds the Order policy reference and snapped Seller hold days.

New relationships use foreign keys and restricted deletes where applicable. Existing orders, collections, settlements and historical earnings are not backfilled, removed or repriced.

### Services and authorization

- FinanceMoney: decimal conversion and integer percentage arithmetic.
- FinancialAudit: append-only application audit writer.
- CodRemittanceService: submission, item allocation, receipt verification, discrepancy and custody posting.
- PartnerCompensationService: scoped policies, verified service events, monthly payroll, approval and actual-payment recording.
- PersonnelFinanceReadService: scoped compensation summaries, arrangement metadata and history.
- PlatformFinancialPolicyService: effective rules and Seller eligibility.
- ShopHopFinance: updated snapshots, settlement integrity, dispute checks and independent historical Rider approvals.
- PickupRiderAccrualService and LinehaulService: existing verified hooks now invoke Partner compensation.

Existing OrderFinancial and MarketplaceRateSetting models were extended. HybridFinanceController supplies the new workspace and mutations. Existing Admin, Logistics, Rider and mobile controllers remain in use. Authorization lives in existing middleware plus explicit service/controller ownership checks; there is no new global permission bypass or client approval logic.

Request validation covers decimal precision, positive/allowed amounts, distinct item IDs, bounded batch sizes, facility references, policy/personnel types, effective dates, payout references and explicit actual-payment confirmation. Database transactions and row locks guard financial state transitions.

## D. Web screens and routes

- Main application: `/financial-workspace` for approved Admin oversight and authenticated Riders.
- Logistics host: `/financial-workspace` for the owning Logistics Partner and approved facility Branch Users.
- Existing Admin commission screen: configurable commissions, allocation/payout controls and a workspace link.
- Existing Logistics earnings/remittance screens: workspace links and historical Rider approval independent of platform settlement.
- Branch navigation: COD verification link.
- New workspace: multi-order Rider submission; batch/item history; independent per-order receipt forms; Partner compensation policies; prospective policy end dates; monthly payroll; earnings approval; confirmed external payment records; operating expenses; Admin policy scheduling and audit history.

Main route names are `hybrid.index`, `hybrid.submit`, `hybrid.verify`, `hybrid.platform-rule`, plus guarded main-host mutation routes. Logistics names are `logistics.hybrid`, `.verify`, `.policy`, `.policy.end`, `.payroll`, `.approve`, `.paid`, `.legacy.approve`, and `.expense`.

## E. Flutter/API integration

- Existing `GET /api/mobile/rider/cod` now returns authoritative to-collect, collected, verified-remitted, outstanding and discrepancy summaries, facility options, collections and recent batches with items.
- Outstanding collections remain available even if older than the latest 100 history entries.
- New `POST /api/mobile/rider/cod/batches` accepts a facility, stable request key, evidence reference and per-collection decimal amounts under Sanctum authentication.
- Existing Rider/Linehaul earnings responses include a separate partner_compensation object. Historical ledgers are retained.
- RiderCodOverview maps decimal strings and signed integer-centavo history for display.
- Existing Rider COD navigation opens the enhanced screen, with multi-order selection, facility choice, actual handover amounts, validation, server errors and refresh.
- Rider and Linehaul screens display Partner earnings independently of COD. Decimal API amounts are parsed only for presentation; financial approval/calculation remains on Laravel.
- Existing sessions, navigation routes, API error envelopes, loading indicators and the application theme are reused.

## F. Financial verification

The automated primary journey verifies the following configured example; no actual funds were moved:

| Component | Amount |
| --- | ---: |
| Merchandise | PHP 1,000.00 |
| Shipping | PHP 100.00 |
| Buyer COD / Rider collection | PHP 1,100.00 |
| Independently verified remittance | PHP 1,100.00 |
| Product commission at 5% | PHP 50.00 |
| Shipping commission at 15% | PHP 15.00 |
| Seller allocation | PHP 950.00 |
| Platform combined fees | PHP 65.00 |
| Logistics Partner allocation | PHP 85.00 |
| Allocation check | PHP 950 + PHP 65 + PHP 85 = PHP 1,100 |
| Independent Partner delivery earning | PHP 25.00 |
| COD still due before handover | PHP 1,100.00 |

The test approves and pays the PHP 25 personnel earning before Buyer confirmation/platform release, then separately verifies the full PHP 1,100 COD. Repeated preparation creates exactly two Seller/Partner settlement entries, with no new personnel/facility split.

Shortage verification uses PHP 1,050 received, retains a PHP 50 liability and accepts a separately referenced PHP 50 supplement. Overage verification uses PHP 1,120 received and retains the PHP 20 discrepancy. Two equal/opposite per-order differences still produce a discrepant batch and preserve the shortage liability.

## G. Validation

Final command results are recorded in the validation section below. Tests use the configured SQLite `:memory:` database. No production migration or payout was tested or executed.

Meaningful test coverage includes the primary allocation and compensation journey, shortages/top-ups, overages, multiple Orders, idempotent retries, duplicate reservations, cross-Partner and role denials, Branch receipt, effective rule timing, Seller hold timing, all six compensation arrangements, no-policy behavior, verified pickup/trip events, historical obligations, expense isolation, Order holds/cancellation and decimal rounding.

Screen-level verification also caught and fixed a malformed Admin rate-form Blade expression and an obsolete 10% Seller report estimate. Rendered Admin/Seller pages now verify PHP 65 combined platform fees and PHP 950 Seller allocation.

Fixture repairs found during regression testing: a Rider workspace fixture omitted the required operational token ability; two legacy promotion fixtures manually built obsolete partial schemas; a voucher fixture omitted the now-required merchandise/shipping kind. Test fixtures were brought up to current application contracts without relaxing application permissions or removing business assertions.

## H. Exact changed files

The source lists below include only this implementation's changes. The pre-existing mobile `docs/rider-device-acceptance-2026-11-09.md` is excluded.

## I. Setup and verification

In the Laravel repository, use the existing environment and installed dependencies. Apply the two additive migrations before using the updated web/API or deploying the corresponding mobile build:

```powershell
php artisan migrate --path=database/migrations/2026_11_09_000001_add_hybrid_financial_workflows.php --path=database/migrations/2026_11_09_000002_add_financial_policy_history.php
php artisan optimize:clear
npm run build
```

For a fresh checkout, install the repository's locked Composer/npm dependencies first and configure the existing database, main/logistics domains, session and authentication settings normally. No new payment credentials or production example seed data are required by these recordkeeping workflows.

1. Sign in as an approved Admin. Configure fallback product/shipping commissions or schedule a prospective financial rule in the workspace. Scheduled active rules take precedence. Effective timestamps use `config('app.timezone')`, displayed on the form.
2. Choose Seller automatic-eligibility days explicitly. Zero retains Buyer confirmation; positive values permit release after that many delivered days. All returns, disputes and cash checks still apply. This setting never becomes a Rider payroll hold.
3. Sign in as a Logistics Partner. Create personnel policies for its existing Rider/Driver IDs, actual rates, effective period and agreed payout schedule. Policies must exist before the verified service event. No wages are assumed automatically.
4. Complete actual pickup/delivery/trip operations through existing verified flows. A COD delivery requires proof and explicit cash collection.
5. As the collector, open COD Cash Handover, choose the orders/facility, record actual handed-over amounts and an evidence reference.
6. As the receiving Branch User or owning Partner, count and independently verify each item's cash. Review differences; submit a separate top-up for a shortage.
7. Continue the existing Partner-to-platform remittance and Admin funding-confirmation lane before Seller/Partner settlement preparation. These actions record actual custody/receipt, not automatic transfers.
8. Admin prepares and distributes an eligible Order. Record actual Seller/Partner external payments with references only after they happened.
9. Independently, the Partner approves personnel earnings and confirms actual personnel payments using its operating funds. Review audit events with the Admin workspace.

Backend verification command:

```powershell
php vendor/bin/phpunit tests/Feature/HybridFinancialWorkflowTest.php tests/Feature/CodCollectionRemittanceJourneyTest.php tests/Feature/FinanceBranchRoutesTest.php tests/Unit/ShopHopFinanceMathTest.php tests/Feature/MobileRoleWorkspaceContractTest.php tests/Feature/CheckoutVerifiedNetworkEligibilityTest.php tests/Feature/RiderBatchWorkflowTest.php tests/Feature/SellerVoucherJourneyTest.php tests/Feature/SellerPromotionsWorkspaceRegressionTest.php tests/Feature/MobileMarketplaceParityTest.php --no-progress
php vendor/bin/pint --test --dirty
```

Flutter verification commands, from the mobile repository:

```powershell
flutter test test/rider_financial_workflow_test.dart test/rider_management_test.dart
flutter analyze lib/features/rider/data/models/rider_cod_overview.dart lib/features/rider/data/services/rider_delivery_service.dart lib/features/rider/presentation/screens/rider_cod_remittance_screen.dart lib/features/rider/presentation/screens/rider_transit_earnings_screen.dart lib/features/linehaul/presentation/screens/linehaul_driver_home_screen.dart test/rider_financial_workflow_test.dart
```

## J. Remaining requirements and limits

1. **Refunds and financial reversals:** existing return/manual-hold/dispute controls block unsafe release. There is no implemented general partial/full refund allocation engine, approved monetary adjustment/reversal workflow, or recovery ledger for already paid settlements. Refundability of product/shipping/platform fees, promotions and post-payment recovery needs an explicit policy. Do not release held funds by inventing those allocations.
2. **Custody beyond supported lanes:** Rider-to-facility batches and the existing Partner-to-platform lane are traceable. Arbitrary facility-to-Hub/bank/alternate custodian chains, deposit reconciliation and overage return/disposition are not implemented. Overages remain under review; verified original receipts are not overwritten.
3. **Hub/staff delegation:** Branch User accounts and Partner owners can verify under the described scopes. `logistics_staff` Hub managers/scanners have not automatically received financial authority. An explicit delegated permission model and corresponding receipt actor relationships are still needed for those guards.
4. **Prepaid integration:** the current Order model/checkout is deliberately COD-only. A legacy non-COD row is not treated as paid. No prepaid provider, verified prepaid settlement lane, or automated bank/e-wallet transfer was introduced. New personnel/settlement payment records confirm external payments; they do not execute them.
5. **Payroll scope:** fixed/base/hourly payroll records currently use completed calendar months and approved minutes. Partial-month proration, payroll deductions/taxes, attendance integrations, earnings reversals and arbitrary payroll periods require agreed rules. Partner schedules are recorded descriptions; no automatic fund release scheduler is claimed.
6. **Historical data:** existing snapshots/earnings remain frozen. Missing historical snapshots and conflicting historical records require an audited review/import process that is not supplied here. Legacy handling/rate fields do not become new employee wage policies.
7. **Accounting scope:** these are auditable operational ledgers, not a complete double-entry general ledger. There is no claimed statutory accounting export or bank balance.
8. **Verification scope:** selected automated regressions, widget tests and static checks do not establish that every existing repository test passes. Production-engine concurrency, deployed-domain session behavior, browser usability and physical-device acceptance still need staging verification.

Consequently, the entire final acceptance checklist in the request must remain open for the unimplemented refund/adjustment and extended custody/delegation/prepaid workflows. The tested core is available for review and staging configuration; it is not represented as the full production-ready specification.

### Web repository

- `app/Http/Controllers/Admin/CodRemittanceController.php`
- `app/Http/Controllers/Admin/MarketplaceFinanceController.php`
- `app/Http/Controllers/Api/MobileLinehaulController.php`
- `app/Http/Controllers/Api/MobileRiderCodController.php`
- `app/Http/Controllers/Api/MobileRiderDeliveryController.php`
- `app/Http/Controllers/Api/MobileRiderTransitController.php`
- `app/Http/Controllers/HybridFinanceController.php`
- `app/Http/Controllers/Logistics/CodSettlementController.php`
- `app/Http/Controllers/Logistics/EarningsController.php`
- `app/Http/Controllers/Rider/CodSettlementController.php`
- `app/Http/Controllers/Rider/DeliveryController.php`
- `app/Http/Controllers/Seller/ReportController.php`
- `app/Models/Finance/MarketplaceRateSetting.php`
- `app/Models/Finance/OrderFinancial.php`
- `app/Services/BuyerCheckoutService.php`
- `app/Services/CodRemittanceService.php`
- `app/Services/FinanceMoney.php`
- `app/Services/FinancialAudit.php`
- `app/Services/LinehaulService.php`
- `app/Services/PartnerCompensationService.php`
- `app/Services/PersonnelFinanceReadService.php`
- `app/Services/PickupRiderAccrualService.php`
- `app/Services/PlatformFinancialPolicyService.php`
- `app/Services/ShopHopFinance.php`
- `database/migrations/2026_11_09_000001_add_hybrid_financial_workflows.php`
- `database/migrations/2026_11_09_000002_add_financial_policy_history.php`
- `docs/financial-system-implementation-2026-11-09.md`
- `resources/views/admin/commission.blade.php`
- `resources/views/branch/layout/app.blade.php`
- `resources/views/finance/hybrid.blade.php`
- `resources/views/logistics/earnings.blade.php`
- `resources/views/logistics/settlements/index.blade.php`
- `resources/views/rider/settlements.blade.php`
- `resources/views/seller/reports.blade.php`
- `routes/api.php`
- `routes/logistics.php`
- `routes/web.php`
- `tests/Feature/HybridFinancialWorkflowTest.php`
- `tests/Feature/MobileRoleWorkspaceContractTest.php`
- `tests/Feature/SellerPromotionsWorkspaceRegressionTest.php`
- `tests/Feature/SellerVoucherJourneyTest.php`
- `tests/Unit/ShopHopFinanceMathTest.php`

### Mobile repository

- `lib/features/linehaul/presentation/screens/linehaul_driver_home_screen.dart`
- `lib/features/rider/data/models/rider_cod_overview.dart`
- `lib/features/rider/data/services/rider_delivery_service.dart`
- `lib/features/rider/presentation/screens/rider_cod_remittance_screen.dart`
- `lib/features/rider/presentation/screens/rider_transit_earnings_screen.dart`
- `test/rider_financial_workflow_test.dart`

## Final validation results

- Selected Laravel regression suite: **67 tests passed, 580 assertions, zero failures/errors**. This includes financial workflows, rendered Admin/Seller reports, COD regressions, Branch routes, money math, mobile role contracts, checkout eligibility, Rider batches, Seller voucher/promotion journeys and web/mobile marketplace parity.
- Flutter financial and Rider management tests: **14 passed**, including the interactive facility-selection/full-COD handover form.
- Flutter static analysis for the six changed financial files/tests: **no issues found**.
- Laravel Pint: **passed** after formatting the modified/new PHP files.
- Git diff whitespace checks: **clean** in both repositories.
- Intermediate failures were corrected: required facility fixture address fields; decimal snapshot casting; stale operational token abilities; obsolete partial schemas; missing voucher kind; malformed Blade markup; stale report commission estimation.
- Not executed: the entire repository-wide test suite, production migrations, live transfers, staging/browser/device acceptance and release builds.
