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
- Status: Superseded by D-073
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
- Status: Amended by D-107 (renewal start date)
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
- Status: Superseded by D-066 for the person rule only (premises and photo rules remain)
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
- Status: Amended by D-101 and D-106 (no WITHDRAWN status; no LICENSE_PREPARATION stage)
- Source: Stakeholder (written procedure 6.1–6.14)
- Decision: The Company License workflow follows the official procedure: SUBMITTED → ADMIN_REVIEW → TECHNICAL_DOC_REVIEW → INSPECTION_SCHEDULED → INSPECTION_REPORT → CONSOLIDATED_REVIEW → DIRECTOR_REVIEW → PERMISSION_TO_PAY → AWAITING_PAYMENT → PAYMENT_VERIFICATION → DG_DECISION → LICENSE_PREPARATION → ISSUED. Other statuses: DRAFT, RETURNED, REJECTED, WITHDRAWN.
- Reason: Official SARIS procedure.
- Affects: Workflow engine, Application, Admin Portal, docs/design/04-workflow.md

## D-023: Staff roles
- Date: 2026-10-27
- Status: Amended by D-106 (Licensing/Records Officer role removed)
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
- Status: Superseded by D-106
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
- Status: Amended by D-094 (Cloudflare limited to R2)
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
- Status: Amended by D-072 (no limit for Import Permits)
- Source: Claude.ai design chat
- Decision: A company may have only one open (not final) application per service and license category at a time.
- Reason: Prevents duplicate processing.
- Affects: Application validation

## D-045: Simplified status names for companies
- Date: 2026-10-27
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Companies see simplified statuses (Draft, Submitted, Under Review, Inspection Scheduled, Action Required, Payment Required, Payment Under Verification, Approved, Rejected). Staff see the exact stage.
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
- Status: Amended by D-095 (also handles account recovery and ID release)
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
- Status: Amended by D-094 (Nginx public behind DigitalOcean Cloud Firewall; no Cloudflare proxy)
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

## Data Model Decisions (confirmed 2026-10-28)

## D-065: Embedded address
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The address is a set of `address_*` columns (FMS and region from lists, district, town or market, street, details) on companies, persons and premises, copied as-is into snapshots.
- Reason: One structured format (D-019) without extra joins.
- Affects: Company, Person, Premises, snapshots

## D-066: One company per national ID
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (replaces the flag in D-018 / B6)
- Decision: A national ID can exist in only one company in the whole system, for every person including the Owner/MD. A duplicate is blocked with a message that does not name the other company. When a person moves, the new company contacts SARIS; staff verify and release the ID from the old company (reason mandatory, audit-logged). The old company is notified and marked amendment required.
- Reason: Prevents the same person serving several companies.
- Affects: Person (unique `active_nid_key`), Admin Portal, notifications

## D-067: Typed documents with history
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Documents have a type; a new upload of the same type replaces the current one and keeps history (`replaced_by_id`); every file is an immutable StoredFile.
- Reason: Single current document with full history.
- Affects: Document, StoredFile

## D-068: One licenses table
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Licenses and Import Permits are stored in one `licenses` table, distinguished by `document_type`, each with a separate immutable LicenseSnapshot.
- Reason: One numbering, lifecycle and verification model.
- Affects: License, LicenseSnapshot, verification

## D-069: Snapshot format
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Submission and license snapshots are versioned JSON (`schema_version`) with a SHA-256 hash. Files are referenced by `stored_file_id`, never copied.
- Reason: Integrity and long-term readability.
- Affects: ApplicationVersion, LicenseSnapshot

## D-070: Typed application tables
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Application data is stored in typed tables (ImportPermitDetail, ApplicationPremises, ApplicationPerson), not a generic JSON form store.
- Reason: Validation, reporting and clear business rules.
- Affects: Applications module

## D-071: Workflow configuration tables
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Workflow templates, stages and transitions are database tables seeded from configuration files. WorkflowAction is the immutable history of every transition.
- Reason: Implements D-041.
- Affects: Workflow module

## D-072: No limit on open Import Permits
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (amends D-044 for the Import Permit)
- Decision: A company may have any number of open Import Permit applications. The system warns, without blocking, when another open permit exists for the same product.
- Reason: One product per permit (D-021); a consignment may need several permits at once.
- Affects: Application validation

