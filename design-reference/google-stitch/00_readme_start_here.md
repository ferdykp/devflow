# DevFlow 2.0 — Google Stitch UI/UX Design Handoff

**Purpose:** Produce a coherent, high-fidelity, comprehensive desktop + mobile interface design for **every defined screen** of DevFlow Personal Productivity & Growth Workspace. This package is a design brief, **not** a request for a functioning backend.

## Read in order
1. `01_MASTER_STITCH_PROMPT.md` — paste this into Stitch with the reference files.
2. `02_PRODUCT_PRD.md` — business goals, personas, modules, functional expectations.
3. `03_INFORMATION_ARCHITECTURE.md` — navigation and full screen inventory.
4. `04_USER_FLOWS_AND_CRUD.md` — create/read/update/delete/state-change behavior.
5. `05_DESIGN_SYSTEM.md` — visual language and component contracts.
6. `06_SCREEN_SPECIFICATIONS.md` — screen-by-screen required layout and states.
7. `07_SCREEN_ACCEPTANCE_CRITERIA.md` — strict completeness checklist.
8. `08_SOURCE_AUDIT_AND_HANDOFF.md` — old-repo evidence, new vision, implementation handoff.

## Inputs / source distinction
- Inspected source: user-provided `devflow(1).zip`; routes include Dashboard, Projects, Focus, auth, Workspace/Members, Tasks, PRD/Scope, GitHub, Insights.
- Latest user screenshot (Nov 14 2026) shows a `My Day` page with agenda, 3 priorities, quick-add card, and suggestion card. This page is **not** in the old ZIP's web route file; treat screenshot as more recent visual evidence, not proof of other new features.
- Future-state modules in these docs (Planner, Habits, Learning, Goals, Smart Coach, etc.) are design requirements, **not confirmed implemented features**.

## Use in Google Stitch
Upload the Markdown files as context (if permitted); optionally upload the My Day screenshot as a *problem reference*, **not** the finished design standard. Paste the master prompt. Work screen-by-screen or in batches if the generation limit prevents all screens at once. Keep a stable naming convention and use the same design system across batches.

## Definition of success
Every screen in `03_INFORMATION_ARCHITECTURE.md` and `06_SCREEN_SPECIFICATIONS.md` is designed with primary view, realistic populated state, empty/loading/error where applicable, forms or overlays, clear CRUD actions, responsive variants, and traceable cross-module navigation. **No placeholder-only screen and no giant page with multiple permanent input forms.**
