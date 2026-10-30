# Plan: Phase 1 — Authentication for both portals

- Status: Approved (2026-10-29)
- Branch: `claude/funny-franklin-ikvmdt`
- Date: 2026-10-29
- Delivery: in one go with Phase 2 (company profile), on one branch, as one pull request (D-163). Phase 2 has its own plan.

## 1. Scope

**Goal (09 §3, Phase 1):** staff and companies can each sign in to their own portal only, safely. A company can sign up and reach an empty company dashboard; a staff member can be created, set a password, enrol 2FA and reach the admin dashboard.

**In scope (the Phase 1 table, all rows):**

| # | Row | Decisions |
|---|---|---|
| A | `company_users` table and model, `company` guard, `company_users` password broker | D-048 |
| B | Route files `admin.php`, `portal.php`, `public.php`; `/admin/*` and `/portal/*`; `AdminLayout`, `PortalLayout`, `PublicLayout`; staff pages and URLs move to `/admin` | D-048, D-158 |
| C | `SetAuthArea` middleware before `StartSession`; Fortify routes registered per area; separate session cookies | D-062, D-079 |
| D | Company sign-up with a 1-hour temporary password, "send me a new temporary password", honeypot and minimum fill time, identical responses | D-063, D-080 |
| E | ForcePasswordChange for both realms; password history (last 5); 90-day expiry; reminder 14 days before | D-082 |
| F | Lockout after 5 failures for 15 minutes, with email; System Administrator unlocks staff; password reset unlocks companies | D-081 |
| G | Mandatory staff 2FA; optional company 2FA; staff 2FA reset by the System Administrator | D-052, D-064, D-087 |
| H | Staff creation with an emailed temporary password (24 hours); the administrator never sets a password | D-083 |
| I | Session timeouts (15 min staff, 30 min company), staff 12-hour limit, no "remember me", password-confirmation windows (15 / 30 min) | D-084, D-085 |
| J | Company login-email change with confirmation link and notice to the old address | D-093 |
| K | Auth emails in English and Somali through Resend, queued after commit, logged in `notification_logs` | D-118, D-125–D-130, 07 §3.1 |

Also included because Phase 1 depends on them:
- Fonts served by the app itself, so the CSP allows no external font host (D-157).
- Language choice: the UI follows the user's `preferred_locale`; guests can switch between English and Somali (D-032).

**Out of scope (later phases, per 09):**
- Company profile, persons, premises, documents and reference data (Phase 2).
  - "Company registration" in Phase 1 means the account sign-up; the company profile itself is Phase 2.
  - After sign-up the company lands on an empty dashboard.
- Account recovery (Phase 5).
- The 90-day "unused account" review list (03 §5.2).
- Signature images and staff titles (Phase 4B).
- Any workflow or application feature.

## 2. Design references

- 01 §4 (portal separation)
- 02 §4.1 (User, CompanyUser, PasswordHistory) and §4.12 (NotificationLog)
- 03 (all, v1.3), especially §2–§9, §13–§15
- 05 §2, §3.1–§3.3 (field rules)
- 07 §1, §2, §3.1, §4 (notifications)
- 08 §2, §4–§7 (env, conventions, translations, tests)
- 09 §3 Phase 1 (table, "Done when", prompt)
- Decisions D-048, D-052, D-062–D-064, D-076, D-079–D-085, D-087, D-093, D-118, D-125–D-130, D-133, D-157, D-158

## 3. Target behaviour (summary)

| | Staff (Admin Portal) | Company (Company Portal) |
|---|---|---|
| Table / guard / broker | `users` / `web` / `users` | `company_users` / `company` / `company_users` |
| Auth pages | `/admin/login`, `/admin/forgot-password`, `/admin/reset-password/{token}`, `/admin/two-factor-challenge` | `/login`, `/signup`, `/temporary-password`, `/forgot-password`, `/reset-password/{token}`, `/two-factor-challenge` |
| App pages | `/admin` (dashboard), `/admin/users…`, `/admin/roles…`, `/admin/settings/…` | `/portal` (empty dashboard), `/portal/settings/…` (password, 2FA, login email, language) |
| Session cookie, idle timeout | `saris_admin_session`, 15 min, 12 h absolute | `saris_portal_session`, 30 min |
| 2FA | Mandatory before any admin page | Optional |
| Public | `/` (landing page with links to both logins), `/up` | |