## D-073: Company data editing and locking
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (supersedes the profile-edit part of D-005)
- Decision: Company profile, persons and premises are editable only before the first submission, or while a Company or Operator License application is in Draft or Returned. Edits are saved immediately. At all other times, including while any application is in process and after a license is issued, the data is locked. Import Permit drafts do not unlock it. Exceptions: products can be added or edited at any time but need staff verification (D-039); renewed commerce and tax certificates can be uploaded at any time and staff are notified. The lock is enforced in the backend.
- Reason: Data under review or printed on a license changes only through a license application.
- Affects: Company, Person, Premises, Document, policies, Company Portal UI

## D-074: One receipt, one payment
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: A receipt number can clear only one payment (unique `cleared_receipt_key`).
- Reason: Prevents reuse of a receipt.
- Affects: Payment

## D-075: Number formats
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Application references `CL-YYYY-NNNNN`, `OL-YYYY-NNNNN`, `IP-YYYY-NNNNN`; permission-to-pay letters `PTP-YYYY-NNNN`; account recovery requests `AR-YYYY-NNNN`. All from `number_sequences`.
- Reason: Readable, yearly references.
- Affects: NumberSequence, Applications, Payments, Identity

## D-076: Normalisation on save
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Emails lowercase and trimmed; phones in international format (+252…); registration, tax and ID numbers trimmed and uppercased.
- Reason: Prevents duplicates from typing differences.
- Affects: All input validation

## D-077: Inspection per premises
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: One inspection per selected premises, with a configurable checklist from procedure 6.6 including the four SOPs; photos, video and GPS recorded by the Inspector; the signed report is locked.
- Reason: Implements procedure 6.5–6.7 and D-025.
- Affects: Inspection module

## D-078: Fee currency
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: All SARIS fees are in USD.
- Reason: Stakeholder answer.
- Affects: FeeSchedule, PermissionToPayLetter, Payment

---

## Security Decisions (confirmed 2026-10-28)

## D-079: Fortify wiring per area
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: `Fortify::ignoreRoutes()`; Fortify controllers registered in `routes/admin.php` and `routes/portal.php`; a `SetAuthArea` middleware, running before `StartSession`, sets the Fortify guard, password broker, home path and session cookie per area. Rate-limit keys include the area.
- Reason: Implements D-062.
- Affects: Identity module, routes, middleware, tests

## D-080: Bot protection without outside services
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (modified)
- Decision: Public forms (sign-up, new temporary password, forgot password, account recovery) use a hidden honeypot field, a minimum fill time and rate limits. Responses are identical whether or not the email exists.
- Reason: No Cloudflare in front of the domain (D-094); no third-party CAPTCHA.
- Affects: Identity module, public forms

## D-081: Account lockout
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: 5 consecutive failed logins lock the account for 15 minutes; the owner is emailed; the System Administrator can unlock staff; a password reset unlocks a company account.
- Reason: Brute-force protection.
- Affects: Identity module, users, company_users

## D-082: Password policy and expiry
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (modified)
- Decision: Both realms, all environments except tests: 12+ characters, upper and lower case, number, symbol, not breached. Passwords expire every 90 days (reminder 14 days before; forced change at next login). The last 5 passwords cannot be reused. bcrypt cost 12. A password change ends other sessions.
- Reason: Stakeholder requires periodic expiry; history prevents cycling.
- Affects: Identity module, password_histories, scheduler

## D-083: Staff account creation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The System Administrator creates staff accounts; the system emails a temporary password valid 24 hours; forced change and 2FA set-up at first login. Administrators never set passwords.
- Reason: Nobody knows another user's password.
- Affects: Identity module, UserController

## D-084: Sessions
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (timeouts)
- Decision: Separate encrypted session cookies (`Secure`, `HttpOnly`, `SameSite=Lax`). Idle timeout 15 minutes for staff, 30 minutes for companies; staff absolute limit 12 hours. No "remember me".
- Reason: Stakeholder answer.
- Affects: Session config, middleware

## D-085: Password re-confirmation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Re-entering the password is required for sensitive actions (DG decision, license issue, payment clearance, inspection signing, enforcement, user and role changes, recovery decisions, ID release, 2FA reset; company submission and 2FA changes). Valid 15 minutes for staff, 30 for companies.
- Reason: Protects decisions from an unattended session.
- Affects: Identity, Workflow

## D-086: Segregation of duties
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The System Administrator cannot hold a workflow role. On the same application one person cannot both recommend (Director) and decide (DG), clear the payment and decide, or review as Registrar and issue the permission-to-pay letter. Delegates act only within their dates.
- Reason: No single person controls an application end to end.
- Affects: Workflow module, roles

## D-087: Staff 2FA reset
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The System Administrator resets a staff member's 2FA after face-to-face verification; the user enrols again at next login; audit-logged.
- Reason: Lost device recovery for mandatory 2FA.
- Affects: Identity module

