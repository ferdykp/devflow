# Detailed Screen Specifications — Required Design States

This file supplements `03_INFORMATION_ARCHITECTURE.md`. For each screen ID, **render the primary data view, available actions, key secondary panel, empty state and responsive behavior**.

## S01–S04 Access and onboarding
- **S01 Login:** email/password labels, show password, forgot guidance, validation, loading, error; focused auth layout.
- **S02 Registration:** name, email, password, confirmation, privacy copy, validation and success.
- **S03 Welcome:** clear Personal and Team contexts, existing workspaces, enter/choose/create/skip; no maze.
- **S04 First-use setup:** timezone, working days/time, learning aims as optional presets, customize later, skip.

## S05–S09 Overview, My Day, Inbox, Tasks
- **S05 Overview:** next action, today's priorities, active goals, learning resume, project recency, realistic compact metrics; quick create.
- **S06 My Day:** selected date, pinned max-3 priorities, chronological agenda, unscheduled queue, complete/snooze/reschedule, focused add CTA, compact coach suggestion; populate with at least 5 mixed items; distinct empty day. **Do not reproduce screenshot's dominant empty card + permanently open input.**
- **S07 Inbox:** triage list with title, capture time, project/goal assignment, schedule, dismiss/archive actions, bulk handling.
- **S08 All Tasks:** filterable table/list: title, status, priority, context, due date, assignee where applicable, actions; optional board toggle and responsive cards.
- **S09 Task Detail:** title, notes, labels, checklist, links, due/schedule fields, project context, history, edit, complete, archive/delete; clear secondary actions.

## S10–S14 Planner and routines
- **S10 Week:** columnar week calendar with fixed event vs task block appearance, create on blank slot, overlap warnings, date navigation, unscheduled tasks drawer.
- **S11 Day:** readable hourly timeline, details popover, all-day and free-time hints.
- **S12 Month:** month grid with overflow, event drill-in, current date and date jump.
- **S13 Routines:** list by due today/weekly, check-in actions, frequency, progress based on check-ins, create CTA.
- **S14 Routine Detail:** schedule and recurrence rules, visible history log, pause/skip/resume/edit/archive options.

## S15–S22 Goals and Learning
- **S15 Goals:** active/completed/archived filters, goal list with deadlines, next milestone, linked activities, optional progress where measurable.
- **S16 Goal Detail:** milestones timeline, evidence, task/learning links, next action and edit/archive.
- **S17 Learning Hub:** enrolled/active paths, continue-learning row, domain filters, recent sessions; empty start guide.
- **S18 Learning Path:** overview, ordered modules, prerequisites, overall progress based on meaningful statuses, add/edit path.
- **S19 Module:** lesson list, practice checklist, assessment status, unlock/resume states.
- **S20 Lesson:** distraction-free reader, notes, optional example code, exercise panel, progress and previous/next; hidden answer until review.
- **S21 Study Sessions:** log as table/list: date, path/module, duration, outcome, reflection; create, edit, delete log.
- **S22 Assessments:** upcoming practice queue, attempted/completed states, feedback panel, score/evidence without fabricated grading.

## S23–S26 Notes, Review, Coach
- **S23 Notes:** searchable list with sorting, topic tags, linked context, new note CTA, archived toggle.
- **S24 Note Editor:** page editor with title, formatting, autosave/saved state, tags and links; clear back to library.
- **S25 Weekly Review:** week selector, planned vs completed, deferred list, learning activity, routines, reflection writing area as *one intentional editor*, next-week commitments CTA.
- **S26 Smart Coach:** recommendation list grouped by urgency, why suggested, data basis, proposed change preview, accept/dismiss, manual alternatives, AI unavailable state.

## S27–S39 Developer Workspace
- **S27 Projects Index:** searchable project cards/list, statuses, team scope, open/create, empty.
- **S28 Project Overview:** project identity, active tasks, next milestone, latest activity, navigation into subviews.
- **S29 Project Tasks:** list/table/board toggle; filtering, priority, status, assignee; create/edit/view/delete.
- **S30 Task Detail:** checklist, comments, events/activity, owner, due date, actions, permission-aware controls.
- **S31 Project Focus:** 1–3 top task selection, reorder/complete, next recommended task, clear ability to leave focus.
- **S32 Milestones:** date/status lists or timeline, milestone create/edit, related tasks.
- **S33 Notes & Backlog:** tabs; each list-first with create action; note editor and backlog conversion.
- **S34 Documents/PRD:** document index with statuses/versions, open detail/editor, revision and approval actions where permitted.
- **S35 Scope:** baseline panel, change requests table, decision details and history, approve/reject controls with labels.
- **S36 Resume:** clear project summary, last changed, open threads, next actions, data freshness.
- **S37 Activity & Insights:** activity stream, filters, real metrics only, inspectable insight sources and empty/error.
- **S38 GitHub:** connected repos list, sync status, last sync, permission, connect/disconnect flow, error and not-connected states.
- **S39 Workspace:** member table, invitation list, role labels, invite modal, revoke/remove, permission explanation.

## S40–S45 Settings and utilities
- **S40 Profile:** name/avatar (optional), timezone, locale, account details; grouped form and save feedback.
- **S41 Work schedule:** weekday blocks, default availability, exceptions, preset recommendations (editable).
- **S42 Notifications:** toggle list + channel and reminder settings, notification center empty/populated.
- **S43 Integrations:** GitHub and AI config status; secure connection/permission UI; missing-secret honest state.
- **S44 Command palette:** overlay search destinations/tasks, recent searches, keyboard hints, no fake indexing.
- **S45 Recovery:** 403, 404, 500, offline, no permission with appropriate navigation and retry.

## Screen content example fixtures (non-real)
- Personal task: `Review Bab 01 — Computational Thinking`; status `Belum mulai`; planned 19.00; goal `Siap live coding`.
- English task: `Latihan speaking 20 menit`; planned 20.15; path `English Fundamentals`.
- Developer task: `Perbaiki validasi stok`; project `GrowPOS (contoh)`; deadline tomorrow.
- Habit: `Baca materi 15 menit`; 3 of 4 planned weekly occurrences.
- Coach recommendation: `Sesi belajar bentrok dengan acara pukul 19.00`; offer change preview; no assumption about user's emotion.
- Project note: `Investigasi query inventory` linked to task.

Do not imply these sample records already exist in the user's account or old repository.
