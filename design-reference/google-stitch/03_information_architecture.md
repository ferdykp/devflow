# Information Architecture & Complete Screen Inventory

**Every `Sxx` item is a screen design requirement.** Supporting overlays in the final section must be designed separately or explicitly shown as states of their parent screens.

## Global shell
- Desktop compact rail with readable accessible labels/tooltips and optional expand; clear primary section grouping.
- Top bar: page title/breadcrumb, global search/command launcher, notification entry, account.
- Responsive mobile: bottom nav for Today / Tasks / Planner / Learn / More; contextual back navigation and safe-area-aware actions.
- Global quick-add launches task/event/note from anywhere; routes not overloaded with actions.

## A. Access & setup
| ID | Screen | Primary view |
|---|---|---|
| S01 | Login | Focused authentication form |
| S02 | Registration | Focused account creation form |
| S03 | Welcome / workspace choice | Personal or team intent selection |
| S04 | First-use setup | Short guided preferences, skip allowed |

## B. Personal planning
| ID | Screen | Primary view |
|---|---|---|
| S05 | Overview | Useful cross-module summary |
| S06 | My Day | Priorities + chronological agenda + unscheduled work |
| S07 | Inbox | Fast triage list |
| S08 | All Tasks | Filterable list/table with optional board |
| S09 | Task Detail | Metadata, checklist, activity, actions |
| S10 | Planner / Week | Interactive week time grid |
| S11 | Planner / Day | Time grid + event details |
| S12 | Planner / Month | Month grid + overflow |
| S13 | Routines | Recurring habits list and check-in |
| S14 | Routine Detail | Schedule, history, options |

## C. Growth & learning
| ID | Screen | Primary view |
|---|---|---|
| S15 | Goals | Goal list with status and milestones |
| S16 | Goal Detail | Milestone timeline + linked actions |
| S17 | Learning Hub | Active learning paths and next lessons |
| S18 | Learning Path Detail | Ordered module list |
| S19 | Learning Module Detail | Ordered lessons and practice |
| S20 | Learning Lesson | Reader + notes + exercise status |
| S21 | Study Sessions | Session log list and schedule |
| S22 | Assessments / Review Queue | Practice attempts and review list |
| S23 | Notes Library | List + search + filters |
| S24 | Note Editor | Writing canvas + properties |
| S25 | Weekly Review | Evidence-based insights + reflection |
| S26 | Smart Coach | Explainable recommendation list |

## D. Developer workspace
| ID | Screen | Primary view |
|---|---|---|
| S27 | Projects Index | Project list or structured cards |
| S28 | Project Overview | Project context, health, next actions |
| S29 | Project Tasks | List and board + clear CRUD |
| S30 | Project Task Detail | Checklist, discussion, history |
| S31 | Project Focus | Focused tasks selection and execution |
| S32 | Project Milestones | Milestone list/timeline |
| S33 | Project Notes & Backlog | Two searchable purposeful lists/tabs |
| S34 | Project PRD / Documents | Document version list, status, detail |
| S35 | Project Scope / Reviews | Baseline + change requests + decisions |
| S36 | Project Resume | Context summary + next steps |
| S37 | Project Activity / Insights | Real activity feed + inspectable insights |
| S38 | GitHub Repositories | Linked repos list, connection state, sync |
| S39 | Workspace & Members | Member list, invitations, roles |

## E. Settings, utility & system
| ID | Screen | Primary view |
|---|---|---|
| S40 | Profile & Preferences | Grouped settings form |
| S41 | Working Hours & Schedule Defaults | Weekly availability editor |
| S42 | Notifications | Notification preferences and list |
| S43 | Integrations & AI | Connection states and opt-in control |
| S44 | Search / Command Palette | Searchable actions and destinations |
| S45 | Error / Permission / Offline | Dedicated recovery states |

## Mandatory overlay/component states
- O01 Task quick-add (minimal), O02 Task create/edit full, O03 Task delete/archive confirmation
- O04 Event create/edit and recurring exception dialog; O05 schedule-conflict resolution
- O06 Routine create/edit; O07 routine skip/pause confirmation
- O08 Goal create/edit; O09 Milestone create/edit
- O10 Learning path/module/lesson create/edit; O11 Study session log; O12 Practice/assessment result entry
- O13 Note quick-add/rename/delete; O14 Weekly review completion drawer
- O15 Recommendation preview + explicit approval
- O16 Project create/edit; O17 member invite / role edit / remove
- O18 GitHub connect/disconnect confirmation
- O19 Global success/error toast, inline validation, skeleton/loading, permission empty

## Page-state coverage
For S05–S39 provide populated + meaningful empty (where appropriate); loading and error for asynchronous/data screens. For primary destinations S05, S06, S08, S10, S13, S15, S17, S23, S27, S29, S39, create desktop and mobile compositions. Include responsive treatment for every remaining screen.
