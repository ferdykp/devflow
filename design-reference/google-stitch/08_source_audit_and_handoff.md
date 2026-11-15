# Source Audit (Grounding) and Engineering Handoff

## Evidence inspected
User-provided `devflow(1).zip` was inspected at the file/listing and source-text level. It contains a Laravel application with `routes/web.php`, Blade resources, Tailwind/Vite, project and focus interfaces, and a substantial amount of bundled vendor/node_modules data. No runtime, browser or authenticated application testing was performed for this handoff.

### Verified relevant routes in archived ZIP
- Login / registration / logout
- Dashboard (`/dashboard`)
- Workspaces: create, members, invitations, member removal
- Projects index/create/edit/show/delete
- Project tasks show/create/update/delete; checklists and comments
- Task document import/review
- Project notes, milestones, backlog
- Project PRD documents/version revisions/approve
- Scope baseline/review/decision/backlog
- Project insights
- GitHub connect/sync/disconnect
- Focus index and toggle

### Existing design decisions found in ZIP
- `DESIGN-DECISIONS.md` describes a compact navigation rail + command bar, context-first project workspace, light surfaces/graphite/blue accent.
- `UX-REDESIGN.md` describes one section visible at a time in project workspace, task list/board, accessible select fallback, and no Livewire at that older revision.
- These are source observations, not binding limitations on new UI.

### Screenshot evidence (newer than ZIP)
Latest user screenshot shows `127.0.0.1:8000/my-day` at 14/11/2026, title `Hari ini, satu langkah demi satu langkah.`, section `Agenda hari ini` with empty state, button `Atur 3 prioritas`, right sidebar `Catat tugas cepat`, `Saran untuk harimu`, and a compact left icon rail. `My Day` is **not registered in the inspected older ZIP routes**. It is therefore design feedback for a newer iteration and should not be treated as verified source state for the rest of the updated app.

## Design problem statement
The user reports that some pages show many simultaneous input forms, lack usable list/table views, and have unclear CRUD. These are reported usability concerns, not verified defects on every page. Stitch must solve them through systematic design, not just restyle cards.

## Source of truth precedence
1. This handoff defines the **future design target**.
2. Screen inventory defines full target **coverage**.
3. Old ZIP defines only features verified at that revision.
4. Screenshot defines visible My Day issues at newer revision.
5. Engineering implementers must audit latest repository again before coding.

## Engineering handoff requirements
Export or document screens with exact screen IDs, states, component rules, responsive behavior, field labels, navigation destinations, and CRUD action contracts. Keep design artifacts distinct from assumptions about database schema or existing API. Provide index for every screen and overlay, and note anything not generated due to Stitch limits.

## Privacy/security
Do not upload the full raw repository ZIP to a visual design tool: the ZIP may include `.env`, keys, cache, dependencies and local data. Upload only the safe Markdown brief and optional redacted screenshots. Do not paste secrets into prompts.