## D-088: National ID protection
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: National IDs are encrypted at rest; an HMAC-SHA256 hash column provides lookup and the unique key for D-066.
- Reason: Data protection without losing the duplicate check.
- Affects: Person, 02-data-model

## D-089: Upload security
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Type detected from file content (PDF, JPG, PNG); size limits; images re-encoded on the server; PDFs with JavaScript or encryption rejected; random storage keys; downloads as attachments. Antivirus in Phase 2.
- Reason: Safe handling of uploaded files.
- Affects: Documents module

## D-090: Transport security and headers
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: TLS 1.2+ on Nginx with a Let's Encrypt certificate (auto-renewed), HTTP redirected to HTTPS, HSTS. Nonce-based CSP, frame denial, nosniff, referrer and permissions policies.
- Reason: Browser and transport protection.
- Affects: Nginx, middleware

## D-091: Append-only audit log
- Date: 2026-10-28
- Status: Amended by D-160 (no database triggers; enforced in the application)
- Source: Claude.ai design chat
- Decision: Audit rows are never updated or deleted; MySQL triggers reject UPDATE and DELETE. Staff views and downloads of company documents are logged.
- Reason: Tamper-evident records kept forever.
- Affects: Audit module

## D-092: Recovery approval effects
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Approving an account recovery ends all sessions, sends a temporary password (1 hour) to the new email and a notice to the old email; optional 2FA reset.
- Reason: Detects and limits account takeover.
- Affects: Identity module

## D-093: Company login email change
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: The account holder can change the login email after password confirmation; a confirmation link goes to the new address and a notice to the old one; the change applies only when the link is used.
- Reason: Self-service without takeover risk.
- Affects: Identity module, company_users

## D-094: Cloudflare limited to R2
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Cloudflare is used only for R2 file storage. The domain is served directly by Nginx on the droplet with Let's Encrypt TLS, rate limits and fail2ban, behind a DigitalOcean Cloud Firewall and ufw. No Cloudflare proxy, WAF or Turnstile. Edge DDoS protection is accepted as absent in Phase 1 and can be added later without code changes.
- Reason: Stakeholder decision.
- Affects: Deployment, 01-architecture, 03-auth-security

## D-095: Recovery and ID release by System Administrator
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: The System Administrator handles account recovery requests and national-ID releases. The role still has no workflow permissions.
- Reason: Stakeholder answer.
- Affects: Roles, Identity module, Admin Portal

---

## Workflow Engine Decisions (confirmed 2026-10-28)

## D-096: Workflow engine contract
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Every transition locks the application row and checks `expected_status`; then, in order: transition lookup, realm, role, assignment, delegation, password confirmation, payload, guards. Effects, status change and WorkflowAction are saved in one transaction; emails and PDFs run on the queue after commit.
- Reason: No double processing; all-or-nothing changes.
- Affects: Workflow module

## D-097: Workflow definitions as configuration
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Definitions are versioned PHP files in `config/workflows/`, validated and loaded into the workflow tables. Guards and effects are named classes in `app/Domain/Workflow`. Conditional targets are allowed. Applications keep the version they started with.
- Reason: New services without code changes (D-041).
- Affects: Workflow module

## D-098: Company License transition matrix
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The transitions in 04-workflow-engine §5.2, including: after a DG deferral the application passes consolidated review and goes directly to DG decision (payment kept); the Registrar can order a re-inspection from consolidated review.
- Reason: Implements procedure 6.1–6.12 and D-043.
- Affects: Workflow definition COMPANY_LICENSE_V1

## D-099: Inspection visit and reschedule actions
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: New actions `RECORD_INSPECTION_VISIT` (Inspector; INSPECTION_SCHEDULED → INSPECTION_REPORT) and `RESCHEDULE_INSPECTION` (Registrar).
- Reason: Procedure 6.5–6.6.
- Affects: Workflow, Inspection, 02-data-model §5.2

## D-100: Interim Operator License and Import Permit workflows
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Operator License copies the Company License flow with Form 2 requirements and PCA license. Import Permit is the same flow without inspection, with Importation License re-checked at DG approval, and a PCPIP permit valid 90 days.
- Reason: Until SARIS provides those procedures (D-041).
- Affects: Workflow definitions OPERATOR_LICENSE_V1, IMPORT_PERMIT_V1

## D-101: No withdrawal
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Companies cannot withdraw a submitted application. There is no WITHDRAW action and no WITHDRAWN status. A submitted application ends only as ISSUED or REJECTED. Unsubmitted drafts can be discarded.
- Reason: Stakeholder decision.
- Affects: Workflow, Application, Company Portal

