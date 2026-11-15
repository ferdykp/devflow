# DevFlow UI Design System — High-Fidelity Specification

## Desired personality
Premium **calm productivity**, with clarity and precision of a mature developer tool. Editorial, disciplined, practical. Avoid the look of generic AI-generated dashboards.

## Color system (proposed tokens, designer may tune for contrast)
- Canvas: `#F8F9FB`; surface: `#FFFFFF`; subtle surface: `#F1F4F8`
- Primary text: `#17202C`; secondary: `#677384`; border: `#E2E7EE`
- Primary action: `#2463EB`; primary hover: `#194BC0`; focus ring: `#93B4FF`
- Success: `#16835F`; warning: `#A96716`; danger: `#C43B40`
- Dark theme (optional): canvas `#111720`; surface `#1B2430`; text `#E9EDF5`; borders `#354154`
- Use semantic colors sparingly: status communicates meaning; no rainbow badges everywhere.

## Typography
Inter / Geist / equivalent clean sans-serif; optional monospace for code and numbers. 12px captions, 14px dense secondary labels, 16px body, 20–24px section headers, 28–34px key page title. Maintain legible line heights. Do not overuse giant headings.

## Spatial system
4 / 8 / 12 / 16 / 24 / 32px spacing scale. Card radius 12–16px, input radius 9–12px, quiet 1px borders. Desktop max content width appropriate to tables and calendar; avoid overly narrow full-width dashboard. Use 12-column or adaptive CSS grid. Desktop compact navigation 74–80px, expandable when appropriate.

## Layout recipes
- **Index page:** title + concise description, filters/search/action row, primary list/table/grid, pagination/empty state. No permanent full create form.
- **Detail page:** title/actions + primary object content + metadata activity panel as needed.
- **My Day:** date controls + 3 priorities + chronological agenda + unscheduled tasks; recommendations secondary, not dominant.
- **Planner:** calendar primary, event details contextual.
- **Learning:** paths/modules list with clear resume CTA; lesson content distraction-free with sidebar TOC.
- **Settings:** forms are appropriate; group by topic, indicate save states.

## Shared components and explicit states
- AppShell, CompactRail, MobileNav, Topbar, Breadcrumb, PageHeading
- CommandPalette, GlobalQuickAdd, EmptyState, Skeleton, Toast, InlineAlert
- DataTable, ResponsiveList, ListRow, StatusPill, PriorityIcon, Tag
- TaskRow, TaskDetailDrawer, CalendarBlock, EventPopover, RoutineRow
- GoalProgressRow, ModuleItem, NoteItem, ActivityTimeline, RecommendationCard
- Modal, Drawer, ConfirmationDialog, DropdownMenu, Tabs, SegmentedControl
- Button primary/secondary/quiet/destructive + disabled/loading; Input, Textarea, Select, DateTimePicker, Checkbox, Radio, FileUpload

## Interaction behaviors
- One primary CTA per major content region.
- Row click = open details; checkbox = complete; overflow = contextual operations.
- Modals avoid nested modals. Close behavior must warn for unsaved changes when relevant.
- Status change provides feedback and respects rollback on error.
- Lists allow filter/search/sort, support zero and many items, show item count.
- Use subtle 120–240ms transitions; reduced-motion alternate; no fake glass overlay.

## Responsive
- 1440: rail + flexible main canvas; multi-column only when justified.
- 834: collapse side panels, preserve readable tables or convert to responsive list.
- 390: bottom navigation, sticky/contextual primary CTA, full-width dialogs/bottom sheets, calendar day agenda fallback, no horizontal overflow.

## Content/microcopy rules
- Indonesian first: `Hari Ini`, `Tugas`, `Tambah tugas`, `Simpan perubahan`, `Batalkan`, `Jadwalkan ulang`, `Tandai selesai`.
- State exact consequence: `Hapus tugas ini?` not generic `Yakin?`.
- Relative dates only with clear absolute alternative; time 24-hour format.
- Empty states include practical CTA: `Belum ada tugas. Tambahkan tugas pertama.`
- Do not use empty fake graphs, hardcoded performance scores, misleading "AI knows your mood" claims.
