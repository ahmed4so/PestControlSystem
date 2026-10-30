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

**Current state:** Phase 0 (foundations), Phase 1 (authentication for both portals, `docs/plans/phase-1-auth.md`) and Phase 2 (company profile and reference data, `docs/plans/phase-2-company-profile.md`) are done; `docs/design/00-current-state.md` describes the code before Phase 0. Next is Phase 3 (workflow engine). The **Architecture** section below describes the code as it is today.

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

> **Notes:** `php artisan workflows:load` arrives with the workflow engine (Phase 3). `php artisan identity:password-expiry-reminders` runs daily from the scheduler (D-082). `php artisan security:npm-audit` is the npm step of the check; it fails on any advisory not listed in `config/security.php` and on any list entry past its monthly `review_by` date (D-153). `php artisan translations:report` lists Somali entries still equal to the English (`--fail` is the Phase 5 go-live check, D-154). For a quick loop, run `vendor/bin/pint --dirty`, `npm run lint`, `npm run types` and `php artisan test --compact <file or --filter>`. After route changes run `php artisan wayfinder:generate --with-form`; run `npm run build` if the UI throws "Unable to locate file in Vite manifest".

## Architecture (current code, after Phase 2)

- **Two portals, one app** (D-048, D-079, D-173):
  - `routes/admin.php`: staff, `/admin/*`, route names `admin.*`, guard `web`, broker `users`, cookie `saris_admin_session` (path `/admin`), table `sessions`.
  - `routes/portal.php`: companies, auth pages at the root (`/login`, `/signup`, `/temporary-password`, …) and app pages under `/portal`, route names `portal.*`, guard `company`, broker `company_users`, cookie `saris_portal_session`, table `company_sessions`.
  - `routes/public.php`: `/` landing page and `POST /locale/{en,so}`. There is no API (D-049).
  - Each group starts with the `area:admin` / `area:company` middleware (`SetAuthArea`). `ApplyAuthArea` sets the guard, broker, Fortify home, session cookie, table, path, lifetime and password-confirmation window. It also runs on `RouteMatched`, because Laravel builds controllers (and Fortify's guard) before route middleware runs. `AuthArea::current()` tells code which area it is in.
- **Fortify for both areas** (D-062): `Fortify::ignoreRoutes()`; `FortifyRoutes::authentication()` / `::account()` register Fortify's controllers in each route file. `FortifyServiceProvider` renders `{admin,portal}/auth/*` pages, binds area-aware responses, sets the login pipeline and the per-area rate limiters. Registration and email verification are off (D-165).
- **Login** (`app/Domain/Identity/Login`): `AuthenticateAccount` looks only in the area's table, counts failures and locks after 5 for 15 minutes (D-081), and refuses expired temporary passwords. `RecordSuccessfulLogin` resets the counter, sets `last_login_at` and `email_verified_at`, and starts the session clock. No "remember me" (D-084).
- **Account middleware** (`app/Http/Middleware`):
  - `active` logs out non-`ACTIVE` accounts.
  - `session.timeouts` enforces 15 min staff / 30 min company idle time and the staff 12-hour limit.
  - `password.current` (`EnsurePasswordIsCurrent`) sends first-login and expired (90-day) passwords to the password page.
  - `two-factor.enforced` keeps staff without confirmed 2FA out of admin pages (D-052).
  - `SetLocale` (preferred language, else the `locale` cookie), `SecurityHeaders` and spatie `AddCspHeaders` (`SarisPolicy`; fonts from `'self'` only, D-157).
- **Domain modules** (`app/Domain/<Module>`, D-061):
  - `Identity`: enums `StaffRole`, `AccountStatus`, `AuthArea`; actions `RegisterCompanyUser`, `IssueTemporaryPassword`, `CreateStaffUser`, `ChangePassword`, `UnlockAccount`, `ResetTwoFactor`, `RequestLoginEmailChange`, `ConfirmLoginEmailChange`, `DeactivateStaffUser`; rule `NotRecentPassword` (last 5); `HasSecurityState` on both account models; `AccountSessions::end()`; `PasswordPolicy`, `TemporaryPassword`, `PublicFormMessages`, `Translations`.
  - `Notifications`: `SendNotification` is the only way to send email. It logs to `notification_logs` (no body) and queues one Mailable per `NotificationCode` (`Mail/*`), after commit, 3 tries, encrypted. Templates are `resources/views/mail/{CODE}.blade.php` and `{CODE}-text.blade.php` for one language; the layout shows both, preferred first (D-125, D-167). Failed sends are listed at `/admin/failed-emails`.
  - `Audit`: `Audited` trait, plus `AuditLog::record()` for security events (logins, lockouts, temporary passwords, 2FA, email change).
  - `CompanyRegistry`: enums `PremisesType`, `ChecklistItem` (9 items, max 5 photos), `DocumentType`; `Data/Address` and the `HasAddress` trait (`address_*` columns, D-065); `BelongsToCompany` (global scope: on the Company Portal every query is limited to the account's company, so other IDs give 404); actions `CreateCompany`, `UpdateCompanyProfile`, `SavePerson` (national ID unique system-wide, D-066), `SavePremises`, `AddChecklistPhoto`, `UploadDocument` (replacement history, D-067; renewed certificates notify Registrars), `ReleaseNationalId`; `NationalIdHasher` (HMAC with `NID_HASH_KEY`, D-117); `ProfileCompleteness` (D-178); `CompanyEditLock` with the `SubmissionState` contract (Phase 2 binds `NothingSubmittedYet`; Phase 4 binds the real state, D-073).
  - `Documents`: `FileStorage` (disk from `config('documents.disk')`, R2 in production, D-180; `download()` = 5-minute signed URL or attachment) and `SafeUploads` (`UploadInspector`: type from content, 5 MB, PDFs with JavaScript or encryption refused; `ImageReencoder`: GD re-encode, EXIF removed). Company files go only through `SafeUploads`. Antivirus is in the backlog (D-183).
  - `ReferenceData`: `NumberPrefix`, `NumberSequencer`, `FieldFormats` (05 §2 rules and normalisation), `AddressRules`, `AddressOptions`, `Countries` (PHP intl). `ReferenceDataSeeder` loads `database/seeders/data/fms-regions.php`, a DRAFT list until SARIS sends the official one (D-184).
  - The other modules are empty folders.
- **Models**: `User` (staff) and `CompanyUser` (company accounts) never share a table, guard or session; `PasswordHistory`, `NotificationLog`; `FederalMemberState`, `Region`, `Company`, `Person` (encrypted national ID, generated unique `active_nid_key`), `Premises`, `FacilityChecklistItem`, `FacilityChecklistPhoto`, `Document`.
- **Company Portal pages** (`/portal`, middleware `company.profile` sends accounts without a company to set-up): company, persons, premises with checklist and photos, documents, own-file downloads. Policies (`app/Policies`) apply ownership and the edit lock.
- **Admin company pages**: `/admin/companies` (read-only, `view applications`, IDs masked, every file view audited), `/admin/national-ids` (release, `release national id` + password confirmation), `/admin/reference-data/fms` (`manage reference data`).
- **Authorization**:
  - Controllers declare `permission:<name>` middleware through `HasMiddleware`.
  - `RolesAndPermissionsSeeder` is authoritative: it seeds the System Administrator and the D-023 roles with their permissions, deletes any permission not listed, and removes `Super Admin`. There is no `Gate::before` bypass.
  - Everything is seeded on the `web` guard, and `User::$guard_name = 'web'`.
  - Staff user and role changes sit behind `password.confirm:admin.password.confirm` (D-085).
- **Audit log**: `activity_log` (model `App\Models\AuditActivity`, fills `actor_role`, IP, user agent) is append-only by application rule (D-160): the model refuses updates and deletes, and there are no database triggers. Never write code that changes audit rows, and never schedule `activitylog:clean`.
- **Public forms**: sign-up, temporary-password and forgot-password use `spatie/laravel-honeypot` (`config/honeypot.php`, 3-second minimum) and always give the same answer (`PublicFormMessages`, `SameAnswerSpamResponder`).
- **Frontend shared props** (`HandleInertiaRequests`): `area`, `auth.user` (current area's account only: id, name, email, two_factor_enabled), `auth.permissions` (staff only), `flash.success` / `flash.error`, `sidebarOpen`, `locale` and `translations`. Use `useTranslation()` from `@/lib/i18n` and `t('English text')`; add every key to both `lang/en.json` and `lang/so.json` (also for `__()` in PHP and mail views).
- **Frontend layout**: pages in `resources/js/pages/{admin,portal,public}`; layouts `admin-layout`, `portal-layout`, `public-layout`, `auth-layout`. Shared auth and settings screens live in `components/auth` and `components/settings` and pick their routes through `useArea()` (`@/lib/area`). Import backend routes from `@/routes/...` or `@/actions/...` (Wayfinder) instead of hard-coding URLs. Those directories are generated, so don't edit them by hand.
- **Tests**: helpers in `tests/Pest.php`: `staffUser()`, `systemAdministrator()`, `companyUser($attributes, withCompany: true)`, `honeypotFields()`, `passwordConfirmed()`. After a company request the test stays in the company area, so query company models with `withoutGlobalScopes()` and pass the guard to `actingAs()`.

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
