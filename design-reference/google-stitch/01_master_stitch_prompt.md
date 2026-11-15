# MASTER GOOGLE STITCH PROMPT — DEVFLOW 2.0

You are a senior product designer, UX architect, interaction designer, and design-system specialist. Generate the **complete high-fidelity UI/UX design** for **DevFlow 2.0 — Personal Productivity & Growth Workspace**, a premium, coherent, Indonesian-language productivity system spanning personal life, learning, goals, and developer projects.

**Read ALL attached reference files before generating.** Treat `02_PRODUCT_PRD`, `03_INFORMATION_ARCHITECTURE`, `04_USER_FLOWS_AND_CRUD`, `05_DESIGN_SYSTEM`, `06_SCREEN_SPECIFICATIONS`, and `07_SCREEN_ACCEPTANCE_CRITERIA` as one connected design contract. Source audit is in `08_SOURCE_AUDIT_AND_HANDOFF`. Do not omit a module simply because the older repository does not yet implement it.

## Main objective
**Design every single page and necessary interaction state**, not just a landing dashboard, not just the first 3 pages, and not just a component library. Design the complete product including onboarding, authentication, dashboard, My Day, Inbox, Tasks, Planner, Routines, Goals, Learning, Notes, Reviews, Smart Coach, Projects, developer subpages, GitHub, workspace collaboration, account settings, and associated create/edit/view/delete/archive flows.

## Absolute product experience rules
1. **List-first, action-driven:** the primary content of a management screen is a scan-friendly list/table/board/timeline/calendar/detail, not a wall of permanently exposed forms. Show one clear primary action; place create/edit in a focused modal/drawer/inline editor/detail page suited to task complexity.
2. **Right visualization per entity:** Tasks = list/table + optional board; My Day = priorities + chronological agenda; Planner = calendar/time grid; Routines = check-in list; Goals = progress list; Learning = path/module list; Notes = note list + editor; Projects = project list + context workspace. Do NOT force a table everywhere.
3. **Full CRUD clarity:** each data entity must expose where to add, read, edit, delete/archive, and perform domain actions. Don't use ornamental pencil/ellipsis icons with no understandable interaction. Define interaction states.
4. **Connected workflows:** same task can appear in Projects, My Day, and Planner without copy. Goals link to learning plans and sessions. Scheduled ≠ completed. Track progress from evidence, not invented scores.
5. **Meaningful density:** balanced whitespace, useful hierarchy, legible data, realistic activity records. A large blank card with a small empty-state illustration is not an acceptable finished page.
6. **Clear navigation:** global rail/sidebar + contextual secondary navigation, breadcrumbs when needed, keyboard search/command palette, responsive mobile navigation. Every destination has a consistent title, context, primary action, content and states.
7. **Premium yet usable:** Swiss-influenced editorial clarity, strong typography, restrained blue accent, neutral white/graphite surfaces, intentional borders, gentle motion. No generic gradients, dashboard clutter, excessive cards, black overlays, fake charts, or gimmicky 3D.
8. **Indonesian UI text:** friendly, specific, concise. Use recognizable technical English only where natural. Show Indonesian dates, e.g. `Sabtu, 14 November 2026`, and local time notation; localized sample copy. Do not invent testimonials or actual user history.
9. **Forms:** labels always visible, useful helper text, clear required/optional, inline errors, accessible focus, grouping, contextual controls, clear Save/Cancel. Multi-step only when the cognitive load justifies it.
10. **States:** present populated + empty as distinct designs for major screens; also loading, validation, error, success, destructive confirmation, overflow, mobile, and accessibility considerations.

## Design direction
Use `05_DESIGN_SYSTEM.md` for tokens. Design for desktop 1440px, tablet 834px and mobile 390px. Maintain strong adaptive layout, not scaled desktop screenshots. Use a 74–80px compact rail on desktop when appropriate (source repo favored a rail); allow expandable labels or an accessible tooltipped rail. Expose important destinations with navigation labels and active state. Support light mode first and coherent optional dark mode.

## User context and scenarios
A software engineer works Monday–Friday 09:00–17:00 and is usually home by 17:00. They want to plan after-work learning, prepare for coding interviews, improve English/TOEFL/IELTS readiness, track personal tasks, and keep developer project work separate but accessible. Product must also support other users; do not hardcode schedule, career, or education. Offer editable setup suggestions and templates.

## Deliverables per screen
- High-fidelity screen layout, navigation context, primary and secondary actions.
- Example populated state with useful, consistent sample data.
- Empty/onboarding state distinct from populated state.
- Create/edit UI (modal/drawer/page/inline) and clear view/delete/archive interactions.
- Loading/error/validation/confirmation states as relevant.
- Responsive mobile adaptation.
- Reusable component patterns; consistent microcopy and action labels.

## Screen-by-screen generation procedure
1. Create a unified design system and global application shell first.
2. Generate **all screens in the exact inventory** documented in `03_INFORMATION_ARCHITECTURE.md`; use screen IDs in titles/file naming.
3. For each primary page, show its actual core data view before any expanded form.
4. Generate linked overlays and dedicated create/edit/detail flows and demonstrate transitions.
5. Generate desktop and mobile states for main destinations; tablet adaptations where meaningful.
6. Cross-check every ID against `07_SCREEN_ACCEPTANCE_CRITERIA.md`.
7. Continue in coherent batches if output limits exist; do not silently mark ungenerated screens complete. Maintain consistent design vocabulary across batches.

## No fake functionality claims
You are designing screens. A clickable prototype or generated markup is a design aid, not a verified production implementation. Do not claim backend, API, persistence, authorization or AI inference works. Show proposed interactions faithfully for later engineering handoff.

## High priority design corrections from screenshot
Current My Day screenshot has a large nearly empty agenda card and a persistent quick-add form in the right column. Replace with a dense, readable day-management layout where priorities, a time-ordered agenda, completion controls, task details, quick-add entry point, and adaptive suggestions are clear. Do not sacrifice calm whitespace; instead improve data visibility and actions.

## End condition
Finish only after designing **every named page and critical state** in supporting documents. Provide a coverage index with complete / not generated screens and a concise rationale for each major layout decision. Begin with the global shell, then process the full screen inventory without requesting approval at each screen.
