# SARIS Company Portal — Decisions Log

Confirmed decisions are binding. Proposed decisions need stakeholder confirmation
in the Claude.ai design Project before they are treated as binding.

Entry format:

```
## D-xxx: [Short title]
- Date:
- Status: Proposed | Confirmed | Superseded by D-xxx
- Source: Claude.ai design chat | Claude Code | Stakeholder
- Decision:
- Reason:
- Affects:
```

---

## Accounts

## D-001: One user account per company
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A1, A2, A4, A10)
- Decision: Each company has exactly one portal account (1:1). No additional users and no roles inside a company. The account holder may be any person authorized by the company.
- Reason: Simple ownership and accountability for a small number of companies.
- Affects: CompanyUser, Company, authentication, Company Portal UI

## D-002: Only companies can apply
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A5)
- Decision: Individuals cannot apply for any service, including sample imports. The "NID for individual applicants (sample)" attachment is removed from Form 3.
- Reason: SARIS licenses companies only.
- Affects: Form 3, Application validation

## D-003: Account recovery
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A9, A15)
- Decision: Self-service password reset by email. If the email address is lost, the company submits a public account-recovery request on the login page with a letter signed by the Owner/MD and a copy of the MD's ID. Staff verify it and reassign the account email. All steps are audit-logged.
- Reason: Single-account model needs a safe recovery path.
- Affects: Authentication, AccountRecoveryRequest, Admin Portal

## D-004: No legacy license migration
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A7, A11)
- Decision: Paper licenses are not migrated. Existing licensees apply as new applicants. SARIS announces a cutover date with no transition period.
- Reason: Clean start; all licenses issued through the system.
- Affects: Data migration (none), rollout plan

## D-005: Profile edits and amendments
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A6, A8)
- Decision: No separate profile approval; the profile is verified with the first license application. Ordinary profile edits are allowed. Changes to license-relevant fields require an amendment application.
- Reason: Keep the profile current without bypassing review.
- Affects: Company, Application (type Amendment)

---

## Licenses

## D-006: Issued licenses are immutable
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A8)
- Decision: An issued license or permit is never modified. It stores an immutable snapshot of all data at issue time.
- Reason: Legal integrity of issued documents.
- Affects: License, LicenseSnapshot, document generation

## D-007: New number for every issued license
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A12)
- Decision: Every issued license, including renewals and amendments, gets a new number. The previous license becomes Superseded and links to its successor.
- Reason: One number = one immutable document.
- Affects: License, numbering service

## D-008: Amendments
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (A13, A14)
- Decision: An amendment is charged the full license fee, goes through the full review, and the new license gets a full 1-year validity from its approval date.
- Reason: Prevents using amendments as a cheap way to extend validity.
- Affects: Application (type Amendment), fees, License

## D-009: Validity, renewal and expiry
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (D1, D2, D3) and written procedure 6.14
- Decision: Company and Operator Licenses are valid 1 year from the issue date. The renewal window opens 60 days before expiry with a pre-filled form. Renewal is due 30 days before expiry; later renewals are accepted but flagged as late. Licenses expire on the expiry date with no grace period; after expiry a New application is required.
- Reason: Matches the written procedure.
- Affects: License, Application (type Renewal), scheduled jobs, notifications

## D-010: Suspension and cancellation
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (D5)
- Decision: Staff initiate suspension or cancellation; the DG approves. A suspended or cancelled license blocks new applications that depend on it. Import Permits already issued are flagged, not voided.
- Reason: Management control over enforcement actions.
- Affects: License lifecycle, Import Permit validation

## D-011: License numbering
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (D6)
- Decision: Format `PREFIX-YYYY-NNNN`, sequence resets yearly per prefix. Prefixes: PCPIL (Importation), PCPWS (Wholesale), PCPRT (Retailer), PCA (Operator), PCPIP (Import Permit).
- Reason: Readable and traceable by year.
- Affects: Numbering service, document templates

## D-012: Official document and public verification
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (D7, D8)
- Decision: The PDF (QR code, stamp and signature images) is the official document. The public QR page shows only number, company name, type, status and validity dates.
- Reason: Verifiable documents without exposing personal data.
- Affects: Document generation, public verification endpoint

## D-013: Certificate field meanings
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B14)
- Decision: "Nationality" = Owner/MD nationality. "Registered date" = SARIS license approval date.
- Reason: Clarifies template fields.
- Affects: License templates, LicenseSnapshot

---

## Services and Forms

