# 00 — Current State of the Codebase

- Date: 2026-10-28
- Source: Claude Code, read-only inspection of `ahmed4so/pestcontrolsystem` at `main` (commit `009f947`)
- Scope: the Laravel application at the **repository root**. `saris-portal/` holds documentation only. `saris-portal/src/` is empty.
- Method: read the source, `composer.lock` and `package-lock.json`, and ran `php artisan route:list` (dependencies installed locally, no files changed). The test suite was **not run** because it needs a MySQL server, which this environment doesn't have.

The application is the Laravel React starter kit with a small added base layer: user management, roles/permissions, an account status gate and a forced password change. **No SARIS domain feature exists yet.**

---

## 1. Stack and versions

| Area | Package | Constraint | Locked version |
|---|---|---|---|
| PHP | — | `^8.2` | 8.3.6 in this environment |
| Framework | laravel/framework | `^12.0` | 12.58.0 |
| Server ↔ SPA | inertiajs/inertia-laravel | `^2.0` | 2.0.21 |
| Web auth | laravel/fortify | `^1.30` | 1.37.0 |
| Starter scaffolding | laravel/jetstream | `^5.5` | 5.5.2 (stack `inertia`) |
| API tokens | laravel/passport | `^13.7` | 13.7.5 |
| 2FA (TOTP) | pragmarx/google2fa (via Fortify) | — | 9.0.0 |
| Roles/permissions | spatie/laravel-permission | `^6.24` | 6.25.0 |
| Typed routes | laravel/wayfinder | `^0.1.9` | 0.1.17 |
| Email | resend/resend-laravel | `^1.4` | 1.4.0 |
| Dev tooling | laravel/boost 2.0.0, laravel/pint 1.29.1, laravel/sail 1.58.0, pestphp/pest 3.8.6, laravel/pail, tinker 2.11.1 | | |

**Frontend:** React 19.2.4 with TypeScript 5.9.3 and Inertia (`@inertiajs/react` 2.3.15). No Blade UI beyond the root view `resources/views/app.blade.php`. **No Livewire and no Vue.**
- Styling: Tailwind CSS 4.1.18 (`@tailwindcss/vite`), shadcn-style components on Radix UI (`components.json`, `resources/js/components/ui/`), plus `@headlessui/react` 2.2.9 and `@heroui/alert` 2.2.31.
- Build: Vite 7.3.1, `laravel-vite-plugin` 2.1.0, `@vitejs/plugin-react` 5.1.4 with the React Compiler babel plugin, and `@laravel/vite-plugin-wayfinder` 0.1.7 (`formVariants: true`). SSR entry: `resources/js/ssr.tsx`.
- Lint/format: ESLint 9.39.2, Prettier 3.8.1 (with the Tailwind plugin), Pint (`pint.json`).
- Node: v22.22.0 in this environment.

**Admin panel package:** none. There is no Filament, Nova, Backpack or Orchid. The "admin" screens are hand-built Inertia/React pages (see §4).

## 2. Database

**Engine:**
- `.env.example` and the config default use **SQLite** (`DB_CONNECTION=sqlite`).
- `phpunit.xml` runs tests on **MySQL**, database `fertitrack_testing`. The name suggests it was copied from another project.
- No engine version is pinned anywhere in the repo.

**Migrations and tables:**

