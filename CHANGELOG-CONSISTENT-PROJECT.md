# Consistent Project Workspace

- Shared project header and contextual tab navigation now used by both project overview/sections and dedicated Resume page.
- Resume tab is marked active and all project destinations remain visible, including More submenu.
- Removed Resume-only top-level project header and duplicate status chip.
- Preserved Resume data, links, backend and routes.
- Added regression assertion for active tab in Resume.

## Verification
Syntax and archive checks only. Run `php artisan test` and `npm run build` in your local environment; review browser layouts at mobile and desktop widths.
