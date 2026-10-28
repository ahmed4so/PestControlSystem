# Deliverable 7 — Notifications

- Project: SARIS Company Portal
- Version: 1.1 (approved 2026-10-28)
- Date: 2026-10-28
- Based on: `docs/decisions-log.md` (D-001 to D-124), 03-auth-security, 04-workflow-engine

---

## 1. Principles

| Rule | Detail |
|---|---|
| Channel | Email only in Phase 1 (D-030, G4). No SMS. |
| Sender | `noreply@saris.gov.so` through Resend (D-118); domain verified with SPF, DKIM and DMARC |
| Timing | Queued, and sent only **after** the database transaction commits (D-096). A failed action never sends an email |
| Language | Every email contains **both languages**: the recipient's preferred language first, the other below a divider. The subject is in the preferred language (D-125) |
| Content | Short. The email states what happened and links to the portal page. **No attachments**; letters, licenses and permits are downloaded from the portal after login (D-126) |
| Sensitive data | Never included: national IDs, documents, receipt images, review details beyond the comment. The only secret ever sent is a temporary password (D-063) |
| Links | Plain links to `APP_URL` pages that require login. Only password-reset and email-change links carry a signed token (single use, 60 minutes) |
| Format | HTML with a light SARIS layout **and** a plain-text version (low bandwidth, basic phones) |
| Record | Every send is logged in `notification_logs` (02 §4.12) without the body |
| Failures | Up to 3 retries with back-off. Failed sends are listed for the System Administrator (D-129) |
| Do-not-reply | Footer: "This mailbox is not monitored. Contact SARIS at info@saris.gov.so / +252 612 221 431." |

---

## 2. Recipients

| Recipient | Address used |
|---|---|
| Company | `company_users.email` (the login email) |
| Staff member | `users.email` |
| Staff role | Every **active** staff user holding the role (D-127). If an assignment exists (Technical Reviewer, Inspector), **only the assignee** |
| Account-recovery requester | `new_email` on the request |

Preferred language: `company_users.preferred_locale`; for staff the new column
`users.preferred_locale` (default `en`, D-125).

---

## 3. Notification Catalogue

### 3.1 Accounts and security

| Code | Trigger | To | Says | Link |
|---|---|---|---|---|
| `COMPANY_TEMP_PASSWORD` | Sign-up, or "send a new temporary password" | Company | Temporary password, valid 1 hour; must be changed at first login | `/login` |
| `COMPANY_ACCOUNT_EXISTS` | Sign-up with an already-activated email | Company | You already have an account; use "Forgot password" | `/forgot-password` |
| `STAFF_TEMP_PASSWORD` | Staff account created | Staff member | Temporary password, valid 24 hours; 2FA set-up required | `/admin/login` |
| `PASSWORD_RESET_LINK` | Forgot password (either portal) | Account | Reset link, 60 minutes, single use | Signed link |
| `PASSWORD_CHANGED` | Any password change or reset | Account | Your password was changed; other sessions were signed out; contact SARIS if this was not you | Login page |
| `PASSWORD_EXPIRY_REMINDER` | 14 days before the 90-day expiry (D-082) | Account | Your password expires on {date} | Settings |
| `ACCOUNT_LOCKED` | 5 failed logins (D-081) | Account | Locked for 15 minutes; reset your password if this was not you | Forgot password |
| `TWO_FACTOR_CHANGED` | 2FA enabled, disabled or reset | Account | Two-factor authentication was {enabled / disabled / reset} | Settings |
| `EMAIL_CHANGE_CONFIRM` | Company requests login-email change (D-093) | **New** address | Confirm the new email | Signed link |
| `EMAIL_CHANGE_NOTICE` | Same request | **Old** address | A change of login email was requested; contact SARIS if this was not you | — |
| `RECOVERY_RECEIVED` | Account recovery request submitted | Requester | Received, reference AR-…; SARIS will contact you | — |
| `RECOVERY_APPROVED` | Request approved (D-092) | New email | Temporary password, valid 1 hour | `/login` |
| `RECOVERY_OLD_EMAIL_NOTICE` | Request approved | Old email | Your account email was changed by SARIS | — |
| `RECOVERY_REJECTED` | Request rejected | Requester | Rejected, with reason | — |
| `DELEGATION_GRANTED` | DG creates a delegation (D-027) | Delegate | You may act for the DG from {date} to {date} | `/admin` |
| `DELEGATION_ENDED` | Delegation revoked early | Delegate | Delegation ended | — |

### 3.2 Company data and products

| Code | Trigger | To | Says | Link |
|---|---|---|---|---|
| `CERTIFICATE_EXPIRING` | 30 days before a commerce or tax certificate expires (D-017) | Company | Upload the renewed certificate before {date}; submissions will be blocked after | Documents |
| `CERTIFICATE_UPDATED` | Company uploads a renewed certificate (D-073) | Role: Registrar | {Company} uploaded a renewed {certificate} | Company file |
| `NATIONAL_ID_RELEASED` | System Administrator releases a person's ID (D-066) | Company that lost the person | A person was removed from your profile; submit an amendment | Profile |
| `PRODUCT_PENDING` | Product added or changed (D-039) | Role: Registrar | Product {trade name} of {company} awaits verification | Product |
| `PRODUCT_VERIFIED` | Product verified | Company | {trade name} can now be used in Import Permit applications | My Products |
| `PRODUCT_REJECTED` | Product rejected | Company | Rejected, with reason | My Products |

