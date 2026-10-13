# DevFlow — Product Requirements Document

**Version:** 1.0 (proposed baseline)  
**Date:** 2026-10-10  
**Status:** Draft for approval  
**Stack:** Laravel 12, Livewire 4, Blade, Alpine.js (bundled with Livewire), Tailwind CSS 4, MySQL 8.0+, Vite  
**Product statement:** *One Workspace. Any Development Workflow.*

## 1. Problem and vision
Developers juggle projects and lose track of progress, especially when changing project context, coding with AI, accepting unplanned feature suggestions, and writing vague Git commit messages. DevFlow brings plans, Git activity, tasks, ideas, and next actions into a single lightweight workspace with minimal repetitive input.

**Core principle:** No PRD, GitHub connection, AI provider, or milestone is mandatory. A project can be created using **name only** and expanded later without losing its prior history. No module should invent data to mask a missing source.

## 2. Goals and non-goals
### Goals
- Quick centralized overview of projects, status, latest known activity, and priority.
- Track GitHub events automatically *after remote delivery*, while supporting manual notes/tasks without GitHub.
- Resume a project with evidence-linked context and clear data freshness.
- Support optional, immutable approved PRD baselines, requirements, scope reviews, future ideas, and controlled changes.
- Suggest daily focus (up to three tasks), always editable by user.
- Minimize manual actions and prevent AI-driven scope creep.
- Create useful coding-assistant context for an active task.
- Add commit suggestions through a local CLI in a later release; the website alone cannot inspect local staged changes.

### Non-goals for V1
- Replacing GitHub, the IDE, or complete Jira workflows.
- Reading conversations in third-party AI tools automatically.
- Automatic code edits, Git commits/pushes, or approving PRDs without user confirmation.
- Full offline data editing, multi-user billing, time tracking, deployment monitoring, full source indexing.
- LLM-driven project completion percentages.

## 3. Users and project workflows
Primary: individual full-stack developer. Later: small development team.

**Templates (presets, not irreversible project types):** Basic; Git-based; Task-based; Structured/PRD; Experimental. Users can turn capabilities on/off without deleting underlying project history.

| Source combination | What works | What must not be inferred |
|---|---|---|
| Name only | project overview, settings, notes, idea backlog, manual status | no activity or completion percentage |
| Tasks only | task board, daily focus, progress as completed/total | no Git evidence |
| GitHub only | activity feed, Git-derived resume, issue/PR overview | no % completed from commits |
| PRD only | requirements, scope baseline, manual task breakdown | no automatically completed requirements |
| PRD + GitHub + tasks | evidence-linked scope reviews, task progress, project resume | no auto scope approvals |
| AI disabled | deterministic summaries, activity, tasks, scope review records | no LLM-only capabilities |

## 4. Functional requirements
Priorities: P0 required MVP; P1 near-term; P2 later.

| ID | Priority | Requirement | Acceptance criteria |
|---|---|---|---|
| FR-001 | P0 | Authentication and owned workspaces | authorized access; foreign workspace data returns 403/404 |
| FR-002 | P0 | Projects CRUD, name-only creation, archive/pause | project works without PRD, task, or GitHub |
| FR-003 | P0 | Project notes and idea backlog | can save title, description, source, bucket; idea does not become active task |
| FR-004 | P0 | Tasks without milestone | independent task CRUD, statuses, priority, due date |
| FR-005 | P0 | Optional milestones, task weights | progress computed only with valid task basis; otherwise Not estimated |
| FR-006 | P0 | GitHub App install and repository linking | select least-privilege repos; one project can own multiple repo links |
| FR-007 | P0 | Remote Git activity synchronization | initial backfill, verified webhooks, idempotency, resync, sync timestamps |
| FR-008 | P0 | Adaptive project resume | deterministic snapshot from available activity, tasks, notes; evidence/freshness included |
| FR-009 | P0 | Optional PRD drafts and approvals | immutable approved content and traceable new revisions |
| FR-010 | P0 | Scope baseline without PRD | manually record project goals/in-scope/out-of-scope; no PRD dependency |
| FR-011 | P0 | Scope reviews and decisions | classify in_scope/potential_out_of_scope/focus_misalignment/unplanned/uncertain/not_assessable |
| FR-012 | P0 | Move to Future Backlog | atomic decision + backlog item + event, no active task/PRD/progress mutation |
| FR-013 | P0 | Focus context generator | copyable, data-grounded task context and development boundaries |
| FR-014 | P1 | Change requests and PRD revisions | approved request creates candidate draft; does not edit approved snapshot |
| FR-015 | P1 | Daily focus planner | max 3 suggestions, user override; paused and blocked handling |
| FR-016 | P1 | AI summary/assistant | opt-in provider, sourced insights, graceful fallback without AI |
| FR-017 | P1 | PWA installability | manifest + service worker + offline fallback page; avoid sensitive caching |
| FR-018 | P2 | Local commit suggestion tool | staged diff only, preview/approval before local commit; secret scanning |
| FR-019 | P2 | VS Code extension/team collaboration | optional, separate project scopes and RBAC |

