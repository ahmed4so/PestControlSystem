# Plan: Phase 2 — Company profile and reference data

- Status: Approved (2026-10-30)
- Branch: `claude/funny-franklin-ikvmdt` (same branch and pull request as Phase 1, D-163)
- Date: 2026-10-30
- Depends on: Phase 1 (company accounts, both portals, notifications)

## 1. Scope

**Goal (09 §3, Phase 2):** a company builds its complete profile once (single source of truth, 02 §6.1). Staff can read it but not change it.

**In scope (the Phase 2 table, all rows):**

| # | Row | Decisions |
|---|---|---|
| A | Reference data: FMS and regions, seeded; admin screens to maintain the lists | D-065 |
| B | Company profile with the structured address; normalisation on save; duplicate registration number blocked | D-065, D-076, 02 §6.2 |
| C | Persons: national ID encrypted plus HMAC hash, unique across the whole system, duplicate message that never names the other company | D-066, D-088, D-117, D-132 |
| D | Premises with the facility checklist; photos required per item (max 5); compression on the phone; re-encoding on the server | D-018, D-089, D-135 |
| E | Documents with type, number, dates, expiry and replacement history; renewed commerce and tax certificates can be uploaded at any time, and the Registrars are told | D-017, D-067, D-073 |
| F | Edit lock: everything is editable before the first submission; the locked rules are built and tested now, ready for Phase 4 | D-073 |
| G | Admin: read-only company directory and company file; national-ID release by the System Administrator | D-095, D-066 |

Also included, because the rows depend on them:
- Upload security for every company file (03 §12.2, D-089): type checked from content, 5 MB limit, images re-encoded, PDFs with JavaScript or encryption refused, downloads as attachments through 5-minute signed URLs.
- The two Phase 2 emails from 07 §3.2:
  - `CERTIFICATE_UPDATED`, sent to the Registrars;
  - `NATIONAL_ID_RELEASED`, sent to the company that lost the person.
- After the first password change, a company without a profile is taken to profile set-up (03 §4 diagram, last step).
- Staff viewing or downloading company documents and IDs is audit-logged (03 §13 "Data access").

**Out of scope:**
- Products / My Products (Phase 4C).
- Applications, snapshots and the actual lock trigger (Phase 4).
- The `CERTIFICATE_EXPIRING` reminder job (Phase 5 scheduled jobs).
- Account recovery (Phase 5).
- Restricted ingredients and fee schedules (their phases).
- **Antivirus scanning:** see open question 1. The plan assumes it is not in Phase 2.

## 2. Design references

- 02 §4.2 (FederalMemberState, Region), §4.3 (StoredFile), §4.4 (Company, Person, Premises, FacilityChecklistItem/Photo, Document), §4.9 (Address), §6.1–§6.2
- 03 §11.3 (ownership, edit lock, files), §12.1–§12.2 (sensitive data, uploads), §13 (data access audit), §15 items 3 and 10
- 05 §2, §4.1–§4.5, §6 (national-ID release)
- 07 §3.2 (`CERTIFICATE_UPDATED`, `NATIONAL_ID_RELEASED`), D-127 (staff role recipients)
- 09 §3 Phase 2 (table, "Done when", prompt)
- Decisions D-017, D-018, D-056, D-065, D-066, D-067, D-073, D-076, D-088, D-089, D-095, D-117, D-127, D-132, D-133, D-135

## 3. Target behaviour (summary)

**Company Portal (`/portal`):**

| Page | What it does |
|---|---|
| Profile set-up | First visit without a company: name and business registration number create the company. A number already registered is refused with "This business registration number is already registered. Please contact SARIS." |
| Company | Name, registration and tax numbers, email, phone, address, Owner/MD (chosen from the persons) |
| Persons | List, add, edit; Owner/MD and technical-person fields; national ID (11 digits); degree certificate and CV for technical persons |
| Premises | List, add, edit; type, address, optional GPS, waste disposal method; checklist of 9 items, each with "available", notes and 1–5 photos |
| Documents | Current document per type with number and dates; upload a replacement; see the history |
| Dashboard | Shows profile completeness and what is still missing |

**Admin Portal (`/admin`):**

| Page | Who | What |
|---|---|---|
| Companies | Staff with `view applications` | Directory with search by name or registration number; company file (read-only): profile, persons with **masked** national IDs, premises with checklist and photos, documents with history. Every file view or download is audit-logged |
| National-ID release | System Administrator (`release national id`) | Search by full national ID, see the person and company, release with a mandatory reason (05 §6) |
| FMS and regions | System Administrator (`manage reference data`) | Add, edit, reorder and deactivate; no deletion of values in use |