## D-014: Company License categories
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B1)
- Decision: Form 1 gets a License Category field (Importer, Wholesaler, Retailer). One category per application; a separate certificate per category. A company may hold several categories.
- Reason: SARIS issues three category-specific license documents.
- Affects: Form 1, Application, License, templates

## D-015: Import Permit prerequisite
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B2)
- Decision: An Import Permit application requires an Active Importation License (not expired, suspended or cancelled). Enforced in the backend at submission.
- Reason: Key business rule.
- Affects: Import Permit validation, License

## D-016: Operator License is independent
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B3)
- Decision: The Operator License does not require a Company License. A company may hold either or both.
- Reason: Different business activities.
- Affects: Operator License validation

## D-017: Supporting documents and certificate expiry
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B4, B5)
- Decision: All listed attachments are mandatory; PDF, JPG or PNG up to 5 MB each. Expiry dates of the Ministry of Commerce and tax certificates are tracked; submission is blocked if expired; reminder 30 days before.
- Reason: Complete and valid files.
- Affects: Document, Company, Application validation, notifications

## D-018: Persons, premises and facility photos
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B6, B7, B8)
- Decision: A technical person may be linked to more than one company (flagged to staff). A company may register multiple premises; each application selects one or more. A photo is mandatory for each facilities and safety checklist item.
- Reason: Single source of truth with evidence.
- Affects: Person, Premises, FacilityChecklistItem, Application

## D-019: Form 3 field changes
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (B9–B13, E2)
- Decision: One structured address format with FMS/region lists. Company enters both Registered Uses and Intended Use per permit (no automatic validation). "Warehouse SARIS Approval Reference" removed from Form 3. "Adjuvant" removed from the permit template. Means of Entry is a list (Sea, Air, Land); Entry Port is free text. "Number of pest control products arriving in this consignment" = total products in the whole consignment, information only.
- Reason: Clarified ambiguous form fields.
- Affects: Form 3, Import Permit template, Address

## D-020: Product catalogue
- Date: 2026-10-27
- Status: Superseded by D-039
- Source: Stakeholder (B15, B16)
- Decision: Staff maintain the registered product catalogue in the Admin Portal. Product label and registration certificate are stored once per product and reused.
- Reason: Product registration is not a Phase 1 service.
- Affects: Product, ProductDocument, Admin Portal, Form 3

## D-021: Import Permit rules
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (E1–E7)
- Decision: Single consignment; one product per permit; all products must be SARIS-registered including samples; no quantity cap; valid 90 days; no post-import reporting (declaration sentence kept, not tracked); banned active ingredients block submission, restricted ones are flagged.
- Reason: Stakeholder answers.
- Affects: Import Permit, Product, RestrictedIngredient, validation

---

## Workflow

## D-022: Adopt the written procedure for the Company License
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (written procedure 6.1–6.14)
- Decision: The Company License workflow follows the official procedure: SUBMITTED → ADMIN_REVIEW → TECHNICAL_DOC_REVIEW → INSPECTION_SCHEDULED → INSPECTION_REPORT → CONSOLIDATED_REVIEW → DIRECTOR_REVIEW → PERMISSION_TO_PAY → AWAITING_PAYMENT → PAYMENT_VERIFICATION → DG_DECISION → LICENSE_PREPARATION → ISSUED. Other statuses: DRAFT, RETURNED, REJECTED, WITHDRAWN.
- Reason: Official SARIS procedure.
- Affects: Workflow engine, Application, Admin Portal, docs/design/04-workflow.md

## D-023: Staff roles
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (written procedure; supersedes C1 and C2)
- Decision: Staff roles are Pesticide Registrar, Technical Reviewer (optional, formally assigned), Inspector, Departmental Director, Payment Officer, Director General (with delegate), Licensing/Records Officer.
- Reason: Matches the written procedure.
- Affects: Roles and permissions, Admin Portal

## D-024: Rejection, return and deferral
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (C4, C5, written procedure; supersedes earlier proposal that the HoD could reject)
- Decision: Only the DG can reject; reasons are mandatory; no appeal; the company may reapply; no refund. The Departmental Director returns files internally to the Registrar with reasons. The Registrar (deficient or clarification items) and the DG (Deferred) return applications to the company. No time limit for the company to respond.
- Reason: Written procedure.
- Affects: Workflow engine, Application

## D-025: Inspection scope
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (C3, 6.4)
- Decision: Inspection is mandatory for the Company License and Operator License, not for the Import Permit. The four SOPs are checked on site by the Inspector and are not uploaded.
- Reason: Stakeholder answers.
- Affects: Workflow templates, InspectionReport, inspection checklist

