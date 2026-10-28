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

**Current state:** the app is still the base layer described in `docs/design/00-current-state.md`. It has auth, staff user management and roles/permissions, but no SARIS domain features yet. The confirmed architecture (`docs/design/01-architecture.md` §6, D-047 to D-064) changes parts of that base layer. The **Architecture** section below describes the code **as it is today**, until those changes land.

## Sources of truth (priority order)

1. `docs/decisions-log.md`: decisions with `Status: Confirmed` are binding.
2. `docs/design/`: approved design deliverables (`00-current-state.md`, `01-architecture.md`, `02-data-model.md`, …).
3. `docs/requirements-answers.md`: stakeholder answers. Section W is the Company License written procedure.
4. `docs/forms/`: application forms (`applications/`) and certificate templates (`templates/`).

If two sources disagree, the higher one wins. Still report the conflict to the user.

## Working rules

- **Before any feature:** read the relevant design doc and the decisions log.
- **Design missing, unclear or conflicting** with the code or another doc: **STOP and ask the user**. Never guess business rules, statuses, roles or data fields.
- **Implementation decision the design doesn't cover:** add it to `docs/decisions-log.md` with `Status: Proposed` and `Source: Claude Code`, and tell the user so they can confirm it in the design Project. Keep it easy to change until it is Confirmed.
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

> **Not built yet:** `composer run check`, `php artisan workflows:load` and Larastan don't exist in the repo yet, and `phpunit.xml` still uses `fertitrack_testing`. Until they are added, run the existing checks: `vendor/bin/pint --dirty`, `npm run lint`, `npm run types`, `php artisan test --compact <file or --filter>`. After route changes run `php artisan wayfinder:generate --with-form`; run `npm run build` if the UI throws "Unable to locate file in Vite manifest".

## Architecture (current code)

- **Routes**:
  - `routes/web.php` holds the dashboard, `users` and `roles` routes behind `auth` + `active` + `verified` + `ForcePasswordChange`.
  - `routes/settings.php` holds profile, password, appearance and 2FA.
  - `routes/api.php` has an empty `/v1` group (Passport; to be removed per D-049).
  - Target: `routes/admin.php` (`/admin/*`), `routes/portal.php` (`/portal/*`) and public routes (D-048).
- **Account gates** (`app/Http/Middleware`):
  - `EnsureUserIsActive` (alias `active`) logs out any user whose `users.status` isn't `active`.
  - `ForcePasswordChange` redirects users with `first_login = true` to the password settings page.
- **Authorization**:
  - Controllers declare `permission:<name>` middleware through `HasMiddleware`. See `UserController` and `RoleController`.
  - Permissions are defined in `RolesAndPermissionsSeeder`, which also **deletes any permission not listed there**, so add new permissions to that list.
  - `Gate::before` in `AppServiceProvider` grants `Super Admin` everything. To be replaced by a `System Administrator` role without the bypass (D-051).
  - Everything is seeded on the `web` guard, and `User::$guard_name = 'web'`.
- **Frontend shared props** (`HandleInertiaRequests`): `auth.user`, `auth.permissions` (all permission names), `flash.success` / `flash.error`, and `sidebarOpen`. The sidebar (`components/app-sidebar.tsx`) shows each nav item based on `auth.permissions`.
- **Frontend layout**: pages live in `resources/js/pages/<area>/` and are rendered with `Inertia::render('<area>/page')`. Import backend routes from `@/routes/...` or `@/actions/...` (Wayfinder) instead of hard-coding URLs. Those directories are generated, so don't edit them by hand.
- **Target code organisation** (D-061):
  - Business logic in `app/Domain/<Module>`.
  - Models in `app/Models`.
  - Controllers in `app/Http/Controllers/{Admin,Portal,Public}`.
  - Pages in `resources/js/pages/{admin,portal,public}`.
  - UI text in `lang/{en,so}.json` (D-054).

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
    └── forms/
        ├── applications/       the 3 Phase 1 application forms
        └── templates/          the 5 license and permit certificate templates
```

## Session end checklist

- [ ] Add any new implementation decision to `docs/decisions-log.md` as `Proposed`.
- [ ] Run Pint and the affected tests; summarize what was done (features, files, tests, results).
- [ ] List open questions and blockers for the design Project.
