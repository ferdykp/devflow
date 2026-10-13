# E2E selectors and login redirect synchronization

- The task title is also present in an accessible screen-reader label (`Status for ...`). Playwright's substring text lookup therefore matched two DOM elements and raised a strict-mode violation. All task text assertions now use an exact text match.
- An invalid login returns a POST redirect and then renders a fresh GET /login page containing the server-side validation error. The browser test now waits for the final GET document response before inspecting the alert; otherwise its first assertion may race the redirect.
- No routes, database migrations, UI components, or business logic have been changed.

Run: `php artisan test && npm run build && npm run test:e2e`.
