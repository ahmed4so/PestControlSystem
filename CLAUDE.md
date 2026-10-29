# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Role

You are a senior software engineer implementing a government licensing system from an approved design.
**You implement the design. You do not invent it.**

## What this is

The **SARIS Company Portal**. The Somali Agricultural Regulatory & Inspection Service (SARIS) issues licenses and permits for pest control products.

- **Company Portal** (external): companies register, maintain a company profile and apply for SARIS services online.
- **Admin Portal** (internal): SARIS staff process applications.
- One Laravel app serves both portals with a shared database, but they are **fully separated** in access, roles and permissions (D-048).

Phase 1 services: (1) Pest Control Products Company License (Importer / Wholesaler / Retailer), (2) Pest Control Products Operator License, (3) Import Permit of Pest Control Products. Go-live order: Company License first (D-035).

Key business rules:

- An Import Permit application requires an **Active Importation License**. Enforce this in the backend, at submission and again at `DG_DECISION` (D-015).
- Sign-up: Full Name, Email, Phone → emailed temporary password (1 hour) → login → forced password change → company profile (D-063).
- Shared data (company profile, persons, premises, products) is entered once and referenced by applications. Submissions and issued licenses keep **immutable snapshots** (D-006, D-042).

**Current state:** Phase 0 (clean-up and foundations, `docs/plans/phase-0-follow-up.md`, 09 §3) is done; `docs/design/00-current-state.md` describes the code before it. There are no SARIS domain features yet. The **Architecture** section below describes the code as it is today.

## Sources of truth (priority order)

1. `docs/decisions-log.md`: decisions with `Status: Confirmed` are binding. `Proposed` entries are not binding until the user confirms them.
2. `docs/design/01–09`: the approved design deliverables (`00-current-state.md` is a snapshot of the code before the build).
3. `docs/requirements-answers.md`: stakeholder answers. Section W is the Company License written procedure.
4. `docs/forms/`: application forms (`applications/`) and certificate templates (`templates/`).
5. `docs/plans/`: approved implementation plans. A plan carries out the design; it never overrides it.

If two sources disagree, the higher one wins. Still report the conflict to the user.

## How we work

- **Who does what.**
  - The Claude.ai Project is for brainstorming and decisions only.
  - The user brings decisions from it to Claude Code as **decision briefs**.
  - Claude Code writes every technical implementation plan and the code.
- **Decision briefs.** Record each decision in a brief in `docs/decisions-log.md` as `Status: Confirmed`, with the next free D-number unless the brief gives one. Mark any entry it replaces as `Superseded by D-xxx` or `Amended by D-xxx`.
  - If a brief comes with updated design documents, commit them with the log.
  - If a brief conflicts with `docs/design/01–09` and no updated document comes with it, **stop and ask**.
- **Plan before code.** Before coding any phase or feature:
  1. Write the plan to `docs/plans/<phase-or-feature>.md` with these sections: Scope (in and out), Design references (D-numbers and design sections), Steps, Files, Migrations, Tests, Risks and open questions, and Proposed decisions.
  2. Show the plan to the user and **wait for their approval**. Do not start coding until they approve.
  3. On approval, set the plan's status line to `Approved (YYYY-MM-DD)` and commit it with the first change.
  4. If the plan has to change while coding, update it and get approval again before continuing.
- **Conflicts stop the work.** If a plan, or the code, would conflict with `docs/design/01–09` or a Confirmed decision, stop and ask the user. Never resolve it yourself.
- **Your own choices.** Implementation choices that the design and the decisions log don't cover go into `docs/decisions-log.md` as `Status: Proposed`, `Source: Claude Code`, and are listed in the plan. They become Confirmed only when the user confirms them.

## Working rules

