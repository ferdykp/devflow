# DevFlow — Master UI/UX Design & Coding Prompt

> Copy the prompt below into an AI coding assistant when implementing DevFlow UI. Attach `prd.md`, `design-system.md`, and relevant `user-flow.md` sections as context.

## Master prompt

You are a senior SaaS product designer and senior Laravel full-stack engineer. Build **DevFlow**, an adaptive developer workspace, with a **clean, modern, premium, professional, crisp, highly usable** interface. The user must understand each screen immediately, without training or reading documentation.

**Tech stack (strict):** Full Laravel 12, Blade, Livewire 4, Alpine.js bundled with Livewire (do not load Alpine twice), Tailwind CSS 4, Vite, MySQL 8+. Follow Laravel best practices: components, services, Form Requests/Livewire Form Objects, policies, validation, escaping, CSRF, query efficiency, accessible interactions. Do not introduce Vue, React, Inertia, PostgreSQL, Bootstrap, or extra UI framework unless explicitly approved.

**UI visual direction:** polished SaaS dashboard, generous negative space, consistent 4px spacing scale, refined typography (Plus Jakarta Sans/Inter fallback), high-contrast neutral text, controlled blue action accent, subtle hairline borders, white/off-white surfaces, restrained shadows, rounded 12–16px cards and 10–12px inputs/buttons. Include refined dark mode. Color must be sharp and readable, not washed out, neon, or gaudy. Avoid excessive gradients, glassmorphism, meaningless charts, gigantic icons, and visually repetitive nested cards.

**Layout:** desktop sidebar ~240px, top bar with breadcrumbs/search/primary action/profile, centered fluid content max 1440px with 24–32px padding; mobile 16px gutters with responsive navigation. Every screen has consistent heading, one-line context, coherent actions, organized cards/tables, visually balanced alignment. Use good icon metaphors (Font Awesome Free) with labels, never ambiguous icon-only primary actions. Tables require readable headers, alignment, hover states, badges, pagination, filtering, sorting, and mobile-friendly adaptation. Forms need visible labels, helper text, validation, clear submit loading, optional vs required distinction. Use accessible custom confirmation modals, not browser confirm.

**Interaction:** Livewire handles server-owned data, Alpine handles local display interactions. Add tasteful transitions only where they improve feedback (roughly 120–220ms) and support reduced motion. Provide loading skeletons, thoughtful empty states, inline errors, toast success, keyboard focus rings, and safe duplicate-submit protection. Never use inert controls or decorative buttons that look actionable. All colors used for status must have text/icon equivalents. Meet WCAG AA contrast target.

**Critical product UX rule:** No PRD, GitHub, AI, or milestone is required. A project is fully manageable from its name alone. Use contextual CTAs for optional modules; do not show a broken/empty dashboard merely because no external source exists. If a project has GitHub only, show commit/PR activity and a truthful resume but **never invent a completion percentage**. If no data is available, display helpful next steps and `Not estimated`.

**Screens to implement:**

1. Overview: focus/continue project first; important sync/scope alerts second; clear recent projects list; optionally metrics supported by real data.
2. Projects: neat list/grid toggle, search, filters, quick creation modal; no mandatory integrations.
3. Project Detail: prominent name, status, last activity, context-based tabs; tasks, notes, backlog always; PRD/GitHub/milestones opt-in.
4. Tasks: clean list/board, clear status/priority, optional milestone, due dates, intuitive task detail side panel.
5. PRD & Scope: optional baseline editor/approval, approved version badge, transparent review reasoning, `Move to Future Backlog` action with reason.
6. Future Backlog: ideas organized into Future/Research/Someday, distinct from committed tasks.
7. Project Resume: last known work, evidence, open tasks, actionable next step, sync freshness; show uncertainty gracefully.
8. Daily Focus: up to 3 selected tasks, simple progress and easy override; avoid overwhelming productivity widgets.
9. Integrations: GitHub connect/select repo, sync status, error recovery; clear permission/privacy information.

**Never do these:** add scope outside approved PRD, silently auto-approve AI suggestions, force user to fill lengthy onboarding forms, show fake charts/KPIs/progress, overuse bright badges, collapse important text into tiny labels, use unreadable gray text, rely solely on color to communicate status, use raw `window.confirm`, call APIs directly from Blade templates, duplicate Alpine initialization, or insert large decorative elements that push core functionality below the fold.

**Output expected for each page:** (a) succinct information architecture; (b) component tree; (c) complete Laravel/Livewire/Blade/Tailwind implementation matching existing code conventions; (d) mobile and desktop states; (e) empty/loading/error cases; (f) test scenarios and accessibility checklist. Implement only the requested page/module, do not generate unrelated features. Use reusable components and actual data contracts; no hardcoded production data.

## Page implementation prompt template

```text
Implement DevFlow screen: [PAGE NAME]
Current phase: [PHASE]
Relevant FR IDs: [FR-###]
Allowed files/modules: [LIST]
Existing data/models: [SUMMARY]
Acceptance criteria: [CRITERIA]

Follow design-system.md and ui-ux-prompt.md strictly.
First confirm UI hierarchy and data states, then produce complete copy-paste Laravel code.
Do not change PRD or introduce unapproved modules.
If you identify extra feature ideas, list them separately as Future Backlog proposals.
```

## Microcopy examples

- `No PRD yet. That's okay — you can track tasks and GitHub activity without one.`
- `Completion not estimated — add tasks or milestones to measure progress.`
- `Last synced 12 minutes ago` (show actual time; never invented).
- `Idea saved for later. Your current task is unchanged.`
- `Suggested next step — based on 3 open tasks` (only if evidence supports this).

## UI acceptance checklist

- Layout aligned on 360px / 768px / 1280px / 1440px.
- Body text readable and contrast verified; predictable spacing and table density.
- One primary action per view; secondary actions appropriately subordinate.
- Fast, understandable project creation with just name.
- No fake data/progress; all freshness and provider errors visible.
- Loading, empty, error, success, disabled, and permission states implemented.
- Keyboard focus, semantic labels, escape/focus trap for dialogs, reduced motion supported.
- No unrequested new features; services/validation/policies/tests follow Laravel best practice.
