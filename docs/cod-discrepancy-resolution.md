# COD discrepancy resolution

Implemented in the Laravel web project and consumed by the Flutter Rider COD views.

## Setup

Deploy the Laravel code with its additive migration:
`php artisan migrate`

Migration: `2026_11_12_000001_create_cod_discrepancy_resolution.php`.
It creates cases, resolution proposals and a signed cash adjustment ledger.
Existing collections with status "discrepancy" receive an open investigation case.
It does not change past cash counts, custody movements, settlement amounts or payroll.
Financial mutations and the Rider COD API fail safely until the new tables are available.

## Workflow

1. Branch or the owning Logistics account verifies actual cash per collection.
2. A shortage, overage or collection mismatch opens a case automatically.
3. The assigned Branch, owning Logistics account and ShopHop Admin can append investigation notes and evidence references. Receipts remain immutable.
4. For a shortage, Rider submits supplemental cash through the existing handover workflow. Branch verifies it. The original deficient receipt remains unchanged.
5. When expected, collected and net received cash agree, Logistics proposes closure with a reason and evidence reference.
6. ShopHop Admin independently approves or rejects the proposal. An approved balanced closure resolves the case without a cash adjustment.
7. For an overage, Logistics first identifies the source and proposes returning the exact verified excess to a named recipient. Admin approval authorizes the plan; it does not post a cash return.
8. After actually returning the cash, Logistics records the exact approved amount, unique transaction reference, recipient acknowledgment reference and explicit actual-return confirmation.
9. A negative cash adjustment is appended. The collection's running net received amount is updated; original remittance items, batch differences and custody movements remain unchanged.
10. Logistics-to-ShopHop remittance, Admin receipt confirmation and Seller/Logistics settlement retain their separate approvals.

Case states: open -> pending_approval -> approved (return pending) -> resolved.
Balanced closure moves from pending_approval directly to resolved.
Rejected proposals are preserved; the case returns to open and a new proposal can be submitted.

## Controls

- Cases, proposals, notes and adjustments are scoped to the authenticated company or assigned receiving Branch. Admin provides platform oversight.
- Only the owning Logistics account can propose resolutions or record actual returns. Only approved ShopHop Admin can review them. A proposer cannot approve their own proposal even after a role change.
- Unresolved cases block company remittance, Admin receipt, preparation, distribution and final Seller/Logistics payment. Balanced cash alone does not remove a case hold.
- Pending supplemental handovers must be verified first. Approval checks that cash still matches the proposal snapshot.
- Cash returns are exact, cannot be posted before approval, and are unique per resolution and company transaction reference. Repeating the same confirmed return does not post it again.
- Reasons and evidence references are required. Notes and approval actions append actor, time and evidence to financial_audit_events.
- No Rider earnings deduction, salary deduction, shortage writeoff, automatic bank transfer or automatic recognition of overages as revenue is supported.
- If recorded Buyer collection differs from expected Order COD, the case remains under investigation; the resolution module does not overwrite that original collection or waive the difference.
- Generic order holds and disputes remain independent; resolving a cash case does not clear them.
- Historical deficient batches remain visible after resolution. Historical differences without a tracked case show an unknown review status rather than an invented approval.

## Screens

- Admin: Finance -> Review COD discrepancy cases.
- Logistics: COD Settlements -> Review COD discrepancies.
- Branch: COD Verification -> Review COD discrepancies and add evidence.
- Rider mobile: cash collection cards show the case reference and investigation, Admin review, pending return or resolved state. Sensitive recipient and investigation evidence are not returned to the Rider API.

Evidence is stored as a receipt/document reference. Verify the actual external evidence before approval; the system does not validate external transfers automatically.

## Validation

Targeted Laravel tests exercise case creation, supplemental cash, independent approval, immutable receipt history, return replay protection, role isolation, payroll independence, migration indexing and mobile API privacy.
Flutter tests cover pending case precedence over a balanced cash receipt, case progress visibility and preserved resolved receipt history.
