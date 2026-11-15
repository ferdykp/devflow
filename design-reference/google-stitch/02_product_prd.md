# Product Requirements Document — DevFlow 2.0

## Vision
A practical Personal Productivity & Growth Workspace: **Capture → Organize → Plan → Do → Review → Grow**. Connect everyday life, deliberate learning and developer delivery without duplicating objects across views.

## Users
- **Solo professional / developer:** manages personal actions, professional projects, deep-work sessions and learning.
- **Learner:** builds structured coursework, practices independently, reviews competencies.
- **Workspace contributor:** collaborates on projects; cannot see another user's private personal planner unless explicitly shared.
- **Workspace owner:** manages invitations, members, roles and project governance.

## Core jobs to be done
- Capture an idea/task in seconds; organize later.
- Look at Today and immediately understand next action, deadlines and realistic capacity.
- Schedule work, learning, and recurring routines without losing task context.
- Connect a goal to milestones and objectively track progress.
- Return to a learning module and pick up where they left off.
- Prepare weekly plans and compare planned vs completed activity.
- Maintain developer project work and synchronize task status safely.

## Product modules and requirements
### Identity & onboarding
Login, register, welcome flow, first-use setup (timezone, weekly work hours, primary outcomes), sample/template option, quick start skip. Avoid mandatory long questionnaire.
### Overview
Today snapshot; next scheduled block, 3 top priorities, task health, active goals, learning next steps, project updates, clear quick actions.
### My Day
Date navigation, pinned priorities, agenda by time, unscheduled queue, task completion, reschedule, focus start, end-of-day reflection.
### Inbox & Tasks
Quick capture; list-first table; filters, search, sort; status, priority, due date, subtasks, linked project/goal; full CRUD; statuses actionable.
### Planner
Day/week/month calendars; fixed events vs flexible task blocks; conflict and drag/move state; event creation and recurrence exceptions.
### Routines
Daily/weekly pattern, desired frequency, reminder, complete/skip/pause, history and review; support 15min minimum variant.
### Goals
List; detail with milestones, links, evidence, checkpoints and completion; honest progress (no synthetic percentages).
### Learning Hub
Paths → modules → lessons, attachments, study sessions, practice / assessments, competency evidence, review queue. Domains generic (programming, English examples).
### Notes
Searchable notes library with clean editing, associations to goals, tasks, learning and projects, trash/archive rules.
### Weekly Review
Insights based on data; weekly reflection, planned vs completed, carryover actions, next-week planning.
### Smart Coach
Explainable suggestions, underlying data/rule, accept/dismiss/reschedule; offline rule-based core; optional configured AI assist; requires user action for mutations.
### Developer workspace
Projects, tasks, board/list, focus, milestones, notes, backlog, PRD/documents, scope approvals/reviews, project resume/context, activity, insights, GitHub repositories, workspace members and invitations.
### Settings
Profile, preferences, language/timezone, schedule templates, notifications, account security, integrations, AI settings if configured, privacy.

## Hard product invariants
- A single task ID can appear across relevant views; views are projections, not duplication.
- An event or schedule block does not itself complete a task or habit.
- Moving a planned session does not erase its history.
- Personal entities are private by default and distinguished from workspace-scoped objects.
- Suggestion ≠ applied change. AI confirmation required and provenance visible.
- Goal competence and learning completion are evidence-based, not time-spent proxies.
- Every primary module has usable empty state and genuine next action.

## Non-goals for the design brief
No requirement to build real AI models, promise official TOEFL/IELTS certification, render unrequested finance/budget platforms, or fabricate completed integration status.

## Sample scenario (illustrative only)
Monday–Friday 09.00–17.00 work; Monday/Wednesday 19.00 programming, Tuesday/Thursday 19.00 English, Friday flexible, Saturday deeper coding, Sunday review. User can change every schedule. Goal: 'Lebih percaya diri saat live coding'; learning path: 'Software Engineering Fundamentals'.

## Success criteria
New user understands navigation and adds first task without onboarding dead-end; can execute full task lifecycle; can schedule/reschedule and see consistent view across modules; can follow a learning path without seeing answers automatically; can distinguish private and team-scoped data; can use core flows on mobile.
