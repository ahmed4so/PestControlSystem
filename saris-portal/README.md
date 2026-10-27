# SARIS Company Portal

The external Company Portal for the Somali Agricultural Regulatory & Inspection Service (SARIS). Companies register, maintain a company profile, and apply online for pest control product licenses and permits. SARIS staff use a separate internal Admin/Staff Portal, which shares this backend and database but is fully separated in access.

## Phase 1 services

1. Registration of Pest Control Products Company License
2. Pest Control Products Operator License
3. Import Permit of Pest Control Products. This requires a valid, approved Company License.

## How this repo works

- System design happens in a Claude.ai Project. Implementation is done with Claude Code.
- This repository is the single source of truth between the two.
- Design deliverables go in `docs/design/`, decisions in `docs/decisions-log.md`, requirements answers in `docs/requirements-answers.md`, and application forms in `docs/forms/`.
- Application code will live in `src/` once the tech stack is chosen.

See [`CLAUDE.md`](CLAUDE.md) for the working rules.

## Status

Scaffolding only. No application code yet.