## 4. Steps

### Step 1: Reference data (row A)
- **Migrations:** `federal_member_states` (`code`, `name_en`, `name_so`, `sort_order`, `is_active`) and `regions` (FK, the same fields). Both have timestamps and soft deletes (02 §1).
- **Models:** `FederalMemberState` and `Region` in `App\Models`, with factories, `Audited`, and an `active()` scope.
- **`ReferenceDataSeeder`:**
  - Seeds the official SARIS list from `database/seeders/data/fms-regions.php`.
  - Until SARIS sends the list (open question 12 in the log), that file holds a **draft list for development and testing only**, clearly marked. See open question 2.
- **Admin pages:** `Admin\ReferenceData\FederalMemberStateController` and `RegionController`, with permission `manage reference data`.
  - Deactivating a value hides it from new choices but keeps it on existing records.
  - Values in use cannot be deleted.
- **Shared prop:** the `portal` and `admin` pages that need the lists receive `fms` and `regions` as a lazy prop, with active values only.

### Step 2: Company and address (row B)
- **Migration `companies`:** fields as in 02 §4.4, with the `address_*` columns (§4.9).
  - `business_registration_no` is unique; `owner_person_id` is added after `persons` exists.
  - `profile_completed_at` and `amendment_required_at`, plus soft deletes.
- **Migration:** add the foreign key `company_users.company_id → companies.id` (promised in Phase 1).
- **Model `Company`** (module `CompanyRegistry`, model in `App\Models`):
  - `Audited`; relations `persons`, `premises`, `documents`, `owner`, `account`.
  - Address cast to a small value object `Address` (`app/Domain/CompanyRegistry/Data/Address.php`).
- **`Concerns/BelongsToCompany`** trait with a global scope on every company-owned model:
  - When the request is in the Company area, queries are filtered to the logged-in account's `company_id`.
  - Route model binding goes through the scope, so another company's ID gives 404 (03 §11.3).
  - Staff queries are not filtered; they are read-only by policy.
- **Validation rules** (05 §4.1, §4.5): an `AddressRules` helper (region must belong to the chosen FMS), and `FieldFormats` additions: `companyName()`, `regNo()`, `nationalId()`, `country()`, `textShort()`, `textLong()`.
- **Normalisation** (D-076): registration and tax numbers trimmed and uppercased (as well as the Phase 1 email and phone rules).
- **Actions:** `CreateCompany` (name and registration number; links the account; refuses a duplicate number) and `UpdateCompanyProfile`.
- **Profile save:**
  - Saving checks formats only, so a company can fill its profile in several sessions.
  - Required fields are checked by `ProfileCompleteness`, which sets or clears `profile_completed_at` on every save (Proposed D-178).
- **Redirect:** `EnsureCompanyProfileExists` sends an account without a company to profile set-up, except from settings and logout.

### Step 3: Persons and national IDs (row C)
- **Migration `persons`:** fields as in 02 §4.4, including:
  - `national_id_no` as encrypted text;
  - `national_id_hash` as char(64);
  - `active_nid_key` as a **stored generated column**: `IF(released_at IS NULL AND deleted_at IS NULL, national_id_hash, NULL)`, with a unique index. This enforces D-066 in the database (02 §4.4).
- **`NationalIdHasher`:** HMAC-SHA256 of the 11 digits with `config('identity.nid_hash_key')`, read from `NID_HASH_KEY` (D-117). Booting without the key fails outside tests.
- **Person model:**
  - `national_id_no` uses the `encrypted` cast and is hidden.
  - `auditMaskedAttributes()` masks it to `****1234` (D-091).
  - A `maskedNationalId()` accessor serves staff screens.
- **`SavePerson` action:**
  - Normalises the ID, computes the hash and checks the active key.
  - A duplicate gives exactly "This national ID is already registered. Please contact SARIS." The other company is never named.
  - The unique index also catches two saves at the same moment, which give the same message.
- **Field rules (05 §4.2):**
  - The Owner/MD needs an address.
  - Technical fields are required only when the person is used as a technical person in an application (Phase 4). In Phase 2 they are validated for format and counted by completeness only where the profile needs them.
- **Degree certificate and CV:** documents linked to the person (Step 5).
- **Release (row G):** see Step 7.

### Step 4: Premises, checklist and photos (row D)
- **Migrations:** `premises`; `facility_checklist_items` (unique `premises_id` + `item_code`); `facility_checklist_photos` (`stored_file_id`, `captured_at`, soft deletes).
- **Enums:**
  - `PremisesType` (`WAREHOUSE`, `SHOP`, `STORE`, `OFFICE`).
  - `ChecklistItem`, with the 9 codes from 02 §4.4 and `formOneItems()` / `formTwoItems()` for Phase 4.
