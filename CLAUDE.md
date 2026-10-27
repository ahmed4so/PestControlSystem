# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The backend for a **Pest Control System**. The domain features haven't been built yet. What exists today is a base layer: authentication, user management, and roles/permissions.
Stack: Laravel 12, Inertia v2 + React 19 + Tailwind v4, Wayfinder for typed routes, Fortify for web auth (login, password reset, email verification, 2FA), Passport for API tokens, and spatie/laravel-permission for roles.

## Commands

```bash
composer run dev            # serve + queue:listen + vite (concurrently)
composer run test           # config:clear, pint --test, then full test suite
php artisan test --compact tests/Feature/Users/AccountAccessTest.php
php artisan test --compact --filter="inactive accounts"
vendor/bin/pint --dirty     # format changed PHP (run before finishing)
npm run lint                # eslint --fix
npm run types               # tsc --noEmit
npm run build               # needed if the UI throws "Unable to locate file in Vite manifest"
php artisan wayfinder:generate --with-form   # regenerate resources/js/{actions,routes,wayfinder} after route changes
php artisan migrate:fresh --seed             # roles/permissions + local admin@example.com / password
```

Tests run against the **MySQL** database named in `phpunit.xml` (`DB_DATABASE`), not SQLite, so that database has to exist. Feature tests use `RefreshDatabase` through `tests/Pest.php`.

## Architecture

- **Routes**:
  - `routes/web.php` holds the dashboard, `users` and `roles` routes behind `auth` + `active` + `verified` + `ForcePasswordChange`.
  - `routes/settings.php` holds profile, password, appearance and 2FA.
  - `routes/api.php` has an empty `/v1` group, ready for mobile endpoints guarded by `auth:api` (Passport).
- **Account gates** (`app/Http/Middleware`):
  - `EnsureUserIsActive` (alias `active`) logs out any user whose `users.status` isn't `active`.
  - `ForcePasswordChange` redirects users with `first_login = true` to the password settings page.
- **Authorization**:
  - Controllers declare `permission:<name>` middleware through `HasMiddleware`. See `UserController` and `RoleController`.
  - Permissions are defined in `RolesAndPermissionsSeeder`, which also **deletes any permission not listed there**, so add new permissions to that list.
  - `Gate::before` in `AppServiceProvider` grants `Super Admin` everything.
  - Everything is seeded on the `web` guard, and `User::$guard_name = 'web'` keeps API requests resolving permissions against it.
- **Frontend shared props** (`HandleInertiaRequests`): `auth.user`, `auth.permissions` (all permission names), `flash.success` / `flash.error`, and `sidebarOpen`. The sidebar (`components/app-sidebar.tsx`) shows each nav item based on `auth.permissions`.
- **Frontend layout**: pages live in `resources/js/pages/<area>/` and are rendered with `Inertia::render('<area>/page')`. Import backend routes from `@/routes/...` or `@/actions/...` (Wayfinder) instead of hard-coding URLs. Those directories are generated, so don't edit them by hand.

## Conventions (from Laravel Boost guidelines)

- Validate with Form Request classes, not inline. Use `Model::query()` rather than `DB::`. Eager-load to avoid N+1 queries.
- Give every method an explicit return type. Use constructor property promotion and curly braces on all control structures.
- Call `env()` only inside config files.
- When you modify a column in a migration, restate all of its existing attributes.
- Create files with `php artisan make:* --no-interaction`, and create tests with `php artisan make:test --pest`. New models get factories.
- Every change needs a test. Run only the affected tests, and don't delete tests without approval.
- Follow sibling files for structure and naming. Don't add dependencies or new top-level folders without approval.