## D-102: Review checklists per stage
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Registrar review items per stage (04 §5.3 and §7), each Compliant, Deficient or Clarification required with a comment when not compliant; stored as configuration.
- Reason: Procedure 6.3, 6.4, 6.8.
- Affects: Workflow, ReviewItem

## D-103: Renewal and amendment workflow
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Renewal and amendment use the service's definition; extra guards (previous license Active, renewal window) and effects (late flag, supersede previous license, clear amendment flag).
- Reason: One workflow per service.
- Affects: Workflow, Licensing

## D-104: Coded license lifecycle
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: License statuses (ACTIVE, EXPIRED, SUPERSEDED, SUSPENDED, CANCELLED) and enforcement actions are a coded state machine in Phase 1, not configurable.
- Reason: Small, stable rules.
- Affects: Licensing module

## D-105: Staff work queues
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: My work (stage role or assignment), by-stage counts and ages, waiting on company, and flags (late renewals, restricted ingredients, amendment required, flagged permits).
- Reason: Daily staff work.
- Affects: Admin Portal

## D-106: Automatic license issue at DG approval
- Date: 2026-10-28
- Status: Amended by D-145 (PDF generated on demand, not on the queue)
- Source: Stakeholder (supersedes D-029; amends D-022, D-023)
- Decision: When the DG or delegate approves, the license or permit is issued in the same transaction: number, LicenseSnapshot, verification code, status ACTIVE, register entry. The PDF is generated on the queue with the signer's signature image, stamp image and QR code, and the company is emailed when it is ready. There is no Licensing/Records Officer role and no LICENSE_PREPARATION stage. Written procedure 6.13 should be updated by SARIS to match.
- Reason: Stakeholder decision; faster issue.
- Affects: Workflow, Licensing, Documents, roles

## D-107: Renewal start date
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (amends D-009)
- Decision: A renewed license starts the day after the old license expires, or on the approval date if the old license has already expired, and is valid for 1 year. The old license becomes SUPERSEDED when the new one starts.
- Reason: No days lost for companies that renew early.
- Affects: Licensing, LicenseSnapshot

---

## Tech Stack Decisions (confirmed 2026-10-28)

## D-108: Added packages
- Date: 2026-10-28
- Status: Amended by D-138 (phpoffice/phpword instead of mpdf/mpdf)
- Source: Claude.ai design chat
- Decision: spatie/laravel-activitylog, league/flysystem-aws-s3-v3, mpdf/mpdf, chillerlan/php-qrcode, intervention/image (GD), spatie/laravel-honeypot, spatie/laravel-csp, spatie/laravel-backup, larastan/larastan (dev); npm browser-image-compression.
- Reason: Each implements a confirmed decision with a maintained package.
- Affects: composer.json, package.json

## D-109: PDF engine
- Date: 2026-10-28
- Status: Amended by D-138 (official .docx templates filled with PhpWord, converted by headless LibreOffice)
- Source: Claude.ai design chat
- Decision: Certificates, permits and permission-to-pay letters are generated with mPDF from HTML templates.
- Reason: Pure PHP; Unicode and right-to-left support; no browser on the server.
- Affects: Documents module, Deliverable 6

## D-110: Server-side rendering kept
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Inertia SSR stays as the repository has it. Production builds the SSR bundle and runs `php artisan inertia:start-ssr` under Supervisor; pages fall back to browser rendering if it stops.
- Reason: Stakeholder decision.
- Affects: Deployment, 08-tech-stack

## D-111: UI libraries kept
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: All existing UI libraries are kept (shadcn/ui on Radix, @headlessui/react, @heroui/alert). No new UI library is added without a decision.
- Reason: Stakeholder decision.
- Affects: Frontend

## D-112: Server baseline
- Date: 2026-10-28
- Status: Amended by D-138 (LibreOffice and template fonts on the server)
- Source: Claude.ai design chat
- Decision: PHP-FPM 8.3 with required extensions, MySQL 8.0 on localhost, Nginx with Let's Encrypt, Supervisor for the queue worker and SSR server, cron for the scheduler, Node 22 LTS.
- Reason: Supported versions matching the stack.
- Affects: Deployment

## D-113: Backend coding conventions
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Business logic in app/Domain/<Module>/Actions; thin controllers; Form Requests; Policies; strict types and backed enums; $fillable; transactions for multi-row writes; status changes only through WorkflowEngine::perform() or the Licensing module; files only through the Documents module; no hard-coded fees, lists or durations.
- Reason: Consistency and no bypass of rules.
- Affects: All backend code, CLAUDE.md