- **GPS:** optional; if given, it must be inside the Somalia box (lat −2 to 12.5, lng 40.5 to 51.5).
- **Checklist UI:** all 9 items shown on every premises. Each has "available", notes and photos (1–5 per item, D-135).
- **Photo upload:** one photo per request, so a slow connection only re-sends one photo.
  - The browser compresses before upload: canvas, longest side 1600 px, JPEG quality 0.8, no new library.
  - The server checks the type from the content, then re-encodes with GD, which removes EXIF and any hidden payload (D-089).
  - `captured_at` comes from the file's last-modified time sent by the browser, when available (Proposed D-179).
  - Replacing a photo soft-deletes the old row; the StoredFile is kept (02 §4.4).

### Step 5: Documents (row E)
- **Migration `documents`:** fields as in 02 §4.4.
- **Enum `DocumentType`:** the 7 types, with `requiresNumberAndDates()` (commerce and tax) and `belongsToPerson()` (MD ID, degree, CV).
- **`UploadDocument` action:**
  - Stores the file through `FileStorage`.
  - Creates the new `Document` and sets `replaced_by_id` on the current one of the same type (and person), in one transaction (D-067).
- **Field rules (05 §4.4):** the commerce and tax certificate number (`reg_no`), issued date (past) and expiry date (after the issued date) are required.
- **Edit lock exception (D-073):**
  - Commerce and tax certificate renewals can be uploaded even when the profile is locked.
  - Each upload sends `CERTIFICATE_UPDATED` to every active Pesticide Registrar (D-127) and is audit-logged.
- **History:** the documents page shows the current document per type and its earlier versions.

### Step 6: Upload security (03 §12.2, D-089)
New in the `Documents` module:

| Class | Job |
|---|---|
| `UploadInspector` | Detects the type from content (`finfo`); allows PDF, JPG, PNG only; enforces the 5 MB limit; rejects encrypted PDFs or PDFs with JavaScript (`/JavaScript`, `/JS`, `/OpenAction` or `/AA` actions, including inside compressed streams it can inflate) |
| `ImageReencoder` | Decodes and re-encodes JPEG and PNG with GD; refuses images that do not decode |
| `FileStorage` (existing) | Stores with a random key; a new `download()` gives a 5-minute signed URL that forces `Content-Disposition: attachment` and the stored MIME type |

- **Downloads** go through a controller per area that checks ownership:
  - companies: own company only;
  - staff: the company file permission, audit-logged.

  The controller then redirects to the signed URL.
- **Rate limit:** uploads are limited to 30 per minute per user (03 §14).
- **Local development without R2:** the documents disk is set by `DOCUMENTS_DISK`, default `r2`; local `.env` uses `local` (Proposed D-180).

### Step 7: Admin company directory and national-ID release (row G)
- **`Admin\CompanyController`:** `index` (search, paginated) and `show` (company file), with permission `view applications` (Proposed D-177).
  - National IDs are shown masked.
  - Each document or photo view goes through the audited download.
- **`Admin\NationalIdReleaseController`:** permission `release national id` and password confirmation (D-085).
  - The administrator types the full ID; the system finds the active person by hash and shows the person and company.
  - **`ReleaseNationalId` action:** sets `released_at`, `released_by` and `release_reason` (`comment` rule, 10–2,000 characters), which frees the key. It also sets the old company's `amendment_required_at`, sends `NATIONAL_ID_RELEASED` to the old company and writes an audit entry, all in one transaction.
  - The old company sees the person as "Released by SARIS" (read-only).

### Step 8: Edit lock, ready for Phase 4 (row F)
- **`CompanyEditLock` service** (`CompanyRegistry`) answers `canEditProfile(Company)` and `canUploadRenewedCertificate(Company)`.
  - It asks a `SubmissionState` contract whether the company has applications in process.
  - Phase 2 binds an implementation that always answers "nothing submitted", because there are no applications yet. Phase 4 binds the real one.
- **Policies** (`CompanyPolicy`, `PersonPolicy`, `PremisesPolicy`, `DocumentPolicy`) use it for every update route.
  - Tests bind a fake "locked" state and check every update route is refused, except certificate renewal (D-073).
  - This prepares 03 §15 item 5, which completes in Phase 4.