- **Before any feature:** read the relevant design doc and the decisions log, then write the plan (see How we work).
- **Design missing, unclear or conflicting** with the code or another doc: **STOP and ask the user**. Never guess business rules, statuses, roles or data fields.
- **Implementation decision the design doesn't cover:** add it to `docs/decisions-log.md` with `Status: Proposed` and `Source: Claude Code`, and tell the user so they can confirm it. Keep it easy to change until it is Confirmed.
- **Names:** use the exact entity, status and role names from the design docs, in code, database and UI.
- **Portal separation:** the Company Portal must never reach Admin Portal routes or data, and the reverse. Enforce this in the backend: separate tables, guards, route files, middleware and session cookies (D-048). Scope every company query to the user's own company.
- **Business rules and workflow:** enforce every rule server-side; UI checks are only a convenience. Only allowed workflow transitions may change an application's status, and only for a user who holds that stage's role.
- **Security defaults:**
  - OWASP best practices.
  - Hashed passwords and a strong password policy everywhere.
  - Temporary passwords expire.
  - Rate limiting and account lockout.
  - Mandatory TOTP 2FA for staff (D-052).
  - Audit log for every workflow action (D-055).
  - Soft deletes only (D-053).
  - No secrets in code or git.
- **Tests:** write tests for every business rule and workflow transition, including the forbidden paths: wrong portal, wrong company, missing license.

## Tech stack
Laravel 12 (PHP 8.3) · Inertia 2 · React 19 + TypeScript · Tailwind 4 + shadcn/ui ·
Fortify (both portals) · spatie/laravel-permission · spatie/laravel-activitylog ·
MySQL 8 · database queue/cache/session · Cloudflare R2 (S3 driver) · Resend · PhpWord + LibreOffice (PDF on demand) ·
Pest. Inertia SSR kept as in the repo. Emails from noreply@saris.gov.so. Full detail: docs/design/08-tech-stack.md.

### Commands
- Install: `composer install && npm ci && cp .env.example .env && php artisan key:generate`
- DB: `php artisan migrate --seed && php artisan workflows:load`
- Run: `composer run dev`
- Test: `php artisan test` (MySQL 8, database saris_portal_testing)
- Check before every commit: `composer run check` (Pint, Larastan, ESLint, TypeScript, tests, audits, build)

> **Notes:** `php artisan workflows:load` arrives with the workflow engine (Phase 3). `php artisan security:npm-audit` is the npm step of the check; it fails on any advisory not listed in `config/security.php` and on any list entry past its monthly `review_by` date (D-153). `php artisan translations:report` lists Somali entries still equal to the English (`--fail` is the Phase 5 go-live check, D-154). For a quick loop, run `vendor/bin/pint --dirty`, `npm run lint`, `npm run types` and `php artisan test --compact <file or --filter>`. After route changes run `php artisan wayfinder:generate --with-form`; run `npm run build` if the UI throws "Unable to locate file in Vite manifest".

## Architecture (current code, after Phase 0)

- **Routes**:
  - `routes/web.php` holds the dashboard, `users` and `roles` routes behind `auth` + `active` + `verified` + `ForcePasswordChange`. Users have no delete route; `users.deactivate` / `users.activate` replace it (D-053).
  - `routes/settings.php` holds profile, password, appearance and 2FA (no self-delete).
  - There is no API (Passport and Jetstream removed, D-049).
  - Target for Phase 1: `routes/admin.php` (`/admin/*`), `routes/portal.php` (`/portal/*`) and public routes (D-048).
- **Controllers and requests**: staff controllers in `app/Http/Controllers/Admin`, form requests in `app/Http/Requests/Admin`; `Portal/` and `Public/` are empty until Phase 1.
- **Domain modules** (`app/Domain/<Module>`, D-061):
  - `Identity`: `StaffRole` and `AccountStatus` enums, `DeactivateStaffUser`, `PasswordPolicy`, `Translations`.
  - `Audit`: `Audited` trait (logs before/after, never hidden attributes, masks `auditMaskedAttributes()`).
  - `Documents`: `FileStorage`, the only way to read and write files (R2 disk `r2`).
  - `ReferenceData`: `NumberPrefix` and `NumberSequencer` (locked yearly numbers).
  - The other modules are empty folders.
