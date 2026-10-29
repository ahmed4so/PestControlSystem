# Plan: Phase 0 follow-up

- Status: Approved (2026-10-29)
- Branch: `feature/phase-0-foundations` (not merged)
- Date: 2026-10-29

## 1. Scope

Bring the unmerged Phase 0 branch in line with the decisions confirmed on 2026-10-29 (D-148 to D-160), so it can be merged.

**In scope:**
1. Bring `main` into the branch, so it has the confirmed decisions log, 03 v1.3 and 09 v1.3.
2. D-160: remove the MySQL audit triggers. The application-level refusal stays.
3. D-153: list every ignored audit advisory by ID with a reason, and make it fail when an entry is not re-checked monthly.
4. D-154: add a translation report that lists Somali entries still identical to the English.
5. Update CLAUDE.md and code comments that still describe triggers or "Proposed" decisions.

**Out of scope:**
- D-157 self-hosted fonts and removing `fonts.bunny.net` from the CSP. That is Phase 1 work, as D-157 says.
- Larastan. It still can't be downloaded in this sandbox; CI installs it (open question 16).
- Any Phase 1 work.

## 2. Design references

| Item | References |
|---|---|
| Audit log | D-055, D-091 (amended), D-160; 03 §13 (v1.3); 09 Phase 0 table row "Audit log" |
| Ignored advisories | D-153, D-114; 08 §8 step 6 |
| Translations | D-054, D-116, D-154; 08 §6 |

## 3. Steps