## D-114: Quality checks
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (option chosen)
- Decision: Pint, ESLint, TypeScript, Larastan level 5, Pest on MySQL 8, composer and npm audits, and builds run on GitHub Actions for every pull request if available; otherwise `composer run check` runs them locally and must pass before every commit.
- Reason: Catch errors before merge.
- Affects: CI, composer.json, CLAUDE.md

## D-115: Git rules
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Protected main; work on feature/, fix/ or docs/ branches; merge through pull requests after checks pass; commit messages cite decision numbers where relevant.
- Reason: Controlled changes.
- Affects: Repository

## D-116: Translation files
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: UI text in lang/en.json and lang/so.json with the English text as key; a test fails if a Somali key is missing; SARIS staff review Somali text before go-live.
- Reason: Implements D-032 and D-054.
- Affects: Frontend, backend, tests

## D-117: National-ID hash key
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: A separate NID_HASH_KEY environment variable is used for the national-ID HMAC hash.
- Reason: Duplicate detection survives an APP_KEY rotation.
- Affects: Identity, Person, .env

## D-118: Sender address
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder
- Decision: Portal emails are sent from noreply@saris.gov.so; the domain is verified in Resend with SPF and DKIM records before go-live.
- Reason: Stakeholder answer.
- Affects: Notifications, DNS

---

## Implementation Plan Decisions (confirmed 2026-10-28)

## D-119: Phases and releases
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Build in Phases 0–7 (09-implementation-plan §3). Release 1 = Company License (all three categories) end to end, after Phases 0–5. Release 2 = Import Permit with My Products and Operator License, after Phases 6–7.
- Reason: The Company License is the prerequisite for the Import Permit (D-015) and exercises every part of the system.
- Affects: Implementation plan, all modules

## D-120: Remaining design deliverables
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Deliverable 05 is reduced to per-field validation rules. Deliverables 05, 06 (documents) and 07 (notifications) are designed while Phases 0–3 are built; 05 and 07 are required before Phase 4A, 06 before Phase 4B.
- Reason: Most lifecycle content is already in 02 and 04; no waiting time.
- Affects: Design plan

## D-121: Phase process
- Date: 2026-10-28
- Status: Confirmed (amended by D-163 for Phases 1 and 2)
- Source: Claude.ai design chat
- Decision: One branch per phase, one pull request per sub-feature, tests and `composer run check` passing, "Done when" checklist complete. Implementation decisions not covered by the design are logged as Proposed and confirmed in the design Project before the phase is merged to main.
- Reason: Design and code stay consistent.
- Affects: All phases, CLAUDE.md

## D-122: Deployment and rollback
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: deploy/deploy.sh deploys from main into releases/<timestamp>, backs up the database first, runs migrations and workflows:load, switches a `current` symlink, restarts the queue and SSR processes, checks /up, and rolls back by switching the symlink. The last 5 releases are kept; database changes stay backward-compatible for one release.
- Reason: Safe updates and one-step rollback.
- Affects: Deployment, Phase 5

## D-123: Acceptance testing and sign-off
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (sign-off) and Claude.ai design chat
- Decision: Each release is tested on staging by SARIS staff in each role and 2–3 volunteer companies (09 §5). The SARIS IT Advisor signs off acceptance before every release goes live.
- Reason: Real users confirm the system before go-live.
- Affects: Phase 5, Releases 1 and 2

## D-124: Backlog out of scope
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Antivirus scanning, edge protection, closing abandoned applications, revocation/blacklisting, reports, SMS, online payment, NIRA verification, online product registration and a configurable license lifecycle are not part of Releases 1 and 2.
- Reason: Keep the first releases focused.
- Affects: Scope

---

## Notifications, Field Rules and Documents Decisions (confirmed 2026-10-28)

## D-125: Bilingual emails
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Every email contains both languages, the recipient's preferred language first; staff get `users.preferred_locale` (default en).
- Reason: Readable by every recipient (D-032).
- Affects: Notifications, users

## D-126: No email attachments
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Emails never carry attachments; they link to portal pages that require login.
- Reason: Security and low bandwidth.
- Affects: Notifications

## D-127: Staff notification recipients
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Staff emails go to all active holders of the stage role, or only to the assignee when one exists; never for the user's own action.
- Reason: Right people, no noise.
- Affects: Notifications

## D-128: Notification catalogue
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The codes, triggers and recipients in 07-notifications §3.
- Reason: Complete event coverage.
- Affects: Notifications, Workflow, Licensing, Identity

