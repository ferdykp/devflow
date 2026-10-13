# DevFlow Task & Project UX redesign

## Product direction
- Context-first project workspace. One section visible at a time, rather than one very long page.
- Tasks offer board and list views with existing status enum. Board drag/drop PATCHes the same authenticated Laravel route, and accessible select remains available for keyboard and mobile.
- Project Overview is contextual and never invents repository history.
- Floating toast stack automatically dismisses after 4.5s / 6.5s / 8.5s; hover/focus pauses dismissal. Validation retains server-side Laravel handling.
- No Livewire or new UI package. All other endpoints remain unchanged.

## Important limits
- Board currently displays one paginated set (60 tasks) across columns; it is not globally grouped across all pages.
- Moving cards triggers a full refresh after successful PATCH so all counters update from the server.
- Additional sections reuse their existing forms and actions, now in focused pages.
- CI/runtime and browser testing should be completed before production deployment.

## Run
```sh
composer install
npm ci
php artisan test
npm run build
```