## 4. Steps

Each step is one or more commits on `feature/phase-1-auth`; the tests for a step are written with it.

### Step 1: Data model (rows A, E, F, H, J, K)

**Migrations (new):**

1. `create_company_users_table` (02 §4.1):
   - `id`, `company_id` (nullable unsigned bigint, unique, no FK yet: `companies` arrives in Phase 2, which adds the constraint), `full_name` string(150), `email` string(190) unique, `phone` string(30)
   - `password`, `temp_password_expires_at` datetime null, `first_login` bool default true, `email_verified_at` null
   - `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`
   - `status` string default `ACTIVE`, `failed_login_attempts` uint default 0, `locked_until` null, `last_login_at` null, `password_changed_at` null
   - `pending_email` string(190) null, `pending_email_token_hash` string(64) null, `pending_email_expires_at` null
   - `preferred_locale` string(2) default `en`, `terms_accepted_at` datetime
   - `remember_token` (kept for Fortify compatibility, never used), timestamps, soft deletes
2. `add_security_columns_to_users_table` (02 §4.1, D-125, D-083):
   - `failed_login_attempts`, `locked_until`, `last_login_at`, `password_changed_at`, `preferred_locale` (default `en`)
   - `temp_password_expires_at` (see Proposed D-164)
   - widen `name` to string(150) and `email` to string(190), restating all their attributes
3. `create_company_user_password_reset_tokens_table`: the second password broker's token table (same shape as `password_reset_tokens`).
4. `create_password_histories_table` (02 §4.1): `account_type`, `account_id` (morph, indexed), `password_hash`, `created_at`.
5. `create_notification_logs_table` (02 §4.12): as listed there, with timestamps.

**Models and enums:**
- `App\Models\CompanyUser`: `Authenticatable`, `TwoFactorAuthenticatable`, `Notifiable`, `SoftDeletes`, `Audited` (masks nothing; hidden password and 2FA fields are never logged); `AccountStatus` cast; `$fillable`.
- `App\Models\PasswordHistory`; `App\Models\NotificationLog`.
- Shared concern `App\Domain\Identity\Concerns\HasSecurityState` used by `User` and `CompanyUser`: lockout, password-expiry and temporary-password helpers.
- Factories for every new model.

**Config:**
- `config/auth.php`: guard `company` (session, provider `company_users`), provider `company_users` (Eloquent `CompanyUser`), broker `company_users` (table from migration 3, expire 60, throttle 60).
- `config/identity.php` (new): temporary-password validity (company 60 min, staff 24 h), lockout (5 attempts, 15 min), password expiry (90 days, reminder 14 days, history 5), session timeouts (15 / 30 min, staff absolute 12 h), confirmation windows (15 / 30 min), honeypot minimum fill time (3 s). This follows 08 §4.2 rule 12: no hard-coded durations.

### Step 2: Areas, routes and layouts (rows B, C)

- **`App\Domain\Identity\Enums\AuthArea`** (`Admin`, `Company`), with the guard, broker, home, session cookie, idle timeout and confirmation window per area.
- **`SetAuthArea` middleware** (`set.area:admin` / `set.area:company`). For the current request only, it sets:
  - `fortify.guard`, `fortify.passwords`, `fortify.home`
  - `session.cookie`, `session.lifetime`
  - `auth.defaults.guard`, `auth.defaults.passwords`
  - `auth.password_timeout`
  - the request attribute `auth_area`
- **Middleware groups** in `bootstrap/app.php`:
  - `admin` and `company` groups, each starting with `SetAuthArea` and then the standard web stack (cookies, session, CSRF, Inertia, security headers, CSP).
  - `SetAuthArea` is placed in the middleware priority list **before** `StartSession`, so Laravel never reorders it after the session starts (03 §3 step 4).