### Step 9: Frontend (mobile-first)
- **Pages:** `pages/portal/company/{setup,edit}`, `pages/portal/persons/{index,form}`, `pages/portal/premises/{index,form}`, `pages/portal/documents/index`, `pages/admin/companies/{index,show}`, `pages/admin/national-ids/release`, `pages/admin/reference-data/{fms,regions}`.
- **Components:**
  - `AddressFields`, where the region list follows the chosen FMS.
  - `FileUpload`, with a progress bar, one file per request and a retry.
  - `PhotoUpload`, which compresses before upload.
  - `CountrySelect`, using names from PHP `intl` (already installed), shared once.
- **Navigation:** the portal header gets Company, Persons, Premises and Documents; the admin sidebar gets Companies, National-ID release and Reference data, each by permission.
- **Text:** all through `t()`, with keys in both language files.

### Step 10: Docs, checks, push
- Update CLAUDE.md (CompanyRegistry, uploads, company scope).
- Record the Proposed decisions; run all checks and a browser check of the phone flow at 360 px; push to the same branch.

## 5. Files (main ones)

| Area | New |
|---|---|
| Migrations | `federal_member_states`, `regions`, `companies`, `company_users.company_id` FK, `persons` (with the generated `active_nid_key`), `companies.owner_person_id` FK, `premises`, `facility_checklist_items`, `facility_checklist_photos`, `documents` |
| Models (+ factories) | `FederalMemberState`, `Region`, `Company`, `Person`, `Premises`, `FacilityChecklistItem`, `FacilityChecklistPhoto`, `Document` |
| `app/Domain/CompanyRegistry` | `Enums/{PremisesType, ChecklistItem, DocumentType}`, `Data/Address`, `Concerns/BelongsToCompany`, `Actions/{CreateCompany, UpdateCompanyProfile, SavePerson, SavePremises, SaveChecklistItem, AddChecklistPhoto, RemoveChecklistPhoto, UploadDocument, ReleaseNationalId}`, `Support/{NationalIdHasher, ProfileCompleteness, CompanyEditLock}`, `Contracts/SubmissionState` |
| `app/Domain/Documents` | `Support/{UploadInspector, ImageReencoder}`, `Exceptions/UnsafeUpload`, `FileStorage::download()` |
| `app/Domain/ReferenceData` | `FieldFormats` additions, `Support/Countries`, `Rules/RegionBelongsToFms` |
| `app/Domain/Notifications` | codes `CERTIFICATE_UPDATED`, `NATIONAL_ID_RELEASED`; Mailables and templates; a "send to every active holder of a role" helper (D-127) |
| Policies | `CompanyPolicy`, `PersonPolicy`, `PremisesPolicy`, `DocumentPolicy` |
| Controllers / requests | `Portal/{CompanyController, PersonController, PremisesController, ChecklistPhotoController, DocumentController, FileController}`, `Admin/{CompanyController, NationalIdReleaseController, FileController, ReferenceData/*}` and their Form Requests |
| Middleware | `EnsureCompanyProfileExists` |
| Seeders | `ReferenceDataSeeder` + `data/fms-regions.php` |
| Config | `identity.nid_hash_key`, `documents.disk`, `documents.max_upload_kb` |
| Frontend | pages and components in Step 9 |
| Tests | §7 |

## 6. Migrations
- All new, run after the Phase 1 migrations.
- `persons.active_nid_key` is a MySQL stored generated column with a unique index; the migration uses `storedAs()`.
- `documents.replaced_by_id` and the photo and person links are restrictive foreign keys; nothing is deleted (soft deletes, D-053).
- `down()` drops what each migration adds.

## 7. Tests

**Required by 09 Phase 2:**

| Test | Proves |
|---|---|
| **03 §15 item 3:** for every company route (show, update, file download), another company's ID gives 404 and its data is never returned; the loop runs over the real route list | Ownership |
| **03 §15 item 10:** a renamed executable (`.exe` as `.pdf`), a 6 MB file, a PDF with JavaScript, an encrypted PDF and a corrupt image are refused; a valid PDF, JPG and PNG are accepted | Upload security |
| **Duplicate national ID:** refused in another company with the exact message; the message never contains the other company's name; refused when two saves race; after a release it is accepted, and the old company has `amendment_required_at` set and receives `NATIONAL_ID_RELEASED` | D-066 |
| **Done when:** a company completes its profile, persons, premises (with photos) and documents through the HTTP flow and `profile_completed_at` is set | 09 Phase 2 |

**Also:**
- **Reference data:** a region must belong to the chosen FMS; inactive values can't be chosen; only the System Administrator manages the lists; values in use can't be deleted.
- **Company:**
  - A duplicate registration number is refused with the SARIS message.
  - Normalisation (uppercase numbers, email, phone).
  - An account without a company is sent to set-up.
  - One company per account.