**Step 1: Bring `main` into the branch.**
- Run `git merge main` into `feature/phase-0-foundations`, as a merge commit (no rebase).
- Expected conflicts and how to resolve them:
  - `docs/decisions-log.md`: take `main`'s version. It has D-148 to D-160 Confirmed, which replaces the branch's Proposed versions.
  - `CLAUDE.md`: keep `main`'s new "How we work" section, sources of truth, layout and checklist, plus the branch's "Architecture (current code, after Phase 0)" section and command notes.
  - `docs/design/03-auth-security.md` and `09-implementation-plan.md`: no conflict expected (the branch didn't change them).

**Step 2: Remove the audit triggers (D-160).**
- Change the unmerged migration `2026_10_28_162649_make_activity_log_append_only.php` so it no longer creates triggers. Rename it to `2026_10_28_162649_add_audit_context_columns_to_activity_log_table.php`.
  - It keeps adding `actor_role`, `ip_address` and `user_agent`, which 03 §13 requires.
  - D-160 explicitly says the trigger migration is removed from the unmerged branch, so editing it here does not break "never edit a merged migration".
- `AuditActivity` keeps refusing `update` and `delete`. Fix its doc comment.
- Remove the two database-level tests, "rejects UPDATE on audit rows in the database" and "rejects DELETE on audit rows in the database". They test the triggers, which no longer exist. **Approving this plan approves deleting these two tests.**
- Add tests:
  - "the audit model refuses updates and deletes, and the row is unchanged". This extends the existing model test to re-read the row from the database.
  - "no database triggers exist on activity_log". It queries `information_schema.TRIGGERS`, so the triggers can't come back unnoticed.

**Step 3: Ignored advisories with monthly review (D-153).**
- New `config/security.php` with one allow-list for both audits. Each entry has an advisory ID, the package, the reason and a `review_by` date 30 days ahead:
  - Composer: `PKSA-zk1n-qbm6-d3rq` (web-auth/webauthn-lib, through laravel/fortify → laravel/passkeys; passkeys not used).
  - npm: `GHSA-gcqx-w48m-w2h4` (deepmerge, through @heroui/alert → @heroui/theme; kept by D-111).
- The Composer ignore stays in `composer.json` (`config.audit.ignore`), because Composer reads it natively. Its reason text changes from "Proposed D-153" to "D-153".
- New command `php artisan security:npm-audit`:
  1. Runs `npm audit --omit=dev --json`.
  2. Fails on any advisory, at any severity, that is not in the npm allow-list. This replaces the current `--audit-level=moderate`, which let every low advisory through.
  3. Fails if any allow-list entry, npm or Composer, is past its `review_by` date. That enforces the monthly re-check.
  4. Prints which allowed advisories still apply, and which allowed entries no longer match anything (fixed upstream, so remove them).
- `composer run check`: replace `npm audit --omit=dev --audit-level=moderate` with `@php artisan security:npm-audit`.

**Step 4: Translation report (D-154).**
- New command `php artisan translations:report`:
  - Lists every key whose Somali value is still identical to the English, with a count.
  - With `--fail`, it exits non-zero when the count is above zero. That is for the Phase 5 go-live check; it is not added to `composer run check` now, because every entry is still a placeholder.

**Step 5: Docs and comments.**
- CLAUDE.md "Audit log" line: "append-only by application rule (D-160); the model refuses updates and deletes; no database triggers".
- CLAUDE.md "Notes" line: mention the two new commands.
- `SarisPolicy` comment: note that `fonts.bunny.net` goes away when the fonts are self-hosted in Phase 1 (D-157).
- Mark this plan `Approved (date)` in the first commit.

**Step 6: Verify and push.**
- Run `migrate:fresh --seed`, the full Pest suite, Pint, ESLint, TypeScript, both audits and both builds. Larastan can't run here.
- Push the branch. Do not merge.

## 4. Files

| Action | File |
|---|---|
| Merge | `docs/decisions-log.md`, `CLAUDE.md`, `docs/design/03-auth-security.md`, `docs/design/09-implementation-plan.md` |
| Rename and edit | `database/migrations/2026_10_28_162649_make_activity_log_append_only.php` → `…_add_audit_context_columns_to_activity_log_table.php` |
| Edit | `app/Models/AuditActivity.php` (comment only), `tests/Feature/Audit/AuditLogTest.php`, `composer.json` (reason text, check script), `app/Http/Csp/SarisPolicy.php` (comment), `CLAUDE.md` |
| New | `config/security.php`, `app/Console/Commands/NpmAuditCommand.php`, `app/Console/Commands/TranslationsReportCommand.php`, `tests/Feature/Foundation/NpmAuditCommandTest.php`, `tests/Feature/Foundation/TranslationsReportTest.php` |

`app/Console/Commands` is a new folder inside `app/`, not a new top-level folder.

## 5. Migrations

- No new migration. One unmerged migration is renamed and loses its trigger statements, as D-160 instructs.
- Local and CI databases are rebuilt with `migrate:fresh`.
- No other environment has run the branch's migrations, because nothing is deployed.

## 6. Tests

| Test | Proves |
|---|---|
| Audit model refuses updates and deletes, row unchanged in the database | D-160 application rule |
| No triggers on `activity_log` in `information_schema` | D-160, no database lock |
| Existing audit tests (masking, before/after, causer, role changes) still pass | D-055 unchanged |
| `security:npm-audit` passes when every advisory is allowed and in date | D-153 |
| …fails on an advisory that is not allowed, even if low | D-153 |
| …fails when an allow-list entry is past `review_by` | D-153 monthly re-check |
| …reports an allowed entry that no longer matches | Removal when a fix exists |
| The `composer.json` ignore list matches the Composer entries in `config/security.php` | One list, no drift |
| `translations:report` lists placeholder entries; `--fail` exits non-zero when any remain, zero when none | D-154 |

The npm-audit tests feed the command a fixed JSON sample through an injectable runner, so they don't call the network.

## 7. Risks and open questions

1. **The monthly date will fail the check by design.** Once `review_by` passes, `composer run check` fails until someone re-checks the advisories and moves the date. That is how the monthly re-check is enforced; the failure message says exactly which entry to review.
2. **The npm JSON format could change between npm versions.** The command reads only `vulnerabilities[*].via[*].url` and `severity`, and fails safely (exits non-zero) if the format is unrecognised.
3. **Merge conflicts in CLAUDE.md** need care, so the new workflow text isn't lost. I'll check the merged file line by line.
4. Larastan is still unverified until CI runs (open question 16).

## 8. Proposed decisions (to confirm)

| ID | Proposal |
|---|---|
| D-161 | Ignored audit advisories live in `config/security.php` with an advisory ID, reason and a `review_by` date 30 days ahead. `php artisan security:npm-audit` fails on any non-allowed npm advisory (all severities) and on any expired entry. The Composer list is mirrored in `composer.json` and kept in sync by a test. |
| D-162 | `php artisan translations:report` lists Somali entries identical to the English; `--fail` is the Phase 5 go-live check (D-154). |
