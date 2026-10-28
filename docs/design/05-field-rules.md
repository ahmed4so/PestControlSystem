# Deliverable 5 — Field Validation Rules

- Project: SARIS Company Portal
- Version: 1.1 (approved 2026-10-28)
- Date: 2026-10-28
- Scope (D-120): per-field validation for every form. Lifecycle and business rules are in
  02-data-model and 04-workflow-engine.

---

## 1. General Rules

1. **Server-side always.** Every rule is enforced in a Form Request. The browser repeats the
   simple rules only to help the user.
2. **Drafts save without required checks** (D-131). A draft accepts incomplete data, so
   autosave never fails (D-046); format rules still apply to filled fields. **All** required
   rules and workflow guards run at `SUBMIT` / `RESUBMIT`.
3. **Normalise first, then validate** (D-076): trim spaces; lowercase emails; phones to
   international format; registration, tax and ID numbers uppercase without spaces.
4. **Messages** are translated (D-116), shown next to the field, and say how to fix the problem.
5. **Lengths** below match the database columns in 02-data-model.

## 2. Common Formats (D-133)

| Format | Rule |
|---|---|
| `name` | 2–150 characters; letters (any alphabet), spaces, apostrophe, hyphen, full stop |
| `company_name` | 2–200 characters; letters, digits, spaces and `& . , - ' ( )` |
| `email` | Valid email address (RFC), max 190; lowercase |
| `phone` | International format `+` and 8–15 digits. A number typed without a country code is treated as Somali (`+252`) |
| `reg_no` | 3–60 characters; letters, digits, `-` and `/` |
| `national_id` | Exactly **11 digits** (NIRA format) |
| `country` | ISO 3166-1 two-letter code from a list |
| `date_past` | Valid date, not in the future |
| `date_future` | Valid date, after today |
| `text_short` | Max 255 characters |
| `text_long` | Max 2,000 characters |
| `comment` | 10–2,000 characters |
| `file_doc` | PDF, JPG or PNG by content; max 5 MB (D-017, D-089) |
| `file_photo` | JPG or PNG; compressed on the phone; max 5 MB after compression |

---

## 3. Accounts

### 3.1 Company sign-up (D-063)

| Field | Required | Rules |
|---|---|---|
| Full name | Yes | `name` |
| Email | Yes | `email` |
| Phone | Yes | `phone` |
| Consent to privacy notice | Yes | Must be ticked |
| Honeypot field | — | Must be empty; form filled in ≥ 3 seconds (D-080) |

### 3.2 Login, passwords, email change

| Field | Rules |
|---|---|
| Password (new) | 12+ characters, upper and lower case, number, symbol, not breached, not one of the last 5 (D-082) |
| Password confirmation | Must match |
| New login email (D-093) | `email`; not used by another company account |

### 3.3 Staff user (System Administrator)

| Field | Required | Rules |
|---|---|---|
| Name | Yes | `name` |
| Email | Yes | `email`; unique among staff |
| Phone | No | `phone` |
| Roles | Yes, at least 1 | System Administrator cannot be combined with a workflow role (D-086) |
| Preferred language | Yes | `en` or `so` (D-125) |
| Signature image | No (required for DG and delegates before they sign) | PNG with transparent background, max 1 MB (D-140) |

### 3.4 Account recovery request (D-003)

| Field | Required | Rules |
|---|---|---|
| Company name | Yes | `company_name` |
| Business registration no. | Yes | `reg_no` |
| Contact name, phone | Yes | `name`, `phone` |
| New email | Yes | `email` |
| Letter signed by Owner/MD | Yes | `file_doc` |
| Copy of MD's ID | Yes | `file_doc` |

---

## 4. Company Profile (D-073: editable only when allowed)

### 4.1 Company

