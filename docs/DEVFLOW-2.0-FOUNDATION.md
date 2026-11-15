# DevFlow 2.0 Foundation

This release adds a personal productivity layer while preserving the existing project and workspace workflows. Personal records are owned by a user; project tasks continue to follow workspace access rules.

## Product areas

- **Dashboard and My Day:** timezone-aware greeting, focus tasks, due and overdue work, agenda, routines, learning activity, goals, project activity, conflicts, and quick capture. My Day also supports completion, deferral, routine check-ins, and daily reflection.
- **Task Inbox:** personal and accessible project tasks in list or board views, search and status filtering, priority, due and scheduled times, estimates, categories, tags, recurrence, goal and lesson links, checklist items, task notes, history, and bulk status or priority updates.
- **Calendar:** day, week, and month views; UTC storage with the user’s IANA timezone for display; all-day and flexible blocks; one-off event editing; daily, weekly, and monthly recurrence; per-occurrence rescheduling, completion, and cancellation; series cancellation; and conflict reporting.
- **Routines:** daily or selected weekday patterns, weekly targets, preferred times, estimated durations, minimum/normal/extended actions, goal and learning links, weekly check-in history, pause and resume, and reusable starter templates.
- **Goals:** short, medium, and long horizons; categories and target dates; numeric progress with dated evidence; milestone-based progress without invented percentages; linked tasks, routines, learning paths, notes, and agenda items; status and review dates.
- **Learning Hub:** learning paths, modules, lessons, objectives, Markdown notes, references, private file attachments, assessments, separate content/practice/competency states, evidence, knowledge gaps, review dates, planned and actual learning time, and session history. Software Engineering Fundamentals and English Development are starter outlines, not accredited curricula.
- **Personal Notes:** private notes, collections, tags, search, pinning, archiving, and links to permitted tasks, projects, goals, and lessons.
- **Weekly Review:** completed and deferred work, learning time, routine check-ins, goal progress, calendar conflicts, recent history, reflection, planning notes, and carry-forward of existing tasks.
- **Smart Coach:** deterministic suggestions for focus selection, overdue work, review dates, missed routine check-ins, weekly review, and calendar conflicts. Recommendations remain pending until confirmed or dismissed; confirmation does not silently reschedule work.
- **Settings:** per-user IANA timezone preference.

## Data and database naming

Personal data is scoped through owner relationships and request-level access checks. Calendar event exceptions represent changes to a single recurring occurrence; task recurrence creates the next task only when the current task is completed. Compound indexes use concise aliases such as `task_rec_idx_uniq`, `cal_ev_exc_occ_uniq`, `week_rev_owner_week_uniq`, and `coach_owner_kind_status_uniq` to stay within common database identifier limits.

The migration files are additive. The migrations that add ownership and cross-module links intentionally refuse destructive rollback because they may contain user-created records. Do not use a database reset against an environment with user data.

## Verification

Run the full PHP suite with `php artisan test --compact`. Build frontend assets with `npm run build`. Browser coverage for login and registration password visibility is in `e2e/password-visibility.spec.ts`.
