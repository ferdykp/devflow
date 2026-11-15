# Design Acceptance & Coverage Checklist

## Global gates
- [ ] G01 All S01–S45 screens explicitly designed or enumerated as not generated.
- [ ] G02 All O01–O19 overlays represented by a concrete screen/state, not merely a bullet promise.
- [ ] G03 One consistent shell, navigation, components, typography, spacing and status conventions.
- [ ] G04 Every management page is data-first with an appropriate list, grid, board, timeline or calendar.
- [ ] G05 Each primary entity exposes create, read, update, delete/archive and domain-specific actions, with roles considered.
- [ ] G06 Main screens show meaningful populated and empty states.
- [ ] G07 Modals/drawers/forms clearly labelled; no sprawling multi-form pages.
- [ ] G08 Desktop 1440 and mobile 390 coverage for S05/S06/S08/S10/S13/S15/S17/S23/S27/S29/S39; remaining screens adapt appropriately.
- [ ] G09 No fake persistence, fake AI certainty, fabricated metrics or unlabelled demo data.
- [ ] G10 Accessible focus, keyboard controls, touch targets, labels, contrast and error feedback.

## Task execution gates
- [ ] T01 Task can be captured in <= 2 lightweight actions after opening quick-add.
- [ ] T02 Task row actions are discoverable without hover-only dependencies.
- [ ] T03 Changing task status in Today clearly maps to same task in Projects.
- [ ] T04 Priority selection has defined max 3 and clear rejection messaging.
- [ ] T05 Delete/archive has a specific consequence and confirmation/undo.

## Calendar and routine gates
- [ ] C01 Fixed events and flexible blocks look different.
- [ ] C02 Week/day/month views are not identical mockups.
- [ ] C03 Overlap and recurrence exception flows are shown.
- [ ] C04 Planned action does not appear as automatically completed.
- [ ] C05 Habit skip/pause/check-in states have distinct visual feedback.

## Growth/learning gates
- [ ] L01 Goal list and goal details expose next actions and evidence.
- [ ] L02 Path/module/lesson hierarchy is unmistakable.
- [ ] L03 Practice answers hidden until intentional review.
- [ ] L04 Learning completion ≠ reading progress ≠ skill verified.
- [ ] L05 Weekly review actions allow carryover and next-week planning.
- [ ] L06 Coach offers reason + preview + explicit accept or dismiss.

## Developer and privacy gates
- [ ] D01 Project list, project workspace and task list share navigation language.
- [ ] D02 Documents, scope, milestones, notes and GitHub have explicit useful primary views.
- [ ] D03 Personal vs workspace data context is visible; no accidental sharing implied.
- [ ] D04 Member roles and destructive actions convey scope and consequences.

## Final coverage output format
Return a matrix `Screen ID | Screen name | Desktop | Mobile | Populated | Empty | CRUD/Actions | Status` with actual generated coverage, plus `Overlays ID | Design location | Status`. Never claim completeness if some screens are only named.