## D-129: Email delivery handling
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Emails are queued after commit, retried 3 times, logged without the body; failures are visible to the System Administrator.
- Reason: Reliable, private delivery.
- Affects: Notifications

## D-130: Email format
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: HTML plus plain-text versions with a light layout; subject format `SARIS — {subject} — {reference}`.
- Reason: Works on basic phones and slow connections.
- Affects: Notifications

## D-131: Draft validation
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Drafts save without required-field checks (format rules still apply); full validation and workflow guards run at submit and resubmit.
- Reason: Autosave never fails (D-046).
- Affects: Applications, Form Requests

## D-132: Technical person national ID
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The technical person's national ID is required, although not on the paper forms.
- Reason: Needed for the duplicate block (D-066).
- Affects: Person, Forms 1 and 2

## D-133: Common field formats
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (ID format) and Claude.ai design chat
- Decision: Formats in 05-field-rules §2: names, phone in international format with +252 default, registration numbers, national ID of exactly 11 digits (NIRA), files.
- Reason: Consistent validation.
- Affects: All forms

## D-134: Origin registration number required
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Registration number in the country of origin is required for products.
- Reason: Printed on the Import Permit.
- Affects: Product

## D-135: Photo and video limits
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Up to 5 photos per checklist item; inspection videos MP4 up to 50 MB.
- Reason: Closes the inspection media question.
- Affects: FacilityChecklistPhoto, InspectionMedia

## D-136: Inspection GPS
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: The inspection report records the device GPS position; if unavailable, the Inspector enters a reason.
- Reason: Evidence of the visit (6.6).
- Affects: Inspection

## D-137: Template versions
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Official templates are versioned and never deleted; each license and letter records its `template_code`, and the license snapshot records the signature and stamp files used at approval, so every download is identical.
- Reason: Reproducible documents.
- Affects: Licensing, Documents

## D-138: Official .docx templates filled with saved data
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (supersedes the HTML/letterhead-image approach)
- Decision: Documents are produced by filling the official SARIS .docx templates (copies in resources/templates with placeholders) with the saved snapshot data using phpoffice/phpword, then converting to PDF with headless LibreOffice.
- Reason: Documents look exactly like the official SARIS templates.
- Affects: Documents, 08-tech-stack, server

## D-139: Document field mapping and signer
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Field mapping in 06-documents §3; the signer's own name and title are printed, with "for the Director General" when a delegate signs.
- Reason: Matches the templates; correct signer (D-027).
- Affects: Documents

## D-140: Signatures, stamp and settings
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Each signer's signature image (`users.signature_file_id`) and title, the stamp and document settings (place of issue, footer, payment instructions) are managed by the System Administrator in `system_settings`; a DG or delegate cannot approve until their signature exists.
- Reason: Controlled, audited document assets.
- Affects: Users, SystemSetting, Workflow (T21)

## D-141: QR verification
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Every license and permit carries a QR code to `/verify/{code}` (32 random characters) with the code and URL printed below; the public page shows only number, company, type, status and dates.
- Reason: Authenticity check for customs and the public.
- Affects: Documents, Verification

## D-142: Template wording corrections
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Corrections listed in 06-documents §6 are applied only after SARIS approves the wording.
- Reason: Legal text is SARIS's to change.
- Affects: Templates

## D-143: Valid-from line
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: A "Valid from" line is printed only when it differs from the issue date (early renewal, D-107).
- Reason: Clear validity for early renewals.
- Affects: Templates

## D-144: Date format and place of issue
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Dates printed DD/MM/YYYY; place of issue from settings.
- Reason: Matches the templates.
- Affects: Documents

## D-145: Documents generated on demand
- Date: 2026-10-28
- Status: Confirmed
- Source: Stakeholder (supersedes the store-once proposal)
- Decision: No PDF is stored. Each time a license, permit or letter is opened or downloaded, it is generated from the saved data and the recorded template version. Every download is audit-logged. At most 2 conversions run at once.
- Reason: Stakeholder decision; always produced from the saved data.
- Affects: Documents, Licensing, Payments, 01, 02, 04

## D-146: Permission-to-pay letter
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Letter content as in 06-documents §3.3 using a SARIS letter template; payment instructions come from settings.
- Reason: Procedure 6.10.
- Affects: Payments, Documents

## D-147: License register
- Date: 2026-10-28
- Status: Confirmed
- Source: Claude.ai design chat
- Decision: Admin register of all licenses and permits with filters and CSV export; exports are audit-logged.
- Reason: Procedure 6.13 register.
- Affects: Admin Portal, Licensing

---

## Phase 0 Decisions (confirmed 2026-10-29)