- **Route files** (registered in `bootstrap/app.php`; `routes/web.php` and `routes/settings.php` are removed):
  - `routes/admin.php`, `prefix('admin')`, group `admin`:
    - Fortify auth routes;
    - then `auth:web`, `active`, `password.current`, `two-factor.enforced`, `session.absolute`;
    - dashboard, users, roles and settings (the current staff pages, moved per D-158).
  - `routes/portal.php`, group `company`:
    - company auth routes at the root (`/login`, `/signup`, …);
    - `prefix('portal')` with `auth:company`, `active`, `password.current`: dashboard and settings.
  - `routes/public.php`: `/` landing page; `/locale/{locale}` language switch. `/verify/{code}` comes in Phase 4B.
- **Fortify:**
  - `Fortify::ignoreRoutes()` in `FortifyServiceProvider`.
  - A small `App\Domain\Identity\Support\FortifyRoutes::register()` registers Fortify's own controllers (login, logout, forgot and reset password, two-factor challenge, confirm password, two-factor enable/confirm/disable/QR/recovery codes, password update) inside each route file, with area-specific route names (`admin.login`, `portal.login`, …).
  - The Fortify view callbacks render `admin/auth/*` or `portal/auth/*` pages from the request's area.
  - Fortify's `registration` and `emailVerification` features stay off (see Proposed D-165).
- **Inertia pages:**
  - Move to `resources/js/pages/{admin,portal,public}`; `AppLayout` becomes `AdminLayout`; new `PortalLayout` and `PublicLayout` (mobile-first, 360 px).
  - The sidebar and settings navigation use the new Wayfinder route names.
  - All visible text uses `t()`, with keys added to `lang/en.json` and `lang/so.json` (English placeholders, D-154).
- **Shared props** (`HandleInertiaRequests`): `auth.user` is the user of the current area's guard only; `auth.permissions` is for staff only; `area`, `locale` and `translations` are added.
- Inertia pages are already loaded lazily per page (01 §4 rule 6).

### Step 3: Login protection (rows F, I)

- **Lockout (D-081):** custom Fortify login pipeline (`Fortify::authenticateThrough`), with:
  1. `EnsureLoginIsNotThrottled` (Fortify);
  2. **`RejectLockedAccount`**: if `locked_until` is in the future, fail with the generic message, but only after the correct password, as 03 §7 says;
  3. **`AttemptToAuthenticate`** (Fortify) wrapped by **`RecordLoginAttempt`**:
     - a failure increments `failed_login_attempts`; at 5 it sets `locked_until = now + 15 min`, sends `ACCOUNT_LOCKED` and writes an audit entry;
     - a success resets the counter, sets `last_login_at` and writes an audit entry;
  4. **`RejectExpiredTemporaryPassword`** (company and staff: `temp_password_expires_at` in the past means the generic failure);
  5. `RedirectIfTwoFactorAuthenticatable`;
  6. `PrepareAuthenticatedSession`.
  - The error is always "These credentials do not match our records" (03 §7).
- **Rate-limiter keys** include the area: `login` uses `{area}|{email}|{ip}`, and `two-factor` uses `{area}|{login.id}` (03 §3 step 5).
- **Unlock:** a completed password reset clears `locked_until` for companies; a "Unlock" action on the staff user page (permission `unlock account`) clears it for staff.
- **Sessions (D-084):**
  - Encrypted payload, `Secure`, `HttpOnly`, `SameSite=Lax`.
  - Idle timeout per area through `session.lifetime`, set in `SetAuthArea`.
  - **`EnforceAbsoluteSessionLimit`** (`session.absolute`) logs staff out 12 hours after login, from a timestamp stored at login.
  - "Remember me" is removed from the login pages and ignored by the pipeline.
- **Password confirmation (D-085):** `password.confirm` uses the area's window (15 or 30 min). Phase 1 applies it to role changes, user creation and deactivation, unlock, 2FA reset and 2FA changes. Later phases add the workflow actions.

