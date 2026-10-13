# Browser E2E: workspace selection stabilization

Playwright timed out waiting for the newly created workspace label in the project dropdown. It was not a browser installation or Vite build problem.

Changes:
- Workspace creation now redirects explicitly to `projects.index` rather than relying on the HTTP Referer (`back()`).
- Browser test waits for the workspace POST, verifies navigation, and asserts its option exists before selecting.
- Feature test confirms workspace ownership, persistence, redirect, and presence on Projects page.

Run `php artisan test --filter=ProjectIntegrityTest` and `npm run test:e2e` locally. The browser scenario still needs verification after this change.
