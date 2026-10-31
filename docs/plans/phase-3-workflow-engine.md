# Plan: Phase 3 — Workflow engine core

- Status: Draft, waiting for approval
- Branch: `feature/phase-3-workflow` (09 §3), one pull request
- Date: 2026-10-31
- Depends on: Phases 0–2 (staff roles, company accounts, company profile, notifications)

## 1. Scope

**Goal (09 §3, Phase 3):** the generic engine works, and the Company License definition loads.

**In scope (the Phase 3 table, all rows):**

| # | Row | Decisions |
|---|---|---|
| A | Tables `workflow_templates`, `workflow_stages`, `workflow_transitions`, `workflow_actions`, `review_items`, `stage_assignments` | D-071 |
| B | `config/workflows/company_license_v1.php` (04 §5) and `php artisan workflows:load`, with definition validation | D-097, D-098, D-099, D-102 |
| C | `WorkflowEngine::perform()`: row lock, `expected_status`, ordered checks, one transaction, emails after commit | D-096 |
| D | `availableActions()`; guard and effect base classes; delegation and segregation checks | D-027, D-086 |
| E | Tables `applications` and `application_versions` (and the typed links `application_premises`, `application_persons`); the snapshot builder with hash | D-042, D-069, D-070 |

Also included, because the rows depend on them:
- **The `delegations` table** (02 §4.1). The delegation check (row D) needs it. The DG's delegation screens come later (Phase 4B).
- **Recording review items** as a domain action, `RecordReviewItems` (04 §5.3). The review guards read them; the review screens come in Phase 4.
- **D-192 clean-up of Phase 2** (Step 0): the company facility checklist and its photos are removed, because the checklist is now the Inspector's job.
- **D-193 in the backend:** an unsubmitted draft holds its `open_key` from creation, so it blocks a second application of the same service and category. The domain action `DiscardStaleDraft` lets only the System Administrator discard (soft delete) a never-submitted draft untouched for 30 days. Its screen comes with the other application screens in Phase 4.

**Out of scope (09 says later):**
- All screens: no application forms, work queues or review pages (Phase 4).
- Licenses, inspections, payments, fee schedules, products and Import Permit details. Their tables, guards and effects come with their phases (see Step 5).
- The interim Operator License and Import Permit definitions (04 §6–§7).
- The workflow email codes of 07 §3.3 (Phase 4, with the screens that send them).

## 2. Design references
- 04-workflow-engine (all): §2 concepts, §3 actors, §4 how a transition runs, §5 Company License, §8–§10 checks, §11–§12 catalogues, §13 definition format, §16 tests
- 02-data-model §4.1 (Delegation), §4.6 (Application, ApplicationVersion, ApplicationPremises, ApplicationPerson), §4.7 (workflow tables), §5 (status and action lists), §6.3 (snapshots), §6.5 (open key), §7 (references)
- 09 §3 Phase 3 (table, "Done when", prompt)
- Decisions D-022, D-024, D-027, D-042, D-043, D-044, D-045, D-069, D-070, D-071, D-072, D-075, D-085, D-086, D-096, D-097, D-098, D-099, D-101, D-102, D-103, D-106, D-187, D-192, D-193
- Note: design documents 02 (§4.4 checklist tables, §6.1, §6.3), 04 (§5 discard note, §5.2 T1, §6, §11) and 05 (§4.3, §5.1–§5.2) still describe the company checklist and company-discarded drafts. D-192 and D-193 take precedence; the documents need updating to match.

## 3. How a transition will run (04 §4, D-096)

```
WorkflowEngine::perform(Application, WorkflowActionCode, WorkflowActor, expectedStatus, payload)
  BEGIN
  1. lock the application row (SELECT … FOR UPDATE)
  2. status still = expectedStatus?          else AlreadyProcessed ("already processed", HTTP 409)
  3. find the transition (current stage + action) in the application's template version
  4. actor checks, in order (04 §10): realm → role → assignment → delegation → segregation
  5. password confirmed within the window, if the transition requires it (D-085)
  6. payload: comment (when required), other required fields
  7. guards, in definition order            any failure → TransitionRefused with every reason
  8. effects, in definition order
  9. status (and resume_stage, open_key) updated; the conditional target resolved
 10. WorkflowAction row + audit entry
  COMMIT                                    emails queued by effects are sent only now
```

Any exception before COMMIT rolls back the status, the history and every effect (04 §16 item 5).

## 4. Steps

