# Phase 4A: Company License, application to consolidated review

- Status: Draft (waiting for approval)
- Branch: `feature/phase-4a-license-review` (from `feature/phase-3-workflow`; rebase on `main` once Phase 3 is merged)
- Pull requests: one per row of 09 Phase 4A (D-115), in the order of the Steps below

## 1. Scope

### In
| # | 09 Phase 4A row | Transitions |
|---|---|---|
| A | Company: new application (category), select premises and technical person, declaration with password confirmation, submit, see status, respond to returns, resubmit | T1, T25 |
| B | Edit lock active (Draft/Returned only) | D-073 |
| C | Admin work queues: My work, by stage, waiting on company, flags | D-105 |
| D | Admin application file: snapshot, versions with differences, history timeline | D-042 |
| E | Registrar: acknowledge, admin review, technical review, review checklists, returns, Technical Reviewer assignment | T2–T8 |
| F | Inspection: scheduling, rescheduling, mobile inspection form (checklist incl. SOPs, photos, video, GPS), visit, signed report | T9–T11 |
| G | Consolidated review and re-inspection | T12–T14 |
| H | Emails for these events (07 §3.3) | — |

### Out
- Director, payment, DG decision and license issue (Phase 4B). An application reaches `DIRECTOR_REVIEW` and stops there. T12 always goes to `DIRECTOR_REVIEW`, because `PaymentCleared` stays a Phase 4B placeholder and no payment can exist yet.
- **Renewal and amendment.** Their guards need licenses (Phase 4B). The application form offers **New** only, and the backend refuses the other types: the `PreviousLicenseActive` / `RenewalWindowOpen` placeholders refuse them (D-188).
- Operator License and Import Permit (Phases 6–7).
- Delegation management screens (Phase 4B, with the DG).
- Offline inspection. The form is mobile-first and needs a connection (H6 asks for no native app).

## 2. Design references
- **Decisions:** D-014, D-024, D-025, D-042, D-043, D-044, D-045, D-073, D-077, D-085, D-086, D-099, D-102, D-105, D-125 to D-130, D-135, D-136, D-188 to D-195.
- **Design sections:**
  - 02 §4.6–§4.8, §5, §6.3–§6.5;
  - 04 §5, §9, §10, §15, §16;
  - 05 §5.1, §6;
  - 06 (uploads);
  - 07 §2, §3.3, §4;
  - 09 Phase 4A.

## 3. Steps

### Step 1: Notifications for the workflow (row H, first because every transition sends email)
- **`Notify:<CODE>` becomes a real effect.**
  - It records what to send, and `SendNotification` queues it after commit (D-129).
  - Recipients follow 07 §2 and D-127: the company's login email; for a role, every active holder of the role; when an assignment exists, only the assignee. Nobody gets an email for their own action.
- **New codes** (07 §3.3): `APPLICATION_SUBMITTED`, `WORK_ITEM_READY`, `APPLICATION_ACKNOWLEDGED`, `APPLICATION_RETURNED`, `APPLICATION_RESUBMITTED`, `REVIEWER_ASSIGNED`, `TECHNICAL_REVIEW_DONE`, `INSPECTION_SCHEDULED`, `INSPECTION_RESCHEDULED`.
  - Each has a Mailable, HTML and text templates, and keys in `lang/en.json` and `lang/so.json`.
  - `APPLICATION_RETURNED` lists each deficient or unclear item with its comment, or the DG's comment (07 §3.3).
- **`WORK_ITEM_READY`** is sent whenever the application enters a staff stage (Proposed D-198).
- **The definition's `Notify` names** are replaced by the 07 codes (question Q2).

### Step 2: Company application (row A)
- **Portal pages:**
  - My applications: list with the company status label (D-045).
  - New application: choose category, type New. Creating it sets `open_key`, so a second draft of the same category is refused (D-044, D-193).
  - Draft page: select premises (at least one) and one technical person. Each change is saved immediately (H6).
  - Submit page: shows what `availableActions()` reports as blocking (D-191). The declaration has name pre-filled, designation, "I confirm" tick and password confirmation (05 §5.1).
  - Application page: status, versions submitted, company-visible history, and on `RETURNED` the items to fix with comments, plus a response note and Resubmit.