## D-026: Staff cannot edit application data
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (C8)
- Decision: Staff cannot edit any application data. Corrections are returned to the company.
- Reason: Data submitted is the company's responsibility.
- Affects: Admin Portal, Application

## D-027: DG delegation
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (C6)
- Decision: The DG may delegate final decisions to a signatory for a defined date range. The certificate shows the actual signer.
- Reason: Continuity when the DG is absent.
- Affects: Delegation, roles, document generation

## D-028: Payment flow
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (F2, F3, F4, F5, written procedure 6.10–6.11)
- Decision: For all services, payment happens after the Departmental Director's recommendation and before the DG decision. The Payment Officer issues a system-generated permission-to-pay letter. The company pays at the SARIS office and uploads the receipt with receipt number, date and amount. The Payment Officer clears the payment or rejects the receipt with a reason. No invoices; SARIS keeps its own receipt book. No refunds.
- Reason: Written procedure and stakeholder answers.
- Affects: Payment, PermissionToPayLetter, workflow, Admin Portal

## D-029: License preparation by Records Officer
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (written procedure 6.13)
- Decision: After DG approval, the system pre-generates the license (number, snapshot, QR, PDF). The Licensing/Records Officer checks it, applies signature/stamp and issues it. The license register is updated automatically.
- Reason: Written procedure.
- Affects: Workflow, License, document generation

---

## Platform

## D-030: Integrations and notifications
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (G1–G6)
- Decision: Admin Portal and Company Portal are built together on one shared backend. No Ministry or NIRA integrations; verification is manual. Email only in Phase 1 (no SMS), sent through Resend from a saris.gov.so address.
- Reason: Stakeholder answers.
- Affects: Architecture, notifications

## D-031: One domain and staff login
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (G7)
- Decision: Both portals on one domain, separated by login. Staff may log in from anywhere with a strong password and mandatory two-factor authentication.
- Reason: Stakeholder answer.
- Affects: Architecture, authentication

## D-032: Languages
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (H1)
- Decision: User interface in Somali and English; data entry in English.
- Reason: Local users.
- Affects: UI, localization

## D-033: Hosting, stack and scale
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (H2, H3, H7)
- Decision: Backend in PHP/Laravel. Hosted on SARIS's existing DigitalOcean droplet(s) behind Cloudflare. Designed for fewer than 500 companies in the first two years. Full stack to be defined in docs/design/08-tech-stack.md.
- Reason: Team skills and existing infrastructure.
- Affects: Tech stack, deployment

## D-034: Data retention
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (H4)
- Decision: All records are kept forever. Nothing is hard-deleted; deletions are soft deletes.
- Reason: Regulatory record keeping.
- Affects: All entities, storage, backups

## D-035: Go-live order
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (H8)
- Decision: Company License first, then Operator License and Import Permit.
- Reason: The Import Permit depends on the Company License.
- Affects: Implementation plan

---

## Design Decisions (confirmed 2026-10-27)

## D-036: Backend separation of the two portals
- Date: 2026-10-27
- Status: Superseded by D-048 (separate account tables kept; API routes replaced by Inertia route groups)
- Source: Claude.ai design chat
- Decision: Separate account tables for company users and staff; separate sessions and token audiences; company endpoints under `/api/portal/*` and admin endpoints under `/api/admin/*`, each with its own middleware. A company token is rejected by every admin endpoint and the reverse.
- Reason: Keeps full separation while using one domain (D-031).
- Affects: Authentication, routing, middleware

## D-037: Staff 2FA method
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Staff two-factor authentication uses an authenticator app (TOTP).
- Reason: No SMS in Phase 1 (D-030).
- Affects: Authentication

## D-038: Fee locked from fee schedule
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The fee on the permission-to-pay letter is calculated by the system from the fee schedule and cannot be edited by the Payment Officer. All letters and payment clearances are audit-logged.
- Reason: The Payment Officer both issues the demand and clears the payment.
- Affects: Payment, FeeSchedule, PermissionToPayLetter

## D-039: Company-managed products, verified once by staff
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (replaces staff-maintained catalogue in D-020, B15, B16)
- Decision: The company adds each product once to "My Products": trade name, active ingredient, concentration, formulation, SARIS registration number, country of origin, registration number in country of origin, manufacturer, and uploads the product label and SARIS product registration certificate. Staff verify the product once (status: Pending Verification, Verified, Rejected). Only Verified products can be selected in an Import Permit application, and they are reused without re-checking. The banned/restricted ingredient check (D-021) runs when the product is added. Changing a verified product's data or documents sends it back to Pending Verification.
- Reason: Companies hold the product documents; single source of truth with one staff verification.
- Affects: Product, ProductDocument, Form 3, Admin Portal, Import Permit validation

