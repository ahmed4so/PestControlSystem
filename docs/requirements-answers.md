# SARIS Company Portal — Requirements Answers

- Project: SARIS Company Portal (Somali Agricultural Regulatory & Inspection Service)
- Source: Stakeholder answers given in the Claude.ai design Project
- Last updated: 2026-10-28
- Rule: These answers override any design assumption. Where an answer was later
  replaced, it is marked **Superseded** with the reference that replaces it.

---

## A. Accounts and Company Users

- **A1:** One user account per company. Multiple users per company are not allowed.
- **A2:** No roles inside a company. The single account holder has full rights.
- **A3:** The account holder submits applications and signs the electronic
  declaration (name, designation, timestamp and IP are recorded).
- **A4:** One user belongs to exactly one company (1:1).
- **A5:** Only companies may apply. Individuals cannot apply, including for sample imports.
- **A6:** No separate profile approval. The company profile is verified as part of
  the first license application.
- **A7:** No migration of legacy paper licenses. Existing licensees apply through
  the portal as new applicants.
- **A8:** **Superseded** by D-073. Company data is editable only before the first
  submission or while a Company/Operator License application is in Draft or Returned;
  it is locked at all other times. An issued license is never modified after approval.
- **A9:** Self-service password reset by email. If the company has lost access to
  its email, it submits a support request to SARIS (staff-assisted recovery).
- **A10:** The account holder may be any person authorized by the company.
- **A11:** No transition period for paper licensees. SARIS announces a cutover date;
  paper permits are processed manually until then.
- **A12:** Every newly issued license receives a new number, including renewals and
  amendments. The previous license becomes *Superseded* and is linked to its successor.
- **A13:** An amended license receives a new full validity period from its approval date.
- **A14:** An amendment is charged the full license fee and goes through the full review.
- **A15:** Account recovery is requested through a public form on the login page,
  with a letter signed by the Owner/MD and a copy of the MD's ID. SARIS staff verify
  the request and reassign the account email.

## B. Forms and Supporting Documents

- **B1:** Form 1 gets a **License Category** field: Importer, Wholesaler or Retailer.
  One category per application, with a separate certificate per category. A company
  may hold several categories through separate applications.
- **B2:** An Import Permit requires a valid, Active **Importation License**,
  enforced in the backend.
- **B3:** The Operator License is independent of the Company License. A company may
  hold either or both.
- **B4:** All listed supporting documents are mandatory. Allowed types: PDF, JPG, PNG;
  maximum 5 MB per file.
- **B5:** Expiry dates of the Ministry of Commerce registration and tax compliance
  certificates are tracked. Submission is blocked if expired; a reminder is sent
  30 days before expiry.
- **B6:** **Superseded** by D-066. A national ID can exist in only one company
  (all persons, including the Owner/MD); duplicates are blocked and the System
  Administrator releases an ID when a person moves.
- **B7:** A photo is mandatory for each facilities and safety checklist item.
- **B8:** A company may register multiple premises; each application selects one or more.
- **B9:** One structured address format everywhere; FMS and region selected from lists.
- **B10:** On the Import Permit, the company enters both "Registered Uses" and
  "Intended Use" for each permit. No automatic validation; staff check during review.
- **B11:** "Adjuvant" is removed from the Import Permit template.
- **B12:** "Warehouse SARIS Approval Reference" is removed from Form 3.
- **B13:** Means of Entry: managed list of Sea, Air, Land. Entry Port: free text.
- **B14:** On license certificates, "Nationality" = Owner/MD nationality;
  "Registered date" = SARIS license approval date.
- **B15:** **Superseded** by D-039. The company adds each product once to
  "My Products" (details, label, SARIS registration certificate). Staff verify it once;
  only Verified products can be used in Import Permit applications.
- **B16:** The product label and product registration certificate are uploaded by the
  company once per product and reused across permits.

## C. Workflows and Staff Roles

- **C1:** **Superseded** by the written procedure (section W). Roles are: Pesticide
  Registrar, Technical Reviewer (optional, formally assigned), Inspector, Departmental
  Director, Payment Officer, Director General (or delegate). The Licensing/Records Officer
  step was removed by D-106 (automatic issue at DG approval).
- **C2:** **Superseded** by the written procedure. The Departmental Director records a
  recommendation or returns the file; only the DG decides.
- **C3:** Premises inspection is mandatory for the Company License and Operator
  License, and not required for the Import Permit.
- **C4:** No time limit for the company to respond to a request for more information.
- **C5:** Rejection reasons are mandatory. No appeal; the company may reapply.
- **C6:** The DG may delegate final approval to a signatory for a defined date range.
- **C7:** (Default) No enforced time limits per stage; dashboards show time at each stage.
- **C8:** Staff cannot edit application data. Corrections are returned to the company.

## D. Licenses

- **D1:** Company and Operator Licenses are valid for 1 year from the issue date.
- **D2:** The renewal window opens 60 days before expiry, with a pre-filled form.
  Per the written procedure, renewal is due at least 30 days before expiry; renewals
  submitted after the 30-day mark are accepted but flagged as late.
- **D3:** A license expires on its expiry date. No grace period. After expiry, a New
  application is required.
- **D4:** See A12 and A13.
- **D5:** Suspension and cancellation are initiated by staff and approved by the DG.
  They block new applications. Import Permits already issued are flagged, not voided.
- **D6:** Number format `PREFIX-YYYY-NNNN`, sequence resets every year per prefix.
  Prefixes: PCPIL (Importation), PCPWS (Wholesale), PCPRT (Retailer),
  PCA (Operator), PCPIP (Import Permit).