- **Account gates** (`app/Http/Middleware`):
  - `EnsureUserIsActive` (alias `active`) logs out users whose status isn't `ACTIVE`.
  - `ForcePasswordChange` redirects users with `first_login = true` to the password page; new staff users get `first_login = true`.
  - `SecurityHeaders` plus spatie `AddCspHeaders` (policy `App\Http\Csp\SarisPolicy`) on every web response.
- **Authorization**:
  - Controllers declare `permission:<name>` middleware through `HasMiddleware`.
  - `RolesAndPermissionsSeeder` is authoritative: it seeds the System Administrator and the D-023 roles with their permissions, deletes any permission not listed, and removes `Super Admin`. There is no `Gate::before` bypass.
  - Everything is seeded on the `web` guard, and `User::$guard_name = 'web'`.
- **Audit log**: `activity_log` (model `App\Models\AuditActivity`) is append-only by application rule (D-160): the model refuses updates and deletes, and there are no database triggers. Never write code that changes audit rows, and never schedule `activitylog:clean`.
- **Frontend shared props** (`HandleInertiaRequests`): `auth.user`, `auth.permissions`, `flash.success` / `flash.error`, `sidebarOpen`, `locale` and `translations`. Use `useTranslation()` from `@/lib/i18n` and `t('English text')`; add every key to both `lang/en.json` and `lang/so.json`.
- **Frontend layout**: pages live in `resources/js/pages/<area>/` and are rendered with `Inertia::render('<area>/page')`. Import backend routes from `@/routes/...` or `@/actions/...` (Wayfinder) instead of hard-coding URLs. Those directories are generated, so don't edit them by hand.
- **Target code organisation** (D-061): pages move to `resources/js/pages/{admin,portal,public}` in Phase 1.

## Coding conventions
- Business logic in app/Domain/<Module>/Actions; controllers thin; Form Requests validate; Policies authorize.
- Change application/license status ONLY via WorkflowEngine::perform() / Licensing module.
- Store and read files ONLY via the Documents module.
- Use exact names from docs/design/02-data-model.md and 04-workflow-engine.md.
- strict_types, typed everything, backed enums, $fillable, soft deletes, transactions for multi-row writes.
- Every UI string via __() / t(); keys in lang/en.json and lang/so.json.
- React pages in pages/{admin,portal,public}; use the UI libraries already in the repo (no new ones); Wayfinder routes; no dangerouslySetInnerHTML.
- Every business rule and workflow transition has Pest tests (allowed and refused).
- Never log passwords, tokens or national IDs. No secrets in code.

Also from the Laravel Boost guidelines:
- Use `Model::query()` rather than `DB::`; eager-load to avoid N+1 queries. Call `env()` only inside config files.
- When you modify a column in a migration, restate all of its existing attributes. Never edit a merged migration; add a new one.
- Create files with `php artisan make:* --no-interaction` and tests with `php artisan make:test --pest`. New models get factories.
- Run only the affected tests; don't delete tests without approval. Don't add dependencies or top-level folders without a decision.

## Repository layout

```
/                               Laravel app (app/, routes/, resources/, database/, tests/, …)
├── CLAUDE.md                   this file
└── docs/
    ├── decisions-log.md        D-xxx decisions (binding when Confirmed)
    ├── requirements-answers.md stakeholder answers + written procedure (section W)
    ├── design/                 approved deliverables: 00-current-state.md, 01-architecture.md, …
    ├── plans/                  implementation plans written by Claude Code, approved by the user
    └── forms/
        ├── applications/       the 3 Phase 1 application forms
        └── templates/          the 5 license and permit certificate templates
```

## Session end checklist

- [ ] Record any decision brief from this session in `docs/decisions-log.md` as Confirmed.
- [ ] Add any new implementation choice to `docs/decisions-log.md` as Proposed, and update the plan.
- [ ] Run Pint and the affected tests; summarize what was done (features, files, tests, results).
- [ ] List open questions, blockers, and the Proposed decisions waiting for the user's confirmation.