### Step 4: Passwords (rows E, H)

- **`App\Domain\Identity\Actions\ChangePassword`**: used by the settings page, the forced first-login change and Fortify's `ResetUserPassword`. It:
  - validates the D-082 policy and **rejects any of the last 5 passwords** (`password_histories` plus the current one);
  - saves the new hash, sets `password_changed_at`, clears `first_login` and `temp_password_expires_at`, and clears the lockout;
  - records the history row;
  - ends the account's other sessions (03 §6);
  - sends `PASSWORD_CHANGED` and writes an audit entry.
- **`EnsurePasswordIsCurrent`** (`password.current`) replaces `ForcePasswordChange`:
  - It sends the user to the area's password page while `first_login` is true or `password_changed_at` is older than 90 days.
  - Allowed routes: the password page, logout, and staff 2FA set-up.
- **Temporary passwords (03 §4):** `App\Domain\Identity\Support\TemporaryPassword::generate()` creates 12 characters from letters and digits without look-alikes (`0 O o 1 l I`), using a cryptographic random source. Only the hash is stored.
- **Staff creation (D-083):**
  - `CreateStaffUser` action: name, email, phone, preferred language and roles (05 §3.3); no password fields on the form.
  - It creates the user with a random unusable password replaced by a temporary one, `temp_password_expires_at = now + 24 h` and `first_login = true`, then sends `STAFF_TEMP_PASSWORD`.
  - The staff edit form loses its password fields.
  - A "Send a new temporary password" button for the System Administrator re-issues it.
- **Password expiry reminder:** `php artisan identity:password-expiry-reminders` (daily in `routes/console.php`) sends `PASSWORD_EXPIRY_REMINDER` 14 days before expiry, once per cycle.

### Step 5: Company sign-up and first login (row D)

- **`/signup`** (Public area, `company` group), `SignUpRequest`:
  - Full name `name`, email `email`, phone `phone` (05 §2, §3.1), consent checkbox.
  - Normalised first (D-076): trimmed; email lowercased; phone to `+252…` when typed without a country code.
  - Protected by `spatie/laravel-honeypot` (hidden field plus 3-second minimum, D-080) and the `signup` rate limiter (3 per hour per IP).
- **`RegisterCompanyUser` action** (03 §4 diagram):
  - **Email already belongs to an activated account** (has logged in): send `COMPANY_ACCOUNT_EXISTS`, change nothing.
  - **New or never-activated email:** create or refresh the `CompanyUser` with a new temporary password, `temp_password_expires_at = now + 60 min`, `first_login = true`, `terms_accepted_at = now`, `preferred_locale` = current locale. Send `COMPANY_TEMP_PASSWORD`.
  - **Either way** the page shows the same "Check your email" message (03 §15 item 9).
- **`/temporary-password`** ("Send me a new temporary password"):
  - Email field, honeypot, `temporary-password` limiter (3 per hour per email).
  - Only for accounts that have never logged in; a new password invalidates the previous one.
  - Same response in every case.
- **Forgot password (both areas):** Fortify's controller with the area's broker, plus honeypot and the `forgot-password` limiter (3 per hour per email). The same response is always given, whether or not the email exists.
- **First login:** a successful login with an unexpired temporary password sets `email_verified_at` (receiving the email proves the address, D-063), then `password.current` sends the company to set a new password, then to the empty `/portal` dashboard.

### Step 6: Two-factor authentication (row G)

- **Staff (D-052):**
  - **`EnsureTwoFactorEnabled`** (`two-factor.enforced`) on every `/admin/*` page sends staff to `/admin/settings/two-factor` until `two_factor_confirmed_at` is set. Only the 2FA set-up, password and logout routes are allowed before that.
  - Staff cannot disable 2FA; the disable route is not registered in `admin.php`.
