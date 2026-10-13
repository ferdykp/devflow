# DevFlow Task Board — drag-and-drop reliability patch

- `TaskController::update()` now returns a JSON confirmation containing the saved task ID and status when `Accept: application/json` is sent; existing native form submissions continue using redirects.
- Drag and drop sends an explicit XMLHttpRequest + CSRF header, checks the confirmed persisted status, and prevents overlapping requests. On error the card is restored to its original position and a single error toast is displayed.
- New feature tests cover JSON persistence and authorization for a different user.

## Verify on Mac

```bash
php artisan test
npm run build
```

Then open a project Tasks Board, drag a task to a different status, and refresh the page to confirm persistence. Also test the card's status select to verify the native form fallback still works.

If drag fails, open browser DevTools > Network, find the PATCH request to `/projects/{id}/tasks/{id}`, and inspect the HTTP status and JSON response. Typical codes: 419 (CSRF/session), 401/302 (authentication), 403/404 (access), 422 (validation), 500 (server error). The front end prints details in the console. Do not disable security middleware to work around failures.

This patch has passed syntax checks. Laravel/PHPUnit and interactive browser tests have not been executed in the packaging environment.