### Step 0: Remove the company facility checklist (D-192)
- **Company Portal:** the premises page keeps label, type, address, GPS and waste disposal method. The checklist section, the photo upload and the three checklist routes are removed, along with `PremisesController::updateItem/storePhoto/destroyPhoto`, the `FileController::photo` download and their requests.
- **CompanyRegistry:** `AddChecklistPhoto` is removed. `SavePremises` no longer creates checklist rows. The `ChecklistItem` enum moves to the inspection module's use (its 9 codes are the inspection items, 04 §6).
- **Admin company file:** the per-premises checklist and photo links are removed; `admin.files.photo` is removed.
- **Database:** a new migration drops `facility_checklist_photos` and `facility_checklist_items`. Merged migrations are never edited, and no production data exists yet. The stored photo files stay in the file store (StoredFile is immutable).
- **Tests:** the checklist and photo tests are removed (approving this plan approves removing them). Premises tests stay; new tests check the checklist routes no longer exist.
- **Docs:** CLAUDE.md is updated.

### Step 1: Enums and tables

**Enums** (`app/Domain/Workflow/Enums`, `app/Domain/Applications/Enums`), with the exact names from 02 §5:
- `ApplicationStatus` (15 statuses), `WorkflowActionCode` (21 actions), `Service`, `LicenseCategory`, `ApplicationType`, `ReviewResult`.
- `ActorRole`: `COMPANY`, the staff role codes, `SYSTEM`. It maps to the spatie role names through `StaffRole::fromCode()`.

**Migrations**, as in 02:
- `workflow_templates`, `workflow_stages`, `workflow_transitions`.
  - Transitions also store `guards`, `effects` and the conditional `to` as JSON, plus `requires_password` (04 §13).
- `delegations`.
- `applications`.
  - `previous_license_id` has no foreign key until `licenses` exists (Phase 4B), the same approach as `company_users.company_id` in Phase 1.
  - Unique key on (`company_id`, `open_key`) (02 §6.5).
- `application_versions`.
- `application_premises`, `application_persons`.
- `workflow_actions`, `review_items`, `stage_assignments`.

**Models** with factories:
- `WorkflowTemplate`, `WorkflowStage`, `WorkflowTransition`, `Delegation`, `Application`, `ApplicationVersion`, `WorkflowAction`, `ReviewItem`, `StageAssignment`.
- `ApplicationVersion` and `WorkflowAction` refuse updates and deletes, the same way as `AuditActivity` (02: "never updated").
- `Application` uses `BelongsToCompany`, so another company's application gives 404 on the Company Portal.

### Step 2: Definitions and `workflows:load` (row B)
- **`config/workflows/company_license_v1.php`:** every stage of 04 §5.1 and every transition T1–T25 of §5.2, with guards, effects, the conditional target of T12, password flags, comment flags and the review items of §5.3. T1 and T25 do not have `ChecklistPhotos` (D-192). `SetOpenKey` happens when the draft is created, not at T1 (D-193).
- **`DefinitionValidator`** checks (04 §13):
  - every stage is reachable from `DRAFT`;
  - every non-final stage can reach a final stage;
  - every guard and effect name exists in the registry;
  - every actor is a known role;
  - every `from` and `to` is a declared stage;
  - no duplicate (from, action);
  - every review-item stage exists.
- **`php artisan workflows:load`:**
  - validates every file in `config/workflows/` and loads it into the tables in one transaction;
  - a version already loaded with identical content is skipped;
  - a version already loaded with **different** content is refused (Proposed D-189).

### Step 3: The engine (row C)
- **`WorkflowActor`:** the user (User or CompanyUser), whether the password was confirmed within the window, and the IP address.
- **`WorkflowEngine::perform()`:** exactly the order in §3 above.
  - Exceptions: `AlreadyProcessed` and `TransitionRefused`. `TransitionRefused` carries every reason as a translatable message.
  - Conditional targets are resolved by evaluating their guard (04 §13).
- **WorkflowAction rows** store: version, from and to status, actor (morph), the role used, `on_behalf_of_id` for a delegate (D-027), the comment and the IP. Each one also writes an audit entry (D-055).
- **Email:** effects queue email through `SendNotification`, which already sends only after commit (D-129).

### Step 4: Actor checks and `availableActions()` (row D)
Each check is its own small class, so it can be tested alone (04 §10):

| Check | Rule |
|---|---|
| **Realm** | `COMPANY` transitions need the application's own company account; staff transitions need a staff user |
| **Role** | The staff user holds the transition's role; the System Administrator never has a workflow role (D-086) |
| **Assignment** | `TECHNICAL_REVIEWER` actions need an open StageAssignment for this user. Inspector assignment comes with inspections (Phase 4A); until then its check refuses (D-188) |
| **Delegation** | A non-DG user may perform `DIRECTOR_GENERAL` actions only inside an active, unrevoked Delegation; the action records `on_behalf_of_id` (D-027) |
| **Segregation** | 04 §10 rule 5, from this application's WorkflowAction and ReviewItem history (D-086) |