| Migration | Tables | Key columns |
|---|---|---|
| `0001_01_01_000000_create_users_table` | `users` | id, name, email (unique), phone (30, nullable), status (string, default `active`), first_login (bool, default false), email_verified_at, password, remember_token, timestamps |
| | `password_reset_tokens` | email (PK), token, created_at |
| | `sessions` | id (PK), user_id (nullable, idx), ip_address, user_agent, payload, last_activity |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` | key (PK), value/owner, expiration |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` | standard Laravel queue tables |
| `2025_08_14_170933_add_two_factor_columns_to_users_table` | `users` (alter) | two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at |
| `2026_02_15_113405_create_permission_tables` | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | spatie tables, `name` + `guard_name` unique, teams **off** |
| `2026_04_15_104116…104120` (Passport) | `oauth_auth_codes`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_clients`, `oauth_device_codes` | access/refresh tokens tied to `users.id`; clients are UUID with nullable owner morph |

**Models:**
- `App\Models\User` is the only model. It uses the traits `HasApiTokens` (Passport), `HasFactory`, `HasRoles` (spatie), `Notifiable` and `TwoFactorAuthenticatable` (Fortify), and implements `MustVerifyEmail` and `OAuthenticatable`.
- Relationships: only the package-provided `roles` / `permissions` (spatie) and `tokens` (Passport). There are no application relationships.
- `$guard_name = 'web'` is forced, so API requests resolve permissions against `web`.
- `validateForPassportPasswordGrant()` refuses users whose `status !== 'active'`.
- No `SoftDeletes` anywhere.

**Factory:** `UserFactory`.

**Seeders:**
- `RolesAndPermissionsSeeder` creates 11 permissions on the `web` guard: view/create/update/delete/suspend user, view/create/update/delete/assign role, and assign permissions. It deletes any other permission and creates the role `Super Admin`.
- `SuperAdminSeeder` creates `admin@example.com` / `password` with `Super Admin`, outside production only.

## 3. Authentication

**User model and guards:**
- There is one user model and one provider (`users`, Eloquent `App\Models\User`).
- Guards:
  - `web`: session driver, `users` provider.
  - `api`: Passport driver, the **same** `users` provider.
- `config/jetstream.php` sets `guard => 'sanctum'`, but **laravel/sanctum is not installed**.

**Login (Fortify, guard `web`, username `email`, home `/dashboard`):**
- `POST /login` is throttled to 5 per minute per email+IP. `two-factor` is throttled to 5 per minute per login session.
- There is no account lockout beyond this throttle.
- Inertia views: `auth/login`, `auth/forgot-password`, `auth/reset-password`, `auth/verify-email`, `auth/two-factor-challenge`, `auth/confirm-password`.

**Fortify features enabled:**
- `resetPasswords`: email reset link through the `password_reset_tokens` table.
- `emailVerification`.
- `twoFactorAuthentication`: TOTP with `confirm` and `confirmPassword` on. 2FA is **optional** for every user.

**Self-registration:**
- Fortify `registration` is **disabled**.
- `CreateNewUser`, `pages/auth/register.tsx` and `RegistrationTest` (skipped when disabled) still exist.

**Account creation and gates:**
- Users are created by an admin through `UserController@store`, with name, email and password (min 8, confirmed); `status` is set to `active`.
- `first_login` is not set on creation (it defaults to `false`), so `ForcePasswordChange` only fires when the flag is set manually.
- Password changes (`settings/password`) clear `first_login`.
- `EnsureUserIsActive` (alias `active`) logs out users whose status isn't `active`. Only the value `active` is referenced; no status enum exists.

**No OTP sign-up flow and no OTP tables exist.**

**Password policy:** in production, `Password::defaults()` = min 12, mixed case, letters, numbers, symbols, uncompromised. Outside production it is null. Admin-created users bypass it (`min:8`).

**Passport:**
- Password grant enabled.
- Access tokens expire in 60 minutes, refresh tokens in 30 days.
- No token scopes or audiences are defined.

**Authorization:**
- `Gate::before` grants `Super Admin` everything.
- Controllers use `permission:<name>` middleware via `HasMiddleware`.
- The `suspend user` permission is seeded but **not used** by any route or controller.

## 4. Admin panel (hand-built Inertia pages)

All pages sit behind `auth` + `active` + `verified` + `ForcePasswordChange`. The sidebar (`components/app-sidebar.tsx`) shows Dashboard, plus Users / Permissions / Roles when the user holds the matching permission.

| Page | Controller | What it does | Permission |
|---|---|---|---|
| `dashboard` | `DashboardController` | Empty placeholder page | logged in |
| `users/index` | `UserController@index` | Lists all users (no pagination) | view user |
| `users/show` | `@show` | User detail with roles | view user |
| `users/create` | `@create/@store` | Create user (name, email, password), status `active` | create user |
| `users/edit` | `@edit/@update` | Edit name/email, optional new password | update user |
| — | `@destroy` | **Hard-deletes** the user | delete user |
| `roles/index` | `RoleController@index` | Lists roles | view role |
| `roles/create`, `roles/edit` | `@create/@store`, `@edit/@update` | Create/rename role (guard `web`) | create role / update role |
| — | `@destroy` | Deletes role | delete role |
| `roles/permissions` | `@permissions/@assignPermissions` | Matrix of roles × permissions (grouped by last word), sync | view role / assign permissions |
| `roles/assign` | `@assignRolesPage/@assignRoles` | Assign roles to users, sync | view role / assign role |
| `settings/profile`, `password`, `appearance`, `two-factor` | `Settings\*` | Own profile, password, theme, 2FA setup; profile delete (**hard delete**) | logged in |

## 5. Routes

**Files:**
- `routes/web.php`: home, dashboard, users, roles.
- `routes/settings.php`: settings pages.
- `routes/api.php`: an empty `prefix('v1')` group with no middleware.
- `routes/console.php`: `inspire` only. There are **no scheduled tasks**.
- There is no separate admin route file. Health check: `/up`.

**All 67 registered routes (from `route:list`):**

- **App:** `GET /`, `GET dashboard`, `users` resource (7), `roles` resource without `show` (6), `GET|POST roles/permissions`, `GET|POST roles/assign`, `settings` redirect, `settings/profile` (GET, PATCH, DELETE), `settings/password` (GET, PUT), `settings/appearance`, `settings/two-factor`.
- **Fortify:**
  - `login` (GET, POST), `logout`.
  - `forgot-password` (GET, POST), `reset-password` (GET, POST).
  - `email/verify`, `email/verify/{id}/{hash}`, `email/verification-notification`.
  - `user/confirm-password` (GET, POST), `user/confirmed-password-status`.
  - `two-factor-challenge` (GET, POST).
  - `user/two-factor-authentication` (POST, DELETE), `user/confirmed-two-factor-authentication`, `user/two-factor-qr-code`, `user/two-factor-secret-key`, `user/two-factor-recovery-codes` (GET, POST).
- **Jetstream** (registered automatically, not used by the React UI):
  - `GET user/profile`. Its Inertia page `Profile/Show` doesn't exist in `resources/js/pages`.
  - `DELETE user` (delete the current user).
  - `DELETE user/other-browser-sessions`, `DELETE user/profile-photo`.
- **Passport:** `oauth/authorize` (GET, POST, DELETE), `oauth/token`, `oauth/token/refresh`, `oauth/device`, `oauth/device/code`, `oauth/device/authorize` (GET, POST, DELETE).
- **Resend:** `POST resend/webhook`.
- **Framework:** `storage/{path}` (GET, PUT; local disk serving), `up`.

**API:** there are no application API endpoints. The `/api/v1` group is empty, and there are no `/api/portal/*` or `/api/admin/*` routes.

## 6. Configuration

| Area | Current setting (`.env.example` / config default) |
|---|---|
| Queue | `database` driver (`jobs` table); `composer run dev` runs `queue:listen` |
| Cache | `database` store |
| Session | `database` driver, lifetime 120 min, `SESSION_ENCRYPT=false` |
| File storage | default disk `local`; disks `local`, `public`, `s3` (unused). No R2-specific disk |
| Mail | default mailer `log`; a `resend` mailer exists in `config/mail.php`, with the key read from `RESEND_API_KEY` in `config/services.php`; from address placeholder |
| Broadcasting | `log` |
| Localisation | `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en`, timezone `UTC`. **No `lang/` directory**; no Somali or other translation files; UI strings are hard-coded English |
| Testing (`phpunit.xml`) | MySQL `fertitrack_testing`, cache/session `array`, queue `sync`, mail `array`, `BCRYPT_ROUNDS=4` |

**Env variable names in `.env.example`:**
- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`, `APP_MAINTENANCE_DRIVER`
- `BCRYPT_ROUNDS`
- `LOG_CHANNEL`, `LOG_STACK`, `LOG_DEPRECATIONS_CHANNEL`, `LOG_LEVEL`
- `DB_CONNECTION`
- `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`, `SESSION_DOMAIN`
- `BROADCAST_CONNECTION`, `FILESYSTEM_DISK`, `QUEUE_CONNECTION`, `CACHE_STORE`
- `MEMCACHED_HOST`, `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`
- `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`
- `VITE_APP_NAME`
- `PASSPORT_PASSWORD_CLIENT_ID`, `PASSPORT_PASSWORD_CLIENT_SECRET`

**Read by config but missing from `.env.example`:**
- `RESEND_API_KEY`
- `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY`, `PASSPORT_CONNECTION`
- `AUTH_GUARD`, `AUTH_PASSWORD_BROKER`, `AUTH_MODEL`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

`env()` is used only inside `config/`.

## 7. Tests, CI, Docker and deployment

**Tests** (Pest 3; `tests/Pest.php` applies `RefreshDatabase` to `Feature`): 45 tests in 14 files.

| Area | Files (test count) |
|---|---|
| Auth | Authentication (6), EmailVerification (6), PasswordConfirmation (2), PasswordReset (5), Registration (2, skipped while registration is disabled), TwoFactorChallenge (2), VerificationNotification (2) |
| Settings | PasswordUpdate (3), ProfileUpdate (5), TwoFactorAuthentication (4) |
| Users | AccountAccess (4): inactive accounts logged out, first-login redirect, permission denial, Super Admin access |
| Other | Dashboard (2), Feature/Example (1), Unit/Example (1) |

**Not present:**
- CI: no `.github/` and no other CI config.
- Docker: no `Dockerfile` or `docker-compose`/`compose` file. Laravel Sail is installed as a dev dependency but not configured.
- Deployment: no scripts, Procfile, or DigitalOcean/Cloudflare config.
- Other root files:
  - `.rnd`: an OpenSSL random seed file, committed.
  - `boost.json`, `skills-lock.json`: Laravel Boost.
  - `CLAUDE.md`: guidance for this base app.

## 8. Folder structure (top 3 levels)

Generated directories (`resources/js/routes/`, `resources/js/wayfinder/`, ignored `resources/js/actions/`) and shadcn `components/ui/` are shown collapsed.

```
app/
├── Actions/Fortify/            CreateNewUser.php, ResetUserPassword.php
├── Concerns/                   PasswordValidationRules.php, ProfileValidationRules.php
├── Http/
│   ├── Controllers/            Controller, DashboardController, RoleController, UserController, Settings/
│   ├── Middleware/             EnsureUserIsActive, ForcePasswordChange, HandleAppearance, HandleInertiaRequests
│   └── Requests/               Assign{Permissions,Role}Request, {Store,Update}{User,Role}Request, Settings/
├── Models/                     User.php
└── Providers/                  AppServiceProvider.php, FortifyServiceProvider.php

routes/                         api.php, console.php, settings.php, web.php

resources/
├── css/                        app.css
├── views/                      app.blade.php
└── js/
    ├── app.tsx, ssr.tsx
    ├── components/             app shell, sidebar, nav, 2FA modal, alerts … + ui/ (shadcn)
    ├── hooks/                  use-appearance, use-two-factor-auth, use-mobile …
    ├── layouts/                app-layout, auth-layout, app/, auth/, settings/
    ├── lib/                    utils.ts
    ├── pages/                  dashboard.tsx, welcome.tsx, auth/, roles/, settings/, users/
    ├── routes/                 (Wayfinder generated)
    ├── types/                  auth, navigation, role, ui, global.d.ts …
    └── wayfinder/              (Wayfinder generated)

database/
├── factories/                  UserFactory.php
├── migrations/                 10 migrations (see §2)
└── seeders/                    DatabaseSeeder, RolesAndPermissionsSeeder, SuperAdminSeeder
```

## 9. Conflicts and gaps against the decisions log

These are listed only, not resolved. "Conflict" means the code contradicts a Confirmed decision. "Gap" means the decision isn't implemented yet.

| # | Decision | Codebase today | Type |
|---|---|---|---|
| 1 | **D-036** Separate account tables for company users and staff; separate sessions and token audiences; `/api/portal/*` and `/api/admin/*` with own middleware; company tokens rejected by admin endpoints | One `users` table and one `User` model for everyone. `web` and `api` guards share the `users` provider. One session cookie. Passport tokens have no scopes or audiences. No `/api/portal` or `/api/admin` routes; the only API group is an empty `/api/v1`. `User::$guard_name = 'web'` deliberately merges API and web permission resolution. | Conflict |
| 2 | **D-031** One domain, separated by login; staff must use mandatory 2FA | One login page (`/login`) for all users. 2FA is optional for every user; nothing forces staff to enable it. | Conflict |
| 3 | **D-037** Staff 2FA uses an authenticator app (TOTP) | Fortify TOTP 2FA with recovery codes exists and matches the method, but it's optional and not limited to staff. | Partial (method matches, enforcement missing) |
| 4 | **D-030** Email only, through Resend from a saris.gov.so address | `resend/resend-laravel` is installed and a `resend` mailer is configured, but the default mailer is `log`. `RESEND_API_KEY` is missing from `.env.example`, and the from address is the `hello@example.com` placeholder. No SMS code exists, which matches. | Gap (configuration) |
| 5 | **D-040** Files in a private Cloudflare R2 bucket via the backend; client-side image compression | Default disk is `local`. There is no R2 disk (only the generic, unused `s3` disk). The framework's `storage/{path}` serving route for the local disk is registered. No upload or compression code exists. | Gap |
| 6 | **D-034** Nothing is hard-deleted; soft deletes only | `UserController@destroy`, `Settings\ProfileController@destroy` and Jetstream's `DELETE /user` hard-delete users. `RoleController@destroy` hard-deletes roles. No model uses `SoftDeletes`. | Conflict |
| 7 | **D-001** One account per company (1:1) | No `Company` or `CompanyUser` entity. Users are standalone and admin-created. | Gap |
| 8 | Sign-up flow (CLAUDE.md summary): Full Name, Email, Phone → emailed OTP → login → forced password change → company profile | Self-registration is disabled. No OTP generation, storage or expiry. Admin-created users don't get `first_login = true`, so the forced change never fires automatically. `phone` exists on `users` but isn't in the create/edit forms. | Gap |
| 9 | **D-023** Staff roles (Pesticide Registrar, Technical Reviewer, Inspector, Departmental Director, Payment Officer, Director General, Licensing/Records Officer) | Only the `Super Admin` role is seeded. Roles can be created freely in the UI, and `Gate::before` gives `Super Admin` unrestricted access. Super Admin isn't in the design. | Gap / possible conflict |
| 10 | **D-032** UI in Somali and English | No `lang/` directory and no i18n library on the frontend; all strings are hard-coded English. | Gap |
| 11 | CLAUDE.md security defaults: account lockout, audit logging for all workflow actions | Login is throttled at 5/min, but there is no lockout after repeated failures. No audit log table or package. Role and permission changes aren't logged. | Gap |
| 12 | **D-033** PHP/Laravel on DigitalOcean behind Cloudflare | Laravel matches. No deployment, Docker or CI files. Default DB is SQLite and tests use MySQL; no production engine is chosen. | Gap |
| 13 | **D-003** Account recovery request on the login page | Only the standard Fortify email reset exists. No `AccountRecoveryRequest`. | Gap |
| 14 | General: no admin panel package | Fits the design so far (none prescribed). Jetstream is installed but its `guard` is `sanctum`, which isn't installed. Its routes (`user/profile`, `DELETE user`, `other-browser-sessions`, `profile-photo`) are live, and `user/profile` points at a page that doesn't exist. | Risk to review |
| 15 | Passwords (CLAUDE.md: strong policy) | `Password::defaults()` is strong only in production. Admin-created and admin-edited users use `min:8` and bypass it. | Gap |

No SARIS domain entities (Company, Application, License, Product, Premises, Person, Payment, Workflow) exist. Every Phase 1 decision from D-005 to D-046 that concerns these entities is therefore unimplemented.