## D-148: Role names and actor codes
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed by stakeholder)
- Decision: spatie role names are the D-023 role names, mapped to the workflow actor codes (REGISTRAR, etc.) in one enum.
- Reason: Exact names in one place.
- Affects: Roles, Workflow

## D-149: Permission names
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Permission names for the 03 §11.1 capabilities; `suspend user` is used for deactivation; `delete user` is removed.
- Reason: Implements D-053 and 03 §11.1.
- Affects: Roles and permissions

## D-150: Designed roles locked
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: The 7 designed roles cannot be renamed or deleted, and the System Administrator cannot also hold a workflow role.
- Reason: Enforces D-023 and D-086.
- Affects: Roles

## D-151: Deactivation effects
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Deactivation ends all of the user's sessions immediately; nobody can deactivate their own account.
- Reason: Safe account control.
- Affects: Identity

## D-152: Numbering year
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Yearly number sequences follow the calendar year in Africa/Mogadishu time, not UTC.
- Reason: Correct year at the year boundary.
- Affects: NumberSequence

## D-153: Ignored advisories
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed with condition)
- Decision: The audit steps ignore two low-severity advisories with no fix (a passkeys library pulled in by Fortify, unused; and @heroui/alert, kept by D-111). Each is listed by advisory ID with a reason in the configuration, re-checked monthly, and removed as soon as a fix exists.
- Reason: No fix available; risk low.
- Affects: composer.json / npm audit configuration

## D-154: Somali placeholders and translation report
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed with addition)
- Decision: lang/so.json holds the English text as a placeholder until the Somali wording is ready. A translation report lists every Somali entry still identical to the English; zero untranslated entries is a go-live requirement (Phase 5). Somali text is drafted in the design Project and reviewed by the IT Advisor or SARIS staff.
- Reason: Missing-key test alone cannot detect untranslated text.
- Affects: lang files, tests, Phase 5

## D-155: Password rule in tests
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Automated tests use Laravel's minimal password rule; the full policy (D-082) is tested directly.
- Reason: Fast tests without weakening the policy.
- Affects: Tests

## D-156: Dropping Passport tables
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: A new migration drops the Passport tables; old migrations stay unchanged.
- Reason: Never edit merged migrations.
- Affects: Migrations

## D-157: CSP and fonts
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed with change)
- Decision: The CSP allows inline style attributes (needed by the UI libraries); scripts require the nonce. The fonts are self-hosted in Phase 1, after which the CSP allows no external font host.
- Reason: Faster pages on slow connections; no outside dependency.
- Affects: CSP, frontend, Phase 1

## D-158: Staff URLs to /admin in Phase 1
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Staff pages and URLs move to /admin in Phase 1; Phase 0 moved only the controllers.
- Reason: Matches the Phase 1 plan.
- Affects: Routes

## D-159: Stored file keys
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed)
- Decision: Stored file keys use `purpose/YYYY/MM/random`.
- Reason: Organised, still random and never reused (D-056).
- Affects: Documents module

## D-160: No database lock on the audit log
- Date: 2026-10-29
- Status: Confirmed
- Source: Stakeholder (amends D-091)
- Decision: The audit log is append-only by application rule only: the audit model refuses updates and deletes, and no code path changes audit rows. There are no MySQL triggers, and the portal uses a single database account with normal privileges. The trigger migration is removed from the unmerged Phase 0 branch.
- Reason: Stakeholder decision; the server is considered adequately protected.
- Affects: Audit module, migrations, 03 §13, deployment

---

## Phase 0 Follow-up Decisions (confirmed 2026-10-29)

## D-161: Reviewed audit allow-list
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-0-follow-up.md)
- Decision: Ignored audit advisories live in `config/security.php` with the advisory ID, package, reason and a `review_by` date 30 days ahead. `php artisan security:npm-audit` fails on any npm advisory that is not listed (all severities) and on any entry past its review date. The Composer list is mirrored in `composer.json` (`config.audit.ignore`) and a test keeps the two in sync.
- Reason: Implements the ID, reason and monthly re-check required by D-153.
- Affects: config/security.php, composer run check, composer.json

## D-162: Translation report
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-0-follow-up.md)
- Decision: `php artisan translations:report` lists Somali entries identical to the English; with `--fail` it is the Phase 5 go-live check for zero untranslated entries (D-154).
- Reason: Implements the translation report in D-154.
- Affects: lang files, Phase 5 go-live checklist

---

## Phase 1 Decisions (confirmed 2026-10-29)