- **Controllers stay thin.** Policies check ownership (other company's application gives 404) and that the company is in the company area. The engine performs SUBMIT / RESUBMIT.
- **New guard `PremisesSelected`:** at least one of the company's own premises (Proposed D-197).

### Step 3: Edit lock (row B)
- Bind the real `SubmissionState` (D-073, 02 §6.4).
  - The profile is editable while there is no Company License application in process, or while one is in `DRAFT` or `RETURNED`.
  - Otherwise only renewed certificates can be uploaded.
- An Import Permit draft never unlocks it (02 §6.4 rule 2). There are no Import Permits yet, but the rule is coded and tested.

### Step 4: Admin application file (row D)
- **`/admin/applications/{application}`**, permission `view applications`:
  - the current version's snapshot, shown as the reviewer's source (02 §6.3: reviewers work only from the snapshot);
  - earlier versions with their differences, computed by comparing snapshots (Proposed D-199);
  - review items per version;
  - inspections, and the full WorkflowAction timeline;
  - the action buttons from `availableActions()`, with blocked reasons.
- **Every file opened from a snapshot is audited**, as on `/admin/companies`. National IDs are masked.

### Step 5: Work queues (row C)
- **Queues (04 §15, D-105):**
  - My work: stages whose role I hold, plus my open assignments and inspections;
  - By stage: count and age, for Registrar, Director and DG;
  - Waiting on company: `RETURNED`, with days waiting;
  - Flags: amendment-required companies in Phase 4A; the other flags come with their data in Phase 4B and Phase 6.
- **Paginated and mobile-first.**

### Step 6: Registrar actions (row E, T2–T8)
- Acknowledge.
- Record review items, with the stage's items from the definition (Compliant / Deficient / Clarification required; comment unless compliant). This uses `RecordReviewItems`, so it depends on D-195.
- Complete admin review; return to the company with a comment.
- Assign a Technical Reviewer. The Technical Reviewer records their items and completes the review.
- Complete technical review and schedule the inspection (Step 7).

### Step 7: Inspection (row F, T7, T9–T11)
- **Tables `inspections`, `inspection_items`, `inspection_media`** (02 §4.8). One inspection per selected premises (D-077), each with its own Inspector and planned date (05 §6: Inspector holds the role; date today or later).
- **Effects built now** (replacing the D-188 placeholders): `CreateInspections`, `UpdateInspectionDate`, `SetConductedAt`, `SignInspection`, `CancelOpenInspections`.
- **Guards built now:** `InspectionReportComplete`, `InspectionsSigned`.
- **The assignment check for Inspector actions** uses the inspection named in the payload (04 §10 rule 3).
- **Mobile inspection form:**
  - every checklist item (02 §4.8 list, kept as configuration in `config/inspection.php`; it replaces the Phase 2 `ChecklistItem` enum) as Compliant / Non-compliant / N/A with a note;
  - observed facts (required), recommendations, and deficiencies (required if any item is Non-compliant);
  - GPS from the device, or a reason if it isn't available (D-136);
  - photos: at least 1 per Non-compliant item, up to 5 per item, compressed in the browser;
  - optional MP4 video up to 50 MB (D-135, Proposed D-200).
- **The report is saved while being filled in.** It is locked when signed: signing is SUBMIT_INSPECTION_REPORT with password confirmation.
- **Several premises with different Inspectors:** see question Q1.

### Step 8: Consolidated review and re-inspection (row G, T12–T14)
- Consolidated review items, then complete it (to `DIRECTOR_REVIEW`).
- Return to the company.
- Re-inspection (T14): a new round of inspections with a comment. The earlier signed reports stay.

### Step 9: Docs and checks
- Update CLAUDE.md; run `composer run check`; one pull request per row.

## 4. Files (main ones)

| Area | New or changed |
|---|---|
| Notifications | `NotificationCode` (+9 codes), `Mail/*`, `resources/views/mail/*`, `Workflow/Effects/Notify`, recipient resolver |
| Applications | `Actions/{CreateDraft, SelectPremises, SelectTechnicalPerson}`, `Support/VersionDiff`, `Queries/WorkQueues` |
| Inspection | models `Inspection`, `InspectionItem`, `InspectionMedia` (+ factories), `Actions/{SaveInspectionReport, AddInspectionMedia}`, effects and guards above, `config/inspection.php` |
| CompanyRegistry | real `SubmissionState` binding |
| Documents | MP4 support in `SafeUploads` for inspection media only |
| Workflow definition | `config/workflows/company_license_v1.php` (Q2) |
| Controllers / routes | `Portal/ApplicationController` (+ submit, resubmit), `Admin/ApplicationController`, `Admin/WorkQueueController`, `Admin/ReviewItemController`, `Admin/InspectionController` |
| Pages | `portal/applications/{index,create,show,submit}`, `admin/applications/{index,show}`, `admin/queues`, `admin/inspections/{show,report}` |
| Tests | `tests/Feature/Applications/*`, `tests/Feature/Inspection/*`, `tests/Feature/Workflow/CompanyLicenseTransitionsTest.php` |

## 5. Migrations
- `inspections`, `inspection_items`, `inspection_media` (02 §4.8). Signed reports and their items and media are never changed or deleted.
- No change to existing tables.

## 6. Tests
- **Every transition T1–T14 and T25** on the real Company License definition, allowed and refused (09 "Done when", 04 §16 items 1–3):
  - wrong role, wrong portal, wrong company, missing assignment, missing password confirmation;
  - each guard's failure message.
- **End to end:** Draft to consolidated review, including a return and resubmission, with the right emails to the right people, and none for the actor (07 §3.3, D-127).
- **Edit lock:** each 02 §6.4 row, including an Import Permit draft not unlocking the profile.
- **Inspection:**
  - one inspection per premises;
  - report rules of 05 §6 (deficiencies, photos per non-compliant item, GPS or reason, video type and size);
  - a signed report can't be changed;
  - another Inspector can't open or sign it.
- **Queues and application file:**
  - each queue shows only what it should;
  - a company never reaches admin pages, and staff never reach portal pages;
  - version differences are correct.
- **Phone layout:** the inspection form works at 360 px, checked in the browser.

## 7. Risks
1. **Inspection video on a weak connection.** A 50 MB upload may fail. The form uploads each file separately and keeps the text when a file fails.
2. **Photo GPS.** The device position comes from the browser's location permission. If the Inspector refuses it, a reason is required (D-136).
3. **Phase 3 isn't merged yet.** This branch builds on it, so Phase 3 should be merged first.

## 8. Questions for you (blocking)

**Q1. Several premises, several Inspectors.**
- The problem:
  - The application has one status, but each premises has its own inspection and Inspector (D-077, 04 assumption).
  - After the first Inspector records a visit (T10), the application is at `INSPECTION_REPORT`.
  - From there, the matrix has no transition for a second Inspector's visit, or for rescheduling an unvisited inspection.
- My proposal (D-196):
  - T10 moves the application on the first visit.
  - Later visits are recorded on their own inspection: an audited Inspection action, not a status change.
  - The Registrar can still reschedule an unvisited inspection while the application is at `INSPECTION_REPORT`, through the same Inspection action, with the same email.
  - T11 keeps the stage until the last report of the round is signed. `InspectionsSigned` then moves it to `CONSOLIDATED_REVIEW`; this is already in the definition.
  - Another way would be a matrix change in 04, which the design Project would have to make.

**Q2. Correcting the Company License definition.**
- The problem:
  - `company_license_v1.php` uses `Notify` names (for example `Notify:SUBMITTED`) that differ from the 07 codes (`APPLICATION_SUBMITTED`, …).
  - D-189 (Confirmed) says a loaded version never changes.
  - v1 has only been loaded on development and test databases, and no application uses it.
- Options:
  - (a) Amend D-189 so that a loaded version can be replaced while no application uses it, then fix v1. **My recommendation.**
  - (b) Keep v1 as it is and add `company_license_v2.php`.

**Q3. D-195 (Proposed in Phase 3).** Can a reviewer correct a review-item result before completing the stage? The review screens in Step 6 depend on it.

## 9. Proposed decisions (Source: Claude Code)

| ID | Proposal | Why |
|---|---|---|
| D-196 | Multi-premises inspections as in Q1. | The matrix covers one inspection only. |
| D-197 | New guard `PremisesSelected` on SUBMIT and RESUBMIT: at least one premises, all the company's own. | 05 §5.1 requires at least one premises. The removed `ChecklistPhotos` guard (D-192) was the only check that implied it. |
| D-198 | `WORK_ITEM_READY` is sent by the `Notify` effect for each transition whose target is a staff stage: to the stage role, or to the assignee when there is one. It is listed in the definition like the other codes. | 07 §3.3 says "enters any staff stage" without listing every transition. |
| D-199 | Version differences are computed when the page opens, by comparing the two snapshots field by field; nothing extra is stored. | Snapshots are immutable and complete (02 §6.3). |
| D-200 | Inspection videos: MP4 only, checked from the file content, up to 50 MB, inspection media only; stored as uploaded, with no conversion. Company uploads keep the 5 MB limit. | D-135 sets the type and size, not how the file is checked. |
| D-201 | **What the company sees:** the status changes (company labels, D-045); comments of returns and DG deferrals; review items that are not compliant, with their comments. It never sees internal comments (Director return, re-inspection reason), staff names, or inspection reports beyond the planned date. | 02 §4.7 says "company-visible actions and comments only" without listing them; 02 §4.8 gives the company only the planned date. |