## D-040: File storage
- Date: 2026-10-27
- Status: Confirmed
- Source: Stakeholder (modified from proposal)
- Decision: Uploaded files are stored in Cloudflare R2 object storage (private bucket, accessed through the backend), not on the droplet disk. Images are compressed on the client before upload.
- Reason: Mandatory photos and permanent retention (D-018, D-034); existing Cloudflare setup.
- Affects: Document storage, upload UI

## D-041: Configurable workflow templates
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Each service has a workflow template (ordered stages, roles, allowed actions) stored as configuration. The Operator License uses the Company License template; the Import Permit uses it without inspection stages, until their written procedures are received.
- Reason: Future services without code changes.
- Affects: Workflow engine

## D-042: Submission snapshot and versions
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Each submission stores a snapshot of all referenced data (profile, persons, premises with photos, products). Each resubmission creates a new version (v1, v2, …).
- Reason: Reviewers see exactly what was submitted, even if the profile changes later.
- Affects: Application, ApplicationVersion

## D-043: Resubmission routing
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: An application returned by the Registrar goes back to the Registrar stage that returned it. An application deferred by the DG goes to CONSOLIDATED_REVIEW, then directly to DG_DECISION; the cleared payment is kept and is not repeated.
- Reason: Avoids repeating completed stages and repeat payment.
- Affects: Workflow engine

## D-044: One open application per service and category
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: A company may have only one open (not final) application per service and license category at a time.
- Reason: Prevents duplicate processing.
- Affects: Application validation

## D-045: Simplified status names for companies
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Companies see simplified statuses (Draft, Submitted, Under Review, Inspection Scheduled, Action Required, Payment Required, Payment Under Verification, Approved, Rejected, Withdrawn). Staff see the exact stage.
- Reason: Clear for applicants without exposing internal steps.
- Affects: Company Portal UI

## D-046: Defaults for unasked questions
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: No enforced time limits per stage (C7); no other SARIS system integration (G6); data-protection principles by default (H5); mobile-first responsive web, autosave, compressed uploads, no native app (H6).
- Reason: Not asked; reasonable defaults.
- Affects: Non-functional requirements

---

## Architecture Decisions (confirmed 2026-10-28)

## D-047: Build on the existing application
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat (based on docs/design/00-current-state.md)
- Decision: The portal is built on the existing Laravel app in the repository root: Laravel 12, Inertia 2, React 19 + TypeScript, Tailwind 4 + shadcn/ui, Fortify, spatie/laravel-permission, Resend, Pest. One modular monolith; no separate JSON API in Phase 1.
- Reason: The scaffold already exists (corrects G1); the team knows the stack.
- Affects: Whole codebase, docs/design/01-architecture.md, 08-tech-stack.md

## D-048: Portal separation with Inertia
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat (replaces the API part of D-036)
- Decision: `users` / `App\Models\User` holds staff only; a new `company_users` / `App\Models\CompanyUser` table holds company accounts. Separate guards (`web` for staff, `company` for companies), separate route files (`routes/admin.php`, `routes/portal.php`), URL prefixes `/admin/*` and `/portal/*`, separate middleware groups and separate session cookies (`saris_admin_session`, `saris_portal_session`). Every company query is scoped to the user's company. Controllers are split into Admin, Portal and Public namespaces.
- Reason: Same full separation as D-036 without building an API the app does not use.
- Affects: Authentication, routing, middleware, models, tests

## D-049: Remove Passport and Jetstream
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: laravel/passport and laravel/jetstream are removed with their routes, tables, traits and config.
- Reason: Unused; live broken routes; extra attack surface.
- Affects: composer.json, User model, migrations, routes

## D-050: Database and drivers
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: MySQL 8 in all environments (test database renamed `saris_portal_testing`). Database drivers for session, cache and queue; no Redis in Phase 1.
- Reason: One less service to maintain; enough for < 500 companies.
- Affects: Configuration, deployment, tests

## D-051: System Administrator role
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The `Super Admin` role and the `Gate::before` bypass are removed. A `System Administrator` role manages staff users, roles and reference data only and has no workflow permissions. The D-023 roles are seeded.
- Reason: Nobody may act in the workflow without the stage role.
- Affects: Roles and permissions, seeders, AppServiceProvider