**`availableActions(Application, user)`:**
- Returns the transitions the user passes the actor checks for.
- For each one, also the guard results with reasons, so screens can show a disabled button with the reason (Proposed D-191).
- It never changes anything.

### Step 5: Guards and effects (row D)
Base contracts:
- `Guard::check(Context): GuardResult` (pass, or fail with reasons);
- `Effect::apply(Context): void`.

A registry maps names to classes; parameters use the `Name:param` form of 04 §13.

**Built in Phase 3** (their data exists now):

| Guards | Effects |
|---|---|
| ProfileComplete, CertificatesValid, RequiredDocuments, TechnicalPersonSelected, NoOtherOpenApplication, ReviewItemsAllCompliant, ReviewItemsHaveDeficiency, ReviewItemsRecorded, NoOpenAssignment, Segregation, ActiveDelegation | GenerateReference (CL/OL/IP-YYYY-NNNNN from `number_sequences`, D-075), CreateVersionSnapshot, SetOpenKey, ClearOpenKey, SetResumeStage, ClearResumeStage, CreateAssignment, EndAssignment, SetDecidedAt |

**Named now, built in their phase** (Proposed D-188): these exist as classes, so definitions validate, but they **refuse** (guards) or **throw** (effects) if they ever run, so nothing can slip through unbuilt:
- InspectionsSigned and the inspection effects (Phase 4A).
- FeeScheduleExists, AmountMatchesLetter, ReceiptNotUsed, PaymentCleared and the payment effects (Phase 4B).
- IssueLicense, SupersedePreviousLicense, ClearAmendmentFlag, PreviousLicenseActive, RenewalWindowOpen, SetLateRenewalFlag (Phase 4B).
- SignerHasSignature (Phase 4B).
- ImportationLicenseActive, ProductVerified, IngredientNotBanned, StoragePremisesSelected, FlagRestrictedIngredient (Import Permit phase).
- InspectionReportComplete (Phase 4A).
- `Notify(code)` (Phase 4, with the 07 §3.3 email codes).

### Step 6: Snapshots (row E, D-042, D-069)
- **`SnapshotBuilder::build(Application): array`.** `schema_version: 1`, plus:
  - the company with its address;
  - the Owner/MD;
  - the selected technical person with qualifications and their current documents;
  - the selected premises (label, type, address, GPS, waste disposal method; no checklist, D-192);
  - the company's current documents (type, number, dates, `stored_file_id`);
  - the application fields (service, category, type).

  Files are referenced by `stored_file_id`, never copied (D-069). National IDs are kept encrypted as stored, never in plain text.
- **`SnapshotHasher`.** The hash is SHA-256 of canonical JSON (Proposed D-190). `verify(version)` recomputes it and compares.
- **`CreateVersionSnapshot`** writes the next ApplicationVersion with the declaration fields. These come from the payload; their form is Phase 4.

### Step 7: Docs, checks, push
- CLAUDE.md: the Workflow and Applications modules, `workflows:load` in the install commands.
- Run `composer run check` (Larastan in CI); push the branch; open one pull request.

## 5. Files (main ones)

| Area | New |
|---|---|
| Migrations | the 13 tables of Step 1 |
| `app/Domain/Workflow` | `Enums/*`, `Engine/WorkflowEngine`, `Engine/WorkflowActor`, `Engine/TransitionContext`, `Exceptions/{AlreadyProcessed, TransitionRefused}`, `Checks/{Realm, Role, Assignment, Delegation, Segregation, PasswordConfirmed}`, `Guards/*`, `Effects/*`, `Registry/{GuardRegistry, EffectRegistry}`, `Definitions/{DefinitionValidator, DefinitionLoader}` |
| `app/Domain/Applications` | `Enums/*`, `Snapshots/{SnapshotBuilder, SnapshotHasher}`, `Actions/RecordReviewItems` |
| Models (+ factories) | `WorkflowTemplate`, `WorkflowStage`, `WorkflowTransition`, `Delegation`, `Application`, `ApplicationVersion`, `WorkflowAction`, `ReviewItem`, `StageAssignment` |
| Config | `config/workflows/company_license_v1.php` |
| Console | `WorkflowsLoadCommand` (`workflows:load`) |
| Tests | `tests/Feature/Workflow/*` with a small test definition in `tests/Fixtures/workflows/` |