- **Company (D-064):** optional set-up and disable in `/portal/settings/two-factor`, after password confirmation.
- **Staff 2FA reset (D-087):**
  - "Reset 2FA" on the staff user page, with permission `reset two factor` and password confirmation.
  - It clears the secret, recovery codes and confirmation, ends the user's sessions, sends `TWO_FACTOR_CHANGED` and writes an audit entry.
  - The user must enrol again at next login.
- **Recovery codes:** 8 single-use codes (Fortify's default count).
- `TWO_FACTOR_CHANGED` is sent on enable, disable and reset.

### Step 7: Company login-email change (row J)

- **`/portal/settings/email`**, after password confirmation, `ChangeLoginEmailRequest` (05 §3.2: valid email, not used by another company account).
- **`RequestLoginEmailChange` action:**
  - stores `pending_email`, the SHA-256 hash of a random token, and `pending_email_expires_at = now + 60 min`;
  - sends `EMAIL_CHANGE_CONFIRM` (signed link) to the new address and `EMAIL_CHANGE_NOTICE` to the old address.
- **`/portal/settings/email/confirm/{token}`**: a valid, unexpired token swaps the email, clears the pending fields and writes an audit entry. The email changes only when the link is used.

### Step 8: Notifications (row K)

- **Module `app/Domain/Notifications`:**
  - **`NotificationCode`** enum: the Phase 1 codes from 07 §3.1, namely
    - `COMPANY_TEMP_PASSWORD`, `COMPANY_ACCOUNT_EXISTS`, `STAFF_TEMP_PASSWORD`
    - `PASSWORD_RESET_LINK`, `PASSWORD_CHANGED`, `PASSWORD_EXPIRY_REMINDER`
    - `ACCOUNT_LOCKED`, `TWO_FACTOR_CHANGED`
    - `EMAIL_CHANGE_CONFIRM`, `EMAIL_CHANGE_NOTICE`
  - **`SendNotification` action:**
    - writes a `QUEUED` `notification_logs` row;
    - queues the Mailable `afterCommit`, with 3 tries and back-off;
    - marks the row `SENT` (with the Resend message ID when available) or `FAILED` with the error.
    - The body is never stored (02 §4.12).
  - **One Mailable per code** in `app/Domain/Notifications/Mail/`, sharing a layout `resources/views/mail/layout.blade.php` plus `{code}.blade.php` and `{code}-text.blade.php` (HTML and plain text, D-130).
- **Format:**
  - Subject: `SARIS — {subject} — {reference}`.
  - Body: the preferred language first, the other below a divider (D-125). See Proposed D-167 for what happens while the Somali text is still a placeholder.
  - Footer from 07 §1: a "do not reply" line and SARIS contact details, held in `config/identity.php` for now; `system_settings` arrives in Phase 4B.
- **Fortify's reset link** is sent through the same path: a custom `ResetPassword` notification on both models, with an area-correct URL.
- **Failed sends:** an admin page "Failed emails" (permission `manage system settings`) lists `FAILED` rows (D-129).
- **Mail driver:** `resend` in production, `log` locally, `array` in tests.

### Step 9: Language, fonts and CSP (D-032, D-157)

- **`SetLocale` middleware:** the logged-in user's `preferred_locale`; otherwise a `locale` cookie set by `/locale/{locale}` (`en` or `so` only); otherwise `en`. A language switch appears in all three layouts.
- **Fonts:** self-host DM Sans, Fraunces and Instrument Sans through `@fontsource/dm-sans`, `@fontsource/fraunces` and `@fontsource/instrument-sans` (Proposed D-168).
  - Remove the `fonts.bunny.net` link from `app.blade.php`.
  - Remove the font host from `SarisPolicy`, so the CSP allows fonts only from `'self'`.

### Step 10: Admin pages kept working under `/admin`

- Users, roles and permissions screens work as before at `/admin/...`. Changes:
  - Create and edit forms follow 05 §3.3 (preferred language; no password).
  - New actions: send a new temporary password, unlock, reset 2FA.
- The old `/dashboard`, `/users`, `/roles` and `/settings/*` URLs are removed. Nothing is deployed, so no redirects are needed.

### Step 11: Docs, checks, push

- Update CLAUDE.md:
  - Architecture: areas, route files, middleware, guards, notifications;
  - Notes: `identity:password-expiry-reminders`.
- Update the existing auth and settings tests for the new URLs; keep them all.
- Run `migrate:fresh --seed` and every `composer run check` step that can run here (Larastan cannot). Push the branch and open one pull request, without merging.

## 5. Files (main ones)

| Area | New | Changed / moved | Removed |
|---|---|---|---|
| Migrations | 5 listed in Step 1 | — | — |
| Models | `CompanyUser`, `PasswordHistory`, `NotificationLog` (+ factories) | `User` (concern, casts, notification override) | — |
| Identity | `Enums/AuthArea`, `Concerns/HasSecurityState`, `Actions/{RegisterCompanyUser, IssueTemporaryPassword, CreateStaffUser, ChangePassword, RequestLoginEmailChange, ConfirmLoginEmailChange, UnlockAccount, ResetTwoFactor}`, `Support/{TemporaryPassword, FortifyRoutes}`, `Pipeline/{RejectLockedAccount, RecordLoginAttempt, RejectExpiredTemporaryPassword}` | `Actions/DeactivateStaffUser` (unchanged) | — |
| Notifications | `Enums/NotificationCode`, `Actions/SendNotification`, `Mail/*` (10 Mailables), `Notifications/ResetPasswordNotification` | — | — |
| Middleware | `SetAuthArea`, `EnsurePasswordIsCurrent`, `EnsureTwoFactorEnabled`, `EnforceAbsoluteSessionLimit`, `SetLocale` | `HandleInertiaRequests` | `ForcePasswordChange` (replaced) |
| Controllers | `Portal/{DashboardController, Auth/SignUpController, Auth/TemporaryPasswordController, Settings/*}`, `Admin/FailedEmailController`, `Public/{HomeController, LocaleController}` | `Admin/*` (moved routes, new actions) | — |
| Requests | `Portal/Auth/{SignUpRequest, TemporaryPasswordRequest}`, `Portal/Settings/ChangeLoginEmailRequest` | `Admin/{StoreUserRequest, UpdateUserRequest}` | — |
| Routes | `routes/admin.php`, `routes/portal.php`, `routes/public.php` | `routes/console.php`, `bootstrap/app.php` | `routes/web.php`, `routes/settings.php` |
| Config | `config/identity.php` | `auth.php`, `session.php`, `fortify.php`, `csp.php` via `SarisPolicy` | — |
| Frontend | `layouts/{admin,portal,public}-layout.tsx`, `pages/portal/**`, `pages/public/**`, language switch | `pages/admin/**` (moved from `auth`, `users`, `roles`, `settings`, `dashboard`) | `pages/auth/register.tsx`, `pages/auth/verify-email.tsx` |
| Mail views | `resources/views/mail/layout.blade.php` + per-code HTML and text | `app.blade.php` (fonts) | — |
| Lang | keys in `lang/en.json`, `lang/so.json` | — | — |
| Tests | see §7 | existing auth/settings tests (new URLs) | `RegistrationTest`, `EmailVerificationTest`, `VerificationNotificationTest` (see §7 note) |

## 6. Migrations

- All are new; none edits a merged migration.
- `company_users.company_id` has no foreign key until Phase 2 creates `companies`. Phase 2 adds the constraint in its own migration.
- The `users` column changes restate every existing attribute (CLAUDE.md rule).
- Rollback (`down`) drops what each migration adds.

## 7. Tests

**Required by 09 Phase 1 (03 §15 items 1, 2, 6, 7, 8, 9):**

| # | Test |
|---|---|
| 1 | A staff account cannot log in at `/login`; a company account cannot log in at `/admin/login` |
| 2 | A company session gets a redirect to `/login` (or 403) on every `/admin/*` route; a staff session gets the same on every `/portal/*` route. The test loops over the real route list, so new routes are covered automatically |
| 6 | An expired temporary password is rejected; a new temporary password invalidates the previous one |
| 7 | Lockout after 5 failures; unlocked after 15 minutes (time travel) or after a password reset (company) or by the System Administrator (staff); `ACCOUNT_LOCKED` sent once |
| 8 | Staff without confirmed 2FA cannot open any admin page except 2FA set-up, password and logout |
| 9 | Sign-up, temporary-password and forgot-password responses are identical (status, page, props, timing-independent) for existing and unknown emails |

**Also from the design:**
- Password reset links from one broker don't work in the other area (03 §3).
- Logging in to one area doesn't log the browser in or out of the other: two cookies, both sessions alive (03 §3, "Done when").
- `SetAuthArea` runs before `StartSession` (each area sets its own cookie name).
- Rate limiter keys are separate per area.
- Password history: the last 5 passwords are rejected and the 6th is accepted; expiry after 90 days forces a change; the reminder is sent 14 days before, once.
- A password change ends other sessions and sends `PASSWORD_CHANGED`.
- Idle timeouts 15 / 30 minutes; staff absolute limit 12 hours; there is no remember-me cookie.
- Password confirmation windows: 15 minutes staff, 30 minutes company.
- Staff creation: no password field, a temporary password valid 24 hours, forced change, then 2FA set-up.
- Staff can't disable 2FA; the System Administrator 2FA reset works and is audited.
- Login-email change: the change applies only on the link, the notice goes to the old address, an expired or reused token fails, and an email used by another company is rejected.
- Honeypot: a filled hidden field or a submission under 3 seconds is refused silently (same response).
- Sign-up normalisation (email lowercase, `+252` phone) and 05 §3.1 field rules.
- Notifications: each Phase 1 code is queued after commit; nothing is sent when the transaction rolls back; a log row has no body; it is `FAILED` after 3 tries and shown on the admin page; the subject format is right; both languages appear in the right order.
- The CSP has no external font host, and the pages load fonts from `'self'`.
- Locale: a guest switch, the user's preference, and unknown locales refused.
- Audit entries for login success and failure, lockout, temporary password sent, password change and reset, 2FA enabled/disabled/reset, and email change (03 §13).

**"Done when" end-to-end tests:**
- A new company signs up, logs in with the emailed temporary password, sets a password and reaches the empty company dashboard.
- A new staff member receives a temporary password, sets a password, enrols 2FA (TOTP code generated in the test) and reaches the admin dashboard.

**Existing tests:**
- The auth, settings, users and roles tests move to the new URLs and guards.
- `RegistrationTest` (always skipped; Fortify registration stays off) and `EmailVerificationTest` / `VerificationNotificationTest` test features that Phase 1 removes (Proposed D-165). Their behaviour is replaced by the sign-up and first-login tests above. **Approving this plan approves removing these three test files.**

## 8. Risks

1. **Fortify with two guards** is the riskiest part.
   - Fortify reads `fortify.guard` and `fortify.passwords` when its controllers and actions are resolved, so `SetAuthArea` sets them before routing reaches Fortify.
   - Fortify binds `StatefulGuard` per resolution, not once, so per-request config works.
   - The cross-portal tests (§7 items 1, 2 and the broker test) prove it.
   - If a Fortify internal caches the guard, I stop and report rather than build a custom login (D-062).
2. **Session cookie before session start:** Laravel's middleware priority could reorder `SetAuthArea` after `StartSession`. It is added to the priority list explicitly, and a test checks the cookie name per area.
3. **Large change in one pull request** (your choice): harder to review. I'll keep commits small, one per step, so the history reads step by step.
4. **Email delivery:** Resend isn't reachable from this sandbox. Tests use `Mail::fake()` and the `array` driver; real delivery is checked on staging (Phase 5).
5. **Breached-password check** (HIBP API) isn't called in tests (D-155); outside tests it needs outbound HTTPS from the server (03 assumption).
6. **Somali text:** all new keys get English placeholders (D-154); the translation report grows accordingly.
7. **Larastan** still can't run here; CI must run it (open question 16).

## 9. Decisions (confirmed 2026-10-29)

| ID | Proposal | Why |
|---|---|---|
| D-163 | Phases 1 and 2 are delivered in one go: one branch, one pull request, small commits per step. This departs from 09's "one pull request per row group" (D-121). | Your instruction. |
| D-164 | `users` gets a `temp_password_expires_at` column (not in 02 §4.1) to enforce the 24-hour staff temporary password (D-083). | D-083 needs it; 02 lists it only for company users. |
| D-165 | Fortify's `registration` and `emailVerification` features stay off for both portals. Sign-up is the custom `/signup` flow, and the email address is verified by the first successful login with the emailed temporary password (D-063). Staff use the same rule, since their temporary password is emailed too. The `verified` middleware and the verification pages are removed. | 03 §4 replaces both features. |
| D-166 | Company auth pages sit at the root (`/login`, `/signup`, …) and company app pages under `/portal`, as in 03 §2. `/` is a public landing page with links to the company login and the staff login. | 03 §2 gives the URLs; the landing page is not specified. |
| D-167 | While a Somali line still equals the English placeholder (D-154), an email shows that text once instead of twice. Once real Somali text exists, both languages are shown as D-125 requires. | Avoids every email repeating the same English text. |
| D-168 | The fonts are self-hosted with `@fontsource/dm-sans`, `@fontsource/fraunces` and `@fontsource/instrument-sans` (npm). | Implements D-157 with maintained packages (new dependencies need a decision). |
| D-169 | Guests choose the language through `/locale/{en,so}`, stored in a `locale` cookie; logged-in users use `preferred_locale`, which the language switch also updates. | D-032 says the user can switch; the mechanism is not specified. |
| D-170 | The email footer contact details and "do not reply" text live in `config/identity.php` until `system_settings` exists (Phase 4B), then move there. | 07 assumes settings; the table arrives later. |
| D-171 | Permission names for the new admin actions: unlocking uses `unlock account`, 2FA reset uses `reset two factor`, re-sending a staff temporary password uses `create user`, and the failed-emails page uses `manage system settings`. No new permissions are added. | Reuses the confirmed D-149 permissions. |

## 10. Answered questions

1. Phase 2 (company profile) is delivered in the same go, under its own plan `docs/plans/phase-2-company-profile.md` (D-163).
2. `/` is a landing page with links to both logins (D-166 confirmed).

## 11. Implementation notes (2026-10-30)

Where the code differs from the plan above. None changes the scope; the new choices are logged as Proposed (D-172 to D-176) and wait for your confirmation.

| Plan said | Code does | Why |
|---|---|---|
| `EnforceAbsoluteSessionLimit` (`session.absolute`) | `EnforceSessionTimeouts` (`session.timeouts`) checks the idle timeout and the staff 12-hour limit | One middleware for both limits (D-174) |
| Sessions separated by cookie | Also a second session table, `company_sessions`, and the staff cookie path `/admin` | D-173 |
| `SetAuthArea` applies the area | `ApplyAuthArea` applies it when the route is matched and again in `SetAuthArea` | Fortify's controllers resolve the guard before route middleware (risk 1 in §8 happened; this is the fix, D-173) |
| Staff profile edits the email | Staff cannot change their own login email | D-172 |
| `/locale/{locale}` | `POST /locale/{en,so}` | A state change, so it is a POST with CSRF protection |
| Old tests kept, with new URLs | `tests/Feature/Auth/*` and `tests/Feature/Settings/*` were rewritten for two portals; the three tests named in §7 were removed | Every old test targeted the single-portal routes |
| — | Some UI libraries add `<style>` tags without the CSP nonce (open question 20) | Found in the browser check; not caused by Phase 1 |

**Result:** 208 tests pass. Pint, ESLint, TypeScript, both audits and both builds pass; Larastan runs in CI only (open question 16). A browser check covered the company journey (sign-up, emailed temporary password, password change, empty dashboard) and confirmed that staff and company stay logged in side by side in one browser.