| Field | Required at submit | Rules |
|---|---|---|
| Company name | Yes | `company_name` |
| Business registration no. | Yes | `reg_no`; unique across companies (dedup key) |
| Tax compliance certificate no. | Yes | `reg_no` |
| Company email | Yes | `email` |
| Company phone | Yes | `phone` (printed on licenses) |
| Address | Yes | See §4.5 |
| Owner / Managing Director | Yes | A Person (§4.2) |

### 4.2 Person (Owner/MD or technical person)

| Field | Owner/MD | Technical person | Rules |
|---|---|---|---|
| Full name | Yes | Yes | `name` |
| Nationality | Yes | Yes | `country` |
| National ID no. | Yes | **Yes** (D-132) | `national_id`; unique across the whole system (D-066); duplicate message does not name the other company |
| Email | Yes | Yes | `email` |
| Phone | Yes | Yes | `phone` |
| Address | Yes | No | §4.5 |
| Qualification | — | Yes | `text_short` (e.g. "BSc Agriculture"); minimum BSc is checked by the reviewer |
| University / institution | — | Yes | `text_short` |
| Years of experience | — | Yes | Whole number 0–60 |
| Training in PCP handling | — | Yes (Company and Operator License) | `text_long` |
| Training in fumigation | — | Yes (Operator License) | `text_long` |
| Degree certificate, CV | — | Yes | `file_doc` (Documents `DEGREE_CERTIFICATE`, `CV`) |

The technical person's national ID is not on the paper forms; it is added because D-066
blocks duplicates for every person.

### 4.3 Premises

| Field | Required | Rules |
|---|---|---|
| Label | Yes | 2–100 characters |
| Type | Yes | Warehouse, Shop, Store, Office |
| Address | Yes | §4.5 |
| GPS | No | Latitude −2 to 12.5, longitude 40.5 to 51.5 (Somalia); outside the box is refused |
| Waste disposal method | Required when used as Import Permit storage | `text_short` |

**Facility checklist (per premises):**

| Field | Rules at submit |
|---|---|
| Each item required by the service (Form 1: 6 items; Form 2: 9 items) | Must be "Yes" (available) |
| Photos per item | At least 1, at most 5 (D-135); `file_photo` |
| Notes | Optional, `text_long` |

### 4.4 Documents

| Type | File | Number | Issued on | Expires on |
|---|---|---|---|---|
| Commerce registration certificate | Yes | Yes (`reg_no`) | Yes (`date_past`) | Yes; after issued date; must be in the future at submit (D-017) |
| Tax compliance certificate | Yes | Yes (`reg_no`) | Yes (`date_past`) | Yes; same rule |
| MD national ID copy | Yes | — | — | — |
| Company profile | Yes | — | — | — |
| Degree certificate, CV | Yes | — | — | — |
| Equipment inventory (Operator License) | Yes | — | — | — |

### 4.5 Address (all owners, D-065)

| Field | Required | Rules |
|---|---|---|
| FMS | Yes | From list |
| Region | Yes | From list; must belong to the chosen FMS |
| District | No | Max 100 |
| Town / market | Yes | 2–150 |
| Street | No | Max 150 |
| Details (building, landmark) | No | `text_short` |

---

## 5. Applications

### 5.1 Company License (Form 1)

| Field | Required at submit | Rules |
|---|---|---|
| License category | Yes | Importer, Wholesaler or Retailer (D-014) |
| Application type | Yes | New, Renewal or Amendment; Renewal/Amendment needs a previous Active license (D-103) |
| Premises | Yes, at least 1 | Own company's premises; checklist complete (§4.3) |
| Technical person | Yes, exactly 1 | Own company's person with all technical fields (§4.2) |
| Declaration name | Yes | `name`; pre-filled with the account holder's name |
| Declaration designation | Yes | 2–150 characters |
| "I confirm the information is true" | Yes | Must be ticked |
| Password confirmation | Yes | D-085 |

### 5.2 Operator License (Form 2)

As §5.1 without category; Form 2 checklist items and the equipment inventory document are required.

### 5.3 Import Permit (Form 3)