- **Persons:**
  - National IDs must be exactly 11 digits.
  - The ID is encrypted in the database (the raw column is not the digits) and never appears in the audit log or Inertia props for staff (masked).
  - The hash uses `NID_HASH_KEY`.
- **Premises:** GPS bounds; checklist item unique per premises; 5-photo limit; a replaced photo keeps its file; EXIF removed from stored images.
- **Documents:**
  - A replacement keeps the history.
  - The expiry date must be after the issued date.
  - A renewed certificate is accepted while locked and notifies the active Registrars only (not inactive ones, not other roles).
- **Edit lock:** with a fake "locked" state, every profile, person, premises and document update is refused, and certificate renewal still works.
- **Admin:**
  - The directory and company file need `view applications`.
  - IDs are masked; viewing a document writes an audit entry.
  - National-ID release needs `release national id`, password confirmation and a reason.
- **Downloads:** a signed URL is valid for 5 minutes; it is served as an attachment with the stored MIME type; staff and company download through their own area only.
- **Translations:** every new key is in both language files (existing tests).

## 8. Risks
1. **Hidden JavaScript in PDFs.** Detecting it without a full PDF parser is best effort. The inspector inflates Flate streams and looks for the action keys, which catches the common cases. A dedicated parser or antivirus (open question 1) would close the gap.
2. **The generated column is MySQL-specific.** It is fine for this project (MySQL 8 everywhere, D-050). Tests run on MySQL, so they exercise it.
3. **Phone compression in the browser** depends on the canvas API (all current mobile browsers have it). If compression fails, the original is uploaded and the server limit still applies.
4. **The FMS and region list isn't official yet.** Using a draft list risks wrong values in early data; it is clearly marked and replaceable through the admin screens.
5. **Larastan** still runs only in CI.

## 9. Decisions (confirmed 2026-10-30)

| ID | Proposal | Why |
|---|---|---|
| D-177 | The staff company directory and company file use the existing permission `view applications` (Registrar, Departmental Director, Payment Officer, Director General). The System Administrator sees a person and company only on the national-ID release screen. | 03 §11.1 gives "view applications and company files" to these roles; no separate permission exists. |
| D-178 | The profile saves with format checks only; `profile_completed_at` is set when all of these are present: the company fields of 05 §4.1 with the address; an Owner/MD with nationality, national ID, email, phone and address; current commerce and tax certificates with numbers and dates; the MD national-ID copy; the company profile document. Premises and technical persons are checked per application in Phase 4. | 05 marks profile fields "required at submit"; D-073 says edits are saved immediately. |
| D-179 | A photo's `captured_at` is the file's last-modified time sent by the browser (null if unknown). | Phone compression removes EXIF before upload. |
| D-180 | The documents disk is configurable (`DOCUMENTS_DISK`, default `r2`); local development may use the private `local` disk. Production and staging use R2 (D-056). | Developers have no R2 keys locally. |
| D-181 | Staff see national IDs masked (`****1234`) everywhere except the release screen, where the System Administrator types the full ID. | Least privilege (03 §12.1). |
| D-182 | A business registration number that already exists is refused at profile set-up with "This business registration number is already registered. Please contact SARIS." until account recovery exists (Phase 5). | 02 §6.2 directs these cases to account recovery, which is Phase 5. |

## 10. Answered questions

1. Antivirus follows 09: backlog after Release 2, not Phase 2 (D-183, amends D-089).
2. A draft FMS and region list is seeded for development and testing until SARIS sends the official list (D-184).

## 11. Implementation notes (2026-10-30)

Where the code differs from, or adds detail to, the plan above. None changes the scope.

| Plan said | Code does | Why |
|---|---|---|
| Profile saves with format checks only | Also true for the company and persons; a **premises** needs its address on every save | 05 §4.3 makes the address required, and a premises is created one at a time |
| `FileStorage::download()` gives a signed URL | It also serves a direct attachment when the disk has no signed URLs (local development) | Part of D-180 |
| — | Persons and premises have no delete in Phase 2 | The design defines no deletion for them; released persons stay as history (open question below) |
| — | Browser forms send IDs as strings; the address IDs are cast to integers | Found in the phone check; a test now posts strings |

**Result:** 264 tests pass. Pint, ESLint, TypeScript, both audits and both builds pass; Larastan runs in CI only (open question 16). A browser check at phone width (360 px) took a company from login through profile set-up, a person, company details, a premises with a photo (compressed on the phone, re-encoded on the server) and the four documents, to "Your company profile is complete", with no page errors and no horizontal scrolling.

**Open question:** may a company delete (soft-delete) a person or premises it added by mistake, before any submission? The design does not say. Nothing deletes them for now.