## 6. Migrations
- All new.
- `applications.previous_license_id` gets its foreign key in Phase 4B (with `licenses`).
- `application_versions`, `workflow_actions` and `review_items` keep their rows forever: no update, no delete (02 §4.6–§4.7, D-187).
- `applications.deleted_at` is set only when the System Administrator discards a stale draft (D-193).
- One migration drops `facility_checklist_photos` and `facility_checklist_items` (D-192, Step 0).

## 7. Tests

**Required by 09 Phase 3 (04 §16 items 2, 4, 5, 10), with a small test definition:**

| # | Test |
|---|---|
| 2 | A transition is refused for a wrong role, a wrong realm (company account on a staff action and the reverse), a missing Technical Reviewer assignment, an expired or revoked delegation, each of the three segregation rules, and a missing password confirmation. Each refusal changes nothing and gives its reason |
| 4 | A wrong `expected_status` gives "already processed". Two attempts at the same transition: exactly one succeeds. A second database connection holding the row lock makes the engine wait, not double-process |
| 5 | An effect that throws rolls back the status, the WorkflowAction, the audit entry and the rows written by earlier effects; no email is sent |
| 10 | Loading a definition with an unreachable stage, an unknown guard, an unknown effect, an unknown role, an undeclared stage or a duplicate transition fails with a clear message, and nothing is loaded |

**"Done when":**
- `company_license_v1` loads without errors; running `workflows:load` twice changes nothing.
- A snapshot is identical when rebuilt from the same data, and its hash verifies. Changing one value changes the hash.

**Also:**
- Each guard built in Phase 3 passes its good case and blocks its failure case with its message (part of 04 §16 item 3).
- Each "not yet built" guard refuses, and each such effect throws (D-188).
- `availableActions()` matches what `perform()` allows, for each role.
- A delegate's action records `on_behalf_of_id`.
- The T12 conditional target is resolved.
- `RESUBMIT` goes to `resume_stage`.
- The one-open-application database key is enforced.
- WorkflowAction and ApplicationVersion rows refuse updates and deletes.
- A company cannot open another company's application (404).
- **D-193:** a draft blocks a second application of the same service and category. Only the System Administrator can discard a draft, and only once it is never-submitted and untouched for 30 days. A company can't discard, other staff can't, a submitted application can't be discarded, and discarding frees the slot.
- **D-192:** the checklist and photo routes no longer exist; premises still save.

## 8. Risks
1. **Concurrency test.** The test-database transaction hides rows from a second connection. The lock test uses its own committed data and cleans up after itself.
2. **The Company License definition can't be run end to end yet:** its payment, inspection and license steps are built in Phase 4. Phase 3 proves it loads and validates; tests run the engine on the small test definition.
3. **Snapshot content will grow** with products and permit details. `schema_version` lets old snapshots stay readable.
4. **Larastan** still runs only in CI (open question 16).

## 9. Proposed decisions (to confirm)

| ID | Proposal | Why |
|---|---|---|
| D-188 | Guards and effects whose data arrives in a later phase exist now as named classes. A guard **refuses** and an effect **throws** if it runs before its phase replaces it. Definitions referencing them load and validate. | 04 §13 requires every referenced guard and effect to exist; failing closed means nothing can pass an unbuilt rule. |
| D-189 | `workflows:load` never changes a loaded version: identical content is skipped, different content for the same code and version is refused (a change needs a new version file). The newest loaded version of a service is the active one for new applications; running applications keep theirs. | 04 §2: changing a definition creates a new version; running applications are not affected. |
| D-190 | The snapshot hash is SHA-256 of canonical JSON: keys sorted at every level, UTF-8, unescaped slashes and Unicode, dates in ISO 8601 UTC, `schema_version` 1. | D-069 asks for a hash; rebuilding must give the same bytes. |
| D-191 | `availableActions()` returns the transitions the user passes the actor checks for, each with its guard results and reasons; it changes nothing. | 04 §4 rule 5: buttons always match the rules, and the user sees why one is blocked. |
| D-194 | The System Administrator's draft discarding (D-193) uses a new permission, `discard stale drafts`, given only to the System Administrator; the 30 days are `config('applications.stale_draft_days')`, counted from the draft's `updated_at`. Discarding is audit-logged. | D-193 names the role and the period, not the permission or where the period is kept. |

## 10. Answered questions

1. Discarding a draft application: only the System Administrator, after 30 days untouched; while kept it blocks per D-044 (D-193).
2. Checklist photos: the facility checklist is the Inspector's job; the company checklist is removed (D-192, Step 0).

Still open for a later phase (not Phase 3): where the Import Permit's Form 3 safety section comes from now that companies have no checklist (open question 22).