## D-163: Phase 1 and Phase 2 delivered in one go
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Phase 1 (authentication) and Phase 2 (company profile) are built on one branch and delivered as one pull request, with small commits per step. Each phase still has its own approved plan in `docs/plans/`. This replaces the "one pull request per row group" in 09 §3 for these two phases.
- Reason: Stakeholder instruction (2026-10-29).
- Affects: 09 §3, D-121

## D-164: Staff temporary-password expiry column
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: `users` gets a `temp_password_expires_at` column, used to enforce the 24-hour staff temporary password (D-083). 02 §4.1 lists the column only for `company_users`.
- Reason: D-083 needs a stored expiry.
- Affects: 02 §4.1, users table

## D-165: No Fortify registration or email verification
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Fortify's `registration` and `emailVerification` features stay off for both portals. Company sign-up is the custom `/signup` flow. The email address counts as verified when the account first logs in with the emailed temporary password (D-063); staff follow the same rule. The `verified` middleware and the verification pages are removed.
- Reason: 03 §4 replaces both features.
- Affects: Fortify config, auth routes and pages

## D-166: Public URLs
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Company auth pages sit at the root (`/login`, `/signup`, `/temporary-password`, `/forgot-password`, `/reset-password/{token}`, `/two-factor-challenge`) and company app pages under `/portal`, as in 03 §2. `/` is a public landing page with links to the company login and the staff login.
- Reason: 03 §2 gives the URLs; the landing page was not specified.
- Affects: routes/public.php, routes/portal.php

## D-167: Bilingual emails while Somali is a placeholder
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: While a Somali line still equals its English placeholder (D-154), an email shows that text once instead of twice. Once real Somali text exists, both languages are shown as D-125 requires.
- Reason: Avoids every email repeating the same English text.
- Affects: Notifications module

## D-168: Self-hosted font packages
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Fonts are self-hosted with the npm packages `@fontsource/dm-sans`, `@fontsource/fraunces` and `@fontsource/instrument-sans`. The CSP allows fonts from `'self'` only.
- Reason: Implements D-157 with maintained packages.
- Affects: package.json, app.css, SarisPolicy

## D-169: Language switch
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Guests choose the language through `/locale/{en,so}`, stored in a `locale` cookie. Logged-in users use their `preferred_locale`, which the language switch also updates.
- Reason: D-032 lets the user switch; the mechanism was not specified.
- Affects: SetLocale middleware, layouts

## D-170: Email footer settings
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: The email footer contact details and "do not reply" text live in `config/identity.php` until `system_settings` exists (Phase 4B), then move there.
- Reason: 07 assumes system settings; the table arrives later.
- Affects: Notifications module, config/identity.php

## D-171: Permissions for the new admin actions
- Date: 2026-10-29
- Status: Confirmed
- Source: Claude Code (confirmed; plan docs/plans/phase-1-auth.md)
- Decision: Unlocking uses `unlock account`, 2FA reset uses `reset two factor`, re-sending a staff temporary password uses `create user`, and the failed-emails page uses `manage system settings`. No new permissions are added.
- Reason: Reuses the confirmed D-149 permissions.
- Affects: Admin user controller, RolesAndPermissionsSeeder

---

## Open Questions

1. Fee schedule per service and category (F1).
2. Written procedures for the Operator License and Import Permit.
3. ~~Renewal start date~~ — answered by D-107.
4. How long can an application wait in AWAITING_PAYMENT?
5. Which staff role initiates a suspension (Registrar or Departmental Director)?
6. Are revocation, non-renewal and blacklisting (procedure 6.14) needed in Phase 1?
7. Which staff role verifies company products (D-039)? Default: Pesticide Registrar.
8. Droplet details: size, OS, PHP version and other sites hosted.
9. Final domain (assumed `portal.saris.gov.so`).
10. Staging location: subdomain on the same droplet (recommended) or a second droplet.
11. ~~Maximum size for inspection videos~~ — answered by D-135.
12. Official FMS and region list.
13. Formulation types: free text or fixed list?
14. Abandoned applications: with no withdrawal (D-101) and no time limit (C4), may a staff role close an application left in RETURNED or AWAITING_PAYMENT for a long time (e.g. 6 months)?
15. SARIS to update written procedure 6.13 to reflect automatic issue (D-106).
16. Is GitHub Actions enabled on the repository? Until confirmed, `composer run check` is mandatory before every commit (D-114).
17. Release 1 go-live date: not fixed yet; set when Phase 5 is near.
18. SARIS to approve the template wording corrections (06-documents §6) and provide the Somali wording for the Wholesale and Retailer declarations.
19. SARIS to provide the DG signature image, the stamp image, the final .docx templates and a permission-to-pay letter template.