### 3.3 Application workflow

`{ref}` = application reference; `{service}` = service and category name.

| Code | Trigger (04 transitions) | To | Says |
|---|---|---|---|
| `APPLICATION_SUBMITTED` | T1 SUBMIT | Company | {service} application {ref} received |
| `WORK_ITEM_READY` | Application **enters any staff stage** (T1, T3, T7 assignee, T11, T12, T15, T16, T18, T19, T25…) | Role of the new stage, or the assignee | {ref} is waiting for {stage} |
| `APPLICATION_ACKNOWLEDGED` | T2 ACKNOWLEDGE | Company | Your application is under review |
| `APPLICATION_RETURNED` | T4, T8, T13 RETURN_TO_COMPANY; T22 DEFER | Company | Action required: SARIS returned {ref}. Lists each deficient or unclear item with its comment (or the DG's comment) |
| `APPLICATION_RESUBMITTED` | T25 RESUBMIT | Role of `resume_stage` | {ref} was resubmitted (version {n}) |
| `REVIEWER_ASSIGNED` | T5 ASSIGN_TECHNICAL_REVIEWER | Technical Reviewer | You are assigned to {ref} |
| `TECHNICAL_REVIEW_DONE` | T6 | Registrar who assigned | Technical review of {ref} completed |
| `INSPECTION_SCHEDULED` | T7, T14 | Company and Inspector | Inspection of {premises} planned for {date} |
| `INSPECTION_RESCHEDULED` | T9 | Company and Inspector | Inspection moved to {date} |
| `PAYMENT_REQUIRED` | T17 ISSUE_PERMISSION_TO_PAY | Company | Permission to pay {amount} USD issued (letter {PTP no.}); pay at the SARIS office, then upload the receipt |
| `RECEIPT_REJECTED` | T20 | Company | Receipt not accepted: {reason}; upload a correct receipt |
| `PAYMENT_CLEARED` | T19 | Company | Payment confirmed; your application goes to the Director General |
| `LICENSE_ISSUED` | T21 APPROVE, after commit (D-106) | Company | {license type} {number} issued, valid until {date}; download it from the portal |
| `APPLICATION_REJECTED` | T23 | Company | {ref} rejected, with the DG's reason; you may submit a new application |

Staff never receive an email for actions they performed themselves.

### 3.4 License lifecycle

| Code | Trigger | To | Says |
|---|---|---|---|
| `RENEWAL_REMINDER` | 60 and 30 days before expiry (D-009) | Company | {license} expires on {date}; renew now (renewal due 30 days before expiry) |
| `LICENSE_EXPIRED` | Daily job sets EXPIRED | Company | {license} has expired; a new application is required |
| `IMPORT_PERMIT_EXPIRING` | 14 days before a permit expires | Company | Permit {number} expires on {date} (single consignment) |
| `ENFORCEMENT_PENDING` | Enforcement action requested (D-010) | DG | {action} requested for {license}, awaiting your decision |
| `ENFORCEMENT_DECIDED` | DG decides | Requester | Approved / rejected |
| `LICENSE_SUSPENDED` / `LICENSE_REINSTATED` / `LICENSE_CANCELLED` | Enforcement approved | Company | Status change with reason |
| `PERMITS_FLAGGED` | Importation License suspended or cancelled | Company | Your active import permits are flagged |
| `LICENSE_SUPERSEDED` | Renewal or amendment starts | Company | {old} replaced by {new} |

---

## 4. Templates

- One Laravel Mailable per code in `app/Domain/Notifications/Mail/`, with Blade templates
  `resources/views/mail/{code}.blade.php` and a plain-text version.
- All text comes from `lang/en.json` and `lang/so.json` (D-116); one template renders both
  languages in the order of D-125.
- Subject format: `SARIS — {short subject} — {reference}`.
- Shared layout: SARIS name, plain header (no heavy images), content, link button, plain-text
  URL under the button, footer of §1.

**Example (`APPLICATION_RETURNED`, Somali preferred):**

```
Subject: SARIS — Codsigaaga waxaa loo celiyey — CL-2026-00012

[Somali text first]
Codsiga CL-2026-00012 waa la idiin soo celiyey si aad u saxdaan:
• Shahaadada canshuurta: taariikhda dhicitaanka ma cadda.
Fadlan gal: https://portal.saris.gov.so/portal/applications/…
────────────────────────
[English text]
Application CL-2026-00012 has been returned for correction:
• Tax compliance certificate: expiry date not readable.
Please sign in: https://portal.saris.gov.so/portal/applications/…
```

Somali wording above is illustrative; SARIS reviews all Somali text (D-116).

---

## Open questions

None.

## Assumptions made

- [ASSUMPTION] The SARIS contact details in the footer match the official templates
  (+252 612 221 431, info@saris.gov.so); they are stored as settings and can be changed.

## Decisions (confirmed 2026-10-28)

| Decision | Summary |
|---|---|
| D-125 | Every email contains both languages, preferred language first; staff get a `preferred_locale` column (default English). |
| D-126 | No attachments; emails link to portal pages that require login. |
| D-127 | Staff notifications go to all active holders of the stage role, or only to the assignee when there is one; never for the user's own action. |
| D-128 | The notification catalogue in §3 (codes, triggers, recipients). |
| D-129 | Queued after commit, 3 retries, failures visible to the System Administrator, logged without the body. |
| D-130 | HTML plus plain-text versions with a light layout; subject format in §4. |