| Field | Required at submit | Rules |
|---|---|---|
| Product | Yes | Own company's product with status Verified (D-039) |
| Storage premises | Yes, exactly 1 | Own premises with waste disposal method filled |
| Quantity | Yes | Number > 0, max 9,999,999.999, up to 3 decimals |
| Unit | Yes | L or kg |
| Registered uses | Yes | `text_long` |
| Intended use (crop and pest target) | Yes | `text_long` |
| Entry port | Yes | 2–150 characters, free text (D-019) |
| Means of entry | Yes | Sea, Air or Land |
| Purpose | Yes | Commercial or Sample |
| Number of products in the consignment | Yes | Whole number 1–999 (information only) |
| Declaration, confirmation, password | Yes | As §5.1 |

### 5.4 Products (My Products, D-039)

| Field | Required | Rules |
|---|---|---|
| Trade (commercial) name | Yes | 2–200 |
| Active ingredient | Yes | 2–200; banned ingredient refused, restricted flagged (D-021) |
| Concentration (% a.i.) | Yes | 1–100 characters, e.g. "48% EC" |
| Formulation type | Yes | 1–50 characters (free text until a list is agreed) |
| SARIS registration no. | Yes | `reg_no`; unique within the company |
| Country of origin | Yes | `country` |
| Registration no. in country of origin | Yes (D-134) | `reg_no` |
| Manufacturer name | Yes | `company_name` |
| Manufacturer address | Yes | Max 500 |
| Label (English and Somali) | Yes, at least 1 file | `file_doc` |
| SARIS product registration certificate | Yes | `file_doc` |

---

## 6. Staff Forms

| Form | Field | Rules |
|---|---|---|
| Review item | Result | Compliant, Deficient or Clarification required |
| | Comment | Required unless Compliant; `comment` |
| Any return, defer, reject, re-inspection, director return | Comment | Required; `comment` |
| Inspection scheduling | Inspector, planned date | Inspector holds the role; date today or later |
| Inspection report (D-077, D-136) | Each checklist item | Compliant, Non-compliant or N/A |
| | Observed facts | Required, max 5,000 |
| | Recommendations, deficiencies | Optional, max 5,000; deficiencies required if any item is Non-compliant |
| | GPS | Captured from the device; if unavailable, a reason is required |
| | Photos | At least 1 per Non-compliant item; `file_photo` |
| | Video | Optional; MP4; max 50 MB (D-135) |
| Permission-to-pay letter | — | No input fields: fee comes from the schedule (D-038) |
| Receipt (company) | Receipt no. | Required, 1–60 characters, letters, digits, `-` `/` |
| | Payment date | Required; not in the future; not before the letter date |
| | Amount | Required; > 0; must equal the letter amount at clearance (USD) |
| | Receipt file | Required; `file_doc` |
| Fee schedule | Amount | > 0, 2 decimals, USD |
| | Effective from / to | No overlapping periods for the same service, category and type |
| Delegation | Delegate, dates | Active staff user other than the DG; end ≥ start; no overlap with another active delegation |
| Restricted ingredient | Name, level | Name unique; Banned or Restricted |
| Enforcement action | Action, reason | Reason required; `comment` |
| National-ID release | Reason | Required; `comment` |

---

## Open questions

None.

## Assumptions made

- [ASSUMPTION] Somalia GPS bounding box in §4.3.

## Decisions (confirmed 2026-10-28)

| Decision | Summary |
|---|---|
| D-131 | Drafts save without required-field checks; full validation and guards run at submit and resubmit. |
| D-132 | The technical person's national ID is required (needed by D-066), although not on the paper forms. |
| D-133 | Common formats in §2 (names, phone with +252 default, registration numbers, national ID of exactly 11 digits, files). |
| D-134 | "Registration no. in country of origin" is required for products. |
| D-135 | Up to 5 photos per checklist item; inspection videos up to 50 MB (closes open question 11 in the log). |
| D-136 | GPS is captured on the inspection report, with a reason required if the device cannot provide it. |
