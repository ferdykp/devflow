# DevFlow — Design System

**Design direction:** clean editorial SaaS; professional, precise, high contrast, modern, polished but restrained. Goal: **easy to understand, fast to scan, minimal cognitive load**. No decorative clutter or overloaded cards.

## 1. Design principles
1. Clarity before decoration: one dominant action per view; meaningful hierarchy.
2. Consistent spacing: 4px base grid, 8/12/16/24/32 rhythm.
3. Strong contrast: readable text, clear button hierarchy and status meaning; target WCAG AA contrast.
4. Context-first: show next action, source, freshness, and uncertainty alongside AI claims.
5. Progressive disclosure: optional features appear contextually, not as mandatory setup blockers.
6. Preserve trust: never fake completion %, AI certainty, or synced state.
7. Responsive from 360px to 1440px+; keyboard and screen reader accessibility.

## 2. Visual tokens (starting palette)
| Token | Light | Dark | Use |
|---|---|---|---|
| `--bg` | `#F7F8FA` | `#0E1014` | application canvas |
| `--surface` | `#FFFFFF` | `#171A20` | cards, panels |
| `--surface-muted` | `#F1F3F6` | `#21252D` | nested sections |
| `--border` | `#E2E6EB` | `#353B45` | dividers |
| `--text` | `#111827` | `#F5F7FA` | primary text |
| `--text-muted` | `#566273` | `#AAB5C5` | secondary text |
| `--primary` | `#275DF3` | `#7296FF` | selected/action |
| `--success` | `#157347` | `#47BC87` | confirmed success |
| `--warning` | `#A65B0A` | `#F0B05B` | attention |
| `--danger` | `#BE3030` | `#FF7777` | errors/destructive |

Check AA contrast in actual component pairings, particularly subdued text and light-colored status badges; adjust tokens as required. No gradients required by default. Status must include readable label, not color alone.

## 3. Typography
Use **Plus Jakarta Sans** (if licensed/available via normal web dependency) or `Inter`, fallback system-ui. Base 14–16px, line-height 1.5. Headings: 28/24/20/16px, weights 600–700; body 14–16px, labels 12–13px. Avoid tiny 10px essential text. Numbers tabular where appropriate. Restrict paragraph width ~70 characters.

## 4. Dimensions
- Desktop sidebar 240px expanded, collapsed 72px; content max width 1440px; content padding 24–32px.
- Tablet content padding 20px; mobile 16px, nav as compact sheet or bottom navigation (only frequent actions).
- Grid: 12 columns desktop, 6 tablet, 1–2 mobile depending content; dashboard KPIs 4/2/1 responsive.
- Borders 1px, card radius 14–16px, input 10–12px, buttons 10–12px, modal 18px.
- Inputs/buttons minimum 40–44px high; touch target at least 44px where practical.
- Shadows subtle, avoid heavy stacked elevation; use borders for section definition.

## 5. Layout specification
**App shell:** fixed/steady sidebar desktop; top bar with breadcrumb, global search, create project button, notification/profile. Main content: page title, short supporting description, primary action aligned consistently, optional view filters, content.

**Dashboard order:** (1) daily focus / recommended resume; (2) actionable alerts (failed sync/needs review); (3) project list with last activity & source; (4) cross-project activity; (5) optional metrics. Resist a wall of KPI cards.

**Project detail:** title/status and last sync, contextual actions; tabbed Overview/Tasks/Activity/Resume/Backlog, PRD/Scope and Milestones surfaced when relevant (plus clear Add PRD and Add Milestone CTA).

## 6. Component contract
| Component | Rules |
|---|---|
| Buttons | Primary for one clear action; secondary/outline for alternatives; destructive confirmation via custom modal, not `window.confirm` |
| Cards | Information grouped by meaning; padding 16–24px; headline + concise secondary line |
| Tables | clear header, row hover, 44px row min, truncation with tooltip, responsive fallback stacked items |
| Forms | label above, helper/error below, visible required/optional; inline feedback without clearing input |
| Badges | status vocabulary consistent; include text/icon; no decorative colors |
| Modals | title, explanation, actionable footer, focus trap/escape behavior |
| Empty states | explain why empty; show one small next action; never suggest missing PRD means broken project |
| Loading | skeleton for known layouts, inline spinner for actions, disable duplicate submit |
| Errors | human-readable, recovery action, preserve form input, sync retry CTA |
| Toasts | confirmations concise; crucial errors persist in view until addressed |
| Progress | show % only when denominator meaningful; otherwise `Not estimated` and alternative task/commit metadata |
| AI cards | show evidence, generated timestamp, uncertainty, optional provider badge |

## 7. Interaction and microcopy
- Avoid jargon for generic users: “Last synced” rather than “Webhook delivered.”
- Context actions: “Continue project”, “Save idea for later”, “Review scope”, “Connect GitHub”.
- No silent irreversible actions. Disable controls based on permission with explanatory tooltip when necessary.
- Animations 120–220ms, ease-out; honor `prefers-reduced-motion`; avoid bouncy dashboard widgets.

## 8. Icons
Prefer one coherent icon system (Font Awesome Free, consistent weight). Use icons with labels in primary navigation, not icon-only ambiguous actions. Document semantic mappings (project=folder, activity=code-branch, PRD=file-lines, backlog=lightbulb, focus=bullseye, resume=clock-rotate-left).

## 9. Accessibility & mobile
All form controls labeled; tab order sensible; visible 2px focus ring; actions reachable by keyboard; dialogs properly managed; tables horizontally scroll only where absolutely necessary; dark mode token contrast audited; avoid layout shift and full-height modals on small screens.

## 10. Design acceptance
A first-time user can create a project with **name alone** in under a minute without being forced into PRD/GitHub setup. In project detail, the next action is discoverable. Empty/no-data states are attractive, clear, and truthful. Visual hierarchy survives mobile, long project titles, errors, and dark mode.