## D-052: Mandatory staff 2FA
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Middleware forces every staff user to set up and use TOTP 2FA before any admin page.
- Reason: Implements D-031 and D-037.
- Affects: Authentication, middleware

## D-053: Soft deletes and deactivation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: All models use soft deletes. Staff users are deactivated (status enum: active, inactive), never deleted. Account self-delete features are removed.
- Reason: Implements D-034.
- Affects: All models, UserController, settings pages

## D-054: Languages implementation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: UI text in `lang/en.json` and `lang/so.json`, shared to React through Inertia with a small `t()` helper.
- Reason: Implements D-032.
- Affects: Frontend, lang files

## D-055: Audit logging package
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Audit logging uses spatie/laravel-activitylog, used append-only.
- Reason: Proven package, same vendor as the permission package.
- Affects: Audit module, all modules

## D-056: File storage implementation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Cloudflare R2 through Laravel's S3 driver; private bucket; files never overwritten; downloads through 5-minute signed URLs after a permission check; local `storage/{path}` serving disabled.
- Reason: Implements D-040 securely.
- Affects: Filesystem config, Documents module

## D-057: Deployment and environments
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Isolated deployment on the existing droplet (own Linux user, PHP-FPM pool, database and user; Nginx accepts only Cloudflare IPs). Production and staging environments.
- Reason: Implements H3 safely.
- Affects: Deployment

## D-058: Background jobs
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: A queue worker (Supervisor) runs PDF generation, emails and image processing; the scheduler (cron) runs expiry, reminder, cleanup and backup jobs.
- Reason: Keeps web requests fast; timed lifecycle rules.
- Affects: Deployment, Licensing, Notifications

## D-059: Backups
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Nightly encrypted database backup to a separate R2 bucket.
- Reason: Records are kept forever (D-034).
- Affects: Deployment

## D-060: Repository layout
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: `saris-portal/docs/` moves to `docs/` at the repository root; the two CLAUDE.md files are merged into the root CLAUDE.md; `saris-portal/` is removed.
- Reason: The application lives at the repository root.
- Affects: Repository, CLAUDE.md

## D-061: Code organisation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Business logic in `app/Domain/<Module>` (Identity, CompanyRegistry, Products, Applications, Workflow, Inspection, Payments, Licensing, Documents, Notifications, Audit, ReferenceData); models in `app/Models`; controllers in `Admin/`, `Portal/`, `Public/`.
- Reason: Clear modules within standard Laravel layout.
- Affects: Codebase structure

## D-062: Fortify for both portals
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Fortify handles login, logout, password reset and 2FA for both staff and companies. Its routes are registered for `/admin` (staff guard, `users`) and for the company area (company guard, `company_users`); a middleware sets the Fortify guard and password broker per area. No custom login system. Tests must prove that each portal's login rejects the other portal's accounts.
- Reason: Reuse proven code; less custom security code.
- Affects: Identity module, FortifyServiceProvider, routes, tests

## D-063: Company sign-up with temporary password
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Sign-up form (Full Name, Email, Phone) creates a CompanyUser and emails a random temporary password valid for 1 hour. The company logs in through Fortify; the existing ForcePasswordChange middleware forces a new password first. Receiving the email verifies the address. The company can request a new temporary password if it expires.
- Reason: Implements the sign-up flow with existing components.
- Affects: Identity module, CompanyUser, notifications

## D-064: Optional 2FA for companies
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Companies may switch on TOTP 2FA (Fortify). If they lose both the authenticator and the recovery codes, staff reset 2FA after an approved account recovery request (D-003).
- Reason: Extra security without forcing it on all companies.
- Affects: Identity module, AccountRecoveryRequest, Admin Portal

---

## Open Questions

1. Fee schedule per service and category (F1).
2. Written procedures for the Operator License and Import Permit.
3. Does a renewed license start on its approval date, or the day after the old license expires?
4. How long can an application wait in AWAITING_PAYMENT?
5. Which staff role initiates a suspension (Registrar or Departmental Director)?
6. Are revocation, non-renewal and blacklisting (procedure 6.14) needed in Phase 1?
7. Which staff role verifies company products (D-039)? Default: Pesticide Registrar.
8. Droplet details: size, OS, PHP version and other sites hosted.
9. Final domain (assumed `portal.saris.gov.so`).
10. Staging location: subdomain on the same droplet (recommended) or a second droplet.
