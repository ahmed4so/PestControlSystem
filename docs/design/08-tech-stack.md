# Deliverable 8 — Technology Stack and Coding Conventions

- Project: SARIS Company Portal
- Version: 1.2 (approved 2026-10-28; documents from official .docx templates, D-138)
- Date: 2026-10-28
- Based on: `docs/design/00-current-state.md`, `docs/decisions-log.md` (D-001 to D-107),
  Deliverables 01–04

This deliverable fills the **Tech stack** and **Coding conventions** sections of CLAUDE.md
(§9 gives the text to paste).

---

## 1. Stack Summary

### 1.1 Kept from the existing codebase (D-047)

| Layer | Technology | Version (locked) | Why |
|---|---|---|---|
| Language | PHP | 8.3 on the server (`^8.2` in composer) | Team skill (H7); current, supported release |
| Framework | Laravel | 12.x | Team skill; mature security features (CSRF, hashing, encryption, policies, queues) |
| Server ↔ UI | Inertia | 2.x | Already in place; no separate API to build (D-048) |
| UI | React + TypeScript | 19.x / 5.9 | Already in place; typed UI reduces bugs |
| Styling | Tailwind CSS + shadcn/ui (Radix) | 4.x | Already in place; accessible components |
| Build | Vite + React Compiler plugin | 7.x | Already in place |
| Typed routes | Laravel Wayfinder | 0.1.x | No hard-coded URLs in React |
| Auth | Laravel Fortify | 1.x | Login, reset, 2FA for both portals (D-062) |
| Roles | spatie/laravel-permission | 6.x | Staff roles (D-023, D-051) |
| Email | resend/resend-laravel | 1.x | Resend (D-030) |
| Tests | Pest | 3.x | Already in place |
| Formatting | Pint, ESLint, Prettier | — | Already in place |
| Dev assistant | Laravel Boost | 2.x | Already in place (dev only) |

### 1.2 Removed

| Package | Reason |
|---|---|
| laravel/passport | No API in Phase 1 (D-049) |
| laravel/jetstream | Unused; live broken routes (D-049) |

### 1.3 Added

| Need | Package | Why this one |
|---|---|---|
| Audit log | `spatie/laravel-activitylog` | Decided (D-055) |
| R2 storage | `league/flysystem-aws-s3-v3` | Laravel's S3 driver works with R2 (D-056) |
| Fill official Word templates | `phpoffice/phpword` (TemplateProcessor) | Fills the official SARIS .docx templates with text and images (D-138) |
| Convert to PDF | LibreOffice (headless, server package) | Faithful .docx → PDF conversion, including the Arabic letterhead text (D-109) |
| QR codes | `chillerlan/php-qrcode` | Maintained; generates SVG/PNG without extra system libraries |
| Image re-encoding | `intervention/image` (GD driver) | Strips metadata and hidden content from uploads (D-089) |
| Honeypot | `spatie/laravel-honeypot` | Bot protection without outside services (D-080) |
| Security headers / CSP | `spatie/laravel-csp` | Nonce-based CSP (D-090) |
| Backups | `spatie/laravel-backup` | Nightly encrypted (AES-256) database backup to the R2 backup bucket (D-059) |
| Static analysis | `larastan/larastan` (dev) | Catches type and logic errors before tests |
| Browser image compression | `browser-image-compression` (npm) | Compresses photos on the phone before upload (D-040) |

Built-in features used instead of packages: rate limiting, encryption casts, `hash_hmac`
(national-ID hash, D-088), `finfo` (file type detection), the scheduler and the database queue.

### 1.4 Server baseline (D-057, D-094)