- **D7:** The PDF is the official document (QR code, stamp and signature images).
- **D8:** The public QR verification page shows only: number, company name, type,
  status, validity dates.

## E. Import Permit

- **E1:** Valid for a single consignment only.
- **E2:** One product per permit. The field "Number of pest control products arriving
  in this consignment" records the total number of products in the whole consignment,
  for information only.
- **E3:** Every imported product must be SARIS-registered, including samples.
- **E4:** No quantity cap. Staff approve or return.
- **E5:** Valid for 90 days.
- **E6:** No post-import reporting. The Form 3 declaration sentence on annual import
  records is kept as a commitment but is not tracked by the system.
- **E7:** The system checks a banned/restricted active ingredient list. Banned blocks
  submission; restricted is flagged to staff.

## F. Payments

- **F1:** **Pending.** Official fee amounts per service and per license category.
  All fees are in **USD** (D-078).
- **F2:** For all three services, the fee is paid after the Departmental Director's
  recommendation and before the DG decision.
- **F3:** The company pays at the SARIS office, uploads the receipt in the portal and
  enters the receipt number, date and amount. The Payment Officer clears the payment
  or rejects the receipt with a reason.
- **F4:** The portal does not generate invoices or receipts; SARIS uses its own
  receipt book. The portal does generate the **permission-to-pay letter**, issued by
  the Payment Officer.
- **F5:** No refund if the application is rejected after payment.

## G. Integrations

- **G1:** **Corrected 2026-10-28.** A Laravel 12 + Inertia/React scaffold already exists
  with staff user and role management (see docs/design/00-current-state.md). Both
  portals are built on it, sharing one backend and database (D-047).
- **G2:** (Default) No Ministry of Commerce or Ministry of Finance integration;
  certificates are verified manually.
- **G3:** NIDs are verified manually from the uploaded ID copies. No NIRA integration.
- **G4:** Email only in Phase 1 (no SMS), including sign-up OTP and password reset.
- **G5:** Email provider: **Resend**, sending from a saris.gov.so address.
- **G6:** (Default) No integration with other SARIS systems in Phase 1.
- **G7:** Both portals on one domain, separated by login. Staff may log in from
  anywhere with a strong password and mandatory two-factor authentication.

## H. Non-Functional

- **H1:** User interface in Somali and English; data entry in English.
- **H2:** Fewer than 500 companies expected in the first two years.
- **H3:** Hosted on SARIS's existing DigitalOcean droplet(s). **Corrected 2026-10-28:**
  Cloudflare is used only for R2 file storage, not in front of the domain (D-094).
- **H4:** All records are kept forever. Nothing is hard-deleted.
- **H5:** (Default) Data-protection principles: consent at sign-up, encryption,
  minimal public data, full audit trail.
- **H6:** (Default) Mobile-first responsive web; autosaved drafts; compressed uploads.
  No native app.
- **H7:** The IT team works with **PHP / Laravel**.
- **H8:** Go-live order: Company License first, then Operator License and Import Permit.

---

## W. Written Procedure — Company License (adopted 2026-10-27)

The official SARIS written procedure (steps 6.1–6.14) is the authority for the
Company License workflow.

| Step | Status | Actor |
|---|---|---|
| 6.1 Applicant submits (one category) | `DRAFT` → `SUBMITTED` | Company |
| 6.2 Receipt registered, reference assigned, acknowledged | `SUBMITTED` | Pesticide Registrar |
| 6.3 Administrative review (each item: Compliant / Deficient / Clarification required) | `ADMIN_REVIEW` | Pesticide Registrar |
| 6.4 Technical-document review (technical person's qualifications and CV) | `TECHNICAL_DOC_REVIEW` | Pesticide Registrar (optional Technical Reviewer) |
| 6.5 Inspection scheduling (Inspector and planned date recorded) | `INSPECTION_SCHEDULED` | Pesticide Registrar |
| 6.6–6.7 Inspection and signed report | `INSPECTION_REPORT` | Inspector |
| 6.8 Consolidated review | `CONSOLIDATED_REVIEW` | Pesticide Registrar |
| 6.9 Recommendation, or return to Registrar with reasons | `DIRECTOR_REVIEW` | Departmental Director |
| 6.10 Permission-to-pay letter | `PERMISSION_TO_PAY` | Payment Officer |
| 6.11 Payment and receipt upload | `AWAITING_PAYMENT` | Company |
| 6.11 Payment verification | `PAYMENT_VERIFICATION` | Payment Officer |
| 6.12 Final decision: Approved / Deferred / Rejected | `DG_DECISION` | Director General (or delegate) |
| 6.13 License issue and register entry — **automatic at DG approval** (D-106) | `ISSUED` | System |
| 6.14 Monitoring, renewal, enforcement | License lifecycle | SARIS |

Other statuses: `RETURNED` (company action required), `REJECTED`. No withdrawal (D-101).

Rules from the procedure:
- Only the DG can reject. The Departmental Director returns files internally to the
  Registrar with documented reasons.
- The Inspector does not decide; the report goes to the Registrar.
- The four SOPs (handling and storage; spills and waste; emergency response and first
  aid; stock, sales and distribution records) are checked by the Inspector on site.
  They are not uploaded with the application.
- The applicant pays only after receiving the permission-to-pay letter.
- DG "Deferred" returns the application to the company with reasons.

---

## Pending

1. **F1:** Official fee schedule.
2. Written procedures for the **Operator License** and **Import Permit** (the
   stakeholder will share them). Until then, the Operator License follows the Company
   License procedure, and the Import Permit follows it without the inspection steps.
3. Open questions listed in `decisions-log.md` under entries with Status: Proposed.