## 5. Domain rules
1. Project is independent of PRD/repository/milestone/AI. Disabling integration never deletes history by default.
2. An approved PRD snapshot cannot be edited; new approval supersedes prior version transactionally. Track approval actor/time/hash.
3. Scope classification is **advisory** and never silently changes scope. If no authoritative baseline, use `not_assessable` or `unplanned`, never a definitive violation.
4. Backlog ideas have separate lifecycle from tasks. Promoting an idea requires explicit change control or user-confirmed task conversion depending on the baseline.
5. Task `done` should have user approval or explicit trusted validation; Git events are evidence, not proof of completion.
6. Task and milestone % use finished weighted tasks; if denominator missing, display Not estimated. Git-only shows activity, not completion %.
7. Git events are eventually consistent; show latest sync time/error state. Local unpushed changes invisible to GitHub-only MVP.
8. AI may suggest but cannot modify PRD approval, scope decision, task completion, or Git history without explicit action.
9. Every AI assertion that affects workflow should link to activity/task/requirement IDs and state uncertainty.
10. Project metadata, task, and reviews remain accessible even if external integrations are unavailable.

## 6. Screens/navigation
Global: Overview, Projects, My Focus, Activity, Future Backlog, AI Insights (optional), Settings. Project: Overview, Tasks, Milestones (conditional), Activity (conditional), Documents/PRD (conditional or add CTA), Scope, Resume, Backlog, Settings. Avoid empty permanent tabs where no data exists; accessible CTA should reveal capabilities.

## 7. Critical user journeys
A. Create project name-only -> add note/task -> work.  
B. Connect selected GitHub repo -> backfill -> webhook events -> activity + resume.  
C. Upload/paste PRD optional -> extract draft requirements -> user approves -> freeze version.  
D. Suggest idea -> scope review -> Move to Future Backlog -> atomic audit -> continue active task.  
E. Resume after inactivity -> show last known context, stale data warning, next action.  
F. Choose focus task -> copy AI development context with boundaries.  
G. Later promote backlog -> change request -> approve -> draft new PRD -> version approval.

## 8. Non-functional requirements
- Security: session cookie auth; CSRF; Laravel Policies for every project resource; rate limiting; encrypted secrets; scoped GitHub App permissions; validated webhook signature; redact sensitive logs.
- Reliability: queue retries/backoff; unique event keys; transactional decisions; webhook reconciliation; visibility into failed syncs.
- Performance targets (initial): server-rendered dashboard typical response under 1s on reasonable VPS/data; paginated lists; indexed queries; no N+1; queue heavy sync and AI work. Targets require measurement.
- Accessibility: keyboard controls, visible focus, labels, WCAG AA contrast target, reduced motion support.
- Privacy: metadata-first, no diff/source sent to AI by default; opt-in provider selection and clear consent.
- Testing: feature/unit/Livewire tests including no-PRD/no-GitHub/no-AI, cross-workspace authorization, duplicate webhooks, immutability and transactional backlog.
- Operability: logs, queue worker supervision, daily backup/recovery drill, `APP_DEBUG=false` in production.

## 9. MVP boundaries and releases
**V1 MVP:** Auth, workspace/project, notes, tasks/milestones, GitHub read sync, deterministic resume, PRD optional baseline, scope review + Future Backlog, context copy, polished responsive UI.  
**V1.1:** daily focus scoring, change request workflow, AI opt-in summaries, basic PWA.  
**V1.2+:** local staged-diff CLI commit assistant, VS Code extension, collaboration, expanded analytics.

## 10. Definition of Done
- Name-only project can be used across core dashboard, notes, backlog.
- Optional components can be attached/reconfigured later without data loss.
- GitHub event ingestion verified, idempotent and observable.
- No GitHub-only false progress percent; no PRD-only false completion.
- Move to Backlog preserves PRD, tasks, milestone % and audit records.
- Cross-project and cross-workspace authorization tests pass.
- UI keyboard/mobile/responsive and error/empty/loading states pass review.
- PRD-approved data never edited in place; all overrides are explicit.

## 11. Change control for DevFlow itself
This document is **proposed**, not an already user-approved immutable baseline. During build: log every feature proposal in `docs/ideas.md`; update this PRD only after a deliberate approval, record version, rationale, affected modules and tests. No unsolicited feature expansion by coding AI.
