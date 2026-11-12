# Rider registration and Branch activation

## Where to open

- Branch account: Logistics portal > Riders. The default queue is **For activation**.
- Branch dashboard: **Review rider activation** opens the same queue.
- Main Hub/company account: Riders > **Applications**, **Awaiting Branch activation**, or the other lifecycle queues.
- Branch staff managers use the same Riders menu through their staff workspace.

Queue badges count riders within the signed-in company and, for Branch accounts, the assigned Branch. Search filters narrow the displayed directory.

## Approval and activation

1. The rider registers with verified email, required documents, a preferred Branch, and preferred barangays.
2. Main Hub reviews the application and confirms the assigned Branch.
3. If Main Hub changes the preferred Branch, the rider accepts the new Branch and coverage in the mobile app.
4. The assigned Branch selects **Review & activate**, checks the profile and activation requirements, reviews official barangays, confirms authorization, and submits **Confirm coverage & activate**.
5. The rider refreshes Rider status and selects **Open rider workspace** when activated.

The form preselects saved accepted coverage or preferred coverage for review. Branch coverage must support both pickup and delivery. Counts exclude the selected rider when reviewing Branch activation. Full barangays require a recorded individual Main Hub override. The server rechecks eligibility, capacity, parcel custody, and outstanding cash when activation is submitted.

Inactive riders are under **Inactive** for Branch reactivation. Main Hub must release suspended riders before Branch reactivation. An active rider no longer appears in **For activation**; use **Active** or **All riders**.

## Mobile status

The status page shows approval progress, the next actor, official-coverage guidance, and the next action for document requests, Branch acceptance, activation, inactivity, and suspension. It refreshes while visible; workspace access still requires backend authorization.

## Local refresh

Refresh the web page after these changes. Restart the running Flutter app to load the updated mobile screens. This UI update adds no migration.
