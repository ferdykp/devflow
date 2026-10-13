# Scope Decision Workflow — 2026-10-12

## Included
- A project owner may propose a scope review with a title and optional description.
- The owner may classify it manually and decide to approve **for planning**, reject it, or move it to Future Backlog.
- A reason is mandatory; optional evidence records the PRD section or supporting notes.
- Decisions are written atomically with audit events, with a row-level lock and a one-decision constraint.
- Pending and resolved reviews appear on the project details page.
- Cross-project and cross-user access is blocked using `ProjectAccess` and project-scoped relations.
- Regression tests cover approval without changing baseline, repeated decision, unauthorized access, and missing reason.

## Intentional limitations
- This is a **manual** scope review workflow, not AI classification or automatic PRD analysis.
- “Approve for planning” does not automatically edit approved PRD content, create tasks, or modify the baseline. Any new scope requires a separate draft/version and explicit approval.
- Existing `scope_baseline` free-text editor is not versioned. Do not use it as an immutable approved contract; approved PRD versions remain the immutable source.
- No real GitHub App installation or AI credentials are included.

## Verify before production
1. Use an isolated test database and run `php artisan test --filter=ScopeWorkflowTest`.
2. Run all tests and `npm run build`.
3. Manually exercise keyboard navigation and decision forms.
4. Ensure backups, queue, scheduler, HTTPS and GitHub credentials work on staging.
