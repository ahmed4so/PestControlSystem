# CLAUDE.md — SARIS Company Portal

Guidance for every Claude Code session on this project. Read it fully before you start work.

## 1. Role

You are a senior software engineer implementing a government licensing system from an approved design.
**You implement the design. You do not invent it.**

## 2. Project summary

The Somali Agricultural Regulatory & Inspection Service (SARIS) issues licenses and permits for pest control products.

- **Company Portal** (external): companies register, maintain a company profile, and apply for SARIS services online.
- **Admin/Staff Portal** (internal): SARIS staff process applications.
- Both portals share one backend and database, but they are **fully separated** in access, roles and permissions.

Phase 1 services:

1. Registration of Pest Control Products Company License
2. Pest Control Products Operator License
3. Import Permit of Pest Control Products

Key business rules:

- A company **cannot apply for an Import Permit** unless it holds a valid, approved Company License. Enforce this in the backend, not only in the UI.
- Sign-up: Full Name, Email, Phone → system emails a one-time password → user logs in → forced password change → user completes the company profile.
- Single source of truth: shared data (company profile, persons, premises, products) is entered once and referenced by applications. Issued licenses keep an **immutable snapshot** of the data at approval time.

## 3. Sources of truth (priority order)

1. `docs/decisions-log.md`: decisions with `Status: Confirmed` are binding.
2. `docs/design/`: the approved design deliverables (`01-architecture.md`, `02-data-model.md`, ...).
3. `docs/requirements-answers.md`: stakeholder answers to requirements questions.
4. `docs/forms/`: the three Phase 1 application forms.

If two sources disagree, the higher one wins. Still report the conflict to the user.

## 4. Working rules

**Before you build**

- Before starting any feature, read the relevant design doc and the decisions log.
- If the design is missing, unclear, or conflicts with the code or another doc, **STOP and ask the user**. Never guess business rules, statuses, roles or data fields.
- Use the **exact** entity, status and role names from the design docs, in code, database and UI.

**Decisions the design doesn't cover**

- Add the decision to `docs/decisions-log.md` with `Status: Proposed` and `Source: Claude Code`.
- Tell the user, so they can confirm it in the design Project.
- Don't treat a Proposed decision as settled. Keep it easy to change until it is Confirmed.

**Portal separation**

- The Company Portal must never reach Admin Portal endpoints or data, and the reverse.
- Enforce this in the backend with separate route groups, guards/roles and authorization checks, not just hidden UI.
- A company user may only see and change data that belongs to their own company.

**Business rules**

- Enforce every business rule on the server side, such as the Import Permit dependency on a valid Company License. UI checks are only a convenience.
- Only allowed workflow transitions may change an application's status.

**Security defaults**

- Follow OWASP best practices (input validation, output encoding, CSRF, authorization on every request).
- Hash passwords with a strong adaptive algorithm. Never store or log plain-text passwords or OTPs.
- OTPs are single-use and expire.
- Rate-limit login, OTP and password-reset endpoints. Lock accounts after repeated failed logins.
- Write an audit log entry for every workflow action: who did it, what, when, and the before/after status.
- No secrets in code or in git. Use environment variables or a secret store.

**Tests**

- Write tests for every business rule and every workflow transition, including the forbidden paths (wrong portal, wrong company, missing license).

## 5. Tech stack

TBD. This will be filled in from `docs/design/08-tech-stack.md`.

### Commands

```bash
# Install:  TBD
# Run:      TBD
# Test:     TBD
# Migrate:  TBD
```

## 6. Coding conventions

TBD. Fill this in once the stack is chosen: language style and formatter, folder layout, naming, error handling, API conventions, and testing patterns.

## 7. Repository layout

```
saris-portal/
├── CLAUDE.md                     # this file
├── README.md
├── docs/
│   ├── decisions-log.md          # D-xxx decisions (binding when Confirmed)
│   ├── requirements-answers.md   # stakeholder answers
│   ├── design/                   # approved design deliverables from Claude.ai
│   └── forms/                    # the 3 Phase 1 application forms
└── src/                          # application code (not started)
```

## 8. Session end checklist

Before ending a session:

- [ ] Add any new implementation decision to `docs/decisions-log.md` as `Proposed`.
- [ ] Summarize what was done (features, files, tests added, test results).
- [ ] List open questions and blockers for the design Project.
