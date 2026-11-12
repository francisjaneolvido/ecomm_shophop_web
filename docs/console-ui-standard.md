# ShopHop console UI standard

The canonical styles are in public/css/shophop-console-components.css and loaded through
resources/views/partials/console-assets.blade.php in Admin, Seller, Logistics company,
Province Hub and Station Branch layouts.

## Components
- Cards: white/theme surface, 1px border, 8px corners, 12-16px spacing.
- Actions: navy primary, outlined secondary, red destructive; 6px corners, 36px desktop controls,
  44px controls on touch devices. Teal identifies selected navigation and positive states.
- Lists: a single panel containing title, optional queue tabs, filters, table, and pagination.
  Currency columns use right alignment and tabular figures.
- Tabs: underline selection. Route tabs use aria-current; client tabs use aria-selected,
  linked tab panels, arrow/Home/End navigation, and a preserved query parameter.
- Details: native dialogs for one receipt/action; expandable sections for longer case evidence.
- Use shared sh-btn/sh-btn-soft/sh-btn-danger, ops-panel, ops-table, ops-tabs classes.
  sh-btn-primary, ops-button and admin-btn variants are compatibility aliases.

## Logistics navigation
Company: Overview; Operations; Network & People; Finance; Insights; Account.
Province Hub managers: Overview; Network; Operations; Account.
Station Branch accounts: Overview; Operations; People; Finance.
Staff: only links authorized for that staff role/facility. Account screens use the same shell.
The active link remains visible in the sidebar scroller.

## Finance
COD Settlements shows persisted cash facts and identifies unverified cash as Pending.
Queue cards distinguish recorded collected COD, verified cash, and expected COD in open cases.
Open cases are visible in the table and have their own queue.
Branch handovers and company handovers remain distinct; an absent receiving Branch is never inferred.
Only authorized actors can verify receipts, record remittances, or confirm personnel payouts.
Existing financial actions and server checks remain authoritative.

## Interaction ownership
public/js/shophop-operations-table.js owns tabs, dialogs, menus, direct receipt links and
local table search. Layouts load it once. logistics-directory-v1.js only fills edit forms.
Avoid adding another tab/menu controller or page-specific color definitions.

## Admin and Seller

Admin and Seller share the Logistics sidebar palette and canonical console components. Admin navigation separates accounts/catalog, finance, support, reports, and system settings. Seller navigation follows orders, catalog, finance, support, and account work.

Use underline tabs for workflow queues or panels, quiet bordered chips for secondary status filters, metric cards for totals, and a single table panel for record management. Finance tabs support direct section hashes and keyboard navigation. Seller earnings status filters retain seller scope and pagination. Approval, cash receipt, payout and inventory mutations keep their existing forms and permissions.

Admin COD receipt queues support All records, To verify, With logistics, and Received, with reference/order/partner search. These GET filters preserve the existing reconciled-record scope and do not confirm cash receipt.

UI review: 29 Admin/Seller pages and detail/filter variants rendered locally. Representative pages were inspected at 1280px and 390px, in light and dark themes, including Finance hash links, Inventory tabs, and Order preview dialogs. PHP/JavaScript syntax and Blade compilation were checked. No automated test suite or financial action was run during this UI pass.

## Record detail modals

All five console shells include the shared native Details dialog. Add `data-console-details` to a same-origin record link and mark the authorized record's outer section with `data-console-detail-source`. The existing authenticated page and its forms render within the modal with navigation chrome hidden. Keep the original href for direct links and browser open-in-new-tab behavior. Parent rows refresh after a form navigation is completed and the modal is closed.

For an existing inline record form, give it a stable ID, `data-console-inline-detail`, and `data-console-detail-title`; point a button's `data-console-details` at that ID. The live form is moved and restored, so input state, listeners and unique IDs are preserved. `data-console-auto-open` restores selected riders and validation errors. Read-only templates may keep the existing `data-ops-template` native modal.

Details support Escape, explicit close controls, return focus, contained scrolling, light/dark themes and narrow screens. Approval, activation, dispute and order actions retain their original server routes and authorization.

## Current brand appearance

The current console palette is navy/pink/cream with one light appearance. See [brand-palette.md](brand-palette.md). Earlier dark-mode review notes describe the previous implementation; the controls and conditional styles have been removed.