| Component | Version / setting |
|---|---|
| OS | Ubuntu LTS (confirm the droplet's version; open question 1) |
| Web server | Nginx with Let's Encrypt (certbot), HSTS, `limit_req`, fail2ban |
| PHP | PHP-FPM 8.3 with extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| Database | MySQL 8.0, bound to localhost |
| Processes | Supervisor runs `php artisan queue:work` and `php artisan inertia:start-ssr`; cron runs `php artisan schedule:run` every minute |
| Node | Node 22 LTS, to build front-end assets and run the Inertia SSR server |
| Documents | LibreOffice (headless) and the template fonts, including Noto Naskh Arabic (D-109, D-138) |
| Firewall | DigitalOcean Cloud Firewall + ufw (22, 80, 443) |
| Server-side rendering | **Kept as the repository has it** (D-110): the SSR bundle is built with `npm run build:ssr` and the SSR server runs under Supervisor (`php artisan inertia:start-ssr`). If it stops, Inertia falls back to normal browser rendering |

---

## 2. Environment Variables

Add to `.env.example` (names only; values only on the server, never committed):

| Group | Variables |
|---|---|
| App | `APP_NAME="SARIS Portal"`, `APP_ENV`, `APP_KEY`, `APP_DEBUG=false` (production), `APP_URL`, `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en`, `APP_DISPLAY_TIMEZONE=Africa/Mogadishu` (storage stays UTC) |
| Database | `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Session | `SESSION_DRIVER=database`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` (timeouts set per area in code, D-084) |
| Queue and cache | `QUEUE_CONNECTION=database`, `CACHE_STORE=database` |
| Mail | `MAIL_MAILER=resend`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| R2 files | `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_ENDPOINT`, `R2_BUCKET`, `R2_BACKUP_BUCKET` (disks `r2` and `r2_backups`, region `auto`) |
| Security | `NID_HASH_KEY` (separate from `APP_KEY`, so the national-ID hash survives an app-key rotation, D-088), `BACKUP_ARCHIVE_PASSWORD` |

Rule: `env()` is called only inside `config/` files (already true today).

---

## 3. Commands

These go into CLAUDE.md. Claude Code must check the script names in `composer.json` and
`package.json` and correct them if they differ.

| Purpose | Command |
|---|---|
| Install | `composer install` · `npm ci` · `cp .env.example .env` · `php artisan key:generate` |
| Database set-up | `php artisan migrate --seed` · `php artisan workflows:load` (loads `config/workflows/*`, D-097) |
| Run locally | `composer run dev` (web server, queue listener, Vite) |
| Tests | `php artisan test` (all) · `php artisan test --filter=Name` (one) |
| PHP style | `vendor/bin/pint` (fix) · `vendor/bin/pint --test` (check) |
| Static analysis | `vendor/bin/phpstan analyse` |
| Front-end checks | `npm run lint` · `npm run types` · `npm run format:check` |
| Build assets | `npm run build` and `npm run build:ssr` |
| All checks (local CI) | `composer run check` (§8) |
| New migration | `php artisan make:migration <name>` then `php artisan migrate` |
| Security audit | `composer audit` · `npm audit --omit=dev` |

---

## 4. Backend Conventions

### 4.1 Structure (D-061)

```
app/
├── Domain/<Module>/
│   ├── Actions/      One class per use case, e.g. SubmitApplication, VerifyProduct
│   ├── Enums/        Backed enums (statuses, types, codes)
│   ├── Guards/       Workflow guards (Workflow module)
│   ├── Effects/      Workflow effects (Workflow module)
│   ├── Policies/     Authorization for the module's models
│   ├── Rules/        Custom validation rules
│   └── Services/     Shared logic used by several Actions
├── Http/
│   ├── Controllers/{Admin,Portal,Public}/   Thin controllers
│   ├── Middleware/
│   └── Requests/{Admin,Portal,Public}/      Form Requests (validation)
└── Models/           Eloquent models: relationships, casts, scopes only
```

### 4.2 Rules

1. **Thin controllers.** Controller → Form Request (validation) → Policy (authorization) → Action
   (business logic) → Inertia response. No business logic in controllers, models or React.
2. **Status changes only through the workflow engine.** Never set `applications.status` or
   `licenses.status` directly; call `WorkflowEngine::perform()` (D-096) or the Licensing module.
3. **Files only through the Documents module.** No `Storage::disk()` calls anywhere else (D-056).
4. **Exact names.** Entity, table, status, action, role and enum names come from Deliverables
   02 and 04. New names need a decisions-log entry.
5. **Types.** `declare(strict_types=1);` in every new PHP file; typed properties, parameters and
   return types; backed enums instead of strings or class constants.
6. **Database:**
   - A merged migration is never edited; every change is a new migration.
   - Foreign keys are constrained, and every business table has `deleted_at` (D-053).
   - Money is stored as `decimal(12,2)`.
   - No raw SQL containing user input.
   - Any operation that writes more than one row runs in `DB::transaction()`.
7. **Mass assignment.** Explicit `$fillable` on every model; never `$guarded = []`.
8. **Dates.** Stored in UTC, shown in `Africa/Mogadishu`; use `CarbonImmutable`.
9. **Jobs.** Queued jobs are idempotent and dispatched `afterCommit`.
10. **Audit.** Models with business data use `LogsActivity` with sensitive attributes masked
    (national ID, tokens); never log passwords or secrets (D-091).
11. **Errors.** Business-rule failures throw domain exceptions with a translation key; the user
    sees a clear message, never a stack trace.
12. **Config.** No hard-coded fees, lists, durations or limits in code; use config files or
    reference tables (validity periods, timeouts, upload limits, renewal window).

---

## 5. Front-end Conventions

1. **Pages:** `resources/js/pages/admin/*`, `pages/portal/*` and `pages/public/*`. Layouts are `AdminLayout`, `PortalLayout` and `PublicLayout`. A page never imports from another area.
2. **Components:** the existing UI libraries are all kept (shadcn/ui on Radix, `@headlessui/react`, `@heroui/alert`) (D-111); new components use what is already in the codebase and no new UI library is added without a decisions-log entry. Shared app components go in `components/`, and area-specific ones in `components/admin` or `components/portal`.
3. **TypeScript:** strict mode. Page props are typed in `types/`, and `any` is not allowed.
4. **Routes:** always Wayfinder helpers; no hard-coded URLs.
5. **Forms:** Inertia `useForm`. Server validation messages are shown next to their fields. Drafts autosave (D-046).
6. **Text:** every visible string goes through `t()` (D-054); no hard-coded English.
7. **Security:** no `dangerouslySetInnerHTML`; no secrets in front-end code. Authorization is always decided on the server; the UI only hides what the server already refuses.
8. **Mobile-first:** design for a 360 px-wide phone first; lists are paginated; images are compressed before upload (`browser-image-compression`).
9. **Accessibility:** every input has a label; colour is never the only signal; everything works with the keyboard.

---

## 6. Translations (D-054, D-116)

- Laravel JSON files `lang/en.json` and `lang/so.json`. **The key is the English text**, e.g.
  `"Submit application": "Gudbi codsiga"`.
- Back end: `__('Submit application')`. Front end: `t('Submit application')`, using the
  translations shared through Inertia.
- A test fails if a key exists in `en.json` but not in `so.json`.
- Somali translations are written or reviewed by SARIS staff before go-live.

---

## 7. Testing Conventions

| Type | What | Where |
|---|---|---|
| Feature | Every business rule, every workflow transition (allowed and refused), portal separation, authorization, uploads | `tests/Feature/<Module>/` |
| Unit | Guards, effects, number generation, normalisation, snapshot building | `tests/Unit/<Module>/` |
| Required lists | 03-auth-security §15 and 04-workflow-engine §16 | All must exist before the related feature is merged |

- Tests run on **MySQL 8** (database `saris_portal_testing`, D-050), not SQLite.
- Every model has a factory; factories build valid data by default.
- Test names describe the rule, e.g. `it('refuses an import permit without an active importation license')`.
- A bug fix comes with a test that fails before the fix.

---

## 8. Git and CI

**Branches:**
- `main` is protected: changes go in only through a pull request, after CI passes.
- Work branches are named `feature/<short-name>`, `fix/<short-name>` or `docs/<short-name>`.

**Commits:** short imperative messages, with the decision number when relevant, e.g.
`Implement company sign-up with temporary password (D-063)`.

**Pull requests:**
- One feature per pull request.
- The description lists what was done, the tests added, any decisions-log entries added as Proposed, and open questions.

**CI (D-114):** GitHub Actions runs on every pull request if it is available on the repository. If it is not, the same steps run locally with `composer run check` (a composer script that chains them), and Claude Code must run it and pass before every commit.

**Steps:**
1. `composer install`, `npm ci`
2. `vendor/bin/pint --test`
3. `npm run lint`, `npm run types`
4. `vendor/bin/phpstan analyse` (Larastan level 5 to start)
5. `php artisan test` against a MySQL 8 service container
6. `composer audit`, `npm audit --omit=dev`
7. `npm run build` and `npm run build:ssr`

**Deployment:** from `main` only. The steps are detailed in Deliverable 9.

---

## 9. Text for CLAUDE.md

Replace the two placeholder sections in CLAUDE.md with:

```markdown
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
```

---

## Open questions

1. Droplet operating system and current PHP version (already open in the log, #8). PHP 8.3
   must be installable alongside the other sites.
2. Is GitHub Actions enabled on `ahmed4so/pestcontrolsystem` (Settings → Actions → General)?
   Until confirmed, `composer run check` is mandatory before every commit (D-114).

## Assumptions made

- [ASSUMPTION] The droplet can run PHP 8.3 and MySQL 8.0.
- [ASSUMPTION] Front-end assets are built on the server during deployment (Node 22 installed).
- The saris.gov.so domain will be verified in Resend (SPF and DKIM DNS records) before go-live.

## Decisions (confirmed 2026-10-28)

| Decision | Summary |
|---|---|
| D-108 | Added packages as in §1.3. |
| D-109 | PDF engine: official .docx templates filled with PhpWord, converted by headless LibreOffice, on demand. |
| D-110 | Inertia server-side rendering is kept as the repository has it; the SSR server runs under Supervisor in production. |
| D-111 | All existing UI libraries are kept (shadcn/ui, `@headlessui/react`, `@heroui/alert`); no new UI library without a decision. |
| D-112 | Server baseline in §1.4 (PHP 8.3, MySQL 8.0, Nginx, Supervisor for queue and SSR, Node 22). |
| D-113 | Backend conventions in §4, including: status changes only through the workflow engine, files only through the Documents module. |
| D-114 | Quality checks in §8 run on GitHub Actions if available, otherwise through `composer run check` before every commit. |
| D-115 | Git rules in §8: protected `main`, pull requests, branch and commit naming. |
| D-116 | Translation keys are the English source text in Laravel JSON files; a test enforces matching Somali keys. |
| D-117 | A separate `NID_HASH_KEY` for the national-ID hash. |
| D-118 | Portal emails are sent from `noreply@saris.gov.so`, with the domain verified in Resend. |
