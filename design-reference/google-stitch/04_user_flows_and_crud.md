# User Flows, Interaction Contracts & CRUD Rules

## Universal interaction standard
- **Create:** prominent single primary button (`Tambah tugas`, `Jadwalkan`, `Buat goal`), fast capture when possible; use focused modal/drawer, not multiple always-open form cards.
- **Read:** scan-friendly index + useful filters; clicking row opens detail with breadcrumb/back and stable context.
- **Update:** contextual row menu, detail action or inline editing for status/check only; preserve keyboard focus and give successful feedback.
- **Delete/archive:** exposed through labelled overflow menu and confirmation/undo based on impact; destructive semantics differ from archive.
- **State changes:** complete, skip, postpone, pause, schedule, pin; show what changed and reflect consistently in linked views.
- **Empty:** explain purpose + one suggested action, not decorative filler.
- **Errors:** inline specific error, unsaved data preserved, retry path.
- **Long forms:** labels, grouping, sticky footer actions only when helpful, never force horizontal scrolling on mobile.

## Flow F01 — Quick task capture
Global `+` → choose Task → enter title → create in Inbox → toast + `Buka tugas` → optional assign date/project/goal → appear in selected views. No additional mandatory fields for simple capture.

## Flow F02 — Task lifecycle
Inbox list → choose item → task detail → edit priority/due/status/checklist → schedule time → My Day + Planner share task reference → mark complete → history updated. Uncomplete action reversible.

## Flow F03 — My Day priorities
Today → `Atur prioritas` → select up to 3 from available tasks OR create new → reorder → save → see clear status; max 3 UI with helpful constraint messaging. Other agenda items do not disappear.

## Flow F04 — Planner event and conflict
Week calendar → click free slot → event editor with duration/time → optional recurrence → save → conflict warning if overlapping fixed event → choose modify/cancel; changing recurring item asks `Kejadian ini` versus `Semua kejadian berikutnya` where supported.

## Flow F05 — Routine check-in
Routines → create weekly frequency → appears due on Today → mark complete/skip → habit history and weekly consistency updated; missed day never silently marked completed.

## Flow F06 — Goal to actions
Goals list → create goal → details and milestones → link existing task/learning path → schedule learning → record evidence → update honest progress → archive/complete.

## Flow F07 — Learning journey
Learning Hub → learning path → module → lesson → read material → practice in separate exercise space → manually record/submit result → reviewer feedback and competency evidence → next lesson. Answers hidden until explicitly requested or review mode.

## Flow F08 — Weekly review
Review page → see planned/completed comparison → identify overdue/deferred items → write reflection → choose carryover tasks → schedule following week → confirm; no fabricated scores.

## Flow F09 — Smart Coach suggestion
Coach → list recommendation with cause, impact and source (rule/AI) → preview concrete change → accept or dismiss → only then mutation happens → undo where feasible.

## Flow F10 — Project task to personal day
Projects → task list → `Tambahkan ke Hari Ini` → choose priority/time → My Day shows same task ID with project badge → task completion updates project status; maintain project permissions and private planning context.

## Flow F11 — Project document and scope
Project → Documents list → create/edit document and version → view history → relevant approve/decision interactions; project Scope page shows baseline, proposed changes and decision status clearly, rather than stacked forms.

## Flow F12 — Workspace collaboration
Workspace → members table → invite → pending list → revoke / update role / remove with confirmation; show current access scope.

## Domain-specific CRUD presentation
| Domain | Index primary view | Create | Detail / update | Delete/archive |
|---|---|---|---|---|
| Tasks | List / optional board | Quick-add / modal | Detail drawer or page | Context menu |
| Calendar events | Day/week/month grid | Slot popover/modal | Event panel | Context menu / recurrence prompt |
| Routines | Routine check-in list | Modal | Detail screen | Archive/pause/confirm |
| Goals | Goal progress list | Modal | Goal detail | Archive/delete confirm |
| Learning paths | Path/module list | Modal or focused page | Detail/editor | Archive/confirm |
| Notes | Note list | Quick add | Editor | Overflow menu |
| Projects | Project list | Modal or focused page | Workspace page | Confirm with impact |
| Docs | Version list | Document editor | Version/detail | Appropriate permissions |
| Members | Member table | Invite dialog | Role dropdown | Remove/revoke confirm |

## Keyboard and touch
Use accessible action labels; Tab / Enter / Space / Escape; Cmd/Ctrl+K for command/search; 44px minimum touch targets where practical; mobile actions via clear bottom sheet not hidden hover alone.
